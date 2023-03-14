<?php


if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}


require_once $CFG->libdir.'/authlib.php';

/**
 * Plugin for no authentication.
 */
class auth_plugin_wdmwpmoodle extends auth_plugin_base {
    /**
     * Constructor.
     */

    public function user_login($username, $password = null)
    {
	
        global $CFG, $DB;
        //echo '<pre>';print_R($CFG);echo '</pre>';
        //echo $username.'   '.$password;exit;
        if ($password == null || $password == '') {
            return false;
        }
        $user = $DB->get_record('user', array('username' => $username, 'password' => $password, 'mnethostid' => $CFG->mnet_localhost_id));

        if ($user) {
            return true;
        }

        return false;
    }
    

}


