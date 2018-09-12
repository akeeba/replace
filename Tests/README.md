# Unit Tests for Akeeba Replace

All aspects of the Akeeba Replace library (the `src/lib` directory) are tested using PHPunit. This document explains how to prepare your environment to execute the Unit Tests.

Akeeba Replace unit tests are prepared using a Linux machine. That is to say, our canonical testing environment is standardized on Linux, namely the [Akeeba Vagrant Box](https://github.com/akeeba/vagrant). However, you should be able to execute them on any Operating System, including macOS and Windows. We occasionally do that as well.

Before you begin doing anything you need to have PHP 5.6, 7.1 or 7.2 installed on your computer and available from the command line. If you are on Linux install the PHP package provided by your distribution. You will also need its mysqli (or mysqlnd) and pdo packages. On macOS and Windows it's probably easier to use MAMP or a similar prepackaged PHP distribution. 

## Preparation

### Install dependencies through Composer 

Make sure that you have [Composer](https://getcomposer.org/) installed in your environment. If not, follow the instructions on their site.

Afterwards, go to the root of the repository and run:

```bash
composer install
```

> **IMPORTANT!** Do NOT run `composer update`. We ship the `composer.lock` file with the exact dependency versions we have confirmed that work for Unit Testing. Therefore `composer install` installs these exact dependencies that we are using ourselves.

### Create a test database

Some tests are integration rather than pure unit tests. These tests need to talk to a real MySQL database. You will need to create a MySQL database and user. For example, run the following as MySQL's root user:

```mysql
CREATE DATABASE `replacetest` DEFAULT COLLATE utf8mb4_unicode_520_ci;
GRANT ALL PRIVILEGES ON `replacetest`.* to 'replace'@'localhost' IDENTIFIED BY 'Repl@c3';
```

This creates a database with the following parameters:

* Database name: replacetest
* Username: replace
* Password: Repl@c3

Note that the database is empty. The test tables are created and populated automatically during the unit tests' execution.

### Create a .env file

We use a standard `.env` environment file to communicate crucial parameters to the unit testing framework. 

Go to the `Tests` folder and copy the `.env.example` file to `.env` and then edit the file. You need to change the database connection information to match the test database you created in the previous step.

> **Heads up!** The `.env` and `.env.example` files will not show up if you're on Linux, macOS, or pretty much any OS other than Windows. All files whose names begins with a dot are hidden. It's best to use the command line to copy these files.

> **Windows gotcha** Creating a file whose name starts with a dot is stupidly complicated in Windows. Copy the `.env.example` file into a new file named `.env.` (note there are dots before _and_ after the env). This magically creates the file as `.env` without the trailing dot, i.e. what we were trying to do. Big kudos to @SwiftOnSecurity on Twitter for that tip.  

## Running the Unit Tests

From the `Tests` directory run

```bash
php ../vendor/phpunit/phpunit/phpunit -c ../phpunit.xml
```