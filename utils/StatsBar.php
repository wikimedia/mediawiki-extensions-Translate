<?php
/**
 * Compact stats.
 *
 * @file
 * @author Niklas Laxström
 * @copyright Copyright © 2012 Niklas Laxström
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Compact, colorful stats.
 * @since 2012-11-30
 */
class StatsBar {
	/**
	 * @see MessageGroupStats
	 * @var array
	 */
	protected $stats;

	/// @var string Message group id
	protected $group;

	/// @var string Language
	protected $language;

	public static function getNew( $group, $language, array $stats = null ) {
		$self = new self();
		$self->group = $group;
		$self->language = $language;
		if ( is_array( $stats ) ) {
			$self->stats = $stats;
		} else {
			$self->stats = MessageGroupStats::forItem( $group, $language );
		}
		return $self;
	}

	public function getHtml( IContextSource $context ) {
		$context->getOutput()->addModules( 'ext.translate.statsbar' );

		$total = $this->stats[MessageGroupStats::TOTAL];
		$proofread = $this->stats[MessageGroupStats::PROOFREAD];
		$translated = $this->stats[MessageGroupStats::TRANSLATED];
		$fuzzy = $this->stats[MessageGroupStats::FUZZY];

		// Proofread is subset of translated
		$untranslated = $total - $translated - $fuzzy;

		$wproofread = round( 100 * $proofread / $total, 2 );
		$wtranslated = round( 100 * ( $translated - $proofread ) / $total, 2);
		$wfuzzy = round( 100 * $fuzzy / $total, 2);
		$wuntranslated = round( 100 - $wproofread - $wtranslated - $wfuzzy, 2);

		return Html::element( 'div', array(
			'class' => 'tux-statsbar',
			'data-total' => $total,
			'data-group' => $this->group,
			'data-language' => $this->language,
		) ).
		Html::element( 'span', array(
			'class' => 'tux-proofread',
			'style' => "width: $wproofread%",
			'data-proofread' => $proofread,
			) ).
		Html::element( 'span', array(
			'class' => 'tux-translated',
			'style' => "width: $wtranslated%",
			'data-translated' => $translated,
		) ).
		Html::element( 'span', array(
			'class' => 'tux-fuzzy',
			'style' => "width: $wfuzzy%",
			'data-fuzzy' => $fuzzy,
		) ).
		Html::element( 'span', array(
			'class' => 'tux-untranslated',
			'style' => "width: $wuntranslated%",
			'data-untranslated' => $untranslated,
		) );
	}
}
