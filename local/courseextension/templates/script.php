<?php
//PHP Script
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
require_once '../../config.php';
global $user, $DB, $CFG, $PAGE;

error_log('Script.php has run successfully!');