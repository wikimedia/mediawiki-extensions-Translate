class TranslatePage
	include PageObject

	include URL
	page_url URL.url("Special:Translate?<%=params[:extra]%>")

	div(:workflow_state, class: "tux-workflow-status")
	ul(:workflow_state_selector, class: "tux-workflow-status-selector")
end
