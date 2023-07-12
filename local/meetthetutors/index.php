<?php
require_once '../../config.php';

global $USER, $DB, $CFG;
$PAGE->set_url('/local/meetthetutors/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title("MeetTutors");
$PAGE->set_pagelayout('standard');

require_login();
$obj =[];
// output standard moodle header and footer
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_meetthetutors/form', $obj);
// $mform->display();
echo $OUTPUT->footer();

