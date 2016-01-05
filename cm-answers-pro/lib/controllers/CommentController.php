<?php

class CMA_CommentController extends CMA_BaseController {
	
	protected static $commentsCache = array();
	
	
	public static function initialize() {
		
		add_action('wp_set_comment_status', array(__CLASS__, 'hookSetStatus'), 10, 2);
		
		if (!CMA::isLicenseOk()) return;
		
		add_action('CMA_comments_question', array(get_class(), 'displayQuestionComments'), 20, 1);
		add_action('CMA_comments_single', array(get_class(), 'displayCommentSingle'), 20, 1);
		add_action('CMA_comments_form_add', array(get_class(), 'displayCommentFormAdd'), 20, 2);
		add_action('CMA_comments_answer', array(get_class(), 'displayAnswerComments'), 20, 2);
		add_filter('template_include', array(get_class(), 'overrideTemplate'));
		
	}
	
	
	public static function overrideTemplate($template) {
		if (self::_isPost()) self::_processPostRequest();
		return $template;
	}
	
	
	protected static function _processPostRequest() {
		switch (self::_getParam('cma-action')) {
// 			case 'comment-add-form':
// 				self::displayCommentFormAdd(self::_getParam('thread-id'), self::_getParam('answer-id'));
// 				exit;
// 				break;
			case 'comment-add':
				self::_processCommentAdd();
				break;
			case 'comment-edit':
				self::_processCommentEdit();
				break;
			case 'comment-delete':
				self::_processCommentDelete();
				break;
		}
	}
	

	protected static function _processCommentAdd() {
		$wp_query = self::$query;
		
		$response = array('success' => 0, 'msg' => CMA::__('An error occured.'));
		 
		$post = $wp_query->post;
		$thread = CMA_Thread::getInstance($post->ID);
		$content = self::_getParam('content');
		$answerId = self::_getParam('cma-answer-id');

		if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_comment')) {
			$error = CMA::__('Invalid nonce.');
		}
		else if (!CMA_Comment::canCreate()) {
			$error = CMA::__('You have to be logged-in.');
		}
		else if (empty($content)) {
			$error = CMA::__('Content cannot be empty.');
		}
		else if ($answerId) {
			$answer = CMA_Answer::getById($answerId);
			if (empty($answer)) {
				$error = CMA::__('Unknown answer.');
			}
		}
		
		header('content-type: application/json');
		
		if (empty($error)) {
			try {
				$comment = CMA_Comment::create($content, CMA::getPostingUserId(), $thread->getId(), $answerId);
				if (!$comment) throw new Exception(CMA::__('Failed to add comment.'));
				if ($comment->isApproved()) {
					$thread->setUpdated();
				}
				$msg = ($comment->isApproved()
					? CMA::__('Comment has been added.')
					: CMA::__('Thank you for your comment, it has been held for moderation.')
				);
				$html = ($comment->isApproved() ? self::_loadView('answer/comments/comment-single', compact('comment')) : null);
				$response = array('success' => 1, 'msg' => $msg, 'html' => $html);
			} catch (Exception $e) {
				$error = $e->getMessage();
			}
		}
		
		if (!empty($error)) {
			$response['msg'] = $error;
		}
		
		echo json_encode(apply_filters('cma_comment_add_ajax_response', $response));
		exit;
		 
	}
	
	

	protected static function _processCommentEdit() {
		
		header('content-type: application/json');
		$response = array('success' => 0, 'msg' => CMA::__('An error occured.'));
		$commentId = self::_getParam('cma-comment-id');
		if ($commentId AND $comment = CMA_Comment::getById($commentId) AND $comment->canEdit()) {
			$content = self::_getParam('content');
			if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_comment')) {
				$error = CMA::__('Invalid nonce.');
			}
			else if (empty($content)) {
				$response['error'] = CMA::__('Content cannot be empty.');
			} else {
				if ($comment->getContent() == apply_filters('comment_text', $content)) {
					$success = true;
				} else {
					$comment->setContent($content);
					$success = $comment->save();
				}
				if (!empty($success)) {
					$html = ($comment->isApproved() ? $comment->getContent() : null);
					$response = array('success' => 1, 'msg' => CMA::__('Comment has been saved.'), 'html' => $html);
				}
			}
		}
	
		echo json_encode(apply_filters('cma_comment_edit_ajax_response', $response));
		exit;
			
	}
	
	
	protected static function _processCommentDelete() {
		header('content-type: application/json');
		$response = array('success' => 0, 'msg' => CMA::__('An error occured.'));
		$commentId = self::_getParam('cma-comment-id');
		if (empty($_POST['nonce']) OR !wp_verify_nonce($_POST['nonce'], 'cma_comment_delete')) {
			$response['msg'] = CMA::__('Invalid nonce.');
		}
		else if ($commentId AND $comment = CMA_Comment::getById($commentId)) {
			$comment->trash();
			$response = array('success' => 1, 'msg' => CMA::__('Comment moved to trash.'));
		}
		echo json_encode(apply_filters('cma_comment_delete_ajax_response', $response));
		exit;
	}
	

	public static function displayQuestionComments($threadId) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_COMMENTS_QUESTION_ENABLE)) {
			self::displayComments($threadId, $answerId = 0);
		}
	}
	
	
	public static function displayCommentSingle($comment) {
		echo self::_loadView('answer/comments/comment-single', compact('comment'));
	}
	
	
	public static function displayCommentFormAdd($threadId, $answerId) {
		echo self::_loadView('answer/comments/comment-form', compact('threadId', 'answerId'));
	}
	
	
	
	public static function displayAnswerComments($threadId, $answerId) {
		if (CMA_Settings::getOption(CMA_Settings::OPTION_COMMENTS_ANSWER_ENABLE)) {
			self::displayComments($threadId, $answerId);
		}
	}
	
	
	protected static function displayComments($threadId, $answerId) {
		if (empty(self::$commentsCache[$threadId])) {
			self::$commentsCache[$threadId] = CMA_Comment::getCommentsByThread($threadId);
		}
		if (!empty(self::$commentsCache[$threadId][$answerId])) {
			$comments = self::$commentsCache[$threadId][$answerId];
		} else {
			$comments = array();
		}
		echo self::_loadView('answer/comments/comments-block', compact('comments', 'threadId', 'answerId'));
	}
	
	
	public static function hookSetStatus($commentId, $status) {
		// Update rating cache of the thread
		if ($answer = CMA_Answer::getById($commentId) AND $thread = $answer->getThread()) {
			$thread->updateRatingCache();
		}
	}
	
	
}
