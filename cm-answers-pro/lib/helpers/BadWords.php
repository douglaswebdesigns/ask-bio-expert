<?php

class CMA_BadWords {
	
	static function filterIfEnabled($str) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_BAD_WORDS_FILTER_ENABLE)) {
			return self::contains($str);
		} else {
			return false;
		}
	}
	
	
	static function contains($str) {
		$words = self::getBadWordsList();
		foreach ($words as $word) {
			if (self::isRegExp($word)) {
				if (!self::isCaseInsensitive($word)) {
					$word .= 'i'; // apply case-insensitivity
				}
				if (@preg_match($word, $str, $match)) {
					return $match[0];
				}
			} else { // simple string
				if (stripos($str, $word) !== false) {
					return $word;
				}
			}
		}
		return false;
	}
	
	
	static protected function isRegExp($word) {
		return (preg_match('#^/.+/[a-z]*$#i', $word) > 0);
	}
	
	
	static protected function isCaseInsensitive($regexp) {
		return (preg_match('#^/.+/[a-z]*i[a-z]*$#i', $regexp) > 0);
	}
	
	static protected function getBadWordsList() {
		return array_filter(array_map('trim', explode("\n", CMA_Settings::getOption(CMA_Settings::OPTION_BAD_WORDS_LIST))));
	}
	
}
