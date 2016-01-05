<?php

require_once CMA_PATH . '/lib/models/SettingsAbstract.php';

class CMA_Settings extends CMA_SettingsAbstract {
	
	const TEXT_DOMAIN = 'cm-answers-pro-backend';
		
	// General
	const OPTION_ANSWERS_PERMALINK = 'cma_answers_permalink';
	const OPTION_CATEGORIES_URL_PART = 'cma_categories_url_part';
	const OPTION_ANSWERS_AS_HOMEPAGE = 'cma_answers_as_homepage';
	const OPTION_ADD_ANSWERS_MENU = 'cma_add_to_nav_menu';
	const OPTION_ANSWER_PAGE_DISABLED = 'cma_answer_page_disabled';
	const OPTION_CREATE_AJAX_PAGE = 'cma_create_ajax_page';
	const OPTION_LOGS_ENABLED = 'cma_logs_enabled';
	const OPTION_GEOLOCIATION_API_KEY = 'cma_geolocation_api_key';
	const OPTION_SEO_META_REWRITE_ENABLED = 'cma_seo_meta_rewrite_enabled';
	const OPTION_INDEX_META_TITLE = 'cma_index_meta_title';
	const OPTION_INDEX_META_DESC = 'cma_index_meta_desc';
	const OPTION_INDEX_META_KEYWORDS = 'cma_index_meta_keywords';
	const OPTION_NOINDEX_NON_CANONICAL = 'cma_noindex_non_canonical';
	const OPTION_NOINDEX_CONTRIBUTOR = 'cma_noindex_contributor';
	const OPTION_BACKLINK_PARAM_ENABLED = 'cma_backlink_param_enabled';
	const OPTION_PRIVATE_QUESTIONS_ENABLED = 'cma_private_questions_enabled';
	const OPTION_PRIVATE_QUESTION_EMAIL_SUBJECT = 'cma_private_question_email_subject';
	const OPTION_PRIVATE_QUESTION_EMAIL_TEMPLATE = 'cma_private_question_email_template';
	const OPTION_BAD_WORDS_FILTER_ENABLE = 'cma_bad_words_filter_enable';
	const OPTION_BAD_WORDS_LIST = 'cma_bad_words_list';
	const OPTION_BP_RELATED_CATEGORY = 'cma_bp_related_category';
	
	// Appearance
	const OPTION_INDEX_HEADER_AFTER_TEXT = 'cma_index_header_after_text';
	const OPTION_SHOW_TITLE_FOUND_POSTS = 'cma_show_title_found_posts';
	const OPTION_SHOW_INDEX_TITLE = 'cma_show_index_title';
	const OPTION_ITEMS_PER_PAGE = 'cma_items_per_page';
	const OPTION_INDEX_ORDER_BY = 'cma_index_order_by';
	const OPTION_COLUMN_VOTES_ENABLED = 'cma_column_votes_enabled';
	const OPTION_VIEWS_ALLOWED = 'cma_views_allowed';
	const OPTION_ANSWERS_ALLOWED = 'cma_answers_allowed';
	const OPTION_INDEX_NUMBERS_VERTICALLY = 'cma_index_numbers_vertically';
	const OPTION_AUTHOR_ALLOWED = 'cma_author_allowed';
	const OPTION_AUTHOR_LINK_ENABLED = 'cma_author_link_enabled';
	const OPTION_UPDATED_ALLOWED = 'cma_updated_allowed';
	const OPTION_TABLE_QUESTION_ID_SHOW = 'cma_table_question_id_show';
	const OPTION_VOTES_MODE = 'cma_votes_mode';
	const OPTION_VOTES_NO = 'cma_votes_no';
	const OPTION_STICKY_QUESTION_COLOR = 'cma_sticky_color';
	const OPTION_SHOW_USER_STATS = 'cma_show_user_stats';
	const OPTION_SHOW_GRAVATARS = 'cma_show_gravatars';
	const OPTION_SHOW_SOCIAL = 'cma_show_social';
	const OPTION_SHOW_QUESTION_AUTHOR = 'cma_show_question_author';
	const OPTION_SHOW_ANSWER_AUTHOR = 'cma_show_answer_author';
	const OPTION_ANSWER_SORTING_BY = 'cma_answer_sorting_by';
	const OPTION_ANSWER_SORTING_DESC = 'cma_answer_sorting_desc';
	const OPTION_THREAD_PAGE_TEMPLATE = 'cma_thread_page_template';
	const OPTION_INDEX_PAGE_TEMPLATE = 'cma_index_page_template';
	const OPTION_SHORTCODES_WHITELIST = 'cma_shortcodes_whitelist';
	const OPTION_RICHTEXT_EDITOR = 'cma_richtext_editor';
	const OPTION_EDITOR_ROWS = 'cma_editor_rows';
	const OPTION_NOTIFY_CHECKBOX_CHECKED = 'cma_notify_checkbox_checked';
	const OPTION_HIDE_REQUIRED_CATEGORY_IF_ONE = 'cma_hide_required_category_if_one';
	const OPTION_MARKUP_BOX = 'cma_markup_box';
	const OPTION_JS_LIMIT = 'cma_js_limit';
	const OPTION_JS_LIMIT_QUESTION_TITLE = 'cma_js_limit_question_title';
	const OPTION_JS_LIMIT_QUESTION_DESCRIPTION = 'cma_js_limit_question_description';
	const OPTION_JS_LIMIT_QUESTION_COMMENT = 'cma_js_limit_question_comment';
	const OPTION_JS_LIMIT_ANSWER_COMMENT = 'cma_js_limit_answer_comment';
	const OPTION_SHOW_LOGIN_WIDGET = 'cma_show_login_widget';
	const OPTION_SHOW_LOGIN_FORM = 'cma_show_login_form';
	const OPTION_SHOW_REGISTRATION_LINK = 'cma_show_registration_link';
	const OPTION_REGISTRATION_URL = 'cma_registration_url';
	const OPTION_LOGIN_PAGE_LINK_TITLE = 'cma_login_page_link_title';
	const OPTION_LOGIN_PAGE_LINK_URL = 'cma_login_page_link_url';
	const OPTION_REPLACE_POST_COMMENTS_SHORTCODE_ATTR = 'cma_replace_post_comments_shortcode_attr';
	const OPTION_BP_GROUP_SHORTCODE_ATTR = 'cma_bp_group_shortcode_attr';
	const OPTION_CONTRIBUTOR_CUSTOM_URL = 'cma_contributor_custom_url';
	const OPTION_BREADCRUMBS_ENABLED = 'cma_breadcrumbs_enabled';
	const OPTION_SHOW_QUESTION_PAGE_NAV_BAR = 'cma_show_question_page_nav_bar';
	const OPTION_CATEGORY_FILTER_MODE = 'cma_category_filter_mode';
	const OPTION_ALLOW_POST_ONLY_SUBCATEGORIES = 'cma_allow_post_only_subcategories';
	const OPTION_SUBMIT_BUTTON_CSS_BACKGROUND = 'cma_submit_button_css_background';
	const OPTION_ESCAPE_PRE_CONTENT = 'cma_escape_pre_content';
	const OPTION_CONTENT_ALLOWED_TAGS = 'cma_content_allowed_tags';
	const OPTION_QUESTION_FORM_ON_TOP = 'cma_question_form_on_top';
	const OPTION_QUESTION_FORM_BUTTON = 'cma_question_form_button';
	const OPTION_SUPPORT_THEME_DIR = 'cma_support_theme_dir';
	const OPTION_DISABLE_CSS = 'cma_disable_css';
	const OPTION_HIDE_CATEGORIES = 'cma_hide_categories';
	const OPTION_RELATED_QUESTIONS_POST_TYPES = 'cma_related_questions_post_types';
	const OPTION_RELATED_QUESTIONS_SHORTCODE_PARAMS = 'cma_related_questions_shortcode_params';
	const OPTION_NAVBAR_AUTO_SUBMIT = 'cma_navbar_auto_submit';
	const OPTION_EMBED_ATTACHED_IMAGES = 'cma_embed_attachmed_images';
	const OPTION_EMBED_ATTACHED_IMAGES_SIZE = 'cma_embed_attachmed_images_size';
	
	const OPTION_QUESTION_DESCRIPTION_OPTIONAL = 'cma_question_description_optional';
	const OPTION_QUESTION_REQUIRE_CATEGORY = 'cma_question_require_category';
	const OPTION_TAGS_SWITCH = 'cma_tags_switch';
	const OPTION_ENABLE_MARK_FAVORITE_QUESTIONS = 'cma_enable_mark_favorite_questions';
	const OPTION_ENABLE_QUESTION_RATING = 'cma_enable_question_rating';
	
	
	// Spam
	const OPTION_SPAM_REPORTING_ENABLED = 'CMA_spam_reporting_enabled';
	const OPTION_SPAM_REPORTING_GUESTS = 'CMA_spam_reporting_guests';
	const OPTION_SPAM_REPORTING_EMAIL_ADDR = 'CMA_spam_reporting_email_addr';
	const OPTION_SPAM_REPORTING_EMAIL_SUBJECT = 'CMA_spam_reporting_email_subject';
	const OPTION_SPAM_REPORTING_EMAIL_TEMPLATE = 'CMA_spam_reporting_email_template';
	
