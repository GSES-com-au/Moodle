<?php
namespace local_moodlealert\task;                                              //Required to be first on the page
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

class observer 
{                              
    public static function user_updated(\core\event\user_updated $event)
    {
        global $DB, $PAGE, $CFG;
        $userid = $event->get_context()->instanceid;
        $adminid = $event->userid;
        $siteurl = $CFG->wwwroot;
        
        // Check if user exists in snapshot table
        $sql = "SELECT *
            FROM mdl_user_snapshot
            WHERE moodleuserid = :moodleuserid";
        $params = array(
            'moodleuserid' => $userid
        );
        if ($DB->record_exists_sql($sql, $params)) {
            
            // Get old and new user details
            $sql = "SELECT u.username, u.firstname, u.lastname, u.email, s.username AS snapshot_username, s.firstname AS snapshot_firstname, s.lastname AS snapshot_lastname, s.email AS snapshot_email
                FROM mdl_user u
                LEFT JOIN mdl_user_snapshot s ON u.id = s.moodleuserid
                WHERE u.id = :moodleuserid";
            $params = array(
                'moodleuserid' => $userid
            );
            try {
                $userData = $DB->get_record_sql($sql, $params, $strictness = MUST_EXIST);
            } catch (Exception $e) {
                $message = 'MoodleAlert plugin failed to find the old and new user details for moodle user id ' . $userid . '. Exception details: ' . $e;
                error_log($message);
                email_to_user(get_admin(), '', 'MoodleAlert plugin error', '', $message, '', '', false);  
            }
            
            // Check if relevant fields have been changed
            $sendemail = false;
            if ($userData) {
                if ($userData->username != $userData->snapshot_username) {
                    $sendemail = true;
                }
                if ($userData->firstname != $userData->snapshot_firstname) {
                    $sendemail = true;
                }
                if ($userData->lastname != $userData->snapshot_lastname) {
                    $sendemail = true;
                }
                if ($userData->email != $userData->snapshot_email) {
                    $sendemail = true;
                }
            }
            
            if ($sendemail) {       // Only send email if the relevant fields were changed
                // Compose and send the email with details
                $subject = 'Profile Field Change Notification';
                $messageHtml = '<p><b>USERID: ' . $userid . ' </b> has had their profile field(s) changed by user with an id of <b>' . $adminid . '</b> on ' . $siteurl. ', please see below information</p>';
                if ($userData->username != $userData->snapshot_username) {
                    $messageHtml .= '<p>The <b>username</b> has changed</p>';
                }
            
                if ($userData->firstname != $userData->snapshot_firstname) {
                    $messageHtml .= '<p>The <b>first name</b> has changed</p>';
                }
            
                if ($userData->lastname != $userData->snapshot_lastname) {
                    $messageHtml .= '<p>The <b>last name</b> has changed</p>';
                }
            
                if ($userData->email != $userData->snapshot_email) {
                    $messageHtml .= '<p>The <b>email address</b> has changed</p>';
                }
                $messageHtml .= '<pre>' . htmlspecialchars(var_export($userData, true)) . '</pre><br/><p><b>Note:</b> The snapshot data was taken before the details were changed</p>';

                email_to_user(get_admin(), '', $subject, '', $messageHtml, '', '', false);  
            }
            
        } else {
            // User was not found in snapshot database. This means the user was updated via web services, plugin, or other API. No need to send alert email.
        }
    }
}

