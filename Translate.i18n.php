<?php
/**
 * Translations of Translate extension.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
 */
$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'translate' => 'Translate',
	'translate-desc' => '[[Special:Translate|Special page]] for translating Mediawiki and beyond',
	'translate-edit' => 'edit',
	'translate-talk' => 'talk',
	'translate-history' => 'history',

	'translate-task-view' => 'View all messages from',
	'translate-task-untranslated' => 'View all untranslated messages from',
	'translate-task-optional' => 'View optional messages from',
	'translate-task-review' => 'Review changes to',
	'translate-task-reviewall' => 'Review all translations in',
	'translate-task-export' => 'Export translations from',
	'translate-task-export-to-file' => 'Export translation to file from',
	'translate-task-export-as-po' => 'Export translation in Gettext format',

	'translate-page-no-such-language' => 'Specified language was invalid.',
	'translate-page-no-such-task'     => 'Specified task was invalid.',
	'translate-page-no-such-group'    => 'Specified group was invalid.',

	'translate-page-settings-legend' => 'Settings',
	'translate-page-task'     => 'I want to',
	'translate-page-group'    => 'Group',
	'translate-page-language' => 'Language',
	'translate-page-limit'    => 'Limit',
	'translate-page-limit-option' => '$1 {{PLURAL:$1|message|messages}} per page',
	'translate-submit'        => 'Fetch',

	'translate-page-navigation-legend' => 'Navigation',
	'translate-page-showing' => 'Showing messages from $1 to $2 of $3.',
	'translate-page-showing-all' => 'Showing $1 {{PLURAL:$1|message|messages}}.',
	'translate-page-showing-none' => 'No messages to show.',
	'translate-page-paging-links' => '[ $1 ] [ $2 ]',
	'translate-next' => 'Next page',
	'translate-prev' => 'Previous page',

	'translate-page-description-legend' => 'Information about the group',

	'translate-optional' => '(optional)',
	'translate-ignored' => '(ignored)',

	'translate-edit-definition' => 'Message definition',
	'translate-edit-contribute' => 'contribute',
	'translate-edit-no-information' => "''This message has no documentation. If you know where or how this message is used, you can help other translators by adding documentation to this message.''",
	'translate-edit-information' => 'Information about this message ($1)',
	'translate-edit-in-other-languages' => 'Message in other languages',
	'translate-edit-committed' => 'Current translation in software',
	'translate-edit-warnings' => 'Warnings about incomplete translations',

	'translate-magic-pagename' => 'Extended MediaWiki translation',
	'translate-magic-help' => 'You can translate special pages aliases, magic words, skin names and namespace names.

In magic words you need to include English translations or they stop working. Also leave the first item (0 or 1) as it is.

Special page aliases and magic words can have multiple translations. Translations are seperated by a comma (,). Skin names and namespaces can have only one translation.

In namespace translations <tt>$1 talk</tt> is special. <tt>$1</tt> is replaced with sitename (for example <tt>{{SITENAME}} talk</tt>. If it is not possible in your language to form valid expression without changing sitename, please contact a developer.

You need to be in the translators group to save changes. Changes are not saved until you click save button below.',
	'translate-magic-form' => 'Language: $1 Module: $2 $3',
	'translate-magic-submit' => 'Fetch',
	'translate-magic-cm-to-be' => 'To-be',
	'translate-magic-cm-current' => 'Current',
	'translate-magic-cm-original' => 'Original',
	'translate-magic-cm-fallback' => 'Fallback',

	'translate-magic-cm-comment' => 'Comment:',
	'translate-magic-cm-save' => 'Save',
	'translate-magic-cm-export' => 'Export',

	'translate-magic-cm-updatedusing' => 'Updated using Special:Magic',
	'translate-magic-cm-savefailed' => 'Save failed',

	'translate-magic-special' => 'Special page aliases',
	'translate-magic-words' => 'Magic words',
	'translate-magic-skin' => 'Skins name',
	'translate-magic-namespace' => 'Namespace names',

	'translationchanges' => 'Translation changes',
	'translationchanges-export' => 'export',
	'translationchanges-change' => '$1: $2 by $3',

	'translate-checks-parameters' => 'Following parameters are not used: <strong>$1</strong>',
	'translate-checks-balance' => 'There is uneven amount of parentheses: <strong>$1</strong>',
	'translate-checks-links' => 'Following links are problematic: <strong>$1</strong>',
	'translate-checks-xhtml' => 'Please replace the following tags with correct ones: <strong>$1</strong>',
	'translate-checks-plural' => 'Definition uses <nowiki>{{PLURAL:}}</nowiki> but translation does not.',
);

/** Message documentation (Message documentation)
 * @author Nike
 * @author Siebrand
 * @author SPQRobin
 */
$messages['qqq'] = array(
	'translate'                       => 'Part of the "Translate" extension. This message is the page title of the special page [[Special:Translate]].',
	'translate-page-no-such-language' => "Shown when someone requests a language that doesn't exists. [{{FULLURL:Special:Translate|language=}} Example].",
	'translate-page-no-such-task'     => "Shown when someone requests a task that doesn't exists. [{{FULLURL:Special:Translate|task=}} Example].",
	'translate-page-no-such-group'    => "Shown when someone requests a group that doesn't exists. [{{FULLURL:Special:Translate|group=}} Example].",
	'translate-edit-no-information'   => 'Message is used as a hint to translators that documentation for a message without documentation is needed.',
);

/** Afrikaans (Afrikaans)
 * @author SPQRobin
 */
$messages['af'] = array(
	'translate'                         => 'Vertaal',
	'translate-desc'                    => '[[Special:Translate|Spesiale bladsy]] vir vertaal van MediaWiki en meer',
	'translate-edit'                    => 'wysig',
	'translate-talk'                    => 'bespreking',
	'translate-history'                 => 'geskiedenis',
	'translate-task-view'               => 'alle boodskappe bekyk van',
	'translate-task-untranslated'       => 'alle onvertaalde boodskappe bekyk van',
	'translate-task-optional'           => 'opsionele boodskappe bekyk van',
	'translate-page-task'               => 'Ek wil',
	'translate-page-group'              => 'Groep',
	'translate-page-language'           => 'Taal',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|boodskap|boodskappe}} per bladsy',
	'translate-page-navigation-legend'  => 'Navigasie',
	'translate-page-showing-all'        => 'Wys $1 {{PLURAL:$1|boodskap|boodskappe}}.',
	'translate-page-showing-none'       => 'Geen boodskappe te wys.',
	'translate-next'                    => 'Volgende bladsy',
	'translate-prev'                    => 'Vorige bladsy',
	'translate-page-description-legend' => 'Inligting oor hierdie groep',
	'translate-optional'                => '(opsioneel)',
	'translate-edit-contribute'         => 'wysig',
	'translate-edit-information'        => 'Inligting oor hierdie boodskap ($1)',
	'translate-edit-in-other-languages' => 'Boodskap in andere tale',
	'translate-edit-committed'          => 'Huidige vertaling in sagteware',
	'translate-magic-form'              => 'Taal: $1 Module: $2 $3',
	'translate-magic-cm-current'        => 'Huidig',
	'translate-magic-cm-comment'        => 'Opmerking:',
	'translate-magic-cm-save'           => 'Stoor',
	'translate-magic-namespace'         => 'Naamruimtenamen',
);

/** Aragonese (Aragonés)
 * @author Juanpabl
 */
$messages['an'] = array(
	'translate'                         => 'Traduzir',
	'translate-edit'                    => 'editar',
	'translate-talk'                    => 'descutir',
	'translate-history'                 => 'istorial',
	'translate-task-view'               => 'Beyer toz os mensaches de',
	'translate-task-untranslated'       => 'Beyer toz os mensaches sin traduzir de',
	'translate-task-optional'           => 'Beyer os mensaches opzionals de',
	'translate-task-review'             => 'Rebisar cambeos en',
	'translate-task-reviewall'          => 'Rebisar todas as traduzions en',
	'translate-task-export'             => 'Esportar traduzions de',
	'translate-task-export-to-file'     => 'Esportar á un archibo as traduzions de',
	'translate-task-export-as-po'       => 'Esportar traduzión en formato Gettext',
	'translate-page-no-such-language'   => 'O codigo de idioma furnito no ye balido',
	'translate-page-no-such-task'       => 'A faina espezificata no ye correuta.',
	'translate-page-no-such-group'      => 'A colla de mensaches espezificata no ye correuta.',
	'translate-page-settings-legend'    => 'Achustes',
	'translate-page-task'               => 'Quiero',
	'translate-page-group'              => 'Colla',
	'translate-page-language'           => 'Luenga',
	'translate-page-limit'              => 'Limite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mensache|mensaches}} por pachina',
	'translate-submit'                  => 'Ir á escar-los',
	'translate-page-navigation-legend'  => 'Nabegazión',
	'translate-page-showing'            => "Amostrando os mensaches $1 á $2 d'un total de $3.",
	'translate-page-showing-all'        => 'Amostrando $1 {{PLURAL:$1|mensache|mensaches}}.',
	'translate-page-showing-none'       => 'No bi ha garra mensache ta amostrar.',
	'translate-next'                    => 'Pachina siguient',
	'translate-prev'                    => 'Pachina anterior',
	'translate-page-description-legend' => 'Informazión sobre a colla de mensaches',
	'translate-optional'                => '(opzional)',
	'translate-ignored'                 => '(no considerato)',
	'translate-magic-pagename'          => 'Traduzión ixamplata de MediaWiki',
	'translate-magic-help'              => "Puede traduzir os \"alias\" d'as pachinas espezials, as palabras machicas, os nombres d'as aparenzias y os espazios de nombres.

En as palabras machicas, ha d'encluyir a traduzión en anglés, porque si no lo fa, no funzionarán bien. Deixe tamién o primer elemento (0 u 1) sin cambiar.

Os alias d'as pachinas espezials y as parabras machicas pueden tener barias traduzions. As traduzions se deseparan por una coma (,). Os nombres d'as aparenzias y d'os espazios de nombres no pueden tener que una unica traduzión.

En as traduzions d'os espazios de nombres <tt>\$1 talk</tt> ye espezial. <tt>\$1</tt> ye escambiata por o nombre d'o sitio (por exemplo <tt>{{SITENAME}} talk</tt>). Si no ye posible en a suya luenga formar una esprisión correuta sin cambiar o nombre d'o sitio, contaute con un programador.

Ha de pertenexer á la colla de tradutors ta alzar os cambeos. Ístos no quedan rechistratos dica que no se puncha en o botón \"Alzar pachina\" que ye en o cobaxo d'a pachina.",
	'translate-magic-form'              => 'Luenga: $1 Modulo: $2 $3',
	'translate-magic-submit'            => 'Ir á escar',
	'translate-magic-cm-to-be'          => 'Esdebiene',
	'translate-magic-cm-current'        => 'Autual',
	'translate-magic-cm-original'       => 'Orichinal',
	'translate-magic-cm-fallback'       => "Luenga d'aduya",
	'translate-magic-cm-save'           => 'Alzar',
	'translate-magic-cm-export'         => 'Esportar',
	'translate-magic-cm-updatedusing'   => 'Esbiellato usando Special:Magic',
	'translate-magic-cm-savefailed'     => 'No se podió alzar a pachina',
	'translate-magic-special'           => 'Alias de pachinas espezials',
	'translate-magic-words'             => 'Parabras machicas',
	'translate-magic-skin'              => "Nombres d'aparenzias",
	'translate-magic-namespace'         => 'Espazios de nombres',
	'translationchanges'                => 'Cambeos en a traduzión',
	'translationchanges-export'         => 'esportar',
	'translationchanges-change'         => '$1: $2 por $3',
);

$messages['ang'] = array(
	'translate-edit' => 'ādihtan',
	'translate-talk' => 'mōtung',
	'translate-history' => 'stǣr',
);

/** Arabic (العربية)
 * @author Meno25
 * @author Siebrand
 */
$messages['ar'] = array(
	'translate'                         => 'ترجمة',
	'translate-desc'                    => '[[Special:Translate|صفحة خاصة]] لترجمة الميدياويكي وما بعده',
	'translate-edit'                    => 'عدل',
	'translate-talk'                    => 'نقاش',
	'translate-history'                 => 'تاريخ',
	'translate-task-view'               => 'عرض كل الرسائل من',
	'translate-task-untranslated'       => 'عرض كل الرسائل غير المترجمة من',
	'translate-task-optional'           => 'اعرض الرسائل الاختيارية من',
	'translate-task-review'             => 'عرض التغييرات ل',
	'translate-task-reviewall'          => 'عرض كل الترجمات في',
	'translate-task-export'             => 'صدر الترجمات من',
	'translate-task-export-to-file'     => 'صدر الترجمة لملف من',
	'translate-task-export-as-po'       => 'صدر الترجمة بصيغة جت تكست',
	'translate-page-no-such-language'   => 'كود لغة غير صحيح تم توفيره',
	'translate-page-no-such-task'       => 'المهمة المحددة كانت غير صحيحة.',
	'translate-page-no-such-group'      => 'المجموعة المحددة كانت غير صحيحة.',
	'translate-page-settings-legend'    => 'الإعدادات',
	'translate-page-task'               => 'أريد',
	'translate-page-group'              => 'المجموعة',
	'translate-page-language'           => 'اللغة',
	'translate-page-limit'              => 'الحد',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|رسالة|رسالة}} للصفحة',
	'translate-submit'                  => 'إيجاد',
	'translate-page-navigation-legend'  => 'الإبحار',
	'translate-page-showing'            => 'عرض الرسائل من $1 إلى $2 ل $3.',
	'translate-page-showing-all'        => 'عرض $1 {{PLURAL:$1|رسالة|رسالة}}.',
	'translate-page-showing-none'       => 'لا رسائل للعرض.',
	'translate-next'                    => 'الصفحة التالية',
	'translate-prev'                    => 'الصفحة السابقة',
	'translate-page-description-legend' => 'معلومات حول المجموعة',
	'translate-optional'                => '(اختياري)',
	'translate-ignored'                 => '(متجاهل)',
	'translate-edit-definition'         => 'تعريف الرسالة',
	'translate-edit-contribute'         => 'ساهم',
	'translate-edit-no-information'     => "''هذه الرسالة ليس لديها توثيق. لو كنت تعرف أين أو كيف يتم استخدام هذه الرسالة، يمكنك مساعدة المترجمين الآخرين بواسطة إضافة توثيق إلى هذه الرسالة.''",
	'translate-edit-information'        => 'معلومات حول هذه الرسالة ($1)',
	'translate-edit-in-other-languages' => 'الرسالة بلغات أخرى',
	'translate-edit-committed'          => 'الترجمة الحالية في البرنامج',
	'translate-edit-warnings'           => 'التحذيرات حول الترجمات غير المكتملة',
	'translate-magic-pagename'          => 'ترجمة الميدياويكي الممتدة',
	'translate-magic-help'              => 'يمكنك ترجمة أسماء الصفحات الخاصة، الكلمات السحرية، أسماء الواجهات وأسماء النطاقات.

في الكلمات السحرية تحتاج إلى إضافة الترجمة الإنجليزية وإلا فإنها ستتوقف عن العمل. أيضا اترك المدخل الأول (0 أو 1) كما هو.

أسماء الصفحات الخاصة والكلمات السحرية يمكن أن يكون لهم ترجمات متعددة. الترجمات مفصولة بفاصلة(,). أسماء الواجهات والنطاقات يمكن أن يكون لها ترجمة واحدة.

في ترجمة النطاقات <tt>$1 talk</tt> خاص. <tt>$1</tt> تستبدل باسم الموقع (على سبيل المثال <tt>{{SITENAME}} talk</tt>. لو أنه من غير الممكن في لغتك صياغة تعبير صحيح بدون تغيير اسم الموقع، من فضلك اتصل بمطور.

تحتاج إلى أن تكون في مجموعة المترجمين لحفظ التغييرات. التغييرات لن يتم حفظها حتى على زر الحفظ بالأسفل.',
	'translate-magic-form'              => 'اللغة: $1 القالب: $2 $3',
	'translate-magic-submit'            => 'إيجاد',
	'translate-magic-cm-to-be'          => 'لتصبح',
	'translate-magic-cm-current'        => 'الحالي',
	'translate-magic-cm-original'       => 'الأصلي',
	'translate-magic-cm-fallback'       => 'المراجعة',
	'translate-magic-cm-comment'        => 'تعليق:',
	'translate-magic-cm-save'           => 'حفظ',
	'translate-magic-cm-export'         => 'تصدير',
	'translate-magic-cm-updatedusing'   => 'حدث باستخدام Special:Magic',
	'translate-magic-cm-savefailed'     => 'الحفظ فشل',
	'translate-magic-special'           => 'أسماء الصفحات الخاصة',
	'translate-magic-words'             => 'كلمات سحرية',
	'translate-magic-skin'              => 'أسماء الواجهات',
	'translate-magic-namespace'         => 'أسماء النطاقات',
	'translationchanges'                => 'تغييرات الترجمة',
	'translationchanges-export'         => 'تصدير',
	'translationchanges-change'         => '$1: $2 بواسطة $3',
	'translate-checks-parameters'       => 'المحددات التالية غير مستخدمة: <strong>$1</strong>',
	'translate-checks-balance'          => 'يوجد عدد غير زوجي من الأقواس: <strong>$1</strong>',
	'translate-checks-links'            => 'الوصلات التالية بها مشاكل: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'من فضلك استبدل الوسوم التالية بالبدائل الصحيحة: <strong>$1</strong>',
	'translate-checks-plural'           => 'التعريف يستخدم <nowiki>{{PLURAL:}}</nowiki> لكن الترجمة لا.',
);

/** Araucanian (Mapudungun)
 * @author Poquil
 */
$messages['arn'] = array(
	'translate-talk'                => 'dungun',
	'translate-page-showing'        => 'adkintun mensajes del $1 al $2 de $3',
	'translate-page-showing-all'    => 'adkintun $1 {{PLURAL:$1|message|messages}}.',
	'translate-edit-contribute'     => 'ñma',
	'translate-magic-cm-original'   => 'kuse',
	'translate-magic-cm-save'       => 'elkünun',
	'translate-magic-cm-savefailed' => 'elkünun weda',
);

/** Asturian (Asturianu)
 * @author Esbardu
 */
$messages['ast'] = array(
	'translate'                         => 'Traducir',
	'translate-desc'                    => '[[Special:Translate|Páxina especial]] pa traducir Mediawiki y más',
	'translate-edit'                    => 'editar',
	'translate-talk'                    => 'alderique',
	'translate-history'                 => 'historial',
	'translate-task-view'               => 'Ver tolos mensaxes del',
	'translate-task-untranslated'       => 'Ver tolos mensaxes non traducíos del',
	'translate-task-optional'           => 'Ver los mensaxes opcionales del',
	'translate-task-review'             => 'Revisar los cambeos nel',
	'translate-task-reviewall'          => 'Revisar toles traducciones del',
	'translate-task-export'             => 'Esportar les traducciones del',
	'translate-task-export-to-file'     => 'Esportar a un archivu les traducciones del',
	'translate-task-export-as-po'       => 'Esportar les traducciones en formatu Gettext',
	'translate-page-no-such-language'   => 'La llingua especificada nun foi válida.',
	'translate-page-no-such-task'       => 'El llabor especificáu nun foi válidu.',
	'translate-page-no-such-group'      => 'El grupu especificáu nun foi válidu.',
	'translate-page-settings-legend'    => 'Configuración',
	'translate-page-task'               => 'Quiero',
	'translate-page-group'              => 'Grupu',
	'translate-page-language'           => 'Llingua',
	'translate-page-limit'              => 'Llímite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mensaxe|mensaxes}} per páxina',
	'translate-submit'                  => 'Amosar',
	'translate-page-navigation-legend'  => 'Navegación',
	'translate-page-showing'            => 'Amosando mensaxes del $1 al $2 de $3.',
	'translate-page-showing-all'        => 'Amosando $1 {{PLURAL:$1|mensaxe|mensaxes}}.',
	'translate-page-showing-none'       => "Nun hai mensaxes qu'amosar.",
	'translate-next'                    => 'Páxina siguiente',
	'translate-prev'                    => 'Páxina anterior',
	'translate-page-description-legend' => 'Información del grupu',
	'translate-optional'                => '(opcional)',
	'translate-ignored'                 => '(inoráu)',
	'translate-edit-definition'         => 'Definición del mensaxe',
	'translate-edit-contribute'         => 'contribuyir',
	'translate-edit-no-information'     => "''Esti mensaxe nun tien documentación. Si sabes ú o cómo s'usa esti mensaxe, pues aidar a otros traductores amestando documentación a esti mensaxe.''",
	'translate-edit-information'        => 'Información sobre esti mensaxe ($1)',
	'translate-edit-in-other-languages' => "Mensaxe n'otres llingües",
	'translate-edit-committed'          => 'Traducción actual nel software',
	'translate-edit-warnings'           => 'Avisos sobre traducciones incompletes',
	'translate-magic-pagename'          => 'Traducción estendida de MediaWiki',
	'translate-magic-help'              => "Pues traducir los nomes de les páxines especiales, les pallabres máxiques, los nomes de les pieles y los nomes de los espacios de nome.

Nes pallabres máxiques necesites incluyir les traducciones ingleses, o dexarán de furrular. Dexa tamién el primer elementu (0 ó 1) como ta.

Los nomes de les páxines especiales y les pallabres máxiques puen tener múltiples traducciones. Les traducciones sepárense con una coma (,). Los nomes de les pieles y los espacios de nome namái puen tener una traducción.

Nes traducciones de los espacios de nome <tt>$1 talk</tt> ye especial <tt>$1</tt> ye sustituyíu pol nome del sitiu (por exemplu <tt>{{SITENAME}} talk</tt>). Si na to llingua nun ye posible formar una espresión válida ensin camudar el nome del sitiu, por favor contauta con un desenrollador.

Necesites tar nel grupu de traductores pa guardar los cambeos. Los cambeos nun se graben hasta que calques nel botón guardar d'abaxo.",
	'translate-magic-form'              => 'Llingua: $1 Módulu: $2 $3',
	'translate-magic-submit'            => 'Amosar',
	'translate-magic-cm-to-be'          => 'Propuesta',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Orixinal',
	'translate-magic-cm-fallback'       => 'Llingua por defeutu',
	'translate-magic-cm-comment'        => 'Comentariu:',
	'translate-magic-cm-save'           => 'Guardar',
	'translate-magic-cm-export'         => 'Esportar',
	'translate-magic-cm-updatedusing'   => 'Actualizao usando Special:Magic',
	'translate-magic-cm-savefailed'     => "Falló'l guardáu",
	'translate-magic-special'           => 'Nomes de páxines especiales',
	'translate-magic-words'             => 'Pallabres máxiques',
	'translate-magic-skin'              => 'Nomes de pieles',
	'translate-magic-namespace'         => "Nomes d'espacios de nome",
	'translationchanges'                => 'Cambeos de traducción',
	'translationchanges-export'         => 'esportar',
	'translationchanges-change'         => '$1: $2 por $3',
	'translate-checks-parameters'       => "Los siguientes parámetros nun s'usen: <strong>$1</strong>",
	'translate-checks-balance'          => 'Hai un númberu impar de paréntesis: <strong>$1</strong>',
	'translate-checks-links'            => 'Los siguientes enllaces son problemáticos: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Por favor sustitúi les siguientes etiquetes coles correutes: <strong>$1</strong>',
	'translate-checks-plural'           => 'La definición usa <nowiki>{{PLURAL:}}</nowiki> pero la traducción non.',
);

$messages['bcl'] = array(
	'translate' => 'Sangliân',
	'translate-edit' => 'hirahón',
	'translate-talk' => 'magtaram',
	'translate-history' => 'historya',
	'translate-task-view' => 'Hilingón an gabos na mga mensahe poon',
	'translate-task-untranslated' => 'Hilingón an gabos na mga dai nasangliân na mensahe poon',
	'translate-task-review' => 'Reparohon an mga pagbabâgo sa',
	'translate-task-reviewall' => 'Reparohon an gabos na mga pagsanglî sa',
	'translate-task-export' => 'Ipadara an mga pagsanglî halî sa',
	'translate-task-export-to-file' => 'Ipadara an pagsanglî sa file halî sa',
	'translate-settings' => 'Gusto kong $1 $2 sa tataramon na $3 limitado sa $4. $5',
	'translate-paging' => '<div>Ipinapahiling an mga mensahe poon $1 hasta $2 kan $3. [ $4 | $5 ]</div>',
	'translate-submit' => 'Kûanón',
	'translate-next' => 'Sunod na páhina',
	'translate-prev' => 'Nakaaging páhina',
	'translate-optional' => '(opsyonal)',
	'translate-edit-message-format' => 'b>$1</b> an format kaining mensahe.',
	'translate-magic-form' => 'Tataramon: $1 Module: $2 $3',
	'translate-magic-submit' => 'Kûanón',
	'translate-magic-cm-current' => 'Presente',
	'translate-magic-cm-original' => 'Orihinal',
	'translate-magic-cm-save' => 'Itagama',
	'translate-magic-cm-export' => 'Ipadara',
	'translate-magic-cm-savefailed' => 'Bigô an pagtagama',
);

/** Bulgarian (Български)
 * @author DCLXVI
 * @author Siebrand
 */
$messages['bg'] = array(
	'translate'                         => 'Превеждане',
	'translate-desc'                    => '[[Special:Translate|Специална страница]] за превеждане на Mediawiki и др.',
	'translate-edit'                    => 'редактиране',
	'translate-talk'                    => 'беседа',
	'translate-history'                 => 'история',
	'translate-task-view'               => 'Преглед на всички съобщения от',
	'translate-task-untranslated'       => 'Преглед на всички непреведени съобщения от',
	'translate-task-optional'           => 'Преглед на незадължителните съобщения от',
	'translate-task-review'             => 'Преглед на променените съобщения в',
	'translate-task-reviewall'          => 'Преглед на всички преводи в',
	'translate-task-export'             => 'Изнасяне на преводите от',
	'translate-task-export-to-file'     => 'Изнасяне във файл на преведените съобщения от',
	'translate-task-export-as-po'       => 'Изнасяне на превода в Gettext формат',
	'translate-page-no-such-language'   => 'Избраният език е невалиден.',
	'translate-page-no-such-task'       => 'Избраната задача е невалидна.',
	'translate-page-no-such-group'      => 'Избраната група е невалидна',
	'translate-page-settings-legend'    => 'Настройки',
	'translate-page-task'               => 'Действие:',
	'translate-page-group'              => 'Група:',
	'translate-page-language'           => 'Език:',
	'translate-page-limit'              => 'Показване на:',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|съобщение|съобщения}} на страница',
	'translate-submit'                  => 'Извличане',
	'translate-page-navigation-legend'  => 'Навигация',
	'translate-page-showing'            => 'Показани са съобщения от $1 до $2 от общо $3.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Показано е 1 съобщение|Показани са $1 съобщения}}.',
	'translate-page-showing-none'       => 'Няма съобщения, които да бъдат показани.',
	'translate-next'                    => 'Следваща страница',
	'translate-prev'                    => 'Предишна страница',
	'translate-page-description-legend' => 'Информация за групата',
	'translate-optional'                => '(незадължително)',
	'translate-ignored'                 => '(пренебрегнато)',
	'translate-edit-definition'         => 'Оригинално съобщение',
	'translate-edit-contribute'         => 'добавяне на документация',
	'translate-edit-no-information'     => 'За това съобщение няма документация. Ако знаете къде и как се използва, можете да помогнете на останалите преводачи като добавите документация за това съобщение.',
	'translate-edit-information'        => 'Информация за това съобщение ($1)',
	'translate-edit-in-other-languages' => 'Това съобщение на други езици',
	'translate-edit-committed'          => 'Текущ превод в софтуера',
	'translate-edit-warnings'           => 'Забележки за непълни преводи',
	'translate-magic-pagename'          => 'Разширено превеждане на МедияУики',
	'translate-magic-form'              => 'Език: $1 Модул: $2 $3',
	'translate-magic-submit'            => 'Извличане',
	'translate-magic-cm-to-be'          => 'Желано',
	'translate-magic-cm-current'        => 'Текущо',
	'translate-magic-cm-original'       => 'Оригинално',
	'translate-magic-cm-comment'        => 'Коментар:',
	'translate-magic-cm-save'           => 'Съхранение',
	'translate-magic-cm-export'         => 'Изнасяне',
	'translate-magic-cm-updatedusing'   => 'Обновено чрез Special:Magic',
	'translate-magic-cm-savefailed'     => 'Съхраняването беше неуспешно',
	'translate-magic-words'             => 'Вълшебни думички',
	'translate-magic-skin'              => 'Имена на облици',
	'translate-magic-namespace'         => 'Имена на именни пространства',
	'translationchanges'                => 'Промени в преводите',
	'translationchanges-export'         => 'изнасяне',
	'translationchanges-change'         => '$1: $2 от $3',
	'translate-checks-parameters'       => 'Следните параметри не се използват: <strong>$1</strong>',
	'translate-checks-balance'          => 'Съобщението съдържа необичаен брой скоби: <strong>$1</strong>',
	'translate-checks-links'            => 'Следните препратки са проблемни: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Необходимо е заместване на посочените етикети с правилни: <strong>$1</strong>',
	'translate-checks-plural'           => 'Оригиналното съобщение използва <nowiki>{{PLURAL:}}</nowiki>, а преводът — не.',
);

/** Bengali (বাংলা)
 * @author Bellayet
 * @author Zaheen
 */
$messages['bn'] = array(
	'translate'                     => 'অনুবাদ করুন',
	'translate-edit'                => 'সম্পাদনা',
	'translate-talk'                => 'আলোচনা',
	'translate-history'             => 'ইতিহাস',
	'translate-task-view'           => 'সমস্ত বার্তা',
	'translate-task-untranslated'   => 'অনুবাদ হয়নি এমন সব বার্তা',
	'translate-task-review'         => 'পরিবর্তনসমূহ পুনর্বিবেচনা',
	'translate-task-reviewall'      => 'সমস্ত অনুবাদ পুনর্বিবেচনা',
	'translate-task-export'         => 'অনুবাদসমুহ প্রেরণ',
	'translate-task-export-to-file' => 'অনুবাদসমূহ ফাইলে প্রেরণ',
	'translate-page-task'           => 'আমি চাই',
	'translate-submit'              => 'বের করো',
	'translate-next'                => 'পরবর্তী পাতা',
	'translate-prev'                => 'পূর্ববর্তী পাতা',
	'translate-optional'            => '(ঐচ্ছিক)',
	'translate-ignored'             => '(উপেক্ষিত)',
);

$messages['bpy'] = array(
	'translate' => 'অনুবাদ করিক',
);

/** Breton (Brezhoneg)
 * @author Fulup
 */
