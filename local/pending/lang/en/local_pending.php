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
 * Languages configuration for the local_pending plugin.
 *
 * @package   local_pending
 * @copyright 2023, GSES
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['pluginname'] = 'Pending prerequisite approval';
$string['lln_nys_emailsubject'] = 'GSES enrolment processed';
$string['lln_nys_email'] = '<p>Hi {$a->firstname},</p><p>Your prerequisites for your GSES course {$a->coursename} have been approved. Now you just need to complete the <b>LLN quiz</b> on your student portal before you can get full access to our course.</p><p>You can access your LLN quiz through the student portal <a href="{$a->courseurl}">here</a>.</p><br /><p><b>IMPORTANT: Your course is valid for 12 months, therefore your course expiry date is {$a->expirydate}. Please make sure you complete all online assessments before then.</b></p><p><b>Note:</b> If you do not complete the course within the 12 month validity, an admin fee of $137.50 per month will be incurred to process any extensions. Extension fees will apply from the date of expiry and cannot exceed 6 months.</p><br /><p>If you are having trouble accessing the course or have any further questions, please contact us!</p><br /><p>Kind regards,</p><p>GSES Training Team</p>';
$string['lln_s_emailsubject'] = 'GSES enrolment completed';
$string['lln_s_email'] = '<p>Hi {$a->firstname},</p><p>Your prerequisites for your GSES course {$a->coursename} have been approved and your enrolment is now completed.</p><p>You can access your course through the student portal <a href="{$a->courseurl}">here</a>.</p><br /><p><b>IMPORTANT: Your course is valid for 12 months, therefore your course expiry date is {$a->expirydate}. Please make sure you complete all online assessments before then.</b></p><p><b>Note:</b> If you do not complete the course within the 12 month validity, an admin fee of $137.50 per month will be incurred to process any extensions. Extension fees will apply from the date of expiry and cannot exceed 6 months.</p><br /><p>If you are having trouble accessing the course or have any further questions, please contact us!</p><br /><p>Kind regards,</p><p>GSES Training Team</p>';
$string['eb_emailsubject'] = 'GSES exam prerequisites approved';
$string['eb_email'] = '<p>Hi {$a->firstname},</p><p>Your prerequisites for the {$a->coursename} have been approved.</p><p>You can access the exam through the student portal <a href="{$a->courseurl}">here</a>.</p><br /><p><b>IMPORTANT: You only have access to the exam for 30 days, therefore you must complete the exam by {$a->expirydate}.</b></p><p>If you do not complete the {$a->coursename} by this date, you will need to repurchase the exam.</p><br /><p>If you are having trouble accessing the exam or have any further questions, please contact us!</p><br /><p>Kind regards,</p><p>GSES Training Team</p>';
$string['manage'] = 'Manage Pending plugin settings';
$string['storeurl'] = 'WooCommerce store URL';
$string['storeurl_desc'] = 'The URL for the WooCommerce store, beginning with https and ending without a slash';
$string['storekey'] = 'WooCommerce consumer key';
$string['storekey_desc'] = 'The WooCommerce consumer key - begins with ck_';
$string['storesecret'] = 'WooCommerce consumer secret';
$string['storesecret_desc'] = 'The WooCommerce consumer secret - begins with cs_';