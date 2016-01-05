<?php

require_once CMA_PATH . '/lib/helpers/SettingsViewAbstract.php';

class CMA_SettingsView extends CMA_SettingsViewAbstract {
	
	
	public function renderSubcategory($category, $subcategory) {
		return sprintf('<table><caption>%s</caption>%s</table>',
			esc_html($this->getSubcategoryTitle($category, $subcategory)),
			parent::renderSubcategory($category, $subcategory)
		);
	}
	
	
	public function renderOption($name, array $option = array()) {
		return sprintf('<tr>%s</tr>', parent::renderOption($name, $option));
	}
	
	public function renderOptionTitle($option) {
		return sprintf('<th scope="row">%s:</th>', parent::renderOptionTitle($option));
	}
	
	public function renderOptionControls($name, array $option = array()) {
		return sprintf('<td>%s</td>', parent::renderOptionControls($name, $option));
	}
	
	public function renderOptionDescription($option) {
		return sprintf('<td>%s</td>', parent::renderOptionDescription($option));
	}
	
	
	protected function getSubcategoryTitle($category, $subcategory) {
		$subcategories = $this->getSubcategories();
		if (isset($subcategories[$category]) AND isset($subcategories[$category][$subcategory])) {
			return CMA_Settings::__($subcategories[$category][$subcategory]);
		} else {
			return CMA_Settings::__($subcategory);
		}
	}
	
	
	protected function getCategories() {
		return apply_filters('cma_settings_pages', CMA_Settings::$categories);
	}
	
	
	protected function getSubcategories() {
		return apply_filters('cma_settings_pages_groups', CMA_Settings::$subcategories);
	}
	
	
}