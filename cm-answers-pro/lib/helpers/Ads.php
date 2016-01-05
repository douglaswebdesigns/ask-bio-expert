<?php

class CMA_Ads {
	
	const CSS_CLASS_PREFIX = 'cma-adv';
	const ACTION_PREFIX = 'CMA_';
	const SETTINGS_PREFIX = 'cma_adv_';
	
	
	public static function display($content = null) {
		
		if (empty($content) OR !is_string($content)) {
			$adName = preg_replace('/^'. preg_quote(self::ACTION_PREFIX, '/') .'/', self::SETTINGS_PREFIX, current_filter());
	    	$content = CMA_Settings::getOption($adName);
// 	    	$content = $adName;
		}
		
		$content = trim($content);
		if (!empty($content)) {
			if (substr($content, 0, 1) == '[' AND substr($content, -1, 1) == ']') {
				$result = do_shortcode($content);
			} else {
				$result = $content;
			}
		}
		
		if (!empty($result)) {
			$className = (isset($adName) ? str_replace(self::SETTINGS_PREFIX, self::CSS_CLASS_PREFIX .'-', $adName) : '');
			echo '<div class="'. self::CSS_CLASS_PREFIX .' '. $className .'">'. $result .'</div>';
		}
		
	}
	
}
