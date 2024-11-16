=== FluentBooking Pro - Appointment Solution ===
Contributors: techjewel, wpmanageninja
Author URI: https://fluentbooking.com/
Tags: booking, appointment booking, appointments, booking system, scheduling, event booking system
Requires at least: 6.0
Tested up to: 6.6
Requires PHP: 7.3
Stable tag: 1.5.25
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

The Ultimate WordPress Scheduling Solution for Businesses

== Description ==
Welcome to the age of FluentBooking inside WordPress. Harness the power of unlimited appointments, bookings, webinars, events, sales calls, etc., and save time with scheduling automation!

Go beyond regular booking and scheduling with features tailored for you. FluentBooking handles all your scheduling needs, from sharing availability to meeting guests and more!

== Installation ==
Install the fluent-booking-pro.zip file to your WordPress plugin upload and then just activate and use it.

== Changelog ==

= 1.5.25 (Date: 22 Oct 2024)
- Added Italian and German Language
- Added Missing Translations
- Added Cancellation and Rescheduling Reason in Webhook
- Resolved Response Not Valid Error
- Fixed Event and Calendar ID Not Showing in Webhook
- Fixed Reply To Email Not Pulling Issue
- Fixed Manual Booking Issue with Number Field
- Fixed Selected Events Not Displaying in Elementor Settings
- Fixed Calendar Block Not Scrolling to Event
- Fixed Reserve Slot Still Showing Available Issue
- Fixed Host Not Appearing in Google Calendar Attendee List

= 1.5.22 (Date: 03 Oct 2024) =
- Elementor Integration Available for Free Version
- Added File Input Field in Booking Question
- Added Hidden Input Field in Booking Question
- Updated Checkbox Field Value to yes/no
- Updated Heading h1, h2 to h3, h4 for Fluentform
- Fixed About Field Always Showing in Manual Booking
- Fixed Empty Title Rendering in Booking Lists
- Fixed Slot Overlap Issue When using Custom Duration
- Fixed Integration Not Checking Event Trigger Issue
- Fixed Expired Calendar Showing Issue in Elementor

= 1.5.21 (Date: 04 Sep 2024) =
- Multi-Month Support In Multiple Bookings
- Added Event Time for One-off Group Event
- Added Mark as Paid Option for Paid Bookings Created By Admin
- Fixed Multi Booking Not Working When Display Spot is Disabled
- Fixed Double Event Creating Issue in Google Calendar
- Fixed Group Meeting Reschedule Issue With Zoom

= 1.5.20 (Date: 23 Aug 2024) =
- New: Multiple Bookings at Once
- New: Submit Button Text Changing Option
- Added Duration in Zoom Meeting
- Added Payment Info In Calendar Event Lists
- Added Location and Description in Calendar Block
- Fixed Nextcloud Calendar Sync Issue
- Fixed Group Booking Cancellation Issue with Google Calendar

= 1.5.11 (Date: 31 Jul 2024) =
- Added Location Info in Host View
- Added Description in One-off Group Event
- Improved Conference Location Selection
- Updated Payment Methods UI
- Fixed Holiday Not Syncing Issue
- Fixed Booked Group Slot Not Disappearing on Unavailable Dates

1.5.02 (Date: 24 July 2024)
- Fixed Conflict Checking Issue with Round Robin

1.5.01 (Date: 18 July 2024)
- Fixed: FluentForms Integration
- Improved: FluentBoards Integration Feed

1.5.0 (Date: 16 July 2024)
- New: Implemented Single Event Feature (One-to-One/Group)
- New: Front-end Panel
- Added: Clone Option of Email, SMS, Webhook and Integration Settings
- Added: Fluent Board Due Date based Meeting Date Option
- Added: Filter by Calendar Types
- Added: Calendar Query Parameter - Time
- Added: Duration Labels According to the Duration Hook
- Fixed Round Robin Day Light Saving Issue
- Fixed Issue with Elementor Integration
- Improved Round Robin Scheduling
- Lots of UI-UX Improvements

