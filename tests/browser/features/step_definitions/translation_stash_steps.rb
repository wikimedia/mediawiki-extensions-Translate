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
	on(StashPage) do |page|
		page.translation_element.when_present
		page.translation = 'Pupu'
		page.save_button_element.click
	end
end

Then(/^I should see my translation saved$/) do
	on(StashPage).status_saved_element.when_present.should be_visible
end

Then(/^I should see next message open for translation$/) do
	on(StashPage).translation_element.when_present.should be_visisble
	on(StashPage).translation.should == ''
end

When(/^I reload the page$/) do
	visit(StashPage)
end

Then(/^I can see and edit my earlier translation$/) do
	on(StashPage).translation_element.when_present
	on(StashPage).translation.should == 'Pupu'
end
