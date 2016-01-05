<?php

if ( class_exists( 'BP_Group_Extension' ) AND function_exists('bp_register_group_extension') ) :

class CMA_BP_Group_Extension extends BP_Group_Extension {
	/**
     * Your __construct() method will contain configuration options for 
     * your extension, and will pass them to parent::init()
     */
    function __construct() {
    	$categoryId = $this->getRelatedCategory();
        $args = array(
            'slug' => 'cm-answers',
            'name' => CMA_Labels::getLocalized('Questions'),
        	'enable_nav_item' => !empty($categoryId),
        );
        parent::init( $args );
    }
    

	protected function getRelatedCategory($groupId = null) {
		if (empty($groupId)) $groupId = $this->get_group_id();
		return get_option(CMA_Settings::OPTION_BP_RELATED_CATEGORY .'_'. $groupId);
	}
	
	
	protected function setRelatedCategory($categoryId, $groupId = null) {
		if (empty($groupId)) $groupId = $this->get_group_id();
		update_option(CMA_Settings::OPTION_BP_RELATED_CATEGORY .'_'. $groupId, $categoryId);
	}
	
 
    /**
     * display() contains the markup that will be displayed on the main 
     * plugin tab
     */
    function display($group_id = null) {
        echo do_shortcode(sprintf('[cma-questions cat=%d %s]',
        	$this->getRelatedCategory($group_id),
        	CMA_Settings::getOption(CMA_Settings::OPTION_BP_GROUP_SHORTCODE_ATTR)
        ));
    }
 
    /**
     * settings_screen() is the catch-all method for displaying the content 
     * of the edit, create, and Dashboard admin panels
     */
    function settings_screen( $group_id = NULL ) {
        $categories = CMA_Category::getCategoriesTree(null, 0, $onlyVisible = false);
    	$currentCategory = $this->getRelatedCategory();
    	$options = '<option value="">-- none --</option>';
    	foreach ($categories as $categoryId => $categoryName) {
			$options .= sprintf('<option value="%s"%s>%s</option>',
				esc_attr($categoryId),
				selected($categoryId, $currentCategory, false),
				esc_html($categoryName)
			);
		}
		printf('<p><label for="cma-category">%s</label>
			<select name="cma-category" id="cma-category">%s</select></p>',
			CMA::__( 'Related CM Answers category' ),
			$options
		);
    }
 
    /**
     * settings_sceren_save() contains the catch-all logic for saving 
     * settings from the edit, create, and Dashboard admin panels
     */
    function settings_screen_save( $group_id = NULL ) {
    	if (isset($_POST['cma-category'])) {
			$this->setRelatedCategory($_POST['cma-category']);
		}
    }
}

bp_register_group_extension( 'CMA_BP_Group_Extension' );

endif;