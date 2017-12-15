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
define('DB_NAME', 'museumfirepresents');

/** MySQL database username */
define('DB_USER', 'root');

/** MySQL database password */
define('DB_PASSWORD', 'root');

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
define('AUTH_KEY',         'fr~U]Pc2#aZQ]cP^5]NxHC>#J=INu$-vF6Y!e6?,AJFWXWxT~^Ma^8Ckj@;aua}0');
define('SECURE_AUTH_KEY',  'Rvl%([eG] ]]/5,HeuIQ(W}zJ<YHNlQhGHx2|EO2Emi5g5n^z8Old4x{?v[Uh,Wf');
define('LOGGED_IN_KEY',    '|qRov}bJ37GWaHXuJR@Yu{;WJ%/Z+1X~(xcI je}x,{<YGIPEG5lV8vg_&Ur?,7i');
define('NONCE_KEY',        '+a?HUFJhT]eM0,/YKQ- w$@fe&)+#2-ka.uE!TKXB2YdECJ~i@m$/ V&FtM5+c95');
define('AUTH_SALT',        'HeOG|SxX9oo=y[N455e_,RT<}o`i.sO{H%<%fLqJa?dUs*k+$;mvHY4%B)EkGpe`');
define('SECURE_AUTH_SALT', 'V:P|6_h+^Ph@b;Jv}ovXG7(!S86jn(D71v{yg~Ew%Sqw!q9@`72?eJ%OMqO~HB^K');
define('LOGGED_IN_SALT',   '`aji[Gzz_qt`E>#5>Z{deFC}:$KeGEa8.Ohi2GS6vY3Ub1yVcVV ~ZE;%HSA(QEw');
define('NONCE_SALT',       '9n[c[$w)o(!J|=b7]JW{gG/WRLMz}?YQJzvU<z(!p%)4[Nz@yyvRE/S]iXf.Es8 ');

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
