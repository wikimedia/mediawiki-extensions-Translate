class UserPage
	include PageObject

	include URL
	page_url URL.url("User:<%=params[:extra]%>")

	def babel_box_has_languages?(languages)
		languages.split(/, /).all? do |language|
			@browser.element(css: ".mw-babel-box td[lang=#{language}]").visible?
		end
	end

end
