@login @sandbox.translatewiki.net @stash
Feature: Translation stash

  As a translation administrator,
  I can review translations submitted by sandboxed users and pass or fail them,
  so that I can promote sandboxed translators to full translators.

  Design:
   - http://commons.wikimedia.org/wiki/File:Translate_UX_Onboarding_designs.pdf

  These scenarios test the Special:TranslatorSandbox page.

  Background:
    Given I am logged in as a translation administrator
      And I am on the Special:TranslatorSandbox page

  Scenario: Existing users can be searched on the list
    When I search for 'pupu' in the sandboxed users search field
    Then users whose name begins with 'pupu' are displayed in the first column
      And users whose name begins with 'orava' are not displayed in the first column
      And '5 requests' is displayed at the top of the first column

  Scenario: Searching for non-existing users displays no results
    When I search for 'nosuchuser' in the sandboxed users search field
    Then no users are displayed in the first column
      And '0 requests' is displayed at the top of the first column
      And '10 requests' is displayed at the top of the first column

  Scenario: Emptying the search field shows all the users
    When I search for 'pupu' in the sandboxed users search field
      And I search for '' in the sandboxed users search field
    Then users whose name begins with 'pupu' are displayed in the first column
      And users whose name begins with 'orava' are displayed in the first column

  Scenario: Translation Administrator should be able to see a list of pending requests with usernames in the first column, sorted by the number of translations and the most recent within them, and the first user should be selected
    Then I should see the userlist in the first column sorted by the number of translations and the most recent within them
      And I should see the checkbox next to the name of the first user in the first column checked
      And I should see the checkbox next to the name of the first user in the first column disabled
      And I should see the name of the first user in the first column in the header of the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Clicking on a name of a user who didn't make any translations shows the user information and the action buttons and doesn't show translations
    When I click on the username of a user who didn't make any translations in the first column
    Then I should see the checkbox next to the name of the user who didn't make any translations checked
      And I should see the checkbox next to the name of the user who didn't make any translations disabled
      And I should not see any other users selected
      And I should see the name of the user who didn't make any translations in the header of the second column
      And I should not see the details of sandboxed translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Clicking a username when another user is selected selects only the new user; Clicking on a name of a user who made some translations shows the user information and the action buttons and some translations
    When I click on the username of a user who didn't make any translations in the first column
      And I click on the username of a user who made some translations in the first column
    Then I should see the checkbox next to the name of the user who made some translations checked
      And I should see the checkbox next to the name of the user who made some translations disabled
      And I should not see any other users selected
      And I should see the name of the user who made some translations in the header of the second column
      And I should see the details of sandboxed translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column
      And I should see '1 user selected' at the bottom of the first column

  Scenario: Selecting multiple users
    When I click on the username of the first user who made some translations in the first column
      And I click on the checkbox next to the name of the second user who made some translations in the first column
    Then I should see the checkbox next to the name of two users who made some translations checked
      And I should see the checkbox next to the name of two users who made some translations enabled
      And I should see '2 users selected' in the header of the second column
      And I should see '2 users selected' at the bottom of the first column
      And I should not see the details of sandboxed translations done by the user in the second column
      And I should see the 'Accept all' button displayed in the second column
      And I should see the 'Reject all' button displayed in the second column

  Scenario: Selecting multiple users and then one user again
    When I click on the username of the first user who made some translations in the first column
      And I click on the checkbox next to the name of the second user who made some translations in the first column
      And I click on the checkbox next to the name of the first user who made some translations in the first column
    Then I should see the checkbox next to the name of the first user who made some translations unchecked
      And I should see the checkbox next to the name of the second user who made some translations checked
      And I should see the checkbox next to the name of two users who made some translations disabled
      And I should see '1 user selected' in the header of the second column
      And I should see '1 user selected' at the bottom of the first column
      And I should see the details of sandboxed translations done by the user in the second column
      And I should see the 'Accept' button displayed in the second column
      And I should see the 'Reject' button displayed in the second column

  Scenario: Accepting one user
    When I click on the username of a user who made some translations in the first column
      And I click the 'Accept' button
    Then the user should be accepted XXX

  Scenario: Rejecting one user
    When I click on the username of a user who made some translations in the first column
      And I click the 'reject' button
    Then the user should be rejected XXX

  Scenario: Accepting multiple users
    When I click on the username of a user who made some translations in the first column
      And I click on the username of a user who made some translations in the first column
      And I click the 'Accept all' button
    Then both users should be accepted XXX

  Scenario: Rejecting multiple users
    When I click on the username of a user who made some translations in the first column
      And I click on the username of a user who made some translations in the first column
      And I click the 'Reject all' button
    Then both users should be accepted XXX
