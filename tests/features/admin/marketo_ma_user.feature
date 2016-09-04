@api
Feature: Marketo MA User features
  In order to prove that the marketo_ma_user module is working properly
  As a developer
  I need all of these tests to run successfully

  Background: Modules are clean and users are ready to test
    Given all Marketo MA modules are clean and using "marketo_test_settings"
    And fields:
      | bundle | entity | field_name         | field_type | widget_type |
      | user   | user   | field_firstname123 | text       | text_field  |
      | user   | user   | field_lastname123  | text       | text_field  |
      | user   | user   | field_company123   | text       | text_field  |
    And users:
      | name     | mail                     | field_firstname123 | field_lastname123 | field_company123 | pass     |
      | mmatest1 | mmatest1@mma.example.com | Mma1               | Test1             | MMA Test Co.     | password |
      | mmatest2 | mmatest2@mma.example.com | Mma2               | Test2             | MMA Test Co.     | password |

  @user_field_mapping
  Scenario: Ensure core and custom user fields can be mapped
    Given I am logged in as a user with the "administer marketo" permission

    When I go to "/admin/config/search/marketo_ma"
    Then I should see "[account:uid] (uid)"
    And I should see "[account:name] (name)"
    And I should see "field_firstname123 (field_firstname123)"
    And I should see "field_lastname123 (field_lastname123)"
    And I should see "field_company123 (field_company123)"
    
    When I select "lastName" from "[account:name] (name)"
    And I select "lastName" from "field_company123 (field_company123)"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."
    And the "[account:name] (name)" field should contain "lastName"
    And the "field_company123 (field_company123)" field should contain "lastName"

    When I go to "/user"
    And I click "Edit"
    And I enter "Example Co." for "field_company123"
    And I press "Save"
    Then I should see "The changes have been saved."
    And the "field_company123" field should contain "Example Co."

  @javascript
  Scenario: Mapped user fields are sent to Marketo
    Given I am logged in as a user with the "administer marketo" permission
    When I go to "/admin/config/search/marketo_ma"
    And I click "User Integration"
    And I select "firstName" from "field_firstname123 (field_firstname123)"
    And I select "lastName" from "field_lastname123 (field_lastname123)"
    And I press "Save configuration"
    Then I should see "The configuration options have been saved."
    And I go to "/user/logout"

    When I go to "/user/login"
    And I enter "mmatest1" for "edit-name"
    And I enter "password" for "edit-pass"
    And I press "Log in"
    Then Munchkin associateLead action should send data
      | field     | value                    |
      | Email     | mmatest1@mma.example.com |
      | FirstName | Mma1                     |
      | LastName  | Test1                    |
