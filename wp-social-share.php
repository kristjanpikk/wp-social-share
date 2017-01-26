<?php
/**
 * Plugin Name: WP Social Share
 * Description: Adds Sharing buttons to before/after posts/pages
 * Version: 1.0.0
 *
 * Text Domain: wp-social-share
 *
 * @package WpSocialShare
 */

/**
 * WpSocialShare class
 */
class WpSocialShare {

	/**
	 * Construct the plugin
	 */
	public function __construct() {

		/**
		 * Add settings menu item
		 */
		add_action( 'admin_menu', array( $this, 'create_menu_item' ) );
		add_action( 'admin_init', array( $this, 'social_share_settings' ) );
		add_filter( 'the_content', array( $this, 'add_social_share_before' ) );
		add_filter( 'the_content', array( $this, 'add_social_share_after' ) );
		add_action( 'add_meta_boxes', array( $this, 'add_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box' ) );

	}

	/**
	 * Settings menu item function
	 */
	public function create_menu_item() {
		add_submenu_page( 'options-general.php', 'Social Share', 'Social Share', 'manage_options', 'social-share', array( $this, 'settings_page' ) );
		return;
	}

	/**
	 * Settings page function
	 */
	public function settings_page() {
	?>
		<div class="wrap">
			<form method="post" action="options.php">
				<?php
					settings_fields( 'social_share_config_section' );
					do_settings_sections( 'social-share' );
					submit_button();
				?>
			</form>
		</div>
	<?php
	return;
	}

	/**
	 * Register settings
	 */
	public function social_share_settings() {
		add_settings_section( 'social_share_config_section', '', null, 'social-share' );

		add_settings_field( 'social-share-before-posts', 'Show share buttons before posts?', array( $this, 'social_share_before_posts' ), 'social-share', 'social_share_config_section' );
		add_settings_field( 'social-share-after-posts', 'Show share buttons after posts?', array( $this, 'social_share_after_posts' ), 'social-share', 'social_share_config_section' );

		register_setting( 'social_share_config_section', 'social-share-before-posts' );
		register_setting( 'social_share_config_section', 'social-share-after-posts' );
	}

	/**
	 * Checkbox function for showing before posts
	 */
	public function social_share_before_posts() {
	?>
		<input type="checkbox" name="social-share-before-posts" value="1" <?php checked( 1, get_option( 'social-share-before-posts' ), true ); ?> /> Check for Yes
	<?php
	}

	/**
	 * Checkbox function for showing after posts
	 */
	public function social_share_after_posts() {
	?>
		<input type="checkbox" name="social-share-after-posts" value="1" <?php checked( 1, get_option( 'social-share-after-posts' ), true ); ?> /> Check for Yes
	<?php
	}

	/**
	 * Add meta box
	 */
	public function add_meta_box() {
		$post_types = get_post_types();
		foreach ( $post_types as $post_type ) {
			add_meta_box(
				'social-share',
				'Sharing',
				array( 'WpSocialShare', 'meta_box' ),
				$post_type,
				'normal',
				'default'
			);
		}
	}

	/**
	 * Meta box content
	 */
	public function meta_box() {
		global $post;
		$post_id = get_the_ID();
		?>
		<label>
			<input type="checkbox" value="1" <?php checked( get_post_meta( $post_id, 'disable_sharing', true ) ); ?> name="disable_sharing">
			Disable sharing
		</label>
	<?php }

	/**
	 * Save meta box
	 *
	 * @param  [type] $post_id [description].
	 */
	public function save_meta_box( $post_id ) {
		if ( isset( $_POST['disable_sharing'] ) ) {
			update_post_meta( $post_id, 'disable_sharing', $_POST['disable_sharing'] );
		} else {
			delete_post_meta( $post_id, 'disable_sharing' );
		}
	}

	/**
	 * Add buttons before posts function
	 *
	 * @param $content $content [description].
	 */
	public function add_social_share_before( $content ) {
		global $post;
		$post_id = get_the_ID();

		if ( get_option( 'social-share-before-posts' ) == 1 && ! get_post_meta( $post_id, 'disable_sharing' ) ) {

			wp_enqueue_style( 'social-share-styles', plugin_dir_url( __FILE__ ) . '/assets/style.min.css' );

			$url = get_permalink( $post->ID );
			$url = esc_url( $url );

			$html = "
			<div class='c-share'><div class='c-share__label'>Share on: </div>
			<div class='c-share__facebook'><a target='_blank' href='http://www.facebook.com/sharer.php?u=" . $url . "'></a></div>
			<div class='c-share__twitter'><a target='_blank' href='https://twitter.com/home?status=" . $url . "'></a></div>
			<div class='c-share__google'><a target='_blank' href='https://plus.google.com/share?url=" . $url . "'></a></div>
			</div>";

			return $content = $html . $content ;
		}
		return $content;
	}

	/**
	 * Add buttons before posts function
	 *
	 * @param $content $content [description].
	 */
	public function add_social_share_after( $content ) {
		global $post;
		$post_id = get_the_ID();
		
		if ( get_option( 'social-share-after-posts' ) == 1 && ! get_post_meta( $post_id, 'disable_sharing' ) ) {

			wp_enqueue_style( 'social-share-styles', plugin_dir_url( __FILE__ ) . '/assets/style.min.css' );

			$url = get_permalink( $post->ID );
			$url = esc_url( $url );

			$html = "
			<div class='c-share'><div class='c-share__label'>Share on: </div>
			<div class='c-share__facebook'><a target='_blank' href='http://www.facebook.com/sharer.php?u=" . $url . "'></a></div>
			<div class='c-share__twitter'><a target='_blank' href='https://twitter.com/home?status=" . $url . "'></a></div>
			<div class='c-share__google'><a target='_blank' href='https://plus.google.com/share?url=" . $url . "'></a></div>
			</div>";

			return $content = $content . $html;
		}
		return $content;
	}

}
$wp_social_share = new WpSocialShare();
