<?php
/**
 * File contains Authentication controller
 * @package Library
 * @subpackage Controllers
 */
require_once CMA_PATH . '/lib/helpers/Opauth/Opauth.php';
/**
 * Authentication controller
 *
 * @author SP
 * @version 0.1.0
 * @copyright Copyright (c) 2013, REC
 * @package Library
 * @subpackage Controllers
 */
class CMA_AuthController extends CMA_BaseController
{
    protected static $_authInstance = null;
    protected static $_redirectUri;

    public static function initialize()
    {
        add_filter('CMA_admin_settings', array(get_class(), 'addAdminSettings'));
    }

    public static function facebookHeader()
    {
        if(self::isProviderConfigured('facebook'))
        {
            self::_getAuthInstance();
            exit;
        }
    }

    public static function linkedinHeader()
    {
        self::_getAuthInstance();
        exit;
    }

    public static function googleHeader()
    {
        self::_getAuthInstance();
        exit;
    }
    
    public static function twitterHeader()
    {
    	self::_getAuthInstance();
    	exit;
    }
    
    public static function liveHeader()
    {
    	self::_getAuthInstance();
    	exit;
    }

    public static function callbackHeader()
    {
        $auth     = self::_getAuthInstance(false);
        
        /**
         * Fetch auth response, based on transport configuration for callback
         */
        $response = null;

        switch($auth->env['callback_transport'])
        {
            case 'session':
                if (!session_id()) session_start();
                $response = $_SESSION['opauth'];
                unset($_SESSION['opauth']);
                break;
            case 'post':
                $response = unserialize(base64_decode($_POST['opauth']));
                break;
            case 'get':
                $response = unserialize(base64_decode($_GET['opauth']));
                break;
            default:
                die('<strong style="color: red;">Error: </strong>Unsupported callback_transport.' . "<br>\n");
                break;
        }

        /*
         * If there's no response
         */
        if(!is_array($response))
        {
            self::addMessage(self::MESSAGE_ERROR, __('Authentication failed.'));
            self::_redirectAfterAuthenticationTry();
        }

        /**
         * Check if it's an error callback
         */
        if(array_key_exists('error', $response))
        {
            self::addMessage(self::MESSAGE_ERROR, __('Authentication canceled.'));
            self::_redirectAfterAuthenticationTry();

//             var_dump($response);
            die('<strong style="color: red;">Authentication error: </strong> Opauth returns error auth response.' . "<br>\n");
        }

        /**
         * Auth response validation
         *
         * To validate that the auth response received is unaltered, especially auth response that
         * is sent through GET or POST.
         */
        else
        {
            if(empty($response['auth']) || empty($response['timestamp']) || empty($response['signature']) || empty($response['auth']['provider']) || empty($response['auth']['uid']))
            {
                die('<strong style="color: red;">Invalid auth response: </strong>Missing key auth response components.' . "<br>\n");
            }
            elseif(!$auth->validate(sha1(print_r($response['auth'], true)), $response['timestamp'], $response['signature'], $reason))
            {
                die('<strong style="color: red;">Invalid auth response: </strong>' . $reason . ".<br>\n");
            }
            else
            {
                /**
                 * It's all good. Go ahead with your application-specific authentication logic
                 */
                self::_authenticate($response['auth']);
                self::_redirectAfterAuthenticationTry();
            }
        }
    }

    protected static function _redirectAfterAuthenticationTry()
    {
        if(!empty($_SESSION['cma']['redirectUri']))
        {
            $redirectTo = $_SESSION['cma']['redirectUri'];
            unset($_SESSION['cma']['redirectUri']);
            wp_safe_redirect($redirectTo, 303);
        }
        else if (!empty($_GET['redirect'])) {
        	wp_safe_redirect($_GET['redirect'], 303);
        }
        else
        {
            wp_redirect(home_url(), 303);
        }
        exit;
    }

    protected static function _authenticate($auth = array())
    {
        global $wpdb;
        $provider   = $auth['provider'];
        $uid        = $auth['uid'];
        $email      = $auth['info']['email'];
        $name       = $auth['info']['name'];
        $wp_user_id = $wpdb->get_var($wpdb->prepare("SELECT user_id FROM {$wpdb->usermeta} WHERE meta_key = %s AND meta_value = %s", '_cma_uid_'. strtolower($provider), $uid));

        if(empty($wp_user_id))
        {
            // Look for a user with the same email
            if ($wp_user_obj = get_user_by('email', $email)) {
	            // get the userid from the fb email if the query failed
	            $wp_user_id = $wp_user_obj->ID;
            }
        }
        if(!empty($wp_user_id))
        {
            // We already have this user in the database
        }
        else
        {
            // Oh no, this user is not registered yet, we should create him an account
            $wp_user_id = wp_create_user($email, wp_generate_password(), $email);
        }
        if(!empty($wp_user_id) && !($wp_user_id instanceof WP_Error))
        {
            $userinfo = get_userdata($wp_user_id);
            update_user_meta($wp_user_id, '_cma_uid_' . strtolower($provider), $uid);
            wp_update_user(array(
                'ID' => $wp_user_id,
                'display_name' => $name,
                'user_nicename' => urldecode(sanitize_title_with_dashes($name))
            ));
            update_user_meta($wp_user_id, '_cma_social_url', $auth['info']['urls'][strtolower($provider)]);
            update_user_meta($wp_user_id, '_cma_social_' . strtolower($provider) . '_url', $auth['info']['urls'][strtolower($provider)]);
            wp_clear_auth_cookie();
            wp_set_auth_cookie($wp_user_id);
        }
    }

