<?php
class Advanced_Ads_Ad_Blocker_Admin
{
	/**
	 * Singleton instance of the plugin
	 *
	 * @var     Advanced_Ads_Ad_Blocker_Admin
	 */
	protected static $instance;

	/**
	 * module options
	 *
	 * @since   1.0.0
	 * @var     array (if loaded)
	 */
	protected $options;

	/**
	 * all assets from advanced-ads plugins
	 *
	 * @since   1.0.0
	 * @var     array (filled when searching for assets)
	 */
	protected $assets = array();

	/**
	 * pattern to search assets using preg_match
	 *
	 * @var     string
	 */
	// the string does not contain 'vendor/composer' or '/admin/' and ends with .css/.js/.png/.gif
	protected $search_pattern = '/^(?!.*(vendor\/composer|\/admin\/)).*\.(css|js|png|gif)$/';

	/**
	 * Array, containing path information on the currently configured uploads directory
	 *
	 * @var     array
	 */
	protected $upload_dir;

	/**
	 * Error messages for user
	 *
	 * @var     WP_Error
	 */
	protected $error_messages;

	/**
	 * path where the rebuild_form is located
	 *
	 * @var     string
	 */
	private $form_url = 'admin.php?page=advanced-ads-settings#top#general';

	/**
	 * Initialize the module
	 *
	 * @since   1.0.0
	 */
	public function __construct()
	{
		// Get the most recent options values
		$this->options = Advanced_Ads_Ad_Blocker::get_instance()->options();

		$this->upload_dir = Advanced_Ads_Ad_Blocker::get_instance()->get_upload_directory();

		// add module settings to Advanced Ads settings page
		add_action( 'advanced-ads-settings-init', array( $this, 'settings_init' ), 9, 1 );
		// add rebuild asset form
		add_filter( 'advanced-ads-settings-tab-after-form', array( $this, 'add_asset_rebuild_form_wrap' ) );

		add_filter( "pre_update_option_" . ADVADS_AB_SLUG, array( $this, 'sanitize_settings' ), 10, 2 );

		$this->error_messages = new WP_Error();
	}

