Given(/^I am logged in as a translation administrator$/) do
	step "I am logged in"
end

Given(/^I am on the Translator sandbox management page with no users in the sandbox$/) do
	visit(ManageTranslatorSandboxPage, :using_params => {:extra => "integrationtesting=empty"})
end

Given(/^I am on the Translator sandbox management page with users in the sandbox$/) do
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
	on(ManageTranslatorSandboxPage) do |page|
		Watir::Wait.until { page.visible_requests_element.size < 10 }
		page.visible_users_element.length.should == 0
	end
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

Then(/^I should see the button that clears language selection$/) do
	on(ManageTranslatorSandboxPage).clear_language_selector_element.should be_visible
end

Then(/^I should not see the button that clears language selection$/) do
	on(ManageTranslatorSandboxPage).clear_language_selector_element.should_not be_visible
end

When(/^I click the button that clears language selection$/) do
	on(ManageTranslatorSandboxPage).clear_language_selector_element.click
end

Then(/^only users who translate to language '(.+)' are displayed in the first column$/) do |language|
	on(ManageTranslatorSandboxPage).all_visible_requests_translate_to?(language).should be_true
end

Then(/^I should see '(.+)' at the bottom of the first column$/) do |text|
	on(ManageTranslatorSandboxPage).selected_counter.should == text
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

Then(/^I should see the checkbox next to the request from '(.+)' checked$/) do |user|
	on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should be_checked
end

Then(/^I should see the checkbox next to the request from '(.+)' unchecked$/) do |user|
	on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should_not be_checked
end

Then(/^I should see the checkbox next to the request from '(.+)' disabled$/) do |user|
	on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should be_disabled
end

Then(/^I should see the checkbox next to the request from '(.+)' enabled$/) do |user|
	on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should_not be_disabled
end

Then(/^I should see the '(.+)' button displayed in the second column$/) do |label|
	on(ManageTranslatorSandboxPage).details_button(label).should be_visible
end

When(/^I click the '(.+)' button$/) do |label|
	on(ManageTranslatorSandboxPage).details_button(label).click
end


When(/^I click on '(.+)' in the first column$/) do |username|
	on(ManageTranslatorSandboxPage).request_with_username(username).click
end

When(/^I click on the checkbox near '(.+)' in the first column$/) do |username|
	on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(username).click
end

When(/^I click on the link that says '(.*)' at the bottom of the first column$/) do |older_requests|
	on(ManageTranslatorSandboxPage).footer_link(older_requests).click
end

Then(/^I should not see any users except '(.+)' selected$/) do |username|
	on(ManageTranslatorSandboxPage).only_request_with_username_is_selected?(username).should be_true
end

Then(/^I should not see any translations done by the user in the second column$/) do
	on(ManageTranslatorSandboxPage) do |page|
		page.translation_elements.size.should == 0
		page.details_no_translations.size.should == 1
		page.details_no_translations[0].should be_visible
	end
end

Then(/^I should not see any translations done by the users in the second column$/) do
	on(ManageTranslatorSandboxPage).translation_elements.length.should == 0
end

Then(/^I should see the details of (\d+) sandboxed translations done by the user in the second column$/) do |translations|
	on(ManageTranslatorSandboxPage) do |page|
		page.translation_elements.size.should == translations.to_i
		page.details_no_translations.size.should == 0
	end
end

Then(/^I should not see user '(.+)' in the first column$/) do |username|
	on(ManageTranslatorSandboxPage) do |page|
		Watir::Wait.until { page.visible_requests_element.size < 10 }
		page.request_with_username(username).should_not exist
	end
end
