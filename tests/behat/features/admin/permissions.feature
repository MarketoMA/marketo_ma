@api @permissions
Feature: Module permissions
  In order to prove that module permissions are working properly
  As a variety of user types
  I need to attempt to accesss portions of the system ensuring expected results are returned

  Background: Fresh module install
    Given all Marketo MA modules are clean and using 'marketo_test_settings'
    
  Scenario: Ensure core module permissions work as expected
    Given I am an anonymous user
    When I go to "/admin/config/services/marketo-ma"
    Then the response status code should be 403
    And I should see "Access denied"

    Given I am logged in as a user with the "authenticated user" role
    When I go to "/admin/config/services/marketo-ma"
    Then the response status code should be 403
    And I should see "Access denied"

    Given I am logged in as an administrator
    When I go to "/admin/config/services/marketo-ma"
    Then the response status code should be 200
    And I should see the heading "Marketo MA configuration"
    And I should see a "#marketo-ma-settings" element

    Given I am logged in as a user with the "administer marketo" permission
    When I go to "/admin/config/services/marketo-ma"
    Then the response status code should be 200
    And I should see the heading "Marketo MA configuration"
    And I should see a "#marketo-ma-settings" element
