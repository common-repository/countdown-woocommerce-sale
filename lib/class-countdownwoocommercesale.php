<?php
/**
 * Countdown WooCommerce Sale
 *
 * @package    Countdown WooCommerce Sale
 * @subpackage CountdownWooCommerceSale
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

$cdwoosale = new CountdownWooCommerceSale();

/** ==================================================
 * Main Functions
 */
class CountdownWooCommerceSale {

	/** ==================================================
	 * Construct
	 *
	 * @since 1.00
	 */
	public function __construct() {

		add_action( 'wp_enqueue_scripts', array( $this, 'load_frontend_scripts' ) );
		add_action( 'wp_footer', array( $this, 'load_localize_scripts' ) );
		add_filter( 'woocommerce_get_price_html', array( $this, 'themeprefix_custom_price_message' ) );
	}

	/** ==================================================
	 * Custom price filter
	 *
	 * @param string $price  price.
	 * @return string $price & text
	 * @since 1.00
	 */
	public function themeprefix_custom_price_message( $price ) {

		global $post;
		$product_id = $post->ID;

		$sale_ids = $this->db_sales_load();

		if ( ! empty( $sale_ids ) ) {
			if ( in_array( $product_id, $sale_ids ) && ! is_admin() ) {
				$textafter = $this->view_cd( $product_id );
				return $price . '<br /><span class="price-description">' . $textafter . '</span>';
			}
		}

		return $price;
	}

	/** ==================================================
	 * Load Database
	 *
	 * @return array $sale_ids
	 * @since 1.00
	 */
	private function db_sales_load() {

		global $wpdb;
		$sales = $wpdb->get_results(
			"
			SELECT	post_id, meta_key, meta_value
			FROM	{$wpdb->prefix}postmeta
			WHERE	meta_key LIKE '%%_sale_price%%'
			"
		);
		$sale_ids = array();
		foreach ( $sales as $sale ) {
			if ( '_sale_price' === $sale->meta_key ) {
				if ( ! empty( $sale->meta_value ) ) {
					$sale_ids[] = $sale->post_id;
				}
			}
		}

		return $sale_ids;
	}

	/** ==================================================
	 * Main Countdown
	 *
	 * @param int $pid  pid.
	 * @since 1.00
	 */
	private function view_cd( $pid ) {

		$cdwoosale_option = get_option( 'countdown_woocommerce_sale' );

		$woo_sales_text_tag = $cdwoosale_option['text_tag'];

		$woo_sales_datas = array();

		$to_date_ux = get_post_meta( $pid, '_sale_price_dates_to', true );

		if ( ! empty( $to_date_ux ) ) {
			if ( function_exists( 'wp_date' ) ) {
				$to_date = wp_date( 'Y-m-d H:i:s', $to_date_ux, new DateTimeZone( 'UTC' ) );
			} else {
				$to_date = date_i18n( 'Y-m-d H:i:s', $to_date_ux, false );
			}
			$woo_sales_datas['to_date'] = '<span style="color: ' . $cdwoosale_option['color']['to_date'] . ';">' . $to_date . '</span>';
		}

		$regular_price = get_post_meta( $pid, '_regular_price', true );
		$sale_price = get_post_meta( $pid, '_sale_price', true );

		$currency = get_option( 'woocommerce_currency' );
		$currency_symbol = get_woocommerce_currency_symbol( $currency );
		/* translators: Discount amount */
		$dis_amount = sprintf( get_woocommerce_price_format(), $currency_symbol, intval( $regular_price - $sale_price ) );
		/* translators: Discount amount text */
		$woo_sales_datas['dis_amount'] = '<span style="color: ' . $cdwoosale_option['color']['dis_amount'] . ';">' . sprintf( __( '%1$s %2$s', 'countdown-woocommerce-sale' ), $dis_amount, __( 'OFF', 'countdown-woocommerce-sale' ) ) . '</span>';
		$dis_rate = intval( ( ( $regular_price - $sale_price ) / $regular_price ) * 100 );
		/* translators: Sales rate text */
		$woo_sales_datas['dis_rate'] = '<span style="color: ' . $cdwoosale_option['color']['dis_rate'] . ';">' . sprintf( __( '%1$d&#37; %2$s', 'countdown-woocommerce-sale' ), $dis_rate, __( 'OFF', 'countdown-woocommerce-sale' ) ) . '</span>';

		if ( ! empty( $to_date_ux ) ) {
			$woo_sales_datas['count_down'] = '<span style="color: ' . $cdwoosale_option['color']['count_down'] . ';"><cd_woo_sale_time>' . $to_date . '</cd_woo_sale_time></span>';
		}

		$cd_html = null;
		if ( $woo_sales_datas ) {
			$cd_html = $woo_sales_text_tag;
			foreach ( $woo_sales_datas as $item => $woo_sale ) {
				$cd_html = str_replace( '%' . $item . '%', $woo_sale, $cd_html );
			}
			preg_match_all( '/%(.*?)%/', $cd_html, $woo_sales_text_per_match );
			foreach ( $woo_sales_text_per_match as $key1 ) {
				foreach ( $key1 as $key2 ) {
					$cd_html = str_replace( '%' . $key2 . '%', '', $cd_html );
				}
			}
		}

		return '<span style="color: ' . $cdwoosale_option['color']['other_text'] . ';">' . $cd_html . '</span>';
	}

	/** ==================================================
	 * Load Script
	 *
	 * @since 1.00
	 */
	public function load_frontend_scripts() {

		wp_enqueue_script( 'jquery' );
		wp_enqueue_script( 'jquery-countdown', plugin_dir_url( __DIR__ ) . 'js/jquery.countdown.js', null, '1.2.8', false );
	}

	/** ==================================================
	 * Load Localize Script
	 *
	 * @since 1.00
	 */
	public function load_localize_scripts() {

		wp_enqueue_script( 'cd-woo-sale', plugin_dir_url( __DIR__ ) . 'js/jquery.countdownwoocommercesale.js', array( 'jquery' ), '1.00', false );
		$localize_cd_woo_sale_settings = array();
		$localize_cd_woo_sale_settings = array_merge( $localize_cd_woo_sale_settings, array( 'dd' => __( 'Days', 'countdown-woocommerce-sale' ) ) );
		wp_localize_script( 'cd-woo-sale', 'cdwoosale_settings', $localize_cd_woo_sale_settings );
	}
}
