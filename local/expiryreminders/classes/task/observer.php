<?php
namespace local_expiryreminders\task;                                            //Required to be first on the page
use \DateTime;
use \DateInterval;
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
class observer 
{
    public static function user_enrolment_updated(\core\event\user_enrolment_updated $event)
    {
      $courseid = $event->courseid;
      $course = get_course($courseid);
      $coursename = $course->fullname;
      //Change tutorid when moving to production
      global $tutoruserid;
      $tutoruserid = 17;

      if 
      (str_contains($coursename, "Grid Connected PV Systems")
      || str_contains($coursename, "Battery Storage Systems for Grid-Connected PV Systems")
      || str_contains($coursename, "Stand Alone Power Systems")
      || str_contains($coursename, "Electrical Basics Examination")) 
      { 
        self::handle_enrolment_event($event, $courseid); 
      }

    }
    public static function user_enrolment_created(\core\event\user_enrolment_created $event)
    {
      $courseid = $event->courseid;
      $course = get_course($courseid);
      $coursename = $course->fullname;
      //Change tutorid when moving to production
      global $tutoruserid;
      $tutoruserid = 17;

      if 
      (str_contains($coursename, "Grid Connected PV Systems")
      || str_contains($coursename, "Battery Storage Systems for Grid-Connected PV Systems")
      || str_contains($coursename, "Stand Alone Power Systems")
      || str_contains($coursename, "Electrical Basics Examination")) 
      { 
        self::handle_enrolment_event($event, $courseid); 
      }
    }

