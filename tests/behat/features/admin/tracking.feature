@api @javascript @visitor
Feature: Browser tests

  Background: Fresh module install
    Given all Marketo MA modules are clean and using 'marketo_test_settings'

  @page_visibility
  Scenario: Page visibilty when using default "Every page except the listed pages"
    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | request_path | {"mode":0,"pages":"\/admin\n\/admin\/*\n\/batch\n\/node\/add*\n\/node\/*\/*\n\/user\/*\/*"} |

    And I am logged in as a user with the "administrator" role
    
    When I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am viewing a "article" with the title "Foo"
    Then Munchkin tracking should be enabled
    
    When I visit "/admin"
    Then Munchkin tracking should be disabled
    
    When I visit "/admin/config/services/marketo-ma"
    Then Munchkin tracking should be disabled
    
    When I visit "/node/add"
    Then Munchkin tracking should be disabled
    
    When I visit "/node/add/article"
    Then Munchkin tracking should be disabled
    
  @page_visibility
  Scenario: Page visibilty when using "The listed pages only"
    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | request_path | {"mode":1,"pages":"\/admin\n\/admin\/*\n\/batch\n\/node\/add*\n\/node\/*\/*\n\/user\/*\/*"} |
    And I am logged in as a user with the "administrator" role
    
    When I am on the homepage
    Then Munchkin tracking should be disabled
     
    When I am viewing a "article" with the title "Foo"
    Then Munchkin tracking should be disabled
   
    When I visit "/admin"
    Then Munchkin tracking should be enabled
    
    When I visit "/admin/config/services/marketo-ma"
    Then Munchkin tracking should be enabled
    
    When I visit "/node/add"
    Then Munchkin tracking should be enabled
    
    When I visit "/node/add/article"
    Then Munchkin tracking should be enabled
    
  @role_visibility
  Scenario: Role visibilty when using default "Add to every role except the selected ones"
    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | user_role | {"mode":1,"roles":[]} |

    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | user_role | {"mode":1,"roles":{"authenticated":"authenticated"}} |
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be disabled

    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | user_role | {"mode":0,"roles":[]} |
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be enabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be enabled

    Given I set the configuration item marketo_ma.settings with key tracking with values:
      | key   | value  |
      | user_role | {"mode":0,"roles":{"authenticated":"authenticated"}} |
    
    When I am an anonymous user
    And I am on the homepage
    Then Munchkin tracking should be disabled
    
    When I am logged in as a user with the "authenticated user" role
    And I am on the homepage
    Then Munchkin tracking should be enabled
