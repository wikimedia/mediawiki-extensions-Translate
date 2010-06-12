<?php
/**
 * Translations of Translate extension.
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License 2.0 or later
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
	'translate-fs-pagetitle' => 'Getting started wizard - $1',
	'translate-fs-signup-title' => 'Sign up',
	'translate-fs-settings-title' => 'Configure your preferences',
	'translate-fs-userpage-title' => 'Create your user page',
	'translate-fs-permissions-title' => 'Request translator permissions',
	'translate-fs-target-title' => 'Start translating!',
	'translate-fs-email-title' => 'Confirm your e-mail address',

	'translate-fs-intro' => "Welcome to the {{SITENAME}} first steps wizard.
You will be guided trough the process of becoming a translator step by step.
In the end you will be able to translate ''interface messages'' of all supported projects at {{SITENAME}}.",

	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|frame]]

In the first step you must sign up.

Credits for your translations are attributed to your user name.
The image on the right shows how to fill the fields.

If you have already signed up, $1log in$2 instead.
Once you are signed up, please return to this page.

$3Sign up$4',
	'translate-fs-settings-text' => 'You should now go to your preferences and
at least change your interface language to the language you are going to translate to.

Your interface language is used as the default target language.
It is easy to forget to change the language to the correct one, so setting it now is highly recommended.

While you are there, you can also request the software to display translations in other languages you know.
This setting can be found under tab "{{int:prefs-editing}}".
Feel free to explore other settings, too.

Go to your [[Special:Preferences|preferences page]] now and then return back to this page.',
	'translate-fs-settings-skip' => "I'm done.
Let me proceed.",
	'translate-fs-userpage-text' => 'Now you need to create an user page.

Please write something about yourself; who you are and what you do.
This will help the {{SITENAME}} community to work together.
At {{SITENAME}} there are people from all around the world working on different languages and projects.

In the prefilled box above in the very first line you see <nowiki>{{#babel:en-2}}</nowiki>.
Please complete it with your language knowledge.
The number behind the language code describes how well you know the language.
The alternatives are:
* 1 - a little
* 2 - basic knowledge
* 3 - good knowledge
* 4 - native speaker level
* 5 - you use the language professionally, for example you are a professional translator.

If you are a native speaker of a language, leave the skill level out, and only use the language code.
Example: if you speak Tamil natively, English well, and little Swahili, you would write:
<code><nowiki>{{#babel:ta|en-3|sw-1}}</nowiki></code>

If you do not know the language code of a language, now is good time to look it up.
You can use the list below.',
	'translate-fs-userpage-submit' => 'Create my userpage',
	'translate-fs-userpage-done' => 'Well done! You now have an user page.',
	'translate-fs-permissions-text' => 'Now you need to place a request to be added to the translator group.

Until we fix the code, please go to [[Project:Translator]] and follow the instructions.
Then come back to this page.

After you have submitted your request, one of the volunteer staff members will check your request and approve it as soon as possible.
Please be patient.

<del>Check that the following request is correctly filled and then press the request button.</del>',

	'translate-fs-target-text' => 'Congratulations!
You can now start translating.

Do not be afraid if still feels new and confusing to you.
At [[Project list]] there is an overview of projects you can contribute translations to.
Most of the projects have a short description page with a "\'\'Translate this project\'\'" link, that will take you to a page which lists all untranslated messages.
A list of all message groups with the [[[Special:LanguageStats|current translation status for a language]] is also available.

If you feel that you need to understand more before you start translating, you can read the [[FAQ|Frequently asked questions]].
Unfortanely documentation can be out of date sometimes.
If there is something that you think you should be able to do, but cannot find out how, do not hesitate to ask it at the [[Support|support page]].

You can also contact fellow translators of the same language at [[Portal:$1|your language portal]].
The portal links to your current [[Special:Preferences|language preference]].
Please change it if needed.',

	'translate-fs-email-text' => 'Please provide your e-mail address in [[Special:Preferences|your preferences]] and confirm it from the e-mail that is sent to you.

This allows other users to contact you by e-mail.
You will also receive newsletters at most once a month.
If you do not want receive newsletters, you can opt-out in the tab "{{int:prefs-misc}}" of your [[Special:Preferences|preferences]].',
);

/** Message documentation (Message documentation)
 * @author Lloffiwr
 */
