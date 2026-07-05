<?php
/**
 * The base configuration for WordPress
 *
 * The wp-config.php creation script uses this file during the installation.
 * You don't have to use the website, you can copy this file to "wp-config.php"
 * and fill in the values.
 *
 * This file contains the following configurations:
 *
 * * Database settings
 * * Secret keys
 * * Database table prefix
 * * ABSPATH
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/
 *
 * @package WordPress
 */

// ** Database settings - You can get this info from your web host ** //
/** The name of the database for WordPress */
define( 'DB_NAME', 'dev' );

/** Database username */
define( 'DB_USER', 'dev' );

/** Database password */
define( 'DB_PASSWORD', '123456789' );

/** Database hostname */
define( 'DB_HOST', '192.168.5.3' );

/** Database charset to use in creating database tables. */
define( 'DB_CHARSET', 'utf8mb4' );

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
define( 'AUTH_KEY',         'UbJ(l6L&#=}59xXK0dGiYXN)$l[}Mik$J9d8<or~fAoYiN]* |.5WC*2/U<bnK8&' );
define( 'SECURE_AUTH_KEY',  '~!C;{w_SKy>ogW5x:20mK:.73({}mVjfa)%:2ng~?HTwdvX-cau&ynk2V 45`ujH' );
define( 'LOGGED_IN_KEY',    'q#?B>J%g<YpqbW[Ey+@ |W!4jVN}&%&Jp0,E&, dkHFS 8VOO_N>/XH_IkqlKp}6' );
define( 'NONCE_KEY',        ' 84p:2Yfd#(`Zk`$sx~n5Q<ERw7:N3[)I6.r+d=K--h}Wxyo%~_xAj&q7IC^8nwj' );
define( 'AUTH_SALT',        '^YNu3w,Pvv]@g>XS9eGQMyyJ`qfa$]rJ+Lqxs BU^XG(lX~.p-BS2j%@8/9Ht1WB' );
define( 'SECURE_AUTH_SALT', '~g:2`D6k0EH^@<a>_*psX5a-yD5+;&u$tA05!*u)uk8?nCn&LcLaL,(*vjhw5>+1' );
define( 'LOGGED_IN_SALT',   'TVZv`(6JrTF^oM?XGXtU_RMA4.)kI0?oTBO*L3m6a0N#d=/q$HMELGF@QZ!Sp-W_' );
define( 'NONCE_SALT',       'vtv!r_YQECKJv0p8-5bMrvoA~@YA~r=Pz}3eik^ [hGhUpU.2u!Di2e%?C72kOW^' );

/**#@-*/

/**
 * WordPress database table prefix.
 *
 * You can have multiple installations in one database if you give each
 * a unique prefix. Only numbers, letters, and underscores please!
 *
 * At the installation time, database tables are created with the specified prefix.
 * Changing this value after WordPress is installed will make your site think
 * it has not been installed.
 *
 * @link https://developer.wordpress.org/advanced-administration/wordpress/wp-config/#table-prefix
 */
$table_prefix = 'wp_';

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
 * @link https://developer.wordpress.org/advanced-administration/debug/debug-wordpress/
 */
// define( 'WP_DEBUG', false );
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
define('WP_DEBUG_DISPLAY', true);

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
