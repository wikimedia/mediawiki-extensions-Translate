class ManageTranslatorSandboxPage
	include PageObject

	include URL
	page_url URL.url("Special:ManageTranslatorSandbox?<%=params[:extra]%>")

	div(:details, class: "details")

	div(:requests_list, class: "requests-list")
	div(:request_count, class: "request-count")

	text_field(:search, class: "request-filter-box")

	div(:username) do |page|
		page.requests_list_element.element.divs(class: "username")
	end

	def visible_users_element
		@browser.elements(css: ".row.request:not(.hide) .username")
	end

	def hidden_users_element
		@browser.elements(css: ".requests .request.hide")
	end

	def visible_users_start_with?(prefix)
		Watir::Wait.until { hidden_users_element.size > 0 }
		visible_users_element.all? do |element|
			element.text.match(/^#{prefix}/i)
		end
	end
end
