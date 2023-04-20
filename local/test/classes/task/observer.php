<?php
namespace local_test\task;                                            //Required to be first on the page

class observer 
{
    public static function enrol_instance_created(\core\event\enrol_instance_created $event)
    {
        var_dump($event);
        print_r("hello world");
        error_log();

    }
}