$messages['br'] = array(
	'translate'                         => 'Treiñ',
	'translate-desc'                    => "[[Special:Translate|Pajenn zibar]] evit treiñ Mediawiki ha pelloc'h",
	'translate-edit'                    => 'kemmañ',
	'translate-talk'                    => 'kaozeal',
	'translate-history'                 => 'istor',
	'translate-task-view'               => 'Welet an holl gemennadennoù evit',
	'translate-task-untranslated'       => 'Welet an holl gemennadennoù didro evit',
	'translate-task-optional'           => 'Welet an holl gemennadennoù diret evit',
	'translate-task-review'             => "Adwelet ma c'hemmoù evit",
	'translate-task-reviewall'          => 'Adwelet an holl droidigezhioù evit',
	'translate-task-export'             => 'Ezporzhiañ an troidigezhioù evit',
	'translate-task-export-to-file'     => 'Ezporzhiañ an troidigezhioù en ur restr adal',
	'translate-task-export-as-po'       => 'Ezporzhiañ an troidigezhioù er furmad Gettext',
	'translate-page-no-such-language'   => "Merket ez eus bet ur c'hod yezh direizh",
	'translate-page-no-such-task'       => 'Merket ez eus bet un ober direizh',
	'translate-page-no-such-group'      => 'Merket ez eus bet ur strollad direizh',
	'translate-page-settings-legend'    => 'Dibaboù',
	'translate-page-task'               => "C'hoant am eus da",
	'translate-page-group'              => 'Strollad',
	'translate-page-language'           => 'Yezh',
	'translate-page-limit'              => 'Bevenn',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|gemennadenn|kemennadenn}} dre bajenn',
	'translate-submit'                  => 'Mont',
	'translate-page-navigation-legend'  => 'Merdeiñ',
	'translate-page-showing'            => 'O tiskouez kemennadennoù adal $1 betek $2 diwar $3.',
	'translate-page-showing-all'        => 'War wel $1 {{PLURAL:$1|gemennadenn|kemennadenn}}',
	'translate-page-showing-none'       => 'Netra da ziskouez.',
	'translate-next'                    => 'Pajenn da-heul',
	'translate-prev'                    => 'Pajenn gent',
	'translate-page-description-legend' => 'Titouroù diwar-benn ar strollad',
	'translate-optional'                => '(diret)',
	'translate-ignored'                 => '(laosket a-gostez)',
	'translate-edit-definition'         => 'Termenadur ar gemennadenn',
	'translate-edit-contribute'         => 'kemer perzh',
	'translate-edit-no-information'     => "''N'eus tamm titour ebet diwar-benn ar gemennadenn-mañ. Ma ouzit pelec'h pe benaos emañ da vezañ implijet e c'hallit harpañ troourien all en ur ouzhpennañ titouroù diwar he fenn.''",
	'translate-edit-information'        => 'Titouroù diwar-benn ar gemennadenn-mañ ($1)',
	'translate-edit-in-other-languages' => 'Kemennadenn e yezhoù all',
	'translate-edit-committed'          => 'Troidigezh zo er meziant bremañ',
	'translate-edit-warnings'           => 'Kemennoù diwall diwar-benn an troidigezhioù diglok',
	'translate-magic-pagename'          => 'Troidigezh Mediawiki astennet',
	'translate-magic-help'              => "Gallout a rit treiñ aliasoù ar pajennoù dibar, ar gerioù burzhudus anvioù an etrefasoù hag anvioù an esaouennoù anv.

Evit ar pezh a sell ouzh ar gerioù burzhudus e vo ret deoc'h ouzhpennañ an droidigezh saoznek pe ne'z aint ket en-dro ken. Dalc'hit ivez an elfenn gentañ (0 pe 1) evel m'emañ.

Gallout a ra aliasoù ar pajennoù dibar hag ar gerioù burzhudus kaout meur a droidigezh. Dispartiet eo an troidigezhioù dre skejoù (,). N'hall anvioù an etrefasoù ha re an esaouennoù anv nemet kaout un droidigezh hepken.

E troidigezhioù an esaouennoù anv eo dibar <tt>$1 talk</tt>. Erlec'hiet eo <tt>$1</tt> gant anv al lec'hienn (da skouer <tt>{{SITENAME}} talk</tt>. Ma n'haller ket sevel lavarennoù reizh en ho yezh hep kemmañ anv al lec'hienn, kit e darempred gant un diorroer.

Ret eo deoc'h bezañ ezel eus ur strollad troourien evit enrollañ ar c'hemmoù. Ne vo ket enrollet ar c'hemmoù e-keit ha ne vo ket bet pouezet war ar bouton dindan.",
	'translate-magic-form'              => 'Yezh $1 Modulenn : $2 $3',
	'translate-magic-submit'            => 'Mont',
	'translate-magic-cm-to-be'          => 'A zeu da vezañ',
	'translate-magic-cm-current'        => 'Bremañ',
	'translate-magic-cm-original'       => 'Orin',
	'translate-magic-cm-fallback'       => 'Distreiñ',
	'translate-magic-cm-comment'        => 'Notenn :',
	'translate-magic-cm-save'           => 'Enrollañ',
	'translate-magic-cm-export'         => 'Ezporzhiañ',
	'translate-magic-cm-updatedusing'   => 'Hizivaet en ur implijout Special:Magic',
	'translate-magic-cm-savefailed'     => "C'hwitet enrollañ",
	'translate-magic-special'           => 'Aliasoù pajenn zibar',
	'translate-magic-words'             => 'Gerioù burzhudus',
	'translate-magic-skin'              => 'Anvioù an etrefasoù',
	'translate-magic-namespace'         => 'Anv an esaouennoù anv',
	'translationchanges'                => 'Troidigezhioù bet adwelet',
	'translationchanges-export'         => 'Ezporzhiañ',
	'translationchanges-change'         => '$1: $2 gant $3',
	'translate-checks-parameters'       => 'Ne vez ket graet gant an arventennoù da-heul : <strong>$1</strong>',
	'translate-checks-balance'          => 'Direizh eo an niver a grommelloù : <strong>$1</strong>',
	'translate-checks-links'            => 'Kudennek eo al liammoù da-heul : <strong>$1</strong>',
	'translate-checks-xhtml'            => "Erlec'hiit ar balizennoù da-heul gant ar re a zegouezh mar plij : <strong>$1</strong>",
	'translate-checks-plural'           => 'Ober a ra an termenadur gant <nowiki>{{PLURAL:}}</nowiki> padal an droidigezh ne ra ket.',
);

/** Catalan (Català)
 * @author SMP
 * @author Toniher
 */
$messages['ca'] = array(
	'translate'                         => 'Tradueix',
	'translate-desc'                    => '[[Special:Translate|Pàgina especial]] per a traduir el Mediawiki i altres coses',
	'translate-edit'                    => 'edita',
	'translate-talk'                    => 'discussió',
	'translate-history'                 => 'historial',
	'translate-task-view'               => 'veure tots els missatges de',
	'translate-task-untranslated'       => 'veure els missatges no traduïts de',
	'translate-task-optional'           => 'veure els missatges opcionals de',
	'translate-task-review'             => 'revisar els canvis a',
	'translate-task-reviewall'          => 'revisar les traduccions de',
	'translate-task-export'             => 'exportar les traduccions de',
	'translate-task-export-to-file'     => 'exportar a un fitxer de',
	'translate-task-export-as-po'       => 'exportar en format Gettext',
	'translate-page-no-such-language'   => 'La llengua especificada no és vàlida.',
	'translate-page-no-such-task'       => 'La tasca especificada no és vàlida.',
	'translate-page-no-such-group'      => 'El grup especificat no és vàlid.',
	'translate-page-settings-legend'    => 'Preferències',
	'translate-page-task'               => 'Vull',
	'translate-page-group'              => 'Grup',
	'translate-page-language'           => 'Llengua',
	'translate-page-limit'              => 'Límit',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|missatge|missatges}} per pàgina',
	'translate-submit'                  => 'Mostra',
	'translate-page-navigation-legend'  => 'Navegació',
	'translate-page-showing'            => 'Mostrant missatges del $1 al $2 de $3.',
	'translate-page-showing-all'        => 'Mostrant $1 {{PLURAL:$1|missatge|missatges}}.',
	'translate-page-showing-none'       => 'No hi ha missatges a mostrar.',
	'translate-next'                    => 'Pàgina següent',
	'translate-prev'                    => 'Pàgina anterior',
	'translate-page-description-legend' => 'Informació del grup',
	'translate-optional'                => '(opcional)',
	'translate-ignored'                 => '(ignorat)',
	'translate-edit-definition'         => 'Definició del missatge',
	'translate-edit-contribute'         => 'contribueix',
	'translate-edit-no-information'     => "''Aquest missatge no té documentació. Si sabeu on o com és usat aquest missatge podeu ajudar la resta de traductors afegint-hi la documentació.''",
	'translate-edit-information'        => 'Informació sobre el missatge ($1)',
	'translate-edit-in-other-languages' => 'Missatge en altres llengües',
	'translate-edit-committed'          => 'Traducció utilitzada actualment pel programa',
	'translate-edit-warnings'           => 'Avisos de traducció incompleta',
	'translate-magic-pagename'          => 'Traducció ampliada del MediaWiki',
	'translate-magic-help'              => "Aquí podeu traduir els noms de les pàgines especials, les paraules màgiques, els noms dels estils de pell (''skins'') i els títols dels diferents espais de noms (''namespaces'').

A les paraules màgiques cal que hi incloeu les traduccions en anglès per a que continuïn funcionant. També cal que deixeu el primer ítem (0 o 1) igual que a l'original.

Els títols de les pàgines especials i les paraules màgiques poden tenir múltiples traduccions. Separeu-les per una coma (,) i un espai. Els estils i els espais de noms només poden tenir una traducció.

Dins les traduccions dels espais de noms, la <tt>$1 talk</tt> és especial. <tt>$1</tt> es substitueix pel nom del projecte (per exemple <tt>{{SITENAME}} talk</tt>). Si no és possible fer-ho així en el vostre idioma sense canviar la forma gramatical del nom del projecte, contacteu amb un programador.

Heu de tenir permisos de traductor per a desar els canvis, que no es guardaran fins que no cliqueu el botó corresponent.",
	'translate-magic-form'              => 'Llengua: $1 Mòdul: $2 $3',
	'translate-magic-submit'            => 'Mostra',
	'translate-magic-cm-to-be'          => 'Serà',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Llengua de referència',
	'translate-magic-cm-comment'        => 'Comentari:',
	'translate-magic-cm-save'           => 'Desa',
	'translate-magic-cm-export'         => 'Exporta',
	'translate-magic-cm-updatedusing'   => 'Actualitzat amb Special:Magic',
	'translate-magic-cm-savefailed'     => 'Error al desar',
	'translate-magic-special'           => 'Noms de les pàgines especials',
	'translate-magic-words'             => 'Paraules màgiques',
	'translate-magic-skin'              => 'Noms dels estils',
	'translate-magic-namespace'         => 'Noms dels espais de noms',
	'translationchanges'                => 'Canvis a la traducció',
	'translationchanges-export'         => 'exporta',
	'translationchanges-change'         => '$1:$2 per $3',
	'translate-checks-parameters'       => "Els paràmetres següents no s'estan usant: <strong>$1</strong>",
	'translate-checks-balance'          => 'El format dels parèntesis no és correcte: <strong>$1</strong>',
	'translate-checks-links'            => 'Els enllaços següents són problemàtics: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Reemplaceu les etiquetes següents amb les correctes: <strong>$1</strong>',
	'translate-checks-plural'           => 'La definició utilitza <nowiki>{{PLURAL:}}</nowiki> i en canvi la traducció no.',
);

/** Czech (Česky)
 * @author Li-sung
 * @author Matěj Grabovský
 */
$messages['cs'] = array(
	'translate'                         => 'Přeložit',
	'translate-desc'                    => '[[Special:Translate|Speciální stránka]] zjednodušující překládání systémových hlášení MediaWiki',
	'translate-edit'                    => 'editovat',
	'translate-talk'                    => 'diskuse',
	'translate-history'                 => 'historie',
	'translate-task-view'               => 'Zobrazit všechny zprávy z',
	'translate-task-untranslated'       => 'Zobrazit všechny nepřeložené zprávy z',
	'translate-task-optional'           => 'Zobrazit volitelné zprávy z',
	'translate-task-review'             => 'Porovnat změny v',
	'translate-task-reviewall'          => 'Porovnat všechny překlady v',
	'translate-task-export'             => 'Exportovat překlady z',
	'translate-task-export-to-file'     => 'Exportovat do souboru překlady z',
	'translate-task-export-as-po'       => 'Exportovat překlad do formátu Gettext',
	'translate-page-no-such-language'   => 'Zadaný kód jazyka není platný',
	'translate-page-no-such-task'       => 'Zadaná úloha byla neplatná.',
	'translate-page-no-such-group'      => 'Zadaná skupina byla neplatná.',
	'translate-page-settings-legend'    => 'Nastavení',
	'translate-page-task'               => 'Chci',
	'translate-page-group'              => 'skupina',
	'translate-page-language'           => 'v jazyce',
	'translate-page-limit'              => 's omezením',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|zpráva|zprávy|zpráv}} na stránce',
	'translate-submit'                  => 'Ukázat',
	'translate-page-navigation-legend'  => 'Navigace',
	'translate-page-showing'            => 'Zobrazeny zprávy $1 až $2 z $3.',
	'translate-page-showing-all'        => 'Zobrazeno $1 {{PLURAL:$1|zpráva|zprávy|zpráv}}.',
	'translate-page-showing-none'       => 'Požadavku neodpovídají žádné zprávy.',
	'translate-next'                    => 'Další stránka',
	'translate-prev'                    => 'Předchozí stránka',
	'translate-page-description-legend' => 'Informace o skupině',
	'translate-optional'                => '(volitelné)',
	'translate-ignored'                 => '(ignorované)',
	'translate-edit-definition'         => 'Zdroj zprávy',
	'translate-edit-contribute'         => 'přispět',
	'translate-edit-no-information'     => "''K této zprávě není dokumentace. Pokud víte, kde nebo jak se zpráva používá, můžete pomoci dalším překladatelům tím, že přidáte dokumentaci k této zprávě.''",
	'translate-edit-information'        => 'Informace o této zprávě ($1)',
	'translate-edit-in-other-languages' => 'Zpráva v jiných jazycích',
	'translate-edit-committed'          => 'Současný překlad v úložišti',
	'translate-edit-warnings'           => 'Varování neúplného překladu',
	'translate-magic-pagename'          => 'Rozšířená možnost překladu Mediawiki',
	'translate-magic-form'              => 'Jazyk: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Zobrazit',
	'translate-magic-cm-to-be'          => 'nové',
	'translate-magic-cm-current'        => 'současné',
	'translate-magic-cm-original'       => 'původní',
	'translate-magic-cm-fallback'       => 'rezervní',
	'translate-magic-cm-comment'        => 'Komentář:',
	'translate-magic-cm-save'           => 'Uložit',
	'translate-magic-cm-export'         => 'Exportovat',
	'translate-magic-cm-updatedusing'   => 'Aktualizovat pomocí Special:Magic',
	'translate-magic-cm-savefailed'     => 'Uložení se nepovedlo',
	'translate-magic-special'           => 'Alternativní jména speciálních stránek',
	'translate-magic-words'             => 'Kouzelná slůvka',
	'translate-magic-skin'              => 'Názvy stylů',
	'translate-magic-namespace'         => 'Názvy jmenných prostorů',
	'translationchanges'                => 'Změny překladů',
	'translationchanges-export'         => 'exportovat',
	'translationchanges-change'         => '$1: $2 ($3)',
	'translate-checks-parameters'       => 'Tyto parametry nejsou použity: <strong>$1</strong>',
	'translate-checks-balance'          => 'Vyskytuje se lichý počet závorek: <strong>$1</strong>',
	'translate-checks-links'            => 'Následující odkazy jsou problematické: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Opravte následující značky: <strong>$1</strong>',
	'translate-checks-plural'           => 'Zdroj používá <nowiki>{{PLURAL:}}</nowiki>, ale překlad nikoliv.',
);

/** German (Deutsch)
 * @author Raimond Spekking
 */
$messages['de'] = array(
	'translate'         => 'Übersetze',
	'translate-desc'    => '[[Special:Translate|Spezialseite]] für die Übersetzung von MediaWiki-Systemnachrichten',
	'translate-edit'    => 'Bearbeiten',
	'translate-talk'    => 'Diskussion',
	'translate-history' => 'Versionen',

	'translate-task-view'           => 'Zeige alle Systemnachrichten der',
	'translate-task-untranslated'   => 'Zeige alle nicht übersetzten Systemnachrichten der',
	'translate-task-optional'       => 'Zeige optionale Systemnachrichten der',
	'translate-task-review'         => 'Prüfe Änderungen der',
	'translate-task-reviewall'      => 'Prüfe alle Übersetzungen der',
	'translate-task-export'         => 'Exportiere alle Übersetzungen der',
	'translate-task-export-to-file' => 'Exportiere alle Übersetzungen in eine Datei der',
	'translate-task-export-as-po'   => 'Exportiere alle Übersetzungen in das Gettext-Format der',

	'translate-page-no-such-language' => 'Die angegebene Sprache ist ungültig.',
	'translate-page-no-such-task'     => 'Die angegebene Aufgabe ist ungültig.',
	'translate-page-no-such-group'    => 'Die angegebene Gruppe ist ungültig.',

	'translate-page-settings-legend' => 'Einstellungen',
	'translate-page-task'            => 'Aufgabe',
	'translate-page-group'           => 'Gruppe',
	'translate-page-language'        => 'Sprache',
	'translate-page-limit'           => 'Limit',
	'translate-page-limit-option'    => '$1 {{PLURAL:$1|Systemnachricht|Systemnachrichten}} pro Seite',
	'translate-submit'               => 'Hole',

	'translate-page-navigation-legend' => 'Navigation',
	'translate-page-showing'           => 'Systemnachrichten $1 bis $2 von insgesamt $3.',
	'translate-page-showing-all'       => '$1 {{PLURAL:$1|Systemnachricht|Systemnachrichten}}.',
	'translate-page-showing-none'      => 'Keine Systemnachrichten zur Anzeige vorhanden.',
	'translate-next'                   => 'Nächste Seite',
	'translate-prev'                   => 'Vorherige Seite',

	'translate-page-description-legend' => 'Informationen über diese Gruppe',

	'translate-optional' => '(optional)',
	'translate-ignored'  => '(ignoriert)',

	'translate-edit-definition'         => 'Systemnachricht im Original',
	'translate-edit-contribute'         => 'bearbeiten',
	'translate-edit-no-information'     => "''Diese Systemnachricht hat noch keine Dokumentation. Wenn du weißt, wo und welchem Zusammenhang sie benutzt wird, kannst du anderen Übersetzern helfen, indem du eine Dokumentation hinzufügst.''",
	'translate-edit-information'        => 'Information über diese Systemnachricht ($1)',
	'translate-edit-in-other-languages' => 'Systemnachricht in anderer Sprache',
	'translate-edit-committed'          => 'Aktuelle Übersetzung',
	'translate-edit-warnings'           => 'Warnung über unvollständige Übersetzungen',

	'translate-magic-pagename'    => 'Erweiterte MediaWiki-Übersetzung',
	'translate-magic-help'        => 'Du kannst Aliase für Spezialseiten, den magischen Wörtern, Skinnamen und Namensraum-Namen übersetzen.

Für die magischen Wörter muss das englische Original bestehen bleiben, auch die erste Zahl (0 oder 1) darf nicht verändert werden.

Spezialseiten und magische Wörter können mehrere Übersetzungen, jeweils getrennt durch ein Komma (,) haben. Skins und Namensraum-Namen dürfen nur je eine Übersetzung haben.

In Namensraum-Namen-Übersetzungen hat <tt>$1 talk</tt> eine spezielle Bedeutung. <tt>$1</tt> wird durch den Projektnamen ersetzt (zum Beispiel <tt>{{SITENAME}} talk</tt>. Wenn es in deiner Sprache nicht möglich ist, eine grammatikalisch korrekte Form zu bilden, kontaktiere bitte einen Systemadministrator.

Du musst in der Übersetzer-Gruppe sein um Änderungen zu speichern. Änderungen werden erst beim Klick auf den Speichern-Button gespeichert.',
	'translate-magic-form'        => 'Sprache: $1 Modul: $2 $3',
	'translate-magic-submit'      => 'Hole',
	'translate-magic-cm-to-be'    => 'To-be',
	'translate-magic-cm-current'  => 'Aktuell',
	'translate-magic-cm-original' => 'Original',
	'translate-magic-cm-fallback' => 'Fallback',

	'translate-magic-cm-save'   => 'Speichern',
	'translate-magic-cm-export' => 'Exportieren',

	'translate-magic-cm-updatedusing' => 'Aktualisiert durch Special:Magic',
	'translate-magic-cm-savefailed'   => 'Speichern fehlgeschlagen',

	'translate-magic-special'   => 'Spezialseiten-Aliase',
	'translate-magic-words'     => 'Magische Wörter',
	'translate-magic-skin'      => 'Skins',
	'translate-magic-namespace' => 'Namensraum-Namen',

	'translationchanges'        => 'Übersetzungsänderungen',
	'translationchanges-export' => 'exportieren',
	'translationchanges-change' => '$1: $2 durch $3',

	'translate-checks-parameters' => 'Die folgenden Parameter wurden nicht benutzt: <strong>$1</strong>',
	'translate-checks-balance'    => 'Die Klammersetzung ist nicht ausgeglichen: <strong>$1</strong>',
	'translate-checks-links'      => 'Die folgenden Links sind problematisch: <strong>$1</strong>',
	'translate-checks-xhtml'      => 'Bitte ersettze die folgenden Tags mit den korrekten: <strong>$1</strong>',
	'translate-checks-plural'     => 'Das Original benutzt <nowiki>{{PLURAL:}}</nowiki>, die Übersetzung aber nicht.',
);

/** Lower Sorbian (Dolnoserbski)
 * @author Michawiki
 * @author Dundak
 */
$messages['dsb'] = array(
	'translate'                         => 'Pśełožyś',
	'translate-desc'                    => '[[Special:Translate|Specialny bok]] za pśełožowanje Mediawiki a druge',
	'translate-edit'                    => 'wobźěłaś',
	'translate-talk'                    => 'Diskusija',
	'translate-history'                 => 'Wersije',
	'translate-task-view'               => 'Wšykne powěsći pokazaś',
	'translate-task-untranslated'       => 'Njepśełožone powěsći pokazaś',
	'translate-task-optional'           => 'Opcionelne powěsći pokazaś',
	'translate-task-review'             => 'Změny pśeglědaś',
	'translate-task-reviewall'          => 'Wšykne pśełožki pśeglědaś',
	'translate-task-export'             => 'Pśełožki eksportěrowaś',
	'translate-task-export-to-file'     => 'Pśełožk do dataje eksportěrowaś',
	'translate-task-export-as-po'       => 'Pśełožk we formaśe Gettext eksportěrowaś',
	'translate-page-no-such-language'   => 'Pódana rěc jo njepłaśiwa była.',
	'translate-page-no-such-task'       => 'Pódany nadawk jo njepłaśiwy był.',
	'translate-page-no-such-group'      => 'Pódana kupka jo njepłaśiwa była.',
	'translate-page-settings-legend'    => 'Nastajenja',
	'translate-page-task'               => 'Cu',
	'translate-page-group'              => 'Kupka',
	'translate-page-language'           => 'Rěc',
	'translate-page-limit'              => 'Licba powěsćow',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|powěsć|powěsći|powěsći|powěsćow}} na bok',
	'translate-submit'                  => 'Pokazaś',
	'translate-page-navigation-legend'  => 'Nawigacija',
	'translate-page-showing'            => 'Pokazuju se powěsći wót $1 až $2 z $3.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Pokazujo|Pokazujotej|Pokazuju|Pokazujo}} se $1 {{PLURAL:$1|powěsć|powěsći|powěsći|powěsćow}}.',
	'translate-page-showing-none'       => 'Njedaju powěsći.',
	'translate-next'                    => 'Pśiducy bok',
	'translate-prev'                    => 'Slědny bok',
	'translate-page-description-legend' => 'Informacije wó kupce',
	'translate-optional'                => '(opcionalny)',
	'translate-ignored'                 => '(ignorěrowany)',
	'translate-edit-definition'         => 'Definicija powěsći',
	'translate-edit-contribute'         => 'pśinosowaś',
	'translate-edit-no-information'     => "''Toś ta powěsć njama dokumentaciju. Jolic wěš, źož abo kak toś ta powěsć se wužywa, móžoš drugim pśełožowarjam pomagaś, z tym až dokumentaciju k toś tej powěsći pśidawaš.''",
	'translate-edit-information'        => 'Informacije wó toś tej powěsći ($1)',
	'translate-edit-in-other-languages' => 'Powěsć w drugich rěcach',
	'translate-edit-committed'          => 'Aktualny pśełožk w software',
	'translate-edit-warnings'           => 'Warnowanja wó njedopołnych pśełožkach',
	'translate-magic-pagename'          => 'Rozšyrjony pśełožk MediaWiki',
	'translate-magic-help'              => 'Móžoš aliasy specialnych bokow, magiske słowa, mjenja šatow a mjenja mjenjowych rumow pśełožyś.

Pla magiskich słow dejš engelske wurazy zapśimjeś, howac juž njefunkcioněruju. Wóstaj teke prědny zapisk (0 abo 1) kaž jo.

Aliasy specialnych bokow a magiske słowa mógu někotare pśełožki měś. Pśełožki se pśez komu (,) źěle. Mjenja šatow a mjenjowe rumy mógu jano jaden pśełožk měś.

Mjazy pśełožkami mjenjowych rumow <tt>$1 diskusija</tt> jo wósebny. <tt>$1</tt> se pśez mjenjom sedła wuměnja (na pśikład <tt>{{SITENAME}} diskusija</tt>). Jolic w twójej rěcy njejo móžno płaśiwy wuraz formowaś, mimo až dejš mě sedła změniš, staj se pšosym z wuwiwarjom do zwiska.

Musyš w kupce pśełožowarjow byś, aby změny cyniś mógł. Změny se njeskładuju, až njekliknjoš tłocanko "Składowaś" dołojce.',
	'translate-magic-form'              => 'Rěc: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Pokazaś',
	'translate-magic-cm-to-be'          => 'Ma byś',
	'translate-magic-cm-current'        => 'aktualne',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Alternatiwna rěc',
	'translate-magic-cm-comment'        => 'Komentar',
	'translate-magic-cm-save'           => 'Składowaś',
	'translate-magic-cm-export'         => 'Eksportěrowaś',
	'translate-magic-cm-updatedusing'   => 'Z pomocu Special:Magic zaktualizěrowany',
	'translate-magic-cm-savefailed'     => 'Składowanje jo se njeraźiło',
	'translate-magic-special'           => 'Aliasy specialnych bokow',
	'translate-magic-words'             => 'Magiske słowa',
	'translate-magic-skin'              => 'Mě šatow',
	'translate-magic-namespace'         => 'Mjenja mjenjowych rumow',
	'translationchanges'                => 'Změny pśełožka',
	'translationchanges-export'         => 'eksportěrowaś',
	'translationchanges-change'         => '$1: $2 pśez $3',
	'translate-checks-parameters'       => 'Slědujuce parametry se njewužywaju: <strong>$1</strong>',
	'translate-checks-balance'          => 'Jo njerowna licba spinkow: <strong>$1</strong>',
	'translate-checks-links'            => 'Slědujuce wótkazy su problematiske: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Wuměń pšosym slědujuce tagi pśez korektne: <strong>$1</strong>',
	'translate-checks-plural'           => 'Definicija <nowiki>{{PLURAL:}}</nowiki> wužywa, pśełožk pak nic.',
);

/** Greek (Ελληνικά)
 * @author Consta
 * @author Siebrand
 */
$messages['el'] = array(
	'translate'                         => 'Μεταφράστε',
	'translate-edit'                    => 'επεξεργασία',
	'translate-talk'                    => 'Συζήτηση',
	'translate-history'                 => 'Ιστορικό',
	'translate-task-view'               => 'όλα τα μηνύματα από το',
	'translate-task-untranslated'       => 'όλα τα αμετάφραστα μηνύματα από το',
	'translate-task-optional'           => 'τα προαιρετικά μηνύματα από το',
	'translate-task-review'             => 'τις αλλαγές των επεξεργασιών από το',
	'translate-task-reviewall'          => 'όλες τις αλλαγές των μεταφράσεων στο',
	'translate-page-settings-legend'    => 'Ρυθμίσεις',
	'translate-page-task'               => 'Θέλω',
	'translate-page-group'              => 'Ομάδα',
	'translate-page-language'           => 'Γλώσσα',
	'translate-page-limit'              => 'Όριο',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|μήνυμα|μηνύματα}} ανά σελίδα',
	'translate-submit'                  => 'Πηγαίνετε',
	'translate-page-navigation-legend'  => 'Πλοήγηση',
	'translate-page-showing-all'        => 'Παρουσίαση $1 {{PLURAL:$1|μηνύματος|μηνυμάτων}}.',
	'translate-next'                    => 'Επόμενη σελίδα',
	'translate-prev'                    => 'Προηγούμενη σελίδα',
	'translate-page-description-legend' => 'Πληροφορίες σχετικά με την ομάδα',
	'translate-optional'                => '(προαιρετικά)',
	'translate-ignored'                 => '(αγνοήστε)',
	'translate-edit-information'        => 'Πληροφορίες σχετικά με αυτό το μήνυμα ($1)',
	'translate-edit-in-other-languages' => 'Το Μήνυμα σε άλλες γλώσσες',
	'translate-magic-form'              => 'Γλώσσα: $1 Ενότητα: $2 $3',
	'translate-magic-submit'            => 'Πηγαίνετε',
	'translate-magic-cm-fallback'       => 'Επιφύλαξη',
	'translate-magic-cm-comment'        => 'Σχόλιο:',
	'translate-magic-special'           => 'Πρόσθετα ψευδώνυμα σελίδων',
	'translationchanges'                => 'Αλλαγές μετάφρασης',
	'translationchanges-change'         => '$1: $2 από $3',
	'translate-checks-parameters'       => 'Οι παράμετροι που ακολουθούν δεν χρησιμοποιούνται: <strong>$1</strong>',
	'translate-checks-links'            => 'Οι Ακόλουθοι σύνδεσμοι είναι προβληματικοί: <strong>$1</strong>',
);

/** Esperanto (Esperanto)
 * @author Michawiki
 * @author Tlustulimu
 * @author Yekrats
 */
