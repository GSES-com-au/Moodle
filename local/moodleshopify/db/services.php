<?php

// We define the services to install as pre-build services. A pre-build service is not editable by administrator.
$services = array(
    'Shopify API Service' => array(
        'functions' => array(
            'core_user_create_users',
            'core_user_get_users_by_field',
            'core_user_update_users',
            'core_course_get_courses',
            'core_course_get_categories',
            'core_enrol_get_users_courses',
            'enrol_manual_enrol_users'
        ),
        'restrictedusers' => 0,
        'enabled' => 1,
    ),
);
