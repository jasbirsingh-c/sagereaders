<?php error_reporting(-1);
/**
 * The main template file.
 *
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being style.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists.
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package activello
 */

get_header(); ?>

	<div id="primary" class="content-area">
                <?php $paged = (get_query_var('paged')) ? get_query_var('paged') : 1; ?>

		<main id="main" class="site-main <?php echo "page-".$paged;?> role="main">
		<?php if ( have_posts() ) : ?>

			<div class="article-container">
			
			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php get_template_part( 'content', '' ); ?>

			<?php endwhile; ?>
			
			<?php
			//authors post
				$author_ids = get_users( array('role' => 'author' ,'fields' => 'ID') );
				$query = new WP_Query( array( 'author' => implode(",", $author_ids), 'posts_per_page' => 1) ); 
			?>			
			<?php while ( $query->have_posts() ) : $query->the_post(); ?>

				<?php get_template_part( 'content', '' ); ?>

			<?php endwhile; ?>			
			
			</div>
			
			<?php //activello_paging_nav(); ?>

		<?php else : ?>

			<?php get_template_part( 'content', 'none' ); ?>

		<?php endif; ?>

		<?php get_template_part( 'page_3', '' ); ?>
		
		<div id="add_above_footer">
			<img alt="" src="<?php echo get_template_directory_uri().'/inc/images/ad_728x90.png'; ?>">
		</div>
		
		</main><!-- #main -->
	</div><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>