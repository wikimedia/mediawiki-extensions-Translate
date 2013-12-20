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
 * @author Siebrand Mazeland
 * @author Santhosh Thottingal
 * @author Amire80
 */
$messages['en'] = array(
	'managetranslatorsandbox' => 'Manage translator sandbox',
	'tsb-filter-pending' => 'Pending requests',

	// Reminders
	'tsb-reminder-title-generic' => 'Complete your introduction to become a translator',
	'tsb-reminder-content-generic' => 'Hi $1,

Thanks for registering with {{SITENAME}}.

If you complete your test translations, the administrators will grant you full translation access soon afterwards.

Please come over and make some more translations here:
$2

$3,
{{SITENAME}} staff',
	'tsb-reminder-sending' => 'Sending the reminder...',
	'tsb-reminder-sent' => '{{PLURAL:$1|Sent $1 reminder $2|Sent $1 reminders, the last one $2}}',
	'tsb-reminder-sent-new' => 'Sent a reminder',
	'tsb-reminder-failed' => 'Sending the reminder failed',

	'tsb-email-promoted-subject' => 'You are now a translator at {{SITENAME}}',
	'tsb-email-promoted-body' => 'Hi {{GENDER:$1|$1}},

Congratulations! I checked the test translations that you made at {{SITENAME}} and gave you full translator rights.

Come to {{SITENAME}} to continue translating now, and every day:
$2

Welcome, and thank you for you contributions!

{{GENDER:$3|$3}},
{{SITENAME}} staff',
	'tsb-email-rejected-subject' => 'Your application to be a translator at {{SITENAME}} was rejected',
	'tsb-email-rejected-body' => 'Hi {{GENDER:$1|$1}},

Thank you for applying as a translator at {{SITENAME}}. I regret to inform you that I have rejected your application, because the quality of your translations did not meet the requirements.

If you think that your application was rejected by mistake, please try to apply again as a translator at {{SITENAME}}. You can sign up here:
$2

{{GENDER:$3|$3}},
{{SITENAME}} staff',
	'tsb-request-count' => '$1 {{PLURAL:$1|request|requests}}',
	'tsb-all-languages-button-label' => 'All languages',
	'tsb-search-requests' => 'Search requests',
	'tsb-accept-button-label' => 'Accept',
	'tsb-reject-button-label' => 'Reject',
	'tsb-selected-count' => '{{PLURAL:$1|$1 user selected|$1 users selected}}',
	'tsb-older-requests' => '$1 older {{PLURAL:$1|request|requests}}',
	'tsb-accept-all-button-label' => 'Accept all',
	'tsb-reject-all-button-label' => 'Reject all',
	'tsb-user-posted-a-comment' => 'Not a translator',
	'tsb-reminder-link-text' => 'Send email reminder',
	'tsb-didnt-make-any-translations' => 'This user did not make any translations.',
	'tsb-translations-source' => 'Source',
	'tsb-translations-user' => 'User translations',
	'tsb-translations-current' => 'Existing translations',
	'translationstash' => 'Welcome',
	'translate-translationstash-welcome' => 'Welcome {{GENDER:$1|$1}}, you are a new translator',
	'translate-translationstash-welcome-note' => 'Become familiar with the translation tools. Translate some messages and get full-translator rights to participate in your favorite projects.',
	'translate-translationstash-initialtranslation' => 'Your initial translation',
	'translate-translationstash-translations' => '$1 completed {{PLURAL:$1|translation|translations}}',
	'translate-translationstash-skip-button-label' => 'Try another',

	'tsb-limit-reached-title' => 'Thanks for your translations',
	'tsb-limit-reached-body' => 'You reached the translation limit for new translators.
Our team will verify and upgrade your account soon.
Then you will be able to translate without limits.',
	'tsb-no-requests-from-new-users' => 'No requests from new users',
	'tsb-promoted-from-sandbox' => 'User has been promoted to translator',

	'log-name-translatorsandbox' => 'Translation sandbox',
	'log-description-translatorsandbox' => 'A log of actions on translation sandbox users',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|promoted}} $3 to {{GENDER:$4|translator}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|rejected}} the request from "$3" to become a translator',
);

/** Message documentation (Message documentation)
 * @author Amire80
 * @author Metalhead64
 * @author Nike
 * @author Raymond
 * @author Shirayuki
 */
$messages['qqq'] = array(
	'managetranslatorsandbox' => '{{doc-special|TranslateSandbox}}',
	'tsb-filter-pending' => 'A filter option on [[Special:TranslateSandbox]].

Followed by a list of the pending requests.',
	'tsb-reminder-title-generic' => 'Subject of an email',
	'tsb-reminder-content-generic' => 'Body of an email. Parameters:
* $1 - user name of the recipient
* $3 - URL to the website',
	'tsb-reminder-sending' => 'Shown near {{msg-mw|tsb-reminder-link-text}} while the reminder email is being sent.',
	'tsb-reminder-sent' => 'Shown near {{msg-mw|tsb-reminder-link-text}} after the reminder email was successfully sent. Parameters:
* $1 - the number of reminders that were already sent
* $2 - the human timestamp of the last time a reminder was sent. It is either a date or one of the ago formats in https://github.com/wikimedia/mediawiki-extensions-cldr/blob/master/CldrNames/CldrNamesEn.php#L1151',
	'tsb-reminder-sent-new' => 'Shown near {{msg-mw|tsb-reminder-link-text}} after sending a new reminder.',
	'tsb-reminder-failed' => 'Shown near {{msg-mw|tsb-reminder-link-text}} if sending the reminder email failed.',
	'tsb-email-promoted-subject' => 'The subject for an email that announces that a user received full translation rights ("promoted").',
	'tsb-email-promoted-body' => 'The body text for an email that announces that a user received full translation rights ("promoted"). Parameters:
* $1 - the username of the new user who was promoted
* $2 - the URL to Special:Translate at the website
* $3 - the username of the administrator who promoted the user',
	'tsb-email-rejected-subject' => "The subject for an email that announces that a user's request to become a translator was rejected.",
	'tsb-email-rejected-body' => "The body text for an email that announces that a user's request to become a translator was rejected. Parameters:
* $1 - the username of the new user whose request was rejected
* $2 - the URL to Special:MainPage at the website
* $3 - the username of the administrator who promoted the user",
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
	'tsb-selected-count' => 'Shows how many users are selected for accepting or rejecting. Parameters:
* $1 - the number of users',
	'tsb-older-requests' => 'A link shown at the footer of the requests list. Clicking the link selects all the requests that are older than the oldest currently-selected request.

Parameters:
* $1 - the number of older requests. It can be 0.',
	'tsb-accept-all-button-label' => 'Button label for accept-all button in [[Special:TranslateSandbox]].

See also:
* {{msg-mw|Tsb-reject-all-button-label}}',
	'tsb-reject-all-button-label' => 'Button label for reject-all button in [[Special:TranslateSandbox]].

See also:
* {{msg-mw|Tsb-accept-all-button-label}}',
	'tsb-user-posted-a-comment' => 'A label that appears near some text posted by the user.',
	'tsb-reminder-link-text' => 'Link text for sending reminder emails about translator signup requests.

See also:
* {{msg-mw|Tsb-reminder-sent-new}}',
	'tsb-didnt-make-any-translations' => "Displayed instead of the translations if the selected user didn't make any translations.",
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
	'tsb-no-requests-from-new-users' => 'Shown on [[Special:TranslateSandbox]] when there are no requests for approval from new users.',
	'tsb-promoted-from-sandbox' => '{{logentry}}
Additional parameters:
* $4 - (Unused) user ID',
	'log-name-translatorsandbox' => '{{doc-logpage}}',
	'log-description-translatorsandbox' => 'Log page description',
	'logentry-translatorsandbox-promoted' => '{{logentry}}
* $4 - The name of the user that was promoted, can be used for GENDER.',
	'logentry-translatorsandbox-rejected' => '{{logentry}}',
);

/** Afrikaans (Afrikaans)
 * @author Amire80
 * @author Naudefj
 */
$messages['af'] = array(
	'managetranslatorsandbox' => 'Bestuur vertaler-sandput',
	'tsb-filter-pending' => 'Uitstaande versoeke',
	'tsb-reminder-title-generic' => "Voltooi u bekendstelling om 'n geverifieerde vertaler te word", # Fuzzy
	'tsb-reminder-content-generic' => "Hallo $1,

Dankie dat u op {{SITENAME}} geregistreer het. As u u toesvertalings voltooi, sal die administrateurs spoedig volle regte aan u toeken.

Gaan asseblief na $2 om 'n paar vertalings te maak.", # Fuzzy
);

/** Asturian (asturianu)
 * @author Xuacu
 */