	// Thread Answers
	const OPTION_ANSWER_AFTER_RESOLVED = 'cma_answer_after_resolved';
	const OPTION_ANSWERS_RATING_ALLOWED = 'cma_rating_allowed';
	const OPTION_ENABLED_MARK_BEST_ANSWER = 'cma_enabled_mark_best_answer';
	const OPTION_ALLOW_OWN_BEST_ANSWER = 'cma_allow_own_best_answer';
	const OPTION_ENABLED_UNMARK_BEST_ANSWER = 'cma_enabled_unmark_best_answer';
	const OPTION_BEST_ANSWER_REMOVE_OTHER = 'cma_best_answer_remove_other';
	const OPTION_PRIVATE_ANSWERS_ENABLED = 'cma_private_answers_enabled';
	const OPTION_PRIVATE_ANSWER_BGCOLOR = 'cma_private_answer_bgcolor';
	
	// Thread comments
	const OPTION_COMMENTS_QUESTION_ENABLE = 'CMA_comments_question_enable';
	const OPTION_COMMENTS_ANSWER_ENABLE = 'CMA_comments_answer_enable';
	
	// Thread Common
	const OPTION_INCREMENT_VIEWS = 'cma_increment_views';
	const OPTION_NEGATIVE_RATING_ALLOWED = 'cma_negative_rating_allowed';
	const OPTION_EDIT_MODE = 'cma_edit_mode';
	const OPTION_CAN_EDIT_RESOLVED = 'cma_allow_edit_resolved';
	const OPTION_RESOLVE_THREAD_ENABLED = 'cma_resolve_thread_enabled';
	const OPTION_SHOW_ONLY_OWN_QUESTIONS = 'cma_show_only_own_questions';
	const OPTION_DUPLICATED_QUESTIONS_MODE = 'cma_duplicated_questions_mode';
	const OPTION_ALLOW_QUESTION_DELETE = 'cma_allow_question_delete';
	const OPTION_ALLOW_QUESTION_DELETE_NOANSWERS = 'cma_allow_question_delete_noanswers';
	const OPTION_QUESTION_DELETE_TRASH = 'cma_question_delete_trash';
	const OPTION_USER_RELATED_QUESTIONS_ENABLE = 'cma_user_related_questions_enable';
	const OPTION_THREAD_DISPLAY_ID = 'cma_thread_display_id';
// 	const OPTION_ALLOW_UNVOTE = 'cma_allow_unvote';
	const OPTION_CAN_VOTE_MYSELF = 'cma_can_vote_myself';
	const OPTION_ALLOW_GUESTS_VOTING = 'cma_allow_guests_voting';
	
	// Attachments
	const OPTION_ATTACHMENTS_QUESTIONS_ALLOW = 'CMA_attachments_questions_allow';
	const OPTION_ATTACHMENTS_ANSWERS_ALLOW = 'CMA_attachments_answers_allow';
	const OPTION_ATTACHMENTS_FILE_EXTENSIONS = 'cma_attachment_allowed';
	const OPTION_ATTACHMENTS_MAX_SIZE = 'cma_attachment_max_size';
	

	// Moderation
	const OPTION_QUESTION_AUTO_APPROVE = 'cma_question_auto_approve';
	const OPTION_SIMULATE_COMMENT = 'cma_simulate_comment';
	const OPTION_ANSWER_AUTO_APPROVE = 'cma_answer_auto_approve';
	const OPTION_COMMENTS_AUTO_APPROVE = 'CMA_comments_auto_approve';
	const OPTION_AUTO_APPROVE_AUTHORS = 'cma_answer_authors_auto_approved';
	
	// Access
	const OPTION_VIEW_ACCESS = 'cma_access_view';
	const OPTION_VIEW_ACCESS_ROLES = 'cma_access_view_roles';
	const OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS = 'cma_restrict_unans_questions_to_experts';
	const OPTION_ACCESS_VIEW_ANSWERS = 'cma_access_answers_view';
	const OPTION_ACCESS_VIEW_ANSWERS_ROLES = 'cma_access_answers_view_roles';
	const OPTION_POST_QUESTIONS_ACCESS = 'cma_access_post_questions';
	const OPTION_POST_QUESTIONS_ACCESS_ROLES = 'cma_access_post_questions_roles';
	const OPTION_POST_ANSWERS_ACCESS = 'cma_access_post_answers';
	const OPTION_POST_ANSWERS_ACCESS_ROLES = 'cma_access_post_answers_roles';
	const OPTION_POST_COMMENTS_ACCESS = 'cma_access_post_comments';
	const OPTION_POST_COMMENTS_ACCESS_ROLES = 'cma_access_post_comments_roles';
	const OPTION_ALLOW_FULL_HTML_ROLES = 'cma_allow_full_html_roles';
	
	// Notifications
	const OPTION_EMAIL_USE_HTML = 'cma_email_use_html';
	const OPTION_EMAIL_HTML_NL2BR = 'cma_email_html_nl2br';
	const OPTION_POST_ADMIN_NOTIFICATION_EMAIL = 'cma_new_question_notification';
	const OPTION_NEW_QUESTION_EVERYBODY_FOLLOW_ENABLED = 'cma_new_question_all_users_follow_enabled';
	const OPTION_NEW_QUESTION_ADMIN_NOTIFICATION_ENABLED = 'cma_new_question_admin_notification_enabled';
	const OPTION_NEW_QUESTION_EXPERT_NOTIFICATION_ENABLED = 'cma_new_question_expert_notification_enabled';
	const OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED = 'cma_new_question_all_users_notification_enabled';
	const OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT = 'cma_new_question_all_users_notification_optinout';
	const OPTION_NEW_QUESTION_NOTIFICATION_TITLE = 'cma_new_question_notification_title';
	const OPTION_NEW_QUESTION_NOTIFICATION_CONTENT = 'cma_new_question_notification_content';
	const OPTION_THREAD_NOTIFICATION = 'cma_thread_notification';
	const OPTION_NEW_ANSWER_ADMIN_NOTIFICATION_ENABLED = 'cma_new_answer_admin_notification_enabled';
	const OPTION_NEW_ANSWER_NOTIFY_EVERYBODY_ENABLED = 'cma_new_answer_all_users_notification_enabled';
	const OPTION_THREAD_NOTIFICATION_TITLE = 'cma_thread_notification_title';
	const OPTION_ENABLE_THREAD_FOLLOWING = 'cma_enable_thread_following';
	const OPTION_ENABLE_CATEGORY_FOLLOWING = 'cma_enable_category_following';
	const OPTION_NEW_COMMENT_NOTIFICATION_ENABLED = 'cma_new_comment_nofication_enabled';
	const OPTION_NEW_COMMENT_ADMIN_NOTIFICATION_ENABLED = 'cma_new_comment_admin_nofication_enabled';
	const OPTION_NEW_COMMENT_NOTIFICATION_TITLE = 'cma_new_comment_notification_title';
	const OPTION_NEW_COMMENT_NOTIFICATION_CONTENT = 'cma_new_comment_notification_content';
	const OPTION_NOTIF_BEST_ANSWER_RECEIVERS = 'cma_notif_best_answer_receivers';
	const OPTION_NOTIF_BEST_ANSWER_TITLE = 'cma_notif_best_answer_title';
	const OPTION_NOTIF_BEST_ANSWER_CONTENT = 'cma_notif_best_answer_content';
	
	// Ads
	const OPTION_ADV_QUESTIONS_TABLE_BEFORE = 'cma_adv_questions_table_before';
	const OPTION_ADV_QUESTIONS_TABLE_AFTER = 'cma_adv_questions_table_after';
	const OPTION_ADV_THREAD_BEFORE = 'cma_adv_thread_before';
	const OPTION_ADV_THREAD_ANSWERS_BEFORE = 'cma_adv_thread_answers_before';
	const OPTION_ADV_THREAD_ANSWERS_AFTER = 'cma_adv_thread_answers_after';
	const OPTION_ADV_THREAD_ANSWERS_FORM_AFTER = 'cma_adv_thread_answers_form_after';
	const OPTION_ADV_CSS_PREFIX = 'cma_adv_css_prefix';
	
	// MicroPayments
	const OPTION_MP_POST_QUESTION_ACTION = 'CMA_MP_post_question_action';
	const OPTION_MP_POST_QUESTION_POINTS = 'CMA_MP_post_question_points';
	const OPTION_MP_POST_ANSWER_ACTION = 'CMA_MP_post_answer_action';
	const OPTION_MP_POST_ANSWER_POINTS = 'CMA_MP_post_answer_points';
	const OPTION_MP_REWARD_BEST_ANSWER_ENABLE = 'CMA_MP_reward_best_answer_enable';
	const OPTION_MP_REWARD_BEST_ANSWER_POINTS = 'CMA_MP_reward_best_answer_points';
	
	
	const ACCESS_EVERYONE = 0;
	const ACCESS_USERS = 1;
	const ACCESS_ROLE = 2;
	const ACCESS_AUTHOR = 3;
	const ACCESS_EXPERT = 4;
	
	const EDIT_MODE_DISALLOWED = 0;
	const EDIT_MODE_WITHIN_HOUR = 1;
	const EDIT_MODE_WITHIN_DAY = 2;
	const EDIT_MODE_ANYTIME = 3;
	
	const VOTES_MODE_ANSWERS_COUNT = 1;
	const VOTES_MODE_HIGHEST = 2;
	const VOTES_MODE_QUESTION_COUNT = 3;
	const VOTES_MODE_QUESTION_ANSWERS_COUNT = 4;
	const VOTES_MODE_QUESTION_RATING = 5;
	
