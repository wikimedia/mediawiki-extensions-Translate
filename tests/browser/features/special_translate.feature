@firefox @meta.wikimedia.org
Feature: Special:Translate

  This page is the primary web translation interface for users.

  https://www.mediawiki.org/wiki/Help:Extension:Translate/Quality_assurance#Workflows
  describes how the workflow state selector can be used.
  https://commons.wikimedia.org/wiki/File:Translate-workflow-spec.pdf?page=10
  describes how it is meant to look and behave.

  @sandbox.translatewiki.net
  Scenario: Workflow selector not being visible
    Given I am translating a message group which doesn't have workflow states
    Then I should not see a workflow state

  @custom-setup-needed
  Scenario: Workflow selector being visible
    Given I am translating a message group which has workflow states
    Then I should see a workflow state

  @custom-setup-needed
  Scenario: Workflow selector being clickable
    Given I am translating a message group which has workflow states
    When I click the workflow state
    Then I should see a list of states