$messages['ast'] = array(
	'managetranslatorsandbox' => 'Alministrar la zona de pruebas de los traductores',
	'tsb-filter-pending' => 'Solicitúes pendientes',
	'tsb-reminder-title-generic' => "Complete la so presentación pa convertise'n traductor",
	'tsb-reminder-content-generic' => "Bones, $1:

Gracies por rexistrase'n {{SITENAME}}.

Si completa les traducciones
de prueba, Los alministradores pronto darán-y permisu de traducción
completu.

Por favor, vuelva a $2 y faiga delles traducciones más equí: $2

$3, equipu de {{SITENAME}}",
	'tsb-reminder-sending' => "Unviando'l recordatoriu...",
	'tsb-reminder-sent' => "{{PLURAL:$1|Unviáu $1 recordatoriu $2|Unviaos $1 recordatorios, l'últimu $2}}",
	'tsb-reminder-sent-new' => 'Unviar un recordatoriu',
	'tsb-reminder-failed' => 'Falló unviar un recordatoriu',
	'tsb-email-promoted-subject' => 'Agora yá ye traductor en {{SITENAME}}',
	'tsb-email-promoted-body' => 'Hola {{GENDER:$1|$1}},

¡Norabona! Vengo de revisar les traducciones que ficisti en {{SITENAME}} y dite permisu completu de traductor.

Ven a {{SITENAME}} pa siguir traduciendo, agora y cada día:
$2

¡{{GENDER:$1|Bienveníu|Bienvenida}, y gracies poles tos collaboraciones!

{{GENDER:$3|$3}},
equipu de {{SITENAME}}', # Fuzzy
	'tsb-request-count' => '{{PLURAL:$1|Una solicitú|$1 solicitúes}}',
	'tsb-all-languages-button-label' => 'Toles llingües',
	'tsb-search-requests' => 'Resultaos de la gueta',
	'tsb-accept-button-label' => 'Aceutar',
	'tsb-reject-button-label' => 'Refugar',
	'tsb-selected-count' => '{{PLURAL:$1|$1 usuariu seleicionáu|$1 usuarios seleicionaos}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|solicitú anterior|solicitúes anteriores}}',
	'tsb-accept-all-button-label' => 'Aceutar too',
	'tsb-reject-all-button-label' => 'Refugar too',
	'tsb-reminder-link-text' => 'Unviar un recordatoriu per corréu electrónicu',
	'tsb-didnt-make-any-translations' => 'Esti usuariu nun fizo denguna traducción.',
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
	'tsb-no-requests-from-new-users' => "Nun hai solicitúes d'usuarios nuevos",
	'tsb-promoted-from-sandbox' => 'Esti usuariu promovióse a traductor',
	'log-name-translatorsandbox' => 'Entornu aislláu de traducción',
	'log-description-translatorsandbox' => "Rexistru d'aiciones de los usuarios del entornu aislláu de traducción",
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|promovió}} a $3 a {{GENDER:$4|traductor}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|refugó}} la solicitú de $3 de facese traductor', # Fuzzy
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
	'managetranslatorsandbox' => 'অনুবাদক খেলাঘর পরিচালনা',
	'tsb-filter-pending' => 'অপেক্ষমান অনুরোধ',
	'tsb-reminder-title-generic' => 'একজন যাচাইকৃত অনুবাদক হতে আপনার পরিচিতি সমাপ্ত করুন', # Fuzzy
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
	'managetranslatorsandbox' => 'Übersetzer-Spielwiese konfigurieren',
	'tsb-filter-pending' => 'Ausstehende Anfragen',
	'tsb-reminder-title-generic' => 'Vervollständige deine Einführung, um ein Übersetzer zu werden.',
	'tsb-reminder-content-generic' => 'Hallo $1,

vielen Dank für die Registrierung auf {{SITENAME}}.

Wenn du deine Testübersetzungen vervollständigst, gewähren dir die Administratoren kurz danach vollen Übersetzungszugriff.

Komm vorbei und mache hier einige weitere Übersetzungen:
$2

$3,
die Mitarbeiter von {{SITENAME}}',
	'tsb-reminder-sending' => 'Sende die Erinnerung …',
	'tsb-reminder-sent' => '{{PLURAL:$1|Eine Erinnerung versandt $2|$1 Erinnerungen versandt, die letzte $2}}',
	'tsb-reminder-sent-new' => 'Die Erinnerung wurde versandt',
	'tsb-reminder-failed' => 'Der Versand der Erinnerung ist fehlgeschlagen',
	'tsb-email-promoted-subject' => 'Du bist jetzt ein Übersetzer auf {{SITENAME}}',
	'tsb-email-promoted-body' => 'Hallo {{GENDER:$1|$1}},

herzlichen Glückwunsch! Ich habe deine Testübersetzungen auf {{SITENAME}} überprüft und habe dir die vollen Übersetzerrechte gegeben.

Komme auf {{SITENAME}}, um jetzt mit dem Übersetzen fortzufahren:
$2

Willkommen und vielen Dank für deine Beiträge!

{{GENDER:$3|$3}},
Mitarbeiter von {{SITENAME}}',
	'tsb-email-rejected-subject' => 'Dein Antrag auf Beförderung zum Übersetzer auf {{SITENAME}} wurde abgelehnt',
	'tsb-email-rejected-body' => 'Hallo $1,

vielen Dank für deinen Antrag auf Beförderung {{GENDER:$1|zum Übersetzer|zur Übersetzerin|zum Übersetzer}} auf {{SITENAME}}. Ich bedauere, dich informieren zu müssen, dass ich deinen Antrag abgelehnt habe, da die Qualität deiner Übersetzungen nicht den Anforderungen entspricht.

Falls du denkst, dass dein Antrag durch einen Fehler abgelehnt wurde, versuche, deinen Übersetzerantrag auf {{SITENAME}} erneut einzureichen. Du kannst dich hier registrieren:
$2

$3,
{{GENDER:$3|Mitarbeiter|Mitarbeiterin|Mitarbeiter}} von {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Eine Anfrage|$1 Anfragen}}',
	'tsb-all-languages-button-label' => 'Alle Sprachen',
	'tsb-search-requests' => 'Anfragen durchsuchen',
	'tsb-accept-button-label' => 'Akzeptieren',
	'tsb-reject-button-label' => 'Ablehnen',
	'tsb-selected-count' => '{{PLURAL:$1|Ein|$1}} Benutzer ausgewählt',
	'tsb-older-requests' => '{{PLURAL:$1|Eine ältere Anfrage|$1 ältere Anfragen}}',
	'tsb-accept-all-button-label' => 'Alle akzeptieren',
	'tsb-reject-all-button-label' => 'Alle ablehnen',
	'tsb-user-posted-a-comment' => 'Kein Übersetzer',
	'tsb-reminder-link-text' => 'E-Mail-Erinnerung senden',
	'tsb-didnt-make-any-translations' => 'Dieser Benutzer hat noch keine Übersetzungen durchgeführt.',
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
	'tsb-no-requests-from-new-users' => 'Keine Anträge von neuen Benutzern',
	'tsb-promoted-from-sandbox' => 'Der Benutzer wurde zum Übersetzer befördert',
	'log-name-translatorsandbox' => 'Übersetzungsspielwiesen-Logbuch',
	'log-description-translatorsandbox' => 'Es folgt ein Logbuch von Aktionen auf Übersetzungsspielwiesenbenutzer.',
	'logentry-translatorsandbox-promoted' => '$1 hat $3 {{GENDER:$4|zum Übersetzer|zur Übersetzerin|zum Übersetzer}} {{GENDER:$2|befördert}}',
	'logentry-translatorsandbox-rejected' => '$1 hat die Anfrage von „$3“ zur Beförderung {{GENDER:$2|zum Übersetzer|zur Übersetzerin|zum Übersetzer}} abgelehnt',
);

/** Lower Sorbian (dolnoserbski)
 * @author Michawiki
 */
$messages['dsb'] = array(
	'managetranslatorsandbox' => 'Pśełožowarske grajkanišćo zastojaś',
	'tsb-filter-pending' => 'Njedocynjone napšašowanja',
	'tsb-reminder-title-generic' => 'Wudopołni swójo zapokazanje, aby pśełožowaŕ był',
	'tsb-reminder-content-generic' => 'Witaj $1,

źěkujomy se za registrěrowanje na {{GRAMMAR:lokatiw|{{SITENAME}}}}.

Jolic wudpołnjujoš swóje testowe pśełožki, administratory daju tebi pón połny pśełožowański pśistup.

Pśiź pšosym sem a pśewjeź dalšne pśełožki:
$2

$3,
sobuźěłaśerje {{GRAMMAR:genitiw|{{SITENAME}}}}',
	'tsb-reminder-sending' => 'Dopomnjeśe se sćelo...',
	'tsb-reminder-sent' => '{{PLURAL:$1|$1 dopomnjeśe jo se pósłało $2|$1 dopomnjeśi stej se pósłałej, slědne $2|$1 dopomnjeśa su se pósłali, slědne $2|$1 dopomnjeśow jo se pósłało, slědne $2}}',
	'tsb-reminder-sent-new' => 'Dopomnjeśe jo se pósłało',
	'tsb-reminder-failed' => 'Słanje dopomnjeśa jo se njeraźiło',
	'tsb-email-promoted-subject' => 'Sy něnto pśełožowaŕ na {{GRAMMAR:lokatiw|{{SITENAME}}}}',
	'tsb-email-promoted-body' => 'Witaj {{GENDER:$1|$1}},

wutšobne glukužycenje! Som testowe pśełožki pśeglědał, kótarež sy na {{GRAMMAR:lokatiw|{{SITENAME}}}} pśewjadł a som tebi połne pśełožowarske pšawa dał.

Pśiź pšosym k {{GRAMMAR:datiw|{{SITENAME}}}}, aby něnto a kuždy źeń dalej pśełožował:
$2

Witaj a wjeliki źěk za twóje pśinoski!

{{GENDER:$3|$3}},
sobuźěłaśerje {{GRAMMAR:genitiw|{{SITENAME}}}}',
	'tsb-request-count' => '$1 {{PLURAL:$1|napšašowanje|napšašowani|napšašowanja|napšašowanjow}}',
	'tsb-all-languages-button-label' => 'Wšykne rěcy',
	'tsb-search-requests' => 'Napšašowanja pśepytaś',
	'tsb-accept-button-label' => 'Akceptěrowaś',
	'tsb-reject-button-label' => 'Wótpokazaś',
	'tsb-selected-count' => '{{PLURAL:$1|$1 wužywaŕ jo se wubrał|$1 wužywarja stej se wubrałej|$1 wužywarje su se wubrali|$1 wužywarjow jo se wubrało}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|starše napšašowanje|staršej napšašowani|starše napšašowanja|staršych napšašowanjow}}',
	'tsb-accept-all-button-label' => 'Wšykne akceptěrowaś',
	'tsb-reject-all-button-label' => 'Wšykne wótpokazaś',
	'tsb-reminder-link-text' => 'E-mailowe dopomnjeśe pósłaś',
	'tsb-didnt-make-any-translations' => 'Toś ten wužywaŕ njejo pśełožki pśewjadł.',
	'tsb-translations-source' => 'Žrědło',
	'tsb-translations-user' => 'Wužywarske pśełožki',
	'tsb-translations-current' => 'Eksistěrujuce pśełožki',
	'translationstash' => 'Witaj',
	'translate-translationstash-welcome' => 'Witaj $1, sy {{GENDER:$1|nowy pśełožowaŕ|nowa pśełožowarka}}',
	'translate-translationstash-welcome-note' => 'Wopóznaj se z pśełožowańskimi rědami. Pśełož někotare powěźeńki a dobydni se połne pśełožowarske pšawa, aby se na wašych projektach wobźělił.',
	'translate-translationstash-initialtranslation' => 'Twój prědny pśełožk',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|wudopołnjony pśełožk|wudopołnjonej pśełožka|wudopołnjone pśełožki|wudopołnjonych pśełožkow}}',
	'translate-translationstash-skip-button-label' => 'Drugi wopytaś',
	'tsb-limit-reached-title' => 'Źěkujomy se za twóje pśełožki',
	'tsb-limit-reached-body' => 'Sy pśełožowański limit za nowe pśełožowarje dojśpił. Naš team buźo twójo konto skóro pśeglědowaś a aktualizěrować. Pótom móžoš bźez limitow pśełožowaś.',
	'tsb-no-requests-from-new-users' => 'Žedne napšašowanja wót nowych wužywarjow',
	'tsb-promoted-from-sandbox' => 'Wužywaŕ jo něnto pśełožowaŕ',
	'log-name-translatorsandbox' => 'Pśełožowańske grajkanišćo',
	'log-description-translatorsandbox' => 'Protokol akcijow na wužywarjach pśełožowańskego grajkanišća',
	'logentry-translatorsandbox-promoted' => '$1 jo $3 za {{GENDER:$4|pśełožowarja|pśełožwarku}} {{GENDER:$2|pówušył|pówušyła}}',
	'logentry-translatorsandbox-rejected' => '$1 jo póžedanje wót $3 na pówušenje za {{GENDER:$2|pśełožowarja|pśełožowarku}} {{GENDER:$2|wótpokazał|wótpokazała}}', # Fuzzy
);

