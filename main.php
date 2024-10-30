<?php
require_once(plugin_dir_path(__FILE__).'clockwork-sms-widget.php' );

class Clockwork_SMS_Notifications_Plugin extends Clockwork_Plugin {

  protected $plugin_name = 'SMS Notifications';
  protected $language_string = 'mediaburst-email-to-sms';
  protected $prefix = 'mediaburst-email-to-sms';
  protected $folder = '';
  protected $latest_db_version = 0.1;

  protected $forms = array();

  /**
   * Constructor: setup callbacks and plugin-specific options
   *
   * @author James Inman
   */
  public function __construct() {
    parent::__construct();

    // Set the plugin's Clockwork SMS menu to load the plugin
    $this->plugin_callback = array( $this, 'clockwork_sms_notifications' );
    $this->plugin_dir = basename( dirname( __FILE__ ) );

    // Check if the plugin needs updating
    $this->update_db_check();

    // Check if we're upgrading from free to paid
    if( isset( $_GET['upgrade'] ) && $_GET['upgrade'] == 'true' ) {
      $this->upgrade_plugin();
    }

    add_action( 'publish_post', array( $this, 'publish_post' ) );
    add_action( 'wp_enqueue_scripts', array ($this, 'sms_enqueue_scripts' ) );
    add_action( 'admin_enqueue_scripts', array ($this, 'sms_enqueue_scripts' ) );
    add_action( 'wp_ajax_sms_change_country', array( $this, 'sms_change_country' ) );
    add_action( 'wp_ajax_nopriv_sms_change_country', array( $this, 'sms_change_country' ) );
    add_action( 'wp_ajax_sms_process_free_subscription', array( $this, 'process_free_subscription' ) );
    add_action( 'wp_ajax_nopriv_sms_process_free_subscription', array( $this, 'process_free_subscription' ) );
    add_action( 'wp_ajax_sms_process_paid_subscription', array( $this, 'process_paid_subscription' ) );
    add_action( 'wp_ajax_nopriv_sms_process_paid_subscription', array( $this, 'process_paid_subscription' ) );
    add_action( 'wp_ajax_sms_unsubscribe', array( $this, 'process_unsubscribe' ) );
    add_action( 'wp_head', array( $this, 'sms_ajaxurl' ) );
  }

  /**
   * Setup the admin navigation
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_navigation() {
    if( self::is_upgraded() ) {
      parent::setup_admin_navigation();
    } else {
      add_menu_page( 'SMS Notifications', 'SMS Notifications', 'manage_options', 'clockwork_sms_notifications', $this->plugin_callback, plugin_dir_url(__FILE__).'/images/logo_16px_16px.png' );
    }
  }

  /**
   * Display an error if I don't have a mobile number set
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_message() {
    if( self::is_upgraded() ) {
      parent::setup_admin_message();
    }

    if( !is_active_widget( false, false, 'sms_notification_widget' ) ) {
      $this->show_admin_message( 'You need to enable the SMS Notifications widget in Appearance > Widgets.', true );
    }
  }

  /**
   * Setup HTML for the admin <head>
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_head() {
    print '<link rel="stylesheet" type="text/css" href="' . plugins_url( 'css/clockwork.css', __FILE__ ) . '">';
  }

  /**
   * Register the settings for this plugin
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_init() {
    // Register main Clockwork functions
    parent::setup_admin_init();

    register_setting( 'clockwork_sms_notifications', 'clockwork_sms_notifications' );
    add_settings_section( 'clockwork_sms_notifications', __('Default Settings', 'clockwork_sms_notifications'), array( &$this, 'settings_header' ), 'clockwork_sms_notifications' );
    add_settings_field( 'enabled', __('Enabled', 'clockwork_sms_notifications'), array( &$this, 'settings_enabled' ), 'clockwork_sms_notifications', 'clockwork_sms_notifications' );

    if( !self::is_upgraded() ) {
      add_settings_field( 'from', __('"From" Email Address', 'clockwork_sms_notifications'), array( &$this, 'settings_from' ), 'clockwork_sms_notifications', 'clockwork_sms_notifications' );
    }

    add_settings_field( 'message', __('Message', 'clockwork_sms_notifications'), array( &$this, 'settings_message' ), 'clockwork_sms_notifications', 'clockwork_sms_notifications' );
  }

  /**
   * Output the header paragraph for the settings
   *
   * @return void
   * @author James Inman
   */
  public function settings_header() {
    print '<p>Here you can set default settings for sending out your new post SMS notifications.</p>';
  }