    protected static function _getAuthInstance($run = true)
    {
        if(empty(self::$_authInstance))
        {
            self::$_redirectUri = !empty($_SESSION['cma']['redirectUri']) ? $_SESSION['cma']['redirectUri'] : self::_getParam('redirect');
            $_SESSION['cma']['redirectUri'] = self::$_redirectUri;
            self::$_authInstance = new Opauth(self::_getAuthConfig(), $run);
        }
        return self::$_authInstance;
    }

    protected static function _getAuthConfig()
    {
        $config = array(
            'path' => trailingslashit(parse_url(self::getUrl('auth', 'index'), PHP_URL_PATH)),
            'callback_url' => '{path}callback',
            'security_salt' => 'LDFmiilYf8Fyw5W10rx4Wiquwe675Tuy5vJidQKDx8pMJbmw28R1C4m',
//            'callback_transport' => 'get',
            'Strategy' => array(
                // Define strategies and their respective configs here
                //TODO: get this from settings
                'Facebook' => array(
                    'app_id' => get_option('_cma_fb_app_id', ''),
                    'app_secret' => get_option('_cma_fb_app_secret', ''),
                    'scope' => 'email',
//                 	'redirect_uri' => $_SESSION['cma']['redirectUri'],
                ),
                'Google' => array(
                    'client_id' => get_option('_cma_google_client_id', ''),
                    'client_secret' => get_option('_cma_google_client_secret', '')
                ),
                'LinkedIn' => array(
                    'api_key' => get_option('_cma_linkedin_api_key', ''),
                    'secret_key' => get_option('_cma_linkedin_secret_key', ''),
                    'scope' => 'r_emailaddress',
                    'profile_fields' => array('id', 'first-name', 'last-name',
                        'formatted-name',
                        'email-address',
                        'public-profile-url'),
                ),
            	'Twitter' => array(
            		'consumer_key' => get_option('_cma_twitter_consumer_key', ''),
            		'consumer_secret' => get_option('_cma_twitter_consumer_secret', '')
            	),
            	'Live' => array(
            		'client_id' => get_option('_cma_live_client_id', ''),
            		'client_secret' => get_option('_cma_live_client_secret', '')
            	),
            )
        );
        return $config;
    }

    public static function isProviderConfigured($provider)
    {
        $config = self::_getAuthConfig();
        switch(strtolower($provider))
        {
            case 'facebook':
                $result = (!empty($config['Strategy']['Facebook']['app_id']) && !empty($config['Strategy']['Facebook']['app_secret']));
                break;
            case 'google':
                $result = (!empty($config['Strategy']['Google']['client_id']) && !empty($config['Strategy']['Google']['client_secret']));
                break;
            case 'linkedin':
                $result = (function_exists('curl_init') && !empty($config['Strategy']['LinkedIn']['api_key']) && !empty($config['Strategy']['LinkedIn']['secret_key']));
                break;
            case 'twitter':
               	$result = (function_exists('curl_init') && !empty($config['Strategy']['Twitter']['consumer_key']) && !empty($config['Strategy']['Twitter']['consumer_secret']));
               	break;
            case 'live':
               	$result = (!empty($config['Strategy']['Live']['client_id']) && !empty($config['Strategy']['Live']['client_secret']));
               	break;
        }
        return $result;
    }

    public static function addAdminSettings($params = array())
    {
        
        $params['fb_app_id']            = get_option('_cma_fb_app_id', '');
        $params['fb_app_secret']        = get_option('_cma_fb_app_secret', '');
        $params['google_client_id']     = get_option('_cma_google_client_id', '');
        $params['google_client_secret'] = get_option('_cma_google_client_secret', '');
        $params['linkedin_api_key']     = get_option('_cma_linkedin_api_key', '');
        $params['linkedin_secret_key']  = get_option('_cma_linkedin_secret_key', '');
        $params['twitter_consumer_key'] = get_option('_cma_twitter_consumer_key', '');
        $params['twitter_consumer_secret'] = get_option('_cma_twitter_consumer_secret', '');
        $params['live_client_id'] = get_option('_cma_live_client_id', '');
        $params['live_client_secret'] = get_option('_cma_live_client_secret', '');
        return $params;
    }

}
