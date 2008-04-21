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
	"WikiSearchTitle"       => "Search -",
	"WikiCategoryKeyWord"   => "category",
	"CategoryImagePreFix"   => "Images ",
	"WikiUploadTitle"       => "Upload",
	"ClickChartText"        => "click me!",
	"UnableToConvertMarker" => "## Error converting ##: ",
	"txt_TitlePage"         => "Title page",
	"txt_PageHeader"        => "Page header",
	"txt_PageFooter"        => "Page footer",
	"txt_Footnote"          => "Footnotes",
	"Msg_Upload_Info"       => "Now the image file upload will begin. Before you start you need to set your browser right:\r\n1. Close all sidebars like favorites.\r\n2. Sign in into your wiki.\r\n\Do not click ok before you checked this.",
	"Msg_Finished"          => "Converting finished. Paste your clipboard contents into your wiki editor.",
	"Msg_NoDocumentLoaded"  => "No document was loaded.",
	"Msg_LoadDocument"      => "Please load the document to convert.",
	"Msg_CloseAll"          => "Please close all documents but the one you want to convert! The macro will stop now."
);

/**  Message documentation */
$messages['qqq'] = array(
	'WikiSearchTitle'       => 'Title of browser window after search in wiki',
	'WikiCategoryKeyWord'   => 'category key word according to your language, "category" works always',
	'CategoryImagePreFix'   => 'Standard Prefix to the image category, add blank at last character to separate words',
	'WikiUploadTitle'       => 'Title of browser window when uploading to wiki',
	'ClickChartText'        => 'clickable charts will have additional text in wiki: "click me!"',
	'UnableToConvertMarker' => 'some links can not be converted, give hint text',
	'txt_TitlePage'         => 'Text to go in place of title page',
	'txt_PageHeader'        => 'Text to be inserted in place of page header',
	'txt_PageFooter'        => 'Text to be inserted in place of page footer',
	'txt_Footnote'          => 'Text to be inserted for footnotes',
	'Msg_Upload_Info'       => 'Text asking the user to log into their wiki and close any sidebars in their browser before clicking OK',
	'Msg_Finished'          => 'Text indicating that conversion is finished and that text can now be pasted into the wiki editor.',
	'Msg_NoDocumentLoaded'  => 'Text indicating that no document was loaded',
	'Msg_LoadDocument'      => 'Text asking the user to please load a document to convert',
	'Msg_CloseAll'          => 'Text asking the user to close other documents aside from the one they want to convert',
);

/** Deutsch (Deutsch) */
$messages['de'] = array(
	'WikiSearchTitle'       => 'Suchergebnisse -',
	'WikiCategoryKeyWord'   => 'Kategorie',
	'CategoryImagePreFix'   => 'Bilder',
	'WikiUploadTitle'       => 'Hochladen',
	'ClickChartText'        => 'Klick mich!',
	'UnableToConvertMarker' => '## Fehler bei Konvertierung ##:',
	'txt_TitlePage'         => 'Titelblatt',
	'txt_PageHeader'        => 'Kopfzeile',
	'txt_PageFooter'        => 'Fußzeile',
	'txt_Footnote'          => 'Fußnoten',
	'Msg_Upload_Info'       => 'Jetzt werden die Bilder hochgeladen. Vorher muss der Browser korrekt eingestellt sein, damit es funktioniert:
1. Schließen Sie alle Seitenleisten wie z.B. die Favoriten.
2. Melden Sie sich an Ihrem Wiki an.
Klicken Sie erst OK wenn Sie dies durchgeführt haben.',
	'Msg_Finished'          => 'Konvertierung beendet. Fügen Sie die Daten aus der Zwischenablage in Ihr Wiki ein.',
	'Msg_NoDocumentLoaded'  => 'Es wurde kein Dokument geladen.',
	'Msg_LoadDocument'      => 'Bitte laden Sie das zu konvertierende Dokument.',
	'Msg_CloseAll'          => 'Bitte schließen Sie alle Dokumente bis auf das, welches Sie konvertieren möchten! Das Makro wird jetzt beendet.',
);

/** Français (Français) */
$messages['fr'] = array(
	'WikiSearchTitle'       => 'Rechercher -',
	'WikiCategoryKeyWord'   => 'catégorie',
	'WikiUploadTitle'       => 'Télécharger',
	'ClickChartText'        => 'cliquez moi !',
	'UnableToConvertMarker' => '## Erreur de conversion de ##:',
	'txt_TitlePage'         => 'Page de titre',
	'txt_PageHeader'        => 'En-tête de page',
	'txt_PageFooter'        => 'Pied de page',
	'txt_Footnote'          => 'Notes de bas de page',
	'Msg_Upload_Info'       => "Maintenant le fichier image va être téléchargé. Avant de commencer, vous devez organiser correctement votre navigateur :
1. Fermez toutes les fenêtres latérales comme celle des favoris.
2. Identifiez vous dans votre wiki.
Ne cliquez pas sur OK avant d'avoir vérifé tout cela.",
	'Msg_Finished'          => "Conversion terminée. Collez le contenu du bloc-note dans l'éditeur du wiki.",
	'Msg_NoDocumentLoaded'  => "Aucun document n'a été chargé.",
	'Msg_LoadDocument'      => 'Veuillez charger le document à convertir.',
	'Msg_CloseAll'          => "Veuillez fermer tous les documents sauf celui que vous souhaitez convertir ! La macro va maintenant s'arrêter.",
);

