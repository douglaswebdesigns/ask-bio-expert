<?php

/*
  Plugin Name: CM Answers Pro
  Plugin URI: http://answers.cminds.com/
  Description: PRO Version! Allow users to post questions and answers in stackoverflow style
  Author: CreativeMindsSolutions
  Version: 2.9.5
 */

/*

  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License as published by
  the Free Software Foundation; either version 2 of the License, or
  (at your option) any later version.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */


if (version_compare('5.3', PHP_VERSION, '>')) {
    die('We are sorry, but you need to have at least PHP 5.3 to run this plugin (currently installed version: '.PHP_VERSION.') - please upgrade or contact your system administrator.');
}

//Define constants
define('CMA_PREFIX', 'CMA_');
define('CMA_PATH', dirname(__FILE__));
define('CMA_URL', plugins_url('', __FILE__));
define('CMA_RESOURCE_URL', CMA_URL.'/views/resources/');
define('CMA_PLUGINNAME', plugin_basename( __FILE__ ));
define('CMA_PLUGIN_FILE', __FILE__);

/* AJAX check  */
define('CMA_AJAX', !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest' || !empty($_REQUEST['ajax']));

require_once CMA_PATH . '/licensing_api.php';
require_once CMA_PATH . '/lib/models/Answer.php';
require_once CMA_PATH . '/lib/models/Attachment.php';
require_once CMA_PATH . '/lib/models/QuestionAttachment.php';
require_once CMA_PATH . '/lib/models/AnswerAttachment.php';
require_once CMA_PATH . '/lib/models/Comment.php';
require_once CMA_PATH . '/lib/models/PostType.php';
require_once CMA_PATH . '/lib/helpers/Email.php';
require_once CMA_PATH . '/lib/helpers/ThreadNewsletter.php';
require_once CMA_PATH . '/lib/helpers/FlashMessage.php';
require_once CMA_PATH . '/lib/helpers/BadWords.php';
require_once CMA_PATH . '/lib/helpers/Update.php';
require_once CMA_PATH . '/lib/helpers/Widgets/WidgetAbstract.php';
require_once CMA_PATH . '/lib/helpers/Widgets/CountersWidget.php';
require_once CMA_PATH . '/lib/helpers/Widgets/TagsWidget.php';
require_once CMA_PATH . '/lib/helpers/Widgets/LoginWidget.php';
require_once CMA_PATH . '/lib/helpers/Widgets/RelatedQuestionsWidget.php';
require_once CMA_PATH . '/lib/helpers/Widgets/TopContributorsWidget.php';
require_once CMA_PATH . '/lib/helpers/meta-box/RelatedQuestionsMetaBox.php';
require_once CMA_PATH . '/lib/helpers/shortcodes/QuestionForm.php';
require_once CMA_PATH . '/lib/helpers/StickyQuestion.php';
require_once CMA_PATH . '/lib/helpers/UserRelatedQuestions.php';
require_once CMA_PATH . '/lib/helpers/IPGeolocation.php';
require_once CMA_PATH . '/lib/controllers/BaseController.php';
require_once CMA_PATH . '/lib/controllers/AnswerController.php';
require_once CMA_PATH . '/lib/models/Settings.php';
require_once CMA_PATH . '/lib/models/Labels.php';
require_once CMA_PATH . '/lib/helpers/FollowersEngine.php';
require_once CMA_PATH . '/lib/helpers/VideoHelper.php';
require_once CMA_PATH . '/lib/models/Category.php';
require_once CMA_PATH . '/lib/models/PrivateQuestion.php';
require_once CMA_PATH . '/lib/controllers/LogsController.php';
require_once CMA_PATH . '/lib/models/Thread.php';
require_once CMA_PATH . '/lib/helpers/MicroPayments/MicroPayments.php';
require_once CMA_PATH . '/lib/helpers/SettingsViewAbstract.php';
require_once CMA_PATH . '/lib/helpers/Ads.php';
require_once CMA_PATH . '/lib/helpers/BuddyPress.php';
require_once CMA_PATH . '/lib/controllers/AnswerController.php';

//Init the plugin
require_once CMA_PATH . '/lib/CMA.php';

CMA::init(__FILE__);