$messages['eo'] = array(
	'translate'                         => 'Tradukado',
	'translate-desc'                    => '[[Special:Translate|Speciala paĝo]] por traduki Mediawiki kaj alia',
	'translate-edit'                    => 'redaktu',
	'translate-talk'                    => 'diskuto',
	'translate-history'                 => 'historio',
	'translate-task-view'               => 'Rigardi ĉiujn mesaĝojn de',
	'translate-task-untranslated'       => 'Rigardi ĉiujn netradukitajn mesaĝojn de',
	'translate-task-optional'           => 'Rigardi laŭvolajn mesaĝojn',
	'translate-task-review'             => 'Rekontroli ŝanĝojn al',
	'translate-task-reviewall'          => 'Rekontroli ĉiujn tradukojn en',
	'translate-task-export'             => 'Eksporti tradukojn de',
	'translate-task-export-to-file'     => 'Eksporti tradukon en dosieron de',
	'translate-task-export-as-po'       => 'Eksporti tradukon al la formato Gettext',
	'translate-page-no-such-language'   => 'Specifita lingvo estas malvalida.',
	'translate-page-no-such-task'       => 'Specifita tasko estis malvalida.',
	'translate-page-no-such-group'      => 'Specifita grupo estas malvalida.',
	'translate-page-settings-legend'    => 'Agordoj',
	'translate-page-task'               => 'Mi volas',
	'translate-page-group'              => 'Grupo',
	'translate-page-language'           => 'Lingvo',
	'translate-page-limit'              => 'Nombro de mesaĝoj',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mesaĝo|mesaĝoj}} po paĝo',
	'translate-submit'                  => 'Alportu',
	'translate-page-navigation-legend'  => 'Navigado',
	'translate-page-showing'            => 'Estas motrataj mesaĝoj $1 ĝis $2 el $3.',
	'translate-page-showing-all'        => 'Estas montrataj $1 {{PLURAL:$1|mesaĝo|mesaĝoj}}.',
	'translate-page-showing-none'       => 'Ne estas mesaĝoj por montri.',
	'translate-next'                    => 'Sekva paĝo',
	'translate-prev'                    => 'Antaŭa paĝo',
	'translate-page-description-legend' => 'Informoj pri la grupo',
	'translate-optional'                => '(opcionala)',
	'translate-ignored'                 => '(ignorata)',
	'translate-edit-definition'         => 'Mesaĝa difino',
	'translate-edit-contribute'         => 'kontribui',
	'translate-edit-no-information'     => "''Ĉi tiu mesago ne havas dokumentaron. Se vi scias, kie aŭ kiel ĉi tiu mesaĝo estas uzata, vi povas helpi al aliaj tradukantoj aldonante la dokumentaron al ĉi tiu mesaĝo.''",
	'translate-edit-information'        => 'Informoj pri ĉi tiu mesaĝo ($1)',
	'translate-edit-in-other-languages' => 'Mesaĝo en aliaj lingvoj',
	'translate-edit-committed'          => 'Aktuala traduko en programaro',
	'translate-edit-warnings'           => 'Avertoj pri nekompletaj tradukoj',
	'translate-magic-pagename'          => 'Etendita traduko de MediaWiki',
	'translate-magic-help'              => 'Vi povas traduki specialajn paĝojn, magiajn vortojn, nomojn de etosoj kaj nomojn de nomspacoj.

En la magiajn vortojn vi devas inkludi la anglajn esprimojn aŭ ili ne plu funkcios. Lasu ankaŭ la unuan enskribon (0 aŭ 1) kiel ĝi estas.

La kromnomoj de specialaj paĝoj povas havi plurajn tradukojn. La tradukoj estas disigataj per komo (,). Nomoj de etosoj kaj nomspacoj povas havi nur unu tradukon.

En tradukoj de nomspacoj <tt>$1 diskuto</tt> estas speciala. <tt>$1</tt> estas anstataŭigata per la reteja nomo (ekzemple <tt>{{SITENAME}} diskuto</tt>). Se ne estas eble en via lingvo formi validan esprimon sen ŝanĝi la retejan nomon, bonvolu kontakti programiston.

Vi devas esti en la grupo de tradukantoj por konservi ŝanĝojn. Ŝanĝoj ne estos konservataj, ĝis vi alklakis la butonon Konservu malsupre.',
	'translate-magic-form'              => 'Lingvo: $1 Modulo: $2 $3',
	'translate-magic-submit'            => 'Montri',
	'translate-magic-cm-to-be'          => 'Estu',
	'translate-magic-cm-current'        => 'Nuntempe',
	'translate-magic-cm-original'       => 'Originalo',
	'translate-magic-cm-fallback'       => 'Alternativo',
	'translate-magic-cm-comment'        => 'Komento:',
	'translate-magic-cm-save'           => 'Konservu',
	'translate-magic-cm-export'         => 'Eksportu',
	'translate-magic-cm-updatedusing'   => 'Ĝisdatigita pere de Special:Magic',
	'translate-magic-cm-savefailed'     => 'Konservado malsukcesis',
	'translate-magic-special'           => 'Kromnomoj de specialaj paĝoj',
	'translate-magic-words'             => 'Magiaj vortoj',
	'translate-magic-skin'              => 'Nomoj de etosoj',
	'translate-magic-namespace'         => 'Nomoj de nomspacoj',
	'translationchanges'                => 'Tradukŝanĝoj',
	'translationchanges-export'         => 'eksportu',
	'translationchanges-change'         => '$1: $2 de $3',
	'translate-checks-parameters'       => 'Jenaj parametroj ne estas uzataj: <strong>$1</strong>',
	'translate-checks-balance'          => 'Estas nepara nombro de krampoj: <strong>$1</strong>',
	'translate-checks-links'            => 'Jenaj ligiloj estas problemaj: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Bonvolu anstataŭigi jenajn etikedojn per la korektaj: <strong>$1</strong>',
	'translate-checks-plural'           => 'Difino uzas <nowiki>{{PLURAL:}}</nowiki>, sed traduko ne.',
);

/** Spanish (Español)
 * @author Lin linao
 */
$messages['es'] = array(
	'translate'                         => 'Traducir',
	'translate-edit'                    => 'editar',
	'translate-talk'                    => 'discusión',
	'translate-history'                 => 'historial',
	'translate-task-view'               => 'Ver todos los mensajes de',
	'translate-task-untranslated'       => 'Ver todos los mensajes sin traducir de',
	'translate-task-optional'           => 'Ver los mensajes opcionales de',
	'translate-task-review'             => 'Revisar cambios en',
	'translate-task-reviewall'          => 'Revisar todas las traducciones en',
	'translate-task-export'             => 'Exportar traducciones desde',
	'translate-page-settings-legend'    => 'Preferencias',
	'translate-page-task'               => 'Deseo',
	'translate-page-group'              => 'Grupo',
	'translate-page-language'           => 'Idioma',
	'translate-page-limit'              => 'Límite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mensaje|mensajes}} por página',
	'translate-page-navigation-legend'  => 'Navegación',
	'translate-page-showing'            => 'Mostrando mensajes del $1 al $2 de $3',
	'translate-page-showing-none'       => 'No hay mensajes para mostrar',
	'translate-next'                    => 'Página siguiente',
	'translate-prev'                    => 'Página anterior',
	'translate-page-description-legend' => 'Información acerca del grupo',
	'translate-optional'                => '(opcional)',
	'translate-edit-no-information'     => "''No hay datos para este mensaje. Si sabes dónde o cómo se usa, puedes ayudar a otros traductores añadiéndole datos.''",
	'translate-edit-information'        => 'Información acerca de este mensaje ($1)',
	'translate-edit-in-other-languages' => 'Mensaje en otros idiomas',
	'translate-edit-warnings'           => 'Advertencias acerca de traducciones incompletas',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-save'           => 'Guardar',
	'translate-magic-cm-export'         => 'Exportar',
	'translationchanges-export'         => 'exportar',
	'translate-checks-links'            => 'Los siguientes enlaces son problemáticos: <strong>$1</strong>',
);

$messages['eu'] = array(
	'translate-edit' => 'aldatu',
	'translate-talk' => 'eztabaida',
	'translate-history' => 'historia',
);

$messages['ext'] = array(
	'translate' => 'Traucil',
	'translate-edit' => 'eital',
	'translate-talk' => 'caraba',
	'translate-history' => 'estorial',
	'translate-task-view' => 'Vel tolos mensahis endi',
	'translate-task-untranslated' => 'Vel tolos mensahis sin traucil endi',
	'translate-task-export' => 'Esporteal traucionis endi',
	'translate-task-export-to-file' => 'Esporteal traución a un archivu endi',
	'translate-paging' => '<div>Muestrandu los mensahis endi el $1 al $2 de $3. [ $4 | $5 ]</div>',
	'translate-next' => 'Siguienti páhina',
	'translate-prev' => 'Páhina anteriol',
	'translate-optional' => '(ocional)',
	'translate-ignored' => '(inorau)',
	'translate-edit-message-format' => 'El hormatu desti mensahi es <b>$1</b>.',
	'translate-magic-cm-save' => 'Emburacal',
	'translate-magic-cm-export' => 'Esporteal',
	'translate-magic-words' => 'Parabras máhicas',
);

/** فارسی (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'translate'                         => 'ترجمه',
	'translate-desc'                    => '[[Special:Translate|صفحهٔ ویژه‌ای]] برای ترجمهٔ مدیاویکی و فراتر از آن',
	'translate-edit'                    => 'ویرایش',
	'translate-talk'                    => 'بحث',
	'translate-history'                 => 'تاریخچه',
	'translate-task-view'               => 'نمایش تمام پیغام‌ها',
	'translate-task-untranslated'       => 'نمایش تمام پیغام‌های ترجمه نشده',
	'translate-task-optional'           => 'نمایش پیغام‌های اختیاری',
	'translate-task-review'             => 'بازبینی تغییرها',
	'translate-task-reviewall'          => 'بازبینی تمام ترجمه‌ها',
	'translate-task-export'             => 'صدور ترجمه‌ها',
	'translate-task-export-to-file'     => 'صدور ترجمه‌ها به یک پرونده',
	'translate-task-export-as-po'       => 'صدور ترجمه‌ها در قالب Gettext',
	'translate-page-no-such-language'   => 'زبان مورد نظر غیر مجاز است.',
	'translate-page-no-such-task'       => 'عمل مورد نظر غیر مجاز است.',
	'translate-page-no-such-group'      => 'گروه مورد نظر غیر مجاز است.',
	'translate-page-settings-legend'    => 'تنظیمات',
	'translate-page-task'               => 'دستور',
	'translate-page-group'              => 'گروه',
	'translate-page-language'           => 'زبان',
	'translate-page-limit'              => 'تعداد',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|پیغام|پیغام}} در هر صفحه',
	'translate-submit'                  => 'بیاور',
	'translate-page-navigation-legend'  => 'گشتن',
	'translate-page-showing'            => 'نمایش پیغام‌های $1 تا $2 از $3.',
	'translate-page-showing-all'        => 'نمایش $1 {{PLURAL:$1|پیغام|پیغام}}.',
	'translate-page-showing-none'       => 'پیغامی برای نمایش وجود ندارد.',
	'translate-next'                    => 'صفحهٔ بعدی',
	'translate-prev'                    => 'صفحهٔ قبلی',
	'translate-page-description-legend' => 'اطلاعات در مورد گروه',
	'translate-optional'                => '(اختیاری)',
	'translate-ignored'                 => '(نادیده گرفته شده)',
	'translate-edit-definition'         => 'تعریف پیغام',
	'translate-edit-contribute'         => 'مشارکت',
	'translate-edit-no-information'     => "''این پیغام دارای توضیحات نیست. اگر شما می‌دانید که این پیغام چگونه یا در کجا استفاده می‌شود، شما می‌توانید با اضافه کردن توضیحات به دیگر ترجمه‌کنندگان کمک کنید.''",
	'translate-edit-information'        => 'اطلاعات در مورد این پیغام ($1)',
	'translate-edit-in-other-languages' => 'همین پیغام در دیگر زبان‌ها',
	'translate-edit-committed'          => 'ترجمهٔ فعلی در نرم‌افزار',
	'translate-edit-warnings'           => 'هشدار در مورد ترجمه‌های ناکامل',
	'translate-magic-pagename'          => 'ترجمهٔ گسترش یافتهٔ مدیاویکی',
	'translate-magic-help'              => 'شما می‌توانید نام مستعار صفحه‌های ویژه، واژه‌های جادویی، نام پوسته‌ها و نام فضاهای نام را ترجمه کنید.

در مورد واژه‌های جادویی ترجمهٔ شما باید شامل معادل انگلیسی هم باشد وگرنه واژهٔ جادویی کار نخواهد کرد. هم‌چنین، اولین بخش (0 یا 1) را تغییر ندهید.

نام‌های مستعار صفحه‌های ویژه و واژه‌های جادویی می‌توانند بیش از یک ترجمه داشته باشند. ترجمه‌ها با یک کامای انگلیسی (,) از هم جدا می‌شوند. نام پوسته‌ها و فضاهای نام تنها می‌تواند یک ترجمه داشته باشد.

در ترجمهٔ نام فضاهای نام <tt>$1 talk</tt> خاص است. <tt>$1</tt> توسط نام وبگاه جایگزین می‌شود (مانند <tt>{{SITENAME}} talk</tt>). اگر در زبان شما امکان ایجاد چنین عبارتی بدون تغییر دادن نام وبگاه وجود ندارد، لطفاً با یکی از توسعه‌دهندگان نرم‌افزار تماس بگیرید.

برای ذخیره کردن تغییرها باید عضو گروه ترجمه‌کنندگان باشید. تغییرات زمانی ذخیره می‌شوند که دکمهٔ ذخیره را در پایین صفحه فشار دهید.',
	'translate-magic-form'              => 'زبان: $1 واحد: $2 $3',
	'translate-magic-submit'            => 'بیاور',
	'translate-magic-cm-to-be'          => 'آینده',
	'translate-magic-cm-current'        => 'اخیر',
	'translate-magic-cm-original'       => 'اصلی',
	'translate-magic-cm-fallback'       => 'پشت‌انداز',
	'translate-magic-cm-comment'        => 'توضیحات:',
	'translate-magic-cm-save'           => 'ذخیره',
	'translate-magic-cm-export'         => 'صدور',
	'translate-magic-cm-updatedusing'   => 'به روز شده توسط Special:Magic',
	'translate-magic-cm-savefailed'     => 'شکست در ذخیره کردن اطلاعات',
	'translate-magic-special'           => 'نام مستعار صفحه‌های ویژه',
	'translate-magic-words'             => 'واژه‌های جادویی',
	'translate-magic-skin'              => 'اسم پوسته‌ها',
	'translate-magic-namespace'         => 'اسم فضاهای نام',
	'translationchanges'                => 'تغییرهای ترجمه',
	'translationchanges-export'         => 'صدور',
	'translationchanges-change'         => '$1: $2 توسط $3',
	'translate-checks-parameters'       => 'این پارامترها استفاده نشده‌اند: <strong>$1</strong>',
	'translate-checks-balance'          => 'تعداد پرانتزها زوج نیست: <strong>$1</strong>',
	'translate-checks-links'            => 'پیوندهایی که در ادامه می‌آیند مشکل‌ساز هستند: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'لطفاً این برچسب‌ها را با موارد درست جایگزین کنید: <strong>$1</strong>',
	'translate-checks-plural'           => 'تعریف از <nowiki>{{PLURAL:}}</nowiki> استفاده می‌کند اما ترجمه از آن استفاده نمی‌کند.',

);

/** Finnish (Suomi)
 * @author Nike
 * @author Crt
 */
$messages['fi'] = array(
	'translate'                         => 'Käännä',
	'translate-desc'                    => '[[Special:Translate|Toimintosivu]], jolla voi kääntää MediaWikiä ja muutakin',
	'translate-edit'                    => 'muokkaa',
	'translate-talk'                    => 'keskustelu',
	'translate-history'                 => 'historia',
	'translate-task-view'               => 'nähdä kaikki viestit',
	'translate-task-untranslated'       => 'nähdä kaikki kääntämättömät viestit',
	'translate-task-optional'           => 'nähdä valinnaiset viestit',
	'translate-task-review'             => 'tarkistaa muutokset',
	'translate-task-reviewall'          => 'tarkistaa kaikki käännökset',
	'translate-task-export'             => 'viedä käännökset',
	'translate-task-export-to-file'     => 'viedä käännökset tiedostoon',
	'translate-task-export-as-po'       => 'viedä käännökset Gettext-muodossa',
	'translate-page-no-such-language'   => 'Määritelty kieli ei ollut kelvollinen.',
	'translate-page-no-such-task'       => 'Määritelty tehtävä ei ollut kelvollinen.',
	'translate-page-no-such-group'      => 'Määritelty ryhmä ei ollut kelvollinen.',
	'translate-page-settings-legend'    => 'Asetukset',
	'translate-page-task'               => 'Haluan',
	'translate-page-group'              => 'Ryhmä',
	'translate-page-language'           => 'Kieli',
	'translate-page-limit'              => 'Rajoitus',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|viesti|viestiä}} sivulla',
	'translate-submit'                  => 'Hae',
	'translate-page-navigation-legend'  => 'Selaus',
	'translate-page-showing'            => 'Alla on viestit $1–$2; yhteensä $3.',
	'translate-page-showing-all'        => 'Näytetään $1 {{PLURAL:$1|viesti|viestiä}}.',
	'translate-page-showing-none'       => 'Ei näytettäviä viestejä.',
	'translate-next'                    => 'Seuraava sivu',
	'translate-prev'                    => 'Edellinen sivu',
	'translate-page-description-legend' => 'Tietoja ryhmästä',
	'translate-optional'                => '(valinnainen)',
	'translate-ignored'                 => '(ei-käännettävä)',
	'translate-edit-definition'         => 'Viestin määritelmä',
	'translate-edit-contribute'         => 'auta dokumentoinnissa',
	'translate-edit-no-information'     => "''Tätä viestiä ei ole dokumentoitu. Jos tiedät missä tai miten tätä viestiä käytetään, voit auttaa muita kääntäjiä lisäämällä kommentteja tähän viestiin.''",
	'translate-edit-information'        => 'Tietoja viestistä ($1)',
	'translate-edit-in-other-languages' => 'Viesti muilla kielillä',
	'translate-edit-committed'          => 'Nykyinen ohjelmiston käyttämä käännös',
	'translate-edit-warnings'           => 'Varoituksia virheellisestä käännöksestä',
	'translate-magic-pagename'          => 'Laajennettu MediaWikin kääntäminen',
	'translate-magic-cm-current'        => 'Nykyinen',
	'translate-magic-cm-original'       => 'Alkuperäinen',
	'translate-magic-cm-save'           => 'Tallenna',
	'translate-magic-cm-export'         => 'Vie',
	'translate-magic-cm-savefailed'     => 'Tallennus epäonnistui',
	'translationchanges'                => 'Käännösmuutokset',
	'translationchanges-export'         => 'vie',
	'translationchanges-change'         => '$1: Käyttäjä $3 muutti sivua $2',
);

/** French (Français)
 * @author Grondin
 * @author Urhixidur
 * @author Seb35
 * @author Sherbrooke
 * @author Dereckson
 * @author Siebrand
 * @author ChrisPtDe
 */
$messages['fr'] = array(
	'translate'                         => 'Traduire',
	'translate-desc'                    => '[[Special:Translate|Page spéciale]] pour traduire Mediawiki et même plus encore.',
	'translate-edit'                    => 'éditer',
	'translate-talk'                    => 'discuter',
	'translate-history'                 => 'historique',
	'translate-task-view'               => 'Voir tous les messages du',
	'translate-task-untranslated'       => 'Voir tous les messages non traduits du',
	'translate-task-optional'           => 'Voir tous les messages facultatifs du',
	'translate-task-review'             => 'Revoir mes changements au',
	'translate-task-reviewall'          => 'Revoir toutes les traductions du',
	'translate-task-export'             => 'Exporter les traductions du',
	'translate-task-export-to-file'     => 'Exporter dans un fichier les traductions du',
	'translate-task-export-as-po'       => 'Exporter au format Gettext les traductions du',
	'translate-page-no-such-language'   => 'Un code langage invalide a été indiqué.',
	'translate-page-no-such-task'       => 'La tâche spécifiée est invalide.',
	'translate-page-no-such-group'      => 'Le groupe spécifié est invalide.',
	'translate-page-settings-legend'    => 'Configuration',
	'translate-page-task'               => 'Je veux',
	'translate-page-group'              => 'Groupe',
	'translate-page-language'           => 'Langue',
	'translate-page-limit'              => 'Limite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|message|messages}} par page',
	'translate-submit'                  => 'Atteindre',
	'translate-page-navigation-legend'  => 'Navigation',
	'translate-page-showing'            => 'Visualisation des messages de $1 à $2 sur $3.',
	'translate-page-showing-all'        => 'Visualisation de $1 {{PLURAL:$1|message|messages}}',
	'translate-page-showing-none'       => 'Aucun message à visualiser.',
	'translate-next'                    => 'Page suivante',
	'translate-prev'                    => 'Page précédente',
	'translate-page-description-legend' => 'Information à propos du groupe',
	'translate-optional'                => '(facultatif)',
	'translate-ignored'                 => '(ignoré)',
	'translate-edit-definition'         => 'Définition du message',
	'translate-edit-contribute'         => 'contribuer',
	'translate-edit-no-information'     => "Ce message n'est actuellement pas documenté. Si vous savez où ou comment ce message est utilisé, vous pouvez aider les autres traducteurs en documentant ce message.",
	'translate-edit-information'        => 'Informations concernant ce message ($1)',
	'translate-edit-in-other-languages' => 'Message dans les autres langues',
	'translate-edit-committed'          => 'Traduction courante',
	'translate-edit-warnings'           => 'Avertissements concernant les traductions incomplètes',
	'translate-magic-pagename'          => 'Traduction de MediaWiki étendue',
	'translate-magic-help'              => 'Vous pouvez traduire les alias de pages spéciales, les mots magiques, les noms d’habillages et les noms d’espaces de noms.

Dans les mots magiques, vous devez inclure la traduction en anglais ou ça ne fonctionnera plus. De plus, laissez le premier article (0 ou 1) tel quel.

Les alias de pages spéciales et les mots magiques peuvent avoir plusieurs traductions. Les traductions sont séparées par une virgule (,). Les noms d’habillages et d’espaces de noms ne peuvent avoir qu’une traduction.

Dans les traductions d’espaces de noms, <tt>$1 talk</tt> est spécial. <tt>$1</tt> est remplacé par le nom du site (par exemple <tt>{{SITENAME}} talk</tt>). S’il n’est pas possible d’obtenir une expression valide dans votre langue sans changer le nom du site, veuillez contacter un développeur.

Vous devez appartenir au groupe des traducteurs pour sauvegarder les changements. Les changements ne seront pas sauvegardés tant que vous n’aurez pas cliqué sur le bouton « Sauvegarder ».',
	'translate-magic-form'              => 'Langue $1 Module : $2 $3',
	'translate-magic-submit'            => 'Aller',
	'translate-magic-cm-to-be'          => 'Devient',
	'translate-magic-cm-current'        => 'Actuel',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Revenir',
	'translate-magic-cm-comment'        => 'Commentaire :',
	'translate-magic-cm-save'           => 'Sauvegarder',
	'translate-magic-cm-export'         => 'Exporter',
	'translate-magic-cm-updatedusing'   => 'Mise à jour en utilisant Special:Magic',
	'translate-magic-cm-savefailed'     => 'Échec de la sauvegarde',
	'translate-magic-special'           => 'Page spéciales d’alias',
	'translate-magic-words'             => 'Mots magiques',
	'translate-magic-skin'              => 'Nom des interfaces',
	'translate-magic-namespace'         => 'Intitulé des espaces de nommage',
	'translationchanges'                => 'Traductions modifiées',
	'translationchanges-export'         => 'exporter',
	'translationchanges-change'         => '$1: $2 par $3',
	'translate-checks-parameters'       => 'Les paramètres suivants ne sont pas utilisés : <strong>$1</strong>',
	'translate-checks-balance'          => 'Il y a un nombre incorrect de parenthèses : <strong>$1</strong>',
	'translate-checks-links'            => 'Les liens suivants sont douteux : <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Vous êtes invité à corriger les balises suivantes : <strong>$1</strong>',
	'translate-checks-plural'           => 'La définition utilise <nowiki>{{PLURAL:}}</nowiki> mais pas la traduction.',
);

$messages['frc'] = array(
	'translate' => 'Traduire',
	'translate-edit' => 'changer',
	'translate-talk' => 'discussion',
	'translate-history' => 'changements',
	'translate-task-view' => 'voir tous les messages',
	'translate-task-untranslated' => 'voir tous les messages pas traduits',
	'translate-task-review' => 'regarder les changements',
	'translate-task-reviewall' => 'regarder toutes les traductions',
	'translate-task-export' => 'exporter les traductions',
	'translate-task-export-to-file' => 'exporter les traductions au dossier',
	'translate-settings' => 'J\'veux $1 de la classe $2 en $3 avec une limite de $4. $5',
	'translate-paging' => '<div>Les messages de $1 à $2 de $3. [ $4 | $5 ]</div>',
	'translate-submit' => 'Charcher',
	'translate-next' => 'Page suivante',
	'translate-prev' => 'Page avant',
	'translate-optional' => '(de choix)',
	'translate-ignored' => '(ignoré)',
	'translate-edit-message-format' => 'Le format de ce message est <b>$1</b>.',
	'translate-edit-message-in' => 'Chaîne courante en <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Chaîne courante dans la langue en reculant <b>$1</b> ($2):',
	'translationchanges' => 'Modification des traductions',
);

/** Franco-Provençal (Arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'translate'                         => 'Traduire',
	'translate-desc'                    => '[[Special:Translate|Pâge spèciâla]] por traduire MediaWiki et mémo ples oncor.',
	'translate-edit'                    => 'èditar',
	'translate-talk'                    => 'discutar',
	'translate-history'                 => 'historico',
	'translate-task-view'               => 'Vêre tôs los mèssâjos dês',
	'translate-task-untranslated'       => 'Vêre tôs los mèssâjos pas traduits dês',
	'translate-task-optional'           => 'Vêre tôs los mèssâjos u chouèx dês',
	'translate-task-review'             => 'Revêre mos changements dês',
	'translate-task-reviewall'          => 'Revêre totes les traduccions dens',
	'translate-task-export'             => 'Èxportar les traduccions dês',
	'translate-task-export-to-file'     => 'Èxportar les traduccions dens un fichiér dês',
	'translate-task-export-as-po'       => 'Èxportar les traduccions u format gettext',
	'translate-page-no-such-language'   => 'Un code lengâjo envalido at étâ endicâ.',
	'translate-page-no-such-task'       => 'L’ovrâjo spècefiâ est envalido.',
	'translate-page-no-such-group'      => 'Lo groupe spècefiâ est envalido.',
	'translate-page-settings-legend'    => 'Configuracion',
	'translate-page-task'               => 'Vuel',
	'translate-page-group'              => 'Groupe',
	'translate-page-language'           => 'Lengoua',
	'translate-page-limit'              => 'Limita',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mèssâjo|mèssâjos}} per pâge',
	'translate-submit'                  => 'Avengiér',
	'translate-page-navigation-legend'  => 'Navigacion',
	'translate-page-showing'            => 'Visualisacion des mèssâjos de $1 a $2 sur $3.',
	'translate-page-showing-all'        => 'Visualisacion de $1 {{PLURAL:$1|mèssâjo|mèssâjos}}.',
	'translate-page-showing-none'       => 'Nion mèssâjo a visualisar.',
	'translate-next'                    => 'Pâge siuventa',
	'translate-prev'                    => 'Pâge prècèdenta',
	'translate-page-description-legend' => 'Enformacion a propôs du groupe',
	'translate-optional'                => '(u chouèx)',
	'translate-ignored'                 => '(ignorâ)',
	'translate-edit-definition'         => 'Dèfinicion du mèssâjo',
	'translate-edit-contribute'         => 'contribuar',
	'translate-edit-no-information'     => "''Orendrêt, ceti mèssâjo est pas documentâ. Se vos sâde yô ou coment ceti mèssâjo est utilisâ, vos pouede édiér los ôtros traductors en documentent ceti mèssâjo.''",
	'translate-edit-information'        => 'Enformacions regardent ceti mèssâjo ($1)',
	'translate-edit-in-other-languages' => 'Mèssâjo dens les ôtres lengoues',
	'translate-edit-committed'          => 'Traduccion d’ora ja dens la programeria',
	'translate-edit-warnings'           => 'Avèrtissements regardent les traduccions pas complètes',
	'translate-magic-pagename'          => 'Traduccion de MediaWiki ètendua',
	'translate-magic-help'              => 'Vos pouede traduire los noms de les pâges spèciâles, los mots magicos, los noms de les entèrfaces et los titros des èspâços de nom.

Dens los mots magicos, vos dête encllure la traduccion en anglès ou cen fonccionerat pas més. Et pués, lèssiéd lo premiér èlèment (0 ou ben 1) coment il est.

Los noms de les pâges spèciâles et los mots magicos pôvont avêr plusiors traduccions. Les traduccions sont sèparâs per una virgula (,). Los noms de les entèrfaces et los titros des èspâços de nom pôvont avêr ren que yona traduccion.

Dens les traduccions des èspâços de nom, <tt>$1 talk</tt> est spèciâl. <tt>$1</tt> est remplaciê per lo nom du seto (per ègzemplo <tt>{{SITENAME}} talk</tt>). S’o est pas possiblo d’obtegnir una èxprèssion valida dens voutra lengoua sen changiér lo nom du seto, volyéd vos veriér vers un dèvelopior.

Vos dête apartegnir a la tropa des traductors por sôvar los changements. Los changements seront pas sôvâs devant que vos clicâd sur lo boton « Sôvar » d’avâl.',
	'translate-magic-form'              => 'Lengoua : $1 Modulo : $2 $3',
	'translate-magic-submit'            => 'Alar',
	'translate-magic-cm-to-be'          => 'Vint',
	'translate-magic-cm-current'        => 'Ora',
	'translate-magic-cm-original'       => 'Originâl',
	'translate-magic-cm-fallback'       => 'Lengoua de refèrence',
	'translate-magic-cm-comment'        => 'Comentèro :',
	'translate-magic-cm-save'           => 'Sôvar',
	'translate-magic-cm-export'         => 'Èxportar',
	'translate-magic-cm-updatedusing'   => 'Betâ a jorn en utilisent Special:Magic',
	'translate-magic-cm-savefailed'     => 'Falyita de la sôvegouârda',
	'translate-magic-special'           => 'Noms de les pâges spèciâles',
	'translate-magic-words'             => 'Mots magicos',
	'translate-magic-skin'              => 'Noms de les entèrfaces',
	'translate-magic-namespace'         => 'Titros des èspâços de nom',
	'translationchanges'                => 'Traduccions modifiâs',
	'translationchanges-export'         => 'èxportar',
	'translationchanges-change'         => '$1 : $2 per $3',
	'translate-checks-parameters'       => 'Los paramètres siuvents sont pas utilisâs : <strong>$1</strong>',
	'translate-checks-balance'          => 'Y at un nombro fôx de parentèses : <strong>$1</strong>',
	'translate-checks-links'            => 'Los lims siuvents sont pas de sûr : <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Vos éte envitâ a remetre en place les balises siuventes avouéc celes que sont corrèctes : <strong>$1</strong>',
	'translate-checks-plural'           => 'La dèfinicion utilise <nowiki>{{PLURAL:}}</nowiki> mas pas la traduccion.',
);

/** Galician (Galego)
 * @author Alma
 * @author Xosé
 * @author Toliño
 * @author Siebrand
 */