  /**
   * Output the enabled field
   *
   * @return void
   * @author James Inman
   */
  public function settings_enabled() {
    $options = get_option( 'clockwork_sms_notifications' );
    if( $options['enabled'] == '1' ) {
      print '<input id="sms_notifications_sms_enabled" name="clockwork_sms_notifications[enabled]" type="checkbox" checked="checked" value="1" />';
    } else {
      print '<input id="sms_notifications_sms_enabled" name="clockwork_sms_notifications[enabled]" type="checkbox" value="1" />';
    }

    print '<p class="description">If this is checked, SMS notifications will be sent to your subscribers when a post is published.</p>';
  }

  /**
   * Output the "from" email address field
   *
   * @return void
   * @author James Inman
   */
  public function settings_from() {
    $options = get_option( 'clockwork_sms_notifications' );
    print '<input id="sms_notifications_sms_email" name="clockwork_sms_notifications[from]" size="40" type="text" value="' . $options['from'] . '" style="padding: 3px;" />';
    print '<p class="description">The email address your SMS notifications are sent from.</p>';
  }

  /**
   * Output the default message
   *
   * @return void
   * @author James Inman
   */
  public function settings_message() {
    $options = get_option( 'clockwork_sms_notifications' );
    print '<textarea id="sms_notifications_sms_message" name="clockwork_sms_notifications[message]" rows="6" cols="40" type="text" style="padding: 3px;">' . $options['message'] . '</textarea>';
    print '<p class="description">The message subscribers are sent when a new post is published. You can use the following:';
    print '<br /><br /><code>%site_name%</code> - The name of your Wordpress site';
    print '<br /><code>%post_title%</code> - The title of your post';
    print '<br /><code>%post_link%</code> - The link to your post';
    print '</p>';
  }

  /**
   * Function to provide a callback for the main plugin action page
   *
   * @return void
   * @author James Inman
   */
  public function clockwork_sms_notifications() {
    $tab = isset($_GET['tab']) ? sanitize_text_field($_GET['tab']) : '';
    if( self::is_upgraded() ) {
      $this->render_template( 'options-header-paid', array( 'tab' => $tab ) );

      if( $tab == 'subscribers' ) {
        require_once( plugin_dir_path(__FILE__).'clockwork-subscribers-table.php' );
        $this->render_template( 'subscribers' );
      } else {
        $this->render_template( 'options' );
      }

    } else {

      $this->render_template( 'options-header-free', array( 'tab' => $tab ) );

      if( $tab == 'networks' ) {
        require_once( plugin_dir_path(__FILE__).'clockwork-networks-table.php' );
        $this->render_template( 'networks' );
      } elseif( $tab == 'subscribers' ) {
        require_once( plugin_dir_path(__FILE__).'clockwork-subscribers-table.php' );
        $this->render_template( 'subscribers' );
      } elseif( $tab == 'upgrade' ) {
        $this->render_template( 'upgrade' );
      } else {
        $this->render_template( 'options' );
      }

    }

    $this->render_template( 'options-footer' );
  }

  /**
   * Check if username and password have been entered
   *
   * @return void
   * @author James Inman
   */
  public function get_existing_username_and_password() { }

  /**
   * Send to subscribers when a post is published
   *
   * @return void
   * @author James Inman
   */
  public function publish_post() {
    // Is it enabled?
    $options = get_option( 'clockwork_sms_notifications' );
    if( isset( $options['enabled'] ) && $options['enabled'] == '1' ) {

      global $wpdb;
      $addresses = array();

      $subscribers_table = $wpdb->prefix . 'sms_subscribers';
      $networks_table = $wpdb->prefix . 'sms_networks';

      // Has the post just been published?
      if( $_POST['original_post_status'] != 'publish' && $_POST['post_status'] == 'publish' ) {

        // Are we running the upgraded plugin?
        if( !self::is_upgraded() ) {
          // Get addresses
          $sql = "SELECT REPLACE( $networks_table.send_to, '{number}', $subscribers_table.mobile_number ) AS number FROM $subscribers_table LEFT JOIN $networks_table ON $networks_table.id = $subscribers_table.network_id WHERE $subscribers_table.active = 1";
          foreach( $wpdb->get_results( $sql ) as $res ) {
            $addresses[] = $res->number;
          }

          // Send the emails
          $headers = array( 'Bcc:' . implode( ',', $addresses ), 'From:' . $options['from'] );

          $message = $options['message'];
          $message = str_replace( '%site_name%', get_option( 'blogname' ), $message );
          $message = str_replace( '%post_title%', sanitize_text_field($_POST['post_title']), $message );
          $message = str_replace( '%post_link%', get_permalink( sanitize_text_field($_POST['id']) ), $message );

          wp_mail( $options['to'], '', $message, $headers );
        } else {
          // Get mobile numbers
          $sql = "SELECT mobile_number FROM $subscribers_table WHERE active = 1";
          foreach( $wpdb->get_results( $sql ) as $res ) {
            $addresses[] = $res->mobile_number;
          }

          // Send the SMS messages
          try {
            $options = array_merge( $options, get_option( 'clockwork_options' ) );
            $clockwork = new WordPressClockwork( $options['api_key'] );
            $messages = array();

            $message = $options['message'];
            $message = str_replace( '%site_name%', get_option( 'blogname' ), $message );
            $message = str_replace( '%post_title%', sanitize_text_field($_POST['post_title']), $message );
            $message = str_replace( '%post_link%', get_permalink( sanitize_text_field($_POST['id']) ), $message );

            foreach( $addresses as $to ) {
              $messages[] = array( 'from' => $options['from'], 'to' => $to, 'message' => $message );
            }
            $result = $clockwork->send( $messages );
          } catch( ClockworkException $e ) {
            $result = "Error: " . $e->getMessage();
            exit;
          } catch( Exception $e ) {
            $result = "Error: " . $e->getMessage();
          }
        }

      }

    }
  }

