<?php
namespace local_autoextension\task;                                              //Required to be first on the page
use \DateTime;
use \DateInterval;
class observer //extends \core\task\scheduled_task                        //extends for Cron activation
{                                  
    public static function submission_graded(\mod_assign\event\submission_graded $event)
    {
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

        //String name for Design Task varies between courses, therefore we need a "str_contain"
        if (!function_exists('str_contains')) {
            //For older PHP versions
            if (strpos($assignname, "Design Task Submission") !== false || strpos($assignname, "Preliminary Load Assessment Submission")  !== false || strpos($assignname, "Site Plan and Electrical Schematic") !== false){
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
        else{
            //For newer PHP version 8.0+
                if (str_contains($assignname, "Design Task Submission")|| str_contains($assignname, "Preliminary Load Assessment Submission")|| str_contains($assignname, "Site Plan and Electrical Schematic")) {
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
                    //Extension time = Current date + 2 weeks
                    date_default_timezone_set('Australia/Sydney');
                    $extension = new DateTime('today midnight');
                    $extension->add(new DateInterval('P' . 14 . 'D'));
                    $extensiontimestamp = $extension->getTimestamp();
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
                        $conditions = ['id' => $user];
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
                        $messageHtml = '
                        <header style="font-size:14px;font-family:Arial,sans-serif;">Hi <b>'.$first.'</b></header>
                        <br />
                        <body style="font-size:14px;font-family:Arial,sans-serif;">
                        <p>A tutor has marked your submission for the '. $courseName . ' course. Please login to your <a href="https://www.gsestraining.com/login/index.php">course portal</a> to review their feedback.</p>
                        <p>We have also noticed your course is about to expire soon, we have updated the course expiration to provide a 2 week grace period to respond to tutor feedback.</p>
                        <br />
                        <p>You\'re new expiration date for your ' .$courseName . ' course is <b>' . $extensionFormatted . '</b></p>
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
                        $subject = "GSES Assignment Submission";
                        email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
                        }

                    }
                }
            }
    }
}