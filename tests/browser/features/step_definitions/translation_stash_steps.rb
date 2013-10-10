Then(/^I should see a language selector$/) do
	on(StashPage).language_selector_element.should be_visible
end

Then(/^I should be able to select a language$/) do
	on(StashPage).select_language('fi')
end

When(/^I click on the edit link next to a message$/) do
	on(StashPage).edit_message_element.when_present.click
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
	on(StashPage).second_translation_element.when_present.should be_visible
	on(StashPage).second_translation.should == ''
end

When(/^I reload the page$/) do
	visit(StashPage)
end

Then(/^I can see and edit my earlier translation$/) do
	on(StashPage).translation_element.when_present.text.should == 'Pupu'
end

Then(/^I should see a message indicating I have one completed translation$/) do
	on(StashPage).translation_stats.should match(/1/)
end

When(/^I translate all the messages in the sandbox$/) do
	(0..19).each do |i|
		on(StashPage).make_a_translation(i)
		step 'I should see my translation saved'
	end
end

Then(/^I can see a message that maximum number of translations has been reached$/) do
	on(StashPage).limit_message_element.should be_visible
end
