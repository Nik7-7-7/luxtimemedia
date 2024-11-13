# Changelog

## SSA-VERSION-PREFIX.6.7.65 - 2024-11-05

### Fixes

- Ticket: Timeslots allowing for multiple bookings (without Capacity/Resources or other settings enabled)
- Ticket: Resources double booked

### Features and Improvements

- Support booking with Gravity Wiz - GS Product Config

## SSA-VERSION-PREFIX.6.7.61 - 2024-10-23

### Fixes

- Avoid error when inserting the Google Calendar event in a revision
- Fix select component disabled state reactivity
- Fix team member capability affecting blackout dates
- Fix deleting webhooks results in switching to edit mode.

## SSA-VERSION-PREFIX.6.7.57 - 2024-10-03

### Fixes

- Add Euro Support for Alipay
- Cascade delete queued notifications following an appointment getting deleted.

## SSA-VERSION-PREFIX.6.7.55 - 2024-09-24

### Fixes

- WPscan follow-up security fix
- Fix Basic Edition new customer Information fields being undefined
- Fix appointment schedule for blocked business hours

## SSA-VERSION-PREFIX.6.7.53 - 2024-09-17

### Fixes

- Space before and after the Subscribe to Calendar link when added to a Doc/Notepad
- Changing team member settings in Appointment type, bumps the Capacity to 100,000 🐞

## SSA-VERSION-PREFIX.6.7.50 - 2024-09-10

### Fixes

- Fixes around cancellation and rescheduling notes
- Prevent assigning Team Member outside of availability

## SSA-VERSION-PREFIX.6.7.45 - 2024-08-27

### Fixes

- SMS reminder revisions marking successful SMS delivery as a failure
- Prevent JavaScript in the Twig templates

## SSA-VERSION-PREFIX.6.7.43 - 2024-08-13

### Fixes

- Issues with gcal_cleanup action on the client site
- Missing Booking Flows Views
- Fix reported vulnerability - twig executing commands
- Some time-slots incorrectly marked as un-available

### Features and Improvements

- Improve handling for team member's excluded calendars
- Add Alipay as an enabled Stripe payment method
- Add support for Bancontact - Stripe payment method
- Pass customer email to Stripe payment intents API

## SSA-VERSION-PREFIX.6.7.42 - 2024-08-06

### Fixes

- Allow SSA admin to disable Stripe iDeal payments

## SSA-VERSION-PREFIX.6.7.40 - 2024-07-30

### Fixes

- Appt Type settings are wonky when first adding the appt field to Formidable
- Fix textarea caret not visible on Safari

## SSA-VERSION-PREFIX.6.7.38 - 2024-07-23

### Fixes

- Admin App - Fix Edit As Customer Link

## SSA-VERSION-PREFIX.6.7.36 - 2024-07-16

### Fixes

- Holidays not being checked for conflicts in Google Calendar

## SSA-VERSION-PREFIX.6.7.34 - 2024-07-09

### Fixes

- Prevent Stripe race condition inserting duplicate payment records
- Fix Customer Information Summary Twig Not Displaying Correctly
- Formidable Forms fields and form not editable
- Update twig - remove deprecated function twig_date_converter
- Setup Wizard Gets 'Stuck' If Abandoned Before Appt Type is Saved

## SSA-VERSION-PREFIX.6.7.30 - 2024-06-25

### Fixes

- Fix Booking Flow ShortCode
- Fix revisions to be consistent with notifications
- Fix staff meeting url not passed to appointment when user preference
- Display all team members assigned to appointment
- Fix twig template customer information summary
- Hide appointment actions until appointment information is ready
- Google Calendar is blocking timeslots incorrectly
- Replace fallback IP to country API with a working API

### Features and Improvements

- Removed standard timezones.
- Added missing actions to revisions model.

## SSA-VERSION-PREFIX.6.7.28 - 2024-06-18

### Fixes

- Upcoming appointments module previous appointments bug
- Avoid adding multiple payment records with same payment ID
- Fix round robin assignment to staff with zero appointments
- Fix notification template rendering non valid tags
- Fix | Team reassignment not working on rescheduling

### Features and Improvements

- Fixing translation strings
- Updated the author and author URI.
- Added unique ids and classes to the input fields.

## SSA-VERSION-PREFIX.6.7.26 - 2024-06-13

### Fixes

- update visibleLast for only_available view
- Translate customer information field names based on customer locale

### Features and Improvements

- Adding appointment type views formidable integration
- Add padding to formidable forms
- Add preview to SSA Modules
- Fixing booking views colors

## SSA-VERSION-PREFIX.6.7.24 - 2024-06-04

### Fixes

- Avoid calls to external APIs when country code is set
- Fix Duplicate Blocks in Debug Logs
- Support translating dates with Twig `date` filter
- Stripe modal placement fix
- Fixed deprecated twig date format function.
- Non english holiday calendars not being checked for conflicts in Google Calendar

### Features and Improvements

- Updated the button styles for availability time picker option component.
- Fixed availability button styles on Old Admin App.

## SSA-VERSION-PREFIX.6.7.22 - 2024-05-28

### Fixes

- Optimize how checkboxes are spaced when the text wraps
- Fix date format in upcoming appointments module
- Remove stuck error notice around Stripe web-hook setup being incorrect
- Prevent double clicks on buttons where relevant
- Not able to book after "Uh Oh" error - fix handling on new booking app not working

## SSA-VERSION-PREFIX.6.7.20 - 2024-05-21

### Fixes

- Fixed: Adjusted team schedule calculation with buffer periods and 'any' option.
- Validate the access token using the API as a last resort, to confirm that it's not expired
- Ticket: Team Member 'name' passed as empty string to webhook

## SSA-VERSION-PREFIX.6.7.18 - 2024-05-15

### Fixes

- Fix fonts for mobile
- Fixed logic to reference the most recent date for the twigs business_prev_start_date & customer_prev_start_date
- Segment GCAL availability records by calendar ID only - staff and appointment types exclude the events by just the calendar IDs
- Remove pending reminders corresponding to previous start dates of a rescheduled appointment
- Better error handling when SSA fails to insert or update a Google Calendar event
- Admin app - fix timezone conversion appointment start date
- Ticket: start_date parameter omitted from postMessage in handleSaveAppointment()
- New booking app - Improve text color on dark background
- Fix Stored Cross-Site Scripting vulnerability reported by WordFence
- Update start date notifications after rescheduling an appointment.
- GCAL conflict-checking not working as expected
- Quick Connect - Staff excluded calendars wiped out

### Features and Improvements

- Update minimum required PHP version to 7.4
- Added korean translation.

## SSA-VERSION-PREFIX.6.7.16 - 2024-04-23

### Fixes

- Formidable Forms - fix builder settings not saving appointment types selection
- Simplify the Webex agenda contents to only include title of appointment type, and home URL - avoid being incorrectly blocked by Webex spam detection
- TEC issue with the New Booking App - availability end date included as an available start date
- Tighten the conditions around parsing the short-code passed type as an INT
- Defensive fix - avoid marking an appointment as abandoned if it has payments

## SSA-VERSION-PREFIX.6.7.14 - 2024-04-16

### Fixes

- Expose appointment rescheduling history
- Upcoming Appointments message block fix.
- Secure & re-introduce the secret URLs used for SSA support tasks
- Better security around SQL procedures
- Fixed displaying upcoming appointments titles via shortcode.
- Trim leading and trailing whitespace in values text-area input of custom customer info
- Toggle old booking app off when resources feature is enabled

### Features and Improvements

- Added the new booking app banner alerts.
- Adding Booking Flows new Sidecards.
- Expose rescheduling history using twig variables

## SSA-VERSION-PREFIX.6.7.12 - 2024-04-02

### Fixes

- Improve phone number validation
- Next dates button disabled when it should not be
- Fixing new booking app phone field.

### Features and Improvements

- Adding filter appointment types option to Formidable Forms

## SSA-VERSION-PREFIX.6.7.10 - 2024-03-26

### Fixes

- Updating Google Material Icons to the latest version.
- Defensive fix against having duplicate associations between staff and appointment types
- Appointment Type shortcodes getting updated on reordering
- Authorize fetching appointment meta data for logged out users on public edit URL

## SSA-VERSION-PREFIX.6.7.9 - 2024-03-19

### Fixes

- Prevent SQL injection - reported by Wordfence

## SSA-VERSION-PREFIX.6.7.7 - 2024-03-12

### Fixes

- Improve handling Stripe payments

## SSA-VERSION-PREFIX.6.7.5 - 2024-03-05

### Fixes

- Updated the Upcoming Appointment component to display multiple team members.

## SSA-VERSION-PREFIX.6.7.2 - 2024-03-04

### Fixes

- Attach refresh token to access token as early as possible

