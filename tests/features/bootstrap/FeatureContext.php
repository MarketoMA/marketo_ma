<?php

use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Gherkin\Node\TableNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Drupal\DrupalExtension\Context\DrushContext;
use Drupal\DrupalExtension\Context\MinkContext;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  private $params = array();

  /** @var MinkContext */
  private $minkContext;

  /** @var DrushContext */
  private $drushContext;

  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
//    $this->minkContext = $environment->getContext('Drupal\DrupalExtension\Context\MinkContext');
    $this->drushContext = $environment->getContext('Drupal\DrupalExtension\Context\DrushContext');
  }
  
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
   * @Given I populate the Marketo MA config using :config
   */
  public function iPopulateConfigFromBehatYml($config) {
    $settings = array_merge($this->params['marketo_default_settings'], $this->params[$config]);
    foreach ($settings as $key => $value) {
      $output[] = $this->getDriver('drush')->drush("vset", array($key, "'" . json_encode($value) . "'"), array('format' => 'json'));
    }
  }

  /**
   * @Given these modules are enabled
   */
  public function theseModulesAreInstalled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
//      $this->drushContext->assertDrushCommandWithArgument("pm-enable", "$module --y");
      $this->getDriver('drush')->drush("pm-enable", array($module), array("yes" => NULL));
      $this->drushContext->assertDrushCommandWithArgument("pm-info", "$module --fields=status --format=list");
      $this->drushContext->assertDrushOutput("enabled");
    }
    return $steps;
  }

  /**
   * @Given these modules are disabled
   */
  public function theseModulesAreDisabled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
//      $this->drushContext->assertDrushCommandWithArgument("pm-disable", "$module --y");
      $this->getDriver('drush')->drush("pm-disable", array($module), array("yes" => NULL));
      $this->drushContext->assertDrushCommandWithArgument("pm-info", "$module --fields=status --format=list");
      $this->drushContext->assertDrushOutput("disabled");
    }
    return $steps;
  }

  /**
   * @Given these modules are uninstalled
   */
  public function theseModulesAreUninstalled(TableNode $table) {
    $steps = array();
    foreach ($table->getHash() as $row) {
      $module = $row['module'];
//      $this->drushContext->assertDrushCommandWithArgument("pm-disable", "$module --y");
//      $this->drushContext->assertDrushCommandWithArgument("pm-uninstall", "$module --y");
      $this->getDriver('drush')->drush("pm-disable", array($module), array("yes" => NULL));
      $this->getDriver('drush')->drush("pm-uninstall", array($module), array("yes" => NULL));
      $this->drushContext->assertDrushCommandWithArgument("pm-info", "$module --fields=status --format=list");
      $this->drushContext->assertDrushOutput("not installed");
    }
    return $steps;
  }

  /**
   * @Then Munchkin tracking should be enabled
   */
  public function assertMunchkinTrackingEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (Drupal.settings.marketo_ma === undefined) ? false : Drupal.settings.marketo_ma.track;");
    if ($enabled !== TRUE) {
      throw new Exception("Munchkin tracking is excpected to be ON but is currently OFF");
    }
  }

  /**
   * @Then Munchkin tracking should not be enabled
   * @Then Munchkin tracking should be disabled
   */
  public function assertMunchkinTrackingNotEnabled() {
    $enabled = $this->getSession()->evaluateScript("return (Drupal.settings.marketo_ma === undefined) ? false : Drupal.settings.marketo_ma.track;");
    if ($enabled !== FALSE) {
      throw new Exception("Munchkin tracking is expected to be OFF but is currently ON");
    }
  }

  /**
   * @Given I evaluate script:
   */
  public function iEvaluateScript(PyStringNode $script) {
    $this->getSession()->evaluateScript($script->getRaw());
  }

  /**
   * @Given I execute script:
   */
  public function iExecuteScript(PyStringNode $script) {
    $this->getSession()->executeScript($script->getRaw());
  }

  /**
   * @Given given javascript variable :variable equals :value
   */
  public function givenJavascriptVariableEquals($variable, $value) {
    $result = $this->getSession()->evaluateScript("$variable == $value");
    if ($result === FALSE) {
      throw new \Exception(sprintf("The variable '%s' was expected to be '%s' but evaluated to %s", $variable, $value, $result));
    }
  }

  /**
   * @Given given javascript variable :variable does not equal :value
   */
  public function givenJavascriptVariableDoesNotEqual($variable, $value) {
    throw new PendingException();
  }

  /**
   * @Given I take a dump
   */
  public function iTakeADump() {
    var_dump($this->params);
  }

}
