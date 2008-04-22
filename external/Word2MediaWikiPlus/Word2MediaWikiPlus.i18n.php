<?php
/**
 * Internationalisation file for extension Word2MediaWikiPlus.
 *
 * @comment Word2MediaWikiPlus is a tool that converts Microsoft Word documents to MediaWiki text
 * @comment More info: http://www.mediawiki.org/wiki/Extension:Word2MediaWikiPlus
 */

$messages = array();

/**  English */
$messages['en'] = array(
	'wikiSearchTitle'                        => 'Search -',
	'wikiCategoryKeyWord'                    => 'category',
	'categoryImagePreFix'                    => 'Images ',
	'wikiUploadTitle'                        => 'Upload',
	'clickChartText'                         => 'click me!',
	'unableToConvertMarker'                  => '## Error converting ##: ',
	'txt_TitlePage'                          => 'Title page',
	'txt_PageHeader'                         => 'Page header',
	'txt_PageFooter'                         => 'Page footer',
	'txt_Footnote'                           => 'Footnotes',
	'msg_Upload_Info'                        => 'Now the image file upload will begin. Before you start you need to set your browser right:\\r\\n1. Close all sidebars like favorites.\\r\\n2. Sign in into your wiki.\\r\\n\\Do not click ok before you checked this.',
	'msg_Finished'                           => 'Converting finished. Paste your clipboard contents into your wiki editor.',
	'msg_NoDocumentLoaded'                   => 'No document was loaded.',
	'msg_LoadDocument'                       => 'Please load the document to convert.',
	'msg_CloseAll'                           => 'Please close all documents but the one you want to convert! The macro will stop now.',
	'msg_SimpleTab'                          => 'Simple',
	'msg_ArticleName'                        => 'Article Name',
	'msg_TargetWikiFrame'                    => 'Target Wiki',
	'msg_TestSystem'                         => 'Test System',
	'msg_ProdSystem'                         => 'Production System',
	'msg_CheckURL'                           => 'Check URL',
	'msg_CategoryFrame'                      => 'Category',
	'msg_MultipleCats'                       => 'Seperate multiple categories with a comma (,)',
	'msg_ArticleCategory'                    => 'Article Category',
	'msg_ImageCategory'                      => 'Image Category',
	'msg_ImageDescription'                   => 'Additional text for image description',
	'msg_ImageHandlingFrame'                 => 'Image Handling',
	'msg_ImageExtraction'                    => 'Extract Images',
	'msg_ImageUseMSPhoto'                    => 'Paste in MS Photo Editor as new picture',
	'msg_ImageUpdate'                        => 'Update existing image files',
	'msg_ImageUpload'                        => 'Upload Images',
	'msg_ImageReload'                        => 'Reload Images',
	'msg_ImageUseFrames'                     => 'Use Frames',
	'msg_Always'                             => 'Always',
	'msg_Never'                              => 'Never',
	'msg_AsInDocument'                       => 'As in document',
	'msg_ImageSizeFrame'                     => 'Image Size',
	'msg_ImageFullSize'                      => 'Use full size',
	'msg_ImageFullSizeToolTip'               => 'Use the full image size regardless of whether the image has been reduced in Word',
	'msg_ImageFullSizeMaxWidth'              => 'Use full size up to max width',
	'msg_ImageFullSizeMaxWidthToolTip'       => 'Use the full size image up to the specified max width',
	'msg_ImageDocumentSize'                  => 'Use document size',
	'msg_ImageDocumentSizeToolTip'           => 'Resize the image to the size shown in Word',
	'msg_ImageDocumentSizeMaxWidth'          => 'Use document size up to max width',
	'msg_ImageDocumentSizeMaxWidthToolTip'   => 'Resize the image to the size shown in Word up to max width',
	'msg_ImageMaxWidthFrame'                 => 'Image max width',
	'msg_ConvertTab'                         => 'Advanced Text Conversion',
	'msg_ConvertTextFrame'                   => 'Text Conversion',
	'msg_ConvertWikiSyntax'                  => 'Convert wiki syntax',
	'msg_ConvertFontSize'                    => 'Convert font size',
	'msg_ConvertNumberedLists'               => 'Number lists manually, like word shows',
	'msg_ConvertComments'                    => 'Convert comments',
	'msg_ConvertInternalAnchors'             => 'Convert internal anchors',
	'msg_ConvertFootnotes'                   => 'Convert footnotes',
	'msg_ConvertBookmarks'                   => 'Convert bookmarks',
	'msg_ConvertTablesFrame'                 => 'Tables',
	'msg_ConvertTableStyle'                  => 'Table Style',
	'msg_ConvertTableStyleNoFrames'          => 'Table Style (No Frames)',
	'msg_ConvertTableStyleParagraphNoFrames' => 'Table Style (Paragraph - No Frames)',
	'msg_ConvertTableStyleParagraphFrames'   => 'Table Style (Paragraph - Frames)',
	'msg_ConvertTabTables'                   => 'Convert tables made from tabs',
	'msg_ConvertParagraphsFrame'             => 'Paragraphs',
	'msg_ConvertFirstLineIndents'            => 'Convert fist line indents',
	'msg_ConvertNewParagraphs'               => 'Make new paragraphs with <br/>s',
	'msg_ConvertReplacePageBreaks'           => 'Replace page breaks with horizontal lines',
	'msg_ConvertJustifiedText'               => 'Convert justified text',
	'msg_ConvertHeadingsFrame'               => 'Headings',
	'msg_ConvertFirstLevelHeading'           => 'First level heading',
	'msg_ConvertPageHeaders'                 => 'Convert page headers',
	'msg_ConvertPageFooters'                 => 'Convert page footers',
	'msg_ConvertTitlePage'                   => 'Insert Title Page text',
	'msg_Convert'                            => 'Convert',
	'msg_ImagesOnly'                         => 'Images Only',
	'msg_Cancel'                             => 'Cancel',
);

