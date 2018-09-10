=== Akeeba Backup CORE for WordPress ===
Contributors: nikosdion
Donate link: https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=10903325
Tags: replace, migrate, move
Requires at least: 3.8.0
Tested up to: 4.9
Requires PHP: 5.4
Stable tag: ##VERSION##
License: GPLv3 or later
License URI: http://www.gnu.org/licenses/gpl.html

Easily mass replace data in your database. Make site WordPress transfer painless.

== Description ==

Akeeba Replace is an open-source, free of charge plugin for WordPress to facilitate replacing data in your database. It
can be used for mass replacing of content in your database, as well as making it easier to transfer your WordPress site
to a new domain and / or hosting environment. It's made by the same people who created Akeeba Backup, the popular site
back and transfer software for WordPress, Joomla, Drupal and other PHP-based content management and e-commerce solutions.
It can handle both plain text and, most importantly, serialized data accurately and safely.

*Why is this different than similar scripts*

In short, it's down to the way we handle serialized data and the way we apply the database changes. First, let's talk about security.

Other data replacement solutions, even the one shipped with the WP-CLI tool, use PHP's unserialize() method to decode the serialized data before replacing them. This is insecure as explained [in the PHP site itself](http://php.net/manual/en/function.unserialize.php). In fact, serialization is so broken that [PHP is no longer fixing security bugs in it](https://externals.io/message/100147). Considering that mass data replacement solutions _may_ very plausibly stumble into malicious serialized data submitted by a malicious user and inadvertently put into the database by a well meaning script data replacement could lead to a security mishap, i.e. your site could be hacked. We sidestep this security issue by using smart, partial decoding without going through unserialize() in our code.

Other data replacement solutions try to replace everything in the database in a single go. If you have a big site with thousands of posts and hundreds of thousands of comments this may lead to a server timeout error, leaving your database in a partially replaced state, i.e. some tables or some rows of a table may not have been replaced. This could break your site. We work around this issue in two ways. First, we have experience in iterating really large databases since we first released our backup software in 2006. We know how to adaptively process the database in small chunks, using AJAX to process each step, avoiding timeout issues. Moreover, we only commit to the database the rows which have actually changed instead of every row we read. Since the majority of rows are typically not touched we can further reduce the time it takes for the operation to run.

Most data replacement solutions either run directly against your database ("live mode") or do a test run without touching data ("dry run"). Akeeba Replace includes a third option: exporting the operations it'd run in a SQL file. This file can be run against the database using a third party tool such as phpMyAdmin or Adminer. It doesn't even have to run right now, on the server you are creating the file. You can run it on the server where you are moving your site, right after you move it, therefore minimizing the time your site appears to be broken and / or off-line.

All data replacement solutions tell you to take backups before you run a replacement. Chances are you only realise why you needed to do that after you are in a desperate need of backups. Akeeba Replace is proactive in that regard, taking a backup _while_ it's replacing data and before it runs the actual replacement. The backup technology is rock solid, based on our long experience of maintaining a pure-PHP backup solution since 2006.

Moreover, all data replacement solutions tell you to just provide the replacements you want to make. This works well when you are replacing, for example, your company name but doesn't help you any when you have moved your site to a new host! What replacements should you make? Akeeba Replace takes most of the guesswork out, building on our experience in writing backup software for WordPress designed from the get-go to move sites between different hosts.


**Features**

* WordPress plugin, standalone script and CLI tool versions of Akeeba Replace are available (you are downloading the WordPress plugin; it has links to the other tools).
* Mass replace the contents in your database.
* Safe replacement of serialized data.
* Do it live on your database, make a dry run or export to SQL for applying it with external tools.
* Automatic backups of the data being replaced.

Indicative uses:

* Moving your site to a new domain or hosting environment.
* Mass replacing information on your site.

IMPORTANT:

Restoring the backups made during database replacement, or applying exported SQL files, requires a third party tool such
as phpMyAdmin or Adminer. These are provided by your host. This is on purpose, not an oversight. Restoring a backup is
typically required when your site has crashed as the result of a replacement gone wrong, be it a bug or a small but
serious typo which went unnoticed.

== Installation ==

1. Install Akeeba Replace either via the WordPress.org plugin directory, or by uploading the files to your
   server. In the latter case we suggest you to upload the files into your site's `/wp-content/plugins/akeebareplace`
   directory.
1. Activate the plugin through the 'Plugins' menu in WordPress

== Frequently Asked Questions ==

= I have spotted a bug. Now what? =

Please use [our Contact Us page](https://www.akeebabackup.com/contact-us.html) to file a bug report. Make sure that you
indicate "Bug report" in the Category selection. We will review your bug report and work to fix it. We may contact you
for further information if necessary. If we don't contact you be assured that if you did report a bug we are already
working on fixing it.

= I have a problem using the plugin. What should I do? =

The first thing you should do is [read our extensive documentation](https://www.akeebabackup.com/documentation/akeeba-solo.html). If you'd like to receive personalised support from the developers of the plugin you can [subscribe](https://www.akeebabackup.com/subscribe/new/backupwp.html?layout=default) to our services. Due to the very specialised nature of the software and our goal of providing exceptional support we do not outsource our support. All support requests are answered by the developers who write the software. This is why we require a subscription to provide support.

= Does your software support WordPress MU (multi-sites a.k.a. blog networks)? =

Yes.

= What about serialised data? =

Our software was written with the express purpose of handling serialized data gracefully.

= WordPress moved to UTF8MB4 (UTF-8 Multibyte). Do you support it? =

Yes, of course.

= What are the requirements for your plugin? =

Our plugin requires PHP 5.4 or any later version. Older versions of PHP including PHP 4, 5.0, 5.1,
5.2 and 5.3 are not supported. We recommend using the latest PHP 7 release for security and performance reasons.

We are always testing against the latest released version of WordPress. It should work on earlier versions of WordPress but we cannot guarantee this.

Our software does not have a hard requirement on PHP memory (memory_limit). However, ee strongly suggest 64MB or more for optimal operation on large sites with large amounts of data.

Finally, you need adequate disk space to take a backup of the data which is being replaced and / or exporting the changes to a SQL file.

= Can I use this plugin on commercial sites / sites I am building for my clients? =

Yes, of course! Our plugin is licensed under the GNU General Public License version 3 or, at your option, any later
version of the license published by the Free Software Foundation. This license gives you the same Four Freedoms as
WordPress' license; in fact, GPLv3 is simply a newer version of the same GPLv2 license WordPress is using, one which
protects your interests even more.

= I have sites using other scripts / CMS. Can I use your software with them? =

You can use the CLI or standalone version, providing the database connection information yourself.

== Changelog ==

[CHANGELOG]

== Upgrade Notice ==

Please consult our documentation for any information relevant to updates.