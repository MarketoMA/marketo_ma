Munchkin.init(Drupal.settings.marketo.key);
marketoMunchkinFunction();

function marketoMunchkinFunction() {
  if (typeof Drupal.settings.marketo.data !== 'undefined') {
    mktoMunchkinFunction(Drupal.settings.marketo.lead_type, Drupal.settings.marketo.data, Drupal.settings.marketo.hash);
  }
}