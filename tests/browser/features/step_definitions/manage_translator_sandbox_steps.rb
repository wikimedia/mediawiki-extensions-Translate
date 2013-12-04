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
	on(ManageTranslatorSandboxPage).username_element.each do |name|
		name.text.should match(/^#{prefix}/i)
	end
end

Then(/^a user whose name begins with '(.*)' is displayed in the first column$/) do |prefix|
	on(ManageTranslatorSandboxPage).username_element.any? do |name|
		name.text.match(/^#{prefix}/i)
	end.should be_true
end

Then(/^no users are displayed in the first column$/) do
	on(ManageTranslatorSandboxPage).username_element.length.should == 0
end

Then(/^'(.+)' is displayed at the top of the first column$/) do |requests_number|
	on(ManageTranslatorSandboxPage).request_count.should match(/^#{requests_number}/i)
end

Then(/^I should see '(.*)' in the second column$/) do |text|
	on(ManageTranslatorSandboxPage).details.should == text
end

Then(/^I should see a list of users in the first column$/) do
	on(ManageTranslatorSandboxPage).username_element.length.should == 10
end

Then(/^the list of users should be sorted by the number of translations and the most recent within them$/) do
  require "json"
	on(ManageTranslatorSandboxPage).requests_element.each do |request|
		JSON.parse(request.attribute_value("data-data"))
	end
end

Then(/^I should see the checkbox next to the name of the first user (\w+) and (\w+)/) do |checked, enabled|
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the name of the first user in the header of the second column$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the 'Accept' button displayed in the second column$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see the 'Reject' button displayed in the second column$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^I should see '(\d+) user selected' in the selected users counter at the bottom of the first column$/) do |arg1|
  pending # express the regexp above with the code you wish you had
end
