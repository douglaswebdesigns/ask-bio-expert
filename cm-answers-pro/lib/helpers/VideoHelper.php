<?php

class CMA_VideoHelper {
	
	const PLAYER_DEFAULT_WIDTH = 800;
	const PLAYER_ASPECT_RATIO_YOUTUBE = 1.333333333;
	const PLAYER_ASPECT_RATIO_VIMEO =   1.718213058;
	
	
	public static function processContent($content) {
		return self::insertVideos($content, self::findVideos($content));
	}
	
	
	protected static function findVideos($content) {
		$replace = array();
		preg_match_all('/(?<=\[).+(?=\])/', $content, $match);
		if (!empty($match[0]) AND is_array($match[0])) foreach ($match[0] as $item) {
			if (filter_var($item, FILTER_VALIDATE_URL, FILTER_FLAG_SCHEME_REQUIRED | FILTER_FLAG_HOST_REQUIRED | FILTER_FLAG_PATH_REQUIRED)
					AND preg_match('/(youtube\.com|youtu\.be|vimeo\.com)/', $item)) {
				$provider = null;
				$videoId = null;
				$url = parse_url($item);
				switch ($url['host']) {
					case 'youtu.be':
						$provider = 'youtube';
						$videoId = substr($url['path'], 1, 99);
						break;
					case 'youtube.com':
					case 'www.youtube.com':
						if ($url['path'] == '/watch') {
							parse_str($url['query'], $query);
							if (!empty($query['v'])) {
								$provider = 'youtube';
								$videoId = $query['v'];
							}
						}
						break;
					case 'vimeo.com':
						$path = substr($url['path'], 1, 99);
						if (is_numeric($path)) {
							$provider = 'vimeo';
							$videoId = $path;
						}
						break;
				}
				
				if (!empty($videoId)) {
					$replace[$item] = compact('provider', 'videoId');
				}
			}
		}
		return $replace;
	}
	
	
	protected static function insertVideos($content, $videos) {
		foreach ($videos as $url => $video) {
			$content = str_replace("[$url]", self::getVideoPlayer($video['provider'], $video['videoId']), $content);
		}
		return $content;
	}
	
	
	protected static function getVideoPlayer($provider, $videoId) {
// 		$width = self::getPlayerWidth();
		switch ($provider) {
			case 'youtube':
// 				$height = ceil($width/self::PLAYER_ASPECT_RATIO_YOUTUBE);
				$result = sprintf('<iframe src="//www.youtube.com/embed/%s" frameborder="0" allowfullscreen></iframe>', $videoId);
				break;
			case 'vimeo':
// 				$height = ceil($width/self::PLAYER_ASPECT_RATIO_VIMEO);
				$result = sprintf('<iframe src="//player.vimeo.com/video/%s" frameborder="0" '
					. 'webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>', $videoId);
				break;
		}
		return '<div class="cma-player">'. $result .'</div>';
	}
	
	
}
