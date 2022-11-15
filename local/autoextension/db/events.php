<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\mod_assign\event\submission_graded',
            'callback' => '\local_autoextension\task\observer::submission_graded',
        ),
    );