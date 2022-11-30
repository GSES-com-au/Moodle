<?php


defined('MOODLE_INTERNAL') || die();

    $observers = array(
        array(
            'eventname' => '\core\event\group_member_removed',
            'callback' => '\local_pending\task\observer::group_member_removed',
        ),
    );