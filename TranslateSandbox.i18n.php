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
	'tsb-accept-all-button-label' => 'Accept all',
	'tsb-reject-all-button-label' => 'Reject all',
	'tsb-reminder-link-text' => 'Send email reminder',
	'tsb-translations-source' => 'Source',
	'tsb-translations-user' => 'User translations',
	'tsb-translations-current' => 'Existing translations',
	'translationstash' => 'Welcome',
	'translate-translationstash-welcome' => 'Welcome {{GENDER:$1|$1}}, you are a new translator',
	'translate-translationstash-welcome-note' => 'Become familiar with the translation tools. Translate some messages and get full-translator rights to participate in your favourite projects.',
	'translate-translationstash-initialtranslation' => 'Your initial translation',
	'translate-translationstash-translations' => '$1 completed {{PLURAL:$1|translation|translations}}',
	'translate-translationstash-skip-button-label' => 'Try another',

	'tsb-limit-reached-title' => 'Thanks for your translations',
	'tsb-limit-reached-body' => 'You reached the translation limit for new translators.
Our team will verify and upgrade your account soon.
Then you will be able to translate without limits.',
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
	'tsb-accept-all-button-label' => 'Button label for accept-all button in [[Special:TranslateSandbox]].
{{Identical|Accept all}}',
	'tsb-reject-all-button-label' => 'Button label for reject-all button in [[Special:TranslateSandbox]]
{{Identical|Reject all}}',
	'tsb-reminder-link-text' => 'Link text for sending reminder emails about translator signup requests.',
	'tsb-translations-source' => 'Table header label for source messages of user translations in [[Special:TranslateSandbox]].
{{Identical|Source}}',
	'tsb-translations-user' => 'Table header label for user translations in [[Special:TranslateSandbox]].',
	'tsb-translations-current' => 'Table header label for existing translations in [[Special:TranslateSandbox]].',
	'translationstash' => 'Page title for [[Special:TranslationStash]].
{{Identical|Welcome}}',
	'translate-translationstash-welcome' => 'Title text shown for the [[Special:TranslationStash]]. Parameters:
* $1 - user name of the new translator',
	'translate-translationstash-welcome-note' => 'Title note for the [[Special:TranslationStash]].',
	'translate-translationstash-initialtranslation' => 'Header for messages showing the progress of translations in [[Special:TranslationStash]].

See also:
* {{msg-mw|Translate-translationstash-translations}}',
	'translate-translationstash-translations' => 'Header for messages showing the progress of translations in [[Special:TranslationStash]]. Params:
	* $1 - the number of translations user has completed in the stash',
	'translate-translationstash-skip-button-label' => 'Label for the skip button in translation editor.
{{Identical|Try another}}',
	'tsb-limit-reached-title' => 'Heading shown below translations when the user has reached the limit for number of translations.',
	'tsb-limit-reached-body' => 'Text shown below translations when the user has reached the limit for number of translations.',
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
	'tsb-request-count' => '{{PLURAL:$1|Una solicitú|$1 solicitúes}}',
	'tsb-all-languages-button-label' => 'Toles llingües',
	'tsb-search-requests' => 'Resultaos de la gueta',
	'tsb-accept-button-label' => 'Aceutar',
	'tsb-reject-button-label' => 'Refugar',
	'tsb-reminder-link-text' => 'Unviar un recordatoriu per corréu electrónicu',
	'tsb-translations-source' => 'Fonte',
	'tsb-translations-user' => 'Traducciones del usuariu',
	'tsb-translations-current' => 'Traducciones esistentes',
	'translationstash' => 'Bienveníos',
	'translate-translationstash-welcome' => '{{GENDER:$1|Bienveníu|Bienvenida}}, $1; yá yes {{GENDER:$1|un nuevu traductor|una nueva traductora}}',
	'translate-translationstash-welcome-note' => 'Avézate a les ferramientes de traducción. Traduz dellos mensaxes y consigui permisu de traducción completu pa participar nos tos proyeutos favoritos.',
	'translate-translationstash-initialtranslation' => 'La so traducción inicial',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|traducción completada|traducciones completaes}}',
	'translate-translationstash-skip-button-label' => 'Probar con otra',
	'tsb-limit-reached-title' => 'Gracies poles sos traducciones',
	'tsb-limit-reached-body' => 'Llegó a la llende de traducciones pa traductores nuevos.
