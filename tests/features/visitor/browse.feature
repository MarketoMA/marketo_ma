@api @javascript @visitor
Feature: Browser tests

  @page_visibility
  Scenario: Page visibiilty when using default "All pages except those listed"
    Given Marketo MA is configured using settings from "marketo_default_settings"
    And I am logged in as a user with the "administrator" role
    
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
    
  @page_visibility
  Scenario: Page visibiilty when using "Only the pages listed"
    Given Marketo MA is configured using settings from "marketo_except_page_vis"
    And I am logged in as a user with the "administrator" role
    
    When I am on the homepage
    Then Munchkin tracking should be disabled
    
    When I am viewing a "Article" node with the title "Foo"
    Then Munchkin tracking should be disabled
    
    When I visit path "/edit" belonging to a "Article" node with the title "Bar"
    Then Munchkin tracking should be enabled
    
    When I visit "/admin"
    Then Munchkin tracking should be enabled
    
    When I visit "/admin/config/search/marketo_ma"
    Then Munchkin tracking should be enabled
    
    When I visit "/node/add"
    Then Munchkin tracking should be enabled
    
    When I visit "/node/add/article"
    Then Munchkin tracking should be enabled
    
  @role_visibility
  Scenario: Role visibiilty when using default "All roles except those selected"
    Given Marketo MA is configured using settings from "marketo_default_settings"
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    Given I populate the Marketo MA config using "marketo_role_vis_auth_exclude"
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be disabled

    Given I populate the Marketo MA config using "marketo_role_vis_auth_include"
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be disabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be enabled
