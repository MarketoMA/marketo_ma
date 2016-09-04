@api
Feature: Module configuration
  In order to use the Marketo MA modules
  As an administrator
  I must configure the module settings

  Background: Fresh module install
    Given all Marketo MA modules are clean
    
  @config
  Scenario: Configure module settings
    When I am on the homepage
    
    Given I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    When I press "Save configuration"
    Then I should see "Account ID field is required."
    And I should see "API Private Key field is required."
    
    Given I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."

    Given I am logged in as an administrator
    When I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    And I select the radio button "REST API (Synchronous)" with the id "edit-marketo-ma-tracking-method-rest"
    When I press "Save configuration"
    Then I should see "Unable to validate REST API settings."
  
  @config @live
  Scenario: Configure live module settings
    Given I populate the Marketo MA config using "marketo_settings"
    When I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."