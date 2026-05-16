<?php
/**
 * Admin menu class.
 *
 * @package    Skate_Club_Screen
 * @subpackage Skate_Club_Screen/admin
 */

class Skate_Club_Admin_Menu
{
    /**
     * Add admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu()
    {
        // Main menu
        add_menu_page(
            "Skate Club Screen",
            "Skate Club",
            "manage_options",
            "skate-club-sessions",
            [$this, "render_sessions_page"],
            "dashicons-tickets-alt",
            30,
        );

        // Session Management (same as main menu)
        add_submenu_page(
            "skate-club-sessions",
            "Session Management",
            "Sessions",
            "manage_options",
            "skate-club-sessions",
            [$this, "render_sessions_page"],
        );

        // Song Requests
        add_submenu_page(
            "skate-club-sessions",
            "Song Requests",
            "Song Requests",
            "manage_options",
            "skate-club-song-requests",
            [$this, "render_song_requests_page"],
        );

        // Song Voting
        add_submenu_page(
            "skate-club-sessions",
            "Song Voting",
            "Song Voting",
            "manage_options",
            "skate-club-voting",
            [$this, "render_voting_page"],
        );

        // Media Approval
        add_submenu_page(
            "skate-club-sessions",
            "Media Approval",
            "Media Approval",
            "manage_options",
            "skate-club-media",
            [$this, "render_media_page"],
        );

        // Spinner Wheel
        add_submenu_page(
            "skate-club-sessions",
            "Spinner Wheel",
            "Spinner Wheel",
            "manage_options",
            "skate-club-spinner",
            [$this, "render_spinner_page"],
        );

        // Raffle Draw
        add_submenu_page(
            "skate-club-sessions",
            "Raffle Draw",
            "Raffle Draw",
            "manage_options",
            "skate-club-raffle",
            [$this, "render_raffle_page"],
        );

        // Settings
        add_submenu_page(
            "skate-club-sessions",
            "Settings",
            "Settings",
            "manage_options",
            "skate-club-settings",
            [$this, "render_settings_page"],
        );

        // Report (last menu item)
        add_submenu_page(
            "skate-club-sessions",
            "Report",
            "Report",
            "manage_options",
            "skate-club-reporting",
            [$this, "render_reporting_page"],
        );
    }

    /**
     * Render sessions page.
     *
     * @since    1.0.0
     */
    public function render_sessions_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR .
            "admin/pages/session-management.php";
    }

    /**
     * Render song requests page.
     *
     * @since    1.0.0
     */
    public function render_song_requests_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/song-requests.php";
    }

    /**
     * Render voting page.
     *
     * @since    1.0.0
     */
    public function render_voting_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/song-voting.php";
    }

    /**
     * Render media page.
     *
     * @since    1.0.0
     */
    public function render_media_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/media-approval.php";
    }

    /**
     * Render spinner page.
     *
     * @since    1.0.0
     */
    public function render_spinner_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/spinner-wheel.php";
    }

    /**
     * Render raffle page.
     *
     * @since    1.0.0
     */
    public function render_raffle_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR .
            "admin/pages/raffle-management.php";
    }

    /**
     * Render reporting page.
     *
     * @since    1.0.0
     */
    public function render_reporting_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/reporting.php";
    }

    /**
     * Render settings page.
     *
     * @since    1.0.0
     */
    public function render_settings_page()
    {
        require_once SKATE_CLUB_PLUGIN_DIR . "admin/pages/settings.php";
    }

    /**
     * Display active session notice.
     *
     * @since    1.0.0
     */
    public static function display_active_session_notice()
    {
        $active_session = Skate_Club_Session_Manager::get_active_session();

        if ($active_session) { ?>
			<div class="notice notice-success" style="margin: 20px 0;">
				<p>
					<strong>Active Session:</strong> <?php echo esc_html(
         $active_session->session_name,
     ); ?>
					(<?php echo esc_html($active_session->session_date); ?>)
				</p>
				<p>
					<a href="<?php echo esc_url(
         home_url("/skate-club-screen-display/"),
     ); ?>" target="_blank" class="button">
						View Screen Display
					</a>
					<a href="<?php echo esc_url(
         admin_url("admin.php?page=skate-club-sessions"),
     ); ?>" class="button">
						Manage Session
					</a>
				</p>
			</div>
			<?php }
    }

    /**
     * Enqueue admin styles.
     *
     * @since    1.0.0
     */
    public function enqueue_styles()
    {
        $screen = get_current_screen();

        if (strpos($screen->id, "skate-club") !== false) {
            wp_enqueue_style(
                "skate-club-admin",
                SKATE_CLUB_PLUGIN_URL . "admin/assets/css/admin.css",
                [],
                SKATE_CLUB_VERSION,
            );
        }
    }

    /**
     * Enqueue admin scripts.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts()
    {
        $screen = get_current_screen();

        if (strpos($screen->id, "skate-club") !== false) {
            // Enqueue WordPress media uploader for settings page
            if (strpos($screen->id, "settings") !== false) {
                wp_enqueue_media();
            }

            wp_enqueue_script(
                "skate-club-admin-common",
                SKATE_CLUB_PLUGIN_URL . "admin/assets/js/admin-common.js",
                ["jquery"],
                SKATE_CLUB_VERSION,
                true,
            );

            // Localize script
            wp_localize_script("skate-club-admin-common", "skateClubAdmin", [
                "ajaxUrl" => admin_url("admin-ajax.php"),
                "nonce" => wp_create_nonce("skate_admin_action"),
            ]);

            // Page-specific scripts
            if (strpos($screen->id, "session") !== false) {
                wp_enqueue_script(
                    "skate-club-session-manager",
                    SKATE_CLUB_PLUGIN_URL .
                        "admin/assets/js/session-manager.js",
                    ["jquery", "skate-club-admin-common"],
                    SKATE_CLUB_VERSION,
                    true,
                );
            }

            if (strpos($screen->id, "media") !== false) {
                wp_enqueue_script(
                    "skate-club-media-approval",
                    SKATE_CLUB_PLUGIN_URL . "admin/assets/js/media-approval.js",
                    ["jquery", "skate-club-admin-common"],
                    SKATE_CLUB_VERSION,
                    true,
                );
            }

            if (strpos($screen->id, "spinner") !== false) {
                wp_enqueue_script(
                    "skate-club-spinner-admin",
                    SKATE_CLUB_PLUGIN_URL . "admin/assets/js/spinner-admin.js",
                    ["jquery", "skate-club-admin-common"],
                    SKATE_CLUB_VERSION,
                    true,
                );
            }
        }
    }
}
