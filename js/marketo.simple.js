Munchkin.init(Drupal.settings.marketo.key);
marketoMunchkinFunction();

function marketoMunchkinFunction() {
  console.log(Drupal.settings.marketo);
  mktoMunchkinFunction(Drupal.settings.marketo.lead_type, Drupal.settings.marketo.data, Drupal.settings.marketo.hash);
}