	const NOTIF_ANSWER_AUTHOR = 'answer_author';
	const NOTIF_QUESTION_AUTHOR = 'question_author';
	const NOTIF_FOLLOWERS = 'followers';
	const NOTIF_CONTRIBUTORS = 'contributors';
	
	const CATEGORY_FILTER_MODE_TREE = 1;
    const CATEGORY_FILTER_MODE_TWO_LEVEL = 2;
    const CATEGORY_FILTER_MODE_DISABLED = 0;
	
    const DUPLICATED_QUESTIONS_ALLOW = 0;
    const DUPLICATED_QUESTIONS_SAME_AUTHOR = 1;
    const DUPLICATED_QUESTIONS_ANY_AUTHOR = 2;
    
    const DELETE_MODE_DISALLOWED = 0;
    const DELETE_MODE_WITHIN_HOUR = 1;
    const DELETE_MODE_WITHIN_DAY = 2;
    const DELETE_MODE_ANYTIME = 3;

	public static $categories = array(
		'general' => 'General',
		'appearance' => 'Appearance',
		'thread' => 'Thread',
		'labels' => 'Labels',
		'moderation' => 'Moderation',
		'access_control' => 'Access Control',
		'notifications' => 'Notifications',
		'disclaimer' => 'Disclaimer',
		'social_login' => 'Social Login',
		'sidebar' => 'Sidebar',
		'referrals' => 'Referrals',
		'ads' => 'Advertisements',
		'custom_css' => 'Custom CSS',
		'server_information' => 'Server Information',
	);
	
	public static $subcategories = array(
		'general' => array(
			'general' => 'General',
			'seo' => 'SEO',
			'other' => 'Other',
		),
		'thread' => array(
			'question' => 'Questions',
			'answer' => 'Answers',
			'comments' => 'Comments',
			'common' => 'Common',
			'attachments' => 'Attachments',
			'spam' => 'Spam reporting',
			'private_questions' => 'Private questions',
			'bad_words' => 'Bad words',
		),
		'appearance' => array(
			'index' => 'Index page',
			'table' => 'Table of questions',
			'rating' => 'Questions rating',
			'thread' => 'Thread page',
			'attachments' => 'Attachments',
			'editor' => 'Editor',
			'navigation' => 'Navigation',
			'login' => 'Login Widget',
			'misc' => 'Miscellaneous',
		),
		'moderation' => array(
			'moderation' => 'Moderation',
		),
		'access_control' => array(
			'access' => 'Access Control',
			'other' => 'Other',
		),
		'notifications' => array(
			'notifications' => 'Notifications',
			'question' => 'New questions',
			'answer' => 'New answers',
			'comment' => 'New comments',
			'best_answer' => 'Best answer',
			'other' => 'Other',
		),
		'ads' => array(
// 			'general' => 'General',
			'index' => 'Index page',
			'thread' => 'Thread page',
		),
	);
	
	
	public static function getOptionsConfig() {
		
		return apply_filters('cma_options_config', array(
			
			// General
			self::OPTION_ANSWERS_PERMALINK => array(
				'type' => self::TYPE_STRING,
				'default' => 'answers',
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Answers permalink',
				'desc' => 'You can replace default "answers" slug to whatever you want.',
			),
			self::OPTION_CATEGORIES_URL_PART => array(
				'type' => self::TYPE_STRING,
				'default' => 'categories',
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Categories URL part',
				'desc' => 'Replace the "categories" URL part in the category URL: /answers/<strong>categories</strong>/some-category',
			),
			self::OPTION_ANSWERS_AS_HOMEPAGE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Set as homepage',
				'desc' => 'If enabled, questions listing will be displayed as your site\'s homepage.',
			),
			self::OPTION_ADD_ANSWERS_MENU => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Add "Answers" to WP Nav Menu',
				'desc' => 'You need to have "wp_nav_menu" trigger in your template.',
			),
			self::OPTION_ANSWER_PAGE_DISABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Disable "Answers" page',
				'desc' => 'Disable the "Answers" page completely (it will show 404 instead).',
			),
			self::OPTION_CREATE_AJAX_PAGE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'general',
				'title' => 'Auto-create the AJAX page',
				'desc' => 'If enabled, the CMA AJAX page with the cma-index shortcode will be created automatically if not found. '
							. 'Disable this option to avoid creating this page.',
			),
			// SEO
			self::OPTION_SEO_META_REWRITE_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Enable the meta tags rewrite',
				'desc' => 'If enabled, CMA will add an extra meta-tags for the SEO purpose.<br />'
						. 'If disabled, then the following SEO features will be disabled also.<br />'
						. '<strong>Note:</strong> meta rewrite uses the output buffering and may cause conflicts with some themes or plugins. '
						. 'If you noticed any issues with the CMA pages displaying, try to turn this option off.',
			),
			self::OPTION_INDEX_META_TITLE => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Title bar for index page',
				'desc' => 'Enter text which appears on the browser\'s title bar on the Answers index page for better search engines positioning. '
							. 'If blank then default title will be used.',
			),
			self::OPTION_INDEX_META_DESC => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Meta description for index page',
				'desc' => 'Enter the meta description for the index page for better search engines positioning.',
			),
			self::OPTION_INDEX_META_KEYWORDS => array(
				'type' => self::TYPE_STRING,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Meta keywords',
				'desc' => 'Enter the meta keywords for better search engines positioning.',
			),
			self::OPTION_NOINDEX_NON_CANONICAL => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Add noindex meta to the non-canonical pages',
				'desc' => 'If enabled, the &lt;meta name="robots" content="noindex"&gt; tag will be added to the non-canonical page, '
							. 'for example the question list sorted by non-default parameter.',
			),
			self::OPTION_NOINDEX_CONTRIBUTOR => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'seo',
				'title' => 'Add noindex meta to the contributor page',
				'desc' => 'If enabled, the &lt;meta name="robots" content="noindex"&gt; tag will be added to the contributor page.',
			),
			
			// Other
			self::OPTION_LOGS_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'general',
				'subcategory' => 'other',
				'title' => 'Enable logs',
				'desc' => 'If enabled, the information about posting and voting will be additionaly logged to database.',
			),
			self::OPTION_GEOLOCIATION_API_KEY => array(
				'type' => self::TYPE_STRING,
				'default' => 'answers',
				'category' => 'general',
				'subcategory' => 'other',
				'title' => 'Geolocation API key',
				'desc' => 'Enter an ipinfodb.com IP Location API key to track the downloads locations in logs. '
							. 'If you don\'t have an API key, <a href="http://ipinfodb.com/register.php" target="_blank">register new account</a>.',
			),
			
			
			
			// Appearance Index
			self::OPTION_SHOW_TITLE_FOUND_POSTS => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Show number of questions next to category header\'s title',
				'desc' => 'If enabled, number of questions will be added to the category title in the header, for example: Answers (19).',
			),
			self::OPTION_INDEX_PAGE_TEMPLATE => array(
				'type' => self::TYPE_SELECT,
				'options' => array(__CLASS__, 'getPageTemplatesOptions'),
				'default' => 'page.php',
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Page template for index',
				'desc' => 'Choose the page template of the current theme or set default.',
			),
			self::OPTION_SHOW_INDEX_TITLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Show index page title',
				'desc' => 'This option works only if you are using default CMA page template for the index page.',
			),
			self::OPTION_ITEMS_PER_PAGE => array(
				'type' => self::TYPE_INT,
				'default' => 10,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Amount of Questions per page',
				'desc' => 'Limit of the questions per page.',
			),
			self::OPTION_INDEX_ORDER_BY => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					CMA_Thread::SORT_BY_NEWEST => 'Newest',
					CMA_Thread::SORT_BY_HOTTEST => 'Hottest',
					CMA_Thread::SORT_BY_VOTES => 'Votes',
					CMA_Thread::SORT_BY_VIEWS => 'Views',
				),
				'default' => CMA_Thread::SORT_BY_NEWEST,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Questions sorting',
				'desc' => 'Choose the default option how to sort the questions.',
			),
			self::OPTION_INDEX_HEADER_AFTER_TEXT => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Text below header',
				'desc' => 'You can enter text which will appear below the header of the questions index.',
			),
			self::OPTION_QUESTION_FORM_ON_TOP => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Question form on top',
				'desc' => 'Display the question form above the questions table (if enabled) or below (if disabled).',
			),
			self::OPTION_QUESTION_FORM_BUTTON => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'index',
				'title' => 'Question form on a separate page',
				'desc' => 'If enabled, the question form won\'t be displayed on the index page. Instead CMA will display a link to the question page. '
							. 'Question form will be displayed on the separate page.',
			),
			
			// Appearance Questions Table
			self::OPTION_COLUMN_VOTES_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show number of votes for a question',
				'desc' => 'If enabled, users will see rating column, based on chosen votes mode for questions.',
			),
			self::OPTION_VIEWS_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show number of views for a question',
				'desc' => 'If enabled, users will see number of views next to the question name (main Answers page only).',
			),
			self::OPTION_ANSWERS_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show number of answers for a question',
				'desc' => 'If enabled, users will see number of answers next to the question name (main Answers page only).',
			),
			self::OPTION_INDEX_NUMBERS_VERTICALLY => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Display numbers vertically',
				'desc' => 'If enabled, the votes, answers and views numbers will displayed vertically. If disabled - horizontally.',
			),
			self::OPTION_AUTHOR_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show information about the author of a question',
				'desc' => 'If enabled, users will see the information about the author of the question next to the question name (main Answers page only).',
			),
			self::OPTION_AUTHOR_LINK_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Author name as a link to his page',
				'desc' => 'If enabled, the username will be a link to the contributor\'s page.',
			),
			self::OPTION_UPDATED_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show information about the updates for a question',
				'desc' => 'If enabled, users will see the information about the last update (the author and time/date) next to the question name (main Answers page only).',
			),
			self::OPTION_TABLE_QUESTION_ID_SHOW => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Show question ID on questions table',
			),
			self::OPTION_STICKY_QUESTION_COLOR => array(
				'type' => self::TYPE_STRING,
				'default' => '#EEEEEE',
				'category' => 'appearance',
				'subcategory' => 'table',
				'title' => 'Sticky questions background color',
				'desc' => 'You can write color by name: white, red, lightblue. Or you can use html format #CCCCCC, #0000FF etc.',
			),
			
			// Appearance Votes
			self::OPTION_VOTES_MODE => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::VOTES_MODE_ANSWERS_COUNT => 'Total number of votes of its answers',
					self::VOTES_MODE_HIGHEST => 'Rating of the best answer',
					self::VOTES_MODE_QUESTION_COUNT => 'Total number of votes for the question',
					self::VOTES_MODE_QUESTION_ANSWERS_COUNT => 'Sum of votes for the question and its answers',
					self::VOTES_MODE_QUESTION_RATING => 'Rating of the question',
				),
				'default' => self::VOTES_MODE_ANSWERS_COUNT,
				'category' => 'appearance',
				'subcategory' => 'rating',
				'title' => 'On questions list in "votes" box show',
				'desc' => 'If ratings are disabled, this setting will be ignored.',
			),
			self::OPTION_VOTES_NO => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'rating',
				'title' => 'Replace "0" votes/answers to "no"',
				'desc' => 'If enabled, users will see "no" instead of 0.',
			),
			
			// Appearance Thread
			self::OPTION_THREAD_PAGE_TEMPLATE => array(
				'type' => self::TYPE_SELECT,
				'options' => array(__CLASS__, 'getPageTemplatesOptions'),
				'default' => 'page.php',
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Page template for thread',
				'desc' => 'Choose the page template of the current theme or set default.',
			),
			self::OPTION_SHOW_USER_STATS => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Show number of questions and answers next to username',
				'desc' => 'If enabled, number of questions and answers posted by user will be displayed on answers page next to username.',
			),
			self::OPTION_SHOW_GRAVATARS => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Show gravatar photos',
				'desc' => 'If this is enabled, photos from gravatar (http://www.gravatar.com/) will be shown next to user name and in his profile page.',
			),
			self::OPTION_SHOW_SOCIAL => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Show social share icons in question page',
				'desc' => 'If this is enabled, social media links (Facebook, Linkedin, G+, Twitter) will be shown in the question page.',
			),
			self::OPTION_SHOW_QUESTION_AUTHOR => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Show question author',
			),
			self::OPTION_SHOW_ANSWER_AUTHOR => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Show answer author',
			),
			self::OPTION_ANSWER_SORTING_BY => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					CMA_Answer::ORDER_BY_DATE => 'date',
					CMA_Answer::ORDER_BY_VOTES => 'votes',
				),
				'default' => CMA_Answer::ORDER_BY_DATE,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Sort answers by',
				'desc' => 'Default: by date.',
			),
			self::OPTION_ANSWER_SORTING_DESC => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					0 => 'Ascending order (oldest or lowest rate first)',
					1 => 'Descending order (newest or highest rate first)',
				),
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Sort answers in',
				'desc' => 'Specify how answers to a question should be sorted (default: descending).',
			),
			self::OPTION_SHORTCODES_WHITELIST => array(
				'type' => self::TYPE_CSV_LINE,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Shortcodes whitelist',
				'desc' => 'Enter the shortcodes comma-separated list (only names, without brackets) which can be used by all users in their questions and answers. '
						. 'Other shortcodes won\'t be executed.',
			),
			
			// Appearance attachments
			self::OPTION_EMBED_ATTACHED_IMAGES => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'attachments',
				'title' => 'Embed attached images',
				'desc' => 'If enabled, the images attached with the question/answer will be embeded below the content.',
			),
			self::OPTION_EMBED_ATTACHED_IMAGES_SIZE => array(
				'type' => self::TYPE_INT,
				'default' => 200,
				'category' => 'appearance',
				'subcategory' => 'attachments',
				'title' => 'Attachment image max size',
				'desc' => 'Max width or height of the attached image in pixels.',
			),
			
			// Appearance Editor
			self::OPTION_RICHTEXT_EDITOR => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Show richtext editor for question/answer form',
				'desc' => 'If enabled, the question/answer textarea will be replaced with RichText editor (TinyMCE).',
			),
			self::OPTION_EDITOR_ROWS => array(
				'type' => self::TYPE_INT,
				'default' => 3,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Editor rows number',
			),
			self::OPTION_MARKUP_BOX => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Show markup box near question/answer form',
				'desc' => 'If enabled, the box showing the markup and codewrap possibility will appear.',
			),
			self::OPTION_JS_LIMIT => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Enable JS limit',
				'desc' => 'If enabled, below input fields will be displayed an information about entered text length and the characters left.',
			),
			self::OPTION_JS_LIMIT_QUESTION_TITLE => array(
				'type' => self::TYPE_INT,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Limit for question title',
				'desc' => 'Enter characters limit for a question title. Enter 0 to disable.',
			),
			self::OPTION_JS_LIMIT_QUESTION_DESCRIPTION => array(
				'type' => self::TYPE_INT,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Limit for question description',
				'desc' => 'Enter characters limit for a question title. Enter 0 to disable. Works only if rich text editor is disabled.',
			),
			self::OPTION_JS_LIMIT_QUESTION_COMMENT => array(
				'type' => self::TYPE_INT,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Limit for question comment',
				'desc' => 'Enter characters limit for a comment to question. Enter 0 to disable.',
			),
			self::OPTION_JS_LIMIT_ANSWER_COMMENT => array(
				'type' => self::TYPE_INT,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Limit for answer comment',
				'desc' => 'Enter characters limit for a comment to answer. Enter 0 to disable.',
			),
			self::OPTION_NOTIFY_CHECKBOX_CHECKED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Notification option checked by default',
				'desc' => 'Make the "Notify me of follow" option on the question/answer form to be checked by default.',
			),
