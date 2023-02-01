<?php
namespace local_autoextension\task;                                              //Required to be first on the page
use \DateTime;
use \DateInterval;
class observer 
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
        if (!function_exists('str_-contains')) {
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
                $extension->add(new DateInterval('P' . 15 . 'D'));
                $extension = $extension->getTimestamp();

                //Checks if course is past expiration date or there is less than 2 weeks until course expiration ~ uses enrol plugin
                if (time() > $expiration || $extension > $expiration) {
                    $enrolplugin = enrol_get_plugin($instance->enrol);
                    $enrolplugin->update_user_enrol($instance, $user,'', NULL,$extension, NULL);

                }
            }
        }
        else{
                if (str_contains($assignname, "Design Task Submission")|| str_contains($assignname, "Preliminary Load Assessment Submission")|| strpos($assignname, "Site Plan and Electrical Schematic")) {
                    
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
                    $extension->add(new DateInterval('P' . 15 . 'D'));
                    $extension = $extension->getTimestamp();



                    //Checks if course is past expiration date or there is less than 2 weeks until course expiration ~ uses enrol plugin
                    if (time() > $expiration || $extension > $expiration) {
                        $enrolplugin = enrol_get_plugin($instance->enrol);
                        $enrolplugin->update_user_enrol($instance, $user,'', NULL,$extension, NULL);

                    }
                }
            }
    }
}