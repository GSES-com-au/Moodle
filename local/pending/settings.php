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
 * @package     local_pending
 * @category    admin
 * @copyright   2023 GSES
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $ADMIN->add('localplugins', new admin_category('local_pending_settings', new lang_string('pluginname', 'local_pending')));
    $settingspage = new admin_settingpage('managelocalpending', new lang_string('manage', 'local_pending'));

    if ($ADMIN->fulltree) {
        $settingspage->add(new admin_setting_configtext('local_pending/wcfieldid', get_string('wcfieldid', 'local_pending'),
    get_string('wcfieldid_desc', 'local_pending'), '', PARAM_INT));
        $settingspage->add(new admin_setting_configtext('local_pending/storeurl', get_string('storeurl', 'local_pending'),
    get_string('storeurl_desc', 'local_pending'), 'https://www.gses.com.au', PARAM_TEXT));
        $settingspage->add(new admin_setting_configtext('local_pending/storekey', get_string('storekey', 'local_pending'),
    get_string('storekey_desc', 'local_pending'), '', PARAM_TEXT));
        $settingspage->add(new admin_setting_configtext('local_pending/storesecret', get_string('storesecret', 'local_pending'),
    get_string('storesecret_desc', 'local_pending'), '', PARAM_TEXT));
    }

    $ADMIN->add('localplugins', $settingspage);
}