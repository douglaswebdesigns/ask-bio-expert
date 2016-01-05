<?php

class CMA_CategoryController extends CMA_BaseController {
	
	const NONCE_CUSTOM_FIELDS = 'cma_category_custom_fields_nonce';
	const FIELD_CUSTOM_FIELDS = 'cma_category_custom_fields';
	
	public static function initialize() {
		
		add_action('cma_category_edit_form_fields', array(__CLASS__, 'categoryEditFormAccess'));
		add_action('cma_category_add_form_fields', array(__CLASS__, 'categoryEditFormAccess'));
		add_action('cma_category_edit_form_fields', array(__CLASS__, 'categoryEditFormExperts'));
		add_action('cma_category_add_form_fields', array(__CLASS__, 'categoryEditFormExperts'));
		add_action('cma_category_edit_form_fields', array(__CLASS__, 'categoryEditFormFields'));
		add_action('cma_category_add_form_fields', array(__CLASS__, 'categoryEditFormFields'));
		add_action('edited_cma_category', array(__CLASS__, 'categoryAfterSave'), 10, 2);
		add_action('created_cma_category', array(__CLASS__, 'categoryAfterSave'), 10, 2);
		add_action('cma_question_form_category_custom_fields', array(__CLASS__, 'questionForm'), 10, 2);
		add_action('cma_question_post_after', array(__CLASS__, 'threadSaveAfter'), 10, 2);
		add_action('cma_question_update_after', array(__CLASS__, 'threadSaveAfter'), 10, 1);
		
		self::addAjaxHandler('cma_load_category_custom_fields', array(__CLASS__, 'loadCustomFields'));
		
		if (!CMA::isLicenseOk()) return;
		
	}
	
	
	
	static function categoryEditFormFields($term = null) {
		
		if (!empty($term) AND is_object($term) AND $category = CMA_Category::getInstance($term)) {
			$fields = $category->getCustomFields();
		} else {
			$term = null;
		}
		if (empty($fields)) {
			$fields = array_fill(0, CMA_Category::CUSTOM_FIELDS_NUMBER, '');
		}
		
		include CMA_PATH .'/views/backend/meta/edit-tag-form-custom-fields.php';
		
	}
	

	public static function categoryEditFormAccess($term = null) {
		$roles = get_editable_roles();
		if (!empty($term) AND is_object($term)) {
			$category = CMA_Category::getInstance($term->term_id);
			$categoryRoles = $category->getAccessRoles();
		} else {
			$term = $category = null;
			$categoryRoles = array();
		}
		include CMA_PATH .'/views/backend/meta/edit-tag-form-access.php';
	}
	
	
	public static function categoryEditFormExperts($term = null) {
	
		wp_enqueue_script( 'suggest' );
		wp_enqueue_script('cma-suggest-user', CMA_URL . '/views/resources/js/suggest-user.js', array('suggest', 'jquery'));
		wp_enqueue_style( 'cma-backend', CMA_URL . '/views/resources/backend.css');
	
		if (!empty($term) AND is_object($term)) {
			$category = CMA_Category::getInstance($term->term_id);
			$experts = $category->getExperts();
		} else {
			$term = $category = null;
			$experts = array();
		}
		include CMA_PATH .'/views/backend/meta/edit-tag-form-experts.php';
	}
	
	
	public static function categoryAfterSave($term_id, $term_taxonomy_id = null) {
		global $wpdb;
		
		// Get category object
		$category = CMA_Category::getInstance($term_id);
		if (empty($category)) return;
		
		// Access roles
		if (isset($_POST['cma_roles_enable'])) {
			if (empty($_POST['cma_roles']) OR !$_POST['cma_roles_enable']) {
				$_POST['cma_roles'] = array();
			}
			$category->setAccessRoles($_POST['cma_roles']);
		}
		
		// Experts
		if (isset($_POST['cma_experts_nonce']) AND wp_verify_nonce($_POST['cma_experts_nonce'], 'cma_experts_nonce')) {
			if (empty($_POST['cma_experts'])) {
				$_POST['cma_experts'] = array();
			}
			$category->setExperts($_POST['cma_experts']);
		}
		
		// Custom fields
		if (isset($_POST[self::NONCE_CUSTOM_FIELDS]) AND wp_verify_nonce($_POST[self::NONCE_CUSTOM_FIELDS], self::NONCE_CUSTOM_FIELDS)
				AND !empty($_POST[self::FIELD_CUSTOM_FIELDS])) {
			$category->setCustomFields($_POST[self::FIELD_CUSTOM_FIELDS]);
		}
		
		
	}
	
	
	static function questionForm($categoryId, $threadId = null) {
		if ($category = CMA_Category::getInstance($categoryId)) {
			$fields = $category->getCustomFields();
			if ($threadId AND $thread = CMA_Thread::getInstance($threadId)) {
				$values = $thread->getCategoryCustomFields();
			} else {
				$values = array_fill(0, count($fields), '');
			}
			echo self::_loadView('answer/meta/question-form-category-custom-fields', compact('fields', 'values'));
		}
	}
	
	
	static function loadCustomFields() {
		if (!empty($_POST['categoryId'])) {
			do_action('cma_question_form_category_custom_fields', $_POST['categoryId']);
		}
		exit;
	}
	
	
	static function threadSaveAfter(CMA_Thread $instance, $data = null) {
		if ($category = $instance->getCategory() AND !empty($_POST[self::FIELD_CUSTOM_FIELDS])) {
			$instance->setCategoryCustomFields($_POST[self::FIELD_CUSTOM_FIELDS]);
		}
	}
	
		
}
