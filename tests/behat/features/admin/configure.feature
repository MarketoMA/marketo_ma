@api
Feature: Module configuration
  In order to use the Marketo MA modules
  As an administrator
  I must configure the module settings

  Background: Fresh module install
    Given all Marketo MA modules are clean
    
  @config
  Scenario: Configure module settings
    Given I am logged in as an administrator
    And I am on the homepage
    
    When I go to "/admin/config/search/marketo_ma"
    And I press "Save configuration"
    Then I should see "Account ID field is required."
    And I should see "API Private Key field is required."
    
    When I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."

    When I select the radio button "SOAP API (Synchronous)" with the id "edit-marketo-ma-tracking-method-soap"
    And I press "Save configuration"
    Then I should see "Unable to validate SOAP API settings."

  @javascript
  Scenario: Munchkin Advanced Initialization Parameters
    Given I populate the Marketo MA config using "marketo_test_settings"

    When I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    And I break
    And I click "Advanced Initialization Parameters"
    And I break
    And I fill in "marketo_ma_munchkin_partition" with "100"
    And I fill in "marketo_ma_munchkin_altIds" with "AAA-AAA-AAA, BBB-BBB-BBB"
    And I fill in "marketo_ma_munchkin_cookieLifeDays" with "200"
    And I fill in "marketo_ma_munchkin_clickTime" with ""
    And I fill in "marketo_ma_munchkin_cookieAnon" with "1"
    And I fill in "marketo_ma_munchkin_domainLevel" with "3"
    And I fill in "marketo_ma_munchkin_disableClickDelay" with "0"
    And I fill in "marketo_ma_munchkin_asyncOnly" with "0"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."

    When I am an anonymous user
    And I am on the homepage
    Then Munchkin init parameter "initParams.wsInfo" should be "'100'"
    And Munchkin init parameter "initParams.altIds[0]" should be "'AAA-AAA-AAA'"
    And Munchkin init parameter "initParams.altIds[1]" should be "'BBB-BBB-BBB'"
    And Munchkin init parameter "initParams.cookieLifeDays" should be "'200'"
    And Munchkin init parameter "initParams.clickTime" should be "undefined"
    And Munchkin init parameter "initParams.cookieAnon" should be "true"
    And Munchkin init parameter "initParams.domainLevel" should be "'3'"
    And Munchkin init parameter "initParams.disableClickDelay" should be "false"
    And Munchkin init parameter "initParams.asyncOnly" should be "false"

    Given I populate the Marketo MA config using "marketo_test_settings"

    When I am an anonymous user
    And I am on the homepage
    Then Munchkin init parameter "initParams.wsInfo" should be "undefined"
    And Munchkin init parameter "initParams.altIds" should be "undefined"
    And Munchkin init parameter "initParams.cookieLifeDays" should be "undefined"
    And Munchkin init parameter "initParams.clickTime" should be "undefined"
    And Munchkin init parameter "initParams.cookieAnon" should be "undefined"
    And Munchkin init parameter "initParams.domainLevel" should be "undefined"
    And Munchkin init parameter "initParams.disableClickDelay" should be "undefined"
    And Munchkin init parameter "initParams.asyncOnly" should be "undefined"

  @config @live @production
  Scenario: Configure live module settings
    Given I populate the Marketo MA config using "marketo_settings"
    And I am logged in as an administrator
    When I go to "/admin/config/search/marketo_ma"
    And I press "Save configuration"
    Then I should not see "Unable to validate SOAP API settings."
    And I should see "The configuration options have been saved."
