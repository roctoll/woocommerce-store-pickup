<?php

if ( !defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( !class_exists( 'WC_Store_pickup_Settings' ) ) {
	class WC_Store_pickup_Settings {

		const SETTINGS_NAMESPACE = 'wcst_settings';

		/**
		 * Get the setting fields
		 *
		 * @return array $setting_fields
		 */
		private function get_fields() {

			$setting_fields = array(
				'section_title' => array(
					'name' => __( 'WC Store Pickup settings', 'woocommerce_store_pickup' ),
					'type' => 'title',
					'desc'    => __( 'In order to customize the emails, it is possible to insert a table with all data, or each element individualy.
					Use the following tags:<br>
					<span class="description">[all] - Table with all fieds</span><br>-
					<span class="description">[code] - Generated unique code</span><br><span class="description">[user] - Customer name</span><br><span class="description">[store] - Store name</span><br><span class="description">[product] - Product name</span><br><span class="description">[booking] - Booking dates (if the product is a booking)</span>', 'woocommerce_store_pickup' ),
					'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_title'
				),
				'cu_subject_input' => array(
					'name'    => __( 'Customer email subject', 'woocommerce_store_pickup' ),
					'type'    => 'text',
					'desc'    => __( '', 'woocommerce_store_pickup' ),
					'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_cu_subject_input',
					'default' => '',
				),
				'cu_mail_layout' => array(
					'name'    => __( 'Customer email template', 'woocommerce_store_pickup' ),
					'type'    => 'textarea',
					'class'	  => 'input_email_layout',
					'desc'    => __( 'Customize the email content.', 'woocommerce_store_pickup' ),
					'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_cu_mail_layout',
					'default' => '',
				),
				're_subject_input' => array(
					'name'    => __( 'Store email subject', 'woocommerce_store_pickup' ),
					'type'    => 'text',
					'desc'    => __( '', 'woocommerce_store_pickup' ),
					'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_re_subject_input',
					'default' => '',
				),
				're_mail_layout' => array(
					'name'    => __( 'Store email template', 'woocommerce_store_pickup' ),
					'type'    => 'textarea',
					'class'	  => 'input_email_layout',
					'desc'    => __( 'Customize the email content.', 'woocommerce_store_pickup' ),
					'id'      => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_re_mail_layout',
					'default' => '',
				),
				'section_end'   => array(
					'type' => 'sectionend',
					'id'   => 'wc_settings_' . self::SETTINGS_NAMESPACE . '_section_end'
				)
			);

			return apply_filters( 'wc_settings_tab_' . self::SETTINGS_NAMESPACE, $setting_fields );
		}

		/**
		 * Get an option set in our settings tab
		 *
		 * @param $key
		 *
		 * @return String
		 */
		public function get_option( $key ) {
			$fields = $this->get_fields();

			return apply_filters( 'wc_option_' . $key, get_option( 'wc_settings_' . self::SETTINGS_NAMESPACE . '_' . $key, ( ( isset( $fields[$key] ) && isset( $fields[$key]['default'] ) ) ? $fields[$key]['default'] : '' ) ) );
		}

		/**
		 * Setup the WooCommerce settings
		 *
		 */
		public function setup() {
			add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 70 );
			add_action( 'woocommerce_settings_tabs_' . self::SETTINGS_NAMESPACE, array( $this, 'tab_content' ) );
			add_action( 'woocommerce_update_options_' . self::SETTINGS_NAMESPACE, array( $this, 'update_settings' ) );
		}

		/**
		 * Add a settings tab to the settings page
		 *
		 * @param array $settings_tabs
		 *
		 * @return array
		 */
		public function add_settings_tab( $settings_tabs ) {
			$settings_tabs[self::SETTINGS_NAMESPACE] = __( 'WC Store Pickup', 'woocommerce_store_pickup' );

			return $settings_tabs;
		}

		/**
		 * Output the tab content
		 *
		 */
		public function tab_content() {
			woocommerce_admin_fields( $this->get_fields() );
		}

		/**
		 * Update the settings
		 *
		 */
		public function update_settings() {
			woocommerce_update_options( $this->get_fields() );
		}

	}
}