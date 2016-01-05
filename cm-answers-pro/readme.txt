=== Plugin Name ===
Name: CM Answers Pro
Contributors: CreativeMindsSolutions
Donate link: http://answers.cminds.com/
Tags: answers, forum, questions, comments, question and answer, forum, q&a, list, stackoverflow, splunkbase
Requires at least: 4.0
Tested up to: 4.3.1
Stable tag: 2.9.5

Allow users to post questions and answers (Q&A) in stackoverflow style


**Demo**

* Demo [Read Only mode](http://answers.cminds.com/).


**More About this Plugin**

You can find more information about CM Answers at [CreativeMinds Website](http://answers.cminds.com/).


**More Plugins by CreativeMinds**

* [CM Enhanced ToolTip Glossary](http://wordpress.org/extend/plugins/enhanced-tooltipglossary/) - Parses posts for defined glossary terms and adds links to the static glossary page containing the definition and a tooltip with the definition.

* [CM Multi MailChimp List Manager](http://wordpress.org/extend/plugins/multi-mailchimp-list-manager/) - Allows users to subscribe/unsubscribe from multiple MailChimp lists.

* [CM Invitation Codes](http://wordpress.org/extend/plugins/cm-invitation-codes/) - Allows more control over site registration by adding managed groups of invitation codes.

* [CM Email Blacklist](http://wordpress.org/extend/plugins/cm-email-blacklist/) - Block users from blacklists domain from registering to your WordPress site.


== Installation ==

1. Upload the plugin folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Manage your CM Answers from Left Side Admin Menu

Note: You must have a call to wp_head() in your template in order for the JS plugin files to work properly.  If your theme does not support this you will need to link to these files manually in your theme (not recommended).



== Changelog ==
= 2.9.5 =
* Added option to place question form on a separate page.
* Added new labels.
* Fixed issue with rewrite.
* Fixed issue with tag related questions on the thread page.
* Fixed issue with settings.

= 2.9.4 =
* Added option to hide Mark as best answer button on own posts.
* Added email notification when best answer was choosen.
* Added option to display related questions by tags for a specific post.
* Fixed including some views due to the view overridding issue.
* Fixed sanitizing URLs for Cyrilic.
* Shortcode modification to support multiple tags.
* Added new labels.
* CSS fix.

= 2.9.3 =
* Fixed issue related with showing question author.
* Changes related to new CM Idea Stimulator Addon plugin.
* Labels updated.
* CSS improvements.

= 2.9.2 =
* Fixed PHP notice in the Ads helper.

= 2.9.1 =
* Fixed PHP error.

= 2.9.0 =
* Fixed memory issue on post saving.
* Added new "subtree" parameter for cma-questions and cma-index shortcodes.
* Changes for the CM AnsPress Import addon.
* Added ignoring InstantSearch plugin on the CMA search input field.
* Fixed translation files issue (invalid characters).
* Added question's custom fields feature which can be defined per category.
* Created shortcode cma-question-form.
* Changes for the CM Answers Petition addon.
* Improved the files attaching area on the question form.
* Changes for the CM Download Manager integration.
* Changes in the HTML source code - displaying index page without using the <table> tag.
* CSS improvements.

= 2.8.1 =
* Updated licensing API client.
* Fixed the WP 4.3 related issues.
* Fixed the preg_quote issue.
* Added notice about low memory.
* Added a setting to modify the BuddyPress CMA-shortcode attributes.
* Added custom CSS option and fixed the resolved flag issue.

= 2.8.0 =
* Added points reward for best answers (CM Micropayments integration).
* Fixed the Genesis theme issue with page template and home page.
* Fixed displaying the registration link.
* Fixed home page issue.

= 2.7.4 =
* Added option to change the category's slug URL part.
* Question will be automatically approved when the administrator will post an answer.
* Fixed the new questions notification issue.

= 2.7.3 =
* Fixed the Facebook login issue.

= 2.7.2 =
* Added new parameters and new columns to the cma-categories shortcode.
* Fixed the WP SEO custom taxonomy's title issue.
* Changed the "Categories" labels into unique "CMA Categories".
* Added require parameter on the category's select-box if needed.
* Added new labels.
* Added new translation terms.
* Reviewing the source code - testing for PCRE heap overflow vulnerability.
* Fixed issue with associating the BuddyPress groups with the CMA categories.
* Fixed allowed file extensions validation method for the question attachments.
* CSS improvements.

= 2.7.1 =
* Added the From: header to the emails messages which includes website name and the admin email.
* New hooks made for the new Anonymous Posting version.
* Opening the author page in the same browser's window.
* Fixed issue related to Yoast SEO plugin.
* SEO improvements.

= 2.7.0 =
* Added HTML support for the email notifications.
* Added new thread email notifications support with the opt-in and opt-out options.
* Added opt-out link support in new thread notification email.
* Added opt-out link support in new answer notification email.
* Fixed the attached image ratio.
* Fixed the category shortcode "follow" parameter.
* Fixed issue with the user's questions and answers counter.
* Fixed the Clear database method.
* Fixed displaying messages after posting a question or answer.

= 2.6.8 =
* Fixed issue with anonymous posting questions by AJAX.
* Preventing includes the Facebook and Google JS APIs when the share buttons are disabled.
* Fixed the homepage issue.
* Fixed the editor issue.
* Added option to disable posting/voting logs.
* CSS improvements.

= 2.6.7 =
* Added backend questions sorting by latest answer/comment.
* Fixed issue with sending notifications to all users.
* Fixed CSS lazy loading.
* CSS improvements.

= 2.6.6 =
* Added scrolling to top after loaded thread in the AJAX-based shortcode.
* Added option to hide category and the categories filter.
* Fixed BuddyPress integration issue.
* Fixed the labels issue.

= 2.6.5 =
* Fixed security issue related to the add_query_arg() function.
* Added bad words filter.
* SEO improvements.

= 2.6.4 =
* Added option to show unanswered questions only to experts.
* Added option to require category when posting a question.
* Added option to hide author info on the thread page.
* Added option to change editor's height.
* Added option to set related questions by posting user and meta-box for admin.
* Improved cma-questions shortcode.
* Fixed issue with Answers page title.
* Fixed issue with rich text editor sending empty content using ajax-based shortcode.
* Improved changing question's update date when posting a comment.

= 2.6.3 =
* Fixed JavaScript error.
* Fixed issue with question update date.
* Added some labels.

= 2.6.2 =
* Added support for anonymous voting.
* Added support to delete question by author.
* Added some translations keys.
* Fixed issue with removing other answers when marking best answer.
* Fixed issue with extra line breaks when editting an answer's content.
* Fixed issue with empty question content.
* Fixed issue with AJAX handlers.
* Fixed issue with sorting questions.
* Fixed issue with GA cookie param.
* Leaving the question content when validation went wrong.
* CSS improvements.

= 2.6.1 =
* Fixed issue with blank Answers page.
* Fixed issue with AJAX shortcodes.

= 2.6.0 =
* Support theme's page templates + improvements.
* Support for adding experts to category.
* Translation improvements.
* Fixed issue with CMA dummy page.
* Fixed sorting issue.
* Fixed issue with licensing API caching.
* Restored the login form when using CM Anonymous Posting.

= 2.5.9 =
* Added option to choose the page template for the questions index page and user's profile.
* Added option to hide the registration link on the login widget.
* Fixed issue with messages.
* Fixed PHP warnings.

= 2.5.8 =
* Fixed issue with the permission denied page.

= 2.5.7 =
* Added option to display attached images as thumbnails.
* Added option to disable app.css embedding.
* Improved grouping of the settings option.
* Fixed issue with labels.
* Fixed issue with sorting answers.
* Fixed issue with thread update date.
* Fixed issue while approving comments.
* Added fix to reload the CM Tooltip Glossary JavaScript handlers.

= 2.5.6 =
* Added new Access Control option - "Who can view answers".
* Added Facebook Share Button.
* Added sortbar to the AJAX-based shortcode.
* Made some code fragments independed from PHP session and using transients.
* Fixed issue with purging the W3TC cache.
* Added redirection from non-existing pagination pages.
* Displaying standard WP 404 template.
* Fixed issue with visible private answers.
* Fixed (un)answered questions filtering issue.

= 2.5.5 =
* Fixed issue with pagination for AJAX-based shortcodes.
* Fixed hiding category on the Questions Widget.
* SEO improvements.
* Added Portuguese translations.
* Added purge W3TC page cache on removing questions and answers to avoid 404 errors.

= 2.5.4 =
* Added disclaimer to the ajax-based page.
* SEO improvements.

= 2.5.3 =
* Restored the resolve/unresolve link on the wp-admin questions list.
* Settings page improvement.
* CSS fix.

= 2.5.2 =
* Added option to ask user whether to remove other answers when marking the best.
* Added option to embed noindex meta tag on the non-canonical pages.
* Fixed a hook issue.
* Fixed attachment extension validation: changed into case-insensitive.
* Fixed disclaimer.

= 2.5.1 =
* Added option to apply navbar filters instantly.
* Fixed issue with localization.
* Fixed error caused by User Guide menu item.

= 2.5.0 =
* Added option to choose the page template provided by theme for the thread pages.
* Added option to avoid duplicated questions.
* Added support for the shortcodes whitelist.
* Added new cma-categories shortcode attributes.
* Added new shortcode: cma-categories-list
* Added option to disable contributors' links and show only usernames.
* Added AJAX handler for posting questions on the AJAX-based shortcode.
* Added AJAX handler for tags filtering.
* Added AJAX handler for backlinks.
* Added validation to avoid multiple categories per question if edited via wp-admin UI.
* Added sorting by rating in the Top Contributors widget.
* Fixed error on the "About" and "Addons" page.
* Fixed SQL issue with invalid pagination.
* Fixed issue with jQuery UI.
* Fixed issues with AJAX.
* Fixed some SEO issues.
* Fixed error on creating category.
* Loading CSS and JS assets only when needed.
* Made CSS classes unique.
* Created new labels.

= 2.4.16 =
* Fixed registering sidebars.
* Added option to avoid multiple questions at all or for the same author.
* Fixed the Italian translation.

= 2.4.15 =
* Added nonce verification after forms submitting to prevent CSRF (CWE-352).
* Fixed issue with voting.

= 2.4.14 =
* Security improvements.
* Added Server Information about overridden template directory.
* Fixed the update checking script.
* Fixed voting in the AJAX-based shortcodes.
* Fixed rich text editor support in the AJAX-based shortcodes.

= 2.4.13 =
* Rebuilded the rating store logic.
* Improved the voting method.
* Added canonical URLs.
* Added the backend localization.

= 2.4.12 =
* Fixed the frequency the plugin checks for the update

= 2.4.11 =
* Fixed issue with using AJAX shortcode when the Answers page was disabled.
* Fixed notifications sending.
* Fixed HTML render error for some environments.
* Added localizations.
* Fixed BuddyPress private answers displaying.
* Added option to make private answers public.
* Added shortcode option to filter by resolved/unresolved.
* Added new widget options which were already supported by shortcodes.

= 2.4.10 =
* Integration with the BuddyPress groups: associating the CMA category to BP group and adding the wall activities when posting questions and answers.
* Improved the questions widget: added new display options and icons.
* Added option to disable thread following and notifications.
* Added option to display the question form above the questions list.
* Added option to display question's numbers vertically.
* Added new custom CSS option.
* Added the Access Denied message when using the shortcode.
* Fixed issue with editing questions.

= 2.4.9 =
* Added support to embed the YouTube and Vimeo clips in the questions and answers.
* Added Related Questions widget.
* Sending notifications emails using BCC.
* Added AJAX support to cma-my-answers shortcode.
* Fixed an issue with shortcode AJAX support.
* Fixed issue with notifications about questions being in moderation.

= 2.4.8 =
* Added support to notify all users about new questions and answers.
* Added option to mark all users as a thread followers.
* Improved the BuddyPress "Questions" tab view.
* Added new labels.
* Fixed issue with Social Login on the shortcodes.

= 2.4.7 =
* Some changes for the CM Anonymous Posting compatibility.
* Fixed CSS issue.

= 2.4.6 =
* Added ability to mark/unmark thread as resolved by admin.
* Fixed content filter to do not escape HTML added by admin.
* Fixed some security issues.
* Fixed issue with saving MicroPayments settings.

= 2.4.5 =
* Minor changes for Anonymous Posting.
* Fixed issue with duplicated question form.
* Fixed some warnings.
* CSS fix.

= 2.4.4 =
* Added AJAX support to load the categories in the AJAX interface.
* Fixed the default display options for the shortcodes to include the appearance settings.
* Added protection for the views counter.
* Added Swedish translation files.
* CSS fix.


= 2.4.3 =
* Added BuddyPress notifications.
* Added label "Choose category"
* Fixed the AJAX page navigation bar issues.
* CSS fix
* Added option to change the submit button background color.

= 2.4.2 =
* Added access restriction by roles for each category.
* Fixed updating Social Login settings.
* Hiding the admin menu for users without "manage_options" capability.

= 2.4.1 =
* Fix for the AJAX support.
* Fixed JS limit issue.

= 2.4.0 =
* Added AJAX interface support for the shortcodes and widgets.
* Added new cma-index shortcode to support Answers index page using standard WP page.
* Added responsive layout for the mobile devices.
* Added support for the BuddyPress user profile links.
* Fixed errors.
* Added option to unmark spam from the front-end.
* Added label for registration support.
* Added some enhancement to the thread page UEX.

= 2.3.3 =
* Added option to disable HTML filtering for content posted by chosen roles.
* Added option to disable WP header rewrite for SEO purpose (causing conflict with some themes).
* CSS minor fixes.
* Added option to un-mark spam.
* Added login widget.
* Added more support for admin notifications.
* Fix bug that causes spam postings.
* Added more support to BuddyPress.
* Add option to hide votes.

= 2.3.2 =
* Fixed some conflicts.
* Fixed ads module (stripslashes).

= 2.3.1 =
* Fix a bug with the widgets

= 2.3.0 =
* Private questions support.
* Private answers support.
* Support for the Google AdSense or other advertisements platform by adding the ads blocks.
* Added option to allow user to see only his own questions, which can be answered by admin.
* Breadcrumbs support.
* Changes for integration with anonymous posting.
* Added option to enable/disable thread resolving.
* Added registration link on the login widget as an option.
* From now the backlink parameter won't be added to the URL by default.
* Fixed issue with login widget rendering.
* Fixed conflict with the Kodax theme.
* Updated RU translation.

= 2.2.3 =
* Some issues fixed.

= 2.2.2 =
* AJAX support for comments.
* Improved tags adding and suggests.
* Changed default layout of the answers page to looks like stackoverflow.
* Added option to notify users also when a new comment was posted.
* Added new attribute "contributor" to the cma-questions shortcode.
* Added editable description below the Answers index page title.
* Added option to disable backlink parameter and use HTTP referer header instead.
* Added option to turn on/off voting for self-posted questions.
* Added option to enable JavaScript limit of the input fields.
* Added new editable labels.
* Added error messages on file upload.
* W3C standards improvements.
* Fixed issue with Top Contributors widget. Added new sorting options.
* Fixed bug with the email notifications.
* Improved intergration with default comments by adding extra options.

= 2.2.1 =
* Fix security bug.
* Add several labels support.

= 2.2.0 =
* Support for commenting questions and answers.
* Upload attachments by drag-and-drop.
* Attachments support for answers.
* Added ability to change question rating.
* Added option to set non-default login page URL.
* Fixed the conflict with Premium Press theme.
* Moved old logs into new data model.

= 2.1.12 =
* Added support to follow new threads for a whole category.
* Created shortcode to list followed threads and categories and unsubscribe.
* Added the BuddyPress wall notifications on posting a question/answer.
* Added an option to report question/answer as a spam and send email notification.
* Expanded log and graphs support for posted users' answers and voting. Added erase log option and CSV file download.
* Created new entering users GUI to not load all users list.
* Allowed the user to modify his question tags.
* Fixed problem with captcha displaying when using the Responsive Shop Theme by Premium Press.
* Fixed issue with stripped tags when posting from the rich text editor.
* Fixed the Facebook Like button conflict.

= 2.1.11 =
* Added option to replace default comments system for post/pages with the CMA questions
* Added questions logs support and posted questions graph
* Added new options for the cma-questions shortcode: navbar, displaycategories
* Added licence manager
* Fixed issue with not showing attachments and moved above the question body
* Removed PHP short tags which causing errors on some configurations
* Fixed bug with searching from shortcode
* Added img alt attributes
* Added more configurable labels
* Updated Dutch translation

= 2.1.10 =
* Added support for marking the best answer for the question
* Added support for marking favorite questions
* Added support for posting in the primary categories when two-level categories filter is enabled
* Updated instructions for setting the social login for Facebook and Google+
* Added option to disable the navigation bar on the thread page
* Added option to remove the login box
* Added option to remove the number of answers from question title
* Added the new attribute "answered" for the cma-questions shortcode
* Added the custom CSS examples with a simple editor
* Improved the Server information tab in the Settings
* Improved the settings UX
* Added Dutch translation

== 2.1.9 ==
* Support  for two level category navigation
* Integration for BuddyPress profile

==2.1.8==
* Fixed the bug with attachments not showing on the question page

= 2.1.7 =
* Added some missing translations
* Fixed the bug with Widgets not being saved
* Fixed the rare bug with search
* Renewed the question interface
* Redesigned settings
* Added the options for "votes" box
* Added the options to setup labels

= 2.1.6 =
* Added the option to edit questions and answers
* Added the option to clear the plugin data from the database
* Added the options to change labels
* Fixed the empty category view
* Fixed problems with paragraphs

= 2.1.5 =
* Added the support for BuddyPress (custom type for filter: "bp_blogs_record_comment_post_types")
* Fixed the bug with WP login form not appearing for guest users

= 2.1.4 =
* Fixed a rare bug with answers count
* Fixed problem with missing sidebar/broken template
* Added Estonian language support

= 2.1.3 =
* Redesigned the access control options for the plugin
* Added the option for admin to change the views counter (in "Question Properties" meta-box)
* Added the CMA sidebar to the contributor profile
* Added the option to disable the CMA sidebar on the contributor profile
* Added the template for the contributor profile
* Fixed the filtering dropdown on the answers index page

= 2.1.2 =
* Fixed the bug with unclosed div
* Updated the User Guide link
* Changed the CSS class of the question
* Added a link to the WP login form (optional - enabled by default)

= 2.1.1 =
* Fixed the theme compatibility with 20-14 theme bundled with Wordpress 3.8

= 2.1.0 =
* Fixed notifications appearing on some plugin installations
* Fixed answers count after manual SQL removal

= 2.0.12 =
* Added the option to define the HTML class for the "Questions" Widget (defaults to cma-sidebar-questions)
* Added italian language files
* Fixed the social login module response to the authorization being canceled

= 2.0.11 =
* Fixed the rare conflict when 'the_comments' filter stopped the answers from showing up

= 2.0.10 =
* Fixed the FB warnings
* Fixed the bug with answers disappearing on the question page when answers sort setting was changed to "Votes"

= 2.0.9 =
* Small changes to the settings
* Fixed the bug with author page links
* Changed the source of jQuery UI to bundled

= 2.0.8 =
* Changes to the CSS to make the plugin suit into WP default themes
* Added the option to allow questions without content
* Fixed the rare bug with question's 'asked on' format
* Fixed the bug with contributor page author
* Fixed the bug with trashed answers being counted

= 2.0.7 =
* Fixed the CSS code for the <pre> tags in the answers
* Fixed the timestamp issues for the "last updated" section
* Added the option to include "Custom CSS"
* Added the option to enable answers even after the question is "Resolved"
* Added the option to reject the disclaimer

= 2.0.6 =
* Fixed bug with "Show Author" in the "Question Widget"
* Added new options for shortcodes
* Fixed some PHP bugs from previous releases
* Added the option to disable the main question's list

= 2.0.5 =
* Added the option to show/hide the link to the question's author page on the question's list
* Added the option to show/hide the information about the question's updates
* Added the options to hide author information and updates information for the "Questions Widget"
* Fixed the behavior of the content editor - so now it saves the line breaks
* Fixed the styling of the "Questions Widget" so the title of the question comes first if it's too narrow
* Added the affiliate programme
* Added the backlink for the contributor's page

= 2.0.4 =
* Fixed sidebar styles
* Added the options to hide Views/Votes/Answers for the "Questions Widget"

= 2.0.3 =
* Fixed the contributor's page
* Added the option to turn on richtext editor for question/answer content

= 2.0.2 =
* Fixed the bug with subscribers being unable to post comments
* Allowed users with 'author' role to post questions if the access is restricted
* Fixed a bug with the permalink setting
* Fixed a bug with the question listing title setting

= 2.0.1 =
* Removed the styles from Twenty Twelve which were causing conflicts
* Fixed the setting hiding the tags
* Fixed the incorrect question count on the tag widget
* Fixed some of the styles
* Fixed some PHP bugs

= 2.0.0 =
* Fixed many issues regarding shortcodes and AJAX
* Fixed the moderation options behavior
* Added new supported options for the shortcodes
* Added the table explaining the moderation options behavior on settings page
* Added the option to remove the markup box near question/answer form
* Added the option support for the [cma-my-questions] and [cma-my-answers]
* Tidied up the plugin's views CSS/HTML
* Show only approved comments on highest rating
* "Question marked as spam" issue resolved
* Fixed the css issues with some themes
* Fixed pagination on category pages bug
* Fixed the FB like button
* WP-admin links removed for subscribed
* Fixed the bug with which appeared when a random string was added to the answer's url
* Changed the way how "Moderation" options look and work
* Plugin now shows the user's name from the time when they posted the answer not from the profile
* Prefixed the styles

= 1.9.12 =
* Added option to remove search box and tags from questions widget
* Added option to remove number of answers from contributers widget
* Added Social share in question page


= 1.9.11 =
* Added option to edit author of question for administrators

= 1.9.10 =
* Fixed warning when no categories are added

= 1.9.9 =
* Fixed wpdb->prepare warning

= 1.9.8 =
* Fixed display for permissions warning in widget area and regular lists
* Fixed answer sorting in questions shortcode

= 1.9.7 =
* Added trigger for new questions to be filtered by comment spam filters
* Added option to hide questions/answers from not logged-in users

= 1.9.6 =
* Added category tree in dropdowns (only main and subcategories) for questions

= 1.9.5 =
* Fixed notify on follow email
* Fixed several problems with sticky questions
* Fixed problem with sorting in [cma-questions] shortcode
* Added Disclaimer support for first time users

= 1.9.4 =
* Questions listing title can be changed in settings

= 1.9.3 =
* Added option to set questions list as homepage
* Changed contributor link structure to /contributor/name

= 1.9.2 =
* Added 'remove_accents()' to sanitize_title function

= 1.9.1 =
* Added option to edit "Questions" listing title
* Fixed ajax search
* Fixed problem with login box appearing for resolved questions
* Fixed problem with logging in from shortcode single page

= 1.9.0 =
* Fixed bug with custom permalink
* Add tags support. Admin can control the appearance of tags
* Add tags widget and top contributors widget
* Add Admin control to restrict who can ask questions
* Add option to change plugin permalink
* Add support to sticky posts with admin defined background color
* Add support to code snippets background color
* Added filter for answered and not answered questions in questions list
* Add option to show question description in html title
* Add option to change 0 to no in number of views/answers
* Add support in setting for number of questions in page


= 1.8.3 =
* Fixed bug with wp_enqueue_script

= 1.8.2 =
* Fixed bug with not displaying last poster name for new threads
* Added option to disable sidebar or set its max-width

= 1.8.0 =
* All links added via [cma-questions] shortcode are now working via ajax without changing the page template
* Added user guide

= 1.7.0 =
* Corrected daysAgo calculation, added hours/minutes/seconds
* Corrected translations

= 1.6.6 =
* Fixed bug with Avatar user id
* Support plural and singular in french (views, votes, answers)

= 1.6.5 =
* Changed category submenu capability to manage_categories instead of  manage_options
* Replaced all <? with <?php
* Added support fopr French in number of Votes


= 1.6.4 =
* Fix time ago function

= 1.6.3 =
* Bug with <pre> code insertion
* Bug with hiding upload section


= 1.6.2 =
* Bug preventing question with no file to be sent
* Bug with empty categories not showing up


= 1.6.1 =
* Localization of frontend labels for German, Spanish, Polish
* Fixed renderDaysAgo function
* Fixed pagination to work with permalink structure without trailing slash
* Fixed comment direct link
* Fixed [author] shortcode
* Fixed status header in [cma-my-questions] shortcode
* Fixed problem with adding attachment from [cma-questions] shortcode
* Add question form is now populated with previous data when error occurs

= 1.6.0 =
* Added gravatar profile photos
* Added option to change default sorting for answers between ascending and descending
* Added possibility to add attachments to questions
* Added option to block views incrementation upon site refresh

= 1.5.1 =
* Removed unused admin.js

= 1.5 =
* Added option to hide categories in questions widget
* Fixed "back" link on question page

= 1.4 =
* Datetimes are now formatted according to wordpress general settings
* Dates use date_i18n function to produce localized names
* Fixed escaping for notification titles and contents
* Added images for social login links
* Fixed template
* Added category dropdown for new questions (active when there's at least one category)
* Added user profile pages
* Added category pages

= 1.3 =
* If user logged in via social login, his name will become link to his public profile
* Added shortcodes and widget for latest/hottest/most voted/viewed/categorized questions

= 1.2 =
* Added social login
* Added categories for questions
* Added options to show/hide views/votes/answers
* Added number of QA near each name
* Added tabs for settings

= 1.1 =
* Renamed main list from "Answers" to "Questions"
* fixed bug when sorting answers by votes didn't show answers without any votes (will work only for answers added after upgrade)
* Added validation for question (it's not possible to add empty one now)
* Minor fix in styling
* Added link to answers from admin menu

= 1.0 =
* Initial release
