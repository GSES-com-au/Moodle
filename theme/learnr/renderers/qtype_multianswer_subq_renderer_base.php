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
 * Multianswer question renderer classes.
 * Handle shortanswer, numerical and various multichoice subquestions
 *
 * @package    qtype
 * @subpackage multianswer
 * @copyright  2010 Pierre Pichet
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

abstract class qtype_renderer extends plugin_renderer_base {
    
}

require_once($CFG->dirroot . '/question/type/shortanswer/renderer.php');
require_once($CFG->dirroot . '/question/type/multianswer/renderer.php');


abstract class theme_learnr_qtype_multianswer_subq_renderer_base extends qtype_multianswer_subq_renderer_base {

    abstract public function subquestion(question_attempt $qa,
            question_display_options $options, $index,
            question_graded_automatically $subq);

    /**
     * Render the feedback pop-up contents.
     *
     * @param question_graded_automatically $subq the subquestion.
     * @param float $fraction the mark the student got. null if this subq was not answered.
     * @param string $feedbacktext the feedback text, already processed with format_text etc.
     * @param string $rightanswer the right answer, already processed with format_text etc.
     * @param question_display_options $options the display options.
     * @return string the HTML for the feedback popup.
     */
    protected function feedback_popup(question_graded_automatically $subq,
            $fraction, $feedbacktext, $rightanswer, question_display_options $options) {

        $feedback = array();
        if ($options->correctness) {
            if (is_null($fraction)) {
                $state = question_state::$gaveup;
            } else {
                $state = question_state::graded_state_for_fraction($fraction);
            }
            $feedback[] = $state->default_string(true);
        }

        if ($options->feedback && $feedbacktext) {
            $feedback[] = $feedbacktext;
        }

        if ($options->rightanswer) {
            $feedback[] = get_string('correctansweris', 'qtype_shortanswer', $rightanswer);
        }

        $subfraction = '';
        if ($options->marks >= question_display_options::MARK_AND_MAX && $subq->maxmark > 0
                && (!is_null($fraction) || $feedback)) {
            $a = new stdClass();
            $a->mark = format_float($fraction * $subq->maxmark, $options->markdp);
            $a->max = format_float($subq->maxmark, $options->markdp);
            $feedback[] = get_string('markoutofmax', 'question', $a);
        }

        if (!$feedback) {
            return '';
        }

        return html_writer::tag('span', implode('<br />', $feedback), array('class' => 'feedbackspan accesshide'));
        // $outputfeedback = html_writer::tag('span', implode('<br />', $feedback), array('class' => 'feedbackspan accesshide'));
        // if ($feedbacktext) {
        // $outputfeedback .= html_writer::tag('div', $feedbacktext, array('class' => 'outcome'));
        // }
        // return $outputfeedback;
    }
}
