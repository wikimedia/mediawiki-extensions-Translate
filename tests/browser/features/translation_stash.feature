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

  Scenario: Can make a translation

    When I click on the edit link next to a message
      And I write "pupu" as the translation
    Then What


  Scenario: Translation edit area is enabled for sandboxed users
    Given I am on the sandbox translation page of translatewiki.net
    And I am logged-in
    And I have clicked the 'edit' link next to a message to display the editor
    When I click on the input area
    Then I should see it enabled

  Scenario: Sandboxed User can see translation suggestions for the string being translated
    Given I am on the sandbox translation page of translatewiki.net
    And I am logged-in
    When I click on a message to translate
    Then I should see translation suggestions being displayed

  Scenario: Sandboxed user can select translation suggestions
    Given I am on the sandbox translation page of translatewiki.net
    And I am logged-in
    And translating a message in the sandbox
    When I click on the 'Use this translation' link
    Then I should see translation suggestion copied into the edit area

  Scenario: Sandboxed User can save a translation and move to the next translation
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  And translating a message in the sandbox
  When I click on the 'Save translation' button
  Then I should see the translation being saved
  And I am taken to the next string (assumption: the string being translated was not the 5th message in the sandbox)

  Scenario: Sandboxed User can save  a translation and move to the next translation with the shortcut keys
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  And translating a message in the sandbox
  When I press the 'Ctrl+S' keys
  Then I should see the translation being saved
  And I am taken to the next string (assumption: the string being translated was not the 5th message in the sandbox)

  Scenario: Sandboxed User can cancel and move to the next translation
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  And translating a message in the sandbox
  When I click on the 'Try Another' button
  Then I am taken to the next string without saving the current string (assumption: the string being translated was not the 5th message in the sandbox)

  Scenario: Sandboxed User can cancel and move to the next translation with the shortcut keys
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  And translating a message in the sandbox
  When I press the 'Ctrl+D' keys
  Then I am taken to the next string without saving the current string (assumption: the string being translated was not the 5th message in the sandbox)

  Scenario: Sandboxed User is displayed the number of translated messages
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  When I save the translation
  Then I can see a message indicating the number of completed translations

  #Completed Messages

  Scenario: Sandboxed User is displayed a translation completion message when all messages have been translated
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  When I translate all the messages in the sandbox
  Then I can see a message inside the sandbox page and on my homepage indicating that all messages have been translated


  # Editing Completed Messages

  Scenario: Sandboxed User can edit already translated messages
  Given I am on the sandbox translation page of translatewiki.net
  And I am logged-in
  And I have translated messages in the sandbox
  When I click on the 'Edit' link next to a translated message
  Then I can edit the message in the editor
