=== Registrations for The Events Calendar ===

Contributors: roundupwp
Support Website: https://roundupwp.com/products/registrations-for-the-events-calendar/support/
Tags: The Events Calendar, registration, event registration, event contact, events calendar, events, RSVP, seminar, conference, workshop, meetup, meetings, calendar, add-on, extension, class, tournament
Requires at least: 3.0
Tested up to: 4.7
Stable tag: 1.3.3
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

The Registrations for The Events Calendar plugin is an extension for Modern Tribe's The Events Calendar (free edition).

Create a custom contact form that is revealed on a click on the single event post in a location of your choosing.

The form is submitted without leaving the page using AJAX.

Notification emails are sent to email addresses of your choosing as well as the form submitter. Details of the submission are saved in a custom table in the WordPress database.

Just about everything in the form is customizable including the text, required fields, registration limits, and the email messages.

Add as many text input fields as needed for your form.

Export individual event registrations with the .csv export button.

== Feedback or Support ==

We would love to hear feedback and support the plugin so please visit the "Support" tab inside the plugin settings page for assistance.

== Installation ==

Follow these steps:

1. Make sure you have the plugin "The Events Calendar" by Modern Tribe installed and activated.
2. From the dashboard of your site, navigate to Plugins -> Add New.
3. Select the Upload option and click "Choose File."
4. A popup message will appear. Upload the plugin files from your desktop.
5. Follow any instructions that appear.
6. Activate the plugin from the Plugins page and navigate to Events -> Registrations to get started setting up options.

== Setting up Registrations for the Events Calendar ==

1. Make sure you have the plugin "The Events Calendar" by Modern Tribe installed and activated.
2. If you haven't created an event. Create a new event by going to the WordPress dashboard and navigating to Events -> Add New.
3. A registration form will now appear on you created event or any other published event.
4. You can configure the form fields, messaging, registrations limits etc by navigating to Events -> Registrations and then selecting the "Form" tab.
5. You can configure the email options on the "Email" tab.
6. See a quick overview of your events and registrations on the "Registrations" tab.
7. Add, edit, and remove registrations manually by navigating to the "Registrations" tab and clicking "Detailed View" for an event. You can also export or view submission details here.

== Special Thanks ==

Special thanks to Marco (demontechx) for his valuable input on the plugin!
Special thanks to Henrik (hjald) for fixing a bug in the .csv exporter!

== Screenshots ==

1. Default position and look of the "Register" button in an event page
2. View of the registration form revealed on "click"
3. The "Registrations" tab in at-a-glance view
4. Detailed view of a single event's registrations. Buttons to edit, delete, and add registrations
5. View of the settings on the "Form" tab
6. View of the settings on the "Email" tab
7. Example "confirmation" email
8. Example "notification" email

== Frequently Asked Questions ==

= Can I limit the number of registrations for an event? =

Yes. You can set up the maximum number of registrants on the "Form" tab.

= Can I add more fields to the form? =

Yes. There is a button to add custom text input fields on the "Form" tab.

= How do I disable registrations for a specific event? =

By default, registrations are enabled for every event. You can disable registrations for a specific event by checking the appropriate box on the "Edit Event" page or on the "Registrations" tab "Overview" page. You can also disable registrations by default by checking the checkbox on the "Form" tab.

= Can I set a deadline for when registrations are accepted? =

You can configure an offset for how long registrations will be available relative to the event start time.

= Can I edit registrations and export them for an event? =

Yes. Click on the button "Detailed View" for the event in which you'd like to edit or export registrations for.

= The form is not hidden initially. Why is that? =

It's likely that you have a javascript error somewhere on that page. Try disabling other plugins or switching themes to see if this corrects the issue.

= What do I do if I have a request or need help? =

Go to the "Support" tab on the plugin's settings page and follow the link to our support page, setup instructions page, or feature request page.
== Changelog ==
= 1.3.3 =
* Tweak: Additional troubleshooting information added to "System Info".
* Fix: Fixed "+ Add Field" button not working for some users.

= 1.3.2 =
* Fix: Updated columns in the "rtec_registrations" table to allow larger values.

= 1.3.1 =
* New: Add a setting on the "Form" tab to disable registrations for new and existing events by default.
* Fix: Fixed encoding issue for .csv exporter that was not encoding certain characters correctly.
* Fix: Improved sanitization of some custom field entries.

= 1.3 =
* New: Add more custom text input fields to the form using the "+ Add Field" button on the "Form" tab.
* New: Ability to export a single event's registrations to a .csv file now available in the "Detailed View" of each event.
* New: "custom" column added to the "rtec_registrations" table in the database.
* New: Index added on "event_id" to the "rtec_registrations" table in the database.
* New: Background color of form and buttons in the form are now customizable on the "Form" tab.
* New: Subjects for the notification email are now customizable on the "Email" tab.
* Tweak: The "Overview" will show the first three fields that are used instead of always showing last, first, and email fields.
* Tweak: "Other" field now supports up to 1000 characters when storing in the database.
* Tweak: The bottom row of labels in the "Detailed View" are conditionally displayed when there are 15 registrations or more.
* Tweak: You can now use the dynamic text fields in the "Confirmation From" field i.e. "{event-title}"
* Fix: Text domain changed from "rtec" to "registrations-for-the-events-calendar" (more internationalization/translation improvements to come).
* Fix: Fixed issue where event start time was not being retrieved correctly and causing a problem with the registration deadline

= 1.2.3 =
* Tweak: Allowed up to 100 characters in "Other" field instead of only 20
* Fix: Phone validatation counts were not working correctly in certain circumstances
* Fix: Fixed name spacing issue that was hiding some of the tools in the "Registrations" tab in certain circumstances

= 1.2.2 =
* Fix: A second validation of the "First" and "Last" fields would cause the form to not submit even though no errors were shown to the form submitter. The second check was fixed.

= 1.2.1 =
* Fix: "Last" and "First" labels on "Registrations" tab were reversed.

= 1.2 =
* New: Pagination for Registrations tab, "Overview" page. Now you can view events 20 at a time with option to paginate through using navigation buttons at the bottom of the page.
* New: Labels for First, Last, Email, and Phone input fields are now translatable on the "Form" tab and are applied wherever relevant.
* New: Custom date formatting added for emails messaging on the "Email" tab.
* New: Custom notification messages now supported on the "Email" tab. Click the checkbox to reveal the message area.
* Tweak: Upcoming events now displayed first on Registrations tab, "Overview" page.
* Fix: Fixed PHP warning when creating a new event.

= 1.1.1 =
* Fix: Fixed "Message if no registrations yet" setting to reflect changes to the setting in the admin area.

= 1.1 =
* New: Added support for a phone number input field. This can be added to the form and data can be used everywhere else user data is normally available.
* New: Added the ability to customize how phone numbers are validated. Enter accepted number of digits for your needs on the "Form" tab "Phone" input options.
* New: Added the ability to disable registrations for specific events. This can be done either on the "Edit Event" page or on the "Registrations" tab "Overview" page.
* New: Added the ability to set a deadline for registrations. This can be configured on the "Form" tab.
* New: Several more fields including the ical download url and venue address information can be added to confirmation email.
* New: Added support for a recaptcha spam detection field. Simple math question that robots can not answer correctly.
* Tweak: Move form location setting to the "Styling" area on the same tab.
* Fix: Fixed display issue when viewing the "Registrations" tab on small devices.
* Fix: Fixed issue where venue title would not update when the venue was changed for an event.

= 1.0 =
* Release