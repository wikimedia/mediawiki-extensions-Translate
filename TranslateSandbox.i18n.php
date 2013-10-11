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

	'translationstash' => 'Welcome',
	'translate-translationstash-welcome' => 'Welcome {{GENDER:$1|$1}}, you are a new translator',
	'translate-translationstash-welcome-note' => 'Become familiar with the translation tools by translating some randomly selected messages.',
	'translate-translationstash-initialtranslation' => 'Your initial translation',
	'translate-translationstash-skip-button-label' => 'Try another',
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
	'tsb-request-count' => 'Label showing number of requests. Parameters:
* $1 - number of requests
{{Identical|Request}}',
	'tsb-all-languages-button-label' => 'Button label for filtering the requests by language.
{{Identical|All languages}}',
	'tsb-search-requests' => 'Placeholder text for request search box on top of [[Special:TranslateSandbox]].',
	'tsb-accept-button-label' => 'Button label for accept button in [[Special:TranslateSandbox]].
{{Identical|Accept}}',
	'tsb-reject-button-label' => 'Button label for reject button in [[Special:TranslateSandbox]].
{{Identical|Reject}}',
	'tsb-reminder-link-text' => 'Link text for sending reminder emails about translator signup requests.',
	'translationstash' => 'Page title for [[Special:TranslationStash]].
{{Identical|Welcome}}',
	'translate-translationstash-welcome' => 'Title text shown for the [[Special:TranslationStash]]. Parameters:
* $1 - user name of the new translator',
	'translate-translationstash-welcome-note' => 'Title note for the [[Special:TranslationStash]].',
	'translate-translationstash-initialtranslation' => 'Header for messages showing the progress of translations in [[Special:TranslationStash]].',
	'translate-translationstash-skip-button-label' => 'Label for the skip button in translation editor',
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
	'tsb-request-count' => '{{PLURAL:$1|Eine Anfrage|$1 Anfragen}}',
	'tsb-all-languages-button-label' => 'Alle Sprachen',
	'tsb-search-requests' => 'Anfragen durchsuchen',
	'tsb-accept-button-label' => 'Akzeptieren',
	'tsb-reject-button-label' => 'Ablehnen',
	'tsb-reminder-link-text' => 'E-Mail-Erinnerung senden',
	'translationstash' => 'Willkommen',
	'translate-translationstash-welcome' => 'Danke $1, du bist {{GENDER:$1|ein neuer Übersetzer|eine neue Übersetzerin}}.',
	'translate-translationstash-welcome-note' => 'Werde mit den Übersetzungswerkzeugen vertraut, indem du einige zufällig ausgewählte Nachrichten übersetzt.',
	'translate-translationstash-initialtranslation' => 'Deine erste Übersetzung',
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
	'tsb-request-count' => '{{PLURAL:$1|Une demande|$1 demandes}}',
	'tsb-all-languages-button-label' => 'Toutes les langues',
	'tsb-search-requests' => 'Demandes de recherche',
	'tsb-accept-button-label' => 'Accepter',
	'tsb-reject-button-label' => 'Rejeter',
	'tsb-reminder-link-text' => 'Envoyer un courriel de rappel',
	'translationstash' => 'Bienvenue',
	'translate-translationstash-welcome' => 'Merci {{GENDER:$1|$1}}, vous êtes un nouveau traducteur',
	'translate-translationstash-welcome-note' => 'Familiarisez-vous avec les outils de traduction en traduisant quelques messages sélectionnés aléatoirement.',
	'translate-translationstash-initialtranslation' => 'Votre traduction initiale',
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
	'tsb-request-count' => '{{PLURAL:$1|Una richiesta|$1 richieste}}',
	'tsb-all-languages-button-label' => 'Tutte le lingue',
	'tsb-search-requests' => 'Cerca richiesta',
	'tsb-accept-button-label' => 'Accetta',
	'tsb-reject-button-label' => 'Rifiuta',
	'tsb-reminder-link-text' => 'Invia email di promemoria',
	'translationstash' => 'Benvenuto(a)',
	'translate-translationstash-welcome' => 'Grazie {{GENDER:$1|$1}}, ora sei un nuovo traduttore',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'translatesandbox' => '翻訳者サンドボックスの管理',
	'tsb-filter-pending' => '保留中の申請',
	'tsb-request-count' => '{{PLURAL:$1|$1 件の申請}}',
	'tsb-all-languages-button-label' => 'すべての言語',
	'tsb-search-requests' => '申請の検索',
	'tsb-accept-button-label' => '承認',
	'tsb-reject-button-label' => '却下',
	'translationstash' => 'ようこそ',
	'translate-translationstash-welcome' => '$1 さん、ありがとうございます。あなたは翻訳者になりました',
);

