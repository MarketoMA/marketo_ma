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
   * @Given I take a dump
   */
  public function iTakeADump() {
    var_dump($this->params);
  }

}
