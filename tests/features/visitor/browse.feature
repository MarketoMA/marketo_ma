Feature: Browser tests

  Background: Modules are freshly installed and configured
    Given Marketo MA is configured using settings from "marketo_settings"

  @api @javascript @visitor
  Scenario: Javascript is displayed
    Given I am logged in as a user with the "administrator" role
    
    When I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am viewing a "Article" node with the title "Foo"
    Then Munchkin tracking should be enabled
    
    When I visit path "/edit" belonging to a "Article" node with the title "Bar"
    Then Munchkin tracking should be disabled
    
    When I visit "/admin"
    Then Munchkin tracking should be disabled
    
    When I visit "/admin/config/search/marketo_ma"
    Then Munchkin tracking should be disabled
    
    When I visit "/node/add"
    Then Munchkin tracking should be disabled
    
    When I visit "/node/add/article"
    Then Munchkin tracking should be disabled