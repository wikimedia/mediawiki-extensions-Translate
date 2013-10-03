<?php
/**
 * Translations for the sandbox feature.
 *
 * @file
 * @license GPL-2.0+
 */

$messages = array();

/** English
 * @author Nike
 */
$messages['en'] = array(
	'translatesandbox' => 'Manage translator sandbox',
	'tsb-filter-pending' => 'Pending requests',

	// Reminders
	'tsb-reminder-title-generic' => 'Complete your introduction to become a verified translator',
	'tsb-reminder-content-generic' => 'Hi $1,

Thanks for registering with {{SITENAME}}. If you complete your test
translations, the administrators can soon grant you full translation
access.

Please come to $2 and make some more translations.',
	'tsb-request-count' => '{{PLURAL:$1|One request|$1 requests}}',
	'tsb-all-languages-button-label' => 'All languages',
	'tsb-search-requests' => 'Search requests',
	'tsb-accept-button-label' => 'Accept',
	'tsb-reject-button-label' => 'Reject',
	'tsb-reminder-link-text' => 'Send email reminder',
);

/** Message documentation (Message documentation)
 * @author Nike
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'translatesandbox' => '{{doc-special|TranslateSandbox}}',
	'tsb-filter-pending' => 'A filter option on [[Special:TranslateSandbox]].

Followed by a list of the pending requests.',
	'tsb-reminder-title-generic' => 'Subject of an email',
	'tsb-reminder-content-generic' => 'Body of an email. Parameters:
* $1 - user name of the recipient
* $2 - URL to the website',
	'tsb-request-count' => 'Label showing number of requests
*$1 - Number of requests ',
	'tsb-all-languages-button-label' => 'Button label for filtering the requests by language.',
	'tsb-search-requests' => 'Placeholder text for request search box on top of [[Special:TranslateSandbox]].',
	'tsb-accept-button-label' => 'Button label for accept button in [[Special:TranslateSandbox]]',
	'tsb-reject-button-label' => 'Button label for reject button in [[Special:TranslateSandbox]]',
	'tsb-reminder-link-text' => 'Link text for sending reminder emails about translator signup requests.',
);

/** Afrikaans (Afrikaans)
 * @author Naudefj
 */
$messages['af'] = array(
	'translatesandbox' => 'Bestuur vertaler-sandput',
	'tsb-filter-pending' => 'Uitstaande versoeke',
	'tsb-reminder-title-generic' => "Voltooi u bekendstelling om 'n geverifieerde vertaler te word",
	'tsb-reminder-content-generic' => "Hallo $1,

Dankie dat u op {{SITENAME}} geregistreer het. As u u toesvertalings voltooi, sal die administrateurs spoedig volle regte aan u toeken.

Gaan asseblief na $2 om 'n paar vertalings te maak.",
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'translatesandbox' => 'Alministrar la zona de pruebas de los traductores',
	'tsb-filter-pending' => 'Solicitúes pendientes',
	'tsb-reminder-title-generic' => 'Complete la so presentación pa convertise nun traductor comprobao',
	'tsb-reminder-content-generic' => 'Bones, $1:

Gracies por rexistrase en {{SITENAME}}. Si completa les traducciones
de prueba, Los alministradores pronto darán-y permisu de traducción
completu.

Por favor, vuelva a $2 y faiga delles traducciones más.',
);

/** Bengali (বাংলা)
 * @author Aftab1995
 */
$messages['bn'] = array(
	'translatesandbox' => 'অনুবাদক খেলাঘর পরিচালনা',
	'tsb-filter-pending' => 'অপেক্ষমান অনুরোধ',
	'tsb-reminder-title-generic' => 'একজন যাচাইকৃত অনুবাদক হতে আপনার পরিচিতি সমাপ্ত করুন',
	'tsb-reminder-content-generic' => 'প্রিয় $1,

আপনি সম্প্রতি {{SITENAME}}-এ সাইন আপ করেছেন। বিনামূল্যে অনুবাদ এবং অতিরিক্ত অনুবাদ সাহায্যকারী খুলতে আপনি মাত্র কয়েক ধাপ দূরে।

$2-এ লগ ইন করুন এবং আরো কিছু অনুবাদ করুন।', # Fuzzy
);

