<?php

abstract class CMA_Attachment {
	
	const UPLOAD_PATH = 'cma_attachments';
	
	protected $post;
	
	
	
	public function __construct($post) {
		$this->post = (object)$post;
	}
	
	
	public static function select($threadId, $include = null) {
		$args = array(
				'post_type'      => 'attachment',
				'numberposts'    => null,
				'post_status'    => null,
				'post_parent'    => $threadId,
				'orderby'        => 'ID',
				'order'          => 'ASC',
				'posts_per_page' => 20,
		);
		
		if (!empty($include)) $args['include'] = $include;
		$attachments = get_posts($args);
		$result = array();
		if( $attachments ) {
			foreach($attachments as $attachment) {
				$result[] = new static($attachment);
			}
		}
		return $result;
	}
	
	
	
	public static function handleUpload($threadId) {
		$attachments = array();
		if( !empty($_FILES['attachment']) && is_array($_FILES['attachment']) ) {
			foreach ($_FILES['attachment']['name'] as $i => $name) {
				$attachments[] = self::addUploadedFile($threadId,
						$name,
						$_FILES['attachment']['tmp_name'][$i],
						$_FILES['attachment']['type'][$i],
						$_FILES['attachment']['size'][$i]);
			}
		}
		return $attachments;
	}
	
	
	public static function addUploadedFile($threadId, $name, $tmpName, $type, $size) {
		$name = sprintf('%d', time()) . '_' . sanitize_file_name($name);
		$target = self::getUploadPath() . $name;
		if( move_uploaded_file($tmpName, $target) )
		{
			$wp_filetype = $type;
	
			$attachment = array(
					'guid'           => $target,
					'post_mime_type' => $wp_filetype,
					'post_title'     => urldecode(sanitize_title_with_dashes($name)),
					'post_content'   => '',
					'post_status'    => 'inherit'
			);
			$attach_id = wp_insert_attachment($attachment, $target, $threadId);
			// you must first include the image.php file
			// for the function wp_generate_attachment_metadata() to work
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata($attach_id, $target);
			wp_update_attachment_metadata($attach_id, $attach_data);
	
			return $attach_id;
			
		}
		
	}
	

	public static function getUploadPath()
	{
		$uploadDir = wp_upload_dir();
		$baseDir = $uploadDir ['basedir'] . '/' . self::UPLOAD_PATH . '/';
		if( !file_exists($baseDir) ) mkdir($baseDir);
		return $baseDir;
	}
	
	
	public function getId() {
		return $this->post->ID;
	}
	
	
	public function getThreadId() {
		return $this->post->parent_post_ID;
	}
	
	
	public function getName() {
		return apply_filters('the_title', $this->post->post_title);
	}
	
	
	public function isImage() {
		$type = explode('/', $this->post->post_mime_type);
		return (reset($type) == 'image');
	}
	
	
	public function getImage($size = 'thumbnail', $icon = true, $atts = array()) {
		if ($this->isImage()) {
			return wp_get_attachment_image($this->getId(), $size, $icon, $atts);
		}
	}
	
	
	public function getThumb($size = 'thumbnail', $icon = true, $atts = array()) {
		$size = CMA_Settings::getOption(CMA_Settings::OPTION_EMBED_ATTACHED_IMAGES_SIZE);
		$atts['style'] = sprintf('max-width:%dpx;max-height:%dpx;', $size, $size);
		return $this->getImage($size, $icon, $atts);
	}
	
	
	public function getURL() {
		return wp_get_attachment_url($this->getId());
	}
	
	
}