## SSA-VERSION-PREFIX.6.7.1 - 2024-03-04

### Fixes

- Infinitely spinning wheel - fixes around handling failed Stripe payments
- Fixing formatting in the upcoming appointments component.

### Features and Improvements

- Updating Material Icons to the latest version

## SSA-VERSION-PREFIX.6.7.0 - 2024-03-01

### Fixes

- Fix Resources + First available
- Remove return type: mixed to stay consistent with what PHP 7 supports
- Prevent overriding a valid refresh token with null

### Features and Improvements

- Adding the Elementor Upcoming Appointments Resource Editor Block

## SSA-VERSION-PREFIX.6.6.24 - 2024-02-27

### Fixes

- Replace Google PHP SDK with raw wp_remote_post() / wp_remote_get() API calls
- Preventing identical resource types from showing up in upcoming appointments component.
- Prevent reflected xss attack - sanitize ssa_state parameter

### Features and Improvements

- Adding ids to Admin App Buttons
- Added Romanian translation pack to Simply Schedule Appointments Plugin.
- Added Upcoming appointments widget Elementor editor blocks.

## SSA-VERSION-PREFIX.6.6.22 - 2024-02-20

### Fixes

- Pass the customer locale to the custom confirmation screen
- Adapt SSA to any table prefix set in wp-config
- Persist  team member or resource selections when booking within a form integration
- More validation around appointment type slug

### Features and Improvements

- Adding appointment type layout view for elementor.

## SSA-VERSION-PREFIX.6.6.20 - 2024-02-13

### Fixes

- Fixing Booking form single appointment not showing up bug.
- Avoid error on sending notifications
- Circumvent issue with WP pages endpoint returning html on some sites
- Fix/staff objects not passed to appt types
- Gravity Form Booking Incorrectly Marked as Abandoned
- Fixed dropdown options for mapping formidable form fields to SSA fields

### Features and Improvements

- Added Booking Flows editor block for Elementor.
- Added Appointment types editor block for Elementor.
- Improve performance of options autoloading

## SSA-VERSION-PREFIX.6.6.18 - 2024-02-06

### Fixes

- Prevent warnings in upcoming appointments shortcode
- Fixes around appointment revisions
- Booking with Stripe throwing error with code 400 for some customers
- Fix Display Back Button
- Added preview icons for Booking Flow time views.

## SSA-VERSION-PREFIX.6.6.16 - 2024-01-30

### Fixes

- Fix team members settings in the WP block editor

## SSA-VERSION-PREFIX.6.6.12 - 2024-01-23

### Fixes

- Prevent passing incorrect locale to the internationalization JS API
- Improve automated cleanup to handle a wider variety of server environments

### Features and Improvements

- Added Appointment types editor block for elementor.

## SSA-VERSION-PREFIX.6.6.11 - 2024-01-16

### Fixes

- Fix Form Confirmation Screen With Team Member Selection

### Features and Improvements

- Removed the beta tag from the resorces settings card.
- Added customizable block for upcoming appointments

## SSA-VERSION-PREFIX.6.6.5 - 2023-12-26

### Fixes

- Fix - Infinite getting your Calendar spinner when GCal is not connected
- Bug fix: prevent visually overlapping time slots in new booking app

## SSA-VERSION-PREFIX.6.6.3 - 2023-12-08

### Fixes

- Fix dates boundaries on availability load
- Fix autofill multiple fields on Chrome

### Features and Improvements

- Add Dutch (formal) language support

## SSA-VERSION-PREFIX.6.6.1 - 2023-11-08

### Fixes

- Fix Availability Cache During Booking on Back Button

## SSA-VERSION-PREFIX.6.6.0 - 2023-10-31

### Features and Improvements

- Improve performance of availability schedules
- Improve compatibility with 3rd party plugins that extend wp admin bar

## SSA-VERSION-PREFIX.6.5.28 - 2023-10-10

### Fixes

- Fix Resources with Group Appointment Types Bug

### Features and Improvements

- Allow Stripe to be used in test mode even if live mode settings are empty

## SSA-VERSION-PREFIX.6.5.27 - 2023-09-28

### Fixes

- Fixed: Gravity Form not showing the selected appointment when going back in a multi-page form
- Fix Infinite Loading on Round Robin Selection in Team Members
- Resolved bug causing SSA to skip inserting PayPal payments into ssa_payments table

## SSA-VERSION-PREFIX.6.5.20 - 2023-09-05

### Features and Improvements

- Add filters for advanced customization of notifications

## SSA-VERSION-PREFIX.6.5.19 - 2023-08-27

### Fixes

- Fix error on appointment types without any team members required

## SSA-VERSION-PREFIX.6.5.17 - 2023-08-19

### Fixes

- Avoid fatal error if Google Calendar API quota was exceeded
- Fix errors preventing a customer from booking an appointment with checkboxes or radio buttons

### Features and Improvements

- Add round robin team booking option

## SSA-VERSION-PREFIX.6.5.16 - 2023-08-15

### Fixes

- Fix/new booking app/ provided type not loading
- Bug-Fix: <br> characters displayed as text in SSA's appointment details and reschedule screen
- Bug-Fix: allow translating the placeholder of the phone input
- Fix undefined index notice (stripe_payment)
- Changed id var to str

### Features and Improvements

- Gravity Forms Integration: Allow selecting booking flow in the editor

## SSA-VERSION-PREFIX.6.5.14 - 2023-07-14

### Fixes

- Fix deprecated update_metas() function

## SSA-VERSION-PREFIX.6.5.13 - 2023-07-04

### Fixes

- Fix availability bug on appointments with multiple team members

### Features and Improvements

- Improve WP block editor to allow booking flow customizations
- 
- Associate gravity form bookings with appropriate WP user
- 

## SSA-VERSION-PREFIX.6.5.12 - 2023-06-26

### Fixes

- Fixed: unwanted redirects after booking due to conflict with Jevelin theme

### Features and Improvements

- Add filters for excluding Gravity Forms fields of certain type or certain id
- 
- Add CSS classes to admin app for more styling customization
- 

## SSA-VERSION-PREFIX.6.5.11 - 2023-06-21

### Fixes

- Fixed issue loading booking app when using availability window

### Features and Improvements

- Gravity Forms integration now allows filtering by appointment type label

## SSA-VERSION-PREFIX.6.5.9 - 2023-06-15

### Fixes

- Reintroduced deprecated add_meta() function to avoid fatal errors on sites using it
- 
- Fixed: iframe Appointment field preview disappears in GravityForms editor
- 

### Features and Improvements

- Improved internationalization to add context for label string

## SSA-VERSION-PREFIX.6.4.17

- Added: Ability to dynamically export to ICS subscription feed
- Added: Gravity Forms merge tags for time/date formats
- Fixed: iFrame resizing incorrectly within Gravity Forms
- Fixed: Booking form cut off in some themes

## SSA-VERSION-PREFIX.6.4.16

- Added: Admins can now leave a note when canceling an appointment
- Fixed: Edge case where user could accidentally select a much longer duration than intended

## SSA-VERSION-PREFIX.6.4.15

- Fixed: Database issue affecting appointment types on some servers

## SSA-VERSION-PREFIX.6.4.14

- Improved: Performance of Appointment Types and fixed label_ids on databases with low memory limits
- Improved: Added team members to upcoming appointments wp-admin dashboard widget
- Fixed: Formidable forms without AJAX not running SSA script properly

## SSA-VERSION-PREFIX.6.4.11

- Added: Web_meeting_password field to list of notification variables
- Added: Automatically detect/set contrast mode for dark background colors
- Added: Submenu to directly jump to Team management
- Improved: Appointment Booking block UX
- Fixed: Navigating monthly view in some situations
- Fixed: Appointment.payment_status not rendering accurately in notifications
- Fixed: Availability calculations affecting appointment types with capacity 2 and before/after buffers

## SSA-VERSION-PREFIX.6.4.10

- Added: Team submenu to the Appointment menu in wp-admin
- Improved: Handling of Gravity/Formidable forms submitted after a long delay
- Fixed: label_id error affecting some servers
- Fixed: Changing business timezone was not clearing availability cache

## SSA-VERSION-PREFIX.6.4.6

- Fixed: Performance issue on sites with large number of WP users
- Fixed: Stripe payment information not showing up after new Stripe API changes

## SSA-VERSION-PREFIX.6.4.4

- Added: Support for Japanese Yen as a payment method
- Improved: Notifications and Webhooks properly display generated Zoom and Webex meetings
- Improved: Clear appointment information after an appointment type has been updated
- Improved: Warn about conflict with All-In-One Security conflicts
- Improved: Mailchimp integration
- Improved: Text color accessibility
- Improved: Export/Import handling of edge cases
- Fixed: Instructions field label missing in appointment type editor
- Fixed: Embedding with label shortcode breaking Stripe payment return URL
- Fixed: Stripe payment errors in some regions