	/**
	 * Return an instance of Advanced_Ads_Ad_Blocker
	 *
	 * @since   1.0.0
	 * @return  Advanced_Ads_Ad_Blocker_Admin
	 */
	public static function get_instance()
	{
		// If the single instance hasn't been set, set it now.
		if (null === self::$instance)
		{
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * Render the ad-blocker rebuild assets form wrapper with rebuild assets form inside
	 *
	 * @param str $_setting_tab_id - id of the tab
	 */
	public function add_asset_rebuild_form_wrap( $_setting_tab_id  ) { 
		if ( $_setting_tab_id == 'general' ) {
			$advads_options = Advanced_Ads::get_instance()->options();
			$use_adblocker = isset( $advads_options['use-adblocker'] );
			?>
			<div id="advads-adblocker-wrapper" <?php if ( ! $use_adblocker ) { echo 'style="display:none"'; } ?>>
				<?php 
					// not ajax yet, print the form 
					$button_attrs = array( 'disabled' => 'disabled', 'autocomplete' => 'off' );
					include ADVADS_AB_BASE_PATH . 'admin/views/rebuild_form.php';
				?>
			</div>
			<?php
		}
	}

	/**
	 * Render the ad-blocker rebuild assets form
	 *
	 */
	public function add_asset_rebuild_form() {
		$success = false;
		$message = '';
		// we already have similar check in the ad_ajax_callback.php, but this check is necessary if JS is disabled
		if ( isset( $_POST['advads_ab_form_submit'] ) ) { //new submission
			if ( false === ( $output = $this->process_form( $this->form_url ) ) ) {
				//we are displaying credentials form - no need for further processing
				return;
			} elseif ( is_wp_error( $output ) ) {
				$success = false;
				$message = $output->get_error_message();
			} else {
				$success = true;
				$message = __( 'The asset folder was rebuilt successfully', 'advanced-ads' );
			}

			include ADVADS_AB_BASE_PATH . 'admin/views/rebuild_form.php';
		} 
	}

	/**
	 * Perform processing of the rebuild_form, sent by user
	 *
	 * @param str $form_url - URL of the page to display request form
	 * @return true on success, false in case of wrong credentials, WP_Error in case of error
	 **/
	function process_form( $form_url ){
		check_ajax_referer( 'advads_ab_form_nonce', 'security' );

		global $wp_filesystem;
		//fields that should be preserved across screens (when ftp/ssh login screen appears)
		$preserved_form_fields = array( 'advads_ab_form_submit', 'advads_ab_assign_new_folder', 'security', '_wp_http_referer' ); 
		//leave this empty to perform test for 'direct' writing
		$method = '';
		//target folder
		$context = $this->upload_dir['basedir'];
		//page url with nonce value
		$form_url = wp_nonce_url( $form_url, 'advads_ab_form_nonce', 'security' ); 

		if ( ! $this->filesystem_init( $form_url, $method, $context, $preserved_form_fields ) ) {
			return false; //stop further processing when request form is displaying
		}
		// at this point we do not need ftp/ssh credentials anymore
		$form_post_fields = array_intersect_key( $_POST, array_flip( array( 'advads_ab_assign_new_folder' ) ) );

		$this->create_dummy_plugin( $form_post_fields ); 

		if ( $error_messages = $this->error_messages->get_error_messages() ) {
			foreach ( $error_messages as $error_message ) {
				error_log( __METHOD__ . ': ' . $error_message );
			}

			return $this->error_messages; // WP_Error object
		}

		return true;
	}

	/**
	 * Initialize Filesystem object
	 *
	 * @param str $form_url - URL of the page to display request form
	 * @param str $method - connection method
	 * @param str $context - destination folder
	 * @param array $fields - fileds of $_POST array that should be preserved between screens
	 * @return bool - false on failure, stored text on success
	 *
	 **/
	function filesystem_init( $form_url, $method, $context, $fields = null ) {
		global $wp_filesystem;

		// first attempt to get credentials
		if ( false === ( $creds = request_filesystem_credentials( $form_url, $method, false, $context, $fields ) ) ) {
			// we don't have credentials, so the request for them is displaying, no need for further processing
			return false;
		}

		// now we got some credentials - try to use them       
		if ( ! WP_Filesystem( $creds ) ) {
			// incorrect connection data - ask for credentials again, now with error message
			request_filesystem_credentials( $form_url, $method, true, $context, $fields  );
			return false;
		}

		return true; //filesystem object successfully initiated
	}

	/**
	 * Return a hash based on the folder name and the version of the currently activated plugins
	 *
	 * @return  md5 hash
	 */
	public function generate_plugins_hash() {
		// Check if get_plugins() function exists. This is required on the front end of the
		// site, since it is in a file that is normally only loaded in the admin.
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}
		// Get all plugins that are installed
		$all_plugins = get_plugins();
		// Get all activated plugins
		$active_plugins = get_option('active_plugins');
		// Set up an array to hold all active Advanced Ads plugins
		$active_advads_plugins = array();

		// Loop through all active plugins
		foreach( $active_plugins as $plugin )
		{
			// Check if the current plugin is an Advanced Ads plugin
			if( strpos( $plugin, 'advanced-ads' ) !== false )
			{
				// Get the folder name of this plugin
				$folder = explode( '/', $plugin );
				// Make an array holding both the folder name and the version of the currently installed plugin
				$temp_plugin_data = array(
					'name'  => $folder[0],
					'version'   => $all_plugins[ $plugin ]['Version']
				);
				// Add it to the array
				$active_advads_plugins[] = $temp_plugin_data;
			}
		}

		return md5( serialize( $active_advads_plugins ) );
	}

	/**
	 * Add settings to settings page
	 *
	 * @since   1.0.0
	 * @param   string $hook settings page hook
	 */
	public function settings_init( $hook )
	{
		//register_setting( ADVADS_AB_SLUG, ADVADS_AB_SLUG, array( $this, 'sanitize_settings' ) );

		add_settings_field(
			'use-adblocker',
			__( 'Ad blocker fix', 'advanced-ads' ),
			array( $this, 'render_settings_use_adblocker' ),
			$hook,
			'advanced_ads_setting_section'
		);
	}

	/**
	 * Catch the update options before it's submitted to the database
	 *
	 * @param array $new_options new values that need to be saved
	 * @param array $old_options old values saved in database
	 * @return array  - options to save
	 */
	public function sanitize_settings( $new_options, $old_options ) {
		if ( is_array( $new_options ) ) {
			$this->options = array_merge( $this->options, $new_options );
			// Error, disable the ad-blocker script
			if ( ! isset( $new_options['active_plugins_hash'] ) ) {
				unset( $this->options['active_plugins_hash'] );
			}
		}

		return $this->options;
	}	


	/**
	 * Creates dummy plugin and return new options, that need to be stored in database
	 *
	 * @param   array $form_post_fields options, POST data sent by user
	 * @return  array $new_options - options, that need to be stored in database
	 */
	public function create_dummy_plugin( $form_post_fields ) {
		global $wp_filesystem;

		$need_assign_new_name = isset( $form_post_fields['advads_ab_assign_new_folder'] );

		if ( ! $this->upload_dir ) {
			$message = __( 'There is no writable upload folder', 'advanced-ads' );
			$this->error_messages->add( 'create_dummy_1', $message);
			return false;
		}

		$new_options = $new_options_error = array();
		// Create a new hash to check against.
		// $new_options_error does not have the 'active_plugins_hash' key - ad-blocker script will be inactive and the asset folder will be rebuilt next time
		$new_options['active_plugins_hash'] = $this->generate_plugins_hash(); 

		if ( ! empty( $this->options['folder_name'] ) ) {
			$new_options['folder_name'] = $new_options_error['folder_name'] = $this->options['folder_name'];

			$old_folder_normalized = $this->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) . $this->options['folder_name'] );

			if ( $wp_filesystem->exists( $old_folder_normalized ) ) {

				if ( $need_assign_new_name ) {
					$new_folder_name = $this->generate_unique_name( array( $new_options['folder_name'] ) );
					$new_folder_normalized = $this->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) . $new_folder_name );

					if ( ! $wp_filesystem->move( $old_folder_normalized, $new_folder_normalized ) ) {
						$message = sprintf( __( 'Unable to rename "%s" directory', 'advanced-ads' ), $old_folder_normalized );
						$this->error_messages->add( 'create_dummy_2', $message);
						return false;
					}
					$new_options['folder_name'] = $new_options_error['folder_name'] = $new_folder_name;

				} 

