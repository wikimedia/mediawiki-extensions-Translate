Then(/^I should see a language selector$/) do
	on(StashPage).language_selector_element.should be_visible
end

Then(/^I should be able to select a language$/) do
	on(StashPage).select_language('fi')
end

When(/^I click on the edit link next to a message$/) do
	on(StashPage).edit_message_element.when_present.click
end

Then(/^I should see the editing area for that message$/) do
	on(StashPage).edit_message_element.should_not be_visible
end
