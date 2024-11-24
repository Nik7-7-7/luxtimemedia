=== DaySchedule ===
Author URI: https://dayschedule.com/about
Plugin URI: https://dayschedule.com/
Donate link: 
Contributors: dayschedule
Tags: appointment, booking, calendar, service, appointment scheduling, booking calendar, calendly, booking system, reservations, scheduling, event booking, calendar, event booking system
Requires at least: 4.0
Tested up to: 6.4.2
Stable tag: 1.0.1
Requires PHP: 5.6
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


Appointment scheduling widget to embed on WordPress website and display your available calendar slots for bookings with payment options and reminders

== Description ==
[Embed DaySchedule ](https://dayschedule.com/widget) appointment scheduling plugin helps you add your dayschedule pages to your WordPress website in an easy way. 

This will allow your visitors to check your calendar availability and **schedule meetings, appointments on your WordPress website**, through DaySchedule.

The DaySchedule plugin also includes features like email, [WhatsApp reminder](https://dayschedule.com/docs/t/how-to-send-whatsapp-reminder-for-meeting/355) and SMS notifications, [integration with Google Calendar](https://dayschedule.com/docs/t/how-to-use-google-calendar-for-appointments/338), Zoom etc. It is easy to use and can be set up quickly on any WordPress website using short codes

DaySchedule is a free appointment scheduling software for calendar based appointments, webinar and services like [doctor appointment scheduling](https://dayschedule.com/solutions/doctors-scheduling-software), saloon, gym, spa, consulting, online coaching, sales team appointments etc.

The customers can schedule 3 type of events or services from your website to receive instant confirmation and reminders:

1. [1:1 event](https://dayschedule.com/products/1-1-meeting-sofware)
2. [Round-robin events](https://dayschedule.com/products/round-robin-scheduling-software)
3. [Group events](https://dayschedule.com/products/group-scheduling-software)

[youtube https://www.youtube.com/watch?v=z7_IwonRrp0&rel=0&list=PLAaVrSS8eHaeZNPPiOq36Da2z_ZCQ3ov-]

You will receive a booking confirmation email for each new booking, or can check your bookings on DaySchedule web, android or iOS app by logging in to your account:

- [DaySchedule web](https://app.dayschedule.com/signup)
- [iOS App](https://apps.apple.com/us/app/dayschedule-appointment-app/id6444792037)
- [Android app](https://play.google.com/store/apps/details?id=com.dayschedule)

== Installation ==

1. Go to `Plugins` in the Admin menu
2. Click on the button `Add new`
3. Search for `DaySchedule` and click 'Install Now' or click on the `upload` link to upload `dayschedule.zip`
4. Click on `Activate plugin`

== Shortcode ==

The plugin supports 2 types of embed options, inline and popup to display the appointment booking calendar with avaialble slots synced with your Google or Microsoft calendars:

=== Inline widget ===
To embed the inline appointment scheduling plugin on WordPress with shortcode:

`
[dayschedule url="https://meet.dayschedule.com" type="inline"]
`

=== Popup widget ===
To embed as a button and open the appointment scheduling popup when clicked:

`
[dayschedule url="https://meet.dayschedule.com" type="popup"]
`

== Customization ==

You can customize the scheduling widget by passing the attributes in `dayschedule` shortcode to change the url, text, color etc. :

* **type** : The type of widget to embed. **inline** or **popup**. The default value is:  `inline`
* **url** : The URL of your main dayschedule page or particular event to embed. The default value is: `https://meet.dayschedule.com`
* **text** : The anchor text for the button when the `popup` type is used. The default value is: `Book an appointment`
* **color_primary** : To change the primary color of the appointment calendar. The default value is: `#0f0980`
* **color_secondary** : To change the secondary color of the appointment calendar. The default value is: `#afeefe`
* **color_mode** : To change the color mode (light, dark or auto). The default value is: `light`
* **hide_event** : To hide the left panel with event details on the calendar view. Must be `0` or `1`, the default value is: `0`
* **hide_header** : To hide the header containing the logo and back button. Must be `0` or `1`, the default value is: `0`
* **class** : To add custom CSS classes on your button. The default value is: `wp-block-button__link`

So why wait? Let's simplify your scheduling process with DaySchedule appointments, the ultimate appointment booking plugin for WordPress!

== Frequently Asked Questions ==

= How do I display scheduling popup when clicked on a button? =
Add the following shortcode to any page or post to display a button:

`[dayschedule url="https://meet.dayschedule.com" type="popup"]`

= How do I add an additional CSS class on the book appointment button to match it with my brand? =
Use the `class` attribute when adding the shortcode. Example:

`[dayschedule url="https://meet.dayschedule.com" type="popup" class="wp-block-button"]`

= How do I get my scheduling page link? =
[Login to your dayschedule account](https://app.dayschedule.com/login) and create a resource(event, webinar, services etc), set availability, questionnaire, prices (for paid service) etc to get your scheduling link.
= How do I white-label the appointment scheduling widget?=
You may use the `hide_header` attribute to remove header with logo and `primary_color`, `secondary_color` to match it with your brand. For complete white-label access, you'd need the [enterprise plan](https://dayschedule.com/enterprise).
= Can I add this in doctor appointment booking website for WordPress? =  
Yes, the dayschedule booking appointment plugin is perfect for healthcare providers and businesses that need to manage appointments online. With the added feature of Google Calendar sync, it makes managing your schedule even easier.

== Screenshots ==

1. Appointment calendar view to display available slots synced with Google, Microsoft calendar
2. Dynamic registration form and questionnaire specified on the event settings
3. Booking confirmation email, add to calendar and reminder options for invitees

== Open source ==

The widget is open-sourced on Github and also avaialble on NPM. 
You can install it on other front-end frameworks like React, Vue.js or Angular as well.

- [Github](https://github.com/dayschedule/dayschedule-widget)
- [NPM](https://www.npmjs.com/package/dayschedule-widget)

== Changelog ==
= 1.0: December, 14, 2023 =
* Fixed file upload issue
* Added dark mode option

= 0.1: January 8, 2023 =
* Birthday of DaySchedule WordPress plugin