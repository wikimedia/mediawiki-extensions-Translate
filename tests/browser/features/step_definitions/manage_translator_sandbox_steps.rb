Given(/^I am logged in as a translation administrator$/) do
	step "I am logged in"
end

Given(/^I am on the Special:ManageTranslatorSandbox page with no users in the sandbox$/) do
	visit(ManageTranslatorSandboxPage, :using_params => {:extra => "integrationtesting=empty"})
end

Given(/^I am on the Special:ManageTranslatorSandbox page with users in the sandbox$/) do
	visit(ManageTranslatorSandboxPage, :using_params => {:extra => "integrationtesting=populate"})
end

When(/^I search for '(.*)' in the sandboxed users search field$/) do |string|
	on(ManageTranslatorSandboxPage).search = string
end

Then(/^only users whose name begins with '(.*)' are displayed in the first column$/) do |prefix|
	on(ManageTranslatorSandboxPage).visible_users_start_with?(prefix).should be_true
end

Then(/^a user whose name begins with '(.*)' is displayed in the first column$/) do |prefix|
	on(ManageTranslatorSandboxPage).username_element.any? do |name|
		name.text.match(/^#{prefix}/i)
	end.should be_true
end

Then(/^no users are displayed in the first column$/) do
	# Testing search doesn't work without waiting a bit,
	# because the filtering doesn't happen immediately.
	# TODO: Find a better way to wait until the filtering is complete.
	sleep 1
	on(ManageTranslatorSandboxPage).visible_users_element.length.should == 0
end

Then(/^I should see '(.+)' at the top of the first column$/) do |requests_number|
	on(ManageTranslatorSandboxPage).request_count.should match(/^#{requests_number}/i)
end

Then(/^I should see '(.*)' in the second column$/) do |text|
	on(ManageTranslatorSandboxPage).details.should == text
end

When(/^I click the sandboxed users language filter button$/) do
	on(ManageTranslatorSandboxPage).language_selector_button
end

When(/^I type '(.+)' in the language filter$/) do |text|
	on(ManageTranslatorSandboxPage) do |page|
		page.language_filter = text
		page.language_filter_element.send_keys [:enter, "\n"]
	end
end

Then(/^only users who translate to language '(.+)' are displayed in the first column$/) do |language|
	on(ManageTranslatorSandboxPage).all_visible_requests_translate_to?(language).should be_true
end

Then(/^I should see '(.+)' at the bottom of the first column$/) do |text|
	on(ManageTranslatorSandboxPage).request_footer.should == text
end

Then(/^I should see the name of the first user in the first column in the header of the second column$/) do
	on(ManageTranslatorSandboxPage) do |page|
		page.details_header.should == page.username_in_request(0)
	end
end

Then(/^I should see '(.+)' in the header of the second column$/) do |text|
	on(ManageTranslatorSandboxPage).details_header.should == text
end

When(/^I click the checkbox to select all users$/) do
	on(ManageTranslatorSandboxPage).select_all_checkbox_element.click
end

Then(/^I should see the userlist in the first column sorted by the number of translations and the most recent within them$/) do
	on(ManageTranslatorSandboxPage).requests_are_sorted_by_translation_count_and_date?.should be_true
end

Then(/^I should see the checkbox next to the name of the first user in the first column (.+) and (.+)$/) do |checked, disabled|
	first_request_checkbox = on(ManageTranslatorSandboxPage).visible_request_selectors_element[0]
	first_request_checkbox.attribute_value("checked").should == (checked == "checked").to_s
	first_request_checkbox.attribute_value("disabled").should == (disabled == "disabled").to_s
end

Then(/^I should see the '(.+)' button displayed in the second column$/) do |label|
	on(ManageTranslatorSandboxPage).details_button(label).should be_visible
end
