# Akeeba Replace

A database mass content replace script, primarily focusing on WordPress.

[Download from our GitHub releases](https://github.com/akeeba/replace/releases).

## Under Development

This script and plugin is currently under development. We have no public release (yet). Stay tuned!

## Main features

### Currently implemented

* WordPress plugin 
* Mass replace the contents in your database.
* Automated tests in the library to ensure code quality.
* Safe replacement of serialized data.
* Automatic backups of the data being replaced.
* Do it live on your database, make a dry run or export to SQL for applying it with external tools.
* Change the collation and storage engine of the database tables and / or columns.

### Planned features

* WP-CLI integration
* Quick presets for moving sites between domains and / or hosts.
* Replace with plain text strings or regular expressions.
* Restore the database backup through the plugin.
* WordPress multisite support.

## Frequently Asked Questions (FAQ) and basic troubleshooting

### Can I use this on multisite installations?

Not yet. This is a feature scheduled for Milestone 2.0. 

### Can I use this to do mass replacement of content on my database?

Yes, you can. It's literally what it does.

### Can I use regular expressions to replace data?

Yes. You can use either regular expressions or plain text replacements. Just tell it which one you want.

### Can I use it on non-MySQL databases?

Only if your database implements the MySQL dialect of SQL, e.g. if you are using MariaDB or Percona. This applies to database servers hosted by your host, on the same or a different server, or databases hosted on third party providers such as Amazon RDS (again, only with MySQL or MariaDB databases).

You will not be able to use Akeeba Replace with Microsoft SQL Server, PostgreSQL etc. These speak a different dialect of SQL which is not compatible with Akeeba Replace or even WordPress itself.

### Why use Akeeba Replace instead of another search and replace software?

We have experience building backup and recovery software for web sites since 2006. Based on that experience we can deal with several issues not tackled by other software such as WP-CLI or Search and Replace for WordPress:

* __No database is too big__. We know how to deal with very big databases without timing out, without requiring you to raise the execution time limit of PHP and without using too much memory. The only problems which cannot be overcome are those objectively outside our control: limits on the number of queries which can be executed in a limited amount of time (only controlled by your host) and having database tables with rows too big to fit in memory (well, you can't even work with this data in WordPress or its plugin which created them anyway!).
* __Partial classes are not a show-stopper__. All other solutions choke on serialized data which references PHP classes not already loaded. We don't have that problem.
* __Works with tables which lack a primary key__. Some tables lack a primary key. We figure out how to make replacement work even on these tables, without overwriting data we shouldn't be touching.
* __Works even when your WordPress administrator is inaccessible__. It doesn't matter if you can or cannot access your administrator dashboard (wp-admin). You can still use the SQL files generated by Akeeba Replace.

### Will it replace serialized data?

Yes. Akeeba Replace is designed to replace serialized data _safely_, without going through `unserialize()`. As a result it prevents any unwanted actions from taking place if a developer has serialized PHP objects with a magic `__wakeup` or `unserialize()` method which would run as soon as `unserialize()` is called. It also sidesteps the problem of some plugins' data being impossible to replace because they serialize objects of a PHP class which is normally not loaded outside the plugin.

Akeeba Replace also supports serialized-data-in-serialized-data, an infinite number of levels deep. Beware, though! Just like the movie Inception, the deeper a level you go the slower things become.

### Will it work on really big databases, like millions of rows big?

Yes. Unlike other mass database replacement scripts, Akeeba Replace will step through your database in a time-safe manner. 

Please note that in some cases this could, in fact, break your site since the database in progress of being replaced is not consistent enough for WordPress to load the next replacement step. In this case please use the Export to SQL feature to export all the actions to a SQL file (instead of running the actions directly on your database) and then import the SQL file with phpMyAdmin, Adminer, Bigdump, the mysql command line utility or any method provided by your host.

### Can I export the actions to SQL to reply them to a live site later?

Yes. You can export to SQL on top of or instead of running the actions against your database.

### Can I use this to move my site to a different domain / subdomain?

Yes, you can. You will need to provide all permutations of the site's domain name, relative path, URL and absolute path that need to be replaced in the database yourself.

On top of using Akeeba Replace you _may have to_ update your `wp-config.php` file and your `.htaccess` file. If unsure, [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html), even its free of charge Akeeba Backup Core edition, will do that for you with less fuss.

### Can I use this to move my site to a different host?

Yes, for the most part, i.e. for what concerns just the database contents of your site. You can use Akeeba Replace either before or after the move to update the domain name and location of your site on disk. If you have a multisite installation you will need to do that for all of the URLs and / or subdomains of the blog network. 

 In many cases you will need to use the same table name prefix as your original site (see the next question for an explanation). You do NOT have to use the same database name or domain / subdomain when transferring your site.

Moreover, you will have to edit your `wp-config.php`, `.htaccess` / `web.config` and possibly any `php.ini` and `.user.ini` configuration files on the new host yourself.

Please note that all of these actions can be performed automatically and more easily using [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html). Even its free of charge Akeeba Backup Core edition will do that when restoring a backup -- even for WordPress multisite installations.

### Can I use this to change my database name or database table name prefix?

Maybe, but it's not recommended. This requires more than simple mass replacement of database contents.

In both cases you need to update your configuration files, e.g. `wp-config.php`. This cannot be done by Akeeba Replace.

If you are changing your database table name prefix you have to rename all of your tables, including tables used by each site in the blog network (if you are using a multisite installation). Akeeba Replace cannot do that for you. It replaces data in the database, it does not change table names.

Changing your database name requires moving your data to a new database. This cannot be done by Akeeba Replace.

Your database may contain views, triggers or procedures which reference other tables. Or it may have tables with database-level foreign key relationships between tables. Dealing with these requires changing the structure of your database (e.g. create views afresh). Moreover, they have to be processed in a specific order which doesn't cause database errors because of cross-references. This is a very complicated process Akeeba Replace will not handle. 

All of the above can be done very easily with our [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html) product. Even its free of charge Akeeba Backup Core edition, will perform these actions without a problem when restoring a backup -- even for WordPress multisite installations.

### It doesn't work

That's a shame and we'd like to help you make it work. We need a bit more information to understand what is going on and help you. Moreover, if it's a bug in our code, as opposed to a configuration error or a hosting issue, we want to permanently fix it.

Assuming that the information on this page didn't help you find a solution to your problem we kindly ask you to help us help you. Please give us a copy of the log file with the Log Level set to "Debug" and a short description of what you did and why you believe it doesn't work. If you received any error messages please send us a screenshot or copy and paste them verbatim. If possible, please give us the URL displayed on your browser. We do NOT need your site's domain name -- you can blank it out with #'s. For example, if the URL is `https://www.example.com/mysite/wp-admin/admin.php?page=akeebareplace` you can send us the URL `https://########/mysite/wp-admin/admin.php?page=akeebareplace`.

Kindly keep in mind that our time is finite, we may even live in different ends of the world than you and we do our best trying to reply to everyone. As a result support may be a bit slow. Also kindly note that we don't offer phone or instant messaging assistance. This is for efficiency reasons; the only people who will respond to your technical support request are the developers themselves. We have to forego instant communication to ensure a high quality and reasonable response times. 

### My database is all messed up

Unfortunately this is a risk you face with mass replacement. Either by a typo or a (typically out of the ordinary) case not previously encountered, the replacement may fail in ways which cause your site to misbehave. That's why Akeeba Replace takes automatic backups as regular SQL files. You can restore the backups with phpMyAdmin, Adminer, Bigdump, the mysql command line utility or any other method provided by your host - even if you cannot access your site.

Please note that some badly written software may attempt to write data of the wrong encoding to a database column, e.g. UTF-8 data in a column with ASCII encoding. This is wrong and violates the purpose of having collations and character sets in a database. This kind of data will _always_ get corrupt on replace, no matter which software you use (including phpMyAdmin or even the mysql command line tool itself). The only solution to that problem is asking the developer of the software to fix their code. You can always submit a sample to us and we can try to see if it's possible to work around it -- sometimes _it is_ possible, but we can't promise wonders.

If you believe the problem was not due to a typo on your part or a third party software issue, please let us know. Remember, we don't put bugs out of malice, indifference on incompetence. Mass replacing data, including serialized data, in a live database is an infinitely complex problem which is very sensitive to hosting configuration and the data present in the database. While we make every effort humanly possible to anticipate possible issues there might always be edge cases we've not seen before and which we couldn't have reasonably anticipated. Please, do let us know. Give us enough information to reproduce it (ideally a copy of the affected column and the replacement rules you used) and we can anticipate it in the next version of our software.

### It didn't replace everything

To prevent accidental unwanted behaviour, Akeeba Replace will only replace exactly what you tell it to. This may not always be what you meant to do. For example, a URL `http://www.example.com` may be recorded in the database also as `http:\/\/www.example.com` (also known as "with escaped slashes"). Make sure you create replacement pairs for everything you need to replace.

Akeeba Replace will only replace data in text columns, i.e. columns of the VARCHAR, TEXT, SMALLTEXT, MEDIUMTEXT, LONGTEXT and CHAR. It will not replace data in BLOB (Binary Large OBject) columns. If you are using a plugin which abuses BLOB columns to store text data please contact its developer and ask them to buy a book on MySQL, or even an introductory book on database design, then change their column to a suitable text type (hint: LONGTEXT is the equivalent to BLOB which is safe for text data). This is not sarcasm. It's a practical concern. BLOBs do not carry text encoding information, therefore they are _unsuitable_ for storing text data. While you _can_ try to read them as arbitrary text data, if you don't guess the encoding right you will be reading corrupt data. Corrupt data in means corrupt data out, therefore your database would be all corrupt now. Oops. That's why all database servers offer text column types: they carry the text encoding information with them to prevent data corruption!

If, however, you got an error while replacing please refer to our documentation for troubleshooting instructions.

### It replaced more than I told it to

Remember that computers are really good at doing what they are told, not so much what we meant to tell them to do. If you try to replace `cat` with `dog` the computer will also replace `caterpillar` with `dogterpillar` and `uncategorized` with `undogtegorized`. That is, it doesn't look for words, it looks for bits of text and replaces them blindly.

You may have to use regular expressions to replace small words which are common parts of other words such as `cat`, `help`, `are` and so on. 

If you want full control, use the Export to SQL feature and look at the generated file. It will tell you what is being replaced where. You can even edit the file to remove replacements you didn't want to. 

### It did not do anything on my database

Did you choose the dry-run option and / or told it to only export to a SQL file? Dry-run literally tells it to just go through the motions without executing anything. Only exporting to a SQL file will do exactly that: export the actions to be taken in a SQL file without running them against your database.

If, however, you got an error while replacing please refer to our documentation for troubleshooting instructions.