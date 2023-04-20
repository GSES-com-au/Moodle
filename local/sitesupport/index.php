<?php
require_once '../../config.php';
require_once("form/sitesupport.php");
require_once($CFG->dirroot . '/user/lib.php');

global $USER, $DB, $CFG;
$PAGE->set_url('/local/sitesupport/index.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title(get_string('contactsitesupport', 'admin'));
$PAGE->set_pagelayout('standard');

require_login();

//Instantiate simplehtml_form 
$mform = new sitesupport_form();

//Form processing and displaying is done here
if ($mform->is_cancelled()) {
    //Handle form cancel operation, if cancel button is present on form
} else if ($fromform = $mform->get_data()) {
  //In this case you process validated data. $mform->get_data() returns data posted in form.
} else {
  // this branch is executed if the form is submitted but the data doesn't validate and the form should be redisplayed
  // or on the first display of the form.

  //Set default data (if any)
  $mform->set_data($toform);
  //displays the form
}
// output standard moodle header and footer
echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_sitesupport/form', $obj);
// $mform->display();
echo $OUTPUT->footer();

