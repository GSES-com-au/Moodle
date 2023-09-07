<?php

// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../config.php');
global $USER, $DB, $CFG;

$PAGE->set_url('/local/studentmanager/rates.php');
$PAGE->set_context(context_system::instance());
//$PAGE->requires->js('/local/studentmanager/assets/studentmanager.js');

require_login();

if (!has_capability('local/studentmanager:admin', context_system::instance()))
{
    echo $OUTPUT->header();
    echo "<h3>You do not have permission to view this page.</h3>";
    echo $OUTPUT->footer();
    exit;
}


$strpagetitle = get_string('studentmanager', 'local_studentmanager');
$strpageheading = get_string('rates', 'local_studentmanager');

$PAGE->set_title($strpagetitle);
$PAGE->set_heading($strpageheading);

$rates = $DB->get_records('local_enrolment_rates', null);
// foreach ($rates as $key => $value)
// {
//   $rates[$key]->monthname = date("F", mktime(0, 0, 0, $rates[$key]->month, 10));
// }

$results = new stdClass();
$results->data = array_values($rates);

echo $OUTPUT->header();

//hide new rate button if database is not empty
$hide = $DB->get_record('local_enrolment_rates', ['id'=>'1']);
if ($hide) {
    echo $OUTPUT->render_from_template('local_studentmanager/rates2', $results);
} else {
    echo $OUTPUT->render_from_template('local_studentmanager/rates', $results);
}



echo $OUTPUT->footer();
