LLN auto approval plugin based on student completion

Summary: This plugin provides automatically approves a student LLN upon completion of the LLN quiz. The plugin uses the events API to detect when a quiz attempt is submitted on Moodle and triggers if the quiz name is "LLN Quiz" and they have passed the quiz then it will change their profile "LLN assessment" field to "approved" from "pending". The primary function of this plugin is to automate the approval process to allow students to get started with the course faster.

You must be familiar with events API (https://docs.moodle.org/dev/Events_API#Unit_Testing) and Moodle Data manipulation API (https://moodledev.io/docs/apis/core/dml).
