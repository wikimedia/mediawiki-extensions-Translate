<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate\TranslatorInterface;

use MediaWiki\Api\ApiBase;
use MediaWiki\Api\ApiEditPage;
use MediaWiki\Api\Hook\APIGetAllowedParamsHook;
use Wikimedia\ParamValidator\ParamValidator;

/**
 * Registers the editorinterface parameter, which wiki config reads to apply hCaptcha to translation edits.
 *
 * @license GPL-2.0-or-later
 */
class ApiEditPageHookHandler implements APIGetAllowedParamsHook {
	/** @inheritDoc */
	public function onAPIGetAllowedParams( $module, &$params, $flags ): void {
		// MobileFrontend registers the same parameter.
		if ( $module instanceof ApiEditPage && !isset( $params['editorinterface'] ) ) {
			$params['editorinterface'] = [
				ParamValidator::PARAM_TYPE => 'string',
				// The default key is already used by MobileFrontend
				ApiBase::PARAM_HELP_MSG => 'apihelp-translate-edit-param-editorinterface',
			];
		}
	}
}
