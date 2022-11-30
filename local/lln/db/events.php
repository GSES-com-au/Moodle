<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\mod_quiz\event\attempt_submitted',
            'callback' => '\local_lln\task\observer::attempt_submitted',
        ),
    );