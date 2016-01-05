<?php

require_once dirname(__FILE__) . '/SQLImport.php';

class CMA_OsqaImport extends CMA_SQLImport {
	
	protected $postIdOffset = 2000;
	protected $importedQuestions = 0;
	
	protected $users = false;
	protected $questions = false;
	protected $answers = false;
	protected $comments = false;
	
	
	function __construct($host, $database, $user, $pass) {
		parent::__construct(sprintf('mysql:host=%s;dbname=%s', $host, $database), $user, $pass);
		echo '<style type="text/css">
			div, em, strong {display: block;}
			div {color:#009900;}
			em {color:#666600;}
			strong {color:#990000;}
			</style>
			<script type="text/javascript">
			var autoscroll = function() { window.scrollBy(0, 10000); };
			var scrollInt = setInterval(autoscroll, 100);
			window.onmousewheel = function(event) {
				clearInterval(scrollInt);
			};
			</script>';
	}
	
	
	public function importUsers() {
		parent::importUsers();
		$this->users = array();
	}
	
	
	public function importQuestions($postIdOffset = 2000) {
		$this->postIdOffset = $postIdOffset;
		parent::importQuestions();
		$this->showSuccess(sprintf('Imported %d records.', $this->importedQuestions));
		$this->questions = array();
	}
	
	public function importAnswers() {
		parent::importAnswers();
		$this->answers = array();
	}
	
	public function importComments() {
		parent::importComments();
		$this->comments = array();
	}
	
	
	public function sendUsersPasswords() {
		global $wpdb;
		$blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
		$users = $wpdb->get_results("SELECT DISTINCT user_id FROM $wpdb->usermeta WHERE meta_key = 'cma_import_old_id'");
		foreach ($users as $user_id) {
// 	    	$plaintext_pass = wp_generate_password(15, true);
// 	    	$user = get_userdata( $user_id );
// 	    	wp_set_password($plaintext_pass, $user_id);
// 	    	$message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n";
// 	    	$message .= sprintf(__('Password: %s'), $plaintext_pass) . "\r\n";
// 	    	$message .= wp_login_url() . "\r\n";
// 	    	wp_mail($user->user_email, sprintf(__('[%s] Your username and password'), $blogname), $message);
	    	$this->showSuccess(sprintf('Password send to user %s', $user->user_login));
		}
	}
	
	
	protected function getUser() {
		return $this->getRecord('users',
			'SELECT
				u.id AS import_old_id,
				username AS user_login,
				username AS user_nicename,
				IFNULL(real_name, username) AS display_name,
				email AS user_email,
				date_joined AS user_registered,
				about AS description,
				website AS user_url
			FROM forum_user f
			INNER JOIN auth_user u ON f.user_ptr_id = u.id
			WHERE is_active = ?
			# LIMIT 10',
			array(1));
	}
	
	protected function getQuestion() {
// 		return false;
		return $this->getRecord('questions',
			'SELECT
				n.id AS import_old_id,
				title AS post_title,
				tagnames,
				author_id AS import_old_author_id,
				body as post_content,
				added_at AS post_date,
				added_at AS post_date_gmt,
				state_type
			FROM forum_node n
			LEFT JOIN forum_nodestate ns ON ns.node_id = n.id
			WHERE node_type = ?
				AND (state_type IS NULL OR state_type <> ?)
			# LIMIT 10
			',
			array('question', 'deleted'));
	}
	
	protected function getAnswer() {
		return $this->getRecord('answers',
			'SELECT
				n.id AS import_old_id,
				parent_id AS import_old_parent_id,
				author_id AS import_old_author_id,
				IFNULL(fu.real_name, u.username) AS comment_author,
				u.email AS comment_author_email,
				fu.website AS comment_author_url,
				body as comment_content,
				added_at AS comment_date,
				added_at AS comment_date_gmt
			FROM forum_node n
			LEFT JOIN forum_nodestate ns ON ns.node_id = n.id
			LEFT JOIN forum_user fu ON fu.user_ptr_id = n.author_id
			LEFT JOIN auth_user u ON u.id = fu.user_ptr_id
			WHERE node_type = ?
				AND (state_type IS NULL OR state_type <> ?)',
			array('answer', 'deleted'));
	}
	
	
	protected function getComment() {
		return $this->getRecord('comments',
			'SELECT
				n.id AS import_old_id,
				author_id AS import_old_author_id,
				IFNULL(fu.real_name, u.username) AS comment_author,
				u.email AS comment_author_email,
				fu.website AS comment_author_url,
				abs_parent_id AS import_old_abs_parent_id,
				parent_id AS import_old_parent_id,
				body as comment_content,
				added_at AS comment_date,
				added_at AS comment_date_gmt
			FROM forum_node n
			LEFT JOIN forum_nodestate ns ON ns.node_id = n.id
			LEFT JOIN forum_user fu ON fu.user_ptr_id = n.author_id
			LEFT JOIN auth_user u ON u.id = fu.user_ptr_id
			WHERE node_type = ?
				AND (state_type IS NULL OR state_type <> ?)',
			array('comment', 'deleted'));
	}
	
	
	protected function getRecord($varname, $sql, array $values = array()) {
		if ($this->$varname === false) {
			$st = $this->pdo->prepare($sql);
			$st->execute($values);
			$this->$varname = $st->fetchAll(PDO::FETCH_ASSOC);
			$st->closeCursor();
		}
		return array_shift($this->$varname);
	}
	
	
	protected function importUser(array $user) {
		global $wpdb;
		$existing = $wpdb->get_var("SELECT user_id FROM $wpdb->usermeta WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $user['import_old_id']);
		if ($existing) {
			$this->showWarning(sprintf('User %s with old ID %d already imported.', $user['user_login'], $user['import_old_id']));
			$id = $existing;
		} else {
			try {
				$id = parent::importUser($user);
				if ($id) {
					$this->showSuccess(sprintf('User %s with old ID %d imported.', $user['user_login'], $user['import_old_id']));
				} else {
					$this->showError(sprintf('Failed to import user %s with old ID %d.', $user['user_login'], $user['import_old_id']));
				}
			} catch (Exception $e) {
				$this->showError($e);
			}
		}
		return $id;
	}
	
	
	protected function importQuestion(array $question) {
		global $wpdb;
		$thread = false;
		$existing = $wpdb->get_var("SELECT post_id FROM $wpdb->postmeta WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $question['import_old_id']);
		if ($existing) {
			$this->showWarning(sprintf('Question with old ID %d already imported.', $question['import_old_id']));
			$id = $existing;
			$thread = CMA_Thread::getInstance($id);
		} else {
			try {
				if ($thread = parent::importQuestion($question) AND $thread instanceof CMA_Thread) {
					$thread->setResolved($question['state_type'] == 'closed');
					if (!empty($question["tagnames"])) {
						$this->showSuccess(sprintf('Question with old ID %d imported.', $question['import_old_id']));
						wp_set_post_tags($thread->getId(), str_replace(" ", ',', $question["tagnames"]), true);
						$this->importedQuestions++;
					}
				} else {
					$this->showError(sprintf('Failed to import question with old ID %d', $question['import_old_id']));
				}
			} catch (Exception $e) {
				$this->showError($e);
			}
		}
		return $thread;
	}
	
	
	protected function importAnswer(array $answer) {
		global $wpdb;
		$result = false;
		$existing = $wpdb->get_var("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $answer['import_old_id']);
		if ($existing) {
			$this->showWarning(sprintf('Answer with old ID %d already imported.', $answer['import_old_id']));
			$result = $existing;
		} else {
			try {
				if ($result = parent::importAnswer($answer)) {
					$this->showSuccess(sprintf('Answer with old ID %d imported.', $answer['import_old_id']));
				} else {
					$this->showError(sprintf('Failed to import answer with old ID %d', $answer['import_old_id']));
				}
			} catch (Exception $e) {
				$this->showError($e);
			}
		}
		return $result;
	}
	

	protected function importComment(array $comment) {
		global $wpdb;
		$result = false;
		$existing = $wpdb->get_var("SELECT comment_id FROM $wpdb->commentmeta WHERE meta_key = 'cma_import_old_id' AND meta_value = ". $comment['import_old_id']);
		if ($existing) {
			$this->showWarning(sprintf('Comment with old ID %d already imported.', $comment['import_old_id']));
			$result = $existing;
		} else {
			try {
				if ($result = parent::importComment($comment)) {
					$this->showSuccess(sprintf('Comment with old ID %d imported.', $comment['import_old_id']));
				} else {
					$this->showError(sprintf('Failed to import comment with old ID %d', $comment['import_old_id']));
				}
			} catch (Exception $e) {
				$this->showError($e);
			}
		}
		return $result;
	}
	
	
	protected function showSuccess($msg) {
		echo '<div>'. $msg .'</div>';
		flush();
	}
	
	protected function showWarning($msg) {
		echo '<em>'. $msg .'</em>';
		flush();
	}
	
	protected function showError($msg) {
		if (is_object($msg) AND $msg instanceof Exception) $msg = $msg->getMessage();
		echo '<strong>'. $msg .'</strong>';
		flush();
	}
	
	
}