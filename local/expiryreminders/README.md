This plugin uses the courseid provided by the 'auto-enrol' plugin on wordpress.

Summary: This plugin grabs the course id of the user enrolment being updated or created and sends the expiration details to active campaign.

Reasons for errors:

- Courseid doesn't match the active campaign course id fields, this could be due to auto-enrol failing or there being not enough fields for the courseid to placed. This means that the user must be enrolled in at least 3 courses all within 18 months of their respective start dates.
