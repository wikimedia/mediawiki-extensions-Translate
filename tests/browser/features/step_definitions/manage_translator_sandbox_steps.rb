Given(/^I am logged in as a translation administrator$/) do
	step "I am logged in"
end

Given(/^I am on the Special:ManageTranslatorSandbox page$/) do
	visit ManageTranslatorSandboxPage
end

When(/^I search for '(.*)' in the sandboxed users search field$/) do |string|
	on(ManageTranslatorSandboxPage).search = string
end

Then(/^users whose name begins with '(.*)' are displayed in the first column$/) do |prefix|
	on(ManageTranslatorSandboxPage).username_element.each do |name|
		name.text.should match(/^#{prefix}/i)
	end
end

Then(/^users whose name begins with 'orava' are not displayed in the first column$/) do
  pending # express the regexp above with the code you wish you had
end

Then(/^'(\d+) requests' is displayed at the top of the first column$/) do |arg1|
  pending # express the regexp above with the code you wish you had
end