/** Español (Español) */
$messages['es'] = array(
	'WikiSearchTitle'       => 'Buscar -',
	'WikiCategoryKeyWord'   => 'Categoría',
	'CategoryImagePreFix'   => 'Imagen',
	'WikiUploadTitle'       => 'Subir un Archivo',
	'ClickChartText'        => '¡Chascarme!',
	'UnableToConvertMarker' => '## Error de convertir ##:',
	'txt_TitlePage'         => 'Portada',
	'txt_PageHeader'        => 'Ecabezado',
	'txt_PageFooter'        => 'Pie de página',
	'txt_Footnote'          => 'Nota al pie',
	'Msg_Upload_Info'       => 'Ahora el upload del archivo de la imagen comenzará. Antes de que te comiences necesidad de fijar la tu derecha del browser:
1. Cerrar todo barras laterales como marcadores.
2. Registrarse a su wiki.
No tecleo acepter antes de comprobar esto.',
	'Msg_Finished'          => 'Convertier Listo. Pegar su contentido portapapeles en su editor de wiki.',
	'Msg_NoDocumentLoaded'  => 'No se cargó ningún documento.',
	'Msg_LoadDocument'      => 'Cargar por favor el documento para convertir.',
	'Msg_CloseAll'          => '¡Cerrar por favor todos los documentos pero el que deseas convertir! La macro ahora parará.',
);

/** Nederlands (Nederlands) */
$messages['nl'] = array(
	'WikiSearchTitle'       => 'Zoeken -',
	'WikiCategoryKeyWord'   => 'categorie',
	'CategoryImagePreFix'   => 'Afbeelding ',
	'ClickChartText'        => 'klik mij!',
	'UnableToConvertMarker' => '## converteer Error ##: ',
	'txt_TitlePage'         => 'Hoofdpagina',
	'txt_PageHeader'        => 'Koptekst',
	'txt_PageFooter'        => 'Voettekst',
	'txt_Footnote'          => 'Voetnoten',
	'Msg_Upload_Info'       => 'Nu zal de afbeelding upload beginnen. Voordat je begint moet je je internet browser goed zetten:
1. SLuit alle zijbalken zoals bladwijzers of favorieten.
2. Meld je aan bij je wiki.
Klik niet op ok voordat je dit gecontroleerd hebt.',
	'Msg_Finished'          => 'Converteren klaar. Plak de klembord inhoud in je wiki editor.',
	'Msg_NoDocumentLoaded'  => 'Het document is niet geladen.',
	'Msg_LoadDocument'      => 'AUB laad de documenten om te converteren.',
	'Msg_CloseAll'          => 'AUB sluit alle documenten behalve degene die je wilt converteren! De macro zal nu stoppen.',
);

/** Português (Português) */
$messages['pt'] = array(
	'WikiSearchTitle'       => 'Buscar resultados -',
	'WikiCategoryKeyWord'   => 'Categoria',
	'CategoryImagePreFix'   => 'Imagens de',
	'WikiUploadTitle'       => 'Carregar arquivo',
	'ClickChartText'        => 'clique aqui',
	'UnableToConvertMarker' => '## Erro de conversão ##:',
	'txt_TitlePage'         => 'Página de título',
	'txt_PageHeader'        => 'Cabeçalho da página',
	'txt_PageFooter'        => 'Rodapé da página',
	'txt_Footnote'          => 'Notas de rodapé',
	'Msg_Upload_Info'       => 'Agora o carregamento de imagens vai começar. Primeiro você precisa ajustar o seu navegador:
1. Feche todas as barras laterais (por exemplo, favoritos).
2. Autentique-se na sua wiki.
Não aperte OK antes de ter feito isto.',
	'Msg_Finished'          => 'Conversão concluída. Cole o conteúdo da área de transferência no editor da sua wiki.',
	'Msg_NoDocumentLoaded'  => 'Não foi carregado nenhum documento.',
	'Msg_LoadDocument'      => 'Por favor carregue o documento a converter.',
	'Msg_CloseAll'          => 'Por favor feche todos os documentos menos aquele que você quer converter. A macro vai parar agora.',
);

/** Русский (Русский) */
$messages['ru'] = array(
	'WikiSearchTitle'      => 'Результаты поиска -',
	'WikiCategoryKeyWord'  => 'Категории -',
	'WikiUploadTitle'      => 'Загрузить файл -',
	'txt_TitlePage'        => 'Заглавная страница',
	'txt_PageHeader'       => 'Заголовок страницы',
	'txt_Footnote'         => 'Примечания',
	'Msg_Upload_Info'      => 'Сейчас начнется загрузка сообщений. Перед началом правильно настройте окно проводника Интернет:
1. Закройте все панели вроде Закладки.
2. Войдите под своим логином в Wiki.
Не нажимайте ОК пока не сделаете это!',
	'Msg_Finished'         => 'Конвертация завершена.Вставте текст из буфера обмена в редактор WiKi',
	'Msg_NoDocumentLoaded' => 'Документы не загружены.',
	'Msg_LoadDocument'     => 'Пожалуйста загрузите документ в конвертер.',
	'Msg_CloseAll'         => 'Пожалуйста закройте все документы кроме того который нужно сконвертировать! Макрос остановлен.',
);
