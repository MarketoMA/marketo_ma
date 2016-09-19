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

    When I fill in "marketo_ma_munchkin_account_id" with "bogus000"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus001"
    And I fill in "marketo_ma_instance_host" with "bogus01"
    And I fill in "marketo_ma_munchkin_lead_source" with "bogus02"
    And I check "marketo_ma_logging"
    And I fill in "marketo_ma_munchkin_partition" with "bogus03"
    And I fill in "marketo_ma_munchkin_altIds" with "bogus0301"
    And I fill in "marketo_ma_munchkin_cookieLifeDays" with "bogus0302"
    And I fill in "marketo_ma_munchkin_clickTime" with "bogus0303"
    And I fill in "marketo_ma_munchkin_cookieAnon" with "bogus0304"
    And I fill in "marketo_ma_munchkin_domainLevel" with "bogus0305"
    And I fill in "marketo_ma_munchkin_disableClickDelay" with "bogus0306"
    And I fill in "marketo_ma_munchkin_asyncOnly" with "bogus0307"
    And I fill in "marketo_ma_rest_endpoint" with "bogus04"
    And I fill in "marketo_ma_rest_identity" with "bogus05"
    And I fill in "marketo_ma_rest_client_id" with "bogus06"
    And I fill in "marketo_ma_rest_client_secret" with "bogus07"
    And I fill in "marketo_ma_rest_proxy_host" with "bogus08"
    And I fill in "marketo_ma_rest_proxy_port" with "bogus09"
    And I fill in "marketo_ma_rest_proxy_login" with "bogus10"
    And I fill in "marketo_ma_rest_proxy_password" with "bogus11"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."
    And the "marketo_ma_munchkin_account_id" field should contain "bogus000"
    And the "marketo_ma_munchkin_api_private_key" field should contain "bogus001"
    And the "marketo_ma_instance_host" field should contain "bogus01"
    And the "marketo_ma_munchkin_lead_source" field should contain "bogus02"
    And the "marketo_ma_logging" checkbox should be checked
    And the "marketo_ma_munchkin_partition" field should contain "bogus03"
    And the "marketo_ma_munchkin_altIds" field should contain "bogus0301"
    And the "marketo_ma_munchkin_cookieLifeDays" field should contain "bogus0302"
    And the "marketo_ma_munchkin_clickTime" field should contain "bogus0303"
    And the "marketo_ma_munchkin_cookieAnon" field should contain "bogus0304"
    And the "marketo_ma_munchkin_domainLevel" field should contain "bogus0305"
    And the "marketo_ma_munchkin_disableClickDelay" field should contain "bogus0306"
    And the "marketo_ma_munchkin_asyncOnly" field should contain "bogus0307"
    And the "marketo_ma_rest_endpoint" field should contain "bogus04"
    And the "marketo_ma_rest_identity" field should contain "bogus05"
    And the "marketo_ma_rest_client_id" field should contain "bogus06"
    And the "marketo_ma_rest_client_secret" field should contain "bogus07"
    And the "marketo_ma_rest_proxy_host" field should contain "bogus08"
    And the "marketo_ma_rest_proxy_port" field should contain "bogus09"
    And the "marketo_ma_rest_proxy_login" field should contain "bogus10"
    And the "marketo_ma_rest_proxy_password" field should contain "bogus11"

    When I select the radio button "REST API (Synchronous)" with the id "edit-marketo-ma-tracking-method-rest"
    And I press "Save configuration"
    Then I should see "Unable to validate REST API settings."

    When I populate the Marketo MA config using "marketo_default_settings"
    And I go to "/admin/config/search/marketo_ma"
    And I press "Save configuration"
    Then I should see "Account ID field is required."
    And I should see "API Private Key field is required."

  @javascript
  Scenario: Munchkin Advanced Initialization Parameters
    Given I populate the Marketo MA config using "marketo_test_settings"

    When I am logged in as an administrator
    And I go to "/admin/config/search/marketo_ma"
    And I click "Advanced Initialization Parameters"
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
    Then I should not see "Unable to validate REST API settings."
    And I should see "The configuration options have been saved."
