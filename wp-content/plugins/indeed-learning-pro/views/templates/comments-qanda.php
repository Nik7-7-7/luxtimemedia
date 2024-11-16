<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( post_password_required() ) {
	return;
}
			$postId = get_the_ID();

			$object = new \Indeed\Ulp\Db\QandA();
			$course_id =$object->getCourseIdByQanda($postId);

        	$UlpCourse = new \UlpCourse($course_id);

			$authors[] = str_replace('@', '',str_replace('.', '-',$UlpCourse->AuthorName()));
			$instructors = $UlpCourse->Additional_Instructors();
			if($instructors){
				foreach($instructors as $object){
					$user_info = get_userdata($object);
					$authors[] = str_replace('@', '',str_replace('.', '-',$user_info->user_login));
				}
			}

?>
<div class="ulp-comments-wrapper">

	<?php
	if ( have_comments() ) : ?>


		<?php

		if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav id="comment-nav-above" class="comment-navigation" role="navigation">
				<!--h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'ulp' ); ?></h2-->
				<div class="nav-previous"><?php previous_comments_link( esc_html__( '&larr; Previous', 'ulp' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Next &rarr;', 'ulp' ) ); ?></div>
			</nav>
		<?php endif; ?>

		<?php
		$custom_css = '';
		foreach($authors as $k=>$array){
			$custom_css .=  '.comment-author-'.$array;
			if ($k < count($authors)-1 ){
				 	$custom_css .=  ', ';
			}
		}
		$custom_css .=  '{
			background-color:#f4f8f9;
		}';

		wp_register_style( 'dummy-handle', false );
		wp_enqueue_style( 'dummy-handle' );
		wp_add_inline_style( 'dummy-handle', $custom_css );

		 ?>
		<div class="ulp-comment-list">
			<?php
			wp_list_comments( array(
					'avatar_size' => 40,
					'style'       => 'div',
					'short_ping'  => true,
    'format'      => 'html5',
					'reply_text'  => '',)
					);
			?>
		</div>

		<?php if ( get_comment_pages_count() > 1 && get_option( 'page_comments' ) ) : ?>
			<nav id="comment-nav-below" class="comment-navigation" role="navigation">
				<!--h2 class="screen-reader-text"><?php esc_html_e( 'Comment navigation', 'ulp' ); ?></h2-->
				<div class="nav-previous"><?php previous_comments_link( esc_html__( '&larr; Previous', 'ulp' ) ); ?></div>
				<div class="nav-next"><?php next_comments_link( esc_html__( 'Next &rarr;', 'ulp' ) ); ?></div>
			</nav>
		<?php endif;

	endif;

	if ( ! comments_open() && '0' != get_comments_number() && post_type_supports( get_post_type(), 'comments' ) ) : ?>
		<p class="ulp-no-comments"><?php esc_html_e( 'Comments are closed.', 'ulp' );?></p>
	<?php endif;

	$commenter = wp_get_current_commenter();

	$fields = array(
		'author' => '<label for="author" class="screen-reader-text">' . esc_html__( 'Name', 'ulp' ) . '</label><input placeholder="' . esc_attr__( 'Name', 'ulp' ) . ' *" id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" />',
		'email' => '<label for="email" class="screen-reader-text">' . esc_html__( 'Email', 'ulp' ) . '</label><input placeholder="' . esc_attr__( 'Email', 'ulp' ) . ' *" id="email" name="email" type="email" value="' . esc_attr( $commenter['comment_author_email'] ) . '" size="30" />',
		'url' => '<label for="url" class="screen-reader-text">' . esc_html__( 'Website', 'ulp' ) . '</label><input placeholder="' . esc_attr__( 'Website', 'ulp' ) . '" id="url" name="url" type="url" value="' . esc_attr( $commenter['comment_author_url'] ) . '" size="30" />',
	);

	$defaults = array(
		'fields'		=> apply_filters( 'comment_form_default_fields', $fields ),
		'comment_field' => '<p class="comment-form-comment"><label for="comment" class="screen-reader-text">' . esc_html__( 'Comment', 'ulp' ) . '</label><textarea id="comment" name="comment" cols="45" rows="1" placeholder="Enter your Reply" aria-required="true"></textarea></p>',
		'comment_notes_before' => null,
		'comment_notes_after'  => null,
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => '',
		'label_submit'         => apply_filters( 'generate_post_comment', esc_html__( 'Post Reply', 'ulp' ) ),
	);
?>
	<div class="ulp-comment-submission">
	<div class="ulp-comment-avatar">
		<img src="<?php echo esc_url( get_avatar_url(get_current_user_id(),array('size'=>40)));?>"/>
	</div>
    <?php
	comment_form( $defaults );
	?>
	</div>
</div>