  /**
   * AJAX-called function to repopulate the networks list when changing the country
   *
   * @return void
   * @author James Inman
   */
  public function sms_change_country() {
    $country_code = sanitize_text_field($_POST['country']);
    print json_encode( $this->get_networks( $country_code ) );
    exit;
  }

  /**
   * Process a free subscription
   *
   * @return void
   * @author James Inman
   */
  public function process_free_subscription() {
    $country = sanitize_text_field($_POST['country']);
    $network = sanitize_text_field($_POST['network']);
    $number = sanitize_text_field($_POST['number']);

    if( $_POST['country'] == '' ) {
      header("HTTP/1.0 400 Bad Request");
      print 'Please choose your country.';
      exit;
    } elseif( $_POST['network'] == '' ) {
      header("HTTP/1.0 400 Bad Request");
      print 'Please choose your network.';
      exit;
    } elseif( trim( $_POST['number'] ) == '' ) {
      header("HTTP/1.0 400 Bad Request");
      print 'Please enter your mobile number in local (national) format.';
      exit;
    } else {
      global $wpdb;
      $subscribers_table = $wpdb->prefix . 'sms_subscribers';
      $sql = $wpdb->prepare( "REPLACE INTO $subscribers_table( created_at, mobile_number, network_id ) VALUES( %d, '%s', %d );", time(), $number, $network );
      if( $wpdb->query( $sql ) === false ) {
        header("HTTP/1.0 400 Bad Request");
        print 'There was an error subscribing you to SMS notifications. Please try again or contact us.';
        exit;
      } else {
        header("HTTP/1.0 200 OK");
        print 'You have been successfully subscribed.';
        exit;
      }
    }
  }
  /**
   * Process a paid subscription
   *
   * @return void
   * @author James Inman
   */
  public function process_paid_subscription() {
    $number = trim( sanitize_text_field($_POST['number']) );

    if( $number == '' ) {
      header("HTTP/1.0 400 Bad Request");
      print 'Please enter your mobile number in international format, e.g. 447520123456 or 12345552393.';
      exit;
    } else {
      global $wpdb;
      $subscribers_table = $wpdb->prefix . 'sms_subscribers';
      $sql = $wpdb->prepare( "REPLACE INTO $subscribers_table( created_at, mobile_number, network_id ) VALUES( %d, '%s', NULL );", time(), $number );
      if( $wpdb->query( $sql ) === false ) {
        header("HTTP/1.0 400 Bad Request");
        print 'There was an error subscribing you to SMS notifications. Please try again or contact us.';
        exit;
      } else {
        header("HTTP/1.0 200 OK");
        print 'You have been successfully subscribed.';
        exit;
      }
    }
  }

  /**
   * Process an unsrubscribe request
   *
   * @return void
   * @author Martin Steel
   */
  public function process_unsubscribe() {
    $id = trim( sanitize_text_field($_POST['sub_id']) );
    if( $id == '' ) {
      header("HTTP/1.0 400 Bad Request");
      print 'Pass an ID';
      exit;
    } else {
      global $wpdb;
      $subscribers_table = $wpdb->prefix . 'sms_subscribers';
      $sql = $wpdb->prepare( "UPDATE $subscribers_table SET active = 0 WHERE id = %d", $id);
      if( $wpdb->query( $sql ) === false ) {
        header("HTTP/1.0 400 Bad Request");
        print 'There was an error unsubscribing.';
        exit;
      } else {
        header("HTTP/1.0 200 OK");
        print 'Unsubscribed.';
        exit;
      }
    }
  }