/**  Message documentation */
$messages['qqq'] = array(
	'wikiSearchTitle'                        => 'Title of browser window after search in wiki',
	'wikiCategoryKeyWord'                    => 'category key word according to your language, "category" works always',
	'categoryImagePreFix'                    => 'Standard Prefix to the image category, add blank at last character to separate words',
	'wikiUploadTitle'                        => 'Title of browser window when uploading to wiki',
	'clickChartText'                         => 'clickable charts will have additional text in wiki: "click me!"',
	'unableToConvertMarker'                  => 'some links can not be converted, give hint text',
	'txt_TitlePage'                          => 'Text to go in place of title page',
	'txt_PageHeader'                         => 'Text to be inserted in place of page header',
	'txt_PageFooter'                         => 'Text to be inserted in place of page footer',
	'txt_Footnote'                           => 'Text to be inserted for footnotes',
	'msg_Upload_Info'                        => 'Text asking the user to log into their wiki and close any sidebars in their browser before clicking OK',
	'msg_Finished'                           => 'Text indicating that conversion is finished and that text can now be pasted into the wiki editor.',
	'msg_NoDocumentLoaded'                   => 'Text indicating that no document was loaded',
	'msg_LoadDocument'                       => 'Text asking the user to please load a document to convert',
	'msg_CloseAll'                           => 'Text asking the user to close other documents aside from the one they want to convert',
	'msg_SimpleTab'                          => 'Name for simple tab',
	'msg_ArticleName'                        => 'Text: Article Name',
	'msg_TargetWikiFrame'                    => 'Text: Target Wiki',
	'msg_TestSystem'                         => 'Text: Test System',
	'msg_ProdSystem'                         => 'Text: Production System',
	'msg_CheckURL'                           => 'Text: Check URL',
	'msg_CategoryFrame'                      => 'Name for category frame',
	'msg_MultipleCats'                       => 'Text: Seperate multiple categories with a comma (,)',
	'msg_ArticleCategory'                    => 'Text: Article Category',
	'msg_ImageCategory'                      => 'Text: Image Category',
	'msg_ImageDescription'                   => 'Text: Additional text for image description',
	'msg_ImageHandlingFrame'                 => 'Name for image handling frame',
	'msg_ImageExtraction'                    => 'Text: Extract Images',
	'msg_ImageUseMSPhoto'                    => 'Text: Paste in MS Photo Editor as new picture',
	'msg_ImageUpdate'                        => 'Text: Update existing image files',
	'msg_ImageUpload'                        => 'Text: Upload Images',
	'msg_ImageReload'                        => 'Text: Reload Images',
	'msg_ImageUseFrames'                     => 'Text: Use Frames',
	'msg_Always'                             => 'Text: Always',
	'msg_Never'                              => 'Text: Never',
	'msg_AsInDocument'                       => 'Text: As in document',
	'msg_ImageSizeFrame'                     => 'Name for image size frame',
	'msg_ImageFullSize'                      => 'text indicating that images will be resized to their full size',
	'msg_ImageFullSizeToolTip'               => 'tooltip explaining that the checking this option will cause the full image size to be used regardless of whether the image has been reduced in Word',
	'msg_ImageFullSizeMaxWidth'              => 'text indicating that images will be resized to the specified max width',
	'msg_ImageFullSizeMaxWidthToolTip'       => 'tooltip explaining that checking this option will cause the full size image to be used up to the specified max width',
	'msg_ImageDocumentSize'                  => 'text indicating that images will be resized to the size they are in the document',
	'msg_ImageDocumentSizeToolTip'           => 'tooltip indicating that checking this option will resize the image to the size shown in Word',
	'msg_ImageDocumentSizeMaxWidth'          => 'text indicating that images will be resized to the document size up to max width',
	'msg_ImageDocumentSizeMaxWidthToolTip'   => 'tooltip indicating that checking this option will resize the image to the size shown in Word up to max width',
	'msg_ImageMaxWidthFrame'                 => 'Name for image max width frame',
	'msg_ConvertTab'                         => 'Name for tab that holds the advanced text conversion options',
	'msg_ConvertTextFrame'                   => 'Name for text conversion frame',
	'msg_ConvertWikiSyntax'                  => 'Text: Convert wiki syntax',
	'msg_ConvertFontSize'                    => 'Text: Convert font size',
	'msg_ConvertNumberedLists'               => 'Text: Number lists manually, like word shows',
	'msg_ConvertComments'                    => 'Text: Convert comments',
	'msg_ConvertInternalAnchors'             => 'Text: Convert internal anchors',
	'msg_ConvertFootnotes'                   => 'Text: Convert footnotes',
	'msg_ConvertBookmarks'                   => 'Text: Convert bookmarks',
	'msg_ConvertTablesFrame'                 => 'name for tables frame',
	'msg_ConvertTableStyle'                  => 'Text: Table Style',
	'msg_ConvertTableStyleNoFrames'          => 'Text: Table Style (No Frames)',
	'msg_ConvertTableStyleParagraphNoFrames' => 'Text: Table Style (Paragraph - No Frames)',
	'msg_ConvertTableStyleParagraphFrames'   => 'Text: Table Style (Paragraph - Frames)',
	'msg_ConvertTabTables'                   => 'Text: Convert tables made from tabs',
	'msg_ConvertParagraphsFrame'             => 'Name for paragraphs frame',
	'msg_ConvertFirstLineIndents'            => 'Text: Convert fist line indents',
	'msg_ConvertNewParagraphs'               => 'Text: Make new paragraphs with &lt;br /&gt;s',
	'msg_ConvertReplacePageBreaks'           => 'Text: Replace page breaks with horizontal lines',
	'msg_ConvertJustifiedText'               => 'Text: Convert justified text',
	'msg_ConvertHeadingsFrame'               => 'Name for headings frame',
	'msg_ConvertFirstLevelHeading'           => 'Text: First level heading',
	'msg_ConvertPageHeaders'                 => 'Text: Convert page headers',
	'msg_ConvertPageFooters'                 => 'Text: Convert page footers',
	'msg_ConvertTitlePage'                   => 'Text: Insert Title Page text',
	'msg_Convert'                            => 'Text for button: Convert',
	'msg_ImagesOnly'                         => 'Text for button: Images Only',
	'msg_Cancel'                             => 'Text for button: Cancel',
);

