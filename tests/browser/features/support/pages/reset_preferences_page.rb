class ResetPreferencesPage
	include PageObject
	include URL
	page_url URL.url("Special:Preferences/reset")

	button(:submit, class: "mw-htmlform-submit")
end
