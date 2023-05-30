<?php
namespace local_pending\task;                                            //Required to be first on the page
use Automattic\WooCommerce\Client;                                      //Using woocommerce API
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
class observer //extends \core\task\scheduled_task                        //extends for Cron activation
{

    public static function group_member_removed(\core\event\group_member_removed $event)
    {
        //Declaring Variables (Database and others)
        global $DB, $PAGE, $CFG, $COURSE;
        $relateduserid = $event->relateduserid;         //get userid
        $courseid = $event->courseid;                   //courseid number
        $coursecontext = \context_course::instance($courseid);
        $tutoruserid = 17; //change when moving to production

        
        if (is_enrolled($coursecontext, $relateduserid)) {
        
            //If userid is not enrolled in course skip logic
            //--------------Checks groupid matches name = "Pending"-------------------------------------------------------------------
            $objectid = $event->objectid;                               //get groupid
            $params = ['objectid' => $objectid];                        //sending objectid to mysql query
            $return = 'name';                                           //name from table wanted
            $select = 'id = :objectid';                                 //declaring variable in mysql search
            $table = 'groups';                                          //table name in database
            $name = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING); //query


            if ($name == 'Pending')
            {
            //---------------------SQL Queries------------------------------------------------------------------------------------------------------
        
        
                //sql query
                $params = ['userid' => $relateduserid];         //sending userid to mysql query
                $return = 'data';                               //field from table wanted
                $select = 'fieldid = 2 AND userid = :userid';   //declaring variable in mysql search
                $table = 'user_info_data';                      //table name in database
                $orderid = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING); //query
                // $orderid->close()    

                //------------------WC API CONNECTION---------------------------------------------------------------------------------------------------
            
                require 'access.php';
                require_once($CFG->dirroot . '/local/pending/vendor/autoload.php');

                $woocommerce = new Client(
                $store_url,
                $consumer_key,
                $consumer_secret,
                [
                    'wp_api' => true, // Enable the WP REST API integration
                    'version' => 'wc/v3',
                ]
                );
                        
                $options = array(
                    'debug'           => true,
                    'return_as_array' => false,
                    'validate_url'    => true,
                    'timeout'         => 30,
                    'ssl_verify'      => false,
                );
                
                try {
                //sanity check ~ Checks if status is 'on-hold'
                $woocommerce = new Client($store_url, $consumer_key, $consumer_secret, $options); //required to access woocommerce
                $query = $woocommerce->get("orders/{$orderid}");
                $statuscheck = $query->status;
                        
                        
                } catch ( WC_API_Client_Exception $e ) {
                    
                    echo $e->getMessage() . PHP_EOL;
                    echo $e->getCode() . PHP_EOL;
                    
                    if ( $e instanceof WC_API_Client_HTTP_Exception ) {
                        
                        print_r( $e->get_request() );
                        print_r( $e->get_response() );
                    }
                }
                
                if ($statuscheck == 'on-hold') {
                    try {
                        $data = ["status" => "processing"]; //change status into processing
                        $woocommerce->put("orders/{$orderid}", $data); //searches for order id and changes status to process
                        
                        
                    } catch ( WC_API_Client_Exception $e ) {
                    
                        echo $e->getMessage() . PHP_EOL;
                        echo $e->getCode() . PHP_EOL;
                    
                        if ( $e instanceof WC_API_Client_HTTP_Exception ) {
                    
                            print_r( $e->get_request() );
                            print_r( $e->get_response() );
                        }
                    }
                }
                //-------------------Updating Enrolment Start and End dates----------------------------------------------------------------------------------- 
                $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
                $enrolid = $instance->id;
                
                //------------Finding course expiration--------------------------------------------------------------
                $enrolment = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $relateduserid]);
                if ($enrolment) {
                    // Update the timestart and timeend dates
                    date_default_timezone_set('Australia/Sydney');
                    $date = new \DateTime('today midnight');   //must use '\' to avoid namespace naming conflicts
                    $enrolment->timestart = $date->getTimestamp(); // Set the current timestamp as the timestart
                    $enrolment->timeend = strtotime('+1 year 1 day', $enrolment->timestart); // Set the timeend to one year and 1 day from now
                    $userexpiration = date('d/m/Y', strtotime('-1 day', $enrolment->timeend));     //For email to user with the corrected expiration date
                    
                    // Update the enrolment record in the database
                    $DB->update_record('user_enrolments', $enrolment);
                } else {
                    // Enrolment record not found
                    // Handle the case accordingly
                    error_log('unable to update users enrolment');
                    $conditions = ['id' => $tutoruserid];
                    $table = 'user';            
                    $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
                    $emailuser = new \stdClass();
                    $emailuser->email = $user_object->email;
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
                      <p><b>ERROR:</b>Pending plugin failed to update users enrolment</p>
                      <br />
                    <b>Debugging log</b>
                    <br />
                    <p><i>Beep..Boop..Beep..</i>Human Brain Required!:</p>
                      <ul>
                      <li>Moodle UserID: " . $relateduserid . "</li>
                      <li>Moodle CourseID: " . $courseid . "</li>
                      <li>Course Expiration Start Date: " . $enrolment->timestart . "</li>
                      <li>Course Expiration End Date: " . $enrolment->timeend ."</li>
                      </ul>
                      <br />";
                      $subject = "Moodle Pending plugin failed";
                      email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);    
                }



                //-------------------LLN Check----------------------------------------------------------------------------------- 
        
                $select = 'fieldid = 1 AND userid = :userid';   //declaring variable in mysql search
        
                $LLN = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING);
                
                $conditions = ['id' => $relateduserid];
                $table = 'user';            
                $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
                $emailuser = new \stdClass();
                $emailuser->email = $user_object->email;
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
                
                //Grabing course name
                $coursename = get_course($courseid)->fullname;
                
                
            //-----------------------------------------------Email Templates-------------------------------------------------
        
        
        
                if ($LLN == 'pending')
                {
                    //Html email if prequisites have been approved and LLN incomplete
                    //I had to escape the variable $first and $courseid with '.' to enable the variable to come through (strings are concatenated)
                    $messageHtml = '
                    <header style="font-size:14px;font-family:Arial,sans-serif;">Hi <b>'.$first.'</b></header>
                    <br />
                    <br />
                    <body style="font-size:14px;font-family:Arial,sans-serif;">
                    <p>Your prerequisites have been approved. Now you just need to complete the <b>LLN quiz</b> on your course portal before you can get full access to our course.</p>
                    <p>You can access your LLN quiz through the course portal <a href="https://gsesdev.com/course/view.php?id='.$courseid.'">here</a>. Your enrolment in <b>'.$coursename.'</b> expires on <b>'.$userexpiration .'</b>. Please make sure you complete all online assessments before then.</p>
                    <p><b>Note:</b> If you do not complete the course within the 12 month validity, an admin fee of $137.50 per month will be incurred to process any extensions. Extension fees will apply from the date of expiry and cannot exceed 6 months.</p>
                    <br />
                    <p>If you are having trouble accessing the course or have any further questions, please don\'t hesitate to contact us!</p>
                    <br />
                    <br />
                    <p>Kind Regards,</p>
                    <br />
                    </body>
                    <footer style="font-size:14px"><b><em>GSES Training Team</em></b></footer>
                    <p style="font-family:Arial,sans-serif;font-size:14px;margin:0px;">Global Sustainable Energy Solutions Pty Ltd</p>
                    <span style="color:#0b5394;"><b>E: </b></span><a href="mailto:tutor@gses.com.au" style="color:rgb(17,85,204);font-size:14px;" target="_blank">tutor@gses.com.au</a><b>&nbsp;|&nbsp;</b>
                    <span style="color:#0b5394;"><b>P: </b></span><span style="color:#000000;font-size:14px;">02 9024 5312 |&nbsp;</span>
                    <span style="color:#0b5394;"><b>W: </b></span><a href="mailto:https://www.gses.com.au/" style="color:rgb(17,85,204);font-size:14px;" target="_blank">www.gses.com.au</a>
                    <div>
                        <a href="https://www.linkedin.com/company/global-sustainable-energy-solutions" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/N07MzcgS3ln4rq3tluPyLzMJNOSNJ4bLgCNCUxj_gkHzVpwWK-VZtYOdbyLd7TYzppr1WRillbn3l_EYvJIqi_6dOpnYXTPrmZswgHhJe30I1oExQKEqLpVkfshczcUfA97GMqQqePVaAdQ6COi-rHwAJK_b2-WwuaF4woqpxD2cNgj-BpoA7-HlhRqXTxmi65x1RKwWsHbTq4IvCQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1LqMzcRTyjz9mjvdMddLwIqVAkX0u64Vi&revid=0ByWix7H3He36d1Fic1BHUTBvVng0cThFNmlJU29iejZKUFhZPQ"></img></a>
                        <a href="https://www.youtube.com/channel/UC2rCmfHuM6vKW-sz-c3DMpA" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/PPzVPyhm7H-LglYIFhOxxEovVD47H2rxR57wbOyOYh_x3bLZ-FCXdhAlccQa8rQMLaBdl9xlzpF3kH7ye1Qh2bvbNUxPnJYF0sdHe2wXed1YAlatj01EFP1Ciqr-HnJB5KAFvgdg0TwDlkr2sPZo81VO8LGnYabsL8F8TUp0H4oSRjjCQJCPt9NaZUX9ElVVCd87i-BOvtJI6wFTlg=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1stlO86iMy4mx6dfclzpoD14NaARdpDL3&revid=0ByWix7H3He36NFREZDNDY09VLzRnWityaERuQUhZdGJoRWcwPQ"></img></a>
                        <a href="https://www.facebook.com/gsesaustralia/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/5t4RyJVROJM0Z-G9VNXJOPyF_aUbhASWRh78t4vLR3R8WOJucr8-6-0ZzXGHiNIJOFhkdtn5Pva2MgTSZkzsTSC5oSd-S5TyJ169YCuah8Mi07qNmZc7-hIYbZmF_l_EvLyu9BCPAvmhqdsGH5UfJGVmN1gE3TlJMbCpb-uWZrub5q3cXOHdjkj0D9HqP2Yq0D3kPicE8HFy_VEzzQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1XmXNaLfr_TVeyThbnh09acXdC_D4_YhS&revid=0ByWix7H3He36RTdaOW54TEU5amViSmZQa3BBdW9ucjN6R2xZPQ"></img></a>
                    </div>
                    <div><a href="https://www.gses.com.au/shop/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/iPkc0nF0jfvBDo5MgkvYJ1jlVdiJbgw2uf0qR5YEMC-5juQdgqTOCknLqmzKTmK1cH27jNGkVgADLnspobrRlEDyuM4vprGM2En8H4nM83gplsnuRyXuJds_biju_RA71A82B-Ywhg1TR5Ysju03_GX1t10E-PT54M8B4xJbetr2DUHKHpswhG5ZunTiVGq9uPv7KLbByuv9ZKZ6RA=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1k8V0IrWdffVq2TH7OR1Fkise0B9_eVny&revid=0ByWix7H3He36cmlkTFRpMFJoWFZtT2dNUUZ0YUFKMWRUUFFJPQ"></img></a></div>
                    ';
                    $subject = "Prerequisites Approved!";
                    email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false, $cc);
                    //Sending email to tutor as well
                    $emailuser->email = 'tutor@gses.com.au';
                    email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
                }
                else
                {
                    //Html email if prequisites have been approved and LLN complete
                    $messageHtml = '
                    <header style="font-size:14px;font-family:Arial,sans-serif;">Hi <b>'.$first.'</b></header>
                    <br />
                    <br />
                    <body style="font-size:14px;font-family:Arial,sans-serif;">
                    <p>Your prerequisites have been approved. You should now have full access to our course.</p>
                    <p>You can access your course through the course portal <a href="https://gsesdev.com/course/view.php?id='.$courseid.'">here</a>. Your enrolment in <b>'.$coursename.'</b> expires on <b>'.$userexpiration .'</b>. Please make sure you complete all online assessments before then.</p>
                    <p><b>Note:</b> If you do not complete the course within the 12 month validity, an admin fee of $137.50 per month will be incurred to process any extensions. Extension fees will apply from the date of expiry and cannot exceed 6 months.</p>
                    <br />
                    <p>If you are having trouble accessing the course or have any further questions, please don\'t hesitate to contact us!</p>
                    <br />
                    <br />
                    <p>Kind Regards,</p>
                    <br />
                    </body>
                    <footer style="font-size:14px"><b><em>GSES Training Team</em></b></footer>
                    <p style="font-family:Arial,sans-serif;font-size:14px;margin:0px;">Global Sustainable Energy Solutions Pty Ltd</p>
                    <span style="color:#0b5394;"><b>E: </b></span><a href="mailto:tutor@gses.com.au" style="color:rgb(17,85,204);font-size:14px;" target="_blank">tutor@gses.com.au</a><b>&nbsp;|&nbsp;</b>
                    <span style="color:#0b5394;"><b>P: </b></span><span style="color:#000000;font-size:14px;">02 9024 5312 |&nbsp;</span>
                    <span style="color:#0b5394;"><b>W: </b></span><a href="mailto:https://www.gses.com.au/" style="color:rgb(17,85,204);font-size:14px;" target="_blank">www.gses.com.au</a>
                    <div>
                        <a href="https://www.linkedin.com/company/global-sustainable-energy-solutions" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/N07MzcgS3ln4rq3tluPyLzMJNOSNJ4bLgCNCUxj_gkHzVpwWK-VZtYOdbyLd7TYzppr1WRillbn3l_EYvJIqi_6dOpnYXTPrmZswgHhJe30I1oExQKEqLpVkfshczcUfA97GMqQqePVaAdQ6COi-rHwAJK_b2-WwuaF4woqpxD2cNgj-BpoA7-HlhRqXTxmi65x1RKwWsHbTq4IvCQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1LqMzcRTyjz9mjvdMddLwIqVAkX0u64Vi&revid=0ByWix7H3He36d1Fic1BHUTBvVng0cThFNmlJU29iejZKUFhZPQ"></img></a>
                        <a href="https://www.youtube.com/channel/UC2rCmfHuM6vKW-sz-c3DMpA" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/PPzVPyhm7H-LglYIFhOxxEovVD47H2rxR57wbOyOYh_x3bLZ-FCXdhAlccQa8rQMLaBdl9xlzpF3kH7ye1Qh2bvbNUxPnJYF0sdHe2wXed1YAlatj01EFP1Ciqr-HnJB5KAFvgdg0TwDlkr2sPZo81VO8LGnYabsL8F8TUp0H4oSRjjCQJCPt9NaZUX9ElVVCd87i-BOvtJI6wFTlg=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1stlO86iMy4mx6dfclzpoD14NaARdpDL3&revid=0ByWix7H3He36NFREZDNDY09VLzRnWityaERuQUhZdGJoRWcwPQ"></img></a>
                        <a href="https://www.facebook.com/gsesaustralia/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/5t4RyJVROJM0Z-G9VNXJOPyF_aUbhASWRh78t4vLR3R8WOJucr8-6-0ZzXGHiNIJOFhkdtn5Pva2MgTSZkzsTSC5oSd-S5TyJ169YCuah8Mi07qNmZc7-hIYbZmF_l_EvLyu9BCPAvmhqdsGH5UfJGVmN1gE3TlJMbCpb-uWZrub5q3cXOHdjkj0D9HqP2Yq0D3kPicE8HFy_VEzzQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1XmXNaLfr_TVeyThbnh09acXdC_D4_YhS&revid=0ByWix7H3He36RTdaOW54TEU5amViSmZQa3BBdW9ucjN6R2xZPQ"></img></a>
                    </div>
                    <div><a href="https://www.gses.com.au/shop/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/iPkc0nF0jfvBDo5MgkvYJ1jlVdiJbgw2uf0qR5YEMC-5juQdgqTOCknLqmzKTmK1cH27jNGkVgADLnspobrRlEDyuM4vprGM2En8H4nM83gplsnuRyXuJds_biju_RA71A82B-Ywhg1TR5Ysju03_GX1t10E-PT54M8B4xJbetr2DUHKHpswhG5ZunTiVGq9uPv7KLbByuv9ZKZ6RA=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1k8V0IrWdffVq2TH7OR1Fkise0B9_eVny&revid=0ByWix7H3He36cmlkTFRpMFJoWFZtT2dNUUZ0YUFKMWRUUFFJPQ"></img></a></div>
                    ';
                    $subject = "Prerequisites Approved!";
                    email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
                    //Sending email to tutor as well
                    $emailuser->email = 'tutor@gses.com.au';
                    email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
                    
                }
            }
            else {}
        }
    }
}