<?php

class CMA_Category {
	
	const TAXONOMY = 'cma_category';
	const FOLLOWERS_USER_META_PREFIX = 'cma_follower_category';
	const OPTION_ACCESS_ROLES_PREFIX = 'cma_categories_access_roles';
	const OPTION_EXPERTS_PREFIX = 'cma_categories_experts';
	const OPTION_CUSTOM_FIELDS_PREFIX = 'cma_categories_cfields';
	
	const CUSTOM_FIELDS_NUMBER = 4;
	
	protected $term;
	protected $experts = array();
	protected $accessRoles = array();
	
	
	public static function getInstance($category) {
		if (is_array($category)) $category = (object)$category;
		if ($category) {
			if (is_object($category)) return new self($category);
			else if (is_scalar($category)) {
				return self::getInstance(get_term_by('term_id', $category, self::TAXONOMY));
			}
		}
	}
	
	
	function __construct($term) {
		$this->term = $term;
	}
	
	
	public function getPermalink() {
		return get_term_link($this->getId(), self::TAXONOMY);
	}
	
	
	public function getLink() {
		return sprintf('<a href="%s" class="%s">%s</a>',
			esc_attr($this->getPermalink()),
			'cma-category-link',
			esc_html($this->getName())
		);
	}
	
	
	public function getFollowUrl($backlink = null) {
		if (is_null($backlink) AND $backlink !== false) {
			$backlink = $_SERVER['REQUEST_URI'];
		}
		return $this->getPermalink()
				. sprintf('?cma-action=follow&categoryId=%d&nonce=%s',
					$this->getId(),
					wp_create_nonce(CMA_AnswerController::NONCE_FOLLOW)
				)
				. ($backlink === false ? '' : '&backlink='. urlencode(base64_encode($backlink)));
	}
	
	
	
	public function getId() {
		return intval($this->term->term_id);
	}
	
	
	public function getName() {
		return $this->term->name;
	}
	
	
	public static function getSubcategories($parentCategoryId, $onlyVisible = true) {
		$cats = array();
		$terms = get_terms(self::TAXONOMY, array(
				'orderby'    => 'name',
				'hide_empty' => 0,
				'parent' => $parentCategoryId,
		));
		foreach($terms as $term) {
			if (!$onlyVisible OR ($category = CMA_Category::getInstance($term->term_id) AND $category->isVisible())) {
				$cats[$term->term_id] = $term->name;
			}
		}
		return $cats;
	}
	
	

	public function getParentInstance() {
		if ($this->term->parent) {
			return self::getInstance($this->term->parent);
		}
	}
	
	
	
	public function getParentId() {
		return $this->term->parent;
	}
	

