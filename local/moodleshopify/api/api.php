<?php

// The only use of this File is to get the entire API URLs to run the Moodle Functions. 
$HOST = 'http://localhost/';
$PLUGIN = 'officialmoodleshopifyplugin/';
$TOKEN= 'dd62b826251eb9fcd3d1637b3b777c21&wsfunction=';
$METHOD_COURSE='core_course_get_courses';
$METHOD_CATEGORY='core_course_get_categories';
$WEBSERVICE='webservice/rest/server.php?wstoken=';
$REST_FORMAT='&moodlewsrestformat=';
$REST_VALUE='json';


$URL = $HOST . $PLUGIN . $WEBSERVICE . $TOKEN . $METHOD_COURSE . $REST_FORMAT . $REST_VALUE;

echo($URL);