<?php
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');
// defined('MOODLE_INTERNAL') || die();

// class theme_overridden_renderer_factory_custom extends theme_overridden_renderer_factory {

// public function get_renderer(moodle_page $page, $component, $subtype = null, $target = null) {
//     error_log("renderers.php has run #1");

//     $classnames = $this->standard_renderer_classnames($component, $subtype);
    
//     list($target, $suffix) = $this->get_target_suffix($target);
    
//     // Theme lib.php and renderers.php files are loaded automatically
//     // when loading the theme configs.
    
//     // First try the renderers with correct suffix.
//     foreach ($this->prefixes as $prefix) {
//     foreach ($classnames as $classnamedetails) {
//     if ($classnamedetails['validwithprefix']) {
//     if ($classnamedetails['autoloaded']) {
//     $newclassname = $prefix . $classnamedetails['classname'] . $suffix;
//     } else {
//     $newclassname = $prefix . '_' . $classnamedetails['classname'] . $suffix;
//     } error_log(var_export(($newclassname),true));
//     if (class_exists($newclassname)) {
//     return new $newclassname($page, $target);
//     }
//     }
//     }
//     }
// }
// }

// 