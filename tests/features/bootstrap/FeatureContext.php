<?php

use Behat\Behat\Tester\Exception\PendingException;
use Drupal\DrupalExtension\Context\RawDrupalContext;
use Drupal\DrupalExtension\Context\DrupalContext;
use Drupal\DrupalExtension\Context\DrushContext;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Gherkin\Node\PyStringNode;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;

/**
 * Defines application features from the specific context.
 */
class FeatureContext extends RawDrupalContext implements SnippetAcceptingContext {

  private $params = array();

  /** @var DrupalContext */
  private $drupalContext;

  /** @var DrushContext */
  private $drushContext;

  /** @BeforeScenario */
  public function gatherContexts(BeforeScenarioScope $scope) {
    $environment = $scope->getEnvironment();
    $this->drupalContext = $environment->getContext('Drupal\DrupalExtension\Context\DrupalContext');
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
   * Reinstalls Marketo MA modules.
   *
   * @Given I reinstall all Marketo MA modules
   */
  public function reinstallMarketoMaModules() {
    $module_list = array('marketo_ma', 'marketo_ma_user', 'marketo_ma_webform');

    $this->uninstallMarketoMaModules();
    module_enable($module_list);
    
    $this->drupalContext->assertCacheClear();
    $this->drupalContext->assertCacheClear();

    foreach ($module_list as $module) {
      if (!module_exists($module)) {
        $message = sprintf('Module "%s" is not enabled.', $module);
        throw new \Exception($message);
      }
//      $this->getDriver('drush')->drush("pm-enable", array($module), array("yes" => NULL));
//      $this->drushContext->assertDrushCommandWithArgument("pm-info", "$module --fields=status --format=list");
//      $this->drushContext->assertDrushOutput("enabled");
    }
  }

  /**
   * Uninstalls all Marketo MA modules.
   *
   * @Given I uninstall all Marketo MA modules
   */
  public function uninstallMarketoMaModules() {
    $module_list = array('marketo_ma', 'marketo_ma_user', 'marketo_ma_webform');

    module_disable($module_list);
//    $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
//    echo $this->drushContext->readDrushOutput();

    drupal_uninstall_modules($module_list);
//    $this->drupalContext->assertCacheClear();
//    $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
//    echo $this->drushContext->readDrushOutput();

    foreach ($module_list as $module) {
      if (module_exists($module)) {
//        $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
//        echo $this->drushContext->readDrushOutput();
        $message = sprintf('Module "%s" is not enabled.', $module);
        throw new \Exception($message);
      }
    }
  }

  /**
   * Reinstalls the given modules and asserts that they are enabled.
   *
   * @Given the :modules module(s) is/are clean
   */
  public function assertModulesClean($modules) {
    $this->assertModulesUninstalled($modules);
    $this->assertModulesEnabled($modules);
  }

  /**
   * Asserts that the given modules are enabled
   *
   * @Given the :modules module(s) is/are enabled
   */
  public function assertModulesEnabled($modules) {
    $module_list = preg_split("/,\s*/", $modules);
    module_enable($module_list, TRUE);
    foreach ($module_list as $module) {
      if (!module_exists($module)) {
        $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
        echo $this->drushContext->readDrushOutput();
        $message = sprintf('Module "%s" is not enabled.', $module);
        throw new \Exception($message);
      }
    }
  }

  /**
   * Asserts that the given modules are disabled
   *
   * @Given the :modules module(s) is/are disabled
   */
  public function assertModulesDisabled($modules) {
    $module_list = preg_split("/,\s*/", $modules);
    module_disable($module_list, TRUE);
    foreach ($module_list as $module) {
      if (module_exists($module)) {
        $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
        echo $this->drushContext->readDrushOutput();
        $message = sprintf('Module "%s" is not disabled.', $module);
        throw new \Exception($message);
      }
    }
  }

  /**
   * Asserts that the given modules are uninstalled
   *
   * @Given the :modules module(s) is/are uninstalled
   */
  public function assertModulesUninstalled($modules) {
    $module_list = preg_split("/,\s*/", $modules);
    $this->assertModulesDisabled($modules);
    drupal_uninstall_modules($module_list, TRUE);
    foreach ($module_list as $module) {
      if (module_exists($module)) {
        $this->drushContext->assertDrushCommandWithArgument("pm-list", '--package="Marketo"');
        echo $this->drushContext->readDrushOutput();
        $message = sprintf('Module "%s" could not be uninstalled.', $module);
        throw new \Exception($message);
      }
    }
  }

  /**
   * Creates content of the given type and navigates to a path belonging to it.
   *
   * @Given I am accessing :path belonging to a/an :type (content )with the title :title
   */
  public function accessNodePath($path, $type, $title) {
    // @todo make this easily extensible.
    $node = (object) array(
          'title' => $title,
          'type' => $type,
          'body' => $this->getRandom()->string(255),
    );
    $saved = $this->nodeCreate($node);
    // Set internal page on the new node.
    $this->getSession()->visit($this->locatePath('/node/' . $saved->nid . $path));
  }

  /**
   * @Given Marketo MA is configured using settings from :config
   */
  public function marketoMaIsConfiguredUsingSettingsFrom($config) {
    $this->assertModulesClean("marketo_ma, marketo_ma_user, marketo_ma_webform");

    $settings = array_merge($this->params['marketo_default_settings'], $this->params[$config]);
    foreach ($settings as $key => $value) {
      variable_set($key, $value);
    }
  }

  /**
   * @Given I populate the Marketo MA config using :config
   */
  public function iPopulateConfigFromBehatYml($config) {
    $settings = array_merge($this->params['marketo_default_settings'], $this->params[$config]);
    foreach ($settings as $key => $value) {
      variable_set($key, $value);
    }
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
