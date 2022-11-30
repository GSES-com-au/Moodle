<?php
namespace local_lln\task;                                              //Required to be first on the page

class observer //extends \core\task\scheduled_task                        //extends for Cron activation
{                              
    public static function attempt_submitted(\mod_quiz\event\attempt_submitted $event)
    {
        global $DB, $PAGE;
        
        //Getting Quiz name
        $quizid = $event->other['quizid'];
        $search = $event->get_record_snapshot('quiz', $quizid);
        $quizname = $search->name;
        $courseid = $search->course;

        if ($quizname == "LLN Quiz") {
            $user = $event->relateduserid; 
            
            //Gradebook API
            $grading_info = grade_get_grades($courseid, 'mod', 'quiz', $quizid, $user);
            $gradepass = $grading_info->items[0]->gradepass;
            $grade = $grading_info->items[0]->grades[$user]->grade;

            //-----------------------------------------------------------------------------------------------------------------------------
            
            if ($grade == $gradepass) {
                //--------------------------SQL QUERY----------------------------------------------------------------------------
                //Query Database for fieldid of "LLN" custom field
                $fieldid = $DB->get_field('user_info_field','id',['shortname'=>'LLN']);
                $params = ['userid' => $user, 'fieldid' => $fieldid];                        
                $return = 'id';                                           
                $select = 'userid = :userid AND fieldid = :fieldid';                   
                $table = 'user_info_data';                      
                //Finds the 'id' of the user relative to the table 'user_info_data' - requirement for update_record feature
                $id = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING);
                //Check if id exists ~ student custom field may be empty
                if ($id == false){
                    $dataobject = (object)[];
                    $dataobject->id = $id;
                    $dataobject->userid = $user;
                    $dataobject->fieldid = $fieldid;
                    $dataobject->data = "approved";
                    $dataobject->dataformat = 0;

                    //Updating database
                    $table = 'user_info_data';
                    $DB->insert_record($table, $dataobject, $returnid=false, $bulk=false);
                }
                else {
                    //Defining variables for updating record of user to "approved"
                    $dataobject = (object)[];
                    $dataobject->id = $id;
                    $dataobject->data = "approved";

                    //Updating database
                    $table = 'user_info_data';
                    $DB->update_record($table, $dataobject, $bulk=false);
                }       
            }
        }
        else{}
    }
}