<?php
/**
 * Contains logic for special page Special:FirstSteps to guide users through
 * the process of becoming a translator.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2010-2013, Niklas Laxström, Siebrand Mazeland
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

/**
 * Implements a special page which assists users to become translators.
 * Currently it is tailored for the needs of translatewiki.net
 *
 * @ingroup SpecialPage TranslateSpecialPage
 */
class SpecialFirstSteps extends UnlistedSpecialPage {
	public function __construct() {
		parent::__construct( 'FirstSteps' );
	}

	public function execute( $params ) {
		$out = $this->getOutput();
		$out->addWikiMsg( 'translate-fs-intro' );
		$step = false;

		$step = $this->showSignup( $step );
		$step = $this->showSettings( $step );
		$step = $this->showUserpage( $step );
		$step = $this->showPermissions( $step );
		$step = $this->showTarget( $step );
		$step = $this->showEmail( $step );

		if ( $step === 'translate-fs-target-title' ) {
			$code = $this->getLanguage()->getCode();
			$title = SpecialPage::getTitleFor( 'LanguageStats', $code );
			$out->redirect( $title->getLocalUrl() );
		}

		$stepText = $this->msg( $step )->plain();
		$out->setPageTitle( $this->msg( 'translate-fs-pagetitle', $stepText ) );
	}

