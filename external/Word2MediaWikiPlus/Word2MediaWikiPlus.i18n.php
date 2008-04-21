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
	'wikiSearchTitle'       => 'Search -',
	'wikiCategoryKeyWord'   => 'category',
	'categoryImagePreFix'   => 'Images ',
	'wikiUploadTitle'       => 'Upload',
	'clickChartText'        => 'click me!',
	'unableToConvertMarker' => '## Error converting ##: ',
	'txt_TitlePage'         => 'Title page',
	'txt_PageHeader'        => 'Page header',
	'txt_PageFooter'        => 'Page footer',
	'txt_Footnote'          => 'Footnotes',
	'msg_Upload_Info'       => 'Now the image file upload will begin. Before you start you need to set your browser right:\r\n1. Close all sidebars like favorites.\r\n2. Sign in into your wiki.\r\n\Do not click ok before you checked this.',
	'msg_Finished'          => 'Converting finished. Paste your clipboard contents into your wiki editor.',
	'msg_NoDocumentLoaded'  => 'No document was loaded.',
	'msg_LoadDocument'      => 'Please load the document to convert.',
	'msg_CloseAll'          => 'Please close all documents but the one you want to convert! The macro will stop now.'
);

/**  Message documentation */
$messages['qqq'] = array(
	'wikiSearchTitle'       => 'Title of browser window after search in wiki',
	'wikiCategoryKeyWord'   => 'category key word according to your language, "category" works always',
	'categoryImagePreFix'   => 'Standard Prefix to the image category, add blank at last character to separate words',
	'wikiUploadTitle'       => 'Title of browser window when uploading to wiki',
	'clickChartText'        => 'clickable charts will have additional text in wiki: "click me!"',
	'unableToConvertMarker' => 'some links can not be converted, give hint text',
	'txt_TitlePage'         => 'Text to go in place of title page',
	'txt_PageHeader'        => 'Text to be inserted in place of page header',
	'txt_PageFooter'        => 'Text to be inserted in place of page footer',
	'txt_Footnote'          => 'Text to be inserted for footnotes',
	'msg_Upload_Info'       => 'Text asking the user to log into their wiki and close any sidebars in their browser before clicking OK',
	'msg_Finished'          => 'Text indicating that conversion is finished and that text can now be pasted into the wiki editor.',
	'msg_NoDocumentLoaded'  => 'Text indicating that no document was loaded',
	'msg_LoadDocument'      => 'Text asking the user to please load a document to convert',
	'msg_CloseAll'          => 'Text asking the user to close other documents aside from the one they want to convert',
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
1. Schließen Sie alle Seitenleisten wie z.B. die Favoriten.
2. Melden Sie sich an Ihrem Wiki an.
Klicken Sie erst OK wenn Sie dies durchgeführt haben.',
	'msg_Finished'          => 'Konvertierung beendet. Fügen Sie die Daten aus der Zwischenablage in Ihr Wiki ein.',
	'msg_NoDocumentLoaded'  => 'Es wurde kein Dokument geladen.',
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