/** Bulgarian (Български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'wikiSearchTitle'       => 'Търсене -',
	'wikiCategoryKeyWord'   => 'категория',
	'categoryImagePreFix'   => 'Картинки',
	'wikiUploadTitle'       => 'Качване',
	'clickChartText'        => 'натисни ме!',
	'unableToConvertMarker' => '## Грешка при конвертирането ##:',
	'msg_NoDocumentLoaded'  => 'Не беше зареден документ.',
	'msg_LoadDocument'      => 'Моля заредете документ, който да бъде конвертиран.',
	'msg_CloseAll'          => 'Моля затворете всички документи освен този, който желаете да конвертирате! Макросът ще спре сега.',
);

/** Deutsch (Deutsch) */
$messages['de'] = array(
	'wikiSearchTitle'       => 'Suchergebnisse -',
	'wikiCategoryKeyWord'   => 'Kategorie',
	'categoryImagePreFix'   => 'Bilder',
	'wikiUploadTitle'       => 'Hochladen',
	'clickChartText'        => 'Klick mich!',
	'unableToConvertMarker' => '## Fehler bei Konvertierung ##:',
	'txt_TitlePage'         => 'Titelblatt',
	'txt_PageHeader'        => 'Kopfzeile',
	'txt_PageFooter'        => 'Fußzeile',
	'txt_Footnote'          => 'Fußnoten',
	'msg_Upload_Info'       => 'Jetzt werden die Bilder hochgeladen. Vorher muss der Browser korrekt eingestellt sein, damit es funktioniert:
1. Schließe alle Seitenleisten wie z.B. die Favoriten.
2. Melde dich an deinem Wiki an.
Klicke erst OK, wenn du dies durchgeführt habst.',
	'msg_Finished'          => 'Konvertierung beendet. Füge die Daten aus der Zwischenablage in dein Wiki ein.',
	'msg_NoDocumentLoaded'  => 'Es wurde kein Dokument geladen.',
	'msg_LoadDocument'      => 'Bitte lade das zu konvertierende Dokument.',
	'msg_CloseAll'          => 'Bitte schließe alle Dokumente bis auf das, welches du konvertieren möchtest! Das Makro wird jetzt beendet.',
);

/** Deutsch - formal address (Deutsch - förmliche Anrede) */
$messages['de-formal'] = array(
	'msg_Upload_Info'       => 'Jetzt werden die Bilder hochgeladen. Vorher muss der Browser korrekt eingestellt sein, damit es funktioniert:
1. Schließen Sie alle Seitenleisten wie z.B. die Favoriten.
2. Melden Sie sich an Ihrem Wiki an.
Klicken Sie erst OK, wenn Sie dies durchgeführt haben.',
	'msg_Finished'          => 'Konvertierung beendet. Fügen Sie die Daten aus der Zwischenablage in Ihr Wiki ein.',
	'msg_LoadDocument'      => 'Bitte laden Sie das zu konvertierende Dokument.',
	'msg_CloseAll'          => 'Bitte schließen Sie alle Dokumente bis auf das, welches Sie konvertieren möchten! Das Makro wird jetzt beendet.',
);

