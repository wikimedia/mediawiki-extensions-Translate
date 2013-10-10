class StashPage
	include PageObject

	include URL
	page_url URL.url('Special:TranslationStash')

	span(:language_selector, :class => 'uls')
	text_field(:language_filter, :id => 'languagefilter')

	a(:edit_message, :text => 'Edit', :index => 1)

	button(:save_button, :class => 'tux-editor-save-button')
	button(:skip_button, :class => 'tux-editor-skip-button')

	span(:status_saved, :class => 'tux-status-saved')

	textarea(:translation)

	def select_language(language)
		self.language_selector_element.click
		self.language_filter = language
		self.language_filter_element.send_keys :enter
	end
end
