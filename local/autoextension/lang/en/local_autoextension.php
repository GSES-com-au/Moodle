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
 * Languages configuration for the local_autoextension plugin.
 *
 * @package   local_autoextension
 * @copyright 2023, GSES
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Auto Extension after DT feedback';
$string['nys_extensionemailsubject'] = 'Course access extended';
$string['nys_extensionemail'] = '<p>Hi {$a->firstname},</p><p>A tutor has marked your submission for the {$a->coursename} course and identified some corrections you need to make. Because your course access is either about to expire or has already expired, we have added a 2 week grace period for you to make these corrections.</p><p>Your new course expiry date is <b>{$a->expirydate}</b>.</p><p>Ensure you submit any required corrections by this date to avoid extension fees. Please contact us at tutor@gses.com.au if you have any questions or concerns.</p>';