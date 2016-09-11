@api
Feature: Marketo MA Webform features
  In order to prove that the marketo_ma_webform module is working properly
  As a developer
  I need all of these tests to run successfully

  Background: Fresh module install
    Given all Marketo MA modules are clean and using "marketo_test_settings"

  Scenario: Webform configuration
    Given I am logged in as a user with the "administrator" role
    And I am viewing a "Webform" titled "Testorama"
    And I click "Webform" in the "primary tabs" region
    Then I should see a "form#webform-components-form" element

    When I fill in "edit-add-name" with "First Name"
    And I press "Add"
    And I press "Save component"
    Then I should see "New component First Name added"

    When I fill in "edit-add-name" with "Last Name"
    And I press "Add"
    And I press "Save component"
    Then I should see "New component Last Name added"

    When I fill in "edit-add-name" with "Email Address"
    And I press "Add"
    And I press "Save component"
    Then I should see "New component Email Address added"

    When I click "Marketo" in the "secondary tabs" region
    And I check "edit-marketo-ma-webform-is-active"
    And I select "firstName" from "First Name (first_name)"
    And I select "lastName" from "Last Name (last_name)"
    And I select "email" from "Email Address (email_address)"
    And I press "Save"
    Then I should see "The configuration options have been saved"

    Given I am an anonymous user
    And I visit the node titled "Testorama"
    Then the "First Name" field should contain ""
    And the "Last Name" field should contain ""
    And the "Email Address" field should contain ""