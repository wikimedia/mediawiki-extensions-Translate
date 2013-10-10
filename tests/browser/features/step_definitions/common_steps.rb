Given(/^I am logged in$/) do
	visit(LoginPage).login_with(ENV['MEDIAWIKI_USER'], ENV['MEDIAWIKI_PASSWORD'])
end

Given(/^I am a sandboxed user on the stash page$/) do
	visit(StashPage, :using_params => {:extra => "integrationtesting=activatestash"})
end