## SSA-VERSION-PREFIX.6.4.3

- Improved: Filter appointments by label
- Improved: Critical errors are now highlighted in the admin app
- Improved: Formidable Forms conditional logic involving SSA fields
- Fixed: Alignment of pending status in admin app
- Fixed: Handling of id 0 database records on MS SQL Server
- Fixed: Unable to load appointment details in admin app on some appointments booked through Gravity Forms
- Fixed: Stripe compatibility with 2022-11-15 API changes (causing successful payments, but abandoned appointments)
- Fixed: Error with Formidable Forms field values mapping to SSA incorrectly
- Fixed: Conflict with ssa_locale and TranslatePress

## SSA-VERSION-PREFIX.6.4.2

- Improved: Payment details now show on the booking confirmation screen
- Fixed: Error affecting a few users loading appointment types in the admin

## SSA-VERSION-PREFIX.6.4.1

- Improved: Downloading translations also fetch missing strings for easier manual translation
- Fixed: Rescheduling not working on some sites with custom front page

## SSA-VERSION-PREFIX.6.4.0

- Added: Custom labels for managing appointment types
- Added: Admin can now specify a custom page to be used for rescheduling
- Added: Gravity Forms redirects work with new ssa_confirmation shortcode
- Improved: Automatically refresh appointment list in admin app
- Improved: Interface for managing team members
- Improved: Hide team members filter when no team members are found
- Improved: Better handling of Paypal Return to Merchant button
- Fixed: Blue links on iOS Mobile Safari
- Fixed: Some broken "improve this translation" links

## SSA-VERSION-PREFIX.6.3.0

- Added: Webhooks now contain web meeting details
- Added: Calendar Event settings to customize the event that gets added to customer/admin calendars
- Improved: Hid beta features section when none are available
- Improved: Gravity Forms with Stripe Checkout or Paypal handle pending payments better
- Improved: SSA's Gravity Forms field is easier to select when creating your form
- Fixed: Preview mode broken when editing notifications for appointment types with no bookings
- Fixed: Notifications not saving template variables inserted from the dropdown menu

## SSA-VERSION-PREFIX.6.2.2

- Improved: Time-triggered reminders now available in plus
- Improved: Show warning if SSA plugin directory is renamed
- Fixed: 3 misspelled strings of text in admin-app

## SSA-VERSION-PREFIX.6.2.1

- Improved: Calendar styling on mobile Safari browser
- Fixed: Zoom links not generating for appointments with very long customer submissions
- Fixed: Google Calendar sync errors for some Gravity/Formidable Forms customers
- Fixed: Team member associated with appointments they book on behalf of customers

## SSA-VERSION-PREFIX.6.2.0

- Added: Redirect parameter to ssa_booking shortcode
- Improved: SSA Gravity Forms field allows multiple appointment types to be set by admin
- Improved: SSA Gravity Forms field bubbles change event
- Fixed: Google Calendar sync issues on some older PHP environments

## SSA-VERSION-PREFIX.6.1.2

- Improved: Gravity Forms integration supports conditional logic when an appointment time is selected
- Improved: Abandoned appointments can no longer be marked as canceled
- Improved: Translations and support for formal German
- Fixed: Conflict with Thrive Themes and URL for editing appointments

## SSA-VERSION-PREFIX.6.1.1

- Fixed: Dashboard widget showing appointment details to subscriber-level users
- Fixed: Large action scheduler database size
- Fixed: Rescheduled Paypal bookings marked as pending_payment

## SSA-VERSION-PREFIX.6.1.0

- Added: Developer setting to delete SSA data after plugin is deleted
- Added: Ability to search appointments by customer information
- Fixed: Undefined index PHP warning
- Fixed: Sidebar layout/overlap in notification settings screen

## SSA-VERSION-PREFIX.6.0.4

- Added: Formidable Forms support for upload field
- Improved: Canceling appointments will remove associated webex meeting
- Improved: Display of appointments in pending status
- Fixed: Live preview of notification changes in admin app

## SSA-VERSION-PREFIX.6.0.3

- Fixed: Error on databases with limits of 8126 bytes per row
- Fixed: Error on servers that don't support Action Scheduler

## SSA-VERSION-PREFIX.6.0.2

- Fixed: Error preventing new appointment bookings in some environments

## SSA-VERSION-PREFIX.6.0.1

- Added: Shortcode for past appointments
- Improved: WP-Admin dashboard widget matches appointment type label colors and limits to 5 appointments
- Improved: Handling of intermittent Google Calendar connection errors
- Improved: Remove Webex meetings when an appointment is canceled
- Fixed: Stripe bug when using German Formal language
- Fixed: Unable to submit support tickets on some sites

## SSA-VERSION-PREFIX.6.0.0

- Added: WP-Admin dashboard widget for upcoming appointments
- Added: Team member information to webhook payload
- Improved: Updated timezone database
- Improved: Indicate inactive team member status in appointment list
- Improved: Consistency of translatable strings
- Improved: Gravity Forms field now triggers change event when an appointment time is selected
- Improved: Accessibility of notification toggles
- Fixed: Browser not supported error on some (actually) supported browsers
- Fixed: Google Calendar list not displaying properly for some team members
- Fixed: Customer information rendering out of order on appointment confirmation screen

## SSA-VERSION-PREFIX.5.9.0

- Added: Currency code next to prices
- Added: Button to delete unused translations
- Added: Icons to customer information fields
- Improved: Updated Google Material Icons Font
- Improved: Clear availability cache when business timezone is changed
- Improved: Google Calendar now uses the global get_attendees() function for consistency
- Improved: Add whether a customer agreed to SMS reminders in the Appointment Details Page
- Improved: ssa_upcoming_appointments shortcode now displays web meeting URL
- Fixed: Google Holiday calendars didn't exclude any availability
- Fixed: Compatibility with Cloudflare polish and caching plugins that minify javascript
- Fixed: Web Meeting appointment type settings not working in some cases
- Fixed: Phone Field Dropdown shifted content on page

## SSA-VERSION-PREFIX.5.8.3

- Fixed: Elementor global color styles not applying to scheduling widget
- Fixed: Developer jobs when there are no appointments that fit the criteria
- Fixed: Mobile scrolling on some sites
- Fixed: empty duration for Appointment Type error

## SSA-VERSION-PREFIX.5.8.2

- Fixed: Problem with plugin updating on older server environments

## SSA-VERSION-PREFIX.5.8.1

- Improved: Rescheduling interface now shows in the user's language
- Improved: Compatibility with plugins/themes using the Carbon library
- Fixed: Compatibility with The Events Calendar
- Fixed: Database deadlock error on high-traffic sites

## SSA-VERSION-PREFIX.5.8.0

- Added: Appointment.web_meeting_url to notification token list
- Added: Gravity Forms merge tags for ICS and Add to Google Calendar
- Improved: Abandoned appointments can be booked when a payment is received
- Fixed: Country code in phone field
- Fixed: Handling of stripe error to show error message
- Fixed: CSS pseudo-elements not working
- Fixed: Validation issue with SMS notifications and subject field
- Fixed: Cannot modify header information error in ICS file
- Fixed: Possible pending payment status when payments setting is off

## SSA-VERSION-PREFIX.5.7.9

- Fixed: Fatal error affecting PHP 8.1 in certain scenarios
- Improved: Gravity Forms merge tags for ICS and Add to Calendar links
- Fixed: Required payments still enforced after disabling payments module

## SSA-VERSION-PREFIX.5.7.8

- Added: Appointment.payment_status field for email notifications
- Added: Lithuanian translation pack
- Improved: accessibility of telephone validation errors
- Fixed: Validation error when saving profile for some team members
- Fixed: Month names not translated on all booking views

## SSA-VERSION-PREFIX.5.7.7

- Fixed: Incorrect slot already booked error
- Improved: auto-scroll functionality when switching tabs

## SSA-VERSION-PREFIX.5.7.6

- Added: Czech language pack
- Improved: Replaced appointment.start_date variable with appointment.business_start_date and appointment.customer_start_date
- Improved: Security hardening

## SSA-VERSION-PREFIX.5.7.5

- Improved: Language support for Tamil and other RTL languages
- Fixed: Auto-scroll jumpiness on busy pages with elements loading after initial page load

## SSA-VERSION-PREFIX.5.7.4

- Added: Button for team members to Book an Appointment in the admin
- Added: Developer API endpoint for ICS Subscription feed
- Fixed: Error deleting team members
- Fixed: Disable auto-scroll jumpiness in iFrame resizer

## SSA-VERSION-PREFIX.5.6.1

- Added: Icelandic translation
- Fixed: Fatal Error when Appointment ID is not found
- Fixed: Plus sign in pre-populated emails not working
- Fixed: Bulk editing specific start times
- Fixed: PHP namespacing of third party libraries

