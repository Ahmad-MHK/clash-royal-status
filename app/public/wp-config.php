<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the web site, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * Localized language
 * * ABSPATH
 *
 * @link https://wordpress.org/support/article/editing-wp-config-php/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'local' );

/** Database username */
define( 'DB_USER', 'root' );

/** Database password */
define( 'DB_PASSWORD', 'root' );

/** Database hostname */
define( 'DB_HOST', 'localhost' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8' );

/** The database collate type. Don't change this if in doubt. */
define( 'DB_COLLATE', '' );

/**#@+
 * Authentication unique keys and salts.
 *
 * Change these to different unique phrases! You can generate these using
 * the {@link https://api.wordpress.org/secret-key/1.1/salt/ WordPress.org secret-key service}.
 *
 * You can change these at any point in time to invalidate all existing cookies.
 * This will force all users to have to log in again.
 *
 * @since 2.6.0
 */
define( 'AUTH_KEY',          ';h(2*594qKJVa3$MxoQ[%NR-Yz|ax.K-w;D>h?:viau zHl;3A8pGe^a_NSgv={0' );
define( 'SECURE_AUTH_KEY',   '3nm?Jswt|!VWT&Mu6qmnrkc3&rMqcFpWh(.Tp*jfXnMe1SCk;wf2:59ROK&&.qiI' );
define( 'LOGGED_IN_KEY',     '0w{y}/S+_e-Q`?%^kP{ePN(XEkj1G%#7mu[JU?K@{9EKInfV,vjj5k{<AhoUnM8W' );
define( 'NONCE_KEY',         '*g CKV&.?+$A65=wWiK`8/DzF}6;6*h~D(.Oub9ReFlTS#yTC$dthM7`nQ#7 6R.' );
define( 'AUTH_SALT',         '#*-+CNN&M5OTk7B;S2a:!da!XQFk*V1MX/XVsK|Q]7FCQP|BWhvT+%xr1Zg5@i<.' );
define( 'SECURE_AUTH_SALT',  '|BH13`W`0 :2)i!LRSC0> b@{41dkoGrD>G3-oV#RNW7Ib1m&Oau:fddm`i$k{(N' );
define( 'LOGGED_IN_SALT',    'It,:Mvyrqz*oaBxT3g]d)_NVSDMl%cG#$Y;hHvTOKzfI,8d)#z+T7*h[Kk)P`UVl' );
define( 'NONCE_SALT',        ',r|&q]bvM6bXrmf4Q<hYIM*04j7vNFBV;s^u:WIH|/FUFrxC*qpHORu6B*WW5Qx ' );
define( 'WP_CACHE_KEY_SALT', '3m#!9~`.%/>T<|8^IgSLl-O=<_=TFa<v;h.PQ`[jvgs?_!.0p{7h(uP<dq=_ #lp' );


/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 */
$table_prefix = 'wp_';


/* Add any custom values between this line and the "stop editing" line. */



/**
 * For developers: WordPress debugging mode.
 *
 * Change this to true to enable the display of notices during development.
 * It is strongly recommended that plugin and theme developers use WP_DEBUG
 * in their development environments.
 *
 * For information on other constants that can be used for debugging,
 * visit the documentation.
 *
 * @link https://wordpress.org/support/article/debugging-in-wordpress/
 */
if ( ! defined( 'WP_DEBUG' ) ) {
	define( 'WP_DEBUG', false );
}

define( 'WP_ENVIRONMENT_TYPE', 'local' );
/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
