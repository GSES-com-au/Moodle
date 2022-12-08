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

//send these URL variables to the output render from template
$obj = new stdClass();
$obj->month = (int)$month;
$obj->year = (int)$year;
$obj->startdate = (int)$startdate;
//F display as month 
//use month and year to create a time and convert to date in the format of just the month
$obj->monthname = date('F', strtotime($year."-".$month));

//grab page title from language
$strpagetitle = get_string('studentmanager', 'local_studentmanager'); 
$strpageheading = get_string('studentmanager', 'local_studentmanager'); 

//title as page
$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

//grab obj month and year and grab start time
$start = mktime(0,0,0,1,1,$obj->year);
$end = mktime(23,59,00,12,0,$obj->year);

//database 
$table = 'user_enrolments';    
//$user_list = $DB->get_records($table);
/*
$user_list = $DB->get_records_sql('SELECT ue.userid '.
    'FROM {user_enrolments} ue'.
    'WHERE ue.timecreated = ? '. //put start and end data conditions
    'AND ue.timecreated = ?', array($start, $end));*/

$user_list = $DB->get_records_sql('SELECT ue.userid '.
    'FROM {user_enrolments} ue '.
    'WHERE ue.timecreated >= ? '.
    'AND ue.timecreated <= ? ', array($start, $end));
/*
foreach($user_list as $key => $val) {
    if ($n == 0) {
        echo '<br/>';
        echo '<br/>';
        echo '<br/>';
        print_r($key = $val);
        $n += 1;
    }
    
    if ($key == "timestart" && $val < $start && $val > $end) {
        
    }
}

$results = new stdClass();
$results->data = array_values($user_list);*/



// get all unquie graders for selected month and year
//finds all grades that have been completed and looks at grade_grades tables
//user that did the modifiying (grading)
/*
$sql = "SELECT DISTINCT(gg.usermodified) as graderid
FROM {grade_grades} AS gg  
LEFT JOIN {user} AS grader ON grader.id = gg.usermodified 
WHERE gg.usermodified <> '' AND gg.finalgrade > 0 AND gg.timemodified >= ". $start." AND gg.timemodified <=".$end ;
//gg.usermodified someone actually graded, final grade exits and time modified fits bettween start and end time of 
$graders = $DB->get_records_sql($sql);

//sql to get records

//cycle through list of graders $DB moodle specific
foreach($graders as $key => $value) {
    $graders[$key] = $DB->get_record('user',['id'=>$graders[$key]->graderid],'firstname, lastname, id, email');
}
$results->data = array_values($graders);
*/
//resets array values

//output standard moodle header and footer
echo $OUTPUT->header();

//dynamic html for 
echo $OUTPUT->render_from_template('local_studentmanager/searchbar', $obj);
echo $OUTPUT->render_from_template('local_studentmanager/searchresults', $results);

echo $OUTPUT->footer();