/** Breton (brezhoneg)
 * @author Y-M D
 */
$messages['br'] = array(
	'tsb-filter-pending' => 'Rekedoù war gortoz',
);

/** German (Deutsch)
 * @author Metalhead64
 * @author Rillke
 */
$messages['de'] = array(
	'translatesandbox' => 'Übersetzer-Spielwiese konfigurieren',
	'tsb-filter-pending' => 'Ausstehende Anfragen',
	'tsb-reminder-title-generic' => 'Vervollständige deine Einführung, um ein verifizierter Übersetzer zu werden.',
	'tsb-reminder-content-generic' => 'Hallo $1,

vielen Dank für deine Registrierung auf {{SITENAME}}. Wenn du deine Testübersetzungen
vervollständigst, werden dir bald die Administratoren einen vollen Übersetzungszugriff gewähren.

Bitte besuche $2 und erstelle einige weitere Übersetzungen.',
);

/** Spanish (español)
 * @author Fitoschido
 */
$messages['es'] = array(
	'translatesandbox' => 'Gestionar la zona de pruebas del traductor',
	'tsb-filter-pending' => 'Solicitudes pendientes',
	'tsb-reminder-title-generic' => 'Completa tu introducción para volverte un traductor verificado',
);

/** French (français)
 * @author Gomoko
 */
$messages['fr'] = array(
	'translatesandbox' => 'Gérer le bac à sable de tradution',
	'tsb-filter-pending' => 'Requêtes en attente',
	'tsb-reminder-title-generic' => 'Complétez votre présentation pour devenir un traducteur vérifié',
	'tsb-reminder-content-generic' => 'Bonjour $1,

Merci de vous être inscrit sur {{SITENAME}}. Si vous achevez vos traductions de test, les administrateurs pourront bientôt vous accorder un plein accès aux traductions.

Veuillez venir sur $2 et faire quelques traductions de plus.',
);

/** Galician (galego)
 * @author Toliño
 */
$messages['gl'] = array(
	'translatesandbox' => 'Administrar a zona de probas dos tradutores',
	'tsb-filter-pending' => 'Solicitudes pendentes',
	'tsb-reminder-title-generic' => 'Complete a súa introdución para se converter nun tradutor verificado',
	'tsb-reminder-content-generic' => 'Boas, $1:

Grazas por rexistrarse en {{SITENAME}}. Se completa as traducións
de proba, os adminitradores poderán concederlle axiña acceso completo á
tradución.

Acceda ao sistema en $2 e faga algunhas traducións máis.',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'translatesandbox' => 'ניהול ארגז חול של מתרגמים',
	'tsb-filter-pending' => 'בקשות ממתינות',
	'tsb-reminder-title-generic' => 'נא להשלים את ההיכרות שלך כדי לקבל אישור מלא לתרגם',
	'tsb-reminder-content-generic' => 'שלום $1,

תודה שנרשמת לאתר {{SITENAME}}. עם השלמת תרגומי הבדיקה שלך
המנהלים ייתנו לך גישה מלאה
לתרגום.

נשאר רק לבוא אל $2 ולעשות עוד כמה
תרגומים.',
);

/** Italian (italiano)
 * @author Beta16
 */
$messages['it'] = array(
	'translatesandbox' => 'Gestire la sandbox di traduzione',
	'tsb-filter-pending' => 'Richieste in sospeso',
	'tsb-reminder-title-generic' => "Completa l'introduzione per diventare un traduttore verificato",
	'tsb-reminder-content-generic' => "Ciao $1,

Grazie per esserti registrato su {{SITENAME}}. Una volta che avrai completato i test di traduzione, gli amministratori potranno concederti in breve tempo l'accesso completo da traduttore.

Vieni su $2 e fai alcune altre traduzioni.",
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'translatesandbox' => '翻訳者サンドボックスの管理',
	'tsb-filter-pending' => '保留中の申請',
);

/** Korean (한국어)
 * @author 아라
 */
