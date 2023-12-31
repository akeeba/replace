<?php
/**
 * @package   AkeebaReplace
 * @copyright Copyright (c)2018-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU/GPL v3 or later
 */

/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the
 * installation. You don't have to use the web site, you can
 * copy this file to "wp-config.php" and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * MySQL settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://codex.wordpress.org/Editing_wp-config.php
 *
 * @package WordPress
 */

// ** MySQL settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define('DB_NAME', 'wpdb');

/** MySQL database username */
define('DB_USER', 'wpuser');

/** MySQL database password */
define('DB_PASSWORD', 'wppass');

/** MySQL hostname */
define('DB_HOST', 'localhost');

/** Database Charset to use in creating database tables. */
define('DB_CHARSET', 'utf8mb4');

/** The Database Collate type. Don't change this if in doubt. */
define('DB_COLLATE', '');

/**#@+
 * Authentication Unique Keys and Salts.
 *
 * Change these to different unique phrases!
 * You can generate these using the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}
 * You can change these at any point in time to invalidate all existing cookies. This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define('AUTH_KEY',         'y6}6)T#;B`}7~bvb2{&>OcOX,cRQ?MUjBc%omhF=1m5R<SHXv%XIS704*+q]~oW{');
define('SECURE_AUTH_KEY',  ':5<2zB1@zjV *|T Zh;-uObd]oxMQ?u$7P]Q~7)@37:zF sO=MGXcX w}R,*3%Vq');
define('LOGGED_IN_KEY',    '87{2xod`IByX4]QD&2iP P}?.7_]JL6p48CeND_=NswDKR{*`Of(AE9{42)X(zl>');
define('NONCE_KEY',        'oy2f;!sBfbq+?sKk-9n,evFJ0sR_$oOka>vko7#ztDEL1O,^U~>c!~,>Y@NuM1md');
define('AUTH_SALT',        '^-K;sbA{>weGe0U=eP]A43 #o}]~-3+ 82bR[%4V3~@}ccnM(b.mG|4j~X[}RvC@');
define('SECURE_AUTH_SALT', '+E+QuqC%ayRVoo^sINx-WL`jo$9?s@SuZqgTe{4D/%9c~/Nh?x$d0Z#vCqvey^K`');
define('LOGGED_IN_SALT',   '2ZS>H0*o3o2(*B($nIXX!m+yK6u3kM3^?NUiCxyss)Ge1NYf2QvJfJt:mG! ^ #N');
define('NONCE_SALT',       '@*WG.&X9%P|`zMW}F+$[)@.j`=C@P4^rBHI$$]|,oH*H)rL9xg:`at}(p#U}tgU|');

/**#@-*/

/**
 * WordPress Database Table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix  = 'wp_';

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

/** Absolute path to the WordPress directory. */
if ( !defined('ABSPATH') )
	define('ABSPATH', dirname(__FILE__) . '/');

/** Sets up WordPress vars and included files. */
require_once(ABSPATH . 'wp-settings.php');
