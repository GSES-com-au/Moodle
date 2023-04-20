<?php
namespace local_moodlealert\task;                                              //Required to be first on the page
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/error.log');

class observer 
{                              
    public static function user_updated(\core\event\user_updated $event)
    {
        global $DB, $PAGE;
        $userid = $event->get_context()->instanceid;

        // Get the old and new user data
        $olduserdata = $event->get_record_snapshot('user', $userid);
        $newuserdata = $DB->get_record('user', array('id' => $userid));
        
        // Check if first name, last name or email was updated
        if ($olduserdata->firstname != $newuserdata->firstname || $olduserdata->lastname != $newuserdata->lastname || $olduserdata->email != $newuserdata->email) {
            // Do something here
            error_log("first, last or email was updated");

        }
        
        // $event_str = var_export($event, true);
        // error_log($event_str);
    }
}

// <?php
// error_reporting(E_ALL);
// ini_set('display_errors', 0);
// ini_set('log_errors', 1);
// #Create settings page on backend to store order number
// #Check if order status changes to trigger function

// //when any order status changes, check whether auto-enrol is required
// add_action('woocommerce_order_status_changed', 'auto_extension_finder', 10, 3);


// function auto_extension_finder($order_id, $old_status, $new_status) {
//     // wp_mail(get_option('email_errors'), 'auto extension triggered ', 'this is an error');
//     ini_set('error_log', __DIR__ . '/error.log');
//     error_log('Function called: auto_extension_finder');
//     error_log('Order ID: ' . $order_id);
//     error_log('Old status: ' . $old_status);
//     error_log('New status: ' . $new_status);
   
//     // if (($old_status == 'pending' || $old_status == 'failed' || $old_status == 'on-hold') AND ($new_status == 'processing' || $new_status == 'completed')){

//     $order = wc_get_order($order_id);
//     $items = $order->get_items(); 
//     $productextensionid=12578;
//     foreach ($items as $item) {      
//         if ($item->get_product_id() == $productextensionid) {
//             $courseid_extension = $item->get_meta('_wccf_pf_courseid_extension');
//             $student_email_extension = $item->get_meta('_wccf_pf_student_email_extension');
//             $quantity = $item->get_quantity();
//             error_log($courseid_extension);
//             error_log($student_email_extension);
//             error_log($quantity);



//             // Calculate the new end date by adding the specified number of months
//             $new_enddate = strtotime("+{$quantity} months", $current_enddate);

//             // Enrol the student with the updated end date
//             $endtimestamp = get_enrolment_expiry_date($courseid, $student_email);
//             $enrolments[0]['roleid'] = 5; //student
//             $enrolments[0]['userid'] = 17;
//             $enrolments[0]['email'] = $student_email_extension;
//             $enrolments[0]['courseid'] = $courseid_extension;
//             $enrolments[0]['timeend'] = $endtimestamp;

//             $request_data = array('enrolments' => $enrolments);
//             $webservice_function = 'enrol_manual_enrol_users';
//             $response = make_moodle_connection($webservice_function, $request_data);
//             if (1 === $response['success']) {
//                 return true;
//             } else {
//                 //send error message - user not enrolled
//                 $error_message = 'function auto_enrol_user: User id '.$userid.' was not enrolled into course id '.$moodlecourseid;
//                 // wp_mail(get_option('email_errors'), 'Auto-Enrol website error', $error_message);
//                 error_log(print_r($error_message, true));
//                 return false;
//             }                    
//             break;
//         }
//     }
// }
// function get_enrolment_expiry_date($course_id, $student_email) {
//     // Call Moodle web service API to get enrolment record for the student
//     $enrolments = make_moodle_connection('core_enrol_get_enrolled_users', array('courseid' => $course_id));
//     $enrolment = null;
//     foreach ($enrolments as $enr) {
//         if ($enr->email == $student_email) {
//             $enrolment = $enr;
//             break;
//         }
//     }
//     if ($enrolment == null) {
//         return null; // No enrolment found for student
//     }
//     $expiry_date = $enrolment->timeend;
//     return $expiry_date;
// }

            
//             // $custom_field_value = get_post_meta($product_id, 'wccf_product_field_courseid_extension	', true);
//             // error_log('Custom Field Value for Product ID ' . $product_id . ': ' . $custom_field_value);

//             // $productextensionid=12578;
//             // foreach ($items as $item) { // Iterate over the items in the order
//             //     $product_id = $item->get_product_id(); // Get the product ID of the item


//                 // if ($product_id == $productextensionid) { // Compare to $productextensionid
//                     // Perform your desired action here

   
//                     // break; // Exit the loop since a match was found


    
//                 // }
//             // }
//         // }