	/**
	 * @param $step string
	 * @return mixed
	 */
	protected function showSignup( $step ) {
		$step_message = 'translate-fs-signup-title';
		$out = $this->getOutput();

		if ( $step ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		if ( $this->getUser()->isLoggedIn() ) {
			$out->addHtml( $this->getHeader( $step_message, 'done' ) );

			return $step;
		}

		// Go straight to create account (or login) page
		$create = SpecialPage::getTitleFor( 'Userlogin' );
		$returnto = $this->getTitle()->getPrefixedText();
		$params = array( 'returnto' => $returnto, 'type' => 'signup' );
		$out->redirect( $create->getLocalUrl( $params ) );

		return false;
	}

	protected function showSettings( $step ) {
		$step_message = 'translate-fs-settings-title';
		$out = $this->getOutput();

		if ( $step ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		$user = $this->getUser();
		$request = $this->getRequest();
		if ( true
			&& $request->wasPosted()
			&& $user->matchEditToken( $request->getVal( 'token' ) )
			&& $request->getText( 'step' ) === 'settings'
		) {
			$user->setOption( 'language', $request->getVal( 'primary-language' ) );
			$user->setOption( 'translate-firststeps', '1' );

			$assistant = array();
			for ( $i = 0; $i < 10; $i++ ) {
				$language = $request->getText( "assistant-language-$i", '-' );
				if ( $language === '-' ) {
					continue;
				}
				$assistant[] = $language;
			}

			if ( count( $assistant ) ) {
				$user->setOption( 'translate-editlangs', implode( ',', $assistant ) );
			}
			$user->saveSettings();
			// Reload the page if language changed, just in case and this is the easieast way
			$out->redirect( $this->getTitle()->getLocalUrl() );
		}

		if ( $user->getOption( 'translate-firststeps' ) === '1' ) {
			$out->addHtml( $this->getHeader( $step_message, 'done' ) );

			return $step;
		}

		$out->addHtml( $this->getHeader( $step_message ) );

		$output = Html::openElement( 'form', array( 'method' => 'post' ) );
		$output .= Html::hidden( 'step', 'settings' );
		$output .= Html::hidden( 'token', $user->getEditToken() );
		$output .= Html::hidden( 'title', $this->getTitle() );
		$output .= Html::openElement( 'table' );

		$name = $id = 'primary-language';
		$code = $this->getLanguage()->getCode();
		$selector = TranslateUtils::getLanguageSelector( $code );
		$selector->setDefault( $code );
		$selector->setAttribute( 'id', $id );
		$selector->setAttribute( 'name', $name );
		$text = $this->msg( 'translate-fs-settings-planguage' )->text();
		$row = self::wrap( 'td', Xml::label( $text, $id ) );
		$row .= self::wrap( 'td', $selector->getHtml() );
		$output .= self::wrap( 'tr', $row );

		$desc = $this->msg( 'translate-fs-settings-planguage-desc' )->parse();
		$output .= self::wrap( 'tr', self::wrapRow( $desc ) );

		$helpers = $this->getHelpers( $user, $code );

		$labelOption = $this->msg( 'translate-fs-selectlanguage' )->text();
		$selector = TranslateUtils::getLanguageSelector( $code, $labelOption );

		$num = max( 2, count( $helpers ) );
		for ( $i = 0; $i < $num; $i++ ) {
			$id = $name = "assistant-language-$i";
			$text = $this->msg( 'translate-fs-settings-slanguage' )->numParams( $i + 1 )->text();
			$selector->setDefault( isset( $helpers[$i] ) ? $helpers[$i] : false );
			$selector->setAttribute( 'id', $id );
			$selector->setAttribute( 'name', $name );

			$row = self::wrap( 'td', Xml::label( $text, $id ) );
			$row .= self::wrap( 'td', $selector->getHtml() );
			$output .= self::wrap( 'tr', $row );
		}

		$desc = $this->msg( 'translate-fs-settings-slanguage-desc' )->parse();
		$submit = Xml::submitButton( $this->msg( 'translate-fs-settings-submit' )->text() );
		$output .= ''
			. self::wrap( 'tr', self::wrapRow( $desc ) )
			. self::wrap( 'tr', self::wrapRow( $submit ) )
			. Html::closeElement( 'table' )
			. Html::closeElement( 'form' );

		$out->addHtml( $output );

		return $step_message;
	}

	protected static function wrap( /*string*/ $tag, /*string*/$content ) {
		return Html::rawElement( $tag, array(), $content );
	}

	protected static function wrapRow( /*string*/$content ) {
		return Html::rawElement( 'td', array( 'colspan' => 2 ), $content );
	}

	protected function getHelpers( User $user, /*string*/$code ) {
		global $wgTranslateLanguageFallbacks;
		$helpers = $user->getOption( 'translate-editlangs' );
		if ( $helpers === 'default' ) {
			if ( isset( $wgTranslateLanguageFallbacks[$code] ) ) {
				$helpers = $wgTranslateLanguageFallbacks[$code];
			} else {
				$helpers = array();
			}
		} else {
			$helpers = array_map( 'trim', explode( ',', $helpers ) );
		}

		return $helpers;
	}

	protected function showUserpage( $step ) {
		$textareaId = 'userpagetext';
		$step_message = 'translate-fs-userpage-title';
		$out = $this->getOutput();

		if ( $step ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		$user = $this->getUser();
		$request = $this->getRequest();
		$userpage = $user->getUserPage();
		$preload = "I am [Your Name] and....";

		if ( true
			&& $request->wasPosted()
			&& $user->matchEditToken( $request->getVal( 'token' ) )
			&& $request->getText( 'step' ) === 'userpage'
		) {
			$babel = array();
			for ( $i = 0; $i < 5; $i++ ) {
				$language = $request->getText( "babel-$i-language", '-' );
				if ( $language === '-' ) {
					continue;
				}
				$level = $request->getText( "babel-$i-level", '-' );
				$babel[$language] = $level;
			}

			arsort( $babel );
			$babeltext = '{{#babel:';
			foreach ( $babel as $language => $level ) {
				if ( $level === 'N' ) {
					$level = '';
				} else {
					$level = "-$level";
				}
				$babeltext .= "$language$level|";
			}
			$babeltext = trim( $babeltext, '|' );
			$babeltext .= "}}\n";

			$article = new Article( $userpage, 0 );
			$status = $article->doEdit( $babeltext . $request->getText( $textareaId ), $this->getTitle() );

			if ( $status->isOK() ) {
				$out->addHtml( $this->getHeader( $step_message, 'done' ) );
				$out->addWikiMsg( 'translate-fs-userpage-done' );

				return false;
			} else {
				$out->addWikiText( $status->getWikiText() );
				$preload = $request->getText( 'userpagetext' );
			}
		}

		if ( $userpage->exists() ) {
			$revision = Revision::newFromTitle( $userpage );
			$text = $revision->getText();
			$preload = $text;

			if ( preg_match( '/{{#babel:/i', $text ) ) {
				$out->addHtml( $this->getHeader( $step_message, 'done' ) );

				return false;
			}
		}

		$out->addHtml( $this->getHeader( $step_message ) );
		$out->addWikiMsg( 'translate-fs-userpage-help' );

		$output = ''
			. Html::openElement( 'form', array( 'method' => 'post' ) )
			. Html::hidden( 'step', 'userpage' )
			. Html::hidden( 'token', $this->getUser()->getEditToken() )
			. Html::hidden( 'title', $this->getTitle() );

		$code = $this->getLanguage()->getCode();
		$labelOption = $this->msg( 'translate-fs-selectlanguage' )->text();
		$selector = TranslateUtils::getLanguageSelector( $code, $labelOption );

		// Building a skill selector
		$skill = new XmlSelect();
		foreach ( explode( ',', 'N,5,4,3,2,1' ) as $level ) {
			// Give grep a chance to find the usages:
			// translate-fs-userpage-level-N, translate-fs-userpage-level-5, translate-fs-userpage-level-4,
			// translate-fs-userpage-level-3, translate-fs-userpage-level-2, translate-fs-userpage-level-1
			$skill->addOption( $this->msg( "translate-fs-userpage-level-$level" )->text(), $level );
		}

		$lang = $this->getLanguage();
		for ( $i = 0; $i < 5; $i++ ) {
			// Prefill en-2 and [wgLang]-N if [wgLang] != en
			if ( $i === 0 ) {
				$skill->setDefault( '2' );
				$selector->setDefault( 'en' );
			} elseif ( $i === 1 && $lang->getCode() !== 'en' ) {
				$skill->setDefault( 'N' );
				$selector->setDefault( $lang->getCode() );
			} else {
				$skill->setDefault( false );
				$selector->setDefault( false );
			}

			// [skill level selector][language selector]
			$skill->setAttribute( 'name', "babel-$i-level" );
			$selector->setAttribute( 'name', "babel-$i-language" );
			$output .= $skill->getHtml() . $selector->getHtml() . '<br />';
		}

		$attribs = array(
			'rows' => 5,
			'name' => $textareaId,
			'id' => $textareaId,
		);
		$output .= Html::element( 'textarea', $attribs, $preload );
		$output .= Xml::submitButton( $this->msg( 'translate-fs-userpage-submit' )->text() );
		$output .= Html::closeElement( 'form' );

		$out->addHtml( $output );

		return $step_message;
	}

	protected function showPermissions( $step ) {
		$step_message = 'translate-fs-permissions-title';
		$out = $this->getOutput();

		if ( $step ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		$request = $this->getRequest();
		$user = $this->getUser();

		if ( true
			&& $request->wasPosted()
			&& $user->matchEditToken( $request->getVal( 'token' ) )
			&& $request->getText( 'step' ) === 'permissions'
		) {
			// This is ridiculous
			global $wgCaptchaTriggers;
			$captcha = $wgCaptchaTriggers;
			$wgCaptchaTriggers = null;

			$language = $request->getVal( 'primary-language' );
			$message = $request->getText( 'message' );
			if ( trim( $message ) === '' ) {
				$message = '...';
			}
			$params = array(
				'action' => 'threadaction',
				'threadaction' => 'newthread',
				'token' => $user->getEditToken(),
				'talkpage' => 'Project:Translator',
				'subject' => "{{LanguageHeader|$language}}",
				'reason' => 'Using Special:FirstSteps',
				'text' => $message,
			);
			$request = new FauxRequest( $params, true, $_SESSION );
			$api = new ApiMain( $request, true );
			$api->execute();
			$result = $api->getResultData();
			$wgCaptchaTriggers = $captcha;
			$page = $result['threadaction']['thread']['thread-title'];
			$user->setOption( 'translate-firststeps-request', $page );
			$user->saveSettings();
		}

		$page = $user->getOption( 'translate-firststeps-request' );
		if ( $user->isAllowed( 'translate' ) ) {
			$out->addHtml( $this->getHeader( $step_message, 'done' ) );

			return $step;
		} elseif ( $page ) {
			$out->addHtml( $this->getHeader( $step_message, 'pending' ) );
			$out->addWikiMsg( 'translate-fs-permissions-pending', $page );

			return $step_message;
		}

		$out->addHtml( $this->getHeader( $step_message ) );
		$out->addWikiMsg( 'translate-fs-permissions-help' );

		$output = Html::openElement( 'form', array( 'method' => 'post' ) );
		$output .= Html::hidden( 'step', 'permissions' );
		$output .= Html::hidden( 'token', $user->getEditToken() );
		$output .= Html::hidden( 'title', $this->getTitle() );
		$name = $id = 'primary-language';

		$code = $this->getLanguage()->getCode();
		$selector = TranslateUtils::getLanguageSelector( $code );
		$selector->setAttribute( 'id', $id );
		$selector->setAttribute( 'name', $name );
		$selector->setDefault( $code );

		$text = $this->msg( 'translate-fs-permissions-planguage' )->text();
		$output .= Xml::label( $text, $id ) . "&#160;" . $selector->getHtml() . '<br />';
		$output .= Html::element( 'textarea', array( 'rows' => 5, 'name' => 'message' ), '' );
		$output .= Xml::submitButton( $this->msg( 'translate-fs-permissions-submit' )->text() );
		$output .= Html::closeElement( 'form' );

		$out->addHtml( $output );

		return $step_message;
	}

	protected function showTarget( $step ) {
		$step_message = 'translate-fs-target-title';
		$out = $this->getOutput();

		if ( $step ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		$out->addHtml( $this->getHeader( $step_message ) );
		$code = $this->getLanguage()->getCode();
		$out->addWikiMsg( 'translate-fs-target-text', $code );

		return $step_message;
	}

	protected function showEmail( $step ) {
		$step_message = 'translate-fs-email-title';
		$out = $this->getOutput();

		if ( $step && ( $step !== 'translate-fs-target-title' && $step !== 'translate-fs-permissions-title' ) ) {
			$out->addHtml( $this->getHeader( $step_message, 'inactive' ) );

			return $step;
		}

		if ( $this->getUser()->isEmailConfirmed() ) {
			$out->addHtml( $this->getHeader( $step_message, 'done' ) );

			return $step; // Start translating step
		}

		$out->addHtml( $this->getHeader( $step_message ) );
		$out->addWikiMsg( 'translate-fs-email-text' );

		return $step_message;
	}

	/**
	 * Creates a header for step in Special:FirstSteps.
	 * @param $msg string Message key
	 * @param $options string Either inactive, pending or done
	 * @return String Html
	 */
	protected function getHeader( $msg, $options = '' ) {
		$text = $this->msg( $msg )->text();
		if ( $options === 'done' ) {
			$text .= $this->msg( 'translate-fs-pagetitle-done' )->text();
		} elseif ( $options === 'pending' ) {
			$text .= $this->msg( 'translate-fs-pagetitle-pending' )->text();
		}

		$attribs = array();
		if ( $options === 'inactive' || $options === 'done' ) {
			$attribs['style'] = 'opacity: 0.4';
		}

		return Html::element( 'h2', $attribs, $text );
	}
}