$messages['ko'] = array(
	'translatesandbox' => '번역자 연습장 관리',
	'tsb-filter-pending' => '보류 중인 요청',
	'tsb-reminder-title-generic' => '검증된 번역자가 되려면 소개를 완료하세요',
	'tsb-reminder-content-generic' => '$1님 안녕하세요,

{{SITENAME}}에 등록해주셔서 감사합니다. 테스트 번역을
완료하면, 관리자는 곧 전체 번역 접근 권한을 부여할 수
있습니다.

$2에 와서 조금 더 번역을 해주세요.',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'translatesandbox' => 'Demm en Schpellwiß för de Övversäzer ennreeschde un verwallde.',
	'tsb-filter-pending' => 'Aanfroore en der Waadeschlang',
	'tsb-reminder-title-generic' => 'Maach Ding Sällefsvörschtällong fäädesch, öm enen beschträäteschten Övversäzzer ze wääde.',
	'tsb-reminder-content-generic' => 'Daach $1,
mer bedangke ons dat De Desch köözlesch {{ucfirst:{{GRAMMAR:em|{{ucfirst:{{SITENAME}}}}}}}} aanjemälldt häs. Wann Do jraad noch e paa Övversäzonge för et Prööve fäädesch määß, künne de Wikki_Kööbeße desch freischallde för et Övversäzze.

Bes esu jood un donn Desch op {{GRAMMAR:Dativ|$2}} enlogge un maach e paa Övversäzonge.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'translatesandbox' => 'Iwwersetzer-Sandkëscht geréieren',
	'tsb-filter-pending' => 'Ufroen am Suspens',
	'tsb-reminder-title-generic' => 'Kompletéiert Är Virstellung fir e verifizéierten Iwwersetzer ze ginn',
);

/** Macedonian (македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'translatesandbox' => 'Раководење со преведувачки песочник',
	'tsb-filter-pending' => 'Барања во исчекување',
	'tsb-reminder-title-generic' => 'Пополнете го вашето претставување и станете овластен преведувач',
	'tsb-reminder-content-generic' => 'Здраво $1,

Ви благодариме што се регистриравте на {{SITENAME}}. Пополнете ги пробните преводи, и администраторите набргу ќе ви доделат статус на преведувач.

Појдете на $2 и направете уште некои преводи.',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'translatesandbox' => 'Uruskan kotak pasir penterjemah',
	'tsb-filter-pending' => 'Permohonan yang menunggu',
	'tsb-reminder-title-generic' => 'Lengkapkan pengenalan anda untuk menjadi seorang penterjemah yang sah',
	'tsb-reminder-content-generic' => '$1,

Terima kasih kerana mendaftar untuk {{SITENAME}}. Sekiranya anda melengkapkan ujian penterjemahan ini, anda akan menerima akses penterjemah sepenuhnya dari pihak penyelia.

Sila ke $2 untuk membuat lebih banyak kerja terjemahan.',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'translatesandbox' => 'Vertalersszandbak beheren',
	'tsb-filter-pending' => 'Aanvragen in behandeling',
	'tsb-reminder-title-generic' => 'Voltooi uw introductie om vertaler te worden',
	'tsb-reminder-content-generic' => 'Hallo $1,

Bedankt voor het registreren bij {{SITENAME}}. Als u uw testvertalingen afrondt, kunnen de beheerders u snel volledige vertaaltoegang geven.

Kon alstublieft naar $2 en maak nog wat meer vertalingen.',
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'translatesandbox' => 'Gerir lo nauc de sabla de traduccion',
	'tsb-filter-pending' => 'Requèstas en espèra',
	'tsb-reminder-title-generic' => 'Completatz vòstra presentacion per venir un traductor verificat',
	'tsb-reminder-content-generic' => 'Bonjorn $1,

Mercé de vos èsser inscrich sus {{SITENAME}}. Se acabatz vòstras traduccions de tèst, los administrators poiràn lèu vos acordar un plen accès a las traduccions.

Venètz sus $2 e fasètz qualques traduccions mai.',
);

/** Romanian (română)
 * @author Minisarm
 */
