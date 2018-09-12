--
-- Create a test database
--
CREATE DATABASE `replacetest` DEFAULT COLLATE utf8mb4_unicode_520_ci;
GRANT ALL PRIVILEGES ON `replacetest`.* to 'replace'@'localhost' IDENTIFIED BY 'Repl@c3';
