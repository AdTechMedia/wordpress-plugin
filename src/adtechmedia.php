<?php
/*
   Plugin Name: Adtechmedia
   Plugin URI: http://wordpress.org/extend/plugins/adtechmedia/
   Version: 0.1
   Author: 
   Description: 
   Text Domain: adtechmedia
   License: GPLv3
  */

/*
    "WordPress Plugin Template" Copyright (C) 2016 Michael Simpson  (email : michael.d.simpson@gmail.com)

    This following part of this file is part of WordPress Plugin Template for WordPress.

    WordPress Plugin Template is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    WordPress Plugin Template is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with Contact Form to Database Extension.
    If not, see http://www.gnu.org/licenses/gpl-3.0.html
*/

$adtechmedia_minimal_required_php_version = '5.0';

/**
 * Check the PHP version and give a useful error message if the user's version is less than the required version
 *
 * @return boolean true if version check passed. If false, triggers an error which WP will handle, by displaying
 * an error message on the Admin page
 */
function adtechmedia_notice_php_version_wrong() {
	global $adtechmedia_minimal_required_php_version;
	echo '<div class="updated fade">' .
		__( 'Error: plugin "Adtechmedia" requires a newer version of PHP to be running.', 'adtechmedia' ) .
		'<br/>' . __(
			'Minimal version of PHP required: ',
			'adtechmedia'
		) . '<strong>' . $adtechmedia_minimal_required_php_version . '</strong>' .
		'<br/>' . __( 'Your server\'s PHP version: ', 'adtechmedia' ) . '<strong>' . phpversion() . '</strong>' .
		'</div>';
}


function adtechmedia_php_version_check() {
	global $adtechmedia_minimal_required_php_version;
	if ( version_compare( phpversion(), $adtechmedia_minimal_required_php_version ) < 0 ) {
		add_action( 'admin_notices', 'adtechmedia_notice_php_version_wrong' );
		return false;
	}
	return true;
}


/**
 * Initialize internationalization (i18n) for this plugin.
 * References:
 *      http://codex.wordpress.org/I18n_for_WordPress_Developers
 *      http://www.wdmac.com/how-to-create-a-po-language-translation#more-631
 *
 * @return void
 */
function adtechmedia_i18n_init() {
	$pluginDir = dirname( plugin_basename( __FILE__ ) );
	load_plugin_textdomain( 'adtechmedia', false, $pluginDir . '/languages/' );
}



// Initialize i18n.
add_action( 'plugins_loadedi', 'adtechmedia_i18n_init' );

// Run the version check.
// If it is successful, continue with initialization for this plugin.
if ( adtechmedia_php_version_check() ) {

	include_once( 'adtechmedia-init.php' );
	include_once( 'adtechmedia-request.php' );
	include_once( 'adtechmedia-config.php' );
	include_once( 'adtechmedia-contentmanager.php' );
	adtechmedia_init( __FILE__ );
}
