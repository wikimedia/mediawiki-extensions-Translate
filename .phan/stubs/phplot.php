<?php
/**
 * stub for davefx/phplot
 * @phpcs:disable MediaWiki.Files.ClassMatchesFilename
 */
class PHPlot {

	/**
	 * @param int $width
	 * @param int $height
	 * @param string|null $output_file
	 * @param string|null $input_file
	 */
	public function __construct( $width, $height, $output_file = null, $input_file = null ) {
	}

	/**
	 * @param string|null $which_font
	 * @return bool
	 */
	public function SetDefaultTTFont( $which_font = null ) {
	}

	/**
	 * @param string $which_elem
	 * @param string $which_font
	 * @param int $which_size
	 * @param int|null $which_spacing
	 * @return bool
	 */
	public function SetFontTTF( $which_elem, $which_font, $which_size = 12, $which_spacing = null ) {
	}

	/**
	 * @param array $which_dv
	 * @return bool
	 */
	public function SetDataValues( $which_dv ) {
	}

	/**
	 * @param string|string[] $which_leg
	 * @return bool
	 */
	public function SetLegend( $which_leg ) {
	}

	/**
	 * @param string $which_ytitle
	 * @param string $which_ypos
	 * @return bool
	 */
	public function SetYTitle( $which_ytitle, $which_ypos = 'plotleft' ) {
	}

	/**
	 * @param float $which_ti
	 * @return bool
	 */
	public function SetYTickIncrement( $which_ti = '' ) {
	}

	/**
	 * @param float|null $xmin
	 * @param float|null $ymin
	 * @param float|null $xmax
	 * @param float|null $ymax
	 * @return bool
	 */
	public function SetPlotAreaWorld( $xmin = null, $ymin = null, $xmax = null, $ymax = null ) {
	}

	/**
	 * @param string $which_xtlp
	 * @return bool
	 */
	public function SetXTickLabelPos( $which_xtlp ) {
	}

	/**
	 * @param string $which_tp
	 * @return bool
	 */
	public function SetXTickPos( $which_tp ) {
	}

	/**
	 * @param float $which_xla
	 * @return bool
	 */
	public function SetXLabelAngle( $which_xla ) {
	}

	/**
	 * @param string|int[]|null $which_color
	 * @return bool
	 */
	public function SetTransparentColor( $which_color = null ) {
	}

	/**
	 * @param string|int[] $which_color
	 * @return bool
	 */
	public function SetBackgroundColor( $which_color ) {
	}

	/**
	 * @return bool
	 */
	public function DrawGraph() {
	}

}
