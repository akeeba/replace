<?php
/**
 * @package   AkeebaReplaceTests
 * @copyright Copyright (c)2018 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @author    Nicholas K. Dionysopoulos
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 *
 */

/**
 * This file has commented out sections with "old" values which *FOLLOW AFTER* the correct, "new", values. This is very
 * unlikely but we have to anticipate the worst.
 */

/** The name of the database for WordPress */
define('DB_NAME', 'wpdb');//Comment, slashed and run together
define('DB_USER', 'wpuser');    // Comment, slashed, tab
define('DB_PASSWORD', 'wppass'); # Comment, hash sign, with leading space
define('DB_HOST', 'localhost'); // ' insidious comment with single quotes '
define('DB_CHARSET', "utf8mb4"); /* Block comment, multiline
 Like this */
define('DB_COLLATE', ''); /** Block comment, single line */

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_'; // '; More comments!

/**
$table_prefix  = 'not this one, mate';
 */

/**
define('DB_NAME', 'NOPE!');//Comment, slashed and run together
define('DB_USER', 'NOPE!');    // Comment, slashed, tab
define('DB_PASSWORD', 'NOPE!'); # Comment, hash sign, with leading space
define('DB_HOST', 'NOPE:NOPE'); // ' insidious comment with single quotes '
/**/

//define('DB_CHARSET', "NOPE!");
//define('DB_COLLATE', 'NOPE!'); /** Block comment, single line */


/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the Codex.
 *
 * @link https://codex.wordpress.org/Debugging_in_WordPress
 */
define('WP_DEBUG', false);

/* That's all, stop editing! Happy blogging. */