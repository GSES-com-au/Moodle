<?php
require_once '../../config.php';
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
global $USER, $DB, $CFG, $PAGE, $SESSION, $COURSE, $SITE, $THEME;
$PAGE->set_url('/local/courseextension/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Course Extension');
$PAGE->set_pagelayout('standard');

require_login();

$userid = $USER->id;

$sql = "
    SELECT c.fullname, c.id AS course_id, ue.timestart, ue.timeend
    FROM mdl_course_categories cc
    JOIN mdl_course c ON cc.id = c.category
    JOIN mdl_enrol e ON e.courseid = c.id
    JOIN mdl_user_enrolments ue ON ue.enrolid = e.id AND ue.userid = :userid
    WHERE cc.name LIKE :category1 OR cc.name LIKE :category2 OR cc.name LIKE :category3 OR cc.name LIKE :category4";
$params = [
    'userid' => $userid,
    'category1' => '%(GCPV)%',
    'category2' => '%(GCwB)%',
    'category3' => '%(SAPS)%',
    'category4' => '%Miscellaneous%'
];
$results = $DB->get_records_sql($sql, $params);

//Removes 1 day from timeend value and appends maximumexpiration value into respective arrays
//This also removes courses which are maximum expiration dates are past todays date
foreach ($results as $key => $value) {
    date_default_timezone_set('Australia/Sydney');
    $currenttime = new DateTime('now');

    $results[$key]->timeend = strtotime('-1 day', $value->timeend);
    $extension = strtotime('+1 year 6 months', $value->timestart);
    $results[$key]->maximumexpiration = $extension;
    if ($currenttime->getTimestamp() > $value->maximumexpiration) {
        unset($results[$key]);
    }
}

//Converting associative array into index array
$results = array_values($results);

$excuse = [
['excuse' => 'Ran out of time'],
['excuse' => 'Personal or family emergency'],
['excuse' => 'Lack of motivation or interest'],
['excuse' => 'Learning difficulties'],
['excuse' => 'Technical difficulties'],
['excuse' => 'Language barriers'],
['excuse' => 'Sickness or illness']
];

$obj = [
    'firstname' => $USER->firstname,
    'lastname' =>  $USER->lastname,
    'email' =>  $USER->email,
    'excuse' => $excuse,
    'results' => $results,
];
echo $OUTPUT->header();
// var_dump($USER);
// echo '<pre>'; print_r($filterlist); echo '</pre>';
// echo '<pre>'; print_r($usercourseprofile); echo '</pre>';
// echo '<pre>'; print_r($excuse); echo '</pre>';
echo $OUTPUT->render_from_template('local_courseextension/extensionform', $obj);
echo $OUTPUT->footer();