	public function getFollowersEngine() {
		return new CMA_FollowersEngine(self::FOLLOWERS_USER_META_PREFIX, $this->getId());
	}
	
	
	public function isVisible($userId = null) {
		if (is_null($userId)) $userId = CMA::getPostingUserId();
		if (user_can($userId, 'manage_options')) return true;
		$accessRoles = $this->getAccessRoles();
		if (empty($accessRoles)) {
			return true;
		}
		else if ($user = get_userdata($userId)) {
			$common = array_intersect($user->roles, $accessRoles);
			return !empty($common);
		} else {
			return false;
		}
	}
	
	
	public function getAccessRoles($cache = true) {
		global $wpdb;
		if ($cache AND !empty($this->accessRoles)) return $this->accessRoles;
		$like = self::OPTION_ACCESS_ROLES_PREFIX .'_'. $this->getId() .'_';
		$this->accessRoles = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $like .'%'));
		foreach ($this->accessRoles as &$record) {
			$record = substr($record, strlen($like), strlen($record));
		}
		return $this->accessRoles;
	}
	
	
	function getExperts($cache = true) {
		global $wpdb;
		if ($cache AND !empty($this->experts)) return $this->experts;
		$like = self::OPTION_EXPERTS_PREFIX .'_'. $this->getId() .'_';
		$this->experts = $wpdb->get_col($wpdb->prepare("SELECT option_name FROM $wpdb->options WHERE option_name LIKE %s", $like .'%'));
		foreach ($this->experts as &$record) {
			$record = substr($record, strlen($like), strlen($record));
		}
		return $this->experts;
	}
	
	
	public function setAccessRoles($roles) {
		$currentRoles = $this->getAccessRoles();
		$toAdd = array_diff($roles, $currentRoles);
		$toRemove = array_diff($currentRoles, $roles);
		foreach ($toAdd as $roleName) {
			add_option(self::OPTION_ACCESS_ROLES_PREFIX .'_'. $this->getId() .'_'. $roleName, 1, false, false);
		}
		foreach ($toRemove as $roleName) {
			delete_option(self::OPTION_ACCESS_ROLES_PREFIX .'_'. $this->getId() .'_'. $roleName);
		}
	}
	
	
	public function setExperts($experts) {
		$current = $this->getExperts();
		$toAdd = array_diff($experts, $current);
		$toRemove = array_diff($current, $experts);
		foreach ($toAdd as $userId) {
			add_option(self::OPTION_EXPERTS_PREFIX .'_'. $this->getId() .'_'. $userId, 1, false, false);
		}
		foreach ($toRemove as $userId) {
			delete_option(self::OPTION_EXPERTS_PREFIX .'_'. $this->getId() .'_'. $userId);
		}
	}
	
	
	public function getUnansweredQuestionsCount() {
		global $wpdb;
		
		if (CMA_Category::isAnyCategoryResticted()) {
			$accessFilter = ' AND (ID IN ('. CMA_Thread::getCategoryAccessFilterSubquery() .')
    					OR ID NOT IN ('. CMA_Thread::getCategorizedThreadIdsSubquery() .')
    					OR post_author = '. intval(get_current_user_id()) .'
    				)';
		} else $accessFilter = '';
		
		return $wpdb->get_var($wpdb->prepare("SELECT COUNT(DISTINCT p.ID) AS c
			FROM $wpdb->posts p
			JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID
			JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s
			WHERE p.post_type = %s
			AND p.post_status = 'publish'
			AND tt.term_id = %d
			AND (SELECT COUNT(*) FROM $wpdb->comments c WHERE c.comment_post_id = p.ID AND c.comment_type = %s AND c.comment_approved = 1) = 0
			" . $accessFilter,
			CMA_Category::TAXONOMY,
			CMA_Thread::POST_TYPE,
			$this->getId(),
			CMA_Answer::COMMENT_TYPE
		));
		
	}
	
	
	public function getLastActivity() {
		global $wpdb;
		
		if (CMA_Category::isAnyCategoryResticted()) {
			$accessFilter = ' AND (ID IN ('. CMA_Thread::getCategoryAccessFilterSubquery() .')
    					OR ID NOT IN ('. CMA_Thread::getCategorizedThreadIdsSubquery() .')
    					OR post_author = '. intval(get_current_user_id()) .'
    				)';
		} else $accessFilter = '';
		
		return $wpdb->get_var($wpdb->prepare("SELECT MAX(p.post_modified) AS pm
			FROM $wpdb->posts p
			JOIN $wpdb->term_relationships tr ON tr.object_id = p.ID
			JOIN $wpdb->term_taxonomy tt ON tr.term_taxonomy_id = tt.term_taxonomy_id AND tt.taxonomy = %s
			WHERE p.post_type = %s
			AND p.post_status = 'publish'
			AND tt.term_id = %d
			" . $accessFilter,
			CMA_Category::TAXONOMY,
			CMA_Thread::POST_TYPE,
			$this->getId()
		));
	}
	

    public static function getCategoriesTree($parentId = null, $depth = 0, $onlyVisible = true) {
        $terms = get_terms(CMA_Category::TAXONOMY, array(
            'orderby'    => 'name',
            'hide_empty' => 0,
        	'parent' => $parentId
        ));
        $output = array();
        foreach ($terms as $term) {
        	if (!$onlyVisible OR ($category = CMA_Category::getInstance($term->term_id) AND $category->isVisible())) {
	        	$output[$term->term_id] = str_repeat('-', $depth) .' '. $term->name;
	        	$output += self::getCategoriesTree($term->term_id, $depth+1, $onlyVisible);
        	}
        }
        return $output;
    }
    
    
    public static function getCategoriesTreeArray($parentId = null, $depth = 0, $onlyVisible = true) {
    	$terms = get_terms(CMA_Category::TAXONOMY, array(
    			'orderby'    => 'name',
    			'hide_empty' => 0,
    			'parent' => $parentId
    	));
    	$output = array();
    	foreach ($terms as $term) {
    		if (!$onlyVisible OR ($category = CMA_Category::getInstance($term->term_id) AND $category->isVisible())) {
	    		$term->term_id = intval($term->term_id);
	    		$output[$parentId ? $parentId : 0][$term->term_id] = $term;
	    		$output += self::getCategoriesTreeArray($term->term_id, $depth+1, $onlyVisible);
    		}
    	}
    	return $output;
    }
    

    public static function getCategories($onlyVisible = true)
    {
        $cats = array();
        $terms = get_terms(CMA_Category::TAXONOMY, array(
            'orderby'    => 'name',
            'hide_empty' => 0
        ));
        foreach($terms as $term) {
        	if (!$onlyVisible OR ($category = CMA_Category::getInstance($term->term_id) AND $category->isVisible())) {
            	$cats[$term->term_id] = $term->name;
        	}
        }
        return $cats;
    }
    
    
    public static function getRootCategories($onlyVisible = true) {
    	$cats = array();
    	$terms = get_terms(CMA_Category::TAXONOMY, array(
    			'orderby'    => 'name',
    			'hide_empty' => 0,
    			'parent' => null,
    	));
    	foreach($terms as $term) {
    		if (!$onlyVisible OR ($category = CMA_Category::getInstance($term->term_id) AND $category->isVisible())) {
    			$cats[$term->term_id] = $term->name;
    		}
    	}
    	return $cats;
    }
    
    

    public static function getPostCommentsCategory($postId) {
    	$terms = (array)wp_get_post_terms($postId, CMA_Category::TAXONOMY);
    	return array_pop($terms);
    }
    
    
    public static function setPostCommentsCategoryId($postId, $categoryId) {
    	$result = wp_set_post_terms($postId, array($categoryId), CMA_Category::TAXONOMY);
    }
    

    /**
     * Get number of questions and answers in the each category.
     * 
     * @param int $categoryId (optional) Get data for a specified category or all categories if null.
     * @return object|array
     */
    public static function getCategoriesQACount($categoryId = null, $field = null) {
    	global $wpdb;
    	
	    /**
	     * Cached categories QA count
	     * @var array
	     */
    	static $cachedCategoriesQACount = array();
    	if (empty($cachedCategoriesQACount)) {
	    	$querystr = "SELECT count(DISTINCT $wpdb->posts.ID) AS question_count,
	    		count(DISTINCT $wpdb->comments.comment_ID) AS answer_count, $wpdb->term_taxonomy.term_id
	    		FROM $wpdb->term_taxonomy
	    		JOIN $wpdb->term_relationships ON ($wpdb->term_relationships.term_taxonomy_id = $wpdb->term_taxonomy.term_taxonomy_id)
	    		JOIN $wpdb->posts ON ($wpdb->posts.ID = $wpdb->term_relationships.object_id)
	    		LEFT JOIN $wpdb->comments ON ($wpdb->posts.ID = $wpdb->comments.comment_post_ID AND $wpdb->comments.comment_approved = 1
	    				AND $wpdb->comments.comment_type = '". CMA_Answer::COMMENT_TYPE ."')
				WHERE $wpdb->posts.post_status = 'publish'
		    	AND $wpdb->posts.post_type = '". CMA_Thread::POST_TYPE ."'
				GROUP BY $wpdb->term_taxonomy.term_id";
	    	$rows = $wpdb->get_results($querystr);
	    	$result = array();
	    	foreach ($rows as $row) {
	    		$result[intval($row->term_id)] = $row;
	    	}
	    	$cachedCategoriesQACount = $result;
    	}
    	if (empty($categoryId)) {
    		return $cachedCategoriesQACount;
    	} else {
    		if (empty($cachedCategoriesQACount[$categoryId])) {
    			if (empty($field)) {
    				return (object)array('term_id' => $categoryId, 'question_count' => 0, 'answer_count' => 0);
    			} else {
    				return 0;
    			}
    		} else {
    			$obj = $cachedCategoriesQACount[$categoryId];
    			if (empty($field)) {
    				return $obj;
    			} else {
    				if (isset($obj->$field)) return $obj->$field;
    				else return 0;
    			}
    		}
    	}
    }
    
    
    
    public static function isAnyCategoryResticted() {
    	global $wpdb;
    	static $result = null;
    	if (is_null($result)) {
    		$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
    			self::OPTION_ACCESS_ROLES_PREFIX .'_%'));
    		if ($count > 0) {
    			$result = true;
    		}
    		else if (CMA_Settings::getOption(CMA_Settings::OPTION_RESTRICT_UNANSWERED_QUESTIONS_TO_EXPERTS)) {
    			$count = $wpdb->get_var($wpdb->prepare("SELECT COUNT(*) FROM $wpdb->options WHERE option_name LIKE %s",
    				self::OPTION_EXPERTS_PREFIX .'\_%'));
    			$result = ($count > 0);
    		} else {
    			$result = false;
    		}
    	}
    	return $result;
    }
    
    
    
    public static function getVisibleTermTaxonomyIds($userId = null) {
    	global $wpdb;
    	
    	if (is_null($userId)) $userId = CMA::getPostingUserId();
    	if (empty($userId)) $userId = 0;
    	
    	static $results = array();
    	
    	if (empty($results[$userId])) {
	    	
	    	$rolesConditions = $expertsConditions = '';
	    	if ($user = get_userdata($userId)) {
	    		if (user_can($user, 'manage_options')) {
	    			$rolesConditions = $expertsConditions = ' OR 1=1';
	    		} else {
		    		if (!empty($user->roles) AND is_array($user->roles)) foreach ($user->roles as $role) {
			    		$rolesConditions .= $wpdb->prepare(" OR o.option_name = CONCAT(%s, tt.term_id, %s)",
			    			CMA_Category::OPTION_ACCESS_ROLES_PREFIX . '_',
			    			'_' . $role);
		    		}
	    		}
	    	}
	    	
	    	$sql = $wpdb->prepare("SELECT tt.term_taxonomy_id
				FROM $wpdb->term_taxonomy tt
				LEFT JOIN $wpdb->options o ON o.option_name LIKE CONCAT(%s, tt.term_id, '\_%%')
				WHERE tt.taxonomy = %s
					AND (o.option_id IS NULL $rolesConditions)
	    		",
	    		self::OPTION_ACCESS_ROLES_PREFIX . '_',
	    		self::TAXONOMY
	    	);
	    	$results[$userId] = $wpdb->get_col($sql);
	    	
    	}
    	
    	return $results[$userId];
    	
    }
    
    

    public static function getExpertsTermTaxonomyIds($userId = null) {
    	global $wpdb;
    	 
    	if (is_null($userId)) $userId = CMA::getPostingUserId();
    	if (empty($userId)) $userId = 0;
    	 
    	static $results = array();
    	 
    	if (empty($results[$userId])) {
    
    		$expertsConditions = $wpdb->prepare(" OR o.option_name = CONCAT(%s, tt.term_id, %s)",
    			CMA_Category::OPTION_EXPERTS_PREFIX . '_',
    			'_' . intval(get_current_user_id()));
    		
    		$sql = $wpdb->prepare("SELECT tt.term_taxonomy_id
    			FROM $wpdb->term_taxonomy tt
    			LEFT JOIN $wpdb->options o ON o.option_name LIKE CONCAT(%s, tt.term_id, '\_%%')
    			WHERE tt.taxonomy = %s
    			AND (o.option_id IS NULL $expertsConditions)
    			",
    			self::OPTION_EXPERTS_PREFIX . '_',
    			self::TAXONOMY
    		);
    		$results[$userId] = $wpdb->get_col($sql);
    
    	}
    	
    	return $results[$userId];
    	
    }
    
    
    public static function canBeFollower() {
    	if (!CMA_Settings::getOption(CMA_Settings::OPTION_ENABLE_CATEGORY_FOLLOWING)) return false;
    	return CMA_FollowersEngine::canBeFollower();
    }
    
    
    public function getCustomFields() {
    	$value = array();
    	$fields = get_option(self::OPTION_CUSTOM_FIELDS_PREFIX . '_'. $this->getId(), array_fill(0, CMA_Category::CUSTOM_FIELDS_NUMBER, ''));
    	for ($i=0; $i<self::CUSTOM_FIELDS_NUMBER; $i++) {
    		if (isset($fields[$i])) {
    			$value[$i] = $fields[$i];
    		} else {
    			$value[$i] = '';
    		}
    	}
    	return $value;
    }
    
    
    public function setCustomFields($fields) {
    	$value = array();
    	for ($i=0; $i<self::CUSTOM_FIELDS_NUMBER; $i++) {
    		if (isset($fields[$i])) {
    			$value[$i] = $fields[$i];
    		} else {
    			$value[$i] = '';
    		}
    	}
    	update_option(self::OPTION_CUSTOM_FIELDS_PREFIX . '_'. $this->getId(), $value, $autoload = false);
    }
	
	
}
