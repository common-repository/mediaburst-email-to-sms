<?php
// http://www.wpexplorer.com/create-widget-plugin-wordpress/
class SMS_Notifications_Widget extends WP_Widget {

  /**
   * Register widget
   *
   * @author James Inman
   */
	public function __construct() {
		parent::__construct(
			'sms_notification_widget', 'SMS Notifications', array( 'description' => 'Allow your visitors to subscribe to new post notifications by SMS' )
		);
	}

  /**
   * Form to display in the administration panel for the widget
   *
   * @param string $instance
   * @return void
   * @author James Inman
   */
	public function form( $instance ) {
		$title = 'Subscribe by text message!';
		if( isset( $instance['title'] ) ) {
			$title = $instance['title'];
		}

    $description = 'We send out SMS updates when we publish a new post. Enter your details below to subscribe.';
    if( isset( $instance['description'] ) ) {
      $description = $instance['description'];
    }

    print '<p><label for="title">Title:</label>
		<input class="widefat" id="title" name="title" type="text" value="' . $title . '" /></p>

    <p><label for="description">Description:</label>
    <textarea class="widefat" rows="5" cols="20" id="description" name="description">' . $description . '</textarea>
		</p>';
	}

	/**
   * Sanitize widget form values as they are saved.
   *
   * @see WP_Widget::update()
   *
   * @param array $new_instance Values just sent to be saved.
   * @param array $old_instance Previously saved values from database.
   * @return array Updated safe values to be saved.
   *
   * @author James Inman
   */
	public function update( $new_instance, $old_instance ) {
    $new_instance['title'] = sanitize_text_field($_POST['title']);
    $new_instance['description'] = sanitize_text_field($_POST['description']);
    return $new_instance;
	}

  /**
   * Front-end display of widget
   * @see WP_Widget::widget()
   *
   * @param string $args Widget arguments
   * @param string $instance Saved values from database
   * @return void
   * @author James Inman
   */
  public function widget( $args, $instance ) {
    print $args['before_widget'];
    printf ('%s%s%s', $args['before_title'], apply_filters('widget_title', $instance['title']), $args['after_title']);
    if( Clockwork_SMS_Notifications_Plugin::is_upgraded() ) {
      print $instance['description'];
      print '<form method="post" id="sms_subscribe_paid_form" class="sms_subscribe_form" action="">';
      print '<br /><label for="sms_number">Enter your phone number:</label><input type="text" value="" name="sms_number" id="sms_number" placeholder="">';
      print '<br /><small class="desc">In international format: starts with country code and no leading 0.</small>';
      print '<br /><input type="submit" id="sms_subscribe_submit" value="Subscribe">';
      print '</form>';
    } else {
      print $instance['description'];
      print '<form method="post" id="sms_subscribe_form" class="sms_subscribe_form" action="">';
      print '<br /><label for="sms_countries" id="sms_countries_label">Choose your country:</label><br /><select name="sms_countries" size="1" id="sms_countries_select">';
      print '<option value="" id="country">Choose your country...</option>';
      foreach( $this->get_countries() as $res ) {
        print '<option value="' . $res->country_code . '">' . $res->country . '</option>';
      }
      print '</select>';
      print '<br /><label for="sms_networks" id="sms_networks_label">Choose your network:</label><br /><select name="sms_networks" size="1" id="sms_networks_select">';
      print '<option value="">Choose a country first...</option>';
      print '</select>';
      print '<br /><label for="sms_number">Enter your phone number:</label><input type="text" value="" name="sms_number" id="sms_number" placeholder="">';
      print '<br /><small class="desc">In your normal local format.</small>';
      print '<br /><input type="submit" id="sms_subscribe_submit" value="Subscribe">';
      print '<br /><small class="meta"><a href="http://wordpress.org/plugins/mediaburst-email-to-sms/" title="Free SMS Notifications">Free SMS Notifications</a> powered by Clockwork SMS.</small>';
      print '</form>';
    }
    print $args['after_widget'];
  }

  /**
   * Return the distinct countries from the networks table
   *
   * @return void
   * @author James Inman
   */
  public function get_countries() {
    global $wpdb;
    $networks_table = $wpdb->prefix . 'sms_networks';
    $sql = "SELECT DISTINCT country, country_code FROM $networks_table ORDER BY country ASC";
    return $wpdb->get_results( $sql );
  }

}

// register widget
add_action( 'widgets_init', 'register_clockwork_sms_notifications_widget');

function register_clockwork_sms_notifications_widget(){
  register_widget( 'SMS_Notifications_Widget' );
}