/** Spanish (español)
 * @author Fitoschido
 */
$messages['es'] = array(
	'managetranslatorsandbox' => 'Gestionar la zona de pruebas del traductor',
	'tsb-filter-pending' => 'Solicitudes pendientes',
	'tsb-reminder-title-generic' => 'Completa tu introducción para volverte un traductor verificado', # Fuzzy
);

/** Finnish (suomi)
 * @author Crt
 * @author Nike
 */
$messages['fi'] = array(
	'managetranslatorsandbox' => 'Kääntäjähakemusten hallinta',
	'tsb-filter-pending' => 'Avoimet hakemukset',
	'tsb-reminder-title-generic' => 'Suorita harjoitus loppuun, jotta sinut voidaan hyväksyä kääntäjäksi',
	'tsb-reminder-sending' => 'Lähetetään muistutusta...',
	'tsb-reminder-sent-new' => 'Muistutus lähetetty',
	'tsb-reminder-failed' => 'Muistutuksen lähettäminen epäonnistui',
	'tsb-email-promoted-subject' => '{{SITENAME}}: Sinut on hyväksytty kääntäjäksi',
	'tsb-request-count' => '$1 {{PLURAL:$1|hakemus|hakemusta}}',
	'tsb-all-languages-button-label' => 'Kaikki kielet',
	'tsb-search-requests' => 'Hae hakemuksista',
	'tsb-accept-button-label' => 'Hyväksy',
	'tsb-reject-button-label' => 'Hylkää',
	'tsb-selected-count' => '{{PLURAL:$1|$1 käyttäjä|$1 käyttäjää}} valittu',
	'tsb-older-requests' => '$1 {{PLURAL:$1|vanhempi hakemus|vanhempaa hakemusta}}',
	'tsb-accept-all-button-label' => 'Hyväksy kaikki',
	'tsb-reject-all-button-label' => 'Hylkää kaikki',
	'tsb-reminder-link-text' => 'Lähetä muistutus',
	'tsb-didnt-make-any-translations' => 'Käyttäjä ei ole tehnyt käännöksiä.',
	'tsb-translations-source' => 'Lähde',
	'tsb-translations-user' => 'Käyttäjän käännös',
	'tsb-translations-current' => 'Nykyinen käännös',
	'translationstash' => 'Tervetuloa',
	'translate-translationstash-welcome' => 'Tervetuloa {{GENDER:$1|$1}}. Olet uusi kääntäjä.',
	'translate-translationstash-welcome-note' => 'Tutustu käännöstyökaluihin. Käännä muutamia viestejä, niin saat täydet käännösoikeudet lempiprojektiesi kääntämiseen.',
	'translate-translationstash-initialtranslation' => 'Ensimmäinen käännös',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|valmis käännös|valmista käännöstä}}',
	'translate-translationstash-skip-button-label' => 'Ohita',
	'tsb-limit-reached-title' => 'Kiitos käännöksistäsi',
);

/** French (français)
 * @author Crochet.david
 * @author Gomoko
 * @author NemesisIII
 * @author Nobody
 * @author Wyz
 */
$messages['fr'] = array(
	'managetranslatorsandbox' => 'Gérer le bac à sable de tradution',
	'tsb-filter-pending' => 'Requêtes en attente',
	'tsb-reminder-title-generic' => 'Complétez votre présentation pour devenir un traducteur',
	'tsb-reminder-content-generic' => 'Bonjour $1,

Merci de vous être inscrit sur {{SITENAME}}.

Si vous achevez vos traductions de test, les administrateurs vous accorderont peu après un plein accès aux traductions.

Veuillez aller ici et faire quelques traductions supplémentaires :
$2

$3,
L’équipe de {{SITENAME}}',
	'tsb-reminder-sending' => 'Envoi du rappel en cours…',
	'tsb-reminder-sent' => '{{PLURAL:$1|$1 rappel envoyé $2|$1 rappels envoyés, le dernier $2}}',
	'tsb-reminder-sent-new' => 'Rappel envoyé',
	'tsb-reminder-failed' => 'L’envoi du rappel a échoué',
	'tsb-email-promoted-subject' => 'Vous êtes maintenant un traducteur à {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Une demande|$1 demandes}}',
	'tsb-all-languages-button-label' => 'Toutes les langues',
	'tsb-search-requests' => 'Demandes de recherche',
	'tsb-accept-button-label' => 'Accepter',
	'tsb-reject-button-label' => 'Rejeter',
	'tsb-selected-count' => '{{PLURAL:$1|$1 utilisateur sélectionné|$1 utilisateurs sélectionnés}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|requête plus ancienne|requêtes plus anciennes}}',
	'tsb-accept-all-button-label' => 'Tout accepter',
	'tsb-reject-all-button-label' => 'Tout rejeter',
	'tsb-reminder-link-text' => 'Envoyer un courriel de rappel',
	'tsb-didnt-make-any-translations' => 'Cet utilisateur n’a fait aucune traduction.',
	'tsb-translations-source' => 'Source',
	'tsb-translations-user' => 'Traductions utilisateur',
	'tsb-translations-current' => 'Traductions existantes',
	'translationstash' => 'Bienvenue',
	'translate-translationstash-welcome' => 'Bienvenue {{GENDER:$1|$1}}, vous êtes un nouveau traducteur',
	'translate-translationstash-welcome-note' => 'Familiarisez-vous avec les outils de traduction. Traduisez quelques messages et obtenez les droits complets de traducteur pour participer à vos projets favoris.',
	'translate-translationstash-initialtranslation' => 'Votre traduction initiale',
	'translate-translationstash-translations' => '$1 a achevé {{PLURAL:$1|une traduction|des traductions}}',
	'translate-translationstash-skip-button-label' => 'Essayer une autre',
	'tsb-limit-reached-title' => 'Merci pour vos traductions',
	'tsb-limit-reached-body' => 'Vous atteint le nombre limite de traductions pour les nouveaux traducteurs. !N !Notre équipe va vérifier et mettre à niveau votre compte bientôt. !N !Ensuite, vous serez en mesure de traduire sans limites.',
	'tsb-no-requests-from-new-users' => 'Aucune requête de nouveaux utilisateurs',
	'tsb-promoted-from-sandbox' => 'L’utilisateur a été promu traducteur',
	'log-name-translatorsandbox' => 'Bac à sable de traduction',
	'log-description-translatorsandbox' => 'Un journal des actions sur les utilisateurs du bac à sable de traduction',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|a promu}} $3 comme {{{{GENDER:$4|traducteur}}.', # Fuzzy
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|a rejeté}} la demande de $3 de devenir traducteur', # Fuzzy
);

/** Galician (galego)
 * @author Amire80
 * @author Toliño
 */
$messages['gl'] = array(
	'managetranslatorsandbox' => 'Administrar a zona de probas dos tradutores',
	'tsb-filter-pending' => 'Solicitudes pendentes',
	'tsb-reminder-title-generic' => 'Complete a súa introdución para se converter nun tradutor verificado', # Fuzzy
	'tsb-reminder-content-generic' => 'Boas, $1:

Grazas por rexistrarse en {{SITENAME}}. Se completa as traducións
de proba, os adminitradores poderán concederlle axiña acceso completo á
tradución.

Acceda ao sistema en $2 e faga algunhas traducións máis.', # Fuzzy
	'tsb-request-count' => '{{PLURAL:$1|Unha solicitude|$1 solicitudes}}',
	'tsb-all-languages-button-label' => 'Todas as linguas',
	'tsb-search-requests' => 'Procurar nas solicitudes',
	'tsb-accept-button-label' => 'Aceptar',
	'tsb-reject-button-label' => 'Rexeitar',
	'tsb-accept-all-button-label' => 'Aceptar todos',
	'tsb-reject-all-button-label' => 'Rexeitar todos',
	'tsb-reminder-link-text' => 'Enviar un recordatorio por correo electrónico',
	'tsb-translations-source' => 'Fonte',
	'tsb-translations-user' => 'Traducións do usuario',
	'tsb-translations-current' => 'Traducións existentes',
	'translationstash' => 'Benvido',
	'translate-translationstash-welcome' => '{{GENDER:$1|Benvido|Benvida}}, $1; xa es {{GENDER:$1|un novo tradutor|unha nova tradutora}}',
	'translate-translationstash-welcome-note' => 'Familiarícese coas ferramentas de tradución. Traduza algunhas mensaxes e obteña todos os dereitos de tradutor para participar nos seus proxectos favoritos.',
	'translate-translationstash-initialtranslation' => 'A súa tradución inicial',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|tradución completada|traducións completadas}}',
	'translate-translationstash-skip-button-label' => 'Probar outra',
	'tsb-limit-reached-title' => 'Grazas polas súas traducións',
	'tsb-limit-reached-body' => 'Alcanzou o límite de traducións dos tradutores novos.
O noso equipo ha comprobar e actualizar a súa conta axiña.
Logo diso, poderá traducir sen límites.',
);

/** Hebrew (עברית)
 * @author Amire80
 */
