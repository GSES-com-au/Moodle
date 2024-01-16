Allows students to purchase the course extension from Moodle (https://www.gsestraining.com/local/courseextension/index.php), communicates with wordpress via url webhook

Summary: This plugin provides creates a page for students to purchase course extensions within the Moodle platform itself. The data on the page is populated through various SQL queries and updated in realtime based on javascript. The primary function of this plugin is to ensure the students can seemless purchase an extension for their expired course.

How does it work?
The course extension page is intially populated with the logged in students accreditation courses that are still valid i.e. start date is less than 18 months from current date. The extension quantity and new course expiry date are adjusted based on their current expiration date and any how many additional months they can purchase before they exceed 18 months from initial date of enrollment.


You must be familiar with Moodles mustache templates, javascript and Moodle Data manipulation API (https://moodledev.io/docs/apis/core/dml).
