@api
Feature: Module configuration
  In order to use the Marketo MA modules
  As an administrator
  I must configure the module settings

  Background: Fresh module install
    Given the "marketo_ma, marketo_ma_user, marketo_ma_webform" modules are uninstalled
    And the "marketo_ma, marketo_ma_user, marketo_ma_webform" modules are enabled
    And the cache has been cleared
    
  @config
  Scenario: Configure module settings
    When I am on the homepage
    
    Given I am logged in as a user with the "administrator" role
    And I go to "/admin/config/search/marketo_ma"
    When I press "Save configuration"
    Then I should see "Account ID field is required."
    And I should see "API Private Key field is required."
    
    Given I am logged in as a user with the "administrator" role
    And I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."

    Given I am logged in as a user with the "administrator" role
    When I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    And I select the radio button "SOAP API (Synchronous)" with the id "edit-marketo-ma-tracking-method-soap"
    When I press "Save configuration"
    Then I should see "Unable to validate SOAP API settings."
  
  @config @live
  Scenario: Configure live module settings
    Given I populate the Marketo MA config using "marketo_settings"
    When I am logged in as a user with the "administrator" role
    And I go to "/admin/config/search/marketo_ma"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."