/** Español (Español) */
$messages['es'] = array(
	'wikiSearchTitle'       => 'Buscar -',
	'wikiCategoryKeyWord'   => 'categoría',
	'categoryImagePreFix'   => 'Imagen',
	'wikiUploadTitle'       => 'Subir un Archivo',
	'clickChartText'        => '¡Chascarme!',
	'unableToConvertMarker' => '## Error de convertir ##:',
	'txt_TitlePage'         => 'Portada',
	'txt_PageHeader'        => 'Ecabezado',
	'txt_PageFooter'        => 'Pie de página',
	'txt_Footnote'          => 'Nota al pie',
	'msg_Upload_Info'       => 'Ahora el upload del archivo de la imagen comenzará. Antes de que te comiences necesidad de fijar la tu derecha del browser:
1. Cerrar todo barras laterales como marcadores.
2. Registrarse a su wiki.
No tecleo acepter antes de comprobar esto.',
	'msg_Finished'          => 'Convertier Listo. Pegar su contentido portapapeles en su editor de wiki.',
	'msg_NoDocumentLoaded'  => 'No se cargó ningún documento.',
	'msg_LoadDocument'      => 'Cargar por favor el documento para convertir.',
	'msg_CloseAll'          => '¡Cerrar por favor todos los documentos pero el que deseas convertir! La macro ahora parará.',
);

/** Français (Français) */
$messages['fr'] = array(
	'wikiSearchTitle'       => 'Rechercher -',
	'wikiCategoryKeyWord'   => 'catégorie',
	'wikiUploadTitle'       => 'Télécharger',
	'clickChartText'        => 'cliquez moi !',
	'unableToConvertMarker' => '## Erreur de conversion de ##:',
	'txt_TitlePage'         => 'Page de titre',
	'txt_PageHeader'        => 'En-tête de page',
	'txt_PageFooter'        => 'Pied de page',
	'txt_Footnote'          => 'Notes de bas de page',
	'msg_Upload_Info'       => "Maintenant le fichier image va être téléchargé. Avant de commencer, vous devez organiser correctement votre navigateur :
1. Fermez toutes les fenêtres latérales comme celle des favoris.
2. Identifiez vous dans votre wiki.
Ne cliquez pas sur OK avant d'avoir vérifé tout cela.",
	'msg_Finished'          => "Conversion terminée. Collez le contenu du bloc-note dans l'éditeur du wiki.",
	'msg_NoDocumentLoaded'  => "Aucun document n'a été chargé.",
	'msg_LoadDocument'      => 'Veuillez charger le document à convertir.',
	'msg_CloseAll'          => "Veuillez fermer tous les documents sauf celui que vous souhaitez convertir ! La macro va maintenant s'arrêter.",
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'wikiSearchTitle'                        => 'Sichen -',
	'wikiCategoryKeyWord'                    => 'Kategorie',
	'categoryImagePreFix'                    => 'Biller',
	'wikiUploadTitle'                        => 'Eroplueden',
	'clickChartText'                         => 'Hei klicken!',
	'unableToConvertMarker'                  => '## Feeler beim ëmwandelen ##:',
	'txt_TitlePage'                          => 'Titelsäit',
	'txt_PageHeader'                         => 'En-tête vun der Säit',
	'txt_PageFooter'                         => 'Foussnout vun der Säit',
	'txt_Footnote'                           => 'Foussnouten',
	'msg_Upload_Info'                        => "Elo gëtt de Fichier vum Bild eropgelueden. Ier Dir ufänkt musst Dir äre Browser richteg astellwn:
# Maacht all säitlech Fënsteren, wéi z. Bsp. d'Favoriten, zou.
# Meld iech an ärer Wiki un.

Klickt net op OK ier Dir net all dëst nogekuckt hutt.",
	'msg_Finished'                           => "D'Ëmwandelung ass fäerdeg. Kopéiert d'Daten aus dem Zwëschespäicher elo an ären Editeur vun der Wiki.",
	'msg_NoDocumentLoaded'                   => 'Et gouf keen Dokument gelueden.',
	'msg_LoadDocument'                       => 'Lued w.e.g. dat Dokument dat dir ëmwandele wëllt.',
	'msg_CloseAll'                           => "Maacht w.e.g. all Dokumenter zou ausser deem dat dir ëmwandele wëllt! D'Makro stoppt elo.",
	'msg_SimpleTab'                          => 'Einfach',
	'msg_ArticleName'                        => 'Artikelnumm',
	'msg_TargetWikiFrame'                    => 'Zilwiki',
	'msg_TestSystem'                         => 'Testsystem',
	'msg_ProdSystem'                         => 'Produktiounssystem',
	'msg_CheckURL'                           => 'URL nokucken',
	'msg_CategoryFrame'                      => 'Kategorie',
	'msg_ArticleCategory'                    => 'Artikel-Kategorie',
	'msg_ImageCategory'                      => 'Bild-Kategorie',
	'msg_ImageDescription'                   => "Zousätzlechen Text fir d'Bildbeschreiwung",
	'msg_ImageUpload'                        => 'Biller eroplueden',
	'msg_ImageReload'                        => 'Biller nei lueden',
	'msg_ImageUseFrames'                     => 'Rumme benotzen',
	'msg_Always'                             => 'Ëmmer',
	'msg_Never'                              => 'Ni',
	'msg_AsInDocument'                       => 'Esou wéi am Dokument',
	'msg_ImageSizeFrame'                     => 'Gréisst vum Bild',
	'msg_ImageDocumentSize'                  => 'Benotzt Dokumentegréisst',
	'msg_ImageMaxWidthFrame'                 => 'Maximal Breet vun engem Bild',
	'msg_ConvertTab'                         => 'Erweidert Textëmwandlung',
	'msg_ConvertTextFrame'                   => 'Textëmwandlung',
	'msg_ConvertComments'                    => 'Bemierkungen ëmwandelen',
	'msg_ConvertTablesFrame'                 => 'Tabellen',
	'msg_ConvertTableStyle'                  => 'Tabellestyl',
	'msg_ConvertTableStyleNoFrames'          => 'Tabellestyl (Keng Rummen (no frames))',
	'msg_ConvertTableStyleParagraphNoFrames' => 'Tabellestyl (Abschnitt - Keng Rummen (no frames))',
	'msg_ConvertTableStyleParagraphFrames'   => 'Tabellestyl (Abschnitt - Rummen (frames))',
	'msg_ConvertParagraphsFrame'             => 'Abschnitter',
	'msg_ConvertReplacePageBreaks'           => 'Ersetz Säitenëmbrech duerch horizontal Linnen',
	'msg_ConvertJustifiedText'               => 'Geriiten Text ëmwandelen',
	'msg_ConvertHeadingsFrame'               => 'Iwwerschrëften',
	'msg_ConvertFirstLevelHeading'           => 'Iwwerschrëft vum éischte Niveau',
	'msg_Convert'                            => 'Ëmwandelen',
	'msg_ImagesOnly'                         => 'Nëmme Biller',
	'msg_Cancel'                             => 'Zréck',
);

