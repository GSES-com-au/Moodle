<?php

require_once '../../config.php';
global $USER, $DB, $CFG;

require_login();

if (!has_capability('local/studentmanager:admin', context_system::instance()))
{
    echo $OUTPUT->header();
    echo "<h3>You do not have permission to view this page.</h3>";
    echo $OUTPUT->footer();
    exit;
}

$dataformat = optional_param('dataformat', '', PARAM_ALPHA);
//grab url parameters
$start_date = optional_param('startdate', '', PARAM_TEXT);
$end_date = optional_param('enddate', '', PARAM_TEXT);
$obj = new stdClass();
$obj->startdate = (string)$start_date;
$obj->enddate = (string)$end_date;

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
    
    $enrolments = [];
    foreach($enrol_user_list as $key => $value) {
        $enrolments[$key] = $DB->get_record('user', ['id'=>$value->userid],'firstname, lastname, email');
        $courseidlist[$key] = $DB->get_record('enrol', ['id'=>$value->enrolid], 'courseid');
        $coursename_object = $DB->get_record('course',['id'=>$courseidlist[$key]->courseid], 'fullname');
        $startdate_object = $DB->get_record('user_enrolments',['id'=>$value->userid],'timecreated');
        $enrolments[$key]->coursename = $coursename_object->fullname;
        $enrolments[$key]->startdate = date("Y-m-d", $startdate_object->timecreated);

        $enrolments[$key]->value = $cost->enrolmentrate;
        $totalcost += 1;
    }
    
    //cost calculations and send to searchresults template
    $flatcost = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
    $flatcost = $flatcost->flatcost;
    $totalenrolcost = $totalcost*$cost->enrolmentrate;
    $totalcost = $totalenrolcost + $flatcost;

    $cost_object = $DB->get_record('local_enrolment_rates', ['id'=>'1'], 'enrolmentrate');
    $cost_object->flatcost = $flatcost;
    $cost_object->totalenrolcost = $totalenrolcost;
    $cost_object->totalcost = $totalcost;

    if ($enrolments) {
        $results->data = array_values($enrolments);
        //$results->costdata = $test;
        $resultsbot->data = $cost_object;
    }
}



$objj = new ArrayObject($results->data);
$it = $objj->getIterator();

error_log(print_r($it, true));


$columns = array(
    'firstname' => "First Name",
    'lastname' => "Last Name",
    'email' => "Email",
    'coursename' => "Course",
    'startdate' => "Start Date",
    'value' => "Fee"
);


\core\dataformat::download_data('graderdata', $dataformat, $columns, $it, function($record) {
    return $record;
});