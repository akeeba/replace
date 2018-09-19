# Akeeba Replace

A database replace script for WordPress.

[Download from our GitHub releases](https://github.com/akeeba/replace/releases).

## Under Development

This script and plugin is currently under development. It's not ready to be used (yet). Stay tuned!

## Main features

### Currently implemented

> This section will be filled in as we develop each feature, also noting the version it was made available in.

### Planned features

* WordPress plugin (with WP-CLI integration), standalone script and CLI tool.
* Automated tests in the library to ensure code quality.
* Quick presets for moving sites between domains and / or hosts.
* Mass replace the contents in your database.
* Replace with plain text strings or regular expressions.
* Safe replacement of serialized data.
* Do it live on your database, make a dry run or export to SQL for applying it with external tools.
* Automatic backups of the data being replaced with restore option.
* Change the collation and storage engine of the database tables and / or columns.


## Frequently Asked Questions (FAQ) and basic troubleshooting

### Can I use this to do mass replacement of content on my database?

Yes, you can. It's literally what it does.

### Can I use regular expressions to replace data?

Yes. You can use either regular expressions or plain text replacements. Just tell it which one you want.

### Will it replace serialized data?

Yes. Akeeba Replace is designed to replace serialized data _safely_, without going through `unserialize()`. As a result it prevents any unwanted actions from taking place if a developer has serialized PHP objects with a magic `__wakeup` or `unserialize()` method which would run as soon as `unserialize()` is called.

Akeeba Replace also supports serialized-data-in-serialized-data, an infinite number of levels deep. Beware, though! Just like the movie Inception, the deeper a level you go the slower things become.

### Will it work on really big databases, like millions of rows big?

Yes. Unlike other mass database replacement scripts, Akeeba Replace will step through your database in a time-safe manner. 

Please note that in some cases this could, in fact, break your site since the database in progress of being replaced is not consistent enough for WordPress to load the next replacement step. In this case please use the Export to SQL feature to export all the actions to a SQL file (instead of running the actions directly on your database) and then import the SQL file with phpMyAdmin, Adminer, Bigdump, the mysql command line utility or any method provided by your host.

### Can I export the actions to SQL to reply them to a live site later?

Yes. You can export to SQL on top of or instead of running the actions against your database.

### Can I use this to move my site to a different domain / subdomain?

Yes, you can.

On top of using Akeeba Replace you _may have to_ update your `wp-config.php` file and your `.htaccess` file. If unsure, [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html), even its free of charge Akeeba Backup Core edition, will do that for you with less fuss.

### Can I use this to move my site to a different host?

Yes, for the most part, i.e. for what concerns just the database contents of your site. You can use Akeeba Replace either before or after the move to update the domain name and location of your site on disk. If you have a multisite installation you will need to do that for all of the URLs and / or subdomains of the blog network. 

 In many cases you will need to use the same table name prefix as your original site (see the next question for an explanation). You do NOT have to use the same database name or domain / subdomain when transferring your site.

Moreover, you will have to edit your `wp-config.php`, `.htaccess` / `web.config` and possibly any `php.ini` and `.user.ini` configuration files on the new host yourself.

Please note that all of these actions can be performed automatically and more easily using [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html). Even its free of charge Akeeba Backup Core edition will do that when restoring a backup -- even for WordPress multisite installations.

### Can I use this to change my database name or database table name prefix?

Maybe, but it's not recommended. This requires more than simple mass replacement of database contents.

In both cases you need to update your configuration files, e.g. `wp-config.php`. This cannot be done by Akeeba Replace.

Changing your database name requires moving your data to a new database. This cannot be done by Akeeba Replace.

Your database may contain views, triggers or procedures which reference other tables. Or it may have tables with database-level foreign key relationships between tables. Dealing with these requires changing the structure of your database (e.g. create views afresh). Moreover, they have to be processed in a specific order which doesn't cause database errors because of cross-references. This is a very complicated process Akeeba Replace will not handle. 

All of the above can be done very easily with our [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html) product. Even its free of charge Akeeba Backup Core edition, will perform these actions without a problem when restoring a backup -- even for WordPress multisite installations.

### It doesn't work

That's a shame and we'd like to help you make it work. We need a bit more information to understand what is going on and help you. Moreover, if it's a bug in our code, as opposed to a configuration error or a hosting issue, we want to permanently fix it. We want to publish software that works. Otherwise, what's the point?

We understand that you are frustrated it didn't work -- it's a perfectly valid, human reaction. Please try to set your frustration aside and be a bit more specific so we can help you. Describe what you did, what you expected and what happened. This will help isolate your issue down to a few, specific possibilities. The more specific you can get, the fastest and more accurately you can get a solution to your problem.

For ideas of what could possibly go wrong and how to address these issues please look below. If you cannot find a solution yourself please give us a copy of the log file with the Log Level set to "Debug" and a short description of what you did and why you believe it doesn't work. Kindly keep in mind that our time is finite, we may even live in different ends of the world than you and we do our best trying to reply to everyone. As a result support may be a bit slow.

Please note that we don't offer phone or instant messaging assistance on purpose, not because we don't care about you. In fact, the only people who will respond to your technical support request are the developers themselves. Moreover, based on our experience of nearly two decades of doing technical support we have determined that no issue trivial enough to be answered with a short chat is unsolvable by just reading the documentation. Issues which do require a developer to solve requires concentration on our part and most likely further troubleshooting steps you will be told to follow, or even a code fix. None of these are well-suited for instant communication.

### My database is all messed up

This is a risk you face with mass replacement. Either by a typo or an edge case not previously encountered, the replacement may fail in ways which cause your site to misbehave. That's why Akeeba Replace takes automatic backups as regular SQL files. You can restore the backups with phpMyAdmin, Adminer, Bigdump, the mysql command line utility or any other method provided by your host - even if you cannot access your site.

If you believe the problem was not due to a typo on your part, please let us know. Remember, we don't put bugs out of malice, indifference on incompetence. Mass replacing data, including serialized data, in a live database is a complex problem which is very sensitive to hosting configuration and the data present in the database. While we make every effort humanly possible to anticipate possible issues there are always edge cases we've not seen before and which we couldn't have reasonably anticipated. Please, do let us know. Give us enough information to reproduce it and we can anticipate it in the next version of our software.

### It didn't replace everything

To prevent accidental unwanted behaviour, Akeeba Replace will only replace exactly what you tell it to. This may not always be what you meant to do. For example, a URL `http://www.example.com` may be recorded in the database also as `http:\/\/www.example.com` (also known as "with escaped slashes"). Make sure you create replacement pairs for everything you need to replace. 

If, however, you got an error while replacing please refer to our documentation for troubleshooting instructions.

### It replaced more than I told it to

Remember that computers are really good at doing what they are told, not so much what we meant to tell them to do. If you try to replace `cat` with `dog` the computer will also replace `caterpillar` with `dogterpillar` and `uncategorized` with `undogtegorized`. That is, it doesn't look for words, it looks for bits of text and replaces them blindly.

You may have to use regular expressions to replace small words which are common parts of other words such as `cat`, `help`, `are` and so on. 

If you want full control, use the Export to SQL feature and look at the generated file. It will tell you what is being replaced where. You can even edit the file to remove replacements you didn't want to. 

### It did not do anything on my database

Did you choose the dry-run option and / or told it to only export to a SQL file? Dry-run literally tells it to just go through the motions without executing anything. Only exporting to a SQL file will do exactly that: export the actions to be taken in a SQL file without running them against your database.

If, however, you got an error while replacing please refer to our documentation for troubleshooting instructions.