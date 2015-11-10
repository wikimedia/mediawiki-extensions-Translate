Given(/^I am logged in as a translation administrator$/) do
  step 'I am logged in'
end

Given(/^I am on the Translator sandbox management page with no users in the sandbox$/) do
  visit(ManageTranslatorSandboxPage, using_params: { extra: 'integrationtesting=empty' })
end

Given(/^I am on the Translator sandbox management page with users in the sandbox$/) do
  visit(ManageTranslatorSandboxPage, using_params: { extra: 'integrationtesting=populate' })
end

When(/^I search for "(.*)" in the sandboxed users search field$/) do |string|
  on(ManageTranslatorSandboxPage) do |page|
    page.search = string
    page.search_element.send_keys :enter
  end
end

When(/^I click the sandboxed users language filter button$/) do
  on(ManageTranslatorSandboxPage).language_selector_button
end

When(/^I type "(.+)" in the language filter$/) do |text|
  on(ManageTranslatorSandboxPage) do |page|
    page.language_filter = text
    page.language_filter_element.send_keys [:enter, "\n"]
  end
end

When(/^I click the button that clears language selection$/) do
  on(ManageTranslatorSandboxPage).clear_language_selector_element.click
end

When(/^I click the checkbox to select all users$/) do
  on(ManageTranslatorSandboxPage).select_all_checkbox_element.click
end

When(/^I click the "(.+)" button$/) do |label|
  on(ManageTranslatorSandboxPage).click_button(label)
end

When(/^I click on "(.+)" in the first column$/) do |username|
  on(ManageTranslatorSandboxPage).request_with_username(username).click
end

When(/^I click on the checkbox near "(.+)" in the first column$/) do |username|
  on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(username).click
end

When(/^I click on the link that says "\d+ older requests?" at the bottom of the first column$/) do
  on(ManageTranslatorSandboxPage).older_requests_indicator_element.click
end

When(/^I go to the userpage of user "(.*?)"$/) do |username|
  visit(UserPage, using_params: { extra: username })
end

Then(/^I should see a babel box with languages "(.*?)"$/) do |languages|
  on(UserPage).babel_box_has_languages?(languages).should be_true
end

Then(/^I should not see the older requests link at the bottom of the first column$/) do
  on(ManageTranslatorSandboxPage).older_requests_indicator_element.should_not be_visible
end

Then(/^I should not see any users except "(.+)" selected$/) do |username|
  on(ManageTranslatorSandboxPage).only_request_with_username_is_selected?(username).should be_true
end

Then(/^I should not see any translations done by the user in the second column$/) do
  on(ManageTranslatorSandboxPage) do |page|
    page.translation_elements.size.should == 0
    page.details_no_translations.size.should == 1
    page.details_no_translations[0].should be_visible
  end
end

Then(/^I should not see any translations done by the users in the second column$/) do
  on(ManageTranslatorSandboxPage).translation_elements.length.should == 0
end

Then(/^I should see the details of (\d+) sandboxed translations done by the user in the second column$/) do |translations|
  on(ManageTranslatorSandboxPage) do |page|
    page.translation_elements.size.should == translations.to_i
    page.details_no_translations.size.should == 0
  end
end

Then(/^I should not see user "(.+)" in the first column$/) do |username|
  on(ManageTranslatorSandboxPage) do |page|
    Watir::Wait.until { page.visible_requests_element.size < 11 }
    page.request_with_username(username).should_not exist
  end
end

