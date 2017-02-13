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

		add_settings_field( 'social-share-before-content', 'Show share buttons before content?', array( $this, 'social_share_before_content' ), 'social-share', 'social_share_config_section' );
		add_settings_field( 'social-share-after-content', 'Show share buttons after content?', array( $this, 'social_share_after_content' ), 'social-share', 'social_share_config_section' );
		add_settings_field( 'social-share-only-posts', 'Show share buttons only on single "Posts" pages?', array( $this, 'social_share_only_posts' ), 'social-share', 'social_share_config_section' );
		add_settings_field( 'social-share-before-buttons', 'Label before buttons', array( $this, 'social_share_before_buttons' ), 'social-share', 'social_share_config_section' );

		register_setting( 'social_share_config_section', 'social-share-before-content' );
		register_setting( 'social_share_config_section', 'social-share-after-content' );
		register_setting( 'social_share_config_section', 'social-share-only-posts' );
		register_setting( 'social_share_config_section', 'social-share-before-buttons' );
	}

	/**
	 * Checkbox function for showing before content
	 */
	public function social_share_before_content() {
	?>
		<input type="checkbox" name="social-share-before-content" value="1" <?php checked( 1, get_option( 'social-share-before-content' ), true ); ?>> Check for Yes
	<?php
	}

	/**
	 * Checkbox function for showing after content
	 */
	public function social_share_after_content() {
	?>
		<input type="checkbox" name="social-share-after-content" value="1" <?php checked( 1, get_option( 'social-share-after-content' ), true ); ?>> Check for Yes
	<?php
	}

	/**
	 * Checkbox function for only on single post pages
	 */
	public function social_share_only_posts() {
	?>
		<input type="checkbox" name="social-share-only-posts" value="1" <?php checked( 1, get_option( 'social-share-only-posts' ), true ); ?>> Check for Yes
	<?php
	}

	/**
	 * Label before buttons function
	 */
	public function social_share_before_buttons() {
		echo '<input type="text" id="social-share-before-buttons" name="social-share-before-buttons" value="' . get_option( 'social-share-before-buttons' ) . '">';
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

		if ( get_option( 'social-share-before-content' ) == 1 && ! get_post_meta( $post_id, 'disable_sharing' ) ) {

			wp_enqueue_style( 'social-share-styles', plugin_dir_url( __FILE__ ) . 'assets/style.min.css', false, filemtime( plugin_dir_path( __FILE__ ) . 'assets/style.min.css' ) );

			$url = get_permalink( $post->ID );
			$url = esc_url( $url );
			$label = ( get_option( 'social-share-before-buttons' ) ) ? "<div class='c-share__label'>" . get_option( 'social-share-before-buttons' ) . "</div>" : '';

			$html = "
			<div class='c-share'>
			" . $label . "
			<div class='c-share__facebook'><a target='_blank' href='http://www.facebook.com/sharer.php?u=" . $url . "'></a></div>
			<div class='c-share__twitter'><a target='_blank' href='https://twitter.com/home?status=" . $url . "'></a></div>
			<div class='c-share__google'><a target='_blank' href='https://plus.google.com/share?url=" . $url . "'></a></div>
			</div>";

			if ( get_option( 'social-share-only-posts' ) == 1 && get_post_type() == 'post' ) {
				if ( get_post_type() !== 'post' ) {
					return $content;
				}
				return $content = $html . $content;
			}
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

		if ( get_option( 'social-share-after-content' ) == 1 && ! get_post_meta( $post_id, 'disable_sharing' ) ) {

			wp_enqueue_style( 'social-share-styles', plugin_dir_url( __FILE__ ) . 'assets/style.min.css', false, filemtime( plugin_dir_path( __FILE__ ) . 'assets/style.min.css' ) );

			$url = get_permalink( $post->ID );
			$url = esc_url( $url );
			$label = ( get_option( 'social-share-before-buttons' ) ) ? "<div class='c-share__label'>" . get_option( 'social-share-before-buttons' ) . "</div>" : '';

			$html = "
			<div class='c-share'>
			" . $label . "
			<div class='c-share__facebook'><a target='_blank' href='http://www.facebook.com/sharer.php?u=" . $url . "'></a></div>
			<div class='c-share__twitter'><a target='_blank' href='https://twitter.com/home?status=" . $url . "'></a></div>
			<div class='c-share__google'><a target='_blank' href='https://plus.google.com/share?url=" . $url . "'></a></div>
			</div>";

			if ( get_option( 'social-share-only-posts' ) == 1 && get_post_type() == 'post' ) {
				if ( get_post_type() !== 'post' ) {
					return $content;
				}
				return $content = $content . $html;
			}
		}
		return $content;
	}

}
$wp_social_share = new WpSocialShare();
