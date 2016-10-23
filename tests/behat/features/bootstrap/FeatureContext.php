<?php

use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Tester\Exception\PendingException;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  private $params = array();

  /**
   * Initializes context.
   *
   * Every scenario gets its own context instance.
   * You can also pass arbitrary arguments to the
   * context constructor through behat.yml.
   */
  public function __construct(array $parameters) {
    $this->params = $parameters;
  }

  /**
   * Resets all Marketo MA modules to their default enabled state.
   *
   * @Given all Marketo MA modules are clean
   * @Given all Marketo MA modules are clean and using :config
   * @Given all Marketo MA modules are clean and using :config with lead fields :fields
   */
  public function allMarketoMaModulesClean($config = 'marketo_default_settings', $fields = 'marketo_default_lead_fields') {
    $module_list = array('marketo_ma');

    foreach ($module_list as $module) {
      if (!\Drupal::moduleHandler()->moduleExists($module)) {
        \Drupal::service('module_installer')->install([$module]);
      }
    }

    $this->marketoMaIsConfiguredUsingSettingsFrom($config);
//    $this->iPopulatedLeadFieldsUsingConfig($fields);
//    drupal_flush_all_caches();

    foreach ($module_list as $module) {
      if (!\Drupal::moduleHandler()->moduleExists($module)) {
        $message = sprintf('Module "%s" could not be enabled.', $module);
        throw new \Exception($message);
      }
    }
  }

  /**
   * @Given Marketo MA is configured using settings from :config
   */
  public function marketoMaIsConfiguredUsingSettingsFrom($config) {
    $cf = \Drupal::service('config.factory');
    $settings = array_replace_recursive($this->params['marketo_default_settings'], $this->params[$config]);
    $cf->getEditable('marketo_ma.settings')->setData($settings)->save();
    drupal_flush_all_caches();
  }

  /**
   * @Then Munchkin init parameter :param should be :value
   */
  public function assertMunchkinInitParameter($param, $value) {
    $result = $this->getSession()->evaluateScript("return drupalSettings.marketo_ma.$param == $value");
    if (!$result) {
      $message = sprintf('Field "drupalSettings.marketo_ma.%s" is not equal to "%s"', $param, $value);
      throw new \Exception($message);
    }
  }

  /**
   * @Then Munchkin tracking should be enabled
   */
  public function assertMunchkinTrackingEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (typeof drupalSettings === 'undefined' || typeof drupalSettings.marketo_ma === 'undefined') ? false : drupalSettings.marketo_ma.track;");
    if ($enabled !== TRUE) {
      throw new Exception("Munchkin tracking is excpected to be ON but is currently OFF");
    }
  }

  /**
   * @Then Munchkin tracking should not be enabled
   * @Then Munchkin tracking should be disabled
   */
  public function assertMunchkinTrackingNotEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (typeof drupalSettings === 'undefined' || typeof drupalSettings.marketo_ma === 'undefined') ? false : drupalSettings.marketo_ma.track;");
    if ($enabled !== FALSE) {
      throw new Exception("Munchkin tracking is expected to be OFF but is currently ON");
    }
  }

  /**
   * @Given I take a dump
   */
  public function iTakeADump() {
    var_dump($this->params);
  }

}
