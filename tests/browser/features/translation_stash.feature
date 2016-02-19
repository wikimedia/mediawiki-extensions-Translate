@firefox @sandbox.translatewiki.net @stash
Feature: Translation stash

  As a new translator, I can make translations in sandbox mode so that a translation administrator
  can check them and give me translator rights, so that I do not have to register and wait for
  approval before contributing translation for the site.

  Design:
   - http://commons.wikimedia.org/wiki/File:Translate_UX_Onboarding_designs.pdf

  These scenarios test the Special:TranslationStash page. User needs to be inside the sandbox
  to access this page.
  The easiest way to achieve this is to add the test username to $wgTranslateTestUsers[].

  Background:
    Given I am logged in at sandbox
      And I am a sandboxed user on the stash page

  Scenario: Can select a language to translate into
    Then I should see a language selector
     And I should be able to select a language

  Scenario: Common elements in translation widget

    The first message is automatically opened for editing

    Then I should see the save button
      And I should see the skip button

  Scenario: Can make a translation
    When I make a translation
    Then I should see my translation saved
      And I should see the next message open for translation
      And I should see a message indicating I have one completed translation

  Scenario: Can improve own earlier translation
    When I make a translation
    Then I should see my translation saved
      And I reload the page
      And I can open and see my earlier translation

  Scenario: User is displayed a message when all messages have been translated
    When I translate all the messages in the sandbox
    Then I can see a message that maximum number of translations has been reached
