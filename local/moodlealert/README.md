Alert from Moodle when students update their username, firstname, lastname or email address

Plugin utilises custom code inserted into core moodle public_html/user/editadvanced_form.php lines 170 - 227

editadvanced_form.php

- Creates a snapshot of user data before the database is updated
- Snapshot is saved in 'mdl_user_snapshot' table

Once the user clicks "update profile" in Moodle the 'moodlealert' plugin will run

- This plugin will check if the user exists in the 'mdl_user_snapshot' table
- If user exists:
  - The plugin will get the combined fields from both 'mdl_user' and 'mdl_user_snapshot' and compares each of the relevant fields for differences in field values
  - Any differences will trigger an email to 'tutor@gses.com.au'
- It should be noted that 'mdl_user_snapshot' table was created manually as it not a default moodle table
