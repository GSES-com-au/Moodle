<?php
namespace local_lln\task;                                              //Required to be first on the page

class observer //extends \core\task\scheduled_task                        //extends for Cron activation
{
    // public function get_name()                          //Only visual difference
    // {
    //     return get_string('observer', 'local_lln');
    // }

    // /**
    //  * Execute the task.
    //  */
    // public function execute() {                                             //Required for Cron
    //     attempt_submitted();
    // }                                        
    
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
                //---------------------------------------------------------------------------------------------------------------------
                
                
            }
            // else{
            // $params = ['userid' => $user];                        
            // $return = 'id';                                           
            // $select = 'userid = :userid AND fieldid = 1';                   
            // $table = 'user_info_data';                      
            // $id = $DB->get_field_select($table, $return, $select, $params, $strictness=IGNORE_MISSING);
            // //id = 17 = tutor@gses.com.au
            // $conditions = ['id' => '17'];
            // $table = 'user';            
            // $user_object = $DB->get_record($table, $conditions, $fields='*', $strictness=IGNORE_MISSING);
            // $emailuser = new \stdClass();
            // $emailuser->email = $user_object->email;
            // $emailuser->firstname = $user_object->firstname;
            // $emailuser->lastname = $user_object->lastname;
            // $emailuser->maildisplay = $user_object->maildisplay;
            // $emailuser->mailformat = 1;
            // $emailuser->id = $user_object->id;
            // $emailuser->firstnamephonetic = $user_object->firstnamephonetic;
            // $emailuser->lastnamephonetic = $user_object->lastnamephonetic;
            // $emailuser->middlename = $user_object->middlename;
            // $emailuser->alternatename = $user_object->alternatename;
            // $first = $emailuser->firstname;
            // $last = $emailuser->lastname;
            //         $messageHtml = '
            // <header style="font-size:12.8px;font-family:\'Open Sans\',Helvetica,Arial,sans-serif;"><b>Hi Chief,</b></header>
            // <body style="font-size:14px;font-family:\'Open Sans\',Helvetica,Arial,sans-serif;">
            // <p><b>Student grade doesnt equal maximum attainable grade for '.$user.'.</p>
            // <p>Check if student has achieved the attainable maximum grade</p>
            // <p>Error info: </p>
            // <p>Studentgrade = '.$grade.'</p>
            // <p>Maximumgrade = '.$gradepass.'</p>
            // <p>Quizname = '.$quizname.'</p>
            // <p>Quizid = '.$quizid.'</p>
            // <p>id = '.$id.'</p>
            // </body>
            // <footer style="font-size:12.8px"><b><em>GSES Tutors</em></b></footer>
            // <p style="font-family:\'Open Sans\',Helvetica,Arial,sans-serif;font-size:14px;margin:0px;">Global Sustainable Energy Solutions Pty Ltd</p>
            // <span style="color:#0b5394;"><b>E: </b></span><a href="mailto:tutor@gses.com.au" style="color:rgb(17,85,204);font-size:14px;" target="_blank">tutor@gses.com.au</a><b>&nbsp;|&nbsp;</b>
            // <span style="color:#0b5394;"><b>P: </b></span><span style="color:#000000;font-size:14px;">02 9024 5312 |&nbsp;</span>
            // <span style="color:#0b5394;"><b>W: </b></span><a href="mailto:https://www.gses.com.au/" style="color:rgb(17,85,204);font-size:14px;" target="_blank">www.gses.com.au</a>
            // <div>
            //     <a href="https://www.linkedin.com/company/global-sustainable-energy-solutions" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/N07MzcgS3ln4rq3tluPyLzMJNOSNJ4bLgCNCUxj_gkHzVpwWK-VZtYOdbyLd7TYzppr1WRillbn3l_EYvJIqi_6dOpnYXTPrmZswgHhJe30I1oExQKEqLpVkfshczcUfA97GMqQqePVaAdQ6COi-rHwAJK_b2-WwuaF4woqpxD2cNgj-BpoA7-HlhRqXTxmi65x1RKwWsHbTq4IvCQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1LqMzcRTyjz9mjvdMddLwIqVAkX0u64Vi&revid=0ByWix7H3He36d1Fic1BHUTBvVng0cThFNmlJU29iejZKUFhZPQ"></img></a>
            //     <a href="https://www.youtube.com/channel/UC2rCmfHuM6vKW-sz-c3DMpA" target="_blank"><img src="https://ci3.googleusercontent.com/proxy/PPzVPyhm7H-LglYIFhOxxEovVD47H2rxR57wbOyOYh_x3bLZ-FCXdhAlccQa8rQMLaBdl9xlzpF3kH7ye1Qh2bvbNUxPnJYF0sdHe2wXed1YAlatj01EFP1Ciqr-HnJB5KAFvgdg0TwDlkr2sPZo81VO8LGnYabsL8F8TUp0H4oSRjjCQJCPt9NaZUX9ElVVCd87i-BOvtJI6wFTlg=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1stlO86iMy4mx6dfclzpoD14NaARdpDL3&revid=0ByWix7H3He36NFREZDNDY09VLzRnWityaERuQUhZdGJoRWcwPQ"></img></a>
            //     <a href="https://www.facebook.com/gsesaustralia/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/5t4RyJVROJM0Z-G9VNXJOPyF_aUbhASWRh78t4vLR3R8WOJucr8-6-0ZzXGHiNIJOFhkdtn5Pva2MgTSZkzsTSC5oSd-S5TyJ169YCuah8Mi07qNmZc7-hIYbZmF_l_EvLyu9BCPAvmhqdsGH5UfJGVmN1gE3TlJMbCpb-uWZrub5q3cXOHdjkj0D9HqP2Yq0D3kPicE8HFy_VEzzQ=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1XmXNaLfr_TVeyThbnh09acXdC_D4_YhS&revid=0ByWix7H3He36RTdaOW54TEU5amViSmZQa3BBdW9ucjN6R2xZPQ"></img></a>
            // </div>
            // <div><a href="https://www.gses.com.au/shop/" target="_blank"><img src="https://ci5.googleusercontent.com/proxy/iPkc0nF0jfvBDo5MgkvYJ1jlVdiJbgw2uf0qR5YEMC-5juQdgqTOCknLqmzKTmK1cH27jNGkVgADLnspobrRlEDyuM4vprGM2En8H4nM83gplsnuRyXuJds_biju_RA71A82B-Ywhg1TR5Ysju03_GX1t10E-PT54M8B4xJbetr2DUHKHpswhG5ZunTiVGq9uPv7KLbByuv9ZKZ6RA=s0-d-e1-ft#https://docs.google.com/uc?export=download&id=1k8V0IrWdffVq2TH7OR1Fkise0B9_eVny&revid=0ByWix7H3He36cmlkTFRpMFJoWFZtT2dNUUZ0YUFKMWRUUFFJPQ"></img></a></div>
            // ';
            // $subject = "LLN Auto Pass may have failed";
            // email_to_user($emailuser, '',$subject, '',$messageHtml, '', '', false);
            // }
        }
        else{}
    }
}

