<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<h3>Free SMS Networks</h3>

<p>Some networks provide email to SMS addresses that this plugin allows you to send to.</p>

<p>This list of networks has been compiled by us as a service to the community. We have included routes where we can say with some certainty that these exist, but networks are changing them all the time so you may find some routes do not work anymore and messages cannot be sent to those subscribers. You may also wish to add networks to this list: we are happy to do this where you can provide us with an email to SMS address for this network and confirm that this works.</p>

<p>If you have any networks to add or remove from this list, please <a href="mailto:hello@clockworksms.com">email us</a>.</p>

<?php
$wp_list_table = new Clockwork_Networks_Table();
$wp_list_table->prepare_items();
$wp_list_table->display();
?>
