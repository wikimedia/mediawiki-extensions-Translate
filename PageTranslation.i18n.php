<?php
/**
 * Translations of Page Translation feature of Translate extension.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'pagetranslation' => 'Page translation',
	'right-pagetranslation' => 'Mark versions of pages for translation',
	'tpt-desc' => 'Extension for translating content pages',
	'tpt-section' => 'Translation unit $1',
	'tpt-section-new' => 'New translation unit. Name: $1',
	'tpt-section-deleted' => 'Translation unit $1',
	'tpt-template' => 'Page template',
	'tpt-templatediff' => 'The page template has changed.',

	'tpt-diff-old' => 'Previous text',
	'tpt-diff-new' => 'New text',
	'tpt-submit' => 'Mark this version for translation',

	'tpt-sections-oldnew' => 'New and existing translation units',
	'tpt-sections-deleted' => 'Deleted translation units',
	'tpt-sections-template' => 'Translation page template',
	
	# Specific page on the special page
	'tpt-badtitle' => 'Page name given ($1) is not a valid title',
	'tpt-oldrevision' => '$2 is not the latest version of the page [[$1]].
Only latest versions can be marked for translation.',
	'tpt-notsuitable' => 'Page $1 is not suitable for translation.
Make sure it has <nowiki><translate></nowiki> tags and has a valid syntax.',
	'tpt-saveok' => 'The page [[$1]] has been marked up for translation with $2 {{PLURAL:$2|translation unit|translation units}}.
The page can now be <span class="plainlinks">[$3 translated]</span>.',
	'tpt-badsect' => '"$1" is not a valid name for translation unit $2.',
	'tpt-showpage-intro' => 'Below new, existing and deleted sections are listed.
Before marking this version for translation, check that the changes to sections are minimised to avoid unnecessary work for translators.',
	'tpt-mark-summary' => 'Marked this version for translation',
	'tpt-edit-failed' => 'Could not update the page: $1',
	'tpt-already-marked' => 'The latest version of this page has already been marked for translation.',

	# Page list on the special page
	'tpt-list-nopages' => 'No pages are marked for translation nor ready to be marked for translation.',
	'tpt-old-pages' => 'Some version of {{PLURAL:$1|this page has|these pages have}} been marked for translation.',
	'tpt-new-pages' => '{{PLURAL:$1|This page contains|These pages contain}} text with translation tags, but no version of {{PLURAL:$1|this page is|these pages are}} currently marked for translation.',
	'tpt-rev-latest' => 'latest version',
	'tpt-rev-old' => 'difference to previous marked version',
	'tpt-rev-mark-new' => 'mark this version for translation',
	'tpt-translate-this' => 'translate this page',

	# Source and translation page headers
	'translate-tag-translate-link-desc' => 'Translate this page',
	'translate-tag-markthis' => 'Mark this page for translation',
	'translate-tag-markthisagain' => 'This page has <span class="plainlinks">[$1 changes]</span> since it was last <span class="plainlinks">[$2 marked for translation]</span>.',
	'translate-tag-hasnew' => 'This page contains <span class="plainlinks">[$1 changes]</span> which are not marked for translation.',
	'tpt-translation-intro' => 'This page is a <span class="plainlinks">[$1 translated version]</span> of a page [[$2]] and the translation is $3% complete and up to date.',
	'tpt-translation-intro-fuzzy' => 'Outdated translations are marked like this.',

	'tpt-languages-legend' => 'Other languages:',

	'tpt-target-page' => 'This page cannot be updated manually.
This page is a translation of page [[$1]] and the translation can be updated using [$2 the translation tool].',
	'tpt-unknown-page' => 'This namespace is reserved for content page translations.
The page you are trying to edit does not seem to correspond any page marked for translation.',

	'tpt-install' => 'Run php maintenance/update.php or web install to enable page translation feature.',

	'tpt-render-summary' => 'Updating to match new version of source page',
);

/** Message documentation (Message documentation)
 * @author Darth Kule
 * @author EugeneZelenko
 * @author Fryed-peach
 * @author Mormegil
 * @author Purodha
 * @author Siebrand
 */
