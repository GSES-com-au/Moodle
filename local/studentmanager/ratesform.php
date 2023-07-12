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

require_once("forms/rates.php");



$PAGE->set_url('/local/studentmanager/rates.php');
$PAGE->set_context(context_system::instance());
// $PAGE->requires->js('/local/staffmanager/assets/staffmanager.js');

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

$id = optional_param('id', '', PARAM_TEXT);
error_log("$id 1");

$mform = new rates_form("?id=$id");
error_log("$id 2");
$toform = [];
error_log("$id 3");




if ($mform->is_cancelled()) {
    redirect("/local/studentmanager/rates.php", '',10);
} elseif ($fromform = $mform->get_data()) {
    error_log("$id 4");
    if ($id) {
        //has id then update
        $obj = $DB->get_record('local_enrolment_rates', ['id'=>$id]);
        $obj->enrolmentrate = $fromform->enrolmentrate;
        $obj->flatcost = $fromform->flatcost;
        $DB->update_record('local_enrolment_rates', $obj);
        error_log("$id 5");
    } else {
        //otherwise add new record
        $obj = new stdClass();
        $obj->enrolmentrate = $fromform->enrolmentrate;
        $obj->flatcost = $fromform->flatcost;
        $orgid = $DB->insert_record('local_enrolment_rates', $obj);
        error_log("$id 6");
    }
    error_log("$id 7");
    redirect("/local/studentmanager/rates.php?id=$id", 'Changes saved', 10,  \core\output\notification::NOTIFY_SUCCESS);
} else {
    if ($id) {
        $toform = $DB->get_record('local_enrolment_rates', ['id'=>$id]);
        error_log("$id log");
    }
    //Set default data (if any)
    $mform->set_data($toform);

    echo $OUTPUT->header();
    $mform->display();
    echo $OUTPUT->footer();
}
?>