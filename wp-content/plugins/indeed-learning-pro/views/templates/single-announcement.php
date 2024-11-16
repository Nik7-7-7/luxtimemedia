<?php

get_header();
global $post;
?>

<div class="page-content single-announcement-wrapper">
	<div id="primary" class="content-area">
		<main id="main" class="site-main" role="main">


        <div class="single-announcement-top">
        	<div class="single-announcement-author-image">
            	<img src="<?php echo DbUlp::getAuthorImage($post->post_author);?>" />
            </div>
            <div class="single-announcement-author-title">
            	<h3><?php echo esc_ulp_content($post->post_title);?></h3>
                <div class="single-announcement-details">
                	<span class="single-announcement-author"><?php echo DbUlp::getUserFulltName($post->post_author).' '; ?></span>
                    <span class="single-announcement-time"><?php esc_html_e('posted', 'ulp'); echo esc_html(' '); echo indeed_time_elapsed_string($post->post_date);?></span>,
                    <span class="single-announcement-no-comments"><i class="fa-ulp fa-no-comments-ulp"></i> <?php echo esc_html($post->comment_count).' '; esc_html_e('Comments', 'ulp');?></span>
                </div>
            </div>
            <div class="ulp-clear"></div>
        </div>
        <div class="single-announcement-content"><?php echo esc_ulp_content($post->post_content);?></div>

				<div class="ulp-announcement-course-link">
					<div><?php esc_html_e('Back to', 'ulp');?></div>
					<a href="<?php echo add_query_arg(['subtab' => 'announcements'], \DbUlp::getCoursePermalinkForAnnouncement($post->ID));?>"><?php echo DbUlp::getCourseNameForAnnouncement($post->ID);?></a>
				</div>

        <?php comments_template();?>

		</main><!-- #main -->
	</div><!-- #primary -->

</div><!-- .wrap -->

<?php get_footer();