    private static function handle_enrolment_event($event, $courseid) {
      require 'access.php';
      global $DB, $tutoruserid, $fstartdate, $fenddate, $contact_id, $student_email, $user;
      // error_log(var_export($event, true));
      $user = $event->relateduserid; 
      //finding studentemail
      $email = $DB->get_record('user', array('id' => $user));
      $student_email = $email->email;


      //URL TO FIND ALL FIELD IDS IN ACTIVE CAMPAIGN (can use in postman)
      //CURLOPT_URL => "https://gses.api-us1.com/api/3/fields?limit=1000"


      $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
      $enrolid = $instance->id;
      
      //------------Finding course expiration--------------------------------------------------------------
      $search = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $user]);
      $expiration = $search->timeend;
      $startdate = $search->timestart;
      $fenddate = date('d/m/Y', $expiration);
      $fstartdate = date('d/m/Y', $startdate);


      //Handles AC displaying wrong date to student by subtracting 1 day to account for midnight date in Moodle
      $newenddate = DateTime::createFromFormat('d/m/Y', $fenddate);
      $newstartdate = DateTime::createFromFormat('d/m/Y', $fstartdate);
            
      $dayDifference = $newenddate->diff($newstartdate);

      // error_log('Day Difference: ' . var_export($dayDifference,true));

      if ($dayDifference->d > 0) {
          $newenddate->modify('-1 day');
          $fenddate = $newenddate->format('d/m/Y');
      }
      //---------------------------------------------------------------------------------------------------


      $api_url = 'https://gses.api-us1.com/api/3/contacts';
      $contact_id = '';
      
      // Get contact ID from email 
      $curl = curl_init();
      curl_setopt_array($curl, [
        CURLOPT_URL => $api_url.'?email='.urlencode($student_email).'&status=-1',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_ENCODING => "",
        CURLOPT_MAXREDIRS => 10,
        CURLOPT_TIMEOUT => 30,
        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
        CURLOPT_CUSTOMREQUEST => "GET",
        CURLOPT_HTTPHEADER => [
          "Api-Token: ".$api_token,
          "accept: application/json"
        ],
      ]);

      $response = curl_exec($curl);
      $err = curl_error($curl);
      curl_close($curl);

      //error handling
      if ($err) {
        error_log("cURL Error #:" . $err);
        error_log("Error #1: Student id not found");
      } 
      else {
        $data = json_decode($response, true);
      
      //Ensures the contact exists
      if (!empty($data['contacts'])) {
          $contact_id = $data['contacts'][0]['id'];


            //Gets an array of fieldValues for all customfield ids of the specified $contact_id
            $curl = curl_init();
            curl_setopt_array($curl, [
              CURLOPT_URL => "https://gses.api-us1.com/api/3/contacts/{$contact_id}/fieldValues",
              CURLOPT_RETURNTRANSFER => true,
              CURLOPT_ENCODING => "",
              CURLOPT_MAXREDIRS => 10,
              CURLOPT_TIMEOUT => 30,
              CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
              CURLOPT_CUSTOMREQUEST => "GET",
              CURLOPT_HTTPHEADER => [
                "Api-Token:". $api_token,
                "accept: application/json",
                "content-type: application/json"
              ],
            ]);
            $response = curl_exec($curl);
            $err = curl_error($curl);
            curl_close($curl);
            if ($err) {
              echo "cURL Error #:" . $err;
              error_log("Error #2: There was a problem with the GET request for the field value");
            } else {
              //Active Campaign courseid field ids
              $course_id_1 = 57;
              $course_id_2 = 58;
              $course_id_3 = 59;
              $course_id_array = array($course_id_1, $course_id_2, $course_id_3);
              
              //Finds the courseid values for all fields
              $fieldvalues = json_decode($response, true)['fieldValues'];
              $hasduplicateid = false;
              $coursevalues=[];
              foreach ($fieldvalues as $fieldvalue) {
                if (in_array($fieldvalue['field'], $course_id_array)) {
                    $value = $fieldvalue['value'];
                    if (in_array($value, array_column($coursevalues, 'value'))) {
                        // Error: Value exists more than once
                        $hasduplicateid = true;
                        break; // Exit the loop if error occurs
                    }
                    $coursevalues[] = array(
                        'field' => $fieldvalue['field'],
                        'value' => $value
                    );
                }
            }
  
          
          
          }

              if (!$hasduplicateid) {
                self::checkcoursevalues($coursevalues, $courseid);
              }else{
                error_log("
                Error #3: Duplicate courseids exist for active campaign user below:
                  ActiveCampaign ContactId: $contact_id
                  Moodle UserId: $user
                  Moodle Email: $student_email
                  Course Expiration Start Date: $fstartdate
                  Course Expiration End Date: $fenddate
                  ");
                  //Tutor account ID
                  $conditions = ['id' => $tutoruserid];
                  $table = 'user';            
                  $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
                  $emailuser = new \stdClass();
                  $emailuser->email = $user_object->email;
                  $emailuser->username = $user_object->username;
                  $emailuser->firstname = $user_object->firstname;
                  $emailuser->lastname = $user_object->lastname;
                  $emailuser->maildisplay = $user_object->maildisplay;
                  $emailuser->mailformat = 1;
                  $emailuser->id = $user_object->id;
                  $emailuser->firstnamephonetic = $user_object->firstnamephonetic;
                  $emailuser->lastnamephonetic = $user_object->lastnamephonetic;
                  $emailuser->middlename = $user_object->middlename;
                  $emailuser->alternatename = $user_object->alternatename;
                  $first = $emailuser->firstname;
                  $last = $emailuser->lastname;
                    $messageHtml = "
                    <p><b>ERROR:</b> Duplicate courseids exist for active campaign user below:</p>
                    <br />
                    <b>Debugging log</b>
                    <br />
                    <p><i>Beep..Boop..Beep..</i>Human Brain Required!:</p>
                      <ul>
                      <li>ActiveCampaign ContactID: ". $contact_id . "</li>
                      <li>Moodle UserID: " . $user . "</li>
                      <li>Moodle CourseID: " . $courseid . "</li>
                      <li>Moodle Email: " . $student_email . "</li>
                      <li>Course Expiration Start Date: " . $fstartdate . "</li>
                      <li>Course Expiration End Date: " . $fenddate ."</li>
                      </ul>
                      <br />
                    <b>Possible Solutions</b>
                    <ul>
                    <li>Check the active campaign contact information and ensure there is a courseid of ".$courseid ." in one of the available slots.</li>
                    <li>Check if all enrolments are within 18 months of the start date and the contact is enrolled in 3 or more accreditation courses</li>
                    </ul>
                      ";
                    $subject = "Expiry reminder failed";
                    email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);    
              }
            
            }else {
              //error for if student email doesn't exist in Active Campaign
              error_log("
              Error #4: The contact with email of $student_email does not exist in Active Campaign. Please see user information:
                ActiveCampaign ContactId: $contact_id
                Moodle UserId: $user
                Moodle Email: $student_email
                Course Expiration Start Date: $fstartdate
                Course Expiration End Date: $fenddate
                ");
                //Tutor account ID
                $conditions = ['id' => $tutoruserid];
                $table = 'user';            
                $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
                $emailuser = new \stdClass();
                $emailuser->email = $user_object->email;
                $emailuser->username = $user_object->username;
                $emailuser->firstname = $user_object->firstname;
                $emailuser->lastname = $user_object->lastname;
                $emailuser->maildisplay = $user_object->maildisplay;
                $emailuser->mailformat = 1;
                $emailuser->id = $user_object->id;
                $emailuser->firstnamephonetic = $user_object->firstnamephonetic;
                $emailuser->lastnamephonetic = $user_object->lastnamephonetic;
                $emailuser->middlename = $user_object->middlename;
                $emailuser->alternatename = $user_object->alternatename;
                $first = $emailuser->firstname;
                $last = $emailuser->lastname;
                  $messageHtml = "
                  <p>Contact could not be found!</p>
                  <br />
                  <b>Debugging log</b>
                  <br />
                  </p>The contact with email of " . $student_email . " does not exist in Active Campaign. Please see user information:
                    <ul>
                    <li>ActiveCampaign ContactID: ". $contact_id . "</li>
                    <li>Moodle UserID: " . $user . "</li>
                    <li>Moodle CourseID: " . $courseid . "</li>
                    <li>Moodle Email: " . $student_email . "</li>
                    <li>Course Expiration Start Date: " . $fstartdate . "</li>
                    <li>Course Expiration End Date: " . $fenddate ."</li>
                    </ul>
                    ";
                  $subject = "Expiry Reminder Failed";
                  email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
            }
        }
  
      }
      //checks which start and end date fields to update based on the courseid value and field id, once found timestart and timeend are sent to Active Campaign
      private static function checkcoursevalues($coursevalues, $courseid) {
        require 'access.php';
        global $DB, $tutoruserid, $fstartdate, $fenddate, $contact_id, $student_email, $user;
        $COURSE_ENROLMENT_START_DATE = null;
        $COURSE_ENROLMENT_END_DATE = null;

        foreach ($coursevalues as $fieldvalue) {
            if ($fieldvalue['value'] == $courseid && $fieldvalue['field'] == 57) {
                //Active Campaign enrolment start and end date field ids
                // error_log('Enrolment 1 variables triggered');
                $COURSE_ENROLMENT_START_DATE = 47;
                $COURSE_ENROLMENT_END_DATE = 48;
                break;
              }
            elseif ($fieldvalue['value'] == $courseid && $fieldvalue['field'] == 58) {
                //Active Campaign enrolment start and end date field ids
                // error_log('Enrolment 2 variables triggered');
                $COURSE_ENROLMENT_START_DATE = 50;
                $COURSE_ENROLMENT_END_DATE = 52;
                break;
            }
            elseif ($fieldvalue['value'] == $courseid && $fieldvalue['field'] == 59) {
                //Active Campaign enrolment start and end date field ids
                // error_log('Enrolment 3 variables triggered');
                $COURSE_ENROLMENT_START_DATE = 55;
                $COURSE_ENROLMENT_END_DATE = 56;
                break;
            }
        }
        //Error handling ~ If none of the courseids match
        if ($COURSE_ENROLMENT_START_DATE == NULL OR $COURSE_ENROLMENT_END_DATE == NULL) {
            error_log("
            Error #5: The CourseId of $courseid does not match any Active Campaign course ids. Please see user information:
              ActiveCampaign ContactId: $contact_id
              Moodle UserId: $user
              Moodle Email: $student_email
              Course Expiration Start Date: $fstartdate
              Course Expiration End Date: $fenddate
              Tutoruserid: $tutoruserid
              ");
              //Tutor account ID
              $conditions = ['id' => $tutoruserid];
              $table = 'user';            
              $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
              $emailuser = new \stdClass();
              $emailuser->email = $user_object->email;
              $emailuser->username = $user_object->username;
              $emailuser->firstname = $user_object->firstname;
              $emailuser->lastname = $user_object->lastname;
              $emailuser->maildisplay = $user_object->maildisplay;
              $emailuser->mailformat = 1;
              $emailuser->id = $user_object->id;
              $emailuser->firstnamephonetic = $user_object->firstnamephonetic;
              $emailuser->lastnamephonetic = $user_object->lastnamephonetic;
              $emailuser->middlename = $user_object->middlename;
              $emailuser->alternatename = $user_object->alternatename;
              $first = $emailuser->firstname;
              $last = $emailuser->lastname;
                $messageHtml = "
                <p>Plugin failed to get student enrolment dates, please see moodle error logs for more information.</p>
                <br />
                <b>Debugging log</b>
                <br />
                </p>The CourseId of " . $courseid . " does not match any Active Campaign course ids. Please see user information:
                  <ul>
                  <li>ActiveCampaign ContactID: ". $contact_id . "</li>
                  <li>Moodle UserID: " . $user . "</li>
                  <li>Moodle CourseID: " . $courseid . "</li>
                  <li>Moodle Email: " . $student_email . "</li>
                  <li>Course Expiration Start Date: " . $fstartdate . "</li>
                  <li>Course Expiration End Date: " . $fenddate ."</li>
                  </ul>
                  <br />
                <b>Possible Solutions</b>
                <ul>
                <li>Check the enrolments for courseid: " . $courseid . " to see if this is a re-enrolment</li>
                <li>Check if the customer accidentally double ordered the course/li>
                <li>Check if the customer is enrolling in Electrical Basics Exam again/li>
                <li>Check if the customer is enrolling in design or install version of the course in which they were previously enrolled in/li>
                </ul>
                  ";
                $subject = "Expiry reminder failed";
                email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);                  
        }



        $curl = curl_init();

        //updates the course enrolment start date
        curl_setopt_array($curl, [
          CURLOPT_URL => "https://gses.api-us1.com/api/3/fieldValues",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"fieldValue\":{\"contact\":\"$contact_id\",\"field\":\"$COURSE_ENROLMENT_START_DATE\",\"value\":\"$fstartdate\"},\"useDefaults\":true}",
          CURLOPT_HTTPHEADER => [
            "Api-Token:". $api_token,
            "accept: application/json",
            "content-type: application/json"
          ],
        ]);
        

        $response = curl_exec($curl);
        $err = curl_error($curl);
        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
          error_log(var_export($err, true));
        } else {
          echo $response;
        }

        $curl = curl_init();

        //updates the course enrolment end date
        curl_setopt_array($curl, [
          CURLOPT_URL => "https://gses.api-us1.com/api/3/fieldValues",
          CURLOPT_RETURNTRANSFER => true,
          CURLOPT_ENCODING => "",
          CURLOPT_MAXREDIRS => 10,
          CURLOPT_TIMEOUT => 30,
          CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
          CURLOPT_CUSTOMREQUEST => "POST",
          CURLOPT_POSTFIELDS => "{\"fieldValue\":{\"contact\":\"$contact_id\",\"field\":\"$COURSE_ENROLMENT_END_DATE\",\"value\":\"$fenddate\"},\"useDefaults\":true}",
          CURLOPT_HTTPHEADER => [
            "Api-Token:". $api_token,
            "accept: application/json",
            "content-type: application/json"
          ],
        ]);

        $response = curl_exec($curl);
        $err = curl_error($curl);

        curl_close($curl);

        if ($err) {
          echo "cURL Error #:" . $err;
          error_log(var_export($err, true));
        } else {
          echo $response;
        } 
      }
}


