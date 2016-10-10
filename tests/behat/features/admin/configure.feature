@api
Feature: Module configuration
  In order to use the Marketo MA modules
  As an administrator
  I must configure the module settings

  Background: Fresh module install
    Given I run drush "pm-uninstall marketo_ma --y"
    Then I run drush "en marketo_ma --y"
    
  @config
  Scenario: Configure module settings
    Given I am logged in as an administrator
    And I am on the homepage

    When I go to "/admin/config/services/marketo-ma"
    And I fill in "munchkin_account_id" with "bogus000"
    And I fill in "munchkin_javascript_library" with "bogus0000"
    And I fill in "munchkin_api_private_key" with "bogus001"
    And I fill in "instance_host" with "bogus01"
    And I fill in "munchkin_lead_source" with "bogus02"
    And I check "logging"
    And I fill in "munchkin_partition" with "bogus03"
    And I fill in "munchkin_altIds" with "bogus0301"
    And I fill in "munchkin_cookieLifeDays" with "bogus0302"
    And I fill in "munchkin_clickTime" with "bogus0303"
    And I fill in "munchkin_cookieAnon" with "bogus0304"
    And I fill in "munchkin_domainLevel" with "bogus0305"
    And I fill in "munchkin_disableClickDelay" with "bogus0306"
    And I fill in "munchkin_asyncOnly" with "bogus0307"
    And I fill in "rest_client_id" with "bogus06"
    And I fill in "rest_client_secret" with "bogus07"
    And I check "rest_batch_requests"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."
    And the "munchkin_account_id" field should contain "bogus000"
    And the "munchkin_javascript_library" field should contain "bogus0000"
    And the "munchkin_api_private_key" field should contain "bogus001"
    And the "instance_host" field should contain "bogus01"
    And the "munchkin_lead_source" field should contain "bogus02"
    And the "logging" checkbox should be checked
    And the "munchkin_partition" field should contain "bogus03"
    And the "munchkin_altIds" field should contain "bogus0301"
    And the "munchkin_cookieLifeDays" field should contain "bogus0302"
    And the "munchkin_clickTime" field should contain "bogus0303"
    And the "munchkin_cookieAnon" field should contain "bogus0304"
    And the "munchkin_domainLevel" field should contain "bogus0305"
    And the "munchkin_disableClickDelay" field should contain "bogus0306"
    And the "munchkin_asyncOnly" field should contain "bogus0307"
    And the "rest_client_id" field should contain "bogus06"
    And the "rest_client_secret" field should contain "bogus07"
    And the "rest_batch_requests" checkbox should be checked

  @javascript
  Scenario: Munchkin Advanced Initialization Parameters
    Given Marketo MA is configured using settings from 'marketo_test_settings'

    When I am logged in as an administrator
    And I go to "/admin/config/services/marketo-ma"
    And I click "Advanced Initialization Parameters"
    And I fill in "munchkin_partition" with "100"
    And I fill in "munchkin_altIds" with "AAA-AAA-AAA, BBB-BBB-BBB"
    And I fill in "munchkin_cookieLifeDays" with "200"
    And I fill in "munchkin_clickTime" with ""
    And I fill in "munchkin_cookieAnon" with "1"
    And I fill in "munchkin_domainLevel" with "3"
    And I fill in "munchkin_disableClickDelay" with "0"
    And I fill in "munchkin_asyncOnly" with "0"
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

    Given Marketo MA is configured using settings from 'marketo_test_settings'

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
