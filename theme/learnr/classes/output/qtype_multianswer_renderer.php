<?php
// namespace theme_learnr\output\qtype\multianswer;

// error_reporting(E_ALL);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// ini_set('error_log', __DIR__ . '/error.log');


// require_once($CFG->dirroot . '/question/type/multianswer/renderer.php');

// // [24-Jul-2023 15:47:42 Australia/Sydney] 'theme_learnr\\output\\qtype_multianswer_renderer'
// // [24-Jul-2023 15:47:42 Australia/Sydney] 'theme_learnr_qtype_multianswer_renderer'

// class qtype_multianswer_subq_renderer_base extends \qtype\multianswer\qtype_multianswer_subq_renderer_base {
//     protected function feedback_popup(question_graded_automatically $subq,
//             $fraction, $feedbacktext, $rightanswer, question_display_options $options) {
//                 error_log("cow");

//         $feedback = array();
//         if ($options->correctness) {
//             // if (is_null($fraction)) {
//             //     $state = question_state::$gaveup;
//             // } else {
//             //     $state = question_state::graded_state_for_fraction($fraction);
//             // }
//             // $feedback[] = $state->default_string(true);
//         }

//         if ($options->feedback && $feedbacktext) {
//             $feedback[] = $feedbacktext;
//         }

//         if ($options->rightanswer) {
//             $feedback[] = get_string('correctansweris', 'qtype_shortanswer', $rightanswer);
//         }

//         $subfraction = '';
//         if ($options->marks >= question_display_options::MARK_AND_MAX && $subq->maxmark > 0
//                 && (!is_null($fraction) || $feedback)) {
//             $a = new stdClass();
//             $a->mark = format_float($fraction * $subq->maxmark, $options->markdp);
//             $a->max = format_float($subq->maxmark, $options->markdp);
//             $feedback[] = get_string('markoutofmax', 'question', $a);
//         }

//         if (!$feedback) {
//             return '';
//         }

//         return html_writer::tag('span', implode('<br />', $feedback), [
//             'class' => 'feedbackspan',
//         ]);

//     }
// }
