<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\core\event\user_updated',
            'callback' => '\local_moodlealert\task\observer::user_updated',
        ),
    );