## SSA-VERSION-PREFIX.5.6.0

- Added: Webex integration
- Improved: SCA support for Stripe
- Improved: Team Members can now deauthorize their Google Calendar account
- Fixed: Rescheduled date/time timezone sometimes incorrect in admin
- Fixed: Zoom authorization redirect error affecting some sites
- Fixed: Gravity Forms phone field not syncing to SSA

## SSA-VERSION-PREFIX.5.5.2

- Added: Twig Template variables for "Add to Calendar" link
- Improved: Google Calendar attendees always include assigned team members

## SSA-VERSION-PREFIX.5.5.1

- Fixed: "setting up" error on some sites

## SSA-VERSION-PREFIX.5.5.0

- Added: web_meeting_url merge tag for Gravity Forms
- Added: ssa_admin shortcode to display SSA management interface on the frontend
- Fixed: Browser auto-fill sometimes breaking phone number validation

## SSA-VERSION-PREFIX.5.4.7

- Fixed: Some symbols getting improperly encoded in custom Google Calendar Events
- Fixed: Deprecated warnings affecting Divi sites running PHP8

## SSA-VERSION-PREFIX.5.4.6

- Improved: Show error details if submitting a support ticket fails for any reason
- Fixed: Advanced Scheduling Settings still apply to Appointment Types when disabled
- Fixed: SMS Notifications not sending properly when Rescheduling appointments

## SSA-VERSION-PREFIX.5.4.5

- Fixed: Error with trim() function on some customer information fields

## SSA-VERSION-PREFIX.5.4.4

- Improved: Better error messaging when customizing Google Calendar events
- Improved: Recommend relevant documentation when submitting a support ticket
- Fixed: Error affecting Google Calendar Sync

## SSA-VERSION-PREFIX.5.4.2

- Fixed: Some bookings not assigned to team members

## SSA-VERSION-PREFIX.5.4.1

- Added: Google Calendar event customization (beta feature)
- Added: Filter to allow lazy loading of booking app
- Added: Filter to allow hidden Gravity Forms fields to be passed to appointment
- Improved: Independent Availability is now found under Advanced Scheduling Options

## SSA-VERSION-PREFIX.5.2.3

- Added: Show relevant help center guides on the sidebar
- Added: Option for large sites to only export upcoming appointments
- Improved: Show better error message when setting a price below Stripe's minimum requirement
- Fixed: Prevent double-bookings caused by unexpected browser behavior

## SSA-VERSION-PREFIX.5.2.2

- Improved: New icons in SSA Settings screen
- Improved: Stripe error handling
- Fixed: Checkboxes showing incorrectly when editing team members assigned to an appointment type
- Fixed: Exception in appointment factory

## SSA-VERSION-PREFIX.5.2.1

- Fixed: Parse error affecting some sites

## SSA-VERSION-PREFIX.5.2.0

- Added: Option to resend out emails for all upcoming appointments
- Improved: Detect and prevent twig syntax errors when editing notifications
- Improved: Error message if your site's REST API is not returning proper JSON
- Improved: Error logging
- Fixed: "Leave without Saving" dialog showing incorrectly
- Fixed: Back button not working when a single date and time is available

## SSA-VERSION-PREFIX.5.1.1

- Improved: dropdown for "Change timezone" in booking app
- Fixed: PHP warning with wp_localize_script on newer PHP versions
- Fixed: Phone number field showing error warning incorrectly
- Fixed: Jumpy scrolling when SSA is embedded in other plugin layouts

## SSA-VERSION-PREFIX.5.1.0

- Added: Team Members can now specify their own web meeting URL
- Improved: Ability to search for languages in translation settings
- Fixed: Team Members not being assigned to some appointments
- Fixed: Incorrect availability calculation with multi-day appointments
- Fixed: Prevent negative appointment durations
- Fixed: Prevent invalid property error

## SSA-VERSION-PREFIX.5.0.2

- Fixed: PHP Warning
- Fixed: Potential fatal error if two copies of SSA are installed

## SSA-VERSION-PREFIX.5.0.0

- Added: Additional date format to better support more countries
- Added: Developer option to manually sync appointments to Google Calendar
- Fixed: Bug affecting Formidable Forms displaying entries containing deleted appointments

## SSA-VERSION-PREFIX.5.0.0

- Added: Gravity Forms merge tags for team members
- Added: Norwegian language pack
- Added: wp-cli command to import SSA code
- Fixed: Incorrect availability for appointment types with multiple-day durations or buffers
- Fixed: Some appointments getting booked with no team member assigned

## SSA-VERSION-PREFIX.4.9.11

- Improved: Change the position of the Name and Email fields on the booking form
- Fixed: Locked timezone not displaying properly on the customer edit screen
- Fixed: Gravity Forms bug with changing selected time showing incorrect dates available
- Fixed: Error with previewing notifications containing new template variables
- Fixed: Error with phone number field when editing an appointment
- Fixed: Support status on hosts where core updates are locked

## SSA-VERSION-PREFIX.4.9.10

- Added: Slovak language pack
- Improved: Display of rescheduled appointments in the admin app

## SSA-VERSION-PREFIX.4.9.9

- Added: Greek language pack
- Improved: Support for recent timezone changes
- Improved: Highlight when a new version is available

## SSA-VERSION-PREFIX.4.9.8

- Added: Gravity Forms merge tags to show appointment details in Gravity Forms notifications
- Added: Estonian language pack
- Fixed: Gravity Forms and Formidable Forms integration on forms with multiple pages
- Fixed: "Add to Calendar" link not working on some sites with mixed HTTP/HTTPS path

## SSA-VERSION-PREFIX.4.9.6

- Added: Customer's rescheduling/cancel link to the admin appointment details
- Added: Developer option to purge old appointments from the database
- Improved: Ability to access previously-deleted appointment types
- Fixed: Notice Required couldn't be set to zero minutes in some circumstances
- Fixed: Conflict with WPSSO Core plugin
- Fixed: Bug affecting multi-page forms (Gravity/Formidable integration)

## SSA-VERSION-PREFIX.4.9.5

- Fixed: Potential error in notifications caused by missing customer timezone data

## SSA-VERSION-PREFIX.4.9.4

- Fixed: Unexpected ) error for sites running PHP7.2

## SSA-VERSION-PREFIX.4.9.2

- Fixed: Bug affecting customer information fields for some users upgrading from free to plus
- Fixed: Bug preventing Minimum Booking Notice being set to 0

## SSA-VERSION-PREFIX.4.9.1

- Improved: Send more SSA appointment details for customers using Gravity Forms Webhook add-on
- Fixed: Bug causing some users to see bookings with no customer information fields
- Fixed: Bug with Spanish language pack not formatting date correctly
- Fixed: Group appointments incorrrectly generating different Zoom meeting IDs for same time slot
- Fixed: Formidable Forms Name fields getting formatted incorrectly in SSA appointments
- Fixed: Formidable Forms entry not pointing to SSA appointment

## SSA-VERSION-PREFIX.4.8.7

- Improved: Performance with reduced CPU usage
- Fixed: Conflicts with other plugins using action scheduler

## SSA-VERSION-PREFIX.4.8.6

- Fixed: Unexpected availability for some customers

## SSA-VERSION-PREFIX.4.8.5

- Improved: Compatibility with plugins using action scheduler

## SSA-VERSION-PREFIX.4.8.4

- Improved: Show "rescheduled" status in CSV export
- Improved: Automatically clean out old ICS files
- Fixed: Bug affecting min/max booking notice on some sites

## SSA-VERSION-PREFIX.4.8.3

- Fixed: Fatal error on sites running outdated PHP versions (PHP5.6 and PHP7.0)
- Fixed: Fatal error affecting some sites when calculating availability

## SSA-VERSION-PREFIX.4.8.2

- Improved: Remove unnecessary library to reduce filesize

## SSA-VERSION-PREFIX.4.8.1

- Added: New Gravity Forms merge tag for public_edit_url so you can include SSA Cancel/Reschedule links inside of Gravity Forms notifications
- Improved: Performance of admin-app
- Fixed: Missing icons in Elementor widgets
- Fixed: Leave without saving option for Team Members
- Fixed: Back arrow displays when specific shortcode is used

## SSA-VERSION-PREFIX.4.7.3

- Improved: Importing SSA export code now performs an emergency backup to help recover accidental data deletion
- Improved: Performance when lots of SSA schedulers are embedded on the same page
- Improved: Date formatting in Gravity Forms and Formidable Forms now uses SSA date formatting preferences
- Fixed: Original content of Appointment wiped out after reschedule/cancel
- Fixed: SMS Notification cut off if the less than character is used
- Fixed: Export code wasn't clearing appointments when only appointment types were exported

