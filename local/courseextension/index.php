<?php
require_once '../../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
global $USER, $DB, $CFG, $PAGE, $SESSION, $COURSE, $SITE, $THEME;
$PAGE->set_url('/local/courseextension/index.php');
// $PAGE->requires->js('/local/courseextension/assets/loader.js');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Course Extension');
$PAGE->set_pagelayout('standard');

require_login();

$userid = $USER->id;
$enrolids = [];

//sql query ~ Grabs an array of records which the user is enrolled in 
$conditions = ['userid' => $userid];                                        //sending userid to mysql query
$table = 'user_enrolments';                                                  //table name in database
$enrolids = $DB->get_records($table, $conditions, $sort='', $fields='enrolid', $limitfrom=0, $limitnum=0); 

//Removes std class in array converts to usable array
foreach ($enrolids as $value){
    $arrayid[] = $value->enrolid;
}
//Finding all courseids the user is enrolled in
foreach ($arrayid as $value) {
    $conditions = ['id' => $value];                                                  //sending userid to mysql query
    $table = 'enrol';                                                                //table name in database
    $courseid = $DB->get_record($table, $conditions, $fields='courseid', $strictness = IGNORE_MISSING);
    $courselist[] = $courseid->courseid;
}
//finiding all ACCREDITATION coursenames/ids the user is enrolled in
foreach ($courselist as $value) {
    $assignname = $DB->get_record_sql('SELECT fullname, id FROM {course} WHERE id = :value AND (fullname REGEXP :course1|:course2|:course3)',
    [
    'value' => $value,
    'course1' => '(GCPV)',
    'course2' => '(GCwB)',
    'course3' => '(SAPS)',
    ]
    );
    $coursenamelist[] = $assignname;
}
//filters list and re-indexes array values from 0
$filterlist = array_values(array_filter($coursenamelist));

//Finding enrolids of ACCREDITATION courses
foreach ($filterlist as $value) {
    $conditions = ['courseid' => $value->id];                                                  //sending userid to mysql query
    $table = 'enrol';                                                                //table name in database
    $filterenrol = $DB->get_record($table, $conditions, $fields='id', $strictness = IGNORE_MISSING);
    $filterenrol_ids[] = $filterenrol;
}
//Finding Startdates of enrolments in user ACCREDITATION Courses
foreach ($filterenrol_ids as $value) {
    $conditions = ['enrolid' => $value ->id];                                                  //sending userid to mysql query
    $table = 'user_enrolments';                                                                //table name in database
    $course_startdate= $DB->get_record($table, $conditions, $fields='timestart, timeend', $strictness = IGNORE_MISSING);
    $filter_course_startdate[] = $course_startdate;
}

//Calculating Maximum expiration date of users ACCREDITATION Courses
foreach ($filter_course_startdate as $value) {
    date_default_timezone_set('Australia/Sydney');
    $extension = strtotime('+1 year 6 months', $value->timestart);
    $maximumexpiration[] = $extension;
}
//Creating new std class object array
foreach ($maximumexpiration as $value) {
    $obj = new stdClass();
    $obj->maximumexpiration = $value;
    $objects[] = $obj;
}
  
foreach ($maximumexpiration as $value) {
    date_default_timezone_set('Australia/Sydney');
    $currenttime = new DateTime('now');
    if ($currenttime->getTimestamp() > $value) {
        $currentcourses[] = $value;
    }
}




//Combining $filterlist and $filter_course_startdate together
$combinedArray = array();

foreach ($filterlist as $index => $value) {
$combinedArray[$index] = (object) array_merge((array) $value, (array) $filter_course_startdate[$index]);
}
//Combining $combinedArray and $maxexpiration together ~ Final product is a user profile with course expiration dates for each course
$usercourseprofile = array();
foreach ($combinedArray as $index => $value) {
    $usercourseprofile[$index] = (object) array_merge((array) $value, (array) $objects[$index]);
    }

$excuse = [
['excuse' => 'Ran out of time'],
['excuse' => 'Personal or family emergency'],
['excuse' => 'Lack of motivation or interest'],
['excuse' => 'Learning difficulties'],
['excuse' => 'Technical difficulties'],
['excuse' => 'Language barriers'],
['excuse' => 'Sickness or illness']
];
$quantity = [
    ['quantity' => '1'],
    ['quantity' => '2'],
    ['quantity' => '3'],
    ['quantity' => '4'],
    ['quantity' => '5'],
    ['quantity' => '6']


];
$obj = [
    'firstname' => $USER->firstname,
    'lastname' =>  $USER->lastname,
    'email' =>  $USER->email,
    'excuse' => $excuse,
    'quantity' => $quantity,
    'usercourseprofile' => $usercourseprofile,

];
echo $OUTPUT->header();
// var_dump($USER);
// echo($USER->firstname);
// echo($USER->lastname);
// echo($USER->email);
// echo($userid); 
// echo '<pre>'; print_r($filterlist); echo '</pre>';

// echo '<pre>'; print_r($usercourseprofile); echo '</pre>';
// echo '<pre>'; print_r($excuse); echo '</pre>';
echo $OUTPUT->render_from_template('local_courseextension/extensionform', $obj);
echo $OUTPUT->footer();

//Grab array index and select timeend value of that index