El nuesu equipu pronto comprobará y promocionará la so cuenta.
Darréu podrá traducir ensin llendes.',
);

/** Bulgarian (български)
 * @author DCLXVI
 */
$messages['bg'] = array(
	'tsb-all-languages-button-label' => 'Всички езици',
	'tsb-accept-button-label' => 'Приемане',
	'tsb-translations-source' => 'Източник',
	'tsb-translations-current' => 'Съществуващи преводи',
	'tsb-limit-reached-title' => 'Благодарности за направените преводи',
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
	'tsb-translations-source' => 'Mammenn',
	'tsb-translations-current' => 'Troidigezhioù zo diouto',
	'tsb-limit-reached-title' => 'Trugarez evit ho troidigezhioù',
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
	'tsb-translations-source' => 'Quelle',
	'tsb-translations-user' => 'Benutzerübersetzungen',
	'tsb-translations-current' => 'Vorhandene Übersetzungen',
	'translationstash' => 'Willkommen',
	'translate-translationstash-welcome' => 'Willkommen $1, du bist {{GENDER:$1|ein neuer Übersetzer|eine neue Übersetzerin}}.',
	'translate-translationstash-welcome-note' => 'Werde mit den Übersetzungswerkzeugen vertraut. Übersetze einige Nachrichten und erhalte die vollen Übersetzerrechte zur Teilnahme an deinen Lieblingsprojekten.',
	'translate-translationstash-initialtranslation' => 'Deine erste Übersetzung',
	'translate-translationstash-translations' => '{{PLURAL:$1|Eine vervollständigte Übersetzung|$1 vervollständigte Übersetzungen}}',
	'translate-translationstash-skip-button-label' => 'Eine andere versuchen',
	'tsb-limit-reached-title' => 'Danke für deine Übersetzungen',
	'tsb-limit-reached-body' => 'Du hast das Übersetzungslimit für neue Übersetzer erreicht.
Unser Team wird dein Benutzerkonto bald verifizieren und hochstufen.
Du wirst dann in der Lage sein, ohne Limits zu übersetzen.',
);

/** Spanish (español)
 * @author Fitoschido
 */
$messages['es'] = array(
	'translatesandbox' => 'Gestionar la zona de pruebas del traductor',
	'tsb-filter-pending' => 'Solicitudes pendientes',
	'tsb-reminder-title-generic' => 'Completa tu introducción para volverte un traductor verificado',
);

/** Finnish (suomi)
 * @author Crt
 */
$messages['fi'] = array(
	'translationstash' => 'Tervetuloa',
);

