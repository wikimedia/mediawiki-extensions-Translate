class StashPage
	include PageObject

	include URL
	page_url URL.url('Special:TranslationStash?<%=params[:extra]%>')

	span(:language_selector, :class => 'uls')
	text_field(:language_filter, :id => 'languagefilter')

	a(:edit_message, :text => 'Edit', :index => 1)

	button(:skip_button, :class => 'tux-editor-skip-button')

	span(:status_saved, :class => 'tux-status-translated')

	textarea(:second_translation, :class => 'tux-textarea-translation', :index => 1)

	div(:translation_stats, :class => 'stash-stats')
	div(:limit_message, :class => 'limit-reached')

	def make_a_translation(index = 0)
    translation_element(index).when_present.set 'Pupu'
    save_button_element(index).click
  end
  def save_button_element(index = 0)
    @browser.button(:class => 'tux-editor-save-button', :index => index)
  end
  def select_language(language)
		self.language_selector_element.click
		self.language_filter = language
		self.language_filter_element.send_keys :enter
  end
  def translation_element(index = 0)
    @browser.text_field(:class => 'tux-textarea-translation', :index => index)
  end
end
