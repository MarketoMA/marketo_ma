@api @permissions
Feature: Module permissions
  In order to prove that module permissions are working properly
  As a variety of user types
  I need to attempt to accesss portions of the system ensuring expected results are returned

  Background: Fresh module install
    Given all Marketo MA modules are clean and using "marketo_test_settings"
    
  Scenario: Ensure core module permissions work as expected
    Given I am an anonymous user
    When I go to "/admin/config/search/marketo_ma"
    Then the response status code should be 403
    And I should see "Access denied"

    Given I am logged in as a user with the "authenticated user" role
    When I go to "/admin/config/search/marketo_ma"
    Then the response status code should be 403
    And I should see "Access denied"

    Given I am logged in as an administrator
    When I go to "/admin/config/search/marketo_ma"
    Then the response status code should be 200
    And I should see the heading "Marketo MA"
    And I should see a "#marketo-ma-admin-settings-form" element

    Given I am logged in as a user with the "administer marketo" permission
    When I go to "/admin/config/search/marketo_ma"
    Then the response status code should be 200
    And I should see the heading "Marketo MA"
    And I should see a "#marketo-ma-admin-settings-form" element

  @marketo_ma_user
  Scenario: Ensure Marketo MA User specific permissions work as expected
    Given I am an anonymous user
    When I go to "/user"
    Then I should not see the link "Marketo" in the "primary tabs" region
    When I go to "/user/1/marketo"
    Then the response status code should be 403
    
    Given I am logged in as a user with the "authenticated user" role
    When I go to "/user"
    Then I should not see the link "Marketo" in the "primary tabs" region
    When I go to "/user/1/marketo"
    Then the response status code should be 403
    
    Given I am logged in as an administrator
    When I go to "/user"
    Then I should see the link "Marketo" in the "primary tabs" region
    When I click "Marketo" in the "primary tabs" region
    Then I should see the link "Lead" in the "secondary tabs" region
    And I should see the link "Activity" in the "secondary tabs" region
    
    Given I am logged in as a user with the "access own marketo lead data" permissions
    When I go to "/user"
    Then I should see the link "Marketo" in the "primary tabs" region
    When I click "Marketo" in the "primary tabs" region
    Then I should see the link "Lead" in the "secondary tabs" region
    And I should see the link "Activity" in the "secondary tabs" region
    When I go to "/user/1/marketo"
    Then the response status code should be 403
    
    Given I am logged in as a user with the "access all marketo lead data" permissions
    When I go to "/user"
    Then I should see the link "Marketo" in the "primary tabs" region
    When I click "Marketo" in the "primary tabs" region
    Then I should see the link "Lead" in the "secondary tabs" region
    And I should see the link "Activity" in the "secondary tabs" region
    When I go to "/user/1/marketo"
    Then the response status code should be 200
    Then I should see the link "Lead" in the "secondary tabs" region
    And I should see the link "Activity" in the "secondary tabs" region

  @marketo_ma_webform
  Scenario: Ensure Marketo MA Webform specific permissions work as expected
    Given I am an anonymous user
    And I am accessing "/webform/marketo" belonging to a "Webform" with the title "Testorama"
    Then the response status code should be 403
    
    Given I am logged in as a user with the "authenticated user" role
    And I am accessing "/webform/marketo" belonging to a "Webform" with the title "Testorama"
    Then the response status code should be 403
    
    Given I am logged in as a user with the "administrator" role
    And I am accessing "/webform/marketo" belonging to a "Webform" with the title "Testorama"
    Then the response status code should be 200
    And I should see the link "Marketo" in the "secondary tabs" region
    And I should see a "#marketo-ma-webform-settings-form" element