<?php

$checkBlacklist = array(

array(
	'check' => 'plural',
	'code' => array( 'gan','gan-hans','gan-hant','gn','hak','hu','ja',
		'ka','kk-arab','kk-cyrl','kk-latn','ko','lzh','mn','ms','sah','sq',
		'tet','th','wuu','xmf','yue','zh','zh-classical','zh-cn','zh-hans',
		'zh-hant','zh-hk','zh-sg','zh-tw','zh-yue'
	),
),
array(
	'group' => 'core',
	'check' => 'variable',
	'message' => array(
		'confirmemail_body', // Optional time parameters
		'currentrev-asof', // Optional time parameters
		'filehist-thumbtext', // Optional time parameters
		'history-feed-item-nocomment', // Optional time parameters
		'lastmodifiedatby', // Optional time parameters
		'protect-expiring', // Optional time parameters
		'rcnotefrom', // Optional time parameters
		'revision-info', // Optional time parameters
		'revisionasof', // Optional time parameters
	),
),
array(
	'group' => 'ext-abusefilter',
	'check' => 'variable',
	'message' => array(
		'abusefilter-edit-lastmod-text', // Optional username parameter for GENDER, optional time parameters
		'abusefilter-reautoconfirm-none', // Optional username parameter for GENDER
	)
),
array(
	'group' => 'ext-advancedrandom',
	'check' => 'links',
	'message' => array(
		'advancedrandom-desc', // Contains link parts that may need translations
	)
),
array(
	'group' => 'ext-blahtex',
	'check' => 'balance',
	'message' => array(
		'math_MissingOpenBraceAfter', // Contains unbalanced {
		'math_MissingOpenBraceAtEnd', // Contains unbalanced {
		'math_MissingOpenBraceBefore', // Contains unbalanced {
	)
),
array(
	'group' => 'ext-call',
	'check' => 'links',
	'message' => array(
		'call-text', // Contains links that are translated
	)
),
array(
	'group' => 'ext-centralauth',
	'check' => 'links',
	'message' => array(
		'centralauth-readmore-text', // Contains link to page that may be available in a translated version
		'centralauth-finish-problems', // Contains link to page that may be available in a translated version
	)
),
array(
	'group' => 'ext-confirmaccount',
	'check' => 'variable',
	'message' => array(
		'requestaccount-email-body', // Optional time parameters
	)
),
array(
	'group' => 'ext-configure',
	'check' => 'variable',
	'message' => array(
		'configure-condition-description-4', // Optional parameter for PLURAL
		'configure-edit-old', // Optional time parameters
		'configure-viewconfig-line', // Optional time parameters
	)
),
array(
	'group' => 'ext-deletequeue',
	'check' => 'variable',
	'message' => array(
		'deletequeue-page-prod', // Optional time parameters
		'deletequeue-page-deletediscuss', // Optional time parameters
	)
),
array(
	'group' => 'ext-editsubpages',
	'check' => 'links',
	'message' => array(
		'unlockedpages', // Contains links that are translated
	)
),
array(
	'group' => 'ext-farmer',
	'check' => 'links',
	'message' => array(
		'farmer-confirmsetting-text', // Contains links that are translated
	)
),
array(
	'group' => 'ext-flaggedrevs-stabilization',
	'check' => 'variable',
	'message' => array(
		'stabilize-expiring', // Optional time parameters
	)
),
array(
	'group' => 'ext-flaggedrevs-stableversions',
	'check' => 'variable',
	'message' => array(
		'stableversions-review', // Optional time parameters, and name for GENDER
	)
),
array(
	'group' => 'ext-newusernotification',
	'check' => 'variable',
	'message' => array(
		'newusernotifbody', // Optional time parameters
	)
),
array(
	'group' => 'ext-regexblock',
	'check' => 'variable',
	'message' => array(
		'regexblock-match-stats-record', // Optional time parameters
		'regexblock-view-time', // Optional time parameters
	)
),
array(
	'group' => 'ext-ui-edittoolbar',
	'check' => 'links',
	'message' => array(
		'edittoolbar-help-content-ilink-syntax', // Contains links that are translated
		'edittoolbar-help-content-file-syntax', // Contains links that are translated
	)
)
);