$messages['he'] = array(
	'managetranslatorsandbox' => 'ניהול ארגז חול של מתרגמים',
	'tsb-filter-pending' => 'בקשות ממתינות',
	'tsb-reminder-title-generic' => 'נא להשלים את ההיכרות שלך כדי לקבל אישור לתרגם',
	'tsb-reminder-content-generic' => 'שלום $1,

תודה שנרשמת לאתר {{SITENAME}}.

אם {{GENDER:$1|תסיים|תסיימי}} לעשות את תרגומי הבדיקה, המנהלים ייתנו לך גישה מלאה לתרגם קצת אחרי־כן.

נשאר רק לבוא לעשות עוד כמה תרגומים כאן:
$2

$3
צוות {{SITENAME}}',
	'tsb-reminder-sending' => 'שליחת התזכורת...',
	'tsb-reminder-sent' => '{{PLURAL:$1|נשלחה תזכורת אחת|נשלחו $1 תזכורות, האחרונה $2}}',
	'tsb-reminder-sent-new' => 'נשלחה תזכורת',
	'tsb-reminder-failed' => 'שליחת התזכורת נכשלה',
	'tsb-email-promoted-subject' => 'קיבלת הרשאה לתרגם באתר {{SITENAME}}',
	'tsb-email-promoted-body' => 'שלום $1,

ברכות! בדקתי את תרגומי הבדיקה שעשית באתר {{SITENAME}} ונתתי לך הרשאות מלאות לתרגם.

{{GENDER:$1|בוא|בואי}} אל {{SITENAME}} כדי להמשיך לתרגם, עכשיו וכל יום:
$2

{{GENDER:$1|ברוך הבא|ברוכה הבאה}} ותודה על {{GENDER:$1|תרומותיך|תרומותייך}}!

$3,
צוות {{SITENAME}}',
	'tsb-email-rejected-subject' => 'הבקשה שלך להיות מתרגם באתר {{SITENAME}} נדחתה',
	'tsb-email-rejected-body' => 'שלום $1,

תודה על בקשתך להיות {{GENDER:$1|מתרגם|מתרגמת}} באתר {{SITENAME}}. אני {{GENDER:$3|מצטער|מצטערת}} להודיע לך שדחיתי את בקשתך משום שהאיכות של התרגומים שלך לא עמדה בדרישות.

אם נראה לך שהבקשה נדחתה בטעות, {{GENDER:$1|נסה|נסי}} להירשם שוב בתור {{GENDER:$1|מתרגם|מתרגמת}} באתר {{SITENAME}} בכתובת הבאה:
$2

$3
סגל {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|בקשה אחת|$1 בקשות}}',
	'tsb-all-languages-button-label' => 'כל השפות',
	'tsb-search-requests' => 'חיפוש בקשות',
	'tsb-accept-button-label' => 'לקבל',
	'tsb-reject-button-label' => 'לדחות',
	'tsb-selected-count' => '{{PLURAL:$1|נבחר משתמש אחד|נבחרו $1 משתמשים}}',
	'tsb-older-requests' => '{{PLURAL:$1|בקשה אחת ישנה|$1 בקשות ישנות|0=אין בקשות ישנות}} יותר',
	'tsb-accept-all-button-label' => 'לקבל את כולם',
	'tsb-reject-all-button-label' => 'לדחות את כולם',
	'tsb-user-posted-a-comment' => 'לא מתרגם',
	'tsb-reminder-link-text' => 'לשלוח תזכורת בדוא"ל',
	'tsb-didnt-make-any-translations' => 'המשתמש הזה לא עשה שום תרגום.',
	'tsb-translations-source' => 'מחרוזת מקור',
	'tsb-translations-user' => 'תרגומי המשתמש',
	'tsb-translations-current' => 'תרגומים קיימים',
	'translationstash' => 'ברוך בואך',
	'translate-translationstash-welcome' => '{{GENDER:$1|ברוך הבא $1, אתה מתרגם חדש|ברוכה הבאה $1, את מתרגמת חדשה}}',
	'translate-translationstash-welcome-note' => 'עכשיו נכיר לך את כלי התרגום. אנו מבקשים ממך לתרגם מספר מחרוזות כדי לקבל הרשאות תרגום מלאות ולהשתתף במיזמים שמעניינים אותך.',
	'translate-translationstash-initialtranslation' => 'התרגום ההתחלתי שלך',
	'translate-translationstash-translations' => '{{PLURAL:$1|תרגום אחד הושלם|$1 תרגומים הושלמו}}',
	'translate-translationstash-skip-button-label' => 'לנסות משהו אחר',
	'tsb-limit-reached-title' => 'תודה על התרגומים שלך',
	'tsb-limit-reached-body' => 'הגעת למגבלת התרגומים למתרגמים חדשים.
הסגל שלנו יבדוק וישדרג את החשבון שלך בקרוב.
אחרי־כן תהיה לך אפשרות לתרגם בלי הגבלה.',
	'tsb-no-requests-from-new-users' => 'אין בקשות ממשתמשים חדשים',
	'tsb-promoted-from-sandbox' => 'המשתמש קודם לתפקיד מתרגם',
	'log-name-translatorsandbox' => 'ארגז חול של תרגומים',
	'log-description-translatorsandbox' => 'יומן פעולות על משתמשים בארגז חול של תרגומים',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|קידם|קידמה}} את $3 לתפקיד {{GENDER:$4|מתרגם|מתרגמת}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|דחה|דחתה}} את הבקשה של "$3" לקבל הרשאת מתרגם',
);

/** Upper Sorbian (hornjoserbsce)
 * @author Michawiki
 */
$messages['hsb'] = array(
	'managetranslatorsandbox' => 'Přełožowarske hrajkanišćo zrjadować',
	'tsb-filter-pending' => 'Njesčinjene naprašowanja',
	'tsb-reminder-title-generic' => 'Wudospołń swoje zawjedźenje, zo by so z přełožowarjom stał',
	'tsb-reminder-content-generic' => 'Witaj $1,

dźakujemy so za registrowanje na {{GRAMMAR:lokatiw|{{SITENAME}}}}.

Jeli swoje testowe přełožki wudospołnješ, administratorojo dadźa tebi potom połny přełožowanski přistup.

Přińdź prošu sem a přewjedź dalše přełožki:
$2

$3,
sobudźěłaćerjo {{GRAMMAR:genitiw|{{SITENAME}}}}',
	'tsb-reminder-sending' => 'Dopomnjeće so sćele...',
	'tsb-reminder-sent' => '{{PLURAL:$1|$1 dopomnjeće je so pósłało $2|$1 dopomnjeći stej so pósłałoj, poslednje $2|$1 dopomnjeća su so pósłali, poslednje $2|$1 dopomnjećow je so pósłało, poslednje $2}}',
	'tsb-reminder-sent-new' => 'Dopomnjeće je so pósłało',
	'tsb-reminder-failed' => 'Słanje dopomnjeća je so njeporadźiło',
	'tsb-email-promoted-subject' => 'Sy nětko přełožowar na {{GRAMMAR:lokatiw|{{SITENAME}}}}',
	'tsb-email-promoted-body' => 'Witaj {{GENDER:$1|$1}},

wutrobne zbožopřeće! Sym testowe přełožki přepruwował, kotrež sy na {{GRAMMAR:lokatiw|{{SITENAME}}}} přewjedł a sym tebi połne přełožowarske prawa dał.

Přińdź prošu k {{GRAMMAR:datiw|{{SITENAME}}}}, zo by nětko a kóždy dźeń dale přełožował:
$2

Witaj a wulki dźak za twoje přinoški!

{{GENDER:$3|$3}},
sobudźěłaćerjo {{GRAMMAR:genitiw|{{SITENAME}}}}',
	'tsb-request-count' => '$1 {{PLURAL:$1|naprašowanje|naprašowani|naprašowanja|naprašowanjow}}',
	'tsb-all-languages-button-label' => 'Wšě rěče',
	'tsb-search-requests' => 'Naprašowanja přepytać',
	'tsb-accept-button-label' => 'Akceptować',
	'tsb-reject-button-label' => 'Wotpokazać',
	'tsb-selected-count' => '{{PLURAL:$1|$1 wužiwar je so wubrał|$1 wužiwarjej staj so wubrałoj|$1 wužiwarjo su so wubrali|$1 wužiwarjow je so wubrało}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|starše naprašowanje|staršej naprašowani|starše naprašowanja|staršich naprašowanjow}}',
	'tsb-accept-all-button-label' => 'Wšě akceptować',
	'tsb-reject-all-button-label' => 'Wšě wotpokazać',
	'tsb-reminder-link-text' => 'E-mejlowe dopomnjeće pósłać',
	'tsb-didnt-make-any-translations' => 'Tutón wužiwar njeje přełožki přewjedł.',
	'tsb-translations-source' => 'Žórło',
	'tsb-translations-user' => 'Wužiwarske přełožki',
	'tsb-translations-current' => 'Eksistowace přełožki',
	'translationstash' => 'Witaj',
	'translate-translationstash-welcome' => 'Witaj $1, sy {{GENDER:$1|nowy přełožowar|nowa přełožowarka}}',
	'translate-translationstash-welcome-note' => 'Zeznaj so z přełožowanskimi nastrojemi. Přełož někotre zdźělenki a dobudź połne přełožowarske prawa, zo by so na wašich projektach wobdźělił.',
	'translate-translationstash-initialtranslation' => 'Twój prěni přełožk',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|wudospołnjeny přełožk|wudospołnjenej přełožkaj|wudospołnjene přełožki|wudospołnjenych přełožkow}}',
	'translate-translationstash-skip-button-label' => 'Druhi spytać',
	'tsb-limit-reached-title' => 'Dźakujemy so za twoje přełožki',
	'tsb-limit-reached-body' => 'Sy přełožowanski limit za nowych přełožowarjow docpěł. Naš team budźe twoje konto bórze přepruwować a aktualizować. Potom móžeš bjez limitow přełožować.',
	'tsb-no-requests-from-new-users' => 'Žane naprašowanja wot nowych wužiwarjow',
	'tsb-promoted-from-sandbox' => 'Wužiwar je nětko přełožowar',
	'log-name-translatorsandbox' => 'Přełožowanske hrajkanišćo',
	'log-description-translatorsandbox' => 'Protokol akcijow na wužiwarjach přełožowanskeho hrajkanišća',
	'logentry-translatorsandbox-promoted' => '$1 je $3 za {{GENDER:$4|přełožowarja|přełožwarku}} {{GENDER:$2|powyšił|powyšiła}}',
	'logentry-translatorsandbox-rejected' => '$1 je naprašowanje wot $3 na powyšenje za {{GENDER:$2|přełožowarja|přełožowarku}} {{GENDER:$2|wotpokazał|wotpokazała}}', # Fuzzy
);

