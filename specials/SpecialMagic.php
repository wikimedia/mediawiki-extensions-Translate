<?php
/**
 * Contains logic for special page Special:AdvancedTranslate
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @license GPL-2.0+
 */

/**
 * This special page helps with the translations of %MediaWiki features that are
 * not in the main messages array (special page aliases, magic words, namespace names).
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialMagic extends SpecialPage {
	const MODULE_MAGIC = 'words';
	const MODULE_SPECIAL = 'special';
	const MODULE_NAMESPACE = 'namespace';

	/**
	 * List of supported modules
	 */
	private $aModules = array(
		self::MODULE_SPECIAL,
		self::MODULE_NAMESPACE,
		self::MODULE_MAGIC
	);

	/**
	 * Page options
	 */
	private $options = array();
	private $defaults = array();
	private $nondefaults = array();

	public function __construct() {
		parent::__construct( 'Magic' );
	}

	public function doesWrites() {
		return true;
	}

	protected function getGroupName() {
		return 'wiki';
	}

	/**
	 * @see SpecialPage::getDescription
	 *
	 * @return string
	 */
	function getDescription() {
		return $this->msg( 'translate-magic-pagename' )->text();
	}

	/**
	 * Returns HTML5 output of the form
	 * GLOBALS: $wgScript
	 * @return string
	 */
	protected function getForm() {
		global $wgScript;

		$form = Xml::tags( 'form',
			array(
				'action' => $wgScript,
				'method' => 'get'
			),

			'<table><tr><td>' .
				$this->msg( 'translate-page-language' )->escaped() .
				'</td><td>' .
				TranslateUtils::languageSelector(
					$this->getLanguage()->getCode(),
					$this->options['language']
				) .
				'</td></tr><tr><td>' .
				$this->msg( 'translate-magic-module' )->escaped() .
				'</td><td>' .
				$this->moduleSelector( $this->options['module'] ) .
				'</td></tr><tr><td colspan="2">' .
				Xml::submitButton( $this->msg( 'translate-magic-submit' )->text() ) . ' ' .
				Xml::submitButton(
					$this->msg( 'translate-magic-cm-export' )->text(),
					array( 'name' => 'export' )
				) .
				'</td></tr></table>' .
				Html::hidden( 'title', $this->getPageTitle()->getPrefixedText() )
		);

		return $form;
	}

	/**
	 * Helper function get module selector.
	 *
	 * @param string $selectedId Which value should be selected by default
	 * @return string HTML5-compatible select-element.
	 */
	protected function moduleSelector( $selectedId ) {
		// Give grep a chance to find the usages:
		// translate-magic-words, translate-magic-special, translate-magic-namespace
		$selector = new XmlSelect( 'module', 'module', $selectedId );
		foreach ( $this->aModules as $code ) {
			$selector->addOption( $this->msg( 'translate-magic-' . $code )->text(), $code );
		}

		return $selector->getHTML();
	}

	protected function setup( $parameters ) {
		$defaults = array(
			/* str  */'module'   => '',
			/* str  */'language' => $this->getUser()->getOption( 'language' ),
			/* bool */'export'   => false,
			/* bool */'savetodb' => false,
		);

		/**
		 * Place where all non default variables will end.
		 */
		$nondefaults = array();

		/**
		 * Temporary store possible values parsed from parameters.
		 */
		$options = $defaults;
		$request = $this->getRequest();
		foreach ( $options as $v => $t ) {
			if ( is_bool( $t ) ) {
				$r = $request->getBool( $v, $options[$v] );
			} elseif ( is_int( $t ) ) {
				$r = $request->getInt( $v, $options[$v] );
			} elseif ( is_string( $t ) ) {
				$r = $request->getText( $v, $options[$v] );
			}

			if ( !isset( $r ) ) {
				throw new MWException( '$r was not set' );
			}

			wfAppendToArrayIfNotDefault( $v, $r, $defaults, $nondefaults );
		}

		$this->defaults = $defaults;
		$this->nondefaults = $nondefaults;
		$this->options = $nondefaults + $defaults;
	}

	/**
	 * The special page running code
	 *
	 * @param null|string $parameters
	 * @throws MWException|PermissionsError
	 */
	public function execute( $parameters ) {
		$this->setup( $parameters );
		$this->setHeaders();

		$out = $this->getOutput();
		$out->addHelpLink( '//translatewiki.net/wiki/FAQ#Special:AdvancedTranslate', true );

		$out->addHTML( $this->getForm() );

		if ( !$this->options['module'] ) {
			return;
		}
		switch ( $this->options['module'] ) {
			case 'alias':
			case self::MODULE_SPECIAL:
				$o = new SpecialPageAliasesCM( $this->options['language'] );
				break;
			case self::MODULE_MAGIC:
				$o = new MagicWordsCM( $this->options['language'] );
				break;
			case self::MODULE_NAMESPACE:
				$o = new NamespaceCM( $this->options['language'] );
				break;
			default:
				throw new MWException( "Unknown module {$this->options['module']}" );
		}

		$request = $this->getRequest();
		if ( $this->options['savetodb'] && $request->wasPosted() ) {
			if ( !$this->getUser()->isAllowed( 'translate' ) ) {
				throw new PermissionsError( 'translate' );
			}

			$errors = array();
			$o->loadFromRequest( $request );
			$o->validate( $errors );
			if ( $errors ) {
				$out->wrapWikiMsg( '<div class="error">$1</div>',
					'translate-magic-notsaved' );
				$this->outputErrors( $errors );
				$out->addHTML( $o->output() );

				return;
			} else {
				$o->save( $request );
				$out->wrapWikiMsg( '<strong>$1</strong>', 'translate-magic-saved' );
				$out->addHTML( $o->output() );

				return;
			}
		}

		if ( $this->options['export'] ) {
			$output = $o->export();
			if ( $output === '' ) {
				$out->addWikiMsg( 'translate-magic-nothing-to-export' );

				return;
			}
			$result = Xml::element( 'textarea', array( 'rows' => '30' ), $output );
			$out->addHTML( $result );

			return;
		}

		$out->addWikiMsg( 'translate-magic-help' );
		$errors = array();
		$o->validate( $errors );
		if ( $errors ) {
			$this->outputErrors( $errors );
		}
		$out->addHTML( $o->output() );
	}

	protected function outputErrors( $errors ) {
		$count = $this->getLanguage()->formatNum( count( $errors ) );
		$out = $this->getOutput();
		$out->addWikiMsg( 'translate-magic-errors', $count );
		$out->addHTML( '<ol>' );
		foreach ( $errors as $error ) {
			$out->addHTML( "<li>$error</li>" );
		}
		$out->addHTML( '</ol>' );
	}
}
