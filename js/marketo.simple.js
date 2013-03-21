Munchkin.init(Drupal.settings.marketo.key);
if(typeof Drupal.settings.marketo.data !== 'undefined') marketoMunchkinFunction();

function marketoMunchkinFunction() {
  console.log(Drupal.settings.marketo);
  mktoMunchkinFunction(Drupal.settings.marketo.lead_type, Drupal.settings.marketo.data, Drupal.settings.marketo.hash);
}