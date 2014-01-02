Given(/^I am logged in$/) do
	visit(LoginPage).login_with(ENV["MEDIAWIKI_USER"], ENV["MEDIAWIKI_PASSWORD"])
end

Given(/^I have reset my preferences$/) do
	visit(ResetPreferencesPage).submit_element.click
end
