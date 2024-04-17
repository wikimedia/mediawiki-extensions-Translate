--[=[
Translate Message bundle Lua module
]=]

local php

local translateMessageBundle = {}

function translateMessageBundle.setupInterface( options )
	-- Boilerplate
	translateMessageBundle.setupInterface = nil
	php = mw_interface
	mw_interface = nil

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
function translateMessageBundle.new( title )
	if type( title ) == 'string' then
		title = mw.title.new( title )
	end

	assert( title, 'Message bundle title is needed' )

	local obj = {
		title = title,
	}

	local translationCache = {}

	function obj:loadTranslations( languageCode )
		if translationCache[languageCode] == nil then
			translationCache[languageCode] = php.getMessageBundleTranslations( title.prefixedText, languageCode )
		end

		return translationCache[languageCode]
	end

	return obj
end

return translateMessageBundle