$messages['qqq'] = array(
	'translate-fs-permissions-text' => 'Synonym for "filed" is "submitted".',
);

/** Breton (Brezhoneg)
 * @author Y-M D
 */
$messages['br'] = array(
	'firststeps' => 'Pazenn gentañ',
	'translate-fs-pagetitle-done' => '↓  - graet !',
	'translate-fs-signup-title' => 'En em enskrivañ',
);

/** Spanish (Español)
 * @author Diego Grez
 */
$messages['es'] = array(
	'firststeps' => 'Primeros pasos',
);

/** French (Français)
 * @author Peter17
 */
$messages['fr'] = array(
	'firststeps' => 'Premiers pas',
	'firststeps-desc' => '[[Special:FirstSteps|Page spéciale]] pour guider les utilisateurs sur un wiki utilisant l’extension Translate',
	'translate-fs-pagetitle-done' => ' - fait !',
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
	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|cadre]]

La première étape consiste à s’inscrire.

Les traductions que vous effectuerez seront créditées à votre nom d’utilisateur.
L’image sur la droite montre comment remplir les champs.

Si vous vous êtes déjà inscrit, veuillez $1vous identifier$2.
Une fois inscrit, veuillez revenir vers cette page.

$3Inscrivez-vous$4',
	'translate-fs-settings-text' => 'Vous devez à présent vous rendre dans vos préférences et au moins choisir comme langue d’interface celle dans laquelle vous voulez traduire.

La langue choisie pour l’interface est utilisée comme langue par défaut pour les traductions.
Il est facile d’oublier de changer cette préférence et donc hautement recommandé de le faire maintenant.

Tant que vous y êtes, vous pouvez aussi demander au logiciel d’afficher les traductions dans les autres langues que vous connaissez.
Cette préférence se trouve sous l’onglet « {{int:prefs-editing}} ».
N’hésitez pas à parcourir également les autres préférences.

Allez maintenant à votre [[Special:Preferences|page de préférences]] puis revenez à cette page.',
	'translate-fs-settings-skip' => 'J’ai fini. Laissez-moi continuer.',
	'translate-fs-userpage-text' => 'Vous devez maintenant créer une page utilisateur.

Veuillez écrire quelque chose à propos de vous : qui vous êtes et ce que vous faites.
Cela aidera la communauté de {{SITENAME}} à travailler ensemble.
Sur {{SITENAME}}, il y a des gens de tous les coins du monde qui travaillent sur différentes langues et projets.