$messages['gl'] = array(
	'translate'                         => 'Traducir',
	'translate-desc'                    => '[[Special:Translate|Páxina especial]] para traducir Mediawiki e máis',
	'translate-edit'                    => 'Editar',
	'translate-talk'                    => 'conversa',
	'translate-history'                 => 'Historial',
	'translate-task-view'               => 'Ver todas as mensaxes de',
	'translate-task-untranslated'       => 'Ver todas as mensaxes sen traducir de',
	'translate-task-optional'           => 'Ver mensaxes opcionais de',
	'translate-task-review'             => 'Revisar cambios en',
	'translate-task-reviewall'          => 'Revisar todas as traducións en',
	'translate-task-export'             => 'Exportar traducións de',
	'translate-task-export-to-file'     => 'Exportar a tradución a un ficheiro de',
	'translate-task-export-as-po'       => 'Exportar a tradución en formato Gettext',
	'translate-page-no-such-language'   => 'Forneceuse un código de lingua non válido',
	'translate-page-no-such-task'       => 'Tarefa especificada non válida',
	'translate-page-no-such-group'      => 'Grupo especificado non válido.',
	'translate-page-settings-legend'    => 'Configuracións',
	'translate-page-task'               => 'Quero',
	'translate-page-group'              => 'Grupo',
	'translate-page-language'           => 'Lingua',
	'translate-page-limit'              => 'Límite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mensaxe|mensaxes}} por páxina',
	'translate-submit'                  => 'Procura',
	'translate-page-navigation-legend'  => 'Navegación',
	'translate-page-showing'            => 'Amosando mensaxes de $1 a $2 de $3.',
	'translate-page-showing-all'        => 'Amosando $1 {{PLURAL:$1|mensaxe|mensaxes}}.',
	'translate-page-showing-none'       => 'Non hai mensaxes para amosar.',
	'translate-next'                    => 'Páxina seguinte',
	'translate-prev'                    => 'Páxina anterior',
	'translate-page-description-legend' => 'Información acerca do grupo',
	'translate-optional'                => '(opcional)',
	'translate-ignored'                 => '(ignorado)',
	'translate-edit-definition'         => 'Definición da mensaxe',
	'translate-edit-contribute'         => 'contribuír',
	'translate-edit-no-information'     => "''Esta mensaxe non ten documentación. Se vostede sabe onde ou como se usa esta mensaxe, pode axudar a outros tradutores engadindo documentación a esta mensaxe.''",
	'translate-edit-information'        => 'Información acerca desta mensaxe ($1)',
	'translate-edit-in-other-languages' => 'Mensaxe noutras linguas',
	'translate-edit-committed'          => 'Tradución actual no software',
	'translate-edit-warnings'           => 'Avisos acerca de traducións incompletas',
	'translate-magic-pagename'          => 'Tradución extendida de MediaWiki',
	'translate-magic-help'              => 'Pode traducir os alias das páxinas especiais, as palabras máxicas, os nomes das aparencias e os nomes dos espazos de nomes.

Nas páxinas máxicas ten que incluír as traducións en inglés ou non funcionarán. Deixe tamén o primeiro elemento (0 ou 1) tal e como está.

Os alias de páxinas especiais e as palabras máxicas poden ter varias traducións. As traducións sepáranse mediante unha vírgula (,). Os nomes das aparencias e dos espazos de nomes só poden ter unha tradución.

Nas traducións dos espazos de nomes, <tt>$1 talk</tt> é especial. <tt>$1</tt> substitúese polo nome do sitio (por exemplo <tt>{{SITENAME}} talk</tt>). Se na súa lingua non resulta posíbel formar unha expresión válida sen mudar o nome do sitio, contacte cun programador.',
	'translate-magic-form'              => 'Lingua: $1 Módulo: $2 $3',
	'translate-magic-submit'            => 'Procurar',
	'translate-magic-cm-to-be'          => 'Será',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Orixinal',
	'translate-magic-cm-fallback'       => 'Reserva',
	'translate-magic-cm-comment'        => 'Comentario:',
	'translate-magic-cm-save'           => 'Gardar',
	'translate-magic-cm-export'         => 'Exportar',
	'translate-magic-cm-updatedusing'   => 'Actualizado mediante Special:Magic',
	'translate-magic-cm-savefailed'     => 'Fallou o gardado',
	'translate-magic-special'           => 'Alias de páxinas especiais',
	'translate-magic-words'             => 'Palabras máxicas',
	'translate-magic-skin'              => 'Nome das aparencias',
	'translate-magic-namespace'         => 'Nomes dos espazos de nomes',
	'translationchanges'                => 'Modificacións na tradución',
	'translationchanges-export'         => 'exportar',
	'translationchanges-change'         => '$1: $2 por $3',
	'translate-checks-parameters'       => 'Os seguintes parámetros non son usados: <strong>$1</strong>',
	'translate-checks-balance'          => 'Hai unha cantidade irregular de parénteses: <strong>$1</strong>',
	'translate-checks-links'            => 'As seguintes ligazóns son problemáticas: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Por favor reemprace as seguintes etiquetas por unhas correctas: <strong>$1</strong>',
	'translate-checks-plural'           => 'A definición usa <nowiki>{{PLURAL:}}</nowiki> pero a tradución non.',
);

$messages['he'] = array(
	'translate'                     => 'תרגום',
	'translate-edit-message-format' => 'המבנה של הודעה זו הוא <b>$1</b>.',
	'translate-edit-message-in'     => 'המחרוזת הנוכחית ל־<b>$1</b> ($2):',
	'translate-edit-message-in-fb'  => 'המחרוזת הנוכחית ל־<b>$1</b> בשפת הגיבוי ($2):',
);

/** Croatian (Hrvatski)
 * @author Dnik
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'translate'                         => 'Prijevodi sistemskih poruka',
	'translate-edit'                    => 'uredi',
	'translate-talk'                    => 'razgovor',
	'translate-history'                 => 'povijest',
	'translate-task-view'               => 'Vidjeti sve poruke u prostoru',
	'translate-task-untranslated'       => 'Vidjeti sve neprevedene poruke u prostoru',
	'translate-task-optional'           => 'Vidjeti dodatne (optional) poruke u prostoru',
	'translate-task-review'             => 'Vidjeti promjene u prostoru',
	'translate-task-reviewall'          => 'Vidjeti sve prijevode u prostoru',
	'translate-task-export'             => 'Izvesti (export) prijevode iz prostora',
	'translate-task-export-to-file'     => 'Izvesti (export) u datoteku prijevode iz prostora',
	'translate-task-export-as-po'       => 'Izvesti (export) prijevod u formatu Gettext',
	'translate-page-no-such-language'   => 'Unešen je nevaljani kod jezika',
	'translate-page-settings-legend'    => 'Postavke',
	'translate-page-task'               => 'Želim',
	'translate-page-group'              => 'Grupa',
	'translate-page-language'           => 'Jezik',
	'translate-page-limit'              => 'Prikaži maks.',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|poruka|poruke|poruka}} po stranici',
	'translate-submit'                  => 'Nađi',
	'translate-page-navigation-legend'  => 'Navigacija',
	'translate-page-showing'            => 'Prikazane poruke od $1 do $2 od ukupno $3.',
	'translate-page-showing-all'        => 'Prikazano: $1 {{PLURAL:$1|poruka|poruke|poruka}}.',
	'translate-page-showing-none'       => 'Nema traženih poruka.',
	'translate-next'                    => 'Slijedeća stranica',
	'translate-prev'                    => 'Prethodna stranica',
	'translate-page-description-legend' => 'Podaci o grupi',
	'translate-optional'                => '(opcionalno)',
	'translate-ignored'                 => '(zanemareno)',
	'translate-edit-definition'         => 'Definicija poruke',
	'translate-edit-contribute'         => 'dodaj',
	'translate-edit-no-information'     => "''Ova poruka nema dokumentacije. Ako znate gdje ili kako se koristi poruka, možete pomoći drugim prevoditeljima dodavajući dokumentaciju ovoj poruci.''",
	'translate-edit-information'        => 'Informacije o ovoj poruci ($1)',
	'translate-edit-in-other-languages' => 'Poruka u drugim jezicima',
	'translate-edit-warnings'           => 'Upozorenja o nepotpunim prijevodima',
	'translate-magic-form'              => 'Jezik: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Dohvati',
	'translate-magic-cm-to-be'          => 'Budući',
	'translate-magic-cm-current'        => 'Trenutni',
	'translate-magic-cm-original'       => 'Izvornik',
	'translate-magic-cm-fallback'       => 'Pričuvna inačica',
	'translate-magic-cm-save'           => 'Snimi',
	'translate-magic-cm-export'         => 'Izvezi',
	'translate-magic-cm-updatedusing'   => 'Osvježeno uporabom Special:Magic stranice',
	'translate-magic-cm-savefailed'     => 'Snimanje nije uspjelo',
	'translate-magic-special'           => 'Alijasi posebnih stranica',
	'translate-magic-words'             => 'Magične riječi (stringovi)',
	'translate-magic-skin'              => 'Imena skinova',
	'translate-magic-namespace'         => 'Imena imenskih prostora',
	'translationchanges'                => 'Prevoditeljske promjene',
	'translationchanges-export'         => 'izvedi (export)',
	'translate-checks-parameters'       => 'Sljedeći parametri se ne koriste: <strong>$1</strong>',
	'translate-checks-balance'          => 'Nejednak broj zagrada: <strong>$1</strong>',
	'translate-checks-links'            => 'Sljedeće poveznice su problematične: <strong>$1</strong>',
	'translate-checks-plural'           => 'Definicija koristi <nowiki>{{PLURAL:}}</nowiki>, ali prijevod ne.',
);

/** Upper Sorbian (Hornjoserbsce)
 * @author Michawiki
 * @author Siebrand
 * @author Dundak
 */
$messages['hsb'] = array(
	'translate'                         => 'Přełožić',
	'translate-desc'                    => '[[Special:Translate|Specialna strona]] za přełožowanje Mediawiki a druheho',
	'translate-edit'                    => 'wobdźěłać',
	'translate-talk'                    => 'diskusija',
	'translate-history'                 => 'stawizny',
	'translate-task-view'               => 'Pokazaj wšě zdźělenki',
	'translate-task-untranslated'       => 'Pokazaj njepřełožene zdźělenki',
	'translate-task-optional'           => 'Pokazaj opcionalne zdźělenki',
	'translate-task-review'             => 'Přepruwuj změny za',
	'translate-task-reviewall'          => 'Přepruwuj wšě přełožki w',
	'translate-task-export'             => 'Eksportuj přełožki',
	'translate-task-export-to-file'     => 'Eksportuj přełožk do dataje',
	'translate-task-export-as-po'       => 'Přełožk we formaće Gettext eksportować',
	'translate-page-no-such-language'   => 'Njepłaćiwy rěčny kod podaty',
	'translate-page-no-such-task'       => 'Podaty nadawk bě njepłaćiwy.',
	'translate-page-no-such-group'      => 'Podata skupina bě njepłaćiwa.',
	'translate-page-settings-legend'    => 'Nastajenja',
	'translate-page-task'               => 'Akcija',
	'translate-page-group'              => 'Skupina',
	'translate-page-language'           => 'Rěč',
	'translate-page-limit'              => 'Ličba zdźělenkow',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|zdźělenka|zdźělence|zdźělenki|zdźělenkow}} na stronu',
	'translate-submit'                  => 'Pokazać',
	'translate-page-navigation-legend'  => 'Nawigacija',
	'translate-page-showing'            => 'Zdźělenki wot $1 do $2 z $3 pokazać.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Pokazuje so|Pokazujetej so|Pokazuja so|Pokazuje so}} $1 {{PLURAL:$1|zdźělenka|zdźělence|zdźělenki|zdźělenkow}}.',
	'translate-page-showing-none'       => 'Njejsu zdźělenki, kotrež hodźa so pokazać.',
	'translate-next'                    => 'Přichodna strona',
	'translate-prev'                    => 'Předchadna strona',
	'translate-page-description-legend' => 'Informacije wo skupinje',
	'translate-optional'                => '(opcionalny)',
	'translate-ignored'                 => '(ignorowany)',
	'translate-edit-definition'         => 'Definicija zdźělenki',
	'translate-edit-contribute'         => 'přinošować',
	'translate-edit-no-information'     => "''Tuta zdźělenka dokumentaciju nima, Jeli wěš, hdźež tuta zdźělenka so wužiwa, móžeš druhim přełožowarjam pomhać přidawajo dokumentaciju k tutej zdźělence.''",
	'translate-edit-information'        => 'Informacije wo tutej zdźělence ($1)',
	'translate-edit-in-other-languages' => 'Zdźělenka w druhich rěčach',
	'translate-edit-committed'          => 'Aktualny přełožk w softwarje',
	'translate-edit-warnings'           => 'Warnowanja wo njedospołnych přełožkach',
	'translate-magic-pagename'          => 'Rozšěrjeny přełožk MediaWiki',
	'translate-magic-help'              => 'Móžěs aliasy specialnych stronow, magiske słowa, mjena šatow a mjena mjenowych rumow přełožić.

W magiskich słowach dyrbiš jendźelske přełožki zapřijeć abo hižo njebudu fungować. Wostaj tež prěni zapisk (0 abo 1) kaž je.

Aliasy specialnych stronow a magiske słowa móža wjacore přełožki měć. Přełožki so přez komy (,) wotdźěleja. Mjeno šatow a mjenowe rumy móže jenož jedyn přełožk měć.

W přełožkach mjenowych rumow <tt>$1 diskusija</tt> je specialna. <tt>$1</tt> so přez mjeno strony, na př. <tt>{{SITENAME}} diskusija</tt> naruna. Jeli w twojej rěči njeje móžno płaćiwy wuraz tworić, bjeztoho zo by so mjeno strony změniło, skontaktuj prošu wuwiwarja.

Dyrbiš w skupinje přełožowarjow być, zo by změny składował. Změny so njeskładuja, doniž  składowanske tłóčatko njekliknješ.',
	'translate-magic-form'              => 'Rěč: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Pokazać',
	'translate-magic-cm-to-be'          => 'Ma być:',
	'translate-magic-cm-current'        => 'Tuchwilu',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Wuhibna rěč',
	'translate-magic-cm-save'           => 'Składować',
	'translate-magic-cm-export'         => 'Eksportować',
	'translate-magic-cm-updatedusing'   => 'Z Special:Magic zaktualizowany',
	'translate-magic-cm-savefailed'     => 'Składowanje njeporadźiło',
	'translate-magic-special'           => 'Aliasy specialnych stronow',
	'translate-magic-words'             => 'Magiske słowa',
	'translate-magic-skin'              => 'Mjeno šatow',
	'translate-magic-namespace'         => 'Mjena mjenowych rumow',
	'translationchanges'                => 'Přełožowanske změny',
	'translationchanges-export'         => 'eksportować',
	'translationchanges-change'         => '$1: $2 wot $3',
	'translate-checks-parameters'       => 'Slědowace parametry so njewužiwaja: <strong>$1</strong>',
	'translate-checks-balance'          => 'Je njeruna ličba spinkow: <strong>$1</strong>',
	'translate-checks-links'            => 'Slědowace wotkazy su problematiske: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Narunaj prošu slědowace taflički přez korektne: <strong>$1</strong>',
	'translate-checks-plural'           => 'Definicija wužiwa <nowiki>{{PLURAL:}}</nowiki>, přełožk pak nic.',
);

/** Haitian (Kreyòl ayisyen)
 * @author Masterches
 */
$messages['ht'] = array(
	'translate' => 'Tradui',
);

/** Hungarian (Magyar)
 * @author Bdanee
 */
$messages['hu'] = array(
	'translate'                         => 'Fordítás',
	'translate-edit'                    => 'szerk',
	'translate-talk'                    => 'vita',
	'translate-history'                 => 'laptörténet',
	'translate-task-view'               => 'Összes üzenet megtekintése',
	'translate-task-untranslated'       => 'Összes fordítatlan üzenet megtekintése',
	'translate-task-optional'           => 'Nem kötelező üzenetek megtekintése',
	'translate-task-review'             => 'Változások áttekintése',
	'translate-task-reviewall'          => 'Összes fordítás áttekintése',
	'translate-task-export'             => 'Fordítások kimentése',
	'translate-task-export-to-file'     => 'Fordítások kimentése fájlba',
	'translate-task-export-as-po'       => 'Fordítás kimentése Gettext formátumba',
	'translate-page-no-such-language'   => 'A megadott nyelv érvénytelen',
	'translate-page-no-such-task'       => 'A megadott művelet érvénytelen',
	'translate-page-no-such-group'      => 'A megadott csoport érvénytelen',
	'translate-page-settings-legend'    => 'Beállítások',
	'translate-page-task'               => 'Elvégzendő művelet',
	'translate-page-group'              => 'Csoport',
	'translate-page-language'           => 'Nyelv',
	'translate-page-limit'              => 'Megjelenítendő elemek',
	'translate-page-limit-option'       => '$1 üzenet/oldal',
	'translate-submit'                  => 'Megjelenítés',
	'translate-page-navigation-legend'  => 'Navigáció',
	'translate-page-showing'            => 'Üzenetek: $1–$2 (összesen $3)',
	'translate-page-showing-all'        => '$1 üzenet megjelenítve',
	'translate-page-showing-none'       => 'Nincs a keresési feltételeknek megfelelő üzenet',
	'translate-next'                    => 'következő',
	'translate-prev'                    => 'előző',
	'translate-page-description-legend' => 'Információk a csoportról',
	'translate-optional'                => '(nem kötelező)',
	'translate-ignored'                 => '(figyelmen kívül hagyva)',
	'translate-edit-definition'         => 'Alapértelmezett érték',
	'translate-edit-contribute'         => 'szerkesztés',
	'translate-edit-no-information'     => "''Ehhez az üzenethez még nincs leírás. Ha tudod, hogy hogyan kell használni, akkor segítheted a többi fordítót a dokumentálásával.''",
	'translate-edit-information'        => 'Használat ($1)',
	'translate-edit-in-other-languages' => 'Az üzenet más nyelveken',
	'translate-edit-committed'          => 'Jelenlegi fordítás',
	'translate-edit-warnings'           => 'Hiányosságok a fordításban',
	'translate-magic-form'              => 'Nyelv: $1, modul: $2 $3',
	'translate-magic-submit'            => 'Lekérés',
	'translate-magic-cm-to-be'          => 'Leendő',
	'translate-magic-cm-current'        => 'Jelenlegi',
	'translate-magic-cm-original'       => 'Eredeti',
	'translate-magic-cm-save'           => 'Mentés',
	'translate-magic-cm-export'         => 'Exportálás',
	'translate-magic-cm-updatedusing'   => 'Frissítve a Special:Magic használatával',
	'translate-magic-cm-savefailed'     => 'Mentés sikertelen',
	'translate-magic-special'           => 'Speciális lapok álnevei',
	'translate-magic-skin'              => 'Felületek nevei',
	'translate-magic-namespace'         => 'Névterek nevei',
	'translationchanges'                => 'Változások a fordításokban',
	'translationchanges-export'         => 'kimentés',
	'translationchanges-change'         => '$1: $2 $3 által',
	'translate-checks-parameters'       => 'A következő paraméterek nincsenek használva: <strong>$1</strong>',
	'translate-checks-balance'          => 'Nem egyenlő számban vannak használva a nyitó- és zárójelek: <strong>$1</strong>',
	'translate-checks-links'            => 'A következő linkek nem megfelelőek: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'A következő tag-eket cseréld le a megfelelőekre: <strong>$1</strong>',
	'translate-checks-plural'           => 'Az alapértelmezett változatban van <nowiki>{{PLURAL:}}</nowiki> forma, míg a fordításban nincs (magyar nyelv esetén ez nem feltétlenül probléma!).',
);

/** Indonesian (Bahasa Indonesia)
 * @author IvanLanin
 */