/** Dutch (Nederlands)
 * @author Siebrand
 * @author SPQRobin
 */
$messages['nl'] = array(
	'wikiSearchTitle'       => 'Zoeken -',
	'wikiCategoryKeyWord'   => 'categorie',
	'categoryImagePreFix'   => 'Afbeelding ',
	'wikiUploadTitle'       => 'Uploaden',
	'clickChartText'        => 'klik mij!',
	'unableToConvertMarker' => '## Conversiefout ##:',
	'txt_TitlePage'         => 'Paginanaam',
	'txt_PageHeader'        => 'Koptekst',
	'txt_PageFooter'        => 'Voettekst',
	'txt_Footnote'          => 'Voetnoten',
	'msg_Upload_Info'       => 'Nu zal de afbeeldingupload beginnen. Voordat u begint moet u uw internetbrowser goed zetten:
1. Sluit alle zijbalken zoals bladwijzers of favorieten.
2. Meld u aan bij uw wiki.
Klik niet op OK voordat u dit gecontroleerd hebt.',
	'msg_Finished'          => 'De conversie is afgerond. U kunt nu de inhoud van uw klembord in uw wiki-editor plakken.',
	'msg_NoDocumentLoaded'  => 'Het document is niet geladen.',
	'msg_LoadDocument'      => 'Laad alstublieft de te converteren documenten.',
	'msg_CloseAll'          => 'Sluit alstublieft alle documenten behalve het document dat u wilt converteren! De marco wordt nu afgebroken.',
);

