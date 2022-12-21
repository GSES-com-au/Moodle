<?php
require_once '../../config.php';
global $user, $DB, $CFG;

$PAGE->set_url('/local/studentmanager/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->requires->js('/local/studentmanager/assets/studentmanager.js');
//user must be logged in to access this page
require_login();

//grab url parameters
$start_date = optional_param('startdate', '', PARAM_TEXT);
$end_date = optional_param('enddate', '', PARAM_TEXT);

$obj = new stdClass();
$obj->startdate = (string)$start_date;
$obj->enddate = (string)$end_date;
//$obj->monthname = date('F', strtotime($year."-".$month)); converting time to str

//grab page title from language
$strpagetitle = get_string('studentmanager', 'local_studentmanager'); 
$strpageheading = get_string('studentmanager', 'local_studentmanager'); 

//title as page
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

$results = new stdClass();

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

    
    $enrolments = [];
    foreach($enrol_user_list as $key => $value) {
        $enrolments[$key] = $DB->get_record('user', ['id'=>$value->userid],'firstname, lastname, id, email');
        $courseidlist[$key] = $DB->get_record('enrol', ['id'=>$value->enrolid], 'courseid');
        $coursename_object = $DB->get_record('course',['id'=>$courseidlist[$key]->courseid], 'fullname');
        $enrolments[$key]->coursename = $coursename_object->fullname;
    }
    
    if ($enrolments) {
        $results->data = array_values($enrolments);
    }
}

echo '<br/>';
echo '<br/>';
echo '<br/>';



//output standard moodle header and footer
echo $OUTPUT->header();

echo $OUTPUT->render_from_template('local_studentmanager/searchbar', $obj); //[]
////echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results);

//cast to an array to check if empty 
$result_arr = (array)$results;
if (!$result_arr) {
    echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $obj);
} else {
    echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results); //[]
}

echo $OUTPUT->footer();