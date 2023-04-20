<?php
// Include Moodle config file and libraries
require_once '../../config.php';

// Get course ID and user ID from query string
$courseId = $_GET['courseId'];
$userId = $_GET['userId'];

// Connect to Moodle database
$conn = mysqli_connect($CFG->dbhost, $CFG->dbuser, $CFG->dbpass, $CFG->dbname);

// Query to get expiration date for specified user and course
$query = "SELECT expiration FROM {enrol} WHERE courseid = $courseId AND userid = $userId";

// Execute query and get result
$result = mysqli_query($conn, $query);

// Fetch row from result
$row = mysqli_fetch_assoc($result);

// Extract expiration date from row
$expiration = $row['expiration'];

// Return expiration date in JSON format
echo json_encode(['expiration' => $expiration]);