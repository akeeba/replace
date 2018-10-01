What is this folder?
================================================================================

This folder is where Akeeba Replace will store up to three different kinds of
information when you tell it to run a replacement:

* Replacements SQL in files such as SOMETHING_replace.sql

  The database actions necessary to apply the replacements you have configured.
  This file will not be generated if you have disabled the “Export as a SQL
  file” option.

* Backup SQL in files such as SOMETHING_backup.sql

  The database actions necessary to undo the replacements you have configuraed.
  This file will not be generated if you have disabled the “Take backups”
  option.

* Log in files such as SOMETHING.log

  A running log detailing what is taking place when you run the replacements.
  The detail level of the contents of this file are affected by the “Log
  level” option.

File naming
================================================================================

The “SOMETHING” part corresponds to the date and time you started running
the replacements, as well as your site's timezone.

For example, if you started running replacements on October 1st, 2018 at
1:43:21 pm Pacific Daylight Time on a site whose timezone is set to America,
Los Angeles then the name of the files will begin with 20181001_134321_pdt.

However, if you started the same replacement on exactly the same second on a
site whose timezone is set to Europe, London then the name of the files would
begin with 20181001_204321_europe_london (or something like that, depending on
your exact setting in WordPress' Options and how PHP understands it).

Multi-part files
================================================================================

Some servers have limits on how much data you can put into a single file when
the file is created by a script. To work around this limitation on these servers
the SQL and log files may be split in parts.

For SQL files, the first file has the extension .sql, the next file has the
extension .s01, the next one .s02 and so on and so forth. When you are trying
to apply them  on your database using phpMyAdmin, Adminer, etc please remember
to apply every file at the order specified above.

For log files, the first file has the extension .log, the next file has the
extension .l01, the next one .l02 and so on and so forth. When you are trying
to view the logs please read each file at the order specified above.

Restoring backups
================================================================================

If your site becomes inaccessible after applying a replacement come back to this
folder and locate the latest backup archive. You can order the files by name
descending to find the files from the latest run. Locate the backup .sql file
(and any .s01, .s02 etc parts it may consist of). Download them to your
computer. You can then apply them to your site's database using phpMyAdmin or
any other database management tool your host has provided you.

Advanced users may of course use the mysql command line tool to restore these
files. If you are on a local server you can use a database management
application such as HeidiSQL (Windows, Linux), Sequel Pro (macOS) and so on.

CAVEAT: If your data became corrupt as the result of changing the collation of
your database tables, restoring the automatic backups will NOT help you. This is
due to the way collations work in the database server. That's why we tell you to
always take a full database backup before making any collation changes to your
database tables. You can use our free of charge software Akeeba Backup Core for
WordPress to do that and even more.

Security concerns about this folder
================================================================================

This folder will contain privileged information about your site. It should not
be accessible over the web. We have included a .htaccess and a web.config file
which make sure of that on web servers running Apache 1.3 or later, Litespeed or
IIS 7 or later. For other web servers you should contact your host and ask for
the best way to make this folder inaccessible over the web.