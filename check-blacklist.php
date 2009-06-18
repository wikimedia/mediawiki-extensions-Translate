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
		 'abusefilter-reautoconfirm-none', // Optional username parameter for GENDER
	)
),
array(
	'group' => 'ext-advancedrandom',
	'check' => 'links',
	'message' => array(
		 'advancedrandom-desc', // Contains link parts that may need translations
	)
)
);
