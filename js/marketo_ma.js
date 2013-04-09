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
            if (typeof settings.marketo.actions !== 'undefined') {
              jQuery.each(settings.marketo.actions, function(){
                marketoMunchkinFunction(this.action, this.data, this.hash);
              });
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