// 			self::OPTION_HIDE_REQUIRED_CATEGORY_IF_ONE => array(
// 				'type' => self::TYPE_BOOL,
// 				'default' => 1,
// 				'category' => 'appearance',
// 				'subcategory' => 'editor',
// 				'title' => 'Hide required categories select-box if only one exists',
// 				'desc' => 'If enabled, the categories select-box will be hidden if category is required to post a question and only one category exists.',
// 			),
			self::OPTION_SUBMIT_BUTTON_CSS_BACKGROUND => array(
				'type' => self::TYPE_STRING,
				'default' => '',
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Submit button background color',
				'desc' => 'Set the custom background for the submit button or leave empty to use default.<br />Example: #66cc66',
			),
			self::OPTION_ESCAPE_PRE_CONTENT => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Escape the &lt;pre&gt; tag content',
				'desc' => 'If enabled, HTML code inside the &lt;pre&gt; tag will be escaped. If disabled, HTML code inside won\'t be escaped.',
			),
			self::OPTION_CONTENT_ALLOWED_TAGS => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "a href title, pre class, p, del, ins, code, blockquote, em, strong, b, br, ul, ol, li, h1, h2, h3, h4, h5, h6, hr",
				'category' => 'appearance',
				'subcategory' => 'editor',
				'title' => 'Allowed HTML tags and attributes',
				'desc' => 'Enter list of the allowed tags and it\'s attributes. Other tags will be escaped from the question or answer content.<br />'
						. 'Separate tags with commas or new lines. '
						. 'Allowed tag\'s attributes type next to the tag, separated by spaces.<br /><br />'
						. 'Example:<pre>a href title, img src, p style</pre>',
			),
			
			// Appearance Navigation
			self::OPTION_SHOW_QUESTION_PAGE_NAV_BAR => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Show navigation bar on the question page',
				'desc' => 'If disabled, the navigation bar will be not displayed on the single question page.',
			),
			self::OPTION_CATEGORY_FILTER_MODE => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::CATEGORY_FILTER_MODE_TREE => 'Single select box with categories tree',
					self::CATEGORY_FILTER_MODE_TWO_LEVEL => 'Two-level filter',
					self::CATEGORY_FILTER_MODE_DISABLED => 'Disabled',
				),
				'default' => self::CATEGORY_FILTER_MODE_TREE,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Category filter mode',
				'desc' => 'Category filter which displays on the thread list.',
			),
			self::OPTION_BREADCRUMBS_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Show breadcrumbs',
				'desc' => 'If enabled, the breadcrumbs will be added above the title on the index page and the thread page.',
			),
			self::OPTION_ALLOW_POST_ONLY_SUBCATEGORIES => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Allow to post questions only in subcategories when the two-level categories filter enabled',
				'desc' => 'Works only when two-level categories filter is set.<br />' .
							'If disabled, users will be able to post questions to any category.<br />' .
							'If enabled, users will be ale to post questions only in the subcategories.',
			),
			self::OPTION_BACKLINK_PARAM_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Add a backlink parameter to URL',
				'desc' => 'If enabled, the backlink parameter will be added to the thread\'s permalink to create backlinks. '
							. 'If disbled, plugin will use HTTP referer instead.',
			),
			self::OPTION_NAVBAR_AUTO_SUBMIT => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'navigation',
				'title' => 'Apply navbar filters instantly',
				'desc' => 'If enabled, changing the navigation bar select-box filters will be automatically applied.',
			),
			
			// Appearance Login Widget
			self::OPTION_SHOW_LOGIN_WIDGET => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Show Login Widget',
				'desc' => 'If disabled, users will not see the login widget with the login form and social login links on the CM Answers pages at all.',
			),
			self::OPTION_SHOW_LOGIN_FORM => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Show login form',
				'desc' => 'If disabled, users will not see WP login form on the Login Widget, but they will see Social Login buttons, if defined.',
			),
			self::OPTION_LOGIN_PAGE_LINK_TITLE => array(
				'type' => self::TYPE_STRING,
				'default' => 'Go to WP login page',
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Login page link title',
				'desc' => 'The title of the link which leads to the WP login page if the user is not logged in. Leave blank to disable.',
			),
			self::OPTION_LOGIN_PAGE_LINK_URL => array(
				'type' => self::TYPE_STRING,
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Login page link URL',
				'desc' => 'Enter the custom login page URL if using non-default. Leave blank to use default WP login page.',
			),
			self::OPTION_SHOW_REGISTRATION_LINK => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Show registration link',
				'desc' => 'If enabled, the registration link will be added to the login form. Link will be visible only if WP allows guests registration.',
			),
			self::OPTION_REGISTRATION_URL => array(
				'type' => self::TYPE_STRING,
				'category' => 'appearance',
				'subcategory' => 'login',
				'title' => 'Registration link URL',
				'desc' => 'Enter the custom registration page URL if using non-default. Leave blank to use default WP registration page.',
			),
			
			// Appearance Misc
			self::OPTION_REPLACE_POST_COMMENTS_SHORTCODE_ATTR => array(
				'type' => self::TYPE_STRING,
				'default' => 'limit=100 pagination=0 search=0',
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Attributes for shortcode replacing default comments',
				'desc' => 'If you replaced the default comments on a post or page, instead of default comments, '
						. 'the questions from chosen category will be displayed using this shortcode attributes. '
						. 'You can use the same attributes like in the cma-questions shortcode.',
			),
			self::OPTION_BP_GROUP_SHORTCODE_ATTR => array(
				'type' => self::TYPE_STRING,
				'default' => 'formontop=0 limit=10 sort=hottest form=1 navbar=0',
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Shortcode attributes for BuddyPress Group',
				'desc' => 'If you assigned some BP Group with a CMA category you can adjust the shortcode which is used to display the CMA questions\' list.',
			),
			self::OPTION_CONTRIBUTOR_CUSTOM_URL => array(
				'type' => self::TYPE_STRING,
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'User\'s profile custom URL',
				'desc' => 'Enter the custom URL of the user\'s profile page, for example if you are using BuddyPress.<br />'
							. 'Leave blank to use default CMA contributor\'s page.<br /><br />'
							. '%s - user\'s nicename<br />%d - user\'s ID<br />Example: /user/profile/%s (will give /user/profile/admin).',
			),
			self::OPTION_SUPPORT_THEME_DIR => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Support template overriding',
				'desc' => 'If enabled, the templates will be overriden by using files in the "CMA" directory created in your theme folder.',
			),
			self::OPTION_DISABLE_CSS => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Disable app.css',
				'desc' => 'You can disable attaching the app.css file. The custom CSS will be still attached.',
			),
			self::OPTION_HIDE_CATEGORIES => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Hide categories',
				'desc' => 'If enabled, the categories won\'t be displayed in the questions table.',
			),
			self::OPTION_RELATED_QUESTIONS_POST_TYPES => array(
				'type' => self::TYPE_MULTICHECKBOX,
				'options' => array(__CLASS__, 'getPostTypesOptions'),
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Show related questions below chosen post types',
				'desc' => 'If enabled, the related questions widget will be automatically added below each post for the chosen post types. '
							.'You can override this settings on the specific post edit page.',
			),
			self::OPTION_RELATED_QUESTIONS_SHORTCODE_PARAMS => array(
				'type' => self::TYPE_STRING,
				'default' => 'limit=5',
				'category' => 'appearance',
				'subcategory' => 'misc',
				'title' => 'Post\'s related questions shortcode params',
				'desc' => 'Define the params of related questions\' shortcode to show below the post.',
			),
			
			
			// Thread question
			self::OPTION_QUESTION_DESCRIPTION_OPTIONAL => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Allow questions with no description',
				'desc' => 'If enabled, users will be able to post questions without description.',
			),
			self::OPTION_QUESTION_REQUIRE_CATEGORY => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Require category',
				'desc' => 'If enabled, users won\'t be able to post question without choosing a category.',
			),
			self::OPTION_TAGS_SWITCH => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Enable tags in questions',
				'desc' => 'If enabled, tags field will appear when submitting a question.',
			),
			self::OPTION_ENABLE_MARK_FAVORITE_QUESTIONS => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Allow to mark question as favorite',
			),
			self::OPTION_ENABLE_QUESTION_RATING => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Enable ratings for questions',
				'desc' => 'If enabled, users will be able to rate questions.',
			),
			self::OPTION_RESOLVE_THREAD_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Allow author to resolve thread',
				'desc' => 'If this is enabled, author can mark thread as resolved. You can disable posting answers in the resolved thread.',
			),
			self::OPTION_SHOW_ONLY_OWN_QUESTIONS => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Show only users own questions',
				'desc' => 'If enabled, user will be able to display only questions that he posted.',
			),
			self::OPTION_DUPLICATED_QUESTIONS_MODE => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::DUPLICATED_QUESTIONS_ALLOW => 'Allow',
					self::DUPLICATED_QUESTIONS_SAME_AUTHOR => 'Disallow when the author asked a question with the same title before',
					self::DUPLICATED_QUESTIONS_ANY_AUTHOR => 'Disallow when any author asked a question with the same title before',
				),
				'default' => self::DUPLICATED_QUESTIONS_ALLOW,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Duplicated questions',
				'desc' => 'Decide how to support the duplicated questions.',
			),
			self::OPTION_ALLOW_QUESTION_DELETE => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::DELETE_MODE_DISALLOWED => 'Disallowed',
					self::DELETE_MODE_WITHIN_HOUR => 'Within one hour',
					self::DELETE_MODE_WITHIN_DAY => 'Within one day',
					self::DELETE_MODE_ANYTIME => 'Anytime',
				),
				'default' => self::DELETE_MODE_DISALLOWED,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Allow author to delete his question',
				'desc' => 'Decide whether the question author can delete his question.',
			),
			self::OPTION_ALLOW_QUESTION_DELETE_NOANSWERS => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Delete question allowed only if no answers posted',
				'desc' => 'If author is allowed to delete his questions, you can limit this for only those questions which have no answers.',
			),
			self::OPTION_QUESTION_DELETE_TRASH => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Put questions to trash instead deleting',
				'desc' => 'If enabled, admin will be allowed to browse the Trash to figure out which questions has been deleted and restore them or empty the Trash.',
			),
			self::OPTION_USER_RELATED_QUESTIONS_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'question',
				'title' => 'Allow to set related questions by user',
				'desc' => 'If enabled, user will be able to select related questions which will be displayed on the thread page.',
			),
			self::OPTION_THREAD_DISPLAY_ID => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'appearance',
				'subcategory' => 'thread',
				'title' => 'Display question ID',
				'desc' => 'If enabled, thread page will display question ID in order to refer in another question (see related question option).',
			),
			
			// Thread Answers
			self::OPTION_ANSWER_AFTER_RESOLVED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Allow answers after the question has been resolved',
				'desc' => 'If enabled, users will be able to post answers even after the question was resolved.',
			),
			self::OPTION_ANSWERS_RATING_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Enable ratings for answers',
				'desc' => 'If enabled, users will be able to rate answers and will see the number of answers next to the question name (main Answers page only).',
			),
			self::OPTION_ENABLED_MARK_BEST_ANSWER => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Enable marking as the best answer',
				'desc' => 'If enabled, author will be able to mark the best answer for his question which will be highlighted.',
			),
			self::OPTION_ALLOW_OWN_BEST_ANSWER => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Allow marking own answer as best answer',
				'desc' => 'If enabled, question author will be able to mark own answer as the best answer.',
			),
			self::OPTION_ENABLED_UNMARK_BEST_ANSWER => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Enable unmarking the best answer',
				'desc' => 'If enabled, author will be able to unmark the best answer.',
			),
			self::OPTION_BEST_ANSWER_REMOVE_OTHER => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Allow to remove non-best answers',
				'desc' => 'If enabled, when marking the best answer, user will be aksed wheter to remove other non-best answers from the thread or not.',
			),
			self::OPTION_PRIVATE_ANSWERS_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Allow private answers',
				'desc' => 'If enabled, user will be allowed to post answers which can be viewed only by the author of the question.',
			),
			self::OPTION_PRIVATE_ANSWER_BGCOLOR => array(
				'type' => self::TYPE_STRING,
				'default' => '#f3fff3',
				'category' => 'thread',
				'subcategory' => 'answer',
				'title' => 'Private answer background color',
				'desc' => 'CSS color for the .cma-answer-private selector.',
			),
			
			// Thread Spam
			self::OPTION_SPAM_REPORTING_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'spam',
				'title' => 'Enable spam reporting for questions/answers',
			),
			self::OPTION_SPAM_REPORTING_GUESTS => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'spam',
				'title' => 'Allow guests to report a spam',
				'desc' => 'If set to Yes then non-logged users also will can send a spam report.',
			),
			self::OPTION_SPAM_REPORTING_EMAIL_ADDR => array(
				'type' => self::TYPE_STRING,
				'default' => '',
				'category' => 'thread',
				'subcategory' => 'spam',
				'title' => 'Send spam report to an email address',
				'desc' => 'Multiple addresses separate by comma.',
			),
			self::OPTION_SPAM_REPORTING_EMAIL_SUBJECT => array(
				'type' => self::TYPE_STRING,
				'default' => "[[blogname]] Spam report in thread: [title]",
				'category' => 'thread',
				'subcategory' => 'spam',
				'title' => 'Spam report email subject',
				'desc' => 'You can use the following tokens: [blogname], [url], [title], [author], [content], [user], [datetime], [trash], [spam].',
			),
			self::OPTION_SPAM_REPORTING_EMAIL_TEMPLATE => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "Hi, user [user] has reported a spam.\n\nThread: [title]\nURL: [url]\nAuthor: [author]\nContent:\n[content]\n\n"
							."Trash it: [trash]\nSpam: [spam]",
				'category' => 'thread',
				'subcategory' => 'spam',
				'title' => 'Spam report email template',
				'desc' => 'You can use the following tokens: [blogname], [url], [title], [author], [content], [user], [datetime], [trash], [spam].',
			),
				
			self::OPTION_COMMENTS_QUESTION_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'comments',
				'title' => 'Enable the question comments',
				'desc' => 'If enabled, users except normal answers will be able to post comments to the question.',
			),
			self::OPTION_COMMENTS_ANSWER_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'comments',
				'title' => 'Enable the answer comments',
				'desc' => 'If enabled, users will be able to post comments to the answers.',
			),
			
			// Thread Common
			self::OPTION_INCREMENT_VIEWS => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Increment number of views every time the page is refreshed',
				'desc' => 'If enabled, views counter will be increased every time the page is refreshed. If disabled, cookie will be set to block user '
						. 'from increasing the counter on current machine.',
			),
			self::OPTION_NEGATIVE_RATING_ALLOWED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Enable negative ratings',
				'desc' => 'If enabled, users will be able to give "thumbs down" for an answer. If previous settings is disabled, this will be ignored.',
			),
			self::OPTION_EDIT_MODE => array(
				'type' => self::TYPE_RADIO,
				'options' => array(
					self::EDIT_MODE_DISALLOWED => 'Disallowed',
					self::EDIT_MODE_WITHIN_HOUR => 'Within one hour',
					self::EDIT_MODE_WITHIN_DAY => 'Within one day',
					self::EDIT_MODE_ANYTIME => 'Anytime',
				),
				'default' => self::EDIT_MODE_DISALLOWED,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Editing question or answer by its author',
				'desc' => 'Choose the mode of editing questions and answers.',
			),
			self::OPTION_CAN_EDIT_RESOLVED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Allow to edit resolved questions or its answers',
				'desc' => 'If this is enabled, user can edit resolved questions and answers of a resolved question.',
			),