/** Korean (한국어)
 * @author Hym411
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
	'tsb-all-languages-button-label' => '모든 언어',
	'tsb-search-requests' => '검색 요청',
	'tsb-accept-button-label' => '승인',
	'tsb-reject-button-label' => '거부',
	'tsb-reminder-link-text' => '이메일 알림 보내기',
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
	'tsb-all-languages-button-label' => 'All Sproochen',
	'tsb-accept-button-label' => 'Akzeptéieren',
	'tsb-reject-button-label' => 'Refuséieren',
	'translationstash' => 'Wëllkomm',
	'translate-translationstash-welcome' => 'Merci {{GENDER:$1|$1}}, dir sidd en neien Iwwersetzer',
	'translate-translationstash-initialtranslation' => 'Är éischt Iwwersetzung',
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
	'tsb-request-count' => '{{PLURAL:$1|Едно барање|$1 барања}}',
	'tsb-all-languages-button-label' => 'Сите јазици',
	'tsb-search-requests' => 'Пребарајте барања',
	'tsb-accept-button-label' => 'Прифати',
	'tsb-reject-button-label' => 'Одбиј',
	'tsb-reminder-link-text' => 'Испрати потсетник по е-пошта',
	'translationstash' => 'Добре дојдовте',
	'translate-translationstash-welcome' => 'Благодарам {{GENDER:$1|$1}}, вие сте нов преведувач',
	'translate-translationstash-welcome-note' => 'Запознајте се со преводните алатки преведувајќи некои произволно избрани пораки.',
	'translate-translationstash-initialtranslation' => 'Вашиот првичен превод',
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
 * @author Sjoerddebruin
 */
$messages['nl'] = array(
	'translatesandbox' => 'Vertalersszandbak beheren',
	'tsb-filter-pending' => 'Aanvragen in behandeling',
	'tsb-reminder-title-generic' => 'Voltooi uw introductie om vertaler te worden',
	'tsb-reminder-content-generic' => 'Hallo $1,

Bedankt voor het registreren bij {{SITENAME}}. Als u uw testvertalingen afrondt, kunnen de beheerders u snel volledige vertaaltoegang geven.

Kon alstublieft naar $2 en maak nog wat meer vertalingen.',
	'tsb-all-languages-button-label' => 'Alle talen',
	'tsb-accept-button-label' => 'Accepteren',
	'tsb-reject-button-label' => 'Afwijzen',
	'tsb-reminder-link-text' => 'Herinnering per e-mail verzenden',
	'translationstash' => 'Welkom',
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
 * @author Okras
 */
$messages['ru'] = array(
	'translatesandbox' => 'Управление песочницей переводчика',
	'tsb-filter-pending' => 'Запросы, ожидающие обработки',
	'tsb-reminder-title-generic' => 'Завершите свой вводный курс, чтобы стать проверенным переводчиком.',
	'tsb-reminder-content-generic' => 'Привет, $1!

Спасибо за регистрацию на сайте «{{SITENAME}}». Если вы завершили свои пробные переводы, администраторы могут предоставить вам полный доступ к инструменту перевода.

Пожалуйста, перейдите по ссылке $2 и сделайте ещё несколько переводов.',
	'tsb-request-count' => '{{PLURAL:$1|Один запрос|$1 запроса|$1 запросов}}',
	'tsb-all-languages-button-label' => 'Все языки',
	'tsb-search-requests' => 'Искать запросы',
	'tsb-accept-button-label' => 'Принять',
	'tsb-reject-button-label' => 'Отклонить',
	'tsb-reminder-link-text' => 'Отправить напоминание по электронной почте',
	'translationstash' => 'Добро пожаловать',
	'translate-translationstash-welcome' => 'Спасибо, {{GENDER:$1|$1}}, теперь вы новый переводчик',
	'translate-translationstash-welcome-note' => 'Ознакомиться с инструментами перевода путём перевода нескольких случайно выбранных сообщений.',
	'translate-translationstash-initialtranslation' => 'Ваш первоначальный перевод',
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
 * @author Andriykopanytsia
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
	'tsb-request-count' => '{{PLURAL:$1|Один запит|$1 запити|$1 запитів}}',
	'tsb-all-languages-button-label' => 'Усі мови',
	'tsb-search-requests' => 'Пошукові запити',
	'tsb-accept-button-label' => 'Прийняти',
	'tsb-reject-button-label' => 'Відмовитися',
	'tsb-reminder-link-text' => 'Надсилати нагадування по електронній пошті',
	'translationstash' => 'Ласкаво просимо',
	'translate-translationstash-welcome' => 'Дякуємо {{GENDER:$1|$1}}, ви є новий перекладач',
	'translate-translationstash-welcome-note' => 'Ознайомитися з інструментами перекладу шляхом перекладу деяких випадково вибраних повідомлень.',
	'translate-translationstash-initialtranslation' => 'Ваш початковий переклад',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'translatesandbox' => 'Quản lý chỗ thử biên dịch',
	'tsb-filter-pending' => 'Yêu cầu đang chờ',
	'tsb-reminder-title-generic' => 'Hoàn thành lời giới thiệu của bạn để trở thành một biên dịch viên xác minh',
	'tsb-reminder-content-generic' => 'Xin chào $1,

Cảm ơn bạn đã tham gia {{SITENAME}}. Sau khi bạn dịch các bản dịch kiểm tra, các bảo quản viên sẽ sớm cấp quyền biên dịch đầy đủ cho bạn.

Xin vui lòng trở lại $2 để dịch tiếp.',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Hzy980512
 * @author Liuxinyu970226
 */
$messages['zh-hans'] = array(
	'translatesandbox' => '管理译者沙盒',
	'tsb-filter-pending' => '待解决请求',
	'tsb-all-languages-button-label' => '所有语言',
	'translationstash' => '欢迎',
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