/** Italian (italiano)
 * @author Beta16
 * @author Nemo bis
 */
$messages['it'] = array(
	'managetranslatorsandbox' => 'Gestire la sandbox di traduzione',
	'tsb-filter-pending' => 'Richieste in sospeso',
	'tsb-reminder-title-generic' => "Completa l'introduzione per diventare un traduttore",
	'tsb-reminder-content-generic' => "Ciao $1,

Grazie per esserti registrato su {{SITENAME}}.

Se completati i test di traduzione, gli amministratori ti concederanno l'accesso completo da traduttore in un breve periodo.

Vieni e fai alcune altre traduzioni su:
$2

$3,
Lo staff di {{SITENAME}}",
	'tsb-reminder-sending' => 'Invio i promemoria...',
	'tsb-reminder-sent' => "{{PLURAL:$1|Inviato $1 promemoria $2|Inviati $1 promemoria, l'ultimo $2}}",
	'tsb-reminder-sent-new' => 'Inviato un promemoria',
	'tsb-reminder-failed' => 'Invio del promemoria non riuscito',
	'tsb-email-promoted-subject' => 'Ora sei un traduttore su {{SITENAME}}',
	'tsb-email-promoted-body' => 'Ciao $1,

Congratulazioni! Ho controllato le traduzioni di prova che hai effettuato su {{SITENAME}} e ti ho concesso i diritti completi di {{GENDER:$1|traduttore|traduttrice|traduttore/trice}}.

Vieni su {{SITENAME}} per continuare a tradurre ora e ogni giorno:
$2

{{GENDER:$1|Benvenuto|Benvenuta|Benvenuto/a}} e grazie per i tuoi contributi!

{{GENDER:$3|$3}},
Lo staff di {{SITENAME}}',
	'tsb-email-rejected-subject' => 'La tua richiesta di essere un traduttore su {{SITENAME}} è stata rifiutata',
	'tsb-request-count' => '{{PLURAL:$1|Una richiesta|$1 richieste}}',
	'tsb-all-languages-button-label' => 'Tutte le lingue',
	'tsb-search-requests' => 'Cerca richiesta',
	'tsb-accept-button-label' => 'Accetta',
	'tsb-reject-button-label' => 'Rifiuta',
	'tsb-selected-count' => '{{PLURAL:$1|$1 utente selezionato|$1 utenti selezionati}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|richiesta precedente|richieste precedenti}}',
	'tsb-accept-all-button-label' => 'Accetta tutto',
	'tsb-reject-all-button-label' => 'Rifiuta tutto',
	'tsb-user-posted-a-comment' => 'Non un traduttore',
	'tsb-reminder-link-text' => 'Invia email di promemoria',
	'tsb-didnt-make-any-translations' => 'Questo utente non ha fatto alcuna traduzione.',
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
	'tsb-no-requests-from-new-users' => 'Nessuna richiesta da nuovi utenti',
	'tsb-promoted-from-sandbox' => "L'utente è stato promosso a traduttore",
	'log-name-translatorsandbox' => 'Sandbox di traduzione',
	'log-description-translatorsandbox' => 'Un registro delle azioni sugli utenti della sandbox di traduzione',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|ha promosso}} $3 a {{GENDER:$4|traduttore|traduttrice|traduttore/trice}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|ha rifiutato}} la richiesta di "$3" di diventare un traduttore',
);

/** Japanese (日本語)
 * @author Shirayuki
 */
$messages['ja'] = array(
	'managetranslatorsandbox' => '翻訳者サンドボックスの管理',
	'tsb-filter-pending' => '保留中の申請',
	'tsb-reminder-title-generic' => '翻訳者になるには、自己紹介を記入してください',
	'tsb-reminder-sending' => '通知を送信しています...',
	'tsb-reminder-sent' => '{{PLURAL:$1|$2に $1 件の通知を送信しました|$1 件の通知を送信しました。最終送信は $2です}}',
	'tsb-reminder-sent-new' => '通知を送信しました',
	'tsb-reminder-failed' => '通知を送信できませんでした',
	'tsb-email-promoted-subject' => 'あなたは{{SITENAME}}の翻訳者になりました',
	'tsb-email-rejected-subject' => 'あなたへの{{SITENAME}}での翻訳者権限の付与申請は却下されました',
	'tsb-request-count' => '$1 {{PLURAL:$1|件の申請}}',
	'tsb-all-languages-button-label' => 'すべての言語',
	'tsb-search-requests' => '申請の検索',
	'tsb-accept-button-label' => '承認',
	'tsb-reject-button-label' => '却下',
	'tsb-selected-count' => '{{PLURAL:$1|$1 人の利用者を選択しています}}',
	'tsb-older-requests' => '以前の $1 {{PLURAL:$1|件の申請}}',
	'tsb-accept-all-button-label' => 'すべて受理',
	'tsb-reject-all-button-label' => 'すべて却下',
	'tsb-user-posted-a-comment' => '非翻訳者',
	'tsb-reminder-link-text' => '通知を送信',
	'tsb-didnt-make-any-translations' => 'この利用者は何も翻訳していません。',
	'tsb-translations-source' => '原文',
	'tsb-translations-user' => '利用者による翻訳',
	'tsb-translations-current' => '既存の翻訳',
	'translationstash' => 'ようこそ',
	'translate-translationstash-welcome' => 'ようこそ、$1 さん。あなたは翻訳者になりました',
	'translate-translationstash-initialtranslation' => 'あなたの最初の翻訳',
	'translate-translationstash-translations' => '{{PLURAL:$1|翻訳}}済 $1 件',
	'translate-translationstash-skip-button-label' => 'スキップ',
	'tsb-limit-reached-title' => '翻訳していただいてありがとうございます',
	'tsb-limit-reached-body' => '新規翻訳者の翻訳数の上限に達しました。
私たちのチームがまもなく、アカウントを検証してアップグレードします。
その後、上限なしで翻訳できるようになります。',
	'tsb-no-requests-from-new-users' => '新規利用者からの申請はありません',
	'tsb-promoted-from-sandbox' => '利用者は翻訳者に昇格しました',
	'log-name-translatorsandbox' => '翻訳サンドボックス',
	'log-description-translatorsandbox' => '翻訳サンドボックス利用者への操作の記録',
	'logentry-translatorsandbox-promoted' => '$1 が $3 を{{GENDER:$4|翻訳者}}に{{GENDER:$2|昇格させました}}',
	'logentry-translatorsandbox-rejected' => '$1 が「$3」の翻訳者権限の付与申請を{{GENDER:$2|却下しました}}',
);

/** Korean (한국어)
 * @author Daisy2002
 * @author Hym411
 * @author 아라
 */
$messages['ko'] = array(
	'managetranslatorsandbox' => '번역자 연습장 관리',
	'tsb-filter-pending' => '보류 중인 요청',
	'tsb-reminder-title-generic' => '검증된 번역자가 되려면 소개를 완료하세요', # Fuzzy
	'tsb-reminder-content-generic' => '$1님 안녕하세요,

{{SITENAME}}에 등록해주셔서 감사합니다. 테스트 번역을
완료하면, 관리자는 곧 전체 번역 접근 권한을 부여할 수
있습니다.

$2에 와서 조금 더 번역을 해주세요.', # Fuzzy
	'tsb-request-count' => '{{PLURAL:$1|요청 한 개|요청 $1개}}',
	'tsb-all-languages-button-label' => '모든 언어',
	'tsb-search-requests' => '검색 요청',
	'tsb-accept-button-label' => '승인',
	'tsb-reject-button-label' => '거부',
	'tsb-accept-all-button-label' => '모두 승인',
	'tsb-reject-all-button-label' => '모두 거절',
	'tsb-reminder-link-text' => '이메일 알림 보내기',
	'tsb-translations-source' => '출처',
	'tsb-translations-user' => '사용자 번역',
	'tsb-translations-current' => '기존 번역',
	'translationstash' => '환영합니다',
	'translate-translationstash-welcome' => '$1님 환영합니다, 당신은 이제 번역자입니다.',
	'translate-translationstash-welcome-note' => '번역 도구에 익숙해지세요. 몇개의 메시지를 번역하고, 당신이 좋아하는 위키에서 번역자 권한을 얻어 위키에 기여하세요.',
	'translate-translationstash-initialtranslation' => '내 초기 번역',
	'translate-translationstash-translations' => '완성한 {{PLURAL:$1|번역}} $1개',
	'translate-translationstash-skip-button-label' => '다른 문서',
	'tsb-limit-reached-title' => '당신의 번역에 감사드립니다.',
	'tsb-limit-reached-body' => '새 번역자를 위한 번역 제한에 도달했습니다. 저희가 당신의 계정을 확인하고 업그레이드한 후에, 제한 없이 번역하실 수 있습니다.',
);

/** Colognian (Ripoarisch)
 * @author Purodha
 */