/** French (français)
 * @author Gomoko
 * @author NemesisIII
 * @author Wyz
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
	'tsb-translations-user' => 'Traductions utilisateur',
	'tsb-translations-current' => 'Traductions existantes',
	'translationstash' => 'Bienvenue',
	'translate-translationstash-welcome' => 'Bienvenue {{GENDER:$1|$1}}, vous êtes un nouveau traducteur',
	'translate-translationstash-welcome-note' => 'Familiarisez-vous avec les outils de traduction en traduisant quelques messages sélectionnés aléatoirement.', # Fuzzy
	'translate-translationstash-initialtranslation' => 'Votre traduction initiale',
	'translate-translationstash-translations' => '$1 a achevé {{PLURAL:$1|une traduction|des traductions}}',
	'translate-translationstash-skip-button-label' => 'Essayer une autre',
	'tsb-limit-reached-title' => 'Merci pour vos traductions',
	'tsb-limit-reached-body' => 'Vous atteint le nombre limite de traductions pour les nouveaux traducteurs. !N !Notre équipe va vérifier et mettre à niveau votre compte bientôt. !N !Ensuite, vous serez en mesure de traduire sans limites.',
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
	'tsb-request-count' => '{{PLURAL:$1|Unha solicitude|$1 solicitudes}}',
	'tsb-all-languages-button-label' => 'Todas as linguas',
	'tsb-search-requests' => 'Procurar nas solicitudes',
	'tsb-accept-button-label' => 'Aceptar',
	'tsb-reject-button-label' => 'Rexeitar',
	'tsb-reminder-link-text' => 'Enviar un recordatorio por correo electrónico',
	'translationstash' => 'Benvido',
	'translate-translationstash-welcome' => '{{GENDER:$1|Benvido|Benvida}}, $1; xa es {{GENDER:$1|un novo tradutor|unha nova tradutora}}',
	'translate-translationstash-welcome-note' => 'Familiarícese coas ferramentas de tradución traducindo algunhas mensaxes seleccionadas ao chou.', # Fuzzy
	'translate-translationstash-initialtranslation' => 'A súa tradución inicial',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|tradución completada|traducións completadas}}',
	'translate-translationstash-skip-button-label' => 'Probar outra',
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
	'tsb-translations-source' => 'Sorgente',
	'tsb-translations-user' => 'Traduzione utente',
	'tsb-translations-current' => 'Traduzioni esistenti',
	'translationstash' => 'Benvenuto(a)',
	'translate-translationstash-welcome' => '{{GENDER:$1|Benvenuto|Benvenuta|Benvenuto/a}} $1, ora sei {{GENDER:$1|un nuovo traduttore|una nuova traduttrice}}',
	'translate-translationstash-welcome-note' => 'Acquisisci familiarità con gli strumenti di traduzione. Traduci alcuni messaggi ed ottieni i diritti completi per partecipare ai tuoi progetti preferiti.',
	'translate-translationstash-initialtranslation' => 'La tua traduzione iniziale',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|traduzione completa|traduzioni complete}}',
	'translate-translationstash-skip-button-label' => 'Prova con un altro',
	'tsb-limit-reached-title' => 'Grazie per le tue traduzioni',
	'tsb-limit-reached-body' => 'Hai raggiunto il limite di traduzioni per i nuovi traduttori.
Il nostro team verificherà ed aggiornerà presto la tua utenza.
Successivamente sarai in grado di tradurre senza limiti.',
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
	'tsb-translations-source' => '原文',
	'tsb-translations-user' => '利用者による翻訳',
	'tsb-translations-current' => '既存の翻訳',
	'translationstash' => 'ようこそ',
	'translate-translationstash-welcome' => '$1 さん、ありがとうございます。あなたは翻訳者になりました', # Fuzzy
	'translate-translationstash-initialtranslation' => 'あなたの最初の翻訳',
	'translate-translationstash-translations' => '{{PLURAL:$1|翻訳}}済 $1 件',
	'translate-translationstash-skip-button-label' => 'スキップ',
	'tsb-limit-reached-title' => '翻訳していただいてありがとうございます',
	'tsb-limit-reached-body' => '新規翻訳者の翻訳数の上限に達しました。
私たちのチームがまもなく、アカウントを検証してアップグレードします。
その後、上限なしで翻訳できるようになります。',
);

/** Korean (한국어)
 * @author Daisy2002
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
	'tsb-request-count' => '{{PLURAL:$1|요청 한 개|요청 $1개}}',
	'tsb-all-languages-button-label' => '모든 언어',
	'tsb-search-requests' => '검색 요청',
	'tsb-accept-button-label' => '승인',
	'tsb-reject-button-label' => '거부',
	'tsb-reminder-link-text' => '이메일 알림 보내기',
	'tsb-translations-source' => '출처',
	'tsb-translations-user' => '사용자 번역',
	'tsb-translations-current' => '기존 번역',
	'translationstash' => '환영합니다',
	'translate-translationstash-welcome' => '$1님 환영합니다, 당신은 이제 번역자입니다.',
	'translate-translationstash-welcome-note' => '무작위로 선택된 어떤 메시지를 번역하여 번역 도구에 익숙해지세요.', # Fuzzy
	'translate-translationstash-initialtranslation' => '내 초기 번역',
	'translate-translationstash-skip-button-label' => '다른 문서',
	'tsb-limit-reached-title' => '당신의 번역에 감사드립니다.',
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
	'tsb-request-count' => '{{PLURAL:$1|Eng Ufro|$1 Ufroen}}',
	'tsb-all-languages-button-label' => 'All Sproochen',
	'tsb-accept-button-label' => 'Akzeptéieren',
	'tsb-reject-button-label' => 'Refuséieren',
	'tsb-translations-source' => 'Quell',
	'tsb-translations-user' => 'Benotzer Iwwersetzungen',
	'tsb-translations-current' => 'Iwwersetzungen déi et gëtt',
	'translationstash' => 'Wëllkomm',
	'translate-translationstash-welcome' => 'Wëllkomm {{GENDER:$1|$1}}, Dir sidd en neien Iwwersetzer',
	'translate-translationstash-initialtranslation' => 'Är éischt Iwwersetzung',
	'tsb-limit-reached-title' => 'Merci fir Är Iwwersetzungen',
	'tsb-limit-reached-body' => "Dir hutt d'Iwwersetzungslimit fir nei Iwwersetzer erreecht.
Eis Equipe kuckt Äre Benotzerkont geschwënn no a setzt en erop.
Da kënnt Dir ouni Limitatiounen iwwersetzen.",
);

/** لوری (لوری)
 * @author Mogoeilor
 */
