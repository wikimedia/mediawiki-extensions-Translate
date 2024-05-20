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

--[=[
Represents translate message bundle object
]=]
function translateMessageBundle.new( title, languageCode )
	util.checkTypeMulti( 'translateMessageBundle:new', 1, title, { 'string', 'table' } )
	util.checkType( 'translateMessageBundle:new', 2, languageCode, 'string', true )

	if type( title ) == 'string' then
		title = mw.title.new( title )
	end

	assert( title, 'Message bundle title is needed' )

	-- Verify that this is a valid message bundle
	php.validate( title.prefixedText )

	-- Determine the language code to use for the message bundle
	languageCode = languageCode or pageLanguageCode

	local obj = {};
	local translations = nil;

	function loadTranslations( languageCode )
		if translations == nil then
			translations = php.getMessageBundleTranslations( title.prefixedText, languageCode )
		end

		return translations
	end

	function obj:t( key )
		local languageTranslations = loadTranslations( languageCode )
		return languageTranslations[ key ]
	end

	return obj
end

return translateMessageBundle
