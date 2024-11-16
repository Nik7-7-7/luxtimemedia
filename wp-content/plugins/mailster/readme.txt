=== Mailster - Email Newsletter Plugin for WordPress ===

Contributors: everpress  
Tags: email newsletter, newsletter, newsletter signup, email signup, email marketing  
Requires at least: 6.2  
Tested up to: 6.6  
Stable tag: 4.1.4  
License: GPLv2 or later  
Requires PHP: 7.4

== Description ==

= Mailster is an easy to use Email Newsletter Plugin for WordPress. Create, send and track your Newsletter Campaigns without hassle =

#### Send Your Latest Posts, Products, Events or any other post type

Mailster lets you send all your posts, products, events or other custom post types. Every type can also be used for email automation.

#### Welcome Your New Subscribers

Set up your welcome series and leave the rest to Mailster.

You sit back and focus on your business while Mailster makes sure your new subscribers feel welcome.

#### Free and Premium Templates

Mailster offers you a choice of more than 400 free and premium designs.

Create stunning and engaging campaigns in minutes!

#### GDPR Compliance

When it comes to GDPR compliance, Mailster is your newsletter marketing tool of choice: Mailster fully complies with GDPR requirements.

All your data is stored in your own database and is not transferred or shared with third parties.

#### Grow Without Limits

With Mailster there’s no limit in subscribers. For a one-time fee you can have as many subscribers as you want.

Go ahead and grow as much as you like!

- Unlimited Subscribers
- Unlimited Forms
- Unlimited Lists

#### Send With Any Email Provider

Mailster works with any email provider, no matter if you want to use a professional provider or your own server.

#### RSS to Email

Your subscribers will want to know if there’s new content on a third-party site they follow.

With our RSS-to-email feature, new posts are automatically imported into an email and sent to your subscribers. Just set up your automation campaign and you’re ready to go.

#### Time Zone Based Sending

You have subscribers all over the world? With Mailster you send your email campaigns based on the time zone of your subscribers.

This way you ensure that your readers receive your emails at the exact times when they are most likely to open them.

#### Know Your Subscribers

Analyze your subscribers´ data and target your marketing strategy according to their needs.
Mailster collects and uses your subscribers’ data while staying completely within GDPR requirements.

#### Merge Tags

Merge tags are the key to marketing personalization: They are placeholder tags that get replaced with content tailored to your subscribers.

Our merge tags are customizable, extendable and easy to remember.

#### Create Better Segmentations

Increase your open rates with targeted campaigns and segmentation. Mailster provides many metrics to choose from so only the right audience gets the right email.

#### Great Campaign Insights

Track and analyse your campaigns and subscribers: Benefit from real time insights into your campaigns.

Find out about your subscribers’ click rates and open rates, their location, and other activities relevant for your online marketing strategy.

#### Test Your Email Quality Before Sending

With Mailster you can give your email campaign a thorough pre-check before sending it out.
Mailster gives you feedback on what you should improve.

Fix these issues, send and relax – your campaign will be good.

#### Automation

Send automatic welcome emails, birthday emails, drip campaigns, follow-ups, auto-responders and more.

Just sit back and let Mailster take care of your email marketing.

#### You Own Your Data

All your subscribers’ data is stored in your own database.
No third party has access to that data.

#### Dozens of Integrations With Plugins

We’ve partnered with many popular developers so you can continue using your favorite plugins along with Mailster.

#### Built in Capability Management

Assign specific rights to the people in your team. This makes it easy to keep full control over who does what.

Let your team support you!

#### Custom Template Language

With Mailster’s simple and straightforward template markup language, coding your own template or adopting existing ones is easy.

#### Developer Friendly

Mailster provides plenty hooks and filters you can use to increase its functionality and options.

Just go ahead and adapt Mailster to your unique needs.

#### Features

- Capture subscribers on your website or blog.
- Send your latest posts/products/custom post types.
- Welcome Emails.
- Birthday Campaigns.
- Follow up/Drip Campaigns.
- 400 free and premium templates.
- RSS email campaigns.
- Built in email campaign precheck.
- Integration with your favorite plugins.
- Import your existing data with ease.
- Available in over 15 languages.
- GDPR Compliance.
- Send with any email provider.
- Time zone based delivery.
- Merge Tags.
- Advanced Segmentation.
- Newsletter Campaign Insights.
- Auto Responders and email marketing automation.
- Full Data Control.
- Reports.
- Public archive of your latest newsletters.
- Capability management.
- Developer friendly.

