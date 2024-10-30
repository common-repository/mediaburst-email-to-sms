<?php
/*
Plugin Name: Clockwork Free and Paid SMS Notifications
Plugin URI: http://wordpress.org/extend/plugins/mediaburst-email-to-sms/
Description: Free and paid SMS notifications from Clockwork (previously Mediaburst: Free SMS Notifications). Send a text message to your users when you post.
Version: 3.0.4
Author: Clockwork
Author URI: http://www.clockworksms.com/
License: MIT
*/

/*  Copyright 2014, Mediaburst Limited.

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in
all copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
THE SOFTWARE.
*/

// Version of the Clockwork plugin in use
$GLOBALS['clockwork_plugins'][ basename( dirname( __FILE__ ) ) ] = '1.2.0';

if( !function_exists( 'clockwork_loader' ) ) {

  /**
   * Load Clockwork plugins based on version numbering
   *
   * @return void
   * @author James Inman
   */
  function clockwork_loader() {
    $versions = array_flip( $GLOBALS['clockwork_plugins'] );
    uksort( $versions, 'version_compare' );
    $versions = array_reverse( $versions );
    $first_plugin = reset( $versions );

    // Require Clockwork plugin architecture
    if( !class_exists( 'Clockwork_Plugin' ) ) {
      require_once( dirname( dirname( __FILE__ ) ) . '/' . $first_plugin . '/lib/class-clockwork-plugin.php' );
    }

    // Require each plugin, unless major version doesn't match
    $version_keys = array_keys( $versions );
    preg_match( '/([0-9]+)\./', reset($version_keys), $matches );
    $major_version = intval( $matches[1] );

    foreach( $GLOBALS['clockwork_plugins'] as $plugin => $version ) {
      preg_match( '/([0-9]+)\./', $version, $matches );

      if( intval( $matches[1] ) < $major_version ) {
        // If it's a major version behind, automatically deactivate it
        require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-admin/includes/plugin.php' );
        $plugin_path = dirname( dirname( __FILE__ ) ) . '/' . $plugin . '/' . $plugin . '.php';
        $plugin_data = get_plugin_data( $plugin_path );
        deactivate_plugins( $plugin_path );

        // Output a message to tell the admin what's going on
        $message = '<div id="message" class="error"><p><strong>The plugin ' . $plugin_data['Name'] . ' has an important update available. It has been disabled until it has been updated.</strong></p></div>';
        print $message;
      } else {
        require_once( dirname( dirname( __FILE__ ) ) . '/' . $plugin . '/main.php' );
      }

    }
  }

}

add_action( 'plugins_loaded', 'clockwork_loader' );
//register_deactivation_hook( __FILE__, 'uninstall' );

/**
 * Called on plugin deactivation
 *
 * @return void
 * @author James Inman
 */
function uninstall() {
  global $wpdb;
  delete_option( 'clockwork_sms_notifications_db_version' );
  delete_option( 'clockwork_sms_notifications' );

  $tables = array( 'sms_networks', 'sms_subscribers', 'sms_countries' );
  foreach( $tables as $table ) {
    $wpdb->query( 'DROP TABLE IF EXISTS ' . $wpdb->prefix . $table );
  }
}
