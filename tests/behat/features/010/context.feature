@api
Feature: Drupal-specific steps
  In order to prove that the Marketo MA feature context is working properly
  As a developer
  I need to be able to use the steps provided here

  Scenario: Settings merged correctly
    Given all Marketo MA modules are clean
    When I run drush "cget marketo_ma.settings munchkin.account_id"
    Then drush output should contain ": ''"

    Given all Marketo MA modules are clean and using 'marketo_test_settings'
    When I run drush "cget marketo_ma.settings munchkin.account_id"
    Then drush output should not contain ": ''"

    Given Marketo MA is configured using settings from 'marketo_default_settings'
    When I run drush "cget marketo_ma.settings munchkin.account_id"
    Then drush output should contain ": ''"

    Given Marketo MA is configured using settings from 'marketo_test_settings'
    And I run drush "cget marketo_ma.settings munchkin.account_id"
    Then drush output should not contain ": ''"
