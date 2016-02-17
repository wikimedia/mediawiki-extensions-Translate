Given(/^I am logged in at sandbox$/) do
  log_in
end

Given(/^I am a sandboxed user on the stash page$/) do
  visit(StashPage, using_params: { extra: 'integrationtesting=activatestash' })
end

Then(/^I should see a language selector$/) do
  on(StashPage).language_selector_element.should be_visible
end

Then(/^I should be able to select a language$/) do
  on(StashPage).select_language('fi')
end

Then(/^I should see the save button$/) do
  on(StashPage).save_button_element.when_present.should be_visible
end

Then(/^I should see the skip button$/) do
  on(StashPage).skip_button_element.when_present.should be_visible
end

When(/^I make a translation$/) do
  on(StashPage).make_a_translation
end

Then(/^I should see my translation saved$/) do
  on(StashPage).status_saved_element.when_present.should be_visible
end

Then(/^I should see the next message open for translation$/) do
  on(StashPage) do |page|
    page.translation_element(1).when_present.should be_visible
    page.translation_element(1).value.should == ''
  end
end

When(/^I reload the page$/) do
  visit StashPage
end

Then(/^I can open and see my earlier translation$/) do
  pending('Does not work because of random language selection on reload.') do
    on(StashPage) do |page|
      page.edit_element.when_present(10).click
      page.translation_element.when_present.value.should == 'Pupu'
    end
  end
end

Then(/^I should see a message indicating I have one completed translation$/) do
  on(StashPage).translation_stats.should match(/1/)
end

When(/^I translate all the messages in the sandbox$/) do
  # Sandbox needs to get enough messages
  pending
  (0..19).each do |i|
    on(StashPage).make_a_translation(i)
    step 'I should see my translation saved'
  end
end

Then(/^I can see a message that maximum number of translations has been reached$/) do
  on(StashPage).limit_message_element.should be_visible
end
