<?php
/**
 * The template for displaying Archive pages.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package activello
 */

get_header();


$curauth = (isset($_GET['author_name'])) ?
get_user_by('slug', $author_name) :
get_userdata(intval($author));

?>

	<section id="primary" class="content-area">
		<main id="main" class="site-main" role="main">

		<?php
			if(!in_array("photographer", $curauth->roles))
			{
		?>
			<?php if ( have_posts() ) : ?>
			<header class="page-header">
				<h1 class="page-title">
					<?php
						if ( is_category() ) :
							single_cat_title();

						elseif ( is_tag() ) :
							single_tag_title();

						elseif ( is_author() ) :
							printf( esc_html__( 'Author: %s', 'activello' ), '<span class="vcard">' . get_the_author() . '</span>' );
						?>
						<?php
						// If a user has filled out their description, show a bio on their entries.
						if ( get_the_author_meta( 'description' ) ) : ?>
						<div id="author-info">
							<div id="author-avatar">
								<?php echo get_avatar( get_the_author_meta( 'user_email' ), 150 ); ?>
							</div><!-- #author-avatar -->
							<div id="author-description">
								<h2><?php printf( __( 'About %s', 'activello' ), get_the_author() ); ?></h2>
								<?php the_author_meta( 'description' ); ?>
							</div><!-- #author-description	-->
						</div><!-- #author-info -->
						<?php endif; ?>						
						<?php 

						elseif ( is_day() ) :
							printf( esc_html__( 'Day: %s', 'activello' ), '<span>' . get_the_date() . '</span>' );

						elseif ( is_month() ) :
							printf( esc_html__( 'Month: %s', 'activello' ), '<span>' . get_the_date( _x( 'F Y', 'monthly archives date format', 'activello' ) ) . '</span>' );

						elseif ( is_year() ) :
							printf( esc_html__( 'Year: %s', 'activello' ), '<span>' . get_the_date( _x( 'Y', 'yearly archives date format', 'activello' ) ) . '</span>' );

						elseif ( is_tax( 'post_format', 'post-format-aside' ) ) :
							esc_html_e( 'Asides', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-gallery' ) ) :
							esc_html_e( 'Galleries', 'activello');

						elseif ( is_tax( 'post_format', 'post-format-image' ) ) :
							esc_html_e( 'Images', 'activello');

						elseif ( is_tax( 'post_format', 'post-format-video' ) ) :
							esc_html_e( 'Videos', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-quote' ) ) :
							esc_html_e( 'Quotes', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-link' ) ) :
							esc_html_e( 'Links', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-status' ) ) :
							esc_html_e( 'Statuses', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-audio' ) ) :
							esc_html_e( 'Audios', 'activello' );

						elseif ( is_tax( 'post_format', 'post-format-chat' ) ) :
							esc_html_e( 'Chats', 'activello' );

						else :
							esc_html_e( 'Archives', 'activello' );

						endif;
					?>
				</h1>
				<?php
					// Show an optional term description.
					$term_description = term_description();
					if ( ! empty( $term_description ) ) :
						printf( '<div class="taxonomy-description">%s</div>', $term_description );
					endif;
				?>
			</header><!-- .page-header -->

			<?php /* Start the Loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>

				<?php
					/* Include the Post-Format-specific template for the content.
					 * If you want to override this in a child theme, then include a file
					 * called content-___.php (where ___ is the Post Format name) and that will be used instead.
					 */
					get_template_part( 'content', get_post_format() );
				?>

			<?php endwhile; ?>

			<?php activello_paging_nav(); ?>

		<?php else : ?>

			<?php get_template_part( 'content', 'none' ); ?>

		<?php endif; ?>
		
		<?php
			}
			else
			{ ?>
				<header class="page-header">
					<h1 class="page-title">		
					<?php 
						printf( esc_html__( 'Author: %s', 'activello' ), '<span class="vcard">' . $author_name . '</span>' );
					?>
					<div id="author-info">
						<div id="author-avatar">
							<?php echo get_avatar( $curauth->ID, 150 ); ?>
						</div><!-- #author-avatar -->
						<div id="author-description">
							<h2><?php printf( __( 'About %s', 'activello' ), $author_name ); ?></h2>
							<?php echo $curauth->description; ?>
						</div><!-- #author-description	-->
					</div><!-- #author-info -->					
			<?php
			
				$args = array(
						'author' => $curauth->ID,
						'post_type' => 'snap'
				);
				
				$author_posts = new WP_Query( $args );
				if( $author_posts->have_posts() ) { ?>
					<h2>His snaps</h2>
						<div class="snaps-flexslider">
  							<ul class="slides">					
				<?php 
					while( $author_posts->have_posts()) {
						$author_posts->the_post();
						// title, content, etc
						?>
							<li>
								<img src="<?php echo get_post_meta(get_the_id(), 'snap', true )['guid']; ?>">
    						</li>						
						<?php 
						
						// you should have access to any of the tags you normally
						// can use in The Loop
					}
					?>
					</ul>
					</div>
					<script type="text/javascript">
						jQuery(window).load(function() {
							  jQuery('.snaps-flexslider').flexslider({
							    animation: "slide"
							  });
							});					
					</script>
					<?php 
					wp_reset_postdata();					
					
				}
			}
		?>

		</main><!-- #main -->
	</section><!-- #primary -->

<?php get_sidebar(); ?>
<?php get_footer(); ?>