				$is_rebuild_needed = ! isset( $this->options['active_plugins_hash'] ) || $this->options['active_plugins_hash'] != $new_options['active_plugins_hash'];
				// we have an error while the method is being executed
				update_option( ADVADS_AB_SLUG, $new_options_error );					
			   
				if ( $is_rebuild_needed ) {
					$lookup_table = $this->copy_assets( $new_options['folder_name'], true ); 
					if ( ! $lookup_table ) {
						$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
						$this->error_messages->add( 'create_dummy_3', $message);                        
						return false;
					}
					$new_options['lookup_table'] = $lookup_table;
				}
				
			} else {
				// we have an error while the method is being executed
				update_option( ADVADS_AB_SLUG, $new_options_error );	
				// old folder does not exist, let's create it 
				$lookup_table = $this->copy_assets( $new_options['folder_name'] ); 
				if ( ! $lookup_table ) {
					$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
					$this->error_messages->add( 'create_dummy_4', $message);                    
					return false;
				}
				$new_options['lookup_table'] = $lookup_table;
			}
		} else {
			// It seems this is the first time this plugin was ran, let's create everything we need in order to
			// have this plugin function normally.
			$new_folder_name = $this->generate_unique_name();
			// Create a unique folder name
			$new_options['folder_name'] = $new_options_error['folder_name'] = $new_folder_name;
			// we have an error while the  method is being executed
			update_option( ADVADS_AB_SLUG, $new_options_error );
			// Copy the assets
			$lookup_table = $this->copy_assets( $new_options['folder_name'] ); 
			if ( ! $lookup_table ) {
				$message = sprintf( __( 'Unable to copy assets to the "%s" directory', 'advanced-ads' ), $new_options['folder_name'] );
				$this->error_messages->add( 'create_dummy_5', $message);                    
				return false;
			}
			$new_options['lookup_table'] = $lookup_table;
		}
		// successful result, save options and rewrite previous error options
		update_option( ADVADS_AB_SLUG, $new_options);
	}

	/**
	 * Copy all assets (JS/CSS) to the magic directory
	 *
	 * @since   1.0.0
	 * @param   string $folder_name destination folder
	 * @return  bool/array  - bool false on failure, array lookup table on success
	 */
	public function copy_assets( $folder_name, $rebuild = false ) {
		global $wp_filesystem;

		// Are we completely rebuilding the assets folder?
		$normalized_asset_path = $this->normalize_path( trailingslashit( $this->upload_dir['basedir'] ) . $folder_name );
		$asset_path = trailingslashit( $this->upload_dir['basedir'] ) . $folder_name ;
		if ( $rebuild ) {
			// Check if there is a previous asset folder
			if ( $wp_filesystem->exists( $normalized_asset_path ) ) {
				// Remove the old directory and its contents
				if ( ! $wp_filesystem->rmdir( trailingslashit( $normalized_asset_path ), true ) ) {
					$message = sprintf( __( 'We do not have direct write access to the "%s" directory', 'advanced-ads' ), $normalized_asset_path );
					$this->error_messages->add( 'copy_assets_1', $message);
					return false;
				}
			}
		}

		if ( ! $wp_filesystem->exists( $normalized_asset_path ) ) {
			if ( ! $wp_filesystem->mkdir( $normalized_asset_path ) ) {
				$message = sprintf( __( 'We do not have direct write access to the "%s" directory', 'advanced-ads' ), $this->upload_dir['basedir'] );
				$this->error_messages->add( 'copy_assets_2', $message);
				return false;
			}
		}

		$this->recursive_search( trailingslashit( WP_PLUGIN_DIR ) . 'advanced-ads*', $this->search_pattern );

		if ( ! $this->assets ) {
			$message = __( 'There are no assets to copy', 'advanced-ads' );
			$this->error_messages->add( 'copy_assets_3', $message);
			return false;
		}

		// contains associations between the original path of the asset and it path within our magic folder
		// i.e: [advanced-ads-layer/admin/assets/css/admin.css] => /12/34/56/78/1347107783.css
		$lookup_table = array();
		/* Do not rename directories. If, for example, some library uses in file.css something like this:
		'background: url(/img/image.png)', you should add 'img') to this array */		
		$not_rename_dirs = array( 'public', 'assets', 'js', 'css', 'fancybox' );
		//do not rename this files
		$not_rename_files = array( 'advanced.js', 'jquery.fancybox-1.3.4.css' );

		$rand_file_names = array();
		$rand_folder_names = array();
		// Loop through all the found assets
		foreach( $this->assets as $file ) {
			if ( ! file_exists( $file ) ) {
				continue;
			}

			$first_cleanup = str_replace( WP_PLUGIN_DIR , '', $file );
			$first_cleanup_dir = dirname( $first_cleanup );
			$first_cleanup_filename = basename( $first_cleanup );
			$first_cleanup_file_extension = pathinfo( $first_cleanup, PATHINFO_EXTENSION );
			$path_components = preg_split('/\//', $first_cleanup_dir, -1, PREG_SPLIT_NO_EMPTY);
			$path_components_new = array();

			foreach ( $path_components as $k => $dir ) {
				if ( in_array( $dir, $not_rename_dirs ) ) {
					$path_components_new[ $k ] = $dir;
				} elseif ( array_key_exists( $dir, $rand_folder_names ) ) {
					$path_components_new[ $k ] = $rand_folder_names[ $dir ];
				} else {
					$new_rand_folder_name = $this->generate_unique_name( $rand_folder_names );
					$path_components_new[ $k ] = $new_rand_folder_name;
					$rand_folder_names[ $dir ] = $new_rand_folder_name;
				}
			}

			$new_dir_full = trailingslashit( $asset_path ) . trailingslashit( implode( '/', $path_components_new ) );
			$new_dir = trailingslashit( implode( '/', $path_components_new ) );
			if ( ! in_array( $first_cleanup_filename, $not_rename_files ) && ( $first_cleanup_file_extension == 'js' || $first_cleanup_file_extension == 'css' ) ) {
				$new_filename = $this->generate_unique_name( $rand_file_names ) . '.' . $first_cleanup_file_extension;
				$rand_file_names[] = $new_filename; 
				$new_abs_file = $new_dir_full . $new_filename;
				$new_rel_file = $new_dir . $new_filename;
			} else {
				$new_abs_file = $new_dir_full . $first_cleanup_filename;
				$new_rel_file = $new_dir . $first_cleanup_filename;
			}

			if ( ! file_exists( $new_dir_full ) ) {
				// Create the path if it doesn't exist (prevents the copy() function from failing)
				if ( ! wp_mkdir_p( $new_dir_full ) ) {
					$message = sprintf( __( 'Unable to create "%s" directory. Is its parent directory writable by the server?', 'advanced-ads' ), $asset_path );
					$this->error_messages->add( 'copy_assets_4', $message);
					return false;
				}
			}

			$file = $this->normalize_path( $file );
			$new_abs_file = $this->normalize_path( $new_abs_file );

			// Copy the file to our new magic directory
			if ( ! $wp_filesystem->copy( $file, $new_abs_file, false, FS_CHMOD_FILE ) ) {
				$message = sprintf( __( 'Unable to copy files to %s', 'advanced-ads' ), $normalized_asset_path );
				$this->error_messages->add( 'copy_assets_5', $message);
				return false;
			}

			$lookup_table[ $first_cleanup ] = $new_rel_file;
		}
		return $lookup_table;
	}

	/**
	 * This function will recursively search for files with a certain extension within a given directory
	 *
	 * @since   1.0.0
	 * @param   string $dir The directory to search in
	 * @param   string $pattern pattern for preg_match function
	 */
	public function recursive_search( $dir, $pattern )
	{
		$tree = glob( rtrim( $dir, '/' ) . '/*' );
		if ( is_array( $tree ) )
		{
			foreach( $tree as $file )
			{
				if ( is_dir( $file ) )
				{
					$this->recursive_search( $file, $pattern );
				}
				elseif ( is_file( $file ) )
				{
					if ( preg_match( $pattern, $file ) )
					{
						$this->assets[] = $file;
					}
				}
			}
		}
	}

	/**
	 * Replaces the 'direct' absolute path with the Filesystem API path. Useful only when the 'direct' method is not used.
	 * Check https://codex.wordpress.org/Filesystem_API for info
	 *
	 * @param    string  existing path
	 * @return   string  normalized path
	 */
	public function normalize_path( $path ) {
		global $wp_filesystem;
		return str_replace( ABSPATH, $wp_filesystem->abspath(), $path );
	}

	/**
	 * Renders the settings page for this module
	 *
	 * @since   1.0.0
	 */
	public function render_settings(){}

	/**
	* render setting to enable/disable adblocker
	*
	*/
	public function render_settings_use_adblocker() {
		$advads_options = Advanced_Ads::get_instance()->options();
		$checked = ( ! empty( $advads_options['use-adblocker'] ) ) ? 1 : 0;

		echo '<input id="advanced-ads-use-adblocker" type="checkbox" value="1" name="' . ADVADS_SLUG . '[use-adblocker]" ' . checked( $checked, 1, false ) . '>';
		echo '<p class="description">' . __( 'Prevents ad block software from breaking your website when blocking asset files (.js, .css).', 'advanced-ads' ) . '</p>';
	}

	/**
	 * Generate unique name
	 *
	 * @param    array    $haystack array to check, that the returned string does not exist in this array
	 * @return   string   unique name
	 */
	function generate_unique_name( $haystack = false ) {
		if ( $haystack ) {
			while ( true ) {
				$needle =  mt_rand( 0, 999 );
				if ( ! in_array( $needle, $haystack ) ) {
					return  $needle;
				}
			}
		}
		return mt_rand( 0, 999 );
	}
}