1.4.3 (Date: 16 Jun 2024)
- Assign Other Team Members in Team Events
- Fixed Conflict With WP Fusion

1.4.2 (Date: 14 Jun 2024)
- New: FluentBoards Integration
- New: Elementor Integration
- New: Fluent Booking Calendar Shortcode
- New: Reschedule and Cancel Event Triggers in Webhook Feeds
- Assign Team Member Option When Creating Team Meeting
- Added Calendar Event Search Option
- Added Full RTL Support
- Applied Redirection Url in Woo Booking
- Applied Jump to the Next Available Slot Within Date Ranges
- Fixed Round Robin Issue with Unavailable Date
- Fixed Question Fields Not Moving Issue
- Fixed Require Confirmation  Not Working With Fluentform

1.4.1 (Date: 28 May 2024)
- Implemented feature: Can not cancel/reschedule before meeting start
- Added New Field: Booking Title with smartcodes option
- Added Permission Denied Message input field
- Added Cancellation and Reschedule input fields
- Added missing textarea field in manual booking
- Fixed pending not showing in admin bookings
- Fixed approval email sending issue with woo
- Fixed summary report day not showing issue
- Fixed team block missing description issue
- Improved logged-in user bookings responsiveness
- Added default duration of multi-duration event in url params

1.4.0 (Date: 20 May 2024)
- New Gutenberg Block: Logged-in user bookings
- New shortcode: Add booking to calendar
- Implemented booking confirmation require feature
- Google Calendar additional settings
- Clone meetings from other host
- Added formatted date shortcode for mapping with FluentCRM birthday field
- Added ICS file attachment option
- Added Invitee email edit option for admin
- Added option to use custom phone field for sending SMS
- Added help message field in custom fields
- Added format field for date
- Updated group event title for remote group events
- Resolved Nextcloud Calendar sync issue
- Fixed an issue with event details for long descriptions
- Fixed email sent from Fluent SMTP instead of the default From address of FluentBooking
- Fixed the meeting reschedule permission issue for admin
- Corrected display of available days for rescheduled group events syncing with Google Calendar
- Fixed Stripe payment issue when the event was set in other language
- Resolved availability issues for the booking calendar
- Fixed the Gutenberg block's primary color not reflecting across the booking calendar
- Added missing translation strings to improve UI

1.3.1 (Date: 10 Apr 2024)
- Displayed woo product price on booking page
- Fixed stripe payment issue with shortcode
- Fixed gutenberg block's back to team button issue
- Fixed minor typo

1.3.0 (Date: 3 Apr 2024)
- New Gutenberg Block - FluentBooking Calendar
- Added Multi-payment Option Based on Meeting Duration
- Implemented PayPal Integration
- Added Stripe Refund Option for Admin
- Mark as Paid Option for Admin
- Custom Redirect URL on Paid Bookings
- Catching Time in Apple Calendar & NextCloud Calendar
- Added New Shortcode - Full Start and End Date Time
- Added Booking Rescheduled Trigger
- Resolved Daylight Saving Time Issue with Europe Timezones
- Fixed Group Event Double Slot Appears Issue
- Fixed Availability Permission Issue
- Improved UI

1.2.63 (Date: 26 Feb 2024)
- Daylight saving time fix
- Fixed issues With multi-hour slot availability
- Resolved minor UI issues

1.2.62 (Date: 10 Feb 2024)
- Reverting Daylight Saving Time Fix

1.2.61 (Date: 09 Feb 2024)
- Implemented Timezone Lock Feature
- Fixed Issue With Daylight Saving Time
- Fixed Booking Summary Email Issue

1.2.60 (Date: 06 Feb 2024)
- Improved frontend accessibility - achieve 100% score in lighthouse report
- RTL support
- Added booking type filter in booking listing
- Improved all cleanups
- Fixed booking date mismatch issue
- Fixed cutout time issue of availabilities
- Allow author to update timezone
- Added event slug update ability
- Displayed error messages of booking form
- Added no availability behaviour on schedule meeting
- Improved UI-UX

