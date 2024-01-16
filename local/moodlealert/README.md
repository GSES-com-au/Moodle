This plugin provides an alert from Moodle when students update their username, firstname, lastname or email address

Plugin utilises custom code inserted into core moodle public_html/user/editadvanced_form.php lines 170 - 228 (Refer to GSES Edits)

Summary: This plugin provides automatically emails tutor@gses.com.au when a student updates the above mentioned details in their profile. The plugin uses the events API to detect when a user profile is changed. The primary function of this plugin is to ensure our RTO data and AC records are kept updated incase of a student email update. This plugin is also meant to detect multiple students using one account by changing name and email address.

Once the user clicks "update profile" in Moodle the 'moodlealert' plugin will run:
- Creates a snapshot of user data before the database is updated (This is the job of the custom core Moodle edit in editadvanced_form.php)
- Snapshot is saved in 'mdl_user_snapshot' table
- This plugin will check if the user exists in the 'mdl_user_snapshot' table
- If user exists:
  - The plugin will get the combined fields from both 'mdl_user' and 'mdl_user_snapshot' and compares each of the relevant fields for differences in field values
  - Any differences will trigger an email to 'tutor@gses.com.au'
- It should be noted that 'mdl_user_snapshot' table was created manually as it not a default moodle table

You must be familiar with events API (https://docs.moodle.org/dev/Events_API#Unit_Testing) and Moodle Data manipulation API (https://moodledev.io/docs/apis/core/dml).
