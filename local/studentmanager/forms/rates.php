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
 * Rates form
 *
 * @package    local_staffmanager
 * @copyright  2020 Ricoshae Pty Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("$CFG->libdir/formslib.php");

class rates_form extends moodleform
{
    //Add elements to form
    public function definition()
    {
        global $CFG;

        $mform = $this->_form; // Don't forget the underscore!
        $mform->addElement('html', '<strong>Rates form</strong><br><br>');

        $mform->addElement('text', 'enrolmentrate', 'Enrolment rate', ' size="100%" '); // Add elements to your form
        $mform->setType('enrolmentrate', PARAM_NUMBER);                   //Set type of element
        $mform->setDefault('enrolmentrate', 0);        //Default value

        $mform->addElement('text', 'flatcost', 'Quarterly flat fee', ' size="100%" '); // Add elements to your form
        $mform->setType('flatcost', PARAM_NUMBER);                   //Set type of element
        $mform->setDefault('flatcost', 0);        //Default value

        $buttonarray=array();
        $buttonarray[] = $mform->createElement('submit', 'Submit', 'Save');
        $buttonarray[] = $mform->createElement('cancel');
        $mform->addgroup($buttonarray, 'buttonar', '', ' ', false);

    }
    //Custom validation should be added here
    public function validation($data, $files)
    {
        return array();
    }
}
