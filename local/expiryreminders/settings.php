<?php
// This file is part of Moodle - https://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Adds admin settings for the plugin.
 *
 * @package     local_expiryreminders
 * @category    admin
 * @copyright   2023 GSES
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_expiryreminders_settings', new lang_string('pluginname', 'local_expiryreminders')));
    $settingspage = new admin_settingpage('managelocalexpiryreminders', new lang_string('manage', 'local_expiryreminders'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext('local_expiryreminders/acapiurl', get_string('acapiurl', 'local_expiryreminders'),
    get_string('acapiurl_desc', 'local_expiryreminders'), 'https://gses.api-us1.com/api/3/contacts', PARAM_TEXT));
        $settingspage->add(new admin_setting_configtext('local_expiryreminders/acapikey', get_string('acapikey', 'local_expiryreminders'),
    get_string('acapikey_desc', 'local_expiryreminders'), '', PARAM_TEXT));
    }

    $ADMIN->add('localplugins', $settingspage);
}