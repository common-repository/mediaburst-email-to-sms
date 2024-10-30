<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">

    <h2 class="nav-tab-wrapper">SMS Notifications &nbsp; &nbsp; &nbsp;
      <a class="nav-tab <?php if( $data['tab'] == '' ) { ?>nav-tab-active<?php } ?>" href="?page=clockwork_sms_notifications">Options</a>
      <a class="nav-tab <?php if( $data['tab'] == 'subscribers' ) { ?>nav-tab-active<?php } ?>" href="?page=clockwork_sms_notifications&amp;tab=subscribers">Subscribers</a>
    </h2>
