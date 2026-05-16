<?php
/**
 * The core plugin class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/includes
 */

class Skate_Club_Core {

	/**
	 * The loader that's responsible for maintaining and registering all hooks.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      object    $loader    Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $plugin_name    The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string    $version    The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		$this->version     = SKATE_CLUB_VERSION;
		$this->plugin_name = 'skate-club-screen';

		$this->load_dependencies();
		$this->define_admin_hooks();
		$this->define_public_hooks();
		$this->define_login_hooks();
		$this->define_ajax_hooks();
		$this->register_rewrite_rules();
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {
		// Core classes
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-database.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-session-manager.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-qr-generator.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-security.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-login-customizer.php';

		// Module classes
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/modules/class-song-request.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/modules/class-song-voting.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/modules/class-media-upload.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/modules/class-spinner-wheel.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/modules/class-raffle-draw.php';

		// Admin classes
		if ( is_admin() ) {
			require_once SKATE_CLUB_PLUGIN_DIR . 'admin/class-admin-menu.php';
			require_once SKATE_CLUB_PLUGIN_DIR . 'admin/class-admin-ajax.php';
		}

		// Public classes
		require_once SKATE_CLUB_PLUGIN_DIR . 'public/class-public-display.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'public/class-form-handler.php';
		require_once SKATE_CLUB_PLUGIN_DIR . 'public/class-ajax-handler.php';
	}

	/**
	 * Register all of the hooks related to the admin area functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {
		if ( ! is_admin() ) {
			return;
		}

		// Admin menu
		$admin_menu = new Skate_Club_Admin_Menu();
		add_action( 'admin_menu', array( $admin_menu, 'add_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $admin_menu, 'enqueue_styles' ) );
        add_action( 'admin_enqueue_scripts', array( $admin_menu, 'enqueue_scripts' ) );

		// Access Control
		require_once SKATE_CLUB_PLUGIN_DIR . 'includes/class-access-control.php';
		$access_control = new Skate_Club_Access_Control();
		add_action( 'admin_menu', array( $access_control, 'restrict_admin_menus' ), 9999 );



		// Admin AJAX
		$admin_ajax = new Skate_Club_Admin_Ajax();
		add_action( 'wp_ajax_skate_create_session', array( $admin_ajax, 'create_session' ) );
		add_action( 'wp_ajax_skate_activate_session', array( $admin_ajax, 'activate_session' ) );
		add_action( 'wp_ajax_skate_close_session', array( $admin_ajax, 'close_session' ) );
		add_action( 'wp_ajax_skate_get_sessions', array( $admin_ajax, 'get_sessions' ) );
		add_action( 'wp_ajax_skate_create_voting_list', array( $admin_ajax, 'create_voting_list' ) );
		add_action( 'wp_ajax_skate_get_pending_media', array( $admin_ajax, 'get_pending_media' ) );
		add_action( 'wp_ajax_skate_approve_media', array( $admin_ajax, 'approve_media' ) );
		add_action( 'wp_ajax_skate_reject_media', array( $admin_ajax, 'reject_media' ) );
		add_action( 'wp_ajax_skate_add_spinner_entry', array( $admin_ajax, 'add_spinner_entry' ) );
		add_action( 'wp_ajax_skate_remove_spinner_entry', array( $admin_ajax, 'remove_spinner_entry' ) );
		add_action( 'wp_ajax_skate_bulk_delete_spinner_entries', array( $admin_ajax, 'bulk_delete_spinner_entries' ) );
		add_action( 'wp_ajax_skate_spin_wheel', array( $admin_ajax, 'spin_wheel' ) );
		add_action( 'wp_ajax_skate_set_active_spinner_group', array( $admin_ajax, 'set_active_spinner_group' ) );
		add_action( 'wp_ajax_skate_rename_spinner_group', array( $admin_ajax, 'rename_spinner_group' ) );
		add_action( 'wp_ajax_skate_update_spinner_entry', array( $admin_ajax, 'update_spinner_entry' ) );
		add_action( 'wp_ajax_skate_close_raffle', array( $admin_ajax, 'close_raffle' ) );
		add_action( 'wp_ajax_skate_draw_raffle_winner', array( $admin_ajax, 'draw_raffle_winner' ) );
		add_action( 'wp_ajax_nopriv_skate_draw_raffle_winner', array( $admin_ajax, 'draw_raffle_winner' ) );
		add_action( 'wp_ajax_skate_get_song_requests', array( $admin_ajax, 'get_song_requests' ) );
		add_action( 'wp_ajax_skate_save_settings', array( $admin_ajax, 'save_settings' ) );
		add_action( 'wp_ajax_skate_export_user_data', array( $admin_ajax, 'export_user_data' ) );
		add_action( 'wp_ajax_skate_export_song_requests', array( $admin_ajax, 'export_song_requests' ) );
		add_action( 'wp_ajax_skate_export_raffle_entries', array( $admin_ajax, 'export_raffle_entries' ) );
		add_action( 'wp_ajax_skate_remove_song_request', array( $admin_ajax, 'remove_song_request' ) );
		add_action( 'wp_ajax_skate_bulk_delete_requests', array( $admin_ajax, 'bulk_delete_requests' ) );
		add_action( 'wp_ajax_skate_remove_voting_song', array( $admin_ajax, 'remove_voting_song' ) );
		add_action( 'wp_ajax_skate_bulk_delete_voting_songs', array( $admin_ajax, 'bulk_delete_voting_songs' ) );
		add_action( 'wp_ajax_skate_remove_raffle_entry', array( $admin_ajax, 'remove_raffle_entry' ) );
		add_action( 'wp_ajax_skate_bulk_delete_raffle_entries', array( $admin_ajax, 'bulk_delete_raffle_entries' ) );
		add_action( 'wp_ajax_skate_remove_media', array( $admin_ajax, 'remove_media' ) );
		add_action( 'wp_ajax_skate_bulk_delete_media', array( $admin_ajax, 'bulk_delete_media' ) );
	}

	/**
	 * Register all of the hooks related to the public-facing functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {
		// Public display
		$public_display = new Skate_Club_Public_Display();
		add_action( 'template_redirect', array( $public_display, 'handle_screen_display' ) );
		add_action( 'wp_enqueue_scripts', array( $public_display, 'enqueue_styles' ) );
		add_action( 'wp_enqueue_scripts', array( $public_display, 'enqueue_scripts' ) );

		// Form handler
		$form_handler = new Skate_Club_Form_Handler();
		add_action( 'template_redirect', array( $form_handler, 'handle_forms' ) );

		// Custom query vars
		add_filter( 'query_vars', array( $this, 'add_query_vars' ) );
		
		// Add noindex meta tag
		add_action( 'wp_head', array( $this, 'add_noindex_meta_tag' ) );

		// Disable admin bar on frontend
		add_filter( 'show_admin_bar', '__return_false' );
	}

	/**
	 * Register all of the hooks related to the login page customization.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_login_hooks() {
		$login_customizer = new Skate_Club_Login_Customizer();
		add_action( 'login_enqueue_scripts', array( $login_customizer, 'enqueue_login_styles' ) );
		add_filter( 'login_headerurl', array( $login_customizer, 'change_login_logo_url' ) );
		add_filter( 'login_headertext', array( $login_customizer, 'change_login_logo_title' ) );
		add_filter( 'login_redirect', array( $login_customizer, 'redirect_after_login' ), 10, 3 );
	}

	/**
	 * Add noindex meta tag to plugin pages.
	 *
	 * @since    1.0.1
	 */
	public function add_noindex_meta_tag() {
		if ( get_query_var( 'skate_screen_display' ) || get_query_var( 'skate_form' ) || get_query_var( 'skate_fullview' ) ) {
			echo '<meta name="robots" content="noindex, nofollow" />' . "\n";
		}
	}

