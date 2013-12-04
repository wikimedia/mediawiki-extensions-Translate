Given(/^I am logged in as a translation administrator$/) do
	step "I am logged in"
end

Given(/^I am on the Special:ManageTranslatorSandbox page$/) do
	visit ManageTranslatorSandboxPage
end

When(/^I search for '(.*)' in the sandboxed users search field$/) do |string|
	on(ManageTranslatorSandboxPage).search = string
end

Then(/^only users whose name begins with '(.*)' are displayed in the first column$/) do |prefix|
	on(ManageTranslatorSandboxPage).username_element.each do |name|
		name.text.should match(/^#{prefix}/i)
	end
end

Then(/^no users are displayed in the first column$/) do
	on(ManageTranslatorSandboxPage).username_element.length.should == 0
end

Then(/^'(.+)' is displayed at the top of the first column$/) do |requests_number|
	on(ManageTranslatorSandboxPage).request_count.should match(/^#{requests_number}/i)
end
