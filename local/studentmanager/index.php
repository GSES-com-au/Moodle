<?php
require_once '../../config.php';
global $user, $DB, $CFG;

$PAGE->set_url('/local/studentmanager/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/studentmanager/assets/studentmanager.js');
//user must be logged in to access this page and is headteacher
require_login();

if (!has_capability('local/studentmanager:admin', context_system::instance()))
{
    echo $OUTPUT->header();
    echo "<h3>You do not have permission to view this page.</h3>";
    echo $OUTPUT->footer();
    exit;
}

//grab url parameters
$start_date = optional_param('startdate', '', PARAM_TEXT);
$end_date = optional_param('enddate', '', PARAM_TEXT);
$course_filter = optional_param('course', '', PARAM_TEXT);

$course = $DB->get_records('course', $conditions = [], $sort='', $fields='id,fullname', $limitfrom=0, $limitnum=0); 

$course = (array) $course;

$obj = new stdClass();
$obj->startdate = (string)$start_date;
$obj->enddate = (string)$end_date;
$obj->data = array_values($course);
//$obj->data = array_values($course_list);
//$obj->monthname = date('F', strtotime($year."-".$month)); converting time to str

//grab page title from language
$strpagetitle = get_string('studentmanager', 'local_studentmanager'); 
$strpageheading = get_string('studentmanager', 'local_studentmanager'); 

//title as page
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

$results = new stdClass();
$resultsbot = new stdClass();

//Database query
if (!empty($start_date)) {
    $start_date_array = getDate(strtotime("$start_date"));
    $end_date_array = getDate(strtotime("$end_date"));
    /* Array object mapping for $start_date_array
    Array
    (
        [seconds] => 0
        [minutes] => 0
        [hours] => 0
        [mday] => 21
        [wday] => 6
        [mon] => 5
        [year] => 2011
        [yday] => 140
        [weekday] => Saturday
        [month] => May
        [0] => 1305936000
    )
    */
    //grab obj month and year and grab start time
    $start_date_query = mktime(0,0,0, "$start_date_array[mon]", "$start_date_array[mday]", "$start_date_array[year]");
    $end_date_query = mktime(23,59,00, "$end_date_array[mon]", "$end_date_array[mday]", "$end_date_array[year]");
    
    

    $table = 'user_enrolments';    
    $enrol_user_list = $DB->get_records_sql('SELECT ue.id, ue.userid, ue.enrolid '.
        'FROM {user_enrolments} ue '.
        'WHERE ue.timecreated >= ? '.
        'AND ue.timecreated <= ? ', array($start_date_query, $end_date_query));
    
    //get cost per student
    $cost = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
    
    $totalcost = 0;
    
    //apply course filter if not all courses was selected
    if ($course_filter != 0) {
        $enrol_user_list = $DB->get_records_sql('SELECT ue.id, ue.userid, ue.enrolid, e.courseid '.
        'FROM {user_enrolments} ue '.
        'LEFT JOIN {enrol} e ON ue.enrolid = e.id '.
        'WHERE ue.timecreated >= ? '.
        'AND ue.timecreated <= ? '.
        'AND e.courseid = ?', array($start_date_query, $end_date_query, $course_filter));
    }

    $enrolments = [];
    foreach($enrol_user_list as $key => $value) {
        $enrolments[$key] = $DB->get_record('user', ['id'=>$value->userid],'firstname, lastname, id, email');
        if ($course_filter != 0) {
            $courseidlist[$key] = $DB->get_record('enrol', ['id'=>$value->enrolid, 'courseid'=>$course_filter], 'courseid');
        } else {
            $courseidlist[$key] = $DB->get_record('enrol', ['id'=>$value->enrolid], 'courseid');
        }
        #$courseidlist[$key] = $DB->get_record('enrol', ['id'=>$value->enrolid], 'courseid'); original
       
        $coursename_object = $DB->get_record('course',['id'=>$courseidlist[$key]->courseid], 'fullname');
        $startdate_object = $DB->get_record('user_enrolments',['id'=>$value->userid],'timecreated');
        $enrolments[$key]->coursename = $coursename_object->fullname;
        error_log(print_r($startdate_object,true));
        #$enrolments[$key]->startdate = date("Y-m-d", $startdate_object->timecreated);
        $enrolments[$key]->startdate = "2222-05-01";
        $enrolments[$key]->value = $cost->enrolmentrate;
        $totalcost += 1;
    }
    
    $enrolmentamount = $totalcost;
    //cost calculations and send to searchresults template
    $flatcost = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
    $flatcost = $flatcost->flatcost;
    $totalenrolcost = $totalcost*$cost->enrolmentrate;
    $totalenrolcost = number_format((float)$totalenrolcost, 2, '.', '');
    

    $no_days = date("d",strtotime($end_date) - strtotime($start_date));
    $no_days = (int)$no_days;
    $cost_per_day = $flatcost / 365;
    $cost_per_day = number_format((float)$cost_per_day, 2, '.', '');
    $flatcost_for_period = number_format((float)$no_days * $cost_per_day,2,'.', '');;

    $totalcost = $totalenrolcost + $flatcost_for_period;

    $cost_object = $DB->get_record('local_enrolment_rates', ['id'=>'1'], 'enrolmentrate');
    $cost_object->flatcost_for_period = $flatcost_for_period;
    $cost_object->bill_period = $no_days;
    $cost_object->cost_per_day = $no_days;
    $cost_object->totalenrolcost = $totalenrolcost;
    $cost_object->totalcost = $totalcost;
    $cost_object->enrolmentnumber = $enrolmentamount;
    $cost_object->enrolmentrate = $cost->enrolmentrate;

    if ($enrolments) {
        $results->data = array_values($enrolments);
        //$results->costdata = $test;
        $resultsbot->data = $cost_object;
    }
}



echo '<br/>';
echo '<br/>';
echo '<br/>';

//output standard moodle header and footer
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_studentmanager/searchbar', $obj);
////echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results);

//cast to an array to check if empty 
$result_arr = (array)$results;
//$result_arr = array_filter($result_arr);

if (!empty($start_date)) {
    $start_date = date("d-m-Y", strtotime($start_date));  
    $end_date = date("d-m-Y", strtotime($end_date));  
}

$results->startdate = $start_date;
$results->enddate = $end_date;
if (empty($start_date)) {
    echo $OUTPUT->render_from_template('local_studentmanager/homepagetable', $obj);
} else {
    echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results); //[]
    echo $OUTPUT->render_from_template('local_studentmanager/searchresultsbot', $resultsbot);
    echo $OUTPUT->download_dataformat_selector('Download', 'download.php', 'dataformat', array('startdate' => $start_date, 'enddate' => $end_date));
}

echo $OUTPUT->footer();