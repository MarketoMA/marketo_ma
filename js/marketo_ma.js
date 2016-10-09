(function($, Drupal) {
  Drupal.behaviors.marketo_ma = {
    attach: function(context, settings) {
      // Only load Marketo Once.
      $(document).once('marketo').each(function() {
        // Only track Marketo if the setting is enabled.
        if (typeof settings.marketo_ma !== 'undefined' && settings.marketo_ma.track) {
          $.ajax({
            url: document.location.protocol + settings.marketo_ma.library,
            dataType: 'script',
            cache: true,
            success: function () {
              Munchkin.init(settings.marketo_ma.key, settings.marketo_ma.initParams);
              if (typeof settings.marketo_ma.actions !== 'undefined') {
                jQuery.each(settings.marketo_ma.actions, function () {
                  Drupal.behaviors.marketo_ma.marketoMunchkinFunction(this.action, this.data, this.hash);
                });
              }
            }
          });
        }
      });
    },
    marketoMunchkinFunction: function(actionType, data, hash) {
      Munchkin.munchkinFunction(actionType, data, hash);
    }
  };
})(jQuery, Drupal);
