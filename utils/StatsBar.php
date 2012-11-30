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
		$untranslated = $total - $proofread - $translated - $fuzzy;

		$wproofread = floor( 100 * $proofread / $total );
		$wtranslated = floor( 100 * $translated / $total );
		$wfuzzy = floor( 100 * $fuzzy / $total );
		$wuntranslated = 100 - $wproofread - $wtranslated - $wfuzzy;

		$header = Html::openElement( 'div', array(
			'class' => 'tux-statsbar',
			'data-total' => $total,
			'data-group' => $this->group,
			'data-language' => $this->language,
		) );

		return <<<HTML
$header
	<span class="tux-proofread" style="width: $wproofread%">$proofread</span>
	<span class="tux-translated" style="width: $wtranslated%">$translated</span>
	<span class="tux-fuzzy" style="width: $wfuzzy%">$fuzzy</span>
	<span class="tux-untranslated" style="width: $wuntranslated%">$untranslated</span>
</div>
HTML;
	}
}
