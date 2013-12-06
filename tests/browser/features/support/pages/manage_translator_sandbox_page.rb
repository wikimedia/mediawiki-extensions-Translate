require "json"

class ManageTranslatorSandboxPage
	include PageObject

	include URL
	page_url URL.url("Special:ManageTranslatorSandbox?<%=params[:extra]%>")

	div(:details, class: "details")
	div(:details_header, class: "tsb-header")

	text_field(:language_filter, id: "languagefilter")
	button(:language_selector_button, class: "language-selector")

	div(:requests_list, class: "requests-list")
	div(:request_count, class: "request-count")
	div(:request_footer, class: "request-footer")

	text_field(:search, class: "request-filter-box")

	checkbox(:select_all_checkbox, class: "request-selector-all")
	div(:username) do |page|
		page.requests_list_element.element.divs(class: "username")
	end

	def details_button_is_visible?(label)
		@browser.elements(css: ".details button", text: label)
	end

	def visible_request_selectors_element
		@browser.elements(css: ".row.request:not(.hide) .request-selector")
	end

	def visible_users_element
		@browser.elements(css: ".row.request:not(.hide) .username")
	end

	def visible_requests_element
		@browser.elements(css: ".row.request:not(.hide)")
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

	def all_visible_requests_translate_to?(language)
		Watir::Wait.until { hidden_users_element.size > 0 }
		visible_requests_element.all? do |element|
			user_data = JSON.parse(element.attribute_value("data-data"))
			user_data["languagepreferences"]["languages"].include?(language)
		end
	end

	def username_in_request(index)
		visible_users_element[0].text
	end

	def requests_are_sorted_by_translation_count_and_date?
		prev_translations = false
		prev_registrationdate = false

		visible_requests_element.all? do |element|
			user_data = JSON.parse(element.attribute_value("data-data"))

			correct_date_order = prev_registrationdate ? prev_registrationdate >= user_data["registrationdate"] : true
			correct_translations_order = prev_translations ? prev_translations >= user_data["translations"] : true

			prev_translations = user_data["translations"]
			prev_registrationdate = user_data["registrationdate"]

			correct_date_order and correct_translations_order
		end
	end
end
