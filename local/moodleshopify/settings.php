<?php
// This file is part of moodle single sign



defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_helloworld_settings', new lang_string('pluginname', 'local_moodleshopify')));
    $settingspage = new admin_settingpage('managelocalhelloworld', new lang_string('heading', 'local_moodleshopify'));

    if ($ADMIN->fulltree) {

        $settingspage->add(new admin_setting_heading('auth_wdmwpmoodle/description', '', new lang_string('description', 'local_moodleshopify')));

        $settingspage->add(new admin_setting_configtext(
            'local_moodleshopify/sharedsecret',
            get_string('secretkey', 'local_moodleshopify'),
            get_string('secretkey_desc', 'local_moodleshopify'),
            '',
            PARAM_RAW
        ));



    }

    $ADMIN->add('localplugins', $settingspage);
}
