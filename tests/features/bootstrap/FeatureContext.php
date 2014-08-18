<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException,
    Behat\Behat\Context\Step;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Drupal\DrupalExtension\Context\DrupalContext,
    Drupal\DrupalExtension\Event\EntityEvent;

/**
 * Features context.
 */
// class FeatureContext extends BehatContext
class FeatureContext extends DrupalContext {

  private $params = array();

  /**
   * Initializes context.
   * Every scenario gets its own context object.
   *
   * @param array $parameters context parameters (set them up through behat.yml)
   */
  public function __construct(array $parameters) {
    $this->params = $parameters;
  }

  /**
   * @Given /^Marketo MA is configured using settings from "(?P<config>[^"]*)"$/
   */
  public function marketoMaIsConfiguredUsingSettingsFrom($config) {
    $output = array();
    $modules = array('marketo_ma_user', 'marketo_ma_webform', 'marketo_ma');
    foreach ($modules as $module) {
      $output[] = $this->getDriver('drush')->drush("pm-disable", array($module), array("yes" => NULL));
    }
    foreach ($modules as $module) {
      $output[] = $this->getDriver('drush')->drush("pm-uninstall", array($module), array("yes" => NULL));
    }
    foreach (array_reverse($modules) as $module) {
      $output[] = $this->getDriver('drush')->drush("pm-enable", array($module), array("yes" => NULL));
    }

    $settings = array_merge($this->params['marketo_default_settings'], $this->params[$config]);
    foreach ($settings as $key => $value) {
      $output[] = $this->getDriver('drush')->drush("vset", array($key, "'" . json_encode($value) . "'"), array('format' => 'json'));
    }
  }

  /**
   * @Given /^I populate the Marketo MA config using "(?P<config>[^"]*)"$/
   */
  public function iPopulateConfigFromBehatYml($config) {
    $settings = array_merge($this->params['marketo_default_settings'], $this->params[$config]);
    foreach ($settings as $key => $value) {
      $output[] = $this->getDriver('drush')->drush("vset", array($key, "'" . json_encode($value) . "'"), array('format' => 'json'));
    }
  }

  /**
   * @Given /^these modules are enabled/
   */
  public function theseModulesAreInstalled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
      $steps[] = new Step\When("I run drush \"pm-enable\" \"$module --y\"");
      $steps[] = new Step\When("I run drush \"pm-info\" \"$module --fields=status --format=list\"");
      $steps[] = new Step\Then("drush output should contain \"enabled\"");
    }
    return $steps;
  }

  /**
   * @Given /^these modules are disabled/
   */
  public function theseModulesAreDisabled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
      $steps[] = new Step\When("I run drush \"pm-disable\" \"$module --y\"");
      $steps[] = new Step\When("I run drush \"pm-info\" \"$module --fields=status --format=list\"");
      $steps[] = new Step\Then("drush output should contain \"disabled\"");
    }
    return $steps;
  }

  /**
   * @Given /^these modules are uninstalled$/
   */
  public function theseModulesAreUninstalled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
      $steps[] = new Step\When("I run drush \"pm-disable\" \"$module --y\"");
      $steps[] = new Step\When("I run drush \"pm-uninstall\" \"$module --y\"");
      $steps[] = new Step\When("I run drush \"pm-info\" \"$module --fields=status --format=list\"");
      $steps[] = new Step\Then("drush output should contain \"not installed\"");
    }
    return $steps;
  }

  /**
   * @Given /^I visit path "([^"]*)" belonging to a "([^"]*)" node with the title "([^"]*)"$/
   */
  public function iVisitPathBelongingToANodeWithTheTitle($path, $type, $title) {
    // @todo make this easily extensible.
    $node = (object) array(
        'title' => $title,
        'type' => $type,
        'body' => $this->getDrupal()->random->string(255),
    );
    $this->dispatcher->dispatch('beforeNodeCreate', new EntityEvent($this, $node));
    $saved = $this->getDriver()->createNode($node);
    $this->dispatcher->dispatch('afterNodeCreate', new EntityEvent($this, $saved));
    $this->nodes[] = $saved;

    // Set internal page on the new node.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid . $path));
  }

  /**
   * @Then /^Munchkin tracking should be enabled$/
   */
  public function assertMunchkinTrackingEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (Drupal.settings.marketo_ma === undefined) ? false : Drupal.settings.marketo_ma.track;");
    if ($enabled !== TRUE) {
      throw new Exception("Munchkin tracking is excpected to be ON but is currently OFF");
    }
  }

  /**
   * @Then /^Munchkin tracking should not be enabled$/
   * @Then /^Munchkin tracking should be disabled$/
   */
  public function assertMunchkinTrackingNotEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (Drupal.settings.marketo_ma === undefined) ? false : Drupal.settings.marketo_ma.track;");
    if ($enabled !== FALSE) {
      throw new Exception("Munchkin tracking is expected to be OFF but is currently ON");
    }
  }

  /**
   * @Given /^I evaluate script:$/
   */
  public function iEvaluateScript(PyStringNode $script) {
    $this->getSession()->evaluateScript($script->getRaw());
  }

  /**
   * @Given /^I execute script:$/
   */
  public function iExecuteScript(PyStringNode $script) {
    $this->getSession()->executeScript($script->getRaw());
  }

  /**
   * @Given /^given javascript variable "([^"]*)" equals "([^"]*)"$/
   */
  public function givenJavascriptVariableEquals($variable, $value) {
    $result = $this->getSession()->evaluateScript("$variable == $value");
    if ($result === FALSE) {
      throw new \Exception(sprintf("The variable '%s' was expected to be '%s' but evaluated to %s", $variable, $value, $result));
    }
  }

  /**
   * @Given /^given javascript variable "([^"]*)" does not equal "([^"]*)"$/
   */
  public function givenJavascriptVariableDoesNotEqual($variable, $value) {
    throw new PendingException();
  }

  /**
   * @Given /^I take a dump$/
   */
  public function iTakeADump() {
    var_dump($this->params['marketo_fields']);
  }

}
