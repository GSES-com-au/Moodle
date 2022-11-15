<?php

/*Requirements:
    - Design Course needs to have same expiration as install course Solar Vic only
    - Take courseid and userid number
    - Expiration for install only or return error message

*/
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/externallib.php');
require_once($CFG->dirroot.'/user/lib.php');
require_once($CFG->dirroot.'/course/lib.php');

class local_solarvic_external extends external_api {

    public static function expiration_date_parameters() {
        return new external_function_parameters(
            array (
                'courseid' => new external_value(PARAM_INT, 'Course Ids', VALUE_DEFAULT, 46), 
                'userid'   => new external_value(PARAM_INT, 'Users Ids', VALUE_DEFAULT, 353),
                    )
        );
    }
    public static function expiration_date($courseid, $userid) {
        global $DB, $CFG;
        $requestinfo =[];

        $params = self::validate_parameters(self::expiration_date_parameters(), array('courseid' => $courseid, 'userid' => $userid));

        // $transaction = $DB->start_delegated_transaction(); //If an exception is thrown in the below code, all DB queries in this code will be rollback.



        // if (trim($enrol->courseid) == '') {
        //     throw new invalid_parameter_exception('Invalid courseid');
        // }
        // if (trim($enrol->userid) == '') {
        //     throw new invalid_parameter_exception('Invalid userid');
        // }

        $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);

        foreach ($instance as $key => $value) {
            if($DB->record_exists('grade_items',array('courseid'=>$value->courseid,'categoryid'=>$value->category,'itemtype'=>'mod','itemmodule'=>'lti','iteminstance'=>$value->ltiid))){
                //$all_module[] = $value;
            // }else{
            //     $new_grade_item = new stdClass();
            //     $new_grade_item->courseid = $value->courseid;
            //     $new_grade_item->categoryid = $value->category;
            //     $new_grade_item->itemname = $value->ltiname;
            //     $new_grade_item->itemtype = 'mod';
            //     $new_grade_item->itemmodule = 'lti';
            //     $new_grade_item->iteminstance = $value->ltiid;
            //     $new_grade_item->itemnumber = 0;
            //     $new_grade_item->grademax = $value->grade;
            //     $new_grade_item->timecreated = $value->timecreated;
            //     $new_grade_item->timemodified = $value->timemodified;

            //     $insert_new_gradeitem = $DB->insert_record('grade_items',$new_grade_item);
            //     $count++;
            // }
        }


        if ($DB->record_exists('enrol', ['courseid' => $courseid, 'enrol' => 'manual'])){
            
            $instance = $DB->get_record('enrol', ['courseid' => $courseid, 'enrol' => 'manual']);
            $enrolid = $instance->id;

            if ($DB->record_exists('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid])){
                //------------Finding course expiration--------------------------------------------------------------
                $search = $DB->get_record('user_enrolments', ['enrolid' => $enrolid, 'userid' => $userid]);
                $expiration = $search->timeend;

                $reason = 'NULL';
                $feedback = 'Success!';

            }
            else{
                $expiration = 0;
                $reason = 'record in user_enrolments does not exist, check userid';
                $feedback = 'Failed';
            }
        }
        else{
            $expiration = 0;
            $reason = 'record in enrol does not exist, check courseid';
            $feedback = 'Failed';
        }


        /// now security checks
        // $context = context_course::instance($enrol->courseid);
        // self::validate_context($context);
        // require_capability('moodle/course:managegroups', $context);

        // $transaction->allow_commit();
        $requestinfo = [
            'courseid'=>$courseid,
            'userid'=>$userid,
            'expiration'=>$expiration,
            'reason'=>$reason,
            'message'=>$feedback
            ];
        return $requestinfo;

    }
    public static function expiration_date_returns() {
        return new external_multiple_structure( 
            new external_single_structure(
                array(
                    'courseid' => new external_value(PARAM_INT, 'course ids'),
                    'userid'=> new external_value(PARAM_INT, 'user ids'),
                    'expiration'=>new external_value(PARAM_INT,'expiration date'),
                    'reason'=>new external_value(PARAM_TEXT,'reason for failure'),
                    'message'=>new external_value(PARAM_TEXT,'Success or Failure')

                )
            )
        );
    }
}