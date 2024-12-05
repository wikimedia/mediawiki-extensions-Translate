<?php
declare( strict_types = 1 );

namespace MediaWiki\Extension\Translate;

/**
 * Constants for log channel names used in this extension.
 *
 * @author Niklas Laxström
 * @since 2024.12
 * @internal
 */
final class LogNames {
	/** Default log channel for the extension */
	public const MAIN = 'Translate';

	/** Channel for message group synchronization */
	public const GROUP_SYNCHRONIZATION = 'Translate.GroupSynchronization';

	/** Channel for translation services */
	public const TRANSLATION_SERVICES = 'translationservices';

	/** Channel for message bundle code */
	public const MESSAGE_BUNDLE = 'Translate.MessageBundle';

	/** Channel for message group subscription */
	public const GROUP_SUBSCRIPTION = 'Translate.MessageGroupSubscription';

	/** Channel for jobs */
	public const JOBS = 'Translate.Jobs';

	/** Channel for ElasticSearchTtmServer */
	public const ELASTIC_SEARCH_TTMSERVER = 'ElasticSearchTtmServer';
}
