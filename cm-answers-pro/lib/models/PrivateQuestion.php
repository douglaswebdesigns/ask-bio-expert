<?php

class CMA_PrivateQuestion {
	
	
	public static function send($senderId, $recipientId, $title, $question) {
		
		$title = trim($title);
		$question = trim($question);
		
		$errors = array();
		if (empty($senderId)) $errors['sender'] = 'Invalid sender ID.';
		if (empty($recipientId)) $errors['recipient'] = 'Invalid recipient ID.';
		if (empty($title)) $errors['title'] = 'Title is required.';
		if (empty($question)) $errors['question'] = 'Question is required.';
		if (!empty($errors)) throw new Exception(serialize($errors));
		
		if ($senderId == $recipientId) $errors['recipient'] = 'You cannot send question to yourself.';
		$sender = get_userdata($senderId);
		if (empty($sender)) $errors['sender'] = 'Unknown sender.';
		$recipient = get_userdata($recipientId);
		if (empty($recipient)) $errors['recipient'] = 'Unknown recipient.';
		if (!empty($errors)) throw new Exception(serialize($errors));
		
		$replace = array(
			'[blogname]' => get_bloginfo('name'),
			'[username]' => strip_tags($sender->display_name),
			'[title]' => htmlspecialchars($title),
			'[question]' => htmlspecialchars($question),
		);
		
		$to = sprintf('%s <%s>', $recipient->display_name, $recipient->user_email);
		$replyTo = sprintf('%s <%s>', $sender->display_name, $sender->user_email);
		CMA_Email::send($to, CMA_Settings::getOption(CMA_Settings::OPTION_PRIVATE_QUESTION_EMAIL_SUBJECT), CMA_Settings::getOption(CMA_Settings::OPTION_PRIVATE_QUESTION_EMAIL_TEMPLATE), $replace, array('Reply-To: '. $replyTo));
// 		return wp_mail($to, $subject, $template, array('Reply-To: '. $replyTo));
		
	}
	
	
}