  /**
   * Return the distinct networks for a particular country from the networks table
   *
   * @return void
   * @author James Inman
   */
  public function get_networks( $country_code ) {
    global $wpdb;
    $networks_table = $wpdb->prefix . 'sms_networks';
    $sql = $wpdb->prepare( "SELECT id, name FROM $networks_table WHERE country_code = '%s' ORDER BY country ASC", $country_code );
    return $wpdb->get_results( $sql );
  }

  /**
   * Make the admin AJAX URL available to the front-end
   *
   * @return void
   * @author James Inman
   */
  public function sms_ajaxurl() {
    print "<script type=\"text/javascript\">var ajaxurl = '" . admin_url( 'admin-ajax.php' ) . "';</script>";
  }

  /**
   * Install and update the plugin
   *
   * From http://codex.wordpress.org/Creating_Tables_with_Plugins
   * Be careful of syntax for dbDelta, it's tricksy: http://hungred.com/how-to/wordpress-dbdelta-function/
   *
   * @return void
   * @author James Inman
   */
  public function install() {
    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    global $wpdb;

    // Setup options
    update_option( 'clockwork_sms_notifications', array( 'enabled' => '1', 'from' => get_option( 'admin_email' ), 'message' => 'A new post %post_title% has been posted at %site_name%: %post_link%' ) );

    // Do database setup
    $subscribers_table = $wpdb->prefix . 'sms_subscribers';
    $networks_table = $wpdb->prefix . 'sms_networks';
    $countries_table = $wpdb->prefix . 'sms_countries';

    // Subscribers table
    $sql = "CREATE TABLE $subscribers_table (
    id INT(11) NOT NULL AUTO_INCREMENT,
    created_at INT(11) NOT NULL,
    mobile_number VARCHAR(255) NOT NULL,
    network_id INT(11) DEFAULT NULL,
    active TINYINT(1) DEFAULT 1,
    UNIQUE KEY id (id)
    );";
    dbDelta( $sql );

