(function($) {
  Drupal.behaviors.marketo = {
    attach: function(context, settings) {
      if (typeof settings.marketo !== 'undefined' && settings.marketo.track) {
        jQuery.ajax({
          url: document.location.protocol + settings.marketo.library,
          dataType: 'script',
          cache: true,
          success: function() {
            Munchkin.init(settings.marketo.key);
            if (typeof settings.marketo.data !== 'undefined') {
              marketoMunchkinFunction(settings.marketo.lead_type, settings.marketo.data, settings.marketo.hash);
            }
          }
        });
      }
    }
  }

})(jQuery);

function marketoMunchkinFunction(leadType, data, hash) {
  mktoMunchkinFunction(leadType, data, hash);
}