	/**
	 * Register all of the hooks related to AJAX functionality.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_ajax_hooks() {
		$ajax_handler = new Skate_Club_Ajax_Handler();

		// Public AJAX endpoints (no authentication required)
		add_action( 'wp_ajax_nopriv_skate_get_screen_data', array( $ajax_handler, 'get_screen_data' ) );
		add_action( 'wp_ajax_skate_get_screen_data', array( $ajax_handler, 'get_screen_data' ) );

		add_action( 'wp_ajax_nopriv_skate_submit_song_request', array( $ajax_handler, 'submit_song_request' ) );
		add_action( 'wp_ajax_skate_submit_song_request', array( $ajax_handler, 'submit_song_request' ) );

		add_action( 'wp_ajax_nopriv_skate_submit_vote', array( $ajax_handler, 'submit_vote' ) );
		add_action( 'wp_ajax_skate_submit_vote', array( $ajax_handler, 'submit_vote' ) );

		add_action( 'wp_ajax_nopriv_skate_upload_media', array( $ajax_handler, 'upload_media' ) );
		add_action( 'wp_ajax_skate_upload_media', array( $ajax_handler, 'upload_media' ) );

		add_action( 'wp_ajax_nopriv_skate_submit_raffle_entry', array( $ajax_handler, 'submit_raffle_entry' ) );
		add_action( 'wp_ajax_skate_submit_raffle_entry', array( $ajax_handler, 'submit_raffle_entry' ) );

		add_action( 'wp_ajax_nopriv_skate_get_active_session', array( $ajax_handler, 'get_active_session' ) );
		add_action( 'wp_ajax_skate_get_active_session', array( $ajax_handler, 'get_active_session' ) );
	}

	/**
	 * Register rewrite rules.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function register_rewrite_rules() {
		add_action( 'init', array( $this, 'add_rewrite_rules' ) );
		add_action( 'init', array( $this, 'maybe_flush_rewrite_rules' ) );
	}

	/**
	 * Flush rewrite rules if needed (one-time after update).
	 *
	 * @since    1.0.0
	 */
	public function maybe_flush_rewrite_rules() {
		$flushed = get_option( 'skate_club_rewrite_flushed_v3' );

		if ( ! $flushed ) {
			flush_rewrite_rules();
			update_option( 'skate_club_rewrite_flushed_v3', true );
		}
	}

