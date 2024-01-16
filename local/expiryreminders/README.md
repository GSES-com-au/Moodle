This plugin uses the courseid provided by the 'auto-enrol' plugin on wordpress.

Summary: This plugin grabs the course id of the user enrolment being updated or created and sends the expiration details to active campaign. The primary function of this plugin is to ensure the expiration dates are updated in Activecampaign if any changes are made in Moodle i.e. extensions, manual change of their expiration date. Active campaign runs expiryreminders automations based on the expiration date this plugin provide to AC. You must be familiar with Moodles events API to fully understand the functions being called in this plugin https://docs.moodle.org/dev/Events_API#Unit_Testing

Error logs should provide detailed enough diagnostics for any issues with this plugin.
