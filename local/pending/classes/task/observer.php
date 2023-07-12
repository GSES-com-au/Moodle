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
        $coursename = get_course($courseid)->fullname;  //get course name
        $coursecontext = \context_course::instance($courseid);
        
        //If userid is not enrolled in course skip logic
        if (is_enrolled($coursecontext, $relateduserid)) {
        
            //Checks groupid matches name = "Pending"
            $objectid = $event->objectid;          //get groupid
            $params = ['objectid' => $objectid];                        //sending objectid to mysql query
            $return = 'name';       //name from table wanted
            $select = 'id = :objectid';                                 //declaring variable in mysql search
            $table = 'groups';         //table name in database
            $name = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING); //query


            if ($name == 'Pending')
            {
            //---------------------SQL Queries
                $orderid = '';
                //sql query
                $params = ['fieldid' => get_config('local_pending', 'wcfieldid'), 'userid' => $relateduserid]; //sending fieldid and userid to mysql query
                $return = 'data';                               //field from table wanted
                $select = 'fieldid = :fieldid AND userid = :userid';   //declaring variable in mysql search
                $table = 'user_info_data';                      //table name in database
                $orderid = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING); //query

                //------------------WC API CONNECTION
                if ($orderid && !str_contains($coursename, "Electrical Basics Examination")) {
                    //require 'access.php';
                    require_once($CFG->dirroot . '/local/pending/vendor/autoload.php');
                    
                    $store_url = get_config('local_pending', 'storeurl');
                    $consumer_key = get_config('local_pending', 'storekey');
                    $consumer_secret = get_config('local_pending', 'storesecret');

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
                
                    if ($statuscheck == 'checking') {
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
                }
                
                //-------------------Updating Enrolment Start and End dates
                $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
                if ($instance) {
                    $enrolid = $instance->id;
                    
                    //------------Finding course expiration
                    $enrolment = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $relateduserid]);
                    
                } else {
                    // Manual enrolment mode not found for this course.
                    error_log('unable to update users enrolment, failed enrolid lookup');
                    $message = "<p><b>ERROR:</b>Pending plugin failed to update user's enrolment dates because the manual enrolment instance for this course couldn't be found.</p>
                      <br />
                    <b>Debugging log</b>
                    <br />
                    <p><i>Beep..Boop..Beep..</i>Human Brain Required!:</p>
                      <ul>
                      <li>Moodle UserID: " . $relateduserid . "</li>
                      <li>Moodle CourseID: " . $courseid . "</li>
                      </ul>
                      <br />";
                      email_to_user(get_admin(), '', 'Moodle Pending plugin failed', '', $message, '', '', false);
                }
                
                if ($enrolment) {
                    // Update the timestart and timeend dates
                    date_default_timezone_set('Australia/Sydney');
                    $date = new \DateTime('today midnight');   //must use '\' to avoid namespace naming conflicts
                    $enrolment->timestart = $date->getTimestamp(); // Set the current timestamp as the timestart
                    if (str_contains($coursename, "Electrical Basics Examination")) {
                        $enrolment->timeend = strtotime('+31 days', $enrolment->timestart); // Set the timeend to 31 days from now
                    } else {
                    $enrolment->timeend = strtotime('+1 year 1 day', $enrolment->timestart); // Set the timeend to one year and 1 day from now
                    }
                    $userexpiration = date('jS F Y', strtotime('-1 day', $enrolment->timeend));     //For email to user with the corrected expiration date
                    
                    // Update the enrolment record in the database
                    $enrolplugin = enrol_get_plugin($instance->enrol);
                    $enrolplugin->update_user_enrol($instance, $relateduserid,'', NULL,$enrolment->timeend, $enrolment->timestart);
                } else {
                    // Enrolment record not found
                    // Handle the case accordingly
                    error_log('unable to update users enrolment, failed user_enrolments lookup');
                    $message = "<p><b>ERROR:</b>Pending plugin failed to update user's enrolment because the user's enrolment instance couldn't be found.</p>
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
                      email_to_user(get_admin(), '', 'Moodle Pending plugin failed', '', $message, '', '', false);
                }



                //-------------------LLN Check
                $params = ['userid' => $relateduserid];         //sending userid to mysql query
                $return = 'data';                               //field from table wanted
                $select = 'fieldid = 1 AND userid = :userid';   //declaring variable in mysql search
                $table = 'user_info_data';                      //table name in database
        
                $LLN = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING);
                
                //define user object
                $emailuser = new \stdClass();
                $emailuser = \core_user::get_user($relateduserid);
                
                $courseurl = $CFG->wwwroot . '/course/view.php?id=' . $courseid;
                $emaildetails = array('firstname' => $emailuser->firstname, 'coursename' => $coursename, 'expirydate' => $userexpiration, 'courseurl' => $courseurl);

            //-----------------------------------------------Email Templates
                if (str_contains($coursename, "Electrical Basics Examination")) {
                    //Email if prerequisites have been approved for the EB exam
                    email_to_user($emailuser, '', get_string('eb_emailsubject', 'local_pending'), '', get_string('eb_email', 'local_pending', $emaildetails), '', '', false);
                    //Sending email to tutor as well
                    email_to_user(get_admin(), '', get_string('eb_emailsubject', 'local_pending') . ' - sent to ' . $emailuser->email, '', get_string('eb_email', 'local_pending', $emaildetails), '', '', false);
                } else if ($LLN != 'approved') {
                    //Email if prerequisites have been approved and LLN incomplete
                    email_to_user($emailuser, '', get_string('lln_nys_emailsubject', 'local_pending'), '', get_string('lln_nys_email', 'local_pending', $emaildetails), '', '', false);
                    //Sending email to tutor as well
                    email_to_user(get_admin(), '', get_string('lln_nys_emailsubject', 'local_pending') . ' - sent to ' . $emailuser->email, '', get_string('lln_nys_email', 'local_pending', $emaildetails), '', '', false);
                }
                else {
                    //Email if prerequisites have been approved and LLN complete
                    email_to_user($emailuser, '', get_string('lln_s_emailsubject', 'local_pending'), '', get_string('lln_s_email', 'local_pending', $emaildetails), '', '', false);
                    //Sending email to tutor as well
                    email_to_user(get_admin(), '', get_string('lln_s_emailsubject', 'local_pending') . ' - sent to ' . $emailuser->email, '', get_string('lln_s_email', 'local_pending', $emaildetails), '', '', false);
                    
                }
            }
            else {}
        }
    }
}