class ManageTranslatorSandboxPage
	include PageObject

	include URL
	page_url URL.url("Special:ManageTranslatorSandbox?<%=params[:extra]%>")

	text_field(:search, class: "request-filter-box")
	div(:requests_list, class: "requests-list")
	div(:request_count, class: "request-count")

	div(:details, class: "details")
	div(:username) do |page|
		page.requests_list_element.element.divs(class: "username")
	end
end
