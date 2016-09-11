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

  @config @live @production
  Scenario: Configure live module settings
    Given I populate the Marketo MA config using "marketo_settings"
    And I am logged in as an administrator
    When I go to "/admin/config/search/marketo_ma"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."