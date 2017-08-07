=== Registrations for The Events Calendar ===

Contributors: roundupwp
Support Website: https://roundupwp.com/support
Tags: registration, The Events Calendar, RSVP, events, workshops, meetups, meetings, seminars, groups, conferences, registrations, add-on, extension, community, event registration, event contact, events calendar
Requires at least: 3.0
Tested up to: 4.8
Stable tag: 2.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

== Description ==

Manage registrations for your events and classes with ease. The Registrations for The Events Calendar plugin is an extension for Modern Tribe's The Events Calendar (free edition) though it may also work with paid extensions. Collect registration form submissions for your events, classes, meetings, meetups, workshops and more.

= Highly Customizable =

Create a simple, straight-forward registration form with the ability to add as many text form fields as you need. Automatically add the form to every single event page in one of four areas (parts of the template). Change the labels, error messaging and whether or not the field is required. The form is submitted using AJAX so your guests never have to leave the single event page while submitting a registration. Optionally, you can set limits for the number of guests allowed to register per event along with displaying a list of current attendees above the form.

= Quick to Set Up and Easy to Get Started =

As soon as you install and activate the plugin you can begin collecting registrations. No need for shortcodes (although they are available), the registration form is added to every single event page by default. The form asks for a guest's first name, last name, and email address by default though you can change or add to these fields on the "Form" tab.

= Powerful Backend Features for Easy Management =

Registrations are saved in the WordPress database and can be manually added, edited, and deleted in the admin area. See notifications of when new registrations need to be reviewed. View a breakdown of registrations by event and browse them quickly in an overview. Export your registrations into a spreadsheet using a .csv export feature.

= Features =

* Visitors can register for your events
* Registration form automatically appears on all single event pages, options to disable them for any specific event
* Customizable form and messages. Easy to manually translate and tweak to your use case.
* Set limits customized to each event for total registrations
* Form submits without the visitor needing to leave the page or even refresh using AJAX.
* Registrants are sent a customizable confirmation email and the event organizer can be sent a notification email when a registration is made.
* Optional attendee list visible above the form
* Validate emails, allow only one registration per email per event
* Many options can be customized for each event including email recipients, email "from" addresses, deadline, and registration limits
* Shortcodes to display registration forms in other areas of your website
* Manage registrations in the backend with the ability to add, edit, and delete.
* Export registrations to a spreadsheet in .csv form.

= Benefits =
* Simple workflow for setting up registrations for your events frees up time.
* Give your events a personal touch with custom messages in emails
* Build a community by allowing others to see who's attending
* Straight-forward registration process provides a great user experience for your guests.

