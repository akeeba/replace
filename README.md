# Akeeba Replace

A database replace script for WordPress

## Prerequisites

In order to build the scripts in this distribution you need to have the following tools:

- A command line environment. bash under Linux / Mac OS X works best. On Windows you will need to run most tools using an elevated privileges (administrator) command prompt.

- The PHP CLI binary in your path

- Command line Subversion and Git binaries(*)

- PEAR and Phing installed, with the Net_FTP and VersionControl_SVN PEAR packages installed

- libxml and libxslt tools if you intend to build the documentation PDF files

You will also need the following path structure on your system

- buildfiles	Akeeba Build Tools (https://github.com/akeeba/buildfiles)

You will need to use the exact folder names specified here.

## Useful Phing tasks

All of the following commands are to be run from the build subdirectory.

1. Relinking internal files

   This is required the first time you clone a repository.

        phing link

1. Creating a dev release installation package

   This creates the distributable ZIP packages inside release directory.

        phing git