$messages['id'] = array(
	'translate' => 'Terjemahan',
	'translate-desc' => '[[Special:Translate|Halaman istimewa]] untuk menerjemahkan Mediawiki',
	'translate-edit' => 'sunting',
	'translate-talk' => 'bicara',
	'translate-history' => 'versi',

	'translate-task-view' => 'Tampilkan semua pesan dari',
	'translate-task-untranslated' => 'Tampilkan semua pesan yang belum diterjemahkan dari',
	'translate-task-optional' => 'Tampilkan pesan opsional dari',
	'translate-task-review' => 'Tinjau perubahan dari',
	'translate-task-reviewall' => 'Tinjau semua perubahan pada',
	'translate-task-export' => 'Ekspor terjemahan dari',
	'translate-task-export-to-file' => 'Ekspor terjemahan ke berkas dari',
	'translate-task-export-as-po' => 'Ekspor terjemahan ke format Gettext dari',

	'translate-page-no-such-language' => 'Bahasa yang dipilih tak valid.',
	'translate-page-no-such-task'     => 'Operasi yang dipilih tak valid.',
	'translate-page-no-such-group'    => 'Grup yang dipilih tak valid.',

	'translate-page-settings-legend' => 'Pengaturan',
	'translate-page-task'     => 'Saya ingin',
	'translate-page-group'    => 'Grup',
	'translate-page-language' => 'Bahasa',
	'translate-page-limit'    => 'Limit',
	'translate-page-limit-option' => '$1 {{PLURAL:$1|pesan|pesan}} per halaman',
	'translate-submit'        => 'Ambil',

	'translate-page-navigation-legend' => 'Navigasi',
	'translate-page-showing' => 'Menampilkan pesan $1 hingga $2 dari $3.',
	'translate-page-showing-all' => 'Menampilkan $1 {{PLURAL:$1|pesan|pesan}}.',
	'translate-page-showing-none' => 'Tak ada pesan yang dapat ditampilkan.',
	'translate-page-paging-links' => '[ $1 ] [ $2 ]',
	'translate-next' => 'Halaman selanjutnya',
	'translate-prev' => 'Halaman sebelumnya',

	'translate-page-description-legend' => 'Informasi mengenai grup',

	'translate-optional' => '(opsional)',
	'translate-ignored' => '(diabaikan)',

	'translate-edit-definition' => 'Definisi pesan',
	'translate-edit-contribute' => 'berkontribusi',
	'translate-edit-no-information' => "''Pesan ini tak memiliki dokumentasi. JIka Anda tahu di mana dan bagaimana pesan ini digunakan, Anda dapat menolong penerjemah lain dengan menambahkan dokumentasi bagi pesan ini.''",
	'translate-edit-information' => 'Informasi mengenai pesan ini ($1)',
	'translate-edit-in-other-languages' => 'Pesan dalam bahasa lain',
	'translate-edit-committed' => 'Translasi yang ada di perangkat lunak',
	'translate-edit-warnings' => 'Peringatan mengenai terjemahan yang tak lengkap',

	'translate-magic-pagename' => 'Perluasan terjemahan Mediawiki',
	'translate-magic-help' => 'Anda dapat menerjemahkan alias untuk halaman istimewa, kata magis, nama kulit, dan nama ruang nama.

Untuk kata magis, Anda perlu mencantumkan pula terjemahan bahasa Inggris atau akan terjadi kesalahan. Juga tetap cantumkan item pertama (0 atau 1) begitu saja.

Alias untuk halaman istimewa dan kata magis dapat memiliki lebih dari satu terjemahan yang masing-masing dipisahkan dengan koma (,). Nama kulit dan ruang nama hanya dapat memiliki satu terjemahan.

Dalam terjemahan ruang nama <tt>$1 talk</tt> diperlakukan khusus. <tt>$1</tt> digantikan dengan nama situs (contohnya <tt>{{SITENAME}} talk</tt>. Jika bahasa Anda tidak memungkinkan untuk membentuk suatu ekspresi yang valid tanpa mengganti nama situs, silakan kontak salah seorang pengembang.

Anda perlu menjadi anggota grup penerjemah untuk menyimpan perubahan. Perubahan tak akan disimpan hingga Anda mengklik tombol simpan di bawah.',
	'translate-magic-form' => 'Bahasa: $1 Modul: $2 $3',
	'translate-magic-submit' => 'Ambil',
	'translate-magic-cm-to-be' => 'Menjadi',
	'translate-magic-cm-current' => 'Kini',
	'translate-magic-cm-original' => 'Asal',
	'translate-magic-cm-fallback' => 'Fallback',

	'translate-magic-cm-comment' => 'Komentar:',
	'translate-magic-cm-save' => 'Simpan',
	'translate-magic-cm-export' => 'Ekspor',

	'translate-magic-cm-updatedusing' => 'Diubah menggunakan Special:Magic',
	'translate-magic-cm-savefailed' => 'Gagal disimpan',

	'translate-magic-special' => 'Alias halaman istimewa',
	'translate-magic-words' => 'Kata magis',
	'translate-magic-skin' => 'Nama kulit',
	'translate-magic-namespace' => 'Nama ruang nama',

	'translationchanges' => 'Perubahan terjemahan',
	'translationchanges-export' => 'ekspor',
	'translationchanges-change' => '$1: $2 oleh $3',

	'translate-checks-parameters' => 'Parameter-parameter berikut tidak digunakan: <strong>$1</strong>',
	'translate-checks-balance' => 'Jumlah pengapit tak seimbang: <strong>$1</strong>',
	'translate-checks-links' => 'Pranala berikut bermasalah: <strong>$1</strong>',
	'translate-checks-xhtml' => 'Harap ganti tag-tag berikut dengan tag yang tepat: <strong>$1</strong>',
	'translate-checks-plural' => 'Definisi menggunakan <nowiki>{{PLURAL:}}</nowiki> tapi terjemahannya tidak.',
);

/** Icelandic (Íslenska)
 * @author S.Örvarr.S
 */
$messages['is'] = array(
	'translate'                         => 'Þýða',
	'translate-edit'                    => 'breyta',
	'translate-talk'                    => 'spjall',
	'translate-history'                 => 'breytingaskrá',
	'translate-task-view'               => 'Skoða allar meldingar frá',
	'translate-task-untranslated'       => 'Skoða allar óþýddar meldingar frá',
	'translate-task-optional'           => 'Skoða valfrjálsar meldingar frá',
	'translate-task-review'             => 'Kanna breytingar á',
	'translate-page-settings-legend'    => 'Stillingar',
	'translate-page-task'               => 'Ég vil',
	'translate-page-group'              => 'Hópur',
	'translate-page-language'           => 'Tungumál',
	'translate-page-limit'              => 'Takmark',
	'translate-submit'                  => 'Sækja',
	'translate-page-navigation-legend'  => 'Flakk',
	'translate-page-showing'            => 'Sýni meldingar frá $1 til $2 af $3.',
	'translate-page-showing-all'        => 'Sýni $1 {{PLURAL:$1|melding|meldingar}}.',
	'translate-page-showing-none'       => 'Engar meldingar til að sýna.',
	'translate-next'                    => 'Næsta síða',
	'translate-prev'                    => 'Fyrri síða',
	'translate-page-description-legend' => 'Upplýsingar um hópinn',
	'translate-optional'                => '(valfrjálst)',
	'translate-ignored'                 => '(hunsað)',
	'translate-edit-information'        => 'Upplýsingar um þessa meldingu ($1)',
	'translate-edit-in-other-languages' => 'Melding á öðrum tungumálum',
	'translate-edit-warnings'           => 'Viðvaranir vegna ókláraðar þýðinga',
	'translate-magic-form'              => 'Tungumál: $1 Eining: $2 $3',
	'translate-magic-submit'            => 'Sækja',
	'translate-magic-cm-current'        => 'Núverandi',
	'translate-magic-cm-save'           => 'Vista',
	'translate-magic-cm-export'         => 'Flytja',
	'translate-magic-namespace'         => 'Heiti nafnrýma',
	'translationchanges'                => 'Breytingar þýðinga',
	'translationchanges-export'         => 'flytja',
	'translationchanges-change'         => '$1: $2 eftir $3',
	'translate-checks-links'            => 'Eftirfarandi tenglar eru vafasamir: <strong>$1</strong>',
);

$messages['it'] = array(
	'translate' => 'Traduzione',
	'translate-settings' => 'Desidero $1 $2 in lingua $3 fino a un massimo di $4. $5',
	'translate-edit-message-format' => 'Formato del messaggio: <b>$1</b>.',
	'translate-edit-message-in' => 'Contenuto attuale in <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Contenuto attuale nella lingua di riserva <b>$1</b> ($2):',
);

/** Japanese (日本語)
 * @author JtFuruhata
 * @author Marine-Blue
 */
$messages['ja'] = array(
	'translate'                         => 'ソフトウェアメッセージの翻訳',
	'translate-desc'                    => 'MediaWikiをはじめとするソフトウェアのメッセージを翻訳するための[[Special:Translate|特別ページ]]',
	'translate-edit'                    => '編集',
	'translate-talk'                    => 'ノート',
	'translate-history'                 => '履歴',
	'translate-task-view'               => 'すべてのメッセージ',
	'translate-task-untranslated'       => '未翻訳メッセージ',
	'translate-task-optional'           => '任意翻訳のメッセージ',
	'translate-task-review'             => '更新反映待ちのメッセージ',
	'translate-task-reviewall'          => '翻訳済みメッセージ',
	'translate-task-export'             => '翻訳された PHP コードをテキストエリアに出力',
	'translate-task-export-to-file'     => '翻訳された PHP コードをファイルとしてエクスポート',
	'translate-task-export-as-po'       => '翻訳された PHP コードを gettext 形式でエクスポート',
	'translate-page-no-such-language'   => '言語指定が不正です',
	'translate-page-no-such-task'       => '絞り込みの指定が不正です',
	'translate-page-no-such-group'      => '種類の指定が不正です',
	'translate-page-settings-legend'    => '設定',
	'translate-page-task'               => '絞込み',
	'translate-page-group'              => '種類',
	'translate-page-language'           => '言語',
	'translate-page-limit'              => '表示数',
	'translate-page-limit-option'       => '1ページごとに $1 項目',
	'translate-submit'                  => '再表示',
	'translate-page-navigation-legend'  => 'ナビゲーション',
	'translate-page-showing'            => '全 $3 件中 $1 件目から $2 件目まで表示しています',
	'translate-page-showing-all'        => '全 $1 件を表示しています',
	'translate-page-showing-none'       => '該当する項目はありません。',
	'translate-next'                    => '次のページ',
	'translate-prev'                    => '前のページ',
	'translate-page-description-legend' => 'このグループについて',
	'translate-optional'                => '（任意翻訳）',
	'translate-ignored'                 => '（翻訳無効）',
	'translate-edit-definition'         => '元のメッセージ',
	'translate-edit-contribute'         => '寄稿する',
	'translate-edit-no-information'     => "''このメッセージに関する説明はありません。もし、このメッセージがどこでどのように使われているかご存知でしたら、他の翻訳者のために説明を寄稿してください。なお、このメッセージは多言語共通の表示となりますので、翻訳者全員が理解できる言語（MediaWikiでは英語）での記述をお願いします。''",
	'translate-edit-information'        => 'このメッセージに関する説明（$1）',
	'translate-edit-in-other-languages' => '他言語でのメッセージ',
	'translate-edit-committed'          => '現在ソフトウェア上で採用されている翻訳メッセージ',
	'translate-edit-warnings'           => '不完全な翻訳に対する警告',
	'translate-magic-pagename'          => 'MediaWiki拡張項目の翻訳',
	'translate-magic-help'              => '特別ページへのエイリアス、マジックワード、スキン名、名前空間名も翻訳できます。

マジックワードを翻訳する際には、英語のものも含めておく必要があることに注意してください。さもなくば、それらは動作しなくなります。また、最初の項目（0か1）はそのままにしておいてください。

特別ページへのエイリアスとマジックワードは、コンマ（,）で区切ることにより、複数の翻訳メッセージを持つことができます。スキン名と名前空間名は単一の翻訳メッセージのみを持ちます。

名前空間を翻訳する際、<tt>$1 talk</tt> には特別な注意事項があります。それは、<tt>$1</tt> がサイト名に変更される点です（例えば<tt>{{SITENAME}} talk</tt>の様に）。あなたが翻訳しようとしている言語において、サイト名の変更なしには正しい形式で表現できない場合、開発者に相談してください。

変更を保存できるのは、翻訳者グループに属する利用者のみです。{{int:translate-magic-cm-save}}ボタンを押すまで変更は保存されません。',
	'translate-magic-form'              => '言語: $1 翻訳対象: $2 $3',
	'translate-magic-submit'            => '再表示',
	'translate-magic-cm-to-be'          => '変更後',
	'translate-magic-cm-current'        => '変更前',
	'translate-magic-cm-original'       => '元の内容',
	'translate-magic-cm-fallback'       => '予備',
	'translate-magic-cm-comment'        => '編集内容の要約:',
	'translate-magic-cm-save'           => '保存',
	'translate-magic-cm-export'         => 'エクスポート',
	'translate-magic-cm-updatedusing'   => 'Special:Magic による更新に成功しました',
	'translate-magic-cm-savefailed'     => '保存に失敗しました',
	'translate-magic-special'           => '特別ページへのエイリアス',
	'translate-magic-words'             => 'マジックワード',
	'translate-magic-skin'              => 'スキン名',
	'translate-magic-namespace'         => '名前空間名',
	'translationchanges'                => '翻訳変更状況',
	'translationchanges-export'         => 'エクスポート',
	'translationchanges-change'         => '$1: $2 翻訳者-$3',
	'translate-checks-parameters'       => '次のパラメータが利用されていません: <strong>$1</strong>',
	'translate-checks-balance'          => '括弧の数が一致していません: <strong>$1</strong>',
	'translate-checks-links'            => 'リンクに問題があります: <strong>$1</strong>',
	'translate-checks-xhtml'            => '正しいタグに修正してください: <strong>$1</strong>',
	'translate-checks-plural'           => '元のメッセージでは <nowiki>{{PLURAL:}}</nowiki> を使用していますが、翻訳の中にはありません。',
);

/** Georgian (ქართული)
 * @author Malafaya
 */
$messages['ka'] = array(
	'translate-edit'          => 'რედაქტირება',
	'translate-talk'          => 'განხილვა',
	'translate-history'       => 'ისტორია',
	'translate-page-language' => 'ენა',
);

/** ‫قازاقشا (تٴوتە)‬ (‫قازاقشا (تٴوتە)‬)
 * @author AlefZet
 */
$messages['kk-arab'] = array(
	'translate'                         => 'اۋدارۋ',
	'translate-edit'                    => 'وڭدەۋ',
	'translate-talk'                    => 'تالقىلاۋ',
	'translate-history'                 => 'تارىيحى',
	'translate-task-view'               => 'بارلىق حابارىن قاراۋ',
	'translate-task-untranslated'       => 'اۋدارىلماعان بارلىق حابارىن قاراۋ',
	'translate-task-optional'           => 'مىندەتتى ەمەس حابارلارىن قاراۋ',
	'translate-task-review'             => 'وزگەرىستەرىن قاراپ شىعۋ',
	'translate-task-reviewall'          => 'بارلىق اۋدارمالارىن قاراپ شىعۋ',
	'translate-task-export'             => 'اۋدارمالارىن سىرتقا بەرۋ',
	'translate-task-export-to-file'     => 'اۋدارمالارىن فايلمەن سىرتقا بەرۋ',
	'translate-task-export-as-po'       => 'اۋدارمالارىن Gettext پىشىمىمەن سىرتقا بەرۋ',
	'translate-page-no-such-language'   => 'كەلتىرىلگەن ٴتىل بەلگىلەمەسى جارامسىز',
	'translate-page-no-such-task'       => 'ەنگىزىلگەن تاپسىرما جارامسىز.',
	'translate-page-no-such-group'      => 'ەنگىزىلگەن توب جارامسىز.',
	'translate-page-settings-legend'    => 'باپتاۋ',
	'translate-page-task'               => 'تالابىم:',
	'translate-page-group'              => 'حابار توبى',
	'translate-page-language'           => 'ٴتىلى',
	'translate-page-limit'              => 'شەكتەمى',
	'translate-page-limit-option'       => 'بەت سايىن  {{PLURAL:$1|1|$1}} حابار',
	'translate-submit'                  => 'كەلتىر!',
	'translate-page-navigation-legend'  => 'باعىتتاۋ',
	'translate-page-showing'            => 'كورسەتىلگەن حابار اۋقىمى: $1 — $2 (نە بارلىعى $3).',
	'translate-page-showing-all'        => 'كورسەتىلۋى: {{PLURAL:$1|1|$1}} حابار.',
	'translate-page-showing-none'       => 'كورسەتىلەتىن ەش حابار جوق.',
	'translate-next'                    => 'كەلەسى بەت',
	'translate-prev'                    => 'الدىڭعى بەت',
	'translate-page-description-legend' => 'بۇل توپ تۋرالى مالىمەت',
	'translate-optional'                => '(مىندەتتى ەمەس)',
	'translate-ignored'                 => '(ەلەمەيتىن)',
	'translate-edit-definition'         => 'حاباردىڭ انىقتالىمى',
	'translate-edit-contribute'         => 'ۇلەس بەر',
	'translate-edit-no-information'     => "''بۇل حابار قۇجاتتاماسىز. ەگەر وسى حاباردىڭ قايدا نەمەسە قالاي قولدانعانىن بىلسەڭىز, بۇل حابارعا قۇجاتتاما كەلتىرىپ, باسقا اۋدارۋشىلارعا كومەكتەسە الاسىز.''",
	'translate-edit-information'        => 'بۇل حابار تۋرالى مالىمەت ($1)',
	'translate-edit-in-other-languages' => 'حابار باسقا تىلدەردە',
	'translate-edit-committed'          => 'باعدارلاماداعى اعىمدىق اۋدارما',
	'translate-edit-warnings'           => 'تولىق اۋدارىلماعان حابارلار تۋرالى اڭعارتپالار',
	'translate-magic-pagename'          => 'كەڭەيتىلگەن MediaWiki اۋدارۋى',
	'translate-magic-help'              => 'ارنايى بەت بۇركەمەلەرىن, سىيقىرلى سوزدەرىن, بەزەندىرۋ مانەر اتاۋلارىن جانە ەسىم ايا اتاۋلارىن اۋدارا الاسىز.

سىيقىرلى سوزدەردە اعىلشىنشا نۇسقاسىن كىرگىزۋىڭىز ٴجون, ايتپەسە قىزمەتى توقتالادى. تاعى دا ٴبىرىنشى بابىن (0 نە 1) ٴاردايىم قالدىرىڭىز.

ارنايى بەت بۇركەمەلەرىندە جانە سىيقىرلى سوزدەرىندە بىرنەشە اۋدارما بولۋى مۇمكىن. اۋدارمالار ۇتىرمەن (,) بولىكتەنەدى. بەزەندىرۋ مانەر جانە ەسىم ايا اتاۋلارىندا تەك ٴبىر اۋدارما بولۋى ٴتىيىس.

ەسىم ايا اۋدارمالارىندا <tt>$1_talk</tt> دەگەن ارنايى كەلتىرىلەدى. <tt>$1</tt> دەگەن اينالمالى وزدىكتىك توراپ اتاۋىمەن الماستىرىلادى (مىسالى, <tt>{{SITENAME}} تالقىلاۋى</tt>). ەگەر ٴسىزدىڭ تىلىڭىزدە توراپ اتاۋىن وزگەرتپەي دۇرىس سويلەم قۇرىلماسا, دامىتۋشىلارعا حابارلاسىڭىز.',
	'translate-magic-form'              => 'ٴتىلى: $1 قۇراشى: $2 $3',
	'translate-magic-submit'            => 'كەلتىر',
	'translate-magic-cm-to-be'          => 'بولۋعا ٴتىيىستىسى',
	'translate-magic-cm-current'        => 'اعىمداعىسى',
	'translate-magic-cm-original'       => 'تۇپنۇسقاسى',
	'translate-magic-cm-fallback'       => 'سۇيەمەلدەۋى',
	'translate-magic-cm-save'           => 'ساقتا!',
	'translate-magic-cm-export'         => 'سىرتقا بەر',
	'translate-magic-cm-updatedusing'   => 'Special:Magic دەگەندى قولدانىپ ساقتالعان',
	'translate-magic-cm-savefailed'     => 'ساقتاۋ ٴساتسىز بولدى',
	'translate-magic-special'           => 'ارنايى بەت بۇركەمەلەرى',
	'translate-magic-words'             => 'سىيقىر سوزدەر',
	'translate-magic-skin'              => 'بەزەندىرۋ مانەرى اتاۋلارى',
	'translate-magic-namespace'         => 'ەسىم ايا اتاۋلارى',
	'translationchanges'                => 'اۋدارما وزگەرىستەرى',
	'translationchanges-export'         => 'سىرتقا بەرۋ',
	'translationchanges-change'         => '$1: $2 ($3 ىستەگەن)',
	'translate-checks-parameters'       => 'كەلەسى باپتالىمدار پايدالانىلماعان: <strong>$1</strong>',
	'translate-checks-balance'          => 'مىندا جاقشالاردىڭ بارلىق سانى جۇپ ەمەس: <strong>$1</strong>',
	'translate-checks-links'            => 'كەلەسى سىلتەمەلەر جارامسىز: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'كەلەسى بەلگىلەمەلەردى دۇرىستارىمەن الماستىرىڭىز: <strong>$1</strong>',
	'translate-checks-plural'           => 'انىقتالىمدا <nowiki>{{PLURAL:}} پايدالانىلعان, بىراق اۋدارمادا بۇل جوق.',
);

/** Kazakh (Cyrillic) (Қазақша (Cyrillic))
 * @author AlefZet
 * @author Siebrand
 */
$messages['kk-cyrl'] = array(
	'translate'                         => 'Аудару',
	'translate-edit'                    => 'өңдеу',
	'translate-talk'                    => 'талқылау',
	'translate-history'                 => 'тарихы',
	'translate-task-view'               => 'барлық хабарын қарау',
	'translate-task-untranslated'       => 'аударылмаған барлық хабарын қарау',
	'translate-task-optional'           => 'міндетті емес хабарларын қарау',
	'translate-task-review'             => 'өзгерістерін қарап шығу',
	'translate-task-reviewall'          => 'барлық аудармаларын қарап шығу',
	'translate-task-export'             => 'аудармаларын сыртқа беру',
	'translate-task-export-to-file'     => 'аудармаларын файлмен сыртқа беру',
	'translate-task-export-as-po'       => 'аудармаларын Gettext пішімімен сыртқа беру',
	'translate-page-no-such-language'   => 'Келтірілген тіл белгілемесі жарамсыз',
	'translate-page-no-such-task'       => 'Енгізілген тапсырма жарамсыз.',
	'translate-page-no-such-group'      => 'Енгізілген тоб жарамсыз.',
	'translate-page-settings-legend'    => 'Баптау',
	'translate-page-task'               => 'Талабым:',
	'translate-page-group'              => 'Хабар тобы',
	'translate-page-language'           => 'Тілі',
	'translate-page-limit'              => 'Шектемі',
	'translate-page-limit-option'       => 'бет сайын  {{PLURAL:$1|1|$1}} хабар',
	'translate-submit'                  => 'Келтір!',
	'translate-page-navigation-legend'  => 'Бағыттау',
	'translate-page-showing'            => 'Көрсетілген хабар ауқымы: $1 — $2 (не барлығы $3).',
	'translate-page-showing-all'        => 'Көрсетілуі: {{PLURAL:$1|1|$1}} хабар.',
	'translate-page-showing-none'       => 'Көрсетілетін еш хабар жоқ.',
	'translate-next'                    => 'Келесі бет',
	'translate-prev'                    => 'Алдыңғы бет',
	'translate-page-description-legend' => 'Бұл топ туралы мәлімет',
	'translate-optional'                => '(міндетті емес)',
	'translate-ignored'                 => '(елемейтін)',
	'translate-edit-definition'         => 'Хабар анықтауы',
	'translate-edit-contribute'         => 'үлес бер',
	'translate-edit-no-information'     => "''Бұл хабар құжаттамасыз. Егер осы хабардың қайда немесе қалай қолданғанын білсеңіз, бұл хабарға құжаттама келтіріп, басқа аударушыларға көмектесе аласыз.''",
	'translate-edit-information'        => 'Бұл хабар туралы мәлімет ($1)',
	'translate-edit-in-other-languages' => 'Хабар басқа тілдерде',
	'translate-edit-committed'          => 'Ағымдағы аударма бағдарламада',
	'translate-edit-warnings'           => 'Толық аударылмаған хабарлар туралы аңғартпалар',
	'translate-magic-pagename'          => 'Кеңейтілген MediaWiki аударуы',
	'translate-magic-help'              => 'Арнайы бет бүркемелерін, сиқырлы сөздерін, безендіру мәнер атауларын және есім ая атауларын аудара аласыз.

Сиқырлы сөздерде ағылшынша нұсқасын кіргізуіңіз жөн, әйтпесе қызметі тоқталады. Тағы да бірінші бабын (0 не 1) әрдайым қалдырыңыз.

Арнайы бет бүркемелерінде және сиқырлы сөздерінде бірнеше аударма болуы мүмкін. Аудармалар үтірмен (,) бөліктенеді. Безендіру мәнер және есім ая атауларында тек бір аударма болуы тиіс.

Есім ая аудармаларында <tt>$1_talk</tt> деген арнайы келтіріледі. <tt>$1</tt> деген айналмалы өздіктік торап атауымен алмастырылады (мысалы, <tt>{{SITENAME}} талқылауы</tt>). Егер сіздің тіліңізде торап атауын өзгертпей дұрыс сөйлем құрылмаса, дамытушыларға хабарласыңыз.',
	'translate-magic-form'              => 'Тілі: $1 Құрашы: $2 $3',
	'translate-magic-submit'            => 'Келтір',
	'translate-magic-cm-to-be'          => 'Болуға тиістісі',
	'translate-magic-cm-current'        => 'Ағымдағысы',
	'translate-magic-cm-original'       => 'Түпнұсқасы',
	'translate-magic-cm-fallback'       => 'Сүйемелдеуі',
	'translate-magic-cm-save'           => 'Сақта!',
	'translate-magic-cm-export'         => 'Сыртқа бер',
	'translate-magic-cm-updatedusing'   => 'Special:Magic дегенді қолданып сақталған',
	'translate-magic-cm-savefailed'     => 'Сақтау сәтсіз болды',
	'translate-magic-special'           => 'Арнайы бет бүркемелері',
	'translate-magic-words'             => 'Сиқыр сөздер',
	'translate-magic-skin'              => 'Безендіру мәнері атаулары',
	'translate-magic-namespace'         => 'Есім ая атаулары',
	'translationchanges'                => 'Аударма өзгерістері',
	'translationchanges-export'         => 'сыртқа беру',
	'translationchanges-change'         => '$1: $2 ($3 істеген)',
	'translate-checks-parameters'       => 'Келесі бапталымдар пайдаланылмаған: <strong>$1</strong>',
	'translate-checks-balance'          => 'Мында жақшалардың барлық саны жұп емес: <strong>$1</strong>',
	'translate-checks-links'            => 'Келесі сілтемелер жарамсыз: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Келесі белгілемелерді дұрыстарымен алмастырыңыз: <strong>$1</strong>',
	'translate-checks-plural'           => 'Анықталымда <nowiki>{{PLURAL:}}</nowiki> пайдаланылған, бірақ аудармада бұл жоқ.',
);

/** Kazakh (Latin) (Қазақша (Latin))
 * @author AlefZet
 */
$messages['kk-latn'] = array(
	'translate'                         => 'Awdarw',
	'translate-edit'                    => 'öñdew',
	'translate-talk'                    => 'talqılaw',
	'translate-history'                 => 'tarïxı',
	'translate-task-view'               => 'barlıq xabarın qaraw',
	'translate-task-untranslated'       => 'awdarılmağan barlıq xabarın qaraw',
	'translate-task-optional'           => 'mindetti emes xabarların qaraw',
	'translate-task-review'             => 'özgeristerin qarap şığw',
	'translate-task-reviewall'          => 'barlıq awdarmaların qarap şığw',
	'translate-task-export'             => 'awdarmaların sırtqa berw',
	'translate-task-export-to-file'     => 'awdarmaların faýlmen sırtqa berw',
	'translate-task-export-as-po'       => 'awdarmaların Gettext pişimimen sırtqa berw',
	'translate-page-no-such-language'   => 'Keltirilgen til belgilemesi jaramsız',
	'translate-page-no-such-task'       => 'Engizilgen tapsırma jaramsız.',
	'translate-page-no-such-group'      => 'Engizilgen tob jaramsız.',
	'translate-page-settings-legend'    => 'Baptaw',
	'translate-page-task'               => 'Talabım:',
	'translate-page-group'              => 'Xabar tobı',
	'translate-page-language'           => 'Tili',
	'translate-page-limit'              => 'Şektemi',
	'translate-page-limit-option'       => 'bet saýın  {{PLURAL:$1|1|$1}} xabar',
	'translate-submit'                  => 'Keltir!',
	'translate-page-navigation-legend'  => 'Bağıttaw',
	'translate-page-showing'            => 'Körsetilgen xabar awqımı: $1 - $2 (ne barlığı $3).',
	'translate-page-showing-all'        => 'Körsetilwi: {{PLURAL:$1|1|$1}} xabar.',
	'translate-page-showing-none'       => 'Körsetiletin eş xabar joq.',
	'translate-next'                    => 'Kelesi bet',
	'translate-prev'                    => 'Aldıñğı bet',
	'translate-page-description-legend' => 'Bul top twralı mälimet',
	'translate-optional'                => '(mindetti emes)',
	'translate-ignored'                 => '(elemeýtin)',
	'translate-edit-definition'         => 'Xabardıñ anıqtalımı',
	'translate-edit-contribute'         => 'üles ber',
	'translate-edit-no-information'     => "''Bul xabar qujattamasız. Eger osı xabardıñ qaýda nemese qalaý qoldanğanın bilseñiz, bul xabarğa qujattama keltirip, basqa awdarwşılarğa kömektese alasız.''",
	'translate-edit-information'        => 'Bul xabar twralı mälimet ($1)',
	'translate-edit-in-other-languages' => 'Xabar basqa tilderde',
	'translate-edit-committed'          => 'Bağdarlamadağı ağımdıq awdarma',
	'translate-edit-warnings'           => 'Tolıq awdarılmağan xabarlar twralı añğartpalar',
	'translate-magic-pagename'          => 'Keñeýtilgen MediaWiki awdarwı',
	'translate-magic-help'              => 'Arnaýı bet bürkemelerin, sïqırlı sözderin, bezendirw mäner atawların jäne esim aya atawların awdara alasız.

Sïqırlı sözderde ağılşınşa nusqasın kirgizwiñiz jön, äýtpese qızmeti toqtaladı. Tağı da birinşi babın (0 ne 1) ärdaýım qaldırıñız.

Arnaýı bet bürkemelerinde jäne sïqırlı sözderinde birneşe awdarma bolwı mümkin. Awdarmalar ütirmen (,) böliktenedi. Bezendirw mäner jäne esim aya atawlarında tek bir awdarma bolwı tïis.

Esim aya awdarmalarında <tt>$1_talk</tt> degen arnaýı keltiriledi. <tt>$1</tt> degen aýnalmalı özdiktik torap atawımen almastırıladı (mısalı, <tt>{{SITENAME}} talqılawı</tt>). Eger sizdiñ tiliñizde torap atawın özgertpeý durıs söýlem qurılmasa, damıtwşılarğa xabarlasıñız.',
	'translate-magic-form'              => 'Tili: $1 Quraşı: $2 $3',
	'translate-magic-submit'            => 'Keltir',
	'translate-magic-cm-to-be'          => 'Bolwğa tïistisi',
	'translate-magic-cm-current'        => 'Ağımdağısı',
	'translate-magic-cm-original'       => 'Tüpnusqası',
	'translate-magic-cm-fallback'       => 'Süýemeldewi',
	'translate-magic-cm-save'           => 'Saqta!',
	'translate-magic-cm-export'         => 'Sırtqa ber',
	'translate-magic-cm-updatedusing'   => 'Special:Magic degendi qoldanıp saqtalğan',
	'translate-magic-cm-savefailed'     => 'Saqtaw sätsiz boldı',
	'translate-magic-special'           => 'Arnaýı bet bürkemeleri',
	'translate-magic-words'             => 'Sïqır sözder',
	'translate-magic-skin'              => 'Bezendirw mäneri atawları',
	'translate-magic-namespace'         => 'Esim aya atawları',
	'translationchanges'                => 'Awdarma özgeristeri',
	'translationchanges-export'         => 'sırtqa berw',
	'translationchanges-change'         => '$1: $2 ($3 istegen)',
	'translate-checks-parameters'       => 'Kelesi baptalımdar paýdalanılmağan: <strong>$1</strong>',
	'translate-checks-balance'          => 'Mında jaqşalardıñ barlıq sanı jup emes: <strong>$1</strong>',
	'translate-checks-links'            => 'Kelesi siltemeler jaramsız: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Kelesi belgilemelerdi durıstarımen almastırıñız: <strong>$1</strong>',
	'translate-checks-plural'           => 'Anıqtalımda <nowiki>{{PLURAL:}}</nowiki> paýdalanılğan, biraq awdarmada bul joq.',
);

/** Khmer (ភាសាខ្មែរ)
 * @author Chhorran
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'translate'                         => 'ប្រែសំរួល',
	'translate-desc'                    => '[[Special:Translate|ទំព័រ​ពិសេស]] សំរាប់ប្រែសំរួល​មេឌាវិគី​ និង របស់​ផ្សេងទៀត',
	'translate-edit'                    => 'កែប្រែ',
	'translate-talk'                    => 'ពិភាក្សា',
	'translate-history'                 => 'ប្រវត្តិ',
	'translate-task-view'               => 'មើល​គ្រប់សារ ពី',
	'translate-task-untranslated'       => 'មើល​គ្រប់​សារដែល​មិនទាន់ប្រែសំរួល ពី',
	'translate-task-optional'           => 'មើល​សារជំរើស ពី',
	'translate-task-review'             => 'មើល​ឡើងវិញ​នូវបំលាស់ប្តូរ​នានា​ចំពោះ',
	'translate-task-reviewall'          => 'មើល​ឡើងវិញ​បទប្រែសំរួល​ទាំងអស់​ក្នុង',
	'translate-task-export'             => 'នាំចេញ​បទប្រែសំរួល ពី',
	'translate-task-export-to-file'     => 'នាំចេញ បទប្រែសំរួល ជាឯកសារ ពី',
	'translate-task-export-as-po'       => 'នាំចេញ​បទប្រែសំរួល​ជា​ទំរង់ អក្សរសុទ្ធ',
	'translate-page-no-such-language'   => 'ភាសាដែលបានសំដៅ គ្មានសុពលភាព ។',
	'translate-page-no-such-task'       => 'កិច្ចការដែលបានសំដៅ គ្មានសុពលភាព ។',
	'translate-page-no-such-group'      => 'ក្រុមដែលបានសំដៅ គ្មានសុពលភាព ។',
	'translate-page-settings-legend'    => 'កំណត់ នានា',
	'translate-page-task'               => 'ខ្ញុំចង់',
	'translate-page-group'              => 'ក្រុម',
	'translate-page-language'           => 'ភាសា',
	'translate-page-limit'              => 'កំរិត',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|សារ}} ក្នុងមួយទំព័រ',
	'translate-submit'                  => 'នាំមក​បង្ហាញ',
	'translate-page-navigation-legend'  => 'ត្រាច់រក',
	'translate-page-showing'            => 'កំពុងបង្ហាញ​សារ​តាមលំដាប់ ពី $1 ដល់ $2 នៃ $3 ។',
	'translate-page-showing-all'        => 'កំពុងបង្ហាញ $1 {{PLURAL:$1|សារ}}។',
	'translate-page-showing-none'       => 'គ្មានសារ​ត្រូវបង្ហាញ ។',
	'translate-next'                    => 'ទំព័របន្ទាប់',
	'translate-prev'                    => 'ទំព័រមុន',
	'translate-page-description-legend' => 'ពត៌មាន​អំពី​ក្រុម',
	'translate-optional'                => '(ជំរើស)',
	'translate-ignored'                 => '(បានបោះបង់)',
	'translate-edit-definition'         => 'និយមន័យ​របស់​សារ',
	'translate-edit-contribute'         => 'រួមចំណែក',
	'translate-edit-information'        => 'ពត៌មាន​អំពី​សារនេះ ($1)',
	'translate-edit-in-other-languages' => 'សារ​ជាភាសា​ដទៃទៀត',
	'translate-edit-committed'          => 'បទប្រែសំរួល​បច្ចុប្បន្ន​ក្នុងផ្នែកទន់',
	'translate-edit-warnings'           => 'ការព្រមាន​អំពី​បទប្រែសំរួលមិនពេញលេញ',
	'translate-magic-pagename'          => 'បទប្រែសំរួល​មេឌាវិគី​បន្ថែម',
	'translate-magic-help'              => "អ្នកអាចប្រែសំរួល ឈ្មោះក្លែង នៃ ទំព័រពិសេស, ពាក្យទិព្វ, ឈ្មោះសំបក និង ឈ្មោះ នៃវាលឈ្មោះ ។

ក្នុងពាក្យទិព្វ ត្រូវដាក់រួមទាំង បទប្រែសំរួល ភាសាអង់គ្លេស, បើមិនដូច្នោះ វាលែងធ្វើការ ។ ដាក់ផងដែរ លេខរៀងដំបូង (0 ឬ 1) តាម ដែលវាមាន ។

ឈ្មោះក្លែង នៃ ទំព័រពិសេស និង ពាក្យទិព្វ អាចមាន ច្រើនបទប្រែសំរួល ។ បទប្រែសំរួល ត្រូវបានខណ្ឌ ដោយ សញ្ញាក្បៀស (,) ។ ឈ្មោះសំបក និង វាលឈ្មោះ អាចមានត្រឹមតែ មួយបទប្រែសំរួល ។

ក្នុងបទប្រែសំរួល វាលឈ្មោះ <tt>$1 talk</tt> មាន ករណីពិសេស។ <tt>$1</tt> ត្រូវបានជំនួស ដោយ ឈ្មោះសៃថ៍ (ឧទាហរ <tt>{{SITENAME}} talk</tt> ។ បើ ភាសារបស់អ្នក មិនបាន បង្ហាញត្រឹមត្រូវ ដោយមិនប្តូរ ឈ្មោះសៃថ៍, សូមទាក់ទង អ្នកអភិវឌ្ឍ ។

អ្នកចាំបាច់ ត្រូវតែថិតក្នុង ក្រុមអ្នកប្រែសំរួល ទើបអាច រក្សាទុក បំលាស់ប្តូរ ។  បំលាស់ប្តូរ នឹងមិនត្រូវបានរក្សាទុក លើកលែងតែ អ្នកបានចុច ប្រអប់ 'រក្សាទុក' ខាងក្រោម ។",
	'translate-magic-form'              => 'ភាសា៖ $1 កញ្ចប់៖ $2 $3',
	'translate-magic-submit'            => 'នាំមក​បង្ហាញ',
	'translate-magic-cm-to-be'          => 'ទៅជា',
	'translate-magic-cm-current'        => 'បច្ចុប្បន្ន',
	'translate-magic-cm-original'       => 'ដើម',
	'translate-magic-cm-comment'        => 'វិចារ៖',
	'translate-magic-cm-save'           => 'រក្សាទុក',
	'translate-magic-cm-export'         => 'នាំចេញ',
	'translate-magic-cm-updatedusing'   => 'បានបន្ទាន់សម័យ​ដោយប្រើប្រាស់ Special:Magic',
	'translate-magic-cm-savefailed'     => 'រក្សាទុក​បានបរាជ័យ',
	'translate-magic-words'             => 'ពាក្យទិព្វ',
	'translate-magic-skin'              => 'ឈ្មោះ សំបកនានា',
	'translate-magic-namespace'         => 'ឈ្មោះនានា នៃវាលឈ្មោះ',
	'translationchanges'                => 'បំលាស់ប្តូរ នៃបំរែសំរួល',
	'translationchanges-export'         => 'នាំចេញ',
	'translate-checks-parameters'       => 'ប៉ារ៉ាម៉ែតទាំងឡាយនេះមិនត្រូវបានគេប្រើ៖ <strong>$1</strong>',
	'translate-checks-balance'          => 'មានវង់ក្រចក​ដែលមិន​មាន​គូ៖ <strong>$1</strong>',
	'translate-checks-links'            => 'តំណភ្ជាប់ទាំងនេះ​មានបញ្ហា ៖ <strong>$1</strong>',
	'translate-checks-xhtml'            => 'សូម​ជំនួស​ប្លាកទាំងនេះ​ដោយ​ប្លាកដែលត្រឹមត្រូវ ៖ <strong>$1</strong>',
	'translate-checks-plural'           => 'និយមន័យ​ប្រើប្រាស់ <nowiki>{{PLURAL:}}</nowiki> ប៉ុន្តែ​បទប្រែសំរួល​មិនមាន ។',
);


$messages['la'] = array(
	'translate' => 'Traducere',
	'translate-edit' => 'recensere',
	'translate-talk' => 'disputatio',
	'translate-history' => 'historia',
	'translate-settings' => 'Volo $1 $2 in lingua $3 cum fine $4. $5',
	'translate-next' => 'Pagina proxima',
	'translate-prev' => 'Pagina superior',
	'translate-magic-cm-save' => 'Servare',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 * @author Siebrand
 */
$messages['lb'] = array(
	'translate'                         => 'Iwwersetzt',
	'translate-desc'                    => "[[Special:Translate|Spezialsäit]] fir d'Iwwersetzung vu MediaWiki-Systemmessagen a fir Aneres",
	'translate-edit'                    => 'änneren',
	'translate-talk'                    => 'Diskussioun',
	'translate-history'                 => 'Versiounen',
	'translate-task-view'               => 'All Systemmessagen uweisen',
	'translate-task-untranslated'       => 'All net iwwersate Systemmessagen uweisen',
	'translate-task-optional'           => 'Optional Messagen uweisen',
	'translate-task-review'             => 'Ännerungen uweisen',
	'translate-task-reviewall'          => 'All Iwwersetzungen nokucken',
	'translate-task-export'             => 'All Iwwersetzunge exportéieren',
	'translate-task-export-to-file'     => "D'Iwwersetzung an e Fichier exportéieren",
	'translate-task-export-as-po'       => "Iwwersetzung an de ''Gettext Format'' exportéieren",
	'translate-page-no-such-language'   => 'Ongëltege Sproochcode benotzt',
	'translate-page-no-such-task'       => 'Déi gefroten Aufgab gëtt et net.',
	'translate-page-no-such-group'      => 'Déi Gefrote Grupp gëtt et net.',
	'translate-page-settings-legend'    => 'Astellungen',
	'translate-page-task'               => 'Ech wëll',
	'translate-page-group'              => 'Grupp',
	'translate-page-language'           => 'Sprooch',
	'translate-page-limit'              => 'Maximum',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|Message|Message}} pro Säit',
	'translate-submit'                  => 'Uweisen',
	'translate-page-navigation-legend'  => 'Navigatioun',
	'translate-page-showing'            => "D'Message vun $1 bis $2 vun am Ganzen $3 gi gewisen.",
	'translate-page-showing-all'        => '$1 {{PLURAL:$1|Message|Message}} gi gewisen',
	'translate-page-showing-none'       => 'Kee Message fir ze weisen',
	'translate-next'                    => 'Nächst Säit',
	'translate-prev'                    => 'Virescht Säit',
	'translate-page-description-legend' => 'Informatiounen iwwert de Grupp',
	'translate-optional'                => '(optional)',
	'translate-ignored'                 => '(ignoréiert)',
	'translate-edit-definition'         => 'Definitioun vum Message',
	'translate-edit-contribute'         => 'mathëllefen',
	'translate-edit-no-information'     => 'Dëse Message huet keng Dokumentatioun. Wann Dir wësst wou oder wéi dëse Message gebraucht gëtt, da kënnt Dir aneren Iwwersetzer hëllefen an dem dir Informatiounen iwwert dëse Message gitt.',
	'translate-edit-information'        => 'Informatioun iwwert dëse Message ($1)',
	'translate-edit-in-other-languages' => 'Message an anere Sproochen',
	'translate-edit-committed'          => 'Aktuell Iwwersetzung an der Software',
	'translate-edit-warnings'           => 'Warnunge virun onkompletten Iwwersetzungen',
	'translate-magic-pagename'          => 'Erweidert MediaWiki Iwwersetzung',
	'translate-magic-form'              => 'Sprooch: $1: Modul: $2 $3',
	'translate-magic-submit'            => 'Weisen',
	'translate-magic-cm-to-be'          => 'Gëtt',
	'translate-magic-cm-current'        => 'Aktuell',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Ersatzsprooch',
	'translate-magic-cm-comment'        => 'Bemierkung:',
	'translate-magic-cm-save'           => 'Späicheren',
	'translate-magic-cm-export'         => 'Export',
	'translate-magic-cm-updatedusing'   => 'Geännert ma Hëllef vu Spezial:Magic',
	'translate-magic-cm-savefailed'     => 'Späicheren huet net fonktionéiert',
	'translate-magic-special'           => "Spezialsäit vun den 'Aliasnimm'",
	'translate-magic-words'             => 'Magesch Wierder',
	'translate-magic-skin'              => 'Numm vum Interface (Skin)',
	'translate-magic-namespace'         => 'Nummraum Nimm',
	'translationchanges'                => 'Iwwersetzung ännert',
	'translationchanges-export'         => 'exportéieren',
	'translationchanges-change'         => '$1: $2 vun $3',
	'translate-checks-parameters'       => 'Dës Parameter ginn net benotzt: <strong>$1</strong>',
	'translate-checks-balance'          => 'Et gëtt eng ongerued Zuel vu Klammere benotzt: <strong>$1</strong>',
	'translate-checks-links'            => 'Dës Linke si problematesch: <strong>$1</strong>',
	'translate-checks-xhtml'            => "Ersetzt dës ''Tag''en w.e.g. duerch déi korrekt: <strong>$1</strong>",
	'translate-checks-plural'           => "D'Definitioun benotzt <nowiki>{{PLURAL:}}</nowiki> awer d'Iwwersetzung mécht dat net.",
);