<strong>Check out [Registrations for the Events Calendar Pro](https://roundupwp.com/products/registrations-for-the-events-calendar-pro/) for premium features like a form builder, enforced email address confirmation, and more options for logged-in users.</strong>

Also, consider donating to the "parent" plugin for this extension [The Events Calendar](https://wordpress.org/plugins/the-events-calendar/). It's a truly awesome plugin that's why we extended it!
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

1. Make sure you have the plugin "The Events Calendar" by Modern Tribe installed and activated before activating "Registrations for The Events Calendar"
2. If you haven't created an event. Create a new event by going to the WordPress dashboard and navigating to Events -> Add New.
3. A registration form will now appear on your created event or any other published event.
3. Add a registration form for a specific event to another page on your site using the shortcode [rtec-registration-form event=743] with the "event" setting being the post ID for that event.
4. You can configure the form fields, messaging, registrations limits etc by navigating to Events -> Registrations and then selecting the "Form" tab.
5. You can configure the email options on the "Email" tab.
6. See a quick overview of your events and registrations on the "Registrations" tab.
7. Add, edit, and remove registrations manually by navigating to the "Registrations" tab and clicking "Detailed View" for an event. You can also export or view submission details here.

== Special Thanks ==

Special thanks to Marco (demontechx) for his valuable input on the plugin!

Special thanks to Henrik (hjald) for fixing a bug in the .csv exporter!

== Screenshots ==


1. View of the registration form revealed on "click"
2. Default position and look of the Register button in an event page
3. The Registrations tab in at-a-glance view
4. Detailed view of a single event's registrations. Buttons to delete, edit, add and export registrations
5. View of the settings on the "Form" tab
6. View of the settings on the "Email" tab
7. Example confirmation email
8. Example notification email
9. Search through registrants
10. Example .csv export file

== Frequently Asked Questions ==

= Can I limit the number of registrations for an event? =

Yes. You can set up the maximum number of registrants on the "Form" tab or set this for each event individually.

= Can I add more fields to the form? =

Yes. There is a button to add custom text input fields on the "Form" tab.

= How do I disable registrations for a specific event? =

By default, registrations are enabled for every event. You can disable registrations for a specific event by checking the appropriate box on the "Edit Event" page or on the "Registrations" tab "Overview" page. You can also disable registrations by default by checking the checkbox on the "Form" tab.

= Can I set a deadline for when registrations are accepted? =

You can configure an offset for how long registrations will be available relative to the event start time or set a specific deadline for each event.

= Can I edit registrations and export them for an event? =

Yes. Click on the button "Detailed View" for the event in which you'd like to edit or export registrations for.

= Can I display a list of event attendees on the front-end? =

Yes. There is an option on the "Form" tab to display a list of attendees above the registration form. A guest's first and last name will only appear after you have had a chance to review it in the backend of the site.

= Can I display the registration form on another page or post? =

Yes. You would need to use the post ID for that event in the shortcode. Example: [rtec-registration-form event=743]

= The form is not hidden initially. Why is that? =

It's likely that you have a javascript error somewhere on that page. Try disabling other plugins or switching themes to see if this corrects the issue.

= What do I do if I have a request or need help? =

Go to the "Support" tab on the plugin's settings page and follow the link to our support page, setup instructions page, or feature request page.
== Changelog ==
= 2.0 =
* New: Much of the codebase has changed. Custom code may no longer work. See documentation for new hooks for developers.
* New: Redesigned "Registrations" tab now offers more filtering options for events, list view of events, and ability to search through registrations
* New: Notification and Confirmation are now HTML emails. You can use the tiny mce editor for your email templates on the "Email" tab.
* New: Several styling/UI improvements for the settings pages. Some options reordered for a more logical flow. Asterisks added by settings that can be set for each event.
* New: Field added to set specific date and times for deadlines for each event. Find this on the "edit event" screen or in the event options drop-down menu on the "Register" tab.
* Tweak: If the form is filled out incorrectly, the registrant will be scrolled to the field with the first error automatically
* Tweak: If "The Events Calendar" is not active, notice appears at the top of the admin page to notify the user that "The Events Calendar" needs to be activated.
* Tweak: Custom field data is now stored differently in the database.
* Tweak: CSS added to override theme styling that may cause problems with form field display.
* Fix: Attendee list will not appear on events that have registrations disabled.

= 1.6.2 =
* Fix: CSV export feature not working in certain circumstances

= 1.6.1 =
* Fix: Default confirmation from address not working in some circumstances.

= 1.6 =
* New: Allow custom "from address" and notification recipients for individual events
* New: Check for duplicate emails before allowing guest to register. This can be enabled on the "Form" tab. This adds a check to see if the input for the email field is a valid email and that it doesn't match an existing email for a registration for the event.
* New: Users with the "edit posts" privilege can now manage registrations in the backend. Only administrators can change options still.
* New: Attendee list can now be viewed above the form. Enable this through the option on the "Form" tab. Only first and last names of registrations that have been viewed in the backend (no longer have the "new" bubble by them) will appear in the list.
* New: Optional header to show event title and start/end times above the form when generated from a shortcode.
* Fix: There was an extra slash in certain file paths when css and javascript files were included on a page.

= 1.5.2 =
* Tweak: Several email defaults were changed like the confirmation subject, confirmation from name, and date format
* Fix: Multiple registrations would be submitted if there was more than one registration form on a page and ajax was disabled.

= 1.5.1 =
* Tweak: If the number of registrations saved in the event meta is inaccurate, this is updated with a count from the database when visiting the registrations tab and viewing that event.
* Fix: Phone validation count causing issue if left blank. Saving an empty setting for this will now allow any submission containing numbers to be accepted.

= 1.5 =
* New: More options for individual events including options for registration limits and deadlines.
* New: Ability to set the registration deadline to the end date or have no deadline for an event (helpful for recurring events)
* New: Logged in user's information will now pre-populate first, last, and email fields if those fields are used.
* New: Support added for shortcodes. To add a registration form to another page/post/widget use the shortcode [rtec-registration-form event=743 hidden=false]. "event" setting is the post id for the event, "hidden" setting represents whether or not to display the form initially or reveal it with a button click.
* Tweak: Max width set for the form along with some other styling to help it display better on wide screens.
* Tweak: Ems used in the CSS for field and message spacing in form.
* Fix: Featured images for events were causing some display issues for the form.

= 1.4 =
* New: More translation support added.
* New: Option added to use translations or custom text.
* New: Count of registrations available on "Registrations" tab.
* Tweak: Upcoming events with registrations is now the default view on the "Registrations" tab with link to see all.
* Tweak: Only the latest 10 registrations are shown in "Overview" with link to view all.
* Tweak: Indices were added to the "rtec_registrations" table.
* Tweak: More CSS styling added to the form.

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