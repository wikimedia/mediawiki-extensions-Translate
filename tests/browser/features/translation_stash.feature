Feature: Translation stash

  As a new translator, I can make translations in sandbox mode so that translation administrator
  can judge those and give me translator rights, so that I do not have to register and wait for
  approval before contributing translation for the site.

  Design:
   - http://commons.wikimedia.org/wiki/File:Translate_UX_Onboarding_designs.pdf

  These scenarios test the Special:TranslationStash page. User needs to be inside sandbox to access
  this page.

  Background:
    Given I am logged in
      And I am a sandboxed user
      And I am on the stash page

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
      And I should see next message open for translation

  Scenario: Can improve own earlier translation
    When I make a translation
      And I reload the page
      And I click on the edit link next to a message
    Then I can see and edit my earlier translation

# Not implemented in code yet
  Scenario: Sandboxed User is displayed the number of translated messages

    When I make a translation
    Then I can see a message indicating the number of completed translations

  Scenario: Sandboxed User is displayed a translation completion message when all messages have been translated

    When I translate all the messages in the sandbox
    Then I can see a message inside the sandbox page and on my homepage indicating that all messages have been translated