// object(stdClass)#1426 (2) 
// { 
//     ["items"]=> array(1) 
//     { 
//         [0]=> object(stdClass)#146 (13) 
//         { 
//             ["id"]=> string(3) "897" 
//             ["itemnumber"]=> string(1) "0" 
//             ["itemtype"]=> string(3) "mod" 
//             ["itemmodule"]=> string(4) "quiz" 
//             ["iteminstance"]=> string(3) "778" 
//             ["scaleid"]=> int(0) ["name"]=> string(8) "LLN Quiz" 
//             ["grademin"]=> string(7) "0.00000" 
//             ["grademax"]=> string(8) "18.00000" 
//             ["gradepass"]=> string(8) "18.00000" 
//             ["locked"]=> bool(false) 
//             ["hidden"]=> bool(false) 
//             ["grades"]=> array(1) 
//             { 
//                 [397]=> object(stdClass)#147 (12) 
//                 { 
//                     ["grade"]=> string(8) "17.82000" 
//                     ["locked"]=> bool(false) 
//                     ["hidden"]=> bool(false) 
//                     ["overridden"]=> string(1) "0" 
//                     ["feedback"]=> NULL 
//                     ["feedbackformat"]=> string(1) "0" 
//                     ["usermodified"]=> string(3) "397" 
//                     ["datesubmitted"]=> string(10) "1666224407" 
//                     ["dategraded"]=> string(10) "1666224407" 
//                     ["str_grade"]=> string(5) "17.82" 
//                     ["str_long_grade"]=> string(13) "17.82 / 18.00" 
//                     ["str_feedback"]=> string(0) "" 
//                 } 
//             } 
//         } 
//     } 
//     ["outcomes"]=> array(0) { } } NULL NULL