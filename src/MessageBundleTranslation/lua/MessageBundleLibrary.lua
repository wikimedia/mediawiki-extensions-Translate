--[=[
Translate Message bundle Lua module
@experimental
]=]
local util = require 'libraryUtil'

local php
local pageLanguageCode
local translateMessageBundle = {}

function translateMessageBundle.setupInterface( options )
	-- Boilerplate
	translateMessageBundle.setupInterface = nil
	php = mw_interface
	mw_interface = nil
	pageLanguageCode = options.pageLanguageCode

	-- Install into the mw global
	mw = mw or {}
	mw.ext = mw.ext or {}
	mw.ext.translate  = mw.ext.translate or {}
	mw.ext.translate.messageBundle = translateMessageBundle

	-- Indicate that we're loaded
	package.loaded['mw.ext.translate.messageBundle'] = translateMessageBundle
end

--- Returns a table to access translations loaded with fallbacks from the requested message bundle
--- @param title string|table Message bundle page name
--- @param languageCode string Language to load the translations in
--- @param skipFallbacks boolean Whether to skip loading fallback translations
--- @return table A new translate message bundle table
function getMessageBundle( title, languageCode, skipFallbacks )
	if type( title ) == 'string' then
		title = mw.title.new( title )
	end

	assert( title, 'Message bundle title is needed' )

	-- Verify that this is a valid message bundle
	php.validate( title.prefixedText )

	local obj = {};
	local translations = nil;

	--- Loads all the messages in a table format with the key being the key in the message bundle
	--- value being the string
	--- @return table A table containing all translations.
	function obj:getAllTranslations()
		if translations == nil then
			translations = php.getMessageBundleTranslations( title.prefixedText, languageCode, skipFallbacks )
		end

		return translations
	end

	--- Loads the translation for a given key, with the specified params
	--- @param key string Key in message bundle for which to retrieve the translation
	--- @return mw.message The translation for the given key wrapped in mw.message object
	function obj:t( key )
		local languageTranslations = self:getAllTranslations()
		local translation = languageTranslations[ key ]
		return translation ~= nil and mw.message.newRawMessage( translation ) or nil
	end

	return obj
end

--- Returns a table to access translations loaded with fallbacks from the requested message bundle
--- @param title string|table Message bundle page name
--- @param languageCode string (Optional) Language to load the translations in, defaults to page language code
--- @return table A new translate message bundle table
function translateMessageBundle.new( title, languageCode )
	util.checkTypeMulti( 'translateMessageBundle.new', 1, title, { 'string', 'table' } )
	util.checkType( 'translateMessageBundle.new', 2, languageCode, 'string', true )

	return getMessageBundle( title, languageCode or pageLanguageCode, false )
end

--- Returns a table to access translations without fallbacks from the requested message bundle
--- @param title string|table Message bundle page name
--- @param languageCode string (Optional) Language to load the translations in, defaults to page language code
--- @return table A new translate message bundle table loaded without access to fallbacks
function translateMessageBundle.newWithoutFallbacks( title, languageCode )
	util.checkTypeMulti( 'translateMessageBundle.newWithoutFallbacks', 1, title, { 'string', 'table' } )
	util.checkType( 'translateMessageBundle.newWithoutFallbacks', 2, languageCode, 'string', true )

	return getMessageBundle( title, languageCode or pageLanguageCode, true )
end

return translateMessageBundle
