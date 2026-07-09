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
define( 'DB_PASSWORD', 'dev' );

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
define( 'AUTH_KEY',         '6f&C{2#]MWvgQ>T!em+19s66x2(0Eb~s`4Xzk8G++b<]M`H$mV=NQDPT$%&gbbrI' );
define( 'SECURE_AUTH_KEY',  'FR;yZi$C(rz0e! JhC(*b^eg#!B=>JTWzkIp.^.b7|;:!:(li8N~Ck0NVk{F>?RN' );
define( 'LOGGED_IN_KEY',    'oLDG6SIp( qha<(N*y)j=Drk>B_g%#G=)l?pLf1cP|+6v~fxQ]o~]wmTa})};;CD' );
define( 'NONCE_KEY',        'AiNQ8T6OJ-|H[YBZ>$<0-)v<I}-W;giPs.3w-@VHFvO6b8kMI_YmXYSRubflH?}4' );
define( 'AUTH_SALT',        '?<*.x F?WIvd#;OGtOqSzj)[s=`C_:G8fm#;HMLC|*Y<q+fLnaR1hv*.E@dIk{5@' );
define( 'SECURE_AUTH_SALT', '%~de&uzTWS!,05BfBxioLk&Xh6Z3=$QJ`8[pH_w[)>Uyf_8~qM|Jn0F>~@IZLDT.' );
define( 'LOGGED_IN_SALT',   'Nr%0zf_okdM>)R&)=sH1geM3HOOo7.XDUlvi~?VcP/}*X9/2UuAk8OU9`/mniab^' );
define( 'NONCE_SALT',       'g8nywVRBjSSHc>t<Z$d%WlQ8X:ge=fBt@%N^.wp#Wn.:&bwCL M=Hp*YJpXK>YSk' );

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
define( 'WP_DEBUG', false );

/* Add any custom values between this line and the "stop editing" line. */



/* That's all, stop editing! Happy publishing. */

/** Absolute path to the WordPress directory. */
if ( ! defined( 'ABSPATH' ) ) {
	define( 'ABSPATH', __DIR__ . '/' );
}

/** Sets up WordPress vars and included files. */
require_once ABSPATH . 'wp-settings.php';