/** Norwegian (bokmål)‬ (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 */
$messages['no'] = array(
	'wikiSearchTitle'                        => 'Søk –',
	'wikiCategoryKeyWord'                    => 'kategori',
	'categoryImagePreFix'                    => 'Bilder',
	'wikiUploadTitle'                        => 'Last opp',
	'clickChartText'                         => 'klikk her.',
	'unableToConvertMarker'                  => '## Feil under konvertering ##:',
	'txt_TitlePage'                          => 'Tittelside',
	'txt_PageHeader'                         => 'Sidetopp',
	'txt_PageFooter'                         => 'Sidebunn',
	'txt_Footnote'                           => 'Fotnoter',
	'msg_Upload_Info'                        => 'Opplastingen begynner nå. Før du starter må du fikse innstillingene i nettleseren.\\r\\n1. Steng alle sidemenyer, som favoritter eller logg.\\r\\n2. Logg inn på wikien.\\r\\nIkke klikk OK før du har gjort dette.',
	'msg_Finished'                           => 'Konvertering fullført. Lim inn innholdet fra utklippstavlen til wikiredigeringen.',
	'msg_NoDocumentLoaded'                   => 'Ingen dokumenter lastet.',
	'msg_LoadDocument'                       => 'Last dokumentet for å konvertere det.',
	'msg_CloseAll'                           => 'Steng alle dokumenter utenom det du ønsker å konvertere. Makroen stopper nå.',
	'msg_SimpleTab'                          => 'Enkel',
	'msg_ArticleName'                        => 'Artikkelnavn',
	'msg_TargetWikiFrame'                    => 'Målwiki',
	'msg_TestSystem'                         => 'Testsystem',
	'msg_ProdSystem'                         => 'Produksjonssystem',
	'msg_CheckURL'                           => 'Sjekk URL',
	'msg_CategoryFrame'                      => 'Kategori',
	'msg_MultipleCats'                       => 'Skill mellom kategorier med komma (,)',
	'msg_ArticleCategory'                    => 'Artikkelkategori',
	'msg_ImageCategory'                      => 'Bildekategori',
	'msg_ImageDescription'                   => 'Ekstra tekst for bildebeskrivelse',
	'msg_ImageHandlingFrame'                 => 'Bildebehandling',
	'msg_ImageExtraction'                    => 'Utvinn bilder',
	'msg_ImageUseMSPhoto'                    => 'Lim inn i MS Photo Editor som nytt bilde',
	'msg_ImageUpdate'                        => 'Oppdater eksisterende bildefiler',
	'msg_ImageUpload'                        => 'Last opp bilder',
	'msg_ImageReload'                        => 'Last bilder på nytt',
	'msg_ImageUseFrames'                     => 'Bruk rammer',
	'msg_Always'                             => 'Alltid',
	'msg_Never'                              => 'Aldri',
	'msg_AsInDocument'                       => 'Som i dokumentet',
	'msg_ImageSizeFrame'                     => 'Bildestørrelse',
	'msg_ImageFullSize'                      => 'Bruk full størrelse',
	'msg_ImageFullSizeToolTip'               => 'Bruk bildet i full størrelse selv om bildet har blitt redusert i Word',
	'msg_ImageFullSizeMaxWidth'              => 'Bruk full størrelse opp til maksimum bredde',
	'msg_ImageFullSizeMaxWidthToolTip'       => 'Bruk full størrelse opptil oppgitt maksimal bredde',
	'msg_ImageDocumentSize'                  => 'Bruk dokumentstørrelse',
	'msg_ImageDocumentSizeToolTip'           => 'Øk/minst bildestørrelsen til størrelsen vist i Word',
	'msg_ImageDocumentSizeMaxWidth'          => 'Bruk dokumentstørrelsen opptil maksimal bredde',
	'msg_ImageDocumentSizeMaxWidthToolTip'   => 'Øk/minsk bildet til størrelsen vist i Word opptil maksimal bredde',
	'msg_ImageMaxWidthFrame'                 => 'Maksimal bildebredde',
	'msg_ConvertTab'                         => 'Avansert tekstkonvertering',
	'msg_ConvertTextFrame'                   => 'Tekstkonvertering',
	'msg_ConvertWikiSyntax'                  => 'Konverter wikikode',
	'msg_ConvertFontSize'                    => 'Konverter skriftstørrelse',
	'msg_ConvertNumberedLists'               => 'Nummerer lister manuelt, slik Word viser dem',
	'msg_ConvertComments'                    => 'Konverter kommentarer',
	'msg_ConvertInternalAnchors'             => 'Konverter interne lenker',
	'msg_ConvertFootnotes'                   => 'Konverter fotnoter',
	'msg_ConvertBookmarks'                   => 'Konverter bokmerker',
	'msg_ConvertTablesFrame'                 => 'Tabeller',
	'msg_ConvertTableStyle'                  => 'Tabellstil',
	'msg_ConvertTableStyleNoFrames'          => 'Tabellstil (ingen rammer)',
	'msg_ConvertTableStyleParagraphNoFrames' => 'Tabellstil (avsnitt – ingen rammer)',
	'msg_ConvertTableStyleParagraphFrames'   => 'Tabellstil (avsnitt – rammer)',
	'msg_ConvertTabTables'                   => 'Konverter tabeller laget av tabulatorer',
	'msg_ConvertParagraphsFrame'             => 'Avsnitt',
	'msg_ConvertFirstLineIndents'            => 'Konverter innrykk i første linje',
	'msg_ConvertNewParagraphs'               => 'Lag nye avsnitt med <br />',
	'msg_ConvertReplacePageBreaks'           => 'Erstatt linjeskift med horisontale linjer',
	'msg_ConvertJustifiedText'               => 'Konverter autojustert tekst',
	'msg_ConvertHeadingsFrame'               => 'Overskrifter',
	'msg_ConvertFirstLevelHeading'           => 'Førstenivås overskrift',
	'msg_ConvertPageHeaders'                 => 'Konverter sidetopp',
	'msg_ConvertPageFooters'                 => 'Konverter sidebunn',
	'msg_ConvertTitlePage'                   => 'Sett inn tekst med sidens tittel',
	'msg_Convert'                            => 'Konverter',
	'msg_ImagesOnly'                         => 'Kun bilder',
	'msg_Cancel'                             => 'Avbryt',
);

/** Occitan (Occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'wikiSearchTitle'       => 'Recercar -',
	'wikiCategoryKeyWord'   => 'categoria',
	'categoryImagePreFix'   => 'Imatges',
	'wikiUploadTitle'       => 'Telecargar',
	'clickChartText'        => 'clicatz-me !',
	'unableToConvertMarker' => '## Error de conversion de ##:',
	'txt_TitlePage'         => 'Pagina de títol',
	'txt_PageHeader'        => 'Entèsta de pagina',
	'txt_PageFooter'        => 'Pè de pagina',
	'txt_Footnote'          => 'Nòtas de bas de pagina',
	'msg_Upload_Info'       => "Ara lo fichièr imatge va èsser telecargat. Abans de començar, vos cal organizar corrèctament vòstre navigaire :
1. Tampatz totas las fenèstras lateralas coma la dels favorits.
2. Identificatz-vos dins vòstre wiki.
Cliquetz pas sus D'ACÒRDI abans d'aver verificat tot aquò.",
	'msg_Finished'          => "Conversion acabada. Empegatz lo contengut del blòt de nòtas dins l'editor del wiki.",
	'msg_NoDocumentLoaded'  => 'Cap de document es pas estat cargat.',
	'msg_LoadDocument'      => 'Cargatz lo document de convertir.',
	'msg_CloseAll'          => "Tampatz totes los documents exceptat lo que desiratz convertir ! Ara la màcro va s'arrestar.",
);

/** Polish (Polski)
 * @author Sp5uhe
 */
