Given(/^I am logged in$/) do
	visit(LoginPage).login_with(ENV['MEDIAWIKI_USER'], ENV['MEDIAWIKI_PASSWORD'])
end

Given(/^I am a sandboxed user$/) do
	# todo
	#@browser.cookies.add('key', 'value')
end


Given(/^I am on the stash page$/) do
	visit(StashPage)
end

