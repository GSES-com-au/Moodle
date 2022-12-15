<?php

require_once '../../config.php';
global $USER, $DB, $CFG;
$PAGE->set_url('/local/studentmanager/index.php');
$PAGE->set_context(context_system::instance());
//loads student manager js
$PAGE->requires->js('/local/studentmanager/assets/studentmanager.js');

//user must be logged in to access this page
require_login();

//grab url params
$month = optional_param('month', '', PARAM_TEXT);
$year = optional_param('year', '', PARAM_TEXT);
$start_date = optional_param('startdate', '', PARAM_TEXT);
$end_date = optional_param('enddate', '', PARAM_TEXT);

//send these URL variables to the output render from template
$obj = new stdClass();
$obj->month = (int)$month;
$obj->year = (int)$year;
$obj->startdate = (string)$start_date;
$obj->enddate = (string)$end_date;

/*echo '<br/>';
echo '<br/>';
echo '<br/>';
print_r("$obj->startdate");
echo '<br/>';
print_r("$obj->enddate");*/
//F display as month 
//use month and year to create a time and convert to date in the format of just the month
$obj->monthname = date('F', strtotime($year."-".$month));

//grab page title from language
$strpagetitle = get_string('studentmanager', 'local_studentmanager'); 
$strpageheading = get_string('studentmanager', 'local_studentmanager'); 

//title as page
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

//convert string to date array
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

//database 
$table = 'user_enrolments';    
$enrol_user_list = $DB->get_records_sql('SELECT ue.id, ue.userid '.
    'FROM {user_enrolments} ue '.
    'WHERE ue.timecreated >= ? '.
    'AND ue.timecreated <= ? ', array($start_date_query, $end_date_query));

echo '<br/>';
//print_r($enrol_user_list);

$results = new stdClass();
//$results->data = array_values($user_list);*/

// get all unquie graders for selected month and year
//finds all grades that have been completed and looks at grade_grades tables
//user that did the modifiying (grading)

//gg.usermodified someone actually graded, final grade exits and time modified fits between start and end time of 
//$graders = $DB->get_records_sql($sql);

//sql to get records

//cycle through list of graders $DB moodle specific
foreach($enrol_user_list as $key => $value) {
    $enrolments[$key] = $DB->get_record('user',['id'=>$value->userid],'firstname, lastname, id, email');
}
//print_r($enrolments);
if ($enrolments) {
    $results->data = array_values($enrolments);
}
//resets array values

//output standard moodle header and footer
echo $OUTPUT->header();

//dynamic html for 
echo $OUTPUT->render_from_template('local_studentmanager/searchbar', $obj);
echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results);

echo $OUTPUT->footer();
