					<?php  
						wp_dropdown_categories( array( 
							'name'          => 'question-category',
							'id'            => 'question-category',
							'taxonomy'      => 'dwqa-question_category',
							'show_option_none' => __( 'Select question category', 'dwqa' ),
							'hide_empty'    => 0,
							'quicktags'     => array( 'buttons' => 'strong,em,link,block,del,ins,img,ul,ol,li,code,spell,close' ),
							'selected'      => isset( $_POST['question-category'] ) ? esc_html( $_POST['question-category'] ) : false,
						) );
					?>