$messages['qqq'] = array(
	'pagetranslation' => 'Title of [[Special:PageTranslation]] and its name in [[Special:SpecialPages]].',
	'right-pagetranslation' => '{{doc-right}}',
	'tpt-desc' => 'Short description of this extension, shown on [[Special:Version]]. Do not translate or change links.',
	'tpt-sections-oldnew' => '"New and existing" refers to the sum of: (a) new translation units, that were added, plus (b) the already existing ones, which were retained.',
	'tpt-saveok' => '$1 is a page title,
$2 is a count of sections which can be used with PLURAL,
$3 is an URL.',
	'tpt-mark-summary' => 'This message is used as an edit summary.',
	'tpt-old-pages' => 'The words "some version" refer to "one version of the page", or "a single version of each of the pages", respectively. Each page can have either one or none of its versions marked for translaton.',
	'tpt-rev-old' => '',
	'translate-tag-markthisagain' => '"has changes" is to be understood as "has been altered/edited"',
	'translate-tag-hasnew' => '"has changes" is to be understood as "has been altered/edited"',
	'tpt-languages-legend' => 'The caption of a language selector displayed using <code>&lt;languages /&gt;</code>, e.g. on [[Project list]].',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 */
$messages['af'] = array(
	'translate-tag-translate-link-desc' => 'Vertaal die bladsy',
);

/** Arabic (العربية)
 * @author Meno25
 * @author ترجمان05
 */
$messages['ar'] = array(
	'pagetranslation' => 'ترجمة صفحة',
	'tpt-section' => 'القسم:',
	'tpt-section-new' => 'قسم جديد:',
	'tpt-template' => 'قالب صفحة',
	'tpt-diff-old' => 'نص سابق',
	'tpt-diff-new' => 'نص جديد',
	'tpt-sections-template' => 'قالب صفحة ترجمة',
	'tpt-badtitle' => 'اسم الصّفحة المعطى ($1) ليس عنوانا صحيحا',
	'tpt-oldrevision' => '$2 ليست آخر نسخة للصّفحة [[$1]].
فقط آخر النسخ يمكن أن تؤشّر للترجمة.',
	'tpt-rev-latest' => 'آخر نسخة',
	'tpt-rev-old' => 'النسخة $1',
	'tpt-translate-this' => 'ترجم هذه الصّفحة',
	'translate-tag-translate-link-desc' => 'ترجمة هذه الصفحة',
	'tpt-languages-legend' => 'لغات أخرى:',
);

/** Egyptian Spoken Arabic (مصرى)
 * @author Meno25
 */
$messages['arz'] = array(
	'translate-tag-translate-link-desc' => 'ترجمة هذه الصفحة',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
	'translate-tag-translate-link-desc' => 'Traducir esta páxina',
);

/** Belarusian (Taraškievica orthography) (Беларуская (тарашкевіца))
 * @author EugeneZelenko
 * @author Jim-by
 */
$messages['be-tarask'] = array(
	'pagetranslation' => 'Пераклад старонкі',
	'right-pagetranslation' => 'пазначаць вэрсіяў старонак для перакладу',
	'tpt-desc' => 'Пашырэньне для перакладу старонак зьместу',
	'tpt-section' => 'Адзінка перакладу $1',
	'tpt-section-new' => 'Новая адзінка перакладу. Назва: $1',
	'tpt-section-deleted' => 'Адзінка перакладу $1',
	'tpt-template' => 'Старонка шаблёну',
	'tpt-templatediff' => 'Старонка шаблёну была зьменена.',
	'tpt-diff-old' => 'Папярэдні тэкст',
	'tpt-diff-new' => 'Новы тэкст',
	'tpt-submit' => 'Пазначыць гэту вэрсію для перакладу',
	'tpt-sections-oldnew' => 'Новыя і існуючыя адзінкі перакладу',
	'tpt-sections-deleted' => 'Выдаленыя адзінкі перакладу',
	'tpt-sections-template' => 'Шаблён старонкі перакладу',
	'tpt-badtitle' => 'Пададзеная назва старонкі ($1) не зьяўляецца слушнай',
	'tpt-oldrevision' => '$2 не зьяўляецца апошняй вэрсіяй старонкі [[$1]].
Толькі апошнія вэрсіі могуць пазначацца для перакладу.',
	'tpt-notsuitable' => 'Старонка $1 ня можа быць перакладзеная.
Упэўніцеся, што яна ўтрымлівае тэгі <nowiki><translate></nowiki> і мае слушны сынтаксіс.',
	'tpt-saveok' => 'Старонка «$1» была пазначаная для перакладу з $2 {{PLURAL:$2|адзінкай перакладу|адзінкамі перакладу|адзінкамі перакладу}}.
Зараз старонка можа быць <span class="plainlinks">[$3 перакладзеная]</span>.',
	'tpt-badsect' => '«$1» не зьяўляецца слушнай назвай для адзінкі перакладу $2.',
	'tpt-showpage-intro' => 'Ніжэй знаходзяцца новыя, існуючыя і выдаленыя сэкцыі.
Перад пазначэньнем гэтай вэрсіі для перакладу, праверце зьмены ў сэкцыях для таго, каб пазьбегнуць непатрэбнай працы для перакладчыкаў.',
	'tpt-mark-summary' => 'Пазначыў гэтую вэрсію для перакладу',
	'tpt-edit-failed' => 'Немагчыма абнавіць старонку: $1',
	'tpt-already-marked' => 'Апошняя вэрсія гэтай старонкі ўжо была пазначана для перакладу.',
	'tpt-list-nopages' => 'Старонкі для перакладу не пазначаныя альбо не падрыхтаваныя.',
	'tpt-old-pages' => 'Некаторыя вэрсіі {{PLURAL:$1|гэтай старонкі|гэтых старонак}} былі пазначаны для перакладу.',
	'tpt-new-pages' => '{{PLURAL:$1|Гэта старонка ўтрымлівае|Гэтыя старонкі ўтрымліваюць}} тэкст з тэгамі перакладу, але {{PLURAL:$1|пазначанай для перакладу вэрсіі гэтай старонкі|пазначаных для перакладу вэрсіяў гэтых старонак}} няма.',
	'tpt-rev-latest' => 'апошняя вэрсія',
	'tpt-rev-old' => 'розьніца з папярэдняй пазначанай вэрсіяй',
	'tpt-rev-mark-new' => 'пазначыць гэту вэрсію для перакладу',
	'tpt-translate-this' => 'перакласьці гэту старонку',
	'translate-tag-translate-link-desc' => 'Перакласьці гэту старонку',
	'translate-tag-markthis' => 'Пазначыць гэту старонку для перакладу',
	'translate-tag-markthisagain' => 'Гэта старонка ўтрымлівае <span class="plainlinks">[$1 зьмены]</span> пасьля апошняй <span class="plainlinks">[$2 пазнакі для перакладу]</span>.',
	'translate-tag-hasnew' => 'Гэта старонка ўтрымлівае <span class="plainlinks">[$1 зьмены]</span> не пазначаныя для перакладу.',
	'tpt-translation-intro' => 'Гэта старонка <span class="plainlinks">[$1 перакладзеная вэрсія]</span> старонкі [[$2]], пераклад завершаны на $3%.',
	'tpt-translation-intro-fuzzy' => 'Састарэлыя пераклады пазначаны наступным чынам.',
	'tpt-languages-legend' => 'Іншыя мовы:',
	'tpt-target-page' => 'Гэта старонка ня можа быць абноўлена ўручную.
Гэта старонка зьяўляецца перакладам старонкі [[$1]], пераклад можа быць абноўлены з выкарыстаньнем [$2 інструмэнта перакладу].',
	'tpt-unknown-page' => 'Гэта прастора назваў зарэзэрваваная для перакладаў старонак зьместу.
Старонка, якую Вы спрабуеце рэдагаваць, верагодна не зьвязана зь якой-небудзь старонкай пазначанай для перакладу.',
	'tpt-install' => 'Запусьціце php maintenance/update.php альбо усталюйце праз вэб-інтэрфэйс для актывізацыі інструмэнтаў перакладу старонак.',
	'tpt-render-summary' => 'Абнаўленьне для адпаведнасьці новай вэрсіі крынічнай старонкі',
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'tpt-rev-mark-new' => 'отбелязване на тази версия за превеждане',
	'tpt-translate-this' => 'превеждане на тази страница',
	'translate-tag-translate-link-desc' => 'Превеждане на тази страница',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'translate-tag-translate-link-desc' => 'Treiñ ar bajenn-mañ',
);

/** Bosnian (Bosanski)
 * @author CERminator
 */
$messages['bs'] = array(
	'pagetranslation' => 'Prijevod stranice',
	'right-pagetranslation' => 'Označanje verzija stranica za prevođenje',
	'tpt-desc' => 'Proširenje za prevođenje stranica sadržaja',
	'tpt-section' => 'Jedinica prevođenja $1',
	'tpt-section-new' => 'Nova jedinica prevođenja. Naziv: $1',
	'tpt-section-deleted' => 'Jedinica prevođenja $1',
	'tpt-template' => 'Šablon stranice',
	'tpt-templatediff' => 'Šablon stranice se izmijenio.',
	'tpt-diff-old' => 'Prethodni tekst',
	'tpt-diff-new' => 'Novi tekst',
	'tpt-submit' => 'Označi ovu verziju za prevođenje',
	'tpt-sections-oldnew' => 'Nove i postojeće prevodilačke jedinice',
	'tpt-sections-deleted' => 'Obrisane prevodilačke jedinice',
	'tpt-sections-template' => 'Šablon stranice prevođenja',
	'tpt-badtitle' => 'Zadano ime stranice ($1) nije valjan naslov',
	'tpt-oldrevision' => '$2 nije posljednja verzija stranice [[$1]].
Jedino posljednje verzije se mogu označiti za prevođenje.',
	'tpt-notsuitable' => 'Stranica $1 nije pogodna za prevođenje.
Provjerite da postoje oznake <nowiki><translate></nowiki> i da ima valjanu sintaksu.',
	'tpt-badsect' => '"$1" nije valjano ime za jedinicu prevođenja $2.',
	'tpt-mark-summary' => 'Ova vezija označena za prevođenje',
	'tpt-edit-failed' => 'Nije moguće ažurirati stranicu: $1',
	'tpt-already-marked' => 'Posljednja verzija ove stranice je već označena za prevođenje.',
	'tpt-old-pages' => 'Neke verzije {{PLURAL:$1|ove stranice|ovih stranica}} su označene za prevođenje.',
	'tpt-rev-latest' => 'posljednja verzija',
	'tpt-rev-old' => 'razlika od ranije označene verzije',
	'tpt-rev-mark-new' => 'označi ovu verziju za prevođenje',
	'tpt-translate-this' => 'prevedi ovu stranicu',
	'translate-tag-translate-link-desc' => 'Prevedi ovu stranicu',
	'translate-tag-markthis' => 'Označi ovu stranicu za prevođenje',
	'translate-tag-hasnew' => 'Ova stranica sadrži <span class="plainlinks">[$1 izmjena]</span> koje nisu označene za prevođenje.',
	'tpt-translation-intro-fuzzy' => 'Zastarijeli prijevodi su označeni ovako.',
	'tpt-languages-legend' => 'Drugi jezici:',
);

/** Buginese (ᨅᨔ ᨕᨘᨁᨗ)
 * @author Kurniasan
 */
$messages['bug'] = array(
	'translate-tag-translate-link-desc' => "Tare'juma iyyedé leppa",
);

/** Catalan (Català)
 * @author Jordi Roqué
 * @author SMP
 * @author Solde
 */
$messages['ca'] = array(
	'pagetranslation' => "Traducció d'una pàgina",
	'tpt-section' => 'Unitat de traducció $1',
	'tpt-section-new' => 'Nova unitat de traducció. Nom: $1',
	'tpt-diff-old' => 'Text anterior',
	'tpt-diff-new' => 'Text nou',
	'tpt-badtitle' => 'El nom de pàgina donat ($1) no és un títol vàlid',
	'tpt-notsuitable' => 'La pàgina $1 no està preparada per a la seva traducció.
Assegureu-vos que té les etiquetes <nowiki><translate></nowiki> i una sintaxi vàlida.',
	'tpt-rev-latest' => 'última versió',
	'translate-tag-translate-link-desc' => 'Traduir aquesta pàgina',
	'tpt-languages-legend' => 'Altres idiomes:',
);

/** Sorani (Arabic script) (‫کوردی (عەرەبی)‬)
 * @author رزگار
 */
$messages['ckb-arab'] = array(
	'translate-tag-translate-link-desc' => 'وەرگێڕانی ئەم پەرە',
	'tpt-languages-legend' => 'زمانەکانی‌تر:',
);

/** Czech (Česky)
 * @author Matěj Grabovský
 * @author Mormegil
 */
$messages['cs'] = array(
	'pagetranslation' => 'Překlad stránek',
	'translate-tag-translate-link-desc' => 'Přeložit tuto stránku',
	'translate-tag-hasnew' => 'Tato stránka obsahuje <span class="plainlinks">[$1 změny]</span>, které nebyly označeny pro překlad.',
	'tpt-translation-intro' => 'Toto je <span class="plainlinks">[$1 přeložená verze]</span> stránky [[$2]], překlad je úplný a aktuální na $3 %.',
	'tpt-translation-intro-fuzzy' => 'Takto jsou označeny zastaralé části překladu.',
	'tpt-languages-legend' => 'Jiné jazyky:',
);

/** German (Deutsch)
 * @author ChrisiPK
 * @author Purodha
 * @author Umherirrender
 */
$messages['de'] = array(
	'pagetranslation' => 'Übersetzung von Seiten',
	'right-pagetranslation' => 'Seitenversionen für die Übersetzung markieren',
	'tpt-desc' => 'Erweiterung zur Übersetzung von Wikiseiten',
	'tpt-section' => 'Übersetzungseinheit $1',
	'tpt-section-new' => 'Neue Übersetzungseinheit. Name: $1',
	'tpt-section-deleted' => 'Übersetzungseinheit $1',
	'tpt-template' => 'Seitenvorlage',
	'tpt-templatediff' => 'Die Seitenvorlage hat sich geändert.',
	'tpt-diff-old' => 'Vorheriger Text',
	'tpt-diff-new' => 'Neuer Text',
	'tpt-submit' => 'Diese Version zur Übersetzung markieren',
	'tpt-sections-oldnew' => 'Neue und vorhandene Übersetzungseinheiten',
	'tpt-sections-deleted' => 'Gelöschte Übersetzungseinheiten',
	'tpt-sections-template' => 'Übersetzungsseitenvorlage',
	'tpt-badtitle' => 'Der angegebene Seitenname „$1“ ist kein gültiger Titel',
	'tpt-oldrevision' => '$2 ist nicht die letzte Version der Seite [[$1]].
Nur die letzte Version kann zur Übersetzung markiert werden.',
	'tpt-notsuitable' => 'Die Seite $1 ist nicht zum Übersetzen geeignet.
Stelle sicher, das ein <nowiki><translate></nowiki>-Tag und gültige Syntax verwendet wird.',
	'tpt-saveok' => 'Die Seite [[$1]] wurde mit $2 {{PLURAL:$2|übersetzbarem Abschnitt|übersetzbaren Abschnitten}} für die Übersetzung markiert.
Diese Seite kann nun <span class="plainlinks">[$3 übersetzt]</span> werden.',
	'tpt-badsect' => '„$1“ ist kein gültiger Name für Übersetzungseinheit $2.',
	'tpt-showpage-intro' => 'Untenstehend sind neue, vorhandene und gelöschte Abschnitte aufgelistet.
Bevor du diese Version zur Übersetzung markierst, stelle sicher, dass die Änderungen an den Abschnitten minimal sind, um unnötige Arbeit für Übersetzer zu verhindern.',
	'tpt-mark-summary' => 'Diese Seite wurde zum Übersetzen markiert',
	'tpt-edit-failed' => 'Seite kann nicht aktualisiert werden: $1',
	'tpt-already-marked' => 'Die letzte Version dieser Seite wurde bereits zur Übersetzung markiert.',
	'tpt-list-nopages' => 'Es sind keine Seiten zur Übersetzung markiert und auch keine bereit, zur Übersetzung markiert zu werden.',
	'tpt-old-pages' => 'Eine Version dieser {{PLURAL:$1|Seite|Seiten}} wurde zur Übersetzung markiert.',
	'tpt-new-pages' => '{{PLURAL:$1|Diese Seite beinhaltet|Diese Seiten beinhalten}} Text zum Übersetzen, aber es wurde noch keine Version dieser {{PLURAL:$1|Seite|Seiten}} zur Übersetzung markiert.',
	'tpt-rev-latest' => 'Letzte Version',
	'tpt-rev-old' => 'Unterschied zu vorheriger markierter Version',
	'tpt-rev-mark-new' => 'diese Version zur Übersetzung markieren',
	'tpt-translate-this' => 'diese Seite übersetzen',
	'translate-tag-translate-link-desc' => 'Diese Seite übersetzen',
	'translate-tag-markthis' => 'Diese Seite zur Übersetzung markieren',
	'translate-tag-markthisagain' => 'Diese Seite wurde <span class="plainlinks">[$1 bearbeitet]</span>, nachdem sie zuletzt <span class="plainlinks">[$2 zur Übersetzung markiert]</span> wurde.',
	'translate-tag-hasnew' => 'Diese Seite enthält <span class="plainlinks">[$1 Bearbeitungen]</span>, die nicht zur Übersetzung markiert sind.',
	'tpt-translation-intro' => 'Diese Seite ist eine <span class="plainlinks">[$1 übersetzte Version]</span> der Seite [[$2]] und die Übersetzung ist zu $3 % abgeschlossen und aktuell.',
	'tpt-translation-intro-fuzzy' => 'Nicht aktuelle Übersetzungen werden wie dieser Text markiert.',
	'tpt-languages-legend' => 'Andere Sprachen:',
	'tpt-target-page' => 'Diese Seite kann nicht manuell aktualisiert werden.
Diese Seite ist eine Übersetzung der Seite [[$1]] und die Übersetzung kann mithilfe des [$2 Übersetzungswerkzeuges] aktualisiert werden.',
	'tpt-unknown-page' => 'Dieser Namensraum ist für das Übersetzen von Wikiseiten reserviert.
Die Seite, die gerade bearbeitet wird, hat keine Verbindung zu einer übersetzbaren Seite.',
	'tpt-install' => 'Bitte <tt>maintenance/update.php</tt> oder Webinstallation ausführen, um die Seitenübersetzung zu aktivieren.',
	'tpt-render-summary' => 'Übernehme Bearbeitung einer neuen Version der Quellseite',
);

/** German (formal address) (Deutsch (Sie-Form))
 * @author Umherirrender
 */
$messages['de-formal'] = array(
	'tpt-showpage-intro' => 'Untenstehend sind neue, vorhandene und gelöschte Abschnitte aufgelistet.
Bevor Sie diese Version zur Übersetzung markieren, stellen Sie bitte sicher, dass die Änderungen an den Abschnitten minimal sind, um unnötige Arbeit für Übersetzer zu verhindern.',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'pagetranslation' => 'Pśełožowanje bokow',
	'right-pagetranslation' => 'Wersije bokow za pśełožowanje markěrowaś',
	'tpt-desc' => 'Rozšyrjenje za pśełožowanje wopśimjeśowych bokow',
	'tpt-section' => 'Pśełožowańska jadnotka $1',
	'tpt-section-new' => 'Nowa pśełožowańska jadnotka. Mě: $1',
	'tpt-section-deleted' => 'Pśełožowańska jadnotka $1',
	'tpt-template' => 'Bokowa pśedłoga',
	'tpt-templatediff' => 'Bokowa pśedłoga jo se změniła.',
	'tpt-diff-old' => 'Pśedchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-sections-oldnew' => 'Nowe a eksistowace pśełožowańske jadnotki',
	'tpt-sections-deleted' => 'Wulašowane pśełožowańske jadnotki',
	'tpt-sections-template' => 'Pśedłoga pśełožowańskego boka',
	'tpt-badtitle' => 'Pódane bokowe mě ($1) njejo płaśiwy titel',
	'tpt-oldrevision' => '$2 njejo aktualna wersija boka [[$1]].
Jano aktualne wersije daju se za pśełožowanje markěrowaś.',
	'tpt-notsuitable' => 'Bok $1 njejo gódny za pśełožowanje.
Zawěsć, až ma toflicki <nowiki><translate></nowiki> a płaśiwu syntaksu.',
	'tpt-saveok' => 'Bok [[$1]] jo se markěrował za pśełožowanje z $2 {{PLURAL:$2|pśełožujobneju jadnotku|pśełožujobnyma jadnotkoma|pśełožujobnymi jadnotkami|pśełožujobnymi jadnotkami}}. Bok móže se něnto <span class="plainlinks">[$3 pśełožowaś]</span>.',
	'tpt-badsect' => '"$1" njejo płaśiwe mě za pśełožowańsku jadnotku $2.',
	'tpt-showpage-intro' => 'Dołojce su nowe, eksistěrujuce a wulašowane wótrězki nalicone.
Nježli až markěrujoš toś tu wersiju za pśełožowanje, pśekontrolěruj, lěc změny na wótrězkach su zminiměrowane, aby se wobinuł njetrěbne źěło za pśełožowarjow.',
	'tpt-mark-summary' => 'Jo toś tu wersiju za pśełožowanje markěrował',
	'tpt-edit-failed' => 'Toś ten bok njejo se dał aktualizěrowaś: $1',
	'tpt-already-marked' => 'Aktualna wersija toś togo boka jo južo za pśełožowanje markěrowana.',
	'tpt-list-nopages' => 'Žedne boki njejsu za pśełožowanje markěrowane ani su gótowe, aby se za pśełožowanje markěrowali.',
	'tpt-old-pages' => 'Někaka wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} jo se za pśełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Toś ten bok wopśimujo|Toś tej boka wopśumujotej|Toś te boki wopśimuju|Toś te boki wopśimuju}} tekst z pśełožowańskimi toflickami, ale žedna wersija {{PLURAL:$1|toś togo boka|toś teju bokowu|toś tych bokow|toś tych bokow}} njejo tuchylu za pśełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'rozdźěl k pjerwjejšnej markěrowanej wersiji',
	'tpt-rev-mark-new' => 'toś tu wersiju za pśełožowanje markěrowaś',
	'tpt-translate-this' => 'toś ten bok pśełožyś',
	'translate-tag-translate-link-desc' => 'Toś ten bok pśełožyś',
	'translate-tag-markthis' => 'Toś ten bok za pśełožowanje markěrowaś',
	'translate-tag-markthisagain' => 'Toś ten bok ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wót togo casa, ako jo se slědny raz <span class="plainlinks">[$2 za pśełožowanje markěrował]</span>.',
	'translate-tag-hasnew' => 'Toś ten bok wopśimujo <span class="plainlinks">[$1 {{PLURAL:$1|změnu, kótaraž njejo markěrowana|změnje, kótarejž njejstej markěrowanej|změny, kótare njejsu markěrowane|změnow, kótarež njejsu markěrowane}}]</span> za pśełožowanje.',
	'tpt-translation-intro' => 'Toś ten bok jo <span class="plainlinks">[$1 pśełožona wersija]</span> boka [[$2]] a $3 % pśełožka jo dogótowane a pśełožk jo aktualne.',
	'tpt-translation-intro-fuzzy' => 'Zestarjone pśełožki su kaž toś ten markěrowany.',
	'tpt-languages-legend' => 'Druge rěcy:',
	'tpt-target-page' => 'Toś ten bok njedajo se manuelnje aktualizěrowaś.
Toś ten bok jo pśełožk boka [[$1]] a pśełožk dajo se z pomocu [$2 Pśełožyś] aktualizěrowaś.',
	'tpt-unknown-page' => 'Toś ten mjenjowy rum jo za pśełožki wopśimjeśowych bokow wuměnjony.
Zda se, až bok, kótaryž wopytujoš wobźěłaś, njewótpowědujo bokoju, kótaryž jo za pśełožowanje markěrowany.',
	'tpt-install' => 'Wuwjeź php maintenance/update.php abo webinstalaciju, aby zmóžnił funkciju pśełožowanja bokow.',
	'tpt-render-summary' => 'Aktualizacija pó nowej wersiji žrědłowego boka',
);

/** Greek (Ελληνικά)
 * @author Dead3y3
 */
$messages['el'] = array(
	'translate-tag-translate-link-desc' => 'Μεταφράστε αυτή τη σελίδα',
);

/** Esperanto (Esperanto)
 * @author Yekrats
 */
$messages['eo'] = array(
	'translate-tag-translate-link-desc' => 'Traduki ĉi tiun paĝon',
);

/** Spanish (Español)
 * @author Crazymadlover
 * @author Sanbec
 */
$messages['es'] = array(
	'pagetranslation' => 'Traducción de página',
	'right-pagetranslation' => 'Marcar versiones de páginas para traducción',
	'tpt-desc' => 'Extensiones para traducir páginas de contenido',
	'tpt-section' => 'Unidad de traducción $1',
	'tpt-section-new' => 'Nueva unidad de traducción. Nombre: $1',
	'tpt-section-deleted' => 'Unidad de traducción $1',
	'tpt-template' => 'Plantilla de página',
	'tpt-templatediff' => 'La plantilla de página ha cambiado.',
	'tpt-diff-old' => 'Texto previo',
	'tpt-diff-new' => 'Nuevo texto',
	'tpt-submit' => 'Marcar esta versión para traducción',
	'tpt-sections-oldnew' => 'Unidades de traducción nuevas y existentes',
	'tpt-sections-deleted' => 'Unidades de traducción borradas',
	'tpt-sections-template' => 'Plantilla de página de traducción',
	'tpt-badtitle' => 'Nombre de página dado ($1) no es un título válido',
	'tpt-oldrevision' => '$2 no es la última versión de la página [[$1]].
Solamente las últimas versiones pueden ser marcadas para traducción',
	'tpt-notsuitable' => 'La página $1 no es adecuada para traducción.
Asegúrate que tiene etiquetas <nowiki><translate></nowiki> y tiene una sintaxis válida.',
	'tpt-saveok' => 'La página [[$1]] ha sido marcada para traducción con $2 {{PLURAL:$2|unidad de traducción |unidades de traducción}}.
La página puede ser ahora <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" no es un nombre válido para una unidad de traducción $2.',
	'tpt-showpage-intro' => 'Debajo secciones nuevas, existentes y borradas están listadas.
Antes de marcar esta versión para traducción, verifica que los cambios a las secciones son mínimos para evitar trabajo innecesario a los traductores.',
	'tpt-mark-summary' => 'Marcada esta sección para traducción',
	'tpt-edit-failed' => 'No pudo actualizar la página : $1',
	'tpt-already-marked' => 'La última versión de esta página ya ha sido marcada para traducción.',
	'tpt-list-nopages' => 'Ninguna página está marcada para traducción ni lista para ser marcada para traducción.',
	'tpt-old-pages' => 'Alguna versión de {{PLURAL:$1|esta página|estas páginas han}} sido marcadas para traducción.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contiene|Estas páginas contienen}} texto con etiquetas de traducción, pero ninguna versión de {{PLURAL:$1|esta página est|estas páginas están}} actualmente marcadas para traducción.',
	'tpt-rev-latest' => 'última versión',
	'tpt-rev-old' => 'diferenciar a la versión marcada previa',
	'tpt-rev-mark-new' => 'marcar esta versión para traducción',
	'tpt-translate-this' => 'traducir esta página',
	'translate-tag-translate-link-desc' => 'Traducir esta página',
	'translate-tag-markthis' => 'Marcar esta página para traducción',
	'translate-tag-markthisagain' => 'Esta página tiene <span class="plainlinks">[$1 cambios]</span> desde la última vez que fue <span class="plainlinks">[$2 marcada para traducción]</span>.',
	'translate-tag-hasnew' => 'Esta página contiene <span class="plainlinks">[$1 cambios]</span> los cuales no han sido marcados para traducción.',
	'tpt-translation-intro' => 'Esta página es una <span class="plainlinks">[$1 versión traducida]</span> de una página [[$2]] y la traducción está $3% completa y actualizada.',
	'tpt-translation-intro-fuzzy' => 'Traducciones desactualizadas están marcadas así.',
	'tpt-languages-legend' => 'Otros idiomas:',
	'tpt-target-page' => 'Esta página no puede ser actualizada manualmente.
Esta página es una traducción de la página [[$1]] y la traducción puede ser actualizada usando [$2 la herramienta de traducción].',
	'tpt-unknown-page' => 'Este espacio de nombre está reservado para traducciones de páginas de contenido.
La página que estás tratando de editar no parece corresponder con alguna página marcada para traducción.',
);

/** Estonian (Eesti)
 * @author Ker
 */
$messages['et'] = array(
	'translate-tag-translate-link-desc' => 'Tõlgi see leht',
);

/** Basque (Euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'translate-tag-translate-link-desc' => 'Itzuli orri hau',
);

/** Finnish (Suomi)
 * @author Cimon Avaro
 * @author Crt
 * @author Silvonen
 */
$messages['fi'] = array(
	'pagetranslation' => 'Sivun kääntäminen',
	'right-pagetranslation' => 'Merkitä versiot, jotka otetaan käännettäviksi',
	'tpt-desc' => 'Laajennus sisältösivujen kääntämiseen.',
	'tpt-section' => 'Käännösosio $1',
	'tpt-section-new' => 'Uusi käännösosio. Nimi: $1',
	'tpt-section-deleted' => 'Käännösosio $1',
	'tpt-template' => 'Sivun mallipohja',
	'tpt-templatediff' => 'Sivun mallipohja on muuttunut.',
	'tpt-diff-old' => 'Aikaisempi teksti',
	'tpt-diff-new' => 'Uusi teksti',
	'tpt-submit' => 'Merkitse tämä versio käännettäväksi',
	'tpt-sections-oldnew' => 'Uudet ja jo olemassa olevat käännösosiot',
	'tpt-sections-deleted' => 'Poistetut käännösosiot',
	'tpt-sections-template' => 'Käännössivun mallipohja',
	'tpt-badtitle' => 'Sivun nimi ($1) jota tarjottiin ei ole kelvollinen otsikko',
	'tpt-oldrevision' => '$2 ei ole uusin versio sivusta [[$1]]. 
Ainoastaan uusimmat versiot voidaan merkitä käännettäviksi.',
	'tpt-notsuitable' => 'Sivu $1 ei sovellu käännettäväksi.
Varmista, että siinä on <nowiki><translate></nowiki>-merkinnät ja että sillä on toimiva syntaksi.',
	'tpt-saveok' => 'Sivu [[$1]] on merkitty käännettäväksi $2 {{PLURAL:$2|käännösosiolla|käännösosioilla}}.
Sivu voidaan nyt <span class="plainlinks">[$3 kääntää]</span>.',
	'tpt-badsect' => '"$1" on asiaankuuulumaton nimi käännösosiolle $2.',
	'tpt-showpage-intro' => 'Alempana listattu uusia, nykyisiä ja poistettavia osioita. Ennen kuin merkitset tämän version käännettäväksi, tarkista, että muutokset osioihin on minimoitu, jotta vältämme turhan työn kääntäjille.',
	'tpt-mark-summary' => 'Merkitty tämä versio käännettäväksi',
	'tpt-edit-failed' => 'Ei voitu tallentaa muutosta sivulle: $1',
	'tpt-already-marked' => 'Uusin versio tästä sivusta on jo merkitty käännettäväksi.',
	'tpt-list-nopages' => 'Sivuja ei ole merkitty käännettäväksi eikä valmiina käännettäväksi merkintää varten.',
	'tpt-old-pages' => 'Joku versio {{PLURAL:$1|tästä sivusta on|näistä sivuista on}} on merkitty käännettäväksi.',
	'tpt-new-pages' => '{{PLURAL:$1|Tämä sivu sisältää|Nämä sivut sisältävät}} tekstiä johon on tehty käännös-merkkaus, mutta mikään versio {{PLURAL:$1|tästä sivusta|näistä sivuista}} ei ole tällä hetkellä merkattu käännettäväksi.',
	'tpt-rev-latest' => 'uusin versio',
	'tpt-rev-old' => 'eroavaisuudet edelliseen merkittyyn versioon',
	'tpt-rev-mark-new' => 'merkitse tämä versio käännettäväksi',
	'tpt-translate-this' => 'käännä tämä sivu',
	'translate-tag-translate-link-desc' => 'Käännä tämä sivu',
	'translate-tag-markthis' => 'Merkitse tämä sivu käännettäväksi',
	'translate-tag-markthisagain' => 'Tähän sivuun on tehty <span class="plainlinks">[$1 muutoksia]</span> sen jälkeen kun se viimeksi <span class="plainlinks">[$2 merkittiin käännettäväksi]</span>.',
	'translate-tag-hasnew' => 'Tämä sivu sisältää <span class="plainlinks">[$1 muutoksia],</span> joita ei ole merkitty käännettäväksi.',
	'tpt-translation-intro' => 'Tämä sivu on <span class="plainlinks">[$1 käännetty versio]</span> sivusta [[$2]] ja käännös on $3% täydellinen ja ajan tasalla.',
	'tpt-translation-intro-fuzzy' => 'Vanhentuneet käännökset, joiden lähdeteksti on muuttunut merkitään näin.',
	'tpt-languages-legend' => 'Muut kielet:',
	'tpt-target-page' => 'Tätä sivua ei voi päivittää manuaalisesti. Tämä sivu on käännös sivusta [[$1]] ja käännös voidaan päivittää käyttämällä [$2 käännöstyökalua].',
	'tpt-unknown-page' => 'Nimiavaruus on varattu sisältösivujen käännöksille. Sivu, jota yrität muokata, ei näytä vastaavan mitään sivua joka on merkitty käännettäväksi.',
	'tpt-install' => 'Suorita maintenance/update.php tai verkkoasennus, jotta sivun käännösominaisuus toimii.',
	'tpt-render-summary' => 'Päivittäminen vastaamaan uutta versiota lähdesivusta',
);

/** French (Français)
 * @author Crochet.david
 * @author Grondin
 * @author IAlex
 */
$messages['fr'] = array(
	'pagetranslation' => 'Traduction de pages',
	'right-pagetranslation' => 'Marquer des versions de pages pour être traduites',
	'tpt-desc' => 'Extension pour traduire des pages de contenu',
	'tpt-section' => 'Unité de traduction $1',
	'tpt-section-new' => 'Nouvelle unité de traduction. Nom : $1',
	'tpt-section-deleted' => 'Unité de traduction $1',
	'tpt-template' => 'Modèle de page',
	'tpt-templatediff' => 'Le modèle de page a changé.',
	'tpt-diff-old' => 'Texte précédent',
	'tpt-diff-new' => 'Nouveau texte',
	'tpt-submit' => 'Marquer cette version pour être traduite',
	'tpt-sections-oldnew' => 'Unités de traduction nouvelles et existantes',
	'tpt-sections-deleted' => 'Unités de traduction supprimées',
	'tpt-sections-template' => 'Modèle de page de traduction',
	'tpt-badtitle' => 'Le nom de page donné ($1) n’est pas un titre valide',
	'tpt-oldrevision' => '$2 n’est pas la dernière version de la page [[$1]].
Seule la dernière version de la page peut être marquée pour être traduite.',
	'tpt-notsuitable' => 'La page $1 n’est pas convenable pour être traduite.
Soyez sûr qu’elle contient la balise <nowiki><translate></nowiki> et qu’elle a une syntaxe correcte.',
	'tpt-saveok' => 'La page « $1 » a été marqué pour être traduite avec $2 {{PLURAL:$2|unité de traduction|unités de traduction}}.
La page peut être <span class="plainlinks">[$3 traduite]</span> dès maintenant.',
	'tpt-badsect' => '« $1 » n’est pas un nom valide pour une unité de traduction $2.',
	'tpt-showpage-intro' => 'Ci-dessous, les nouvelles traductions, celles existantes et supprimées.
Avant de marquer ces versions pour être traduites, vérifier que les modifications aux sections sont minimisées pour éviter du travail inutile aux traducteurs.',
	'tpt-mark-summary' => 'Cette version a été marqué pour être traduite',
	'tpt-edit-failed' => 'Impossible de mettre à jour la page $1',
	'tpt-already-marked' => 'La dernière version de cette page a déjà été marquée pour être traduite.',
	'tpt-list-nopages' => 'Aucune page n’a été marquée pour être traduite ou prête pour l’être.',
	'tpt-old-pages' => 'Des versions de {{PLURAL:$1|cette page|ces pages}} ont été marquées pour être traduites.',
	'tpt-new-pages' => '{{PLURAL:$1|Cette page contient|Ces pages contiennent}} du texte avec des balises de traduction, mais aucune version de {{PLURAL:$1|cette page n’est marqué pour être traduite|ces page ne sont marquées pour être traduites}}.',
	'tpt-rev-latest' => 'dernière version',
	'tpt-rev-old' => 'différence avec la version marquée précédente',
	'tpt-rev-mark-new' => 'marquer cette version pour être traduite',
	'tpt-translate-this' => 'traduire cette page',
	'translate-tag-translate-link-desc' => 'Traduire cette page',
	'translate-tag-markthis' => 'Marquer cette page pour être traduite',
	'translate-tag-markthisagain' => 'Cette page a eu <span class="plainlinks">[$1 des modifications]</span> depuis qu’il a été dernièrement <span class="plainlinks">[$2 marqué pour être traduis]</span>.',
	'translate-tag-hasnew' => 'Cette page contient <span class="plainlinks">[$1 des modifications]</span> qui ne sont pas marqués pour la traduction.',
	'tpt-translation-intro' => 'Cette page est une <span class="plainlinks">[$1 traduction]</span> de la page [[$2]] et la traduction est complétée à $3 % et à jour.',
	'tpt-translation-intro-fuzzy' => 'Les traductions obsolètes sont marquées comme ceci.',
	'tpt-languages-legend' => 'Autres langues :',
	'tpt-target-page' => 'Cette page ne peut pas être mise à jour manuellement.
Elle est une version traduite de [[$1]] et la traduction peut être mise à jour en utilisant [$2 l’outil de traduction].',
	'tpt-unknown-page' => 'Cet espace de noms est réservé pour la traduction de pages.
La page que vous essayé de modifier ne semble pas correspondre à aucune page marqué pour être traduite.',
	'tpt-install' => 'Lancez « php maintenance/update.php » ou l’installation web pour activer la fonctionnalité de traduction de pages.',
	'tpt-render-summary' => 'Mise à jour pour être en accord avec la nouvelle version de la source de la page',
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'pagetranslation' => 'Traduccion de pâges',
	'right-pagetranslation' => 'Marcar des vèrsions de pâges por étre traduites',
	'tpt-desc' => 'Èxtension por traduire des pâges de contegnu.',
	'tpt-section' => 'Unitât de traduccion $1',
	'tpt-section-new' => 'Unitât de traduccion novèla. Nom : $1',
	'tpt-section-deleted' => 'Unitât de traduccion $1',
	'tpt-template' => 'Modèlo de pâge',
	'tpt-templatediff' => 'Lo modèlo de pâge at changiê.',
	'tpt-diff-old' => 'Tèxto devant',
	'tpt-diff-new' => 'Novél tèxto',
	'tpt-submit' => 'Marcar ceta vèrsion por étre traduita',
	'tpt-sections-oldnew' => 'Unitâts de traduccion novèles et ègzistentes',
	'tpt-sections-deleted' => 'Unitâts de traduccion suprimâs',
	'tpt-sections-template' => 'Modèlo de pâge de traduccion',
	'tpt-badtitle' => 'Lo nom de pâge balyê ($1) est pas un titro valido',
	'tpt-oldrevision' => '$2 est pas la dèrriére vèrsion de la pâge [[$1]].
Solament la dèrriére vèrsion de la pâge pôt étre marcâ por étre traduita.',
	'tpt-notsuitable' => 'La pâge $1 est pas convegnâbla por étre traduita.
Seyâd de sûr que contint la balisa <nowiki><translate></nowiki> et qu’at una sintaxa justa.',
	'tpt-saveok' => 'La pâge « $1 » at étâ marcâ por étre traduita avouéc $2 {{PLURAL:$2|unitât de traduccion|unitâts de traduccion}}.
La pâge pôt étre <span class="plainlinks">[$3 traduita]</span> dês ora.',
	'tpt-badsect' => '« $1 » est pas un nom valido por una unitât de traduccion $2.',
	'tpt-showpage-intro' => 'Ce-desot, les novèles traduccions, celes ègzistentes et suprimâs.
Devant que marcar cetes vèrsions por étre traduites, controlâd que los changements a les sèccions sont petiôts por èvitar de travâly inutilo ux traductors.',
	'tpt-mark-summary' => 'Ceta vèrsion at étâ marcâ por étre traduita',
	'tpt-edit-failed' => 'Empossiblo de betar a jorn la pâge $1',
	'tpt-already-marked' => 'La dèrriére vèrsion de ceta pâge at ja étâ marcâ por étre traduita.',
	'tpt-list-nopages' => 'Niona pâge at étâ marcâ por étre traduita ou ben prèsta por l’étre.',
	'tpt-old-pages' => 'Des vèrsions de {{PLURAL:$1|ceta pâge|cetes pâges}} ont étâ marcâs por étre traduites.',
	'tpt-new-pages' => '{{PLURAL:$1|Ceta pâge contint|Cetes pâges contegnont}} de tèxto avouéc des balises de traduccion, mas niona vèrsion de {{PLURAL:$1|ceta pâge est marcâ por étre traduita|cetes pâges sont marcâs por étre traduites}}.',
	'tpt-rev-latest' => 'dèrriére vèrsion',
	'tpt-rev-old' => 'difèrence avouéc cela vèrsion marcâ',
	'tpt-rev-mark-new' => 'marcar ceta vèrsion por étre traduita',
	'tpt-translate-this' => 'traduire ceta pâge',
	'translate-tag-translate-link-desc' => 'Traduire ceta pâge',
	'translate-tag-markthis' => 'Marcar ceta pâge por étre traduita',
	'translate-tag-markthisagain' => 'Ceta pâge at avu des <span class="plainlinks">[$1 changements]</span> dês qu’at étâ <span class="plainlinks">[$2 marcâ por étre traduita]</span> dèrriérement.',
	'translate-tag-hasnew' => 'Ceta pâge contint des <span class="plainlinks">[$1 changements]</span> que sont pas marcâs por la traduccion.',
	'tpt-translation-intro' => 'Ceta pâge est una <span class="plainlinks">[$1 traduccion]</span> de la pâge [[$2]] et la traduccion est complètâ a $3 % et a jorn.',
	'tpt-translation-intro-fuzzy' => 'Les traduccions dèpassâs sont marcâs d’ense.',
	'tpt-languages-legend' => 'Ôtres lengoues :',
	'tpt-target-page' => 'Ceta pâge pôt pas étre betâ a jorn a la man.
El est una vèrsion traduita de [[$1]] et la traduccion pôt étre betâ a jorn en utilisent l’[$2 outil de traduccion].',
	'tpt-unknown-page' => 'Ceti èspâço de noms est resèrvâ por la traduccion de pâges de contegnu.
La pâge que vos tâchiéd de changiér semble pas corrèspondre a gins de pâge marcâ por étre traduita.',
	'tpt-install' => 'Lanciéd « php maintenance/update.php » ou ben l’enstalacion Vouèbe por activar la fonccionalitât de traduccion de pâges.',
	'tpt-render-summary' => 'Misa a jorn por étre en acôrd avouéc la novèla vèrsion de la pâge d’origina',
);

/** Galician (Galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'pagetranslation' => 'Tradución da páxina',
	'right-pagetranslation' => 'Marcar as versións de páxinas para seren traducidas',
	'tpt-desc' => 'Extensión para traducir contidos de páxinas',
	'tpt-section' => 'Unidade de tradución $1',
	'tpt-section-new' => 'Nova unidade de tradución. Nome: $1',
	'tpt-section-deleted' => 'Unidade de tradución $1',
	'tpt-template' => 'Modelo de páxina',
	'tpt-templatediff' => 'Cambiou o modelo de páxina.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto novo',
	'tpt-submit' => 'Marcar esta versión para ser traducida',
	'tpt-sections-oldnew' => 'Unidades de tradución novas e existentes',
	'tpt-sections-deleted' => 'Unidades de tradución borradas',
	'tpt-sections-template' => 'Modelo de páxina de tradución',
	'tpt-badtitle' => 'O nome de páxina dado ("$1") non é un título válido',
	'tpt-oldrevision' => '$2 non é a última versión da páxina "[[$1]]".
Só as últimas versións poden ser marcadas para seren traducidas.',
	'tpt-notsuitable' => 'A páxina "$1" non é válida para ser traducida.
Comprobe que teña as etiquetas <nowiki><translate></nowiki> e mais unha sintaxe válida.',
	'tpt-saveok' => 'A páxina "[[$1]]" foi marcada para ser traducida, {{PLURAL:$2|cunha unidade de tradución|con $2 unidades de tradución}}.
A páxina agora pode ser <span class="plainlinks">[$3 traducida]</span>.',
	'tpt-badsect' => '"$1" non é un nome válido para a unidade de tradución $2.',
	'tpt-showpage-intro' => 'A continuación están listadas as seccións existentes e borradas.
Antes de marcar esta versión para ser traducida, comprobe que as modificacións feitas ás seccións foron minimizadas para evitarlles traballo innecesario aos tradutores.',
	'tpt-mark-summary' => 'Marcou esta versión para ser traducida',
	'tpt-edit-failed' => 'Non se puido actualizar a páxina: $1',
	'tpt-already-marked' => 'A última versión desta páxina xa foi marcada para ser traducida.',
	'tpt-list-nopages' => 'Non hai ningunha páxina marcada para ser traducida, nin preparada para ser marcada para ser traducida.',
	'tpt-old-pages' => 'Algunha versión {{PLURAL:$1|desta páxina|destas páxinas}} ten sido marcada para ser traducida.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta páxina contén|Estas páxinas conteñen}} texto con etiquetas de tradución, pero ningunha versión {{PLURAL:$1|desta páxina|destas páxinas}} está actualmente marcada para ser traducida.',
	'tpt-rev-latest' => 'última versión',
	'tpt-rev-old' => 'diferenza coa versión previa marcada',
	'tpt-rev-mark-new' => 'marcar esta versión para ser traducida',
	'tpt-translate-this' => 'traducir esta páxina',
	'translate-tag-translate-link-desc' => 'Traducir esta páxina',
	'translate-tag-markthis' => 'Marcar esta páxina para ser traducida',
	'translate-tag-markthisagain' => 'Esta páxina sufriu <span class="plainlinks">[$1 cambios]</span> desde que foi <span class="plainlinks">[$2 marcada para a súa tradución]</span> por última vez.',
	'translate-tag-hasnew' => 'Esta páxina contén <span class="plainlinks">[$1 cambios]</span> que non están marcados para a súa tradución.',
	'tpt-translation-intro' => 'Esta páxina é unha <span class="plainlinks">[$1 versión traducida]</span> da páxina "[[$2]]" e a tradución está completada e actualizada ao $3%.',
	'tpt-translation-intro-fuzzy' => 'As traducións desfasadas están marcadas coma este texto.',
	'tpt-languages-legend' => 'Outras linguas:',
	'tpt-target-page' => 'Esta páxina non pode ser actualizada manualmente.
Esta páxina é unha tradución da páxina "[[$1]]" e a tradución pode ser actualizada usando [$2 a ferramenta de tradución].',
	'tpt-unknown-page' => 'Este espazo de nomes está reservado para traducións de páxinas de contido.
A páxina que está intentando editar parece non corresponder a algunha páxina marcada para ser traducida.',
	'tpt-install' => 'Executar o php maintenance/update.php ou o instalador web para activar a funcionalidade de tradución de páxinas.',
	'tpt-render-summary' => 'Actualizando para coincidir coa nova versión da páxina de orixe',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'pagetranslation' => 'Sytenibersetzig',
	'right-pagetranslation' => 'D Syte, wu sotte ibersetzt wäre, markiere',
	'tpt-desc' => 'Erwyterig fir d Iberstzig vu Inhaltssyte',
	'tpt-section' => 'Iberstzigs-Abschnitt $1',
	'tpt-section-new' => 'Neje Iberstzigs-Abschnitt. Name: $1',
	'tpt-section-deleted' => 'Ibersetzigs-Abschnitt $1',
	'tpt-template' => 'Sytevorlag',
	'tpt-templatediff' => 'D Sytevorlag het sich gänderet.',
	'tpt-diff-old' => 'Vorige Tekscht',
	'tpt-diff-new' => 'Neje Tekscht',
	'tpt-submit' => 'Die Version zum Ibersetze markiere',
	'tpt-sections-oldnew' => 'Neji un vorhandeni Ibersetzigs-Abschnitt',
	'tpt-sections-deleted' => 'Gleschti Ibersetzigs-Abschnitt',
	'tpt-sections-template' => 'Ibersetzigs-Sytevorlag',
	'tpt-badtitle' => 'Dr Sytename, wu Du aagee hesch ($1), isch kei giltige Sytename',
	'tpt-oldrevision' => '$2 isch nit di letscht Version vu dr Syte [[$1]].
Nume di letschte Versione chenne zum Iberseze markiert wäre.',
	'tpt-notsuitable' => 'D Syte $1 cha nit iberstez wäre.
Stell sicher, ass si <nowiki><translate></nowiki>-Markierige un e giltige Syntax het.',
	'tpt-saveok' => 'D Syte [[$1]] isch zum Ibersetze markiert wore mit $2 {{PLURAL:$2|Ibersetzigs-Abschnitt|Ibersetzigs-Abschnitt}}.
D Syte cha jetz <span class="plainlinks">[$3 ibersetzt]</span> wäre.',
	'tpt-badsect' => '"$1" isch kei giltige Name fir dr Iberstzigs-Abschnitt $2.',
	'tpt-showpage-intro' => 'Unte sin Abschnitt ufglischtet, wu nej sin, sonigi wu s git un sonigi wu s nit git.
Voreb Du die Versione zum Ibersetze markiersch, iberprief, ass d Änderige an dr Abschnitt gring ghalte sin go uunetigi Arbed bi dr Ibersetzig vermyde.',
	'tpt-mark-summary' => 'het die Versione zum Ibersetze markiert',
	'tpt-edit-failed' => 'Cha d Syte nit aktualisiere: $1',
	'tpt-already-marked' => 'Di letscht Version vu däre Syte isch scho zum Ibersetze markiert wore.',
	'tpt-list-nopages' => 'S sin kei Syte zum Ibersetze markiert wore un sin au no keini Syte fertig, wu chennte zum Ibersetze markiert wäre',
	'tpt-old-pages' => '{{PLURAL:$1|E Version vu däre Syte isch|E paar Versione vu däne Syte sin}} zum Ibersetze markiert wore',
	'tpt-new-pages' => '{{PLURAL:$1|In däre Syte|In däne Syte}} het s Tekscht mit Ibersetzigs-Markierige, aber zur Zyt isch kei Version {{PLURAL:$1|däre Syte|däne Syte}} zum Ibersetze markiert.',
	'tpt-rev-latest' => 'letschti Version',
	'tpt-rev-old' => 'Unterschid zue dr letschte markierte Version',
	'tpt-rev-mark-new' => 'die Version zum Ibersetze markiere',
	'tpt-translate-this' => 'die Syte ibersetze',
	'translate-tag-translate-link-desc' => 'Die Syte ibersetze',
	'translate-tag-markthis' => 'Die Syte zum ibersetze markiere',
	'translate-tag-markthisagain' => 'An däre Syte het s <span class="plainlinks">[$1 Änderige]</span> gee, syt si s lescht Mol <span class="plainlinks">[$2 zum Ibersetze markiert wore isch]</span>.',
	'translate-tag-hasnew' => 'In däre Syte het s <span class="plainlinks">[$1 Änderige]</span>, wu nit zum Ibersetze markiert sin.',
	'tpt-translation-intro' => 'Die Syte isch e <span class="plainlinks">[$1 ibersetzti Version]</span> vun ere Syte [[$2]] un d Ibersetzig isch zue $3% vollständig un aktuäll.',
	'tpt-translation-intro-fuzzy' => 'Nit aktuälli Ibersetzige wäre wie dää Tekscht markiert.',
	'tpt-languages-legend' => 'Anderi Sproche:',
	'tpt-target-page' => 'Die Syte cha nit vu Hand aktualisiert wäre.
Die Syte isch e Ibersetzig vu dr Syte [[$1]] un d Ibersetzig cha aktualisert wäre mit em [$2 Ibersetzigstool].',
	'tpt-unknown-page' => 'Dää Namensruum isch reserviert fir Ibersetzige vu Inhaltssyte.
D Syte, wu Du witt bearbeite, ghert schyns zue keire Syte, wu zum Ibersetze markiert isch.',
	'tpt-install' => 'php maintenance/update.php oder d Webinstallation laufe loo go s Syte-Ibersetzigs-Feature megli mache.',
	'tpt-render-summary' => 'Aktualisiere zum e neji Version vu dr Quällsyte z finde',
);

/** Hebrew (עברית)
 * @author Rotemliss
 */
$messages['he'] = array(
	'translate-tag-translate-link-desc' => 'תרגום דף זה',
);

/** Croatian (Hrvatski)
 * @author Suradnik13
 */
$messages['hr'] = array(
	'translate-tag-translate-link-desc' => 'Prevedi ovu stranicu',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'pagetranslation' => 'Přełožowanje strony',
	'right-pagetranslation' => 'Wersije strony za přełožowanje markěrować',
	'tpt-desc' => 'Rozšěrjenje za přełožowanje wobsahowych stronow',
	'tpt-section' => 'Přełožowanska jednotka $1',
	'tpt-section-new' => 'Nowa přełožowanska jednotka. Mjeno: $1',
	'tpt-section-deleted' => 'Přełožowanska jednotka $1',
	'tpt-template' => 'Předłoha strony',
	'tpt-templatediff' => 'Předłoha strony je so změniła.',
	'tpt-diff-old' => 'Předchadny tekst',
	'tpt-diff-new' => 'Nowy tekst',
	'tpt-submit' => 'Tutu wersiju za přełožowanje markěrować',
	'tpt-sections-oldnew' => 'Nowe a eksistowace přełožowanske jednotki',
	'tpt-sections-deleted' => 'Wušmórnjene přełožowanske jednotki',
	'tpt-sections-template' => 'Předłoha přełožowanskeje strony',
	'tpt-badtitle' => 'Podate mjeno strony ($1) płaćiwy titul njeje',
	'tpt-oldrevision' => '$2 aktualna wersija strony [[$1]] njeje.
Jenož aktualne wersije hodźa so za přełožowanje markěrować.',
	'tpt-notsuitable' => 'Strona $1 za přełožowanje přihódna njeje.
Zaswěsć, zo ma taflički <nowiki><translate></nowiki> a płaćiwu syntaksu.',
	'tpt-saveok' => 'Strona [[$1]] je so za přełožowanje z $2 {{PLURAL:$2|přełožujomnej jednotku|přełožujomnej jednotkomaj|přełožujomnymi jednotkami|přełožujomnymi jednotkami}} markěrowała.
Strona hodźi so nětko <span class="plainlinks">[$3 přełožować]</span>.',
	'tpt-badsect' => '"$1" płaćiwe mjeno za přełožowansku jednotku $2 njeje.',
	'tpt-showpage-intro' => 'Deleka su nowe, eksistowace a wušmórnjene wotrězki nalistowane.
Prjedy hač tutu wersiju za přełožowanje markěruješ, skontroluj, hač změny wotrězkow su miniměrowane, zo by njetrěbne dźěło za přełožowarjow wobešoł.',
	'tpt-mark-summary' => 'Je tutu wersiju za přełožowanje markěrował',
	'tpt-edit-failed' => 'Strona njeda so aktualizować: $1',
	'tpt-already-marked' => 'Akutalna wersija tuteje strony je so hižo za přełožowanje markěrowała.',
	'tpt-list-nopages' => 'Strony njejsu ani za přełožowanje markěrowali ani njejsu hotowe za přełožowanje.',
	'tpt-old-pages' => 'Někajka wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} je so za přełožowanje markěrowała.',
	'tpt-new-pages' => '{{PLURAL:$1|Tuta strona wobsahuje|Tutej stronje|Tute strony wobsahuja|Tute strony wobsahuja}} tekst z přełožowanskimi tafličkimi, ale žana wersija {{PLURAL:$1|tuteje strony|tuteju stronow|tutych stronow|tutych stronow}} njeje tuchwilu za přełožowanje markěrowana.',
	'tpt-rev-latest' => 'aktualna wersija',
	'tpt-rev-old' => 'rozdźěl k předchadnej markěrowanej wersiji',
	'tpt-rev-mark-new' => 'tutu wersiju za přełožowanje markěrować',
	'tpt-translate-this' => 'tutu stronu přełožić',
	'translate-tag-translate-link-desc' => 'Tutu stronu přełožić',
	'translate-tag-markthis' => 'Tutu stronu za přełožowanje markěrować',
	'translate-tag-markthisagain' => 'Tuta strona ma <span class="plainlinks">[$1 {{PLURAL:$1|změnu|změnje|změny|změnow}}]</span>, wot toho zo, bu posledni raz <span class="plainlinks">[$2 za přełožowanje markěrowana]</span>.',
	'translate-tag-hasnew' => 'Tuta strona wobsahuje <span class="plainlinks">[$1 {{PLURAL:$1|změna, kotraž njeje markěrowana|změnje, kotrejž njejstej markěrowanej|změny, kotrež njejsu markěrowane|změnow, kotrež njejsu markěrowane}}]</span> za přełožowanje.',
	'tpt-translation-intro' => 'Tuta strona je <span class="plainlinks">[$1 přełožena wersija]</span> strony [[$2]] a $3 % přełožka je dokónčene a přełožk je aktualny.',
	'tpt-translation-intro-fuzzy' => 'Zestarjene přełožki su kaž tutón markěrowane.',
	'tpt-languages-legend' => 'Druhe rěče:',
	'tpt-target-page' => 'Tuta strona njeda so manulenje aktualizować.
Tuta strona je přełožk strony [[$1]] a přełožk hodźi so z pomocu [$2 Přełožić] aktualizować.',
	'tpt-unknown-page' => 'Tutón mjenowy rum je za přełožki wobsahowych stronow wuměnjeny.
Strona, kotruž pospytuješ wobdźěłać, po wšěm zdaću stronje markěrowanej za přełožowanje njewotpowěduje.',
	'tpt-install' => 'Wuwjedź php maintenance/update.php ab webinstalaciju, zo by funkcija přełožowanje stronow zmóžnił.',
	'tpt-render-summary' => 'Aktualizacija po nowej wersiji žórłoweje strony',
);

/** Hungarian (Magyar)
 * @author Dani
 */
$messages['hu'] = array(
	'translate-tag-translate-link-desc' => 'A lap fordítása',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'pagetranslation' => 'Traduction de paginas',
	'right-pagetranslation' => 'Marcar versiones de paginas pro traduction',
	'tpt-desc' => 'Extension pro traducer paginas de contento',
	'tpt-section' => 'Unitate de traduction $1',
	'tpt-section-new' => 'Nove unitate de traduction. Nomine: $1',
	'tpt-section-deleted' => 'Unitate de traduction $1',
	'tpt-template' => 'Patrono de pagina',
	'tpt-templatediff' => 'Le patrono del pagina ha cambiate.',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Texto nove',
	'tpt-submit' => 'Marcar iste version pro traduction',
	'tpt-sections-oldnew' => 'Unitates de traduction nove e existente',
	'tpt-sections-deleted' => 'Unitates de traduction delite',
	'tpt-sections-template' => 'Patrono de pagina de traduction',
	'tpt-badtitle' => 'Le nomine de pagina specificate ($1) non es un titulo valide',
	'tpt-oldrevision' => '$2 non es le version le plus recente del pagina [[$1]].
Solmente le versiones le plus recente pote esser marcate pro traduction.',
	'tpt-notsuitable' => 'Le pagina $1 non es traducibile.
Assecura que illo contine etiquettas <nowiki><translate></nowiki> e ha un syntaxe valide.',
	'tpt-saveok' => 'Le pagina [[$1]] ha essite marcate pro traduction con $2 {{PLURAL:$2|unitate|unitates}} de traduction.
Le pagina pote ora esser <span class="plainlinks">[$3 traducite]</span>.',
	'tpt-badsect' => '"$1" non es un nomine valide pro le unitate de traduction $2.',
	'tpt-showpage-intro' => 'In basso es listate sectiones nove, existente e delite.
Ante de marcar iste version pro traduction, assecura que le modificationes al sectiones sia minimisate pro evitar labor innecessari pro traductores.',
	'tpt-mark-summary' => 'Marcava iste version pro traduction',
	'tpt-edit-failed' => 'Non poteva actualisar le pagina: $1',
	'tpt-already-marked' => 'Le version le plus recente de iste pagina ha jam essite marcate pro traduction.',
	'tpt-list-nopages' => 'Il non ha paginas marcate pro traduction, ni paginas preparate pro isto.',
	'tpt-old-pages' => 'Alcun {{PLURAL:$1|version de iste pagina|versiones de iste paginas}} ha essite marcate pro traduction.',
	'tpt-new-pages' => 'Iste {{PLURAL:$1|pagina|paginas}} contine texto con etiquettas de traduction, ma nulle version de iste {{PLURAL:$1|pagina|paginas}} es actualmente marcate pro traduction.',
	'tpt-rev-latest' => 'ultime version',
	'tpt-rev-old' => 'differentia con previe version marcate',
	'tpt-rev-mark-new' => 'marcar iste version pro traduction',
	'tpt-translate-this' => 'traducer iste pagina',
	'translate-tag-translate-link-desc' => 'Traducer iste pagina',
	'translate-tag-markthis' => 'Marcar iste pagina pro traduction',
	'translate-tag-markthisagain' => 'Iste pagina ha <span class="plainlinks">[$1 modificationes]</span> depost le ultime vice que illo esseva <span class="plainlinks">[$2 marcate pro traduction]</span>.',
	'translate-tag-hasnew' => 'Iste pagina contine <span class="plainlinks">[$1 modificationes]</span> le quales non ha essite marcate pro traduction.',
	'tpt-translation-intro' => 'Iste pagina es un <span class="plainlinks">[$1 version traducite]</span> de un pagina [[$2]] e le traduction es complete e actual a $3%.',
	'tpt-translation-intro-fuzzy' => 'Le traductiones obsolete es marcate assi.',
	'tpt-languages-legend' => 'Altere linguas:',
	'tpt-target-page' => 'Iste pagina non pote esser actualisate manualmente.
Iste pagina es un traduction del pagina [[$1]] e le traduction pote esser actualisate con le [$2 instrumento de traduction].',
	'tpt-unknown-page' => 'Iste spatio de nomines es reservate pro traductiones de paginas de contento.
Le pagina que tu vole modificar non pare corresponder con alcun pagina marcate pro traduction.',
	'tpt-install' => 'Executa maintenance/update.php o le installation web pro activar le traduction de paginas.',
	'tpt-render-summary' => 'Actualisation a un nove version del pagina de origine',
);

/** Indonesian (Bahasa Indonesia)
 * @author Rex
 */
$messages['id'] = array(
	'pagetranslation' => 'Penerjemahan halaman',
	'right-pagetranslation' => 'Menandai revisi-revisi halaman untuk diterjemahkan',
	'tpt-desc' => 'Ekstensi untuk menerjemahkan halaman-halaman isi',
	'tpt-section' => 'Unit penerjemahan $1',
	'tpt-section-new' => 'Unit penerjemahan baru. Nama: $1',
	'tpt-section-deleted' => 'Unit penerjemahan $1',
	'tpt-template' => 'Templat halaman',
	'tpt-templatediff' => 'Templat halaman telah diubah.',
	'tpt-diff-old' => 'Teks sebelumnya',
	'tpt-diff-new' => 'Teks baru',
	'tpt-submit' => 'Tandai revisi ini untuk diterjemahkan',
	'tpt-sections-oldnew' => 'Unit-unit penerjemahan baru dan yang telah ada',
	'tpt-sections-deleted' => 'Unit penerjemahan yang dihapus',
	'tpt-sections-template' => 'Templat halaman penerjemahan',
	'tpt-badtitle' => 'Nama halaman yang diberikan ($1) tidak valid',
	'tpt-oldrevision' => '$2 bukan revisi terakhir dari halaman [[$1]].
Hanya revisi terakhir yang dapat ditandai untuk diterjemahkan.',
	'tpt-notsuitable' => 'Halaman $1 tidak dapat diterjemahkan.
Pastikan bahwa halaman ini memiliki tag <nowiki><translate></nowiki> dan memiliki sintaksis yang valid.',
	'tpt-saveok' => 'Halaman [[$1]] telah ditandai untuk diterjemahkan dengan $2 {{PLURAL:$2|unit penerjemahan|unit penerjemahan}}.
Halaman ini sekarang dapat <span class="plainlinks"[$3 diterjemahkan]</span>.',
	'tpt-badsect' => '"$1" bukanlah nama yang valid untuk unit penerjemahan $2.',
	'tpt-showpage-intro' => 'Berikut adalah daftar bagian baru, bagian yang telah ada, dan bagian yang dihapus.
Sebelum menandai revisi ini untuk diterjemahkan, harap periksa agar perubahan ke bagian-bagian dapat diminimalisasi guna menghindarkan para penerjemah dari melakukan pekerjaan yang tidak diperlukan.',
	'tpt-mark-summary' => 'Menandai revisi ini untuk diterjemahkan',
	'tpt-edit-failed' => 'Tidak dapat memperbarui halaman: $1',
	'tpt-already-marked' => 'Revisi terakhir halaman ini telah ditandai untuk diterjemahkan.',
	'tpt-list-nopages' => 'Tidak ada halaman yang ditandai untuk diterjemahkan atau siap ditandai untuk diterjemahkan.',
	'tpt-old-pages' => 'Beberapa revisi dari {{PLURAL:$1|halaman ini|halaman-halaman ini}} telah ditandai untuk diterjemahkan.',
	'tpt-new-pages' => '{{PLURAL:$1|Halaman ini berisikan|Halaman-halaman ini berisikan}} teks dengan tag terjemahan, tetapi tidak ada versi {{PLURAL:$1|halaman ini|halaman-halaman ini}} yang sudah ditandai untuk diterjemahkan.',
	'tpt-rev-latest' => 'revisi terakhir',
	'tpt-rev-old' => 'beda dengan revisi terakhir yang ditandai',
	'tpt-rev-mark-new' => 'tandai revisi ini untuk diterjemahkan',
	'tpt-translate-this' => 'terjemahkan halaman ini',
	'translate-tag-translate-link-desc' => 'Terjemahkan halaman ini',
	'translate-tag-markthis' => 'Tandai halaman ini untuk diterjemahkan',
	'translate-tag-markthisagain' => 'Halaman ini telah diubah <span class="plainlinks">[$1 kali]</span> sejak terakhir <span class="plainlinks">[$2 ditandai untuk diterjemahkan]</span>.',
	'translate-tag-hasnew' => 'Halaman ini berisikan <span class="plainlinks">[$1 revisi]</span> yang tidak ditandai untuk diterjemahkan.',
	'tpt-translation-intro' => 'Halaman ini adalah sebuah <span class="plainlinks">[$1 versi terjemahan]</span> dari halaman [[$2]] dan terjemahannya telah selesai $3% dari sumber terkini.',
	'tpt-translation-intro-fuzzy' => 'Terjemahan usang ditandai seperti ini.',
	'tpt-languages-legend' => 'Bahasa lain:',
	'tpt-target-page' => 'Halaman ini tidak dapat diperbarui secara manual.
Halaman ini adalah terjemahan dari halaman [[$1]] dan terjemahannya dapat diperbarui menggunakan [$2 peralatan penerjemahan].',
	'tpt-unknown-page' => 'Ruang nama ini dicadangkan untuk terjemahan halaman isi.
Halaman yang ingin Anda sunting ini tampaknya tidak memiliki hubungan dengan halaman manapun yang ditandai untuk diterjemahkan.',
	'tpt-install' => 'Jalankan php maintenance/update.php atau instalasi web untuk mengaktifkan fitur terjemahan halaman.',
	'tpt-render-summary' => 'Memperbarui ke revisi terbaru halaman sumber',
);

/** Italian (Italiano)
 * @author Darth Kule
 */
$messages['it'] = array(
	'pagetranslation' => 'Traduzione pagine',
	'right-pagetranslation' => 'Segna versione di pagine per la traduzione',
	'tpt-desc' => 'Estensione per la traduzione di pagine di contenuti',
	'tpt-diff-old' => 'Testo precedente',
	'tpt-diff-new' => 'Testo successivo',
	'tpt-submit' => 'Segna questa versione per la traduzione',
	'tpt-badtitle' => 'Il nome fornito per la pagina ($1) non è un titolo valido',
	'tpt-oldrevision' => "$2 non è l'ultima versione della pagina [[$1]].
Solo le ultime versioni possono essere segnate per la traduzione.",
	'tpt-notsuitable' => 'La pagina $1 non è adatta per la traduzione.
Assicurarsi che abbia i tag <nowiki><translate></nowiki> e una sintassi valida.',
	'tpt-showpage-intro' => 'Di seguito sono elencate le sezioni nuove, esistenti e cancellate.
Prima di segnare questa versione per la traduzione, controllare che i cambiamenti per le sezioni siano ridotti al minimo per evitare lavoro non necessario ai traduttori.',
	'tpt-edit-failed' => 'Impossibile aggiornare la pagina: $1',
	'tpt-already-marked' => "L'ultima versione di questa pagina è già stata segnata per la traduzione.",
	'tpt-list-nopages' => 'Nessuna pagina è segnata per la traduzione oppure è pronta per essere segnata per la traduzione.',
	'tpt-old-pages' => 'Alcune versioni di {{PLURAL:$1|questa pagina|queste pagine}} sono state segnate per la traduzione.',
	'translate-tag-translate-link-desc' => 'Traduci questa pagina',
);

/** Japanese (日本語)
 * @author Aotake
 * @author Fryed-peach
 */
$messages['ja'] = array(
	'pagetranslation' => 'ページ翻訳',
	'right-pagetranslation' => 'ページの版を翻訳対象に指定する',
	'tpt-desc' => 'コンテンツページの翻訳のための拡張機能',
	'tpt-section' => '翻訳単位 $1',
	'tpt-section-new' => '新規翻訳単位。名前: $1',
	'tpt-section-deleted' => '翻訳単位 $1',
	'tpt-template' => 'ページの雛型',
	'tpt-templatediff' => 'このページの雛型が変更されました。',
	'tpt-diff-old' => '前のテキスト',
	'tpt-diff-new' => '新しいテキスト',
	'tpt-submit' => 'この版を翻訳対象に指定する',
	'tpt-sections-oldnew' => '新規および既存の翻訳単位',
	'tpt-sections-deleted' => '削除された翻訳単位',
	'tpt-sections-template' => '翻訳ページの雛型',
	'tpt-badtitle' => '指定したページ名 ($1) は無効なタイトルです',
	'tpt-oldrevision' => '$2 はページ [[$1]] の最新版ではありません。翻訳対象に指定できるのは最新版のみです。',
	'tpt-notsuitable' => 'ページ $1 は翻訳に対応していません。<nowiki><translate></nowiki>が含まれていること、またマークアップが正しいことを確認してください。',
	'tpt-saveok' => 'ページ [[$1]] は翻訳対象に指定されており、$2{{PLURAL:$2|個}}の翻訳単位を含んでいます。このページを<span class="plainlinks">[$3 翻訳]</span>することができます。',
	'tpt-badsect' => '「$1」は翻訳単位 $2 の名前として無効です。',
	'tpt-showpage-intro' => '以下には新しいセクション、既存のセクション、そして削除されたセクションが一覧されています。この版を翻訳対象に指定する前に、セクションの変更を最小限にすることで不要な翻訳作業を回避できないか確認してください。',
	'tpt-mark-summary' => 'この版を翻訳対象に指定しました',
	'tpt-edit-failed' => 'ページを更新できませんでした: $1',
	'tpt-already-marked' => 'このページの最新版がすでに翻訳対象に指定されています。',
	'tpt-list-nopages' => '翻訳対象に指定されたページがない、または翻訳対象に指定する準備ができているページがありません。',
	'tpt-old-pages' => '{{PLURAL:$1|これらの|この}}ページには翻訳対象に指定された版があります。',
	'tpt-new-pages' => '{{PLURAL:$1|以下のページ}}は本文に翻訳タグを含んでいますが、翻訳対象に指定されている版が{{PLURAL:$1|ありません}}。',
	'tpt-rev-latest' => '最新版',
	'tpt-rev-old' => '以前に翻訳指定された版との差分',
	'tpt-rev-mark-new' => 'この版を翻訳対象に指定する',
	'tpt-translate-this' => 'このページを翻訳する',
	'translate-tag-translate-link-desc' => 'このページを翻訳する',
	'translate-tag-markthis' => 'このページを翻訳対象に指定する',
	'translate-tag-markthisagain' => 'このページには最後に<span class="plainlinks">[$2 翻訳が指定]</span>されて以降の<span class="plainlinks">[$1 変更]</span>があります。',
	'translate-tag-hasnew' => 'このページには翻訳対象に指定されていない<span class="plainlinks">[$1 変更]</span>があります。',
	'tpt-translation-intro' => 'このページはページ [[$2]] の<span class="plainlinks">[$1 翻訳版]</span> です。翻訳は $3% 完了しており、最新の状態を反映しています。',
	'tpt-translation-intro-fuzzy' => '古くなった翻訳はこのような印が付いています。',
	'tpt-languages-legend' => '他言語での翻訳:',
	'tpt-target-page' => 'このページは手動で更新できません。このページはページ [[$1]] の翻訳で、[$2 翻訳ツール]を使用して更新します。',
	'tpt-unknown-page' => 'この名前空間はコンテンツページの翻訳のために使用します。あなたが編集しようとしているページに対応する翻訳対象ページが存在しないようです。',
	'tpt-install' => 'ページ翻訳機能を有効にするために、php maintenance/update.php またはウェブ・インストーラーを実行する。',
	'tpt-render-summary' => '翻訳元ページの新版に適合するように更新中',
);

/** Javanese (Basa Jawa)
 * @author Pras
 */
$messages['jv'] = array(
	'translate-tag-translate-link-desc' => 'Terjemahaké kaca iki',
);

/** Khmer (ភាសាខ្មែរ)
 * @author គីមស៊្រុន
 * @author វ័ណថារិទ្ធ
 */
$messages['km'] = array(
	'pagetranslation' => 'ការ​បក​ប្រែ​ទំព័រ​',
	'tpt-rev-latest' => 'កំណែ (version) ចុង​ក្រោយ​គេ​',
	'translate-tag-translate-link-desc' => 'បកប្រែទំព័រនេះ',
);

/** Korean (한국어)
 * @author Kwj2772
 */
$messages['ko'] = array(
	'translate-tag-translate-link-desc' => '이 문서 번역하기',
	'tpt-languages-legend' => '다른 언어:',
);

/** Ripoarisch (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'pagetranslation' => 'Sigge Övversäze',
	'right-pagetranslation' => 'Donn Versione vun Sigge för et Övversäze makeere',
	'tpt-desc' => 'Projrammzohsatz för Sigge vum Enhalt vum Wiki ze övversäze.',
	'tpt-section' => 'Knubbel $1 för ze Övversäze',
	'tpt-section-new' => 'Ene neue Knubbel för ze Övversäze: $1',
	'tpt-section-deleted' => 'Knubbel $1 för ze Övversäze',
	'tpt-template' => 'Siggeschabloon',
	'tpt-templatediff' => 'De Siggeschabloon hät sesch jeändert.',
	'tpt-diff-old' => 'Dä vörrijje Täx',
	'tpt-diff-new' => 'Dä neue Täx',
	'tpt-submit' => 'Donn hee di Version för et Övversäze makeere',
	'tpt-sections-oldnew' => 'De Knubbelle för ze Övversäze (Jez neu, un de älldere, zosamme)',
	'tpt-sections-deleted' => 'Fottjeschmeße Knubbelle för et Övversäze',
	'tpt-sections-template' => 'Övversäzungßsiggschabloon',
	'tpt-badtitle' => 'Dä Name „$1“ es keine jöltijje Tittel för en Sigg',
	'tpt-oldrevision' => '„$2“ es nit de neuste Version fun dä Sigg „[[$1]]“, ävver bloß de neuste kam_mer för et Övversäze makeere.',
	'tpt-notsuitable' => 'Di Sigg „$1“ paß nit för et Övversäze. Maach <code><nowiki><translate></nowiki></code>-Makeerunge erin, un looer dat de Süntax shtemmp.',
	'tpt-saveok' => 'De Sigg „$1“ es för ze Övversäze makeet. Doh dren {{PLURAL:$2|es eine Knubbel|sinn_er $2 Knubbelle|es ävver keine Knubbel}} för ze Övversäze. Di Sigg kam_mer <span class="plainlinks">[$3 jäz övversäze]</span>.',
	'tpt-badsect' => '„$1“ es kein jöltejje Name för dä Knubbel zom Övversäze $2.',
	'tpt-showpage-intro' => 'Hee dronger sin Afschnedde opjeleß, di eruß jenumme woode, un di noch doh sin. Ih dat De hee di Version för ze Övversäze makeere deihß, loor drop, dat esu winnisch wi müjjelesch Änderonge aan Afschnedde doh sin, öm dä Övversäzere et Levve leisch ze maache.',
	'tpt-mark-summary' => 'Han di Version för ze Övversäze makeet',
	'tpt-edit-failed' => 'Kunnt de Sigg „$1“ nit ändere',
	'tpt-already-marked' => 'De neuste Version vun dä Sigg es ald för zem Övversäze makeet.',
	'tpt-list-nopages' => 'Et sinn_er kein Sigge för zem Övversäze makeet, un et sin och kein doh, wo esu en Makeerunge eren künnte.',
	'tpt-old-pages' => 'En Version vun hee dä {{PLURAL:$1|Sigg|Sigge|-}} es för zem Övversäze makeet.',
	'tpt-new-pages' => '{{PLURAL:$1|Di Sigg hät|Di Sigge han|Kein Sigg hät}} ene <code lang="en">translation</code>-Befähl en sesch, ävve kei Version dofun es för ze Övversäze makeet.',
	'tpt-rev-latest' => 'Neuste Version',
	'tpt-rev-old' => 'Ongerscheid zor vörijje makeete Version',
	'tpt-rev-mark-new' => 'donn di Version för et Övversäze makeere',
	'tpt-translate-this' => 'donn di Sigg övversäze',
	'translate-tag-translate-link-desc' => 'Don di Sigg hee övversäze',
	'translate-tag-markthis' => 'Donn hee di Sigg för et Övversäze makeere',
	'translate-tag-markthisagain' => 'Hee di Sigg es <span class="plainlinks">[$1 jeändert woode]</span> zick se et läz <span class="plainlinks">[$2 för ze Övversäze]</span> makeet woode es.',
	'translate-tag-hasnew' => 'Hee di Sigg <span class="plainlinks">[$1 es jeändert woode]</span>, es ävver nit för ze Övversäze makeet woode.',
	'tpt-translation-intro' => 'Hee di Sigg es en <span class="plainlinks">[$1 övversaz Version]</span> vun dä Sigg „[[$2]]“ un es zoh $3% jedonn un om aktoälle Shtandt.',
	'tpt-translation-intro-fuzzy' => 'Övverhollte Övversäzunge wäde su makeet, wi hee dä Täx.',
	'tpt-languages-legend' => 'Ander Shprooche:',
	'tpt-target-page' => 'Hee di Sigg kam_mer nit vun Hand ändere. Dat hee es en Översäzungß_Sigg vun dä Sigg [[$1]]. De Övversäzung kam_mer övver däm Wiki sing [$2 Övversäzungß_Wärkzüsch] op der neußte Shtand bränge.',
	'tpt-unknown-page' => 'Dat Appachtemang hee es för Sigge vum Enhallt vum Wiki ze Övversäze jedaach. Di Sigg, di de jraad ze ändere versöhks, schingk ävver nit met ööhnds en Sigg ze donn ze han, di för zem Övversäze makeet es.',
	'tpt-install' => 'Lohß op Dingem Wiki singem ßööver dat Skrip <code>php maintenance/update.php</code> loufe, udder schmiiß dat Enreeschdungsprojramm övver et Web aan, öm de Müjjeleschkeit för Sigge ze övversäze en däm Wiki aan et Loufe ze bränge.',
	'tpt-render-summary' => 'Ändere, öm op de neue Version fun de Ojinaal_Sigg ze kumme',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'pagetranslation' => 'Iwwersetzung vun der Säit',
	'right-pagetranslation' => 'Versioune vu Säite fir Iwwersetzung markéieren',
	'tpt-desc' => "Erweiderung fir ihaltlech Säiten z'iwwersetzen",
	'tpt-section' => 'Iwwersetzungseenheet $1',
	'tpt-section-new' => 'Numm: $1',
	'tpt-section-deleted' => 'Iwwersetzungseenheet $1',
	'tpt-template' => 'Säiteschabloun',
	'tpt-templatediff' => "D'Säiteschabloun gouf geännert.",
	'tpt-diff-old' => 'Viregen Text',
	'tpt-diff-new' => 'Neien Text',
	'tpt-submit' => "Dës Versioun fir d'Iwwersetze markéieren",
	'tpt-sections-oldnew' => 'Nei an Iwwersetzungseeenheeten déi et scho gëtt',
	'tpt-sections-deleted' => 'Geläschten Iwwersetzungseenheeten',
	'tpt-sections-template' => 'Iwwersetzung Säiteschabloun',
	'tpt-badtitle' => 'De Säitennumm deen ugi gouf ($1) ass kee valabelen Titel',
	'tpt-oldrevision' => "$2 ass net déi lescht Versioun vun der Säit [[$1]].
Nëmmen déi lescht Versioune kënne fir d'Iwwersetzung markéiert ginn.",
	'tpt-notsuitable' => "D'Säit $1 ass net geeegent fir iwwersat ze ginn.
Vergewëssert Iech ob se <nowiki><translate></nowiki>-Taggen  an eng valabel Syntax huet.",
	'tpt-saveok' => 'D\'Säit [[$1]] gouf fir d\'Iwwersetzung mat $2 {{PLURAL:$2|Iwwersetzungseenheet|Iwwersetzungseenheete}} markéiert.
D\'Säit kann elo <span class="plainlinks">[$3 iwwersat]</span> ginn.',
	'tpt-badsect' => '"$1" ass kee valbelen Numm fir d\'Iwwersetzungseenheet $2.',
	'tpt-showpage-intro' => "Ënnendrënner stinn déi nei, aktuell a gescläschten Abschnitter.
Ier dir dës Versioun fir d'iwwersetze markéiert, kuckt w.e.g. no datt d'Ännerunge vun den Abschnitter op e Minimum reduzéiert gi fir onnëtz Aarbecht vun den Iwwersezer ze vermeiden.",
	'tpt-mark-summary' => "huet dës Versioun fir d'Iwwersetzung markéiert",
	'tpt-edit-failed' => "D'Säit $1 konnt net aktualiséiert ginn",
	'tpt-already-marked' => "Déilescht Versioun vun dëser Säit gouf scho fir d'Iwwersetzung markéiert.",
	'tpt-list-nopages' => "Et si keng Säite fir d'Iwwersetzung markéiert respektiv fäerdeg fir fir d'Iwersetzung markéiert ze ginn.",
	'tpt-old-pages' => "Eng Versioun vun {{PLURAL:$1|dëser Säit|dëse Säite}} gouf fir d'Iwwersetze markéiert.",
	'tpt-new-pages' => "Op {{PLURAL:$1|dëser Säit|dëse Säiten}} ass Text mat Iwwersetzungs-Markéierungen, awer keng Versioun vun {{PLURAL:$1|dëser Säit|dëse Säiten}} ass elo fir d'Iwwersetze  markéiert.",
	'tpt-rev-latest' => 'lescht Versioun',
	'tpt-rev-old' => 'Ënnerscheed zu der vireger markéierter Versioun',
	'tpt-rev-mark-new' => "dës Versioun fir d'Iwwersetzung markéieren",
	'tpt-translate-this' => 'dës Säit iwwersetzen',
	'translate-tag-translate-link-desc' => 'Dës Säit iwwersetzen',
	'translate-tag-markthis' => "Dës Säit fir d'Iwwersetzung markéieren",
	'translate-tag-markthisagain' => 'Dës Säit huet <span class="plainlinks">[$1 Ännerungen]</span> zënter datt se fir d\'lescht <span class="plainlinks">[$2 fir d\'Iwwersetzung markéiert gouf]</span>.',
	'translate-tag-hasnew' => 'Op dëser Säit si(nn)s <span class="plainlinks">[$1 Ännerungen]</span> déi net fir d\'iwwersetzung markéiert sinn.',
	'tpt-translation-intro' => 'Dës Säit ass eng <span class="plainlinks">[$1 iwwersate Versioun]</span> vun der Säit [[$2]] an d\'Iwweersetzung ass zu $3 % ofgeschloss an aktuell.',
	'tpt-translation-intro-fuzzy' => 'Net aktuell Iwwersetzunge sinn esou markéiert.',
	'tpt-languages-legend' => 'aner Sproochen:',
	'tpt-target-page' => "Dës Säit kann net manuell aktualiséiert ginn.
Dës Säit ass eng Iwwersetzung vun der Säit [[$1]] an d'Iwwersetzung ka mat Hëllef vun der [$2 Iwwersetzungs-Fonctioun] aktulaiséiert ginn.",
	'tpt-unknown-page' => "Dëse Nummraum ass fir d'Iwwersetze vu Säitemat Inhalt reservéiert.
D'Säit, déi Dir versicht z'änneren schéngt net mat enger Säit déi fir d'iwwersetzung markéiert ass ze korrespondéieren.",
	'tpt-install' => "Lancéiert php maintenance/update.php oder web install fir d'Fonctioun vun der Säiteniwwersetzung anzeschalten.",
	'tpt-render-summary' => 'Aktualiséieren fir mat der neier Versioun vun der Quellsäit iwwereneenzestëmmen',
);

/** Macedonian (Македонски)
 * @author Brest
 */
$messages['mk'] = array(
	'translate-tag-translate-link-desc' => 'Преведи ја оваа страница',
);

/** Erzya (Эрзянь)
 * @author Botuzhaleny-sodamo
 */
$messages['myv'] = array(
	'translate-tag-translate-link-desc' => 'Йутавтык те лопанть',
);

/** Nahuatl (Nāhuatl)
 * @author Fluence
 */
$messages['nah'] = array(
	'translate-tag-translate-link-desc' => 'Tictlahtōlcuepāz inīn zāzanilli',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'pagetranslation' => 'Paginavertaling',
	'right-pagetranslation' => "Versies van pagina's voor de vertaling markeren",
	'tpt-desc' => "Uitbreiding voor het vertalen van wikipagina's",
	'tpt-section' => 'Vertaaleenheid $1',
	'tpt-section-new' => 'Nieuwe vertaaleenheid.
Naam: $1',
	'tpt-section-deleted' => 'Vertaaleenheid $1',
	'tpt-template' => 'Paginasjabloon',
	'tpt-templatediff' => 'Het paginasjabloon is gewijzigd.',
	'tpt-diff-old' => 'Vorige tekst',
	'tpt-diff-new' => 'Nieuwe tekst',
	'tpt-submit' => 'Deze versie voor vertaling markeren',
	'tpt-sections-oldnew' => 'Nieuwe en bestaande vertaaleenheden',
	'tpt-sections-deleted' => 'Verwijderde vertaaleenheden',
	'tpt-sections-template' => 'Vertaalpaginasjabloon',
	'tpt-badtitle' => 'De opgegeven paginanaam ($1) is geen geldige paginanaam',
	'tpt-oldrevision' => '$2 is niet de meest recente versie van de pagina "[[$1]]".
Alleen de meest recente versie kan voor vertaling gemarkeerd worden.',
	'tpt-notsuitable' => 'De pagina "$1" kan niet voor vertaling gemarkeerd worden.
Zorg ervoor dat de labels <nowiki><translate></nowiki> geplaatst zijn en dat deze juist zijn toegevoegd.',
	'tpt-saveok' => 'De pagina [[$1]] is gemarkeerd voor vertaling met $2 te vertalen {{PLURAL:$2|vertaaleenheid|vertaaleenheden}}.
De pagina kan nu  <span class="plainlinks">[$3 vertaald]</span> worden.',
	'tpt-badsect' => '"$1" is geen geldige naam voor vertaaleenheid $2.',
	'tpt-showpage-intro' => 'Hieronder zijn nieuwe, bestaande en verwijderde secties opgenomen.
Controleer voordat u deze versie voor vertaling markeert of de wijzigingen aan de secties zo klein mogelijk zijn om onnodig werk voor vertalers te voorkomen.',
	'tpt-mark-summary' => 'Heeft deze versie voor vertaling gemarkeerd',
	'tpt-edit-failed' => 'De pagina "$1" kon niet bijgewerkt worden.',
	'tpt-already-marked' => 'De meest recente versie van deze pagina is al gemarkeerd voor vertaling.',
	'tpt-list-nopages' => "Er zijn geen pagina's gemarkeerd voor vertaling, noch klaar om gemarkeerd te worden voor vertaling.",
	'tpt-old-pages' => "Er is al een versie van deze {{PLURAL:$1|pagina|pagina's}} gemarkeerd voor vertaling.",
	'tpt-new-pages' => "Deze {{PLURAL:$1|pagina bevat|pagina's bevatten}} tekst met vertalingslabels, maar van deze {{PLURAL:$1|pagina|pagina's}} is geen versie gemarkeerd voor vertaling.",
	'tpt-rev-latest' => 'meest recente versie',
	'tpt-rev-old' => 'verschil met de vorige gemarkeerde versie',
	'tpt-rev-mark-new' => 'deze versie voor vertaling markeren',
	'tpt-translate-this' => 'deze pagina vertalen',
	'translate-tag-translate-link-desc' => 'Deze pagina vertalen',
	'translate-tag-markthis' => 'Deze pagina voor vertaling markeren',
	'translate-tag-markthisagain' => 'Deze pagina is <span class="plainlinks">[$1 gewijzigd]</span> sinds deze voor het laatst <span class="plainlinks">[$2 voor vertaling gemarkeerd]</span> is geweest.',
	'translate-tag-hasnew' => 'Aan deze pagina zijn <span class="plainlinks">[$1 wijzigingen]</span> gemaakt die niet voor vertaling zijn gemarkeerd.',
	'tpt-translation-intro' => 'Deze pagina is een <span class="plainlinks">[$1 vertaalde versie]</span> van de pagina [[$2]] en de vertaling is $3% compleet en bijgewerkt.',
	'tpt-translation-intro-fuzzy' => 'Verouderde vertaling worden zo weergegeven.',
	'tpt-languages-legend' => 'Andere talen:',
	'tpt-target-page' => 'Deze pagina kan niet handmatig worden bijgewerkt.
Deze pagina is een vertaling van de pagina [[$1]].
De vertaling kan bijgewerkt worden via de [$2 vertaalhulpmiddelen].',
	'tpt-unknown-page' => "Deze naamruimte is gereserveerd voor de vertalingen van van pagina's.
De pagina die u probeert te bewerken lijkt niet overeen te komen met een te vertalen pagina.",
	'tpt-install' => 'Voer php maintenance/update.php of de webinstallatie uit om de paginavertaling te activeren.',
	'tpt-render-summary' => 'Bezig met bijwerken vanwege een nieuwe basisversie van de bronpagina',
);

/** Norwegian Nynorsk (‪Norsk (nynorsk)‬)
 * @author Frokor
 * @author Gunnernett
 * @author Harald Khan
 */
$messages['nn'] = array(
	'pagetranslation' => 'Sideomsetjing',
	'right-pagetranslation' => 'Merk versjonar av sider for omsetjing',
	'tpt-desc' => 'Utviding for omsetjing av innhaldssider',
	'tpt-section' => 'Omsetjingseining $1',
	'tpt-section-new' => 'Ny omsetjingseining. Namn: $1',
	'tpt-section-deleted' => 'Omsetjingseining $1',
	'tpt-template' => 'Sidemal',
	'tpt-templatediff' => 'Sidemalen har vorte endra.',
	'tpt-diff-old' => 'Førre tekst',
	'tpt-diff-new' => 'Ny tekst',
	'tpt-submit' => 'Merk denne versjonen for omsetjing',
	'tpt-sections-oldnew' => 'Nye og eksisterande omsetjingseiningar',
	'tpt-sections-deleted' => 'Sletta omsetjingseiningar',
	'tpt-sections-template' => 'Mal for omsetjingsside',
	'tpt-badtitle' => 'Det gjevne sidenamnet ($1) er ikkje ein gyldig tittel',
	'tpt-oldrevision' => '$2 er ikkje den siste versjonen av sida [[$1]].
Berre siste versjonar kan verta markert for omsetjing.',
	'tpt-notsuitable' => 'Side $1 er ikkje høveleg for omsetjing.
Sjekk at sida er merka med <nowiki><translate></nowiki> merke og har ein gyldig syntaks.',
	'tpt-saveok' => 'Sida [[$1]] er vorten merkt for omsetjing med {{PLURAL:$2|éi omsetjingseining|$2 omsetjingseiningar}}. Ho kan no verta <span class="plainlinks">[$3 sett om]</span>.',
	'tpt-badsect' => '«$1» er ikkje eit gyldig namn for omsetjingseininga $2.',
	'tpt-mark-summary' => 'Markerte denne versjonen for omsetjing',
	'tpt-edit-failed' => 'Kunne ikkje oppdatera sida: $1',
	'tpt-already-marked' => 'Den siste versjonen av denne sida har allereie vorte markert for omsetjing.',
	'tpt-list-nopages' => 'Ingen sider er markerte for omsetjing, eller klar til å verta markert for omsetjing.',
	'tpt-rev-latest' => 'siste versjon',
	'tpt-rev-old' => 'skilnad frå den førre markerte versjonen',
	'tpt-rev-mark-new' => 'marker denne versjonen for omsetjing',
	'tpt-translate-this' => 'set om denne sida',
	'translate-tag-translate-link-desc' => 'Set om denne sida',
	'translate-tag-markthis' => 'Merk denne sida for omsetjing',
	'tpt-translation-intro-fuzzy' => 'Utdaterte omsetjingar er markerte på dette viset.',
	'tpt-languages-legend' => 'Andre språk:',
	'tpt-render-summary' => 'Oppdatering for å svara til ny versjon av kjeldesida',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Audun
 * @author Jon Harald Søby
 * @author Laaknor
 * @author Nghtwlkr
 */
$messages['no'] = array(
	'pagetranslation' => 'Sideoversetting',
	'right-pagetranslation' => 'Merk versjoner av sider for oversettelse',
	'tpt-desc' => 'Utvidelse for oversetting av innholdssider',
	'tpt-section' => 'Oversettelsesenhet $1',
	'tpt-section-new' => 'Ny oversettelsesenhet. Navn: $1',
	'tpt-section-deleted' => 'Oversettelsesenhet $1',
	'tpt-template' => 'Sidemal',
	'tpt-templatediff' => 'Sidemalen har blitt endret.',
	'tpt-diff-old' => 'Forrige tekst',
	'tpt-diff-new' => 'Ny tekst',
	'tpt-submit' => 'Marker denne versjonen for oversetting',
	'tpt-sections-oldnew' => 'Nye og eksisterende oversettelsesenheter',
	'tpt-sections-deleted' => 'Slettede oversettelsesenheter',
	'tpt-sections-template' => 'Mal for oversettelsesside',
	'tpt-badtitle' => 'Det angitte sidenavnet ($1) er ikke en gyldig tittel',
	'tpt-oldrevision' => '$2 er ikke den siste versjonen av siden [[$1]].
Kun siste versjoner kan bli markert for oversettelse.',
	'tpt-notsuitable' => 'Side $1 er ikke egnet for oversettelse.
Sjekk at siden er merket med <nowiki><translate></nowiki>-merke og har en gyldig syntaks.',
	'tpt-saveok' => 'Siden [[$1]] har blitt markert for oversettelse med {{PLURAL:$2|én oversettelse|$2 oversettelser}}.
Den kan nå bli <span class="plainlinks">[$3 oversatt]</span>.',
	'tpt-badsect' => '«$1» er ikke et gyldig navn for oversettelsen $2.',
	'tpt-showpage-intro' => 'Under er nye, eksisterende og slettede seksjoner listet opp.
Før denne versjonen merkes for oversettelse, sjekk at endringene i seksjonene er minimert for å unngå unødvendig arbeid for oversetterne.',
	'tpt-mark-summary' => 'Markerte denne versjonen for oversettelse',
	'tpt-edit-failed' => 'Kunne ikke oppdatere siden: $1',
	'tpt-already-marked' => 'Den siste versjonen av denne siden har allerede blitt markert for oversettelse.',
	'tpt-list-nopages' => 'Ingen sider er marker for oversettelse, eller er klare for å bli markert for oversettelse.',
	'tpt-old-pages' => 'En versjon av {{PLURAL:$1|denne siden|disse sidene}} har blitt markert for oversettelse.',
	'tpt-new-pages' => '{{PLURAL:$1|Denne siden|Disse sidene}} inneholder tekst med oversettelsesmerker, men ingen versjon av {{PLURAL:$1|denne siden|disse sidene}} er for tiden markert for oversettelse.',
	'tpt-rev-latest' => 'siste versjon',
	'tpt-rev-old' => 'forskjell fra forrige markerte versjon',
	'tpt-rev-mark-new' => 'merk denne versjonen for oversettelse',
	'tpt-translate-this' => 'oversett denne siden',
	'translate-tag-translate-link-desc' => 'Oversett denne siden',
	'translate-tag-markthis' => 'Merk denne siden for oversettelse',
	'translate-tag-markthisagain' => 'Denne siden har hatt <span class="plainlinks">[$1 endringer]</span> siden den sist ble <span class="plainlinks">[$2 markert for oversettelse]</span>.',
	'translate-tag-hasnew' => 'Denne siden inneholder <span class="plainlinks">[$1 endringer]</span> som ikke har blitt markert for oversettelse.',
	'tpt-translation-intro' => 'Denne siden er en <span class="plainlinks">[$1 oversatt versjon]</span> av en side [[$2]] og oversettelsen er $3% ferdig og oppdatert.',
	'tpt-translation-intro-fuzzy' => 'Utdaterte oversettelser er markert på denne måten.',
	'tpt-languages-legend' => 'Andre språk:',
	'tpt-target-page' => 'Denne siden kan ikke oppdateres manuelt.
Denne siden er en oversettelse av siden [[$1]] og oversettelsen kan bli oppdatert ved å bruke [$2 oversettelsesverktøyet].',
	'tpt-unknown-page' => 'Dette navnerommet er reservert for oversettelser av innholdssider.
Denne siden som du prøver å redigere virker ikke å samsvare med noen av sidene som er markert for oversettelse.',
	'tpt-install' => 'Kjør php maintenance/update.php eller webinnstallering for å muliggjøre sideoversettelsesegenskapen.',
	'tpt-render-summary' => 'Oppdaterer for å samsvare ny versjon av kildesiden',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'pagetranslation' => 'Traduccion de paginas',
	'right-pagetranslation' => 'Marcar de versions de paginas per èsser traduchas',
	'tpt-desc' => 'Extension per traduire de paginas de contengut',
	'tpt-section' => 'Unitat de traduccion $1',
	'tpt-section-new' => 'Unitat de traduccion novèla. Nom : $1',
	'tpt-section-deleted' => 'Unitat de traduccion $1',
	'tpt-template' => 'Modèl de pagina',
	'tpt-templatediff' => 'Lo modèl de pagina a cambiat.',
	'tpt-diff-old' => 'Tèxte precedent',
	'tpt-diff-new' => 'Tèxte novèl',
	'tpt-submit' => 'Marcar aquesta version per èsser traducha',
	'tpt-sections-oldnew' => 'Unitats de traduccion novèlas e existentas',
	'tpt-sections-deleted' => 'Unitats de traduccion suprimidas',
	'tpt-sections-template' => 'Modèl de pagina de traduccion',
	'tpt-badtitle' => 'Lo nom de pagina donada ($1) es pas un títol valid',
	'tpt-oldrevision' => '$2 es pas la darrièra version de la pagina [[$1]].
Sola la darrièra version de la pagina pòt èsser marcada per èsser traducha.',
	'tpt-notsuitable' => "La pagina $1 conven pas per èsser traducha.
Siatz segur(a) que conten la balisa <nowiki><translate></nowiki> e qu'a una sintaxi corrècta.",
	'tpt-saveok' => 'La pagina « $1 » es estada marcada per èsser traducha amb $2 {{PLURAL:$2|unitat de traduccion|unitats de traduccion}}.
La pagina pòt èsser <span class="plainlinks">[$3 traducha]</span> tre ara.',
	'tpt-badsect' => '« $1 » es pas un nom valid per una unitat de traduccion $2.',
	'tpt-showpage-intro' => "Çaijós, las traduccions novèlas, las qu'existisson e las suprimidas.
Abans de marcar aquestas versions per èsser traduchas, verificatz que las modificacions a las seccions son minimizadas per evitar de trabalh inutil als traductors.",
	'tpt-mark-summary' => 'Aquesta version es estada marcada per èsser traducha',
	'tpt-edit-failed' => 'Impossible de metre a jorn la pagina $1',
	'tpt-already-marked' => "La darrièra version d'aquesta pagina ja es estada marcada per èsser traducha.",
	'tpt-list-nopages' => "Cap de pagina es pas estada marcada per èsser traducha o prèsta per l'èsser.",
	'tpt-old-pages' => "De versions d'{{PLURAL:$1|aquesta pagina|aquestas paginas}} son estadas marcadas per èsser traduchas.",
	'tpt-new-pages' => "{{PLURAL:$1|Aquesta pagina conten|Aquestas paginas contenon}} de tèxte amb de balisas de traduccion, mas cap de version d'{{PLURAL:$1|aquesta pagina es pas marcada per èsser traducha|aquestas paginas son pas marcadas per èsser traduchas}}.",
	'tpt-rev-latest' => 'darrièra version',
	'tpt-rev-old' => 'diferéncia amb la version marcada precedenta',
	'tpt-rev-mark-new' => 'marcar aquesta version per èsser traducha',
	'tpt-translate-this' => 'traduire aquesta pagina',
	'translate-tag-translate-link-desc' => 'Traduire aquesta pagina',
	'translate-tag-markthis' => 'Marcar aquesta pagina per èsser traducha',
	'translate-tag-markthisagain' => 'Aquesta pagina a agut <span class="plainlinks">[$1 de modificacions]</span> dempuèi qu’es estada darrièrament <span class="plainlinks">[$2 marcada per èsser traducha]</span>.',
	'translate-tag-hasnew' => 'Aquesta pagina conten <span class="plainlinks">[$1 de modificacions]</span> que son pas marcadas per la traduccion.',
	'tpt-translation-intro' => 'Aquesta pagina es una <span class="plainlinks">[$1 traduccion]</span> de la pagina [[$2]] e la traduccion es completada a $3 % e a jorn.',
	'tpt-translation-intro-fuzzy' => 'Las traduccions obsolètas son marcadas atal.',
	'tpt-languages-legend' => 'Autras lengas :',
	'tpt-target-page' => "Aquesta pagina pòt pas èsser mesa a jorn manualament.
Es una version traducha de [[$1]] e la traduccion pòt èsser mesa a jorn en utilizant [$2 l'esplech de traduccion].",
	'tpt-unknown-page' => "Aqueste espaci de noms es reservat per la traduccion de paginas.
La pagina qu'ensajatz de modificar sembla pas correspondre a cap de pagina marcada per èsser traducha.",
	'tpt-install' => "Aviatz php maintenance/update.php o l'installacion web per activar la foncionalitat de traduccion de paginas.",
	'tpt-render-summary' => 'Mesa a jorn per èsser en acòrd amb la version novèla de la font de la pagina',
);

/** Polish (Polski)
 * @author Leinad
 */
$messages['pl'] = array(
	'translate-tag-translate-link-desc' => 'Przetłumacz tę stronę',
);

/** Portuguese (Português)
 * @author Malafaya
 * @author Waldir
 */
$messages['pt'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marcar versões de páginas para tradução',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Unidade de tradução $1',
	'tpt-section-new' => 'Nova unidade de tradução. Nome: $1',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Novo texto',
	'tpt-submit' => 'Marcar esta versão para tradução',
	'tpt-badtitle' => 'Nome de página fornecido ($1) não é um título válido',
	'tpt-oldrevision' => '$2 não é a versão mais recente da página [[$1]].
Apenas as últimas versões podem ser marcadas para tradução.',
	'tpt-notsuitable' => 'Página $1 não é adequada para tradução.
Certifique-se que a mesma contém tags <nowiki><translate></nowiki> e possui uma sintaxe válida.',
	'tpt-saveok' => 'A página [[$1]] foi marcada para tradução com $2 {{PLURAL:$2|unidade|unidades}} de tradução.
A página pode agora ser <span class="plainlinks">[$3 traduzida]</span>.',
	'tpt-badsect' => '"$1" não é um nome válido para a unidade de tradução $2.',
	'tpt-showpage-intro' => 'Abaixo estão listadas secções novas, existentes e apagadas.
Antes de marcar esta versão para tradução, verifique que as alterações às secções são minimizadas para evitar trabalho desnecessário para os tradutores.',
	'tpt-mark-summary' => 'Marcada esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-already-marked' => 'A versão mais recente desta página já foi marcada para tradução.',
	'tpt-list-nopages' => 'Não existem páginas marcadas para tradução, nem prontas a ser marcadas para tradução.',
	'tpt-old-pages' => 'Alguma versão {{PLURAL:$1|desta página|destas páginas}} foi marcada para tradução.',
	'tpt-new-pages' => '{{PLURAL:$1|Esta página contém|Estas páginas contêm}} texto com tags de tradução, mas nenhuma versão {{PLURAL:$1|desta página|destas páginas}} está atualmente marcada para tradução.',
	'tpt-rev-latest' => 'versão mais recente',
	'tpt-rev-old' => 'Diferença em relação à versão marcada anterior',
	'tpt-rev-mark-new' => 'marcar esta versão para tradução',
	'tpt-translate-this' => 'traduzir esta página',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'tpt-translation-intro' => 'Esta página é uma <span class="plainlinks">[$1 versão traduzida]</span> de uma página [[$2]], e a tradução está $3% completa e atualizada.',
	'tpt-languages-legend' => 'Outras línguas:',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Esta página é uma tradução da página [[$1]], e a tradução pode ser atualizada utilizando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este domínio está reservado para traduções de páginas de conteúdo.
A página que você está a tentar editar não parece corresponder a qualquer página marcada para tradução.',
);

/** Brazilian Portuguese (Português do Brasil)
 * @author Eduardo.mps
 */
$messages['pt-br'] = array(
	'pagetranslation' => 'Tradução de páginas',
	'right-pagetranslation' => 'Marca versões de páginas para tradução',
	'tpt-desc' => 'Extensão para traduzir páginas de conteúdo',
	'tpt-section' => 'Seção:',
	'tpt-section-new' => 'Nova Seção:',
	'tpt-diff-old' => 'Texto anterior',
	'tpt-diff-new' => 'Novo texto',
	'tpt-submit' => 'Marca esta versão para tradução',
	'tpt-badtitle' => 'O nome de página dado ($1) não é um título válido',
	'tpt-oldrevision' => '$2 não é a versão atual da página [[$1]].
Apenas as versões atuais pode ser marcadas para tradução.',
	'tpt-notsuitable' => 'A página $1 não está adequada para tradução.
Tenha certeza que ela tem marcas <nowiki><translate></nowiki> e tem a sintaxe válida.',
	'tpt-saveok' => 'A página "$1" foi marcada para tradução com $2 seções traduzíveis.
A página pode ser <span class="plainlinks">[$3 traduzida]</span> agora.',
	'tpt-badsect' => '"$1" não é um nome válido para a seção $2.',
	'tpt-showpage-intro' => 'Abaixo estão listadas seções novas, existentes e removidas.
Antes de marcar esta versão para tradução, verifique se as mudanças nas seções foram minimizadas para evitar trabalho desnecessário para os tradutores.',
	'tpt-mark-summary' => 'Marcou esta versão para tradução',
	'tpt-edit-failed' => 'Não foi possível atualizar a página: $1',
	'tpt-already-marked' => 'A versão atual desta página já foi marcada para tradução.',
	'tpt-list-nopages' => 'Nenhuma página está marcada para tradução nem pronta para ser marcada para tradução.',
	'tpt-old-pages' => 'Algumas versões destas páginas foram marcadas para tradução.',
	'tpt-new-pages' => 'Estas páginas contém texto com marcas "translation", mas nenhuma delas está marcada para tradução atualmente.',
	'tpt-rev-latest' => 'versão atual',
	'tpt-rev-old' => 'versão $1',
	'tpt-rev-mark-new' => 'marcar esta versão para traduçao',
	'tpt-translate-this' => 'traduzir esta página',
	'translate-tag-translate-link-desc' => 'Traduzir esta página',
	'translate-tag-markthis' => 'Marcar esta página para tradução',
	'tpt-target-page' => 'Esta página não pode ser atualizada manualmente.
Esta página é uma tradução da página [[$1]] e a tradução pode ser atualizada usando [$2 a ferramenta de tradução].',
	'tpt-unknown-page' => 'Este domínio é reservado para traduções de páginas de conteúdo.
Esta página que você está tentando editar não aparenta corresponder a nenhuma página marcada para tradução.',
);

/** Romanian (Română)
 * @author Firilacroco
 * @author KlaudiuMihaila
 */
$messages['ro'] = array(
	'translate-tag-translate-link-desc' => 'Tradu această pagină',
	'tpt-translation-intro-fuzzy' => 'Traducerile învechite sunt marcate în acest fel.',
	'tpt-languages-legend' => 'Alte limbi:',
);

/** Tarandíne (Tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'translate-tag-translate-link-desc' => 'Traduce sta vosce',
);

/** Russian (Русский)
 * @author Ferrer
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'pagetranslation' => 'Перевод страниц',
	'right-pagetranslation' => 'отметка версий страниц для перевода',
	'tpt-desc' => 'Расширение для перевода содержимого страниц',
	'tpt-section' => 'Блок перевода $1',
	'tpt-section-new' => 'Новый блок перевода. Название: $1',
	'tpt-section-deleted' => 'Элемент перевода $1',
	'tpt-template' => 'Страничный шаблон',
	'tpt-templatediff' => 'Этот страничный шаблон изменён.',
	'tpt-diff-old' => 'Предыдущий текст',
	'tpt-diff-new' => 'Новый текст',
	'tpt-submit' => 'Отметить эту версию для перевода',
	'tpt-sections-oldnew' => 'Новые и существующие элементы перевода',
	'tpt-sections-deleted' => 'Удалённые элементы перевода',
	'tpt-sections-template' => 'Шаблон страницы перевода',
	'tpt-badtitle' => 'Указанное название страницы ($1) не является допустимым',
	'tpt-oldrevision' => '$2 не является последней версией страницы [[$1]].
Только последние версии могут быть отмечены для перевода.',
	'tpt-notsuitable' => 'Страницы $1 является неподходящей для перевода.
Убедитесь, что она имеет теги <nowiki><translate></nowiki> и правильный синтаксис.',
	'tpt-saveok' => 'Страница [[$1]] был отмечена для перевода, она содержит $2 {{PLURAL:$2|блок перевода|блока перевода|блоков переводов}}.
Теперь страницу можно <span class="plainlinks">[$3 переводить]</span>.',
	'tpt-badsect' => '«$1» не является допустимым названием для блока перевода $2.',
	'tpt-showpage-intro' => 'Ниже приведены новые, существующие и удалённые разделы.
Перед отметкой этой версии для перевода, убедитесь, что изменения в разделе будут минимальны, это позволит сократить объём работы переводчиков.',
	'tpt-mark-summary' => 'Отметить эту версию для перевода',
	'tpt-edit-failed' => 'Невозможно обновить эту страницу: $1',
	'tpt-already-marked' => 'Последняя версия этой страницы уже была отмечена для перевода.',
	'tpt-list-nopages' => 'Нет страниц, отмеченных для перевода, а также нет страниц готовых к отметке.',
	'tpt-old-pages' => 'Некоторые версии {{PLURAL:$1|этой страницы|этих страниц}} были отмечены для перевода.',
	'tpt-new-pages' => '{{PLURAL:$1|Эта страница содержит|Эти страницы содержат}} текст с тегами перевода, но ни одна из версий {{PLURAL:$1|этой страницы|этих страниц}} не отмечена для перевода.',
	'tpt-rev-latest' => 'последняя версия',
	'tpt-rev-old' => 'различия с предыдущей отмеченной версией',
	'tpt-rev-mark-new' => 'отметить эту версию для перевода',
	'tpt-translate-this' => 'перевести эту страницу',
	'translate-tag-translate-link-desc' => 'Перевести эту страницу',
	'translate-tag-markthis' => 'Отметить эту страницу для перевода',
	'translate-tag-markthisagain' => 'На этой странице было произведено <span class="plainlinks">[$1 изменений]</span> с момента последней <span class="plainlinks">[$2 отметки о переводе]</span>.',
	'translate-tag-hasnew' => 'На этой странице было произведено <span class="plainlinks">[$1 изменений]</span>, которые не отмечены для перевода.',
	'tpt-translation-intro' => 'Эта страница является <span class="plainlinks">[$1 переводом]</span> страницы [[$2]]. Перевод актуален и выполнен на $3%.',
	'tpt-translation-intro-fuzzy' => 'Устаревшие переводы отмечены следующим образом.',
	'tpt-languages-legend' => 'Другие языки:',
	'tpt-target-page' => 'Эта страница не может быть обновлена вручную.
Эта страница является переводом страницы [[$1]], перевод может быть обновлен с помощью специального [$2 инструмента перевода].',
	'tpt-unknown-page' => 'Это пространство имён зарезервировано для переводов текстов страниц.
Страница, которую вы пытаетесь изменить, не соответствует какой-либо странице, отмеченной для перевода.',
	'tpt-install' => 'Запустите php-скрипт maintenance/update.php или веб-установку, чтобы включить возможность перевода страниц.',
	'tpt-render-summary' => 'Обновление для соответствия новой версии исходной страницы.',
);

/** Slovak (Slovenčina)
 * @author Helix84
 * @author Mormegil
 */
$messages['sk'] = array(
	'pagetranslation' => 'Preklad stránky',
	'right-pagetranslation' => 'Označiť verzie stránok na preklad',
	'tpt-desc' => 'Rozšírenie na preklad stránok s obsahom',
	'tpt-section' => 'Jednotka prekladu $1',
	'tpt-section-new' => 'Nová jednotka prekladu. Názov: $1',
	'tpt-section-deleted' => 'Jednotka prekladu $1',
	'tpt-template' => 'Šablóna stránky',
	'tpt-templatediff' => 'Šablóna stránky sa zmenila.',
	'tpt-diff-old' => 'Predošlý text',
	'tpt-diff-new' => 'Nový text',
	'tpt-submit' => 'Označiť túto verziu na preklad',
	'tpt-sections-oldnew' => 'Nové a existujúce jednotky prekladu',
	'tpt-sections-deleted' => 'Zmazané jednotky prekladu',
	'tpt-sections-template' => 'Šablóna stránky na preklad',
	'tpt-badtitle' => 'Zadaný názov stránky ($1) nie je platný',
	'tpt-oldrevision' => '$2 nie je najnovšia verzia stránky [[$1]].
Na preklad je možné označiť iba posledné verzie stránok.',
	'tpt-notsuitable' => 'Stránka $1 nie je vhodná na preklad.
Uistite sa, že obsahuje značky <nowiki><translate></nowiki> a má platnú syntax.',
	'tpt-saveok' => 'Stránka [[$1]] bola označená na preklad s $2 {{PLURAL:$2|jednotkou prekladu, ktorú|jednotkami prekladu, ktoré}} možno preložiť.
Túto stránku je teraz možné <span class="plainlinks">[$3 preložiť]</span>.',
	'tpt-badsect' => '„$1“ nie je platný názov jednotky prekladu $2.',
	'tpt-showpage-intro' => 'Dolu sú uvedené nové, súčasné a zmazané sekcie,
Predtým než túto verziu označíte na preklad skontrolujte, že zmeny sekcií sú minimálne aby ste zabránili zbytočnej práci prekladateľov.',
	'tpt-mark-summary' => 'Táto verzia je označená na preklad',
	'tpt-edit-failed' => 'Nebolo možné aktualizovať stránku: $1',
	'tpt-already-marked' => 'Najnovšia verzia tejto stránky už bola označená na preklad.',
	'tpt-list-nopages' => 'Žiadne stránky nie sú označené na preklad alebo na to nie sú pripravené.',
	'tpt-old-pages' => 'Niektoré verzie {{PLURAL:$1|tejto stránky|týchto stránok}} boli označené na preklad.',
	'tpt-new-pages' => '{{PLURAL:$1|Táto stránka obsahuje|Tieto stránky obsahujú}} text so značkami na preklad, ale žiadna verzia {{PLURAL:$1|tejto stránky|týchto stránok}} nie je označená na preklad.',
	'tpt-rev-latest' => 'najnovšia verzia',
	'tpt-rev-old' => 'rozdiel oproti predošlej označenej verzii',
	'tpt-rev-mark-new' => 'označiť túto verziu na preklad',
	'tpt-translate-this' => 'preložiť túto stránku',
	'translate-tag-translate-link-desc' => 'Preložiť túto stránku',
	'translate-tag-markthis' => 'Označiť túto stránku na preklad',
	'translate-tag-markthisagain' => 'Táto stránka obsahuje <span class="plainlinks">[$1 {{PLURAL:$1|zmenu|zmeny|zmien}}]</span> odkedy bola naposledy <span class="plainlinks">[$2 označená na preklad]</span>.',
	'translate-tag-hasnew' => 'Táto stránka obsahuje <span class="plainlinks">[$1 zmeny]</span>, ktoré nie sú označené na preklad.',
	'tpt-translation-intro' => 'Táto stránka je <span class="plainlinks">[$1 preloženou verziou]</span> stránky [[$2]] a preklad je hotový a aktuálny na $3 %.',
	'tpt-translation-intro-fuzzy' => 'Zastaralé preklady sú označené takto.',
	'tpt-languages-legend' => 'Iné jazyky:',
	'tpt-target-page' => 'Túto stránku nemožno aktualizovať ručne.
Táto stránka je prekladom stránky [[$1]] a preklad možno aktualizovať pomocou [$2 nástroja na preklad].',
	'tpt-unknown-page' => 'Tento menný priestor je vyhradený na preklady stránok s obsahom.
Zdá sa, že stránka, ktorú sa pokúšate upravovať nezodpovedá žiadnej stránke označenej na preklad.',
	'tpt-install' => 'Funkciu prekladu webových stránok zapnete spustením php maintenance/update.php alebo webovej inštalácie.',
	'tpt-render-summary' => 'Aktualizácia na novú verziu zdrojovej stránky',
);

/** Serbian Cyrillic ekavian (ћирилица)
 * @author Михајло Анђелковић
 */
$messages['sr-ec'] = array(
	'translate-tag-translate-link-desc' => 'Преведите ову страну',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 */
$messages['stq'] = array(
	'translate-tag-translate-link-desc' => 'Disse Siede uursätte',
);

/** Swedish (Svenska)
 * @author M.M.S.
 * @author Najami
 */
$messages['sv'] = array(
	'pagetranslation' => 'Sidöversättning',
	'right-pagetranslation' => 'Märk versioner av sidor för översättning',
	'tpt-desc' => 'Programtillägg för översättning av innehållssidor',
	'tpt-section' => 'Översättningsenhet $1',
	'tpt-section-new' => 'Ny översättningsenhet. Namn: $1',
	'tpt-section-deleted' => 'Översättningsenhet $1',
	'tpt-template' => 'Sidmall',
	'tpt-templatediff' => 'Sidmallen har ändrats.',
	'tpt-diff-old' => 'Föregående text',
	'tpt-diff-new' => 'Ny text',
	'tpt-submit' => 'Märk den här versionen för översättning',
	'tpt-sections-oldnew' => 'Nya och existerande översättningsenheter',
	'tpt-sections-deleted' => 'Raderade översättningsenheter',
	'translate-tag-translate-link-desc' => 'Översätt den här sidan',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'pagetranslation' => 'పేజీ అనువాదం',
	'tpt-section' => 'విభాగం:',
	'tpt-section-new' => 'కొత్త విభాగం:',
	'tpt-template' => 'పేజీ మూస',
	'tpt-diff-new' => 'కొత్త పాఠ్యం',
	'tpt-translate-this' => 'ఈ పేజీని అనువదించండి',
	'translate-tag-translate-link-desc' => 'ఈ పేజీని అనువదించండి',
	'tpt-languages-legend' => 'ఇతర భాషలు:',
);

/** Thai (ไทย)
 * @author Ans
 */
$messages['th'] = array(
	'translate-tag-translate-link-desc' => 'แปลหน้านี้',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'translate-tag-translate-link-desc' => 'Isalinwika ang pahinang ito',
);

/** Turkish (Türkçe)
 * @author Joseph
 * @author Karduelis
 */
$messages['tr'] = array(
	'pagetranslation' => 'Çeviri sayfası',
	'tpt-diff-new' => 'Yeni metin',
	'tpt-translate-this' => 'Bu sayfayı çevir',
	'translate-tag-translate-link-desc' => 'Bu sayfayı çevir',
	'tpt-languages-legend' => 'Diğer diller:',
);

/** Ukrainian (Українська)
 * @author AS
 * @author Ahonc
 */
$messages['uk'] = array(
	'pagetranslation' => 'Переклад сторінок',
	'right-pagetranslation' => 'позначення версій сторінок для перекладу',
	'tpt-desc' => 'Розширення для перекладу статей',
	'tpt-section' => 'Блок перекладу $1',
	'tpt-section-new' => 'Новий блок перекладу. Назва: $1',
	'tpt-section-deleted' => 'Блок перекладу $1',
	'tpt-template' => 'Шаблон сторінки',
	'tpt-templatediff' => 'Шаблон сторінки змінений.',
	'tpt-diff-old' => 'Попередній текст',
	'tpt-diff-new' => 'Новий текст',
	'tpt-submit' => 'Позначити цю версію для перекладу',
	'tpt-sections-oldnew' => 'Нові та існуючі блоки перекладу',
	'tpt-sections-deleted' => 'Вилучені блоки перекладу',
	'tpt-badtitle' => 'Зазначена назва сторінки ($1) недопустима',
	'tpt-translate-this' => 'перекласти цю сторінку',
	'translate-tag-translate-link-desc' => 'Перекласти цю сторінку',
	'tpt-languages-legend' => 'Інші мови:',
);

/** Vèneto (Vèneto)
 * @author Candalua
 */
$messages['vec'] = array(
	'translate-tag-translate-link-desc' => 'Tradusi sta pagina',
);

/** Veps (Vepsan kel')
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'translate-tag-translate-link-desc' => "Käta nece lehtpol'",
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'tpt-template' => 'Mẫu trang',
	'tpt-templatediff' => 'Mẫu trang đã thay đổi.',
	'tpt-diff-old' => 'Văn bản trước',
	'tpt-diff-new' => 'Văn bản mới',
	'tpt-rev-latest' => 'phiên bản mới nhất',
	'tpt-rev-old' => 'khác biệt với phiên bản đánh dấu trước',
	'tpt-rev-mark-new' => 'đánh dấu phiên bản này là cần dịch',
	'tpt-translate-this' => 'dịch trang này',
	'translate-tag-translate-link-desc' => 'Dịch trang này',
	'translate-tag-markthis' => 'Đánh dấu trang này là cần dịch',
	'tpt-languages-legend' => 'Ngôn ngữ khác:',
	'tpt-render-summary' => 'Cập nhật đến phiên bản mới của trang nguồn',
);

/** Volapük (Volapük)
 * @author Smeira
 */
$messages['vo'] = array(
	'translate-tag-translate-link-desc' => 'Tradutön padi at',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'translate-tag-translate-link-desc' => 'פֿארטײַטשט דעם בלאט',
);

/** Simplified Chinese (‪中文(简体)‬)
 * @author Gzdavidwong
 */
$messages['zh-hans'] = array(
	'translate-tag-translate-link-desc' => '翻译本页',
);

/** Traditional Chinese (‪中文(繁體)‬)
 * @author Wrightbus
 */
$messages['zh-hant'] = array(
	'translate-tag-translate-link-desc' => '翻譯本頁',
);

