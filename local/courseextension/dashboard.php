<?php
require_once '../../config.php';
global $user, $DB, $CFG, $PAGE;

$PAGE->set_url('/local/courseextension/dashboard.php');
$PAGE->set_context(context_system::instance());
$PAGE->set_title('Course Extension Settings');
$PAGE->set_pagelayout('standard');

//user must be logged in to access this page and is headteacher
require_login();

// require_capability('local/courseextension:admin', context_system::instance());
//serialising course categories to store as an array
//database: mdl_courseextensions
if (!has_capability('local/studentmanager:admin', context_system::instance()))
{
    echo $OUTPUT->header();
    echo "<h3>You do not have permission to view this page.</h3>";
    echo $OUTPUT->footer();
    exit;
}



// Process form submission
function updating_courseextension_database () {
        // Retrieve form data
        // // $apitoken = $_POST['apitoken'];
        // $value2 = $_POST['Secret Key'];
        // $value3 = $_POST['Paramter URL'];
        // $value4 = $_POST['Paramter URL'];
        // // ... add more form fields as needed

        // // Store the values in the database or perform any necessary processing
        // $db = $DB->get_connection();

        // $apitoken = $_POST['apitoken'];

        // $data = new stdClass();
        // $data->apitoken = $apitoken;

        // $DB->insert_record('courseextension', $data);

        // $db->close();
        // // Display a success message or perform any other action
        echo '<p>Values have been saved successfully!</p>';
}

echo $OUTPUT->header();
echo $OUTPUT->render_from_template('local_courseextension/dashboardsettings', $obj);

echo $OUTPUT->footer();