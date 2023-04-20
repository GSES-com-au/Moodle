<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\core\event\user_enrolment_updated',
            'callback' => '\local_expiryreminders\task\observer::user_enrolment_updated',
        ),
        array(
            'eventname' => '\core\event\user_enrolment_created',
            'callback' => '\local_expiryreminders\task\observer::user_enrolment_created',
        ),
    );