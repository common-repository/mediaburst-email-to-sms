<?php
if( !class_exists( 'WP_List_Table' ) ) {
  require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

// http://wp.smashingmagazine.com/2011/11/03/native-admin-tables-wordpress/
abstract class Clockwork_Table extends WP_List_Table {

  protected $override_query;

  /**
   * Override the constructor to pass our own arguments
   *
   * @author James Inman
   */
  public function __construct( $singular, $plural ) {
    parent::__construct( array(
      'singular'=> $singular,
      'plural' => $plural,
      'ajax'	=> false
    ) );

    if( !isset( $this->table_name ) ) {
      throw new Exception( 'You must set $this->table_name' );
    }
  }

  /**
   * Add extra markup in the toolbars before or after the list
   *
   * @param string $which, helps you decide if you add the markup after (bottom) or before (top) the list
   * @return void
   * @author James Inman
   */
  public function extra_tablenav( $which ) {
    if ( $which == 'top' ) {
    }

    if ( $which == 'bottom' ){
    }
  }

  /**
   * Define the columns that are going to be used in the table
   *
   * @return array $columns, the array of columns to use with the table
   * @author James Inman
   */
  public function get_columns() {

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
   * Output the row for the given columnname
   *
   * @author James Inman
   */
  abstract public function output_cell( $column_name, $record );

  /**
   * Prepare the table with different parameters, pagination, columns and table elements
   *
   * @return void
   * @author James Inman
   */
  public function prepare_items() {
    global $wpdb, $_wp_column_headers;
    $screen = get_current_screen();
    $table_name = $wpdb->prefix . $this->table_name;

    if( isset( $this->override_query ) ) {
      $query = $this->override_query;
    } else {
      $query = "SELECT * FROM $table_name";
    }

    // Parameters that are going to be used to order the result
    $orderby = !empty( $_GET['orderby'] ) ? esc_sql( $_GET['orderby'] ) : 'ASC';
    $order = !empty( $_GET['order'] ) ? esc_sql( $_GET['order'] ) : '';
    if( !empty( $orderby ) & !empty( $order ) ) {
      $query .= ' ORDER BY ' . $orderby . ' ' . $order;
    }
    // Return the number of rows
    $totalitems = $wpdb->query( $query );
    $perpage = $this->per_page;
    $paged = !empty( $_GET['paged'] ) ? esc_sql( $_GET['paged'] ) : '';

    if( empty( $paged ) || !is_numeric( $paged ) || $paged <= 0 ) {
      $paged = 1;
    }

    $totalpages = ceil( $totalitems / $perpage );

    if( !empty( $paged ) && !empty( $perpage ) ) {
      $offset = ( $paged - 1 ) * $perpage;
      $query .= ' LIMIT ' . (int) $offset . ',' . (int) $perpage;
    }

    // Register the pagination
    $this->set_pagination_args( array(
      'total_items' => $totalitems,
      'total_pages' => $totalpages,
      'per_page' => $perpage,
    ) );

    $columns = $this->get_columns();
    $sortable = $this->get_sortable_columns();
    $this->_column_headers = array( $columns, array(), $sortable );

    $this->items = $wpdb->get_results( $query );
  }

  /**
   * Display the rows of records in the table
   *
   * @return string, echo the markup of the rows
   * @author James Inman
   */
  public function display_rows() {
    $records = $this->items;
    $columns = $this->get_columns();

  	if( !empty( $records ) ) {
      foreach( $records as $rec ) {

        echo '<tr id="record_' . $rec->id . '">';

    		foreach ( $columns as $column_name => $column_display_name ) {
          print '<td>';
          $this->output_cell( $column_name, $rec );
          print '</td>';
        }

        echo '</tr>';
      }
    }
  }

}
