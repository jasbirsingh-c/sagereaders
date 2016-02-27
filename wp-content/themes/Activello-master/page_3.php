<?php
/**
 * @package activello
 */
?>

<!-- page 3 starts -->
<div>
	<h4>Page 3</h4>
	<?php
			$params = array(
					'orderby' => 't.id DESC',
					'limit'   => 1
			);
			
			// Create and find in one shot
			$page_3 = pods( 'page 3', $params );
			if ( 0 < $page_3->total() )
			{ 
        		while ( $page_3->fetch() )
        		{
        			echo '<span class="page-3-title">'.$page_3->display( "title" ).'</span>';
        			echo '<div>';
        			echo '<div class="page-3-desc">'.$page_3->display( 'description_excerpt' ).'</div>';
        			if($page_3->display( 'media_type' ) == 'audio')
        			{
        				?>
				<audio controls>
				  <source src="<?php echo $page_3->display( 'audio' ); ?>">
				  Your browser does not support the audio tag.
				</audio>
				<?php         				
        			}
        			elseif ($page_3->display( 'media_type' ) == 'video')
        			{ ?>
				<video width="320" height="240" controls>
				  <source src="<?php echo $page_3->display( 'video' ); ?>">
						  Your browser does not support the video tag.
						</video>        			
        			<?php         				
        			}
        			echo '<div class="clear"></div></div>';
        		}
			}
			?>
</div>