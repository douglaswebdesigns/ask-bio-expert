<?php

class CMA_Labels {
	
	const FILENAME = 'labels.tsv';
	const OPTION_LABEL_PREFIX = 'cma_label_';
	
	protected static $labels = array();
	protected static $labelsByCategories = array();
	
	
	public static function bootstrap() {
		
		self::loadLabelFile();
		do_action('cma_labels_init');
		
		/* You can use the following filters to add new labels for CMA:
		add_filter('cma_labels_init_labels', function($labels) {
			$labels['label_name'] = array('default' => 'Value', 'desc' => 'Description', 'category' => 'Other');
			return $labels;
		});
		add_filter('cma_labels_init_labels_by_categories', function($labelsByCategories) {
			$labelsByCategories['Other'][] = 'label_name';
			return $labelsByCategories;
		});
		*/
		
		self::$labels = apply_filters('cma_labels_init_labels', self::$labels);
		self::$labelsByCategories = apply_filters('cma_labels_init_labels_by_categories', self::$labelsByCategories);
		
	}
	

	public static function getLabel($labelKey) {
		$optionName = self::OPTION_LABEL_PREFIX . $labelKey;
		$default = self::getDefaultLabel($labelKey);
		$result = get_option($optionName, (empty($default) ? $labelKey : $default));
		return apply_filters('cma_get_label_filter', $result, $labelKey, $optionName, $default);
	}
	
	public static function setLabel($labelKey, $value) {
		$optionName = self::OPTION_LABEL_PREFIX . $labelKey;
		if (strlen($value) == 0) $value = self::getDefaultLabel($labelKey);
		update_option($optionName, $value);
	}
	
	public static function getLocalized($labelKey) {
		return CMA::__(self::getLabel($labelKey));
	}
	
	
	public static function n($singularLabelKey, $pluralLabelKey, $number) {
		return _n($singularLabelKey, $pluralLabelKey, $number, CMA::TEXT_DOMAIN);
	}
	
	
	public static function getDefaultLabel($key) {
		if ($label = self::getLabelDefinition($key)) {
			return $label['default'];
		}
	}
	
	
	public static function getDescription($key) {
		if ($label = self::getLabelDefinition($key)) {
			return $label['desc'];
		}
	}
	
	
	public static function getLabelDefinition($key) {
		$labels = self::getLabels();
		return (isset($labels[$key]) ? $labels[$key] : NULL);
	}
	
	
	public static function getLabels() {
		return self::$labels;
	}
	
	
	public static function getLabelsByCategories() {
		return self::$labelsByCategories;
	}
	
	
	public static function getDefaultLabelsPath() {
		return dirname(__FILE__) .'/'. self::FILENAME;
	}

	
	public static function loadLabelFile($path = null) {
		$file = explode("\n", file_get_contents(empty($path) ? self::getDefaultLabelsPath() : $path));
		foreach ($file as $row) {
			$row = explode("\t", trim($row));
			if (count($row) >= 2) {
				$label = array(
					'default' => $row[1],
					'desc' => (isset($row[2]) ? $row[2] : null),
					'category' => (isset($row[3]) ? $row[3] : null),
				);
				self::$labels[$row[0]] = $label;
				self::$labelsByCategories[$label['category']][] = $row[0];
			}
		}
	}
	
	
}

add_action('cma_load_label_file', array('CMA_Labels', 'loadLabelFile'), 1);
