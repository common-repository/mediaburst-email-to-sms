<?php
require_once( dirname( __FILE__ ) . '/lib/class-clockwork-table.php' );

class Clockwork_Subscribers_Table extends Clockwork_Table {
  
  protected $table_name = 'sms_subscribers';
  protected $per_page = 25;
  
  /**
   * Override the constructor to pass our own arguments
   *
   * @author James Inman
   */
  public function __construct() {
    global $wpdb;
    
    $networks_table = $wpdb->prefix . 'sms_networks';
    $subscribers_table = $wpdb->prefix . 'sms_subscribers';
    
    $this->override_query = "SELECT $subscribers_table.*, $networks_table.country, $networks_table.name FROM $subscribers_table LEFT JOIN $networks_table ON $networks_table.id = $subscribers_table.network_id WHERE $subscribers_table.active != 0";
    
    parent::__construct( 'wp_list_subscriber', 'wp_list_subscribers' );
  }
  
  /**
   * Define the columns that are going to be used in the table
   *
   * @return array $columns, the array of columns to use with the table
   * @author James Inman
   */
  public function get_columns() {
  	return $columns = array(
      'mobile_number' => __( 'Mobile Number' ),
      'network' => __( 'Network' ),
      'created_at' => __( 'Signed Up' ),
      'id' => __('Unsubscribe')
    );
  }
  
  /**
   * Output the given cell
   *
   * Include an if/case statement in here if you need it
   *
   * @param string $column_name Database column to output
   * @param string $row Record for the current row returned from $wpdb->query()
   * @return void
   * @author James Inman
   */
  public function output_cell( $column_name, $row ) {
    if( $column_name == 'created_at' ) {
      print stripslashes( date( 'jS F Y g:ia', $row->$column_name ) );
    } elseif( $column_name == 'network' ) {
      print stripslashes( $row->country ) . ' - ' . stripslashes( $row->name );
    } elseif($column_name == 'id') {
      printf('<a href="#" data-clockwork-unsub="%s">Unsubscribe</a>', $row->id);
    } else {
      print stripslashes( $row->$column_name );
    }
  }

}
?>
