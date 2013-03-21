(function($) {
  Drupal.behaviors.marketoMunchkinLibrary = {
    attach: function(context, settings) {
      jQuery.ajax({
        url: document.location.protocol + Drupal.settings.marketo.library,
        dataType: 'script',
        cache: true,
        success: function() {
          Munchkin.init(Drupal.settings.marketo.key);
          if(typeof settings.marketo.data !== 'undefined') marketoMunchkinFunction();
        }
      });
    }
  }

})(jQuery);

function marketoMunchkinFunction() {
  console.log(Drupal.settings.marketo);
  mktoMunchkinFunction(Drupal.settings.marketo.lead_type, Drupal.settings.marketo.data, Drupal.settings.marketo.hash);
}