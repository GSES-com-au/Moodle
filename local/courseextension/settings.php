<?php
// This file is part of the Certificate localule for Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or localify
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
 * Creates a link to the upload form on the settings page.
 *
 * @package    local_courseextension
 * @copyright  2013 Mark Nelson <markn@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 defined('MOODLE_INTERNAL') || die;
 
 $ADMIN->add('modules', new admin_category('courseextension', new lang_string("pluginname", "local_courseextension")));

 // Dashboard link.
$ADMIN->add('courseextensions',
new admin_externalpage(
    'local_courseextensions/dashboard',
    new lang_string("myhome"),
    new moodle_url("/local/courseextension/dashboard.php")
)
);

// Settings.
$ADMIN->add('courseextension',
    new admin_externalpage(
        'local_courseextension/settings',
        new lang_string("settings"),
        new moodle_url("/local/courseextension/dashboard.php")
    )
);



//  $ADMIN->add('localsettings', new admin_category('courseextension', get_string('pluginname', 'local_courseextension')));
//  $settings = new admin_settingpage('localsettingcourseextension', new lang_string('courseextensionsettings', 'local_courseextension'));
 
//  $settings->add(new admin_setting_configcheckbox('courseextension/verifyallcertificates',
//      get_string('verifyallcertificates', 'courseextension'),
//      get_string('verifyallcertificates_desc', 'courseextension', $url),
//      0));
 
//  $settings->add(new admin_setting_configcheckbox('courseextension/showposxy',
//      get_string('showposxy', 'courseextension'),
//      get_string('showposxy_desc', 'courseextension'),
//      0));
//  $settings->add(new admin_setting_heading('defaults',
//      get_string('localeditdefaults', 'admin'), get_string('condiflocaleditdefaults', 'admin')));
 
//  $yesnooptions = [
//      0 => get_string('no'),
//      1 => get_string('yes'),
//  ];
 
//  $ADMIN->add('courseextension', $settings);
 
//  // Element plugin settings.
//  $ADMIN->add('courseextension', new admin_category('courseextensionelements', get_string('elementplugins', 'courseextension')));
//  $plugins = \core_plugin_manager::instance()->get_plugins_of_type('courseextensionelement');
//  foreach ($plugins as $plugin) {
//      $plugin->load_settings($ADMIN, 'courseextensionelements', $hassiteconfig);
//  }
 
//  // Tell core we already added the settings structure.
//  $settings = null;
 
