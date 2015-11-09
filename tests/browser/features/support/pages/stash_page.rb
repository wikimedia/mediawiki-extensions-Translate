class StashPage
  include PageObject

  page_url 'Special:TranslationStash?<%=params[:extra]%>'

  a(:edit, text: 'Edit')

  a(:language_selector, class: 'uls-trigger')
  text_field(:language_filter, id: 'uls-languagefilter')

  button(:skip_button, class: 'tux-editor-skip-button')

  span(:status_saved, class: 'tux-status-translated')

  div(:translation_stats, class: 'stash-stats')
  div(:limit_message, class: 'limit-reached')

  def make_a_translation(index = 0)
    translation_element(index).when_present.set 'Pupu'
    save_button_element(index).click
  end

  def save_button_element(index = 0)
    browser.button(class: 'tux-editor-save-button', index: index)
  end

  def select_language(language)
    language_selector_element.click
    self.language_filter = language
    language_filter_element.send_keys :enter
  end

  def translation_element(index = 0)
    browser.text_field(class: 'tux-textarea-translation', index: index)
  end
end
