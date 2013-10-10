class StashPage
	include PageObject

	include URL
	page_url URL.url('Special:TranslationStash')

	span(:language_selector, :class => 'uls')
	text_field(:language_filter, :id => 'languagefilter')

	a(:edit_message, :text => 'Edit', :index => 2)

	def select_language(language)
		self.language_selector_element.click
		self.language_filter = language
		self.language_filter_element.send_keys :enter
	end
end

