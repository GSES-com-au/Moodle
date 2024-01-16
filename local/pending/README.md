This plugin provides an triggers when a 'pending' group tag is removed from a student in a course.

Plugin utilises a woocommerce API connection to update the customers order status.

Summary: This plugin triggers automatically when a course 'pending' group tag is removed from a user. New student enrollments in a course are automatically put in the 'pending' group of a course. This 'pending' group means they won't be able to access the rest of the course until we verify their pre-requisites on our end. Once we have approved their pre-requisites we remove the 'pending' tag manually which triggers the plugin. The plugin uses the events API to detect this group change. Upon trigger the plugin will change the order status in woocommerce from "checking" to "processing" which means the textbook associated with the course is ready to be sent out. The plugin will also update the enrollment start date to the same day the 'pending' tag was removed and enrollment end date to 1 year + 1 day from the start date at midnight.

The primary function of this plugin is to limit course access until we have validated their prerequisities. This plugin also updates their enrollment start and enddate to ensure students have a more realistic access period on their course i.e. students may purchase a course but only 1 month later send us their prerequisites which previously may have resulted in 1 month lost out of their 12 month access.

You must be familiar with events API (https://docs.moodle.org/dev/Events_API#Unit_Testing), Moodle Data manipulation API (https://moodledev.io/docs/apis/core/dml) and woocommerce REST API (https://woocommerce.github.io/woocommerce-rest-api-docs/).
