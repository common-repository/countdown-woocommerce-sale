<?php
/**
 * Countdown WooCommerce Sale
 *
 * @package    CountdownWooCommerceSale
 * @subpackage CountdownWooCommerceSale Management screen
/*
	Copyright (c) 2018- Katsushi Kawamori (email : dodesyoswift312@gmail.com)
	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License as published by
	the Free Software Foundation; version 2 of the License.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 */

$countdownwoocommercesaleadmin = new CountdownWooCommerceSaleAdmin();

/** ==================================================
 * Management screen
 */
class CountdownWooCommerceSaleAdmin {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'register_settings' ) );

		add_action( 'admin_menu', array( $this, 'plugin_menu' ), 100 );
		add_action( 'admin_enqueue_scripts', array( $this, 'load_custom_wp_admin_style' ) );
		add_filter( 'plugin_action_links', array( $this, 'settings_link' ), 10, 2 );
	}

	/** ==================================================
	 * Add a "Settings" link to the plugins page
	 *
	 * @param  array  $links  links array.
	 * @param  string $file   file.
	 * @return array  $links  links array.
	 * @since 1.00
	 */
	public function settings_link( $links, $file ) {
		static $this_plugin;
		if ( empty( $this_plugin ) ) {
			$this_plugin = 'countdown-woocommerce-sale/countdownwoocommercesale.php';
		}
		if ( $file == $this_plugin ) {
			$links[] = '<a href="' . admin_url( 'admin.php?page=CountdownWooCommerceSale' ) . '">' . __( 'Settings' ) . '</a>';
		}
			return $links;
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_menu() {
		add_submenu_page(
			'woocommerce',
			__( 'Countdown Sale', 'countdown-woocommerce-sale' ),
			__( 'Countdown Sale', 'countdown-woocommerce-sale' ),
			'manage_woocommerce',
			'CountdownWooCommerceSale',
			array( $this, 'plugin_options' )
		);
	}

	/** ==================================================
	 * Add Css and Script
	 *
	 * @since 1.00
	 */
	public function load_custom_wp_admin_style() {
		if ( $this->is_my_plugin_screen() ) {
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_script( 'jquery' );
			wp_enqueue_script( 'colorpicker-admin-js', plugin_dir_url( __DIR__ ) . 'js/jquery.colorpicker.admin.js', array( 'wp-color-picker' ), '1.0.0', false );
		}
	}

	/** ==================================================
	 * For only admin style
	 *
	 * @since 1.00
	 */
	private function is_my_plugin_screen() {
		$screen = get_current_screen();
		if ( is_object( $screen ) && 'woocommerce_page_CountdownWooCommerceSale' == $screen->id ) {
			return true;
		} else {
			return false;
		}
	}

	/** ==================================================
	 * Settings page
	 *
	 * @since 1.00
	 */
	public function plugin_options() {

		if ( ! current_user_can( 'manage_woocommerce' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.' ) );
		}

		$this->options_updated();

		$scriptname = admin_url( 'admin.php?page=CountdownWooCommerceSale' );

		$cd_woo_sale_option = get_option( 'countdown_woocommerce_sale' );

		?>

		<div class="wrap">
		<h2>Countdown WooCommerce Sale</h2>

			<details>
			<summary><strong><?php esc_html_e( 'Various links of this plugin', 'countdown-woocommerce-sale' ); ?></strong></summary>
			<?php $this->credit(); ?>
			</details>

			<div class="wrap">
				<h2><?php esc_html_e( 'Settings' ); ?></h2>	

				<form method="post" action="<?php echo esc_url( $scriptname ); ?>">
				<?php wp_nonce_field( 'cd_woo_set', 'countdownwoosale_settings' ); ?>

				<div class="submit">
				  <input type="submit" class="button" name="CdWooSave" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				  <input type="submit" class="button" name="Default" value="<?php esc_attr_e( 'Default' ); ?>" />
				</div>

				<div style="display: block; padding:5px 5px;">
					<h3><?php esc_html_e( 'Sale', 'countdown-woocommerce-sale' ); ?> <?php esc_html_e( 'Tags' ); ?> & <?php esc_html_e( 'Color' ); ?> </h3>
					<div style="display: block; padding:5px 20px;">

						<table>
						<tr style="background: #fff;"><td><strong><?php esc_html_e( 'Tags' ); ?></strong></td><td><strong><?php esc_html_e( 'Description' ); ?></strong></td><td><strong align="center"><?php esc_html_e( 'Color' ); ?></strong></td></tr>
						<tr style="background: #eee;"><td><b>%dis_amount%</b></td><td><?php esc_html_e( 'Discount amount', 'countdown-woocommerce-sale' ); ?></td><td><input type="text" class="wpcolor" name="cdwoosale_color_dis_amount" value="<?php echo esc_attr( $cd_woo_sale_option['color']['dis_amount'] ); ?>" size="10" /></td></tr>
						<tr style="background: #fff;"><td><b>%dis_rate%</b></td><td><?php esc_html_e( 'Discount rate', 'countdown-woocommerce-sale' ); ?></td><td><input type="text" class="wpcolor" name="cdwoosale_color_dis_rate" value="<?php echo esc_attr( $cd_woo_sale_option['color']['dis_rate'] ); ?>" size="10" /></td></tr>
						<tr style="background: #eee;"><td><b>%to_date%</b></td><td><?php esc_html_e( 'Last day of sale', 'countdown-woocommerce-sale' ); ?></td><td><input type="text" class="wpcolor" name="cdwoosale_color_to_date" value="<?php echo esc_attr( $cd_woo_sale_option['color']['to_date'] ); ?>" size="10" /></td></tr>
						<tr style="background: #fff;"><td><b>%count_down%</b></td><td><?php esc_html_e( 'Countdown', 'countdown-woocommerce-sale' ); ?></td><td><input type="text" class="wpcolor" name="cdwoosale_color_count_down" value="<?php echo esc_attr( $cd_woo_sale_option['color']['count_down'] ); ?>" size="10" /></td></tr>
						<tr style="background: #eee;"><td></td><td><?php esc_html_e( 'Other text', 'countdown-woocommerce-sale' ); ?></td><td><input type="text" class="wpcolor" name="cdwoosale_color_other_text" value="<?php echo esc_attr( $cd_woo_sale_option['color']['other_text'] ); ?>" size="10" /></td></tr>
						</table>
						<li><b><?php esc_html_e( 'Please write any letters between tags. You can move and delete tags freely.', 'countdown-woocommerce-sale' ); ?></b></li>
						<li><b><?php esc_html_e( 'The only HTML tag you can use is &lt;b&gt;,&lt;br&gt;,&lt;strong&gt;.', 'countdown-woocommerce-sale' ); ?></b></li>
						<textarea name="cdwoosale_text_tag" style="width: 100%;"><?php echo esc_textarea( $cd_woo_sale_option['text_tag'] ); ?></textarea>
					</div>
				</div>

				<div class="submit">
					<input type="submit" class="button" name="CdWooSave" value="<?php esc_attr_e( 'Save Changes' ); ?>" />
				</div>

				</form>

			</div>
		</div>

		<?php
	}

	/** ==================================================
	 * Credit
	 *
	 * @since 1.00
	 */
	private function credit() {

		$plugin_name    = null;
		$plugin_ver_num = null;
		$plugin_path    = plugin_dir_path( __DIR__ );
		$plugin_dir     = untrailingslashit( wp_normalize_path( $plugin_path ) );
		$slugs          = explode( '/', $plugin_dir );
		$slug           = end( $slugs );
		$files          = scandir( $plugin_dir );
		foreach ( $files as $file ) {
			if ( '.' === $file || '..' === $file || is_dir( $plugin_path . $file ) ) {
				continue;
			} else {
				$exts = explode( '.', $file );
				$ext  = strtolower( end( $exts ) );
				if ( 'php' === $ext ) {
					$plugin_datas = get_file_data(
						$plugin_path . $file,
						array(
							'name'    => 'Plugin Name',
							'version' => 'Version',
						)
					);
					if ( array_key_exists( 'name', $plugin_datas ) && ! empty( $plugin_datas['name'] ) && array_key_exists( 'version', $plugin_datas ) && ! empty( $plugin_datas['version'] ) ) {
						$plugin_name    = $plugin_datas['name'];
						$plugin_ver_num = $plugin_datas['version'];
						break;
					}
				}
			}
		}
		$plugin_version = __( 'Version:' ) . ' ' . $plugin_ver_num;
		/* translators: FAQ Link & Slug */
		$faq       = sprintf( __( 'https://wordpress.org/plugins/%s/faq', 'countdown-woocommerce-sale' ), $slug );
		$support   = 'https://wordpress.org/support/plugin/' . $slug;
		$review    = 'https://wordpress.org/support/view/plugin-reviews/' . $slug;
		$translate = 'https://translate.wordpress.org/projects/wp-plugins/' . $slug;
		$facebook  = 'https://www.facebook.com/katsushikawamori/';
		$twitter   = 'https://twitter.com/dodesyo312';
		$youtube   = 'https://www.youtube.com/channel/UC5zTLeyROkvZm86OgNRcb_w';
		$donate    = __( 'https://shop.riverforest-wp.info/donate/', 'countdown-woocommerce-sale' );

		?>
		<span style="font-weight: bold;">
		<div>
		<?php echo esc_html( $plugin_version ); ?> | 
		<a style="text-decoration: none;" href="<?php echo esc_url( $faq ); ?>" target="_blank" rel="noopener noreferrer">FAQ</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $support ); ?>" target="_blank" rel="noopener noreferrer">Support Forums</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $review ); ?>" target="_blank" rel="noopener noreferrer">Reviews</a>
		</div>
		<div>
		<a style="text-decoration: none;" href="<?php echo esc_url( $translate ); ?>" target="_blank" rel="noopener noreferrer">
		<?php
		/* translators: Plugin translation link */
		echo esc_html( sprintf( __( 'Translations for %s' ), $plugin_name ) );
		?>
		</a> | <a style="text-decoration: none;" href="<?php echo esc_url( $facebook ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-facebook"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $twitter ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-twitter"></span></a> | <a style="text-decoration: none;" href="<?php echo esc_url( $youtube ); ?>" target="_blank" rel="noopener noreferrer"><span class="dashicons dashicons-video-alt3"></span></a>
		</div>
		</span>

		<div style="width: 250px; height: 180px; margin: 5px; padding: 5px; border: #CCC 2px solid;">
		<h3><?php esc_html_e( 'Please make a donation if you like my work or would like to further the development of this plugin.', 'countdown-woocommerce-sale' ); ?></h3>
		<div style="text-align: right; margin: 5px; padding: 5px;"><span style="padding: 3px; color: #ffffff; background-color: #008000">Plugin Author</span> <span style="font-weight: bold;">Katsushi Kawamori</span></div>
		<button type="button" style="margin: 5px; padding: 5px;" onclick="window.open('<?php echo esc_url( $donate ); ?>')"><?php esc_html_e( 'Donate to this plugin &#187;' ); ?></button>
		</div>

		<?php
	}

	/** ==================================================
	 * Update wp_options table.
	 *
	 * @since 1.00
	 */
	private function options_updated() {

		if ( isset( $_POST['Default'] ) && ! empty( $_POST['Default'] ) ) {
			if ( check_admin_referer( 'cd_woo_set', 'countdownwoosale_settings' ) ) {
				$woo_sales_text_tag_def = '%dis_rate%<br />' . __( 'Until the end of sale&#44;', 'countdown-woocommerce-sale' ) . '<br />%count_down%';
				$cd_woo_sale_reset_tbl = array(
					'color' => array(
						'dis_amount' => '#000000',
						'dis_rate' => '#000000',
						'to_date' => '#000000',
						'count_down' => '#000000',
						'other_text' => '#000000',
					),
					'text_tag' => $woo_sales_text_tag_def,
				);
				update_option( 'countdown_woocommerce_sale', $cd_woo_sale_reset_tbl );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Default' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}

		if ( isset( $_POST['CdWooSave'] ) && ! empty( $_POST['CdWooSave'] ) ) {
			if ( check_admin_referer( 'cd_woo_set', 'countdownwoosale_settings' ) ) {
				$cd_woo_sale_settings = get_option( 'countdown_woocommerce_sale' );
				if ( ! empty( $_POST['cdwoosale_color_dis_amount'] ) ) {
					$cd_woo_sale_settings['color']['dis_amount'] = sanitize_text_field( wp_unslash( $_POST['cdwoosale_color_dis_amount'] ) );
				}
				if ( ! empty( $_POST['cdwoosale_color_dis_rate'] ) ) {
					$cd_woo_sale_settings['color']['dis_rate'] = sanitize_text_field( wp_unslash( $_POST['cdwoosale_color_dis_rate'] ) );
				}
				if ( ! empty( $_POST['cdwoosale_color_to_date'] ) ) {
					$cd_woo_sale_settings['color']['to_date'] = sanitize_text_field( wp_unslash( $_POST['cdwoosale_color_to_date'] ) );
				}
				if ( ! empty( $_POST['cdwoosale_color_count_down'] ) ) {
					$cd_woo_sale_settings['color']['count_down'] = sanitize_text_field( wp_unslash( $_POST['cdwoosale_color_count_down'] ) );
				}
				if ( ! empty( $_POST['cdwoosale_color_other_text'] ) ) {
					$cd_woo_sale_settings['color']['other_text'] = sanitize_text_field( wp_unslash( $_POST['cdwoosale_color_other_text'] ) );
				}
				if ( ! empty( $_POST['cdwoosale_text_tag'] ) ) {
					$allowed_html = array(
						'b' => array(),
						'br' => array(),
						'strong' => array(),
					);
					$cd_woo_sale_settings['text_tag'] = wp_kses( wp_unslash( $_POST['cdwoosale_text_tag'] ), $allowed_html );
				}
				update_option( 'countdown_woocommerce_sale', $cd_woo_sale_settings );
				echo '<div class="notice notice-success is-dismissible"><ul><li>' . esc_html( __( 'Settings' ) . ' --> ' . __( 'Changes saved.' ) ) . '</li></ul></div>';
			}
		}
	}

	/** ==================================================
	 * Settings register
	 *
	 * @since 1.00
	 */
	public function register_settings() {

		if ( ! get_option( 'countdown_woocommerce_sale' ) ) {
			$woo_sales_text_tag = '%dis_rate%<br />' . __( 'Until the end of sale&#44;', 'countdown-woocommerce-sale' ) . '<br />%count_down%';
			$countdown_woocommerce_sale_tbl = array(
				'color' => array(
					'dis_amount' => '#000000',
					'dis_rate' => '#000000',
					'to_date' => '#000000',
					'count_down' => '#000000',
					'other_text' => '#000000',
				),
				'text_tag' => $woo_sales_text_tag,
			);
			update_option( 'countdown_woocommerce_sale', $countdown_woocommerce_sale_tbl );
		}
	}
}


