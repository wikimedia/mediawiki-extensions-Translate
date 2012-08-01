<?php
/**
 * Contains logic for special page Special:FirstSteps to guide users through
 * the process of becoming a translator.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2010-2012, Niklas Laxström, Siebrand Mazeland
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
			$title = SpecialPage::getTitleFor( 'LanguageStats', $this->getLanguage()->getCode() );
			$out->redirect( $title->getLocalUrl() );
		}

		$out->setPageTitle( $this->msg( 'translate-fs-pagetitle', $this->msg( $step )->text() )->escaped() );
	}

	protected function showSignup( $step ) {
		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-signup-title';
		$header->style( 'opacity', 0.4 )->content( $this->msg( $step_message )->text() );

		if ( $step ) {
			$this->getOutput()->addHtml( $header );
			return $step;
		}

		if ( $this->getUser()->isLoggedIn() ) {
			$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
			$this->getOutput()->addHtml( $header );
			return $step;
		} else {
			// Go straight to create account (or login) page
			$create = SpecialPage::getTitleFor( 'Userlogin' );
			$returnto = $this->getTitle()->getPrefixedText();
			$this->getOutput()->redirect( $create->getLocalUrl( array( 'returnto' => $returnto , 'type' => 'signup' ) ) );
		}
	}

	protected function showSettings( $step ) {
		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-settings-title';
		$header->style( 'opacity', 0.4 )->content( $this->msg( $step_message )->text() );

		$out = $this->getOutput();
		if ( $step ) {
			$out->addHtml( $header );
			return $step;
		}

		$user = $this->getUser();
		$request = $this->getRequest();
		if ( $request->wasPosted() &&
			$user->matchEditToken( $request->getVal( 'token' ) ) &&
			$request->getText( 'step' ) === 'settings' )
		{
			$user->setOption( 'language', $request->getVal( 'primary-language' ) );
			$user->setOption( 'translate-firststeps', '1' );

			$assistant = array();
			for ( $i = 0; $i < 10; $i++ ) {
				$language = $request->getText( "assistant-language-$i", '-' );
				if ( $language === '-' ) continue;
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
			$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
			$out->addHtml( $header );
			return $step;
		}

		$out->addHtml( $header->style( 'opacity', false ) );

		$code = $this->getLanguage()->getCode();

		$languages = $this->languages( $code );
		$selector = new XmlSelect();
		$selector->addOptions( $languages );
		$selector->setDefault( $code );

		$output = Html::openElement( 'form', array( 'method' => 'post' ) );
		$output .= Html::hidden( 'step', 'settings' );
		$output .= Html::hidden( 'token', $this->getUser()->editToken() );
		$output .= Html::hidden( 'title', $this->getTitle() );
		$output .= Html::openElement( 'table' );

		$name = $id = 'primary-language';
		$selector->setAttribute( 'id', $id );
		$selector->setAttribute( 'name', $name );
		$text = $this->msg( 'translate-fs-settings-planguage' )->text();
		$row  = self::wrap( 'td', Xml::label( $text, $id ) );
		$row .= self::wrap( 'td', $selector->getHtml() );
		$output .= self::wrap( 'tr', $row );

		$row = Html::rawElement( 'td', array( 'colspan' => 2 ), $this->msg( 'translate-fs-settings-planguage-desc' )->parse() );
		$output .= self::wrap( 'tr', $row );

		$helpers = $this->getHelpers( $user, $code );

		$selector = new XmlSelect();
		$selector->addOption( $this->msg( 'translate-fs-selectlanguage' )->text(), '-' );
		$selector->addOptions( $languages );

		$num = max( 2, count( $helpers ) );
		for ( $i = 0; $i < $num; $i++ ) {
			$id = $name = "assistant-language-$i";
			$text = $this->msg( 'translate-fs-settings-slanguage' )->numParams( $i + 1 )->text();
			$selector->setDefault( isset( $helpers[$i] ) ? $helpers[$i] : false );
			$selector->setAttribute( 'id', $id );
			$selector->setAttribute( 'name', $name );

			$row  = self::wrap( 'td', Xml::label( $text, $id ) );
			$row .= self::wrap( 'td', $selector->getHtml() );
			$output .= self::wrap( 'tr', $row );
		}

		$output .= Html::openElement( 'tr' );
		$output .= Html::rawElement( 'td', array( 'colspan' => 2 ), $this->msg( 'translate-fs-settings-slanguage-desc' )->parse() );
		$output .= Html::closeElement( 'tr' );
		$output .= Html::openElement( 'tr' );
		$output .= Html::rawElement( 'td', array( 'colspan' => 2 ), Xml::submitButton( $this->msg( 'translate-fs-settings-submit' )->text() ) );
		$output .= Html::closeElement( 'tr' );
		$output .= Html::closeElement( 'table' );
		$output .= Html::closeElement( 'form' );

		$out->addHtml( $output );

		return $step_message;
	}

	protected static function wrap( /*string*/ $tag, /*string*/ $content ) {
		return Html::rawElement( $tag, array(), $content );
	}

	protected function getHelpers( User $user, /*string*/ $code ) {
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

		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-userpage-title';
		$header->style( 'opacity', 0.4 )->content( $this->msg( $step_message )->text() );

		if ( $step ) {
			$this->getOutput()->addHtml( $header );
			return $step;
		}

		$userpage = $this->getUser()->getUserPage();
		$preload = "I am My Name and....";

		$request = $this->getRequest();
		if ( $request->wasPosted() &&
			$this->getUser()->matchEditToken( $request->getVal( 'token' ) ) &&
			$request->getText( 'step' ) === 'userpage' )
		{
			$babel = array();
			for ( $i = 0; $i < 5; $i++ ) {
				$language = $request->getText( "babel-$i-language", '-' );
				if ( $language === '-' ) continue;
				$level = $request->getText( "babel-$i-level", '-' );
				$babel[$language] = $level;
			}

			arsort( $babel );
			$babeltext = '{{#babel:';
			foreach ( $babel as $language => $level ) {
				if ( $level === 'N' ) $level = '';
				else $level = "-$level";
				$babeltext .= "$language$level|";
			}
			$babeltext = trim( $babeltext, '|' );
			$babeltext .= "}}\n";

			$article = new Article( $userpage, 0 );
			$status = $article->doEdit( $babeltext . $request->getText( $textareaId ), $this->getTitle() );

			if ( $status->isOK() ) {
				$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
				$this->getOutput()->addHtml( $header );
				$this->getOutput()->addWikiMsg( 'translate-fs-userpage-done' );

				return false;
			} else {
				$this->getOutput()->addWikiText( $status->getWikiText() );
				$preload = $request->getText( 'userpagetext' );
			}
		}

		if ( $userpage->exists() ) {
			$revision = Revision::newFromTitle( $userpage );
			$text = $revision->getText();
			$preload = $text;

			if ( preg_match( '/{{#babel:/i', $text ) ) {
				$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
				$this->getOutput()->addHtml( $header );

				return false;
			}
		}

		$this->getOutput()->addHtml( $header->style( 'opacity', false ) );

		$this->getOutput()->addWikiMsg( 'translate-fs-userpage-help' );

		$form = new HtmlTag( 'form' );
		$items = new TagContainer();
		$form->param( 'method', 'post' )->content( $items );

		$items[] = new RawHtml( Html::hidden( 'step', 'userpage' ) );
		$items[] = new RawHtml( Html::hidden( 'token', $this->getUser()->editToken() ) );
		$items[] = new RawHtml( Html::hidden( 'title', $this->getTitle() ) );

		$lang = $this->getLanguage();
		$languages = $this->languages( $lang->getCode() );
		$selector = new XmlSelect();
		$selector->addOption( $this->msg( 'translate-fs-selectlanguage' )->text(), '-' );
		$selector->addOptions( $languages );

		// Building a skill selector
		$skill = new XmlSelect();
		$levels = 'N,5,4,3,2,1';
		foreach ( explode( ',', $levels ) as $level ) {
			$skill->addOption( $this->msg( "translate-fs-userpage-level-$level" )->text(), $level );
		}
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
			$items[] = New RawHtml( $skill->getHtml() . $selector->getHtml() . '<br />' );
		}

		$textarea = new HtmlTag( 'textarea', $preload );
		$items[] = $textarea->param( 'rows' , 5 )->id( $textareaId )->param( 'name', $textareaId );
		$items[] = new RawHtml( Xml::submitButton( $this->msg( 'translate-fs-userpage-submit' )->text() ) );

		$this->getOutput()->addHtml( $form );

		return $step_message;
	}

	protected function showPermissions( $step ) {
		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-permissions-title';
		$header->content( $this->msg( $step_message )->text() )->style( 'opacity', 0.4 );

		if ( $step ) {
			$this->getOutput()->addHtml( $header );
			return $step;
		}

		$request = $this->getRequest();
		if ( $request->wasPosted() &&
			$this->getUser()->matchEditToken( $request->getVal( 'token' ) ) &&
			$request->getText( 'step' ) === 'permissions' )
		{
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
				'token' => $this->getUser()->editToken(),
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
			$this->getUser()->setOption( 'translate-firststeps-request', $page );
			$this->getUser()->saveSettings();
		}

		$page = $this->getUser()->getOption( 'translate-firststeps-request' );
		if ( $this->getUser()->isAllowed( 'translate' ) ) {
			$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
			$this->getOutput()->addHtml( $header );
			return $step;
		} elseif ( $page ) {
			$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-pending' )->text() );
			$this->getOutput()->addHtml( $header->style( 'opacity', false ) );
			$this->getOutput()->addWikiMsg( 'translate-fs-permissions-pending', $page );
			return $step_message;
		}

		$this->getOutput()->addHtml( $header->style( 'opacity', false ) );
		$this->getOutput()->addWikiMsg( 'translate-fs-permissions-help' );

		$output = Html::openElement( 'form', array( 'method' => 'post' ) );
		$output .= Html::hidden( 'step', 'permissions' );
		$output .= Html::hidden( 'token', $this->getUser()->editToken() );
		$output .= Html::hidden( 'title', $this->getTitle() );
		$name = $id = 'primary-language';
		$selector = new XmlSelect();
		$langCode = $this->getLanguage()->getCode();
		$selector->addOptions( $this->languages( $langCode ) );
		$selector->setAttribute( 'id', $id );
		$selector->setAttribute( 'name', $name );
		$selector->setDefault( $langCode );
		$text = $this->msg( 'translate-fs-permissions-planguage' )->text();
		$output .= Xml::label( $text, $id ) . "&#160;" . $selector->getHtml() . '<br />';
		$output .= Html::element( 'textarea', array( 'rows' => 5, 'name' => 'message' ), '' );
		$output .= Xml::submitButton( $this->msg( 'translate-fs-permissions-submit' )->text() );
		$output .= Html::closeElement( 'form' );

		$this->getOutput()->addHtml( $output );
		return $step_message;
	}

	protected function showTarget( $step ) {
		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-target-title';
		$header->content( $this->msg( $step_message )->text() );

		if ( $step ) {
			$header->style( 'opacity', 0.4 );
			$this->getOutput()->addHtml( $header );

			return $step;
		}

		$this->getOutput()->addHtml( $header );
		$this->getOutput()->addWikiMsg( 'translate-fs-target-text', $this->getLanguage()->getCode() );

		return $step_message;
	}

	protected function showEmail( $step ) {
		$header = new HtmlTag( 'h2' );
		$step_message = 'translate-fs-email-title';
		$header->style( 'opacity', 0.4 )->content( $this->msg( $step_message )->text() );

		if ( $step && ( $step !== 'translate-fs-target-title' && $step !== 'translate-fs-permissions-title' ) ) {
			$this->getOutput()->addHtml( $header );
			return $step;
		}

		if ( $this->getUser()->isEmailConfirmed() ) {
			$header->content( $header->content . $this->msg( 'translate-fs-pagetitle-done' )->text() );
			$this->getOutput()->addHtml( $header );
			return $step; // Start translating step
		}

		$this->getOutput()->addHtml( $header->style( 'opacity', false ) );
		$this->getOutput()->addWikiMsg( 'translate-fs-email-text' );

		return $step_message;
	}

	protected function languages( $language ) {
		if ( is_callable( array( 'LanguageNames', 'getNames' ) ) ) {
			$languages = LanguageNames::getNames( $language,
				LanguageNames::FALLBACK_NORMAL,
				LanguageNames::LIST_MW
			);
		} else {
			$languages = Language::getLanguageNames( false );
		}

		ksort( $languages );

		$options = array();
		foreach ( $languages as $code => $name ) {
			$options["$code - $name"] = $code;
		}
		return $options;
	}
}
