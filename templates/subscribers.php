<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<?php
$wp_list_table = new Clockwork_Subscribers_Table();
$wp_list_table->prepare_items();
$wp_list_table->display();
?>
