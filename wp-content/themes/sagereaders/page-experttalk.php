<?php
/**
 *  Template Name: Expert talk
 *
 * This is the template that displays all pages by default.
 *
 * @package activello
 */

get_header(); ?>

	<div id="primary" class="content-area">

		<main id="main" class="site-main" role="main">

			<?php while ( have_posts() ) : the_post(); ?>

				<?php //get_template_part( 'content', 'page' ); ?>

				<?php
				$authors_info = get_users( array('role' => 'administrator') );
				//echo '<pre>';print_r($authors_info);die;
				foreach ($authors_info as $author_info)
				{
					?>
					<div class="author-box col-md-12 col-sm-12">
						<div class="col-md-3 col-sm-3">
							<a href="<?php echo get_author_posts_url( $author_info->ID); ?>"><?php echo get_avatar( $author_info->ID, 150) ?></a>
						</div>
						<div class="col-md-9 col-sm-9">
							<p><a href="<?php echo get_author_posts_url( $author_info->ID); ?>"><?php echo $author_info->display_name; ?></a></p>
							<p><?php echo $author_info->description; ?></p>
						</div>
					</div>
						<div class="clear"></div>
					<?php 					
				}
				
				?>
				
				<?php
				$authors_info = get_users( array('role' => 'author') );
				//echo '<pre>';print_r($authors_info);die;
				foreach ($authors_info as $author_info)
				{
					?>
					<div class="author-box col-md-12 col-sm-12">
						<div class="col-md-3 col-sm-3">
						<a href="<?php echo get_author_posts_url( $author_info->ID); ?>"><?php echo get_avatar( $author_info->ID, 150) ?></a>
						</div>
						<div class="col-md-9 col-sm-9">
							<p><a href="<?php echo get_author_posts_url( $author_info->ID); ?>"><?php echo $author_info->display_name; ?></a></p>
							<p><?php echo (strlen($author_info->description) > 200) ? substr($author_info->description, 0, 200).'...' : $author_info->description; ?></p>
						</div>
					</div>
						<div class="clear"></div>
					<?php 					
				}
				
				?>
				<?php
					// If comments are open or we have at least one comment, load up the comment template
					if ( get_theme_mod( 'activello_page_comments' ) == 1 ) :
						if ( comments_open() || '0' != get_comments_number() ) :
							comments_template();
						endif;
					endif;
				?>

			<?php endwhile; // end of the loop. ?>

		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>