$messages['lrc'] = array(
	'tsb-all-languages-button-label' => 'همه زونيا',
	'tsb-accept-button-label' => 'پذيرشت',
	'tsb-reject-button-label' => 'رد كردن',
	'tsb-translations-source' => 'سرچشمه',
	'translationstash' => 'خوش اومايت',
	'translate-translationstash-skip-button-label' => 'يكی هنی نه امتحان بكيد',
	'tsb-limit-reached-title' => 'سی والرستن تو منمونيم',
);

/** Latvian (latviešu)
 * @author Papuass
 */
$messages['lv'] = array(
	'tsb-all-languages-button-label' => 'Visas valodas',
	'tsb-search-requests' => 'Meklēt pieprasījumus',
	'tsb-accept-button-label' => 'Pieņemt',
	'tsb-reject-button-label' => 'Noraidīt',
	'tsb-reminder-link-text' => 'Sūtīt e-pasta atgādinājumu',
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
	'tsb-translations-source' => 'Извор',
	'tsb-translations-user' => 'Кориснички преводи',
	'tsb-translations-current' => 'Постоечки преводи',
	'translationstash' => 'Добре дојдовте',
	'translate-translationstash-welcome' => 'Добре дојдовте {{GENDER:$1|$1}}, вие сте нов преведувач',
	'translate-translationstash-welcome-note' => 'Запознајте се со преводните алатки. Преведете некои пораки и стекнете полни преведувачки права за да учествувате во вашите омилени проекти.',
	'translate-translationstash-initialtranslation' => 'Вашиот првичен превод',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|завршен превод|завршени преводи}}',
	'translate-translationstash-skip-button-label' => 'Дај друга',
	'tsb-limit-reached-title' => 'Ви благодариме за преводите',
	'tsb-limit-reached-body' => 'Ја достигнавте границата на преводи од нови преведувачи.
