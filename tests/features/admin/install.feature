Feature: Module setup
  In order to prove that this module can be installed and uninstalled cleanly
  As an administrator
  I need to do the following

  Background: Reset to a clean state
    Given these modules are uninstalled
    | module |
    | marketo_ma_webform |    
    | marketo_ma_user |
    | marketo_ma |

  @drush @install
  Scenario: Install all Marketo MA modules
    Given these modules are enabled
    | module |
    | marketo_ma |
    | marketo_ma_user |
    | marketo_ma_webform |

    When I am logged in as a user with the "administrator" role
    And I go to "/admin/config/search/marketo_ma"
    Then I should see the heading "Marketo MA"
    And I should see a "#marketo-ma-admin-settings-form" element

  @drush @api @uninstall
  Scenario: Disable and uninstall all Marketo MA modules
    Given these modules are enabled
    | module |
    | marketo_ma |
    | marketo_ma_user |
    | marketo_ma_webform |

    When I am logged in as a user with the "administrator" role
    And I go to "/admin/config/search/marketo_ma"
    And I fill in "marketo_ma_munchkin_account_id" with "bogus"
    And I fill in "marketo_ma_munchkin_api_private_key" with "bogus"
    When I press "Save configuration"
    Then I should see "The configuration options have been saved."

    When these modules are uninstalled
    | module |
    | marketo_ma_user |
    | marketo_ma_webform |
    | marketo_ma |
    And I run drush "vget" "marketo_ma"
    Then drush output should contain "No matching variable found."
