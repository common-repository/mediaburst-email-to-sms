<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>
<div class="wrap">
  <div class="left-content">

    <form method="post" action="options.php" id="clockwork_options_form">

    <?php
    foreach( array_unique( get_settings_errors( 'clockwork_sms_notifications' ) ) as $error ) {
      if( $error['type'] == 'updated' ) {
        print '<div id="message" class="updated fade"><p><strong>' . $error['message'] . '</strong></p></div>';
      } else {
        print '<div id="message" class="error"><p><strong>' . $error['message'] . '</strong></p></div>';
      }
    }

    settings_fields( 'clockwork_sms_notifications' );
    do_settings_sections( 'clockwork_sms_notifications' );
    submit_button();
    ?>

    </form>

  </div>