$messages['pl'] = array(
	'wikiSearchTitle'       => 'Wyszukiwanie –',
	'wikiCategoryKeyWord'   => 'Kategoria',
	'categoryImagePreFix'   => 'Grafika',
	'wikiUploadTitle'       => 'Prześlij',
	'clickChartText'        => 'kliknij tu!',
	'unableToConvertMarker' => '## Błędy konwersji ##:',
	'txt_TitlePage'         => 'Strona główna',
	'txt_PageHeader'        => 'Nagłówek strony',
	'txt_PageFooter'        => 'Stopka strony',
	'txt_Footnote'          => 'Przypisy',
	'msg_Upload_Info'       => 'Plik zostanie teraz przesłany na serwer. Jednak zanim się się to stanie musisz przygotować przeglądarkę:\\r\\n1. Zamknij wszystkie paski narzędzi – jak „Łącza” lub „Ulubione”.\\r\\n2. Zaloguj się do wiki.\\r\\nNie klikaj OK zanim nie wykonasz tych czynności.',
	'msg_Finished'          => 'Konwersja zakończona. Wklej zawartość schowka do swojego edytora wiki.',
	'msg_NoDocumentLoaded'  => 'Nie ma obecnie załadowanego żadnego dokumentu.',
	'msg_LoadDocument'      => 'Załaduj dokument, który ma zostać skonwertowany.',
	'msg_CloseAll'          => 'Zamknij wszystkie dokumenty poza tym jednym, który chcesz poddać konwersji! Konwersja została wstrzymana.',
);

/** Português (Português) */
$messages['pt'] = array(
	'wikiSearchTitle'       => 'Buscar resultados -',
	'wikiCategoryKeyWord'   => 'categoria',
	'categoryImagePreFix'   => 'Imagens de',
	'wikiUploadTitle'       => 'Carregar arquivo',
	'clickChartText'        => 'clique aqui',
	'unableToConvertMarker' => '## Erro de conversão ##:',
	'txt_TitlePage'         => 'Página de título',
	'txt_PageHeader'        => 'Cabeçalho da página',
	'txt_PageFooter'        => 'Rodapé da página',
	'txt_Footnote'          => 'Notas de rodapé',
	'msg_Upload_Info'       => 'Agora o carregamento de imagens vai começar. Primeiro você precisa ajustar o seu navegador:
1. Feche todas as barras laterais (por exemplo, favoritos).
2. Autentique-se na sua wiki.
Não aperte OK antes de ter feito isto.',
	'msg_Finished'          => 'Conversão concluída. Cole o conteúdo da área de transferência no editor da sua wiki.',
	'msg_NoDocumentLoaded'  => 'Não foi carregado nenhum documento.',
	'msg_LoadDocument'      => 'Por favor carregue o documento a converter.',
	'msg_CloseAll'          => 'Por favor feche todos os documentos menos aquele que você quer converter. A macro vai parar agora.',
);

/** Русский (Русский) */
$messages['ru'] = array(
	'wikiSearchTitle'      => 'Результаты поиска -',
	'wikiCategoryKeyWord'  => 'Категории -',
	'wikiUploadTitle'      => 'Загрузить файл -',
	'txt_TitlePage'        => 'Заглавная страница',
	'txt_PageHeader'       => 'Заголовок страницы',
	'txt_Footnote'         => 'Примечания',
	'msg_Upload_Info'      => 'Сейчас начнется загрузка сообщений. Перед началом правильно настройте окно проводника Интернет:
1. Закройте все панели вроде Закладки.
2. Войдите под своим логином в Wiki.
Не нажимайте ОК пока не сделаете это!',
	'msg_Finished'         => 'Конвертация завершена.Вставте текст из буфера обмена в редактор WiKi',
	'msg_NoDocumentLoaded' => 'Документы не загружены.',
	'msg_LoadDocument'     => 'Пожалуйста загрузите документ в конвертер.',
	'msg_CloseAll'         => 'Пожалуйста закройте все документы кроме того который нужно сконвертировать! Макрос остановлен.',
);

/** Swedish (Svenska)
 * @author M.M.S.
 */