	/**
	 * Add rewrite rules on init.
	 *
	 * @since    1.0.0
	 */
	public function add_rewrite_rules() {
		// Screen display
		add_rewrite_rule( '^skate-club-screen-display/?$', 'index.php?skate_screen_display=1', 'top' );

		// Form endpoints with skate-club prefix
		add_rewrite_rule( '^skate-club-submit-song-request/?$', 'index.php?skate_form=song_request', 'top' );
		add_rewrite_rule( '^skate-club-vote-songs/?$', 'index.php?skate_form=vote_songs', 'top' );
		add_rewrite_rule( '^skate-club-upload-media/?$', 'index.php?skate_form=upload_media', 'top' );
		add_rewrite_rule( '^skate-club-enter-raffle/?$', 'index.php?skate_form=enter_raffle', 'top' );

		// Full-view pages for widgets
		add_rewrite_rule( '^skate-club-fullview-songs/?$', 'index.php?skate_fullview=songs', 'top' );
		add_rewrite_rule( '^skate-club-fullview-spinner/?$', 'index.php?skate_fullview=spinner', 'top' );
		add_rewrite_rule( '^skate-club-fullview-raffle/?$', 'index.php?skate_fullview=raffle', 'top' );
		add_rewrite_rule( '^skate-club-fullview-gallery/?$', 'index.php?skate_fullview=gallery', 'top' );
	}

	/**
	 * Add custom query vars.
	 *
	 * @since    1.0.0
	 * @param    array    $vars    The array of query vars.
	 * @return   array
	 */
	public function add_query_vars( $vars ) {
		$vars[] = 'skate_screen_display';
		$vars[] = 'skate_form';
		$vars[] = 'skate_fullview';
		$vars[] = 'session';
		return $vars;
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		// Plugin is initialized via hooks
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @since     1.0.0
	 * @return    string    The name of the plugin.
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @since     1.0.0
	 * @return    string    The version number of the plugin.
	 */
	public function get_version() {
		return $this->version;
	}
}
