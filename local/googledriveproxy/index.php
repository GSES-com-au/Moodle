<?php
require_once '../../config.php';
require_once($CFG->dirroot.'/local/googledriveproxy/lib.php');

global $USER, $DB, $CFG;

require_login();

$PAGE->set_url('/local/googledriveproxy/index.php');
$PAGE->set_context(context_system::instance());
$strpagetitle = 'Google Drive Proxy';
$PAGE->set_title($strpagetitle);

$file_id = '1Q2xyo0K9UpRRGZvV2DCpPe1KDJ0vcdsR';

$embed_url = local_yourplugin_get_google_drive_embed_url($file_id);

echo $OUTPUT->header();
echo '<iframe src="' . s($embed_url) . '" width="100%" height="600px" frameborder="0" allowfullscreen></iframe>';
echo $OUTPUT->footer();

function local_yourplugin_get_oauth_token() {
    global $DB;

    // Get the Google OAuth issuer
    $issuers = \core\oauth2\api::get_all_issuers();
    $issuer = null;
    foreach ($issuers as $candidate) {
        if ($candidate->get('servicetype') === 'google') {
            $issuer = $candidate;
            break;
        }
    }

    if (!$issuer) {
        throw new moodle_exception('error:no_oauth2_issuer', 'local_yourplugin');
    }

    // Get system OAuth client
    $client = \core\oauth2\api::get_system_oauth_client($issuer);
    if (!$client) {
        throw new moodle_exception('error:no_oauth2_client', 'local_yourplugin');
    }

    $refresh_token = $client->get_refresh_token();
    return local_yourplugin_get_access_token($refresh_token);
}

function local_yourplugin_get_access_token($refresh_token) {
    $client_id = 'YOUR_CLIENT_ID'; // Replace with your client ID
    $client_secret = 'YOUR_CLIENT_SECRET'; // Replace with your client secret

    $url = 'https://oauth2.googleapis.com/token';

    $post_fields = http_build_query([
        'client_id' => $client_id,
        'client_secret' => $client_secret,
        'refresh_token' => $refresh_token,
        'grant_type' => 'refresh_token',
    ]);

    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => $post_fields,
        ],
    ];

    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);

    if ($response === FALSE) {
        throw new moodle_exception('error:token_request_failed', 'local_yourplugin');
    }

    $data = json_decode($response, true);
    return $data['access_token'];
}

function local_yourplugin_get_google_drive_embed_url($file_id) {
    $token = local_yourplugin_get_oauth_token();

    // Generate the embed URL with OAuth 2 authentication
    return "https://drive.google.com/embeddedview?id={$file_id}&access_token={$token}";
}
