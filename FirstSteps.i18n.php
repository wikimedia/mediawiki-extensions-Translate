<?php
/**
 * %Messages for Special:FirstSteps of the Translate extension.
 *
 * @file
 * @author Niklas Laxström
 * @author Siebrand Mazeland
 * @copyright Copyright © 2008-2010, Niklas Laxström, Siebrand Mazeland
 * @license GPL-2.0+
 */

$messages = array();

/** English
 * @author Nike
 * @author Siebrand
 */
$messages['en'] = array(
	'firststeps' => 'First steps',
	'firststeps-desc' => '[[Special:FirstSteps|Special page]] for getting users started on a wiki using the Translate extension',
	'translate-fs-pagetitle-done' => ' - done!',
	'translate-fs-pagetitle-pending' => ' - pending',
	'translate-fs-pagetitle' => 'Getting started wizard - $1',
	'translate-fs-signup-title' => 'Sign up',
	'translate-fs-settings-title' => 'Configure your preferences',
	'translate-fs-userpage-title' => 'Create your user page',
	'translate-fs-permissions-title' => 'Request translator permissions',
	'translate-fs-target-title' => 'Start translating!',
	'translate-fs-email-title' => 'Confirm your email address',

	'translate-fs-intro' => "Welcome to the {{SITENAME}} first steps wizard.
You will be guided through the process of becoming a translator step by step.
In the end you will be able to translate ''interface messages'' of all supported projects at {{SITENAME}}.",

	'translate-fs-selectlanguage' => "Pick a language",
	'translate-fs-settings-planguage' => "Primary language:",
	'translate-fs-settings-planguage-desc' => "The primary language doubles as your interface language on this wiki
and as default target language for translations.",
	'translate-fs-settings-slanguage' => "Assistant language $1:",
	'translate-fs-settings-slanguage-desc' => "It is possible to show translations of messages in other languages in the translation editor.
Here you can choose which languages, if any, you would like to see.",
	'translate-fs-settings-submit' => "Save preferences",
	'translate-fs-userpage-level-N' => 'I am a native speaker of',
	'translate-fs-userpage-level-5' => 'I am a professional translator of',
	'translate-fs-userpage-level-4' => 'I know it like a native speaker',
	'translate-fs-userpage-level-3' => 'I have a good command of',
	'translate-fs-userpage-level-2' => 'I have a moderate command of',
	'translate-fs-userpage-level-1' => 'I know a little',
	'translate-fs-userpage-help' => 'Please indicate your language skills and tell something about yourself. If you know more than five languages, you can add more later.',
	'translate-fs-userpage-submit' => 'Create your userpage',
	'translate-fs-userpage-done' => 'Well done! You now have an user page.',
	'translate-fs-permissions-planguage' => "Primary language:",
	'translate-fs-permissions-help' => 'Now you need to place a request to be added to the translator group.
Select the primary language you are going to translate to.

You can mention other languages and other remarks in textbox below.',
	'translate-fs-permissions-pending' => 'Your request has been submitted to [[$1]] and someone from the site staff will check it as soon as possible.
If you confirm your email address, you will get an email notification as soon as it happens.',
	'translate-fs-permissions-submit' => 'Send request',
	'translate-fs-target-text' => 'Congratulations!
You can now start translating.

Do not be afraid if it still feels new and confusing to you.
At [[Project list]] there is an overview of projects you can contribute translations to.
Most of the projects have a short description page with a "\'\'Translate this project\'\'" link, that will take you to a page which lists all untranslated messages.
A list of all message groups with the [[Special:LanguageStats|current translation state for a language]] is also available.

If you feel that you need to understand more before you start translating, you can read the [[FAQ|Frequently asked questions]].
Unfortunately documentation can be out of date sometimes.
If there is something that you think you should be able to do, but cannot find out how, do not hesitate to ask it at the [[Support|support page]].

You can also contact fellow translators of the same language at [[Portal:$1|your language portal]]\'s [[Portal_talk:$1|talk page]].
If you have not already done so, [[Special:Preferences|change your user interface language to the language you want to translate in]], so that the wiki is able to show the most relevant links for you.',

	'translate-fs-email-text' => 'Please provide your email address in [[Special:Preferences|your preferences]] and confirm it from the email that is sent to you.

This allows other users to contact you by email.
You will also receive newsletters at most once a month.
If you do not want to receive newsletters, you can opt-out in the tab "{{int:prefs-personal}}" of your [[Special:Preferences|preferences]].',
);

/** Message documentation (Message documentation)
 * @author EugeneZelenko
 * @author Lloffiwr
 * @author Purodha
 * @author Shirayuki
 * @author The Evil IP address
 */
$messages['qqq'] = array(
	'firststeps' => '{{doc-special|FirstSteps|unlisted=1}}',
	'translate-fs-pagetitle' => 'Used as page title. Parameters:
* $1 - any one of the following messages:
** {{msg-mw|translate-fs-signup-title}}
** {{msg-mw|translate-fs-settings-title}}
** {{msg-mw|translate-fs-userpage-title}}
** {{msg-mw|translate-fs-permissions-title}}
** {{msg-mw|translate-fs-target-title}}
** {{msg-mw|translate-fs-email-title}}',
	'translate-fs-signup-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}
{{Identical|Sign up}}',
	'translate-fs-settings-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}',
	'translate-fs-userpage-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}',
	'translate-fs-permissions-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}',
	'translate-fs-target-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}',
	'translate-fs-email-title' => 'Used as a part of the page title.

See also:
* {{msg-mw|Translate-fs-pagetitle}}',
	'translate-fs-selectlanguage' => "Default value in language selector, acts as 'nothing chosen'",
	'translate-fs-settings-planguage' => 'Label for choosing interface language, followed by language selector.
{{Identical|Primary language}}',
	'translate-fs-settings-planguage-desc' => 'Help message for choosing interface language',
	'translate-fs-settings-slanguage' => 'Other languages shown while translating, followed by language selector. Parameters:
* $1 - running number
{{Identical|Assistant language}}',
	'translate-fs-settings-slanguage-desc' => 'Help message for choosing assistant languages',
	'translate-fs-settings-submit' => 'Submit button',
	'translate-fs-userpage-level-N' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-userpage-level-5' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-userpage-level-4' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-userpage-level-3' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-userpage-level-2' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-userpage-level-1' => 'A language skill level option.
It is used inside a selector, which is followed by another selector, where you choose a language.
Wording of this message may refer to it per "this language" or "the selected language", for example.

The data collected via the pair of selectors will later be used in the <code>{<!-- -->{#Babel|&hellip;}}</code> context.',
	'translate-fs-permissions-planguage' => '{{Identical|Primary language}}',
	'translate-fs-permissions-pending' => 'Parameters:
* $1 - page title of the thread',
	'translate-fs-target-text' => 'Parameters:
* $1 - language code (e.g. Fr)
The title for this message is: "{{int:Translate-fs-pagetitle|{{int:Translate-fs-target-title}}}}"

See also:
* {{msg-mw|Translate-fs-pagetitle}}
* {{msg-mw|Translate-fs-target-title}}',
	'translate-fs-email-text' => 'Preceded by {{msg-mw|Translate-fs-email-title}}.

Refers to {{msg-mw|Prefs-personal}}.',
);

/** Arabic (العربية)
 * @author OsamaK
 * @author ترجمان05
 * @author روخو
 */
$messages['ar'] = array(
	'firststeps' => 'الخطوات الأولى',
	'translate-fs-pagetitle-done' => '- تمّ!',
	'translate-fs-pagetitle-pending' => ' - معلقة',
	'translate-fs-pagetitle' => 'معالج البدء  - $1',
	'translate-fs-signup-title' => 'سجّل',
	'translate-fs-settings-title' => 'اضبط تفضيلاتك',
	'translate-fs-userpage-title' => 'أنشئ صفحة المستخدم',
	'translate-fs-permissions-title' => 'اطلب صلاحيات مترجم',
	'translate-fs-target-title' => 'ابدأ الترجمة!',
	'translate-fs-email-title' => 'أكّد عنوان بريدك الإلكتروني',
	'translate-fs-selectlanguage' => 'اختر اللغة',
	'translate-fs-settings-planguage' => 'اللغة الأساسية:',
	'translate-fs-settings-slanguage' => 'مساعد لغوي $1:',
	'translate-fs-userpage-level-5' => 'أنا مترجم محترف في',
	'translate-fs-userpage-level-3' => 'لدي نزعة قيادية جيدة في',
	'translate-fs-userpage-level-2' => 'لدي نزعة قيادية متوسطة في',
	'translate-fs-userpage-level-1' => 'أعرف القليل',
	'translate-fs-userpage-help' => 'يرجى الإشارة إلى مهاراتك اللغوية واخبرنا شيئا عن نفسك. إذا كنت تعرف أكثر من خمس لغات يمكنك إضافة المزيد لاحقا.', # Fuzzy
	'translate-fs-userpage-submit' => 'أنشئ صفحة المستخدم', # Fuzzy
	'translate-fs-userpage-done' => 'أحسنت! لديك الآن صفحة مستخدم.',
	'translate-fs-permissions-planguage' => 'اللغة الأساسية:',
	'translate-fs-permissions-help' => 'الآن تحتاج إلى لطلب مكان تضاف فيه إلى مجموعة مترجمين.

حدد اللغة الأساسية أنت سوف تترجم الى.

يمكنك ذكر لغات وملاحظات أخرى في مربع النص أدناه.',
	'translate-fs-permissions-submit' => 'إرسال طلب',
);

/** Assamese (অসমীয়া)
 * @author Bishnu Saikia
 */
$messages['as'] = array(
	'firststeps' => 'প্ৰথম পৰ্যায়',
	'translate-fs-pagetitle-done' => ' - কৰা হ’ল!',
	'translate-fs-signup-title' => 'সদস্য ভুক্তি',
	'translate-fs-userpage-title' => 'আপোনাৰ সদস্য পৃষ্ঠা সৃষ্টি কৰক',
	'translate-fs-target-title' => 'ভাঙনি আৰম্ভ কৰক',
	'translate-fs-email-title' => 'আপোনাৰ ই-মেইল ঠিকনাটো প্ৰমাণিত কৰক',
	'translate-fs-settings-planguage' => 'প্ৰাথমিক ভাষা:',
	'translate-fs-permissions-planguage' => 'প্ৰাথমিক ভাষা:',
	'translate-fs-permissions-submit' => 'অনুৰোধ প্ৰেৰণ কৰক',
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'firststeps' => 'Primeros pasos',
	'firststeps-desc' => "[[Special:FirstSteps|Páxina especial]] pa los usuarios que principien con una wiki qu'use la estensión Translate",
	'translate-fs-pagetitle-done' => '- ¡fecho!',
	'translate-fs-pagetitle-pending' => ' - pendiente',
	'translate-fs-pagetitle' => 'Asistente pa los primeros pasos - $1',
	'translate-fs-signup-title' => "Date d'alta",
	'translate-fs-settings-title' => 'Configura les tos preferencies',
	'translate-fs-userpage-title' => "Crea la to páxina d'usuariu",
	'translate-fs-permissions-title' => 'Pidi permisos de traductor',
	'translate-fs-target-title' => '¡Comienza a traducir!',
	'translate-fs-email-title' => 'Confirma la to direición de corréu',
	'translate-fs-intro' => "Bienveníu al asistente pa dar los primeros pasos en {{SITENAME}}.
Vamos guiate, pasu ente pasu, pel procesu de convertite nun traductor.
Cuando acabes, podrás traducir los ''mensaxes de la interfaz'' de tolos proyeutos sofitaos en {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Escueyi una llingua',
	'translate-fs-settings-planguage' => 'Llingua principal:',
	'translate-fs-settings-planguage-desc' => 'A llingua principal ye tanto la llingua de la interfaz de la wiki
como la llingua predeterminada pa facer les traducciones.',
	'translate-fs-settings-slanguage' => "Llingua d'ayuda $1:",
	'translate-fs-settings-slanguage-desc' => 'Ye posible amosar les traducciones de los mensaxes a otres llingües ne editor de traducciones.
Equí pues escoyer qué llingües quies ver, si quies dalguna.',
	'translate-fs-settings-submit' => 'Guardar les preferencies',
	'translate-fs-userpage-level-N' => 'Soi falante nativu de',
	'translate-fs-userpage-level-5' => 'Soi traductor profesional de',
	'translate-fs-userpage-level-4' => 'La conozo como un falante nativu',
	'translate-fs-userpage-level-3' => 'Tengo un bon dominiu de',
	'translate-fs-userpage-level-2' => 'Tengo un dominiu moderáu de',
	'translate-fs-userpage-level-1' => 'Se un poco de',
	'translate-fs-userpage-help' => 'Indica les tos capacidaes llingüístiques y cunta daqué tocante a ti. Si sabes más de cinco llingües, podrás amestales más alantre.',
	'translate-fs-userpage-submit' => "Cree la so páxina d'usuariu",
	'translate-fs-userpage-done' => "¡Bien fecho! Agora tienes una páxina d'usuariu.",
	'translate-fs-permissions-planguage' => 'Llingua principal:',
	'translate-fs-permissions-help' => "Agora tienes de facer una solicitú pa que t'amiesten al grupu de traductores.
Seleiciona la llingua principal a la que vas a traducir.

Pues mentar más llingües y otros comentarios nel cuadru de testu d'abaxo.",
	'translate-fs-permissions-pending' => "La to solicitú s'unvió a «[[$1]]» y dalguién del equipu d'esi sitiu la revisará tan ceo como pueda.
Si confirmes la to direición de corréu electrónicu, recibirás un avisu pel corréu cuando lo faiga.",
	'translate-fs-permissions-submit' => 'Unviar la solicitú',
	'translate-fs-target-text' => "¡Felicidaes!
Agora pues comenzar a traducir.

Nun tengas mieu si te paez nuevo y te confunde.
Na [[Project list]] hai una vista xeneral de los proyeutos nos que pues collaborar coles tos traducciones.
La mayoría de los proyeutos tien una páxina de descripción curtia con un enllaz \"''Traducir esti proyeutu''\", que te llevará a una páxina cola llista de tollos mensaxes por traducir.
Tamién ta disponible la llista de tolos grupos de mensaxes col [[Special:LanguageStats|estáu actual de la traducción a una llingua]].

Si crees que necesites entender más enantes de principiar coles traducciones, pues lleer les [[FAQ|Entrugues frecuentes]].
Por desgracia la documentación pue tar ensin actualizar dacuando.
Si hai dalgo que crees que podríes facer, pero nun yes a alcontrar cómo, nun duldes n'entrugalo na [[Support|páxina de sofitu]].

Tamién pues ponete en contautu con otros traductores a la mesma llingua na [[Portal_talk:\$1|páxina d'alderique]] del [[Portal:\$1|portal de la to llingua]].
Si nun lo ficisti entá, [[Special:Preferences|camuda la llingua de la interfaz d'usuariu a la llingua a la que quies traducir]], pa que la wiki te pueda amosar los enllaces más relevantes pa ti.",
	'translate-fs-email-text' => 'Por favor da la to direición de corréu electrónicu nes tos [[Special:Preferences|preferencies]] y confírmala dende\'l corréu que vamos unviate.

Esto permite qu\'otros usuarios se pongan en contautu contigo per corréu.
Tamién recibirás boletinos de noticies tolo más una vegada al mes.
Si nun quies recibir boletinos de noticies, pues desapuntate na llingüeta "{{int:prefs-personal}}" de les tos [[Special:Preferences|preferencies]].',
);

/** Azerbaijani (azərbaycanca)
 * @author Khan27
 */
$messages['az'] = array(
	'firststeps' => 'İlk addımlar',
	'translate-fs-pagetitle-done' => ' - hazırdı!',
	'translate-fs-pagetitle-pending' => ' - gözləyir',
	'translate-fs-signup-title' => 'Qeydiyyatdan keç',
	'translate-fs-settings-title' => 'Təklifinizi nizamlayın',
	'translate-fs-userpage-title' => 'İstifadəçi səhifəni yarat',
	'translate-fs-permissions-title' => 'Tərcüməçi icazələrini istə',
	'translate-fs-target-title' => 'Tərcüməyə başla!',
	'translate-fs-email-title' => 'E-poçt ünvanını təsdiq et',
	'translate-fs-settings-planguage' => 'İlkin dil:',
	'translate-fs-settings-slanguage' => 'Köməkçi dil $1:',
	'translate-fs-settings-submit' => 'Nizamlamaları saxla',
	'translate-fs-userpage-submit' => 'Öz istifadəçi səhifəmi yarat', # Fuzzy
	'translate-fs-userpage-done' => 'Çox gözəl! İndi bir istifadəçi səhifəniz var.',
	'translate-fs-permissions-planguage' => 'İlkin dil:',
	'translate-fs-permissions-submit' => 'Sorğu göndər',
);

/** South Azerbaijani (تورکجه)
 * @author Mousa
 */
$messages['azb'] = array(
	'firststeps' => 'ایلک آددیملار',
	'firststeps-desc' => 'چئویرمه اوزانتیسینی ایشلتماغا ویکی‌ده ایستیفاده‌چیلری یولا سالماق اوچون [[Special:FirstSteps|اؤزل صحیفه]]',
	'translate-fs-pagetitle-done' => ' - ائدیلدی!',
	'translate-fs-pagetitle-pending' => ' - گؤزلَنیلیر',
	'translate-fs-pagetitle' => 'ایشه باشلاماق سحربازی - $1',
	'translate-fs-signup-title' => 'آد یازدیر',
	'translate-fs-settings-title' => 'ترجیحلرینی تنظیم‌له',
	'translate-fs-userpage-title' => 'اؤز ایستیفاده‌چی صحیفه‌نیزی یارادین',
	'translate-fs-permissions-title' => 'ترجومه‌چی ایجازه‌لری ایسته‌یین',
	'translate-fs-target-title' => 'چئویرمه‌یه باشلا!',
	'translate-fs-email-title' => 'ایمیل آدرسینی دوغرولا',
	'translate-fs-intro' => "{{SITENAME}} ایلک آددیملار سحربازینا خوش گلمیسینیز.
بوردا آددیم آددیم  بیر ترجومه‌چی اولماغا یاردیم اولاجاقسینیز.
سونوندا سیز {{SITENAME}}-ده دستک‌لنن بوتون پروژه‌لرین ''آرا-اوز مئساژلارینی'' چئویره بیله‌جکسینیز.",
	'translate-fs-selectlanguage' => 'بیر دیل سئچین',
	'translate-fs-settings-planguage' => 'اصلی دیل:',
	'translate-fs-settings-planguage-desc' => 'اصلی دیل بو ویکی‌ده سیزین آرا-اوز دیلینیز
و چئویرمک اوچون سیزین ایلک هدف دیلینیز اولاجاق‌دیر.',
	'translate-fs-settings-slanguage' => 'یاردیم‌چی دیل $1:',
	'translate-fs-settings-slanguage-desc' => 'چئویرمک قوتوسوندا، مئساژلارین چئویرمه‌لرینی آیری دیل‌لرده ده گؤسترمک اولا بیلر.
بوردا ائده بیلرسینیز او دیل‌لری، اگر اولسا، سئچه‌سینیز.',
	'translate-fs-settings-submit' => 'ترجیحلری قئید ائت',
	'translate-fs-userpage-level-N' => 'بو منیم آنا دیلیم‌دیر',
	'translate-fs-userpage-level-5' => 'من بو دیل‌ده بیر ماهر ترجومه‌چی‌یم',
	'translate-fs-userpage-level-4' => 'من بو دیلی آنا دیلیم کیمی بیلیرم',
	'translate-fs-userpage-level-3' => 'من بو دیل‌ده یاخشی بیلگیم وار',
	'translate-fs-userpage-level-2' => 'من بو دیل‌ده اورتا بیلگیم وار',
	'translate-fs-userpage-level-1' => 'من بو دیلی بیر آز باشاریرام',
	'translate-fs-userpage-help' => 'لوطفاً اؤز دیل مهارت‌لرینیزی بیلدیرین و بیزه اؤزونوزه گؤره بیر آز دئیین. اگر بئش دیل‌دن چوخ بیلیرسینیز، سونرا چوخ آرتیرا بیلرسینیز.', # Fuzzy
	'translate-fs-userpage-submit' => 'ایستیفاده‌چی صحیفه‌نیزی یارادین',
	'translate-fs-userpage-done' => 'لاپ یاخشی! ایندی سیزین ایستیفاده‌چی صحیفه‌نیز واردیر.',
	'translate-fs-permissions-planguage' => 'اصلی دیل:',
	'translate-fs-permissions-help' => 'سیز ترجومه‌چی گروپونو آرتیریلماق اوچون، گرک بیر ایستک یول‌لایاسینیز.
اصلی دیل کی اونا چئویره‌جکسینیز، سئچین.

آیری دیل‌لر و آیری توضیحلری، آشاغیداکی یازی قوتوسوندا دئیه بیلرسینیز.',
	'translate-fs-permissions-pending' => 'سیزین ایستگینیز [[$1]]-ه یول‌لاندی و ایلک زامان‌دا سایت آداملاریندان بیری اونو یوخلایاجاق‌دیر.
اگر ایمیل آدرسینیز دوغرولاساز، او ایش اولان زامان ایمیل ایله بیله‌جکسینیز.',
	'translate-fs-permissions-submit' => 'ایستگی یول‌لا',
	'translate-fs-target-text' => "تبریکلر!
سیز ایند چئویرمگه باشلیا بیلرسینیز.

اگر هله بیر آز سیزه گیجردن کیمی اولسا، قورخمایین.
[[Project list|پروژه‌لر لیستی]]نده، سیزد اونلاردا چالیشا بیلن پروژه‌لرین تانیتماسی واردیر.
پروژه‌لرین چوخوندا بیر کیچیک تعریف وار و بیر «''بو پروژه‌نی چئویر''» باغلانتی‌سی وار کی سیزی چئویریلمه‌میش مئساژلاری لیست ائدن صحیفه‌یه آپارار.
هم‌ده مئساژ گروپلارینین لیستی، [[Special:LanguageStats|بیر دیل‌ده ایندیکی چئویرمه وضعیتی]] ایله بیرلیک‌ده واردیر.

اگر هله‌ده ایشه باشلاماغا داها بیلگی اله گتیرمگی گرکلی گؤرورسونوز، [[FAQ|چوخلو سوروشولموش سوال‌لار]]دا اوخویا بیلرسینیز.
تأسف‌له بعضی واختلار سندلندیرمه‌لر گونجل دئییل‌لر.
اگر بیر ایش وار کی اونا ائتمک ایمکانینی گرکلی گؤرورسونوز، اما تاپانمیرسینیز نئجه، [[Support|دستک صحیفه‌سی]]نده سوروشماقدان چکینمه‌یین.

هر دیلین چئویرنلریله [[Portal:$1|او دیلین پورتال صحیفه‌سی]]نین [[Portal_talk:$1|دانیشیق صحیفه‌سی]]نده ایلگی قورا بیلرسینیز.
اگر ایندیه کیمی ائتمه‌میسینیز، [[Special:Preferences|آرا-اوز دیلینی، ترجومه ائتمک ایسته‌ین دیله چئویر]]ه بیلرسینیز، بئله‌لیکله ویکی سیزه داها ایلگیلی اولان باغلانتیلاری گؤستره بیلر.",
	'translate-fs-email-text' => 'لوطفاً ایمیل آدرسینیزی [[Special:Preferences|ترجیحلرینیز]]ده وئرین و اونو سیزه گلن ایمیل ایله دوغرولایین.

بو ایجازه وئرر آیری ایستیفاده‌چیلر سیزله ایمیل ایله ایلگی قورا بیلسینلر.
هم‌ده سیز چوخو آی‌دا بیر دفعه خبرنامه آلابیلرسینیز.
اگر خبرنامه آلماغی ایسته‌میرسینیز، [[Special:Preferences|ترجیحلرینیز]]ین «{{int:prefs-personal}}» بؤلوموندن بونو بیلدیره بیلرسینیز.',
);

/** Bashkir (башҡортса)
 * @author Assele
 */
$messages['ba'] = array(
	'firststeps' => 'Тәүге аҙымдар',
	'firststeps-desc' => 'Викилағы тәржемә киңәйеүен ҡуллана башлаусы яңы ҡатнашыусылар өсөн [[Special:FirstSteps|Махсус бит]]',
	'translate-fs-pagetitle-done' => ' — булды!',
	'translate-fs-pagetitle' => 'Башланғыс өйрәнеү программаһы — $1',
	'translate-fs-signup-title' => 'Теркәлегеҙ',
	'translate-fs-settings-title' => 'Көйләгеҙ',
	'translate-fs-userpage-title' => 'Үҙегеҙҙең ҡатнашыусы битен булдырығыҙ',
	'translate-fs-permissions-title' => 'Тәржемәсе хоҡуҡтарын һорағыҙ',
	'translate-fs-target-title' => 'Тәржемә итә башлағыҙ!',
	'translate-fs-email-title' => 'Электрон почта адресығыҙҙы раҫлағыҙ',
	'translate-fs-intro' => '{{SITENAME}} башланғыс өйрәнеү программаһына рәхим итегеҙ.
Һеҙ тәржемәселәр өйрәнеү программаһы буйынса аҙымлап үтерһегеҙ.
Әҙерлек үтеү менән, һеҙ {{SITENAME}} проектында мөмкин булған бөтә интерфейс яҙмаларын тәржемә итә аласаҡһығыҙ.',
	'translate-fs-userpage-submit' => 'Минең ҡатнашыусы битен булдырырға', # Fuzzy
	'translate-fs-userpage-done' => 'Бик яҡшы! Хәҙер һеҙҙең ҡатнашыусы битегеҙ бар.',
	'translate-fs-target-text' => "Ҡотлайбыҙ!
Хәҙер һеҙ тәржемә итә башлай алаһығыҙ.

Әгәр нимәлер һеҙгә һаман да яңы һәм буталған һымаҡ күренһә, ҡурҡмағыҙ.
[[Project list|Проекттар битендә]] һеҙ тәржемә итә алған проекттар исемлеге бар.
Проекттарҙың күпселегенең ҡыҫҡаса тасуирламаһы һәм бөтә тәржемә ителмәгән яҙмалар исемлеге менән биткә барған ''«Был проектты тәржемә итергә»'' һылтанмаһы бар.
Шулай уҡ [[Special:LanguageStats|тел өсөн хәҙерге тәржемә статусы]] күрһәтелгән бөтә яҙмалар төркөмө исемлеге бар.

Әгәр һеҙгә тәржемә итер алдынан күберәк мәғлүмәт алырға кәрәк һымаҡ күренһә, һеҙ [[FAQ|йыш бирелгән һорауҙар]] менән таныша алаһығыҙ.
Ҡыҙғанысҡа ҡаршы, ҡайһы бер мәғлүмәт иҫкергән булыуы мөмкин.
Әгәр нимәнелер, һеҙҙең уйығыҙса, эшләй алаһығыҙ, әммә нисек эшләргә белмәйһегеҙ икән, [[Support|ярҙам битендә]] был турала һорарға оялмағыҙ.

Һеҙ шулай уҡ тәржемәселәр менән [[Portal:$1|һеҙҙең тел порталының]] [[Portal_talk:$1|фекерләшеү битендә]] аралаша алаһығыҙ.
Әгәр һеҙ быларҙы әле эшләмәһәгеҙ, үҙегеҙҙең [[Special:Preferences|көйләүҙәр битендә]] ниндәй телгә тәржемә итергә йыйынаһығыҙ, шул телде күрһәтегеҙ, һәм кәрәкле һылтанмалар интерфейста күрһәтеләсәк.",
	'translate-fs-email-text' => 'Зинһар, үҙегеҙҙең [[Special:Preferences|көйләү битендә]] электрон почта адресығыҙҙы күрһәтегеҙ һәм уны ебәреләсәк хат аша раҫлағыҙ.

Был башҡа ҡатнашыусыларға һеҙҙең менән электрон почта аша аралашырға мөмкинлек бирәсәк.
Һеҙ шулай уҡ айына бер яңылыҡтар алып торасаҡһығыҙ.
Әгәр һеҙ яңылыҡтар алырға теләмәһәгеҙ, һеҙ унан [[Special:Preferences|көйләүҙәр битендә]],  «{{int:prefs-personal}}» бүлегендә баш тарта алаһығыҙ.',
);

/** Bavarian (Boarisch)
 * @author Mucalexx
 */
$messages['bar'] = array(
	'firststeps' => "D' erschten Schriet",
);

/** Belarusian (Taraškievica orthography) (беларуская (тарашкевіца)‎)
 * @author EugeneZelenko
 * @author Jim-by
 * @author Renessaince
 * @author Wizardist
 * @author Zedlik
 */
$messages['be-tarask'] = array(
	'firststeps' => 'Першыя крокі',
	'firststeps-desc' => '[[Special:FirstSteps|Спэцыяльная старонка]] для пачатку працы з пашырэньнем Translate',
	'translate-fs-pagetitle-done' => ' — зроблена!',
	'translate-fs-pagetitle-pending' => ' — чаканьне',
	'translate-fs-pagetitle' => 'Майстар пачатковага навучаньня — $1',
	'translate-fs-signup-title' => 'Зарэгіструйцеся',
	'translate-fs-settings-title' => 'Вызначыце Вашыя налады',
	'translate-fs-userpage-title' => 'Стварыце Вашую старонку ўдзельніка',
	'translate-fs-permissions-title' => 'Запытайце правы перакладчыка',
	'translate-fs-target-title' => 'Пачніце перакладаць!',
	'translate-fs-email-title' => 'Пацьвердзіць Ваш адрас электроннай пошты',
	'translate-fs-intro' => "Запрашаем у майстар пачатковага навучаньня {{GRAMMAR:родны|{{SITENAME}}}}.
Вас правядуць праз працэс станаўленьня перакладчыкам крок за крокам.
Пасьля гэтага Вы зможаце перакладаць ''паведамленьні інтэрфэйсу'' ўсіх праектаў, якія падтрымліваюцца ў {{GRAMMAR:месны|{{SITENAME}}}}.",
	'translate-fs-selectlanguage' => 'Выберыце мову',
	'translate-fs-settings-planguage' => 'Асноўная мова:',
	'translate-fs-settings-planguage-desc' => 'Асноўная мова выступае ў ролі як мовы інтэрфэйсу, гэтак і перадвызначанай мовы перакладу.',
	'translate-fs-settings-slanguage' => 'Дапаможная мова $1:',
	'translate-fs-settings-slanguage-desc' => 'Існуе магчымасьць паказваць пераклады паведамленьняў на іншыя мовы ў акне рэдактара перакладаў.
Тут Вы можаце выбраць мовы, калі патрэбна, на якіх будуць паказвацца падобныя пераклады.',
	'translate-fs-settings-submit' => 'Захаваць налады',
	'translate-fs-userpage-level-N' => 'Мая родная мова',
	'translate-fs-userpage-level-5' => 'Я — прафэсійны перакладчык на',
	'translate-fs-userpage-level-4' => 'Ведаю яе як родную',
	'translate-fs-userpage-level-3' => 'Добра валодаю',
	'translate-fs-userpage-level-2' => 'На сярэднім узроўні валодаю',
	'translate-fs-userpage-level-1' => 'Крыху знаю',
	'translate-fs-userpage-help' => 'Калі ласка, пазначце вашыя моўныя здатнасьці і раскажыце пра сябе. Калі вы ведаеце больш як пяць моваў, вы зможаце дадаць астатнія пазьней.',
	'translate-fs-userpage-submit' => 'Стварыць вашую старонку ўдзельніка',
	'translate-fs-userpage-done' => 'Выдатна! Цяпер Вы маеце старонку ўдзельніка.',
	'translate-fs-permissions-planguage' => 'Асноўная мова:',
	'translate-fs-permissions-help' => 'Цяпер вам трэба даслаць запыт на далучэньне да групы перакладчыкаў.
Выберыце асноўную мову, на якую вы зьбіраецеся перакладаць.

Вы можаце прыгадаць і іншыя мовы разам з заўвагамі ў полі ніжэй.',
	'translate-fs-permissions-pending' => 'Ваш запыт быў дасланы ў [[$1]] і нехта з адміністрацыі сайту зоймецца ім як мага хутчэй.
Калі вы пацьвердзіце ваш e-mail адрас, вы атрымаеце апавяшчэньне па пошце, як толькі нешта будзе вядома.',
	'translate-fs-permissions-submit' => 'Даслаць запыт',
	'translate-fs-target-text' => "Віншуем!
Цяпер Вы можаце пачаць перакладаць.

Не бойцеся, калі што-небудзь здаецца Вам новым і незразумелым.
У [[Project list|сьпісе праектаў]] знаходзіцца агляд праектаў, для якіх Вы можаце перакладаць.
Большасьць праектаў мае старонку з кароткім апісаньнем са спасылкай «''Перакласьці гэты праект''», якая перанясе Вас на старонку са сьпісам усіх неперакладзеных паведамленьняў.
Таксама даступны сьпіс усіх групаў паведамленьняў з [[Special:LanguageStats|цяперашнім статусам перакладу для мовы]].

Калі Вам здаецца, што неабходна даведацца болей перад пачаткам перакладаў, Вы можаце пачытаць [[FAQ|адказы на частыя пытаньні]].
На жаль дакумэнтацыя можа быць састарэлай.
Калі ёсьць што-небудзь, што, як Вы мяркуеце, Вы можаце зрабіць, але ня ведаеце як, не вагаючыся пытайцеся на [[Support|старонцы падтрымкі]].

Таксама, Вы можаце зьвязацца з перакладчыкамі на Вашую мову на [[Portal_talk:$1|старонцы абмеркаваньня]] [[Portal:$1|парталу Вашай мовы]].
Калі Вы яшчэ гэтага не зрабілі, Вы можаце [[Special:Preferences|зьмяніць Вашыя моўныя налады інтэрфэйсу на мову, на якую жадаеце перакладаць]], для таго каб вікі паказала Вам адпаведныя спасылкі.",
	'translate-fs-email-text' => 'Калі ласка, падайце адрас Вашай электроннай пошты ў [[Special:Preferences|Вашых наладах]] і пацьвердзіце яго з электроннага ліста, які будзе Вам дасланы.

Гэта дазволіць іншым удзельнікам зносіцца з Вамі праз электронную пошту.
Таксама, Вы будзеце атрымліваць штомесячныя лісты з навінамі.
Калі Вы не жадаеце атрымліваць лісты з навінамі, Вы можаце адмовіцца ад іх на закладцы «{{int:prefs-personal}}» Вашых [[Special:Preferences|наладаў]].',
);

/** Bulgarian (български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'firststeps' => 'Първи стъпки',
	'translate-fs-signup-title' => 'Регистриране',
	'translate-fs-settings-submit' => 'Съхраняване на предпочитанията',
	'translate-fs-userpage-done' => 'Готово! Вече имате потребителска страница.',
);

/** Bengali (বাংলা)
 * @author Aftab1995
 * @author Bellayet
 */
$messages['bn'] = array(
	'firststeps' => 'প্রথম ধাপ',
	'translate-fs-pagetitle-done' => ' - সম্পন্ন!',
	'translate-fs-pagetitle-pending' => ' - অমীমাংসিত',
	'translate-fs-pagetitle' => 'আরম্ভ করার উইজার্ড - $1',
	'translate-fs-signup-title' => 'নিবন্ধন',
	'translate-fs-settings-title' => 'আপনার পছন্দসমূহ নির্ধারণ করুন',
	'translate-fs-userpage-title' => 'আপনার ব্যবহারকারী পাতা তৈরি করুন',
	'translate-fs-permissions-title' => 'অনুবাদক হিসেবে অনুমোদনের আবেদন',
	'translate-fs-target-title' => 'অনুবাদ করা শুরু!',
	'translate-fs-email-title' => 'আপনার ই-মেইলের ঠিকানা নিশ্চিত করুন',
	'translate-fs-selectlanguage' => 'যেকোন ভাষা নির্বাচন করুন',
	'translate-fs-settings-planguage' => 'প্রধান ভাষা:',
	'translate-fs-settings-slanguage' => 'সহযোগী ভাষা $1:',
	'translate-fs-settings-submit' => 'পছন্দ সংরক্ষণ',
	'translate-fs-userpage-submit' => 'আপনার ব্যবহারকারী পাতা তৈরি করুন',
	'translate-fs-permissions-planguage' => 'প্রধান ভাষা:',
	'translate-fs-permissions-submit' => 'অনুরোধ পাঠাও',
);

/** Tibetan (བོད་ཡིག)
 * @author Freeyak
 */
$messages['bo'] = array(
	'firststeps' => 'ཐོག་མའི་གོམ་པ།',
	'translate-fs-pagetitle-done' => '- འགྲིག་སོང་།',
	'translate-fs-signup-title' => 'ཐོ་འགོད་པ།',
	'translate-fs-userpage-title' => 'སྤྱོད་མིའི་ཤོག་ངོས་གསར་བཟོ།',
	'translate-fs-permissions-title' => 'སྐད་སྒྱུར་བའི་ཆོག་འཆན་ཞུ་བ།',
	'translate-fs-target-title' => 'སྐད་སྒྱུར་འགོ་འཛུགས་པ།',
	'translate-fs-email-title' => 'ཁྱེད་ཀྱི་གློག་འཕྲིན་ཁ་བྱང་གཏན་འཁེལ་བྱེད་པ།',
	'translate-fs-userpage-submit' => 'ངའི་སྤྱོད་མིའི་ཤོག་ངོས་བཟོ་བ།', # Fuzzy
	'translate-fs-userpage-done' => 'ཡག་པོ་བྱུང་། ད་ནི་ཁྱོད་ལ་སྤྱོད་མིའི་ཤོག་ངོས་ཡོད།',
);

/** Breton (brezhoneg)
 * @author Fulup
 * @author Y-M D
 */
$messages['br'] = array(
	'firststeps' => 'Pazenn gentañ',
	'firststeps-desc' => '[[Special:FirstSteps|Pajenn dibar]] evit hentañ an implijerien war ur wiki a implij an astenn Translate',
	'translate-fs-pagetitle-done' => ' - graet !',
	'translate-fs-pagetitle-pending' => ' - war ober',
	'translate-fs-pagetitle' => "Heñcher loc'hañ - $1",
	'translate-fs-signup-title' => 'En em enskrivañ',
	'translate-fs-settings-title' => "Kefluniañ hoc'h arventennoù",
	'translate-fs-userpage-title' => 'Krouiñ ho pajenn implijer',
	'translate-fs-permissions-title' => 'Goulennit an aotreoù troer',
	'translate-fs-target-title' => 'Kregiñ da dreiñ !',
	'translate-fs-email-title' => "Kadarnait ho chomlec'h postel",
	'translate-fs-intro' => 'Degemer mat deoc\'h er skoazeller a ambrougo ho pazennoù kentañ war {{SITENAME}}.
Heñchet e viot kammed-ha-kammed evit dont da vezañ un troer.
En dibennn e c\'hallot treiñ "kemennadennoù etrefas" an holl raktresoù meret gant {{SITENAME}}.',
	'translate-fs-selectlanguage' => 'Dibab ur yezh',
	'translate-fs-settings-planguage' => 'Yezh pennañ :',
	'translate-fs-settings-planguage-desc' => 'Talvezout a ra ar yezh pennañ da yezh an etrefas evit ar wiki-mañ ha da yezh labour evit an troidigezhioù.',
	'translate-fs-settings-slanguage' => 'Yezh skoazell $1 :',
	'translate-fs-settings-slanguage-desc' => "Posupl eo d'ar skridaozer treiñ diskouez deoc'h troidigezhioù ar c'hemennadennoù e yezhoù all .
Amañ e c'hallit dibab peseurt yezhoù a garfec'h gwelet, mar karit.",
	'translate-fs-settings-submit' => 'Enrollañ ar penndibaboù',
	'translate-fs-userpage-level-N' => 'A-vihanik e komzan',
	'translate-fs-userpage-level-5' => 'Troer a-vicher on war ar',
	'translate-fs-userpage-level-4' => 'Evel ur yezher a-vihanik e komzan',
	'translate-fs-userpage-level-3' => 'Ampart on war ar',
	'translate-fs-userpage-level-2' => "Barrek a-walc'h on war ar",
	'translate-fs-userpage-level-1' => 'Un tammig e ouzon',
	'translate-fs-userpage-help' => "Roit titouroù diwar-benn ho parregezhioù yezh ha kontit deomp un draig bennak diwar ho penn. Mard ouzit ouzhpenn 5 yezh e c'hallot ouzhpennañ anezho diwezhatoc'hik.", # Fuzzy
	'translate-fs-userpage-submit' => 'Krouiñ ho pajenn implijer',
	'translate-fs-userpage-done' => "Dispar ! Ur bajenn implijer hoc'h eus bremañ.",
	'translate-fs-permissions-planguage' => 'Yezh pennañ :',
	'translate-fs-permissions-help' => "Bremañ eo ret deoc'h goulenn ma vo ouzhpennet hoc'h anv e strollad an droerien.
Dibabit ar yezh pennañ hoc'h eus c'hoant da dreiñ enni.

Gallout a rit menegiñ yezhoù all ha lakaat evezhiadennoù all en takad skrivañ a-is.",
	'translate-fs-permissions-pending' => "Kaset eo bet ho koulenn da [[$1]] ha gwiriet e vo gant unan bennak eus ar skipailh a-raok pell.
Ma kadarnait ho chomlec'h postel e resevot ur gemennadenn dre bostel pa vo bet graet.",
	'translate-fs-permissions-submit' => 'Kas ar goulenn',
	'translate-fs-target-text' => "Gourc'hemennoù !
Kregiñ da dreiñ a c'hallit ober bremañ.

Arabat bezañ chalet ma seblant pep tra bezañ nevez ha divoas.
E [[Project list|Roll ar raktresoù]] e c'hallit kaout ur sell war an holl raktresoù a c'hallit kemer perzh en o zroidigezh.
Ar pep brasañ eus ar raktresoù zo bet savet evito ur bajenn warni un deskrivadur berr gant ul liamm \"''Troit ar raktres-mañ''\", a gaso ac'hanoc'h d'ur bajenn ma kavot an holl gemennadennoù didro.
Gallout a c'haller kaout ivez roll an holl gemennadennoù dre strollad dre [[Special:LanguageStats|stad an troidigezhioù en ur yezh bennak]].

Ma soñj deoc'h eo ret deoc'h kompren gwelloc'h an traoù a-raok stagañ ganti, e c'hallit lenn [[FAQ|Foar ar Goulennoù]].
Diwallit, a-wezhioù e c'hall bezañ blaz ar c'hozh gant an titouroù.
Ma soñj deoc'h ez eus un dra bennak a zlefec'h bezañ gouest d'ober ha ma ne gavit ket, kit da c'houlenn war [[Support|ar bajenn skoazell]].

Gallout a rit ivez mont e darempred gant ho keneiled troourien a ra gant ho yezh war [[Portal_talk:\$1|pajenn gaozeal]] [[Portal:\$1|ar porched evit ho yezhl]].
Mar n'eo ket bet graet ganeoc'h c'hoazh e c'hallit [[Special:Preferences|lakaat yezh hoc'h etrefas implijer er yezh a fell deoc'h treiñ enni]]. Evel-se e c'hallo ar wiki kinnig deoc'h al liammoù a zere ar gwellañ evideoc'h.",
	'translate-fs-email-text' => "Lakait ho chomlec'h postel en [[Special:Preferences|ho penndibaboù]] ha kadarnait dre ar postel a vo kaset deoc'h.

Evel-se e c'hallo an implijerien all mont e darempred ganeoc'h dre bostel.
Keleier a resevot ivez ur wezh ar miz.
Mar ne fell ket deoc'h resev keleier e c'hallit disteuler anezho dre ivinell \"{{int:prefs-personal}}\" en ho [[Special:Preferences|penndibaboù]].",
);

/** Bosnian (bosanski)
 * @author CERminator
 * @author Palapa
 */
$messages['bs'] = array(
	'firststeps' => 'Prvi koraci',
	'firststeps-desc' => '[[Special:FirstSteps|Posebna stranica]] za pomoć korisnicima koji počinju sa wiki korištenjem proširenja za prevod',
	'translate-fs-pagetitle-done' => ' - urađeno!',
	'translate-fs-pagetitle' => 'Čarobnjak za početak - $1',
	'translate-fs-signup-title' => 'Prijavite se',
	'translate-fs-settings-title' => 'Podesi svoje postavke',
	'translate-fs-userpage-title' => 'Napravi svoju korisničku stranicu',
	'translate-fs-permissions-title' => 'Zahtijevaj prevodilačku dozvolu',
	'translate-fs-target-title' => 'Počni prevoditi!',
	'translate-fs-email-title' => 'Potvrdi svoju e-mail adresu',
	'translate-fs-intro' => "Dobro došli u čarobnjak za prve korake na {{SITENAME}}.
Ovaj čarobnjak će vas postepeno voditi kroz proces dobijanja prava prevodioca.
Na kraju ćete moći prevoditi ''poruke interfejsa'' svih podržanih projekata na {{SITENAME}}.",
	'translate-fs-userpage-submit' => 'Napravi moju korisničku stranicu', # Fuzzy
	'translate-fs-userpage-done' => 'Odlično urađeno! Sada imate korisničku stranicu.',
	'translate-fs-target-text' => 'Čestitamo!
Sad možete početi prevoditi.

Ne plašite se ako se još osjećate novi i zbunjeni.
Na stranici [[Project list]] postavljen je pregled projekata na kojima možete raditi na prevodu.
Najveći dio projekata ima stranicu sa kratkim opisom sa linkom "\'\'Prevedite ovaj projekat\'", koji će vas odvesti na stranicu sa spiskom svih neprevedenih poruka.
Spisak svih grupa poruka sa [[Special:LanguageStats|trenutnim stanjem prevoda za jezik]] je također dostupan.
Ako želite da shvatite više o samom prevođenju prije nego što počnete, možete pročitati [[FAQ|Najčešće postavljana pitanja]].
Nažalost, dokumentacija nekad može biti zastarjela.
Ako nađete nešto što mislite da možete da uradite, a ne znate kako, ne ustručavajte se da pitate na [[Support|stranici za podršku]].

Također možete kontaktirati prijatelje prevodioce na isti jezik na[[Portal_talk:$1|stranici za razgovor]] [[Portal:$1|portala vašeg jezika]].
Ako već niste uradili, [[Special:Preferences|promijenite vaš jezik interfejsa na jezik na koji želite prevoditi]], tako će wiki biti u mogućnosti da vam prikaže najvažnije linkove za vas.',
	'translate-fs-email-text' => 'Molimo navedite vašu e-mail adresu u [[Special:Preferences|vašim postavkama]] i potvrdite je iz vašeg e-maila koji vam je poslan.

Ovo omogućava drugim korisnicima da vas kontaktiraju putem e-maila.
Također ćete dobijati novosti najviše jednom mjesečno.
Ako ne želite primati novosti, možete se odjaviti na jezičku "{{int:prefs-personal}}" u vašim [[Special:Preferences|postavkama]].',
);

/** Catalan (català)
 * @author SMP
 * @author Toniher
 */
$messages['ca'] = array(
	'firststeps' => 'Primers passos',
	'translate-fs-pagetitle-done' => ' - fet!',
	'translate-fs-settings-title' => 'Configureu les vostres preferències',
	'translate-fs-userpage-title' => "Creeu la vostra pàgina d'usuari",
	'translate-fs-target-title' => 'Comenceu a traduir!',
	'translate-fs-email-title' => "Confirmeu l'adreça electrònica",
	'translate-fs-selectlanguage' => 'Trieu una llengua',
	'translate-fs-settings-planguage' => 'Llengua primària:',
	'translate-fs-settings-slanguage' => "Llengua d'ajuda $1:",
	'translate-fs-settings-slanguage-desc' => "És possible mostrar traduccions de missatges en altres llengües en l'editor de traducció. A continuació podeu triar quines llengües voldríeu veure-hi.",
	'translate-fs-settings-submit' => 'Desa les preferències',
	'translate-fs-permissions-submit' => 'Envia la sol·licitud',
);

/** Chechen (нохчийн)
 * @author Умар
 */
$messages['ce'] = array(
	'translate-fs-settings-planguage' => 'Коьрта мотт:',
	'translate-fs-settings-slanguage' => 'ГӀоьнан мотт $1:',
	'translate-fs-userpage-level-N' => 'Сан дай мотт',
	'translate-fs-permissions-planguage' => 'Коьрта мотт:',
);

/** Sorani Kurdish (کوردی)
 * @author Muhammed taha
 */
$messages['ckb'] = array(
	'firststeps' => 'هەنگاوە سەرەتاییەکان',
	'translate-fs-pagetitle-done' => '- تەواو!',
	'translate-fs-signup-title' => '- تۆماربوون',
	'translate-fs-settings-title' => 'هەڵبژاردنەکانت رێکبخە',
	'translate-fs-userpage-title' => 'لاپەڕەی بەکارهێنەریت دروست بکە',
	'translate-fs-permissions-title' => 'داوای دەسەڵاتەکانی وەرگێڕ بکە',
	'translate-fs-target-title' => 'دەست بکە بەوەرگێڕان!',
	'translate-fs-email-title' => 'ئیمەیلەکەت پشت‌ڕاست بکەرەوە',
	'translate-fs-selectlanguage' => 'زمانێک دیاری بکە',
	'translate-fs-settings-planguage' => 'زمانی سەرەکی:',
	'translate-fs-settings-submit' => 'هەڵبژاردنەکانت بپارێزە',
	'translate-fs-userpage-submit' => 'دروستکردنی پەڕەی بەکارهێنەریم', # Fuzzy
	'translate-fs-userpage-done' => 'باشە! ئێستا لاپەڕەی بەکارهێنەریت هەیە.',
	'translate-fs-permissions-planguage' => 'زمانی سەرەکی:',
	'translate-fs-permissions-submit' => 'ناردنی داواکاری',
);

/** Czech (česky)
 * @author Mormegil
 */
$messages['cs'] = array(
	'firststeps' => 'První kroky',
	'firststeps-desc' => '[[Special:FirstSteps|Speciální stránka]] pomáhající uživatelům začít pracovat na wiki s rozšířením Translate',
	'translate-fs-pagetitle-done' => ' – hotovo!',
	'translate-fs-pagetitle-pending' => ' – probíhá',
	'translate-fs-pagetitle' => 'Průvodce začátkem – $1',
	'translate-fs-signup-title' => 'Registrace',
	'translate-fs-settings-title' => 'Úprava nastavení',
	'translate-fs-userpage-title' => 'Založení uživatelské stránky',
	'translate-fs-permissions-title' => 'Žádost o překladatelská práva',
	'translate-fs-target-title' => 'Začněte překládat!',
	'translate-fs-email-title' => 'Ověření e-mailové adresy',
	'translate-fs-intro' => "Vítejte v průvodci prvními kroky po {{grammar:7sg|{{SITENAME}}}}.
Provedeme vás všemi kroky, které jsou třeba, abyste se {{gender:|mohl stát překladatelem|mohla stát překladatelkou|mohli stát překladateli}}.
Na konci budete moci překládat ''zprávy uživatelského rozhraní'' všech projektů podporovaných na {{grammar:6sg|{{SITENAME}}}}.",
	'translate-fs-selectlanguage' => 'Vyberte jazyk',
	'translate-fs-settings-planguage' => 'Primární jazyk:',
	'translate-fs-settings-planguage-desc' => 'Primární jazyk slouží na této wiki i jako jazyk pro vaše rozhraní
a jako implicitní cílový jazyk pro překlady.',
	'translate-fs-settings-slanguage' => 'Pomocný jazyk $1:',
	'translate-fs-settings-slanguage-desc' => 'V editoru překladů je možné zobrazovat překlady zpráv do jiných jazyků.
Zde si můžete zvolit, které jazyky, pokud vůbec nějaké, chcete vidět.',
	'translate-fs-settings-submit' => 'Uložit nastavení',
	'translate-fs-userpage-level-N' => 'Jsem rodilý mluvčí jazyka',
	'translate-fs-userpage-level-5' => 'Jsem profesionální překladatel jazyka',
	'translate-fs-userpage-level-4' => 'Jazyk ovládám jako rodilý mluvčí',
	'translate-fs-userpage-level-3' => 'Mám dobrou znalost jazyka',
	'translate-fs-userpage-level-2' => 'Mám průměrnou znalost jazyka',
	'translate-fs-userpage-level-1' => 'Umím trochu jazyk',
	'translate-fs-userpage-help' => 'Uveďte své jazykové znalosti a řekněte něco o sobě. Pokud umíte víc než pět jazyků, budete později moci přidat další.',
	'translate-fs-userpage-submit' => 'Založte si uživatelskou stránku',
	'translate-fs-userpage-done' => 'Výtečně! Teď máte svou uživatelskou stránku.',
	'translate-fs-permissions-planguage' => 'Primární jazyk:',
	'translate-fs-permissions-help' => 'Nyní bude potřeba požádat o přidání do skupiny překladatelů.
Zvolte primární jazyk, do kterého budete překládat.

Další jazyky a jiné poznámky můžete zmínit v textovém poli níže.',
	'translate-fs-permissions-pending' => 'Vaše žádost byla přidána na [[$1]] a někdo z pracovníků ji co nejdříve zkontroluje.
Pokud si ověříte svou e-mailovou adresu, dostanete poté upozornění e-mailem.',
	'translate-fs-permissions-submit' => 'Odeslat žádost',
	'translate-fs-target-text' => "Gratulujeme!
Teď můžete začít překládat.

Nebojte se, pokud vám to tu připadá nové a matoucí.
Na stránce [[Project list]] najdete přehled projektů, do kterých můžete přispívat překlady.
Většina projektů obsahuje stručný popis a odkaz ''Translate this project'', který vás dovede na stránku s přehledem všech nepřeložených zpráv.
Také je k dispozici seznam všech skupin zpráv spolu s [[Special:LanguageStats|aktuálním stavem překladu do daného jazyka]].

Pokud máte potřebu rozumět věcem lépe, ještě než začnete překládat, můžete si přečíst [[FAQ|často kladené otázky]].
Dokumentace může být bohužel někdy zastaralá.
Pokud najdete něco, co si myslíte, že byste {{gender:|měl být schopen|měla být schopna|měli být schopni}} dělat, ale nejde to, neváhejte se zeptat na [[Support|stránce podpory]].

Také můžete kontaktovat spolupřekladatele do stejného jazyka pomocí [[Portal_talk:$1|diskusní stránky]] [[Portal:$1|vašeho jazykového portálu]].
Pokud jste to dosud {{gender:|neučinil|neučinila|neučinili}}, [[Special:Preferences|nastavte svůj jazyk rozhraní na jazyk, do kterého chcete překládat]], aby vám tato wiki byla schopna ukazovat nejrelevantnější odkazy.",
	'translate-fs-email-text' => 'Prosíme, uveďte v [[Special:Preferences|nastavení]] svou e-mailovou adresu a potvrďte ji pomocí zprávy, která vám byla poslána.

To umožní ostatním, aby vás kontaktovali pomocí e-mailu.
Také budete maximálně jednou měsíčně dostávat novinky.
Pokud novinky nechcete dostávat, můžete se z odběru odhlásit na záložce „{{int:prefs-personal}}“ v [[Special:Preferences|nastavení]].',
);

/** Welsh (Cymraeg)
 * @author Lloffiwr
 */
$messages['cy'] = array(
	'firststeps' => 'Y camau cyntaf',
	'translate-fs-selectlanguage' => 'Dewiswch iaith',
	'translate-fs-settings-planguage' => 'Prif iaith:',
	'translate-fs-settings-submit' => 'Rhodder y dewisiadau ar gadw',
	'translate-fs-userpage-level-N' => 'Yr iaith hon yw fy mamiaith:',
	'translate-fs-userpage-level-5' => 'Rwyn gyfieithydd proffesiynol yn yr iaith hon:',
	'translate-fs-userpage-level-4' => 'Rwyn siarad yr iaith hon yn rhugl:',
	'translate-fs-userpage-level-3' => "Rwyn medru'r iaith hon yn dda:",
	'translate-fs-userpage-level-2' => "Rwyn medru'r iaith hon yn weddol dda:",
	'translate-fs-userpage-level-1' => 'Rwyn medru ychydig ar yr iaith hon:',
	'translate-fs-userpage-help' => 'Nodwch eich sgiliau ieithyddol a dywedwch ychydig amdanoch eich hunain. Os ydych yn siarad mwy na phum iaith gallwch ychwanegu ieithoedd yn hwyrach.',
	'translate-fs-userpage-submit' => 'Dechrau fy nhudalen defnyddiwr',
	'translate-fs-userpage-done' => 'Da iawn! Erbyn hyn mae gennych dudalen defnyddiwr.',
	'translate-fs-permissions-planguage' => 'Prif iaith:',
);

/** Danish (dansk)
 * @author Emilkris33
 * @author Peter Alberti
 */
$messages['da'] = array(
	'firststeps' => 'De første skridt',
	'firststeps-desc' => '[[Special:FirstSteps|Specialside]] for at hjælpe brugere i gang på en wiki, der bruger oversættelsesudvidelsen',
	'translate-fs-pagetitle-done' => '- færdig!',
	'translate-fs-pagetitle-pending' => '- afventer',
	'translate-fs-pagetitle' => 'Kom godt i gang guiden - $1',
	'translate-fs-signup-title' => 'Opret en konto',
	'translate-fs-settings-title' => 'Konfigurer dine indstillinger',
	'translate-fs-userpage-title' => 'Opret din brugerside',
	'translate-fs-permissions-title' => 'Anmodning om oversættertilladelse',
	'translate-fs-target-title' => 'Start med at oversætte!',
	'translate-fs-email-title' => 'Bekræft din e-mail-adresse',
	'translate-fs-intro' => "Velkommen til {{SITENAME}} kom godt i gang guide.
Du vil blive guidet igennem processen med til at blive en oversætter trin for trin.
I sidste ende vil du være i stand til at oversætte ''brugerflade beskeder'' hos alle støttede projekter på {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Vælg et sprog',
	'translate-fs-settings-planguage' => 'Primært sprog:',
	'translate-fs-settings-planguage-desc' => 'Det primære sprog er både sproget for din brugerflade på denne wiki og standardsproget for dine oversættelser.',
	'translate-fs-settings-slanguage' => 'Hjælpesprog $1:',
	'translate-fs-settings-slanguage-desc' => 'Under oversættelsesredigeringen, er det muligt at vise oversættelser af beskeder i andre sprog.
Her kan du vælge hvilke sprog, om nogen, du ønsker at se.',
	'translate-fs-settings-submit' => 'Gem indstillinger',
	'translate-fs-userpage-level-N' => 'Mit modersmål er',
	'translate-fs-userpage-level-5' => 'Jeg er en professionel oversætter af',
	'translate-fs-userpage-level-4' => 'Jeg er lige så god som en indfødt til',
	'translate-fs-userpage-level-3' => 'Jeg er god til',
	'translate-fs-userpage-level-2' => 'Jeg er nogenlunde god til',
	'translate-fs-userpage-level-1' => 'Jeg kan lidt',
	'translate-fs-userpage-help' => 'Vær så venlig at angive dine sprogfærdigheder og fortælle lidt om dig selv. Hvis du kan flere end fem sprog, kan du tilføje dem senere.',
	'translate-fs-userpage-submit' => 'Opret din brugerside',
	'translate-fs-userpage-done' => 'Godt gået! Du har nu en bruger side.',
	'translate-fs-permissions-planguage' => 'Primært sprog:',
	'translate-fs-permissions-help' => 'Nu skal du indsende en anmodning om at blive tilføjet til gruppen af oversættere.
Vælg det primære sprog, du ønsker at oversætte til.

Du kan nævne andre sprog eller tilføje andre bemærkninger i tekstfeltet nedenfor.',
	'translate-fs-permissions-pending' => 'Din anmodning er blevet sendt til [[$1]], og en af hjemmesidens ansatte vil tjekke den snarest muligt.
Hvis du bekræfter din email-adresse, vil du modtage en notits per email, så snart det sker.',
	'translate-fs-permissions-submit' => 'Send anmodning',
	'translate-fs-target-text' => 'Tillykke!
Du kan nu begynde at oversætte.

Vær ikke bange, hvis det stadig føles nyt og forvirrende for dig.
På [[Project list]] er der en oversigt over projekter, som du kan bidrage oversættelser til.
De fleste af projekterne har en kort beskrivelses side med et "\'\'Oversæt dette projekt\'\'" link, der vil tage dig til en side som indeholder alle uoversatte beskeder.
En liste over alle besked grupper med [[Special:LanguageStats|aktuelle oversættelse status for et sprog]] er også tilgængelig.

Hvis du føler at du har brug for at forstå mere, før du begynder at oversætte, kan du læse [[FAQ|Ofte stillede spørgsmål.]]
Desværre kan dokumentation være forældede tider.
Hvis der er noget, du tror, du bør være i stand til at gøre, men kan ikke finde ud af, hvordan, så tøv ikke med at spørge på [[Support|support-siden]].

Du kan også kontakte andre oversættere af samme sprog på [[Portal:$1|din sprog portal]]s [[Portal_talk:$1|diskussionsside]].
Hvis du ikke allerede har gjort det, [[Special:Preferences|ændrer dit brugergrænseflade sprog til det sprog, du ønsker at oversætte til]], således at wiki er i stand til at vise de mest relevante links til dig.',
	'translate-fs-email-text' => 'Angiv venligst din e-mail-adresse i [[Special:Preferences|dine indstillinger]] og bekræft den via den e-mail der sendes til dig.

Dette gør det muligt for andre brugere at kontakte dig via e-mail.
Du vil også modtage nyhedsbreve for det meste en gang om måneden.
Hvis du ikke ønsker at modtage nyhedsbreve, kan du framelde det i fanebladet "{{int:prefs-personal}}" i dine [[Special:Preferences|indstillinger]].',
);

/** German (Deutsch)
 * @author Als-Holder
 * @author Geitost
 * @author Kghbln
 * @author Metalhead64
 * @author Purodha
 * @author The Evil IP address
 */
$messages['de'] = array(
	'firststeps' => 'Erste Schritte',
	'firststeps-desc' => '[[Special:FirstSteps|Spezialseite]] zur Starterleichterung auf Wikis mit der „Translate“-Extension',
	'translate-fs-pagetitle-done' => ' – erledigt!',
	'translate-fs-pagetitle-pending' => '– in Arbeit',
	'translate-fs-pagetitle' => 'Startassistent - $1',
	'translate-fs-signup-title' => 'Registrierung durchführen',
	'translate-fs-settings-title' => 'Einstellungen anpassen',
	'translate-fs-userpage-title' => 'Benutzerseite erstellen',
	'translate-fs-permissions-title' => 'Übersetzerrechte beantragen',
	'translate-fs-target-title' => 'Übersetzen!',
	'translate-fs-email-title' => 'E-Mail-Adresse bestätigen',
	'translate-fs-intro' => "Willkommen beim translatewiki.net-Startassistenten.
Dir wird hier gezeigt, wie du Schritt für Schritt ein Übersetzer bei  translatewiki.net wirst.
Am Ende wirst du alle ''Nachrichten der Benutzeroberfläche'' der von translatewiki.net unterstützten Projekte übersetzen können.",
	'translate-fs-selectlanguage' => 'Wähle eine Sprache',
	'translate-fs-settings-planguage' => 'Hauptsprache:',
	'translate-fs-settings-planguage-desc' => 'Die Hauptsprache ist zum einen deine Sprache für die Benutzeroberfläche auf diesem Wiki
und zum anderen die Zielsprache für deine Übersetzungen.',
	'translate-fs-settings-slanguage' => 'Unterstützungssprache $1:',
	'translate-fs-settings-slanguage-desc' => 'Es ist möglich im Übersetzungseditor Übersetzungen von Nachrichten in anderen Sprachen anzeigen zu lassen.
Hier kannst du wählen, welche Sprachen du, wenn überhaupt, angezeigt bekommen möchtest.',
	'translate-fs-settings-submit' => 'Einstellungen speichern',
	'translate-fs-userpage-level-N' => 'Ich bin ein Muttersprachler',
	'translate-fs-userpage-level-5' => 'Ich bin ein professioneller Übersetzer',
	'translate-fs-userpage-level-4' => 'Ich habe die Kenntnisse eines Muttersprachlers',
	'translate-fs-userpage-level-3' => 'Ich habe gute Kenntnisse',
	'translate-fs-userpage-level-2' => 'Ich habe mittelmäßige Kenntnisse',
	'translate-fs-userpage-level-1' => 'Ich habe kaum Kenntnisse',
	'translate-fs-userpage-help' => 'Bitte gib deine Sprachkenntnisse an und erzähle etwas über dich. Sofern du Kenntnisse zu mehr als fünf Sprachen hast, kannst du diese später angeben.',
	'translate-fs-userpage-submit' => 'Erstelle deine Benutzerseite',
	'translate-fs-userpage-done' => 'Gut gemacht! Du hast nun eine Benutzerseite',
	'translate-fs-permissions-planguage' => 'Hauptsprache:',
	'translate-fs-permissions-help' => 'Jetzt musst du eine Anfrage stellen, um in die Benutzergruppe der Übersetzer aufgenommen werden zu können.
Wähle die Hauptsprache in die du übersetzen möchtest.

Du kannst andere Sprachen sowie weitere Hinweise im Textfeld unten angeben.',
	'translate-fs-permissions-pending' => 'Deine Anfrage wurde auf Seite [[$1]] gespeichert. Einer der Mitarbeiter von translatewiki.net wird sie sobald wie möglich prüfen.
Wenn du deine E-Mail-Adresse bestätigst, erhältst du eine E-Mail-Benachrichtigung, sobald dies erfolgt ist.',
	'translate-fs-permissions-submit' => 'Anfrage absenden',
	'translate-fs-target-text' => "Glückwunsch!
Du kannst nun mit dem Übersetzen beginnen.

Sei nicht verwirrt, wenn es dir noch neu und unübersichtlich vorkommt.
In der [[Project list|Projektliste]] gibt es eine Übersicht über die Projekte, die du übersetzen kannst.
Die meisten Projekte haben eine kurze Beschreibungsseite zusammen mit einem ''„Übersetzen“''-Link, der dich auf eine Seite mit nicht übersetzten Nachrichten bringt.
Eine Liste aller Nachrichtengruppen und dem [[Special:LanguageStats|momentanen Status einer Sprache]] gibt es auch.

Wenn du mehr hiervon verstehen möchtest, kannst du die [[FAQ|häufig gestellten Fragen]] lesen.
Leider kann die Dokumentation zeitweise veraltet sein.
Wenn du etwas tun möchtest, jedoch nicht weißt wie, zögere nicht, auf der [[Support|Hilfeseite]] zu fragen.

Du kannst auch Übersetzer deiner Sprache auf der [[Portal_talk:$1|Diskussionsseite]] des [[Portal:$1|entsprechenden Sprachportals]] kontaktieren.
Das Portal verlinkt auf deine momentane [[Special:Preferences|Spracheinstellung]].
Bitte ändere sie, falls nötig.",
	'translate-fs-email-text' => 'Bitte gib deine E-Mail-Adresse in [[Special:Preferences|deinen Einstellungen]] ein und bestätige die an dich versandte E-Mail.

Dies gibt anderen die Möglichkeit, dich über E-Mail zu erreichen.
Du erhältst außerdem bis zu einmal im Monat einen Newsletter.
Wenn du keinen Newsletter erhalten möchtest, kannst du dich im Tab „{{int:prefs-personal}}“ in deinen [[Special:Preferences|Einstellungen]] austragen.',
);

/** Swiss High German (Schweizer Hochdeutsch)
 * @author Geitost
 */
$messages['de-ch'] = array(
	'translate-fs-target-text' => "Glückwunsch!
Du kannst nun mit dem Übersetzen beginnen.

Sei nicht verwirrt, wenn es dir noch neu und unübersichtlich vorkommt.
In der [[Project list|Projektliste]] gibt es eine Übersicht über die Projekte, die du übersetzen kannst.
Die meisten Projekte haben eine kurze Beschreibungsseite zusammen mit einem ''«Übersetzen»''-Link, der dich auf eine Seite mit nicht übersetzten Nachrichten bringt.
Eine Liste aller Nachrichtengruppen und dem [[Special:LanguageStats|momentanen Status einer Sprache]] gibt es auch.

Wenn du mehr hiervon verstehen möchtest, kannst du die [[FAQ|häufig gestellten Fragen]] lesen.
Leider kann die Dokumentation zeitweise veraltet sein.
Wenn du etwas tun möchtest, jedoch nicht weisst wie, zögere nicht, auf der [[Support|Hilfeseite]] zu fragen.

Du kannst auch Übersetzer deiner Sprache auf der [[Portal_talk:$1|Diskussionsseite]] des [[Portal:$1|entsprechenden Sprachportals]] kontaktieren.
Das Portal verlinkt auf deine momentane [[Special:Preferences|Spracheinstellung]].
Bitte ändere sie, falls nötig.",
	'translate-fs-email-text' => 'Bitte gib deine E-Mail-Adresse in [[Special:Preferences|deinen Einstellungen]] ein und bestätige das an dich versandte E-Mail.

Dies gibt anderen die Möglichkeit, dich über E-Mail zu erreichen.
Du erhältst ausserdem bis zu einmal im Monat einen Newsletter.
Wenn du keinen Newsletter erhalten möchtest, kannst du dich im Tab «{{int:prefs-personal}}» in deinen [[Special:Preferences|Einstellungen]] austragen.',
);

/** German (formal address) (Deutsch (Sie-Form)‎)
 * @author Geitost
 * @author Kghbln
 * @author Purodha
 * @author The Evil IP address
 */
$messages['de-formal'] = array(
	'translate-fs-settings-title' => 'Ihre Einstellungen anpassen',
	'translate-fs-userpage-title' => 'Ihre Benutzerseite erstellen',
	'translate-fs-email-title' => 'Ihre E-Mail-Adresse bestätigen',
	'translate-fs-intro' => "Willkommen beim translatewiki.net-Startassistenten.
Ihnen wird hier gezeigt, wie Sie Schritt für Schritt ein Übersetzer bei translatewiki.net werden.
Am Ende werden Sie alle ''Nachrichten der Benutzeroberfläche'' der von translatewiki.net unterstützten Projekte übersetzen können.",
	'translate-fs-selectlanguage' => 'Wählen Sie eine Sprache',
	'translate-fs-settings-planguage-desc' => 'Die Hauptsprache ist zum einen Ihre Sprache für die Benutzeroberfläche auf diesem Wiki
und zum anderen die Zielsprache für Ihre Übersetzungen.',
	'translate-fs-settings-slanguage-desc' => 'Es ist möglich im Übersetzungseditor Übersetzungen von Nachrichten in anderen Sprachen anzeigen zu lassen.
Hier können Sie wählen, welche Sprachen Sie, wenn überhaupt, angezeigt bekommen möchten.',
	'translate-fs-userpage-help' => 'Bitte geben Sie Ihre Sprachkenntnisse an und teilen Sie uns etwas über sich mit. Sofern Sie Kenntnisse zu mehr als fünf Sprachen haben, können Sie diese später angeben.', # Fuzzy
	'translate-fs-userpage-done' => 'Gut gemacht! Sie haben nun eine Benutzerseite',
	'translate-fs-permissions-help' => 'Jetzt müssen Sie eine Anfrage stellen, um in die Benutzergruppe der Übersetzer aufgenommen werden zu können.
Wählen Sie die Hauptsprache in die Sie übersetzen möchten.

Sie können andere Sprachen sowie weitere Hinweise im Textfeld unten angeben.',
	'translate-fs-permissions-pending' => 'Ihre Anfrage wurde auf Seite [[$1]] gespeichert. Einer der Mitarbeiter von translatewiki.net wird sie sobald wie möglich prüfen.
Wenn Sie Ihre E-Mail-Adresse bestätigen, erhalten Sie eine E-Mail-Benachrichtigung, sobald dies erfolgt ist.',
	'translate-fs-target-text' => "Glückwunsch!
Sie können nun mit dem Übersetzen beginnen.

Seien Sie nicht verwirrt, wenn es Ihnen noch neu und unübersichtlich vorkommt.
In der [[Project list|Projektliste]] gibt es eine Übersicht über die Projekte, die Sie übersetzen können.
Die meisten Projekte haben eine kurze Beschreibungsseite zusammen mit einem ''„Übersetzen“''-Link, der Sie auf eine Seite mit nicht übersetzten Nachrichten bringt.
Eine Liste aller Nachrichtengruppen und dem [[Special:LanguageStats|momentanen Status einer Sprache]] gibt es auch.

Wenn Sie mehr hiervon verstehen möchten, können Sie die [[FAQ|häufig gestellten Fragen]] lesen.
Leider kann die Dokumentation zeitweise veraltet sein.
Wenn Sie etwas tun möchten, jedoch nicht wissen wie, zögern Sie nicht, auf der [[Support|Hilfeseite]] zu fragen.

Sie können auch Übersetzer Ihrer Sprache auf der [[Portal_talk:$1|Diskussionsseite]] des [[Portal:$1|entsprechenden Sprachportals]] kontaktieren.
Das Portal verlinkt auf Ihre momentane [[Special:Preferences|Spracheinstellung]].
Bitte ändern Sie sie, falls nötig.",
	'translate-fs-email-text' => 'Bitte geben Sie Ihre E-Mail-Adresse in [[Special:Preferences|Ihren Einstellungen]] ein und bestätigen Sie die an Sie versandte E-Mail.

Dies gibt anderen die Möglichkeit, Sie über E-Mail zu erreichen.
Sie erhalten außerdem bis zu einmal im Monat einen Newsletter.
Wenn Sie keinen Newsletter erhalten möchten, können Sie sich im Tab „{{int:prefs-personal}}“ in Ihren [[Special:Preferences|Einstellungen]] austragen.',
);

/** Zazaki (Zazaki)
 * @author Erdemaslancan
 * @author Mirzali
 */
$messages['diq'] = array(
	'firststeps' => 'Gamê sıfteyêni',
	'translate-fs-pagetitle-done' => '- tamam!',
	'translate-fs-signup-title' => 'Qeyd be',
	'translate-fs-settings-title' => 'Tercihanê cı saz ke',
	'translate-fs-userpage-title' => 'Pela karberiya cı vıraze',
	'translate-fs-target-title' => 'Açarnayışi rê serokne!',
	'translate-fs-selectlanguage' => 'Zıwan berzê cı',
	'translate-fs-settings-planguage' => 'Zıwanê dıyın:',
	'translate-fs-settings-slanguage' => 'Asistan zıwan $1:',
	'translate-fs-settings-submit' => 'Terciha qeyd ke',
	'translate-fs-userpage-submit' => 'Pela karberi vırazê', # Fuzzy
	'translate-fs-permissions-planguage' => 'Zıwana sıfteyên:',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'firststeps' => 'Prědne kšace',
	'firststeps-desc' => '[[Special:FirstSteps|Specialny bok]], aby  wólažcył wužywarjam wužywanje rozšyrjenja Translate',
	'translate-fs-pagetitle-done' => ' - wótbyte!',
	'translate-fs-pagetitle-pending' => '´- wobźěłujo se',
	'translate-fs-pagetitle' => 'Startowy asistent - $1',
	'translate-fs-signup-title' => 'Registrěrowaś',
	'translate-fs-settings-title' => 'Twóje nastajenja konfigurěrowaś',
	'translate-fs-userpage-title' => 'Twój wužywarski bok napóraś',
	'translate-fs-permissions-title' => 'Póžedanje na pśełožowarske pšawa stajiś',
	'translate-fs-target-title' => 'Zachop pśełožowaś!',
	'translate-fs-email-title' => 'Twóju e-mailowu adresu wobkšuśiś',
	'translate-fs-intro' => "Witaj do startowego asistenta {{GRAMMAR:genitiw|SITENAME}}.
Pokazujo so śi kšać pó kšać, kak buźoš pśełožowaŕ.
Na kóńcu móžoš ''powěźeńki wužywarskego powjercha'' wšyknych pódpěranych projektow na {{SITENAME}} pśełožowaś.",
	'translate-fs-selectlanguage' => 'Wubjeŕ rěc',
	'translate-fs-settings-planguage' => 'Głowna rěc:',
	'translate-fs-settings-planguage-desc' => 'Głowna rěc ma dwě funkciji: słužy ako rěc wužywarskego pówjercha w toś tom wikiju a ako standardna celowa rěc za pśełožki.',
	'translate-fs-settings-slanguage' => 'Pomocna rěc $1:',
	'translate-fs-settings-slanguage-desc' => 'Jo móžno pśełožki powěźeńkow w drugich rěcach w pśełožowańskem editorje pokazaś.
How móžoš wubraś, kótare rěcy coš rady wiźeś.',
	'translate-fs-settings-submit' => 'Nastajenja składowaś',
	'translate-fs-userpage-level-N' => 'Som maminorěcny',
	'translate-fs-userpage-level-5' => 'Som profesionelny pśełožowaŕ',
	'translate-fs-userpage-level-4' => 'Mam znajobnosći maminorěcnego',
	'translate-fs-userpage-level-3' => 'Mam dobre znajobnosći',
	'translate-fs-userpage-level-2' => 'Mam pśerězne znajobnosći',
	'translate-fs-userpage-level-1' => 'Mam jano mało znajobnosćow',
	'translate-fs-userpage-help' => 'Pšosym pódaj swóje rěcne znajobnosći a daj nam něco wó sebje k wěsći. Jolic maš znajobnosći we wěcej ako pěś rěcach, móžoš je pózdźej pódaś.', # Fuzzy
	'translate-fs-userpage-submit' => 'Twój wužywarski bok napóraś',
	'translate-fs-userpage-done' => 'Derje cynił! Maš něnto wužywarski bok.',
	'translate-fs-permissions-planguage' => 'Głowna rěc:',
	'translate-fs-permissions-help' => 'Musyš něnto napšašowanje stajiś, aby se do kupki pśełožowarjow pśiwzeł.
Wubjeŕ głownu rěc, do kótarejež coš pśełožowaś.

Móžoš druge rěcy a druge pśipomnjeśa w slědujucem tekstowem pólu pódaś.',
	'translate-fs-permissions-pending' => 'Twójo napšašowanje jo se do [[$1]] wótpósłało a něchten z teama translatewiki.net buźo jo tak skóro ako móžno pśeglědowaś. Jolic swóju e-mailowu adresu wobkšuśiš, dostanjoš e-mailowu powěźeńku, gaž jo se to stało.',
	'translate-fs-permissions-submit' => 'Napšašowanje pósłaś',
	'translate-fs-target-text' => 'Gratulacija!
Móžoš něnto pśełožowanje zachopiś.

Buź mimo starosći, jolic zda se śi hyšći nowe a konfuzne.
Na [[Project list|lisćinje projektow]] jo pśeglěd projektow, ku kótarymž móžoš pśełožki pśinosowaś. Nejwěcej projektow ma krotky wopisański bok z wótkazom "\'\'Toś ten projekt pśełožyś\'\'", kótaryž wjeźo śi k bokoju, kótaryž wšykne njepśełožone powěźeńki wopśimujo.
Lisćina wšyknych kupkow powěźeńkow z [[Special:LanguageStats|aktualnym pśełožowanskim stawom za rěc]] stoj teke k dispoziciji.

Jolic měniš, až dejš nejpjerwjej wěcej rozumiś, nježli až zachopijoš  pśełožowaś, móžoš [[FAQ|Ceste pšašanja]] cytaś.
Dokumentacija móžo bóžko wótergi zestarjona byś.
Joli něco jo, wó kótaremž mysliš, až by měło móžno byś, ale njenamakajoš, kak móžoš to cyniś, pšašaj se ga na boku [[Support|Pódpěra]].

Móžoš se teke ze sobupśełožowarjami teje sameje rěcy na [[Portal_talk:$1|diskusijnem boku]] [[Portal:$1|portala swójeje rěcy]] do zwiska stajiś.
Jolic hyšći njejsy to cynił, [[Special:Preferences|změń swój wužywarski powjerch do rěcy, do kótarejež coš pśełožowaś]], aby se wiki mógał wótkaze pokazaś, kótarež su relewantne za tebje.',
	'translate-fs-email-text' => 'Pšosym pódaj swóju e-mailowu adresu w [[Special:Preferences|swójich nastajenach]] a wobkšuś ju pśez e-mail, kótaraž sćelo se na tebje.

To dowólujo drugim wužywarjam se z tobu do zwiska stajiś.
Buźoš teke powěsćowe listy jaden raz na mjasec dostaś.
Jolic njocoš  powěsćowe listy dostaś, móžoš to na rejtarku "{{int:prefs-personal}}" swójich [[Special:Preferences|nastajenjow]] wótwóliś.',
);

/** Greek (Ελληνικά)
 * @author Protnet
 */
$messages['el'] = array(
	'firststeps' => 'Πρώτα βήματα',
	'translate-fs-signup-title' => 'Εγγραφή',
	'translate-fs-settings-title' => 'Ρύθμιση των προτιμήσεών σας',
	'translate-fs-userpage-title' => 'Δημιουργία της σελίδας χρήστη σας',
	'translate-fs-permissions-title' => 'Αίτηση δικαιωμάτων μεταφραστή',
	'translate-fs-target-title' => 'Ξεκινήσετε τη μετάφραση!',
	'translate-fs-email-title' => 'Επαληθεύστε τη διεύθυνση του ηλεκτρονικού σας ταχυδρομείου',
	'translate-fs-selectlanguage' => 'Επιλέξτε γλώσσα',
	'translate-fs-settings-planguage' => 'Κύρια γλώσσα:',
	'translate-fs-settings-slanguage' => 'Βοηθητική γλώσσα $1:',
	'translate-fs-settings-submit' => 'Αποθήκευση προτιμήσεων',
	'translate-fs-userpage-level-N' => 'Έχω σαν μητρική γλώσσα τα',
	'translate-fs-userpage-level-5' => 'Είμαι επαγγελματίας μεταφραστής στα',
	'translate-fs-userpage-level-4' => 'Γνωρίζω σαν μητρική γλώσσα τα',
	'translate-fs-userpage-level-3' => 'Έχω μεγάλη ευχέρεια στα',
	'translate-fs-userpage-level-2' => 'Έχω μια μέτρια ευχέρεια στα',
	'translate-fs-userpage-level-1' => 'Γνωρίζω λίγα',
	'translate-fs-userpage-submit' => 'Δημιουργία της σελίδας χρήστη σας',
	'translate-fs-userpage-done' => 'Πολύ καλά! Τώρα έχετε μια σελίδα χρήστη.',
	'translate-fs-permissions-planguage' => 'Κύρια γλώσσα:',
	'translate-fs-permissions-submit' => 'Αποστολή αίτησης',
);

/** Esperanto (Esperanto)
 * @author ArnoLagrange
 * @author Blahma
 * @author Yekrats
 */
$messages['eo'] = array(
	'firststeps' => 'Unuaj paŝoj',
	'firststeps-desc' => '[[Special:FirstSteps|Speciala paĝo]] por helpi novajn viki-uzantojn ekuzi la Traduk-etendaĵon',
	'translate-fs-pagetitle-done' => '- farita!',
	'translate-fs-pagetitle-pending' => '- pritraktota',
	'translate-fs-pagetitle' => 'Asistilo por ekuzado - $1',
	'translate-fs-signup-title' => 'Ensalutu',
	'translate-fs-settings-title' => 'Agordu viajn preferojn.',
	'translate-fs-userpage-title' => 'Kreu vian uzantopaĝon.',
	'translate-fs-permissions-title' => 'Petu rajtojn de tradukisto',
	'translate-fs-target-title' => 'Ek traduku!',
	'translate-fs-email-title' => 'Konfirmu vian retpoŝtan adreson',
	'translate-fs-intro' => "Bonvenon en la ekuz-asistilo de {{SITENAME}}.
Vi estos gvidata tra la proceso por fariĝi tradukisto pason post paŝo.
Fine vi kapablos traduki ''interfacajn mesaĝojn'' de ĉiuj eltenitaj projektoj je {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Elektu lingvon',
	'translate-fs-settings-planguage' => 'Ĉefa lingvo:',
	'translate-fs-settings-planguage-desc' => 'Tiu ĉi ĉefa lingvo samtempe uziĝas kiel la lingvo de via uzantointerfaco en tiu ĉi vikio kaj kiel defaŭlta cellingvo de viaj tradukoj.',
	'translate-fs-settings-slanguage' => 'Helpa lingvo $1:',
	'translate-fs-settings-slanguage-desc' => 'En la tradukilo eblas montri alilingvajn tradukojn de la mesaĝoj.
Se vi volas, ĉi tie vi povas elekti kiujn helpajn lingvojn vi ŝatus vidi.',
	'translate-fs-settings-submit' => 'Konservi preferojn',
	'translate-fs-userpage-level-N' => 'Mi estas denaska parolanto de',
	'translate-fs-userpage-level-5' => 'Mi estas profesia tradukisto de',
	'translate-fs-userpage-level-4' => 'Mi estas kvazaŭ-denaska parolanto de',
	'translate-fs-userpage-level-3' => 'Mi bone regas la lingvon',
	'translate-fs-userpage-level-2' => 'Mi sufiĉe regas la lingvon',
	'translate-fs-userpage-level-1' => 'Mi iom regas la lingvon',
	'translate-fs-userpage-help' => 'Bonvole indiku viajn linvosciojn kaj diru al ni ion pri vi mem. Se vi scias pli ol kvin lingvojn, vi povos aldoni pliajn poste.', # Fuzzy
	'translate-fs-userpage-submit' => 'Krei mian uzantopaĝon.', # Fuzzy
	'translate-fs-userpage-done' => 'Bone! Vi nun havas uzantopaĝon.',
	'translate-fs-permissions-planguage' => 'Ĉefa lingvo:',
	'translate-fs-permissions-help' => 'Nun vi devas peti ke oni aldonu vin en la grupon de tradukistoj.
Elektu la ĉefan lingvon en kiun vi tradukados.

Vi povas mencii aliajn lingvojn kaj aliajn rimarkojn en la suba tekstujo.',
	'translate-fs-permissions-pending' => 'Via peto aldoniĝis al [[$1]] kaj iu el la administrantoj de la retejo pritraktos ĝin laŭeble baldaŭ.
Se vi konfirmos vian retpoŝtadreson, vi ricevos poste sciigon per retpoŝto.',
	'translate-fs-permissions-submit' => 'Sendi la peton',
	'translate-fs-target-text' => "Gratulojn  !
Vi povas nun ektraduki.

Ne maltrankviliĝu se vi trovas tion iom nova kaj stranga.
Sur la [[Project list|projektolisto]] troviĝas superrigardo de la projektojn al kies traduko vi povas helpi.

Plej multaj el tiuj projektoj enhavas paĝon entenantan  mallongan priskribon kaj ligilon « ''Traduki ĉi tiun projekton'' » kiu gvidos vin al paĝo listiganta ĉiuj netradukitajn mesaĝojn. Havebla estas listo de ĉiuj mesaĝgrupoj kun la [[Special:LanguageStats|nuna tradukostato por difinita lingvo]].

Se vi sentas ke vi bezonas pli da informoj antaŭ ektraduki, vi povas legi al [[FAQ|Plej oftajn demandojn]]. Bedaŭrinde la dokumentado povas esti eksdata. Se vi opinias ke vi povus fari ion, ne trovante kiel fari, ne hezitu fari demandojn en la [[Support|helppaĝo]].

Vi ankaŭ povas kontakti la aliajn tradukantojn de la sama lingvo sur [[Portal_talk:$1|diskutpaĝo]] de [[Portal:$1|via propra lingvo]].
Se vi ne jam faris tion,  [[Special:Preferences|agordu la interfacan lingvon]] por ke ĝi estu tiu en kiun vi estas tradukonta. Tiel la ligiloj kiujn proponas la vikio estos plej adaptitaj al via situacio.",
	'translate-fs-email-text' => 'Bonvolu enigi vian retpoŝtadreson en  [[Special:Preferences|viaj preferoj]] kaj konfirmi ĝin per la mesaĝo kiun vi ricevos.

Tio ebligos al la aliaj uzantoj kontakti vin per retpoŝto.
Vi ankaŭ ricevos informleteron maksimume unu fojon en la monato.
Se vi ne deziras ricevi ĝin, vi povas malaktivigi en la langeto  « {{int:prefs-personal}} »  de  [[Special:Preferences|viaj preferoj]].',
);

/** Spanish (español)
 * @author Crazymadlover
 * @author Dferg
 * @author Diego Grez
 * @author Drini
 * @author Fitoschido
 * @author MarcoAurelio
 * @author Mor
 * @author Tempestas
 * @author Vivaelcelta
 */
$messages['es'] = array(
	'firststeps' => 'Primeros pasos',
	'firststeps-desc' => '[[Special:FirstSteps|Página especial]] para que los usuarios comiencen en un wiki usando la extensión de traducción',
	'translate-fs-pagetitle-done' => '- ¡hecho!',
	'translate-fs-pagetitle-pending' => '- pendiente',
	'translate-fs-pagetitle' => 'Guía de inicio - $1',
	'translate-fs-signup-title' => 'Registrarse',
	'translate-fs-settings-title' => 'Configurar tus preferencias',
	'translate-fs-userpage-title' => 'Crear tu página de usuario',
	'translate-fs-permissions-title' => 'Solicitar permisos de traducción',
	'translate-fs-target-title' => '¡Comienza a traducir!',
	'translate-fs-email-title' => 'Confirmar tu dirección de correo electrónico',
	'translate-fs-intro' => "Bienvenido al asistente de los primeros pasos en {{SITENAME}}.
Serás guíado a través del proceso de convertirte en un traductor pasa a paso.
Al final serás capaz de traducir los ''mensajes de interfaz'' de todos los proyectos soportados en {{SITENAME}}",
	'translate-fs-selectlanguage' => 'Elija un idioma',
	'translate-fs-settings-planguage' => 'Idioma principal:',
	'translate-fs-settings-planguage-desc' => 'El idioma principal es tanto el idioma de la interfaz en este wiki
y también el idioma en el que se van a realizar las traducciones.',
	'translate-fs-settings-slanguage' => 'Idioma soportado $1:',
	'translate-fs-settings-slanguage-desc' => 'Es posible mostrar las traducciones de los mensajes en otros idiomas en el editor de traducciones.
Aquí puede elegir, si quiere, los idiomas que le gustaría ver.',
	'translate-fs-settings-submit' => 'Guardar las preferencias',
	'translate-fs-userpage-level-N' => 'Soy hablante nativo de',
	'translate-fs-userpage-level-5' => 'Soy traductor profesional de',
	'translate-fs-userpage-level-4' => 'Lo conozco como un hablante nativo',
	'translate-fs-userpage-level-3' => 'Tengo un buen dominio de',
	'translate-fs-userpage-level-2' => 'Tengo un dominio con moderado de',
	'translate-fs-userpage-level-1' => 'Sé un poco de',
	'translate-fs-userpage-help' => 'Por favor indique sus competencias lingüísticas y coméntenos algo sobre usted. Si sabe más de cinco idiomas los puede añadir más adelante.', # Fuzzy
	'translate-fs-userpage-submit' => 'Crear mi página de usuario', # Fuzzy
	'translate-fs-userpage-done' => '¡Bien hecho! Ahora tienes una página de usuario.',
	'translate-fs-permissions-planguage' => 'Idioma principal:',
	'translate-fs-permissions-help' => 'Ahora tiene que hacer una solicitud para pasar a formar parte del grupo de traductores.
Seleccione el idioma principal en el que se va a traducir.

Puede mencionar otros idiomas y otras observaciones en el cuadro de texto inferior.',
	'translate-fs-permissions-pending' => 'Su solicitud ha sido enviada a "[[$1]]" y alguno de los miembros del personal del sitio atenderá tan pronto como sea posible.
Si confirmas tu dirección de correo electrónico, recibirá una notificación por correo electrónico tan pronto como ocurra.',
	'translate-fs-permissions-submit' => 'Enviar la solicitud',
	'translate-fs-target-text' => '¡Felicidades!
Ahora puedes comenzar a traducir.

No temas si lo sientes nuevo y confuso para ti.
En la [[Project list]] hay una visión general de los proyectos en los que puedes contribuir con traducciones.
La mayoría de los proyectos tiene una página de descripción corta con un enlace "\'\'Traducir este proyecto\'\'", que te llevará a una página que lista todos los mensajes sin traducir.
Una lista de todos los grupos de mensajes con el [[Special:LanguageStats|status de traducción actual para un idioma]] está también disponible.

Si sientes que necesitas entender más antes de empezar a traducir, puedes leer las [[FAQ|Preguntas frecuentes]].
Desafortunadamente la documentación puede estar desactualizada a veces.
Si hay algo que crees que deberías ser capaz de hacer, pero no sabes cómo, no dudes en preguntarlo en la [[Support|página de apoyo]].

También puedes contactar con otros traductores del mismo idioma en la [[Portal_talk:$1|página de discusión]] del [[Portal:$1|portal de tu idioma]].
El portal enlaza a tu [[Special:Preferences|preferencia de idioma]] actual.
Si todavía no lo has hecho, [[Special:Preferences|cambia el idioma de tu interfaz de usuario al idioma al que quieras traducir]], para que el wiki te pueda mostrar los enlaces más relevantes para ti.',
	'translate-fs-email-text' => 'Por favor proporciona tu dirección de correo electrónico en [[Special:Preferences|tus preferencias]] y confírmala desde el mensaje de correo que se te envíe.

Esto permite a los otros usuarios contactar contigo por correo electrónico.
También recibirás boletines de noticias como máximo una vez al mes.
Si no deseas recibir boletines de noticias, puedes cancelarlos en la pestaña "{{int:prefs-personal}}" de tus [[Special:Preferences|preferencias]].',
);

/** Estonian (eesti)
 * @author Pikne
 */
$messages['et'] = array(
	'firststeps' => 'Esimesed sammud',
	'firststeps-desc' => '[[Special:FirstSteps|Erilehekülg]], mis aitab tõlkimisga alustada',
	'translate-fs-pagetitle-done' => ' – valmis!',
	'translate-fs-pagetitle-pending' => ' – ootel',
	'translate-fs-pagetitle' => 'Alustusviisard – $1',
	'translate-fs-signup-title' => 'Registreerumine',
	'translate-fs-settings-title' => 'Eelistuste seadmine',
	'translate-fs-userpage-title' => 'Kasutajalehekülje loomine',
	'translate-fs-permissions-title' => 'Tõlkijaõiguste taotlemine',
	'translate-fs-target-title' => 'Alusta tõlkimist!',
	'translate-fs-email-title' => 'E-posti aadressi kinnitamine',
	'translate-fs-intro' => "Tere tulemast {{GRAMMAR:genitive|{{SITENAME}}}} alustusviisardisse.
Sul aidatakse sammhaaval tõlkijaks saada.
Lõpuks saad tõlkida kõikide {{GRAMMAR:genitive|{{SITENAME}}}} toetatud projektide ''liidese sõnumeid''.",
	'translate-fs-selectlanguage' => 'Vali keel',
	'translate-fs-settings-planguage' => 'Põhikeel:',
	'translate-fs-settings-planguage-desc' => 'Põhikeel kattub sinu siin vikis kasutatava liidesekeelega
ja keelega, millesse vaikimisi tõlgid.',
	'translate-fs-settings-slanguage' => '$1. abikeel:',
	'translate-fs-settings-slanguage-desc' => 'Tõlkeredaktoris saab näidata sõnumite teiskeelseid tõlkeid.
Siin saad valida, milliseid keeli soovid näha, kui soovid.',
	'translate-fs-settings-submit' => 'Salvesta eelistused',
	'translate-fs-userpage-level-N' => 'See on minu emakeel',
	'translate-fs-userpage-level-5' => 'Mul on selle keele tõlkija kutse',
	'translate-fs-userpage-level-4' => 'Räägin seda keelt emakeelelähedasel tasemel',
	'translate-fs-userpage-level-3' => 'Räägin seda keelt heal tasemel',
	'translate-fs-userpage-level-2' => 'Räägin seda keelt keskmisel tasemel',
	'translate-fs-userpage-level-1' => 'Räägin natuke seda keelt',
	'translate-fs-userpage-help' => 'Palun kirjelda oma keelteoskust ja räägi midagi endast. Kui oskad rohkem kui viit keelt, saad ülejäänud hiljem lisada.', # Fuzzy
	'translate-fs-userpage-submit' => 'Loo oma kasutajalehekülg',
	'translate-fs-userpage-done' => 'Hästi tehtud! Nüüd on sul kasutajalehekülg.',
	'translate-fs-permissions-planguage' => 'Põhikeel:',
	'translate-fs-permissions-help' => 'Nüüd pead esitama taotluse, et sind lisataks tõlkijate rühma.
Vali põhikeel, millesse tõlgid.

Allolevas tekstikastis saad mainida teisi keeli ja teha muid märkusi.',
	'translate-fs-permissions-pending' => 'Sinu taotlus on lisatud leheküljele "[[$1]]" ja keegi võrgukoha kooseisust vaatab selle esimesel võimalusel üle.
Kui kinnitad oma e-posti aadressi, saad e-kirja niipea, kui su taotlus on üle vaadatud.',
	'translate-fs-permissions-submit' => 'Saada taotlus',
);

/** Basque (euskara)
 * @author An13sa
 */
$messages['eu'] = array(
	'firststeps' => 'Lehen urratsak',
	'translate-fs-pagetitle-done' => ' - egina!',
	'translate-fs-pagetitle' => 'Martxan jarri - $1',
	'translate-fs-signup-title' => 'Kontua sortu',
	'translate-fs-settings-title' => 'Zure hobespenak konfiguratu',
	'translate-fs-userpage-title' => 'Zure lankide orria sortu',
	'translate-fs-permissions-title' => 'Itzultzaile eskubidea eskatu',
	'translate-fs-target-title' => 'Hasi itzultzen!',
	'translate-fs-selectlanguage' => 'Hizkuntza aukeratu',
	'translate-fs-settings-planguage' => 'Lehen hizkuntza:',
	'translate-fs-userpage-submit' => 'Nire lankide orria sortu', # Fuzzy
	'translate-fs-userpage-done' => 'Ondo egina! Orain lankide orrialdea duzu.',
	'translate-fs-permissions-submit' => 'Eskaera bidali',
);

/** Persian (فارسی)
 * @author Huji
 */
$messages['fa'] = array(
	'firststeps' => 'گام‌های نخست',
	'firststeps-desc' => '[[Special:FirstSteps|گام‌های نخست]] برای به راه افتادن کاربران در ویکی با استفاده از افزونه ترجمه',
	'translate-fs-pagetitle-done' => '- شد!',
	'translate-fs-pagetitle-pending' => '- در انتظار',
	'translate-fs-pagetitle' => 'جادوگر آغاز به کار - $1',
	'translate-fs-signup-title' => 'ثبت نام',
	'translate-fs-settings-title' => 'تنظیمات‌تان را پیکربندی کنید',
	'translate-fs-userpage-title' => 'صفحه کاربری‌تان را ایجاد کنید',
	'translate-fs-permissions-title' => 'درخواست مجوز مترجم بدهید',
	'translate-fs-target-title' => 'شروع به ترجمه کنید!',
	'translate-fs-email-title' => 'نشانی پست الکترونیکی خود را تأیید کنید',
	'translate-fs-intro' => "به جادوگر گام‌های نخست {{SITENAME}} خوش آمدید.
شما گام به گام در راه مترجم شدن راهنمایی خواهید شد.
در انتها شما قادر خواهید بود ''پیغام‌های رابط کاربری'' تمام پروژه‌های پشتیبانی شده در {{SITENAME}} را ترجمه کنید.",
	'translate-fs-selectlanguage' => 'یک زبان انتخاب کنید',
	'translate-fs-settings-planguage' => 'زبان اصلی:',
	'translate-fs-settings-planguage-desc' => 'زبان اصلی به عنوان زبان رابط کاربری این ویکی
و نیز به عنوان زبان هدف در ترجمه‌ها در نظر گرفته می شود.',
	'translate-fs-settings-slanguage' => 'زبان دستیار $1:',
	'translate-fs-settings-slanguage-desc' => 'ترجمه‌های پیغام‌ها در زبان‌های دیگر نیز می‌تواند در ویرایشگر ترجمه نمایش یابد.
در این‌جا شما می‌توانید انتخاب کنید چه زبانی را می‌خواهید ببینید.',
	'translate-fs-settings-submit' => 'ذخیره کردن ترجیحات',
	'translate-fs-userpage-level-N' => 'این زبان مادری من است',
	'translate-fs-userpage-level-5' => 'من مترجم حرفه‌ای این زبان هستم',
	'translate-fs-userpage-level-4' => 'این زبان را مانند زبان مادری بلدم',
	'translate-fs-userpage-level-3' => 'این زبان را خوب بلدم',
	'translate-fs-userpage-level-2' => 'این زبان را در حد متوسط بلدم',
	'translate-fs-userpage-level-1' => 'این زبان را کمی بلدم',
	'translate-fs-userpage-help' => 'لطفا مهارت‌های زبانی خود را مشخص کنید و کمی درباره خودتان به ما بگویید. اگر بیش از پنج زبان می‌دانید می‌توانید بقیه را بعداً اضافه کنید.', # Fuzzy
	'translate-fs-userpage-submit' => 'ایجاد صفحه کاربری', # Fuzzy
	'translate-fs-userpage-done' => 'آفرین! اکنون یک صفحه کاربری دارید.',
	'translate-fs-permissions-planguage' => 'زبان اصلی:',
	'translate-fs-permissions-help' => 'اکنون باید درخواست کنید تا به گروه مترجمان اضافه شوید.
زبان اصلی که قادرید به آن ترجمه کنید را انتخاب کنید.

شما می‌توانید زبان‌های دیگر و سایر توضیحات را در جعبه زیر وارد کنید.',
	'translate-fs-permissions-pending' => 'درخواست شما به  [[$1]] ارسال شد و یکی از کارکنان سایت در اولین فرصت آن را بررسی خواهد کرد.
اگر نشانی پست الکترونیکی خود را تأیید کنید، به محض این که این اتفاق بیفتد از طریق پست الکترونیکی با خبر خواهید شد.',
	'translate-fs-permissions-submit' => 'ارسال درخواست',
	'translate-fs-target-text' => "تبریک!
شما اینک می‌توانید شروع به ترجمه کنید.

از این که این که برایتان تازگی دارد و گیج شده‌اید نگران نباشید.
در [[Project list|فهرست پروژه‌ها]] چکیده‌ای از پروژه‌هایی که می‌توانید به ترجمه‌شان کمک کنید وجود دارد.
بیشتر پروژه‌ها یک صفحه توضیحات به همراه یک پیوند «''این پروژه را ترجمه کنید''» دارند که شما را به صفحه‌ای می‌برد که تمام پیغام‌های ترجمه نشده را فهرست می‌کند.
فهرستی از تمام گروه‌های پیغام‌ها به همراه [[Special:LanguageStats|وضعیت فعلی ترجمه آن‌ها به هر زبان]] نیز موجود است.

اگر فکر می‌کنید که قبل از شروع به ترجمه نیاز به دانستن چیزهای بیشتری دارید، می‌توانید [[FAQ|پرسش‌های متداول]] را مطالعه کنید.
متاسفانه مستندات گاهی قدیمی هستند.
اگر فکر می‌کنید کاری را باید بتوانید انجام بدهید، اما نمی‌دانید چگونه، بدون تردید در [[Support|صفحه پشتیبانی]] سوال کنید.

شما همچنین می‌توانید با دیگر مترجمان همزبان با خودتان از طریق [[Portal_talk:$1|صفحه بحث]] [[Portal:$1|ورودی زبان خودتان]] ارتباط برقرار کنید.
لطفا همین الان [[Special:Preferences|زبان رابطه کاربری را به زبانی که به آن ترجمه می‌کنید تغییر دهید]] تا ویکی پیوندها مرتبط را به شما نشان دهد.",
	'translate-fs-email-text' => 'لطفاً نشانی پست الکترونیکی خود را در [[Special:Preferences|تنظیامت خود]] مشخص کنید و از طریق نامه‌ای که به شما فرستاده می‌شود آن را تأیید کنید.

این کار باعث می‌شود دیگران بتوانند با شما از طریق پست الکترونیکی تماس بگیرند.
همچنین ماهی یک بار یک خبرنامه دریافت خواهید کرد.
اگر نمی‌خواهید خبرنامه دریافت کنید، می توانید در زبانه «{{int:prefs-personal}}» [[Special:Preferences|ترجیحات]] آن را غیر فعال کنید.',
);

/** Finnish (suomi)
 * @author Centerlink
 * @author Crt
 * @author Nike
 * @author Olli
 * @author ZeiP
 */
$messages['fi'] = array(
	'firststeps' => 'Alkutoimet',
	'firststeps-desc' => '[[Special:FirstSteps|Toimintosivu]], joka ohjastaa uudet käyttäjät Translate-laajennoksen käyttöön.',
	'translate-fs-pagetitle-done' => ' - valmis!',
	'translate-fs-pagetitle-pending' => ' - vireillä',
	'translate-fs-pagetitle' => 'Alkutoimet - $1',
	'translate-fs-signup-title' => 'Rekisteröityminen',
	'translate-fs-settings-title' => 'Asetusten määrittäminen',
	'translate-fs-userpage-title' => 'Käyttäjäsivun luominen',
	'translate-fs-permissions-title' => 'Pyyntö kääntäjäryhmään liittämisestä',
	'translate-fs-target-title' => 'Kääntäminen voi alkaa!',
	'translate-fs-email-title' => 'Sähköpostiosoitteen vahvistus',
	'translate-fs-intro' => "Tervetuloa {{GRAMMAR:genitive|{{SITENAME}}}} ohjattuihin ensiaskeleisiin.
Seuraamalla sivun ohjeita pääset kääntäjäksi alta aikayksikön.
Suoritettuasi kaikki askeleet, voit kääntää kaikkien {{GRAMMAR:inessive|{{SITENAME}}}} olevien projektien ''käyttöliittymäviestejä''.",
	'translate-fs-selectlanguage' => 'Valitse kieli',
	'translate-fs-settings-planguage' => 'Ensisijainen kieli',
	'translate-fs-settings-planguage-desc' => 'Ensisijainen kieli on sekä tämän wikin käyttöliittymäkieli että oletuskielesi käännöksille.',
	'translate-fs-settings-slanguage' => '$1. apukieli',
	'translate-fs-settings-slanguage-desc' => 'Tässä voit valita minkä muiden kielten käännöksiä haluat nähdä käännöstyökalussa.',
	'translate-fs-settings-submit' => 'Tallenna asetukset',
	'translate-fs-userpage-level-N' => 'Äidinkieli',
	'translate-fs-userpage-level-5' => 'Ammattimainen kääntäjä',
	'translate-fs-userpage-level-4' => 'Äidinkielisen veroinen',
	'translate-fs-userpage-level-3' => 'Hyvä taito',
	'translate-fs-userpage-level-2' => 'Keskinkertainen taito',
	'translate-fs-userpage-level-1' => 'Tiedän vähän',
	'translate-fs-userpage-help' => 'Kerro kielitaidostasi ja jotain itsestäsi. Jos osaat yli viittä kieltä, voit lisätä lisää myöhemmin.', # Fuzzy
	'translate-fs-userpage-submit' => 'Luo oma käyttäjäsivu',
	'translate-fs-userpage-done' => 'Hyvin tehty! Sinulla on nyt käyttäjäsivu.',
	'translate-fs-permissions-planguage' => 'Ensisijainen kieli',
	'translate-fs-permissions-help' => 'Nyt sinun pitää esittää pyyntö kääntäjäryhmään lisäämisestä.
Valitse ensisijainen kieli, jolle aiot kääntää.

Voit mainita muita kieliä ja kirjoittaa muita huomautuksia alla olevaan kenttään.',
	'translate-fs-permissions-pending' => 'Pyyntösi on lisätty sivulle [[$1]] ja joku sivuston henkilökunnasta tarkistaa sen niin pian kuin mahdollista.
Jos vahvistat sähköpostiosoitteesi, saat huomautuksen sähköpostin kautta heti, kun se tapahtuu.',
	'translate-fs-permissions-submit' => 'Lähetä pyyntö',
	'translate-fs-target-text' => "Onnittelut!
Voit nyt aloittaa kääntämisen.

Älä huolestu, vaikka et vielä täysin ymmärtäisi miten kaikki toimii.
Meillä on [[Project list|luettelo projekteista]], joiden kääntämiseen voit osallistua.
Useimmilla projekteilla on lyhyt kuvaussivu, jossa on ''Käännä tämä projekti'' -linkki varsinaiselle käännössivulle.
[[Special:LanguageStats|Kielen nykyisen käännöstilanteen]] näyttävä lista on myös saatavilla.

Jos haluat tietää lisää, voit lukea vaikkapa [[FAQ|usein kysyttyjä kysymyksiä]].
Valitettavasti dokumentaatio voi joskus olla hivenen vanhentunutta.
Jos et keksi, miten joku tarvitsemasi asia tehdään, älä epäröi pyytää apua [[Support|tukisivulla]].

Voit myös ottaa yhteyttä muihin saman kielen kääntäjiin [[Portal:$1|oman kielesi portaalin]] [[Portal_talk:$1|keskustelusivulla]].
Valikon portaalilinkki osoittaa [[Special:Preferences|valitsemasi kielen]] portaaliin.
Jos valitsemasi kieli on väärä, muuta se.",
	'translate-fs-email-text' => 'Anna sähköpostiosoitteesi [[Special:Preferences|asetuksissasi]] ja vahvista se sähköpostiviestistä, joka lähetetään sinulle.

Tämä mahdollistaa muiden käyttäjien ottaa sinuun yhteyttä sähköpostitse.
Saat myös uutiskirjeen korkeintaan kerran kuukaudessa.
Jos et halua vastaanottaa uutiskirjeitä, voit muuttaa asetusta välilehdellä »{{int:prefs-personal}}» omat [[Special:Preferences|asetukset]].',
);

/** Faroese (føroyskt)
 * @author EileenSanda
 */
$messages['fo'] = array(
	'firststeps' => 'Fyrstu stigini',
	'firststeps-desc' => '[[Special:FirstSteps|Serstøk síða]] fyri at fáa brúkarar í gongd á einari wiki við hjálp frá Translate víðkanini',
	'translate-fs-pagetitle-done' => ' - liðugt!',
	'translate-fs-pagetitle-pending' => ' - bíðar',
	'translate-fs-pagetitle' => 'Leiðbeining fyri at koma í gongd - $1',
	'translate-fs-signup-title' => 'Upprætta eina konto',
	'translate-fs-settings-title' => 'Samanset tínar innstillingar',
	'translate-fs-userpage-title' => 'Upprætta tína brúkarasíðu',
	'translate-fs-permissions-title' => 'Bið um loyvi til at týða',
	'translate-fs-target-title' => 'Byrja at týða!',
	'translate-fs-email-title' => 'Vátta tína t-post adressu',
	'translate-fs-intro' => "Vælkomin til leiðbeiningina Fyrstu stigini hjá {{SITENAME}}.
Tú fært eina stig fyri stig vegleiðing gjøgnum alla mannagongdina at gerast ein týðari.
At enda verður tú før/ur fyri at týða ''markamót boð'' frá øllum stuðlaðum verkætlanum á {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Vel eitt mál',
	'translate-fs-settings-planguage' => 'Høvuðmál:',
	'translate-fs-settings-submit' => 'Goym innstillingar',
	'translate-fs-userpage-level-N' => 'Mítt móðurmál er',
	'translate-fs-userpage-level-4' => 'Eg dugi tað eins væl og móðurmálstalarar',
	'translate-fs-userpage-level-3' => 'Eg havi góðan kunnleika til',
	'translate-fs-userpage-level-2' => 'Eg dugi hampuliga væl',
	'translate-fs-userpage-level-1' => 'Eg dugi eitt sindur',
	'translate-fs-userpage-submit' => 'Upprætta tína brúkarasíðu',
	'translate-fs-userpage-done' => 'Gott klárað! Tú hevur nú eina brúkarasíðu.',
	'translate-fs-permissions-planguage' => 'Høvuðmál:',
);

/** French (français)
 * @author Gomoko
 * @author Hashar
 * @author Peter17
 */
$messages['fr'] = array(
	'firststeps' => 'Premiers pas',
	'firststeps-desc' => '[[Special:FirstSteps|Page spéciale]] pour guider les utilisateurs sur un wiki utilisant l’extension Translate',
	'translate-fs-pagetitle-done' => ' - fait !',
	'translate-fs-pagetitle-pending' => '- en cours',
	'translate-fs-pagetitle' => 'Guide de démarrage - $1',
	'translate-fs-signup-title' => 'Inscrivez-vous',
	'translate-fs-settings-title' => 'Configurez vos préférences',
	'translate-fs-userpage-title' => 'Créez votre page utilisateur',
	'translate-fs-permissions-title' => 'Demandez les permissions de traducteur',
	'translate-fs-target-title' => 'Commencez à traduire !',
	'translate-fs-email-title' => 'Confirmez votre adresse électronique',
	'translate-fs-intro' => "Bienvenue sur l’assistant premiers pas de {{SITENAME}}.
Nous allons vous guider étape par étape pour devenir un traducteur.
À la fin du processus, vous pourrez traduire les ''messages des interfaces'' de tous les projets gérés par {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Choisissez une langue',
	'translate-fs-settings-planguage' => 'Langue principale:',
	'translate-fs-settings-planguage-desc' => 'La langue principale sert aussi comme la langue de votre interface sur ce wiki
et comme la langue cible par  défaut pour les traductions.',
	'translate-fs-settings-slanguage' => "Langue d'assistance $1:",
	'translate-fs-settings-slanguage-desc' => "Il est possible d'afficher des traductions de message dans d'autres langues dans l'éditeur de traduction.
Ici, vous pouvez choisir quelles langues, si c'est le cas, vous aimeriez voir.",
	'translate-fs-settings-submit' => 'Enregistrer les préférences',
	'translate-fs-userpage-level-N' => 'Je suis un locuteur natif de',
	'translate-fs-userpage-level-5' => 'Je suis un traducteur professionnel de',
	'translate-fs-userpage-level-4' => 'Je la connais comme un locuteur natif',
	'translate-fs-userpage-level-3' => "J'ai une bonne maîtrise de",
	'translate-fs-userpage-level-2' => "J'ai une maîtrise modérée de",
	'translate-fs-userpage-level-1' => 'Je connais un peu',
	'translate-fs-userpage-help' => 'Veuillez indiquer vos compétences linguistiques et parler un peu de vous-même. Si vous connaissez plus de cinq langues, vous pourrez en ajouter plus tard.',
	'translate-fs-userpage-submit' => 'Créer votre page utilisateur',
	'translate-fs-userpage-done' => 'Bien joué ! Vous avez à présent une page utilisateur.',
	'translate-fs-permissions-planguage' => 'Langue principale:',
	'translate-fs-permissions-help' => "Maintenant, vous devez faire une demande pour être ajouté au groupe des traducteurs.
Sélectionnez la langue principale dans laquelle vous allez traduire.

Vous pouvez mentionner d'autres langues et d'autres remarques dans la zone de texte ci-dessous.",
	'translate-fs-permissions-pending' => "Votre demande a été transmise à [[$1]] et quelqu'un de l'équipe du site la vérifiera dès que possible.
Si vous confirmez votre adresse électronique, vous recevrez une notification par courriel dès que ce sera le cas.",
	'translate-fs-permissions-submit' => 'Envoyer la demande',
	'translate-fs-target-text' => "Félicitations !
Vous pouvez maintenant commencer à traduire.

Ne vous inquiétez pas si cela vous paraît un peu nouveau et étrange.
Sur la [[Project list|liste des projets]] se trouve une vue d’ensemble des projets que vous pouvez contribuer à traduire.
Ces projets possèdent, pour la plupart, une page contenant une courte description et un lien « ''Traduire ce projet'' » qui vous mènera vers une page listant tous les messages non traduits.
Une liste de tous les groupes de messages avec l’[[Special:LanguageStats|état actuel de la traduction pour une langue donnée]] est aussi disponible.

Si vous sentez que vous avez besoin de plus d’informations avant de commencer à traduire, vous pouvez lire la [[FAQ|foire aux questions]].
La documentation peut malheureusement être périmée de temps à autres.
Si vous pensez que vous devriez pouvoir faire quelque chose, sans parvenir à trouver comment, n’hésitez pas à poser la question sur la [[Support|page support]].

Vous pouvez aussi contacter les autres traducteurs de la même langue sur [[Portal_talk:$1|la page de discussion]] du [[Portal:$1|portail de votre langue]].
Si vous ne l’avez pas encore fait, [[Special:Preferences|ajustez la langue de l’interface pour qu’elle soit celle dans laquelle vous voulez traduire]]. Ainsi, les liens que vous propose le wiki seront les plus adaptés à votre situation.",
	'translate-fs-email-text' => 'Merci de bien vouloir saisir votre adresse électronique dans [[Special:Preferences|vos préférences]] et la confirmer grâce au message qui vous sera envoyé.

Cela permettra aux autres utilisateurs de vous contacter par courrier électronique.
Vous recevrez aussi un courrier d’informations au plus une fois par mois.
Si vous ne souhaitez pas recevoir ce courrier d’informations, vous pouvez le désactiver dans l’onglet « {{int:prefs-personal}} » de vos [[Special:Preferences|préférences]].',
);

/** Franco-Provençal (arpetan)
 * @author ChrisPtDe
 */
$messages['frp'] = array(
	'firststeps' => 'Premiérs pâs',
	'firststeps-desc' => '[[Special:FirstSteps|Pâge spèciâla]] por guidar los utilisators sur un vouiqui qu’empleye l’èxtension « Translate »',
	'translate-fs-pagetitle-done' => ' - fêt !',
	'translate-fs-pagetitle-pending' => ' - en cors',
	'translate-fs-pagetitle' => 'Guido d’emmodâ - $1',
	'translate-fs-signup-title' => 'Enscrîde-vos',
	'translate-fs-settings-title' => 'Configurâd voutres prèferences',
	'translate-fs-userpage-title' => 'Féte voutra pâge utilisator',
	'translate-fs-permissions-title' => 'Demandâd les pèrmissions de traductor',
	'translate-fs-target-title' => 'Betâd-vos a traduire !',
	'translate-fs-email-title' => 'Confirmâd voutron adrèce èlèctronica',
	'translate-fs-intro' => "Benvegnua sur l’assistent des premiérs pâs de {{SITENAME}}.
Nos vos volens guidar a châ ètapa por sè fâre traductor.
A la fin vos porréd traduire los ''mèssâjos de l’entèrface'' de tôs los projèts recognus per {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Chouèsésséd na lengoua',
	'translate-fs-settings-planguage' => 'Lengoua principâla :',
	'translate-fs-settings-planguage-desc' => 'La lengoua principâla sèrvét avouéc coment la lengoua de voutron entèrface sur ceti vouiqui
et pués coment la lengoua ciba per dèfôt por les traduccions.',
	'translate-fs-settings-slanguage' => 'Lengoua d’assistance $1 :',
	'translate-fs-settings-slanguage-desc' => 'O est possiblo de fâre vêre des traduccions de mèssâjos dens d’ôtres lengoues dedens lo changior de traduccion.
Ique vos pouede chouèsir quintes lengoues, s’o est lo câs, vos ameriâd vêre.',
	'translate-fs-settings-submit' => 'Encartar les prèferences',
	'translate-fs-userpage-level-N' => 'Su un parlant natif de',
	'translate-fs-userpage-level-5' => 'Su un traductor profèssionèl de',
	'translate-fs-userpage-level-4' => 'La cognesso coment un parlant natif',
	'translate-fs-userpage-level-3' => 'J’é na bôna mêtrise de',
	'translate-fs-userpage-level-2' => 'J’é na mêtrise moderâye de',
	'translate-fs-userpage-level-1' => 'Cognesso un pou',
	'translate-fs-userpage-help' => 'Volyéd endicar voutres capacitâts lengouistiques et pués nos prègiér un pou de vos-mémo. Se vos cognesséd més de cinq lengoues, vos en porréd apondre ples târd.', # Fuzzy
	'translate-fs-userpage-submit' => 'Fâre ma pâge utilisator', # Fuzzy
	'translate-fs-userpage-done' => 'Bien fêt ! Ora vos avéd na pâge utilisator.',
	'translate-fs-permissions-planguage' => 'Lengoua principâla :',
	'translate-fs-permissions-help' => 'Ora vos dête fâre na demanda por étre apondu a la tropa des traductors.
Chouèsésséd la lengoua principâla que vos voléd traduire.

Vos pouede mencionar d’ôtres lengoues et d’ôtres remârques dedens la zona de changement ce-desot.',
	'translate-fs-permissions-pending' => 'Voutra demanda est étâye mandâye a [[$1]] et pués yon du mondo du seto la controlerat setout que possiblo.
Se vos confirmâd voutron adrèce èlèctronica, vos recevréd na notificacion per mèssageria èlèctronica setout que serat lo câs.',
	'translate-fs-permissions-submit' => 'Mandar la demanda',
	'translate-fs-target-text' => "Fèlicitacions !
Ora vos vos pouede betar a traduire.

Vos enquiètâd pas se cen vos parêt un pou novél et ètranjo.
Sur la [[Project list|lista des projèts]] sè trove un apèrçu des projèts que vos pouede contribuar a traduire.
Celos projèts ont, por la plepârt, na pâge que contint na côrta dèscripcion et un lim « ''Traduire ceti projèt'' » que vos menerat vers na pâge que liste tôs los mèssâjos pas traduits.
Na lista de tôs los groupos de mèssâjos avouéc l’[[Special:LanguageStats|ètat d’ora de la traduccion por na lengoua balyêye]] est asse-ben disponibla.

Se vos sentéd que vos avéd fôta de més d’enformacions devant que vos betar a traduire, vos pouede liére les [[FAQ|quèstions sovent posâyes]].
Mâlherosament la documentacion pôt étre dèpassâye de temps en temps.
Se vos pensâd que vos devriâd povêr fâre quârque-ren, sen arrevar a trovar coment, hèsitâd pas a posar la quèstion sur la [[Support|pâge d’assistance]].

Vos vos pouede asse-ben veriér vers los ôtros traductors de la méma lengoua sur la [[Portal_talk:$1|pâge de discussion]] du [[Portal:$1|portâl de voutra lengoua]].
Se vos l’éd p’oncor fêt, [[Special:Preferences|changiéd la lengoua de l’entèrface por que seye cela que vos voléd traduire]]. D’ense, los lims que vos propôse lo vouiqui seront los ples adaptâs a voutra situacion.",
);

/** Friulian (furlan)
 * @author Klenje
 */
$messages['fur'] = array(
	'firststeps' => 'Prins pas',
	'translate-fs-pagetitle-done' => '- fat!',
	'translate-fs-signup-title' => 'Regjistriti',
	'translate-fs-settings-title' => 'Configure lis tôs preferencis',
	'translate-fs-userpage-title' => 'Cree la tô pagjine utent',
	'translate-fs-target-title' => 'Scomence a tradusi!',
	'translate-fs-email-title' => 'Conferme la tô direzion email',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'firststeps' => 'Primeiros pasos',
	'firststeps-desc' => '[[Special:FirstSteps|Páxina especial]] para iniciar aos usuarios no uso da extensión Translate',
	'translate-fs-pagetitle-done' => '; feito!',
	'translate-fs-pagetitle-pending' => '; pendente',
	'translate-fs-pagetitle' => 'Asistente para dar os primeiros pasos: $1',
	'translate-fs-signup-title' => 'Rexístrese',
	'translate-fs-settings-title' => 'Configure as súas preferencias',
	'translate-fs-userpage-title' => 'Cree a súa páxina de usuario',
	'translate-fs-permissions-title' => 'Solicite permisos de tradutor',
	'translate-fs-target-title' => 'Comece a traducir!',
	'translate-fs-email-title' => 'Confirme o seu enderezo de correo electrónico',
	'translate-fs-intro' => "Benvido ao asistente para dar os primeiros pasos en {{SITENAME}}.
Esta guía axudaralle, paso a paso, a través do proceso para se converter nun tradutor.
Cando remate, poderá traducir as ''mensaxes da interface'' de todos os proxectos soportados por {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Escolla unha lingua',
	'translate-fs-settings-planguage' => 'Lingua principal:',
	'translate-fs-settings-planguage-desc' => 'A lingua principal é tanto a lingua da interface neste wiki
como a lingua na que vai realizar as traducións.',
	'translate-fs-settings-slanguage' => 'Lingua axudante $1:',
	'translate-fs-settings-slanguage-desc' => 'É posible mostrar as traducións das mensaxes noutras linguas no editor de traducións.
Aquí pode elixir, se quere, as linguas que queira ver.',
	'translate-fs-settings-submit' => 'Gardar as preferencias',
	'translate-fs-userpage-level-N' => 'Son falante nativo de',
	'translate-fs-userpage-level-5' => 'Son tradutor profesional de',
	'translate-fs-userpage-level-4' => 'Coñézoa como un falante nativo',
	'translate-fs-userpage-level-3' => 'Teño un bo dominio de',
	'translate-fs-userpage-level-2' => 'Teño un dominio moderado de',
	'translate-fs-userpage-level-1' => 'Sei un pouco de',
	'translate-fs-userpage-help' => 'Indique as súas competencias lingüísticas e conte algo sobre vostede. Se sabe máis de cinco linguas, pódeas engadir máis adiante.',
	'translate-fs-userpage-submit' => 'Crear a súa páxina de usuario',
	'translate-fs-userpage-done' => 'Ben feito! Agora xa ten unha páxina de usuario.',
	'translate-fs-permissions-planguage' => 'Lingua principal:',
	'translate-fs-permissions-help' => 'Agora ten que facer unha petición para pasar a formar parte do grupo de tradutores.
Seleccione a lingua principal na que vai traducir.

Pode mencionar outras linguas ou observacións na caixa de texto inferior.',
	'translate-fs-permissions-pending' => 'A súa solicitude enviouse a "[[$1]]" e algún dos membros do persoal do sitio atenderá a petición axiña.
Se confirma o seu enderezo de correo electrónico recibirá unha notificación en canto ocorra.',
	'translate-fs-permissions-submit' => 'Enviar a solicitude',
	'translate-fs-target-text' => 'Parabéns!
Agora xa pode comezar a traducir.

Non teña medo se aínda se sente novo e confuso.
En [[Project list]] hai unha visión xeral dos proxectos nos que pode contribuír coas súas traducións.
A maioría dos proxectos teñen unha páxina cunha breve descrición e mais unha ligazón que di "\'\'Traducir este proxecto\'\'", que o levará a unha páxina que lista todas as mensaxes non traducidas.
Tamén hai dispoñible unha lista con todos os grupos de mensaxes co seu [[Special:LanguageStats|estado actual da tradución nunha lingua]].

Se pensa que necesita aprender máis antes de comezar a traducir, pode ler as [[FAQ|preguntas máis frecuentes]].
Por desgraza, a documentación pode estar desactualizada ás veces.
Se cre que hai algo que debe ser capaz de facer, pero non sabe como, non dubide en pedir [[Support|axuda]].

Tamén pode poñerse en contacto cos demais tradutores da mesma lingua na [[Portal_talk:$1|páxina de conversa]] do [[Portal:$1|portal da súa lingua]].
Se aínda non o fixo, [[Special:Preferences|cambie a lingua da interface de usuario elixindo aquela na que vai traducir]]; deste xeito, o wiki pode mostrar as ligazóns máis relevantes e que lle poidan interesar.',
	'translate-fs-email-text' => 'Proporcione o seu enderezo de correo electrónico [[Special:Preferences|nas súas preferencias]] e confírmeo mediante a mensaxe que chegará á súa bandexa de entrada.

Isto permite que outros usuarios se poñan en contacto con vostede por correo electrónico.
Tamén recibirá boletíns informativos, como máximo unha vez ao mes.
Se non quere recibir estes boletíns, pode cancelar a subscrición na lapela "{{int:prefs-personal}}" das súas [[Special:Preferences|preferencias]].',
);

/** Swiss German (Alemannisch)
 * @author Als-Holder
 */
$messages['gsw'] = array(
	'firststeps' => 'Erschti Schritt',
	'firststeps-desc' => '[[Special:FirstSteps|Spezialsyte]] as Hilf fir neji Benutzer zum Aafange uf eme Wiki mit dr „Translate“-Erwyterig',
	'translate-fs-pagetitle-done' => '- erledigt!',
	'translate-fs-pagetitle-pending' => '– hängig',
	'translate-fs-pagetitle' => 'Hilfsprogramm zum Aafang - $1',
	'translate-fs-signup-title' => 'Regischtriere',
	'translate-fs-settings-title' => 'Dyy Yystellige aapasse',
	'translate-fs-userpage-title' => 'Dyy Benutzersyte aalege',
	'translate-fs-permissions-title' => 'E Aatrag stelle uf s Ibersetzerrächt',
	'translate-fs-target-title' => 'Aafange mit em Ibersetze!',
	'translate-fs-email-title' => 'Dyy E-Mail-Adräss bstetige',
	'translate-fs-intro' => "Willchuu bi dr {{SITENAME}}-Hilf zue dr erschte Schritt.
Dir wird zeigt, wie Du Schritt fir Schritt e Ibersetzer wirsch.
Am Änd wirsch alli ''Oberfleche-Nochrichte'' vu dr Projäkt, wu vu {{SITENAME}} unterstitzt wäre, chenne ibersetze.",
	'translate-fs-selectlanguage' => 'Wehl e Sproch',
	'translate-fs-settings-planguage' => 'Hauptsproch:',
	'translate-fs-settings-planguage-desc' => 'D Hauptsproch isch zum eine Dyy Sproch fir d Benutzeroberflechi uf däm Wiki
un zum andere d Ziilsproch fir Dyni Ibersetzige.',
	'translate-fs-settings-slanguage' => 'Unterstitzigssproch $1:',
	'translate-fs-settings-slanguage-desc' => 'Du chasch Dir us em Ibersetzigsspycher Ibersetzige vu Nochrichten in andere Sprochen aazeige loo.
Do chasch wehle, weli Sproche Du, wänn iberhaupt, witt aazeigt kriege.',
	'translate-fs-settings-submit' => 'Yystellige spychere',
	'translate-fs-userpage-level-N' => 'Ich bii ne Muetersprochler',
	'translate-fs-userpage-level-5' => 'Ich bii ne professionälle Ibersetzer',
	'translate-fs-userpage-level-4' => 'Ich cha s eso guet wie ne Muetersprochler',
	'translate-fs-userpage-level-3' => 'Ich cha die Sproch guet',
	'translate-fs-userpage-level-2' => 'Ich cha die Sproch mittelmäßig',
	'translate-fs-userpage-level-1' => 'Ich cha die Sproch e bitzli',
	'translate-fs-userpage-help' => 'Bitte gib Dyni Sprochchänntnis aa un schryb ebis iber Dii. Wänn Du zue meh wie fimf Sproche Chänntnis hesch, no chasch des speter aagee.', # Fuzzy
	'translate-fs-userpage-submit' => 'Myy Benutzersyte aalege', # Fuzzy
	'translate-fs-userpage-done' => 'Guet gmacht! Du hesch jetz e Benutzersyte',
	'translate-fs-permissions-planguage' => 'Hauptsproch:',
	'translate-fs-permissions-help' => 'Jetz muesch e Aafrog stelle, ass Du in d Benutzergruppe vu dr Ibersetzer chasch ufgnuu wäre.
Wehl d Hauptsproch, wu du dryy ibersetze wettsch.

Du chasch anderi Sproche un wyteri Hiiwys im Textfäld unten aagee.',
	'translate-fs-permissions-pending' => 'Dyy Aafrog isch uf Syte [[$1]] gspycheret wore. Ein vu dr Mitarbeiter vu translatewiki.net wird si so schnäll wie megli priefe.
Wänn Du dyni E-Mail-Adräss bstetigsch, chunnsch e E-Mail-Benochrichtigung iber, wänn des erfolgt isch.',
	'translate-fs-permissions-submit' => 'Aafrog abschicke',
	'translate-fs-target-text' => "Glickwunsch!
Du chasch jetz aafange mit Ibersetze.

Bi nit verwirrt, wänn s dir no nej un unibersichtli vorchunnt.
Uf dr Syte [[Project list|Projäkt]] git s e Ibersicht vu dr Projäkt, wu Du chasch ibersetze.
Di meischte Projäkt hän e churzi Bschryybigssyte zämme mit eme „''Ibersetze''“- Link, wu di uf e Syte mit Nochrichte bringt, wu nonig ibersetzt sin.
E Lischt vu allne Nochrichtegruppe un em [[Special:LanguageStats|momentane Status vun ere Sproch]] git s au.

Wänn Du meh dodervu witt verstoh, chasch di [[FAQ|hyfig gstellte Froge]] läse.
Leider cha d Dokumäntation zytwyys veraltet syy.
Wänn Du ebis witt mache, weisch aber nit wie, no frog no uf dr [[Support|Hilfssyte]].

Du chasch au Ibersetzer vu Dyyre Sproch uf dr [[Portal_talk:$1|Diskussionssyte]] [[Portal:$1|vum Sprochportal]] kontaktiere.
S Portal verlinkt uf Dyyni derzytig [[Special:Preferences|Sprochyystellig]].
Bitte tue si ändere wänn netig.",
	'translate-fs-email-text' => 'Bitte gib Dyy E-Mail-Adräss yy in [[Special:Preferences|Dyyne Yystellige]] un tue d E-Mail, wu an di gschickt wird, bstetige.

Des git andere d Megligkeit, di iber E-Mail z erreiche.
Du chunnsch derno derzue eimol im Monet e Newsletter iber.
Wänn Du kei Newsletter witt iberchuu, chasch di im Tab „{{int:prefs-personal}}“ in [[Special:Preferences|Dyyne Yystellige]] uustrage.',
);

/** Hakka (Hak-kâ-fa)
 * @author Anson2812
 * @author Jetlag
 */
$messages['hak'] = array(
	'firststeps' => '第一步',
	'firststeps-desc' => '讓用戶開始維基翻譯嘅[[Special:FirstSteps|引導頁面]]',
	'translate-fs-pagetitle-done' => '搞掂！',
	'translate-fs-pagetitle-pending' => ' - 待定',
	'translate-fs-pagetitle' => '入門指導 - $1',
	'translate-fs-signup-title' => '註冊',
	'translate-fs-settings-title' => '設定汝嘅偏好',
	'translate-fs-userpage-title' => '建立汝嘅用戶頁',
	'translate-fs-permissions-title' => '請求翻譯者權限',
	'translate-fs-target-title' => '開始翻譯！',
	'translate-fs-email-title' => '確認汝嘅電郵地址',
	'translate-fs-intro' => "歡迎來到 {{SITENAME}} 入門指導。
汝將會分指導如何成為一名翻譯者。
最後你將可以翻譯 {{SITENAME}} 裏肚所有計畫个''界面訊息''.",
	'translate-fs-selectlanguage' => '選一種語言',
	'translate-fs-settings-planguage' => '首選語言：',
	'translate-fs-settings-planguage-desc' => '該首選語言作為邇隻維基項目嘅用戶界面，
並成為默認嘅翻譯目標語言。',
	'translate-fs-settings-slanguage' => '輔助語言$1：',
	'translate-fs-settings-slanguage-desc' => '在翻譯編輯器之內可以顯示其他語言翻譯个消息。
汝可以在邇位選擇您想顯示个語言。',
	'translate-fs-settings-submit' => '儲存設定',
	'translate-fs-userpage-level-N' => '𠊎嘅母語係',
	'translate-fs-userpage-level-5' => '𠊎可以專業嘅翻譯邇種語言',
	'translate-fs-userpage-level-4' => '𠊎熟練到像母語者共樣流利',
	'translate-fs-userpage-level-3' => '𠊎掌握到還算可以',
	'translate-fs-userpage-level-2' => '𠊎掌握到一般般',
	'translate-fs-userpage-level-1' => '𠊎稍微知一滴',
	'translate-fs-userpage-help' => '請標明汝嘅語言能力，並作自我介紹。係讲汝知得超過五種語言，汝做得另擺添加又較多。', # Fuzzy
	'translate-fs-userpage-submit' => '建立汝嘅用戶頁',
	'translate-fs-userpage-done' => '當好！今下汝擁有矣一隻使用者頁面。',
	'translate-fs-permissions-planguage' => '首選語言：',
	'translate-fs-permissions-help' => '今下，汝需要請求參加翻譯組。
請選擇汝想愛加入翻譯嘅首選語言。

您可以在以下嘅文本框裏肚提及其他語言與其他備註。',
	'translate-fs-permissions-pending' => '汝嘅請求已提交至[[$1]]，站點管理員會儘快查閱汝嘅請求。
係話汝已驗證汝嘅電子郵箱，遐時邇隻請求有答覆矣，就會發送郵件分汝。',
	'translate-fs-permissions-submit' => '發送請求',
	'translate-fs-target-text' => '恭喜 ！
汝今下做得開始翻譯。

係話汝還係試到毋知若何做，莫驚！
在[[Project list|項目列表]] 有汝可以貢獻嘅翻譯項目嘅概述。
大部分嘅項目有一隻簡短嘅說明頁與“翻譯邇隻項目”鏈接，其將帶汝到一隻頁面，其中列出矣所有還吂翻譯嘅消息。
 [[Special:LanguageStats|同一語言中所有還吂翻譯嘅訊息]]列表也係一隻好起點。

係話汝開始翻譯前想了解更多，汝可以去看一下[[FAQ|常見問題]]。
毋好彩嘅係文檔可能係舊版，如果汝尋毋到答案，莫愁，請到[[Support|幫助頁]]發問。

汝也可以在[[Portal:$1|語言門戶]] 嘅[[Portal_talk:$1|talk 頁]]聯繫相同語言嘅翻譯人員在。
請到[[Special:Preferences|偏好設定]]設定汝嘅用戶界面與愛翻譯嘅語言，以便wiki顯示最合適汝嘅鏈接。',
	'translate-fs-email-text' => '請到[[Special:Preferences|偏好設定]]留下並確認汝嘅電郵地址。

邇樣做得使其他譯者聯絡汝，汝也可收取我等嘅每月電子報。

係話汝不想收到月刊，可以到[[Special:Preferences|偏好設定]]頁面嘅{{int:prefs-personal}}標籤選擇停止接收。',
);

/** Hawaiian (Hawai`i)
 * @author Kolonahe
 */
$messages['haw'] = array(
	'firststeps' => 'Nā Mea Mua Loa',
	'firststeps-desc' => '[[Special:FirstSteps|Special page]] no ka hoʻomaka ʻana o nā mea hoʻohana ma kekahi wiki i hoʻohana i ka pākuʻi unuhi',
	'translate-fs-pagetitle-done' => ' - hoʻopau ʻia!',
	'translate-fs-pagetitle' => 'Polokalamu Hana Kōkua me ka Hoʻomaka ʻana - $1',
	'translate-fs-signup-title' => 'Kāinoa',
	'translate-fs-settings-title' => 'Hoʻololi i Kau Makemake',
	'translate-fs-userpage-title' => 'Hana i Kau ʻAoʻao Mea hoʻohana',
	'translate-fs-permissions-title' => 'Noi no nā ʻAe Unuhi',
	'translate-fs-target-title' => 'E Hoʻomaka i ka ʻUnuhina!',
	'translate-fs-email-title' => 'Hōʻoia i Kau Wahi leka uila',
	'translate-fs-intro' => 'Welina mai i ke Polokalamu hana kōkua no nā mea hana mua loa o {{SITENAME}}.
E  alakaʻi ana ʻoe i kēia hana o ka lilo ʻana i mea unuhi.
Ma ka hopena, hiki iā ʻoe ke uhuhi i nā "leka aloloko" o nā papa hana a pau i kākoʻo ʻia ma {{SITENAME}}.',
	'translate-fs-userpage-submit' => 'Hana i kaʻu ʻaoʻao mea hoʻohana', # Fuzzy
	'translate-fs-userpage-done' => 'Maikaʻi! Loaʻa ka ʻaoʻao mea hoʻohana i kēia manawa.',
	'translate-fs-target-text' => 'Hoʻomaikaʻi ʻana!
Hiki ke hoʻomaka i ka uhuni ʻana.

Mai makaʻu inā huikau ʻoe.
Ma [[Project list]], aia kekahi ʻike piha o nā papa hana i hiki iā ʻoe ke kōkua.
ʻO ka nui o nā papa hana, loaʻa kekahi ʻaoʻao kikoʻī pōkole me kekahi loulou "\'\'Translate this project\'\'" e lawe ana ʻoe i kekahi ʻaoʻao i helu i nā leka unuhi ʻole.
Loaʻa nō hoʻi kekahi helu no nā leka hui a pau me ke [[Special:LanguageStats|nā kūlana unuhi o kēia wa no kekahi ʻōlelo]].

Inā makemake ʻoe e maopopo ma mua hoʻi o kou unuhi ʻana, hiki ke heluhelu i ka [[FAQ|Nīnau i nīnau pinepine]].
Akā hiki i nā hana palapala ke hele a kahiko i kekahi mau manawa.
Inā loaʻa kekahi mau mea āu i noʻonoʻo hiki paha iā ʻoe ke hana, akā ʻaʻole maopopo, mai nīnau ʻole ma ke [[Support|ʻaoʻao kākoʻo]].

Hiki ke walaʻau kekahi me nā mea unuhi ʻē aʻe o ka ʻōlelo like ma loko o ko [[Portal:$1|kau wahi ʻōlelo]] [[Portal_talk:$1|ʻaoʻao kūkākūkā]].
Inā ʻaʻole i hana, [[Special:Preferences|hoʻololi i kau mea hoʻohana aloloko ʻōlelo i ka ʻōlelo āu e makemake e unuhi]], i hiki i ka wiki ke hōʻike i nā loulou e pili ana nau.',
	'translate-fs-email-text' => 'E ʻoluʻolu, e kau kau wahi leka uila i loko o [[Special:Preferences|kau makemake]] a ʻae mai loko o ka leka uila e hoʻouna ana iā ʻoe.

Hiki i kēia ʻano hana ke hoʻokuʻu i nā mea hoʻohana ʻē aʻe ke walaʻau me ʻoe i ka leka uila ʻana.
E loaʻa ana nā nū hou i hoʻokahi manawa kēlā me kēia māhina.
Inā ʻaʻole makemake e loaʻa nā nū hou, hiki ke pale i ke kāwāholo "{{int:prefs-personal}}" o kau [[Special:Preferences|makemake]].',
);

/** Hebrew (עברית)
 * @author Amire80
 * @author YaronSh
 */
$messages['he'] = array(
	'firststeps' => 'הצעדים הראשונים',
	'firststeps-desc' => 'דף מיוחד כדי לעזור למשתמשים להתחיל לעבוד בוויקי שמשתמש בהרחבת תרגום',
	'translate-fs-pagetitle-done' => ' - בוצע!',
	'translate-fs-pagetitle-pending' => ' - בהמתנה',
	'translate-fs-pagetitle' => 'אשף תחילת עבודה – $1',
	'translate-fs-signup-title' => 'הרשמה',
	'translate-fs-settings-title' => 'הגדרת ההעדפות שלך',
	'translate-fs-userpage-title' => 'ליצור את דף המשתמש שלך',
	'translate-fs-permissions-title' => 'לבקש הרשאות מתרגם',
	'translate-fs-target-title' => 'להתחיל לתרגם!',
	'translate-fs-email-title' => 'לאשר את כתובת הדוא״ל',
	'translate-fs-intro' => "ברוכים הבאים לאשף הצעדים הראשונים של אתר {{SITENAME}}.
האשף ידריך אתכם בתהליך שיהפוך אתכם לחלק מצוות המתרגמים.
בסופו תוכלו לתרגם '''הודעות ממשק''' של כל הפרויקטים הנתמכים באתר {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'בחירת שפה',
	'translate-fs-settings-planguage' => 'שפה עיקרית:',
	'translate-fs-settings-planguage-desc' => 'השפה העיקרית היא גם שפת הממשק בוויקי הזה
ושפת היעד לתרגומים לפי בררת המחדל.',
	'translate-fs-settings-slanguage' => 'שפת עזר $1:',
	'translate-fs-settings-slanguage-desc' => 'אפשר להראות תרגומים של הודעות לשפות אחרות בעורך התרגומים.
כאן אפשר לבחור אילו שפות, אם בכלל, תרצו לראות.',
	'translate-fs-settings-submit' => 'שמירת העדפות',
	'translate-fs-userpage-level-N' => 'אני דובר ילידי של',
	'translate-fs-userpage-level-5' => 'אני מתרגם מקצועי בשפה הזאת',
	'translate-fs-userpage-level-4' => 'אני יודע אותה כמו דובר ילידי',
	'translate-fs-userpage-level-3' => 'אני יודע טוב',
	'translate-fs-userpage-level-2' => 'אני יודע באופן בינוני',
	'translate-fs-userpage-level-1' => 'אני יודע קצת',
	'translate-fs-userpage-help' => 'נא לציין את כישורי השפה שלך ולספר לנו כמה דברים על עצמך. מי שיודע יותר מחמש שפות, יכול להוסיף אותן מאוחר יותר.',
	'translate-fs-userpage-submit' => 'יצירת דף המשתמש שלך',
	'translate-fs-userpage-done' => 'מצוין! כעת יש לך דף משתמש.',
	'translate-fs-permissions-planguage' => 'שפה עיקרית:',
	'translate-fs-permissions-help' => 'עכשיו צריך להעלות בקשה להתווסף לקבוצת מתרגמים.
נא לבחור את השפה העיקרית שתתרגמו אליה.

אפשר להזכיר שפות אחרות והערות אחרות בתיבה להלן.',
	'translate-fs-permissions-pending' => 'בקשתך נשלחה אל [[$1]] ומישהו מסגל האתר יבדוק אותה מהר ככל האפשר.
אם תאמתו את הכתובת הדואר האקטרוני שלכם, תקבלו הודעה כשזה יקרה.',
	'translate-fs-permissions-submit' => 'שליחת בקשה',
	'translate-fs-target-text' => "מזל טוב!
כעת, תוכלו להתחיל לתרגם.

אל תפחדו אם האתר הזה עדיין נראה לכם מבלבל וחדש.
ב[[Project list|רשימת הפרויקטים]] יש סקירה של פרויקטים שתוכלו לתרום להם תרגומים.
לרוב הפרויקטים יש דף תיאור קצר עם קישור \"'''לתרגם פרוייקט זה'''\", שיוביל אותך אל דף המפרט את כל ההודעות שאינן מתורגמות.
יש גם רשימה של כל קבוצות ההודעות עם [[Special:LanguageStats|המצב הנוכחי של תרגום אל השפה]].

אם אתם מרגישים שאתם צריכים להבין עוד לפני שתתחילו לתרגם, תוכלו לקרוא את [[FAQ|דף שאלות נפוצות]].
למרבה הצער התיעוד יכול להיות לעתים לא עדכני.
אם יש משהו שנראה לכם שאמורה להיות לכם אפשרות לעשות, ואתם לא מוצאים איך, אל תהססו לשאול אותו בדף [[Support]].

ניתן גם לפנות אל המתרגמים העמיתים באותה השפה ב[[Portal_talk:\$1|דף השיחה]] של [[Portal:\$1|פורטל השפה שלכם]].
אם טרם עשיתם זאת, [[Special:Preferences|שנו את שפת הממשק את השפה שאליה אתם רוצים לתרגם]], כדי שהוויקי הזה יציג את הקישורים המתאימים ביותר עבורכם.",
	'translate-fs-email-text' => 'נא להקליד את כתובת הדואר האלקטרוני שלכם ב[[Special:Preferences|דף ההעדפות]] ואשרו אותו באמצעות המכתב שיישלח אליכם.

פעולה זו מאפשרת למשתמשים אחרים ליצור אִתכם קשר באמצעות דואר אלקטרוני.
כמו־כן, תקבלו ידיעונים, לכל היותר פעם בחודש.
אם אינכם רוצים לקבל ידיעונים, תוכלי לבטל זאת בלשונית "{{int:prefs-personal}}" של [[Special:Preferences|דף ההעדפות]].',
);

/** Croatian (hrvatski)
 * @author SpeedyGonsales
 */
$messages['hr'] = array(
	'firststeps' => 'Prvi koraci',
	'translate-fs-pagetitle-done' => ' - učinjeno!',
	'translate-fs-signup-title' => 'Prijavite se',
	'translate-fs-settings-title' => 'Namjestite vaše postavke',
	'translate-fs-userpage-title' => 'Stvorite svoju suradničku stranicu',
	'translate-fs-permissions-title' => 'Zatražite prevoditeljski status',
	'translate-fs-target-title' => 'Počnite prevoditi poruke!',
	'translate-fs-email-title' => 'Potvrdite svoju adresu e-pošte',
	'translate-fs-userpage-submit' => 'Stvori moju suradničku stranicu', # Fuzzy
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'firststeps' => 'Prěnje kroki',
	'firststeps-desc' => '[[Special:FirstSteps|Specialna strona]] za startowu pomoc na wikiju, kotryž rozšěrjenje Translate wužiwa',
	'translate-fs-pagetitle-done' => ' - sčinjene!',
	'translate-fs-pagetitle-pending' => '´- wobdźěłuje so',
	'translate-fs-pagetitle' => 'Startowy asistent - $1',
	'translate-fs-signup-title' => 'Registrować',
	'translate-fs-settings-title' => 'Konfiguruj swoje nastajenja',
	'translate-fs-userpage-title' => 'Wutwor swoju wužiwarsku stronu',
	'translate-fs-permissions-title' => 'Wo přełožowanske prawa prosyć',
	'translate-fs-target-title' => 'Započń přełožować!',
	'translate-fs-email-title' => 'Wobkruć swoju e-mejlowu adresu',
	'translate-fs-intro' => "Witaj do startoweho asistenta projekta {{SITENAME}}.
Dóstanješ nawod krok po kroku, kak so z přełožowarjom stanješ.
Na kóncu móžeš ''zdźělenki programoweho powjercha'' wšěch podpěrowanych projektow na {{SITENAME}} přełožić.",
	'translate-fs-selectlanguage' => 'Wubjer rěč',
	'translate-fs-settings-planguage' => 'Hłowna rěč:',
	'translate-fs-settings-planguage-desc' => 'Hłowna rěč ma dwě funkciji: słuži jako rěč wužiwarskeho powjercha w tutym wikiju a jako standardna cilowa rěč za přełožki.',
	'translate-fs-settings-slanguage' => 'Pomocna rěč $1:',
	'translate-fs-settings-slanguage-desc' => 'Je móžno přełožki zdźělenkow w druhich rěčach w přełožowanskim editorje pokazać.
Tu móžeš wubrać, kotre rěče chceš rady widźeć.',
	'translate-fs-settings-submit' => 'Nastajenja składować',
	'translate-fs-userpage-level-N' => 'Sym maćernorěčnik',
	'translate-fs-userpage-level-5' => 'Sym profesionalny přełožowar',
	'translate-fs-userpage-level-4' => 'Mam znajomosće maćernorěčnika',
	'translate-fs-userpage-level-3' => 'Mam dobre znajomosće',
	'translate-fs-userpage-level-2' => 'Mam přerězne znajomosće',
	'translate-fs-userpage-level-1' => 'Mam snadne znajomosće',
	'translate-fs-userpage-help' => 'Prošu podaj swoje rěčne znajomosće a zdźěl nam něšto wo sebje. Jeli maš znajomosće we wjace hač pjeć rěčach, móžeš je pozdźišo podać.', # Fuzzy
	'translate-fs-userpage-submit' => 'Twoju wužiwarsku stronu wutworić',
	'translate-fs-userpage-done' => 'Gratulacija! Maš nětko wužiwarsku stronu.',
	'translate-fs-permissions-planguage' => 'Hłowna rěč:',
	'translate-fs-permissions-help' => 'Dyrbiš nětko naprašowanje stajić, zo by so do skupiny přełožowarjow přiwzał.
Wubjer hłownu rěč, do kotrejež chceš přełožować.

Móžeš druhe rěče a druhe přispomnjenki w slědowacym tekstowym polu podać.',
	'translate-fs-permissions-pending' => 'Twoje naprašowanje je so do [[$1]] wotpósłało a něchtó z teama translatewiki.net budźe jo tak bórze kaž móžno přehladować. Jeli swoju e-mejlowu adresu wobkrućiš, dóstanješ e-mejlowu zdźělenku, tak chětře kaž je so to stało.',
	'translate-fs-permissions-submit' => 'Naprašowanje pósłać',
	'translate-fs-target-text' => 'Zbožopřeće!
Móžeš nětko přełožowanje započeć.

Nječiń sej žane starosće, jeli so ći hišće nowe a konfuzne zda.
Na [[Project list|lisćinje projektow]] je přehlad projektow, ke kotrymž móžeš přełožki přinošować.
Najwjace projektow ma krótku wopisansku stronu z wotkazom "\'\'Tutón projekt přełožić\'\'", kotryž će k stronje wjedźe, kotraž wšě njepřełožene zdźělenki nalistuje.
Lisćina wšěch skupinow zdźělenkow z [[Special:LanguageStats|aktualnym přełožowanskim stawom za rěč]] tež k dispoziciji steji.

Jeli měniš, zo dyrbiš najprjedy wjace rozumić, prjedy hač zapóčnješ přełožować, móžeš [[FAQ|Časte prašenja]] čitać.
Bohužel móže dokumentacija druhdy zestarjena być.
Jeli něšto je, wo kotrymž mysliš, zo měło móžno być, ale njenamakaš, kak móžeš to činić, prašej so woměrje na stronje [[Support|Podpěra]].

Móžeš so tež ze sobupřełožowarjemi samsneje rěče na [[Portal_talk:$1|diskusijnej stronje]] [[Portal:$1|portala swojeje rěče]] do zwiska stajić.
Jeli hišće njejsy to činił, [[Special:Preferences|změń swój wužiwarski powjerch do rěče, do kotrejež chceš přełožować]], zo by wiki móhł wotkazy pokazać, kotrež su relewantne za tebje.',
	'translate-fs-email-text' => 'Prošu podaj swoju e-mejlowu adresu w [[Special:Preferences|swojich nastajenjach]] a wobkruć ju přez e-mejl, kotraž so ći sćele.

To dowola druhim wužiwarjam, so z tobu přez e-mejl do zwiska stajić.
Dóstanješ tež powěsćowe listy, zwjetša jónkróć wob měsac.
Jeli nochceš powěsćowe listy dóstać, móžeš tutu opciju na rajtarku "{{int:prefs-personal}}" swojich [[Special:Preferences|nastajenjow]] znjemóžnić.',
);

/** Haitian (Kreyòl ayisyen)
 * @author Boukman
 */
$messages['ht'] = array(
	'firststeps' => 'Premye etap yo',
	'firststeps-desc' => '[[Special:FirstSteps|Paj espesyal]] pou gide itilizatè yo sou yon wiki ki sèvi ak ekstansyon Tradiksyon',
	'translate-fs-pagetitle-done' => '- fini!',
	'translate-fs-pagetitle' => 'Gid pou komanse - $1',
	'translate-fs-signup-title' => 'Anrejistre ou',
	'translate-fs-settings-title' => 'Konfigire preferans ou yo',
	'translate-fs-userpage-title' => 'Kreye paj itilizatè ou an',
	'translate-fs-permissions-title' => 'Mande pou otorizasyon tradiktè yo',
	'translate-fs-target-title' => 'Kòmanse tradui!',
	'translate-fs-email-title' => 'Konfime adrès imèl ou an',
	'translate-fs-intro' => "Byenveni nan asistan premye etap {{SITENAME}}.
N ap gide ou atravè tout etap pwosesis pou ou vin yon tradiktè.
Lè ou rive nan bout pwosesis sa, w ap kapab tradui tou ''mesaj entèfas'' pou tout pwojè ki sipòte nan {{SITENAME}}.",
	'translate-fs-userpage-submit' => 'Kreye paj itilizatè mwen', # Fuzzy
	'translate-fs-userpage-done' => 'Byen fè!  Kounye a ou gen yon paj itilizatè.',
	'translate-fs-target-text' => 'Konpliman!
Ou kapab komanse tradui kounye a.

Ou pa bezwen pè si ou santi bagay sa nouvo epi le ba ou konfizyon.
Nan [[Project list|Lis pwojè yo]], genyen yon apèsi tout projè ou kapab kontribye tradiksyon pou yo.
Pifò nan pwojè yo gen yon paj ki bay yon deskripsyon kout avèk yon lyen "\'\'Tradui pwojè sa a\'", k ap mennen ou nan yon paj ki liste tout mesaj ki poko tradui.
Yon lis ak tout gwoup mesaj yo ki bay [[Special:LanguageStats|estati tradiksyon yo pou yon lang]] disponib tou.

Si ou santi ou ta bezwen konnen pi plis anvan ou komanse tradui, ou ka li [[FAQ|Kesyon ki mande souvan]].
Malerezman, dokimantasyon gendwa pa a jou.
Si gen yon bagay ou panse ou ta dwe kapab fè, men ou pa ka jwenn kijan, pa ezite mande nan [[Support|paj sipò]].

Ou kapab kontakte lòt tradiktè nan menm lang tou nan [[Portal_talk:$1|paj diskisyon]] pou [[Portal:$1|potay pou lang ou an]].
Si ou poko fè sa, [[Special:Preferences|chanje lang entèfas itilizatè ou an pou l sèvi ak lang ou pral tradui ladan l]]',
	'translate-fs-email-text' => 'Tanpri, bay adrès imèl ou an nan [[Special:Preferences|preferans ou yo]] epi konfime l depi imèl ki te voye ba ou.

Sa ap pèmèt lòt itilizatè kontakte ou pa imèl.
W ap resevwa nouvèl tou yon fwa pa mwa o maksimòm.
Si ou pa vle resevwa nouvèl, ou kapab retire ou nan opsyon sa nan onglè "{{int:prefs-personal}}" ki nan [[Special:Preferences|preferans ou yo]].',
);

/** Hungarian (magyar)
 * @author Dani
 * @author Dj
 * @author Misibacsi
 */
$messages['hu'] = array(
	'firststeps' => 'Első lépések',
	'firststeps-desc' => '[[Special:FirstSteps|Speciális lap]], ami felkészíti az új felhasználókat a fordító kiterjesztés használatára',
	'translate-fs-pagetitle-done' => ' - kész!',
	'translate-fs-pagetitle-pending' => ' - függőben',
	'translate-fs-pagetitle' => 'Első lépések varázsló – $1',
	'translate-fs-signup-title' => 'Regisztráció',
	'translate-fs-settings-title' => 'Nézd át a beállításaidat!',
	'translate-fs-userpage-title' => 'Hozz létre egy felhasználói lapot',
	'translate-fs-permissions-title' => 'Kérj fordítói jogosultságot!',
	'translate-fs-target-title' => 'Kezdj fordítani!',
	'translate-fs-email-title' => 'Erősítsd meg az e-mail címedet!',
	'translate-fs-intro' => "Üdvözlünk a {{SITENAME}} használatának első lépéseiben segítő varázslóban!
Lépésről lépésre segítünk a fordítóvá válás folyamatában.
A végén hozzákezdhetsz bármelyik, {{SITENAME}} által támogatott projekt ''felületének üzeneteinek'' fordításához.",
	'translate-fs-selectlanguage' => 'Válassz egy nyelvet!',
	'translate-fs-settings-planguage' => 'Elsődleges nyelv:',
	'translate-fs-settings-slanguage' => 'Segédnyelv $1:',
	'translate-fs-settings-submit' => 'Beállítások mentése',
	'translate-fs-userpage-level-N' => 'Anyanyelvi beszélője vagyok:',
	'translate-fs-userpage-level-5' => 'Profi fordítója vagyok:',
	'translate-fs-userpage-level-4' => 'Szinte anyanyelvi szinten beszélem:',
	'translate-fs-userpage-level-3' => 'Jól beszélek:',
	'translate-fs-userpage-level-2' => 'Megértetem magam:',
	'translate-fs-userpage-level-1' => 'Ismerem valamennyire:',
	'translate-fs-userpage-help' => 'Kérjük jelezd, hogy mennyire beszélsz idegen nyelveken és mondj valamit magadról. Ha több mint öt nyelvet ismersz, ezt később megadhatod.',
	'translate-fs-userpage-submit' => 'Felhasználói lap létrehozása', # Fuzzy
	'translate-fs-userpage-done' => 'Felhasználói lap létrehozva.',
	'translate-fs-permissions-planguage' => 'Elsődleges nyelv:',
	'translate-fs-permissions-submit' => 'Kérelem elküldése',
	'translate-fs-target-text' => "Gratulálunk!
Most már elkezdhetsz fordítani.

Ne ijedj meg, ha még új a felület, és valami összezavar.
A [[Project list|projektlista]] lapon megtalálod azon projektek listáját, melyek fordításában részt vehetsz.
A legtöbb projekthez tartozik egy rövid leírás és egy „''Projekt fordítása''” hivatkozás, ami elvezet arra a lapra, ahol a fordítatlan üzenetek vannak listázva.
Rendelkezésre áll egy olyan lista is, ahol az üzenetcsoportok tekinthetőek meg, a hozzátartozó, [[Special:LanguageStats|adott nyelvre vonatkozó fordítási állapottal]].

Ha a fordítás előtt inkább tájékozódni szeretnél, olvasd el a [[FAQ|gyakran ismételt kérdéseket]].
Sajnos a dokumentáció néha kicsit elavult lehet.
Ha úgy érzed, hogy valamit meg tudnál csinálni, de nem jössz rá, hogyan, ne habozz, kérdezz a [[Support|támogatással foglalkozó oldalon]].

Kapcsolatba léphetsz fordítótársaiddal a [[Portal:$1|nyelvedhez tartozó portál]] [[Portal_talk:$1|vitalapján]] keresztül.
Ha még nem tetted meg, [[Special:Preferences|állítsd át a felhasználói felületed nyelvét arra a nyelvre, amire fordítani szeretnél]], hogy a wiki a megfelelő linkeket tudja nyújtani neked.",
);

/** Interlingua (interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'firststeps' => 'Prime passos',
	'firststeps-desc' => '[[Special:FirstSteps|Pagina special]] pro familiarisar le usatores de un wiki con le extension Translate',
	'translate-fs-pagetitle-done' => ' - finite!',
	'translate-fs-pagetitle-pending' => ' - pendente',
	'translate-fs-pagetitle' => 'Assistente de initiation - $1',
	'translate-fs-signup-title' => 'Crear un conto',
	'translate-fs-settings-title' => 'Configurar tu preferentias',
	'translate-fs-userpage-title' => 'Crear tu pagina de usator',
	'translate-fs-permissions-title' => 'Requestar permissiones de traductor',
	'translate-fs-target-title' => 'Comenciar a traducer!',
	'translate-fs-email-title' => 'Confirmar tu adresse de e-mail',
	'translate-fs-intro' => "Benvenite al assistente de initiation de {{SITENAME}}.
Tu essera guidate passo a passo trans le processo de devenir traductor.
Al fin tu potera traducer le ''messages de interfacie'' de tote le projectos supportate in {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Selige un lingua',
	'translate-fs-settings-planguage' => 'Lingua primari:',
	'translate-fs-settings-planguage-desc' => 'Le lingua primari es le lingua de interfacie in iste wiki,
e le lingua predefinite pro traductiones.',
	'translate-fs-settings-slanguage' => 'Lingua assistente $1:',
	'translate-fs-settings-slanguage-desc' => 'Es possibile monstrar traductiones de messages in altere linguas in le editor de traductiones.
Hic tu pote seliger le linguas que tu vole vider (si desirate).',
	'translate-fs-settings-submit' => 'Confirmar preferentias',
	'translate-fs-userpage-level-N' => 'Io es un parlante native de',
	'translate-fs-userpage-level-5' => 'Io es un traductor professional de',
	'translate-fs-userpage-level-4' => 'Io ha cognoscentia quasi native de',
	'translate-fs-userpage-level-3' => 'Io ha un bon maestria de',
	'translate-fs-userpage-level-2' => 'Io ha un maestria moderate de',
	'translate-fs-userpage-level-1' => 'Io cognosce un poco de',
	'translate-fs-userpage-help' => 'Per favor indica tu habilitates linguistic e parla un poco de te. Si tu cognosce plus de cinque linguas, tu pote adder alteres plus tarde.',
	'translate-fs-userpage-submit' => 'Crear tu pagina de usator',
	'translate-fs-userpage-done' => 'Ben facite! Tu ha ora un pagina de usator.',
	'translate-fs-permissions-planguage' => 'Lingua primari:',
	'translate-fs-permissions-help' => 'Ora tu debe poner un requesta de esser addite al gruppo de traductores.
Selige le lingua primari in le qual tu va traducer.

Tu pote mentionar altere linguas e altere remarcas in le quadro de texto hic infra.',
	'translate-fs-permissions-pending' => 'Tu requesta ha essite submittite a [[$1]] e un persona del personal del sito lo verificara si presto como possibile.
Si tu confirma tu adresse de e-mail, tu recipera un notification in e-mail al momento que isto eveni.',
	'translate-fs-permissions-submit' => 'Inviar requesta',
	'translate-fs-target-text' => 'Felicitationes!
Tu pote ora comenciar a traducer.

Non sia intimidate si isto te pare ancora nove e confundente.
In [[Project list]] il ha un summario del projectos al quales tu pote contribuer traductiones.
Le major parte del projectos ha un curte pagina de description con un ligamine "\'\'Traducer iste projecto\'\'", que te portara a un pagina que lista tote le messages non traducite.
Un lista de tote le gruppos de messages con le [[Special:LanguageStats|stato de traduction actual pro un lingua]] es etiam disponibile.

Si tu senti que tu ha besonio de comprender plus ante de traducer, tu pote leger le [[FAQ|folio a questiones]].
Infelicemente le documentation pote a vices esser obsolete.
Si il ha un cosa que tu pensa que tu deberea poter facer, ma non succede a discoperir como, non hesita a poner le question in le [[Support|pagina de supporto]].

Tu pote etiam contactar altere traductores del mesme lingua in [[Portal_talk:$1|le pagina de discussion]] del [[Portal:$1|portal de tu lingua]].
Si tu non ja lo ha facite, [[Special:Preferences|cambia tu lingua de interfacie de usator al lingua in le qual tu vole traducer]], de sorta que le wiki pote monstrar te le ligamines le plus relevante a te.',
	'translate-fs-email-text' => 'Per favor entra tu adresse de e-mail in [[Special:Preferences|tu preferentias]] e confirma lo per medio del e-mail que te essera inviate.

Isto permitte que altere usatores te contacta via e-mail.
Tu recipera anque bulletines de novas al plus un vice per mense.
Si tu non vole reciper bulletines de novas, tu pote disactivar los in le scheda "{{int:prefs-personal}}" de tu [[Special:Preferences|preferentias]].',
);

/** Indonesian (Bahasa Indonesia)
 * @author Farras
 * @author Irwangatot
 * @author IvanLanin
 */
$messages['id'] = array(
	'firststeps' => 'Langkah pertama',
	'firststeps-desc' => '[[Special:FirstSteps|Halaman istimewa]] untuk mendapatkan pengguna memulai di wiki menggunakan ekstensi Terjemahan',
	'translate-fs-pagetitle-done' => '- Selesai!',
	'translate-fs-pagetitle' => 'Wisaya perkenalan - $1',
	'translate-fs-signup-title' => 'Mendaftar',
	'translate-fs-settings-title' => 'Mengkonfigurasi preferensi anda',
	'translate-fs-userpage-title' => 'Buat halaman pengguna anda',
	'translate-fs-permissions-title' => 'Permintaan izin penerjemah',
	'translate-fs-target-title' => 'Mulai menerjemahkan!',
	'translate-fs-email-title' => 'Konfirmasikan alamat surel Anda',
	'translate-fs-intro' => "Selamat datang di wisaya tahapan pertama {{SITENAME}}.
Anda akan dipandu melalui proses untuk menjadi seorang penerjemah tahap demi tahap.
Hasilnya Anda akan mampu menerjemahkan ''pesan antarmuka'' semua proyek yang didukung di {{SITENAME}}.",
	'translate-fs-userpage-submit' => 'Buat halaman pengguna saya', # Fuzzy
	'translate-fs-userpage-done' => 'Bagus! Sekarang Anda memiliki halaman pengguna.',
	'translate-fs-target-text' => 'Selamat!
Sekarang Anda dapat mulai menerjemahkan.

Jangan takut apabila masih terasa baru dan membingungkan Anda.
Di [[Project list]] ada gambaran mengenai proyek yang dapat Anda sumbangkan terjemahannya.
Sebagian besar proyek memiliki halaman deskripsi pendek dengan pranala "\'\'Terjemahkan proyek ini\'\'", pranala tersebut akan membawa Anda ke halaman yang berisi daftar semua pesan yang belum diterjemahkan.
Daftar semua grup pesan dengan [[Special:LanguageStats|status terjemahan saat ini untuk suatu bahasa]] juga tersedia.

Jika Anda merasa bahwa Anda perlu untuk memahami lebih lanjut sebelum mulai menerjemahkan, Anda dapat membaca [[FAQ|Pertanyaan-pertanyaan yang Sering Diajukan]].
Sayangnya dokumentasi kadang dapat kedaluwarsa.
Jika ada sesuatu yang Anda pikir Anda harus mampu lakukan, tetapi tidak dapat menemukan caranya, jangan ragu untuk menanyakannya di [[Support|halaman dukungan]].

Anda juga dapat menghubungi sesama penerjemah bahasa yang sama di [[Portal_talk:$1|halaman pembicaraan]] [[Portal:$1|portal bahasa Anda]].
Jika Anda belum melakukannya, [[Special:Preferences|ubah bahasa antarmuka pengguna Anda menjadi bahasa terjemahan Anda]] sehingga wiki dapat menunjukkan pranala paling relevan untuk Anda.',
	'translate-fs-email-text' => 'Mohon masukkan alamat surel Anda di [[Special:Preferences|preferensi Anda]] dan konfirmasikan dari surel yang dikirimkan ke Anda.

Tindakan ini memungkinkan pengguna lain menghubungi Anda melalui surel.
Anda juga akan menerima langganan berita sekali sebulan.
Jika Anda tidak ingin menerima langganan berita, Anda dapat memilih tidak di tab "{{int:prefs-personal}}" di [[Special:Preferences|preferensi]] Anda.',
);

/** Igbo (Igbo)
 * @author Ukabia
 */
$messages['ig'] = array(
	'translate-fs-pagetitle-done' => '- ọméchá!',
);

/** Icelandic (íslenska)
 * @author Snævar
 */
$messages['is'] = array(
	'firststeps' => 'Fyrstu skrefin',
	'translate-fs-pagetitle-done' => '- búið!',
	'translate-fs-pagetitle-pending' => '- í bið',
	'translate-fs-signup-title' => 'Skráðu þig',
	'translate-fs-settings-title' => 'Breyttu stillingunum þínum',
	'translate-fs-userpage-title' => 'Búðu til notendasíðu',
	'translate-fs-permissions-title' => 'Óskaðu eftir þýðingar réttindum',
	'translate-fs-target-title' => 'Byrjaðu að þýða!',
	'translate-fs-email-title' => 'Staðfestu netfangið þitt',
	'translate-fs-selectlanguage' => 'Veldu tungumál',
	'translate-fs-settings-planguage' => 'Fyrsta tungumál:',
	'translate-fs-settings-planguage-desc' => 'Fyrsta tungumál verður notað bæði sem viðmótstungumálið þitt á þessum wiki og sjálfgefið tungumál fyrir þýðingar.',
	'translate-fs-settings-slanguage' => 'Aðstoðar tungumál: $1',
	'translate-fs-settings-slanguage-desc' => 'Það er hægt að sýna þýðingar fyrir skilaboð á öðrum tungumálum í þýðingar viðmótinu.
Hér getur þú valið hvaða tungumál, ef einhver, þú villt sjá.',
	'translate-fs-settings-submit' => 'Vista stillingar',
	'translate-fs-userpage-level-N' => 'Móðurmál mitt er',
	'translate-fs-userpage-level-5' => 'Ég hef atvinnufærni í',
	'translate-fs-userpage-level-4' => 'Ég tala málið eins og innfæddur',
	'translate-fs-userpage-level-3' => 'Ég hef yfirburðarkunnáttu á',
	'translate-fs-userpage-level-2' => 'Ég hef miðlungskunnáttu á',
	'translate-fs-userpage-level-1' => 'Ég hef grundvallarkunnáttu á',
	'translate-fs-userpage-help' => 'Vinsamlegast gefðu upp færni þína á tungumálinu og segðu okkur eitthvað um þig. Ef þú þekkir fleiri en fimm tungumál, þá getur þú bætt við fleirum seinna.', # Fuzzy
	'translate-fs-userpage-submit' => 'Búðu til notendasíðuna mína', # Fuzzy
	'translate-fs-userpage-done' => 'Vel gert! Þú hefur nú notendasíðu.',
	'translate-fs-permissions-planguage' => 'Fyrsta tungumál:',
	'translate-fs-permissions-help' => 'Nú þarft þú að óska eftir að fá þýðinda réttindi.
Veldu það tungumál sem þú ætlar að þýða á.

Þú getur nefnt önnur tungumál og athugasemdir í texta boxinu hér fyrir neðan.',
	'translate-fs-permissions-pending' => 'Beiðni þín hefur verið sent til [[$1]] og starfsmaður síðunnar mun fara yfir hana eins fljótt og auðið er.
Ef þú staðfestir netfangið þitt, þá færð þú tölvupóst um leið og það gerist.',
	'translate-fs-permissions-submit' => 'Senda beiðni',
	'translate-fs-email-text' => 'Vinsamlegast tilgreindu netfangið þitt í [[Special:Preferences|stillingunum þínum]] og staðfestu það frá tölvupóstinum sem er sendur til þín.

Þetta gerir öðrum notendum kleift að hafa samband við þig með tölvupósti.
Þú munt einnig fá fréttabréf allt að einu sinni í mánuði.
Ef þú villt ekki fá send fréttabréf þá getur þú afvirkjað möguleikann undir "{{int:prefs-personal}}" flipanum í [[Special:Preferences|stillingunum þínum]]',
);

/** Italian (italiano)
 * @author Beta16
 * @author Nemo bis
 */
$messages['it'] = array(
	'firststeps' => 'Primi passi',
	'firststeps-desc' => "[[Special:FirstSteps|Pagina speciale]] per aiutare gli utenti nei loro inizi in un wiki che fa uso dell'estensione Translate.",
	'translate-fs-pagetitle-done' => '- fatto!',
	'translate-fs-pagetitle-pending' => '- in attesa',
	'translate-fs-pagetitle' => 'Percorso guidato per i primi passi - $1',
	'translate-fs-signup-title' => 'Registrati',
	'translate-fs-settings-title' => 'Configura le tue preferenze',
	'translate-fs-userpage-title' => 'Crea la tua pagina utente',
	'translate-fs-permissions-title' => 'Richiedi i permessi per tradurre',
	'translate-fs-target-title' => 'Comincia a tradurre!',
	'translate-fs-email-title' => 'Conferma il tuo indirizzo email',
	'translate-fs-intro' => "Benvenuto/a nel percorso guidato per i primi passi in {{SITENAME}}.
Sarai guidato passo passo nel processo di diventare un traduttore.
Alla fine sarai in grado di tradurre i ''messaggi di sistema'' di tutti i progetti supportati da {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Scegli una lingua',
	'translate-fs-settings-planguage' => 'Lingua principale:',
	'translate-fs-settings-planguage-desc' => "La tua lingua principale funge per te sia da lingua dell'interfaccia del wiki sia da lingua predefinita in cui tradurre.",
	'translate-fs-settings-slanguage' => 'Lingua di confronto $1:',
	'translate-fs-settings-slanguage-desc' => "È possibile mostrare le traduzioni dei messaggi in altre lingue nell'interfaccia di traduzione.
Qui puoi scegliere eventualmente quali lingue vuoi vedere.",
	'translate-fs-settings-submit' => 'Salva preferenze',
	'translate-fs-userpage-level-N' => 'Sono madrelingua in',
	'translate-fs-userpage-level-5' => 'Sono un traduttore professionista di',
	'translate-fs-userpage-level-4' => 'La conosco come un madrelingua.',
	'translate-fs-userpage-level-3' => 'Ho una buona conoscenza di',
	'translate-fs-userpage-level-2' => 'Ho una discreta conoscenza di',
	'translate-fs-userpage-level-1' => "Conosco un po' di",
	'translate-fs-userpage-help' => 'Indica le tue abilità linguistiche e dicci qualcosa di te. Se conosci più di cinque lingue, puoi aggiungerne altre in seguito.',
	'translate-fs-userpage-submit' => 'Crea la tua pagina utente',
	'translate-fs-userpage-done' => 'Ben fatto! Ora hai una pagina utente.',
	'translate-fs-permissions-planguage' => 'Lingua principale:',
	'translate-fs-permissions-help' => 'Ora devi fare richiesta per essere aggiunto al gruppo dei traduttori.
Seleziona la lingua principale in cui vuoi tradurre.

Puoi indicare altre lingue e altre informazioni rilevanti nella casella di testo sottostante.',
	'translate-fs-permissions-pending' => "La tua richiesta è stata pubblicata in [[$1]] e un membro dell'organico del sito la verificherà al più presto.
Se confermi il tuo indirizzo e-mail, riceverai una notifica via e-mail appena succederà.",
	'translate-fs-permissions-submit' => 'Invia richiesta',
	'translate-fs-target-text' => "Congratulazioni!
Ora puoi cominciare a tradurre.

Non preoccuparti se tutto ti sembra ancora nuovo e ti confonde.
Alla pagina [[Project list]] c'è una panoramica dei progetti alla cui traduzione puoi collaborare.
La maggior parte dei progetti ha una breve pagina di descrizione con un collegamento \"''Traduci questo progetto''\" che ti porterà a una pagina che elenca tutti i messaggi rimasti da tradurre.
C'è anche un elenco di tutti i gruppi di messaggi con [[Special:LanguageStats|l'attuale stato della loro traduzione in una certa lingua]].

Se pensi di aver bisogno di capire meglio prima di cominciare a tradurre, puoi leggere le [[FAQ|risposte alle domande più frequenti]].
Purtroppo la documentazione è talvolta non aggiornata.
Se c'è qualcosa che pensi dovresti poter fare, ma non riesci a capire come, non farti problemi a chiedere alla [[Support|pagina d'aiuto]].

Puoi anche contattare colleghi traduttore della stessa lingua nella [[Portal_talk:\$1|pagina di discussione]] del [[Portal:\$1|portale della tua lingua]].
Se non l'hai già fatto, [[Special:Preferences|seleziona come lingua dell'interfaccia utente la lingua in cui vuoi tradurre]], così che il wiki sia in grado di mostrarti i collegamenti più pertinenti per te.",
	'translate-fs-email-text' => 'Ti consigliamo di inserire il tuo indirizzo e-mail nelle [[Special:Preferences|tue preferenze]] e di confermarlo attraverso il messaggio che ti sarà inviato.

Ciò permetterà agli altri utenti di contattarti per e-mail.
Inoltre, riceverai la newsletter al più una volta al mese.
Se non vuoi ricevere la newsletter, puoi esserne escluso attraverso l\'apposita opzione della scheda "{{int:prefs-personal}}" delle [[Special:Preferences|tue preferenze]].',
);

/** Japanese (日本語)
 * @author Fryed-peach
 * @author Hosiryuhosi
 * @author Shirayuki
 * @author Whym
 * @author 青子守歌
 */
$messages['ja'] = array(
	'firststeps' => '開始手順',
	'firststeps-desc' => 'Translate 拡張機能を使用するウィキで利用者が開始準備をするための[[Special:FirstSteps|特別ページ]]',
	'translate-fs-pagetitle-done' => ' - 完了!',
	'translate-fs-pagetitle-pending' => ' - 保留中',
	'translate-fs-pagetitle' => '開始準備ウィザード - $1',
	'translate-fs-signup-title' => '利用者登録',
	'translate-fs-settings-title' => '個人設定の構成',
	'translate-fs-userpage-title' => '自分の利用者ページの作成',
	'translate-fs-permissions-title' => '翻訳者権限の申請',
	'translate-fs-target-title' => '翻訳を始めましょう!',
	'translate-fs-email-title' => '自分のメールアドレスの確認',
	'translate-fs-intro' => '{{SITENAME}} 開始準備ウィザードへようこそ。これから翻訳者になるための手順について1つずつ案内していきます。それらを終えると、あなたは {{SITENAME}} でサポートしているすべてのプロジェクトのインターフェイスメッセージを翻訳できるようになります。',
	'translate-fs-selectlanguage' => '言語を選択',
	'translate-fs-settings-planguage' => '第一言語:',
	'translate-fs-settings-planguage-desc' => '第一言語は、このウィキのインターフェイスで使用する言語と、既定の翻訳先言語を兼ねます。',
	'translate-fs-settings-slanguage' => '補助言語$1:',
	'translate-fs-settings-slanguage-desc' => '翻訳編集画面に、メッセージに対する他の言語への翻訳を表示できます。
表示させたい言語があれば、ここで選択してください。',
	'translate-fs-settings-submit' => '設定を保存',
	'translate-fs-userpage-level-N' => '母語話者です',
	'translate-fs-userpage-level-5' => '翻訳の専門家です',
	'translate-fs-userpage-level-4' => '母語のように扱えます',
	'translate-fs-userpage-level-3' => '流暢に扱えます',
	'translate-fs-userpage-level-2' => '中級程度の能力です',
	'translate-fs-userpage-level-1' => '少し使うことができます',
	'translate-fs-userpage-help' => '自分の言語能力を紹介し、何か自己紹介をしてください。6つ以上の言語を知っている場合は、あとで追加できます。',
	'translate-fs-userpage-submit' => '自分の利用者ページを作成',
	'translate-fs-userpage-done' => 'お疲れ様です。あなたの利用者ページができました。',
	'translate-fs-permissions-planguage' => '第一言語:',
	'translate-fs-permissions-help' => '次に翻訳者グループへの追加申請をする必要があります。
翻訳する予定の第一言語を選択してください。

他の言語やその他の事項については、以下のテキストボックスで説明できます。',
	'translate-fs-permissions-pending' => '申請は[[$1]]に送信され、サイトのスタッフの誰かが早急に確認します。
もしメールアドレスを設定していれば、メール通知によって結果をすぐに知ることができます。',
	'translate-fs-permissions-submit' => '申請を送信',
	'translate-fs-target-text' => "お疲れ様でした!
あなたが翻訳を開始する準備が整いました。

まだ慣れないことや分かりにくいことがあっても、心配することはありません。
[[Project list|プロジェクト一覧]]にあなたが翻訳できる各プロジェクトの概要があります。
ほとんどのプロジェクトには短い解説ページがあり、「'''Translate this project'''」というリンクからそのプロジェクトのすべての未翻訳メッセージの一覧ページに移動できます。
[[Special:LanguageStats|各言語内での現在の翻訳状況]]には、すべてのメッセージ群の一覧もあります。

翻訳を始める前にもっと知らなければならないことがあると感じた場合は、[[FAQ]] のページを読むのもよいでしょう。
残念なことに説明文の中には更新が途絶えてしまっているものもあります。
もし、何かやりたいことがあって、その方法が分からない場合には、遠慮なく[[Support|サポートページ]]で質問してください。

また、同じ言語で作業している仲間の翻訳者とは[[Portal:$1|言語別のポータル]]の[[Portal_talk:$1|トークページ]]で連絡を取れます。
まだ設定していない場合は、[[Special:Preferences|インターフェイス言語を、翻訳先としたい言語に変更]]すれば、ウィキ上では最も関連性のあるリンクが表示されます。",
	'translate-fs-email-text' => 'あなたのメールアドレスを[[Special:Preferences|個人設定]]に入力して、お送りするメールでメールアドレスの確認を行ってください。

これにより他の利用者があなたに連絡できるようになります。
また、毎月 1 回まで、ニュースレターをお送りします。
ニュースレターが不要な場合は、[[Special:Preferences|個人設定]]の「{{int:prefs-personal}}」タブで受信を中止できます。',
);

/** Jamaican Creole English (Patois)
 * @author Yocahuna
 */
$messages['jam'] = array(
	'firststeps' => 'Fos tepdem',
	'firststeps-desc' => '[[Special:FirstSteps|Peshal piej]] fi get yuuza taat pan a wiki a yuuz di Chransliet extenshan',
	'translate-fs-pagetitle-done' => '- don!',
	'translate-fs-pagetitle' => 'Taat op wizad - $1',
	'translate-fs-signup-title' => 'Sain op',
	'translate-fs-settings-title' => 'Kanfiga yu prefransdem',
	'translate-fs-userpage-title' => 'Kriet yu yuuza piej',
	'translate-fs-permissions-title' => 'Rikwes chranslieta pomishan',
	'translate-fs-target-title' => 'Taat fi chransliet!',
	'translate-fs-email-title' => 'Kanfoerm yu e-miel ajres.',
	'translate-fs-intro' => "Welkom tu di {{SITENAME}} fos tep wizad.
Yu wi gaid chruu di pruoses fi ton chranslieta tep bi tep.
Wen yu don yu wi iebl fi chransliet '''intafies mechiz''' a aal prajek wa supuot a {{SITENAME}}.",
	'translate-fs-userpage-submit' => 'Kriet mi yuuza piej', # Fuzzy
	'translate-fs-userpage-done' => 'Yaa gwaan! Yu nou ab a yuuza piej.',
);

/** Javanese (Basa Jawa)
 * @author NoiX180
 */
$messages['jv'] = array(
	'firststeps' => 'Tahap wiwitan',
	'firststeps-desc' => '[[Special:FirstSteps|Kaca astamiwa]] kanggo mayaraké panganggo nglekasi wiki nganggo èkstènsi Terjemahaké',
	'translate-fs-pagetitle-done' => '- rampung!',
	'translate-fs-pagetitle-pending' => '- ditundha',
	'translate-fs-pagetitle' => 'Wisaya pangenalan - $1',
	'translate-fs-signup-title' => 'Daptar',
	'translate-fs-settings-title' => 'Atur prèferènsi Sampéyan',
	'translate-fs-userpage-title' => 'Gawé kaca panganggo Sampéyan',
	'translate-fs-permissions-title' => 'Njaluk idin panerjemah',
	'translate-fs-target-title' => 'Mulai nerjemahaké!',
	'translate-fs-email-title' => 'Konfirmasi alamat layang èlèktronik Sampéyan',
	'translate-fs-selectlanguage' => 'Pilih basa',
	'translate-fs-settings-planguage' => 'Basa utama:',
	'translate-fs-settings-slanguage' => 'Basa panyengkuyung $1:',
	'translate-fs-settings-submit' => 'Simpen préperensi',
	'translate-fs-userpage-level-N' => 'Kula panutur iku saka',
	'translate-fs-userpage-level-5' => 'Kula panerjemah profesional saka',
	'translate-fs-userpage-level-4' => 'Kula ngerti kayata panutur ibu',
	'translate-fs-userpage-level-3' => 'Kula ngertèni',
	'translate-fs-userpage-level-2' => 'Kula cukup ngertèni',
	'translate-fs-userpage-level-1' => 'Kula ngerti sithik',
	'translate-fs-userpage-submit' => 'Gawé kaca panganggo kula', # Fuzzy
	'translate-fs-userpage-done' => 'Rampung! Sampéyan saiki nduwé kaca panganggo.',
	'translate-fs-permissions-planguage' => 'Basa utama:',
	'translate-fs-permissions-submit' => 'Kirim panjalukan',
);

/** Georgian (ქართული)
 * @author David1010
 */
$messages['ka'] = array(
	'firststeps' => 'პირველი ნაბიჯები',
	'translate-fs-pagetitle-done' => ' - გაკეთდა!',
	'translate-fs-pagetitle-pending' => ' - მოლოდინში',
	'translate-fs-signup-title' => 'დარეგისტრირდით',
	'translate-fs-userpage-title' => 'შექმენით თქვენი მომხმარებლის გვერდი',
	'translate-fs-target-title' => 'დაიწყეთ თარგმნა!',
	'translate-fs-email-title' => 'დაადასტურეთ თქვენი ელ. ფოსტის მისამართი',
	'translate-fs-selectlanguage' => 'აირჩიეთ ენა',
	'translate-fs-settings-planguage' => 'ძირითადი ენა:',
	'translate-fs-settings-slanguage' => 'დამხმარე ენა $1:',
	'translate-fs-settings-submit' => 'კონფიგურაციის შენახვა',
	'translate-fs-userpage-level-N' => 'ჩემი მშობლიური ენაა',
	'translate-fs-userpage-level-5' => 'მე ვარ პროფესიონალი მთარგმნელი',
	'translate-fs-userpage-level-4' => 'მე თავისუფლად ვფლობ',
	'translate-fs-userpage-level-3' => 'მე კარგად ვიცი',
	'translate-fs-userpage-level-2' => 'მე საშუალოდ ვფლობ',
	'translate-fs-userpage-level-1' => 'საწყისი ცოდნა',
	'translate-fs-userpage-submit' => 'შექმენით თქვენი მომხმარებლის გვერდი',
	'translate-fs-userpage-done' => 'ყოჩაღ! ახლა თქვენ უკვე გაქვთ მომხმარებლის გვერდი.',
	'translate-fs-permissions-planguage' => 'ძირითადი ენა:',
	'translate-fs-permissions-submit' => 'მოთხოვნის გაგზავნა',
);

/** Khmer (ភាសាខ្មែរ)
 * @author គីមស៊្រុន
 */
$messages['km'] = array(
	'firststeps' => 'ជំហានដំបូង',
);

/** Korean (한국어)
 * @author Freebiekr
 * @author LFM
 * @author 관인생략
 * @author 아라
 */
$messages['ko'] = array(
	'firststeps' => '첫걸음',
	'firststeps-desc' => '번역 확장 기능을 사용해 기여를 시작하기 위한 [[Special:FirstSteps|특수 문서]]',
	'translate-fs-pagetitle-done' => '- 완료!',
	'translate-fs-pagetitle-pending' => '- 처리 대기 중',
	'translate-fs-pagetitle' => '시작 마법사 - $1',
	'translate-fs-signup-title' => '가입하기',
	'translate-fs-settings-title' => '환경 설정',
	'translate-fs-userpage-title' => '사용자 문서 만들기',
	'translate-fs-permissions-title' => '번역자 권한 신청',
	'translate-fs-target-title' => '번역 시작하기!',
	'translate-fs-email-title' => '이메일 주소 확인하기',
	'translate-fs-intro' => "{{SITENAME}} 첫걸음 마법사에 오신 것을 환영합니다.
번역자가 되는 과정을 차례로 거칠 것입니다.
결국에는 {{SITENAME}}에서 지원하는 모든 프로젝트의 ''인터페이스 메시지''를 번역할 수 있을 것입니다.",
	'translate-fs-selectlanguage' => '언어 선택',
	'translate-fs-settings-planguage' => '모어:',
	'translate-fs-settings-planguage-desc' => '모어는 여기 웹사이트에서 인터페이스 언어이자
번역할 때 기본 도착어가 됩니다.',
	'translate-fs-settings-slanguage' => '보조 언어 $1:',
	'translate-fs-settings-slanguage-desc' => '번역 편집기에서 다른 언어로 된 메시지의 번역을 나타낼 수도 있습니다.
여기서 보고 싶은 언어를 선택할 수 있습니다.',
	'translate-fs-settings-submit' => '환경 설정 저장하기',
	'translate-fs-userpage-level-N' => '이 언어는 내 모어입니다.',
	'translate-fs-userpage-level-5' => '나는 전문 번역가입니다.',
	'translate-fs-userpage-level-4' => '나는 이 언어를 모어 수준으로 압니다.',
	'translate-fs-userpage-level-3' => '나는 이 언어를 잘 구사합니다.',
	'translate-fs-userpage-level-2' => '저는 이 언어를 보통 수준으로 구사합니다.',
	'translate-fs-userpage-level-1' => '나는 이 언어를 거의 모릅니다.',
	'translate-fs-userpage-help' => '자신의 언어 능력을 밝히고 자신을 소개하세요. 언어를 여섯 가지 이상 안다면 나중에 첨가할 수 있습니다.',
	'translate-fs-userpage-submit' => '내 사용자 문서 만들기',
	'translate-fs-userpage-done' => '잘 했습니다! 이제 내 사용자 문서가 생겼습니다.',
	'translate-fs-permissions-planguage' => '모어:',
	'translate-fs-permissions-help' => '지금 번역자 그룹에 추가되도록 요청할 필요가 있습니다.
번역 모어를 선택하세요.

아래 상자에서 다른 언어 및 의견을 말할 수 있습니다.',
	'translate-fs-permissions-pending' => '요청이 [[$1]]로 제출되었으며 여기 웹사이트 직원이 되도록 빨리 그 요청을 검토할 것입니다.
이메일 주소를 인증하면 요청 결과를 이메일로 받아볼 것입니다.',
	'translate-fs-permissions-submit' => '요청 제출',
	'translate-fs-target-text' => '축하합니다!
지금부터 번역할 수 있습니다.

아직 낯설고 혼란스러울지라도 두려워하지 마세요.
[[Project list]]에서 기여할 수 있는 프로젝트의 개요를 보세요. 
프로젝트 대부분에는 "\'\'이 프로젝트를 번역하라\'\'"는 링크와 함께 간략한 설명이 있습니다. 그 링크를 클릭하면 미번역된 모든 메시지가 나타날 것입니다.
[[Special:LanguageStats|언어 번역 공정]]이 있는 모든 메시지 그룹 목록도 이용할 수 있습니다.

번역하기 전에 더 이해할 필요가 있다고 생각한다면  [[FAQ|자주 묻는 질문]]을 읽으세요.
불행하게도 문서는 종종 더 이상 유용하지 않을 수 있습니다. 
할 수 있어야 한다고 생각하는 무엇이 있지만 어떻게 할 수 있을지 찾을 수 없다면 [[Support|지원 문서]]에 문의하세요.

[[Portal:$1|언어 들머리]] [[Portal_talk:$1|토론 문서]]에서 자신과 같은 언어의 번역자와 연락할 수도 있습니다. 
아직 바꾸지 않았다면 [[Special:Preferences|자신의 언어 인터페이스를 번역 도착어로 바꾸세요]]. 그러면 웹사이트에서 가장 관련있는 링크를 보여줄 수 있습니다.',
	'translate-fs-email-text' => '[[Special:Preferences|환경 설정]]에서 이메일 주소를 등록하고 그 계정으로 온 이메일을 확인하세요.

다른 사용자가 이메일로 연락할 수 있게 됩니다.
보통 한 달에 한 번 발송하는 소식지도 받을 것입니다.
소식지를 받고 싶지 않으면 [[Special:Preferences|환경 설정]] "{{int:prefs-personal}}" 탭에서 선택 해제하세요.',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'firststeps' => 'Eetste Schredde',
	'firststeps-desc' => '[[Special:FirstSteps|Extra Sigg]] för Metmaacher op Wikis met däm Zohsazprojramm <i lang="en">Translate</i> aan et werke ze krijje.',
	'translate-fs-pagetitle-done' => ' - jedonn!',
	'translate-fs-pagetitle-pending' => ' - noch nit jedonn',
	'translate-fs-pagetitle' => 'En de Jäng kumme - $1',
	'translate-fs-signup-title' => 'Aanmälde',
	'translate-fs-settings-title' => 'Enstellunge maache',
	'translate-fs-userpage-title' => 'Metmaachersigg aanlääje',
	'translate-fs-permissions-title' => 'Noh dem Rääsch als {{int:Group-translator-member}} froore',
	'translate-fs-target-title' => 'Loßlääje mem Övversäze!',
	'translate-fs-email-title' => 'De <i lang="en">e-mail</i> Adräß beschtääteje',
	'translate-fs-intro' => 'Wellkumme bei {{GRAMMAR:Genitiv ier|{{SITENAME}}}} Hölp bei de eetsde Schredde för neu Metmaacher.
Heh kreß De Schrett för Schrett jesaat, wi De ene Övversäzer weeß.
Aam Engk kanns De de Täxte un Nohreeschte uß alle Projäkte övversäze, di {{GRAMMAR:em Dativ|{{SITENAME}}}} ongerstöz wääde.',
	'translate-fs-selectlanguage' => 'Söhg en Shprooch uß',
	'translate-fs-settings-planguage' => 'Houpshprooch:',
	'translate-fs-settings-planguage-desc' => 'De Houpshprooch is och di Schprooch, die heh dat Wiki met Der kallt, un des es och Ding shtandattmääßeje Shprooch för dren ze övversäze.',
	'translate-fs-settings-slanguage' => 'Zohsäzlejje Shprooch Nommer $1:',
	'translate-fs-settings-slanguage-desc' => 'Et es müjjelesch, sesch Övversäzonge en ander Schprooche beim sellver Övversäze aanzeije ze lohße. Söhg uß, wat för esu en Shprooche De ze sinn krijje wells, wann De övverhoup wälsche han wells.',
	'translate-fs-settings-submit' => 'Enstellunge faßhallde',
	'translate-fs-userpage-level-N' => 'Ming Mottershprooch es:',
	'translate-fs-userpage-level-5' => 'Esch ben ene beroofsmääßeje Övversäzer vun:',
	'translate-fs-userpage-level-4' => 'Esch kennen mesch esu jood uß, wi wann et ming Motterschprooch wöhr, met:',
	'translate-fs-userpage-level-3' => 'Esch kann joot ömjonn met dä Schprooch:',
	'translate-fs-userpage-level-2' => 'Esch kann di Schprooch meddelmääßesch:',
	'translate-fs-userpage-level-1' => 'Esch kann e beßje vun dä Schprooch:',
	'translate-fs-userpage-help' => 'Jiv Ding Shprooche aan, un sach ons jät övvr Desch. Wann De mieh wi fönnef Schprooche kanns, kanns De di schpääder emmer noch derbei donn.',
	'translate-fs-userpage-submit' => 'Don en Metmaachersigg för Desch aanlääje',
	'translate-fs-userpage-done' => 'Joot jemaat! Jäz häs De en Metmaachersigg.',
	'translate-fs-permissions-planguage' => 'Ding Houpshprooch:',
	'translate-fs-permissions-help' => 'Jäz moß De en Aanfrooch loßlohße, öm en de Övversäzer-Jropp ze kumme.
Donn Ding Houpschprooch aanjävve, woh De et miehts noh övversäze wells.

Do kanns natöörlesch ander Schprooche un wat De söns noch saare wells en dä Kaßte heh endraare.',
	'translate-fs-permissions-pending' => 'Ding Aanfrooch es jäz noh [[$1]] övvermeddelt, un eine vun de {{int:group-staff/ksh}}
weed sesch esu flöck, wi_t jeiht, dröm kömmere.
Wann De Ding Addräß för de <i lang="en">e-mail<i> beschtäätesch häs, kriß De en Nohreesch drövver, wann ed esu wigg es.',
	'translate-fs-permissions-submit' => 'Lohß Jonn!',
	'translate-fs-target-text' => 'Onse Jlöckwonsch!
Jez kanns De et Övversäze aanfange

Lohß Desch nit jeck maache, wann dat eets ens jet fresch un onövversseshlesh schingk.
Op dä Sigg [[Project list|met dä Leß met de Projäkte]] kanns Der enne Övverbleck holle, woh De jäz övverall jet zoh beidraare kanns met Dinge Övversäzonge.
De miehßte Projäkte han en koote Sigg övver dat Projäk, woh ene Lengk „Translate this project<!--{{int:xxxxxxxxxxx}}-->“ drop es. Dä brengk Desh op en Leß met alle Täxte un Nohreeschte för dat Projäk, di noch nit övversaz sin.
Et jitt och en [[Special:LanguageStats|Leß met alle Jroppe vun Täxte un Nohreeshte un de Zahle dohzoh]].

Wann De meins, dat De noch jät mieh wesse mööts, ih dat De mem Övversäze aanfängks, jangk en de [[FAQ|öff jeshtallte Froore]] dorsh.
Onjlöcklesch es, uns Dokemäntazjuhn künnt ald ens övverhollt sin.
Wann De jät donn wells, wat De nit esu eifach henkriß wie et sin sullt, dann bes nit bang un donn op dä Sigg „[[Support|{{int:bw-mainpage-support-title}}]]“ donoh froore.

Do kanns och met Dinge Kolleje, di de sellve Shprooche övversäze wi Do, övver de [[Portal:$1|Pooz-Sigg för Ding Shprooch]] ier [[Portal_talk:$1|Klaafsigg]] zosamme kumme.
Wann De dat noch nit jedonn häs, [[Special:Preferences|donn Ding Shprooch för de Bovverflääsch vum Wiki op di Shprooch ensthälle, woh de noh övversäze wells]], domet et wiki Der de beß zopaß Lengks automattesch aanzeije kann.',
	'translate-fs-email-text' => 'Bes esu joot un jiff Ding Adräß för de <i lang="en">e-mail</i> en [[Special:Preferences|Dinge Enstellunge]]
aan, un dun se beschtäätejje. Doför es ene Lengk udder en <i lang="en">URL</i> en dä <i lang="en">e-mail</i>
an Desh dren.

Dat määd et müjjelesch, dat ander Metmaacher Dir en <i lang="en">e-mail</i> schecke künne.
Do kriß och Neueschkeite vum Wiki zohjescheck, esu ätwa eijmohl em Mohnd.
Wann De dat nit han wells, kanns De et onger „{{int:prefs-personal}}“ en [[Special:Preferences|Dinge Enstellunge]] afschallde.',
);

/** Kurdish (Latin script) (Kurdî (latînî)‎)
 * @author George Animal
 * @author Gomada
 */
$messages['ku-latn'] = array(
	'firststeps' => 'Gavên yekem',
	'translate-fs-pagetitle-done' => '- çêbû!',
	'translate-fs-target-title' => 'Bi wergerê dest pê bike!',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Les Meloures
 * @author Robby
 * @author Soued031
 */
$messages['lb'] = array(
	'firststeps' => 'Éischt Schrëtt',
	'firststeps-desc' => "[[Special:FirstSteps|Spezialsäit]] fir datt Benotzer besser ukommen fir d'Erweiderung 'Translate' op enger Wiki ze benotzen",
	'translate-fs-pagetitle-done' => ' - fäerdeg!',
	'translate-fs-pagetitle-pending' => '- am gaang',
	'translate-fs-pagetitle' => 'Assistent fir unzefänken - $1',
	'translate-fs-signup-title' => 'Schreift Iech an',
	'translate-fs-settings-title' => 'Är Astellunge festleeën',
	'translate-fs-userpage-title' => 'Maacht Är Benotzersäit',
	'translate-fs-permissions-title' => 'Iwwersetzerrechter ufroen',
	'translate-fs-target-title' => 'Ufänke mat iwwersetzen!',
	'translate-fs-email-title' => 'Confirméiert är E-Mail-Adress',
	'translate-fs-intro' => "Wëllkomm beim {{SITENAME}}-Startassistent.
Iech gëtt gewisen, Déi Dir Schrëtt fir Schrëtt zum Iwwersetzer gitt.
Um Schluss kënnt Dir all ''Interface-Messagen'' vun de vun {{SITENAME}} ënnerstetzte Projeten iwwersetzen.",
	'translate-fs-selectlanguage' => 'Eng Sprooch eraussichen',
	'translate-fs-settings-planguage' => 'Haaptsprooch:',
	'translate-fs-settings-planguage-desc' => "Déi éischt Sprooch ass gläichzäiteg d'Sprooch vun Ärem Interface op dëser Wiki
an d'Standard-Zilsprooch fir Iwwersetzungen.",
	'translate-fs-settings-slanguage' => 'Ënnerstetzungs-Sprooch $1:',
	'translate-fs-settings-slanguage-desc' => 'Et ass méiglech fir Iwwersetzunge vu Messagen an anere Sproochen am Iwwersetzungsediteur ze weisen.
Hei kënnt Dir eraussiche wat fir eng Sprooch, wann Dir dat wëllt, Dir gesi wëllt.',
	'translate-fs-settings-submit' => 'Astellunge späicheren',
	'translate-fs-userpage-level-N' => 'Meng Mamme sprooch ass',
	'translate-fs-userpage-level-5' => 'Ech sinn e professionellen Iwwersetzer vu(n)',
	'translate-fs-userpage-level-4' => 'Ech kenne se wéi wann et meng Mammesprooch wier',
	'translate-fs-userpage-level-3' => 'Ech ka mech gutt ausdrécken op',
	'translate-fs-userpage-level-2' => 'Ech hu mëttelméisseg Kenntnisser vu(n)',
	'translate-fs-userpage-level-1' => 'Ech kann a bëssen',
	'translate-fs-userpage-help' => 'Gitt w.e.g. Är Sproochkenntnisser un an erzielt eppes iwwer Iech. Wann Dir méi wéi fënnef Sprooche kënnt da kënnt Dir déi méi spéit derbäisetzen.',
	'translate-fs-userpage-submit' => 'Är Benotzersäit maachen',
	'translate-fs-userpage-done' => 'Gutt gemaach! dir hutt elo eng Benotzersäit.',
	'translate-fs-permissions-planguage' => 'Haaptsprooch:',
	'translate-fs-permissions-help' => "Elo musst Dir eng Ufro maache fir an de Grupp vun den Iwwersetzer derbäigesat ze ginn.
Sicht Är Haaptsprooch eraus an déi Dir iwwersetze wäert.

Dir kënnt aner Sproochen an aner Bemierkungen an d'Textkëscht ënnendrënner derbäisetzen.",
	'translate-fs-permissions-pending' => 'Är Ufro gouf op [[$1]] gespäichert an e vun de Mataarbechter vum Site wäert dat esou séier wéi méiglech nokucken. Wann Dir Är Mail-Adress confirméiert, da kritt Dir eng Confirmatioun esou bal wéi dat gemaaach ass.',
	'translate-fs-permissions-submit' => 'Ufro schécken',
	'translate-fs-target-text' => "Felicitatiounen!
Dir kënnt elo ufänke mat iwwersetzen.

Maacht Iech näischt doraus wann dat am Ufank fir Iech nach e komescht Gefill ass.
Op [[Project list]] gëtt et eng Iwwersiicht vu Projeten bäi deenen Dir hëllefe kënnt z'iwwersetzen.
Déi meescht Projeten hunn eng kuerz Beschreiwungssäit mat engem \"''Iwwersetz dës e Projet''\" Link, deen Iech op eng Säit op där all net iwwersate Messagen dropstinn.
Eng Lëscht mat alle Gruppe vu Messagen mat dem [[Special:LanguageStats|aktuellen Iwwersetzungsstatus fir eng Sprooch]] gëtt et och.

Wann dir mengt Dir sollt méi verstoen ier Dir ufänkt mat Iwwersetzen, kënnt Dir déi [[FAQ|dacks gestallte Froe]] liesen.
Onglécklecherweis kann et virkommen datt d'Dokumentatioun heiansdo net à jour ass.
Wann et eppes gëtt vun deem Dir mengt datt Dir e maache kënnt, awer Dir fannt net eraus wéi, dann zéckt net fir eis op der [[Support|Support-Säit]] ze froen.

Dir kënnt och aner Iwwersetzer vun der selwechter Sprooch op der [[Portal_talk:\$1|Diskussiounssäit]] vun [[Portal:\$1|Ärem Sproocheportal]] kontaktéieren. Wann dir et net scho gemaach hutt, [[Special:Preferences|ännert d'Sprooch vum Interface an déi Sprooch an déi Dir iwwersetze wëllt]], esou datt d'Wiki Iech déi wichtegst Linke weise kann.",
	'translate-fs-email-text' => 'Gitt w.e.g. Är E-Mail-Adress an [[Special:Preferences|Ären Astellungen]] un a confirméiert se vun der E-Mail aus déi Dir geschéckt kritt.

Dat erlaabt et anere Benotzer fir Iech per Mail ze kontaktéieren.
Dir kritt och Newsletteren awer héchstens eng pro Mount.
Wann Dir keng Newslettere kréie wëllt, da kënnt Dir dat am Tab "{{int:prefs-personal}}"  vun Ären [[Special:Preferences|Astellungen]] ausschalten.',
);

/** Lithuanian (lietuvių)
 * @author Mantak111
 */
$messages['lt'] = array(
	'firststeps' => 'Pirmieji žingsniai',
	'translate-fs-pagetitle-done' => '- baigta!',
	'translate-fs-pagetitle-pending' => '- laukiama',
	'translate-fs-pagetitle' => 'Pradžios vedlys - $1',
	'translate-fs-signup-title' => 'Užsiregistruoti',
	'translate-fs-settings-title' => 'Konfigūruokite savo parinktys',
	'translate-fs-userpage-title' => 'Susikurkite savo naudotojo puslapį',
	'translate-fs-permissions-title' => 'Prašykite vertėjo leidimus',
	'translate-fs-target-title' => 'Pradėkite versti!',
	'translate-fs-email-title' => 'Patvirtinkite savo elektroninio pašto adresą',
	'translate-fs-intro' => 'Sveiki atvykę į {{SITENAME}} pirmų žingsnių vedlį.
Jums bus vadovaujamasi taikant tapti vertėju žingsnį po žingsnio.
Galų gale jūs galėsite versti sąsajos pranešimus visų remiamų projektų {{SITENAME}} svetainėje.',
	'translate-fs-selectlanguage' => 'Pasirinkite kalbą',
	'translate-fs-settings-planguage' => 'Pagrindinė kalba:',
	'translate-fs-settings-slanguage' => 'Asistentės kalba $1:',
	'translate-fs-settings-submit' => 'Išsaugoti nustatymus',
	'translate-fs-userpage-level-N' => 'Mano gimtoji kalba yra',
	'translate-fs-userpage-level-5' => 'Aš esu profesionalus vertėjas',
	'translate-fs-userpage-level-4' => 'Aš žinau kaip gimtąją kalbą',
	'translate-fs-userpage-level-3' => 'Turiu gerą komandą',
	'translate-fs-userpage-level-2' => 'Turiu vidutinį komandą',
	'translate-fs-userpage-level-1' => 'Aš žinau šiek tiek',
	'translate-fs-userpage-help' => 'Nurodykite savo kalbos įgūdžius ir papasakokite ką nors apie save. Jei žinote daugiau nei penkias kalbas, jūs galite pridėti daugiau vėliau.',
	'translate-fs-userpage-submit' => 'Sukurkite savo naudotojo puslapį',
	'translate-fs-userpage-done' => 'Gerai atlikai! Dabar jūs turite naudotojo puslapį.',
	'translate-fs-permissions-planguage' => 'Pagrindinė kalba:',
	'translate-fs-permissions-help' => 'Dabar jums reikia pateikti prašymą, kad būtumėte priskirtas Vertėjų grupei.
Pasirinkite pagrindinę kalbą kurią norėtumėte išversti į.

Galima paminėti kitas kalbas ir kitas pastabas laukelyje žemiau.',
	'translate-fs-permissions-pending' => 'Jūsų prašymas buvo pateiktas [[$1]] ir kažkas iš svetainės darbuotojų patikrins tai kuo skubiau.
Jei patvirtinsite savo elektroninio pašto adresą, jūs gausite pranešimą elektroniniu paštu, kai tik tai atsitiks.',
	'translate-fs-permissions-submit' => 'Siųsti prašymą',
);

/** Latvian (latviešu)
 * @author Papuass
 */
$messages['lv'] = array(
	'firststeps' => 'Pirmie soļi',
	'translate-fs-target-title' => 'Sāciet tulkot!',
	'translate-fs-email-title' => 'Apstipriniet savu e-pasta adresi',
	'translate-fs-selectlanguage' => 'Izvēlēties valodu',
	'translate-fs-settings-planguage' => 'Galvenā valoda:',
	'translate-fs-settings-submit' => 'Saglabāt izvēles',
	'translate-fs-userpage-submit' => 'Izveidojiet savu lietotāja lapu',
);

/** Literary Chinese (文言)
 * @author Yanteng3
 */
$messages['lzh'] = array(
	'translate-fs-email-title' => '惠考郵驛',
	'translate-fs-settings-submit' => '存註',
);

/** Malagasy (Malagasy)
 * @author Jagwar
 */
$messages['mg'] = array(
	'firststeps' => 'Dingana voalohany',
	'firststeps-desc' => '[[Special:FirstSteps|Pejy manokana]] hanoroana ireo mpikambana miasa eo amina wiki mampiasa ny fanitarana Translate',
	'translate-fs-pagetitle-done' => '- vita!',
	'translate-fs-pagetitle-pending' => '- mbola ampanaovana',
	'translate-fs-pagetitle' => "Mpanampy amin'ny fanombohana - $1",
	'translate-fs-signup-title' => 'Misorata anarana',
	'translate-fs-settings-title' => 'Ovay ny safidinao',
	'translate-fs-userpage-title' => 'Forony ny pejim-pikambanao',
	'translate-fs-permissions-title' => 'Angataho ny sata mpandika teny',
	'translate-fs-target-title' => 'Atombohy ny fandikana!',
	'translate-fs-email-title' => 'Hamarino ny adiresy imailakao',
	'translate-fs-intro' => "Tonga soa eto amin'ny mpanampin'i {{SITENAME}} manao ny dingana voalohany.
Ho ampian'ity mpanampy ity ianao mba hahazoanao ny satan'ny mpandika teny.
Amin'ny farany ianao dia hafaka mandika ny \"hafatra asehon'ny tranonkala\" eo amin'ny tetikasa rehetra izay zakan'i {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Misafidia fiteny iray',
	'translate-fs-settings-planguage' => 'Fiteny voalohany:',
	'translate-fs-settings-planguage-desc' => "Ny fiteny voalohany no fitenin'ny ahehoana ny hafatra eto amin'ity wiki ity
ary natao ho fiteny dikaina raha tsy misy zavatra hafa voalaza.",
	'translate-fs-settings-slanguage' => 'Fiteny fanampiana $1:',
	'translate-fs-settings-slanguage-desc' => "Azo atao ny maneho ny dikan-tenin'ny hafatra amin'ny fiteny hafa ao amin'ny mpanova dikan-teny.
Eto ianao afaka mifidy fiteny izay tianao haseho.",
	'translate-fs-settings-submit' => 'Tahirizina ny safidy',
	'translate-fs-userpage-level-N' => "Izaho dia lehibe tamin'ny fiteny",
	'translate-fs-userpage-level-5' => "Izaho dia mpandika teny arak'asa ny teny",
	'translate-fs-userpage-level-4' => "Mahalala sahala ireo lehibe tamin'ny fiteny aho",
	'translate-fs-userpage-level-3' => 'Mahalala tsara ny',
	'translate-fs-userpage-level-2' => "Manana lenta antonontonony amin'ny",
	'translate-fs-userpage-level-1' => 'Mahay kely ny',
	'translate-fs-userpage-help' => "Lazao eto ambany ny mombamomba anao ary ny famehezanao ny fiteny. Raha mahery ny dimy ny isan'ny fiteny hainao tenenina, azonao ampiana rehefa avy eo izy ireo.", # Fuzzy
	'translate-fs-userpage-submit' => 'Hamorona ny pejin-pikambako', # Fuzzy
	'translate-fs-userpage-done' => "Manana pejim-pikambana amin'izay ianao.",
	'translate-fs-permissions-planguage' => 'Fiteny voalohany:',
	'translate-fs-permissions-help' => "Izao ianao mila mangataka mba hanampiana anao any amina vondrom-pandikan-teny.
Safidio ny fiteny voalohany izay hodikainao.

Azonao atao ny milaza fiteny ary hevitra hafa eo amin'ilay fampiritan-tsoratra eo ambany.",
	'translate-fs-permissions-pending' => "Nalefa tany amin'i [[$1]] ny hatakao ary homarinan'ny olona ao amin'ny staff ny tranonkala izy io.
Raha efa marinanao ny adiresy imailakao, dia hahazo imailaka fampahafantarana rehefa mitranga izay fanamarinana izay",
	'translate-fs-permissions-submit' => 'Hangataka',
	'translate-fs-target-text' => "Arahabaina!
Efa afaka mandika teny amin'izay ianao.

Aza matahotra raha somary vaovao na mahafanimpanina.
Eo amin'ny [[Project list|lisitry ny tetikasa]] no ahitanao ny tetikasa rehetra azonao andraisana anjara.

Manana famisavisana fohifohy ireo tetikasa ireo, miaraka amin'ilay rohy \"dikao teny\" izay hitondra anao any amina pejy ahitana ny hafatra rehetra izay tsy mbola voadika teny.
Azo vangiana ihany koa ny lisitry ny vondron-kafatra miaraka amin'ny [[Special:LanguageStats|sata ankehitrinin'ny dikan-teny amin'ny fiteny iray]].

Raha mila fampahalalana fanampiny ianao, dia jereo ny [[FAQ|Fanontaniana Apetraka Matetika]].
Fa mety lany daty ihany ny toromarika tsindraindray.
Raha misy zavatra heverinao fa afaka ataonao, fa tsy hainao hoe ahoaa, dia manontania ao amin'ny [[Support|pejy fanohanana]].

Azonao atao ihany koa ny mifandray amin'ny mpndika teny miteny ny fiteninnao eo amin'ny [[Portal_talk:\$1|pejin-dresaky]] ny [[Portal:\$1|vahavadin'ny fiteninao]].
Raha mbola tsy efan nanao izany ianao, [[Special:Preferences|Ovay ny fitenin'ny hafatra miseho amin'ny fiteny tianao dikaina]], mba hahafahan'ilay wiki maneho anao ny rohy ilainao indrindra.",
	'translate-fs-email-text' => "Omeo ny adiresy mailakao ao amin'ny [[Special:Preferences|safidy]] ary manamarina fa tena nalefa ho anao ilay izy.

Hahafahan'ny mpikambana hafa mifandray aminao amin'ny alalan'ny mailaka izany.
Hahazo vaovao indray isam-bolana ianao farafahabetsany.
Raha tsy tia hahazo vaovao ianao, dia azonao atao ny miala amin'izany ao amin'i \"{{int:prefs-personal}}\" ny [[Special:Preferences|safidinao]].",
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'firststeps' => 'Први чекори',
	'firststeps-desc' => '[[Special:FirstSteps|Специјална страница]] за помош со првите чекори на вики што го користи додатокот Преведување (Translate)',
	'translate-fs-pagetitle-done' => '- завршено!',
	'translate-fs-pagetitle-pending' => ' — во исчекување',
	'translate-fs-pagetitle' => 'Помошник „Како да започнете“ - $1',
	'translate-fs-signup-title' => 'Регистрација',
	'translate-fs-settings-title' => 'Поставете ги вашите нагодувања',
	'translate-fs-userpage-title' => 'Создајте своја корисничка страница',
	'translate-fs-permissions-title' => 'Барање на дозвола за преведување',
	'translate-fs-target-title' => 'Почнете со преведување!',
	'translate-fs-email-title' => 'Потврдете ја вашата е-пошта',
	'translate-fs-intro' => "Добредојдовте на помошникот за први чекори на {{SITENAME}}.
Овој помошник постепено ќе води низ постапката за станување преведувач.
Потоа ќе можете да преведувате ''посреднички (interface) пораки'' за сите поддржани проекти на {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Одберете јазик',
	'translate-fs-settings-planguage' => 'Главен јазик',
	'translate-fs-settings-planguage-desc' => 'Главниот јазик е вашиот јазик на посредникот на ова вики,
а воедно и стандарден целен јазик за преводите.',
	'translate-fs-settings-slanguage' => 'Помошен јазик $1:',
	'translate-fs-settings-slanguage-desc' => 'Додека преведувате, во уредникот можат да се прикажуваат преводи од други јазици.
Доколку сакате да ја користите функцијава, тука можете да одберете кои јазици да ви се прикажуваат.',
	'translate-fs-settings-submit' => 'Зачувај нагодувања',
	'translate-fs-userpage-level-N' => 'Мајчин јазик ми е',
	'translate-fs-userpage-level-5' => 'Стручно преведувам на',
	'translate-fs-userpage-level-4' => 'Го владеам како мајчин',
	'translate-fs-userpage-level-3' => 'Добро владеам',
	'translate-fs-userpage-level-2' => 'Умерено го владеам',
	'translate-fs-userpage-level-1' => 'Знам по малку',
	'translate-fs-userpage-help' => 'Тука наведете кои јазици ги познавате и колку добро го владеете секој од нив. Воедно напишете и нешто за себе. Доколку знаете повеќе од пет јазика, останатите додајте ги подоцна.',
	'translate-fs-userpage-submit' => 'Создајте своја корисничка страница',
	'translate-fs-userpage-done' => 'Одлично! Сега имате корисничка страница.',
	'translate-fs-permissions-planguage' => 'Главен јазик:',
	'translate-fs-permissions-help' => 'Сега ќе треба да поставите барање за да ве додадеме во групата на преведувачи.
Одберете го главниот јазик на кој ќе преведувате.

Во полето за текст подолу можете да споменете други јазици и да напишете забелешки.',
	'translate-fs-permissions-pending' => 'Вашето барање е поднесено на [[$1]] и ќе разгледано во најкус можен рок.
Доколку ја потврдите вашата е-пошта, тогаш известувањето ќе го добиете таму.',
	'translate-fs-permissions-submit' => 'Испрати барање',
	'translate-fs-target-text' => "Честитаме!
Сега можете да почнете со преведување.

Не плашете се ако сето ова сè уште ви изгледа ново и збунително.
[[Project list|Списокот на проекти]] дава преглед на проектите каде можете да придонесувате со ваши преводи.
Највеќето проекти имаат страница со краток опис и врска „''Преведи го проектов''“, која ќе ве одвете до страница со сите непреведени пораки за тој проект.
Има и список на сите групи на пораки со [[Special:LanguageStats|тековниот статус на преведеност за даден јазик]].

Ако мислите дека треба да осознаете повеќе пред да почнете со преведување, тогаш прочитајте ги [[FAQ|често поставуваните прашања]].
Нажалост документацијата напати знае да биде застарена.
Ако има нешто што мислите дека би требало да можете да го правите, но не можете да дознаете како, најслободно поставете го прашањето на [[Support|страницата за поддршка]].

Можете и да се обратите кај вашите колеги што преведуваат на истиот јазик на [[Portal_talk:$1|страницата за разговор]] на [[Portal:$1|вашиот јазичен портал]].
Ако ова веќе го имате сторено, тогаш [[Special:Preferences|наместете го јазикот на посредникот на оној на којшто сакате да преведувате]], и така викито ќе ви ги прикажува врските што се однесуваат на вас.",
	'translate-fs-email-text' => 'Наведете ја вашата е-пошта во [[Special:Preferences|нагодувањата]] и потврдете ја преку пораката испратена на неа.

Ова им овозможува на корисниците да ве контактираат преку е-пошта.
На таа адреса ќе добивате и билтени со новости, највеќе еднаш месечно.
Ако не сакате да добиват билтени, можете да се отпишете преку јазичето „{{int:prefs-personal}}“ во вашите [[Special:Preferences|нагодувања]].',
);

/** Malayalam (മലയാളം)
 * @author Praveenp
 * @author Santhosh.thottingal
 */
$messages['ml'] = array(
	'firststeps' => 'ആദ്യ ചുവടുകൾ',
	'translate-fs-pagetitle-done' => '- ചെയ്തു കഴിഞ്ഞു!',
	'translate-fs-pagetitle' => 'ചുവടുവെക്കാനൊരു സഹായി -$1',
	'translate-fs-signup-title' => 'അംഗത്വമെടുക്കുക',
	'translate-fs-settings-title' => 'താങ്കളുടെ ഐച്ഛികങ്ങൾ ക്രമീകരിക്കുക',
	'translate-fs-userpage-title' => 'താങ്കളുടെ ഉപയോക്തൃ താൾ സൃഷ്ടിക്കുക',
	'translate-fs-permissions-title' => 'തർജ്ജമയ്ക്കുള്ള അനുമതി ആവശ്യപ്പെടുക',
	'translate-fs-target-title' => 'പരിഭാഷപ്പെടുത്തൽ തുടങ്ങുക!',
	'translate-fs-email-title' => 'ഇമെയിൽ വിലാസം സ്ഥിരീകരിക്കുക',
	'translate-fs-intro' => "{{SITENAME}} ആദ്യചുവടുകൾ സഹായത്തിലേയ്ക്ക് സ്വാഗതം.
പരിഭാഷക(ൻ) പദവിയിലേക്ക് ഘട്ടം ഘട്ടമായി എത്താനുള്ള വഴികാട്ടിയാണിത്.
അവസാനം {{SITENAME}} സംരംഭത്തിൽ പിന്തുണയുള്ള എല്ലാ പദ്ധതികളുടെയും ''സമ്പർക്കമുഖ സന്ദേശങ്ങൾ'' പരിഭാഷപ്പെടുത്താൻ താങ്കൾക്ക് സാധിച്ചിരിക്കും.",
	'translate-fs-selectlanguage' => 'ഭാഷ തിരഞ്ഞെടുക്കുക',
	'translate-fs-settings-planguage' => 'പ്രാഥമികഭാഷ:',
	'translate-fs-settings-planguage-desc' => 'പ്രാഥമിക ഭാഷ നിങ്ങളുടെ സമ്പർക്കമുഖ ഭാഷയായും പരിഭാഷയ്ക്കുള്ള ഭാഷയായും മാറുന്നു.',
	'translate-fs-settings-slanguage' => 'സഹായകഭാഷ $1:',
	'translate-fs-settings-submit' => 'ക്രമീകരണങ്ങൾ ഓർത്തുവെയ്ക്കുക',
	'translate-fs-userpage-submit' => 'താങ്കളുടെ ഉപയോക്തൃ താൾ സൃഷ്ടിക്കുക',
	'translate-fs-userpage-done' => 'കൊള്ളാം! താങ്കൾക്കിപ്പോൾ ഒരു ഉപയോക്തൃതാൾ ഉണ്ട്.',
	'translate-fs-permissions-planguage' => 'പ്രാഥമികഭാഷ:',
	'translate-fs-permissions-submit' => 'അഭ്യർത്ഥന അയയ്ക്കുക',
);

/** Marathi (मराठी)
 * @author Htt
 * @author Shantanoo
 * @author Shubhamlanke
 */
$messages['mr'] = array(
	'firststeps' => 'पहिल्या पायर्‍या',
	'firststeps-desc' => '[[महत्त्वाचे; पहिली पायरी महत्त्वाचे पान]] भाषांतर विस्तार वापरून सुरु केलेल्या युजर्सना मिळण्यासाठी .', # Fuzzy
	'translate-fs-pagetitle-done' => ' - झाले!',
	'translate-fs-pagetitle-pending' => 'अनिर्णीत,राहिलेले,',
	'translate-fs-pagetitle' => 'सुरु झालेले विझार्ड मिळण्यासाठी ‌-$१', # Fuzzy
	'translate-fs-signup-title' => 'करार करणे.',
	'translate-fs-userpage-title' => 'माझे सदस्यपान तयार करा.',
	'translate-fs-permissions-title' => 'भाषांतर करण्याची परवानगी मिळण्यासाठी विनंती करा. (भाषांतर करणाऱ्या व्यक्तीस)',
	'translate-fs-target-title' => 'भाषांतरास सुरुवात करा!',
	'translate-fs-email-title' => 'आपला ई-मेल पत्ता पडताळून पहा.',
	'translate-fs-intro' => '{{साइटचे नाव}} साइटवर तुमचे स्वागत आहे पहिली पायरी
योग्य भाषांतकार होण्याच्या प्रक्रियेद्वारे तुम्हाला क्रमा-क्रमाने मार्गदर्शन केले जाईल.
शेवटी तुम्ही ह्या साईटवर  {{साइटचे नाव}} उपलब्ध  असलेल्या सर्व प्रकल्प ईंटरफेस संदेशांचे भाषांतर करण्यास लायकवान बनाल.',
	'translate-fs-selectlanguage' => '(योग्य) भाषा निवडा.',
	'translate-fs-settings-planguage' => 'मुख्य(महत्त्वाची) भाषा निवडा.',
	'translate-fs-settings-planguage-desc' => 'तुमची मुख्य भाषा ही विकीवर तुमची दुवा साधणारी भाषा आणि भाषांतरासाठी दिफॉल्ट भाषा म्हणुन वापरली जाते.',
	'translate-fs-settings-slanguage' => 'उप‌-भाषा $१:', # Fuzzy
	'translate-fs-settings-slanguage-desc' => 'भाषांतर एडिटर मध्ये संदेशाचे  दुसऱ्या भाषेमध्ये भाषांतर सहज शक्य आहे.
जर तुम्हाला एखादी भाषा पाहण्यासाठी आवडेल; तर इथे तुम्ही ती भाषा निवडू शकता.',
	'translate-fs-settings-submit' => 'माझ्या पसंती जतन करा.',
	'translate-fs-userpage-level-N' => 'मी जन्मतः (..........)(एखादी भाषा)  बोलतो.',
	'translate-fs-userpage-level-5' => 'मी( ..........)(एखाद्या भाषेचे दुसऱ्या भाषेत रुपांतर)व्यवसायिक भाषांतरकार आहे',
	'translate-fs-userpage-level-4' => 'मी त्या (भाषेला)माझ्या मूळ बोलीभाषे एवढा जाणतो.
उदा. एखादी भाषा,गोष्ट',
	'translate-fs-userpage-level-3' => 'माझी त्या ...... चांगली पकड(कौशल्य) आहे.',
	'translate-fs-userpage-level-2' => 'माझी त्या.....(भाषेवर) मध्यम कौशल्य आहे.',
	'translate-fs-userpage-level-1' => 'मला थोडेसे माहिती आहे.',
	'translate-fs-userpage-help' => 'क्रुपया तुमचे भाषेचे कौशल्य दाखवा आणि स्वतःबद्दल काहीतरी सांगा. जर तुम्हाला पाच पेक्षा जास्त भाषा माहित असतील; तर त्यांचा तुम्ही नंतर समावेश करू शकता.', # Fuzzy
	'translate-fs-userpage-submit' => 'माझे सदस्यपान तयार करा.', # Fuzzy
	'translate-fs-userpage-done' => 'छान! तुम्हाला आता सदस्यपान आहे.',
	'translate-fs-permissions-planguage' => 'मुख्य(महत्त्वाची) भाषा निवडा',
	'translate-fs-permissions-help' => 'तुम्ही भाषांतर करणाऱ्या समूहामध्ये समाविष्ट होण्यासाठी विनंती पाठवावी.
तुम्ही भाषांतर करण्यासाठी वापरणारी मुख्य भाषा निवडा.
तुम्ही खाली टेक्सबॉक्स मध्ये इतर भाषा आणि सूचना देऊ शकता.',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'firststeps' => 'Langkah pertama',
	'firststeps-desc' => '[[Special:FirstSteps|Laman khas]] untuk melatih pengguna untuk menggunakan sambungan Terjemahan untuk membangunkan wiki',
	'translate-fs-pagetitle-done' => '- siap!',
	'translate-fs-pagetitle-pending' => ' - menunggu',
	'translate-fs-pagetitle' => 'Pendeta permulaan - $1',
	'translate-fs-signup-title' => 'Daftar diri',
	'translate-fs-settings-title' => 'Tataletak keutamaan anda',
	'translate-fs-userpage-title' => 'Cipta laman pengguna anda',
	'translate-fs-permissions-title' => 'Pohon kebenaran penterjemah',
	'translate-fs-target-title' => 'Mula menterjemah!',
	'translate-fs-email-title' => 'Sahkan alamat e-mel anda',
	'translate-fs-intro' => 'Selamat datang ke pendeta langkah pertama {{SITENAME}}.
Anda akan dibimbing sepanjang proses langkah demi langkah untuk menjadi penterjemah.
Pada akhirnya, anda akan dapat menterjemahkan "pesanan antara muka" bagi semua projek yang disokong di {{SITENAME}}.',
	'translate-fs-selectlanguage' => 'Pilih bahasa',
	'translate-fs-settings-planguage' => 'Bahasa utama:',
	'translate-fs-settings-planguage-desc' => 'Bahasa utama ini juga merupakan bahasa antara muka anda di wiki ini
dan juga bahasa sasaran asali untuk terjemahan.',
	'translate-fs-settings-slanguage' => 'Bahasa pembantu: $1',
	'translate-fs-settings-slanguage-desc' => 'Anda boleh memaparkan terjemahan mesej dalam bahasa lain dalam editor penterjemahan.
Di sini anda boleh memilih bahasa-bahasa yang anda ingin lihat.',
	'translate-fs-settings-submit' => 'Simpan keutamaan',
	'translate-fs-userpage-level-N' => 'Saya penutur asli',
	'translate-fs-userpage-level-5' => 'Saya penterjemah profesional',
	'translate-fs-userpage-level-4' => 'Saya fasih seperti penutur asli',
	'translate-fs-userpage-level-3' => 'Saya agak fasih',
	'translate-fs-userpage-level-2' => 'Saya sederhana fasih',
	'translate-fs-userpage-level-1' => 'Saya tahu sedikit',
	'translate-fs-userpage-help' => 'Sila nyatakan kemahiran bahasa anda dan perihalkan diri anda kepada kami. Jika anda tahu lebih daripada lima bahasa, anda boleh tambahkan banyak lagi lain kali.',
	'translate-fs-userpage-submit' => 'Wujudkan halaman pengguna anda',
	'translate-fs-userpage-done' => 'Syabas! Sekarang, anda ada laman pengguna.',
	'translate-fs-permissions-planguage' => 'Bahasa utama:',
	'translate-fs-permissions-help' => 'Kini, anda perlu membuat permintaan untuk disertakan dalam kumpulan penterjemah.
Pilih bahasa utama yang anda ingin membuat terjemahan anda.

Anda boleh menyebut bahasa-bahasa lain dan catatan-catatan lain dalam ruangan teks di bawah.',
	'translate-fs-permissions-pending' => 'Permintaan anda telah diserahkan kepada [[$1]] untuk dilihat oleh seseorang kakitangan secepat mungkin.
Jika anda mengesahkan alamat e-mel anda, anda akan menerima pemberitahuan melalui e-mel secepat mungkin.',
	'translate-fs-permissions-submit' => 'Hantar permohonan',
	'translate-fs-target-text' => "Syabas! Sekarang, anda boleh mulai menterjemah.

Jangan risau jika kebingungan kerana anda memerlukan masa untuk membiasakan diri. Di [[Project list]] terdapat sekilas pandang projek yang boleh anda sumbangkan terjemahan. Kebanyakan projek mempunyai laman keterangan ringkas dengan pautan \"''Translate this project''\" yang membawa anda ke laman yang menyenaraikan pesanan yang belum diterjemah. Juga terdapat senarai semua kumpulan pesanan dengan [[Special:LanguageStats|status penterjemahan semasa bahasa itu]].

Jika anda rasa anda perlu meningkatkan kefahaman anda sebelum memulakan penterjemahan, anda boleh membaca [[FAQ|Soalan Lazim]] kami, tetapi berhati-hati kerana sesetengah isinya mungkin ketinggalan zaman. Jika anda merasa apa-apa yang anda sepatutnya boleh lakukan, tetapi tidak dapat mengetahui caranya, jangan malu untuk bertanya di [[Support|laman bantuan]].

Anda juga boleh menghubungi para penterjemah lain yang sama bahasa dengan anda di [[Portal_talk:\$1|laman perbincangan]] [[Portal:\$1|portal bahasa anda]]. Sekiranya anda belum berbuat demikian, sila [[Special:Preferences|ubah bahasa antara muka pengguna anda kepada bahasa terjemahan anda]] supaya wiki ini dapat menunjukkan pautan-pautan (''links'') yang paling relevan kepada anda.",
	'translate-fs-email-text' => 'Sila berikan alamat e-mel anda di [[Special:Preferences|keutamaan anda]] dan sahkannya daripada e-mel yang dihantar kepada anda.

Ini membolehkan pengguna lain untuk menghubungi anda melalui e-mel.
Anda juga akan menerima surat berita selebih-lebihnya sebulan sekali.
Jika anda tidak ingi menerima surat berita, anda boleh memilih untuk mengecualikan diri daripada senarai penghantaran kami dalam tab "{{int:prefs-personal}}" dalam [[Special:Preferences|keutamaan]] anda.',
);

/** Norwegian Bokmål (norsk bokmål)
 * @author Nghtwlkr
 */
$messages['nb'] = array(
	'firststeps' => 'Første steg',
	'firststeps-desc' => '[[Special:FirstSteps|Spesialside]] for å få brukere igang med wikier som bruker Translate-utvidelsen',
	'translate-fs-pagetitle-done' => ' – ferdig!',
	'translate-fs-pagetitle-pending' => ' – venter',
	'translate-fs-pagetitle' => 'Veiviser for å komme igang – $1',
	'translate-fs-signup-title' => 'Registrer deg',
	'translate-fs-settings-title' => 'Konfigurer innstillingene dine',
	'translate-fs-userpage-title' => 'Opprett brukersiden din',
	'translate-fs-permissions-title' => 'Spør om oversetterrettigheter',
	'translate-fs-target-title' => 'Start å oversette!',
	'translate-fs-email-title' => 'Bekreft e-postadressen din',
	'translate-fs-intro' => "Velkommen til veiviseren for å komme igang med {{SITENAME}}.
Du vil bli veiledet gjennom prosessen med å bli en oversetter steg for steg.
Til slutt vil du kunne oversette ''grensesnittsmeldinger'' for alle støttede prosjekt på {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Velg et språk',
	'translate-fs-settings-planguage' => 'Hovedspråk:',
	'translate-fs-settings-planguage-desc' => 'Hovedspråket fungerer også som grensesnittspråket ditt på denne wikien og som standardspråk for oversettelser.',
	'translate-fs-settings-slanguage' => 'Hjelpespråk $1:',
	'translate-fs-settings-slanguage-desc' => 'Det er mulig å vise oversettelser av meldinger på andre språk i oversettelseseditoren.
Her kan du velge hvilke språk du ønsker å se, om noen.',
	'translate-fs-settings-submit' => 'Lagre innstillinger',
	'translate-fs-userpage-level-N' => 'Morsmålet mitt er',
	'translate-fs-userpage-level-5' => 'Jeg er profesjonoll oversetter av',
	'translate-fs-userpage-level-4' => 'Jeg er like god som en morsmålsbruker',
	'translate-fs-userpage-level-3' => 'Jeg har god kjennskap til',
	'translate-fs-userpage-level-2' => 'Jeg har grei kjennskap til',
	'translate-fs-userpage-level-1' => 'Jeg kan litt',
	'translate-fs-userpage-help' => 'Oppgi dine språkferdigheter og fortell oss litt om deg selv. Om du kan mer enn fem språk kan du legge til flere senere.', # Fuzzy
	'translate-fs-userpage-submit' => 'Opprett brukersiden min', # Fuzzy
	'translate-fs-userpage-done' => 'Flott! Nå har du en brukerside.',
	'translate-fs-permissions-planguage' => 'Hovedspråk:',
	'translate-fs-permissions-help' => 'Du må nå be om å få bli med i oversettergruppen.
Velg språket du hovedsakelig vil oversette til.

Du kan nevne andre språk og andre kommentarer i tekstboksen nedenfor.',
	'translate-fs-permissions-pending' => 'Forespørselen din har blitt sendt til [[$1]], og noen av våre medarbeidere vil sjekke den så snart som mulig.
Om du bekrefter e-postadressen din vil du få en melding så fort det skjer.',
	'translate-fs-permissions-submit' => 'Send forespørsel',
	'translate-fs-target-text' => "Gratulerer.
Du kan nå begynne å oversette.

Ikke vær redd om det fortsatt føles nytt og forvirrende.
I [[Project list|prosjektlisten]] er det en liste over prosjekt du kan bidra med oversettelser til.
De fleste av prosjektene har en kort beskrivelsesside med en «''Oversett dette prosjektet''»-lenke som vil føre deg til en side som lister opp alle uoversatte meldinger.
En liste over alle meldingsgruppene med den [[Special:LanguageStats|nåværende oversettelsesstatusen for et språk]] er også tilgjengelig.

Om du synes at du må forstå mer før du begynner å oversette kan du lese [[FAQ|Ofte stilte spørsmål]].
Dessverre kan dokumentasjonen av og til være utdatert.
Om det er noe du tror du kan gjøre men ikke vet hvordan, ikke nøl med å spørre på [[Support|støttesiden]].

Du kan også kontakte medoversettere av samme språk på [[Portal:$1|din språkportal]]s [[Portal_talk:$1|diskusjonsside]].
Om du ikke allerede har gjort det, [[Special:Preferences|endre grensesnittspråket ditt til det språket du vil oversette til]] slik at wikien kan vise de mest relevante lenkene for deg.",
	'translate-fs-email-text' => 'Oppgi e-postadressen din i [[Special:Preferences|innstillingene dine]] og bekreft den i e-posten som blir sendt til deg.

Dette lar andre brukere kontakte deg via e-post.
Du vil også motta nyhetsbrev høyst én gang i måneden.
Om du ikke vil motta nyhetsbrevet kan du melde deg ut i fanen «{{int:prefs-personal}}» i [[Special:Preferences|innstillingene]] dine.',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 */
$messages['nl'] = array(
	'firststeps' => 'Eerste stappen',
	'firststeps-desc' => '[[Special:FirstSteps|Speciale pagina]] voor het op gang helpen van gebruikers op een wiki met de uitbreiding Translate',
	'translate-fs-pagetitle-done' => ' - afgerond!',
	'translate-fs-pagetitle-pending' => '- in behandeling',
	'translate-fs-pagetitle' => 'Aan de slag - $1',
	'translate-fs-signup-title' => 'Registreren',
	'translate-fs-settings-title' => 'Uw voorkeuren instellen',
	'translate-fs-userpage-title' => 'Uw gebruikerspagina aanmaken',
	'translate-fs-permissions-title' => 'Vertaalrechten aanvragen',
	'translate-fs-target-title' => 'Beginnen met vertalen!',
	'translate-fs-email-title' => 'Uw e-mailadres bevestigen',
	'translate-fs-intro' => 'Welkom bij de wizard Aan de slag van {{SITENAME}}.
We loodsen u stap voor stap door het proces van vertaler worden.
Aan het einde kunt u alle door {{SITENAME}} ondersteunde projecten vertalen.',
	'translate-fs-selectlanguage' => 'Kies een taal',
	'translate-fs-settings-planguage' => 'Primaire taal:',
	'translate-fs-settings-planguage-desc' => 'De primaire taal is de taal van de interface op deze wiki en ook de standaard taal voor vertalingen.',
	'translate-fs-settings-slanguage' => 'Hulptaal $1:',
	'translate-fs-settings-slanguage-desc' => 'Het is mogelijk om vertalingen van berichten in andere talen weer te geven in de vertalingsbewerker.
Hier kunt u kiezen welke talen u wilt zien.',
	'translate-fs-settings-submit' => 'Voorkeuren opslaan',
	'translate-fs-userpage-level-N' => 'Dit is mijn moedertaal',
	'translate-fs-userpage-level-5' => 'In deze taal vertaal ik professioneel',
	'translate-fs-userpage-level-4' => 'Ik ken deze taal zo goed als een moedertaalspreker',
	'translate-fs-userpage-level-3' => 'Deze taal beheers ik goed',
	'translate-fs-userpage-level-2' => 'Deze taal beheers ik gemiddeld',
	'translate-fs-userpage-level-1' => 'Deze taal ken ik een beetje',
	'translate-fs-userpage-help' => 'Geef uw taalvaardigheden aan en vertel iets over uzelf. Als u meer dan vijf talen kent, kunt u er later meer toevoegen.',
	'translate-fs-userpage-submit' => 'Uw gebruikerspagina aanmaken',
	'translate-fs-userpage-done' => 'Goed gedaan!
U hebt nu een gebruikerspagina.',
	'translate-fs-permissions-planguage' => 'Primaire taal:',
	'translate-fs-permissions-help' => 'Plaats nu een verzoek om te mogen vertalen.
Selecteer de primaire taal waarin u gaat vertalen.

U kunt andere talen en andere opmerkingen vermelden in het tekstvak hieronder.',
	'translate-fs-permissions-pending' => 'Uw verzoek is opgenomen op de pagina [[$1]] en een staflid van deze site handelt dit zo snel mogelijk af. Als uw e-mailadres bevestigd is, ontvangt u een melding per e-mail zodra dit gebeurt.',
	'translate-fs-permissions-submit' => 'Verzoek versturen',
	'translate-fs-target-text' => 'Gefeliciteerd!
U kunt nu beginnen met vertalen.

Wees niet bang als het nog wat verwarrend aanvoelt.
In de [[Project list|Projectenlijst]] vindt u een overzicht van projecten waar u vertalingen aan kunt bijdragen.
Het merendeel van de projecten heeft een korte beschrijvingspagina met een koppeling "\'\'Dit project vertalen\'\'", die u naar een pagina leidt waarop alle onvertaalde berichten worden weergegeven.
Er is ook een lijst met alle berichtengroepen beschikbaar met de [[Special:LanguageStats|huidige status van de vertalingen voor een taal]].

Als u denkt dat u meer informatie nodig hebt voordat u kunt beginnen met vertalen, lees dan de [[FAQ|Veel gestelde vragen]].
Helaas kan de documentatie soms verouderd zijn.
Als er iets is waarvan u denkt dat het mogelijk moet zijn, maar u weet niet hoe, aarzel dan niet om het te vragen op de [[Support|pagina voor ondersteuning]].

U kunt ook contact opnemen met collegavertalers van dezelfde taal op de [[Portal_talk:$1|overlegpagina]] van [[Portal:$1|uw taalportaal]].
Als u het niet al hebt gedaan, [[Special:Preferences|wijzig dan de taal van de gebruikersinterface in de taal waarnaar u gaat vertalen]], zodat de wiki u de meest relevante koppelingen kan presenteren.',
	'translate-fs-email-text' => 'Geef uw e-mail adres in in [[Special:Preferences|uw voorkeuren]] en bevestig het via de e-mail die naar u verzonden is.

Dit makt het mogelijk dat andere gebruikers contact met u opnemen per e-mail.
U ontvangt dan ook maximaal een keer per maand de nieuwsbrief.
Als u geen nieuwsbrieven wilt ontvangen, dan kunt u dit aangeven in het tabblad "{{int:prefs-personal}}" van uw [[Special:Preferences|voorkeuren]].',
);

/** Norwegian Nynorsk (norsk nynorsk)
 * @author Harald Khan
 * @author Njardarlogar
 */
$messages['nn'] = array(
	'firststeps' => 'Dei fyrste stega',
	'translate-fs-selectlanguage' => 'Vel eit språk',
	'translate-fs-userpage-level-N' => 'Morsmålet mitt er',
	'translate-fs-userpage-level-5' => 'Eg er ein profesjonell omsetjar av',
	'translate-fs-userpage-submit' => 'Opprett brukarsida mi', # Fuzzy
	'translate-fs-userpage-done' => 'Bra! No har du ei brukarside.',
	'translate-fs-permissions-planguage' => 'Hovudspråk:',
	'translate-fs-permissions-submit' => 'Send førespurnad',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'firststeps' => 'Primièrs passes',
	'translate-fs-pagetitle-done' => ' - fach !',
	'translate-fs-pagetitle-pending' => '- en cors',
	'translate-fs-pagetitle' => "Guida d'aviada - $1",
	'translate-fs-signup-title' => 'Inscrivètz-vos',
	'translate-fs-settings-title' => 'Configuratz vòstras preferéncias',
	'translate-fs-userpage-title' => "Creatz vòstra pagina d'utilizaire",
	'translate-fs-target-title' => 'Començatz de tradusir !',
	'translate-fs-email-title' => 'Confirmatz vòstra adreça de corrièr electronic',
	'translate-fs-selectlanguage' => 'Causissètz una lenga',
);

/** Punjabi (ਪੰਜਾਬੀ)
 * @author Babanwalia
 */
$messages['pa'] = array(
	'firststeps' => 'ਪਹਿਲੇ ਕਦਮ',
	'translate-fs-pagetitle-done' => ' - ਹੋ ਗਿਆ!',
	'translate-fs-pagetitle-pending' => ' - ਲਟਕਿਆ ਹੋਇਆ',
	'translate-fs-signup-title' => 'ਸਾਈਨ ਅੱਪ',
	'translate-fs-settings-title' => 'ਆਪਣੀਆਂ ਪਸੰਦਾਂ ਚੁਣੋ',
	'translate-fs-userpage-title' => 'ਆਪਣਾ ਵਰਤੋਂਕਾਰ ਸਫ਼ਾ ਬਣਾਓ',
	'translate-fs-permissions-title' => 'ਅਨੁਵਾਦਕ ਇਜਾਜ਼ਤਾਂ ਲਈ ਬੇਨਤੀ ਕਰੋ',
	'translate-fs-target-title' => 'ਅਨੁਵਾਦ ਸ਼ੁਰੂ ਕਰੋ!',
	'translate-fs-email-title' => 'ਆਪਣਾ ਈਮੇਲ ਪਤਾ ਤਸਦੀਕ ਕਰਾਓ',
	'translate-fs-selectlanguage' => 'ਇੱਕ ਭਾਸ਼ਾ ਚੁਣੋ',
	'translate-fs-settings-planguage' => 'ਮੁਢਲੀ ਭਾਸ਼ਾ:',
	'translate-fs-settings-slanguage' => 'ਸਹਾਇਕ ਭਾਸ਼ਾ $1:',
	'translate-fs-settings-submit' => 'ਪਸੰਦਾਂ ਸਾਂਭੋ',
	'translate-fs-userpage-level-N' => 'ਮੇਰੀ ਮਾਂ ਬੋਲੀ ਹੈ',
	'translate-fs-userpage-level-4' => 'ਮੈਂ ਇਹਨੂੰ ਮਾਂ ਬੋਲੀ ਵਾਂਗ ਜਾਣਦਾ ਹਾਂ',
	'translate-fs-userpage-level-3' => 'ਮੈਂ ਚੰਗੀ ਤਰ੍ਹਾਂ ਜਾਣਦਾ ਹਾਂ',
	'translate-fs-userpage-level-2' => 'ਮੈਂ ਠੀਕ-ਠਾਕ ਜਾਣਦਾ ਹਾਂ',
	'translate-fs-userpage-level-1' => 'ਮੈਂ ਥੋੜ੍ਹੀ ਥੋੜ੍ਹੀ ਜਾਣਦਾ ਹਾਂ',
	'translate-fs-userpage-submit' => 'ਆਪਣਾ ਵਰਤੋਂਕਾਰ ਸਫ਼ਾ ਬਣਾਓ',
	'translate-fs-userpage-done' => 'ਸ਼ਾਬਾਸ਼! ਹੁਣ ਤੁਹਾਡੇ ਕੋਲ ਇੱਕ ਵਰਤੋਂਕਾਰ ਸਫ਼ਾ ਹੈ।',
	'translate-fs-permissions-planguage' => 'ਮੁਢਲੀ ਭਾਸ਼ਾ:',
	'translate-fs-permissions-submit' => 'ਬੇਨਤੀ ਭੇਜੋ',
);

/** Deitsch (Deitsch)
 * @author Xqt
 */
$messages['pdc'] = array(
	'translate-fs-pagetitle-done' => '- geduh!',
);

/** Pälzisch (Pälzisch)
 * @author Manuae
 */
$messages['pfl'] = array(
	'firststeps' => 'Easchde Schridd',
	'firststeps-desc' => '[[Special:FirstSteps|Sondasaid]] dmid Benudza uffm Wiki laischda mide „Translate“-Eawaidarung oafonge kenne',
	'translate-fs-pagetitle-done' => ' – feadisch!',
	'translate-fs-pagetitle-pending' => ' – noch ned feadisch',
	'translate-fs-pagetitle' => 'Hilf fas Oafonge: $1',
	'translate-fs-signup-title' => 'Oamelde',
	'translate-fs-settings-title' => 'Oischdellunge oabasse',
	'translate-fs-userpage-title' => 'Eazaisch doi Benudzasaid',
	'translate-fs-permissions-title' => 'Frooch noch Räschd zum Iwasedze',
	'translate-fs-target-title' => 'Iwasedze!',
	'translate-fs-email-title' => 'E-Mail-Adress bschdedische',
	'translate-fs-intro' => "Willkumme baide {{SITENAME}} Hilf, zum Oafonge.
Do weaschd Schridd fa Schridd ins Iwasedze oifgiad.
Oam Schluß weaschd alli ''Nochrischde vunde Benudzaowaflesch'' vun alli Brojegd bai {{SITENAME}} iwasedze kenne.",
	'translate-fs-selectlanguage' => 'Wählda ä Schbrooch',
	'translate-fs-settings-planguage' => 'Haubdschbrooch:',
	'translate-fs-settings-planguage-desc' => 'Die Haubdschbrooch isch fa doi Owaflesch uffm Wiki 
un a die Schbrooch inwus Iwasedze wilschd.',
	'translate-fs-settings-slanguage' => 'Hilfschbrooch: $1',
	'translate-fs-settings-slanguage-desc' => "S'meschlisch da Iwasedzunge vun Nochrischde in oanare Schbrooche zaische zu losse.
Wonns hawe wilschd un wonnses gibd, konschdda do die Schbrooch wehle.",
	'translate-fs-settings-submit' => 'Oischdellunge schbaischare',
	'translate-fs-userpage-level-N' => 'Des isch moi Muddaschbrooch',
	'translate-fs-userpage-level-5' => 'Isch binän fachkundische Iwasedza',
	'translate-fs-userpage-level-4' => 'Isch babbls faschd wie moi Muddaschbrooch',
	'translate-fs-userpage-level-3' => 'Isch babbls goans guud',
	'translate-fs-userpage-level-2' => 'Isch babbls schun guud',
	'translate-fs-userpage-level-1' => 'Isch babbls ä bissl',
	'translate-fs-userpage-help' => 'Bidde saach uns was iwa disch un wasfa Schbrooche du babblschd. Wonn mea als finf Schbrooche koanschd, konschdse a schbeda oagewe.', # Fuzzy
	'translate-fs-userpage-submit' => 'Benudzasaid oaleesche', # Fuzzy
	'translate-fs-userpage-done' => 'Brima, jedz hoschd ä Benudzasaid',
	'translate-fs-permissions-planguage' => 'Haubdschbrooch:',
	'translate-fs-permissions-help' => 'Jedzd mugschd oafroche, um zude Benudzagrubb vunde Iwasedza uffgnumme werre zu kenne.
Wehl doi Haubdschbrooch, in wus iwasedze wilschd.

Onare Schbrooche un Hiwies konschd une im Tegschdfeld oagewe.',
	'translate-fs-permissions-pending' => 'Doi Oafroch isch jedzd uffde Said [[$1]] un iaschndwea werdse doan iwbriefe.
Wonn doi E-Mail-Adress bschedische dudsch, krigschd dodriwa a Bschaid wonns basiad isch.',
	'translate-fs-permissions-submit' => 'Oafroch abschigge',
	'translate-fs-target-text' => "Gliggwunsch!
Du konschd glaisch midm Iwasedze oafonge.

Losdisch ned vum Naije vawirre.
Uffde Said [[Project list|Brojegde]] gibds ä Iwasischd vunde Brojegd, wu dro schaffe konschd.
Maischdns hods ä korzi Bschraiwung zsomme midm „''Iwasedze''“-Ling'g, wu disch uff ä Said mid Nochrische bringe dud, wu noch iwasedzd werre missen.
Ä Lischd vunde Nochrischdegrubbe unäm [[Special:LanguageStats|geschewerdische Zuschdoand vunär Schbrooch]] gibds aa.

Wonn do mea wisse wilschd, konschd a die [[FAQ|oam haifigschdi gschdellde Froche]] lese.
Die Unalaache kennen awa a ä bissl ald soi.
Wonn was mache megschd, awa ned wegschd wie, frooch efach uff de [[Support|Hilfssaid]].

Konschd awa a Iwasedza vun doinare Schbrooch uffde [[Portal_talk:$1|Dischbediersaid]] [[Portal:$1|vum Schbroochpordal]] oaschbresche.
S'Podal vabinded uff doin [[Special:Preferences|Schbroochoischdellunge]].
Wenn nedisch mugschd des änare.",
	'translate-fs-email-text' => 'Geb bidde doi E-Mail-Adress in [[Special:Preferences|doine Oischdellunge]] oa un bschedischse iwa die E-Mail, wu krische duschd.

S\'eameschlischd oanare dia E-Mails z\'schigge.
Krigschd a de monadlische Newsletter gschiggd.
Wonnen awa ned hawe wilschd, konschdn inde Tab "{{int:Prefs-personal}}" vun doine [[Special:Preferences|Oischdellunge]] wegnemme.',
);

/** Polish (polski)
 * @author BeginaFelicysym
 * @author Chrumps
 * @author Sp5uhe
 * @author Woytecr
 */
$messages['pl'] = array(
	'firststeps' => 'Pierwsze kroki',
	'firststeps-desc' => '[[Special:FirstSteps|Strona specjalna]] ułatwiająca rozpoczęcie pracy na wiki z wykorzystaniem rozszerzenia Translate',
	'translate-fs-pagetitle-done' => '&#32;– gotowe!',
	'translate-fs-pagetitle-pending' => '- oczekiwanie',
	'translate-fs-pagetitle' => 'Kreator pierwszych kroków – $1',
	'translate-fs-signup-title' => 'Rejestracja',
	'translate-fs-settings-title' => 'Konfiguracja preferencji',
	'translate-fs-userpage-title' => 'Tworzenie swojej strony użytkownika',
	'translate-fs-permissions-title' => 'Wniosek o uprawnienia tłumacza',
	'translate-fs-target-title' => 'Zacznij tłumaczyć!',
	'translate-fs-email-title' => 'Potwierdź swój adres e‐mail',
	'translate-fs-intro' => "Witaj w kreatorze pierwszych kroków na {{GRAMMAR:MS,pl|{{SITENAME}}}}.
Pomożemy Ci krok po kroku przejść przez proces zostania tłumaczem.
Po jego zakończeniu będziesz mógł tłumaczyć ''komunikatu interfejsu'' wszystkich wspieranych przez {{GRAMMAR:B.lp|{{SITENAME}}}} projektów.",
	'translate-fs-selectlanguage' => 'Wybierz język',
	'translate-fs-settings-planguage' => 'Język podstawowy:',
	'translate-fs-settings-planguage-desc' => 'Język podstawowy służy jako język interfejsu tej wiki
i jako domyślny język docelowy tłumaczenia.',
	'translate-fs-settings-slanguage' => 'Język pomocniczy $1:',
	'translate-fs-settings-slanguage-desc' => 'Istnieje możliwość pokazania tłumaczeń wiadomości w innych językach w oknie edycji tłumaczenia.
W tym miejscu można wybrać, tłumaczenia na jakie ewentualne języki chcesz widzieć.',
	'translate-fs-settings-submit' => 'Zapisz preferencje',
	'translate-fs-userpage-level-N' => 'Moim językiem ojczystym jest',
	'translate-fs-userpage-level-5' => 'Jestem profesjonalnym tłumaczem na',
	'translate-fs-userpage-level-4' => 'Znam język jak ojczysty',
	'translate-fs-userpage-level-3' => 'Mam dobrą znajomość',
	'translate-fs-userpage-level-2' => 'Mam umiarkowaną znajomość',
	'translate-fs-userpage-level-1' => 'Znam trochę',
	'translate-fs-userpage-help' => 'Proszę, wskaż swoje umiejętności językowe i opowiedz coś o sobie. Jeśli znasz więcej niż pięć języków, możesz później dodać ich więcej.',
	'translate-fs-userpage-submit' => 'Utwórz swoją stronę użytkownika',
	'translate-fs-userpage-done' => 'Udało się! Masz już swoją stronę użytkownika.',
	'translate-fs-permissions-planguage' => 'Język podstawowy:',
	'translate-fs-permissions-help' => 'Teraz musisz umieścić wniosek o dodania siebie do grupy tłumaczy.
Wybierz język podstawowy, na który zamierzasz tłumaczyć.

Można wymienić inne języki i inne uwagi w polu tekstowym poniżej.',
	'translate-fs-permissions-pending' => 'Wniosek został złożony na [[$1]] i ktoś z personelu witryny sprawdzi go tak szybko, jak to możliwe.
Jeśli adres e-mail został przez ciebie potwierdzony, otrzymasz wiadomość z powiadomieniem, gdy tylko się to stanie.',
	'translate-fs-permissions-submit' => 'Wyślij wniosek',
	'translate-fs-target-text' => 'Gratulujemy!
Możesz teraz rozpocząć tłumaczenie.

Nie bój, jeśli wydaje ci się to się wciąż nowe i pogmatwane.
Na [[Project list|liście projektu]] zamieszczono przegląd projektów, do których możesz dodawać tłumaczenia.
Większość projektów ma krótki opis strony z łączem "\'\'Tłumacz ten projekt\'\'", który przenosi do strony, która zawiera listę wszystkich nieprzetłumaczonych wiadomości.
Lista wszystkich grup wiadomości z [[Special:LanguageStats|bieżącym stanem tłumaczenia na język]] jest również dostępna.

Jeśli uważasz, że musisz zrozumieć więcej, zanim zaczniesz tłumaczyć, możesz przeczytać [[FAQ|Często zadawane pytania]].
Niestety dokumentacja może być czasami nieaktualna.
Jeśli istnieje coś, co myślisz, że powinno zostać zrobione, ale nie możesz dowiedzieć się, jak, nie wahaj się o to pytać na [[Support|stronie wsparcia]].

Można również skontaktować się kolegów tłumaczy na ten sam język na [[Portal_talk:$1|stronie rozmowy]] [[Portal:$1|portalu własnego języka]].
Jeśli to jeszcze zostało ustawione, [[Special:Preferences|zmień język interfejsu użytkownika na język, na który chcesz tłumaczyć]], aby wiki była w stanie pokazać łącza najbardziej odpowiednie dla ciebie.',
	'translate-fs-email-text' => 'Podaj swój adres e‐mail w [[Special:Preferences|preferencjach]] i potwierdź go korzystając z e‐maila wysłanego do Ciebie.

Umożliwi to innym użytkownikom kontakt z Tobą.
Będziesz również, nie częściej niż co miesiąc, otrzymywać biuletyny.
Jeśli nie chcesz otrzymywać informacji o aktualnościach możesz z nich zrezygnować w zakładce „{{int:prefs-personal}}” w swoich [[Special:Preferences|preferencjach]].',
);

/** Piedmontese (Piemontèis)
 * @author Borichèt
 * @author Dragonòt
 */
$messages['pms'] = array(
	'firststeps' => 'Prim pass',
	'firststeps-desc' => "[[Special:FirstSteps|Pàgina special]] për anandié j'utent an sna wiki dovrand l'estension Translate",
	'translate-fs-pagetitle-done' => ' - fàit!',
	'translate-fs-pagetitle-pending' => '- an cors',
	'translate-fs-pagetitle' => 'Guida për parte - $1',
	'translate-fs-signup-title' => "Ch'as anscriva",
	'translate-fs-settings-title' => 'Configura ij tò gust',
	'translate-fs-userpage-title' => 'Crea toa pàgina utent',
	'translate-fs-permissions-title' => "Ch'a ciama ij përmess ëd tradutor",
	'translate-fs-target-title' => "Ch'a ancamin-a a volté!",
	'translate-fs-email-title' => 'Che an conferma soa adrëssa ëd pòsta eletrònica',
	'translate-fs-intro' => "Bin ëvnù an sl'assistent dij prim pass ëd {{SITENAME}}.
A sarà guidà pass për pass ant ël process dë vnì un tradutor.
A la fin a sarà bon a volté ij ''mëssagi dj'antërfasse'' ëd tùit ij proget gestì da {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Sern na lenga',
	'translate-fs-settings-planguage' => 'Lenga primaria:',
	'translate-fs-settings-planguage-desc' => "La lenga primaria a funsion-a com toa lenga d'antërfacia dzora a sta wiki
e com lenga obietiv predefinìa për le tradussion.",
	'translate-fs-settings-slanguage' => "Lenga dl'assistent $1:",
	'translate-fs-settings-slanguage-desc' => "A l'é possìbil smon-e dle tradussion ëd mëssagi an d'àutre lenghe ant l'editor ëd tradussion.
Ambelessì chiel a peul serne che lenghe, s'a-i na j'é, ch'a-j piaserìa vëdde.",
	'translate-fs-settings-submit' => 'Salvé ij sò gust',
	'translate-fs-userpage-level-N' => 'Mi i son un parlant nativ ëd',
	'translate-fs-userpage-level-5' => 'Mi i son un tradutor professional ëd',
	'translate-fs-userpage-level-4' => 'Mi i lo conòsso com un parlant nativ',
	'translate-fs-userpage-level-3' => "Mi i l'heu na bon-a conossensa ëd",
	'translate-fs-userpage-level-2' => "Mi i l'heu na conossensa moderà ëd",
	'translate-fs-userpage-level-1' => 'Mi i conòsso un pòch',
	'translate-fs-userpage-help' => "Për piasì ìndica toe conossense dla lenga e dis-ne quaicòs a propòsit ëd ti. S'it conòsse pi che sinch lenghe it peule giontene ëd pi pi tard.", # Fuzzy
	'translate-fs-userpage-submit' => 'Crea mia pàgina utent', # Fuzzy
	'translate-fs-userpage-done' => "Bin fàit! Adess it l'has na pàgina utent.",
	'translate-fs-permissions-planguage' => 'Lenga primaria:',
	'translate-fs-permissions-help' => "Adess a dev fé n'arcesta për esse giontà a la partìa dij tradutor.
Ch'a selession-a la lenga prinsipal ant la qual a veul volté.

A peul massioné d'àutre lenghe e d'àutre armarche ant la zòna ëd test sì-sota.",
	'translate-fs-permissions-pending' => "Soa arcesta a l'é stàita spedìa a [[$1]] e quaidun ëd l'echip dël sit a la controlerà pen-a possìbil.
S'a confirma toa adrëssa eletrònica, a arseivrà na notìfica për pòsta eletrònica cand ch'a sarà ël cas.",
	'translate-fs-permissions-submit' => "Mandé l'arcesta",
	'translate-fs-target-text' => "Congratulassion!
Adess a peul ancaminé a volté!

Ch'a l'abia pa tëmma s'as sent anco' neuv e confus.
A [[Project list]] a-i é na presentassion dij proget ch'a peul contribuì a volté.
Vàire proget a l'han na curta pàgina ëd descrission con un colegament \"''Vòlta ës proget''\", ch'a lo pòrta a na pàgina ch'a lista tùit ij mëssagi nen voltà.
Na lista ëd tute le partìe ëd mëssagi con lë [[Special:LanguageStats|stat corent ëd tradussion për na lenga]] a l'é ëdcò disponìbil.

S'a pensa ch'a l'ha dabzògn ëd capì ëd pi prima d'ancaminé a volté, a peul lese le [[FAQ|chestion ciamà ëd soens]].
Për maleur, dle vire la documentassion a peul esse veja.
S'a-i é quaicòs ch'a pensa ch'a podrìa esse bon a fé, ma a tiess pa a trové coma, ch'as gene pa a ciamelo a la [[Support|pàgina d'agiut]].

A peul ëdcò contaté ij tradutor amis ëd la midema lenga a la [[Portal_talk:\$1|pàgina ëd discussion]] ëd [[Portal:\$1|sò portal ëd la lenga]]'.
S'a l'ha pa anco' falo, [[Special:Preferences|ch'a cangia la lenga ëd soa antërfacia utent a la lenga ant la qual a veul fé dle tradussion]], an manera che la wiki a sia bon-a a smon-e ij colegament pi amportant për chiel.",
	'translate-fs-email-text' => "Për piasì, ch'a buta soa adrëssa ëd pòsta eletrònica ant ij [[Special:Preferences|sò gust]] e ch'a la conferma dal mëssagi che i l'oma mandaje.

Sòn a përmët a j'àutri utent ëd contatelo për pòsta eletrònica.
A arseivrà ëdcò na litra d'anformassion, al pi na vira al mèis.
S'a veul pa arsèive le litre d'anformassion, a peule serne ëd nò ant la tichëtta \"{{int:prefs-personal}}\" dij sò [[Special:Preferences|gust]].",
);

/** Pashto (پښتو)
 * @author Ahmed-Najib-Biabani-Ibrahimkhel
 */
$messages['ps'] = array(
	'firststeps' => 'لومړي ګامونه',
	'translate-fs-pagetitle-done' => ' - ترسره شو!',
	'translate-fs-signup-title' => 'نومليکل',
	'translate-fs-userpage-title' => 'ستاسې کارن مخ جوړول',
	'translate-fs-permissions-title' => 'د ژباړې د اجازې غوښتنه',
	'translate-fs-target-title' => 'په ژباړې پيل وکړۍ',
	'translate-fs-selectlanguage' => 'يوه ژبه وټاکۍ',
	'translate-fs-settings-planguage' => 'آرنۍ ژبه:',
	'translate-fs-settings-slanguage' => 'مرستياله ژبه $1:',
	'translate-fs-settings-submit' => 'غوره توبونه خوندي کول',
	'translate-fs-userpage-level-N' => 'دا زما مورنۍ ژبه ده',
	'translate-fs-userpage-level-5' => 'زه د دې ژبې څانګپوه ژباړن يم',
	'translate-fs-userpage-level-1' => 'يو څه پرې پوهېږم',
	'translate-fs-userpage-submit' => 'خپل کارن مخ جوړول',
	'translate-fs-permissions-planguage' => 'آرنۍ ژبه:',
	'translate-fs-permissions-submit' => 'غوښتنه ورلېږل',
);

/** Portuguese (português)
 * @author Giro720
 * @author Hamilton Abreu
 * @author Luckas
 * @author Malafaya
 * @author SandroHc
 */
$messages['pt'] = array(
	'firststeps' => 'Primeiros passos',
	'firststeps-desc' => '[[Special:FirstSteps|Página especial]] para familiarizar os utilizadores com o uso da extensão Translate numa wiki',
	'translate-fs-pagetitle-done' => ' - terminado!',
	'translate-fs-pagetitle-pending' => ' - pendente',
	'translate-fs-pagetitle' => 'Assistente de iniciação - $1',
	'translate-fs-signup-title' => 'Registe-se',
	'translate-fs-settings-title' => 'Configure as suas preferências',
	'translate-fs-userpage-title' => 'Crie a sua página de utilizador',
	'translate-fs-permissions-title' => 'Solicite permissões de tradutor',
	'translate-fs-target-title' => 'Comece a traduzir!',
	'translate-fs-email-title' => 'Confirme o seu endereço de correio electrónico',
	'translate-fs-intro' => "Bem-vindo ao assistente de iniciação da {{SITENAME}}.
Será conduzido passo a passo através do processo necessário para se tornar um tradutor.
No fim, será capaz de traduzir as ''mensagens da interface'' de todos os projetos suportados na {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Escolha uma língua',
	'translate-fs-settings-planguage' => 'Idioma principal:',
	'translate-fs-settings-slanguage' => 'Idioma de ajuda $1:',
	'translate-fs-settings-submit' => 'Salvar preferências',
	'translate-fs-userpage-level-N' => 'Sou um falante nativo de',
	'translate-fs-userpage-level-5' => 'Sou um tradutor profissional de',
	'translate-fs-userpage-level-4' => 'Conheço como se fosse um falante nativo',
	'translate-fs-userpage-level-3' => 'Tenho um bom domínio de',
	'translate-fs-userpage-level-2' => 'Tenho conhecimentos moderados de',
	'translate-fs-userpage-level-1' => 'Sei um pouco de',
	'translate-fs-userpage-help' => 'Por favor, Indique-nos as suas habilidades em idiomas e nos fale algo sobre você. Caso tenha conhecimentos em mais de cinco idiomas, será possível especificá-los noutra altura.', # Fuzzy
	'translate-fs-userpage-submit' => 'Criar a sua página de utilizador',
	'translate-fs-userpage-done' => 'Bom trabalho! Agora tem uma página de utilizador.',
	'translate-fs-permissions-planguage' => 'Idioma principal:',
	'translate-fs-permissions-pending' => 'O seu pedido foi enviado para [[$1]]. Alguém da equipa do site o verificará assim que possível.
Você receberá uma notificação por e-mail quando isso acontecer se confirmar/validar o seu endereço de e-mail.',
	'translate-fs-permissions-submit' => 'Enviar pedido',
	'translate-fs-target-text' => 'Parabéns!
Agora pode começar a traduzir.

Não se amedronte se tudo lhe parece ainda novo e confuso.
Na [[Project list|lista de projetos]] há um resumo dos projetos de tradução em que pode colaborar.
A maioria dos projetos tem uma página de descrição breve com um link «Traduza este projeto», que o leva a uma página com todas as mensagens ainda por traduzir.
Também está disponível uma lista de todos os grupos de mensagens com o [[Special:LanguageStats|estado presente de tradução para uma língua]].

Se acredita que precisa de compreender o processo melhor antes de começar a traduzir, pode ler as [[FAQ|perguntas frequentes]].
Infelizmente a documentação pode, por vezes, estar desatualizada.
Se há alguma coisa que acha que devia poder fazer, mas não consegue descobrir como, não hesite em perguntar na [[Support|página de suporte]].

Pode também contactar os outros tradutores da mesma língua na [[Portal_talk:$1|página de discussão]] do [[Portal:$1|portal da sua língua]].
Se ainda não o fez, [[Special:Preferences|defina como a sua língua da interface a língua para a qual pretende traduzir]]. Isto permite que a wiki lhe apresente os links mais relevantes para si.',
	'translate-fs-email-text' => 'Forneça o seu endereço de correio electrónico nas [[Special:Preferences|suas preferências]] e confirme-o a partir da mensagem que lhe será enviada.

Isto permite que os outros utilizadores o contactem por correio electrónico.
Também receberá newsletters, no máximo uma vez por mês.
Se não deseja receber as newsletters, pode optar por não recebê-las no separador "{{int:prefs-personal}}" das suas [[Special:Preferences|preferências]].',
);

/** Brazilian Portuguese (português do Brasil)
 * @author Giro720
 * @author Luckas
 * @author 555
 */
$messages['pt-br'] = array(
	'firststeps' => 'Primeiros passos',
	'firststeps-desc' => '[[Special:FirstSteps|Página especial]] para familiarizar os usuários com o uso da extensão Translate',
	'translate-fs-pagetitle-done' => ' - feito!',
	'translate-fs-pagetitle-pending' => ' - pendente',
	'translate-fs-pagetitle' => 'Assistente de iniciação - $1',
	'translate-fs-signup-title' => 'Criar uma conta',
	'translate-fs-settings-title' => 'Configurar suas preferências',
	'translate-fs-userpage-title' => 'Criar a sua página de usuário',
	'translate-fs-permissions-title' => 'Solicitar privilégios de tradutor',
	'translate-fs-target-title' => 'Começar a traduzir',
	'translate-fs-email-title' => 'Confirmar endereço de e-mail',
	'translate-fs-intro' => "Bem-vindo ao assistente de iniciação ao {{SITENAME}}.
Você será conduzido passo-a-passo através do processo necessário para se tornar um tradutor.
Ao terminar, você terá como traduzir as ''mensagens da interface'' de todos os projetos suportados no {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Escolha uma língua',
	'translate-fs-settings-planguage' => 'Idioma principal:',
	'translate-fs-settings-planguage-desc' => 'O idioma principal será tanto a língua da interface deste wiki
quanto o idioma para o qual você fará suas traduções.',
	'translate-fs-settings-slanguage' => 'Idioma de ajuda $1:',
	'translate-fs-settings-slanguage-desc' => 'É possível mostrar traduções de mensagens em outros idiomas no editor de traduções.
Aqui você os escolherá, se assim desejar.',
	'translate-fs-settings-submit' => 'Salvar preferências',
	'translate-fs-userpage-level-N' => 'Sou um falante nativo de',
	'translate-fs-userpage-level-5' => 'Sou um tradutor profissional de',
	'translate-fs-userpage-level-4' => 'Conheço como se fosse um falante nativo',
	'translate-fs-userpage-level-3' => 'Tenho um bom domínio de',
	'translate-fs-userpage-level-2' => 'Tenho conhecimentos moderados de',
	'translate-fs-userpage-level-1' => 'Sei um pouco de',
	'translate-fs-userpage-help' => 'Indique suas habilidades em idiomas e nos fale algo sobre você. Caso tenha conhecimentos em mais de cinco idiomas, será possível especificá-los em outro momento.', # Fuzzy
	'translate-fs-userpage-submit' => 'Criar a sua página de usuário',
	'translate-fs-userpage-done' => 'Bom trabalho! Agora você tem uma página de usuário.',
	'translate-fs-permissions-planguage' => 'Idioma principal:',
	'translate-fs-permissions-help' => 'Agora você precisa realizar o pedido de adição ao grupo com privilégios de tradução.
Selecione o idioma principal no qual você irá traduzir.

É possível mencionar outras línguas e comentários adicionais na caixa de texto abaixo.',
	'translate-fs-permissions-pending' => 'Seu pedido foi enviado para [[$1]]. Alguém da equipe do site o verificará assim que possível.
Você receberá uma notificação por e-mail quando isso acontecer se confirmar/validar seu endereço de e-mail.',
	'translate-fs-permissions-submit' => 'Enviar pedido',
	'translate-fs-target-text' => 'Parabéns!
Você já pode começar a traduzir.

Não tenha medo se tudo te parecer novo e confuso.
Na [[Project list|lista de projetos]] há um resumo dos projetos de tradução em que você pode colaborar.
A maioria deles tem uma página de descrição breve com um link "\'\'Traduza este projeto\'\'", que o leva a uma página com todas as mensagenspor traduzir.
Também está disponível uma lista de todos os grupos de mensagens com o [[Special:LanguageStats|estado presente de tradução para uma língua]].

Se acredita que precisa compreender o processo melhor antes de começar a traduzir, pode ler as [[FAQ|perguntas frequêntes]].
Infelizmente a documentação pode, às vezes, estar desatualizada.
Se há alguma coisa que acha que poderia fazer mas não consegue descobrir como, não hesite em perguntar na [[Support|página de suporte]].

Também é possível contatar os outros tradutores da mesma língua na [[Portal_talk:$1|página de discussão]] do [[Portal:$1|portal do seu idioma]].
Se ainda não o fez, [[Special:Preferences|defina como a sua língua da interface a língua para a qual pretende traduzir]]. Isto permite que a wiki lhe apresente os links mais relevantes para você.',
	'translate-fs-email-text' => 'Forneça o seu endereço de e-mail nas [[Special:Preferences|suas preferências]] e confirme-o a partir da mensagem que lhe será enviada.

Isto permite que os outros usuários o contatem por e-mail.
Você também passará a receber newsletters no máximo uma vez por mês.
Se não deseja receber as newsletters, é possível optar por não recebê-las na tab "{{int:prefs-personal}}" das suas [[Special:Preferences|preferências]].',
);

/** Romanian (română)
 * @author Minisarm
 */
$messages['ro'] = array(
	'firststeps' => 'Primii pași',
	'firststeps-desc' => '[[Special:FirstSteps|Pagină specială]] pentru a veni în întâmpinarea utilizatorilor unui site wiki care folosesc extensia Translate',
	'translate-fs-pagetitle-done' => ' – realizat!',
	'translate-fs-pagetitle-pending' => ' – în așteptare',
	'translate-fs-pagetitle' => 'Ghidul începătorului – $1',
	'translate-fs-signup-title' => 'Înregistrare',
	'translate-fs-settings-title' => 'Configurați-vă preferințele',
	'translate-fs-userpage-title' => 'Creați-vă propria pagină de utilizator',
	'translate-fs-permissions-title' => 'Cereți permisiuni de traducător',
	'translate-fs-target-title' => 'Să traducem!',
	'translate-fs-email-title' => 'Confirmați-vă adresa de e-mail',
	'translate-fs-intro' => "Bine ați venit: acesta este un ghid al începătorului oferit de {{SITENAME}}.
Veți fi îndrumat pas cu pas pentru a deveni un traducător.
În finalul procesului, veți putea traduce ''mesaje din interfața'' tuturor proiectelor care dispun de serviciile {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Alegeți o limbă',
	'translate-fs-settings-planguage' => 'Limbă principală:',
	'translate-fs-settings-planguage-desc' => 'Limba primară va deveni limba interfeței pe acest wiki și limba implicită pentru traducerile dumneavoastră.',
	'translate-fs-settings-slanguage' => 'Limba ajutătoare nr. $1:',
	'translate-fs-settings-slanguage-desc' => 'Este posibilă afișarea altor traduceri ale mesajelor, în alte limbi, în cadrul editorului.
Aici puteți alege limbile pe care doriți să le afișați.',
	'translate-fs-settings-submit' => 'Salvează preferințele',
	'translate-fs-userpage-level-N' => 'Sunt vorbitor nativ de',
	'translate-fs-userpage-level-5' => 'Sunt traducător profesionist în',
	'translate-fs-userpage-level-4' => 'O cunosc asemenea unui vorbitor nativ',
	'translate-fs-userpage-level-3' => 'Am cunoștințe bune de',
	'translate-fs-userpage-level-2' => 'Am cunoștințe rezonabile de',
	'translate-fs-userpage-level-1' => 'O cunosc destul de puțin',
	'translate-fs-userpage-help' => 'Vă rugăm să vă indicați competențele lingvistice și să ne spuneți câte ceva despre dumneavoastră. Dacă cunoașteți mai mult de cinci limbi, le puteți adăuga mai târziu pe restul.', # Fuzzy
	'translate-fs-userpage-submit' => 'Creați-vă pagina de utilizator',
	'translate-fs-userpage-done' => 'Foarte bine! Acum aveți o pagină de utilizator.',
	'translate-fs-permissions-planguage' => 'Limbă principală:',
	'translate-fs-permissions-help' => 'Acum va trebuie să faceți o cerere pentru a fi adăugat grupului de traducători.
Selectați limba principală în care veți traduce.

Puteți menționa alte limbi și remarci în caseta de mai jos.',
	'translate-fs-permissions-pending' => 'Cererea dumneavoastră a fost transmisă către [[$1]], iar cineva din cadrul echipei site-ului o va verifica cât mai curând posibil.
Dacă vă confirmați adresa electronică, veți primi o notificare prin e-mail de îndată ce cererea va fi fost verificată.',
	'translate-fs-permissions-submit' => 'Trimite cererea',
	'translate-fs-target-text' => "Felicitări!
Din acest moment puteți traduce.

Nu vă faceți griji dacă încă nu v-ați acomodat, iar unele lucruri vi se par ciudate.
[[Project list|Lista de aici]] reprezintă o trecere în revistă a proiectelor la care puteți contribui.
Majoritatea proiectelor beneficiază de o pagină descriptivă care conține și legătura „''Tradu acest proiect''”, legătură ce vă va conduce către o pagină afișând toate mesajele netraduse.
De asemenea, este disponibilă o listă a grupurilor de mesaje cu [[Special:LanguageStats|situația curentă în funcție de limbă]].

Dacă simțiți că detaliile de până acum sunt insuficiente, puteți consulta  [[FAQ|întrebările frecvente]] înainte de a traduce.
Din păcate, în unele cazuri, documentația este învechită și neactualizată.
Dacă există vreun lucru de care bănuiți că sunteți capabil, dar nu ați descoperit încă cum să procedați, nu ezitați să puneți întrebări la [[Support|cafeneaua locală]].

Puteți, de asemenea, să contactați și alți traducători de aceeași limbă pe [[Portal_talk:$1|pagina de discuție]] a [[Portal:$1|portalului lingvistic]] asociat comunității dumneavoastră.
Dacă nu ați procedat deja conform îndrumărilor, [[Special:Preferences|schimbați limba interfeței în așa fel încât să fie identică cu limba în care traduceți]]. Astfel, site-ul wiki este capabil să se plieze nevoilor dumneavoastră mult mai bine prin legături relevante.",
	'translate-fs-email-text' => 'Vă rugăm să ne furnizați o adresă de e-mail prin intermediul [[Special:Preferences|paginii preferințelor]], după care să o confirmați (verificați-vă căsuța de poștă electronică căutând un mesaj trimis de noi).

Acest lucru oferă posibilitatea altor utilizator să vă contacteze utilizând poșta electronică.
De asemenea, veți primi, cel mult o dată pe lună, un mesaj cu noutăți și știri.
Dacă nu doriți să recepționați acest newsletter, vă puteți dezabona în fila „{{int:prefs-personal}}” a [[Special:Preferences|preferințelor]] dumneavoastră.',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'firststeps' => 'Prime passe',
	'firststeps-desc' => "[[Special:FirstSteps|Pàgena speciale]] pe fà accumenzà le utinde sus a 'na uicchi a ausà l'estenzione de Traduzione",
	'translate-fs-pagetitle-done' => '- apposte!',
	'translate-fs-pagetitle-pending' => '- appese',
	'translate-fs-pagetitle' => 'Procedure guidate pe accumenzà - $1',
	'translate-fs-signup-title' => 'Reggistrate',
	'translate-fs-settings-title' => "'Mboste le preferenze tune",
	'translate-fs-userpage-title' => "Ccreje 'a pàgena utende toje",
	'translate-fs-permissions-title' => 'Cirche le permesse de traduttore',
	'translate-fs-target-title' => 'Accuminze a traducere!',
	'translate-fs-email-title' => "Conferme l'indirizze e-mail tune",
	'translate-fs-intro' => "Bovègne jndr'à 'a guide de le prime passe de {{SITENAME}}.
Tu avìne guidate 'mbrà le processe pe devendò traduttore passe pe passe.
Quanne amme spicciate, tu sì capace de traducete le \"messàgge de inderfacce\" de tutte le pruggette supportate sus a {{SITENAME}}.",
	'translate-fs-selectlanguage' => "Pigghie 'na lènghe",
	'translate-fs-settings-planguage' => 'Lènga prengepàle:',
	'translate-fs-settings-planguage-desc' => "'A lènghe prengepàle raddoppie cumme 'a lènghe de inderfacce sus a sta uicchi e cumme lènghe de destinazione de base pe le tradutture.",
	'translate-fs-settings-slanguage' => "Assistende d'a lènghe $1:",
	'translate-fs-settings-slanguage-desc' => "Jè possibbile fa vedè le traduziune de le messàgge jndr'à otre lènghe jndr'à 'u cangiatore de traduzione.
Aqquà tu puè scacchià ce lènghe, vuè ccu vide.",
	'translate-fs-settings-submit' => 'Reggistre le preferenze',
	'translate-fs-userpage-level-N' => "Ije sò 'nu lènga madre",
	'translate-fs-userpage-level-5' => "Ije sò 'nu professore",
	'translate-fs-userpage-level-4' => 'Ije parle probbie accellènde',
	'translate-fs-userpage-level-3' => "Ije parle cumme 'ndermèdie.",
	'translate-fs-userpage-level-2' => "'U canosche 'nu picche",
	'translate-fs-userpage-level-1' => "'U sacce tèrra-tèrre",
	'translate-fs-userpage-help' => "Pe piacere indiche le canoscenze tue d'a lènga e dinne quaccheccose sus a te. Ce tu canusce cchiù de cinghe lènghe, tu le puè aggiungere apprisse.",
	'translate-fs-userpage-submit' => "Ccreje 'a pàgena utende toje",
	'translate-fs-userpage-done' => "Fatte bbuène! Tu è 'na pàgena utende nove.",
	'translate-fs-permissions-planguage' => 'Lènga prengepàle:',
	'translate-fs-permissions-help' => "Mò tu è abbesògne de mettere 'na richieste pe esseer aggiunde jndr'à 'u gruppe de tradutture.
Scacchie 'a lènghe prengepàle ca tu vuè ccu traduce.

Tu puè scrivere otre lènghe e otre segnalaziune jndr'à buatte de teste aqquà sotte.",
	'translate-fs-permissions-pending' => "'A richiesta toje ha state confermate sus a [[$1]] e quaccheotre da 'a squadre d'u site adda verificà quanne pò.
Cer tu conferme l'indirizze email tune, tu è 'na notifiche cu 'a mail aprrime possibbile.",
	'translate-fs-permissions-submit' => "Manne 'na richieste",
	'translate-fs-target-text' => "Comblimende!
Tu puè accumenzà a traducere.

No te pigghià 'u sckande ce angore te sinde nuève e confuse.
Jndr'à l'[[Project list|elenghe de le pruggette]] stè 'na panorameche de le pruggette ca puè traducere.
Assaije de le pruggette onne 'na pàgene de descrizione corte cu 'nu collegamende \"''Traduce stu proggette''\", ca te porte sus a 'na pàgene ca elenghe tutte le messàgge none tradotte.
'N'elenghe de tutte le gruppe de messàgge cu 'u [[Special:LanguageStats|state de mò de le traduziune pe lènghe]] jè ppure disponibbile.

Ce tu sinde ca è abbesògne de capì megghie apprime de accumenzà a traducere, tu puè leggere le [[FAQ|domande cchiù frequende]].
Sfortunatamende 'a documendazione certe vote pò essere vecchie.
Ce stè quacchecose ca tu pinze ca avissa riuscì a fà, ma non ge iacche accumme fà, nò te fà probbleme a cercà a 'a [[Support|pàgene d'aijute]].

Tu puè pure condattà le tradutture d'a stessa lènga toje sus a [[Portal_talk:\$1|pàgene de le 'ngazzaminde]] d'u [[Portal:\$1|portale d'a lènga toje]].
Ce non g'è fatte accussì, [[Special:Preferences|cange l'inderfacce d'a lènghe, jndr'à lènghe ca vuè ccu traducere]], accussì 'a uicchi te face 'ndrucà le collegaminde cchiù 'mbortande pe te.",
	'translate-fs-email-text' => "Pe piacere mitte l'indirizze email tune jndr'à [[Special:Preferences|le preferenze tune]] e confermale da l'email ca t'arrive.

Quiste permette a le otre utinde de condattarte cu l'email.
Tu puè pure ricevere le newsletter 'na vote a 'u mese.
Ce tu non ge vuè ricevere le newsletter, tu puè luà 'a scelte jndr'à schede \"{{int:prefs-personal}}\" de le [[Special:Preferences|preferenze tune]].",
);

/** Russian (русский)
 * @author Eleferen
 * @author G0rn
 * @author Hypers
 * @author Kaganer
 * @author Александр Сигачёв
 */
$messages['ru'] = array(
	'firststeps' => 'Первые шаги',
	'firststeps-desc' => '[[Special:FirstSteps|Служебная страница]] для новых пользователей вики с установленным расширением перевода',
	'translate-fs-pagetitle-done' => ' — сделано!',
	'translate-fs-pagetitle-pending' => ' — в ожидании',
	'translate-fs-pagetitle' => 'Программа начального обучения — $1',
	'translate-fs-signup-title' => 'Зарегистрируйтесь',
	'translate-fs-settings-title' => 'Произведите настройку',
	'translate-fs-userpage-title' => 'Создайте свою страницу участника',
	'translate-fs-permissions-title' => 'Запросите права переводчика',
	'translate-fs-target-title' => 'Начните переводить!',
	'translate-fs-email-title' => 'Подтвердите свой адрес электронной почты',
	'translate-fs-intro' => 'Добро пожаловать в программу начального обучения проекта {{SITENAME}}.
Шаг за шагом вы будете проведены по обучающей программе переводчиков.
По окончанию обучения вы сможете переводить интерфейсные сообщения всех поддерживаемых проектов {{SITENAME}}.',
	'translate-fs-selectlanguage' => 'Выберите язык',
	'translate-fs-settings-planguage' => 'Основной язык:',
	'translate-fs-settings-planguage-desc' => 'Основной язык дублирует ваш язык интерфейса в этой вики
и по умолчанию рассматривается как целевой язык переводов.',
	'translate-fs-settings-slanguage' => 'Вспомогательный язык $1:',
	'translate-fs-settings-slanguage-desc' => 'Это позволяет видеть переводы сообщений на другие языки в интерфейсе редактирования переводов.
Здесь можно выбрать, какие из имеющихся языков вы хотели бы видеть.',
	'translate-fs-settings-submit' => 'Сохранить настройки',
	'translate-fs-userpage-level-N' => 'Мой родной язык',
	'translate-fs-userpage-level-5' => 'Я профессиональный переводчик с',
	'translate-fs-userpage-level-4' => 'Я в совершенстве владею',
	'translate-fs-userpage-level-3' => 'Я хорошо знаю',
	'translate-fs-userpage-level-2' => 'Я средне владею',
	'translate-fs-userpage-level-1' => 'Начальные знания',
	'translate-fs-userpage-help' => 'Пожалуйста, укажите свои знания языков и расскажите немного о себе. Если вы знаете больше пяти языков, вы сможете добавить их позже.',
	'translate-fs-userpage-submit' => 'Создайте свою страницу участника',
	'translate-fs-userpage-done' => 'Отлично! Теперь у вас есть страница участника.',
	'translate-fs-permissions-planguage' => 'Основной язык:',
	'translate-fs-permissions-help' => 'Теперь вам нужно разместить запрос, для вступления в группу переводчиков.
Выберите основной язык, на который Вы планируете осуществлять переводы.

Можно указать другие языки и примечания в текстовом поле ниже.',
	'translate-fs-permissions-pending' => 'Ваша заявка была подана [[$1]], и кто-то из сотрудников сайта проверит её в ближайшее время.
Если вы подтвердите свой адрес электронной почты, Вы получите уведомление по электронной почте, как только это произойдёт.',
	'translate-fs-permissions-submit' => 'Отправить запрос',
	'translate-fs-target-text' => "Поздравляем!
Теперь вы можете начать переводить.

Не бойтесь, если что-то до сих пор кажется новым и запутанным для вас.
В [[Project list|списке проектов]] находится обзор проектов, для которых вы можете осуществлять перевод.
Большинство проектов имеют небольшую страницу с описанием и ссылкой ''«Translate this project»'', которая ведёт на страницу со списком всех непереведённых сообщений.
Также имеется список всех групп сообщений с [[Special:LanguageStats|текущим статусом перевода для языка]].

Если вам кажется, что вам необходимо получить больше сведений перед началом перевода, то вы можете прочитать [[FAQ|часто задаваемые вопросы]].
К сожалению, документация иногда может быть устаревшей.
Если есть что-то, что по вашему мнению вы можете сделать, но не знаете как, то не стесняйтесь спросить об этом на [[Support|странице поддержки]].

Вы также можете связаться с переводчиками на странице [[Portal_talk:$1|обсуждения]] [[Portal:$1|портала вашего языка]].
Если вы этого ещё не сделали, укажите в [[Special:Preferences|ваших настройках]] язык, на который вы собираетесь переводить, тогда в интерфейсе вам будут показаны соответствующие ссылки.",
	'translate-fs-email-text' => 'Пожалуйста, укажите свой адрес электронной почты в [[Special:Preferences|персональных настройках]] и подтвердите его, перейдя по ссылке из письма, которое вам будет отправлено.

Это позволит другим участникам связываться с вами по электронной почте.
Вы также будете получать новостную рассылку раз в месяц.
Если вы не хотите получать рассылку, то можете отказаться от неё на вкладке «{{int:prefs-personal}}» своих [[Special:Preferences|персональных настроек]].',
);

/** Rusyn (русиньскый)
 * @author Gazeb
 */
$messages['rue'] = array(
	'firststeps' => 'Першы крокы',
	'translate-fs-pagetitle-done' => ' - зроблено!',
	'translate-fs-signup-title' => 'Зареґіструйте ся',
	'translate-fs-userpage-title' => 'Створити вашу сторінку хоснователя',
	'translate-fs-permissions-title' => 'Жадати права перекладателя',
	'translate-fs-target-title' => 'Започати перекладаня!',
	'translate-fs-email-title' => 'Підтвердьте свою адресу ел. пошты',
	'translate-fs-userpage-submit' => 'Створити мою сторінку хоснователя', # Fuzzy
	'translate-fs-userpage-done' => 'Добрі зроблено! Теперь маєте сторінку хоснователя.',
);

/** Sakha (саха тыла)
 * @author HalanTul
 */
$messages['sah'] = array(
	'firststeps' => 'Бастакы хардыылар',
	'firststeps-desc' => 'Саҥа кыттааччыларга аналлаах тылбаас сэбиргэллээх [[Special:FirstSteps|сирэй]]',
	'translate-fs-pagetitle-done' => '- оҥоһулунна!',
	'translate-fs-pagetitle-pending' => '- кэтэһии',
	'translate-fs-pagetitle' => 'Саҕалыырга үөрэтэр маастар - $1',
	'translate-fs-signup-title' => 'Бэлиэтэн',
	'translate-fs-settings-title' => 'Туруорууларгын уларыт',
	'translate-fs-userpage-title' => 'Бэйэҥ тус сирэйгэр бэйэҥ тускунан суруй',
	'translate-fs-permissions-title' => 'Тылбаасчыт аатын көрдөө',
	'translate-fs-target-title' => 'Тылбаастаан бар!',
	'translate-fs-email-title' => 'Эл. аадырыскын бигэргэт',
	'translate-fs-selectlanguage' => 'Тылгын тал',
	'translate-fs-settings-planguage' => 'Сүрүн тылыҥ:',
	'translate-fs-settings-planguage-desc' => 'Бу биикигэ сүрүн тыл диэн интерфейсиҥ тыла, бу тылга тылбаастыыгын.',
	'translate-fs-settings-slanguage' => 'Көмө тыл $1:',
	'translate-fs-settings-slanguage-desc' => 'Атын тылларга хайдах тылбаастаабыттарын көрөргө аналлаах.
Ханнык тыллары билэргинэн талыаххын сөп.',
	'translate-fs-settings-submit' => 'Туруоруулары бигэргэт',
	'translate-fs-userpage-level-N' => 'Төрөөбүт тылым',
	'translate-fs-userpage-level-5' => 'Бу тылтан тылбаастыыр мин идэм -',
	'translate-fs-userpage-level-4' => 'Бу тылы олус үчүгэйдик билэбин -',
	'translate-fs-userpage-level-3' => 'Үчүгэйдик билэбин -',
	'translate-fs-userpage-level-2' => 'Ортотук билэбин -',
	'translate-fs-userpage-level-1' => 'Кыратык билэбин -',
	'translate-fs-userpage-submit' => 'Бэйэҥ тус сирэйгэр бэйэҥ тускунан суруй',
	'translate-fs-userpage-done' => 'Бэрт! Манна тус сирэйдэнниҥ.',
);

/** Sinhala (සිංහල)
 * @author පසිඳු කාවින්ද
 * @author බිඟුවා
 */
$messages['si'] = array(
	'firststeps' => 'පළමු පියවරවල්',
	'translate-fs-pagetitle-done' => ' - හරි!',
	'translate-fs-pagetitle-pending' => ' - බලාපොරොත්තු වෙමින්',
	'translate-fs-pagetitle' => 'පටන් ගැනීමේ මායා අඳුන - $1',
	'translate-fs-signup-title' => 'ප්‍රවිෂ්ඨ වන්න',
	'translate-fs-settings-title' => 'ඔබේ අභිරුචින් වින්‍යාසගත කරන්න',
	'translate-fs-userpage-title' => 'ඔබේ පරිශීලක පිටුව තනන්න',
	'translate-fs-permissions-title' => 'පරිවර්තක අවසර අයදින්න',
	'translate-fs-target-title' => 'පරිවර්තනය කිරීම අරඹන්න!',
	'translate-fs-email-title' => 'ඔබගේ විද්‍යුත් තැපැල් ලිපිනය තහවුරු කරන්න',
	'translate-fs-selectlanguage' => 'භාෂාවක් තෝරාගන්න',
	'translate-fs-settings-planguage' => 'ප්‍රාථමික භාෂාව:',
	'translate-fs-settings-slanguage' => 'සහායක භෂාව $1:',
	'translate-fs-settings-submit' => 'අභිරුචීන් සුරකින්න',
	'translate-fs-userpage-level-N' => 'මම සහජ කථිකයෙකි',
	'translate-fs-userpage-level-5' => 'මම වෘර්තියමය පරිවර්තකයෙකි',
	'translate-fs-userpage-level-4' => 'මම එය සහජ කථිකයෙකු ලෙස දනිමි',
	'translate-fs-userpage-level-3' => 'මා සතුව හොඳ දක්ෂකමක් ඇත',
	'translate-fs-userpage-level-2' => 'මා සතුව මධ්‍යම දක්ෂකමක් ඇත',
	'translate-fs-userpage-level-1' => 'මම පොඩ්ඩක් දන්නවා',
	'translate-fs-userpage-submit' => 'මගේ පරිශීලක පිටුව තනන්න', # Fuzzy
	'translate-fs-userpage-done' => 'නියමයි! ඔබට දැන් පරිශීලක පිටුවක් තිබේ.',
	'translate-fs-permissions-planguage' => 'ප්‍රාථමික භාෂාව:',
	'translate-fs-permissions-submit' => 'ඉල්ලීම යවන්න',
);

/** Slovak (slovenčina)
 * @author Teslaton
 */
$messages['sk'] = array(
	'translate-fs-target-text' => "Gratulujeme!
Teraz môžete začať prekladať.

Nebojte sa, ak vám to tu pripadá nové a mätúce.
Na stránke [[Project list/sk|Zoznam projektov]] nájdete prehľad projektov, do ktorých môžete prispievať prekladmi.
Väčšina projektov obsahuje stručný popis a odkaz ''Translate this project'', ktorý vás dovedie na stránku s prehľadom všetkých nepreložených správ.
Je tiež k dispozícii zoznam všetkých skupín správ spolu s [[Special:LanguageStats|aktuálnym stavom prekladu do daného jazyka]].

Ak máte potrebu porozumieť veciam dôkladnejšie, ešte než začnete prekladať, môžete si prečítať [[FAQ|často kladené otázky]].
Dokumentácia môže byť bohužiaľ niekedy zastaraná.
Ak nájdete niečo, čo si myslíte, že by ste {{gender:|mal byť schopný|mala byť schopná|mali byť schopní}} robiť, ale nejde to, neváhajte sa opýtať na [[Support|stránke podpory]].

Môžete tiež kontaktovať spoluprekladateľa do rovnakého jazyka pomocou [[Portal_talk:$1|diskusnej stránky]] [[Portal:$1|vášho jazykového portálu]].
Ak ste tak dosiaľ {{gender:|neurobil|neurobila|neurobili}}, [[Special:Preferences|nastavte svoj jazyk rozhrania]] na jazyk, do ktorého chcete prekladať, aby vám táto wiki bola schopná zobrazovať čo najrelevantnejšie odkazy.", # Fuzzy
);

/** Slovenian (slovenščina)
 * @author Dbc334
 * @author Eleassar
 */
$messages['sl'] = array(
	'firststeps' => 'Prvi koraki',
	'firststeps-desc' => '[[Special:FirstSteps|Posebna stran]] za pripravo uporabnikov na začetek uporabe wikija z uporabo razširitve Translate',
	'translate-fs-pagetitle-done' => ' – končano!',
	'translate-fs-pagetitle-pending' => ' - na čakanju',
	'translate-fs-pagetitle' => 'Čarovnik prvih korakov – $1',
	'translate-fs-signup-title' => 'Prijavite se',
	'translate-fs-settings-title' => 'Konfigurirajte svoje nastavitve',
	'translate-fs-userpage-title' => 'Ustvarite svojo uporabniško stran',
	'translate-fs-permissions-title' => 'Zaprosite za prevajalska dovoljenja',
	'translate-fs-target-title' => 'Začnite prevajati!',
	'translate-fs-email-title' => 'Potrdite svoj e-poštni naslov',
	'translate-fs-intro' => "Dobrodošli v čarovniku prvih korakov na {{GRAMMAR:dajalnik|{{SITENAME}}}}.
Vodili vas bomo skozi postopek, da postanete prevajalec, korak za korakom.
Na koncu boste lahko prevajali ''sporočila vmesnika'' vseh podprtih projektov na {{GRAMMAR:dajalnik|{{SITENAME}}}}.",
	'translate-fs-selectlanguage' => 'Izberite jezik',
	'translate-fs-settings-planguage' => 'Prvotni jezik:',
	'translate-fs-settings-planguage-desc' => 'Prvotni jezik se kaže kot vaš jezik vmesnika na tem wikiju
in kot privzeti ciljni jezik prevodov.',
	'translate-fs-settings-slanguage' => 'Pomožni jezik $1:',
	'translate-fs-settings-slanguage-desc' => 'V urejevalniku prevodov je mogoče prikazati prevode sporočil v drugih jezikih.
Tukaj lahko izberete jezike, ki bi jih radi videli, če to želite.',
	'translate-fs-settings-submit' => 'Shrani nastavitve',
	'translate-fs-userpage-level-N' => 'Sem naravni govorec',
	'translate-fs-userpage-level-5' => 'Sem profesionalni prevajalec',
	'translate-fs-userpage-level-4' => 'Govorim ga skoraj enako dobro kakor prvi jezik',
	'translate-fs-userpage-level-3' => 'Zelo dobro govorim',
	'translate-fs-userpage-level-2' => 'Srednje dobro govorim',
	'translate-fs-userpage-level-1' => 'Poznam osnove',
	'translate-fs-userpage-help' => 'Prosimo, navedite svoje znanje jezikov in nam povejte nekaj o sebi. Če znate več kot pet jezikov, jih lahko dodate pozneje.', # Fuzzy
	'translate-fs-userpage-submit' => 'Ustvarite svojo uporabniško stran',
	'translate-fs-userpage-done' => 'Dobro opravljeno! Sedaj imate uporabniško stran.',
	'translate-fs-permissions-planguage' => 'Prvotni jezik:',
	'translate-fs-permissions-help' => 'Zdaj morate vložiti prošnjo za priključitev k skupini prevajalcev.
Izberite primarni jezik, v katerega boste prevajali.

V spodnjem polju lahko omenite tudi druge jezike in druge pripombe.',
	'translate-fs-permissions-pending' => 'Vašo prošnjo smo posredovali na [[$1]] in nekdo od osebja strani jo bo čim prej preveril.
Če potrdite svoj e-poštni naslov, boste prejeli e-poštno obvestilo takoj, ko se to zgodi.',
	'translate-fs-permissions-submit' => 'Pošlji zahtevo',
	'translate-fs-target-text' => "Čestitamo!
Zdaj lahko začnete prevajati.

Ne bojte se, če se vam vse še vedno zdi novo in zmedeno.
Na [[Project list|Seznamu projektov]] boste našli pregled projektov, h katerim lahko prispevate prevode.
Večina projektov ima kratko opisno stran s povezavo »''Prevedi ta projekt''«, ki vas bo ponesla na stran s seznamom neprevedenih sporočil.
Na razpolago je tudi seznam vseh skupin sporočil s [[Special:LanguageStats|trenutnim stanjem prevodov za  posamezen jezik]].

Če ste mnenja, da morate pred začetkom prevajanja okolje bolje spoznati, si lahko preberete odgovore na [[FAQ|Pogosto zastavljena vprašanja]].
Žal je lahko dokumentacija na nekaterih mestih zastarela.
Če se pojavi kaj, kar bi želeli storiti, vendar ne veste, kako, ne oklevajte in nas povprašajte na [[Support|podporni strani]].

Prav tako lahko stopite v stik s kolegi prevajalci istega jezika na [[Portal_talk:$1|pogovorni strani]] [[Portal:$1|vašega jezikovnega portala]].
Če tega niste morda že storili, lahko nastavite [[Special:Preferences|jezik vašega uporabniškega vmesnika na jezik, v katerega želite prevajati]], da bo wiki lahko prikazal za vas najustreznejše povezave.",
	'translate-fs-email-text' => 'Prosimo, navedite svoj e-poštni naslov v [[Special:Preferences|svojih nastavitvah]] in ga potrdite iz e-pošte, ki vam bo poslana.

To omogoča drugim uporabnikom, da stopijo v stik z vami preko e-pošte.
Prav tako boste prejemali glasilo, največ enkrat mesečno.
Če ne želite prejemati glasila, se lahko odjavite na zavihku »{{int:prefs-personal}}« v vaših [[Special:Preferences|nastavitvah]].',
);

/** Somali (Soomaaliga)
 * @author Abshirdheere
 */
$messages['so'] = array(
	'translate-fs-pagetitle' => 'Tilaabada hore ee - $1',
	'translate-fs-settings-title' => 'Isku hagaajinta dooqyedaada',
	'translate-fs-userpage-title' => 'Abuur Bogga adeegsedaha',
	'translate-fs-settings-planguage' => 'Luqada rasmiga ah',
	'translate-fs-settings-planguage-desc' => 'Luqadaada rasmiga ah ee kuu gaarka ah aadna ku arki doonto qoraalada wiki waana turjimaad luqad tusaale ah.',
	'translate-fs-settings-slanguage' => 'Luqada kaalmo $1:',
	'translate-fs-settings-slanguage-desc' => 'Waxaa suutoowda in la arko fasiaada fariimaha luqadaha kale habaytna turjimidda, halkaan waxaad ka dooran kartaa luqada aad rabto inaad wax ku aragtid, hadiiba meesha laga helo.',
	'translate-fs-settings-submit' => 'Kaydi dooqyeda',
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Милан Јелисавчић
 */
$messages['sr-ec'] = array(
	'firststeps' => 'Први кораци',
	'firststeps-desc' => "[[Special:FirstSteps|Посебна страница]] за почетнике на викију који користе ''Translate'' додатак",
	'translate-fs-pagetitle-done' => ' - урађено!',
	'translate-fs-pagetitle-pending' => ' - на чекању',
	'translate-fs-pagetitle' => 'Помоћник за почетнике - $1',
	'translate-fs-signup-title' => 'Отворите налог',
	'translate-fs-settings-title' => 'Подесите своје поставке',
	'translate-fs-userpage-title' => 'Направите корисничку страницу',
	'translate-fs-permissions-title' => 'Тражење преводилачке дозволе',
	'translate-fs-target-title' => 'Почните превођење!',
	'translate-fs-email-title' => 'Потврдите е-адресу',
	'translate-fs-intro' => "Добродошли на {{SITENAME}} помоћник за почетнике.
Бићете спроведени кроз поступак упознавања преводиоца корак по корак.
На крају ћете бити у могућности да преводите ''поруке интерфејса'' свих подржаних пројеката на {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Изаберите језик',
	'translate-fs-settings-planguage' => 'Примарни језик:',
	'translate-fs-settings-planguage-desc' => 'Примарни језик се удвостручује као језику интерфејса на овом викију и као подразумевани језик за циљне преводе.',
	'translate-fs-settings-slanguage' => 'Помоћни језик $1:',
	'translate-fs-settings-slanguage-desc' => 'Могуће је приказати преводе порука на другим језицима у уређивачу превода.
Овде можете да изаберете које језике, ако је потребно, бисте желели да видите.',
	'translate-fs-settings-submit' => 'Сачувај поставке',
	'translate-fs-userpage-level-N' => 'Мој матерњи језик је',
	'translate-fs-userpage-level-5' => 'Професионално преводим са',
	'translate-fs-userpage-level-4' => 'Познајем као матерњи',
	'translate-fs-userpage-level-3' => 'Добро се сналазим са',
	'translate-fs-userpage-level-2' => 'Осредње се сналазим са',
	'translate-fs-userpage-level-1' => 'Познајем мало',
	'translate-fs-userpage-help' => 'Наведите своје језичке вештине и реците нешто о себи. Ако знате више од пет језика, можете их додати још касније.',
	'translate-fs-userpage-submit' => 'Направите корисничку страницу',
	'translate-fs-userpage-done' => 'Одлично! Сада имате корисничку страницу.',
	'translate-fs-permissions-planguage' => 'Примарни језик:',
	'translate-fs-permissions-help' => 'Сада треба да поставите захтев да будете додати у групу преводиоца.
Изаберите примарни језик на који ћете преводити.

Можете поменути и друге језике и друге напомене у поље за унос текста испод.',
	'translate-fs-permissions-pending' => 'Захтев је послат на [[$1]] и неко од особља сајта ће га проверити у најкраћем могућем року. Ако сте потврдили своју е-адресу, добићете мејл са обавештењем чим се то деси.',
	'translate-fs-permissions-submit' => 'Пошаљи захтев',
	'translate-fs-target-text' => "Честитамо!
Сада можете да почнете са превођењем.

Не бојте се ако вам све и даље изгледа ново и збуњујуће.
На страници [[Project list]] је дат преглед пројеката на које можете допринети преводе.
Већина пројеката имају кратак опис странице са везом „''Преведи овај пројекат''“, која ће вас одвести на страницу на којој су наведене све непреведене поруке.
Списак свих група порука са [[Special:LanguageStats|тренутним стањем превода за дати језик]] је такође доступна.

Ако сматрате да вам је потребно више да разумете пре него што почнете са превођењем, можете прочитати [[FAQ|често постављана питања]].
Нажалост, документација може бити понекад застарела.
Ако постоји нешто за шта мислите да би требало да можете да урадите, али не знате како, не устручавајте се да питате на [[Support|страници подршке]].

Такође можете контактирати колеге преводиоце истог језика на [[Portal_talk:$1|страници за разговор]] [[Portal:$1|портала на вашем језику]]. Ако то већ нисте урадили, [[Special:Preferences|промените језик корисничког интерфејса на језик на који желите да преводите]], тако да вики буде у стању да прикажи најбитније везе.",
	'translate-fs-email-text' => 'Наведите адресу е-поште у [[Special:Preferences|вашим подешавањима]] и потврдите је са мејла који ће вам бити послат.

Ово омогућава другим корисницима да вас контактирају путем е-поште.
Такође ћете добијати билтене највише једном месечно.
Ако не желите да примате новости, можете то да онемогућите на картици „{{int:prefs-personal}}“ у вашим [[Special:Preferences|подешавањима]].',
);

/** Sundanese (Basa Sunda)
 * @author Kandar
 */
$messages['su'] = array(
	'translate-fs-pagetitle-done' => ' - anggeus!',
	'translate-fs-pagetitle' => 'Sulap mitembeyan - $1',
	'translate-fs-signup-title' => 'Daptar',
	'translate-fs-settings-title' => 'Setél préferénsi anjeun',
	'translate-fs-userpage-title' => 'Jieun kaca pamaké anjeun',
	'translate-fs-permissions-title' => 'Ménta kawenangan panarjamah',
	'translate-fs-target-title' => 'Mimitian narjamahkeun!',
	'translate-fs-email-title' => 'Konfirmasi alamat surélék anjeun',
);

/** Swedish (svenska)
 * @author Fredrik
 * @author Jopparn
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'firststeps' => 'Komma igång',
	'firststeps-desc' => '[[Special:FirstSteps|Särskild sida]] för att få användare att komma igång med en wiki med hjälp av översättningstillägget',
	'translate-fs-pagetitle-done' => ' – klart!',
	'translate-fs-pagetitle-pending' => ' - pågående',
	'translate-fs-pagetitle' => 'Guide för att komma igång - $1',
	'translate-fs-signup-title' => 'Skapa ett konto',
	'translate-fs-settings-title' => 'Konfigurera inställningar',
	'translate-fs-userpage-title' => 'Skapa din användarsida',
	'translate-fs-permissions-title' => 'Ansök om översättarbehörigheter',
	'translate-fs-target-title' => 'Börja översätta!',
	'translate-fs-email-title' => 'Bekräfta din e-postadress',
	'translate-fs-intro' => "Välkommen till guiden för att komma igång med {{SITENAME}}. Du kommer att vägledas stegvis i hur man blir översättare. När du är färdig kommer du att kunna översätta ''gränssnittsmeddelanden'' av alla projekt som stöds av {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Välj ett språk',
	'translate-fs-settings-planguage' => 'Huvudspråk:',
	'translate-fs-settings-planguage-desc' => 'Huvudspråket fungerar som ditt gränssnittsspråk på denna wiki
och som standardspråk för översättningar.',
	'translate-fs-settings-slanguage' => 'Hjälpspråk $1:',
	'translate-fs-settings-slanguage-desc' => 'Det kan vara möjligt att visa översättningar av meddelanden på andra språk i översättningsredigeraren.
Här kan du välja vilka språk, om några, du skulle vilja se.',
	'translate-fs-settings-submit' => 'Spara inställningar',
	'translate-fs-userpage-level-N' => 'Mitt modersmål är',
	'translate-fs-userpage-level-5' => 'Jag är en professionell översättare av',
	'translate-fs-userpage-level-4' => 'Jag är lika bra som en modersmålstalare',
	'translate-fs-userpage-level-3' => 'Jag har god kunskap om',
	'translate-fs-userpage-level-2' => 'Jag har måttlig kunskap om',
	'translate-fs-userpage-level-1' => 'Jag kan lite',
	'translate-fs-userpage-help' => 'Var god ange dina språkkunskaper och berätta någonting om dig själv. Om du kan fler än fem språk kan du lägga till fler senare.',
	'translate-fs-userpage-submit' => 'Skapa din användarsida',
	'translate-fs-userpage-done' => 'Mycket bra! Du har nu en användarsida.',
	'translate-fs-permissions-planguage' => 'Huvudspråk:',
	'translate-fs-permissions-help' => 'Nu behöver du skicka en begäran för att läggas till i översättningsgruppen.
Välj huvudspråket du kommer att översätta till.

Du kan nämna andra språk och andra bemärkningar i textrutan nedan.',
	'translate-fs-permissions-pending' => 'Din begäran har skickats till [[$1]] och någon från sidans personal kommer att kontrollera den så fort som möjligt.
Om du bekräftar din e-postadress kommer du få ett e-postmeddelande så fort det händer.',
	'translate-fs-permissions-submit' => 'Skicka begäran',
	'translate-fs-target-text' => 'Grattis! Nu kan du börja översätta.

Var inte rädd om det fortfarande känns nytt och främmande för dig.
På sidan [[Project list|Projektlista]] finns en översikt över projekt du kan bidra med översättningar till. De flesta projekt har en sida med en kort beskrivning och en länk "\'\'Översätt det här projektet\'\'" som tar dig till en sida som listar alla oöversatta meddelanden.
Det finns även en förteckning över alla meddelandegrupper med [[Special:LanguageStats|den aktuella översättningsstatusen för ett språk]].

Om du känner att du behöver förstå mer innan du börjar översätta kan du läsa igenom [[FAQ|Vanliga frågor]].
Tyvärr kan dokumentationen vara föråldrad ibland.
Om det finns något som du tror att du skulle kunna göra men inte lyckas ta på reda på hur, så tveka inte att fråga på [[Support|supportsidan]].

Du kan också ta kontakt med de andra översättarna av samma språk på [[Portal:$1|din språkportals]] [[Portal_talk:$1|diskussionssida]].
Portalen länkar till språket i din nuvarande [[Special:Preferences|språkinställning]].
Du kan ändra om det behövs.',
	'translate-fs-email-text' => 'Ange din e-postadress i [[Special:Preferences|dina inställningar]] och bekräfta den genom det e-postmeddelande som skickas till dig.

Detta gör det möjligt för andra användare att kontakta dig via e-post.
Du kommer också att få ett nyhetsbrev högst en gång i månaden.
Om du inte vill få några nyhetsbrev så kan kan välja bort dem under fliken "{{int:prefs-personal}}" i dina [[Special:Preferences|inställningar]].',
);

/** Swahili (Kiswahili)
 * @author Kwisha
 * @author Stephenwanjau
 */
$messages['sw'] = array(
	'firststeps' => 'Hatua za kwanza',
	'translate-fs-pagetitle-done' => '- imefanyika!',
	'translate-fs-signup-title' => 'Jisajili',
	'translate-fs-target-title' => 'Anza kutafsiri!',
	'translate-fs-email-title' => 'Dhibitisha anwani yako ya barua pepe',
	'translate-fs-selectlanguage' => 'Chagua lugha',
	'translate-fs-settings-planguage' => 'Lugha ya msingi:',
	'translate-fs-settings-submit' => 'Hifadhi mapendekezo',
	'translate-fs-permissions-planguage' => 'Lugha ya msingi:',
	'translate-fs-permissions-submit' => 'Tuma ombi',
);

/** Tamil (தமிழ்)
 * @author Karthi.dr
 * @author Shanmugamp7
 * @author மதனாஹரன்
 */
$messages['ta'] = array(
	'firststeps' => 'முதற் படிகள்',
	'translate-fs-pagetitle-done' => ' - ஆச்சு!',
	'translate-fs-pagetitle-pending' => ' - நிலுவையில்',
	'translate-fs-signup-title' => 'பதிவுசெய்',
	'translate-fs-settings-title' => 'உங்கள் விருப்பத்தேர்வுகளை அமைக்கவும்',
	'translate-fs-userpage-title' => 'உங்கள் பயனர் பக்க்கத்தை உருவாக்கவும்',
	'translate-fs-permissions-title' => 'மொழிபெயர்ப்பாளர் அனுமதியைக் கோரவும்',
	'translate-fs-target-title' => 'மொழிபெயர்க்கத் தொடங்கவும்!',
	'translate-fs-email-title' => 'உங்கள் மின்னஞ்சல் முகவரியை உறுதிப்படுத்தவும்',
	'translate-fs-selectlanguage' => 'ஒரு மொழியைத் தேர்ந்தெடு',
	'translate-fs-settings-planguage' => 'முதன்மை மொழி:',
	'translate-fs-settings-slanguage' => 'துணை மொழி $1:',
	'translate-fs-settings-submit' => 'விருப்பங்களை சேமி',
	'translate-fs-userpage-level-N' => 'எனது தாய்மொழி',
	'translate-fs-userpage-level-5' => 'நான் தொழில்முறையில் மொழிபெயர்க்கும் மொழி',
	'translate-fs-userpage-level-1' => 'எனக்குச் சிறிதளவு தெரியும்',
	'translate-fs-userpage-submit' => 'என் பயனர் பக்கத்தை உருவாக்கு', # Fuzzy
	'translate-fs-userpage-done' => 'நன்கே முடிந்தது! நீங்கள் இப்போது ஒரு பயனர் பக்கத்தைக் கொண்டுள்ளீர்கள்.',
	'translate-fs-permissions-planguage' => 'முதன்மை மொழி:',
	'translate-fs-permissions-submit' => 'வேண்டுகோளை அனுப்பவும்',
);

/** Tulu (ತುಳು)
 * @author VASANTH S.N.
 */
$messages['tcy'] = array(
	'firststeps' => 'ಸುರುತ ಪಜ್ಜೆಲು',
	'translate-fs-selectlanguage' => 'ಒಂಜಿ ಬಾಸೆನ್ ಆಯ್ಕೆ ಮಲ್ಪುಲೆ',
	'translate-fs-settings-planguage' => 'ಪ್ರಾಥಮಿಕೆ ಬಾಸೆ',
	'translate-fs-settings-slanguage' => 'ಸಹಾಯಕ ಬಾಸೆ: $1',
	'translate-fs-userpage-level-N' => 'ಎನ್ನ ಮಾತೃಭಾಸೆ',
);

/** Telugu (తెలుగు)
 * @author Chaduvari
 * @author Veeven
 */
$messages['te'] = array(
	'firststeps' => 'మొదటి అడుగులు',
	'translate-fs-pagetitle-done' => ' - పూర్తయ్యింది!',
	'translate-fs-signup-title' => 'నమోదు',
	'translate-fs-settings-title' => 'మీ అభిరుచులను అమర్చుకోండి',
	'translate-fs-userpage-title' => 'మీ వాడుకరి పుటని సృష్టించుకోండి',
	'translate-fs-permissions-title' => 'అనువాద అనుమతులకై అభ్యర్థించండి',
	'translate-fs-target-title' => 'అనువదించడం మొదలుపెట్టండి!',
	'translate-fs-email-title' => 'మీ ఈమెయిలు చిరునామాని నిర్ధారించండి',
	'translate-fs-intro' => '{{SITENAME}} యొక్క తొలి అడుగుల విజార్డుకు స్వాగతం.
అంచెలంచెలుగా అనువాదకుడిగా తయారయే విధానం గురించి మీకిక్కడ మార్గదర్శకత్వం లభిస్తుంది.
చివరికి, {{SITENAME}} లో మద్దతు ఉన్న అన్ని ప్రాజెక్టుల్లోను "ఇంటరుఫేసు సందేశాల"ను అనువదించే సామర్ధ్యం మీకు లభిస్తుంది.',
	'translate-fs-selectlanguage' => 'ఒక భాషను ఎంచుకోండి',
	'translate-fs-settings-planguage' => 'ప్రధాన  భాష:',
	'translate-fs-settings-slanguage' => 'సహాయిక భాష $1:',
	'translate-fs-settings-submit' => 'అభిరుచులను భద్రపరచు',
	'translate-fs-userpage-submit' => 'నా వాడుకరి పుటని సృష్టించు', # Fuzzy
	'translate-fs-userpage-done' => 'భళా! మీకు ఇప్పుడు వాడుకరి పుట ఉంది.',
	'translate-fs-permissions-planguage' => 'ప్రధాన  భాష:',
);

/** Thai (ไทย)
 * @author Passawuth
 */
$messages['th'] = array(
	'translate-fs-pagetitle-done' => 'เรียบร้อย!',
	'translate-fs-signup-title' => 'สมัครสมาชิก',
	'translate-fs-settings-title' => 'ตั้งค่าการใช้งาน',
	'translate-fs-userpage-title' => 'สร้างหน้าผู้ใช้ของคุณ',
	'translate-fs-permissions-title' => 'ขออนุญาตแปล',
	'translate-fs-target-title' => 'เริ่มต้นแปล!',
	'translate-fs-email-title' => 'ยืนยันอีเมล',
	'translate-fs-userpage-submit' => 'สร้างหน้าผู้ใช้ของฉัน', # Fuzzy
	'translate-fs-userpage-done' => 'ตอนนี้คุณมีหน้าผู้ใช้ของคุณเองแล้ว',
);

/** Tagalog (Tagalog)
 * @author AnakngAraw
 */
$messages['tl'] = array(
	'firststeps' => 'Unang mga hakbang',
	'firststeps-desc' => '[[Special:FirstSteps|Natatanging pahina]] upang magawang magsimula ang mga tagagamit sa isang wiki sa pamamagitan ng dugtong na Pagsasalinwika',
	'translate-fs-pagetitle-done' => ' - gawa na!',
	'translate-fs-pagetitle-pending' => '- nakabinbin',
	'translate-fs-pagetitle' => 'Masalamangkang pagsisimula - $1',
	'translate-fs-signup-title' => 'Magpatala',
	'translate-fs-settings-title' => 'Isaayos ang mga nais mo',
	'translate-fs-userpage-title' => 'Likhain ang pahina mo ng tagagamit',
	'translate-fs-permissions-title' => 'Humiling ng mga pahintulot na pangtagapagsalinwika',
	'translate-fs-target-title' => 'Magsimulang magsalinwika!',
	'translate-fs-email-title' => 'Tiyakin ang tirahan mo ng e-liham',
	'translate-fs-intro' => "Maligayang pagdating sa masalamangkang unang mga hakbang ng {{SITENAME}}. 
Hakbang-hakbang na gagabayan ka sa proseso ng pagiging isang tagapagsalinwika.
Sa huli, makakapagsalinwika ka ng ''mga mensahe ng ugnayang-mukha'' ng lahat ng tinatangkilik na mga proyekto sa {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Pumili ng isang wika',
	'translate-fs-settings-planguage' => 'Pangunahing wika:',
	'translate-fs-settings-planguage-desc' => 'Ang pangunahing wika ay gumaganap din bilang wikang pang-ugnayang mukha mo sa wiking ito
at bilang isang likas na nakatakdang puntiryang wika para sa mga pagsasalinwika.',
	'translate-fs-settings-slanguage' => 'Katulong na wikang $1:',
	'translate-fs-settings-slanguage-desc' => 'Maaaring magpakita ng mga salinwika ng mga mensahe sa ibang mga wika sa loob ng patnugot ng salinwika.
Makakapili ka rito ng kung anong mga wika, kung mayroon, na nais mong makita.',
	'translate-fs-settings-submit' => 'Sagipin ang mga nais',
	'translate-fs-userpage-level-N' => 'Isa akong katutubong tagapagsalita ng',
	'translate-fs-userpage-level-5' => 'Isa akong propesyunal na tagapagsalinwika ng',
	'translate-fs-userpage-level-4' => 'Alam ko ito na katulad ng isang katutubong tagapagsalita',
	'translate-fs-userpage-level-3' => 'Mayroon akong mabuting katatasan sa',
	'translate-fs-userpage-level-2' => 'Mayroon akong bahagyang katatasan sa',
	'translate-fs-userpage-level-1' => 'May alam akong kaunti',
	'translate-fs-userpage-help' => 'Paki ipahiwatig ang mga kasanayang mo sa wika at magsabi sa amin ng ilang bagay patungkol sa iyong sarili. Kung maalam ka sa mahigit pa sa limang mga wika ay maaari ka pang magdagdag sa pagdaka.', # Fuzzy
	'translate-fs-userpage-submit' => 'Likhain ang aking pahina ng tagagamit', # Fuzzy
	'translate-fs-userpage-done' => 'Mahusay! Mayroon ka na ngayong isang pahina ng tagagamit.',
	'translate-fs-permissions-planguage' => 'Pangunahing wika:',
	'translate-fs-permissions-help' => 'Ngayon ay kailangan mo nang maglagay ng isang kahilingan upang maidagdag sa pangkat ng tagapagsalinwika.
Piliin ang pangunahing wika na patutunguhan ng pagsasalinwika mo.

Mababanggit mo ang iba pang mga wika at iba pang mga pahayag sa loob ng kahon ng tekstong nasa ibaba.',
	'translate-fs-permissions-pending' => 'Ang kahilingan mo ay naipasa na sa [[$1]] at mayroong isang tao mula sa tauhan ng pook na magsusuri nito sa lalong madaling panahon.
Kapag tiniyak mo ang iyong tirahan ng e-liham, makakatanggap ka ng isang pagpapabatid ng e-liham kapag nangyari na ito.',
	'translate-fs-permissions-submit' => 'Ipadala ang kahilingan',
	'translate-fs-target-text' => 'Maligayang bati!
Makapagsisimula ka na sa pagsasalinwika.

Huwag matakot kung bago at nakakalito pa rin ito sa pakiramdam mo.
Doon sa [[Project list|Listahan ng Proyekto]] ay mayroong isang malawakang pagtalakay ng mga proyektong mapag-aambagan mo ng mga salinwika.
Karamihan sa mga proyekto ay mayroong isang maiksing pahina ng paglalarawan na mayroong kawing na "\'\'Isalinwika ang proyektong ito\'\'", na magdadala sa iyo papunta sa isang pahinang naglilista ng lahat ng mga mensaheng hindi pa naisasalinwika.
Makakakuha rin ang isang listahan ng lahat ng mga pangkat ng mensahe na mayroong [[Special:LanguageStats|pangkasalukuyang katayuan ng salinwika para sa isang wika]].

Kung sa pakiramdam mo ay kailangan mong umunawa ng mas marami pa bago ka magsimulang magsalinwika, maaari mong basahin ang [[FAQ|Mga malilimit na itanong]].
Sa kasamaang-palad, ang kasulatan ay maaaring hindi na napapanahon kung minsan.
Kung mayroong kang naiisip na maaari mong gawin, subalit hindi malaman kung papaano, huwag mag-alinlangang itanong ito roon sa [[Support|pahina ng suporta]].

Maaari ka ring makipag-ugnayan sa mga kasamahan mong tagapagsalinwika na nasa kaparehong wika roon sa [[Portal_talk:$1|pahina ng usapan]] ng iyong [[Portal:$1|lagusan ng wika mo]].
Kung hindi mo pa nagagawa ito, [[Special:Preferences|baguhin mo ang wika ng ugnayang-mukha upang maging wikang nais mong pagsalinan ng wika]], upang magawa ng wiki na maipakita ang pinaka may kaugnayang mga kawing para sa iyo.',
	'translate-fs-email-text' => 'Paki ibigay ang tirahan mo ng e-liham sa [[Special:Preferences|mga nais mo]] at tiyakin ito mula sa e-liham na ipinadala sa iyo.

Nagpapahintulot ito sa ibang mga tagagamit na makapag-ugnayan sa iyo sa pamamagitan ng e-liham.
Makakatanggap ka rin ng mga pahayagang paliham, maaari mong piliing huwag tumanggap nito sa loob ng panlaylay na "{{int:prefs-personal}}" ng iyong [[Special:Preferences|mga kanaisan]].',
);

/** Turkish (Türkçe)
 * @author Incelemeelemani
 */
$messages['tr'] = array(
	'firststeps' => 'İlk adımlar',
	'translate-fs-pagetitle-done' => ' - tamam!',
	'translate-fs-pagetitle-pending' => ' - bekliyor',
	'translate-fs-signup-title' => 'Kaydol',
);

/** Uyghur (Arabic script) (ئۇيغۇرچە)
 * @author Sahran
 */
$messages['ug-arab'] = array(
	'firststeps' => 'تۇنجى قەدەم',
	'firststeps-desc' => 'ئىشلەتكۈچىنىڭ ۋىكى تەرجىمىسىنى باشلايدىغان [[Special:FirstSteps|يېتەكلەش بېتى]]',
	'translate-fs-pagetitle-done' => ' - تامام!',
	'translate-fs-pagetitle-pending' => ' - بەلگىلەنمىگەن',
	'translate-fs-pagetitle' => 'باشلاش يېتەكچىسىگە ئېرىش - $1',
	'translate-fs-signup-title' => 'خەتلىتىش',
	'translate-fs-settings-title' => 'مايىللىقىڭىزنى سەپلەڭ',
	'translate-fs-userpage-title' => 'ئىشلەتكۈچى بېتىڭىزنى قۇرۇڭ',
	'translate-fs-permissions-title' => 'تەرجىمە قىلىش ھوقۇق ئىلتىماسى',
	'translate-fs-target-title' => 'تەرجىمە قىلىشنى باشلا!',
	'translate-fs-email-title' => 'ئېلخەت مەنزىلىڭىزنى جەزملەڭ',
	'translate-fs-intro' => "{{SITENAME}} باشلانغۇچ يېتەكچىسىگە مەرھابا.
سىزنى قانداق قىلىپ تەرجىمان بولۇشقا يېتەكلەيدۇ.
ئاخىرىدا {{SITENAME}}دىكى ھەممە قۇرۇلۇشلارنىڭ ''ئارايۈز ئۇچۇرى''نى تەرجىمە قىلالايسىز.",
	'translate-fs-selectlanguage' => 'تىلدىن بىرنى تاللاڭ',
	'translate-fs-settings-planguage' => 'ئاساسىي تىل:',
	'translate-fs-settings-planguage-desc' => 'ئاساسىي تىل بۇ ۋىكى قۇرۇلۇشىدىكى ئىشلەتكۈچى ئارايۈزى قىلىدۇ،
تەرجىمە قىلىدىغان تىلنىڭ كۆڭۈلدىكى نىشان تىلى بولىدۇ.',
	'translate-fs-settings-slanguage' => '$1 ياردەمچى تىل:',
	'translate-fs-settings-slanguage-desc' => 'تەرجىمە تەھرىرلىگۈچتە باشقا تىلدىكى تەرجىمە ئۇچۇرىنى كۆرسىتەلەيدۇ.
بۇ جايدا كۆرسەتمەكچى بولغان تىلنى تاللىيالايسىز.',
	'translate-fs-settings-submit' => 'مايىللىق ساقلا',
	'translate-fs-userpage-level-N' => 'ئانا تىلىم',
	'translate-fs-userpage-level-5' => 'مەن كەسپىي تەرجىمە قىلالايدىغان تىل',
	'translate-fs-userpage-level-4' => 'ئانا تىلىمدەك راۋان',
	'translate-fs-userpage-level-3' => 'مەن ئادەتتىكىدەك پىششىق',
	'translate-fs-userpage-level-2' => 'مەن ئوتتۇرىھال پىششىق',
	'translate-fs-userpage-level-1' => 'ئازراق بىلىمەن',
	'translate-fs-permissions-planguage' => 'ئاساسىي تىل:',
);

/** Ukrainian (українська)
 * @author A1
 * @author Andriykopanytsia
 * @author Base
 * @author Hypers
 * @author Olvin
 * @author Тест
 */
$messages['uk'] = array(
	'firststeps' => 'Перші кроки',
	'firststeps-desc' => '[[Special:FirstSteps|Спеціальна сторінка]], яка полегшує новим користувачам початок роботи з використанням розширення Translate',
	'translate-fs-pagetitle-done' => ' - зроблено!',
	'translate-fs-pagetitle-pending' => '- очікування',
	'translate-fs-pagetitle' => 'Майстер "Початок роботи" - $1',
	'translate-fs-signup-title' => 'Зареєструйтеся',
	'translate-fs-settings-title' => 'Встановіть ваші налаштування',
	'translate-fs-userpage-title' => 'Створіть вашу сторінку користувача',
	'translate-fs-permissions-title' => 'Зробіть запит на права перекладача',
	'translate-fs-target-title' => 'Почніть перекладати!',
	'translate-fs-email-title' => 'Підтвердіть вашу адресу електронної пошти',
	'translate-fs-intro' => 'Ласкаво просимо до майстра "перші кроки" проекту {{SITENAME}}.
Крок за кроком майстер проведе вас шляхом становлення як перекладача.
Зрештою, ви зможете перекладати інтерфейсні повідомлення усіх проектів, що підтримуються на {{SITENAME}}.',
	'translate-fs-selectlanguage' => 'Оберіть мову',
	'translate-fs-settings-planguage' => 'Основна мова:',
	'translate-fs-settings-planguage-desc' => 'Основна мова дублює мову інтерфейсу цієї вікі
і є мовою за замовчуванням для перекладу.',
	'translate-fs-settings-slanguage' => 'Допоміжні мови $1:',
	'translate-fs-settings-slanguage-desc' => 'Це дозволяє бачити переклади повідомлень іншими мовами в інтерфейсі перекладача.
Тут можна вибрати, які саме мови ви хотіли б бачити.',
	'translate-fs-settings-submit' => 'Зберегти налаштування',
	'translate-fs-userpage-level-N' => 'Моя рідна мова',
	'translate-fs-userpage-level-5' => 'Я - професійний перекладач з',
	'translate-fs-userpage-level-4' => 'Досконало володію',
	'translate-fs-userpage-level-3' => 'Добре володію',
	'translate-fs-userpage-level-2' => 'Володію на середньому рівні',
	'translate-fs-userpage-level-1' => 'Трішки знаю',
	'translate-fs-userpage-help' => "Будь ласка, вкажіть свої знання мов і розкажіть трохи про себе. Якщо ви знаєте більше, ніж п'ять мов, ви зможете додати їх пізніше.",
	'translate-fs-userpage-submit' => 'Створіть Вашу сторінку користувача',
	'translate-fs-userpage-done' => 'Чудово! Тепер у вас є сторінка користувача.',
	'translate-fs-permissions-planguage' => 'Основна мова:',
	'translate-fs-permissions-help' => 'Тепер вам потрібно розмістити запит для вступу в групу перекладачів.
Виберіть основну мову, на яку Ви плануєте перекладати.

Можна вказати інші мови і примітки в текстовому полі нижче.',
	'translate-fs-permissions-pending' => 'Ваш запит подано [[$1]], і хтось із співробітників сайту перевірить її найближчим часом.
Якщо ви підтвердите свою адресу електронної пошти, Ви отримаєте повідомлення по електронній пошті, як тільки це станеться.',
	'translate-fs-permissions-submit' => 'Надіслати запит',
	'translate-fs-target-text' => 'Вітаємо!
Тепер ви можете розпочати перекладати.

Не турбуйтеся, якщо це досі здається вам новим і заплутаним.
В [[Project list|переліку проектів]] є огляд проектів, яким ви можете допомогти з перекладами.
Більшість цих проектів має сторінку з невеличким описом та посиланням "\'\'Translate this project\'\'", яке приведе Вас на сторінку з переліком усіх неперекладених повідомлень.
Також доступний список всіх груп повідомлень з [[Special:LanguageStats|поточним статусом перекладу для цієї мови]].

Якщо ви відчуваєте, що вам необхідно отримати більше інформації, перш ніж приступити до перекладу, ви можете прочитати [[FAQ|часті запитання]].
На жаль, іноді документація може бути застарілою.
Якщо ви думаєте, що повинна бути можливість щось зробити, але не можете дізнатися як, не вагайтеся питати про це на [[Support|сторінці підтримки]].

Ви також можете звернутися до колег - перекладачів тієї ж мови на [[Portal_talk:$1|сторінці обговорення]] [[Portal:$1|порталу вашої мови]].
Якщо ви ще не зробили цього, [[Special:Preferences|змініть мову вашого інтерфейсу користувача на ту, якою хочете перекладати]], щоб у вікі була змога показувати найбільш відповідні для Вас посилання.',
	'translate-fs-email-text' => 'Будь ласка, введіть Вашу адресу електронної пошти в [[[Special:Preferences|налаштуваннях]] і підтвердіть її з листа, який буде вам надіслано.

Це дозволить іншим користувачам зв\'язуватися з вами електронною поштою.
Ви також будете отримувати розсилку новин не частіше одного разу на місяць.
Якщо ви не хочете отримувати розсилку новин, ви можете відмовитися від неї у вкладці "{{int:prefs-personal}}" ваших [Special:Preferences|налаштувань]].',
);

/** Urdu (اردو)
 * @author පසිඳු කාවින්ද
 */
$messages['ur'] = array(
	'firststeps' => 'پہلا قدم',
	'translate-fs-signup-title' => 'سائن اپ کریں',
	'translate-fs-selectlanguage' => 'ایک زبان منتخب',
	'translate-fs-settings-planguage' => 'بنیادی زبان:',
	'translate-fs-settings-submit' => 'ترجیحات کو محفوظ کریں',
	'translate-fs-userpage-level-1' => 'میں جانتا ہوں کہ ایک چھوٹا سا',
	'translate-fs-permissions-planguage' => 'بنیادی زبان:',
	'translate-fs-permissions-submit' => 'بھیجنے کی درخواست',
);

/** Uzbek (oʻzbekcha)
 * @author CoderSI
 * @author Sociologist
 */
$messages['uz'] = array(
	'firststeps' => 'Birinchi qadamlar',
	'firststeps-desc' => "Vikining yangi ishtirokchilari uchun tarjima kengaytmasi o'rnatilgan [[Special:FirstSteps|maxsus sahifa]]",
	'translate-fs-pagetitle-done' => ' — bajarildi!',
	'translate-fs-pagetitle-pending' => ' — kutishda',
	'translate-fs-pagetitle' => "Boshlang'ich o'rganish dasturi - $1",
	'translate-fs-signup-title' => "Ro'yxatdan o'ting",
	'translate-fs-settings-title' => "Moslamalarni o'rnating",
	'translate-fs-userpage-title' => 'Foydalanuvchi sahifangizni yarating',
	'translate-fs-permissions-title' => "Tarjimonlik huquqiga so'rov yuboring",
	'translate-fs-target-title' => 'Tarjimani boshlang!',
	'translate-fs-email-title' => "O'z elektron pochta manzilingizni tasdiqlang",
	'translate-fs-selectlanguage' => 'Tilni tanlang',
	'translate-fs-settings-planguage' => 'Asosiy til:',
	'translate-fs-settings-slanguage' => 'Yordamchi til $1:',
	'translate-fs-settings-submit' => 'Moslamalarni saqlash',
);

/** Veps (vepsän kel’)
 * @author Игорь Бродский
 */
$messages['vep'] = array(
	'firststeps' => 'Ezmäižed haškud',
	'translate-fs-pagetitle-done' => '- om tehtud!',
	'translate-fs-target-title' => "Zavot'kat kändmaha!",
	'translate-fs-email-title' => 'Vahvištoitta e-počtan adres',
	'translate-fs-selectlanguage' => "Valita kel'",
	'translate-fs-settings-planguage' => "Aluskel'",
	'translate-fs-settings-slanguage' => "$1. abukel':",
	'translate-fs-settings-submit' => 'Kaita järgendused',
	'translate-fs-userpage-level-N' => "Minun mamankel'",
	'translate-fs-userpage-level-5' => 'Olen professionaline kändai',
	'translate-fs-userpage-level-4' => 'Mahtan pagišta kut kelenkandai',
	'translate-fs-userpage-level-3' => 'Mahtan hüvin',
	'translate-fs-userpage-level-2' => 'Mahtan keskmäras',
	'translate-fs-userpage-level-1' => 'Mahtan vähäižel',
	'translate-fs-userpage-submit' => "Säta minun personaline lehtpol'", # Fuzzy
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 * @author Vinhtantran
 */
$messages['vi'] = array(
	'firststeps' => 'Các bước đầu',
	'firststeps-desc' => '[[Special:FirstSteps|Trang đặc biệt]] để giúp những người mơi đến bắt đầu sử dụng phần mở rộng Dịch',
	'translate-fs-pagetitle-done' => ' – đã hoàn tất!',
	'translate-fs-pagetitle-pending' => ' – đang chờ',
	'translate-fs-pagetitle' => 'Trình Thuật sĩ Bắt đầu – $1',
	'translate-fs-signup-title' => 'Đăng ký',
	'translate-fs-settings-title' => 'Cấu hình tùy chọn',
	'translate-fs-userpage-title' => 'Tạo trang cá nhân',
	'translate-fs-permissions-title' => 'Yêu cầu quyền biên dịch viên',
	'translate-fs-target-title' => 'Tiến hành dịch!',
	'translate-fs-email-title' => 'Xác nhận địa chỉ thư điện tử',
	'translate-fs-intro' => "Hoan nghênh bạn đến với trình hướng dẫn sử dụng {{SITENAME}}.
Bạn sẽ được hướng dẫn từng bước quá trình trở thành biên dịch viên.
Cuối cùng bạn sẽ có thể dịch được ''thông điệp giao diện'' của tất cả các dự án được hỗ trợ tại {{SITENAME}}.",
	'translate-fs-selectlanguage' => 'Chọn một ngôn ngữ',
	'translate-fs-settings-planguage' => 'Ngôn ngữ chính:',
	'translate-fs-settings-planguage-desc' => 'Ngôn ngữ chính cũng là ngôn ngữ giao diện khi bạn sử dụng wiki này
và là ngôn ngữ mặc định để biên dịch sang.',
	'translate-fs-settings-slanguage' => 'Ngôn ngữ bổ trợ $1:',
	'translate-fs-settings-slanguage-desc' => 'Để hiển thị bản dịch tương ứng trong ngôn ngữ khác trong hộp biên dịch, chọn các ngôn ngữ bổ trợ tại đây.',
	'translate-fs-settings-submit' => 'Lưu tùy chọn',
	'translate-fs-userpage-level-N' => 'Ngôn ngữ mẹ đẻ của tôi là',
	'translate-fs-userpage-level-5' => 'Tôi là một chuyên gia biên dịch',
	'translate-fs-userpage-level-4' => 'Tôi biên dịch gần như ngôn ngữ mẹ đẻ sang',
	'translate-fs-userpage-level-3' => 'Tôi biên dịch lưu loát sang',
	'translate-fs-userpage-level-2' => 'Tôi biên dịch với trình độ trung bình sang',
	'translate-fs-userpage-level-1' => 'Tôi biên dịch với trình độ cơ bản sang',
	'translate-fs-userpage-help' => 'Xin vui lòng tự giới thiệu và cho biết khả năng sử dụng các ngôn ngữ. Nếu bạn sử dụng hơn năm thứ tiếng, bạn có thể bổ sung thêm sau này.', # Fuzzy
	'translate-fs-userpage-submit' => 'Tạo trang cá nhân',
	'translate-fs-userpage-done' => 'Tốt lắm! Bây giờ bạn đã có trang người dùng.',
	'translate-fs-permissions-planguage' => 'Ngôn ngữ chính:',
	'translate-fs-permissions-help' => 'Bây giờ bạn cần phải yêu cầu được thêm vào nhóm biên dịch viên.
Chọn ngôn ngữ chính mà bạn sẽ biên dịch sang.

Bạn cũng có thể đề cập đến ngôn ngữ khác và cho biết thêm thông tin trong hộp ở dưới.',
	'translate-fs-permissions-pending' => 'Lời yêu cầu của bạn đã được gửi cho [[$1]]. Một nhân viên trang sẽ duyệt qua nó không lâu.
Nếu bạn xác nhận địa chỉ thư điện tử của bạn, bạn sẽ nhận một thư điện tử báo cho bạn ngay khi nó được duyệt qua.',
	'translate-fs-permissions-submit' => 'Gửi yêu cầu',
	'translate-fs-target-text' => 'Chúc mừng bạn!
Giờ bạn đã có thể bắt đầu biên dịch.

Đừng e ngại nếu bạn còn cảm thấy bỡ ngỡ và rối rắm.
Tại [[Project list]] có danh sách tổng quan các dự án mà bạn có thể đóng góp bản dịch vào.
Phần lớn các dự án đều có một trang miêu tả ngắn cùng với liên kết "\'\'Dịch dự án này\'\'", nó sẽ đưa bạn đến trang trong đó liệt kê mọi thông điệp chưa dịch.
Danh sách tất cả các nhóm thông điệp cùng với [[Special:LanguageStats|tình trạng biên dịch hiện tại của một ngôn ngữ]] cũng có sẵn.

Nếu bạn cảm thấy bạn cần phải hiểu rõ hơn trước khi bắt đầu dịch, bạn có thể đọc [[FAQ|các câu hỏi thường gặp]].
Rất tiếc là văn bản này đôi khi hơi lạc hậu.
Nếu có gì bạn nghĩ bạn nên làm, nhưng không biết cách, đừng do dự hỏi nó tại [[Support|trang hỗ trợ]].

Bạn cũng có thể liên hệ với đồng nghiệp biên dịch của cùng ngôn ngữ ở [[Portal_talk:$1|trang thảo luận]] của [[Portal:$1|cổng ngôn ngữ của bạn]].
Cổng này liên kết đến [[Special:Preferences|tùy chọn ngôn ngữ của bạn]].
Xin hãy thay đổi nếu cần.',
	'translate-fs-email-text' => 'Xin cung cấp cho chúng tôi địa chỉ thư điện tử của bạn trong [[Special:Preferences|tùy chọn cá nhân]] và xác nhận nó trong thư chúng tôi gửi cho bạn.

Nó cho phép người khác liên hệ với bạn qua thư.
Bạn cũng sẽ nhận được thư tin tức tối đa một bức một tháng.
Nếu bạn không muốn nhận thư tin tức, bạn có thể bỏ nó ra khỏi thẻ “{{int:prefs-personal}}” trong [[Special:Preferences|tùy chọn cá nhân]].',
);

/** Yiddish (ייִדיש)
 * @author פוילישער
 */
$messages['yi'] = array(
	'firststeps' => 'ערשטע טריט',
	'translate-fs-userpage-title' => 'שאַפֿן אײַער באַניצער בלאַט',
	'translate-fs-permissions-title' => 'בעטן איבערזעצער אויטאריזאַציע',
	'translate-fs-target-title' => 'אָנהייבן איבערזעצן!',
	'translate-fs-email-title' => 'באַשטעטיקט אײַער בליצפּאָסט אַדרעס',
	'translate-fs-settings-planguage' => 'הויפטשפראך:',
	'translate-fs-userpage-level-1' => 'איך קען א ביסל',
	'translate-fs-userpage-submit' => 'שאַפֿן אײַער באַניצער בלאַט',
	'translate-fs-userpage-done' => 'גוט געפועלט! איר האט אצינד א באניצער בלאט.',
	'translate-fs-permissions-planguage' => 'הויפטשפראך:',
	'translate-fs-permissions-submit' => 'שיקן בקשה',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Anakmalaysia
 * @author Chenxiaoqino
 * @author Hydra
 * @author Hzy980512
 * @author Liangent
 * @author Mark85296341
 * @author Qiyue2001
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'firststeps' => '第一步',
	'firststeps-desc' => '让用户开始wiki翻译的[[Special:FirstSteps|引导页面]]',
	'translate-fs-pagetitle-done' => ' - 完成！',
	'translate-fs-pagetitle-pending' => '- 待定',
	'translate-fs-pagetitle' => '入门向导 - $1',
	'translate-fs-signup-title' => '注册',
	'translate-fs-settings-title' => '设置你的选项',
	'translate-fs-userpage-title' => '创建您的用户页',
	'translate-fs-permissions-title' => '请求翻译者权限',
	'translate-fs-target-title' => '开始翻译！',
	'translate-fs-email-title' => '确认您的邮箱地址',
	'translate-fs-intro' => "欢迎来到 {{SITENAME}}入门向导。
你会被指导如何成为一名翻译者。
最后你将可以翻译{{SITENAME}}里所有项目的''界面消息''.",
	'translate-fs-selectlanguage' => '选择一种语言',
	'translate-fs-settings-planguage' => '首选语言：',
	'translate-fs-settings-planguage-desc' => '该首选语言作为此维基项目的用户界面，
并成为默认的翻译目标语言。',
	'translate-fs-settings-slanguage' => '第$1辅助语言：',
	'translate-fs-settings-slanguage-desc' => '在翻译编辑器之内可以显示其他语言翻译的消息。
您可以在此选择您想显示的语言。',
	'translate-fs-settings-submit' => '保存设定',
	'translate-fs-userpage-level-N' => '我的母语是',
	'translate-fs-userpage-level-5' => '我能专业地翻译的语言是',
	'translate-fs-userpage-level-4' => '我熟练像母语者一样流利',
	'translate-fs-userpage-level-3' => '我熟练不错',
	'translate-fs-userpage-level-2' => '我熟练平平',
	'translate-fs-userpage-level-1' => '我知道一点点',
	'translate-fs-userpage-help' => '请说明您的语言能力，并告诉我们关于您自己。如果您知道超过五种语言，您以后可以添加更多。',
	'translate-fs-userpage-submit' => '创建你的用户页',
	'translate-fs-userpage-done' => '很好！现在你有了一个用户页面。',
	'translate-fs-permissions-planguage' => '主要语言：',
	'translate-fs-permissions-help' => '现在，您需要请求参加翻译组。
请选择您想参入翻译的首选语言。

您可以在以下的文本框之内提及其他语言及其他备注。',
	'translate-fs-permissions-pending' => '您的请求已提交至[[$1]]，站点管理员会尽快查阅您的请求。
如果您已验证您的电子邮箱，那么这个请求有答复后就会给您发送邮件。',
	'translate-fs-permissions-submit' => '发送请求',
	'translate-fs-target-text' => '恭喜 ！
您现在可以开始翻译。

不要害怕如果仍然认为新的和令人困惑，你。
在 [[Project list|项目列表]] 有你可以贡献的翻译项目的概述。
的大多数项目有一个简短说明页"翻译此项目 \'"的链接，将带您到一个页面，其中列出了所有未翻译的消息。
[[Special:LanguageStats|current 翻译状态的一种语言]] 所有邮件组的列表也是可用。

是否你感觉到您需要了解更多，你开始翻译之前，你可以读，[[FAQ|Frequently 问问题]]。
不幸的是文档是过时的有时。
如果有什么，你认为你应该能够做到，但是不能找出如何，不要犹豫，请在 [[Support|帮助页]]。

您也可以联系同翻译人员在语言相同的语言的 [[Portal:$1|your 语言门户]] 的 [[Portal_talk:$1|talk 页]]。
如果已经这样 [[Special:Preferences|change 您的用户界面语言，您要翻译的语言]]，做以便 wiki 是能够为您显示最相关的链接。',
	'translate-fs-email-text' => '请在[[Special:Preferences|选项]]页面留下电子邮箱地址并进行验证。

这能让其他用户通过电子邮件联系你。
你也会收到至多每月一次的电子通讯。
如果你不想收到通讯，你可以在[[Special:Preferences|选项]]"页面的{{int:prefs-personal}}"标签选择停止接收。',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Lauhenry
 * @author Mark85296341
 * @author Simon Shek
 */
$messages['zh-hant'] = array(
	'firststeps' => '第一步',
	'firststeps-desc' => '讓用戶開始維基翻譯的[[Special:FirstSteps|引導頁面]]',
	'translate-fs-pagetitle-done' => ' - 完成！',
	'translate-fs-pagetitle-pending' => '- 待定',
	'translate-fs-pagetitle' => '入門指導 - $1',
	'translate-fs-signup-title' => '註冊',
	'translate-fs-settings-title' => '設定你的偏好',
	'translate-fs-userpage-title' => '建立您的使用者頁面',
	'translate-fs-permissions-title' => '請求翻譯者權限',
	'translate-fs-target-title' => '開始翻譯！',
	'translate-fs-email-title' => '確認您的電郵地址',
	'translate-fs-intro' => "歡迎來到 {{SITENAME}} 入門指導。
你會被指導如何成為一名翻譯者。
最後你將可以翻譯 {{SITENAME}} 裡所有計畫的''介面訊息''.",
	'translate-fs-selectlanguage' => '選擇一種語言',
	'translate-fs-settings-planguage' => '主要語言：',
	'translate-fs-settings-planguage-desc' => '該首選語言作為此維基項目的用戶界面，
並成為默認的翻譯目標語言。',
	'translate-fs-settings-slanguage' => '輔助語言$1：',
	'translate-fs-settings-slanguage-desc' => '在翻譯編輯器之內可以顯示其他語言翻譯的消息。
您可以在此選擇您想顯示的語言。',
	'translate-fs-settings-submit' => '儲存設定',
	'translate-fs-userpage-level-N' => '我的母語是',
	'translate-fs-userpage-level-5' => '我能專業地翻譯的語言是',
	'translate-fs-userpage-level-4' => '我熟練像母語者一樣流利',
	'translate-fs-userpage-level-3' => '我熟練不錯',
	'translate-fs-userpage-level-2' => '我熟練平平',
	'translate-fs-userpage-level-1' => '我知道一點點',
	'translate-fs-userpage-help' => '請說明您的語言能力，並告訴我們關於您自己。如果您知道超過五種語言，您可以以後添加更多。',
	'translate-fs-userpage-submit' => '建立你的用戶頁',
	'translate-fs-userpage-done' => '很好！現在你擁有了一個使用者頁面。',
	'translate-fs-permissions-planguage' => '主要語言：',
	'translate-fs-permissions-help' => '現在，您需要請求參加翻譯組。
請選擇您想參入翻譯的首選語言。

您可以在以下的文本框之內提及其他語言及其他備註。',
	'translate-fs-permissions-pending' => '您的請求已提交至[[$1]]，站點管理員會儘快查閱您的請求。
如果您已驗證您的電子郵箱，那麼這個請求有答覆後就會給您發送郵件。',
	'translate-fs-permissions-submit' => '發送請求',
	'translate-fs-target-text' => '恭喜 ！
您現在可以開始翻譯。

如果你仍覺得不知所措，不要害怕。
在[[Project list|項目列表]] 有你可以貢獻的翻譯項目的概述。
大部分的項目有一個簡短的說明頁與“翻譯這個項目”鏈接，它將帶您到一個頁面，其中列出了所有未翻譯的消息。
 [[Special:LanguageStats|同一語言中所有未翻譯的訊息]]列表也是一個好起點。

如您開始翻譯前想了解更多，您可以去看一下[[FAQ|常見問題]]。
不幸的是文檔可能是舊版，如果你找不到答案，不要猶豫，請到[[Support|幫助頁]]發問。

您也可以在[[Portal:$1|語言門戶]] 的[[Portal_talk:$1|talk 頁]]聯繫相同語言的翻譯人員在。
請到[[Special:Preferences|偏好設定]]設定您的用戶界面和要翻譯的語言，以便wiki顯示和適合您的鏈接。',
	'translate-fs-email-text' => '請到[[Special:Preferences|偏好設定]]留下並確認您的電郵地址。
使其他譯者聯絡您，你亦可收取我們的每月電子報。

如您不想收到月刊，可以到[[Special:Preferences|偏好設定]]頁面的{{int:prefs-personal}}標籤選擇停止接收。',
);
