<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

require_once($CFG->dirroot . '/question/type/multianswer/renderer.php');

// [24-Jul-2023 15:47:42 Australia/Sydney] 'theme_learnr\\output\\qtype_multianswer_renderer'
// [24-Jul-2023 15:47:42 Australia/Sydney] 'theme_learnr_qtype_multianswer_renderer'

class theme_learnr_qtype_multianswer_subq_renderer_base extends qtype_multianswer_subq_renderer_base {
    protected function feedback_popup(question_graded_automatically $subq,
            $fraction, $feedbacktext, $rightanswer, question_display_options $options) {
                error_log("function has run!");

    }
}