1.2.52 (Date: 26 Jan 2024)
- Fixed fluent booking menu not showing issue
- Improved Host/Team Member Searching
- Fixed Date Override issue
- Fixed conference options not coming in new event location issue
- Fixed issue with deleted Availability
- Fixed issue with deleted Calendar Event
- Added Calendar Avatar on Round Robin
- Added few missing translation
- Fixed double event created issue
- Updated Buffer Time by host

1.2.51 (Date: 17 Jan 2024)
- Fixed Manual Booking Issue with Google Calendar
- Fixed Calendar Event Permission Issue

1.2.50 (Date: 15 Jan 2024)
- Arrange Round Robin Meetings
- Invite Additional Guests Field
- Multi-select Field Form Field
- Custom Date Field in Form Builder
- Internal Improvements & Bug Fixes

1.2.41 (Date: 14 Dec 2023)
- Nextcloud Calendar Integration
- Redirection Option After Booking
- New Custom Field Types in Question Settings
- Multiple Meeting Duration
- Manual Booking Add from Admin Panel
- Fixed Issues with Fluent Forms Integration
- Other Improvements & Bug Fixes

1.2.3 (Date: 24 Nov 2023)
- Fixed Integration Settings
- Added Privacy Flag for Google Calendar Integration
- Fix Apple Calendar Integration Issue
- Added Padding to Fluent Forms Block

1.2.2 (Date: 24 Nov 2023)
- Apple Calendar Integration
- Microsoft Teams Support
- One-click Google Calendar Integration
- Dark Mode
- Control Booking Frequency and duration
- Reschedule or Cancel Group Meeting
- Added Default Country for Phone Field
- Improvements and Bug Fixes

1.2.1 (Date: 14 Nov 2023)
- Fixed Location issue for Google Meet
- Added Validation for Location Fields

1.2.0 (Date: 14 Nov 2023)
- Added Outlook integration
- Added Team Block
- Ajax-based Landing Page
- More translation strings added
- Added Pretty URL for Landing Pages
- All day and recurring events for google calendar sync issue fixed

1.1.0 (Date: 02 Nov 2023)
- Added WooCommerce Checkout Integration
- Added Deep integration with FluentCRM
- Personalization shortcodes for FluentCRM Automation
- Fluent Forms Conversational Form support
- One-Click Clone Calendar Events
- Added missing translation strings
- All reported bug fixed & other improvements

1.0.7 (Date: 31 Oct 2023)
- Fixed timezone issue for end of the month / year
- Added Missing Translation Strings
- Responsive Issue fixed
- Added French Translation File (Thanks to Ricardo Da Silva) - 85% Coverage

1.0.6 (Date: 26 Oct 2023)
- Twilio Integration for SMS & WhatsApp Notification
- Added Buffer Time before & after the booking slots
- Translation-Ready - 100% string coverage for translation
- Fluent Forms Integration Improved
- Added Booking delete option for Admin
- Added Booking Reschedule option for Admin
- Show message if Google Calendar API got disconnected
- Added support for past recurring events for Google Calendars Sync
- UI Improvements

1.0.5 (Date: 22 Oct 2023)
- Fix Google Access Token issue

1.0.3 (Date: 20 Oct 2023)
- Added Email Notification on Booking Reschedule
- Minor UI Improvements
- Added Time Format Pre-Selected based on settings
- Zoom Link issue with Google Calendar fixed
- Current date highlighted on the calendar

1.0.1 (Date: 19 Oct 2023)
- Added External Location details like Zoom / Custom Address / Phone Number to Google Calendar
- Added Caching time settings for Google Calendar event conflict check
- Added Block Options to customize the calendar & booking form
- Set default 12h / 24h time format from settings
- Multiple Locations selection issue fixed
- Timezone issue fixed on Availability
- Reminder Email Notification Fixed

1.0.0 (Date: 18 Oct 2023)
- Initial release