$messages['ksh'] = array(
	'managetranslatorsandbox' => 'Demm en Schpellwiß för de Övversäzer ennreeschde un verwallde.',
	'tsb-filter-pending' => 'Aanfroore en der Waadeschlang',
	'tsb-reminder-title-generic' => 'Maach Ding Sällefsvörschtällong fäädesch, öm enen beschträäteschten Övversäzzer ze wääde.', # Fuzzy
	'tsb-reminder-content-generic' => 'Daach $1,
mer bedangke ons dat De Desch köözlesch {{ucfirst:{{GRAMMAR:em|{{ucfirst:{{SITENAME}}}}}}}} aanjemälldt häs. Wann Do jraad noch e paa Övversäzonge för et Prööve fäädesch määß, künne de Wikki_Kööbeße desch freischallde för et Övversäzze.

Bes esu jood un donn Desch op {{GRAMMAR:Dativ|$2}} enlogge un maach e paa Övversäzonge.', # Fuzzy
	'tsb-email-promoted-subject' => 'Do bes jäds_ene Övversäzer em {{SITENAME}}',
	'tsb-all-languages-button-label' => 'Alle Schprohche',
	'tsb-accept-button-label' => 'Aanämme',
	'tsb-reject-button-label' => 'Aflehne',
	'tsb-accept-all-button-label' => 'All aanämme',
	'tsb-reject-all-button-label' => 'All aflehne',
	'tsb-user-posted-a-comment' => 'Keine Övversäzer',
	'tsb-translations-source' => 'Quall',
	'translationstash' => 'Wellkumme',
	'translate-translationstash-welcome' => 'Wellkumme $1, Do bess_en{{GENDER:$1|e||e||e}} neuje Övversäzer{{GENDER:$1||ėn||ėn|}}.',
	'translate-translationstash-initialtranslation' => 'Ding eezde Övversäzong',
	'translate-translationstash-translations' => '$1 fäädeje {{PLURAL:$1|Övversäzong|Övversäzonge|Övversäzong}}',
	'tsb-limit-reached-title' => 'Dangke för Ding Övversäzonge',
	'tsb-no-requests-from-new-users' => 'Kein Aanfroore vun neue Metmaacher',
	'tsb-promoted-from-sandbox' => 'Dä Metmaacher es zom Övversäzer opjeschtohv woode.',
	'log-name-translatorsandbox' => 'Sandkaste för et Övversäze',
	'log-description-translatorsandbox' => 'Et Logbooch vun wat de Metmaacher em Sandkaste för et Övversäze jedonn han',
	'logentry-translatorsandbox-promoted' => '{{GENDER:$2|Dä Metmaacher|De Metmaacherėn|Dä Metmaacher|De Metmaacherėn|Dä Metmaacher}} $1 hät {{GENDER:$4|Dä Metmaacher|De Metmaacherėn|Dä Metmaacher|De Metmaacherėn|Dä Metmaacher}} $3 en di Jrop „Övversäzer“ jedonn.',
	'logentry-translatorsandbox-rejected' => 'D{{GENDER:$2|ä Metmaacher|e Metmaacherėn|ä Metmaacher|e Metmaacherėn|ä Metmaacher}} $1 hät afjelehnt, dä Metmaacher $3 zom Övversäzer opzeschohfe.',
);

/** Luxembourgish (Lëtzebuergesch)
 * @author Robby
 */
$messages['lb'] = array(
	'managetranslatorsandbox' => 'Iwwersetzer-Sandkëscht geréieren',
	'tsb-filter-pending' => 'Ufroen am Suspens',
	'tsb-reminder-title-generic' => 'Kompletéiert Är Virstellung fir Iwwersetzer ze ginn',
	'tsb-reminder-sending' => 'Erënnerung gëtt geschéckt...',
	'tsb-reminder-sent-new' => 'Eng Erënnerung schécken',
	'tsb-email-promoted-subject' => 'Dir sidd elo Iwwersetzer op {{SITENAME}}',
	'tsb-email-rejected-subject' => 'Är Demande fir en Iwwersetzer op {{SITENAME}} ze gi gouf refuséiert.',
	'tsb-request-count' => '{{PLURAL:$1|Eng Ufro|$1 Ufroen}}',
	'tsb-all-languages-button-label' => 'All Sproochen',
	'tsb-accept-button-label' => 'Akzeptéieren',
	'tsb-reject-button-label' => 'Refuséieren',
	'tsb-older-requests' => '$1 méi al {{PLURAL:$1|Ufro|Ufroen}}',
	'tsb-accept-all-button-label' => 'All akzeptéieren',
	'tsb-reject-all-button-label' => 'Alles refuséieren',
	'tsb-user-posted-a-comment' => 'Keen Iwwersetzer',
	'tsb-didnt-make-any-translations' => 'Dëse Benotzer huet nach keng Iwwersetzunge gemaach.',
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
	'tsb-no-requests-from-new-users' => 'Keng Ufroe vun neie Benotzer',
	'log-name-translatorsandbox' => 'Iwwersetzungs-Sandkëscht',
);

/** لوری (لوری)
 * @author Mogoeilor
 */
$messages['lrc'] = array(
	'tsb-all-languages-button-label' => 'همه زونيا',
	'tsb-accept-button-label' => 'پذيرشت',
	'tsb-reject-button-label' => 'رد كردن',
	'tsb-accept-all-button-label' => 'همه نه قوول کو',
	'tsb-reject-all-button-label' => 'همه نه رد کو',
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
	'managetranslatorsandbox' => 'Раководење со преведувачки песочник',
	'tsb-filter-pending' => 'Барања во исчекување',
	'tsb-reminder-title-generic' => 'Пополнете го вашето претставување и станете овластен преведувач', # Fuzzy
	'tsb-reminder-content-generic' => 'Здраво $1,

Ви благодариме што се регистриравте на {{SITENAME}}. Пополнете ги пробните преводи, и администраторите набргу ќе ви доделат статус на преведувач.

Појдете на $2 и направете уште некои преводи.', # Fuzzy
	'tsb-request-count' => '{{PLURAL:$1|Едно барање|$1 барања}}',
	'tsb-all-languages-button-label' => 'Сите јазици',
	'tsb-search-requests' => 'Пребарајте барања',
	'tsb-accept-button-label' => 'Прифати',
	'tsb-reject-button-label' => 'Одбиј',
	'tsb-selected-count' => '{{PLURAL:$1|Избран е еден корисник|Избрани се $1 корисници}}',
	'tsb-older-requests' => '{{PLURAL:$1|Едно постаро барање|$1 постари барања}}',
	'tsb-accept-all-button-label' => 'Прифати ги сите',
	'tsb-reject-all-button-label' => 'Одбиј ги сите',
	'tsb-reminder-link-text' => 'Испрати потсетник по е-пошта',
	'tsb-didnt-make-any-translations' => 'Корисников нема направено ниеден превод.',
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
	'tsb-no-requests-from-new-users' => 'Нема барања од нови корисници',
	'tsb-promoted-from-sandbox' => 'Корисникот е унапреден во преведувач',
	'log-name-translatorsandbox' => 'Преводен песочник',
	'log-description-translatorsandbox' => 'Дневник на дејства со корисници на преводниот песочник',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$4|го|ја}} {{GENDER:$2|унапреди}} $3 во {{GENDER:$4|преведувач}}',
	'logentry-translatorsandbox-rejected' => '$1 го {{GENDER:$2|одби}} барањето на $3 за да стане преведувач', # Fuzzy
);

/** Marathi (मराठी)
 * @author V.narsikar
 */
$messages['mr'] = array(
	'tsb-selected-count' => '{{PLURAL:$1|$1 सदस्य निवडला|$1 सदस्य निवडले}}',
	'tsb-no-requests-from-new-users' => 'नविन सदस्यांपासून काहीच विनंत्या नाहीत',
);

/** Malay (Bahasa Melayu)
 * @author Anakmalaysia
 */
$messages['ms'] = array(
	'managetranslatorsandbox' => 'Uruskan kotak pasir penterjemah',
	'tsb-filter-pending' => 'Permohonan yang menunggu',
	'tsb-reminder-title-generic' => 'Lengkapkan pengenalan anda untuk menjadi seorang penterjemah',
	'tsb-reminder-content-generic' => '$1,

Terima kasih kerana mendaftar untuk {{SITENAME}}.

Sekiranya anda melengkapkan ujian penterjemahan ini, anda akan menerima akses penterjemah sepenuhnya dari pihak penyelia.

Sila ke $2 untuk membuat lebih banyak kerja terjemahan.

$3,
Kakitangan {{SITENAME}}',
	'tsb-reminder-sending' => 'Peringatan sedang dihantar...',
	'tsb-reminder-sent' => '{{PLURAL:$1|Telah menghantar $1 peringatan pada $2|Telah menghantar $1 peringatan, yang terbaru pada $2}}',
	'tsb-reminder-sent-new' => 'Peringatan dihantar',
	'tsb-reminder-failed' => 'Peringatan gagal dihantar',
	'tsb-email-promoted-subject' => 'Anda kini seorang penterjemah di {{SITENAME}}',
	'tsb-email-promoted-body' => '{{GENDER:$1|$1}},

Tahniah! Saya telah memeriksa terjemahan ujian yang telah anda lakukan di {{SITENAME}}, dan ole itu memberi anda hak penterjemah sepenuhnya.

Datanglah ke {{SITENAME}} untuk terus menterjemah sekarang dan setiap hari:
$2

Selamat datang dan terima kasih atas sumbangan anda!

{{GENDER:$3|$3}},
Kakitangan {{SITENAME}}',
	'tsb-request-count' => '$1 {{PLURAL:$1|permintaan}}',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|menaikkan pangkat}} $3 kepada {{GENDER:$4|penterjemah}}',
);

/** Nepali (नेपाली)
 * @author सरोज कुमार ढकाल
 */
$messages['ne'] = array(
	'tsb-older-requests' => '$1 पुराना {{PLURAL:$1|अनुरोध|अनुरोधहरू}}',
	'tsb-didnt-make-any-translations' => 'यस प्रयोगकर्ताले कुनै अनुवाद गरेको छैन ।',
	'tsb-promoted-from-sandbox' => 'प्रयोगकर्तालाई अनुवादकको रुपमा बढावा गरिएको छ',
	'log-name-translatorsandbox' => 'अनुवाद प्रयोगस्थल',
);

/** Dutch (Nederlands)
 * @author SPQRobin
 * @author Siebrand
 * @author Sjoerddebruin
 */
