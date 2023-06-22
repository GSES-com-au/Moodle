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
        global $DB, $PAGE;

        $userid = $event->get_context()->instanceid;
        // error_log('userid: ' . $userid);
        $sql = "SELECT *
        FROM mdl_user_snapshot
        WHERE moodleuserid = :moodleuserid";

        $params = array(
        'moodleuserid' => $userid
        );


        if ($DB->record_exists_sql($sql, $params)) {
            $sql = "SELECT u.username, u.firstname, u.lastname, u.email, s.username AS snapshot_username, s.firstname AS snapshot_firstname, s.lastname AS snapshot_lastname, s.email AS snapshot_email
            FROM mdl_user u
            LEFT JOIN mdl_user_snapshot s ON u.id = s.moodleuserid
            WHERE u.id = :moodleuserid";
    
            $params = array(
                'moodleuserid' => $userid
            );
    
            $userData = $DB->get_record_sql($sql, $params);

            $sendemail = false;
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
            if ($sendemail) {
                // Compose and send the email with details
                $subject = "Profile Field Change Notification";
                $messageHtml = "
                <p>USERID: " . $userid . " has had their profile field(s) changed, please see below information</p>
                <pre>" . htmlspecialchars(var_export($userData, true)) . "</pre>";
                if ($userData->username != $userData->snapshot_username) {
                    $messageHtml .= '<p>The user has changed their <b>username</b></p>';
                }
            
                if ($userData->firstname != $userData->snapshot_firstname) {
                    $messageHtml .= '<p>The user has changed their <b>firstname</b></p>';
                }
            
                if ($userData->lastname != $userData->snapshot_lastname) {
                    $messageHtml .= '<p>The user has changed their <b>lastname</b></p>';
                }
            
                if ($userData->email != $userData->snapshot_email) {
                    $messageHtml .= '<p>The user has changed their <b>email</b></p>';
                }
                $messageHtml .= '<p><b>Note:</b> The snapshot data was taken before the details were changed</p>';


                email_to_user(get_admin(), '',$subject, '',$messageHtml, '', '', false);       
            }

        }else{
            // Compose and send the email with details
            $subject = "Profile Field Plugin Failed";
            $messageHtml = "<p>The MoodleAlert plugin which listens to user profile fields being updated in Moodle has failed to insert or update the record with a " . $userid . " in the mdl_user_snapshot table</p>";
            email_to_user(get_admin(), '',$subject, '',$messageHtml, '', '', false);  
            error_log("User does not exist in mdl_user_snapshot table");
        }
    }
}

