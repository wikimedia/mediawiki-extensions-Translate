@firefox @sandbox.translatewiki.net
Feature: Manage translator sandbox

  As a translation administrator,
  I can review translations submitted by sandboxed users and pass or fail them,
  so that I can promote sandboxed translators to full translators.

  Design:
   - http://commons.wikimedia.org/wiki/File:Translate_UX_Onboarding_designs.pdf

  These scenarios test the Special:TranslatorSandbox page.

  Background:
    Given I am logged in as a translation administrator
      And I have reset my preferences

  Scenario: There are no users in the sandbox
    Given I am on the Translator sandbox management page with no users in the sandbox
    Then no users are displayed in the first column
      And I should see "0 requests" at the top of the first column
      And I should see "0 users selected" at the bottom of the first column
      And I should see "No requests from new users" in the header of the second column
      And I should not see the older requests link at the bottom of the first column

  Scenario: Existing users can be searched on the list
    Given I am on the Translator sandbox management page with users in the sandbox
    When I search for "pupu" in the sandboxed users search field
    Then only users whose name begins with "pupu" are displayed in the first column
      And I should see "5 requests" at the top of the first column

  Scenario: Searching for non-existing users displays no results
    Given I am on the Translator sandbox management page with users in the sandbox
    When I search for "nosuchuser" in the sandboxed users search field
    Then no users are displayed in the first column
      And I should see "0 requests" at the top of the first column

  Scenario: Emptying the search field shows all the users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I search for "pupu" in the sandboxed users search field
      And I search for "" in the sandboxed users search field
    Then a user whose name begins with "pupu" is displayed in the first column
      And a user whose name begins with "orava" is displayed in the first column

  Scenario: Selecting the last request should make the older requests counter disappear
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu0" in the first column
    Then I should not see the older requests link at the bottom of the first column

  Scenario: Selecting older requests
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Orava3" in the first column
      And I click on the link that says "1 older requests" at the bottom of the first column
    Then I should see the checkbox next to the request from "Pupu3" checked
      And I should see the checkbox next to the request from "Pupu3" enabled
      And I should see the checkbox next to the request from "Orava3" checked
      And I should see the checkbox next to the request from "Orava3" enabled
      And I should see the checkbox next to the request from "Pupu2" unchecked
      And I should see "2 users selected" at the bottom of the first column
      And I should see "2 users selected" in the header of the second column

  Scenario: Selecting all users
    Given I am on the Translator sandbox management page with users in the sandbox
      And I click the checkbox to select all users
    Then I should not see the older requests link at the bottom of the first column
      And I should see "11 users selected" at the bottom of the first column
      And I should see "11 users selected" in the header of the second column

  Scenario: Searching for a user by language
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type "he" in the language filter
    Then only users who translate to language "he" are displayed in the first column
      And I should see "3 requests" at the top of the first column
      And I should see "1 user selected" at the bottom of the first column
      And I should see the name of the first user in the first column in the header of the second column
      And I should see the button that clears language selection
      And the direction of the users language filter button is "rtl"
      And the language code of the users language filter button is "he"

  Scenario: Searching for a user by language and selecting all users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type "uk" in the language filter
      And I click the checkbox to select all users
    Then only users who translate to language "uk" are displayed in the first column
      And I should see "3 requests" at the top of the first column
      And I should see "3 users selected" at the bottom of the first column
      And I should see "3 users selected" in the header of the second column

  Scenario: Showing users who translate to all languages
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type "nl" in the language filter
      And I click the button that clears language selection
    Then I should see the checkbox next to the request from "Kissa" checked
      And I should see the checkbox next to the request from "Kissa" disabled
      And I should see "11 requests" at the top of the first column
      And I should see "1 user selected" at the bottom of the first column
      And I should not see the button that clears language selection
      And the direction of the users language filter button is "ltr"
      And the language code of the users language filter button is "en"

  Scenario: Searching for languages to which nobody translates
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click the sandboxed users language filter button
      And I type "be" in the language filter
    Then no users are displayed in the first column
      And I should see "0 requests" at the top of the first column
      And I should see "0 users selected" at the bottom of the first column
      And I should not see the older requests link at the bottom of the first column

  Scenario: Translation Administrator should be able to see a list of pending requests with usernames in the first column, sorted by the number of translations and the most recent within them, and the first user should be selected
    Given I am on the Translator sandbox management page with users in the sandbox
    Then I should see the userlist in the first column sorted by the number of translations and the most recent within them
      And I should see the checkbox next to the request from "Kissa" checked
      And I should see the checkbox next to the request from "Kissa" disabled
      And I should see the name of the first user in the first column in the header of the second column
      And I should see that the user's translations are sorted by the language code
      And I should see the "Accept" button displayed in the second column
      And I should see the "Reject" button displayed in the second column
      And I should see "1 user selected" at the bottom of the first column
      And I should not see the older requests link at the bottom of the first column

  Scenario: Clicking on a name of a user who didn't make any translations shows the user information and the action buttons and doesn't show translations
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Orava0" in the first column
    Then I should see the checkbox next to the request from "Orava0" checked
      And I should see the checkbox next to the request from "Orava0" disabled
      And I should not see any users except "Orava0" selected
      And I should see "Orava0" in the header of the second column
      And I should not see any translations done by the user in the second column
      And I should see the "Accept" button displayed in the second column
      And I should see the "Reject" button displayed in the second column
      And I should see "1 user selected" at the bottom of the first column
      And I should see "11 requests" at the top of the first column
      And I should see that no reminders have been sent to the user

  Scenario: Clicking a username when another user is selected selects only the new user; Clicking on a name of a user who made some translations shows the user information and the action buttons and some translations
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Orava0" in the first column
      And I click on "Orava3" in the first column
    Then I should see the checkbox next to the request from "Orava3" checked
      And I should see the checkbox next to the request from "Orava3" disabled
      And I should not see any users except "Orava3" selected
      And I should see "Orava3" in the header of the second column
      And I should see the details of 3 sandboxed translations done by the user in the second column
      And I should see the "Accept" button displayed in the second column
      And I should see the "Reject" button displayed in the second column
      And I should see "1 user selected" at the bottom of the first column
      And I should see that 3 reminders were sent to the user

  Scenario: Selecting multiple users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Orava4" in the first column
      And I click on the checkbox near "Pupu3" in the first column
    Then I should see the checkbox next to the request from "Orava4" checked
      And I should see the checkbox next to the request from "Orava4" enabled
      And I should see the checkbox next to the request from "Pupu3" checked
      And I should see the checkbox next to the request from "Pupu3" enabled
      And I should see "2 users selected" in the header of the second column
      And I should see "2 users selected" at the bottom of the first column
      And I should not see any translations done by the users in the second column
      And I should see the "Accept all" button displayed in the second column
      And I should see the "Reject all" button displayed in the second column

  Scenario: Selecting multiple users and then one user again
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu3" in the first column
      And I click on the checkbox near "Pupu2" in the first column
      And I click on "Orava2" in the first column
    Then I should see the checkbox next to the request from "Pupu3" unchecked
      And I should see the checkbox next to the request from "Pupu3" enabled
      And I should see the checkbox next to the request from "Pupu2" unchecked
      And I should see the checkbox next to the request from "Pupu2" enabled
      And I should see the checkbox next to the request from "Orava2" checked
      And I should see the checkbox next to the request from "Orava2" disabled
      And I should see "Orava2" in the header of the second column
      And I should see "1 user selected" at the bottom of the first column
      And I should see the details of 2 sandboxed translations done by the user in the second column
      And I should see the "Accept" button displayed in the second column
      And I should see the "Reject" button displayed in the second column
      And I should see the name of language "Nederlands" in the second column
      And I should see that the language of the first translation is "Nederlands"

  Scenario: Selecting a second user with translations and deselecting it
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on the checkbox near "Pupu4" in the first column
      And I click on the checkbox near "Pupu4" in the first column
    Then I should see the checkbox next to the request from "Pupu4" unchecked
      And I should see the checkbox next to the request from "Pupu4" enabled
      And I should see the checkbox next to the request from "Kissa" checked
      And I should see the checkbox next to the request from "Kissa" disabled
      And I should see the details of 5 sandboxed translations done by the user in the second column

  Scenario: Selecting a second user without translations and deselecting it
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu0" in the first column
      And I click on the checkbox near "Orava0" in the first column
      And I click on the checkbox near "Orava0" in the first column
    Then I should see the checkbox next to the request from "Orava0" unchecked
      And I should see the checkbox next to the request from "Orava0" enabled
      And I should see the checkbox next to the request from "Pupu0" checked
      And I should see the checkbox next to the request from "Pupu0" disabled
      And I should not see any translations done by the user in the second column

  Scenario: Selecting a user who wrote a comment when signing up
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Kissa" in the first column
    Then I should see that the user wrote a comment that says "I know some languages, and I'm a developer."

  Scenario: Selecting a user who didn't write a comment when signing up
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu2" in the first column
    Then I should not see that the user wrote a comment

  Scenario: Accepting one user
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu2" in the first column
      And I click the "Accept" button
    Then I should not see user "Pupu2" in the first column
      And I should see "Orava2" in the header of the second column
      And I should see the checkbox next to the request from "Orava2" checked
      And I should see the checkbox next to the request from "Orava2" disabled
      And I should see "1 user selected" at the bottom of the first column
      And I should see "10 requests" at the top of the first column

  Scenario: Rejecting one user
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu2" in the first column
      And I click the "Reject" button
    Then I should not see user "Pupu2" in the first column
      And I should see "Orava2" in the header of the second column
      And I should see the checkbox next to the request from "Orava2" checked
      And I should see the checkbox next to the request from "Orava2" disabled
      And I should see "1 user selected" at the bottom of the first column
      And I should see "10 requests" at the top of the first column

  Scenario: Accepting multiple users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu2" in the first column
      And I click on the checkbox near "Orava3" in the first column
      And I click the "Accept all" button
    Then I should not see user "Pupu2" in the first column
      And I should not see user "Orava3" in the first column
      And I should see "Pupu4" in the header of the second column
      And I should see the checkbox next to the request from "Pupu4" checked
      And I should see the checkbox next to the request from "Pupu4" disabled
      And I should see "1 user selected" at the bottom of the first column
      And I should see "9 requests" at the top of the first column

  Scenario: Rejecting multiple users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Pupu2" in the first column
      And I click on the checkbox near "Orava3" in the first column
      And I click the "Reject all" button
    Then I should not see user "Pupu2" in the first column
      And I should not see user "Orava3" in the first column
      And I should see "Pupu4" in the header of the second column
      And I should see the checkbox next to the request from "Pupu4" checked
      And I should see the checkbox next to the request from "Pupu4" disabled
      And I should see "1 user selected" at the bottom of the first column
      And I should see "9 requests" at the top of the first column

  Scenario: Accepting all users
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click the checkbox to select all users
      And I click the "Accept all" button
    Then no users are displayed in the first column
      And I should see "0 requests" at the top of the first column

  Scenario: Search for users and accept the first user
    Given I am on the Translator sandbox management page with users in the sandbox
    When I search for "pupu" in the sandboxed users search field
      And I click on "Pupu4" in the first column
      And I click the "Accept" button
    Then I should see "Pupu3" in the header of the second column
      And I should see the checkbox next to the request from "Pupu3" checked
      And I should see the checkbox next to the request from "Pupu3" disabled
      And I should see "1 user selected" at the bottom of the first column
      And I should see "4 requests" at the top of the first column

  Scenario: Accepting a user creates a user page
    Given I am on the Translator sandbox management page with users in the sandbox
    When I click on "Kissa" in the first column
      And I click the "Accept" button
      And I go to the userpage of user "Kissa"
    Then I should see a babel box with languages "bn, he, uk, nl, fi"
