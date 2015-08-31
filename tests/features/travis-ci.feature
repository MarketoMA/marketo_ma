@travis
Feature: Travis CI
  In order to run tests for this module
  As a developer
  I must ensure that the Travis CI test environment has been setup successfully

  Scenario: Site is accessiable
    Given I am on the homepage
    Then I should see the heading "Welcome to Site-Install"
  
  Scenario: Friendly URLs are working as expected
    When I visit "/?q=user"
    Then I should see a "#user-login" element
    
    When I visit "/user"
    Then I should see a "#user-login" element
  
  @api
  Scenario: DrupalExtension features are working as expected
    Given I am logged in as a user with the "administrator" role
    And I visit "/admin"
    Then I should see the heading "Administration"
    
    When I run drush "status" "bootstrap"
    Then drush output should contain "Successful"
    