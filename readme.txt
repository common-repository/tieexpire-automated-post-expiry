=== TIEexpire Automated Post Expiry ===
Contributors: TIEro
Donate link: http://www.setupmyvps.com/tieexpire/
Tags: post, expiry, expiration, expire, automatic, automated, category, categories
Requires at least: 3.0.1
Tested up to: 4.0
Stable tag: 1.1
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Expires posts based on multiple criteria, with category and post status options. Sends notifications to users and admin on demand.

== Description ==

*This plugin is now part of the free plugin [TIEtools](http://wordpress.org/plugins/tietools-automatic-maintenance-kit/ "TIEtools"), which also includes duplicate post control and server log file removal.*

Simple post expiration plugin. Expires posts based on a variety of criteria, including category and post status options.

- Expires published, draft, pending and private posts on demand.
- Includes or excludes user-defined list of categories.
- Moves all expired posts to the Trash.
- Notifies post author, site admin and others of post expiry on demand.
- Permanent post deletion is handled by WP's built-in Trash removal.
- Completely automated by wp-cron once options are set.

Currently, the plugin offers four cumulative expiration methods:

1. Expire posts based on their age (e.g. expire posts created more than 90 days ago).
2. Retain a given number of posts and expire all others (e.g. keep the latest 1,500 posts).
3. Detect the BAW Post Views Count plugin and expire posts based on a combination of post age and number of views (e.g. expire all posts over 45 days old which have fewer than 100 views).
4. Detect the WTI Like Post plugin and expire posts based on a combination of post age and total number of likes (e.g. expire all posts over 90 days old which have fewer than 10 likes).

Each expiration check is run separately and in the order listed above, so you can build quite complex expiration structures to catch a variety of criteria. 

Post status choices apply to all expiration methods. The user-defined category filters can be switched on or off for each method.

== Installation ==

1. Upload the plugin folder and its contents to the /wp-content/plugins directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Set your options using Post Expiry at the bottom of the Dashboard menu.

Alternatively, use the built-in 'Add New' option on the Plugins menu to install.

== Frequently Asked Questions ==

= It's not expiring my posts! =

There have been occasional reports of the plugin not properly activating its wp-cron job, so nothing gets expired. If you see this happening on your site, deactivate the plugin and reactivate it. That normally solves the problem. If it doesn't, post about it in the WP support forum as usual.

= Is this plugin actively maintained? =

Yes, it is. Nothing new is added, but bugs will be fixed. All new functionality goes into [TIEtools](http://wordpress.org/plugins/tietools-automatic-maintenance-kit/ "TIEtools").

= Why don't I see options for Views and Likes? =

Because you don't have the other plugins installed: TIEexpire only shows you the options you can use.

= What versions of the other plugins are compatible? =

At present, TIEexpire works with BAW Post Views Count v.2.19.11 and WTI Like Post v.1.4. Since this plugin is based on the database tables rather than any code used in those plugins, it *should* work with newer versions unless there's a major restructuring. No guarantees, though.

= Why is there a {prefix}_wti_totals view in my database? =

The basic WTI plugin stores all its data in individual lines in its own table: one for every like for every post for every user. On a popular, fast-moving site, that's a *lot* of lines of data. In testing on a site with about 5,000 posts and a similar number of likes, TIEexpire locked access for over two minutes while it ran against raw data. The wti_totals view solves the problem by summarising all that WTI data so that it can be accessed quickly to avoid killing your site. It is not used for anything else and is only created if you have the WTI LIke Post plugin installed and active.

= In what order is post expiry done? =

From the top to the bottom on the settings page. By age -> by number of retained posts -> by views -> by likes. All tests are run individually and are cumulative, so the age check is completed and old posts expired, then the post limit test is run against all content remaining, and so on.

= Can I change the order? =

Only by editing the plugin file: look for the do_TIEexpiry_all function and move things around if you're happy doing that. I'm hoping to add a feature to the options screen for this in future versions.

= Can I switch off an expiry test? =

Yes. Just set a value to zero to switch off a test. In those with two options (views/likes), setting either value to zero switches off the test.

= How often does the wp_cron job run? =

At most once per hour. You can change this in the do_activation function: switch the value 'hourly' to whatever suits you (and will work with wp_cron).

= Can I include or exclude specific categories for expiry? =

Yes. As of version 1.0.3, you can list categories to include or exclude and switch the filter on or off for each expiry method. I'm hoping to add a pretty interface for this in future versions - for the moment, a comma-separated list of category numbers will have to suffice.

= I chose one of the category options and now I can't switch it off... help! =

The include/exclude option is a radio button, so it can't be set to "neither". However, you can switch off the category filter for each expiration method. Setting the categories to include (or exclude) to "0" and clicking the radio button efffectively stops all category filtering as well.

= If I enter a category number which has sub-categories, what happens? =

The parent category will be taken into account and all sub-categories will be ignored. Yes, this makes entering a dozen sub-cats a long process, but it gives you much finer control over precisely what is included or excluded in the expiry process.

= How can I get category numbers for my lists? =

You can go to Posts -> Categories, click a category name and look at the URL, which includes a "tag_ID=xxx" part, showing the category number (xxx). Or you could install and activate the Reveal IDs plugin, which adds a column on the categories page to show the ID number of each one. Much easier. The plugin URL is http://wordpress.org/plugins/reveal-ids-for-wp-admin-25

= Can I use different categories or post types on different expiry processes? =

No, not at the moment. I figured it would be unlikely that anyone would want that level of control, though I guess I could put it in if people ask for it. It'd make the options even more complicated.

= Can I include or exclude specific posts or tags for expiry? =

No. The plugin only handles categories at the moment.

= Can I change the notification email? =

Yes. You'll have to edit the plugin file, though. Look for the TIEexpire_send_notification function. There's a different email for each recipient, so you can customise to your heart's content.

= Can I put multiple email addresses in the "Someone else" box? =

Of course you can. There's no guarantee it will work, though. Separate addresses with commas and cross your fingers. The plugin does not check the validity of the email address you put in there.

= Does the plugin cause major slowdowns when it runs? =

The very first time the queries run, it might. This is especially true if you have a *lot* of posts and use several of the checks. Notifications are particularly ponderous. 

In testing, I ran it against a database with around 5,000 posts published across a year and it caused a delay of 10-15 seconds in page serving the first time it ran. After that, I never noticed a delay again, even with a reasonable expiry rate.

= Is there any documentation? =

You're reading it. However, while I was developing the plugin, I kept notes and wrote several blog posts that follow and explain the process. You can read those on http://setupmyvps.com/tieexpire if you want more info (or want to see how a noob built his first plugin). The plugin code is also heavily commented to help you find your way.

== Changelog ==

= 1.1 = 

- Added notification emails for admin, post author and others.
- Compatibility maintained with TIEtools.

= 1.0.4.2 =

Multiple text corrections, additions and clarifications.

= 1.0.4.1 =

Bug fix: post status check was not running in query to retain x posts.

= 1.0.4 =

- Added support for draft, pending and private post expiry in addition to published posts.
- Set defaults for post status and category fields on activation (which do not overwrite existing choices).

= 1.0.3 =
- Added inclusion and exclusion options for list of categories.
- Added individual switches for category filters for each exclusion type.
- SQL query construction adjusted for category filters and future expansion.
- Options page reformatted so it looks prettier.

= 1.0.2 =
Documentation changes, FAQ updated, code heavily commented.

= 1.0.1 =
Minor documentation changes and corrections.

= 1.0 =
Original working release.