    // Networks table
    $sql = "CREATE TABLE $networks_table (
    id INT(11) NOT NULL AUTO_INCREMENT,
    country VARCHAR(255) NOT NULL,
    country_code VARCHAR(255) NOT NULL,
    name VARCHAR(255) NOT NULL,
    send_to VARCHAR(255) NOT NULL,
    legacy_id VARCHAR(255) NOT NULL,
    active TINYINT(1) DEFAULT 1 NOT NULL,
    UNIQUE KEY id (id)
    );";
    dbDelta( $sql );

    // Countries table
    $sql = "CREATE TABLE $countries_table (
    id INT(11) NOT NULL AUTO_INCREMENT,
    country_name VARCHAR(255) NOT NULL,
    country_code VARCHAR(255) NOT NULL,
    international VARCHAR(255) NOT NULL,
    national VARCHAR(255) NOT NULL,
    UNIQUE KEY id (id)
    );";
    dbDelta( $sql );

    // Insert the networks
    $networks_file = file_get_contents( dirname( __FILE__ ) . '/providers.csv' );
    $networks = explode( "\n", $networks_file );

    if( count( $networks ) == 1 ) {
      $networks = explode( "\r", $networks_file );
    }

    foreach( $networks as $network ) {
      $network = explode( ',', $network );

      $data = array(
        'country_code' => $network[0],
        'country' => $network[1],
        'name' => $network[2],
        'send_to' => $network[3],
        'legacy_id' => trim( $network[4] )
      );

      $wpdb->insert( $networks_table, $data, '%s' );
    }

    // Insert the countries
    $countries_file = file_get_contents( dirname( __FILE__ ) . '/trunk_list.csv' );
    $countries = explode( "\n", $countries_file );

    if( count( $countries ) == 1 ) {
      $countries = explode( "\r", $countries_file );
    }

    foreach( $countries as $country ) {

      $country = explode( ',', $country );

      $data = array(
        'country_name' => $country[0],
        'country_code' => $country[1],
        'international' => $country[2],
        'national' => trim( $country[3] )
      );

      $wpdb->insert( $countries_table, $data, '%s' );
    }

    // See if we still have subscribers from the old plugin
    $table_name = $wpdb->prefix . 'mb_subscribers';
    $sql = "SHOW TABLES LIKE '$table_name'";
    $result = $wpdb->get_col( $sql );

    if( count( $result ) != 0 ) {

      // Old plugin is installed
      $old_subscribers = $wpdb->prefix . 'mb_subscribers';
      $old_providers = $wpdb->prefix . 'mb_providers';
      $new_networks = $wpdb->prefix . 'sms_networks';
      $new_subscribers = $wpdb->prefix . 'sms_subscribers';

      $inactives = array();

      // Grab the list of legacy networks we have at the moment, indexed by provider ID
      $sql = "SELECT * FROM $new_networks WHERE legacy_id IS NOT NULL";
      $networks = array();
      foreach( $wpdb->get_results( $sql ) as $row ) {
        $networks[ $row->legacy_id ] = $row->id;
      }

      // Convert all the old subscribers to new ones
      $sql = "SELECT * FROM $old_subscribers LEFT JOIN $new_networks ON wp_mb_subscribers.provider_id = wp_sms_networks.legacy_id";
      foreach( $wpdb->get_results( $sql ) as $row ) {
        $mobile = $row->subscriber_number;

        if( isset( $networks[ $row->provider_id ] ) ) {
          $active = 1;
          $network = $networks[ $row->provider_id ];
        } else {
          $active = 0;
          $network = NULL;
          $inactives[] = $mobile;
        }

        $sql2 = $wpdb->prepare( "REPLACE INTO $new_subscribers( created_at, mobile_number, network_id, active ) VALUES( %d, '%s', %d, %d )", time(), $mobile, $network, $active );
        $q2 = $wpdb->query( $sql2 );
      }


      // Show error message about inactive numbers
      if( count( $inactives ) > 0 ) {
        $msg = "We couldn't convert the following mobile numbers as their networks no longer provide reliable email-to-SMS routes. If you upgrade the plugin, we'll attempt to reactivate these numbers through the paid Clockwork SMS routes:<br /><br />";

        foreach( $inactives as $i ) {
          $msg .= $i . '<br />';
        }

        $this->show_admin_message( $msg, true );
      }

      $wpdb->query( 'DROP TABLE IF EXISTS ' . $old_subscribers );
      $wpdb->query( 'DROP TABLE IF EXISTS ' . $old_providers );
    }

    update_option( 'clockwork_sms_notifications_db_version', $this->latest_db_version );
  }

  /**
   * Are we running the upgraded version?
   *
   * @return void
   * @author James Inman
   */
  public static function is_upgraded() {
    if( get_option( 'clockwork_sms_notifications_upgraded' ) == '1' ) {
      return true;
    } else {
      return false;
    }
  }

  /**
   * Do any updates need running?
   *
   * @return void
   * @author James Inman
   */
  public function update_db_check() {
    if( get_option( 'clockwork_sms_notifications_db_version' ) != $this->latest_db_version ) {
      $this->install();
    }

    if( !self::is_upgraded() ) {
      update_option( 'clockwork_sms_notifications_upgraded', '0' );
    }
  }

  /**
   * Perform the plugin upgrade
   *
   * @return void
   * @author James Inman
   */
  public function upgrade_plugin() {
    global $wpdb;
    $subscribers_table = $wpdb->prefix . 'sms_subscribers';
    $networks_table = $wpdb->prefix . 'sms_networks';
    $countries_table = $wpdb->prefix . 'sms_countries';

    $sql = "UPDATE $subscribers_table
LEFT JOIN wp_sms_networks ON $networks_table.id = $subscribers_table.network_id
LEFT JOIN wp_sms_countries ON $countries_table.country_code = $networks_table.country_code
SET mobile_number = CONCAT( $countries_table.international, SUBSTRING( $subscribers_table.mobile_number, LENGTH( $countries_table.national ) + 1 ) )
WHERE LEFT( $subscribers_table.mobile_number, LENGTH( $countries_table.national ) ) = $countries_table.national";
    $wpdb->query( $sql );

    // Upgrade any inactive numbers as we can now send to them
    $sql = "UPDATE $subscribers_table SET active = 1";
    $wpdb->query( $sql );

    update_option( 'clockwork_sms_notifications_upgraded', '1' );
  }

  /*
   * Enque client side scripts
   *
   * @return void
   * @author Martin Steel
   */
  public function sms_enqueue_scripts() {
    wp_enqueue_script( 'sms_notifications_ajax', plugin_dir_url( __FILE__ ) . 'ajax.js', array( 'jquery' ), '' );
  }
}

$cp = new Clockwork_SMS_Notifications_Plugin();