// 			self::OPTION_ALLOW_UNVOTE => array(
// 				'type' => self::TYPE_BOOL,
// 				'default' => 0,
// 				'category' => 'thread',
// 				'subcategory' => 'common',
// 				'title' => 'Allow to unvote',
// 				'desc' => 'If enabled, user can undone his rating.',
// 			),
			self::OPTION_CAN_VOTE_MYSELF => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Allow user to vote for content posted by himself',
				'desc' => 'If enabled, user can vote for a question or answer posted by himself.',
			),
			self::OPTION_ALLOW_GUESTS_VOTING => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'common',
				'title' => 'Allow guests voting',
				'desc' => 'If enabled, not logged-in users will be able to vote for questions and answers. The voting will be limited for single IP address.',
			),
			
			// Attachments
			self::OPTION_ATTACHMENTS_QUESTIONS_ALLOW => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'attachments',
				'title' => 'Allow attachments in questions',
				'desc' => 'If enabled, user will be able to upload files which will be attached to the posted question.',
			),
			self::OPTION_ATTACHMENTS_ANSWERS_ALLOW => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'attachments',
				'title' => 'Allow attachments in answer',
				'desc' => 'If enabled, user will be able to upload files which will be attached to the posted answer.',
			),
			self::OPTION_ATTACHMENTS_FILE_EXTENSIONS => array(
				'type' => self::TYPE_CSV_LINE,
				'category' => 'thread',
				'subcategory' => 'attachments',
				'title' => 'Allowed attachment file types',
				'desc' => 'Comma-separated list of allowed attachment file types. Leave empty to disable. (example: zip,doc,docx).',
			),
			self::OPTION_ATTACHMENTS_MAX_SIZE => array(
				'type' => self::TYPE_INT,
				'default' => 1024*1024,
				'category' => 'thread',
				'subcategory' => 'attachments',
				'title' => 'Allowed attachment max size (in bytes)',
				'desc' => 'Max size in bytes of attachment. Cannot exceed server max, which is: '. ini_get('upload_max_filesize')
							. ' where 1M equals to 1,048,576 bytes.',
			),
			
			// Thread Private Questions
			self::OPTION_PRIVATE_QUESTIONS_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'private_questions',
				'title' => 'Enable private questions',
				'desc' => 'If enabled, each user will be able to send private question to the other user. Question will be send by email, '
						. 'using recipient\'s email address from Wordpress and put sender\'s email in the Reply-To header, '
						. 'so recipient will be able to reply directly to the sender\'s email address, bypassing the Wordpress.',
			),
			self::OPTION_PRIVATE_QUESTION_EMAIL_SUBJECT => array(
				'type' => self::TYPE_STRING,
				'default' => '[[blogname]] Private question: [title]',
				'category' => 'thread',
				'subcategory' => 'private_questions',
				'title' => 'Email subject',
				'desc' => 'Private question will be send by email with this subject.',
			),
			self::OPTION_PRIVATE_QUESTION_EMAIL_TEMPLATE => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "User [username] has asked a private question to you by website [blogname].\n-----------------------------------\n[question]"
							. "\n-----------------------------------\nIf you want to reply, simply reply to this email.",
				'category' => 'thread',
				'subcategory' => 'private_questions',
				'title' => 'Email template',
				'desc' => 'Private question will be send by email using this template.<br />You can use following tokens: [blogname], [username], [title], [question].',
			),
			
			// Bad words
			self::OPTION_BAD_WORDS_FILTER_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'thread',
				'subcategory' => 'bad_words',
				'title' => 'Enable bad words filter',
				'desc' => 'If enabled, the question, answer or comment which includes a bad word won\'t be accepted.',
			),
			self::OPTION_BAD_WORDS_LIST => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => '',
				'category' => 'thread',
				'subcategory' => 'bad_words',
				'title' => 'Bad words list',
				'desc' => 'Separate each bad word by new line. You can use regular expressons (compatible with '
						. '<a href="http://php.net/manual/en/reference.pcre.pattern.syntax.php" target="_blank" title="PHP: PCRE syntax">preg_match()</a> in PHP) '
						. 'by wrapping the expression by slashes, for example: <kbd>/(bad )?words?/</kbd>.',
			),
			
			// Moderation
			self::OPTION_QUESTION_AUTO_APPROVE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'title' => 'Auto-approve new questions',
				'desc' => 'This option overrides following options. Turning this option ON - turnes OFF the question moderation.',
			),
			self::OPTION_SIMULATE_COMMENT => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'title' => 'Simulate comment add when question is added',
				'desc' => 'Try to add questions as if they were comments. Tip: Triggers external spam filtering functions for comments (e.g. Akismet).',
			),
			self::OPTION_ANSWER_AUTO_APPROVE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'title' => 'Auto-approve answers',
			),
			self::OPTION_COMMENTS_AUTO_APPROVE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'title' => 'Auto-approve new comments',
				'desc' => 'If enabled, comments will appear in the thread without moderation. In disabled, admin must approve every comment before it will be visible.',
			),
			self::OPTION_AUTO_APPROVE_AUTHORS => array(
				'type' => self::TYPE_USERS_LIST,
				'default' => array(1),
				'category' => 'moderation',
				'subcategory' => 'moderation',
				'title' => 'Auto-approve content from following users',
			),
			
			// Access
			self::OPTION_VIEW_ACCESS => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					self::ACCESS_EVERYONE => 'Everyone',
					self::ACCESS_USERS => 'Logged in users',
					self::ACCESS_ROLE => 'By role',
				),
				'default' => self::ACCESS_EVERYONE,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Who can view questions',
				'desc' => 'Choose who can view questions. Users which have no access won\'t be able to see the question index at all.',
			),
			self::OPTION_VIEW_ACCESS_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Which roles can view questions',
				'desc' => 'Works only if chosen "By role" access.',
			),
			self::OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Show unanswered questions only to experts',
				'desc' => 'You can restrict visiblity of the unanswered questions and possibility to post answer only for experts assigned to the question\'s category. '
							. 'Question author also will be albe to display his question. '
							. 'After the first answer will be posted, the question will be visible including previous question visibility options.',
			),
			self::OPTION_ACCESS_VIEW_ANSWERS => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					self::ACCESS_EVERYONE => 'Everyone',
					self::ACCESS_USERS => 'Logged in users',
					self::ACCESS_ROLE => 'By role',
					self::ACCESS_AUTHOR => 'Asker + displaying own answers',
				),
				'default' => self::ACCESS_EVERYONE,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Who can see answers',
				'desc' => 'Choose who can see the answers on the question thread page.<br />Question author will see all answers always.<br />'
							. 'Answer author will see own answers always.',
			),
			self::OPTION_ACCESS_VIEW_ANSWERS_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Which roles can see answers',
				'desc' => 'Works only if chosen "By role" answers view access.',
			),
			self::OPTION_POST_QUESTIONS_ACCESS => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					self::ACCESS_USERS => 'Logged in users',
					self::ACCESS_ROLE => 'By role',
				),
				'default' => self::ACCESS_USERS,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Who can post questions',
			),
			self::OPTION_POST_QUESTIONS_ACCESS_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Which roles can post questions',
				'desc' => 'Works only if chosen "By role" access.',
			),
			self::OPTION_POST_ANSWERS_ACCESS => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					self::ACCESS_USERS => 'Logged in users',
					self::ACCESS_EXPERT => 'Experts',
					self::ACCESS_ROLE => 'By role',
				),
				'default' => self::ACCESS_USERS,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Who can post answers',
				'desc' => 'If you choose "Experts" and the specified thread has no experts related to his category, '
							. 'then it will work as the "Logged in users" option.',
			),
			self::OPTION_POST_ANSWERS_ACCESS_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Which roles can post answers',
				'desc' => 'Works only if chosen "By role" access.',
			),
			self::OPTION_POST_COMMENTS_ACCESS => array(
				'type' => self::TYPE_SELECT,
				'options' => array(
					self::ACCESS_USERS => 'Logged in users',
					self::ACCESS_ROLE => 'By role',
				),
				'default' => self::ACCESS_USERS,
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Who can post comments',
			),
			self::OPTION_POST_COMMENTS_ACCESS_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'access',
				'title' => 'Which roles can post comments',
				'desc' => 'Works only if chosen "By role" access.',
			),
			self::OPTION_ALLOW_FULL_HTML_ROLES => array(
				'type' => self::TYPE_MULTISELECT,
				'options' => array(__CLASS__, 'getRolesOptions'),
				'category' => 'access_control',
				'subcategory' => 'other',
				'title' => 'Which roles can post content with full HTML support',
				'desc' => 'Other roles\' posts will be filtered from HTML tags containing URLs, scripts and unsupported tags.',
			),
			
			// Notifications
			self::OPTION_POST_ADMIN_NOTIFICATION_EMAIL => array(
				'type' => self::TYPE_CSV_LINE,
				'default' => '',
				'category' => 'notifications',
				'subcategory' => 'notifications',
				'title' => 'Email address for the admin notifications',
				'desc' => 'Separate e-mail addresses by commas or leave empty to disable.',
			),
			self::OPTION_EMAIL_USE_HTML => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'notifications',
				'title' => 'Use HTML content type',
				'desc' => 'If enabled, the email will be send wil the Content-type: text/html',
			),
			self::OPTION_EMAIL_HTML_NL2BR => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'notifications',
				'subcategory' => 'notifications',
				'title' => 'Replace new lines with the &lt;br&gt; tags',
				'desc' => 'If using HTML emails, new lines in the email template will be replaced with the &lt;br&gt; tag as in the Wordpress posts.',
			),
			self::OPTION_NEW_QUESTION_EVERYBODY_FOLLOW_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'notifications',
				'title' => 'Add all users as new thread followers',
				'desc' => 'When posting new question, mark all users as thread followers. '
						. 'Until user won\'t unfollow the thread he will receive notifications about new answers.',
			),
			self::OPTION_NEW_QUESTION_ADMIN_NOTIFICATION_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Enable notification for admin',
				'desc' => 'If enabled, the notification about new question will be send to the email addresses defined above.',
			),
			self::OPTION_NEW_QUESTION_EXPERT_NOTIFICATION_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Enable notification for experts',
				'desc' => 'If enabled, experts will be notified about new questions in their categories. You can define experts per each category.',
			),
			self::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Notify all users',
				'desc' => 'Send notification email to all users after a new question will be posted.<br />'
						. '<strong>Warning: all Wordpress users will receive an email.</strong>',
			),
			self::OPTION_NEW_QUESTION_NOTIFY_EVERYBODY_OPTINOUT => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Allow users to opt-in and opt-out',
				'desc' => 'User will be able to opt-in the new questions newsletter by clicking the button visible on the CMA pages. '
						. 'The opt-out link will be added to the footer of the email message.',
			),
			self::OPTION_NEW_QUESTION_NOTIFICATION_TITLE => array(
				'type' => self::TYPE_STRING,
				'default' => "[[blogname]] A new question has been asked by [author]",
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Subject of the notification email',
				'desc' => 'Category subscribers and admin will receive a notification email with this subject.',
			),
			self::OPTION_NEW_QUESTION_NOTIFICATION_CONTENT => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "Hi, a new question has been asked by [author]:\nTitle: [question_title]\n\n"
					. "Click to see: [question_link]",
				'category' => 'notifications',
				'subcategory' => 'question',
				'title' => 'Content of the notification email',
				'desc' => "Category subscribers and admin will receive a notification email with this content.<br /><br />"
							. "[blogname] - Name of the site<br />"
							. "[author] - Author of the question<br />"
							. "[ip] - Author IP address<br />"
							. "[question_title] - Title of the question<br />"
							. "[question_body] - Body of the question<br />"
							. "[question_status] - Approval status of the question (pending, approved)<br />"
							. "[question_link] - URL address of the new question<br />"
							. "[opt_out_url] - URL address of the opt-out page (if enabled)",
			),
			self::OPTION_NEW_ANSWER_ADMIN_NOTIFICATION_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'answer',
				'title' => 'Enable notification for admin',
			),
			self::OPTION_NEW_ANSWER_NOTIFY_EVERYBODY_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'answer',
				'title' => 'Notify all users',
				'desc' => 'Send notification email to all users after a new answer will be posted.<br />'
						. '<strong>Warning: all Wordpress users will receive an email.</strong>',
			),
			self::OPTION_THREAD_NOTIFICATION_TITLE => array(
				'type' => self::TYPE_STRING,
				'default' => "[[blogname]] Someone has posted a new answer on the topic you subscribed to",
				'category' => 'notifications',
				'subcategory' => 'answer',
				'title' => 'Subject of the notification email',
				'desc' => 'Subscribers and admin will receive a notification email with this subject.<br />'
							. '(Subscribers are users who have checked the "Notify me of follow" option or following the question thread or whole category.)',
			),
			self::OPTION_THREAD_NOTIFICATION => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "Someone has posted a new answer on the topic you subscribed to\n\nTopic: [question_title]\nClick to see: [comment_link]",
				'category' => 'notifications',
				'subcategory' => 'answer',
				'title' => 'Content of the notification email',
				'desc' => "Subscribers and admin will receive a notification email with this content.<br /><br />"
							. "You can use the following shortcodes:<br />"
							. "[blogname] - Name of the site<br />"
							. "[question_title] - Title of the question<br />"
							. "[question_body] - Body of the question<br />"
							. "[comment_link] - Link to the new answer<br />"
							. "[author] - Author name<br />"
							. "[ip] - Author IP address<br />"
							. "[answer] - Answer body<br />"
							. "[opt_out_url] - URL address of the opt-out page",
			),
			self::OPTION_NEW_COMMENT_NOTIFICATION_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'comment',
				'title' => 'Enable notification for subscribers',
				'desc' => 'Subscribers are users who have checked the "Notify me of follow" option or following the question thread/whole category.',
			),
			self::OPTION_NEW_COMMENT_ADMIN_NOTIFICATION_ENABLED => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'comment',
				'title' => 'Enable notification for admin',
			),
			self::OPTION_NEW_COMMENT_NOTIFICATION_TITLE => array(
				'type' => self::TYPE_STRING,
				'default' => "[[blogname]] Someone has posted a new comment on the topic you subscribed to",
				'category' => 'notifications',
				'subcategory' => 'comment',
				'title' => 'Subject of the notification email',
				'desc' => 'Subscribers and admin will receive a notification email with this subject.',
			),
			self::OPTION_NEW_COMMENT_NOTIFICATION_CONTENT => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "Someone has posted a new comment on the topic you subscribed to\n\nTopic: [question_title]\nClick to see: [comment_link]",
				'category' => 'notifications',
				'subcategory' => 'comment',
				'title' => 'Content of the notification email',
				'desc' => "Subscribers and admin will receive a notification email with this content.<br /><br />"
							. "You can use the following shortcodes:<br />"
							. "[blogname] - Name of the site<br />"
							. "[question_title] - Title of the question<br />"
							. "[question_body] - Body of the question<br />"
							. "[comment_link] - Link to the new comment<br />"
							. "[author] - Author name<br />"
							. "[comment] - Comment body<br />"
							. "[opt_out_url] - URL address of the opt-out page",
			),
			self::OPTION_NOTIF_BEST_ANSWER_RECEIVERS => array(
				'type' => self::TYPE_MULTICHECKBOX,
				'options' => array(
					self::NOTIF_ANSWER_AUTHOR => 'Answer\'s author',
					self::NOTIF_QUESTION_AUTHOR => 'Question\'s author',
					self::NOTIF_FOLLOWERS => 'Thread followers',
					self::NOTIF_CONTRIBUTORS => 'All thread contributors',
				),
				'category' => 'notifications',
				'subcategory' => 'best_answer',
				'title' => 'Best answer notification receivers',
			),
			self::OPTION_NOTIF_BEST_ANSWER_TITLE => array(
				'type' => self::TYPE_STRING,
				'default' => "[[blogname]] Best answer in: [question_title]",
				'category' => 'notifications',
				'subcategory' => 'best_answer',
				'title' => 'Subject of the notification email',
				'desc' => 'Users will receive a notification email with this subject.',
			),
			self::OPTION_NOTIF_BEST_ANSWER_CONTENT => array(
				'type' => self::TYPE_TEXTAREA,
				'default' => "The best answer has been chosen.\n\nTopic: [question_title]\nLink: [answer_link]\n\nAnswer by [answer_author]:\n[answer]",
				'category' => 'notifications',
				'subcategory' => 'best_answer',
				'title' => 'Content of the notification email',
				'desc' => "Subscribers and admin will receive a notification email with this content.<br /><br />"
				. "You can use the following shortcodes:<br />"
				. "[blogname] - Name of the site<br />"
				. "[question_title] - Title of the question<br />"
				. "[question_body] - Body of the question<br />"
				. "[question_author] - Answer author name<br />"
				. "[answer_link] - Link to the new comment<br />"
				. "[answer_author] - Answer author name<br />"
				. "[answer] - Answer content<br />",
			),
			
			self::OPTION_ENABLE_THREAD_FOLLOWING => array(
				'type' => self::TYPE_BOOL,
				'default' => 1,
				'category' => 'notifications',
				'subcategory' => 'other',
				'title' => 'Enable thread following',
				'desc' => 'If disabled, the "Notify me of follow" and the "Follow" options will be not available '
							. 'and the notifications about new answers and comments won\'t be send.',
			),
			self::OPTION_ENABLE_CATEGORY_FOLLOWING => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'notifications',
				'subcategory' => 'other',
				'title' => 'Enable category following',
				'desc' => 'Category following allow users to receive notifications about all new threads added to the category and its subcategories.',
			),
			
			// MicroPayments
			self::OPTION_MP_POST_QUESTION_ACTION => array(
				'type' => self::TYPE_RADIO,
				'options' => CMA_MicroPaymentsModel::getActions(),
				'default' => CMA_MicroPaymentsModel::ACTION_DISABLE,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Action on question post',
				'desc' => 'Choose what action to do when user posted a question.',
			),
			self::OPTION_MP_POST_QUESTION_POINTS => array(
				'type' => self::TYPE_INT,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Number of points to grant/charge on question post',
			),
			self::OPTION_MP_POST_ANSWER_ACTION => array(
				'type' => self::TYPE_RADIO,
				'options' => CMA_MicroPaymentsModel::getActions(),
				'default' => CMA_MicroPaymentsModel::ACTION_DISABLE,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Action on answer post',
				'desc' => 'Choose what action to do when user posted an answer.',
			),
			self::OPTION_MP_POST_ANSWER_POINTS => array(
				'type' => self::TYPE_INT,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Number of points to grant/charge on answer post',
			),
			self::OPTION_MP_REWARD_BEST_ANSWER_ENABLE => array(
				'type' => self::TYPE_BOOL,
				'default' => 0,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Reward best answer',
				'desc' => 'Grant points to best answer when the author mark question as resolved.',
			),
			self::OPTION_MP_REWARD_BEST_ANSWER_POINTS => array(
				'type' => self::TYPE_INT,
				'default' => 0,
				'category' => 'micropayments',
				'subcategory' => 'general',
				'title' => 'Best answer reward amount of points',
				'desc' => 'If the best answer reward option is enabled, then grant this amount of points to the best answer author.',
			),
			
			// Ads Index
			self::OPTION_ADV_QUESTIONS_TABLE_BEFORE => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'index',
				'title' => 'Before questions table',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
			self::OPTION_ADV_QUESTIONS_TABLE_AFTER => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'index',
				'title' => 'After questions table',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
			self::OPTION_ADV_THREAD_BEFORE => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'thread',
				'title' => 'Before question',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
			self::OPTION_ADV_THREAD_ANSWERS_BEFORE => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'thread',
				'title' => 'Between question and answers',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
			self::OPTION_ADV_THREAD_ANSWERS_AFTER => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'thread',
				'title' => 'Between answers and the answer form',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
			self::OPTION_ADV_THREAD_ANSWERS_FORM_AFTER => array(
				'type' => self::TYPE_TEXTAREA,
				'category' => 'ads',
				'subcategory' => 'thread',
				'title' => 'After the answer form',
				'desc' => 'Enter the AdSense JavaScript code, a Wordpress shortcode or any HTML source.',
			),