$messages['nl'] = array(
	'managetranslatorsandbox' => 'Vertalersszandbak beheren',
	'tsb-filter-pending' => 'Aanvragen in behandeling',
	'tsb-reminder-title-generic' => 'Voltooi uw introductie om vertaler te worden',
	'tsb-reminder-content-generic' => 'Hallo $1,

Bedankt voor het registreren bij {{SITENAME}}.

Als u uw testvertalingen afrondt, kunnen de beheerders u snel volledige vertaaltoegang geven.

Maak alstublieft nog wat meer vertalingen:
$2

$3,
Medewerker van {{SITENAME}}',
	'tsb-reminder-sending' => 'Herinnering verzenden...',
	'tsb-reminder-sent' => '{{PLURAL:$1|Herinnering $2 verzonden|$1 herinneringen verzonden, de laatste $2}}',
	'tsb-reminder-sent-new' => 'Herinnering verzonden',
	'tsb-reminder-failed' => 'Herinnering verzenden mislukt',
	'tsb-email-promoted-subject' => 'U bent nu vertaler bij {{SITENAME}}',
	'tsb-email-promoted-body' => 'Hallo {{GENDER:$1|$1}},

Gefeliciteerd! Ik heb de testvertalingen gecontroleerd die u op {{SITENAME}} hebt gemaakt en heb uw permanente vertaalrechten gegeven.

Kom nu (en bij voorkeur iedere dag) naar {{SITENAME}} om door te gaan met vertalen:
$2

Welkom en dank u wel voor uw bijdragen!

{{GENDER:$3|$3}},
Medewerker van {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Eén verzoek|$1 verzoeken}}',
	'tsb-all-languages-button-label' => 'Alle talen',
	'tsb-search-requests' => 'Verzoeken zoeken',
	'tsb-accept-button-label' => 'Accepteren',
	'tsb-reject-button-label' => 'Afwijzen',
	'tsb-selected-count' => '{{PLURAL:$1|Eén gebruiker|$1 gebruikers}} geselecteerd',
	'tsb-older-requests' => '$1 {{PLURAL:$1|ouder verzoek|oudere verzoeken}}',
	'tsb-accept-all-button-label' => 'Alles accepteren',
	'tsb-reject-all-button-label' => 'Alles afwijzen',
	'tsb-user-posted-a-comment' => 'Geen vertaler',
	'tsb-reminder-link-text' => 'Herinnering per e-mail verzenden',
	'tsb-didnt-make-any-translations' => 'Deze gebruiker heeft nog niets vertaald.',
	'tsb-translations-source' => 'Bron',
	'tsb-translations-user' => 'Gebruikersvertalingen',
	'tsb-translations-current' => 'Bestaande vertalingen',
	'translationstash' => 'Welkom',
	'translate-translationstash-welcome' => 'Welkom {{GENDER:$1|$1}}, u bent nu vertaler',
	'translate-translationstash-welcome-note' => 'Raak vertrouwd met de vertaalhulpmiddelen. Vertaal een aantal willekeurig geselecteerde berichten en krijg volledige vertaalrechten voor uw favoriete projecten.',
	'translate-translationstash-initialtranslation' => 'Uw vertaling',
	'translate-translationstash-translations' => '$1 voltooide {{PLURAL:$1|vertaling|vertalingen}}',
	'translate-translationstash-skip-button-label' => 'Nog één proberen',
	'tsb-limit-reached-title' => 'Bedankt voor uw vertalingen',
	'tsb-limit-reached-body' => 'U hebt de limiet voor het aantal vertalingen voor nieuwe vertalers bereikt.
Ons team gaat ze snel controleren en promoveert uw gebruiker snel, zodat u zonder beperkingen kunt gaan vertalen.',
	'tsb-no-requests-from-new-users' => 'Geen verzoeken van nieuwe gebruikers',
	'tsb-promoted-from-sandbox' => 'Gebruiker is gepromoveerd tot vertaler',
	'log-name-translatorsandbox' => 'Vertalingenzandbak',
	'log-description-translatorsandbox' => 'Een logboek van de handelingen van gebruikers in de vertalingenzandbak.',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|heeft}} $3 gepromoveerd tot {{GENDER:$4|vertaler}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|heeft}} het verzoek van $3 om vertaler te worden geweigerd', # Fuzzy
);

/** Occitan (occitan)
 * @author Cedric31
 */
$messages['oc'] = array(
	'managetranslatorsandbox' => 'Gerir lo nauc de sabla de traduccion',
	'tsb-filter-pending' => 'Requèstas en espèra',
	'tsb-reminder-title-generic' => 'Completatz vòstra presentacion per venir un traductor verificat', # Fuzzy
	'tsb-reminder-content-generic' => 'Bonjorn $1,

Mercé de vos èsser inscrich sus {{SITENAME}}. Se acabatz vòstras traduccions de tèst, los administrators poiràn lèu vos acordar un plen accès a las traduccions.

Venètz sus $2 e fasètz qualques traduccions mai.', # Fuzzy
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
	'managetranslatorsandbox' => 'Administrare cutie cu nisip traducător',
	'tsb-filter-pending' => 'Cereri în așteptare',
	'tsb-reminder-title-generic' => 'Finalizați-vă introducerea pentru a deveni un translator verificat', # Fuzzy
);

/** tarandíne (tarandíne)
 * @author Joetaras
 */
$messages['roa-tara'] = array(
	'managetranslatorsandbox' => "Gestisce 'a sandbox de traduzione",
	'tsb-filter-pending' => 'Richieste appese',
	'tsb-reminder-title-generic' => "Comblete 'a 'ndroduziona toje pe devendà 'nu traduttore verificate", # Fuzzy
	'tsb-reminder-content-generic' => "Cià $1,

Grazie ca tè reggistrate sus a {{SITENAME}}. Ce tu comblete 'u test de traduziune, l'amministrsature ponne darte le privilegge pe l'accesse 'a traduzione comblete.

Pe piacere avìne jndr'à $2 e fà angore quacche otre traduzione.", # Fuzzy
);

/** Russian (русский)
 * @author Kaganer
 * @author Okras
 */
$messages['ru'] = array(
	'managetranslatorsandbox' => 'Управление песочницей переводчика',
	'tsb-filter-pending' => 'Запросы, ожидающие обработки',
	'tsb-reminder-title-generic' => 'Завершите свой вводный курс, чтобы стать переводчиком.',
	'tsb-reminder-content-generic' => 'Привет, $1!

Спасибо за регистрацию на сайте «{{SITENAME}}».

Если вы завершили свои пробные переводы, администраторы могут позднее предоставить вам полный доступ к инструменту перевода.

Пожалуйста, перейдите по ссылке $2 и сделайте ещё несколько переводов.

$3,
Сотрудники сайта «{{SITENAME}}»',
	'tsb-reminder-sending' => 'Отправка напоминания…',
	'tsb-reminder-sent' => '{{PLURAL:$1|Отправлено $1 напоминание $2|Отправлены $1 напоминания, последнее — $2|Отправлены $1 напоминаний, последнее — $2}}',
	'tsb-reminder-sent-new' => 'Напоминание отправлено',
	'tsb-reminder-failed' => 'Отправка напоминания не удалась',
	'tsb-email-promoted-subject' => 'Теперь вы — переводчик сайта «{{SITENAME}}»',
	'tsb-email-promoted-body' => 'Привет, {{GENDER:$1|$1}}.

Поздравляем! Мною были проверены тестовые переводы, которые вы сделали на {{SITENAME}}, и теперь вам предоставлены полные права переводчика.

Прийдите на сайт «{{SITENAME}}», чтоб продолжать переводить его сейчас и каждый день:
$2

Добро пожаловать и спасибо за ваш вклад!

{{GENDER:$3|$3}},
Сотрудник {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Один запрос|$1 запроса|$1 запросов}}',
	'tsb-all-languages-button-label' => 'Все языки',
	'tsb-search-requests' => 'Искать запросы',
	'tsb-accept-button-label' => 'Принять',
	'tsb-reject-button-label' => 'Отклонить',
	'tsb-selected-count' => '{{PLURAL:$1|$1 участник выбран|$1 участника выбрано|$1 участников выбрано}}',
	'tsb-older-requests' => '$1 более {{PLURAL:$1|старый запос|старых запоса|старых запосов}}',
	'tsb-accept-all-button-label' => 'Принять все',
	'tsb-reject-all-button-label' => 'Отклонить все',
	'tsb-reminder-link-text' => 'Отправить напоминание по электронной почте',
	'tsb-didnt-make-any-translations' => 'Этот участник не сделал ни одного перевода.',
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
	'tsb-no-requests-from-new-users' => 'Нет запросов от новых участников',
	'tsb-promoted-from-sandbox' => 'Участник получил статус переводчика',
	'log-name-translatorsandbox' => 'Песочница для переводов',
	'log-description-translatorsandbox' => 'Журнал действий с участниками песочницы переводов',
	'logentry-translatorsandbox-promoted' => '$1 сделал{{GENDER:$2||а}}$3 {{GENDER:$4|переводчиком}}.',
	'logentry-translatorsandbox-rejected' => '$1 отклонил{{GENDER:$2||а}} запрос от $3 статуса переводчика.', # Fuzzy
);

/** Serbian (Cyrillic script) (српски (ћирилица)‎)
 * @author Milicevic01
 */
$messages['sr-ec'] = array(
	'tsb-accept-all-button-label' => 'Прихвати све',
	'tsb-reject-all-button-label' => 'Одбији све',
);

/** Swedish (svenska)
 * @author Jopparn
 * @author WikiPhoenix
 */
$messages['sv'] = array(
	'managetranslatorsandbox' => 'Hantera översättarsandlåda',
	'tsb-filter-pending' => 'Väntande förfrågningar',
	'tsb-reminder-title-generic' => 'Slutför din introduktion för att bli en översättare',
	'tsb-reminder-content-generic' => 'Hej $1,

Tack för din registrering på {{SITENAME}}.

Om du slutför dina testöversättningar kan administratörerna snart ge dig full behörighet till att översätta.

Var god kom och gör några fler översättningar här:
$2

$3
personalen på {{SITENAME}}',
	'tsb-reminder-sent-new' => 'Skicka en påminnelse',
	'tsb-reminder-failed' => 'Det gick inte att skicka påminnelsen',
	'tsb-email-promoted-subject' => 'Du är nu en översättare på {{SITENAME}}',
	'tsb-email-promoted-body' => 'Hej {{GENDER:$1|$1}},

Gratulerar! Jag kollade testöversättningarna du gjorde på {{SITENAME}} och gav dig fullständiga översättningsrättigheter.

Kom till {{SITENAME}} för att fortsätta översätta när som helst:
$2

Välkommen och tack för dina bidrag!

{{GENDER:$3|$3}},
Personal på {{SITENAME}}',
	'tsb-email-rejected-subject' => 'Din ansökan om att bli en översättare på {{SITENAME}} avslogs',
	'tsb-email-rejected-body' => 'Hej {{GENDER:$1|$1}},

Tack för att du ansöker om att bli översättare på {{SITENAME}}. Jag måste tyvärr meddela att jag har avslagit din ansökan, eftersom kvaliteten på dina översättningar inte uppfyllde kraven.

Om du tror att din ansökan avslogs av misstag, var god försök att ansöka igen som en översättare på {{SITENAME}}. Du kan registrera dig här:
$2

{{GENDER:$3|$3}},
Personal på {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|En begäran|$1 begäran}}',
	'tsb-all-languages-button-label' => 'Alla språk',
	'tsb-search-requests' => 'Sökbegäran',
	'tsb-accept-button-label' => 'Acceptera',
	'tsb-reject-button-label' => 'Acceptera inte',
	'tsb-accept-all-button-label' => 'Acceptera alla',
	'tsb-reject-all-button-label' => 'Avvisa alla',
	'tsb-user-posted-a-comment' => 'Inte en översättare',
	'tsb-reminder-link-text' => 'Skicka e-postpåminnelse',
	'tsb-translations-source' => 'Källa',
	'tsb-translations-user' => 'Användaröversättningar',
	'tsb-translations-current' => 'Befintliga översättningar',
	'translationstash' => 'Välkommen',
	'translate-translationstash-welcome' => 'Välkommen {{GENDER:$1|$1}}, du är en ny översättare',
	'translate-translationstash-welcome-note' => 'Bekanta dig med översättningsverktygen. Översätt några meddelanden och få fullständiga översättningsrättigheter för att delta i dina favoritprojekt.',
	'translate-translationstash-initialtranslation' => 'Din ursprungliga översättning',
	'translate-translationstash-translations' => '$1 {{PLURAL:$1|fullbordad översättning|fullbordade översättningar}}',
	'translate-translationstash-skip-button-label' => 'Prova en annan',
	'tsb-limit-reached-title' => 'Tack för dina översättningar',
	'tsb-limit-reached-body' => 'Du har nått översättningsgränsen för nya översättare.
