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
//copy pasted from index.php start
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

$results = new stdClass();
$results2 = new stdClass();
$resultsbot = new stdClass();

//Database query
if (!empty($start_date)) {
    //grab obj month and year and grab start time
    $start_date_array = getDate(strtotime("$start_date"));
    $end_date_array = getDate(strtotime("$end_date"));
    $start_date_query = mktime(0,0,0, "$start_date_array[mon]", "$start_date_array[mday]", "$start_date_array[year]");
    $end_date_query = mktime(23,59,00, "$end_date_array[mon]", "$end_date_array[mday]", "$end_date_array[year]");
    
    $enrol_user_list = $DB->get_records_sql('SELECT ue.id, ue.userid, ue.enrolid '.
        'FROM {user_enrolments} ue '.
        'WHERE ue.timecreated >= ? '.
        'AND ue.timecreated <= ? ', array($start_date_query, $end_date_query));
    
    //get cost per student
    $cost = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
    $totalcost = 0;
  
    if($course_filter != 0) {
        $enrolled_users_test = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname, u.email, c.fullname, ue.timestart
        FROM mdl_user u
        INNER JOIN mdl_user_enrolments ue ON ue.userid = u.id
        INNER JOIN mdl_enrol e ON e.id = ue.enrolid
        INNER JOIN mdl_course c ON c.id = e.courseid
        WHERE c.id = ?
        AND u.id IN (
            SELECT ra.userid
            FROM mdl_role_assignments ra
            INNER JOIN mdl_context ctx ON ctx.id = ra.contextid
            INNER JOIN mdl_course course ON course.id = ctx.instanceid
            WHERE course.id = ?
            AND ra.roleid = 5
            AND ue.timestart >= ? AND  ue.timestart <= ?)', array($course_filter, $course_filter, $start_date_query, $end_date_query));
    } else {
        $enrolled_users_test = $DB->get_records_sql('SELECT u.id, u.firstname, u.lastname, u.email, c.fullname, ue.timestart
        FROM mdl_user u
        INNER JOIN mdl_user_enrolments ue ON ue.userid = u.id
        INNER JOIN mdl_enrol e ON e.id = ue.enrolid
        INNER JOIN mdl_course c ON c.id = e.courseid
        AND u.id IN (
            SELECT ra.userid
            FROM mdl_role_assignments ra
            INNER JOIN mdl_context ctx ON ctx.id = ra.contextid
            INNER JOIN mdl_course course ON course.id = ctx.instanceid
            WHERE 
            ra.roleid = 5
            AND ue.timestart >= ? AND  ue.timestart <= ?)', array($start_date_query, $end_date_query));
    };
    foreach($enrolled_users_test as $user) {
        $user->startdate = date("d-m-Y", $user->timestart);
        $user->value = $cost->enrolmentrate;
        $totalcost += 1;
    }
    
    $enrolmentamount = $totalcost;
    //cost calculations and send to searchresults template
    $flatcost = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
    $flatcost = $flatcost->flatcost;
    $totalenrolcost = $totalcost*$cost->enrolmentrate;
    $totalenrolcost = number_format((float)$totalenrolcost, 2, '.', '');
    
    //time difference between start and end_date in days
    $startDate_datetime = new DateTime($start_date);
    $endDate_date_time = new DateTime($end_date);
    $interval = $startDate_datetime->diff($endDate_date_time);
    $no_days = (int)$interval->days;
    $no_days = $no_days + 1; //ensure period is inclusive date wise

    $cost_per_day = $flatcost / 365;
    $cost_per_day = number_format((float)$cost_per_day, 4, '.', '');
    $flatcost_for_period = number_format((float)$no_days * $cost_per_day,2,'.', '');;

    $totalcost = $totalenrolcost + $flatcost_for_period;

    $cost_object = $DB->get_record('local_enrolment_rates', ['id'=>'1'], 'enrolmentrate');
    $cost_object->flatcost_for_period = $flatcost_for_period;
    $cost_object->bill_period = $no_days;
    $cost_object->cost_per_day = $cost_per_day;
    $cost_object->totalenrolcost = $totalenrolcost;
    $cost_object->totalcost = $totalcost;
    $cost_object->enrolmentnumber = $enrolmentamount;
    $cost_object->enrolmentrate = $cost->enrolmentrate;

    if (!empty($enrolled_users_test)) {
        $results2->data = array_values($enrolled_users_test);
    }
    $resultsbot->data = $cost_object;
}
//copy pasted from index.php end

//remove unessary data
foreach ($results2->data as $key => $object) {
    unset($results2->data[$key]->id);
    unset($results2->data[$key]->timestart);
}
$objj = new ArrayObject($results2->data);
$it = $objj->getIterator();

$columns = array(
    'firstname' => "First Name",
    'lastname' => "Last Name",
    'email' => "Email",
    'coursename' => "Course",
    'startdate' => "Start Date",
    'value' => "Fee"
);

\core\dataformat::download_data(str_replace('.com', '', $CFG->wwwroot), $dataformat, $columns, $it, function($record) {
    return $record;
});