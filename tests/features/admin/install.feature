@api
Feature: Module setup
  In order to prove that this module can be installed and uninstalled cleanly
  As an administrator
  I need to do the following

  Background: Reset to a clean state
    Given I reinstall all Marketo MA modules

  @install
  Scenario: Install all Marketo MA modules
    Given I am logged in as an administrator
    When I go to "/admin/config/search/marketo_ma"
    Then I should see the heading "Marketo MA"
    And I should see a "#marketo-ma-admin-settings-form" element

  @uninstall
  Scenario: Disable and uninstall all Marketo MA modules
    Given I run drush "vset" "marketo_ma_bogus 'bogus'"

    Given I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."

    Given I uninstall all Marketo MA modules
    And I run drush "vget" "marketo_ma --format=json"
    Then drush output should contain '{"marketo_ma_bogus":"bogus"}'
    When I run drush "sqlq" "'show tables'"
    Then drush output should not contain "marketo_ma"
