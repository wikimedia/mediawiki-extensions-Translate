require 'json'

class ManageTranslatorSandboxPage
  include PageObject

  page_url 'Special:ManageTranslatorSandbox?<%=params[:extra]%>'

  button(:clear_language_selector, class: 'clear-language-selector')

  div(:details, class: 'details')

  text_field(:language_filter, id: 'languagefilter')
  button(:language_selector_button, class: 'language-selector')

  a(:older_requests_indicator, class: 'older-requests-indicator')

  div(:request_count, class: 'request-count')
  span(:reminder_status, class: 'reminder-status')

  span(:selected_counter, class: 'selected-counter')

  text_field(:search, class: 'request-filter-box')

  checkbox(:select_all_checkbox, class: 'request-selector-all')

  div(:signup_comment_text, class: 'signup-comment-text')

  # This must be reloaded every time, because it may change during the test
  def details_header
    browser.element(class: 'tsb-header')
  end

  def details_button(label)
    button_class = label.downcase.gsub(' ', '-')
    browser.button(class: button_class)
  end

  def details_no_translations
    browser.divs(class: 'tsb-details-no-translations')
  end

  def visible_request_selectors_element
    browser.elements(css: '.row.request:not(.hide) .request-selector')
  end

  def visible_users_element
    browser.elements(css: '.row.request:not(.hide) .username')
  end

  def visible_requests_element
    browser.elements(css: '.row.request:not(.hide)')
  end

  def hidden_users_element
    browser.elements(css: '.requests .request.hide')
  end

  def request_with_username(username)
    browser.div(id: "tsb-request-#{username}")
  end

  def requests_without_username(username)
    browser.elements(css: ".row.request:not(#tsb-request-#{username})")
  end

  def translation_elements
    browser.elements(css: '.details .translation')
  end

  def checkbox_for_request_with_username(username)
    browser.div(id: "tsb-request-#{username}").checkbox(class: 'request-selector')
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
      user_data = JSON.parse(element.attribute_value('data-data'))
      user_data['languagepreferences']['languages'].include?(language)
    end
  end

  def username_in_request(index)
    visible_users_element[index].text
  end

  def requests_are_sorted_by_translation_count_and_date?
    expected_usernames = []
    Array(0..4).each do |num|
      %w(Pupu Orava).each do |name|
        expected_usernames.unshift("#{name}#{num}")
      end
    end
    expected_usernames.unshift('Kissa')
    usernames = visible_users_element.collect { |element| element.text }

    expected_usernames == usernames
  end

  def details_autonym
    browser.elements(css: '.details.pane .languages span')[0]
  end

  def translations_languages_are_sorted?
    expected_langs = %w(bn fi he nl uk)
    langs = translations_autonyms.collect { |element| element.attribute_value('lang') }

    expected_langs == langs
  end

  def translations_autonyms
    browser.elements(css: '.details.pane .translations .info.autonym')
  end

  def click_button(label)
    details_button(label).click

    # It takes a few moments until Accept and Reject buttons
    # finish performing the action, and this action always
    # removes the currently displayed users and changes the header
    Watir::Wait.while { browser.execute_script 'return window.tsbUpdatingUsers' }
  end

  def only_request_with_username_is_selected?(username)
    requests_without_username(username).all? do |element|
      !element.attribute_value('class').split(' ').include?('selected')
    end
  end
end
