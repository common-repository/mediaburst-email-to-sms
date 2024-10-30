<?php
require_once( dirname( __FILE__ ) . '/lib/class-clockwork-table.php' );

class Clockwork_Networks_Table extends Clockwork_Table {
  
  protected $table_name = 'sms_networks';
  protected $per_page = 25;
  
  /**
   * Override the constructor to pass our own arguments
   *
   * @author James Inman
   */
  public function __construct() {
    parent::__construct( 'wp_list_network', 'wp_list_networks' );
  }
  
  /**
   * Define the columns that are going to be used in the table
   *
   * @return array $columns, the array of columns to use with the table
   * @author James Inman
   */
  public function get_columns() {
  	return $columns = array(
      'country' => __( 'Country' ),
      'name' => __( 'Network' ),
      'send_to' => __( 'Send To' )
    );
  }
  
  /**
   * Define the columns that sorting functionality is active on
   *
   * @return array $columns, the array of columns that can be sorted by the user
   * @author James Inman
   */
  public function get_sortable_columns() {
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
    print stripslashes( $row->$column_name );
  }

}
?>