$messages['sv'] = array(
	'wikiSearchTitle'        => 'Sök -',
	'wikiCategoryKeyWord'    => 'kategori',
	'categoryImagePreFix'    => 'Bilder',
	'wikiUploadTitle'        => 'Ladda upp',
	'clickChartText'         => 'kilcka här!',
	'unableToConvertMarker'  => '## Fel i konvertering ##:',
	'txt_TitlePage'          => 'Titelsida',
	'txt_PageHeader'         => 'Sidhuvud',
	'txt_PageFooter'         => 'Sidfot',
	'txt_Footnote'           => 'Fotnoter',
	'msg_Upload_Info'        => 'Nu börjar bildfilen att laddas upp. Före du startat behöver du ange din webbläsares högerfält:\\r\\n1. Stäng alla sidpaneler som favoriter.\\r\\n2. Logga in på din wiki.\\r\\n\\Klicka inte OK förän du har gjort det här.',
	'msg_Finished'           => 'Konvertering klar. Klistra in dina skrivunderläggsinnehåll till din wikiredigerare.',
	'msg_NoDocumentLoaded'   => 'Inget dokument laddades.',
	'msg_LoadDocument'       => 'Var god ladda dokumentet till konverteringen.',
	'msg_CloseAll'           => 'Var god stäng alla dokument utan det du vill konvertera! Markon stoppas nu.',
	'msg_SimpleTab'          => 'Enkel',
	'msg_ArticleName'        => 'Artikelnamn',
	'msg_TargetWikiFrame'    => 'Målwiki',
	'msg_TestSystem'         => 'Testsystem',
	'msg_ProdSystem'         => 'Produktionssystem',
	'msg_CheckURL'           => 'Kolla URL',
	'msg_CategoryFrame'      => 'Kategori',
	'msg_MultipleCats'       => 'Separera mångfaldiga kategorier med ett komma (,)',
	'msg_ArticleCategory'    => 'Artikelkkategori',
	'msg_ImageCategory'      => 'Bildkategori',
	'msg_ImageDescription'   => 'Extra text för bildbeskrivning',
	'msg_ImageHandlingFrame' => 'Bildbehandling',
	'msg_ImageExtraction'    => 'Extrahera bilder',
	'msg_ImageUseMSPhoto'    => 'Klistra in i MS Photo Editor som en ny bild',
	'msg_ImageUpdate'        => 'Uppdatera existerande bildfiler',
	'msg_ImageUpload'        => 'Ladda upp bilder',
	'msg_ImageReload'        => 'Ladda upp bilder på nytt',
	'msg_ImageUseFrames'     => 'Använd ramar',
	'msg_Always'             => 'Alltid',
	'msg_Never'              => 'Aldrig',
	'msg_AsInDocument'       => 'Som i dokument',
	'msg_Cancel'             => 'Avbryt',
);

/** Vietnamese (Tiếng Việt)
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'wikiSearchTitle'        => 'Tìm kiếm -',
	'wikiCategoryKeyWord'    => 'thể loại',
	'categoryImagePreFix'    => 'Hình',
	'wikiUploadTitle'        => 'Tải lên',
	'clickChartText'         => 'nhấn vào đây!',
	'unableToConvertMarker'  => '## Lỗi khi chuyển đổi ##:',
	'txt_TitlePage'          => 'Trang tựa đề',
	'txt_PageHeader'         => 'Đầu đề trang',
	'txt_PageFooter'         => 'Đoạn cuối trang',
	'txt_Footnote'           => 'Chú thích cuối trang',
	'msg_Upload_Info'        => 'Bây giờ việc tải tập tin hình lên sẽ bắt đầu. Trước bắt đầu bạn cần thiết lập quyền trình duyệt của mình:\\r\\n1. Đóng tất cả các thanh bên như trang ưa thích.\\r\\n2. Đăng nhập vào wiki của bạn.\\r\\n\\Xin đừng nhấn ok trước khi bạn đã làm hết những điều này.',
	'msg_Finished'           => 'Hoàn thành chuyển đổi. Hãy dán nội dung trong clipboard vào cửa sổ soạn thảo wiki.',
	'msg_NoDocumentLoaded'   => 'Không có tài liệu nào được mở.',
	'msg_LoadDocument'       => 'Xin hãy mở tài liệu để chuyển đổi.',
	'msg_CloseAll'           => 'Xin hãy đóng tất cả các tài liệu trừ cái bạn muốn chuyển đổi! Bây giờ macro sẽ dừng.',
	'msg_SimpleTab'          => 'Đơn giản',
	'msg_ArticleName'        => 'Tên Bài',
	'msg_TargetWikiFrame'    => 'Wiki Đích',
	'msg_TestSystem'         => 'Hệ thống Kiểm thử',
	'msg_ProdSystem'         => 'Hệ thống Sinh',
	'msg_CheckURL'           => 'Kiểm tra URL',
	'msg_CategoryFrame'      => 'Thể loại',
	'msg_MultipleCats'       => 'Phân cách các thể loại bằng dấu phẩy (,)',
	'msg_ArticleCategory'    => 'Thể loại Bài viết',
	'msg_ImageCategory'      => 'Thể loại Hình',
	'msg_ImageDescription'   => 'Bổ sung miêu tả hình ảnh',
	'msg_ImageHandlingFrame' => 'Xử lý Hình ảnh',
	'msg_ImageExtraction'    => 'Tách Hình',
	'msg_ImageUseMSPhoto'    => 'Dán vào MS Photo Editor như một hình mới',
	'msg_ImageUpdate'        => 'Cập nhật tập tin hình hiện tại',
	'msg_ImageUpload'        => 'Tải Hình',
);