/** Limburgish (Limburgs)
 * @author Ooswesthoesbes
 */
$messages['li'] = array(
	'translate'                     => 'Vertale',
	'translate-edit'                => 'bewèrk',
	'translate-talk'                => 'euverlèk',
	'translate-history'             => 'gesjiedenis',
	'translate-task-view'           => 'Laot alle berichter zeen van',
	'translate-task-untranslated'   => 'Laot alle ónvertäölde berichter zeen van',
	'translate-task-optional'       => 'optioneel berich bekieke',
	'translate-task-review'         => 'verangeringe keterlieëre',
	'translate-task-reviewall'      => 'alle vertalinge keterlieëre',
	'translate-task-export'         => 'vertalinge exportieëre',
	'translate-task-export-to-file' => 'vertalinge nao bestandj exportieëre',
	'translate-task-export-as-po'   => 'vertalinge nao Gettext-formaat exportieëre',
	'translate-page-task'           => 'Ich wil',
	'translate-page-group'          => 'Groep',
	'translate-page-language'       => 'Taal',
	'translate-page-limit'          => 'Maximaal',
	'translate-submit'              => 'Ophaole',
	'translate-next'                => 'Volgende pazjena',
	'translate-prev'                => 'Vörge pazjena',
	'translate-optional'            => '(optioneel)',
	'translationchanges-export'     => 'exportieëre',
);

/** Lao (ລາວ)
 * @author Passawuth
 */
$messages['lo'] = array(
	'translate-desc' => '[[Special:Translate|ໜ້າພິເສດ]]ສຳຫຼັບແປມີເດຍວິກິແລະອື່ນ າ',
);

/** Lithuanian (Lietuvių)
 * @author Vpovilaitis
 * @author Garas
 * @author Siebrand
 */
$messages['lt'] = array(
	'translate'                         => 'Vertimas',
	'translate-edit'                    => 'redaguoti',
	'translate-talk'                    => 'aptarimas',
	'translate-history'                 => 'istorija',
	'translate-task-view'               => 'Pažiūrėti visus pranešimus iš',
	'translate-task-untranslated'       => 'Pažiūrėti visus neišverstus pranešimus iš',
	'translate-task-optional'           => 'Pažiūrėti nebūtinus pranešimus iš',
	'translate-task-review'             => 'Peržiūrėti pakeitimus iš',
	'translate-task-reviewall'          => 'Peržiūrėti visus vertimus iš',
	'translate-task-export'             => 'Eksportuoti vertimus iš',
	'translate-task-export-to-file'     => 'Eksportuoti į failą vertimus iš',
	'translate-task-export-as-po'       => 'Eksportuoti vertimą Gettext formatu',
	'translate-page-no-such-language'   => 'Buvo nurodytas klaidingas kalbos kodas',
	'translate-page-settings-legend'    => 'Nustatymai',
	'translate-page-task'               => 'Aš noriu',
	'translate-page-group'              => 'Grupė',
	'translate-page-language'           => 'Kalba',
	'translate-page-limit'              => 'Limitas',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|pranešimas|pranešimai|pranešimų}} puslapyje',
	'translate-submit'                  => 'Išrinkti',
	'translate-page-navigation-legend'  => 'Navigacija',
	'translate-page-showing'            => 'Rodomi pranešimai nuo $1 iki $2 iš $3.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Rodomas $1 pranešimas|Rodomi $1 pranešimai|Rodoma $1 pranešimų}}.',
	'translate-page-showing-none'       => 'Nėra pranešimų rodymui.',
	'translate-next'                    => 'Kitas puslapis',
	'translate-prev'                    => 'Ankstesnis puslapis',
	'translate-page-description-legend' => 'Informacija apie grupę',
	'translate-optional'                => '(nebūtinas)',
	'translate-ignored'                 => '(ignoruojamas)',
	'translate-edit-definition'         => 'Pranešimo aprašymas',
	'translate-edit-contribute'         => 'papildyti',
	'translate-edit-no-information'     => "''Šis pranešimas dar neturi dokumentacijos. Jei žinote kur ar kaip šis pranešimas naudojamas, jūs galite padėti kitiems vertėjams pridėdami dokumentacijos į šį pranešimą.''",
	'translate-edit-information'        => 'Informacija apie šį pranešimą ($1)',
	'translate-magic-pagename'          => 'MediaWiki išplėtimų vertimas',
	'translate-magic-help'              => 'Jūs galite išversti specialių puslapių pavadinimus, magiškus žodžius, apvalkalų pavadinimus ir vardų sričių pavadinimus.

Magiško žodžio vertimuose nurodykite ir vertimą į anglų kalbą, kitaip jis nustos veikti. Taip pat palikite pirmąjį elementą (0 arba 1) tokį koks jis yra.

Specialiojo puslapio pavadinimo ir magiško žodžio vertimai gali būti keli. Vertimai yra skiriami kableliu (,). Apvalkalo ir vardų srities pavadinimas gali turėti tik vieną vertimą.

Vardų sričių vertimuose <tt>$1 aptarimas</tt> yra specialus. <tt>$1</tt> yra pakeičiamas svetainės pavadinimu (Pavyzdžiui <tt>{{SITENAME}} aptarimas</tt>. Jei nėra galimybės Jūsų kalboje suformuoti teisingos išraiškos su svetainės pavadinimo pakeitimu, prašome kreiptis į kūrėjus.

Jūs turite priklausyti vertėjų grupei, kad galėtumėte išsaugoti pakeitimus. Pakeitimai nebus išsaugoti iki Jūs nuspausite išsaugojimo butoną apačioje.',
	'translate-magic-form'              => 'Kalba: $1 Tema: $2 $3',
	'translate-magic-submit'            => 'Išrinkti',
	'translate-magic-cm-to-be'          => 'Turi būti',
	'translate-magic-cm-current'        => 'Einamasis',
	'translate-magic-cm-original'       => 'Originalas',
	'translate-magic-cm-fallback'       => 'Atsarginė priemonė',
	'translate-magic-cm-save'           => 'Išsaugoti',
	'translate-magic-cm-export'         => 'Eksportuoti',
	'translate-magic-cm-updatedusing'   => 'Atnaujintas, naudojant Special:Magic',
	'translate-magic-cm-savefailed'     => 'Nepavyko išsaugoti',
	'translate-magic-special'           => 'Specialių puslapių pavadinimai',
	'translate-magic-words'             => 'Magiški žodžiai',
	'translate-magic-skin'              => 'Apvalkalų pavadinimai',
	'translate-magic-namespace'         => 'Vardų srities pavadinimai',
	'translationchanges'                => 'Vertimo pakeitimai',
	'translationchanges-export'         => 'eksportuoti',
	'translationchanges-change'         => '$1: $2 pagal $3',
	'translate-checks-plural'           => 'Aprašymas naudoja <nowiki>{{PLURAL:}}</nowiki>, bet vertimas ne.',
);

/** Malayalam (മലയാളം)
 * @author Jacob.jose
 */
$messages['ml'] = array(
	'translate'                  => 'വിവര്‍ത്തനം ചെയ്യുക',
	'translate-page-showing'     => '$3 സന്ദേശങ്ങളുള്ളതില്‍ $1 മുതല്‍ $2 വരെയുള്ളവ പ്രദര്‍ശിപ്പിച്ചിരിക്കുന്നു',
	'translate-page-showing-all' => '$1 {{PLURAL:$1|സന്ദേശം|സന്ദേശങ്ങള്‍}} പ്രദര്‍ശിപ്പിക്കുന്നു.',
);

/** Marathi (मराठी)
 * @author Mahitgar
 */
$messages['mr'] = array(
	'translate'                         => 'भाषांतर करा',
	'translate-desc'                    => 'मिडीयाविकि आणि त्या पलीकडील भाषांतरणे करण्याकरिता [[Special:Translate|विशेष पान]]',
	'translate-edit'                    => 'संपादन',
	'translate-talk'                    => 'चर्चा',
	'translate-history'                 => 'इतिहास',
	'translate-task-view'               => 'खालीलवर्गाचे सारे सदेश बघावे',
	'translate-task-untranslated'       => 'मधील सर्व अभाषांतरीत संदेश बघावे',
	'translate-task-optional'           => 'चे पर्यायी संदेश बघावे',
	'translate-task-review'             => 'चे बदल तपासा',
	'translate-task-reviewall'          => 'मधील सर्व भाषांतरणे तपासा',
	'translate-task-export'             => 'कडून भाषांतरणे निर्यात करा',
	'translate-page-task'               => 'मी इच्छीतो की',
	'translate-page-group'              => 'गट',
	'translate-page-language'           => 'भाषा',
	'translate-page-limit'              => 'मर्यादा',
	'translate-page-limit-option'       => 'प्रतिपान {{PLURAL:$1|संदेश|संदेश}}$1',
	'translate-submit'                  => 'शेंदा(ओढा)',
	'translate-page-navigation-legend'  => 'सुचालन',
	'translate-page-showing'            => '$3चे $1पासून $2पर्यंत संदेश दाखवत आहे.',
	'translate-page-showing-all'        => '$1 {{PLURAL:$1|संदेश|संदेश}} दाखवत आहे .',
	'translate-next'                    => 'पुढील पान',
	'translate-prev'                    => 'मागील पान',
	'translate-page-description-legend' => 'गटाबद्दल माहिती',
	'translate-optional'                => 'पर्यायी',
	'translate-ignored'                 => '(दुर्लक्षीत)',
	'translate-edit-definition'         => 'संदेश व्याख्या',
	'translate-edit-contribute'         => 'योगदान करा',
	'translate-edit-no-information'     => "''या संदेशाकरिता कोणतेही नोंदीकरण(डॉक्यूमेंटेशन) नाही. हा संदेश कुठे आणि कसा वापरला आहे हे तुम्हाला ठावूक असेल तर, या पानाचे नोंदीकरण(डॉक्यूमेंटेशन) करून तुम्ही इतर भाषांतरकारांना मदत करू शकता.''",
	'translate-edit-information'        => '($1)या संदेशाबद्दल माहिती',
	'translate-edit-in-other-languages' => 'इतर भाषातील संदेश',
	'translate-edit-committed'          => 'संकेतन प्रणालीमधील सध्याचे भाषांतरण',
	'translate-magic-form'              => 'भाषा: $1 मॉड्यूल: $2 $3',
	'translate-magic-submit'            => 'ओढा',
	'translate-magic-cm-to-be'          => 'अपेक्षीत',
	'translate-magic-cm-current'        => 'सद्य',
	'translate-magic-cm-original'       => 'मूळ',
	'translate-magic-cm-comment'        => 'प्रतिक्रीया',
	'translate-magic-cm-save'           => 'जतन करा',
	'translate-magic-cm-export'         => 'नीर्यात',
	'translate-magic-cm-savefailed'     => 'जतन अयशस्वी',
	'translate-magic-special'           => 'विशेष पान टोपणनावे',
	'translate-magic-words'             => 'जादूई शब्द',
	'translate-magic-skin'              => 'त्वचेचे नाव',
	'translate-magic-namespace'         => 'नामविश्व नावे',
	'translationchanges-export'         => 'नीर्यात',
	'translationchanges-change'         => '$1: $2 ने $3',
);

$messages['nap'] = array(
	'translate-edit' => 'càgna',
	'translate-talk' => 'chiàcchiera',
	'translate-history' => 'cronologgia',
);

$messages['nds'] = array(
	'translate' => 'Översetten',
	'translate-edit-message-format' => 'Format vun disse Naricht is \'\'\'$1\'\'\'.',
	'translate-edit-message-in' => 'Disse Narichtentext op \'\'\'$1\'\'\' ($2):',
	'translate-edit-message-in-fb' => 'Disse Narichtentext in de Trüchfall-Spraak \'\'\'$1\'\'\' ($2):',
);

/** Dutch (Nederlands)
 * @author Siebrand
 * @author SPQRobin
 */
$messages['nl'] = array(
	'translate'                         => 'Vertalen',
	'translate-desc'                    => '[[Special:Translate|Speciale pagina]] voor het vertalen van MediaWiki en meer',
	'translate-edit'                    => 'bewerken',
	'translate-talk'                    => 'overleg',
	'translate-history'                 => 'geschiedenis',
	'translate-task-view'               => 'alle teksten bekijken',
	'translate-task-untranslated'       => 'alle onvertaalde teksten bekijken',
	'translate-task-optional'           => 'optionele berichten bekijken',
	'translate-task-review'             => 'wijzigingen controleren',
	'translate-task-reviewall'          => 'alle vertalingen controleren',
	'translate-task-export'             => 'vertalingen exporteren',
	'translate-task-export-to-file'     => 'vertalingen naar bestand exporteren',
	'translate-task-export-as-po'       => 'vertalingen naar Gettext-formaat exporteren',
	'translate-page-no-such-language'   => 'Er is een ongeldige taalcode opgegeven',
	'translate-page-no-such-task'       => 'De aangegeven functie bestaat niet.',
	'translate-page-no-such-group'      => 'De aangegeven groep bestaat niet.',
	'translate-page-settings-legend'    => 'Instellingen',
	'translate-page-task'               => 'Ik wil',
	'translate-page-group'              => 'Groep',
	'translate-page-language'           => 'Taal',
	'translate-page-limit'              => 'Maximaal',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|bericht|berichten}} per pagina',
	'translate-submit'                  => 'Ophalen',
	'translate-page-navigation-legend'  => 'Navigatie',
	'translate-page-showing'            => 'De berichten $1 tot $2 van $3 worden getoond.',
	'translate-page-showing-all'        => 'Er {{PLURAL:$1|wordt 1 bericht|worden $1 berichten}} getoond.',
	'translate-page-showing-none'       => 'Er zijn geen berichten in deze selectie.',
	'translate-next'                    => 'volgende',
	'translate-prev'                    => 'vorige',
	'translate-page-description-legend' => 'Informatie over de groep',
	'translate-optional'                => '(optioneel)',
	'translate-ignored'                 => '(genegeerd)',
	'translate-edit-definition'         => 'Berichtdefinitie',
	'translate-edit-contribute'         => 'bijdragen',
	'translate-edit-no-information'     => "''Dit bericht heeft geen documentatie.
Als u weet waar dit bericht wordt gebruikt, dan kunt u andere gebruikers helpen door documentatie voor dit bericht toe te voegen.''",
	'translate-edit-information'        => 'Informatie over dit bericht ($1)',
	'translate-edit-in-other-languages' => 'Bericht in andere talen',
	'translate-edit-committed'          => 'Huidig bericht in software',
	'translate-edit-warnings'           => 'Waarschuwingen over onjuiste vertalingen',
	'translate-magic-pagename'          => 'Uitgebreide MediaWiki-vertaling',
	'translate-magic-help'              => 'U kunt alternatieven voor speciale pagina\'s, magische woorden, skinnamen en naamruimtebenamingen vertalen.

In magische woorden moet u de Engelstalige vertalingen opnemen, omdat ze anders niet meer werken. Laat ook de eerste cijfers (0 of 1) ongewijzigd.

Alternatieven voor speciale pagina\'s en magische woorden kunnen meerdere vertalingen hebben. Scheid vertalingen met een komma (,). Skinnamen en naamruimtebenamingen kunnen slechts één vertaling hebben.

In naamruimtebenamingen is <tt>$1 talk</tt> een uitzondering. <tt>$1</tt> wordt vervangen door de sitenaam (bijvoorbeeld <tt>{{SITENAME}} talk</tt>. Als het in uw taal niet mogelijk is een geldige uitdrukking te vormen zonder de sitenaam te wijzigen, neem dan contact op met een ontwikkelaar.

Om wijzigingen op te slaan moet u lid zijn van de groep vertalers. Wijzigingen worden niet bewaard totdat u op "Pagina opslaan" hebt geklikt.',
	'translate-magic-form'              => 'Taal: $1 Module: $2 $3',
	'translate-magic-submit'            => 'Ophalen',
	'translate-magic-cm-to-be'          => 'Toekomstig',
	'translate-magic-cm-current'        => 'Huidig',
	'translate-magic-cm-original'       => 'Oorspronkelijk',
	'translate-magic-cm-fallback'       => 'Alternatief',
	'translate-magic-cm-comment'        => 'Samenvatting:',
	'translate-magic-cm-save'           => 'Opslaan',
	'translate-magic-cm-export'         => 'Exporteren',
	'translate-magic-cm-updatedusing'   => 'Bijgewerkt via Special:Magic',
	'translate-magic-cm-savefailed'     => 'Opslaan mislukt',
	'translate-magic-special'           => "Alternatieven speciale pagina's",
	'translate-magic-words'             => 'Magische woorden',
	'translate-magic-skin'              => 'Skinnamen',
	'translate-magic-namespace'         => 'Naamruimtebenamingen',
	'translationchanges'                => 'Vertalingen',
	'translationchanges-export'         => 'exporteren',
	'translationchanges-change'         => '$1: $2 door $3',
	'translate-checks-parameters'       => 'De volgende parameters worden niet gebruikt: <strong>$1</strong>',
	'translate-checks-balance'          => 'Er wordt een oneven aantal haakjes gebruikt: <strong>$1</strong>',
	'translate-checks-links'            => 'De volgende links zijn problematisch: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Vervang de volgende tags door de juiste: <strong>$1</strong>',
	'translate-checks-plural'           => 'De definitie bevat <nowiki>{{PLURAL:}}</nowiki>, maar de vertaling niet.',
);

/** Norwegian (‪Norsk (bokmål)‬)
 * @author Jon Harald Søby
 * @author Siebrand
 */
$messages['no'] = array(
	'translate'                         => 'Oversett',
	'translate-desc'                    => '[[Special:Translate|Spesialside]] for oversettelse av MediaWiki o.a.',
	'translate-edit'                    => 'rediger',
	'translate-talk'                    => 'diskusjon',
	'translate-history'                 => 'historikk',
	'translate-task-view'               => 'se alle beskjeder',
	'translate-task-untranslated'       => 'se alle uoversatte beskjeder',
	'translate-task-optional'           => 'se valgfrie beskjeder',
	'translate-task-review'             => 'gå gjennom endringer',
	'translate-task-reviewall'          => 'gå gjennom oversettelser',
	'translate-task-export'             => 'eksportere oversettelser',
	'translate-task-export-to-file'     => 'eksportere oversettelser til fil',
	'translate-task-export-as-po'       => 'eksportere oversettelser i Gettext-format',
	'translate-page-no-such-language'   => 'Ugyldig språkkode angitt.',
	'translate-page-no-such-task'       => 'Ugyldig oppgave angitt.',
	'translate-page-no-such-group'      => 'Ugyldig gruppe angitt.',
	'translate-page-settings-legend'    => 'Innstillinger',
	'translate-page-task'               => 'Jeg vil',
	'translate-page-group'              => 'Gruppe',
	'translate-page-language'           => 'Språk',
	'translate-page-limit'              => 'Grense',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|beskjed|beskjeder}} per side',
	'translate-submit'                  => 'Hent',
	'translate-page-navigation-legend'  => 'Navigasjon',
	'translate-page-showing'            => 'Viser beskjeder fra $1 til $2 av $3.',
	'translate-page-showing-all'        => 'Viser {{PLURAL:$1|én beskjed|$1 beskjeder}}.',
	'translate-page-showing-none'       => 'Ingen beskjeder å vise.',
	'translate-next'                    => 'Neste side',
	'translate-prev'                    => 'Forrige side',
	'translate-page-description-legend' => 'Informasjon om gruppa',
	'translate-optional'                => '(valgfri)',
	'translate-ignored'                 => '(ignorert)',
	'translate-edit-definition'         => 'Beskjeden som skal oversettes',
	'translate-edit-contribute'         => 'bidra',
	'translate-edit-no-information'     => "''Denne beskjeden har ikke dokumentasjon. Om du vet hvor eller hvordan denne beskjeden brukes, kan du hjelpe andre oversettere ved å legge inn dokumentasjon til denne beskjeden.''",
	'translate-edit-information'        => 'Informasjon om denne beskjeden ($1)',
	'translate-edit-in-other-languages' => 'Beskjeden på andre språk',
	'translate-edit-committed'          => 'Nåværende oversettelse',
	'translate-edit-warnings'           => 'Advarsler om ufullstendige oversettelser',
	'translate-magic-pagename'          => 'Utvidet MediaWiki-oversettelse',
	'translate-magic-help'              => 'Du kan oversette spesialsidenavn, magiske ord, utseendenavn og navneromnavn.

I magiske ord må du inkludere engelskspråklige oversettelser, ellers vil de ikke fungere. La også det første punktet (0 eller 1) være som det er.

Spesialsidenavn og magiske ord kan ha flere oversettelser. Oversettelser skilles med et komma (,). Utseendenavn og navnerom kan kun ha én oversettelse.

I navneromoversettelsene er <tt>$1 talk</tt> spesiell. <tt>$1</tt> erstattes med sidens navn (for eksempel <tt>{{SITENAME}}</tt>. Om det ikke er mulig å få til et gyldig uttrykk på ditt språk her uten å endre sidenavnet, kontakt en utvikler.

Du må være i oversettergruppa for å lagre endringer. Endringer lagres ikke før du klikker på lagre-knappen nedenfor.',
	'translate-magic-form'              => 'Språk: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Skaff',
	'translate-magic-cm-to-be'          => 'Framtidig',
	'translate-magic-cm-current'        => 'Nåværende',
	'translate-magic-cm-original'       => 'Opprinnelig',
	'translate-magic-cm-fallback'       => 'Reserve',
	'translate-magic-cm-comment'        => 'Kommentar:',
	'translate-magic-cm-save'           => 'Lagre',
	'translate-magic-cm-export'         => 'Eksporter',
	'translate-magic-cm-updatedusing'   => 'Oppdatert vha. Special:Magic',
	'translate-magic-cm-savefailed'     => 'Lagring mislyktes',
	'translate-magic-special'           => 'Spesialsidenavn',
	'translate-magic-words'             => 'Magiske ord',
	'translate-magic-skin'              => 'Utseendenavn',
	'translate-magic-namespace'         => 'Navneromnavn',
	'translationchanges'                => 'Oversettelsesendringer',
	'translationchanges-export'         => 'eksporter',
	'translationchanges-change'         => '$1: $2 av $3',
	'translate-checks-parameters'       => 'Følgende parametere brukes ikke: <strong>$1</strong>',
	'translate-checks-balance'          => 'Det er et ujevnt antall parenteser: <strong>$1</strong>',
	'translate-checks-links'            => 'Følgende lenker er problematiske: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Erstatt følgende tagger med de korrekte: <strong>$1</strong>',
	'translate-checks-plural'           => 'Definisjonen bruker <nowiki>{{PLURAL:}}</nowiki>, men oversettelsen gjør ikke det.',
);

/** Occitan (Occitan)
 * @author Cedric31
 * @author ChrisPtDe
 */
$messages['oc'] = array(
	'translate'                         => 'Traduire',
	'translate-desc'                    => '[[Special:Translate|Pagina especiala]] per traduire Mediawiki e quitament mai encara.',
	'translate-edit'                    => 'Edicion',
	'translate-talk'                    => 'Discussion',
	'translate-history'                 => 'Istoric',
	'translate-task-view'               => 'Veire totes los messatges dempuèi',
	'translate-task-untranslated'       => 'Veire totes los messatges pas tradusits dempuèi',
	'translate-task-optional'           => 'Veire totes los messatges facultatius dempuèi',
	'translate-task-review'             => 'Tornar veire mos cambiaments dempuèi',
	'translate-task-reviewall'          => 'Tornar veire totas las traduccions dins',
	'translate-task-export'             => 'Exportar las traduccions dempuèi',
	'translate-task-export-to-file'     => 'Exportar las traduccions dins un fiquièr dempuèi',
	'translate-task-export-as-po'       => 'Exportar las traduccions al format Gettext',
	'translate-page-no-such-language'   => 'Un còde de lengatge invalid es estat indicat',
	'translate-page-no-such-task'       => 'Lo prètzfach especificat es invalid.',
	'translate-page-no-such-group'      => 'Lo grop especificat es invalid.',
	'translate-page-settings-legend'    => 'Configuracion',
	'translate-page-task'               => 'Vòli',
	'translate-page-group'              => 'Grop',
	'translate-page-language'           => 'Lenga',
	'translate-page-limit'              => 'Limit',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|messatge|messatges}} per pagina',
	'translate-submit'                  => 'Aténher',
	'translate-page-navigation-legend'  => 'Navigacion',
	'translate-page-showing'            => 'Visualizacion dels messatges de $1 a $2 sus $3.',
	'translate-page-showing-all'        => 'Visualizacion de $1 {{PLURAL:$1|messatge|messatges}}.',
	'translate-page-showing-none'       => 'Cap de messatge de visualizar.',
	'translate-next'                    => 'Pagina seguenta',
	'translate-prev'                    => 'Pagina precedenta',
	'translate-page-description-legend' => 'Informacion a prepaus del grop',
	'translate-optional'                => '(opcional)',
	'translate-ignored'                 => '(ignorat)',
	'translate-edit-definition'         => 'Definicion del messatge',
	'translate-edit-contribute'         => 'contribuir',
	'translate-edit-no-information'     => 'Actualament, aqueste messatge es pas documentat. Se sabètz ont o cossí aqueste messatge es utilizat, podètz ajudar los autres traductors en documentant aqueste messatge.',
	'translate-edit-information'        => 'Informacions concernent aqueste messatge ($1)',
	'translate-edit-in-other-languages' => 'Messatge dins las autras lengas',
	'translate-edit-committed'          => 'Traduccions actualas ja dins lo logicial',
	'translate-edit-warnings'           => 'Avertiments concernent las traduccions incomplètas',
	'translate-magic-pagename'          => 'Traduccion de MediaWiki espandida',
	'translate-magic-help'              => "Podètz traduire los alias de paginas especialas, los mots magics, los noms de skins e los noms d'espacis de noms. Dins los mots magics, devètz inclòure la traduccion en anglés o aquò foncionarà pas mai. E mai, daissatz lo primièr item (0 o 1) coma es. Los alias de paginas especialas e los mots magics pòdon aver mantuna traduccion. Las traduccions son separadas per una virgula (,). Los noms de skins e d'espacis de noms pòdon pas aver qu'una traduccion. Dins las traduccions d'espacis de noms, <tt>$1 talk</tt> es especial. <tt>$1</tt> es remplaçat pel nom del sit (per exemple <tt>{{SITENAME}} talk</tt>). Se es pas possible d'obténer una expression valida dins vòstra lenga sens cambiar lo nom del sit, contactatz un desvolopaire. Devètz aparténer al grop dels traductors per salvagardar los cambiaments. Los cambiaments seràn pas salvagardats abans que cliquèssetz sul boton Salvagardar en bas.",
	'translate-magic-form'              => 'Lenga $1 Modul : $2 $3',
	'translate-magic-submit'            => 'Anar',
	'translate-magic-cm-to-be'          => 'Desven',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Tornar',
	'translate-magic-cm-comment'        => 'Comentari :',
	'translate-magic-cm-save'           => 'Salvagadar',
	'translate-magic-cm-export'         => 'Exportar',
	'translate-magic-cm-updatedusing'   => 'Mesa a jorn en utilizant Special:Magic',
	'translate-magic-cm-savefailed'     => 'La salvagàrdia a pas capitat',
	'translate-magic-special'           => 'Pagina especiala d’alias',
	'translate-magic-words'             => 'Mots magics',
	'translate-magic-skin'              => 'Nom de las interfàcias',
	'translate-magic-namespace'         => 'Intitolat dels espacis de nomenatge',
	'translationchanges'                => 'Modificacions a las traduccions',
	'translationchanges-export'         => 'exportar',
	'translationchanges-change'         => '$1: [[Mediawiki:$2|$2]] per [[User:$3|$3]]',
	'translate-checks-parameters'       => 'Los paramètres seguents son pas utilizats : <strong>$1</strong',
	'translate-checks-balance'          => 'I a un nombre incorrècte de parentèsis : <strong>$1</strong>',
	'translate-checks-links'            => 'Los ligams seguents son dobtoses : <strong>$1</strong',
	'translate-checks-xhtml'            => 'Sètz convidats a tornar metre en plaça las balisas seguentas amb las que son corrèctas : <strong>$1</strong>',
	'translate-checks-plural'           => 'La definicion utiliza <nowiki>{{PLURAL:}}</nowiki> mas pas la traduccion',
);