Then(/^I should see that (\d+) reminders were sent to the user$/) do |count|
  on(ManageTranslatorSandboxPage) do |page|
    page.reminder_status_element.should be_visible
    page.reminder_status.should match(/^Sent #{count} reminders/i)
  end
end

Then(/^I should see that no reminders have been sent to the user$/) do
  on(ManageTranslatorSandboxPage).reminder_status.should == ''
end

Then(/^the direction of the users language filter button is "(.+)"$/) do |dir_value|
  on(ManageTranslatorSandboxPage).language_selector_button_element.attribute('dir').should == dir_value
end

Then(/^the language code of the users language filter button is "(.+)"$/) do |lang_value|
  on(ManageTranslatorSandboxPage).language_selector_button_element.attribute('lang').should == lang_value
end

Then(/^usernames are visible in the first column$/) do
  on(ManageTranslatorSandboxPage).visible_users_element.size.should_not == 0
end

Then(/^I should see the name of language "(.+)" in the second column$/) do |language|
  on(ManageTranslatorSandboxPage).details_autonym.text.should == language
end

Then(/^I should see that the language of the first translation is "(.+)"$/) do |language|
  on(ManageTranslatorSandboxPage).translations_autonyms[0].text.should == language
end

Then(/^only users whose name begins with "(.*)" are displayed in the first column$/) do |prefix|
  on(ManageTranslatorSandboxPage).visible_users_start_with?(prefix).should be_true
end

Then(/^a user whose name begins with "(.*)" is displayed in the first column$/) do |prefix|
  on(ManageTranslatorSandboxPage).the_first_column_has_username_starting_with?(prefix).should be_true
end

Then(/^no users are displayed in the first column$/) do
  on(ManageTranslatorSandboxPage) do |page|
    Watir::Wait.until { page.visible_requests_element.size < 11 }
    page.visible_users_element.length.should == 0
  end
end

Then(/^I should see "(.+)" at the top of the first column$/) do |text|
  on(ManageTranslatorSandboxPage).request_count.should == text
end

Then(/^I should see the button that clears language selection$/) do
  on(ManageTranslatorSandboxPage).clear_language_selector_element.should be_visible
end

Then(/^I should not see the button that clears language selection$/) do
  on(ManageTranslatorSandboxPage).clear_language_selector_element.should_not be_visible
end

Then(/^only users who translate to language "(.+)" are displayed in the first column$/) do |language|
  on(ManageTranslatorSandboxPage).all_visible_requests_translate_to?(language).should be_true
end

Then(/^I should see "(.+)" at the bottom of the first column$/) do |text|
  on(ManageTranslatorSandboxPage).selected_counter.should == text
end

Then(/^I should see that the user wrote a comment that says "(.*?)"$/) do |text|
  on(ManageTranslatorSandboxPage).signup_comment_text.should == text
end

Then(/^I should not see that the user wrote a comment$/) do
  on(ManageTranslatorSandboxPage).signup_comment_text_element.should_not exist
end

Then(/^I should see the name of the first user in the first column in the header of the second column$/) do
  on(ManageTranslatorSandboxPage) do |page|
    page.details_header.text.should == page.username_in_request(0)
  end
end

Then(/^I should see "(.+)" in the header of the second column$/) do |text|
  on(ManageTranslatorSandboxPage).details_header.text.should == text
end

Then(/^I should see the userlist in the first column sorted by the number of translations and the most recent within them$/) do
  on(ManageTranslatorSandboxPage).requests_are_sorted_by_translation_count_and_date?.should be_true
end

Then(/^I should see the checkbox next to the request from "(.+)" checked$/) do |user|
  on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should be_checked
end

Then(/^I should see the checkbox next to the request from "(.+)" unchecked$/) do |user|
  on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should_not be_checked
end

Then(/^I should see the checkbox next to the request from "(.+)" disabled$/) do |user|
  on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should be_disabled
end

Then(/^I should see the checkbox next to the request from "(.+)" enabled$/) do |user|
  on(ManageTranslatorSandboxPage).checkbox_for_request_with_username(user).should_not be_disabled
end

Then(/^I should see the "(.+)" button displayed in the second column$/) do |label|
  on(ManageTranslatorSandboxPage).details_button(label).should be_visible
end

Then(/^I should see "(.+)" in the older requests link at the bottom of the first column$/) do |text|
  on(ManageTranslatorSandboxPage) do |page|
    page.older_requests_indicator_element.should be_visible
    page.older_requests_indicator_element.text.should == text
  end
end

Then(/^I should see that the user's translations are sorted by the language code$/) do
  on(ManageTranslatorSandboxPage).translations_languages_are_sorted?.should be_true
end