## SSA-VERSION-PREFIX.4.7.2

- Improved: Link from original appointments to rescheduled appointments
- Improved: Layout of dialog buttons on mobile devices

## SSA-VERSION-PREFIX.4.7.1

- Added: Show critical timezone errors in SSA Support tab
- Added: {{ refund_policy }} variable for notification templates
- Improved: Live preview when editing notification templates
- Improved: Appointments that are 150 minutes show up as 2.5hrs to customers when booking
- Improved: Logic for assigning appointments to Team Members
- Improved: Remove problematic/invalid timezones like US/Pacific-New
- Improved: More clear instructions around exiting a page without saving changes
- Improved: SSA no longer saves Gravity Forms fields marked as admin-only or hidden
- Improved: Add Cancel/Reschedule link to shared calendar events
- Fixed: Unable to click into Appointment Details Page on some sites

## SSA-VERSION-PREFIX.4.7.0

- Added: Cancel/Reschedule link to default calendar event description

## SSA-VERSION-PREFIX.4.6.10

- Added: New notification variables for formatted dates {{ Appointment.customer_start_date }} and {{ Appointment.business_start_date }}
- Improved: Prevent search engine indexing on SSA booking app
- Improved: Increase time that SSA holds an appointment waiting for Paypal confirmation (it can take a long time on new accounts or for * certain types of payments)
- Fixed: Warnings in developer console
- Fixed: ssa_full_access user capability

## SSA-VERSION-PREFIX.4.6.9

- Improved: Compatibility with aggressive caching environments
- Improved: The Events Calendar integration

## SSA-VERSION-PREFIX.4.6.8

- Improved: Prevent sending "reminder" notifications that would go out before the appointment was booked (eg. 1-week reminders booked for an * appointment 1 day away)
- Fixed: Logged in user name/email not automatically filling on some sites

## SSA-VERSION-PREFIX.4.6.7

- Improved: Integration with Gravity Forms User Registration add-on
- Fixed: Syncing password data from Gravity Forms to SSA
- Fixed: Syncing empty data from Gravity Forms to SSA
- Fixed: Appointment edit URL affecting some SMS messages

## SSA-VERSION-PREFIX.4.6.6

- Improved: Translations for customer information fields
- Fixed: Default value for Google Calendar refresh interval

## SSA-VERSION-PREFIX.4.6.5

- Added: Appointment.customer_start_date notification variable
- Added: Appointment.business_start_date notification variable
- Added: link filter to notification templating to allow text links with custom label
- Fixed: Unwanted data added to customer information fields in some cases

## SSA-VERSION-PREFIX.4.6.4

- Improved: Google Calendar events with Google Meet meetings only send a single invitation notification
- Fixed: Prevent plugin conflicts with Appointment Edit URL

## SSA-VERSION-PREFIX.4.6.3

- Added: Filter to customize how long appointments are reserved for Gravity Forms integration
- Fixed: Special characters showing in SMS message
- Fixed: Month names not getting translated in admin app
- Fixed: Blackout dates for team members
- Fixed: Bug affecting ?type= in the URL query string

## SSA-VERSION-PREFIX.4.6.2

- Improved: Support for Gravity Forms with multiple pages
- Fixed: Zoom connection expiring and having to be reauthenticated
- Fixed: nbsp; characters showing up in some notifications
- Fixed: bug with phone number field

## SSA-VERSION-PREFIX.4.6.0

- Added: Zoom integration to automatically create Zoom Meetings

## SSA-VERSION-PREFIX.4.5.3

- Improved: Performance when exporting a large number of appointments
- Improved: Compatibility with twentytwentyone theme
- Fixed: &nbsp showing in SMS notifications for some users
- Fixed: incorrect availability for Group appointment types using max-per-day limit

## SSA-VERSION-PREFIX.4.5.2

- Improved: Adjust settings to avoid iframe setting a max-height on iframes
- Improved: Setup wizard prompts you to test booking your first appointment
- Improved: Phone number validation
- Fixed: Improve handling of custom web meeting URLs
- Fixed: Conflict with ProjectHuddle plugin

## SSA-VERSION-PREFIX.4.5.1

- Fixed: Error adding team members in some cases
- Fixed: Improve resizing/scrolling of booking app

## SSA-VERSION-PREFIX.4.5.0

- Improved: Better error logging when connecting to Google Calendar
- Fixed: Prevent potential error with team member capacity
- Fixed: Calendar/description for shared/individual calendar events

## SSA-VERSION-PREFIX.4.4.9

- Improved: Add filter to show end date in Gravity Form entries
- Improved: Made `at` translatable
- Fixed: Caching conflict with some Advance scheduling settings
- Fixed: Scrolling to the booking app when using the 'type' attribute

## SSA-VERSION-PREFIX.4.4.8

- Improved: Additional checks to prevent double bookings on slower servers getting high traffic volume
- Improved: Translation of dates in Gravity/Formidable Forms integrations
- Improved: SMS support for Formidable Forms integration

## SSA-VERSION-PREFIX.4.4.7

- Improved: Error notice when appointment fails to get inserted into the database
- Fixed: Errors with Gravity Forms Zapier add-on with certain forms
- Fixed: Unusual issue with sites located in half-hour timezone offset

## SSA-VERSION-PREFIX.4.4.6

- Fixed: Google Calendar conflict with plugins using an incompatible version of the Guzzle library

## SSA-VERSION-PREFIX.4.4.5

- Fixed: Team availability calculation

## SSA-VERSION-PREFIX.4.4.4

- Added: Integration with The Events Calendar
- Improved: Accessibility and screen reader support
- Improved: Availability Troubleshooting
- Fixed: Appointment Edited webhook

## SSA-VERSION-PREFIX.4.4.3

- Added: Set the default country code (which determines what flag will show up in the phone number field)
- Added: Japanese language pack
- Fixed: Export code not working with some notification templates
- Fixed: Errors with availability and deleted appointments

## SSA-VERSION-PREFIX.4.4.2

- Improved: Translation of upcoming appointments module

## SSA-VERSION-PREFIX.4.4.1

- Improved: Integration with Gravity Forms + Zapier
- Fixed: Ability to edit appointments as the customer
- Fixed: Emails sent for invalid appointments

## SSA-VERSION-PREFIX.4.4.0

- Added: Easily troubleshoot availability to identify common issues with your appointment type settings
- Improved: Performance for all sites
- Improved: Performance for Google Calendar sync

## SSA-VERSION-PREFIX.4.3.7

- Improved: Added function to delete abandoned appointments
- Improved: Support for German (formal)
- Improved: Performance on high traffic sites
- Improved: Performance of asynchronous tasks
- Fixed: Issue handling buffer times with availability caching
- Fixed: Google calendar connections for team members

## SSA-VERSION-PREFIX.4.3.6

- Fixed: Bug with calculating maximum appointments per day

## SSA-VERSION-PREFIX.4.3.5

- Improved: Performance of availability caching
- Added: new "types" argument for shortcode to specify embedding multiple specific appointment types
- Fixed: Mailchimp settings not saving when editing an appointment type
- Fixed: Timezone issue with CSV export

## SSA-VERSION-PREFIX.4.3.4

- Improved: Performance of availability caching
- Improved: Handling site visitors with incorrect UTC-offset timezones set in their browsers
- Fixed: Bug affecting Google Calendar UI for team members
- Fixed: Bug affecting availability on appointment types that only had a "Buffer Before" set

## SSA-VERSION-PREFIX.4.3.3

- Improved: Google Calendar events send email invitations to attendees
- Improved: Provide more SSA data to Gravity Forms Webhooks Add-On
- Improved: Performance for form integrations
- Fixed: Cache not clearing for other appointment types with shared availability
- Fixed: Remove hardcoded text from calendar event description
- Fixed: Phone number validation when editing an appointment

## SSA-VERSION-PREFIX.4.3.2

- Fixed: Issue with calculating maximum appointments per day

## SSA-VERSION-PREFIX.4.3.1

- Added: Web Meetings support for a custom URL
- Improved: Internationalization of additional strings
- Improved: Performance of monthly booking views
- Improved: Availability caching
- Fixed: Bug affecting editing customer information fields
- Fixed: Bug affecting Mobile Safari
- Fixed: Bug affecting team members availablity

## SSA-VERSION-PREFIX.4.2.8

- Improved: Google Calendar performance
- Fixed: Buffers interacting with Google Calendar events

## SSA-VERSION-PREFIX.4.2.7

- Added: Custom web meeting URLs
- Improved: Performance improvements

## SSA-VERSION-PREFIX.4.2.5

- Improved: New scheduling algorithm and caching to make SSA run faster

## SSA-VERSION-PREFIX.4.2.3

- Fixed: Zoom integration coming soon

## SSA-VERSION-PREFIX.4.2.2

