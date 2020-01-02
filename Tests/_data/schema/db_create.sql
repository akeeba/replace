/*
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

--
-- Create a test database
--
CREATE DATABASE `replacetest` DEFAULT COLLATE utf8mb4_unicode_520_ci;
GRANT ALL PRIVILEGES ON `replacetest`.* to 'replace'@'localhost' IDENTIFIED BY 'Repl@c3';
