=== Appointment Booking ===
Contributors: MotoPress
Donate link: https://motopress.com/
Tags: appointment
Requires at least: 5.3
Tested up to: 6.6
Requires PHP: 7.4
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

MotoPress Appointment Booking makes it easy for time and service-based businesses to accept bookings and appointments online.

== Description ==

MotoPress Appointment Booking makes it easy for time and service-based businesses to accept bookings and appointments online.

== Installation ==

1. Upload the Appointment Booking plugin to the /wp-content/plugins/ directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.

== Copyright ==

Appointment Booking plugin, Copyright (C) 2020, MotoPress https://motopress.com/
Appointment Booking plugin is distributed under the terms of the GNU GPL.

== Changelog ==

= 2.1.2, Sep 27 2024 =
* Fixed an issue where the [reservation_details] tag didn't work in emails with data from the Checkout Fields add-on.

= 2.1.1, Sep 17 2024 =
* Fixed an issue where buffer times were not being generated accurately for appointments, resulting in incorrect service availability display in the calendars.

= 2.1.0, Jul 18 2024 =
* Added the ability for admins to search bookings by customer name, email, and phone in the bookings list.
* Added the 'All day' checkbox to the employee schedule editing interface, eliminating the need to specify start and end times.
* Fixed minor issues and made small improvements.

= 2.0.0, Jul 16 2024 =
* Significantly improved group booking functionality to allow multiple independent people to book the same appointment slot as long as there are vacant places available.

= 1.24.0, Jun 26 2024 =
* Added the ability to create custom time steps for services that override the default time step settings.
* Added the ability to set services to a 24-hour duration to accept full-day reservations.
* Added the booking coupon code email tag to display the applied coupon code in emails.
* Added the ability to resend customer confirmation email for any booking.
* Added Reservation Received and Failed Transaction pages to redirect the customer to after their payment is placed on the external payment platform.
* Improved the look of analytics charts.
* Improved time period selection when setting up workdays and custom workdays in the schedule settings.
* Fixed an issue with disabling and enabling the service category when creating the appointment booking form shortcode.
* Fixed an issue with free reservations.
* Fixed an issue with the terms and conditions checkbox in the appointment booking form.
* Fixed an issue with reservation of services that have only a custom work day schedule.
* Fixed an issue that prevented selecting a new service category if a service from another category was already chosen.
* Fixed an issue where time slots were not generated sequentially.
* Fixed an issue with activating/deactivating the license in the plugin settings.
* Fixed an issue with the calculation of the maximum advance reservation rule.
* Fixed an issue where the last time slot was not displayed in the appointment booking form.
* Fixed an issue with WeChat payments in Stripe.
* Fixed an issue where the payment method of Stripe payment was empty.
* Fixed an issue with the date localization in emails, an appointment booking form, and the admin calendar according to your WordPress locale and date/time format settings.
* Fixed an issue where payment instructions for direct bank transfer payments were not displayed in the emails.

--------

[See the previous changelogs here](https://motopress.com/products/appointment-booking/#release-notes).