Dans la boîte pré-remplie ci-dessus, dans la toute première ligne, vous voyez <nowiki>{{#babel:en-2}}</nowiki>.
Veuillez la compléter avec votre connaissance des langues.
Le nombre qui suit le code de la langue décrit comment vous maîtrisez cette langue.
Les valeurs possibles sont :
* 1 - un peu
* 2 - connaissances de base
* 3 - bonnes connaissances
* 4 - niveau bilingue
* 5 - vous utilisez cette langue de manière professionnelle, par exemple en tant que traducteur professionnel.

Pour votre langue maternelle, ignorez le niveau et n’utilisez que le code de la langue.
Exemple : si votre langue maternelle est le tamoul et que vous parlez bien l’anglais et un peu le swahili, écrivez :
<tt><nowiki>{{#babel:ta|en-3|sw-1}}</nowiki></tt>

Si vous ne connaissez pas le code d’une langue donnée, vous pouvez le chercher maintenant dans la liste ci-dessous.',
	'translate-fs-userpage-submit' => 'Créer ma page utilisateur',
	'translate-fs-userpage-done' => 'Bien joué ! Vous avez à présent une page utilisateur.',
	'translate-fs-permissions-text' => 'Vous devez faire une demande pour être ajouté au groupe des traducteurs.

Jusqu’à ce que nous ayons réparé le code, merci d’aller sur [[Project:Translator]] et de suivre les instructions.
Revenez ensuite à cette page.

Quand vous aurez rempli votre demande, un des membre de l’équipe de volontaires la vérifiera et l’approuvera dès que possible.
Merci d’être patient.

<del>Veuillez vérifier que la demande suivante est correctement remplie puis cliquez sur le bouton de demande.</del>',
	'translate-fs-target-text' => "Félicitations !
Vous pouvez maintenant commencer à traduire.

Ne vous inquiétez pas si cela vous paraît un peu nouveau et étrange.
Sur la [[Project list|liste des projets]] se trouve une vue d’ensemble des projets que vous pouvez contribuer à traduire.
Ces projets possèdent, pour la plupart, une page contenant une courte description et un lien « ''Traduire ce projet'' » qui vous mènera vers une page listant tous les messages non traduits.
Une liste de tous les groupes de messages avec l’[[Special:LanguageStats|état actuel de la traduction pour une langue donnée]] est aussi disponible.

Si vous sentez que vous avez besoin de plus d’informations avant de commencer à traduire, vous pouvez lire la [[FAQ|foire aux questions]].
La documentation peut malheureusement être périmée de temps à autres.
Si vous pensez que vous devriez pouvoir faire quelque chose, sans parvenir à trouver comment, n’hésitez pas à poser la question sur la [[Support|page support]].

Vous pouvez aussi contacter les autres traducteurs de la même langue sur [[Portal:$1|le portail de votre langue]].
Le portail lié est celui qui correspond à votre [[Special:Preferences|préférence de langue]] actuelle.
Veuillez la changer si nécessaire.",
	'translate-fs-email-text' => 'Merci de bien vouloir saisir votre adresse électronique dans [[Special:Preferences|vos préférences]] et la confirmer grâce au message qui vous sera envoyé.

Cela permettra aux autres utilisateurs de vous contacter par courrier électronique.
Vous recevrez aussi un courrier d’informations au plus une fois par mois.
Si vous ne souhaitez pas recevoir ce courrier d’informations, vous pouvez le désactiver dans l’onglet « {{int:prefs-misc}} » de vos [[Special:Preferences|préférences]].',
);

/** Interlingua (Interlingua)
 * @author McDutchie
 */
$messages['ia'] = array(
	'firststeps' => 'Prime passos',
	'firststeps-desc' => '[[Special:FirstSteps|Pagina special]] pro familiarisar le usatores de un wiki con le extension Translate',
	'translate-fs-pagetitle-done' => ' - finite!',
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
	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|frame]]

In le prime passo tu debe crear un conto.

Le traductiones que tu facera essera attribuite a tu nomine de usator.
Le imagine al dextra demonstra como completar le formulario.

Si tu possede jam un conto in le sito, $1aperi un session$2.
Quando tu ha create un conto, per favor retorna a iste pagina.

$3Crear un conto$4',
	'translate-fs-settings-text' => 'Tu deberea ora visitar tu preferentias e,
al minus, cambiar le lingua de interfacie al lingua in le qual tu vole traducer.

Tu lingua de interfacie es usate automaticamente como lingua in le qual traducer.
Il es facile oblidar de cambiar al lingua correcte, dunque il es altemente recommendate de facer lo ora.

Intertanto, tu pote etiam demandar que le software presenta traductiones existente in altere linguas que tu cognosce.
Iste preferentia se trova sub le scheda "{{int:prefs-editing}}".
Sia libere de explorar etiam le altere preferentias.

Visita ora tu [[Special:Preferences|pagina de preferentias]] e postea retorna a iste pagina.',
	'translate-fs-settings-skip' => 'Io ha finite. Lassa me continuar.',
	'translate-fs-userpage-text' => 'Ora, tu debe crear un pagina de usator.

Per favor scribe alique super te; qui tu es e lo que tu face.
Isto adjutara le communitate de {{SITENAME}} a collaborar.
In {{SITENAME}} il ha personas de tote le mundo laborante a diverse linguas e projectos.

In le quadro precompletate hic supra, in le primissime linea, tu vide <nowiki>{{#babel:en-2}}</nowiki>.
Per favor completa isto con tu cognoscentia linguistic.
Le numero post le codice de lingua describe tu nivello de maestria del lingua.
Le optiones es:
* 1 - un poco
* 2 - cognoscentia de base
* 3 - bon cognoscentia
* 4 - nivello de parlante native
* 5 - tu usa le lingua professionalmente, per exemplo tu es traductor professional.

Si tu es un parlante native de un lingua, omitte le nivello de cognoscentia, usante solmente le codice de lingua.
Per exemplo: si tu parla tamil nativemente, anglese ben, e un poco de swahili, tu scriberea:
<tt><nowiki>{{#babel:ta|en-3|sw-1}}</nowiki></tt>

Si tu non cognosce le codice de un lingua, ora es un bon tempore pro cercar lo. Tu pote usar le lista hic infra.',
	'translate-fs-userpage-submit' => 'Crear mi pagina de usator',
	'translate-fs-userpage-done' => 'Ben facite! Tu ha ora un pagina de usator.',
	'translate-fs-permissions-text' => 'Ora, tu debe facer un requesta pro esser addite al gruppo de traductores.

Nos non ha ancora automatisate isto; pro le momento, per favor visita [[Project:Translator]] e seque le instructiones.
Postea, retorna a iste pagina.

Post que tu ha submittite tu requesta, un del membros del personal voluntari verificara tu requesta e lo approbara si tosto como possibile.
Per favor sia patiente.

<del>Verifica que le sequente requesta es correcte e complete, postea clicca super le button de requesta.</del>',
	'translate-fs-target-text' => "Felicitationes!
Tu pote ora comenciar a traducer.

Non te inquieta si isto te pare ancora nove e confundente.
In le pagina [[Project list]] il ha un summario del projectos al quales tu pote contribuer traductiones.
Le major parte del projectos ha un curte pagina de description con un ligamine \"''Traducer iste projecto''\", le qual te portara a un pagina que lista tote le messages non traducite.
Un lista de tote le gruppos de messages con le [[Special:LanguageStats|stato de traduction actual pro un lingua]] es etiam disponibile.

Si tu senti que tu ha besonio de comprender plus ante de traducer, tu pote leger le [[FAQ|folio a questiones]].
Infelicemente le documentation pote a vices esser obsolete.
Si il ah un cosa que tu pensa que tu deberea poter facer, ma non pote trovar como facer lo, non hesita a poner le question in le [[Support|pagina de supporto]].

Tu pote etiam contactar altere traductores del mesme lingua in [[Portal:\$1|le portal de tu lingua]].
Le portal liga a tu [[Special:Preferences|preferentia de lingua]] actual.
Per favor cambia lo si necessari.",
	'translate-fs-email-text' => 'Per favor entra tu adresse de e-mail in [[Special:Preferences|tu preferentias]] e confirma lo per medio del e-mail que te essera inviate.

Isto permitte que altere usatores te contacta via e-mail.
Tu recipera anque bulletines de novas al plus un vice per mense.
Si tu non vole reciper bulletines de novas, tu pote disactivar los in le scheda "{{int:prefs-misc}}" de tu [[Special:Preferences|preferentias]].',
);

/** Macedonian (Македонски)
 * @author Bjankuloski06
 */
$messages['mk'] = array(
	'firststeps' => 'Први чекори',
	'firststeps-desc' => '[[Special:FirstSteps|Специјална страница]] за помош со првите чекори на вики што го користи додатокот Преведување (Translate)',
	'translate-fs-pagetitle-done' => '- завршено!',
	'translate-fs-pagetitle' => 'Помошник „Како да започнете“ - $1',
	'translate-fs-signup-title' => 'Регистрација',
	'translate-fs-settings-title' => 'Поставете ги вашите нагодувања',
	'translate-fs-userpage-title' => 'Создајте своја корисничка страница',
	'translate-fs-permissions-title' => 'Барање на дозвола за преведување',
	'translate-fs-target-title' => 'Почнете со преведување!',
	'translate-fs-email-title' => 'Потврдете ја вашата е-пошта',
	'translate-fs-intro' => "Добредојдовте на помошникот за први чекори на {{SITENAME}}.
Овој помошник постепено ќе води низ постапката за станување преведувач.
Потоа ќе можете да преведувате ''посреднички (интерфејс) пораки'' за сите поддржани проекти на {{SITENAME}}.",
	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|frame]]
Најпрвин мора да се регистрирате.

Заслугите за преводите ќе се припишуваат на вашето корисничко име.
Сликата десно покажува како треба да се пополнат полињата.

Ако сте веќе регистрирани, сега $1најавете се$2.
Откога ќе се регистрирате, вратете се на оваа страница.

$3Регистрација$4',
	'translate-fs-settings-text' => 'Сега одете во вашите нагодувања и
барем сменете го јазикот на посредникот (интерфејсот) во јазикот на којшто ќе преведувате.

Јазикот на посредникот ќе се смета за ваш матичен целен јазик.
Може лесно да заборавите да го смените јазикот на исправниот, па затоа поставете го сега.

Додека сте тука, можете да побарате програмот да ги прикажува напревените преводи на други јазици.
Оваа функција ќе ја најдете во јазичето „{{int:prefs-editing}}“.
Најслободно истражувајте ги и другите поставки и можности.

Сега одете на [[Special:Preferences|вашите нагодувања]], па вратете се пак на оваа страница.',
	'translate-fs-settings-skip' => 'Завршив. Одиме понатаму.',
	'translate-fs-userpage-text' => 'Сега ќе треба да направите корисничка страница.

Напишете нешто за вас; кој сте и со што се занимавате.
Така заедницата на {{SITENAME}} ќе може да работи подобро.
На {{SITENAME}} има луѓе од целиот свет кои работат на различни јазици и проекти.

Во подготвената кутија горе, на најпрвиот ред ќе видите <nowiki>{{#babel:en-2}}</nowiki>.
Пополнете ја со јазикот или јазиците од кои имате познавања.
Бројката до јазичната кратенка го означува нивото на кое го владеете јазикот.
Еве ги можностите:
* 1 - малку
* 2 - основни познавања
* 3 - солидни познавања
* 4 - на ниво на мајчин
* 5 - го користите јазикот професионално, на пр. сте професионален преведувач.

Ако јазикот е ваш мајчин јазик, тогаш изоставете го нивото, и ставете го само јазичниот код (кратенка).
Пример: ако зборувате македонски од раѓање, англиски добро, и малку шпански, ќе внесете:
<tt><nowiki>{{#babel:mk|en-3|es-1}}</nowiki></tt>

Ако не го знаете јазичниот код на некој јазик, сега имате добра можност да го дознаете. Погледајте на списокот подолу.',
	'translate-fs-userpage-submit' => 'Создај корисничка страница',
	'translate-fs-userpage-done' => 'Одлично! Сега имате корисничка страница.',
	'translate-fs-permissions-text' => 'Сега ќе треба да поднесете барање за да ве стават во групата на преведувачи.

Додека не го поправиме овој код, одете на [[Project:Translator]] и проследете ги напатствијата.
Потоа вратете се на страницава.

Откако ќе го пополните барањето, доброволец од персоналот ќе го провери и одобри во најкраток можен рок.
Бидете трпеливи.

<del>Проверете дали следново барање е правилно пополнето, а потоа притиснете го копчето за поднесување на барањето.</del>',
	'translate-fs-target-text' => "Честитаме!
Сега можете да почнете со преведување.

Не плашете се ако сето ова сè уште ви изгледа ново и збунително.
Списокот [[Project list]] дава преглед на проектите каде можете да придонесувате со ваши преводи.
Највеќето проекти имаат страница со краток опис и врска „''Преведи го проектов''“, која ќе ве одвете до страница со сите непреведени пораки за тој проект.
Има и список на сите групи на пораки со [[Special:LanguageStats|тековниот статус на преведеност за даден јазик]].

Ако мислите дека треба да осознаете повеќе пред да почнете со преведување, тогаш прочитајте ги [[FAQ|често поставуваните прашања]].
Нажалост документацијата напати знае да биде застарена.
Ако има нешто што мислите дека би требало да можете да го правите, но не можете да дознаете како, најслободно поставете го прашањето на [[Support|страницата за поддршка]].

Можете и да се обратите кај вашите колеги што преведуваат на истиот јазик на [[Portal:$1|вашиот јазичен портал]].
На порталот се наведени тековните [[Special:Preferences|јазични нагодувања]].
Сменете ги ако се јави потреба.",
	'translate-fs-email-text' => 'Наведете ја вашата е-пошта во [[Special:Preferences|нагодувањата]] и потврдете ја преку пораката испратена на неа.

Ова им овозможува на корисниците да ве контактираат преку е-пошта.
На таа адреса ќе добивате и билтени со новости, највеќе еднаш месечно.
Ако не сакате да добиват билтени, можете да се отпишете преку јазичето „{{int:prefs-misc}}“ во вашите [[Special:Preferences|нагодувања]].',
);

/** Dutch (Nederlands)
 * @author Siebrand
 */
$messages['nl'] = array(
	'firststeps' => 'Eerste stappen',
	'firststeps-desc' => '[[Special:FirstSteps|Speciale pagina]] voor het op gang helpen van gebruikers op een wiki met de uitbreiding Translate',
	'translate-fs-pagetitle-done' => ' - afgerond!',
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
	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|frame]]

In de eerste stap moet u registreren.

Uw gebruikersnaam wordt gebruikt als naamsvermelding voor uw vertalingen.
De afbeelding rechts geeft aan hoe u de velden moet invullen.

Als u al bent geregistreerd, dan kunt u zich $1aanmelden$2.
Kom terug naar deze pagina als u bent aangemeld.

$3Registreren$4',
	'translate-fs-settings-text' => 'Ga nu naar uw voorkeuren en wijzig tenminste de interfacetaal naar de taal waarin u gaat vertalen.

Uw interfacetaal wordt gebruikt als de standaardtaal waarin u gaat vertalen.
Het is makkelijk te vergeten de taal te wijzigen, dus maak die instelling vooral nu.

Als u toch uw instellingen aan het wijzigen bent, kunt u ook een instelling maken om vertalingen in andere talen als hulpje weer te geven.
Deze instellingen is te vinden in het tabblad "{{int:prefs-editing}}".
Voel u vrij om ook andere instellingen aan te passen.

Ga nu naar uw [[Special:Preferences|voorkeuren]] en kom na het wijzigen terug naar deze pagina.',
	'translate-fs-settings-skip' => 'Ik ben klaar en wil doorgaan.',
	'translate-fs-userpage-submit' => 'Mijn gebruikerspagina aanmaken',
	'translate-fs-userpage-done' => 'Goed gedaan!
U hebt nu een gebruikerspagina.',
	'translate-fs-permissions-text' => 'Nu moet u een verzoek doen om vertaalrechten te krijgen.

Totdat we de code wijzigen, moet u naar [[Project:Translator]] en daar de instructies volgen.
Kom daarna terug naar deze pagina.

Nadat u uw aanvraag hebt ingediend, controleert een medewerker zo snel mogelijk uw aanvraag.
Heb even geduld, alstublieft.

<del>Controleer of de onderstaande aanvraag correct is ingevuld en klik vervolgens op de knop.</del>',
	'translate-fs-target-text' => "Gefeliciteerd! 
U kunt nu beginnen met vertalen. 

Wees niet bang als het nog wat verwarrend aanvoelt.
In de [[Project list|Projectenlijst]] vindt u een overzicht van projecten waar u vertalingen aan kunt bijdragen.
Het merendeel van de projecten heeft een korte beschrijvingspagina met een verwijzing \"''Dit project vertalen''\", die u naar een pagina leidt waarop alle onvertaalde berichten worden weergegeven.
Er is ook een lijst met alle berichtengroepen beschikbaar met de [[Special:LanguageStats|huidige status van de vertalingen voor een taal]].

Als u denkt dat u meer informatie nodig hebt voordat u kunt beginnen met vertalen, lees dan de [[FAQ|Veel gestelde vragen]].
Helaas kan de documentatie soms verouderd zijn.
Als er iets is waarvan u denkt dat het mogelijk moet zijn, maar u weet niet hoe, aarzel dan niet om het te vragen op de [[Support|pagina voor ondersteuning]].

U kunt ook contact opnemen met collegavertalers van dezelfde taal op [[Portal:\$1|uw taalportaal]].
Deze verwijzing verwijst naar het portaal voor de taal die u hebt ingesteld als uw [[Special:Preferences|voorkeurstaal]].
Wijzig deze als nodig.",
	'translate-fs-email-text' => 'Geef uw e-mail adres in in [[Special:Preferences|uw voorkeuren]] en bevestig het via de e-mail die naar u verzonden is.

Dit makt het mogelijk dat andere gebruikers contact met u opnemen per e-mail.
U ontvangt dan ook maximaal een keer per maand de nieuwsbrief.
Als u geen nieuwsbrieven wilt ontvangen, dan kunt u dit aangeven in het tabblad "{{int:prefs-misc}}" van uw [[Special:Preferences|voorkeuren]].',
);

/** Portuguese (Português)
 * @author Hamilton Abreu
 */
$messages['pt'] = array(
	'firststeps' => 'Primeiros passos',
	'firststeps-desc' => '[[Special:FirstSteps|Página especial]] para familiarizar os utilizadores com o uso da extensão Translate numa wiki',
	'translate-fs-pagetitle-done' => ' - terminado!',
	'translate-fs-pagetitle' => 'Assistente de iniciação - $1',
	'translate-fs-signup-title' => 'Registe-se',
	'translate-fs-settings-title' => 'Configure as suas preferências',
	'translate-fs-userpage-title' => 'Crie a sua página de utilizador',
	'translate-fs-permissions-title' => 'Solicite permissões de tradutor',
	'translate-fs-target-title' => 'Comece a traduzir!',
	'translate-fs-email-title' => 'Confirme o seu endereço de correio electrónico',
	'translate-fs-intro' => "Bem-vindo ao assistente de iniciação da {{SITENAME}}.
Será conduzido passo a passo através do processo necessário para se tornar um tradutor.
No fim, será capaz de traduzir as ''mensagens da interface'' de todos os projectos suportados na {{SITENAME}}.",
);

/** Russian (Русский)
 * @author G0rn
 */
$messages['ru'] = array(
	'firststeps' => 'Первые шаги',
	'translate-fs-pagetitle-done' => '— сделано!',
	'translate-fs-signup-title' => 'Зарегистрируйтесь',
	'translate-fs-userpage-title' => 'Создайте свою страницу участника',
	'translate-fs-permissions-title' => 'Запросите права переводчика',
	'translate-fs-target-title' => 'Начните переводить!',
	'translate-fs-email-title' => 'Подтвердите ваш адрес электронной почты',
	'translate-fs-signup-text' => '[[Image:HowToStart1CreateAccount.png|frame]]

Для начала вам необходимо зарегистрироваться.

Авторство ваших переводов будет присваиваться имени вашей учётной записи.
Изображение справа показывает, как надо заполнять поля.

Если вы уже зарегистрированы, то вместо этого $1представьтесь$2.
После регистрации, пожалуйста, вернитесь на эту страницу.

$3Зарегистрироваться$4',
	'translate-fs-settings-text' => 'Теперь вам надо пройти в настройки и
изменить язык интерфейса на язык, на который вы собираетесь переводить.

Ваш язык интерфейса будет использоваться как язык для перевода по умолчанию.
Поскольку легко забыть изменить язык на правильный, установка его сейчас крайне рекомендуется.

Пока вы там, вы также можете включить отображение переводов на другие языки, которые вы знаете.
Эта опция находится во вкладке «{{int:prefs-editing}}».
Вы также можете изучить и другие настройки.

Сейчас пройдите на свою [[Special:Preferences|страницу настроек]], а потом вернитесь на эту страницу.',
	'translate-fs-settings-skip' => 'Готово. Перейти далее.',
	'translate-fs-userpage-text' => 'Теперь вам надо создать свою страницу участника.

Пожалуйста, напишите что-нибудь о себе; кто вы и чем вы занимаетесь.
Это поможет сообществу {{SITENAME}} работать вместе.
На {{SITENAME}} собираются люди со всего мира для работы над различными языками и проектами.

В предварительно заполненной форме наверху в самой первой строке указано <nowiki>{{#babel:en-2}}</nowiki>.
Пожалуйста, заполните этот блок в соответствии с вашим знанием языка.
Номер после кода языка показывает, насколько хорошо вы знаете этот язык.
Возможные варианты:
* 1 — небольшое знание
* 2 — базовое знание
* 3 — хорошее знание
* 4 — владение на уровне родного языка
* 5 — вы используете язык профессионально, например, если вы профессиональный переводчик.

Если этот язык является вашим родным, то уберите цифру и дефис, оставьте только код языка.
Пример: если тамильский язык является вашим родным, а также у вас есть хорошее знание английского и небольшое знание суахили, то вам нужно написать:
<tt><nowiki>{{#babel:ta|en-3|sw-1}}</nowiki></tt>

Если вы не знаете код языка, то сейчас самое время его узнать. Вы можете использовать список ниже.',
	'translate-fs-userpage-submit' => 'Создать мою страницу участника',
	'translate-fs-userpage-done' => 'Отлично! Теперь у вас есть страница участника.',
	'translate-fs-permissions-text' => 'Теперь вам необходимо подать запрос на добавление в группу переводчиков.

Пока мы не исправим код, пожалуйста, пройдите на страницу [[Project:Translator]] и следуйте инструкциями, а после этого вернитесь сюда.

После того, как вы подали запрос, один из волонтёров из команды сайта проверит его и одобрит как можно скорее.
Пожалуйста, будьте терпеливы.

<del>Убедитесь, что следующий запрос корректно заполнен и нажмите кнопку отправки.</del>',
	'translate-fs-target-text' => "Поздравляем! 
Теперь вы можете начать переводить.

Не бойтесь, если что-то до сих пор кажется новым и запутанным для вас.
В [[Project list|списке проектов]] находится обзор проектов, для которых вы можете осуществлять перевод.
Большинство проектов имеют небольшую страницу с описанием и ссылкой \"''Translate this project''\", которая ведёт на страницу со списком всех непереведённых сообщений.
Также имеется список всех групп сообщений с [[Special:LanguageStats|текущим статусом перевода для языка]].

Если вам кажется, что необходимо узнать больше перед началом перевода, то вы можете прочитать [[FAQ|часто задаваемые вопросы]].
К сожалению, документация иногда может быть устаревшей.
Если есть что-то, что по вашему мнению вы можете сделать, но не знаете как, то не стесняйтесь спросить об этом на [[Support|странице поддержки]].

Вы также можете связаться с переводчиками на тот же язык на [[Portal:\$1|портале вашего языка]].
Ссылка ведёт на портал языка, указанного в ваших [[Special:Preferences|настройках]].
Пожалуйста, измените его, если это необходимо.",
	'translate-fs-email-text' => 'Пожалуйста, укажите ваш адрес электронной почты в [[Special:Preferences|настройках]] и подтвердите его из письма, которое вам будет отправлено.

Это позволяет другим участникам связываться с вами по электронной почте.
Вы также будете получать новостную рассылку раз в месяц.
Если вы не хотите получать рассылку, то вы можете отказаться от неё на вкладке «{{int:prefs-misc}}» ваших [[Special:Preferences|настроек]].',
);

