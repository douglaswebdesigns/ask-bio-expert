<?php

class CMA_Email {
	
	static function send($receivers, $subject, $body, array $vars = array(), array $headers = array()) {
		
		$body = apply_filters('cma_email_body', $body, $receivers, $subject, $vars, $headers);
		$headers = apply_filters('cma_email_headers', $headers, $receivers, $subject, $body, $vars);
		
		if (!self::hasFromHeader($headers)) {
			$headers[] = 'From: '. get_bloginfo('name') . ' <'. get_bloginfo('admin_email') .'>';
		}
		
		$hasReceivers = false;
		if (!is_array($receivers)) {
			$receivers = trim($receivers);
			
			if (self::isEmail($receivers)) {
				$mailTo = $receivers;
				$hasReceivers = true;
			}
		} else {
			$mailTo = null;
			foreach ($receivers as $email) {
				$email = trim($email);
				if (self::isEmail($email)) {
					$headers[] = ' Bcc: '. $email;
					$hasReceivers = true;
				}
			}
		}
		
		if ($hasReceivers) {
			$result = @wp_mail($mailTo, strtr($subject, $vars), strtr($body, $vars), $headers);
		} else {
			$result = false;
		}
		
		return $result;
		
	}
	
	
	static function isEmail($email) {
		if (preg_match('/^([^<]+)(<(.+)>)$/', $email, $match)) {
			$email = $match[3];
		}
		return is_email($email);
	}
	
	
	static function hasFromHeader($headers) {
		foreach ($headers as $header) {
			if (preg_match('#^From:#i', $header)) {
				return true;
			}
		}
		return false;
	}
	
	
}