/* Piedmontese (Bèrto 'd Sèra) */
$messages['pms'] = array(
	'translate' => 'Viragi',
	'translate-edit' => 'modìfica',
	'translate-talk' => 'discussion',
	'translate-history' => 'stòria',
	'translate-task-view' => 'smon-e tuti ij messagi ëd',
	'translate-task-untranslated' => 'Smon-e tuti ij messagi nen virà ëd',
	'translate-task-optional' => 'Smon-e ij messagi opsionaj ëd',
	'translate-task-review' => 'Controlé le modìfiche a',
	'translate-task-reviewall' => 'Controlé tuti ij viragi ëd',
	'translate-task-export' => 'Esporté ij viragi ëd',
	'translate-task-export-to-file' => 'Esporté ij viragi ant n\'archivi da',
	'translate-settings' => 'I veuj $1 $2 an $3 con lìmit $4. $5',
	'translate-paging' => '<div>Messagi smonù da $1 a $2 ëd $3. [ $4 | $5 ]</div>',
	'translate-submit' => 'Smon-e',
	'translate-next' => 'Pàgina anans',
	'translate-prev' => 'Pàgina andré',
	'translate-optional' => '(opsional)',
	'translate-ignored' => '(ignorà)',
	'translate-edit-message-format' => 'La forma d\'ës messagi-sì a l\'é <b>$1</b>.',
	'translate-edit-message-in' => 'Espression corenta an <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Espression corenta ant la lenga ëd riserva <b>$1</b> ($2):',
	'translate-magic-pagename' => 'Viragi estèis ëd MediaWiki',
	'translate-magic-help' => 'A peul viré j\'àlias dle pàgine speciaj, le paròle màgiche, ij nòm dle facie e coj djë spassi nominaj. Con le paròle màgiche a venta ch\'a buta ëdcò ël viragi n\'anglèis, che dësnò a travajo pa pì. Ch\'a vardo ëdcò dë lassé ël prim element (0 or 1) tanme ch\'a lo treuva. J\'àlias dle pàgine soeciaj e le paròle màgiche a peulo avej pì che un viragi. Ij viragi a son separà da vìrgole (,). Ij nòm dle facie e djë spassi nominaj a peulo avej mach un viragi. Ant ël viragi djë spassi nominaj ël cas ëd <tt>$1 talk</tt> a l\'é special. <tt>$1</tt> a ven arpiassà col nòm dël sit (pr\'esempi <tt>{{SITENAME}} talk</tt>). Se sòn as peul nen fesse an soa lenga për rivé a n\'espression bon-a sensa cambié ël nòm dël sit, për piasì, ch\'as buta an contat con un programista. A venta ch\'a sia ant la partìa dij tradutor për podej salvé soe modìfiche. Le modìfiche as salvo nen fin ch\'a-i da nen un colp ansima al al boton ambelessì sota.',
	'translate-magic-form' => 'Lenga: $1 Mòdulo: $2 $3',
	'translate-magic-submit' => 'Smon-e',
	'translate-magic-cm-to-be' => 'da esse',
	'translate-magic-cm-current' => 'Corent',
	'translate-magic-cm-original' => 'Original',#identical but defined
	'translate-magic-cm-fallback' => 'Emergensa',
	'translate-magic-cm-save' => 'Salvé',
	'translate-magic-cm-export' => 'Esporté',
	'translate-magic-cm-updatedusing' => 'Agiornà ën dovrand Special:Magic',
	'translate-magic-cm-savefailed' => 'Salvatagi falì',
	'translate-magic-special' => 'Àlias dle pàgine speciaj',
	'translate-magic-words' => 'Paròle màgiche',
	'translate-magic-skin' => 'Nòm dle facie',
	'translate-magic-namespace' => 'Nòm djë spassi nominaj',
	'translationchanges' => 'Modìfiche ëd viragi',
);

/** Portuguese (Português)
 * @author 555
 * @author Malafaya
 */
$messages['pt'] = array(
	'translate'                         => 'Traduzir',
	'translate-desc'                    => '[[{{ns:special}}:Translate|Página especial]] para traduzir o MediaWiki e mais',
	'translate-edit'                    => 'editar',
	'translate-talk'                    => 'disc',
	'translate-history'                 => 'histórico',
	'translate-task-view'               => 'Ver todas as mensagens de',
	'translate-task-untranslated'       => 'Ver todas as mensagens não traduzidas de',
	'translate-task-optional'           => 'Ver mensagens opcionais de',
	'translate-task-review'             => 'Rever alterações em',
	'translate-task-reviewall'          => 'Rever todas as traduções em',
	'translate-task-export'             => 'Exportar traduções de',
	'translate-task-export-to-file'     => 'Exportar para ficheiro as traduções de',
	'translate-task-export-as-po'       => 'Exportar tradução em formato Gettext',
	'translate-page-no-such-language'   => 'A língua especificada é inválida',
	'translate-page-no-such-task'       => 'A tarefa especificada é inválida',
	'translate-page-no-such-group'      => 'O grupo especificado é inválido.',
	'translate-page-settings-legend'    => 'Configurações',
	'translate-page-task'               => 'Eu desejo',
	'translate-page-group'              => 'Grupo',
	'translate-page-language'           => 'Língua',
	'translate-page-limit'              => 'Limite',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|mensagem|mensagens}} por página',
	'translate-submit'                  => 'Trazer',
	'translate-page-navigation-legend'  => 'Navegação',
	'translate-page-showing'            => 'Exibindo mensagens de $1 a $2 de $3.',
	'translate-page-showing-all'        => 'Exibindo $1 {{PLURAL:$1|mensagem|mensagens}}.',
	'translate-page-showing-none'       => 'Não há mensagens a serem exibidas.',
	'translate-next'                    => 'Página seguinte',
	'translate-prev'                    => 'Página anterior',
	'translate-page-description-legend' => 'Informação sobre o grupo',
	'translate-optional'                => '(opcional)',
	'translate-ignored'                 => '(ignorada)',
	'translate-edit-definition'         => 'Definição da mensagem',
	'translate-edit-contribute'         => 'contribua',
	'translate-edit-no-information'     => "''Esta mensagem ainda não foi documentada. Caso você saiba onde ou como ela é utilizada poderá ajudar outros tradutores adicionando dados sobre esta mensagem.''",
	'translate-edit-information'        => 'Informações sobre esta mensagem',
	'translate-edit-in-other-languages' => 'Mensagem em outros idiomas',
	'translate-edit-committed'          => 'Tradução actualmente disponível no software',
	'translate-edit-warnings'           => 'Avisos sobre traduções incompletas',
	'translate-magic-pagename'          => 'Tradução extra do MediaWiki',
	'translate-magic-help'              => 'Você pode traduzir alias de páginas especiais, palavras mágicas, nomes de temas (skins) e nomes de espaços nominais.

É necessário incluir os termos em Inglês para as palavras mágicas, ou elas pararão de funcionar. Mantenha também o primeiro item (0 ou 1) da forma como se encontra.

Os alias de páginas especiais e palavras mágicas podem receber múltiplas traduções, separadas por vírgulas (,). Nomes de temas e espaços nominais podem receber apenas uma tradução.

Nas traduções de espaços nominais a partícula <tt>$1 talk</tt> é especial. <tt>$1</tt> é trocada pelo nome do sítio (por exemplo, <tt>{{SITENAME}} talk</tt>. Se não é possível formar em seu idioma expressões válidas sem mexer com o nome do sítio, por gentileza, procure um desenvolvedor.

É necessário pertencer ao grupo de tradutores para conseguir salvar as alterações. As alterações não serão salvas até que você clique no botão de salvar.',
	'translate-magic-form'              => 'Língua: $1 Módulo: $2 $3',
	'translate-magic-submit'            => 'Trazer',
	'translate-magic-cm-to-be'          => 'Novo',
	'translate-magic-cm-current'        => 'Actual',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => '"Fallback"',
	'translate-magic-cm-comment'        => 'Comentário:',
	'translate-magic-cm-save'           => 'Guardar',
	'translate-magic-cm-export'         => 'Exportar',
	'translate-magic-cm-updatedusing'   => 'Actualizado usando {{ns:special}}:Magic',
	'translate-magic-cm-savefailed'     => 'Erro ao salvar',
	'translate-magic-special'           => 'Alias de páginas especiais',
	'translate-magic-words'             => 'Palavras mágicas',
	'translate-magic-skin'              => 'Nomes dos temas',
	'translate-magic-namespace'         => 'Nomes de espaços nominais',
	'translationchanges'                => 'Alterações às traduções',
	'translationchanges-export'         => 'exportar',
	'translationchanges-change'         => '$1: $2 por $3',
	'translate-checks-parameters'       => 'Os seguintes parâmetros não são usados: <strong>$1</strong>',
	'translate-checks-balance'          => 'Há um número ímpar de parênteses: <strong>$1</strong>',
	'translate-checks-links'            => 'Os seguintes links possuem problemas: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Por gentileza, troque as seguintes tags pelas corretas: <strong>$1</strong>',
	'translate-checks-plural'           => 'A definição usa <nowiki>{{PLURAL:}}</nowiki>, mas a tradução não.',
);

$messages['rm'] = array(
	'translate-edit' => 'editar',
	'translate-talk' => 'discussiun',
	'translate-history' => 'versiuns',
	'translate-next' => 'Proxima pagina',
);

$messages['ro'] = array(
	'translate' => 'Traducere',
	'translate-edit-message-format' => 'Formatul acestui mesaj este <b>$1</b>.',
	'translate-edit-message-in' => 'Textul curent în <b>$1</b> ($2):',
);

/** Russian (Русский)
 * @author .:Ajvol:.
 * @author Nike
 * @author Ahonc
 */
$messages['ru'] = array(
	'translate'                         => 'Перевод',
	'translate-desc'                    => '[[Special:Translate|Служебная страница]] для перевода Mediawiki и прочих программ',
	'translate-edit'                    => 'править',
	'translate-talk'                    => 'обсуждение',
	'translate-history'                 => 'история',
	'translate-task-view'               => 'Просмотреть все сообщения',
	'translate-task-untranslated'       => 'Просмотреть непереведённые сообщения',
	'translate-task-optional'           => 'Просмотреть необязательные сообщения',
	'translate-task-review'             => 'Проверить изменения',
	'translate-task-reviewall'          => 'Проверить все переводы',
	'translate-task-export'             => 'Выгрузить переводы',
	'translate-task-export-to-file'     => 'Выгрузить переводы в файл',
	'translate-task-export-as-po'       => 'Выгрузить переводы в формате gettext',
	'translate-page-no-such-language'   => 'Передан неверный код языка',
	'translate-page-no-such-task'       => 'Неверно указана задача.',
	'translate-page-no-such-group'      => 'Неверно указана группа.',
	'translate-page-settings-legend'    => 'Параметры',
	'translate-page-task'               => 'Я хочу',
	'translate-page-group'              => 'Группа',
	'translate-page-language'           => 'Язык',
	'translate-page-limit'              => 'Ограничение',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|сообщение|сообщения|сообщений}} на страницу',
	'translate-submit'                  => 'Вывести',
	'translate-page-navigation-legend'  => 'Навигация',
	'translate-page-showing'            => 'Выведены сообщения с $1 по $2 из $3.',
	'translate-page-showing-all'        => 'Выведено $1 {{PLURAL:$1|сообщение|сообщения|сообщений}}.',
	'translate-page-showing-none'       => 'Нет сообщений для отображения.',
	'translate-next'                    => 'следующая страница',
	'translate-prev'                    => 'предыдущая страница',
	'translate-page-description-legend' => 'Информация о группе',
	'translate-optional'                => '(необязательное)',
	'translate-ignored'                 => '(игнорируемое)',
	'translate-edit-definition'         => 'Формулировка сообщения',
	'translate-edit-contribute'         => 'править',
	'translate-edit-no-information'     => "''Это сообщение не имеет описания. Если вы знаете, где или как это сообщение используется, то вы можете помочь другим переводчикам, добавив описание к этому сообщению''",
	'translate-edit-information'        => 'Информация об этом сообщении ($1)',
	'translate-edit-in-other-languages' => 'Сообщение на других языках',
	'translate-edit-committed'          => 'Текущий перевод в программе',
	'translate-edit-warnings'           => 'Предупреждения о неполных переводах',
	'translate-magic-pagename'          => 'Углублённый перевод MediaWiki',
	'translate-magic-help'              => 'Вы можете переводить псевдонимы служебных страниц, магические слова, названия тем оформления и пространств имён.

При редактировании магических слов, желательно оставить английский вариант, иначе он не будет работать. Также стоит оставить первое значение (цифра 0 или 1) таким, какое оно есть.

Псевдонимы служебных страниц и магические слова могут иметь несколько вариантов перевода, они разделяются запятой (,). Названия тем оформления и пространства имён могут иметь только один вариант перевода.

В переводах пространств имён строка «Обсуждение $1» обрабатывается особо, «$1» будет заменено на имя сайта (например «Обсуждение {{SITENAME}}»). Свяжитесь с разработчиками, если подобная грамматическая конструкция неверна для вашего языка.

Чтобы сохранить изменения вы должны входить в группу переводчиков. Изменения не будут сохранены, пока вы не нажмёте кнопку ниже.',
	'translate-magic-form'              => 'Язык: $1 Модуль: $2 $3',
	'translate-magic-submit'            => 'Вывести',
	'translate-magic-cm-to-be'          => 'Должно быть',
	'translate-magic-cm-current'        => 'Текущее',
	'translate-magic-cm-original'       => 'Исходное',
	'translate-magic-cm-fallback'       => 'Подставное',
	'translate-magic-cm-comment'        => 'Примечание:',
	'translate-magic-cm-save'           => 'Сохранить',
	'translate-magic-cm-export'         => 'Выгрузить',
	'translate-magic-cm-updatedusing'   => 'Обновлено с помощью Special:Magic',
	'translate-magic-cm-savefailed'     => 'Сохранение не удалось',
	'translate-magic-special'           => 'Псевдонимы служебных страниц',
	'translate-magic-words'             => 'Магические слова',
	'translate-magic-skin'              => 'Названия тем оформления',
	'translate-magic-namespace'         => 'Пространства имён',
	'translationchanges'                => 'Изменения в переводах',
	'translationchanges-export'         => 'выгрузить',
	'translationchanges-change'         => '$1: $2 $3',
	'translate-checks-parameters'       => 'Следующие параметры не используются: <strong>$1</strong>',
	'translate-checks-balance'          => 'Непарное количество открывающих и закрывающих скобок: <strong>$1</strong>',
	'translate-checks-links'            => 'Следующие ссылки вызывают проблемы: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Пожалуйста, исправьте следующие теги: <strong>$1</strong>',
	'translate-checks-plural'           => 'Оригинал использует <nowiki>{{PLURAL:}}</nowiki>, а перевод — нет.',
);

$messages['sah'] = array(
	'translate' => 'Тылбаас',
	'translate-edit' => 'көннөрүү',
	'translate-talk' => 'ырытыы',
	'translate-history' => 'историята',
	'translate-task-view' => 'Этиилэрин барытын',
	'translate-task-untranslated' => 'Тылбаастамматах этиилэрин',
	'translate-settings' => 'Мин маны $2 $1 бу тылынан $3  $4 лимииттээх көрүөхпүн баҕарабын. $5',
	'translate-paging' => '<div>Барыта $3 этии баарыттан $1 - $2 этиилэр көһүннүлэр [ $4 | $5 ]</div>',
	'translate-submit' => 'Тал',
	'translate-next' => 'Аныгыскы сирэй',
	'translate-prev' => 'Иннинээҕи сирэй',
	'translate-edit-message-format' => 'Бу этии формата <b>$1</b>.',
	'translate-edit-message-in' => 'Бу этии <b>$1</b> ($2) тылынан:',
	'translate-edit-message-in-fb' => 'Бу этии сүрүн <b>$1</b> ($2) тылынан:',
);

/** Slovak (Slovenčina)
 * @author Helix84
 */
$messages['sk'] = array(
	'translate'                         => 'Preložiť',
	'translate-desc'                    => '[[Special:Translate|Špeciálna stránka]] na preklad MediaWiki a iného',
	'translate-edit'                    => 'upraviť',
	'translate-talk'                    => 'diskusia',
	'translate-history'                 => 'história',
	'translate-task-view'               => 'Zobraziť všetky správy z',
	'translate-task-untranslated'       => 'Zobraziť všetky nepreložené správy z',
	'translate-task-optional'           => 'Zobraziť voliteľné správy z',
	'translate-task-review'             => 'Skontrolovať zmeny v',
	'translate-task-reviewall'          => 'Skontrolovať všetky preklady v',
	'translate-task-export'             => 'Exportovať preklady z',
	'translate-task-export-to-file'     => 'Exportovať preklad do súboru z',
	'translate-task-export-as-po'       => 'Exportovať preklad vo formáte Gettext z',
	'translate-page-no-such-language'   => 'Zadaný jazyk bol neplatný.',
	'translate-page-no-such-task'       => 'Zadaná úloha bola neplatná.',
	'translate-page-no-such-group'      => 'Zadaná skupina bola neplatná.',
	'translate-page-settings-legend'    => 'Nastavenia',
	'translate-page-task'               => 'Chcem',
	'translate-page-group'              => 'Skupina',
	'translate-page-language'           => 'Jazyk',
	'translate-page-limit'              => 'Limit',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|správa|správy|správ}} na stránku',
	'translate-submit'                  => 'Vykonať',
	'translate-page-navigation-legend'  => 'Navigácia',
	'translate-page-showing'            => 'Zobrazujú sa správy od $1 do $2 z $3.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Zobrazuje sa $1 správa|Zobrazujú sa $1 správy|Zobrazuje sa $1 správ}}.',
	'translate-page-showing-none'       => 'Žiadne správy.',
	'translate-next'                    => 'Ďalšia stránka',
	'translate-prev'                    => 'Predošlá stránka',
	'translate-page-description-legend' => 'Informácie o skupine',
	'translate-optional'                => '(voliteľné)',
	'translate-ignored'                 => '(ignorované)',
	'translate-edit-definition'         => 'Definícia správy',
	'translate-edit-contribute'         => 'prispejte',
	'translate-edit-no-information'     => "''Táto správa nie je zdokumentovaná. Ak viete kde alebo ako je táto správa použitá, môžete pomôcť ostatným prekladateľom tým, že jej pridáte dokumentáciu.''",
	'translate-edit-information'        => 'Informácie o tejto správe ($1)',
	'translate-edit-in-other-languages' => 'Správa v iných jazykoch',
	'translate-edit-committed'          => 'Aktuálny preklad v softvéri',
	'translate-edit-warnings'           => 'Upozornenia na neúplné preklady',
	'translate-magic-pagename'          => 'Rozšírený preklad MediaWiki',
	'translate-magic-help'              => 'Môžete prekladať aliasy špeciálnych stránok, magické slová, názvy tém vzhľadu a návzy menných priestorov.

V magických slovách musíte zahrnúť aj anglické preklady, inak prestanú fungovať. Tiež ponechajte nezmenenú prvú položku (0 alebo 1).

Aliasy špeciálnych stránok a magických slov môžu mať viacero prekladov. Preklady sa oddeľujú čiarkami („,“). Názvy tém vzhľadu a názvy menných priestorov môžu mať iba jeden preklad.

V prekladoch menných priestorov je <tt>$1 talk</tt> špeciálne. <tt>$1</tt> sa nahradí názvom webovej lokality (napr. <tt>{{SITENAME}} talk</tt>. Ak vo vašom jazyku nie je možné vytvoriť zmysluplný výraz bez zmeny názvu webovej lokality, prosím, kontaktujte vývojára.

Aby ste mohli ukladať zmeny, musíte byť členom skupiny Translators. Zmeny sa neuložia, kým nekliknete na tlačidlo Uložiť dolu.',
	'translate-magic-form'              => 'Jazyk: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Vykonať',
	'translate-magic-cm-to-be'          => 'Byť',
	'translate-magic-cm-current'        => 'Aktuálna',
	'translate-magic-cm-original'       => 'Pôvodná',
	'translate-magic-cm-fallback'       => 'Štandardná',
	'translate-magic-cm-comment'        => 'Komentár:',
	'translate-magic-cm-save'           => 'Uložiť',
	'translate-magic-cm-export'         => 'Exportovať',
	'translate-magic-cm-updatedusing'   => 'Aktualizované pomocou Special:Magic',
	'translate-magic-cm-savefailed'     => 'Uloženie sa nepodarilo',
	'translate-magic-special'           => 'Aliasy špeciálnych stránok',
	'translate-magic-words'             => 'Magické slová',
	'translate-magic-skin'              => 'Názvy tém vzhľadu',
	'translate-magic-namespace'         => 'Názvy menných priestorov',
	'translationchanges'                => 'Zmeny v preklade',
	'translationchanges-export'         => 'export',
	'translationchanges-change'         => '$1: $2 ($3)',
	'translate-checks-parameters'       => 'Nasledovné parametre nie sú použité: <strong>$1</strong>',
	'translate-checks-balance'          => 'Chyba v párovaní zátvoriek: <strong>$1</strong>',
	'translate-checks-links'            => 'Nasledovné odkazy sú problematické: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Prosím, nahraďte nasledovné značky správnymi: <strong>$1</strong>',
	'translate-checks-plural'           => 'Definícia používa <nowiki>{{PLURAL:}}</nowiki>, ale preklad nie.',
);

/** Somali (Soomaaliga)
 * @author Mimursal
 */
$messages['so'] = array(
	'translate' => 'Tarjun',
);

$messages['sr-ec'] = array(
	'translate' => 'Превод',
	'translate-edit-message-in' => 'Тренутни стринг у <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Тренутни стринг у језику <b>$1</b> ($2):',
);

$messages['sr-el'] = array(
	'translate' => 'Prevod',
	'translate-edit-message-in' => 'Trenutni string u <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Trenutni string u jeziku <b>$1</b> ($2):',
);


$messages['ss'] = array(
	'translate' => 'Kuhúmusha',
	'translate-edit' => 'kúhlela',
	'translate-history' => 'umlandvo',
	'translate-settings' => 'Ngifuna $1 $2 in language $3 with limit $4. $5',
	'translate-magic-form' => 'Lúlwîmi: $1 Module: $2 $3',
);

/** Seeltersk (Seeltersk)
 * @author Pyt
 * @author Maartenvdbent
 */
$messages['stq'] = array(
	'translate'                     => 'Uursät',
	'translate-edit'                => 'Beoarbaidje',
	'translate-talk'                => 'Diskussion',
	'translate-history'             => 'Versione',
	'translate-delete'              => 'Annerengen uumekiere',
	'translate-task-view'           => 'Wies aal Systemättergjuchte fon',
	'translate-task-untranslated'   => 'Wies aal nit uursätte Systemättergjuchte fon',
	'translate-task-optional'       => 'Bekiek optionoale Ättergjuchte fon',
	'translate-task-review'         => 'Wröigje Annerengen bit',
	'translate-task-reviewall'      => 'Wröigje aal Uursättengen in',
	'translate-task-export'         => 'Exportier aal Uursättengen fon',
	'translate-task-export-to-file' => 'Exportier aal Uursättengen in ne Doatäi fon',
	'translate-settings'            => 'Iek moate $1 $2 in ju Sproake $3 mäd as Scheed $4. $5',
	'translate-paging'              => '<div>Wies Systemättergjuchte fon $1 bit $2 uut $3. [ $4 | $5 ]</div>',
	'translate-submit'              => 'Hoal',
	'translate-next'                => 'Naiste Siede',
	'translate-prev'                => 'Foarige Siede',
	'translate-optional'            => '(optional)',
	'translate-ignored'             => '(ignorierd)',
	'translate-edit-message-format' => 'Dät Formoat fon disse Ättergjucht is <b>$1</b>.',
	'translate-edit-message-in'     => 'Aktuellen Text in <b>$1</b> ($2):',
	'translate-edit-message-in-fb'  => 'Aktuellen Text in ju Uutwiek-Sproake <b>$1</b> ($2):',
);

$messages['su'] = array(
	'translate' => 'Alih basakeun',
	'translate-edit-message-format' => 'Ieu talatah boga format <b>$1</b>.',
	'translate-edit-message-in' => 'String kiwari dina <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'String kiwari dina basa fallback <b>$1</b> ($2):',
);

/** Swedish (Svenska)
 * @author Sannab
 * @author Lejonel
 * @author Max sonnelid
 * @author Siebrand
 */
$messages['sv'] = array(
	'translate'                         => 'Översätt',
	'translate-desc'                    => '[[Special:Translate|Specialsida]] för översättning av Mediawiki och annat',
	'translate-edit'                    => 'redigera',
	'translate-talk'                    => 'diskussion',
	'translate-history'                 => 'historik',
	'translate-task-view'               => 'se alla meddelanden från',
	'translate-task-untranslated'       => 'se alla oöversatta meddelanden från',
	'translate-task-optional'           => 'se valfria systemmeddelanden från',
	'translate-task-review'             => 'granska ändringar av',
	'translate-task-reviewall'          => 'granska alla översättningar av',
	'translate-task-export'             => 'exportera översättningar från',
	'translate-task-export-to-file'     => 'exportera översättningar till fil från',
	'translate-task-export-as-po'       => 'exportera översättningar i Gettext-format från',
	'translate-page-no-such-language'   => 'Det angivna språket är inte giltigt.',
	'translate-page-no-such-task'       => 'Den angivna åtgärden är inte giltig.',
	'translate-page-no-such-group'      => 'Den angivna gruppen är inte giltig.',
	'translate-page-settings-legend'    => 'Inställningar',
	'translate-page-task'               => 'Jag vill',
	'translate-page-group'              => 'Grupp',
	'translate-page-language'           => 'Språk',
	'translate-page-limit'              => 'Antal',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|meddelande|meddelanden}} per sida',
	'translate-submit'                  => 'Hämta',
	'translate-page-navigation-legend'  => 'Navigering',
	'translate-page-showing'            => 'Visar meddelande $1 till $2 av $3.',
	'translate-page-showing-all'        => 'Visar $1 {{PLURAL:$1|meddelande|meddelanden}}.',
	'translate-page-showing-none'       => 'Det finns inga meddelanden att visa.',
	'translate-next'                    => 'Nästa sida',
	'translate-prev'                    => 'Föregående sida',
	'translate-page-description-legend' => 'Information om gruppen',
	'translate-optional'                => '(valfritt)',
	'translate-ignored'                 => '(används ej)',
	'translate-edit-definition'         => 'Definition av meddelandet',
	'translate-edit-contribute'         => 'bidra',
	'translate-edit-no-information'     => "''Det här meddelandet har ingen dokumentation. Om du vet var eller hur detta meddelande används, så kan du hjälpa andra översättare genom att skriva dokumentation för meddelandet.''",
	'translate-edit-information'        => 'Information om detta meddelande ($1)',
	'translate-edit-in-other-languages' => 'Meddelandet på andra språk',
	'translate-edit-committed'          => 'Nuvarande översättning i mjukvaran',
	'translate-edit-warnings'           => 'Varningar om fel i översättningen',
	'translate-magic-pagename'          => 'Utökad MediaWiki-översättning',
	'translate-magic-help'              => 'Du kan översätta alias för specialsidor, magiska ord, skin-namn och namnrymdsnamn.

För magiska ord så måste du inkludera engelska översättningar eller så slutar de att fungera. Lämna också det första (0 eller 1) som det är.

Alias för specialsidor och magiska ord kan ha flera översättningar. Översättningar skiljs åt av ett komma (,). Skin-namn och namnrymder kan enbart ha en översättning.

Vid översättning av namnrymder så är <tt>$1 talk</tt> speciellt. <tt>$1</tt> ersätts med webbplatsens namn (till exempel <tt>{{SITENAME}} talk</tt>). Om det inte är möjligt att skapa en giltig översättning till ditt språk utan att ändra webbplatsens namn, så ta kontakt med en utvecklare.

För att kunna spara ändringar så behöver du tillhöra översättargruppen. Ändringar sparas inte förrän du klickar på spara-knappen nedan.',
	'translate-magic-form'              => 'Språk: $1 Modul: $2 $3',
	'translate-magic-submit'            => 'Hämta',
	'translate-magic-cm-to-be'          => 'Att-bli',
	'translate-magic-cm-current'        => 'Nuvarande',
	'translate-magic-cm-original'       => 'Original',
	'translate-magic-cm-fallback'       => 'Reserv',
	'translate-magic-cm-comment'        => 'Kommentar:',
	'translate-magic-cm-save'           => 'Spara',
	'translate-magic-cm-export'         => 'Exportera',
	'translate-magic-cm-updatedusing'   => 'Uppdaterad med hjälp av Special:Magic',
	'translate-magic-cm-savefailed'     => 'Det gick ej att spara',
	'translate-magic-special'           => 'Alias till specialsidor',
	'translate-magic-words'             => 'Magiska ord',
	'translate-magic-skin'              => 'Skin-namn',
	'translate-magic-namespace'         => 'Namnrymdsnamn',
	'translationchanges'                => 'Ändrade översättningar',
	'translationchanges-export'         => 'exportera',
	'translationchanges-change'         => '$1: $2 av $3',
	'translate-checks-parameters'       => 'Följande parametrar används inte: <strong>$1</strong>',
	'translate-checks-balance'          => 'Antalet påbörjade och avslutade parenteser är olika: <strong>$1</strong>',
	'translate-checks-links'            => 'Följande länkar är problematiska: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Följande felaktiga taggar bör ersättas: <strong>$1</strong>',
	'translate-checks-plural'           => '<nowiki>{{PLURAL:}}</nowiki> används i definitionen, men inte i översättningen.',
);

/** Tamil (தமிழ்)
 * @author Trengarasu
 */
