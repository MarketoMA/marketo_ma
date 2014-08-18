Feature: Marketo MA User features
  In order to prove that the marketo_ma_user module is working properly
  As a developer
  I need all of these tests to run successfully

  Background: Reset to a clean state
    Given these modules are uninstalled
    | module |
    | marketo_ma_user |
    | marketo_ma_webform |
    | marketo_ma |

    And these modules are enabled
    | module |
    | marketo_ma |
    | marketo_ma_user |
    | marketo_ma_webform |  