$messages['ro'] = array(
	'translatesandbox' => 'Administrare cutie cu nisip traducător',
	'tsb-filter-pending' => 'Cereri în așteptare',
	'tsb-reminder-title-generic' => 'Finalizați-vă introducerea pentru a deveni un translator verificat',
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'translatesandbox' => "Gestisce 'a sandbox de traduzione",
	'tsb-filter-pending' => 'Richieste appese',
	'tsb-reminder-title-generic' => "Comblete 'a 'ndroduziona toje pe devendà 'nu traduttore verificate",
	'tsb-reminder-content-generic' => "Cià $1,

Grazie ca tè reggistrate sus a {{SITENAME}}. Ce tu comblete 'u test de traduziune, l'amministrsature ponne darte le privilegge pe l'accesse 'a traduzione comblete.

Pe piacere avìne jndr'à $2 e fà angore quacche otre traduzione.",
);

/** Russian (русский)
 * @author Kaganer
 */
$messages['ru'] = array(
	'translatesandbox' => 'Управление песочницей переводчика',
	'tsb-filter-pending' => 'Запросы, ожидающие обработки',
	'tsb-reminder-title-generic' => 'Завершите свой вводный курс, чтобы стать проверенным переводчиком.',
	'tsb-reminder-content-generic' => 'Привет, $1!

Спасибо за регистрацию на сайте «{{SITENAME}}». Если вы завершили свои пробные переводы, администраторы могут предоставить вам полный доступ к инструменту перевода.

Пожалуйста, перейдите по ссылке $2 и сделайте ещё несколько переводов.',
);

/** Swedish (svenska)
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'translatesandbox' => 'Hantera översättarsandlåda',
	'tsb-filter-pending' => 'Väntande förfrågningar',
	'tsb-reminder-title-generic' => 'Slutför din introduktion för att bli en verifierad översättare',
	'tsb-reminder-content-generic' => 'Hej $1,

Tack för din registrering på {{SITENAME}}. Om du slutför dina testöversättningar kan administratörerna snart ge dig full behörighet till att översätta.

Var god kom till $2 och gör några fler översättningar.',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'tsb-filter-pending' => 'వేచివున్న అభ్యర్థనలు',
	'tsb-reminder-title-generic' => 'తనిఖీ అయిన అనువాదకుడిగా మారడానికి మీ పరిచయాన్ని పూర్తిచేయండి',
);

/** Ukrainian (українська)
 * @author Base
 * @author Ата
 */
$messages['uk'] = array(
	'translatesandbox' => 'Керування грамайданчиком перекладачів',
	'tsb-filter-pending' => 'Запити в очікуванні',
	'tsb-reminder-title-generic' => 'Завершіть своє представлення, щоб стати перевіреним перекладачем',
	'tsb-reminder-content-generic' => 'Привіт, $1!

Дякуємо за реєстрацію у проекті {{SITENAME}}. Якщо Ви завершите свої тестові
переклади, адміністратори зможуть скоро надати Вам повні права на переклад.

Будь ласка, перейдіть на $2 і зробіть ще декілька перекладів.',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'translatesandbox' => 'Quản lý chỗ thử biên dịch',
	'tsb-filter-pending' => 'Yêu cầu đang chờ',
	'tsb-reminder-title-generic' => 'Hoàn thành lời giới thiệu của bạn để trở thành một biên dịch viên xác minh',
	'tsb-reminder-content-generic' => 'Xin chào $1,

Cám ơn bạn đã tham gia {{SITENAME}}. Sau khi bạn dịch các bản dịch kiểm tra, các bảo quản viên sẽ sớm cấp quyền biên dịch đầy đủ cho bạn.

Xin vui lòng trở lại $2 để dịch tiếp.',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Simon Shek
 */
$messages['zh-hant'] = array(
	'translatesandbox' => '管理翻譯沙盒',
	'tsb-filter-pending' => '未解決的請求',
	'tsb-reminder-title-generic' => '完成介紹後成為核實的翻譯者',
	'tsb-reminder-content-generic' => '$1：

感謝您註冊 {{SITENAME}}。完成翻譯測試後，管理員會授予您完整翻譯權限。

請來 $2 做更多的翻譯。',
);
