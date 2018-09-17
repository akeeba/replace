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

### Can I use this to move my site to a different domain / subdomain?

Yes, you can.

On top of using Akeeba Replace you _may have to_ update your `wp-config.php` file and your `.htaccess` file. If unsure, [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html), even its free of charge Akeeba Backup Core edition, will do that for you with less fuss.

### Can I use this to move my site to a different host?

Yes, for the most part. You will need to use the same table name prefix as your original site. You do NOT have to use the same database name or domain / subdomain.

On top of using Akeeba Replace you _must_ update your `wp-config.php` file and your `.htaccess` file. If unsure, [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html), even its free of charge Akeeba Backup Core edition, will do that for you with less fuss.

### Can I use this to change my database name or prefix?

It is not recommended: there is a much higher than usual risk of failure. This requires more than simple mass replacement of database contents. The trickiest part is cross-references between tables/views. If they are not done in the right order your database will error out. Therefore, the best way to achieve this is by using backup software which allows you to restore to a different database and with a different prefix. Our [Akeeba Backup for WordPress](https://www.akeebabackup.com/products/akeeba-backup-wordpress.html), even its free of charge Akeeba Backup Core edition, will do that without a problem - even for WordPress multisite installations.

### It doesn't work

Finding a solution to a computer-related problem requires applying the scientific method: make a hypothesis based on available evidence and test it. Therefore, you need evidence. "It doesn't work" unfortuantely doesn't give much to work with except the very understandable fact that you are frustrated. Set your frustration aside and try to get a bit more specific. Describe what you did, what you expected and what happened. This will help isolate your issue down to a few, specific possibilities. The more specific you can get, the fastest and more accurately you can get a solution to your problem.

For ideas of what could possibly go wrong and how to address it please look below.

### My database is all messed up

This is a risk you face with mass replacement. Either by a typo or an edge case not previously encountered, the replacement may fail in ways which cause your site to misbehave. That's why Akeeba Replace takes automatic backups which you can restore with phpMyAdmin, Adminer or whatever your host provides - even if you cannot access your site.

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