Наскоро нашата екипа ќе ви ја провери и надгради сметката.
Потоа ќе можете да преведувате неограничено.',
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
	'tsb-request-count' => '{{PLURAL:$1|Eén verzoek|$1 verzoeken}}',
	'tsb-all-languages-button-label' => 'Alle talen',
	'tsb-search-requests' => 'Verzoeken zoeken',
	'tsb-accept-button-label' => 'Accepteren',
	'tsb-reject-button-label' => 'Afwijzen',
	'tsb-reminder-link-text' => 'Herinnering per e-mail verzenden',
	'translationstash' => 'Welkom',
	'translate-translationstash-welcome' => 'Welkom {{GENDER:$1|$1}}, u bent nu vertaler',
	'translate-translationstash-welcome-note' => 'Raak vertrouwd met de vertaalhulpmiddelen door een aantal willekeurig geselecteerde berichten te vertalen.', # Fuzzy
	'translate-translationstash-initialtranslation' => 'Uw vertaling',
	'translate-translationstash-skip-button-label' => 'Nog één proberen',
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

/** Polish (polski)
 * @author Chrumps
 */
$messages['pl'] = array(
	'tsb-all-languages-button-label' => 'Wszystkie języki',
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
	'tsb-translations-source' => 'Источник',
	'tsb-translations-user' => 'Переводы пользователя',
	'tsb-translations-current' => 'Существующие переводы',
	'translationstash' => 'Добро пожаловать',
	'translate-translationstash-welcome' => 'Добро пожаловать, {{GENDER:$1|$1}}, теперь вы новый переводчик',
	'translate-translationstash-welcome-note' => 'Ознакомьтесь с инструментами перевода. Переведите несколько сообщений и получите полные права переводчика, чтобы принять участие в понравившемся проекте.',
	'translate-translationstash-initialtranslation' => 'Ваш первоначальный перевод',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|завершённый перевод|завершённый перевода|завершённых переводов}}',
	'translate-translationstash-skip-button-label' => 'Попробуйте другой',
	'tsb-limit-reached-title' => 'Спасибо за ваши переводы',
	'tsb-limit-reached-body' => 'Вы достигли предела переводов для новых переводчиков.
Наша команда проверит и обновит вашу учётную запись в ближайшее время.
После этого вы сможете переводить без ограничений.',
);

