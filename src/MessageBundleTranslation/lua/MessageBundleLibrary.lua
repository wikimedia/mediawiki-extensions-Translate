--[=[
Translate Message bundle Lua module
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
--- @param title string Message bundle page name
--- @param languageCode string (Optional) Language to load the translations in, defaults to page language code
--- @param skipFallbacks boolean (Optional) Whether to skip loading fallback translations, defaults to false
--- @return table A new translate message bundle table
function translateGetMessageBundle( title, languageCode, skipFallbacks )
	util.checkTypeMulti( 'translateGetMessageBundle', 1, title, { 'string', 'table' } )
	util.checkType( 'translateGetMessageBundle', 2, languageCode, 'string', true )
	util.checkType( 'translateGetMessageBundle', 3, skipFallbacks, 'boolean', true )

	if type( title ) == 'string' then
		title = mw.title.new( title )
	end

	assert( title, 'Message bundle title is needed' )

	-- Verify that this is a valid message bundle
	php.validate( title.prefixedText )

	-- Determine the language code to use for the message bundle
	languageCode = languageCode or pageLanguageCode

	-- Decide whether to skip loading fallbacks, load them by default
	skipFallbacks = skipFallbacks or false

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
--- @param title string Message bundle page name
--- @param languageCode string (Optional) Language to load the translations in, defaults to page language code
--- @return table A new translate message bundle table
function translateMessageBundle.new( title, languageCode )
	return translateGetMessageBundle( title, languageCode, false )
end

--- Returns a table to access translations without fallbacks from the requested message bundle
--- @param title string Message bundle page name
--- @param languageCode string (Optional) Language to load the translations in, defaults to page language code
--- @return table A new translate message bundle table loaded without access to fallbacks
function translateMessageBundle.newWithoutFallbacks( title, languageCode )
	return translateGetMessageBundle( title, languageCode, true )
end

return translateMessageBundle
