<?php
namespace local_expiryreminders\task;                                            //Required to be first on the page
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

      if 
      (str_contains($coursename, "Grid Connected PV Systems")
      || str_contains($coursename, "Battery Storage Systems for Grid-Connected PV Systems")
      || str_contains($coursename, "Stand Alone Power Systems")
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
      if 
      (str_contains($coursename, "Grid Connected PV Systems")
      || str_contains($coursename, "Battery Storage Systems for Grid-Connected PV Systems")
      || str_contains($coursename, "Stand Alone Power Systems")
      || str_contains($coursename, "Stand Alone Power Systems")
      || str_contains($coursename, "Electrical Basics Examination")) 
      { 
        self::handle_enrolment_event($event, $courseid); 
      }
    }

    private static function handle_enrolment_event($event, $courseid) {
      require 'access.php';
      global $DB;
      // var_dump($event);
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
      error_log("Student id not found");
      } 
      else {
      $data = json_decode($response, true);
      //finds the contactid
      if (count($data['contacts']) > 0) {
          $contact_id = $data['contacts'][0]['id'];

          //Active Campaign Course_ids
          $course_id_1 = 57;
          $course_id_2 = 58;
          $course_id_3 = 59;
          $course_id_array = array($course_id_1, $course_id_2, $course_id_3);
          
          //Loops through course_ids until the id matches the selected courseid the enrolment is updated/created for
          foreach ($course_id_array as $id) {

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
              error_log("There was a problem with the GET request for the field value");
            } else {
              //Finds the value for $course_id fieldid
              $fieldValues = json_decode($response, true)['fieldValues'];
              foreach ($fieldValues as $fieldValue) {
                  if ($fieldValue['field'] == $id) {
                      $value = $fieldValue['value'];
                      break;
                  }
              }
            }

            //Field ids required for active campaign API
            //$COURSE_ENROLMENT_START_DATE_1 = 47
            //$COURSE_ENROLMENT_END_DATE_1 = 48
            //$COURSE_ENROLMENT_START_DATE_2 = 50
            //$COURSE_ENROLMENT_END_DATE_2 = 52
            //$COURSE_ENROLMENT_START_DATE_3 = 55
            //$COURSE_ENROLMENT_END_DATE_3 = 56

            //If $courseid matches courseid number on active campaign, update enrolment start and end dates
            if ($value == $courseid) {
              //If iteration over a specific fieldid is true then update those enrolment start and end date fields
              if ($id == 57) {
                $COURSE_ENROLMENT_START_DATE = 47;
                $COURSE_ENROLMENT_END_DATE = 48;
              }
              elseif ($id == 58) {
                $COURSE_ENROLMENT_START_DATE = 50;
                $COURSE_ENROLMENT_END_DATE = 52;
              }
              else {
                $COURSE_ENROLMENT_START_DATE = 55;
                $COURSE_ENROLMENT_END_DATE = 56;
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
                  CURLOPT_POSTFIELDS => "{\"fieldValue\":{\"contact\":\"$contact_id\",\"field\":\"$COURSE_ENROLMENT_START_DATE\",\"value\":\"$fstartdate\"},\"useDefaults\":false}",
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
                  CURLOPT_POSTFIELDS => "{\"fieldValue\":{\"contact\":\"$contact_id\",\"field\":\"$COURSE_ENROLMENT_END_DATE\",\"value\":\"$fenddate\"},\"useDefaults\":false}",
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
                } else {
                  echo $response;
                } 
                break;
              } elseif ($id == $course_id_3 AND $value !== $courseid ) {
                error_log("
                The CourseId of $courseid does not match any Active Campaign course ids. Please see user information:
                  ActiveCampaign ContactId: $contact_id
                  Moodle UserId: $user
                  Moodle Email: $student_email
                  Course Expiration Start Date: $fstartdate
                  Course Expiration End Date: $fenddate
                  ");
              }
            }
          }
        }
  }
}