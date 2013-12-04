class ManageTranslatorSandboxPage
	include PageObject

	include URL
	page_url URL.url("Special:ManageTranslatorSandbox?<%=params[:extra]%>")

	div(:details, class: "details")

	div(:requests) do |page|
		page.requests_list_element.element.divs(class: "request")
	end
	div(:request_count, class: "request-count")
	div(:requests_list, class: "requests-list")

	text_field(:search, class: "request-filter-box")

	div(:username) do |page|
		page.requests_list_element.element.divs(class: "username")
	end
end
