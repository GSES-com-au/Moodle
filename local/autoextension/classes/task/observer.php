<?php
namespace local_autoextension\task;                                              //Required to be first on the page
use \DateTime;
use \DateInterval;
class observer //extends \core\task\scheduled_task                        //extends for Cron activation
{                                  
    public static function submission_graded(\mod_assign\event\submission_graded $event)
    {   
        // for error_log
        // require_once '../../config.php';
        // error_reporting(E_ALL);
        // ini_set('display_errors', 0);
        // ini_set('log_errors', 1);
        // ini_set('error_log', __DIR__ . '/error.log');
        global $DB, $PAGE;
        
        //Objectid required for assign_grades table id
        $objectid = $event->objectid;
        $courseid = $event->courseid;

        //Getting Assign name
        $search = $event->get_record_snapshot('assign_grades', $objectid);
        $assignid = $search->assignment;
        
        //-------------------------SQL QUERY----------------------------------------------------------------------
        $params = ['id' => $assignid, 'course' => $courseid];                             
        $return = 'name';                                           
        $select = 'id = :id AND course = :course';                                
        $table = 'assign';                                          
        $assignname = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING);

        // 2 design task changes, check the other design task has been submitted as students should only receive extensions if they have submitted both design tasks, otherwise they require
        //a course re-enrollment or will need to purchase an extension.
        $designtaskmap = array(
            "Design Task Site B Submission (GCwB)" => "Design Task Site A Submission (GCwB)",
            "Design Task Site A Submission (GCwB)" => "Design Task Site B Submission (GCwB)",
            "Design Task 1 Electrical Schematic and Site Plan Submission" => "Design Task 2 Electrical Schematic and Site Plan Submission",
            "Design Task 2 Electrical Schematic and Site Plan Submission" => "Design Task 1 Electrical Schematic and Site Plan Submission",
            "Design Task 1 Submission" => "Design Task 2 Submission",
            "Design Task 2 Submission" => "Design Task 1 Submission"
        );
        $otherassignname = $designtaskmap[$assignname];
        $user = $event->relateduserid; 
        $otherassignid = $DB->get_record('assign', array('name' => $otherassignname, 'course' => $courseid), '*', MUST_EXIST);
        $submission = $DB->get_record('assign_submission', array('assignment' => $otherassignid->id, 'userid' => $user), '*', IGNORE_MISSING);
        $submission_status = $submission->status;
        $do_extension = strpos($assignname, "Design Task Site A Submission (GCwB)") !== false || strpos($assignname, "Design Task Site B Submission (GCwB)") !== false || 
        strpos($assignname, "Design Task 1 Electrical Schematic and Site Plan Submission") !== false || strpos($assignname, "Design Task 2 Electrical Schematic and Site Plan Submission") !== false || 
        strpos($assignname, "Design Task 1 Submission") !== false || strpos($assignname, "Design Task 2 Submission") !== false;
        $do_extension_8 = str_contains($assignname, "Design Task Site A Submission (GCwB)") || str_contains($assignname, "Design Task Site B Submission (GCwB)")|| 
        str_contains($assignname, "Design Task 1 Electrical Schematic and Site Plan Submission") || str_contains($assignname, "Design Task 2 Electrical Schematic and Site Plan Submission") || 
        str_contains($assignname, "Design Task 1 Submission") || str_contains($assignname, "Design Task 2 Submission");
        //String name for Design Task varies between courses, therefore we need a "str_contain"
        if (!function_exists('str_contains') && $submission_status == 'submitted' ) {
            //For older PHP versions
            if ($do_extension){
                $user = $event->relateduserid; 

                $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
                $enrolid = $instance->id;
                
                //------------Finding course expiration--------------------------------------------------------------
                $search = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $user]);
                $expiration = $search->timeend;
                
                //---------------------------------------------------------------------------------------------------
                //Extension time = Current date + 2 weeks
                date_default_timezone_set('Australia/Sydney');
                $extension = new DateTime('today midnight');
                $extension->add(new DateInterval('P' . 14 . 'D'));
                $extension = $extension->getTimestamp();

                //Checks if course is past expiration date or there is less than 2 weeks until course expiration ~ uses enrol plugin
                if (time() > $expiration || $extension > $expiration) {
                    $enrolplugin = enrol_get_plugin($instance->enrol);
                    $enrolplugin->update_user_enrol($instance, $user,'', NULL,$extension, NULL);

                }
            }
        }
        else if ($submission_status == 'submitted') {
            //For newer PHP version 8.0+
                if ($do_extension_8) {
                    $course = get_course($courseid);
                    // Get the course name
                    $courseName = $course->shortname;

                    $user = $event->relateduserid; 
                
                    $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
                    $enrolid = $instance->id;
                    
                    //------------Finding course expiration--------------------------------------------------------------
                    $search = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $user]);
                    $expiration = $search->timeend;

                    
                    //---------------------------------------------------------------------------------------------------
                    //Extension time = Current date + 2 weeks (add 1 day to account for midnight expiry)
                    date_default_timezone_set('Australia/Sydney');
                    $extension = new DateTime('today midnight');
                    $extension->add(new DateInterval('P' . 15 . 'D'));
                    $extensiontimestamp = $extension->getTimestamp();
                    //subtract 1 day to date shown to student, to account for midnight expiry
                    $extensionFormatted = $extension->modify('-1 day');
                    $extensionFormatted = $extension->format('jS F Y');


                    //Checks if course is past expiration date or there is less than 2 weeks until course expiration ~ uses enrol plugin
                    if (time() > $extensiontimestamp || $extensiontimestamp > $expiration) {
                        $enrolplugin = enrol_get_plugin($instance->enrol);
                        $enrolplugin->update_user_enrol($instance, $user,'', NULL,$extensiontimestamp, NULL);

                        //1 = NYS
                        //2 = S

                        //Getting event data
                        $data = $event->get_data();

                        // Access the 'objectid' to get the grade ID
                        $gradeId = $data['objectid'];
                                                
                        // Retrieve the grade object from the database
                        $sql = "SELECT grade FROM `mdl_assign_grades` WHERE id = :objectid;";
                        $params = ['objectid' => $gradeId,];
                        $results = $DB->get_record_sql($sql, $params);

                        //If grade is NYS then send email to student
                        if ($results->grade == 1) {

                        //---------------------------------------------EMAIL TEMPLATE---------------------------------------------
                        $emailuser = new \stdClass();
                        $emailuser = \core_user::get_user($user);
                        $emaildetails = array('firstname' => $emailuser->firstname, 'coursename' => $courseName, 'expirydate' => $extensionFormatted);
                        email_to_user($emailuser, '', get_string('nys_extensionemailsubject', 'local_autoextension'), '', get_string('nys_extensionemail', 'local_autoextension', $emaildetails), '', '', false);
                        email_to_user(get_admin(), '', get_string('nys_extensionemailsubject', 'local_autoextension'). ' - sent to ' . $emailuser->email, '', get_string('nys_extensionemail', 'local_autoextension', $emaildetails), '', '', false);
                        }

                    }
                }
            }
    }
}