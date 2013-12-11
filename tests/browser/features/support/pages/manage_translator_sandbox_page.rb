require "json"

class ManageTranslatorSandboxPage
	include PageObject

	include URL
	page_url URL.url("Special:ManageTranslatorSandbox?<%=params[:extra]%>")

	button(:clear_language_selector, class: "clear-language-selector")

	div(:details, class: "details")
	div(:details_header, class: "tsb-header")

	text_field(:language_filter, id: "languagefilter")
	button(:language_selector_button, class: "language-selector")

	div(:request_count, class: "request-count")
	span(:selected_counter, class: "selected-counter")

	text_field(:search, class: "request-filter-box")

	checkbox(:select_all_checkbox, class: "request-selector-all")

	def details_button(label)
		@browser.button(text: label)
	end

	def details_no_translations
		@browser.divs(class: "tsb-details-no-translations")
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

	def footer_link(older_requests)
		@browser.a(text: older_requests)
	end

	def request_with_username(username)
		@browser.div(id: "tsb-request-#{username}")
	end

	def requests_without_username(username)
		@browser.elements(css: ".row.request:not(#tsb-request-#{username})")
	end

	def translation_elements
		@browser.elements(css: ".details .translation")
	end

	def checkbox_for_request_with_username(username)
		@browser.div(id: "tsb-request-#{username}").checkbox(class: "request-selector")
	end

	def visible_users_start_with?(prefix)
		Watir::Wait.until { hidden_users_element.size > 0 }
		visible_users_element.all? do |element|
			element.text.match(/^#{prefix}/i)
		end
	end

	def the_first_column_has_username_starting_with?(prefix)
		visible_users_element.any? do |element|
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
		visible_users_element[index].text
	end

	def requests_are_sorted_by_translation_count_and_date?
		expected_usernames = []
		Array(0..4).each do |num|
			%w{Pupu Orava}.each do |name|
				expected_usernames.unshift("#{name}#{num}")
			end
		end

		usernames = visible_users_element.collect { |element| element.text }

		expected_usernames == usernames
	end

	def only_request_with_username_is_selected?(username)
		requests_without_username(username).all? do |element|
			not element.attribute_value("class").split(" ").include?("selected")
		end
	end
end