$messages['ta'] = array(
	'translate'                       => 'மொழிப்பெயர்ப்பு',
	'translate-edit'                  => 'தொகு',
	'translate-talk'                  => 'உரையாடல்',
	'translate-history'               => 'வரலாறு',
	'translate-page-no-such-language' => 'குறித்த மொழி செல்லுபடியற்றதாகும்.',
	'translate-page-language'         => 'மொழி',
	'translate-next'                  => 'அடுத்தப் பக்கம்',
	'translate-prev'                  => 'முந்தைய பக்கம்',
	'translate-magic-cm-save'         => 'பக்கத்தை சேமி',
	'translate-magic-cm-savefailed'   => 'சேமிப்பு தோல்வி',
	'translationchanges-export'       => 'ஏற்றுமதி',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'translate'                         => 'అనువదించు',
	'translate-desc'                    => 'మీడియావికీ మరియు ఆపైవాటిని అనువదించడానికి [[Special:Translate|ప్రత్యేక పేజీ]]',
	'translate-edit'                    => 'మార్చు',
	'translate-talk'                    => 'చర్చ',
	'translate-history'                 => 'చరిత్ర',
	'translate-task-view'               => 'అన్ని సందేశాలు చూడాలనుకుంటున్నాను',
	'translate-task-untranslated'       => 'అన్ని అనువాదంకాని సందేశాలు చూడాలనుకుంటున్నాను',
	'translate-task-optional'           => 'ఐచ్చిక సందేశాలు చూడాలనుకుంటున్నాను',
	'translate-task-review'             => 'మార్పులని సమీక్షించాలనుకుంటున్నాను',
	'translate-task-reviewall'          => 'అన్ని అనువాదాలనూ సమీక్షించాలనుకుంటున్నాను',
	'translate-task-export'             => 'అనువాదాలని ఎగుమతి చేయాలి',
	'translate-page-no-such-language'   => 'ఎంచుకున్న భాష సరైనది కాదు.',
	'translate-page-no-such-task'       => 'ఎంచుకున్న పని సరైనది కాదు.',
	'translate-page-no-such-group'      => 'ఇచ్చిన సమూహం సరైనది కాదు.',
	'translate-page-settings-legend'    => 'అమరికలు',
	'translate-page-task'               => 'నేను',
	'translate-page-group'              => 'సమూహం',
	'translate-page-language'           => 'భాష',
	'translate-page-limit'              => 'పరిమితి',
	'translate-page-limit-option'       => 'పేజీకి $1 {{PLURAL:$1|సందేశం|సందేశాలు}}',
	'translate-submit'                  => 'తీసుకురా',
	'translate-page-navigation-legend'  => 'మార్గదర్శకం',
	'translate-page-showing'            => 'మొత్తం $3 సందేశాల్లో $1 నుండి $2 వరకు చూపిస్తున్నాం.',
	'translate-page-showing-all'        => '$1 {{PLURAL:$1|సందేశాన్ని|సందేశాలను}} చూపిస్తున్నాం.',
	'translate-page-showing-none'       => 'ఇంక సందేశాలేమీ లేవు.',
	'translate-next'                    => 'తర్వాతి పేజీ',
	'translate-prev'                    => 'క్రితం పేజీ',
	'translate-page-description-legend' => 'ఈ సమూహం గురించిన సమాచారం',
	'translate-optional'                => '(ఐచ్ఛికం)',
	'translate-edit-definition'         => 'సందేశ నిర్వచనం',
	'translate-edit-no-information'     => "''ఈ సందేశానికి సహాయ సమాచారం లేదు. ఈ సందేశాన్ని ఎక్కడ లేదా ఎలా ఉపయోగిస్తారో మీకు తెలిస్తే, దీనికి తగిన సమాచారం చేర్చి ఇతర అనువాదకులకు తొడ్పడవచ్చు.''",
	'translate-edit-information'        => 'ఈ సందేశం గురించి సమాచారం ($1)',
	'translate-edit-in-other-languages' => 'ఇతర భాషలలోని సందేశాలు',
	'translate-edit-committed'          => 'సాఫ్ట్&zwnj;వేర్&zwnj;లో ప్రస్తుతమున్న అనువాదం',
	'translate-edit-warnings'           => 'అసంపూర్తి అనువాదాల గురించి హెచ్చరికలు',
	'translate-magic-form'              => 'భాష: $1 మాడ్యూలు: $2 $3',
	'translate-magic-submit'            => 'తీసుకురా',
	'translate-magic-cm-current'        => 'ప్రస్తుత',
	'translate-magic-cm-comment'        => 'వ్యాఖ్య:',
	'translate-magic-cm-save'           => 'భద్రపరచు',
	'translate-magic-special'           => 'ప్రత్యేక పేజీల మారుపేర్లు',
	'translate-magic-words'             => 'మాయా పదాలు',
	'translationchanges'                => 'అనువాద మార్పులు',
	'translationchanges-export'         => 'ఎగుమతించు',
	'translationchanges-change'         => '$1: $3 చే $2',
	'translate-checks-balance'          => 'బ్రాకెట్లు సరి సంఖ్యలో లేవు: <strong>$1</strong>',
	'translate-checks-links'            => 'ఈ లింకులు సమస్యాత్మకంగా ఉన్నాయి: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'ఈ క్రింది టాగులని సరైన వాటితో మార్చండి: <strong>$1</strong>',
	'translate-checks-plural'           => 'అసలు సందేశంలో <nowiki>{{PLURAL:}}</nowiki> అనివుంది కానీ అనువాదంలో లేదు.',
);

/** Tetum (Tetun)
 * @author MF-Warburg
 */
$messages['tet'] = array(
	'translate'                         => 'Tradús',
	'translate-edit'                    => 'edita',
	'translate-talk'                    => 'diskusaun',
	'translate-history'                 => 'istória',
	'translate-task-view'               => 'Haree mensajen hotu husi',
	'translate-page-task'               => "Ha'u hakarak",
	'translate-page-group'              => 'Lubu',
	'translate-page-language'           => 'Lian',
	'translate-submit'                  => 'Hola',
	'translate-page-showing-all'        => 'Dalan $1 mensajen.',
	'translate-next'                    => 'Pájina oinmai',
	'translate-page-description-legend' => 'Informasaun kona-ba lubu',
	'translate-edit-in-other-languages' => 'Mensajen iha lian seluk',
	'translate-magic-submit'            => 'Hola',
);

/** Thai (ไทย)
 * @author Ans
 * @author Passawuth
 */
$messages['th'] = array(
	'translate'                         => 'แปล',
	'translate-desc'                    => '[[Special:Translate|หน้าพิเศษ]]สำหรับแปลมีเดียวิกิและอื่น ๆ',
	'translate-edit'                    => 'แก้ไข',
	'translate-talk'                    => 'พูดคุย',
	'translate-history'                 => 'ประวัติ',
	'translate-task-view'               => 'ดูข้อความทั้งหมด จาก',
	'translate-task-untranslated'       => 'ดูข้อความทั้งหมดที่ยังไม่ได้แปล จาก',
	'translate-task-optional'           => 'ดูข้อความ optional จาก',
	'translate-task-review'             => 'ตรวจดูสิ่งที่เปลี่ยนแปลง ใน',
	'translate-task-reviewall'          => 'ตรวจดูข้อความทั้งหมดที่แปลแล้ว ใน',
	'translate-task-export'             => 'ส่งงานแปลออกมา (export) จาก',
	'translate-task-export-to-file'     => 'ส่งงานแปลออกมา (export) เป็นไฟล์ จาก',
	'translate-task-export-as-po'       => 'ส่งงานแปลออกมา (export) ในรูปแบบ Gettext จาก',
	'translate-page-no-such-language'   => 'ใส่รหัสภาษา (language) ไม่ถูกต้อง',
	'translate-page-no-such-task'       => 'ใส่ชื่อ task ไม่ถูกต้อง',
	'translate-page-no-such-group'      => 'ใส่ชื่อกลุ่ม (group) ไม่ถูกต้อง',
	'translate-page-settings-legend'    => 'กำหนดค่า',
	'translate-page-task'               => 'ต้องการ',
	'translate-page-group'              => 'กลุ่มของ',
	'translate-page-language'           => 'ภาษา',
	'translate-page-limit'              => 'ไม่เกิน',
	'translate-page-limit-option'       => '$1 ข้อความต่อหน้า',
	'translate-submit'                  => 'ดึงข้อมูล',
	'translate-page-navigation-legend'  => 'แถบนำทาง',
	'translate-page-showing'            => 'แสดงตั้งแต่ข้อความที่ $1 ถึง $2 จากทั้งหมด $3 ข้อความ',
	'translate-page-showing-all'        => 'แสดง $1 ข้อความ',
	'translate-page-showing-none'       => 'ไม่มีข้อความแสดง',
	'translate-next'                    => 'หน้าถัดไป',
	'translate-prev'                    => 'หน้าก่อน',
	'translate-page-description-legend' => 'ข้อมูลเกี่ยวกับกลุ่มข้อความ',
	'translate-optional'                => '(สามารถเลือกได้)',
	'translate-ignored'                 => '(เพิกเฉย)',
	'translate-edit-definition'         => 'นิยามข้อความ',
	'translate-edit-contribute'         => 'ช่วยเขียน',
	'translate-edit-no-information'     => "''ข้อความนี้ไม่มีคำอธิบายการใช้งาน.  ถ้าคุณทราบว่าข้อความนี้ใช้ตรงส่วนไหนหรือใช้อย่างไร, คุณสามารถช่วยเพิ่มคำอธิบายการใช้งานของข้อความนี้ เพื่อเป็นประโยชน์แก่ผู้แปลคนอื่นๆ ได้.''",
	'translate-edit-information'        => 'ข้อมูลเกี่ยวกับข้อความนี้ ($1)',
	'translate-edit-in-other-languages' => 'ข้อความนี้ในภาษาอื่นๆ',
	'translate-edit-committed'          => 'ข้อความแปลที่ฝังอยู่ในตัวโปรแกรม',
	'translate-edit-warnings'           => 'คำเตือนเกี่ยวกับงานแปลที่ยังไม่เสร็จสมบูรณ์',
	'translate-magic-pagename'          => 'ส่วนขยายการแปลในมีเดียวิกิ',
	'translate-magic-help'              => 'คุณสามารถแปลชื่อหน้าพิเศษต่าง ๆ, ตัวแปรพิเศษ, ชื่อแบบหน้าตา และ ชื่อเนมสเปซ

ในตัวแปรพิเศษ กรุณาใส่คำแปลภาษาอังกฤษไปด้วยเช่นเดียวกัน มิฉะนั้นมันจะหยุดทำงาน กรุณาเว้นอันที่ 1 (0 หรือ 1) อย่างที่มันเป็น

ชื่อหน้าพิเศษและตัวแปรพิเศษสามารถมีคำแปลได้หลายอย่าง คำแปลจะแยกโดยการใช้ จุลภาค (,) ; ชื่อแบบหน้าตาและเนมสเปซสามารถมีคำแปลได้แค่คำเดียว

ในคำแปลชื่อเนมสเปซ <tt>คุยเรื่อง$1</tt> ต้องระวังเป็นพิเศษ <tt>$1</tt> จะถูกแทนที่โดยชื่อเว็บไซต์ (เช่น <tt>คุยเรื่อง{{SITENAME}}</tt> ถ้าไม่สามารถกระทำการดังกล่าวได้ในภาษาของคุณ, กรุณาติดต่อผู้ดูแลระบบขั้นสูง

คุณต้องอยู่ในกลุ่มคนแปลเพื่อที่จะบันทึกข้อมูลได้ ข้อมูลจะไม่ถูกบันทึกตราบใดที่คุณยังไม่กด "บันทึก"',
	'translate-magic-form'              => 'ภาษา: $1 Module: $2 $3',
	'translate-magic-submit'            => 'ดึงข้อมูล',
	'translate-magic-cm-to-be'          => 'แก้เป็น',
	'translate-magic-cm-current'        => 'ปัจจุบัน',
	'translate-magic-cm-original'       => 'ต้นฉบับ',
	'translate-magic-cm-fallback'       => 'ถอยกลับ',
	'translate-magic-cm-comment'        => 'หมายเหตุ:',
	'translate-magic-cm-save'           => 'บันทึก',
	'translate-magic-cm-export'         => 'ส่งออกมา (export)',
	'translate-magic-cm-updatedusing'   => 'แก้ไขด้วย Special:Magic',
	'translate-magic-cm-savefailed'     => 'บันทึกไม่สำเร็จ',
	'translate-magic-special'           => 'ชื่อ alias ของหน้าพิเศษ',
	'translate-magic-words'             => 'ตัวแปรพิเศษ',
	'translate-magic-skin'              => 'ชื่อแบบหน้าตา',
	'translate-magic-namespace'         => 'ชื่อเนมสเปซ',
	'translationchanges'                => 'สิ่งที่เปลี่ยนแปลงในงานแปล',
	'translationchanges-export'         => 'ส่งออกมา (export)',
	'translationchanges-change'         => '$1: $2 โดย $3',
	'translate-checks-parameters'       => 'ตัวแปรต่อไปนี้ไม่ได้รับการใช้งาน: <strong>$1</strong>',
	'translate-checks-balance'          => 'จับคู่วงเล็บไม่ครบคู่: <strong>$1</strong>',
	'translate-checks-links'            => 'ลิงก์ต่อไปนี้ทำให้เกิดปัญหา: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'tag เหล่านี้ไม่ถูกต้อง กรุณาแก้ไขโดยใช้ tag ที่ถูกต้อง: <strong>$1</strong>',
	'translate-checks-plural'           => 'ข้อความต้นฉบับใช้ <nowiki>{{PLURAL:}}</nowiki> ในขณะที่ข้อความที่แปลแล้วไม่ได้ใช้',
);

/** Turkish (Türkçe)
 * @author Karduelis
 */
$messages['tr'] = array(
	'translate'                      => 'Çeviri',
	'translate-edit'                 => 'Düzelt',
	'translate-talk'                 => 'Tartışma',
	'translate-history'              => 'Geçmiş',
	'translate-page-settings-legend' => 'Ayarlar',
	'translate-page-task'            => 'Seç',
	'translate-page-group'           => 'Grup',
	'translate-page-language'        => 'Dil',
	'translate-page-limit'           => 'Sınır',
	'translate-submit'               => 'Getir',
	'translate-next'                 => 'İleri',
	'translate-prev'                 => 'Geri',
	'translate-optional'             => '(isteğe bağlı)',
	'translate-ignored'              => '(yok sayılan)',
	'translate-edit-contribute'      => 'Katkıda bulun',
	'translate-magic-form'           => 'Dil: $1 Modül: $2 $3',
	'translate-magic-submit'         => 'Getir',
	'translate-magic-cm-to-be'       => 'Yap',
	'translate-magic-cm-current'     => 'Güncelle',
	'translate-magic-cm-original'    => 'Orjinal',
	'translate-magic-cm-comment'     => 'Açıklama :',
	'translate-magic-cm-save'        => 'Kaydet',
);

$messages['ug'] = array(
	'translate-edit' => 'uzgartish',
	'translate-talk' => 'monazire',
	'translate-history' => 'tarih',
);

/** Ukrainian (Українська)
 * @author Ahonc
 * @author AS
 */
$messages['uk'] = array(
	'translate'                         => 'Переклад',
	'translate-desc'                    => '[[Special:Translate|Спеціальна сторінка]] для перекладу Mediawiki та інших програм',
	'translate-edit'                    => 'редагувати',
	'translate-talk'                    => 'обговорення',
	'translate-history'                 => 'історія',
	'translate-task-view'               => 'Переглянути всі повідомлення',
	'translate-task-untranslated'       => 'Переглянути неперекладені повідомлення',
	'translate-task-optional'           => "Переглянути необов'язкові повідомлення",
	'translate-task-review'             => 'Перевірити зміни',
	'translate-task-reviewall'          => 'Перевірити всі переклади',
	'translate-task-export'             => 'Експортувати переклади',
	'translate-task-export-to-file'     => 'Експортувати переклади до файлу',
	'translate-task-export-as-po'       => 'Експортувати переклади у форматі gettext',
	'translate-page-no-such-language'   => 'Передано неправильний код мови.',
	'translate-page-no-such-task'       => 'Неправильно вказане завдання.',
	'translate-page-no-such-group'      => 'Неправильно вказана група.',
	'translate-page-settings-legend'    => 'Параметри',
	'translate-page-task'               => 'Я хочу',
	'translate-page-group'              => 'Група',
	'translate-page-language'           => 'Мова',
	'translate-page-limit'              => 'Обмеження',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|повідомлення|повідомлення|повідомлень}} на сторінку',
	'translate-submit'                  => 'Вивести',
	'translate-page-navigation-legend'  => 'Навігація',
	'translate-page-showing'            => 'Показані повідомлення з $1 по $2 із $3.',
	'translate-page-showing-all'        => '{{PLURAL:$1|Показане $1 повідомлення|Показані $1 повідомлення|Показані $1 повідомлень}}.',
	'translate-page-showing-none'       => 'Нема повідомлень для відображення.',
	'translate-next'                    => 'наступна сторінка',
	'translate-prev'                    => 'попередня сторінка',
	'translate-page-description-legend' => 'Інформація про групу',
	'translate-optional'                => "(необов'язкове)",
	'translate-ignored'                 => '(ігнорується)',
	'translate-edit-definition'         => 'Текст повідомлення',
	'translate-edit-contribute'         => 'редагувати',
	'translate-edit-no-information'     => "''Це повідомлення не має документації. Якщо ви знаєте, де чи як воно використовується, ви можете допомогти іншим перекладачам, додавши опис для цього повідомлення.''",
	'translate-edit-information'        => 'Інформація про це повідомлення ($1)',
	'translate-edit-in-other-languages' => 'Повідомлення іншими мовами',
	'translate-edit-committed'          => 'Поточний переклад у програмі',
	'translate-magic-pagename'          => 'Поглиблений переклад MediaWiki',
	'translate-magic-cm-comment'        => 'Коментар:',
	'translate-magic-words'             => 'Магічні слова',
	'translate-magic-namespace'         => 'Простори імен',
	'translationchanges-change'         => '$1: $2 $3',
);

/** Vietnamese (Tiếng Việt)
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'translate'                         => 'Biên dịch',
	'translate-desc'                    => '[[Special:Translate|Trang đặc biệt]] dùng để dịch Mediawiki và các thứ khác',
	'translate-edit'                    => 'sửa đổi',
	'translate-talk'                    => 'thảo luận',
	'translate-history'                 => 'lịch sử',
	'translate-task-view'               => 'Xem tất cả các thông điệp từ',
	'translate-task-untranslated'       => 'Xem tất cả các thông điệp chưa dịch từ',
	'translate-task-optional'           => 'Xem các thông điệp tùy chọn từ',
	'translate-task-review'             => 'Kiểm lại các thay đổi trong',
	'translate-task-reviewall'          => 'Kiểm lại tất cả các bản dịch trong',
	'translate-task-export'             => 'Xuất các bản dịch từ',
	'translate-task-export-to-file'     => 'Xuất bản dịch ra tập tin từ',
	'translate-task-export-as-po'       => 'Xuất bản dịch theo dạng Gettext',
	'translate-page-no-such-language'   => 'Ngôn ngữ chỉ định không đúng.',
	'translate-page-no-such-task'       => 'Tác vụ chỉ định không đúng.',
	'translate-page-no-such-group'      => 'Nhóm chỉ định không đúng.',
	'translate-page-settings-legend'    => 'Thiết lập',
	'translate-page-task'               => 'Tôi muốn',
	'translate-page-group'              => 'Nhóm',
	'translate-page-language'           => 'Ngôn ngữ',
	'translate-page-limit'              => 'Giới hạn',
	'translate-page-limit-option'       => '$1 {{PLURAL:$1|thông điệp|thông điệp}} mỗi trang',
	'translate-submit'                  => 'Xem',
	'translate-page-navigation-legend'  => 'Điều khiển',
	'translate-page-showing'            => 'Đang hiển thị thông điệp có thứ tự từ $1 đến $2 trong tổng số $3 thông điệp.',
	'translate-page-showing-all'        => 'Đang hiển thị $1 {{PLURAL:$1|thông điệp|thông điệp}}.',
	'translate-page-showing-none'       => 'Không có thông điệp nào.',
	'translate-next'                    => 'Trang sau',
	'translate-prev'                    => 'Trang trước',
	'translate-page-description-legend' => 'Thông tin về nhóm',
	'translate-optional'                => '(tùy chọn)',
	'translate-ignored'                 => '(đã bỏ)',
	'translate-edit-definition'         => 'Định nghĩa thông điệp',
	'translate-edit-contribute'         => 'đóng góp',
	'translate-edit-no-information'     => "''Thông điệp này hiện chưa có tài liệu hướng dẫn. Nếu bạn biết thông điệp này dùng ở đâu và dùng như thế nào, bạn có thể giúp những biên dịch viên khác bằng cách thêm tài liệu hướng dẫn cho nó.''",
	'translate-edit-information'        => 'Thông tin về thông điệp này ($1)',
	'translate-edit-in-other-languages' => 'Thông điệp bằng thứ tiếng khác',
	'translate-edit-committed'          => 'Bản dịch hiện tại trong phần mềm',
	'translate-edit-warnings'           => 'Các cảnh báo về các bản dịch chưa hoàn thành',
	'translate-magic-pagename'          => 'Bản dịch MediaWiki mở rộng',
	'translate-magic-help'              => 'Bạn có thể dịch bí danh của các trang đặc biệt, thần chú, tên hình dạng giao diện và tên của không gian tên.

Trong các từ thần chú bạn cần phải ghi kèm các bản dịch tiếng Anh, nếu không chúng sẽ không hoạt động. Cũng nhớ giữ nguyên, đừng thay đổi mục đầu tiên (0 hoặc 1).

Bí danh của các trang đặc biệt và từ thần chú có thể có nhiều bản dịch. Các bản dịch phân cách nhau bằng dấu phẩy (,). Tên hình dạng giao diện và không gian tên chỉ có thể có một bản dịch.

Trong các bản dịch không gian tên, <tt>$1 talk</tt> có đặc biệt hơn. <tt>$1</tt> được thay thế bằng tên trang (ví dụ <tt>{{SITENAME}} talk</tt>. Nếu ngôn ngữ của bạn không thể hiển thị đúng nếu không đổi tên trang (SITENAME), xin hãy liên hệ với một lập trình viên.

Bạn cần phải thuộc nhóm biên dịch viên để có thể lưu các thay đổi. Các thay đổi sẽ không được lưu lại đến khi nào bạn nhấn vào nút lưu ở dưới.',
	'translate-magic-form'              => 'Ngôn ngữ: $1 Gói: $2 $3',
	'translate-magic-submit'            => 'Xem',
	'translate-magic-cm-to-be'          => 'Trở thành',
	'translate-magic-cm-current'        => 'Hiện hành',
	'translate-magic-cm-original'       => 'Bản gốc',
	'translate-magic-cm-fallback'       => 'Hủy bỏ',
	'translate-magic-cm-comment'        => 'Tóm lược:',
	'translate-magic-cm-save'           => 'Lưu',
	'translate-magic-cm-export'         => 'Xuất',
	'translate-magic-cm-updatedusing'   => 'Đã cập nhật bằng Special:Magic',
	'translate-magic-cm-savefailed'     => 'Lưu thất bại',
	'translate-magic-special'           => 'Bí danh của các trang đặc biệt',
	'translate-magic-words'             => 'Từ thần chú',
	'translate-magic-skin'              => 'Tên hình dạng giao diện',
	'translate-magic-namespace'         => 'Tên của không gian tên',
	'translationchanges'                => 'Các thay đổi bản dịch',
	'translationchanges-export'         => 'xuất',
	'translationchanges-change'         => '$1: $2 bởi $3',
	'translate-checks-parameters'       => 'Những tham số sau không sử dụng: <strong>$1</strong>',
	'translate-checks-balance'          => 'Số dấu ngoặc bị lẻ: <strong>$1</strong>',
	'translate-checks-links'            => 'Các liên kết sau có vấn đề: <strong>$1</strong>',
	'translate-checks-xhtml'            => 'Xin thay thế các thẻ sau bằng thẻ đúng: <strong>$1</strong>',
	'translate-checks-plural'           => 'Định nghĩa sử dụng <nowiki>{{PLURAL:}}</nowiki> nhưng bản dịch không có.',
);

/** Volapük (Volapük)
 * @author Smeira
 * @author Malafaya
 */
$messages['vo'] = array(
	'translate'                         => 'Tradutön',
	'translate-edit'                    => 'redakön',
	'translate-talk'                    => 'bespik',
	'translate-history'                 => 'jenotem',
	'translate-task-view'               => 'logön nunis valik in',
	'translate-task-untranslated'       => 'logön nunis no petradutölis valikis in',
	'translate-page-no-such-language'   => 'Pük pevälöl no dabinon.',
	'translate-page-no-such-task'       => 'Vobod pevilöl no dabinon.',
	'translate-page-no-such-group'      => 'Grup pevälöl no dabinon.',
	'translate-page-settings-legend'    => 'Paramets',
	'translate-page-task'               => 'Vilob',
	'translate-page-group'              => 'Grup:',
	'translate-page-language'           => 'Pük:',
	'translate-page-limit'              => 'Mied:',
	'translate-page-limit-option'       => '{{PLURAL:$1|nun|nuns}} $1 a pad',
	'translate-submit'                  => 'Getolöd',
	'translate-page-navigation-legend'  => 'Nafam',
	'translate-page-showing'            => 'Nuns de nüm: $1 ad $2 (se $3).',
	'translate-page-showing-all'        => '{{PLURAL:$1|Nun|Nuns}} $1 {{PLURAL:$1|pajonon|pajonons}}.',
	'translate-page-showing-none'       => 'Nuns jonabik no dabinons.',
	'translate-next'                    => 'Pad sököl',
	'translate-prev'                    => 'Pad büik',
	'translate-page-description-legend' => 'Nüns tefü grup',
	'translate-ignored'                 => '(penedemöl)',
	'translate-edit-definition'         => 'Miedet nuna',
	'translate-edit-contribute'         => 'keblünön',
	'translate-edit-no-information'     => 'Nun at no peplänon. If sevol, kiöpo u lio nun at pagebon, kanol yufön tradutanis votik medä penol pläni gudik dö geb onik.',
	'translate-edit-information'        => 'Plän nuna at ($1)',
	'translate-edit-in-other-languages' => 'Nun in püks votik',
	'translate-edit-warnings'           => 'Nüneds tefü tradutods no lölöfiks',
	'translate-magic-cm-to-be'          => 'Ovedon',
	'translate-magic-cm-original'       => 'Rigik',
	'translate-magic-cm-save'           => 'Dakipön',
	'translate-magic-cm-savefailed'     => 'Dakip no eplöpon',
	'translate-magic-words'             => 'Vöds magivik',
	'translate-magic-namespace'         => 'Nems nemaspadas',
	'translationchanges-change'         => '$1: $2 fa $3',
	'translate-checks-parameters'       => 'Paramets sököl no pagebons: <strong>$1</strong>',
	'translate-checks-links'            => 'Yüms sököl binons säkädiks: <strong>$1</strong>',
);

$messages['wa'] = array(
	'translate' => 'Ratourner',
	'translate-edit' => 'candjî',
	'translate-talk' => 'copene',
	'translate-history' => 'istwere',
	'translate-task-view' => 'Vey tos les messaedjes',
	'translate-task-untranslated' => 'Vey tos les messaedjes nén ratournés',
	'translate-task-review' => 'Verifyî les candjmints',
	'translate-task-reviewall' => 'Verifyî tos les ratournaedjes',
	'translate-task-export' => 'Copyî foû les ratournaedjes',
	'translate-task-export-to-file' => 'Copyî foû viè on fitchî les ratournaedjes',
	'translate-settings' => '$1 di $2 e lingaedje $3 avou $4 messaedjes so tchaeke pådje. $5',
	'translate-paging' => '<div>Håynant les messaedjes di $1 a $2 foû di $3. [ $4 | $5 ]</div>',
	'translate-submit' => 'I va',
	'translate-next' => 'Pådje shuvante',
	'translate-prev' => 'Pådje di dvant',
	'translate-optional' => '(opcionel)',
	'translate-ignored' => '(ignoré)',
	'translate-edit-message-format' => 'Li format di ç\' messaedje ci c\' est <b>$1</b>.',
	'translate-edit-message-in' => 'Li tecse do moumint e lingaedje <b>$1</b> ($2):',
	'translate-edit-message-in-fb' => 'Tecse pol lingaedje di deujhinme tchuze <b>$1</b> ($2):',
);

/** მარგალური (მარგალური)
 * @author Malafaya
 */
$messages['xmf'] = array(
	'translate-page-language' => 'ნინა',
);

$messages['yue'] = array(
	'translate' => '翻譯',
	'translate-edit' => '編輯',
	'translate-talk' => '對話',
	'translate-history' => '歷史',

	'translate-task-view' => '去睇全部信息自',
	'translate-task-untranslated' => '去睇全部未翻譯好嘅信息自',
	'translate-task-review' => '睇番嗰度嘅更改',
	'translate-task-reviewall' => '睇番響嗰度嘅全部翻譯',
	'translate-task-export' => '倒出翻譯自',
	'translate-task-export-to-file' => '倒出翻譯到檔案自',

	'translate-settings' => '我想去$1響$3語言嘅$2組，上限係$4。 $5',
	'translate-paging' => '<div>顯示緊由$1條到$2條，總共$3條信息。 [ $4 | $5 ]</div>',
	'translate-submit' => '擷取',
	'translate-next' => '下一版',
	'translate-prev' => '上一版',

	'translate-optional' => '(可選)',
	'translate-ignored' => '(已略過)',

	'translate-edit-message-format' => '呢句信息嘅格式係 <b>$1</b>。',
	'translate-edit-message-in' => '響 <b>$1</b> 嘅現行字串 ($2):',
	'translate-edit-message-in-fb' => '響 <b>$1</b> 於倚靠語言中嘅現行字串 ($2):',
);

$messages['zh-hans'] = array(
	'translate' => '翻译',
	'translate-edit' => '编辑',
	'translate-talk' => '对话',
	'translate-history' => '历史',

	'translate-task-view' => '查看全部信息由',
	'translate-task-untranslated' => '查看全部尚未翻译好的信息由',
	'translate-task-review' => '复看该处的更改',
	'translate-task-reviewall' => '复看该处的所有翻译',
	'translate-task-export' => '导出翻译自',
	'translate-task-export-to-file' => '导出翻译至文件由',

	'translate-settings' => '我想去$1于$3语言上的$2群组，上限是$4。 $5',
	'translate-paging' => '<div>显示由$1条到$2条，共$3条信息。 [ $4 | $5 ]</div>',
	'translate-submit' => '撷取',
	'translate-next' => '下一页',
	'translate-prev' => '上一页',

	'translate-optional' => '(可选)',
	'translate-ignored' => '(已略过)',

	'translate-edit-message-format' => '这句信息的格式是 <b>$1</b>。',
	'translate-edit-message-in' => '在 <b>$1</b> 的当前字串 ($2):',
	'translate-edit-message-in-fb' => '在 <b>$1</b> 于倚靠语言中的当前字串 ($2):',
);

$messages['zh-hant'] = array(
	'translate' => '翻譯',
	'translate-edit' => '編輯',
	'translate-talk' => '對話',
	'translate-history' => '歷史',

	'translate-task-view' => '檢視全部信息由',
	'translate-task-untranslated' => '檢視全部尚未翻譯好的信息由',
	'translate-task-review' => '複看該處的更改',
	'translate-task-reviewall' => '複看該處的所有翻譯',
	'translate-task-export' => '匯出翻譯自',
	'translate-task-export-to-file' => '匯出翻譯至檔案由',

	'translate-settings' => '我想去$1於$3語言上的$2群組，上限是$4。 $5',
	'translate-paging' => '<div>顯示由$1條到$2條，共$3條信息。 [ $4 | $5 ]</div>',
	'translate-submit' => '擷取',
	'translate-next' => '下一頁',
	'translate-prev' => '上一頁',

	'translate-optional' => '(可選)',
	'translate-ignored' => '(已略過)',

	'translate-edit-message-format' => '這句信息的格式是 <b>$1</b>。',
	'translate-edit-message-in' => '在 <b>$1</b> 的現行字串 ($2):',
	'translate-edit-message-in-fb' => '在 <b>$1</b> 於倚靠語言中的現行字串 ($2):',
);

