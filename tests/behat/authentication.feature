Feature: Authenticate institution
    In order to use the plugin
    As an institution admin
    I need to be able to authenticate the plugin

    Background:
        Given institutions "Institution One" and "Institution Two" exist
          And I am the admin of "Institution One" and "Institution Two"
          And "Institution Two" is not authenticated
          And I log in as institution admin
          And I go to the institution admin page
         Then I should see "Open Badge Factory"

    @javascript
    Scenario: Select Institution
#        When I follow "Open Badge Factory" <-- cannot be found for some reason
        Given I go to Open Badge Factory management page
          And I select "Institution Two" from "Instituutio"
         Then I should see "Asetukset"
          But I should not see "Osaamismerkit"

    @javascript
    Scenario: Authenticate Institution
        Given I go to Open Badge Factory management page
          And I select "Institution Two" from "Instituutio"
          And I submit a wrong token
         Then I should see "Valtuutus epäonnistui"
#         When I submit a valid token via management page
#         Then I should see "Osaamismerkit"
        