== Templates ==

= Free and Premium Templates =

Mailster supports over 400 templates from various authors. Please visit our website to browse them all.

**[Templates →](https://mailster.co/templates/)**

== Add Ons ==

= Add Ons =

Extend the functionality of Mailster or your site with these add ons.

**[Add ons →](https://mailster.co/addons/)**

== Support ==

= We’re Here to Help =

= Knowledge Base =

Find help on our knowledge base. With over 300 articles, tips and troubleshoot guides you can quickly find answers to the most common problems.

**[Visit Knowledge Base →](https://kb.mailster.co/)**

= Members Area =

We provide all of our official support via the member area. Please login or register if you have not done so yet for access so that you can post your question there.

**[Login →](https://mailster.co/login/)** | **[Register →](https://mailster.co/register/)**

= Hire an expert =

Get professional development help from our expert support partner _Codeable_ for consultations, customisations and small to medium sized projects.

**[Get a Quote →](https://codeable.io/?ref=zATDD)**

== Changelog ==

= Changelog =

= 4.1.4 =

- fixed: profile showed unsubscribe button
- fixed: workflow can be triggered multiple times in some instances
- improved: checks if Mailster methods are called before Mailster is initialized
- improved: Support for WooCommerce Add-on

= 4.1.3 =

- fixed: automation schedule generated invalid query if empty arguments were passed
- fixed: missing translations
- fixed: respect `WPLANG` property when fetching translations
- fixed: typo on dashboard
- improved: allow updating from the dashboard if translations have been loaded already
- improved: hook based triggers now support multiple or all subscribers

= 4.1.2 =

- fixed: PHP warning in automations class
- fixed: missing ID when block forms are used on the frontend
- fixed: missing campaign on email workflow
- fixed: some strings weren't translate-able improved: translations
- improved: form patterns are now translate-able
- added: link to Translator Program on dashboard

= 4.1.1 =

- fixed: PHP Error with PHP version 7.4
- fixed: Jumper Step caused broken block in some cases
- fixed: Step Appender not aligned correctly

= 4.1.0 =

- new: Jumper Step: Jump to a specific step in the workflow.
- new: Notifications Step: Send a notification to an email address.
- new: Allow Prefills forms with URL parameters.
- new: Queued subscribers on workflows can now get inspected and removed/forwarded/finished.
- new: Option to skip steps in workflows.
- new: Option to allow pending Subscribers entering the workflow.
- new: Option to activate/deactivate and duplicate the workflow from the overview page.
- new: Capabilities to manage workflows and block forms.
- new: Shortcode of forms is now quickly copyable from the forms overview page.
- added: option to selected autocomplete type for input elements on forms.
- improved: initiation Mailster. Now the main class is loaded when all plugins are available.
- improved: Newsletter Homepage in the Site editor is not broken anymore.
- improved: frontend script for forms is executed with domReady.
- improved: BlockRecovery now uses less resources.
- improved: refetching data is now less resource intensive.
- improved: missing step appender in WP 6.6 is now back (and better).
- improved: new icons for some steps to make them more distinguishable.
- improved: time related input fields got a "now" button to quickly jump to the current time.
- improved: more option buttons in editor toolbar for better accessibility.
- improved: workflows can now run max 1024 steps in a single process to prevent infinite loops.
- change: Canvas toolbar is now located in the canvas.
- change: `wp_mail` setup: changed the way functions are loaded

= 4.0.11 =

- fixed: PHP error "attempt to assign a property to an array"
- fixed: styling issues on Automations with WP 6.6
- fixed: missing Canvas tools on WP 6.6
- fixed: workflow steps with delay didn't calculated the timezone correctly.

= 4.0.10 =

- fixed: escaping on web version
- fixed: subject placeholder tag in title was not replaced on the webversion
- improved: css improvements
- update plugin api info

= 4.0.9 =

- fixed: line height on form selector input field
- fixed: PHP Warning: preg_replace(): Compilation failed with certain shortcodes.
- fixed: PHP deprecated notice
- fixed: admin header was visible on the newsletter homepage
- fixed: deeplink of steps works again
- fixed: dynamic properties are deprecated
- fixed: fatal error on tags triggers if no tag has been set
- fixed: logos were not applied to some templates
- fixed: translation wasn't loaded before the setup wizard
- improved: bounce performances

= 4.0.8 =

- fixed: linked images in templates weren't mapped correctly.
- fixed: missing array index in options.php
- fixed: using "given-name" for autocomplete value instead of "name" on the first name field for block forms
- improved: legacy forms inclusion
- improved: activation experience for Envato users
- improved: bounce handler to not timeout during processing of large quantities of bounce messages
- removed: beta notice
- removed: health check notice.
- reverted: forcing unsubscribe link on the bottom of the mail if not present in the email.
- fully tested with WordPress 6.5

= 4.0.7 =

- fixed: action links on campaign overview page.
- fixed: linked images in templates weren't mapped correctly.
- fixed: missing array index in options.php
- improved: legacy forms inclusion
- improved: bounce handler to not timeout during processing of large quantities of bounce messages
- removed: deprecated cron trigger

= 4.0.6 =

- fixed: Custom fields with underline were not working
- fixed: check for array on bulk actions on campaigns
- fixed: embeded elements were broken caused by block forms
- fixed: wrong link in the block form overview
- improved: added option to define 'fields' in search query on the subscribers overview to search only within certain fields
- improved: deleted subscribers no longer count to totals on overview page
- improved: loading of lists page with large subscriber base.

= 4.0.5 =

- fixed: SQL error on form preview page
- improved: loading time on subscribers overview

= 4.0.4 =

- fixed: journey were triggered more than once in some circumstances
- fixed: usage of `wp_posts` instead of `{$wpdb->posts}` on some queries
- improved: fast triggers in automations
- improved: cleanup on date related workflows

= 4.0.3 =

- added: upgrade notice
- fixed: small bug fixes and CSS improvements

= 4.0.2 =

- fixed: custom field creation during import if no custom fields are present
- fixed: render error if first step in workflow is removed
- improved: handling different translations for different users
- updated: included templates

= 4.0.1 =

- fixed: some phpcbf issues
- fixed: Newsletter homepage creation causes broken blocks
- fixed: checkout links fixed: typo
- fixed: some automations got stuck on the delay step under certain conditions

= 4.0.0 =

- **Email Health Check**: Check your delivery method to comply with upcoming changes to Gmail and Yahoo.
- **Block Forms**: Create forms and popups with the built-in editor.
- **Automations**: Bring automations to the next stage with customer journeys.
- **One Click Unsubscribe**: Now enabled by default for Gmail and Yahoo, complying with RFC 8058.
- **Save Custom Modules**: Allows for saving and reusing custom modules within the platform.
- **Native Support**:
- For DIVI.
- Improved native support forms for Elementor, including a change in the slug of the Elementor module to prevent conflicts.
- **General**:
- Welcome Wizard.
- Automation triggers now run on the same process.
- List view labels are more descriptive.
- Newsletter homepage block.
- Standardized rendering of campaigns.
- Performance improvements on querying action data from campaigns and on data in the wp_options table.
- Rendered of admin screens.
- Subscribers detail page.
- Allow any type of hook in hook triggers.
- **Forms and Popups**:
- Block form preview.
- Animations on forms are now reduced if clients use "prefer-reduced-motion".
- Honeypot mechanism to prevent false positives on heavily cached sites and improved to prevent false positives more broadly.
- **Compatibility and Standards**:
- PHP 8.2 compatibility.
- Updated WP Coding Standards to 3.0.
- Fully tested for PHP 8.1 and partially tested with PHP 8.2.
- **UI/UX**:
- Improved preview of forms from the block form overview.
- Improved rewrite rules for newsletter homepage.
- Improved placeholder image tags algorithm.
- Improved beacon message loader.
- CSS improvements on the form editor.
- General style updates.
- **Technical and Structural**:
- Page link triggers are now stored differently.
- No longer use trigger post meta value for automation triggers.
- Moved form padding to style section.
- Hide "Show form" option if used in content.
- **Deprecated**:
- Legacy forms.
- **SDKs and Libraries**:
- Freemius SDK updated to 2.6.2.
- **Geo API**: Updated to use preferred single mmdb file instead of multiple data files.
- **Admin Screens**: Improved rendering.
- **General Fixes**:
- Deprecated notice on subscribers detail page for PHP 8.2+.
- Warning on activation if update state is not clear.
- Warnings when trying to resize SVG images in email.
- Custom fields were not saved in some cases.
- Warning on PHP 8.2 if subscriber count is 0.
- CodeMirror editor not responding.
- `mailster_ip2Country` didn't return country codes.
- Forms popup appear in wrong places.
- Conditions were sometimes wrongly not fulfilled.
- Automations don't get triggered due to a wrong db column.
- Inline style attribute got removed in some edge cases.
- Scroll percentage trigger was not working.
- Align property hasn't been stored if forms are used out of context.
- Prevent step ID be the same if multiple blocks are duplicated.
- **Premium Templates**: Now available to certain plans.
- **Specific Date and Anniversary Triggers**: Can now have an offset.
- **Support and Cleanup**:
- Improved log cleanup algorithm.
- New filter 'mailster_cron_simple_output' to change the output of the cron page.
- Run KSES filter on form output via shortcode.
- Smaller fixes and improvements, cleanup.
- Support block form preview in the site editor.
- **Miscellaneous**:
- Option to convert certain autoresponders to workflows.
- Legacy forms menu entry is hidden by default.

= 3.3.13 =

- new: Email Health Check: check your delivery method to comply with upcoming changes to Gmail and Yahoo

= 3.3.12 =

- fixed: deprecated notice on subscribers detail page for php 8.2+
- fixed: warning on activation if update state is not clear
- fixed: warnings when trying to resize SVG images in email
- improved: subscribers detail page

= 3.3.11 =

- fixed: deprecated method
- fixed: issue when replacing links introduced in 3.3.9
- fixed: BF sales dates

= 3.3.10 =

- fixed: warning on PHP 8.2 if subscriber count is 0
- fixed: issue with the admin bar
- fixed: smaller issue

= 3.3.9 =

- PHP 8.2 compatibility 🎉
- updated: WP Coding Standards 3.0
- updated: Freemius SDK to 2.6.0
- fixed: inline style attribute got removed in some edge cases
- improved: placeholder image tags algorithmus
- fixed: smaller issue

= 3.3.8 =

- improved: honeypot mechanism to prevent false positives on heavy cached sites.
- improved: wording on support
- updated: Freemius SDK to 2.5.12

= 3.3.7 =

- fixed: missing action on migration process
- improved: handling of trials
- updated: Freemius SDK to 2.5.10

= 3.3.6 =

- fixed: lists are not confirmed if user choice is enabled.

= 3.3.5 =

- updated: Freemius SDK to 2.5.9
- fixed: small CSS issues on RTL

= 3.3.4 =

- fixed: MySQL error on autoresponders which uses "has received but not opened" or "has received and not clicked" condition.
- fixed: RTL CSS issue on Autoresponder options sidebar
- fixed: RTL CSS issue in admin bar and settings

= 3.3.3 =

- added: 'mailster_admin_header' hook
- added: missing added argument
- added: if verification of subscribers fails the WP_Error object contains now the initial data
- improved: better handling of suppression of WPDB in lists class
- improved: check for missing functions in conditions
- fixed: conditions script must require mailster-script
- update: enabled beacon on the upgrade page
- fixed: check for existence of block editor in admin header
- fixed: default option on custom fields were not displayed correctly
- fixed: id was not set for custom fields on first save
- improved: lists assignments for new subscribers
- improved: refractor of lists class
- improved: the way tags are saved and loaded
- returned ids of subscriber queries are now integers

= 3.3.2 =

- fixed: Division by zero on campaign edit screen
- fixed: Header already sent error on account page
- fixed: dashboard redirects to a blank page if in activation mode
- fixed: deprecated notices in PHP 8.2
- fixed: missing index in placeholder.class.php
- improved: check if `set_time_limit` is disabled

= 3.3.1 =

- updated: Freemius SDK to 2.5.5
- fixed: Undefined variable $output_time in queue.class.php
- fixed: CSS issue on Precheck
- improved: connect screen for Envato licenses

= 3.3.0 =

- new: [Action Required] license provider - please follow the guide to migrate your license
- new: Email logs. You can now enable logging for all outgoing mails sent by Mailster
- new: option to use OpenStreetMap as an alternative for Google Maps
- new: emoji picker in campaign editor

= 3.2.6 =

- added: option to bulk add and remove from every list
- fixed condition on the subscribers over view
- fixed: PHP throws error if `str_replace` with a value below 0
- fixed: RSS feed missing modified date in some cases change: RSS feed extract images from content first
- fixed: issue when bulk confirm/add/delete subscribers from list
- fixed: missing slug on add plugins page
- improved: better sql query for growth calculation.
- improved: database cleanup mechanism
- improved: prevent caching on cron page
- improved: speed of delivery is now split into PHP processing and mail sending on the cron page
- improved: updated queue SQL to handle campaigns when split campaigns is enabled.
- tiny fixes and improvements

= 3.2.5 =

- new: Admin header bar with new support integration.
- new: Help buttons located in the plugin to provide context-specific assistance
- changed: Mailster related notices only show up on Mailster related pages.
- fixed: campaign related conditions for "Any Campaign" now work as expected.
- fixed: restoring of deleted subscribers working again.
- fixed: issue where some subscribers are not able to get deleted/exported
- fixed: missing object error in mailster-script
- fixed: searching with quotes on the subscribers page working again
- fixed: broken RSS feed URL can cause timeouts
- fixed: force array when duplicating a campaign for `wp_insert_post`
- change: do not use the `$wp_filesystem` global when require filesystem
- added: `mailster_get_user_setting` and `mailster_set_user_setting`

= 3.2.4 =

- Do not show form occurrences from auto draft posts
- allow selection private post in the static editbar
- implemented feed item date checks
- improved handling of images in RSS feeds
- added new status upgrade status code
- remove any tinyMCE attributes from the content on campaign save
- use vanilla methods to change target on frontpage

= 3.2.3 =

- fixed: E_ERROR on Geo location class in PHP 8.1
- added enhancement issue template
- check if option from queue has template element
- fixed: footer branding
- fixed: Uncaught TypeError: in notifications.class.php
- standardize outgoing URLs
- tested up to 6.1

= 3.2.2 =

- added: native Advanced Custom Fields support.
- fixed: import of WordPress roles wasn't working in some cases.
- fixed: force hard reload on cron page if opened in browser.
- improved: handling of thickbox modal if other plugins interfere.
- added: `X-Redirect-By` header on all Mailster related redirects.
- added: set the global post inside the template editor.

= 3.2.1 =

- fixed: issue where taxonomies in campaigns are not stored correctly.
- improved: `{unsub}` and `{profile}` tags can now be used in confirmation messages.
- added: message for block form plugin
- support for [Local Google Fonts](https://wordpress.org/plugins/local-google-fonts/)
- new filter: `mailster_do_placeholder` which filters the replaced content
- fixed: Jetpack no longer includes sharing button in content or excerpt
- fixed: some error notices on PHP 8.1

= 3.2.0 =

- fixed: querying subscribers do no longer return subscribers with status deleted.
- fixed: adding an already deleted subscriber working as expected.
- fixed: wrong timestamp on signups if subscriber exists.
- improved: support for multiple campaigns triggered by action hooks.
- improved: removed skeleton loader on foreign columns in overview.
- improved: action hook campaigns support now multiple hooks, separated with a comma.
- improved: database updates now run in the background (optional).
- improved: taxonomies dropdown now uses select2 library to better handle large taxonomy entries.
- new defaults strings form confirmation message.
- confirmation page on newsletter homepage now wrapped with `wpautop`.
- new filters: `mailster_editor_tags` and `mailster_notification_content`.

= 3.1.6 =

- added: option to change tracking of campaigns once the campaign has been finished.
- improved: list counts are loaded now asynchronously to improve page load time.
- improved: embedded images are now found outside the upload folder.
- settings with "token" in the key are now hidden in the test email.

= 3.1.5 =

- new: growth rates on campaign overview.
- opens, clicks, unsubscribes and bounces are now sortable.
- minimum required PHP version is now 7.2.5.
- reduced size of vendor folder.

= 3.1.4.1 =

- fixed: issue with WooCommerce 6.5.1 and third party library.

= 3.1.4 =

- new: forms on frontend no longer requires jQuery.
- fixed: using single quotes in tags causes problems.
- fixed: PHP warning on PHP 8.1.
- improved: better handling of translations on plugin activation.

= 3.1.3 =

- fixed: default placeholder tags where not replaced on system mails.
- fixed: security vulnerability where a logged in user can discover the profile URL from a different user. (discovered by D.Jong from patchstack.com).
- improved: ajax operations are now checked against capabilities.
- improved: updated "Preheader text hack" from Litmus.

= 3.1.2 =

- fixed: CSS for WP 5.9.
- fixed: small typo in variable.
- fixed: compatibility for PHP 5.6+.

= 3.1.1 =

- fixed: time specific auto responders sent only on Sundays causes sending the following to be way in the future.
- fixed: type in test bounce message.
- fixed: typo in subscriber query causes database error.
- fixed: PHP warning of undefined variable in option bar.
- added: unsubscribe link to in mail app unsubscribe message.
- added: filter `mailster_campaign_meta_defaults` to filter default meta values.
- added: defined `wp_mail` filters are now applied if used with Mailster.

= 3.1 =

- new: Remove inactive Subscribers automatically.
- new: Relative conditions for date fields.
- new: filters in the subscriber overview.
- updated: Manage Subscribers page.
- fixed: add trailing space to preheaders to prevent unintentional line breaks in previews.

= 3.0.4 =

- fixed: saving template from editor messed up template header.
- fixed: bulk deletion with actions working again.
- fixed: auto responder no longer triggered if post is published in the past.
- fixed: shortcodes are now handled properly on the web version.
- added: text strings for error messages defined by the security settings page.
- improved: ajax handler.

= 3.0.3 =

- fixed: timeframe settings spanning over midnight.
- fixed: layout issue on form/lists overview on smaller screens.
- fixed: missing dbstructure method on queue process.
- added: option to block and allow people from certain countries to signup.
- update: using `get_user_local()` instead of `get_locale()` when applicable.

= 3.0.2 =

- fixed: bulk options causes a subscriber query error.
- fixed: duplicating forms throw an error.
- fixed: some notifications missed template defined settings.
- change: optional warmup has been extended to 60 days.
- improved: database errors during cron tried to get fixed automatically.
- added: reminder to enable auto updates after a Mailster update.

= 3.0.1 =

- added: editing templates on the templates page is back.
- fixed: mergetags now work correctly in image URL field if fallback is present.
- fixed: draft campaigns can now get duplicated.
- fixed: install plugins on addon page is working.
- fixed: problem if PHPMailer is loaded in another plugin.
- fixed: installed templates were not access able when no required Mailster version was set.
- improved upgrade process from 2.4.x.
- smaller bug fixes.

= 3.0 =

- new: Test the Email Quality with the built in Pre-check Feature.
- new: Tag you subscriber with Tags fro better segmentation.
- new: Improved security with a dedicate security settings page.
- new: Automatic batch size settings to calculate your optimal sending rate.
- new: Option to create new campaigns on action hook based auto responders.
- new: Updated add ons page to browse and install even more integrations.
- new: Updated templates page now lists over 400 free and premium templates.
- new: UI update with new icons based on SVG.
- new: Auto click prevention to prevent bots auto clicking and messing up your stats.
- new: Sending warmup if you send from a new IP address or domain.
- improved: db handling by splitting the actions table into five separate ones.
- improved: added primary keys to these tables: queue, subscriber_meta, subscriber_fields.
- improved: calculation of user rating has been offloaded as it's often server intense.
- improved: changes to tracking for the Apple Privacy Protection plans.
- improved: change on the random handler for random posts.
- added: new dynamic tag for button labels `{post_button:-1}`.
- added: indexes to campaigns to distinguish if multiple ones are sent (like birthday greetings).
- removed: auto update option to prefer native solution.
- removed: deprecated `mymail` hooks and filters.

**For further details please visit our change log page.**

**[Mailster Homepage →](https://mailster.co/changelog/)**
