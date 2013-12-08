@login @sandbox.translatewiki.net
Feature: Manage translator sandbox

  As a translation administrator,
  I can review translations submitted by sandboxed users and pass or fail them,
  so that I can promote sandboxed translators to full translators.

  Design:
   - http://commons.wikimedia.org/wiki/File:Translate_UX_Onboarding_designs.pdf

  These scenarios test the Special:TranslatorSandbox page.

  Background:
    Given I am logged in as a translation administrator

  Scenario: There are no users in the sandbox
    Given I am on the Special:ManageTranslatorSandbox page with no users in the sandbox
    Then no users are displayed in the first column
      And I should see 'No requests from new users' in the second column

  Scenario: Existing users can be searched on the list
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I search for 'pupu' in the sandboxed users search field
    Then only users whose name begins with 'pupu' are displayed in the first column
      And I should see '5 requests' at the top of the first column

  Scenario: Searching for non-existing users displays no results
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I search for 'nosuchuser' in the sandboxed users search field
    Then no users are displayed in the first column
      And I should see '0 requests' at the top of the first column

  Scenario: Emptying the search field shows all the users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I search for 'pupu' in the sandboxed users search field
      And I search for '' in the sandboxed users search field
    Then a user whose name begins with 'pupu' is displayed in the first column
      And a user whose name begins with 'orava' is displayed in the first column

  Scenario: Selecting older requests
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Orava1' in the first column
      And I click on the link that says '3 older requests' at the bottom of the first column
    Then I should see the checkbox next to the request from 'Orava1' checked and enabled
      And I should see the checkbox next to the request from 'Pupu1' checked and enabled
      And I should see the checkbox next to the request from 'Orava0' checked and enabled
      And I should see the checkbox next to the request from 'Pupu0' checked and enabled
      And I should see '4 users selected' at the bottom of the first column
      And I should see '4 users selected' in the header of the second column

  Scenario: Searching for a user by language
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type 'ml' in the language filter
    Then only users who translate to language 'ml' are displayed in the first column
      And I should see '2 requests' at the top of the first column
      And I should see '1 user selected' at the bottom of the first column
      And I should see the name of the first user in the first column in the header of the second column
      And I should see the button that clears language selection

  Scenario: Searching for a user by language and selecting all users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type 'uk' in the language filter
      And I click the checkbox to select all users
    Then only users who translate to language 'uk' are displayed in the first column
      And I should see '2 requests' at the top of the first column
      And I should see '2 users selected' at the bottom of the first column
      And I should see '2 users selected' in the header of the second column

  Scenario: Showing user who translate to all languages
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type 'nl' in the language filter
      And I click the button that clears language selection
    Then I should see the checkbox next to the request from 'Orava4' checked and disabled
      And I should see '10 requests' at the top of the first column
      And I should see '1 user selected' at the bottom of the first column
      And I should not see the button that clears language selection

  Scenario: Translation Administrator should be able to see a list of pending requests with usernames in the first column, sorted by the number of translations and the most recent within them, and the first user should be selected
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    Then I should see the userlist in the first column sorted by the number of translations and the most recent within them
      And I should see the checkbox next to the request from 'Orava4' checked and disabled
      And I should see the name of the first user in the first column in the header of the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Clicking on a name of a user who didn't make any translations shows the user information and the action buttons and doesn't show translations
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Orava0' in the first column
    Then I should see the checkbox next to the request from 'Orava0' checked and disabled
      And I should not see any users except 'Orava0' selected
      And I should see 'Orava0' in the header of the second column
      And I should not see any translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Clicking a username when another user is selected selects only the new user; Clicking on a name of a user who made some translations shows the user information and the action buttons and some translations
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Orava0' in the first column
      And I click on 'Orava3' in the first column
    Then I should see the checkbox next to the request from 'Orava3' checked and disabled
      And I should not see any users except 'Orava3' selected
      And I should see 'Orava3' in the header of the second column
      And I should see the details of 3 sandboxed translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Selecting multiple users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Orava4' in the first column
      And I click on the checkbox near 'Pupu3' in the first column
    Then I should see the checkbox next to the request from 'Orava4' checked and enabled
      And I should see the checkbox next to the request from 'Pupu3' checked and enabled
      And I should see '2 users selected' in the header of the second column
      And I should see '2 users selected' at the bottom of the first column
      And I should not see any translations done by the users in the second column
      And I should see the 'Accept all' button displayed in the second column
      And I should see the 'Reject all' button displayed in the second column

  Scenario: Selecting multiple users and then one user again
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Pupu3' in the first column
      And I click on the checkbox near 'Pupu2' in the first column
      And I click on 'Orava2' in the first column
    Then I should see the checkbox next to the request from 'Pupu3' unchecked and enabled
      And I should see the checkbox next to the request from 'Pupu2' unchecked and enabled
      And I should see the checkbox next to the request from 'Orava2' checked and disabled
      And I should see 'Orava2' in the header of the second column
      And I should see '1 user selected' at the bottom of the first column
      And I should see the details of 2 sandboxed translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column

  Scenario: Accepting one user
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Pupu2' in the first column
      And I click the 'Accept' button
    Then I should not see user 'Pupu2' in the first column
      And I should see 'Orava2' in the header of the second column
      And I should see the checkbox next to the request from 'Orava2' checked and disabled
      And I should see '1 user selected' at the bottom of the first column
      And I should see '9 requests' at the top of the first column

  Scenario: Rejecting one user
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Pupu2' in the first column
      And I click the 'Reject' button
    Then I should not see user 'Pupu2' in the first column
      And I should see 'Orava2' in the header of the second column
      And I should see the checkbox next to the request from 'Orava2' checked and disabled
      And I should see '1 user selected' at the bottom of the first column
      And I should see '9 requests' at the top of the first column

  Scenario: Accepting multiple users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Pupu2' in the first column
      And I click on 'Orava3' in the first column
      And I click the 'Accept' button
    Then I should not see user 'Pupu2' in the first column
      And I should not see user 'Orava3' in the first column
      And I should see 'Orava2' in the header of the second column
      And I should see the checkbox next to the request from 'Orava2' checked and disabled
      And I should see '1 user selected' at the bottom of the first column
      And I should see '8 requests' at the top of the first column

  Scenario: Rejecting multiple users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click on 'Pupu2' in the first column
      And I click on 'Orava3' in the first column
      And I click the 'Reject' button
    Then I should not see user 'Pupu2' in the first column
      And I should not see user 'Orava3' in the first column
      And I should see 'Orava2' in the header of the second column
      And I should see the checkbox next to the request from 'Orava2' checked and disabled
      And I should see '1 user selected' at the bottom of the first column
      And I should see '8 requests' at the top of the first column

  Scenario: Accepting all users
    Given I am on the Special:ManageTranslatorSandbox page with users in the sandbox
    When I click the checkbox to select all users
      And I click the 'Accept' button
    Then no users are displayed in the first column
      And I should see '0 requests' at the top of the first column
