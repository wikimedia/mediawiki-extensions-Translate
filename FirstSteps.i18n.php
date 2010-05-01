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

Your interface languages is used as the default target language.
It is easy to forget to change the langauge to the correct one, so setting it now is higly recommended.

While you are there, you can also request the software to display translations in other languages you know.
This setting can be found under tab "{{int:prefs-editing}}".
Feel free to explore other settings, too.

Go to your [[Special:Preferences|preferences page]] now and then return back to this page.',
	'translate-fs-settings-skip' => "I'm done. Let me proceed.",
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
<tt><nowiki>{{#babel:ta|en-3|sw-1}}</nowiki></tt>

If you do not know the language code of a language, now is good time to look it up. You can use the list below.',
	'translate-fs-userpage-submit' => 'Create my userpage',
	'translate-fs-userpage-done' => 'Well done! You now have an user page.',
	'translate-fs-permissions-text' => 'Now you need to place a request to be added to the translator group.

Until we fix the code, please go to [[Project:Translator]] and follow the instructions.
Then come back to this page.

After you have filed your request, one of the volunteer staff members will check your request and approve it as soon as possible.
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
