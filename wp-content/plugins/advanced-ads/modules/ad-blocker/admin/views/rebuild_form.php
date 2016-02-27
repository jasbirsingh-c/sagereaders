<h3 class="title"><?php _e( 'Ad blocker file folder', 'advanced-ads' ); ?></h3>
<?php 
	$button_attrs = ( isset( $button_attrs ) ) ? $button_attrs : array();

	if ( ! empty( $message ) && isset( $success ) ): ?>
		<div class="<?php echo $success ? 'updated' : 'error'; ?> notice is-dismissible"><p><?php echo $message;?></p></div>
	<?php endif; 

	if ( ! $this->upload_dir ): ?>
		<p class="advads-error-message"><?php _e( 'Upload folder is not writable', 'advanced-ads' ); ?></p>
		<?php
	else: ?>
		<form method="post" action="">
			<input type="hidden" name="advads_ab_form_submit" value="true">
			<?php wp_nonce_field( 'advads_ab_form_nonce', 'security' );
			if ( ! empty( $this->options['folder_name'] ) && ! empty( $this->options['active_plugins_hash'] ) ): ?>
				<table class="form-table">
				<tbody>
					<?php
					$folder = trailingslashit( $this->upload_dir['basedir'] ) . $this->options['folder_name'];
					$url = trailingslashit( $this->upload_dir['baseurl'] ) . $this->options['folder_name']; ?>
					<tr>
						<th scope="row"><?php _e( 'Asset path', 'advanced-ads' ); ?></th>
						<td><?php echo $folder; ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Asset URL', 'advanced-ads' ); ?></th>
						<td><?php echo $url; ?></td>
					</tr>
					<tr>
						<th scope="row"><?php _e( 'Rename asset folder', 'advanced-ads' ); ?></th>
						<td>
							<input  type="checkbox" name="advads_ab_assign_new_folder">
							<p class="description"><?php _e( 'Check if you want to change the name of the assets folder', 'advanced-ads' ); ?></p> 
						</td>
					</tr>
				</tbody>
				</table>
				<?php
			else: ?>   
				<p><?php printf( __( 'Please, rebuild the asset folder. The new folder will be located in <strong>%s</strong>', 'advanced-ads' ), $this->upload_dir['basedir'] ); ?></p> 
				<?php
			endif;
			submit_button( __( 'Rebuild asset folder', 'advanced-ads' ), 'primary', 'submit', true, $button_attrs ); ?>
		</form>
		<?php
	endif; ?>