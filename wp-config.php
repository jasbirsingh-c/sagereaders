<?php
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
define('DB_NAME', 'sagereaders');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', '');

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
define('AUTH_KEY',         '<).g|e5C7}8oL jH-w7Ik@qGCXSyU:T}dh@kgh1.Za%|~9}<xk-ODs|mUH+LO*8&');
define('SECURE_AUTH_KEY',  'd1+9hcd|Xj<iWlMPh~zs|]cJ=nF?}izR+,K1H](>!J2g#R;/Ki@+29y}+bp1vEL-');
define('LOGGED_IN_KEY',    '2g[~C+uxZBOCF$c_TW4L7+f#.GhdF?M>-v:iZBdt<af[r.LJLbD}17([-BwS3Hh<');
define('NONCE_KEY',        '4oLrL.|/hm|$kG!#$M*nC*|^uD%a`^ /2T>gu]--$72B.*$]aO-|6lo7_wO6|g&i');
define('AUTH_SALT',        '4AWS+>O]pnZ/$Pl*tCI^-.?NSwrah}cJ%SzLZQ0I|P3}-:<MdZSKJ50h3xdMNp@y');
define('SECURE_AUTH_SALT', 'B)Xo9l@Q4H|5%-1GH}mTtLyt3x~Q0{FOpO]7g**3?Y*-:iLee<; vZUL:nF$RSe{');
define('LOGGED_IN_SALT',   'oHQkXllC W4^UQOe5JQ81-vq[`&0#+lHa,#`DfIH+LC!>kXp5|C61TC){dTmZ+%H');
define('NONCE_SALT',       'nxfMuDM4A9Tb~+JQ3$G2 (Yb)-f9Ww*o&XOaKVn)&%jx+}CbX{Zgpl?^-u)?g{dg');

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