Vårt team kommer snart verifiera och uppgradera ditt konto.
Sedan kommer du kunna översätta utan begränsningar.',
	'log-name-translatorsandbox' => 'Översättningssandlåda',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|befordrades}} $3 till {{GENDER:$4|översättare}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|avvisade}} begäran från "$3" att bli en översättare',
);

/** Telugu (తెలుగు)
 * @author Veeven
 */
$messages['te'] = array(
	'tsb-filter-pending' => 'వేచివున్న అభ్యర్థనలు',
	'tsb-reminder-title-generic' => 'తనిఖీ అయిన అనువాదకుడిగా మారడానికి మీ పరిచయాన్ని పూర్తిచేయండి', # Fuzzy
);

/** Ukrainian (українська)
 * @author Andriykopanytsia
 * @author Base
 * @author Ата
 */
$messages['uk'] = array(
	'managetranslatorsandbox' => 'Керування грамайданчиком перекладачів',
	'tsb-filter-pending' => 'Запити в очікуванні',
	'tsb-reminder-title-generic' => 'Завершіть своє представлення, щоб стати перекладачем',
	'tsb-reminder-content-generic' => 'Привіт, $1!

Дякуємо за реєстрацію у проекті {{SITENAME}}. Якщо Ви завершите свої тестові
переклади, адміністратори зможуть скоро надати Вам повні права на переклад.

Будь ласка, перейдіть і зробіть ще декілька перекладів тут:
$2

$3,
команда {{SITENAME}}',
	'tsb-reminder-sending' => 'Надсилання нагадування…',
	'tsb-reminder-sent' => '{{PLURAL:$1|Надіслано $1 нагадування $2|Надіслано $1 нагадування, останнє - $2|Надіслано $1 нагадувань, останнє - $2}}',
	'tsb-reminder-sent-new' => 'Надіслано нагадування',
	'tsb-reminder-failed' => 'Не вдалося надіслати нагадування',
	'tsb-email-promoted-subject' => 'Тепер ви - перекладач на {{SITENAME}}',
	'tsb-email-promoted-body' => 'Привіт {{GENDER:$1|$1}},

Вітаємо! Я перевірив тестові переклади, виконані вами на {{SITENAME}}, і надав вам повні права перекладача.

Заходьте на {{SITENAME}}, щоб продовжувати переклад нині і щодня:
$2

Вітаємо вас у команді перекладачів і дякуємо вам за ваш внесок!

{{GENDER:$3|$3}},
{{SITENAME}} staff',
	'tsb-email-rejected-subject' => "Вашу заявку на перекладача на {{ім'я сайту}} відхилено",
	'tsb-email-rejected-body' => 'Привіт {{GENDER:$1|$1}},

Дякуємо вам за намагання стати перекладачем на  translator at {{SITENAME}}. На жаль, я з жалем повідомляю вас, що відхиляю вашу заявку, бо якість ваших перекладів не відповідає вимогам.

Якщо ви вважаєте, що ваша заявка відхилена помилково, то можете спробувати знову подати заявку на перекладача на please try to apply again as a translator at {{SITENAME}}. Ви можете зареєструватися тут:
$2

{{GENDER:$3|$3}},
Команда {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Один запит|$1 запити|$1 запитів}}',
	'tsb-all-languages-button-label' => 'Усі мови',
	'tsb-search-requests' => 'Пошукові запити',
	'tsb-accept-button-label' => 'Прийняти',
	'tsb-reject-button-label' => 'Відмовитися',
	'tsb-selected-count' => '{{PLURAL:$1|$1 користувач вибраний|$1 користувачі вибрані|$1 користувачів вибрано}}',
	'tsb-older-requests' => '$1 {{PLURAL:$1|старший запит|старші запити|старших запитів}}',
	'tsb-accept-all-button-label' => 'Прийняти всі',
	'tsb-reject-all-button-label' => 'Відхилити всі',
	'tsb-user-posted-a-comment' => 'Не перекладач',
	'tsb-reminder-link-text' => 'Надсилати нагадування по електронній пошті',
	'tsb-didnt-make-any-translations' => 'Цей користувач не здійснив жодного перекладу.',
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
	'tsb-no-requests-from-new-users' => 'Немає запитів від нових користувачів',
	'tsb-promoted-from-sandbox' => 'Користувач вже підвищений до перекладача',
	'log-name-translatorsandbox' => 'Пісочниця перекладу',
	'log-description-translatorsandbox' => 'Журнал дій користувачів у пісочниці перекладу',
	'logentry-translatorsandbox-promoted' => '$1 {{GENDER:$2|підвищив|підвищила}} $3 до {{GENDER:$4|перекладача|перекладачки}}',
	'logentry-translatorsandbox-rejected' => '$1 {{GENDER:$2|відхилив|відхилила}} запит від  "$3"  стати перекладачем',
);

/** Vietnamese (Tiếng Việt)
 * @author Minh Nguyen
 */
$messages['vi'] = array(
	'managetranslatorsandbox' => 'Quản lý chỗ thử biên dịch',
	'tsb-filter-pending' => 'Yêu cầu đang chờ',
	'tsb-reminder-title-generic' => 'Hoàn thành lời giới thiệu của bạn để trở thành một biên dịch viên',
	'tsb-reminder-content-generic' => 'Xin chào $1,

Cảm ơn bạn đã tham gia {{SITENAME}}.

Sau khi bạn dịch các bản dịch kiểm tra, các bảo quản viên sẽ sớm cấp quyền biên dịch đầy đủ cho bạn.

Xin vui lòng ghé vào biên dịch thêm thông điệp:

$2

$3,
Ban quản lý {{SITENAME}}',
	'tsb-request-count' => '{{PLURAL:$1|Một yêu cầu|$1 yêu cầu}}',
	'tsb-all-languages-button-label' => 'Tất cả các ngôn ngữ',
	'tsb-search-requests' => 'Yêu cầu tìm kiếm',
	'tsb-accept-button-label' => 'Chấp nhận',
	'tsb-reject-button-label' => 'Từ chối',
	'tsb-accept-all-button-label' => 'Chấp nhận tất cả',
	'tsb-reject-all-button-label' => 'Từ chối tất cả',
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
 * @author Qiyue2001
 * @author Shizhao
 * @author Yfdyh000
 */
$messages['zh-hans'] = array(
	'managetranslatorsandbox' => '管理译者沙盒',
	'tsb-filter-pending' => '待解决请求',
	'tsb-reminder-title-generic' => '填写你的自我介绍，成为一名译者',
	'tsb-reminder-content-generic' => '你好 $1，

感谢你注册{{SITENAME}}。

如果您完成你的测试翻译，我们的管理员将尽快授予您完整的翻译访问权。

来这里做些翻译吧：
$2

$3，
{{SITENAME}}员工',
	'tsb-reminder-sending' => '正在发送提醒...',
	'tsb-reminder-sent-new' => '发送提醒',
	'tsb-reminder-failed' => '发送提醒失败',
	'tsb-email-promoted-subject' => '你现在是一名{{SITENAME}}的译者了',
	'tsb-request-count' => '$1个申请',
	'tsb-all-languages-button-label' => '所有语言',
	'tsb-accept-button-label' => '接受',
	'tsb-reject-button-label' => '拒绝',
	'tsb-accept-all-button-label' => '接受所有',
	'tsb-reject-all-button-label' => '拒绝所有',
	'tsb-reminder-link-text' => '发送电子邮件提醒',
	'tsb-translations-source' => '来源',
	'tsb-translations-user' => '用户翻译',
	'tsb-translations-current' => '现有翻译',
	'translationstash' => '欢迎',
	'translate-translationstash-welcome' => '欢迎您{{GENDER:$1|$1}}，您已成为新的译者',
	'translate-translationstash-initialtranslation' => '你的初始翻译',
	'translate-translationstash-translations' => '$1完成了翻译',
	'translate-translationstash-skip-button-label' => '尝试其他',
	'tsb-limit-reached-title' => '感谢您的翻译',
	'tsb-no-requests-from-new-users' => '没有新用户请求',
	'tsb-promoted-from-sandbox' => '用户已晋升为译者',
	'log-name-translatorsandbox' => '翻译沙盒',
);

/** Traditional Chinese (中文（繁體）‎)
 * @author Simon Shek
 */
$messages['zh-hant'] = array(
	'managetranslatorsandbox' => '管理翻譯沙盒',
	'tsb-filter-pending' => '未解決的請求',
	'tsb-reminder-title-generic' => '完成介紹後成為核實的翻譯者', # Fuzzy
	'tsb-reminder-content-generic' => '$1：

感謝您註冊 {{SITENAME}}。完成翻譯測試後，管理員會授予您完整翻譯權限。

請來 $2 做更多的翻譯。', # Fuzzy
);
