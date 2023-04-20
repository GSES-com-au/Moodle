<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\core\event\enrol_instance_created',
            'callback' => '\local_test\task\observer::enrol_instance_created',
        ),
    );