// 			self::OPTION_ADV_CSS_PREFIX => array(
// 				'type' => self::TYPE_STRING,
// 				'category' => 'ads',
// 				'subcategory' => 'general',
// 				'default' => 'cma-adv',
// 				'title' => 'CSS class name',
// 				'desc' => 'Enter the CSS class name that will be added to the ads block. This class name will be used also as a prefix for the detailed class name.',
// 			),
			
		));
	}
	
	
	public static function canReportSpam($userId = null) {
		return (self::getOption(self::OPTION_SPAM_REPORTING_ENABLED)
				AND (self::getOption(self::OPTION_SPAM_REPORTING_GUESTS) OR self::isLoggedIn($userId)));
	}
	
	
	public static function areAttachmentsAllowed() {
		$ext = self::getOption(self::OPTION_ATTACHMENTS_FILE_EXTENSIONS);
		return (!empty($ext) AND (self::getOption(self::OPTION_ATTACHMENTS_ANSWERS_ALLOW) OR self::getOption(self::OPTION_ATTACHMENTS_QUESTIONS_ALLOW)));
	}
	
	
	public static function getLoginPageURL($returnURL = null) {
		if (empty($returnURL)) {
			$returnURL = get_permalink();
		}
		if ($customURL = CMA_Settings::getOption(CMA_Settings::OPTION_LOGIN_PAGE_LINK_URL)) {
			return add_query_arg(array('redirect_to' => urlencode($returnURL)), $customURL);
		} else {
			return wp_login_url($returnURL);
		}
	}
	
	
	public static function getDisplayOptionsDefaults() {
		return array(
			'hideQuestions' => 0,
			'tags'          => 1,
			'pagination'    => 1,
			'form'          => 1,
			'categories'    => intval(!CMA_Settings::getOption(CMA_Settings::OPTION_HIDE_CATEGORIES)),
			'search'        => 0,
			'views'         => CMA_Settings::getOption(CMA_Settings::OPTION_VIEWS_ALLOWED),
			'votes'         => CMA_Settings::getOption(CMA_Settings::OPTION_COLUMN_VOTES_ENABLED),
			'answers'       => CMA_Settings::getOption(CMA_Settings::OPTION_ANSWERS_ALLOWED),
			'updated'       => CMA_Settings::getOption(CMA_Settings::OPTION_UPDATED_ALLOWED),
			'authorinfo'    => CMA_Settings::getOption(CMA_Settings::OPTION_AUTHOR_ALLOWED),
			'statusinfo'    => 0,
			'wrapperclass'  => '',
			'navbar' => 1,
			'sortbar' => 1,
			'formontop' => CMA_Settings::getOption(CMA_Settings::OPTION_QUESTION_FORM_ON_TOP),
			'resolvedPrefix' => 1,
			'icons' => 1,
			'showid' => CMA_Settings::getOption(CMA_Settings::OPTION_TABLE_QUESTION_ID_SHOW),
			'dateposted' => 0,
			'showcontent' => 0,
			'subtree' => 0,
		);
	}
	
	
	public static function getPageTemplatesOptions() {
		$theme = wp_get_theme();
		$templates = (array)$theme->get_page_templates();
		$result = array(
			0 => 'CMA default',
		);
		if ($pageTemplate = locate_template('page.php', false, false)) {
			$result['page.php'] = 'Theme\'s page.php';
		}
// 		if ($singleTemplate = locate_template('single.php', false, false)) {
// 			$result['single.php'] = 'Theme\'s single.php';
// 		}
		return array_merge($result, $templates);
	}
	
	
	public static function getCustomPageTemplate($template) {
		$available = CMA_Settings::getPageTemplatesOptions();
		if (isset($available[$template])) {
			return $template;
		}
	}
	
	public static function getIndexPageTemplate() {
		return static::getCustomPageTemplate(CMA_Settings::getOption(CMA_Settings::OPTION_INDEX_PAGE_TEMPLATE));
	}
	
	public static function getThreadPageTemplate() {
		return static::getCustomPageTemplate(CMA_Settings::getOption(CMA_Settings::OPTION_THREAD_PAGE_TEMPLATE));
	}
	
	
	static function getPostTypesOptions() {
		$types = get_post_types(array(), 'objects');
		foreach ($types as $name => &$type) {
			if (isset($type->labels->name)) {
				$type = $type->labels->name;
			} else {
				$type = $name;
			}
		}
		return $types;
	}
	
	
}