- Improved: Team Member user role
- Improved: Add gravatars to team member list
- Fixed: Google Calendar authorization for Team Members
- Fixed: Google Meet web meeting creation

## SSA-VERSION-PREFIX.4.2.1

- Fixed: Connection to Stripe failed on some server configurations

## SSA-VERSION-PREFIX.4.2.0

- Added: Team scheduling
- Added: Web Meetings with Google Meet
- Added: Shared Google Calendar Events - invite customers/team as attendees

## SSA-VERSION-PREFIX.4.1.3

- Fixed: CSV Export for customer fields containing commas

## SSA-VERSION-PREFIX.4.1.2

- Added: Slovenian translation support
- Improved: Formidable Forms CSV export

## SSA-VERSION-PREFIX.4.1.1

- Added: Export option for anonymous customer information

## SSA-VERSION-PREFIX.4.0.9

- Improved: Gravity Form integration

## SSA-VERSION-PREFIX.4.0.8

- Added: Admin can now edit an appointment on behalf of the customer
- Improved: Email formatting for customers booking in non-english locales
- Improved: Availability windows now support specific times
- Improved: Gravity Forms export now prints appointment start date/time
- Fixed: Error affecting some customers using the maximum appoinmtents per day limit

## SSA-VERSION-PREFIX.4.0.7

- Improved: Compatibility with Safari browser

## SSA-VERSION-PREFIX.4.0.6

- Improved: Performance of appointment booking interface
- Improved: Handling of ICS files
- Improved: Permissions on appointment types
- Fixed: Bug with importing appointments
- Fixed: PHP warnings

## SSA-VERSION-PREFIX.4.0.5

- Added: Option to book an appointment right from the admin interface
- Improved: Skip straight to booking form for appointment types with a single start time per day
- Improved: Display end date on booking confirmation if it's different than the end time
- Improved: Compatibility with CB Change Mail Sender plugin

## SSA-VERSION-PREFIX.4.0.3

- Improved: Prevent lost connection errors

## SSA-VERSION-PREFIX.4.0.0

- Added: Import / Export functionality to easily migrate between installs
- Added: Filtering of notifications in admin app

## SSA-VERSION-PREFIX.3.9.10

- Improved: Filtering by date range

## SSA-VERSION-PREFIX.3.9.9

- Improved: database schema compatible with more WordPress installs
- Fixed: Bug when filtering appointments by date range

## SSA-VERSION-PREFIX.3.9.8

- Fixed: Divi module issue when a single appointment type is selected
- Fixed: Display of appointment created/modified dates
- Fixed: Load more in admin-app on sites with lots of appointments booked in a single day
- Fixed: PHP notice on some servers

## SSA-VERSION-PREFIX.3.9.7

- Improved: Gravity Forms integration now syncs Phone field to SSA
- Fixed: Developer option for separate availability not always taking effect
- Fixed: Untitled events from Google Calendar failing to sync
- Fixed: Gravity/Formidable Forms with upload fields prevent exporting of SSA appointments

## SSA-VERSION-PREFIX.3.9.6

- Fixed: Divi compatibility on WordPress 5.5

## SSA-VERSION-PREFIX.3.9.5

- Improved: Handling of group events
- Fixed: Removed warnings on WordPress 5.5

## SSA-VERSION-PREFIX.3.9.4

- Added: Filtering by abandoned status in list of appointments
- Improved: Performance of notifications
- Fixed: Compatibility issue with WordPress 5.5

## SSA-VERSION-PREFIX.3.9.3

- Improved: Compatibility with WordPress.com
- Improved: Customer form fields now allow links

## SSA-VERSION-PREFIX.3.9.2

- Improved: Compatibility with caching plugins
- Fixed: 404 messages showing where scheduler should be on some themes
- Fixed: Special characters are handled better when saving booking page title
- Fixed: Conflict with Simple Calendar plugin

## SSA-VERSION-PREFIX.3.9.1

- Added: Tracking which page an appointment was booked on
- Added: Debugger to support tools
- Improved: SSA Custom capabilities and permissions
- Fixed: Bug affecting rescheduled appointments potentially preventing further bookings
- Fixed: Bug affecting some ICS files
- Fixed: Bug affecting some Google Calendar users with many calendars

## SSA-VERSION-PREFIX.3.9.0

- Added: Classes and Group Events
- Improved: Automatically prevent accidental whitespace in appointment type slugs

## SSA-VERSION-PREFIX.3.8.6

- Fixed: Issue switching from availability blocks to start times

## SSA-VERSION-PREFIX.3.8.5

- Added: Date range filter for list of appointments
- Improved: Compatibility with installations with WP core files in a different directory
- Improved: Compatibility with some shared hosts

## SSA-VERSION-PREFIX.3.8.4

- Added: CSS class in the booking form for the appointment type being booked
- Fixed: Stripe payments bug affecting appointment types using capacity
- Fixed: Bug preventing CSV export of appointments when customer information contained special characters
- Fixed: Bug affecting some sites where times in the past might show up as available

## SSA-VERSION-PREFIX.3.8.3

- Added: Developer setting to enqueue SSA scripts on all pages (needed for some sites loading the booking form with AJAX)
- Added: More styling options to the booking form's gutenberg block
- Fixed: Error affecting the editing experience for notifications on some sites
- Fixed: Conflict with WP Rocket lazy loading

## SSA-VERSION-PREFIX.3.8.2

- Fixed: SSA Divi module only worked with Divi Builder plugin and not the Divi theme

## SSA-VERSION-PREFIX.3.8.1

- Improved: Elementor integration now has more styling options
- Fixed: Error affecting the admin appointment filtering on some sites
- Fixed: Errors on sites where REST API is blocked

## SSA-VERSION-PREFIX.3.8.0

- Added: Allow multiple simultaneous bookings of the same appointment type
- Added: Ability to export appointments to CSV
- Added: Ability to filter appointment views by status and type
- Added: Language packs for Hungarian, Turkish, Russian, and Estonian
- Added: Developer settings screen for beta/developer settings
- Added: Divi modules for embedding booking forms and upcoming appointments
- Improved: Support for embedding booking form in Elementor popup
- Improved: Beaver Builder module has more options and settings
- Improved: Minified unsupported.js
- Improved: New option for embedding booking form (API)
- Improved: Added CSS classes for more flexibility in styling booking form
- Fixed: Stripe SDK updated
- Fixed: Load local copies of Google fonts and icons in booking form
- Fixed: Back button bug in booking form integration
- Fixed: Accessible labels for phone number fields in booking forms

## SSA-VERSION-PREFIX.3.7.5

- Improved: Formidable Forms integration: localized date formatting
- Improved: Updated Stripe API integration

## SSA-VERSION-PREFIX.3.7.3

- Fixed: markup for the booking form and confirmation screen

## SSA-VERSION-PREFIX.3.7.2

- Fixed: Non-breaking space entitites inserted into subject line of notifications
- Improved: Consistent markup for the booking form and confirmation screen

## SSA-VERSION-PREFIX.3.7.1

- Added: Support for defining set start times for booking appointments
- Improved: Send customer name to MailChimp
- Improved: Provide filter for MailChimp field mapping

## SSA-VERSION-PREFIX.3.6.10

- Improved: Better accessibility for edit buttons on settings screen
- Improved: Updated version of Material Icon font
- Fixed: Display icons for radio and checkbox fields on booking form
- Fixed: Made more strings translatable
- Fixed: Browser autofill interfering with phone number validation when booking appointments

## SSA-VERSION-PREFIX.3.6.9

- Fixed: Outlook bug caused by X-WR-CALNAME tag in ICS files
- Fixed: Google Font dependency causing slow load times on some sites

## SSA-VERSION-PREFIX.3.6.8

- Added: Swedish (Svenska) translation

## SSA-VERSION-PREFIX.3.6.7

- Added: Danish (Dansk) translation

## SSA-VERSION-PREFIX.3.6.6

- Improved: Set booking app frame to noindex

## SSA-VERSION-PREFIX.3.6.5

- Fixed: Appointment type availability not editable for customers using translated date/time strings
- Fixed: Typo in translated strings

## SSA-VERSION-PREFIX.3.6.4

- Improved: Translations for German (Formal) and Spanish (Venezuela)

## SSA-VERSION-PREFIX.3.6.3

- Improved: Hide timezone warning for locked timezones on appointment types
- Improved: Made two additional strings translatable
- Fixed: Remove conflict with the LanguageTool browser addon when editing notifications

## SSA-VERSION-PREFIX.3.6.2

- Added: Italian translation
- Fixed: Back button functionality in booking form

## SSA-VERSION-PREFIX.3.6.1

- Fixed: Permissions on a couple API endpoints

## SSA-VERSION-PREFIX.3.6.0

