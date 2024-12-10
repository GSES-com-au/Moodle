<?php
require_once($CFG->libdir . '/oauthlib.php');

// function local_yourplugin_get_oauth_token() {
//     global $DB, $USER;

//     $issuers = \core\oauth2\api::get_all_issuers();
//     foreach ($issuers as $x) {
//         if ($x->get_display_name() == 'Google') {
//             $issuer = $x;
//             break;
//         }
//     }

//     if ($issuer->is_system_account_connected()) {
//         $client = \core\oauth2\api::get_system_oauth_client($issuer);
//         if ($client) {
//             echo "<br>";
//             echo "<br>";
//             echo "<br>";
//             echo "<br>";
//             echo print_r($client);
//         }
//     }

//     return 0;

//     // $oauth2service = \core\oauth2\api::get_service('Google');

//     // $oauth2service = $DB->get_record('oauth2_services', array('name' => 'Google'), '*', MUST_EXIST);
//     // $oauth2account = \core\oauth2\api::get_user_oauth2_account($USER->id, $oauth2service->id);

//     // if (!$oauth2account) {
//     //     throw new moodle_exception('error:no_oauth2_account', 'local_yourplugin');
//     // }

//     // return \core\oauth2\api::get_user_oauth_token($oauth2account);
// }

// function local_yourplugin_get_google_drive_embed_url($file_id) {
//     $token = local_yourplugin_get_oauth_token();

//     // Generate the embed URL with OAuth 2 authentication
//     return "https://drive.google.com/embeddedview?id={$file_id}&authuser={$token->user_id}&auth=1";
// }