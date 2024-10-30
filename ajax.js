
var Clockwork = (function(){
    var unsub = function(id) {
        var data = {
            action: 'sms_unsubscribe',
            sub_id: id
        };
        jQuery.post( ajaxurl, data, function( response ) {
            location.reload(true); 
            return;
        }).fail( function(e) {
            return;
        });
    };

    return {
        init: function(){
	    jQuery('a[data-clockwork-unsub]').on('click.clockwork', function(){ 
                unsub(jQuery(this).data('clockwork-unsub'));
            });
        }
    };
}());

jQuery(document).ready( function($) {

  Clockwork.init();
      
  if( $('#sms_subscribe_form').length > 0 ) {
    
    $('body').on( 'change', '#sms_countries_select', function() {
      
      var data = {
        action: 'sms_change_country',
        country: $(this).val()
      };
      
      $.post( ajaxurl, data, function( response ) {
        data = JSON.parse( response );
        $('#sms_networks_select').html( '<option value="">Choose your network...</option>' );
        $.each( data, function( index, network ) {
          $('#sms_networks_select').append( '<option value="' + network.id + '">' + network.name + '</option>' );
        });
      });
      
    });
    
    $('body').on( 'submit', '#sms_subscribe_form', function(e) {
      e.preventDefault();
      
      var data = {
        action: 'sms_process_free_subscription',
        country: $('#sms_subscribe_form #sms_countries_select').val(),
        network: $('#sms_subscribe_form #sms_networks_select').val(),
        number: $('#sms_subscribe_form #sms_number').val()
      };
      
      $.post( ajaxurl, data, function( response ) {
        alert( response );
        $('#sms_subscribe_form #sms_number').val('');
      }).fail( function(e) {
        alert( e.responseText );
      });
      
    });
    
  }
      
  if( $('#sms_subscribe_paid_form').length > 0 ) {
    
    $('body').on( 'submit', '#sms_subscribe_paid_form', function(e) {
      e.preventDefault();
      
      var data = {
        action: 'sms_process_paid_subscription',
        number: $('#sms_subscribe_paid_form #sms_number').val()
      };
      
      $.post( ajaxurl, data, function( response ) {
        alert( response );
        $('#sms_subscribe_form #sms_number').val('');
      }).fail( function(e) {
        alert( e.responseText );
      });
      
    });
    
  }
});

