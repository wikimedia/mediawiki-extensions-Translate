Given(/^I am translating a message group which doesn't have workflow states$/) do
  visit(TranslatePage, using_params: { extra: 'language=fi' })
end

Given(/^I am translating a message group which has workflow states$/) do
  visit(TranslatePage, using_params: { extra: 'language=fi&group=page-Language+committee' })
end

When(/^I click the workflow state$/) do
  on(TranslatePage).workflow_state_element.when_present.click
end

Then(/^I should see a workflow state$/) do
  on(TranslatePage).workflow_state_element.when_present.should be_visible
end

Then(/^I should not see a workflow state$/) do
  on(TranslatePage).workflow_state_element.should_not be_visible
end

Then(/^I should see a list of states$/) do
  on(TranslatePage).workflow_state_selector_element.should be_visible
end
