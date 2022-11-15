<?php
// This file is part of Moodle - http://moodle.org/
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
 * Edwiser Bridge - WordPress and Moodle integration..
 * File used to register all the services we are adding externally.
 *
 * @package     local_edwiserbridge
 * @copyright   2021 WisdmLabs (https://wisdmlabs.com/) <support@wisdmlabs.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Wisdmlabs
 */

defined('MOODLE_INTERNAL') || die();

/*$functions = array(
    'eb_test_connection' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_test_connection',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Course completion status of the user with the given user id',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_get_site_data' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_get_site_data',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Get site wise synchronization settings',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_get_course_progress' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_get_course_progress',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Get course wise progress',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_get_users' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_get_users',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Get Users',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_create_service' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_create_service',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Create web service',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_link_service' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_link_service',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Link web service',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_get_service_info' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_get_service_info',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Link web service',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    ),
    'eb_get_edwiser_plugins_info' => array(
            'classname'     => 'local_edwiserbridge_external',
            'methodname'    => 'eb_get_edwiser_plugins_info',
            'classpath'     => 'local/edwiserbridge/externallib.php',
            'description'   => 'Link web service',
            'type'          => 'read',
            'ajax'          => true,
            'capabilities'  => 'local/edwiserbridge:view',
    )
);
*/

$functions = array(
    'eb_create_service' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_create_service',
        'description'   => 'Create web service',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_get_course_progress' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_get_course_progress',
        'description'   => 'Get course wise progress',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_test_connection' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_test_connection',
        'description'   => 'Course completion status of the user with the given user id',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_get_site_data' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_get_site_data',
        'description'   => 'Get site wise synchronization settings',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_get_users' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_get_users',
        'description'   => 'Get Users',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_link_service' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_link_service',
        'description'   => 'Link web service',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_get_service_info' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_get_service_info',
        'description'   => 'Get service information',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'eb_get_edwiser_plugins_info' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'eb_get_edwiser_plugins_info',
        'description'   => 'Get plugins information',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'edwiserbridge_local_get_course_enrollment_method' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'edwiserbridge_local_get_course_enrollment_method',
        'description'   => 'Get course enrollment methods',
        'type'          => 'read',
        'ajax'          => true,
    ),
    'edwiserbridge_local_update_course_enrollment_method' => array(
        'classname'     => 'local_edwiserbridge\external\api',
        'methodname'    => 'edwiserbridge_local_update_course_enrollment_method',
        'description'   => 'Update course enrollment method',
        'type'          => 'read',
        'ajax'          => true,
    )
);
