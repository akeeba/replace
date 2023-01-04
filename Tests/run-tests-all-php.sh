#!/usr/bin/env sh
##
# @package   AkeebaReplace
# @copyright Copyright (c)2018-2023 Nicholas K. Dionysopoulos / Akeeba Ltd
# @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
#

# Akeeba Replace - Unit Tests
#
# This script runs the Unit Tests against all interesting versions of PHP and generates a report.
# This script is meant to run inside the Akeeba Vagrant Box.
#
# Please note that PHP 5.4 and 5.5. are NOT currently supported since PHPUnit 5 will only run on PHP 5.6+
#

# Remove the report.txt file
if [ -f report.txt ]
then
	rm report.txt
fi

HAS_ERROR=0

echo "################################################################################" >> report.txt
echo "## Akeeba Replace -- Unit Test Report" >> report.txt
echo "################################################################################" >> report.txt
echo "" >> report.txt

echo "Running on PHP 5.6"
echo "================================================================================" >> report.txt
echo "PHP 5.6" >> report.txt
echo "================================================================================" >> report.txt
echo "" >> report.txt

/usr/bin/php56/php ../vendor/phpunit/phpunit/phpunit -c ../phpunit.xml ${@} >> report.txt  2> /dev/null

if [ $? -ne 0 ]
then
	HAS_ERROR=1
fi

echo "" >> report.txt

echo "Running on PHP 7.0"
echo "================================================================================" >> report.txt
echo "PHP 7.0" >> report.txt
echo "================================================================================" >> report.txt
echo "" >> report.txt

/usr/bin/php70/php ../vendor/phpunit/phpunit/phpunit -c ../phpunit.xml ${@} >> report.txt  2> /dev/null

if [ $? -ne 0 ]
then
	HAS_ERROR=1
fi

echo "" >> report.txt

echo "Running on PHP 7.1"
echo "================================================================================" >> report.txt
echo "PHP 7.1" >> report.txt
echo "================================================================================" >> report.txt
echo "" >> report.txt

/usr/bin/php71/php ../vendor/phpunit/phpunit/phpunit -c ../phpunit.xml ${@} >> report.txt  2> /dev/null

if [ $? -ne 0 ]
then
	HAS_ERROR=1
fi

echo "" >> report.txt

echo "Running on PHP 7.2"
echo "================================================================================" >> report.txt
echo "PHP 7.2" >> report.txt
echo "================================================================================" >> report.txt
echo "" >> report.txt

/usr/bin/php72/php ../vendor/phpunit/phpunit/phpunit -c ../phpunit.xml ${@} >> report.txt  2> /dev/null

if [ $? -ne 0 ]
then
	HAS_ERROR=1
fi

echo "" >> report.txt

echo "Done."

if [ $HAS_ERROR -eq 1 ]
then
	echo ""
	echo "ERRORS DETECTED!"
	echo ""
	echo "Run:"
	echo ""
	echo "less report.txt"
	echo ""
	echo "to view the report of the Unit Test run."

	exit 1;
else
	echo "All good! No errors detected."

	rm report.txt
fi