/** Swedish (svenska)
 * @author Jopparn
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'translatesandbox' => 'Hantera översättarsandlåda',
	'tsb-filter-pending' => 'Väntande förfrågningar',
	'tsb-reminder-title-generic' => 'Slutför din introduktion för att bli en verifierad översättare',
	'tsb-reminder-content-generic' => 'Hej $1,

Tack för din registrering på {{SITENAME}}. Om du slutför dina testöversättningar kan administratörerna snart ge dig full behörighet till att översätta.

Var god kom till $2 och gör några fler översättningar.',
	'tsb-request-count' => '{{PLURAL:$1|En begäran|$1 begäran}}',
	'tsb-all-languages-button-label' => 'Alla språk',
	'tsb-search-requests' => 'Sökbegäran',
	'tsb-accept-button-label' => 'Acceptera',
	'tsb-reject-button-label' => 'Acceptera inte',
	'tsb-reminder-link-text' => 'Skicka e-postpåminnelse',
	'tsb-translations-source' => 'Källa',
	'tsb-translations-user' => 'Användaröversättningar',
	'tsb-translations-current' => 'Befintliga översättningar',
	'translationstash' => 'Välkommen',
	'translate-translationstash-welcome' => 'Välkommen {{GENDER:$1|$1}}, du är en ny översättare',
	'translate-translationstash-welcome-note' => 'Bekanta dig med översättningsverktygen genom att översätta några slumpmässigt utvalda meddelanden.', # Fuzzy
	'translate-translationstash-initialtranslation' => 'Din ursprungliga översättning',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|fullbordad översättning|fullbordade översättningar}}',
	'translate-translationstash-skip-button-label' => 'Prova en annan',
	'tsb-limit-reached-title' => 'Tack för dina översättningar',
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
	'tsb-translations-source' => 'Джерело',
	'tsb-translations-user' => 'Користувацькі переклади',
	'tsb-translations-current' => 'Існуючі переклади',
	'translationstash' => 'Ласкаво просимо',
	'translate-translationstash-welcome' => 'Вітаємо {{GENDER:$1|$1}}, ви - {{GENDER:$1|новий перекладач|нова перекладачка}}',
	'translate-translationstash-welcome-note' => 'Ознайомитися з інструментами перекладу. Перекладіть деякі повідомлення і отримайте повні права перекладача для участі у ваших улюблених проектах.',
	'translate-translationstash-initialtranslation' => 'Ваш початковий переклад',
	'translate-translationstash-translations' => 'Завершено $1 {{PLURAL:$1| переклад|переклади|перекладів|перекладу}}',
	'translate-translationstash-skip-button-label' => 'Спробуйте інший',
	'tsb-limit-reached-title' => 'Спасибі за ваші переклади',
	'tsb-limit-reached-body' => 'Вами досягнута межа перекладу для нових перекладачів.
Наша команда невдовзі перевірить і оновить ваш обліковий запис.
Потім зможете перекладати без обмежень.',
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
	'tsb-request-count' => '{{PLURAL:$1|Một yêu cầu|$1 yêu cầu}}',
	'tsb-all-languages-button-label' => 'Tất cả các ngôn ngữ',
	'tsb-search-requests' => 'Yêu cầu tìm kiếm',
	'tsb-accept-button-label' => 'Chấp nhận',
	'tsb-reject-button-label' => 'Từ chối',
	'tsb-reminder-link-text' => 'Nhắc nhở qua thư điện tử',
	'tsb-translations-source' => 'Nguồn',
	'tsb-translations-user' => 'Bản dịch của người dùng',
	'tsb-translations-current' => 'Bản dịch hiện có',
	'translationstash' => 'Hoan nghênh',
	'translate-translationstash-welcome' => 'Chào mừng {{GENDER:$1|$1}} đã trở thành biên dịch viên mới',
	'translate-translationstash-welcome-note' => 'Hãy quen thuộc với các công cụ biên dịch. Hãy dịch một số thông điệp và giành được quyền biên dịch viên đầy đủ để tham gia các dự án ưa thích của bạn.',
	'translate-translationstash-initialtranslation' => 'Bản dịch đầu tiên của bạn',
	'translate-translationstash-translations' => '$1 bản dịch hoàn thành',
	'translate-translationstash-skip-button-label' => 'Thử cái khác',
	'tsb-limit-reached-title' => 'Cảm ơn bạn đã đóng góp các bản dịch',
	'tsb-limit-reached-body' => 'Bạn đã đạt đến giới hạn bản dịch cho biên dịch viên mới.
Chúng tôi sẽ kiểm tra và nâng cấp tài khoản của bạn không lâu.
Sau đó bạn sẽ có thể biên dịch thoải mái không có giới hạn.',
);

/** Simplified Chinese (中文（简体）‎)
 * @author Hzy980512
 * @author Liuxinyu970226
 */
$messages['zh-hans'] = array(
	'translatesandbox' => '管理译者沙盒',
	'tsb-filter-pending' => '待解决请求',
	'tsb-request-count' => '$1个申请',
	'tsb-all-languages-button-label' => '所有语言',
	'tsb-reject-button-label' => '拒绝',
	'tsb-reminder-link-text' => '发送电子邮件提醒',
	'tsb-translations-source' => '来源',
	'tsb-translations-user' => '用户翻译',
	'translationstash' => '欢迎',
	'translate-translationstash-welcome' => '欢迎您{{GENDER:$1|$1}}，您已成为新的译者',
	'translate-translationstash-translations' => '$1完成了翻译',
	'translate-translationstash-skip-button-label' => '尝试其他',
	'tsb-limit-reached-title' => '感谢您的翻译',
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