- Added: Integration with Members plugin for advanced custom user permissions/capabilities [https://wordpress.org/plugins/members/](https://wordpress.org/plugins/members/)
- Improved: Translate day and month names properly in notifications
- Improved: Performance of admin app

## SSA-VERSION-PREFIX.3.5.3

- Improved: Automatic translation for default date format in email and SMS notifications

## SSA-VERSION-PREFIX.3.5.2

- Improved: Better automatic translation for default date formats in non-English languages

## SSA-VERSION-PREFIX.3.5.1

- Improved: Decreased load time for booking form

## SSA-VERSION-PREFIX.3.5.0

- Added: Additional CSS class for styling

## SSA-VERSION-PREFIX.3.4.0

- Added: Integration with Formidable Forms
- Fixed: Checkbox field type throwing Twig error in Notifications
- Fixed: Custom style not applied for appointment type focus in Firefox
- Fixed: Form labels not translatable for default fields
- Fixed: Conflict with Mesmerize theme
- Improved: Contrast between available and unavailable days in monthly booking view

## SSA-VERSION-PREFIX.3.3.1

- Fixed: Incorrect timezone showing in notifications for some users

## SSA-VERSION-PREFIX.3.3.0

- Added: Easily download and install language packs, even if they aren't complete
- Fixed: Catch fatal Twilio error
- Fixed: Layout for loading settings
- Improved: Submitting support ticket through the plugin

## SSA-VERSION-PREFIX.3.2.3

- Improved: Layout of booking form

## SSA-VERSION-PREFIX.3.2.2

- Improved: Update list of Google fonts in style settings
- Improved: Better UX for admin and booking apps on slow servers
- Fixed: SMS appearing disabled after saving Twilio credentials
- Fixed: Issues with embedding multiple booking forms on the same page

## SSA-VERSION-PREFIX.3.2.1

- Improved: Better handling for description of notifications on appointment types
- Improved: Show a warning if an offset is selected instead of a timezone
- Improved: Hide the reorder button for appointment types if there's only one
- Fixed: Disabled dates in weekly view not inheriting the custom font selection in styles
- Fixed: Improper validation applied to text fields named 'Phone'

## SSA-VERSION-PREFIX.3.1.0

- Added: Event tracking – SSA can post events to your analytics or advertising tools as your customers go through the booking process
- Added: Elementor integration – new SSA widgets so you can easily drag booking forms (or a summary of the logged in user's upcoming appointments) right onto your page
- Added: Logged in users' information is automatically filled in the booking form (except for administrators since you are likely booking an appointment for your customer)
- Added: [ssa_upcoming_appointments] shortcode to display the logged in user's upcoming appointments
- Improved: Deleting appointment types API call works now on servers that restrict use of the DELETE method
- Improved: Assign customer's user id based on email address even when user is logged out
- Improved: Display of timezone in customer email notifications
- Fixed: Google Calendar validation error when credentials are empty

## SSA-VERSION-PREFIX.3.0.2

- Improved: Timezone detection and display
- Improved: Stripe: customize the description that shows on your customers' credit card statement
- Fixed: Extra check for appointment availability before processing Stripe payment
- Fixed: Stripe payment confirmation not redirecting to thank you

## SSA-VERSION-PREFIX.3.0.1

- Fixed: Unable to add an appointment type if the wizard is skipped
- Fixed: Unable to enable Google Calendar in the wizard
- Fixed: Unable to manage and add customer information fields in the wizard
- Fixed: Custom styles apply to date selection buttons when booking an appointment

## SSA-VERSION-PREFIX.3.0.0

- Added: Send custom SMS reminders and notifications
- Added: Preview for notifications - see what your notifications will look like
- Improved: Enable notifications to be disabled - all or individually
- Improved: Easier keyboard focus for selecting a date in the booking form
- Improved: Mailchimp authorization UX
- Fixed: Bug when cloning notifications

## SSA-VERSION-PREFIX.2.9.1

- Improved: More robust availability checking for people with lots of booked appointments
- Fixed: Bug that prevented being able to delete appointment types

## SSA-VERSION-PREFIX.2.9.0

- Added: Custom reminder notifications (send X days before/after appointment is booked or appointment start time)
- Added: Ability to reorder appointment types

## SSA-VERSION-PREFIX.2.8.0

- Added: Paypal payments

## SSA-VERSION-PREFIX.2.7.4

- Improved: Added pagination for appointments for admins
- Improved: Added shortcode instructions to final wizard screen
- Improved: Accessibility fixes for the booking form
- Improved: Swapped out user Gravatars for Unavatars
- Improved: Subtle transitions to booking form
- Fixed: Mobile view of availability for admins not fully visible

## SSA-VERSION-PREFIX.2.7.3

- Improved: Buffers won't availability of booking at business start time
- Improved: Upgrade to latest version of Select2
- Improved: Validating booking notice and advance so that booking notice can't be greater than advance
- Improved: Validate URLs for web hooks
- Improved: Styles updated for radio buttons and checkboxes on booking form
- Improved: Show customer and author of appointments if applicable (if they have a WP user account)
- Improved: Added ability to delete appointments from the appointment detail view with warning message
- Fixed: Add to Calendar button on booking form using SSA business name instead of site name

## SSA-VERSION-PREFIX.2.7.1

- Fixed: Bug introduced by WooCommerce v3.6

## SSA-VERSION-PREFIX.2.7.0

- Added: UI for customizing both customer and admin notification emails, plus ability to send different notification messages per appointment type
- Added: Ability to add custom instructions to custom customer information fields
- Fixed: When using advanced scheduling options, an availability window less than 24 hours resulted in no appointment times being available to book

## SSA-VERSION-PREFIX.2.6.12

- Fixed: Issue showing homepage instead of booking form on some sites

## SSA-VERSION-PREFIX.2.6.11

- Improved: Google Calendar authorization
- Improved: WPML compatibility with ?lang= permalink structure
- Fixed: 404 error (or showing homepage) instead of booking app when using certain themes/plugins
- Fixed: Google Calendar bug when excluding a deleted calendar from availability
- Fixed: Bug with availability in booking form
- Fixed: Potential conflict with 2 booking forms embedded in the same page

## SSA-VERSION-PREFIX.2.6.8

- Improved: Handling of errors preventing appointment booking
- Fixed: Conflict with plugins that incorrectly modify admin body class

## SSA-VERSION-PREFIX.2.6.7

- Improved: Webhook payloads now include date "parts" for more advanced use cases

## SSA-VERSION-PREFIX.2.6.6

- Improved: Booking form resizing
- Improved: Show helpful message if JavaScript is disabled or browser doesn't have the capability to run SSA
- Improved: Rescheduling of appointments
- Improved: When rescheduling, link to (now) canceled old appointment
- Improved: When rescheduling appointment, link payment made on original appointment for tracking

## SSA-VERSION-PREFIX.2.6.5

- Improved: Remove restricted width of admin scrollbar
- Fixed: Conflict with other MailChimp plugins
- Fixed: Unable to go back to the first week when booking an appointment
- Fixed: Cancel link in confirmation email not working
- Fixed: Able to remove required email and name customer information fields

## SSA-VERSION-PREFIX.2.6.4

- Improved: Better messaging when there aren't any appointment types
- Improved: Set timezone to local even if wizard is skipped
- Improved: Better feedback about saving in bulk edit mode for appointment types
- Improved: Clearer choices for Google Calendar syncing
- Improved: Better handling of email validation when booking appointment
- Fixed: Mailchimp opt-in text required when editing appointment type
- Fixed: Google Calendar not displaying connection in wizard

## SSA-VERSION-PREFIX.2.6.3

- Fixed: Issue with monthly booking display

## SSA-VERSION-PREFIX.2.6.2

- Added: Developer filter for advanced customization needs
- Fixed: Issue with monthly booking display

## SSA-VERSION-PREFIX.2.6.0

- Added: Accept payments with Stripe when appointments are booked
- Added: New timing interval so appointments can be booked every 20 minutes
- Added: New monthly view option for booking appointments
- Improved: Bulk editing
- Improved: Better messaging when deleting an appointment type
- Improved: Make more reasonable PHP recommendations on the support tab
- Improved: Require customer email address to be properly formatted as an email address
- Improved: Better explanation of appointment time taken when two customers try to book the same appointment time at the same time
- Fixed: Possibility of negative buffer times and booking notices. It turns out that time travel is dangerous.
- Fixed: Number inputs in Firefox now display at correct width
- Fixed: Handle clash between availability, blackout dates, and booking notices more gracefully
- Fixed: MailChimp opt-in box now reliably appears for customers booking appointments

## SSA-VERSION-PREFIX.2.5.0

- Added: Gutenberg block for Appointment Booking Form

## SSA-VERSION-PREFIX.2.4.0

- Added: Show “instructions” field to customer on the appointment confirmation screen
- Added: Easy button to copy shortcode to clipboard on single-appointment edit screen
- Improved: Proactively prevent double-booking by notifying customer right after they select a time that's no longer available
- Improved: Faster cancelation process
- Improved: Bulk editing mode instructions

## SSA-VERSION-PREFIX.2.3.0

- Added: View individual appointment details in the admin interface
- Improved: Show warnings on modules that are enabled but not actively configured
- Fixed: Bug affecting blackout dates in some timezones
- Fixed: Email notifications containing escaped formatting when customers filled in fields with special characters

## SSA-VERSION-PREFIX.2.2.0

- Added: In-plugin support tools to help with troubleshooting server issues and sending debug information to the SSA team
- Improved: Performance of Availability Window
- Improved: Handle unusual timezone settings with some servers/plugins
- Improved: Prevention of double-booking on sites
- Improved: Google Calendar support for all-day events
- Updated: Google Calendar setup documentation
- Fixed: Sometimes days without any availability showed up as clickable in the week view
- Fixed: Google Calendar authentication during the setup process

## SSA-VERSION-PREFIX.2.1.8

- Improved: Google Calendar error checking

## SSA-VERSION-PREFIX.2.1.7

- Improved: Added error handling for PHP 5.3.x (SSA requires 5.5.9+, 7.x recommended)

## SSA-VERSION-PREFIX.2.1.6

- Improved: Error messages
- Improved: Prevention of double-booking on sites with heavy traffic

## SSA-VERSION-PREFIX.2.1.5

- Improved: Availability start date

## SSA-VERSION-PREFIX.2.1.4

- Fixed: Bug with availability (only affecting dates more than 7 weeks away)

## SSA-VERSION-PREFIX.2.1.3

- Improved: Added Custom CSS field to the "Styles" settings for the booking app
- Improved: Newly created appointment types now show up without having to refresh the page
- Fixed: Bug with availability windows for far-off future events

## SSA-VERSION-PREFIX.2.1.2

- Fixed: Incompatibility with older PHP versions (introduced in 1.2.1.1)

## SSA-VERSION-PREFIX.2.1.1

- Added: Ability to define the timezone as locked or localized (on your appointment types) which makes it easy to differentiate between phone calls/virtual meetings vs. physical/in-person events
- Added: Support for custom CSS files for admin-app and booking-app (which can be overridden in your theme)
- Improved: Spacing in booking form
- Improved: Interationalized email notifications

## SSA-VERSION-PREFIX.2.0.3

- Improved: Compatibility with servers that have aggressive caching
- Fixed: Broken "Back to WordPress" button on sites installed in a subdirectory
- Fixed: Another incompatibility with some themes/hosts that have custom handling for 404 pages

## SSA-VERSION-PREFIX.2.0.2

- Fixed: Incompatibility with some themes/hosts that have custom handling for 404 pages

## SSA-VERSION-PREFIX.2.0.1

- Added: Webhooks module to enable better integration & automation with other platforms
- Improved: Added Appt Type Instructions field to email notifications

## SSA-VERSION-PREFIX.1.9.5

- Added: WP Filter so developers can modify the email notification template
- Improved: Added Appt Type Instructions field to email notifications

## SSA-VERSION-PREFIX.1.9.4

- Improved: Layout of appointment type selection in the booking interface
- Improved: Setup Wizard process
- Improved: More readable appointment type slugs when cloning an existing type
- Improved: Moved Availability Window settings into the Availability section
- Improved: Availability & Booking windows default to midnight instead of current time
- Fixed: Bug enforcing booking window restrictions, even when Advanced Scheduling Options is disabled

## SSA-VERSION-PREFIX.1.9.3

- Fixed: Bug preventing some Elementor installations from showing the booking form properly

## SSA-VERSION-PREFIX.1.9.2

- Fixed: Bug affecting Blackout Dates functionality

## SSA-VERSION-PREFIX.1.9.1

- Added: Filter to allow separate availability for each appointment type (to allow custom implementations via code)

## SSA-VERSION-PREFIX.1.9.0

- Added: Advanced scheduling options for appointment types - set a booking window, set an availability window, and limit how far into the future customers can book appointments
- Improved: Layout of appointment type selection in the booking form
- Improved: Appointment types get better slugs
- Improved: Booking form will now fast-forward to the first available appointment slot if it's after the current week
- Fixed: If there is only one appointment type, skip appointment type selection in the booking form

## SSA-VERSION-PREFIX.1.8.3

- Fixed: rest_no_route error on some environments

## SSA-VERSION-PREFIX.1.8.2

- Improved: Handling appointments connected to now-deleted appointment types
- Improved: Display of timezone in admin view
- Improved: Display of appointment types list when there are no appointment types defined

## SSA-VERSION-PREFIX.1.8.1

- Improved: Appointment Cancelation/Reschedule interface
- Improved: Cancelation synchronization with Google Calendar
- Fixed: PHP Notices (non-fatal errors)

## SSA-VERSION-PREFIX.1.7.1

- Fixed: Bug affecting "checkbox" customer fields

## SSA-VERSION-PREFIX.1.7.0

- Added: Easily customize the styling and appearance of your booking forms to match your site (in the new Style Settings)

## SSA-VERSION-PREFIX.1.6.0

- Improved: Better handling of auto-zoom behavior on iPhone
- Improved: Default padding & spacing around booking form
- Fixed: Changes to the date/time format didn't always save
- Fixed: Google Calendar caching times as unavailable even when module is disabled

## SSA-VERSION-PREFIX.1.5.2

- Added: submenus for quicker access in wp-admin sidebar
- Improved: Compatibility with plugins that replace wp_mail() function
- Fixed: Email headers for best handling of from name and reply-to, along with support for WP Mail SMTP for custom From addresses

## SSA-VERSION-PREFIX.1.5.0

- Improved: Added support for custom CSS in booking app
- Improved: Use from contact name, business name, and contact email as From address for email notification to customer
- Improved: Line endings / spacing in email notifications
- Fixed: Canceled events were not always made available for booking again
- Fixed: Potential issue with height of sections when editing appointment types
- Fixed: Partially cut off bulk editing menu

## SSA-VERSION-PREFIX.1.4.1

- Improved: Layout of booking app for mobile devices
- Fixed: Warnings in javascript console

## SSA-VERSION-PREFIX.1.4.0

- Added: Two modes of bulk editing for appointment types - quickly edit multiple appointment types
- Improved: Appointment type editing - easier to navigate all available options
- Fixed: Manage License button text now translateable
- Improved: Linked MailChimp API instructions to make it easier to find your API Key
- Fixed: Allow 0 value for pre- and post- appointment buffers and for booking notice

## SSA-VERSION-PREFIX.1.3.1

- Fixed http/https error on some WordPress server setups

## SSA-VERSION-PREFIX.1.3.0

- Added "Instructions" field to appointment types so you can tell your customers information about the appointment (like where to meet, if you'll contact them or they should contact you at the appointment time, etc.) This field also shows up in the notes for the event they add to their calendar.

## SSA-VERSION-PREFIX.1.2.3

- Fixed: Mailchimp issue with some existing appointment types

## SSA-VERSION-PREFIX.1.2.1

- Added: MailChimp integration
- Added: Bulk editing
- Improved: Appointment Type Editing

## SSA-VERSION-PREFIX.1.1.0

- Improved custom field options for collecting information from customers
- Fixed issue with availability when user had blacked out every day in the next 30 days

## SSA-VERSION-PREFIX.1.0.1

- Improved handling when trying to book an appointment when there is no availability in next 30 days
- Fixed issue where the availability time increment wasn't reflected on the frontend booking form. (ie. show appointments available every 15/30/60 minutes)

## SSA-VERSION-PREFIX.1.0.0

- Added: Customize the information you collect from customers

## SSA-VERSION-PREFIX.0.9.9

- Fixed issue with logged-in non-admin users unable to book some appointment types
- Fixed issue with Google Calendar syncing for newly-booked events

## SSA-VERSION-PREFIX.0.9.7

- Fixed blackout dates applying even when feature is disabled
- Fixed issue with setup wizard

## SSA-VERSION-PREFIX.0.9.6

- Fixed issue preventing some appointments from showing up in admin view

## SSA-VERSION-PREFIX.0.9.4

- Fixed issue when WP is in a subdirectory instead of at the root of the domain

## SSA-VERSION-PREFIX.0.9.3

- Fixed API conflict with other plugins

## SSA-VERSION-PREFIX.0.9.1

- Added Google Calendar integration, so you never double-book again. Automatically exclude events from your Google Calendar so nobody can book during that time
- Overhaul to Admin UI and Booking UI
- New wizard for easy setup

## SSA-VERSION-PREFIX.0.8.0

- Added Blackout Dates feature, so you can block off vacation days that you don't want to book any appointments
