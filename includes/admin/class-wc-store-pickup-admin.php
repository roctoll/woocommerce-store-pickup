<?php

if ( ! defined( 'ABSPATH' ) )
	exit;

/**
 * "Store Pickup" admin options
 */

class WC_Store_pickup_Admin {

	/**
	 * Constructor
	 */
	public function __construct() {

		add_action( 'admin_init', array( $this, 'add_store_pickup_fields') );
		// Save the custom fields in custom post type "Pickup Store"
		add_action( 'save_post', array( $this, 'save_store_pickup_fields' ), 10, 2 );

		// Add checkbox in product data editor 
		add_filter( 'product_type_options', array( $this, 'product_add_store_pickup_checkbox' ) );
		// Add tab in product data editor (enable if checkbox is cheched)
		add_action( 'woocommerce_product_write_panel_tabs', array( $this, 'product_add_store_pickup_tab' ) );
		// Add custom fields in "store pickup" panel in product data editor
		add_action( 'woocommerce_product_write_panels', array( $this, 'product_add_store_pickup_panel' ) );
		// Save "Store pickup" fields data
		add_action( 'woocommerce_process_product_meta', array( $this, 'product_save_store_pickup_meta' ) );
	}

		
	public function add_store_pickup_fields() {
	    add_meta_box( 'store_info_meta_box',
	        'Store information',
	        array( $this, 'display_store_meta_box' ),
	        'pickup_stores', 'normal', 'high'
	    );
	}
	
	public function display_store_meta_box( $store_info ) {
	    // Retrieve current name of the Director and Movie Rating based on review ID
	    $store_email	= esc_html( get_post_meta( $store_info->ID, 'store_email', true ) );
	    $store_address	= esc_html( get_post_meta( $store_info->ID, 'store_address', true ) );
	    $store_phone	= esc_html( get_post_meta( $store_info->ID, 'store_phone', true ) );
	    ?>
	    <table>
	        <tr>
	            <td style="width: 30%"><?php _e('Email','woocommerce_store_pickup'); ?></td>
	            <td><input type="text" size="30" name="store_pickup_email" value="<?php echo $store_email; ?>" /></td>
	        </tr>
	        <tr>
	            <td style="width: 30%"><?php _e('Phone','woocommerce_store_pickup'); ?></td>
	            <td><input type="text" size="30" name="store_pickup_phone" value="<?php echo $store_phone; ?>" /></td>
	        </tr>
	        <tr>
	            <td style="width: 30%"><?php _e('Address','woocommerce_store_pickup'); ?></td>
	            <td><textarea rows=4 name="store_pickup_address" style="margin: 1px; width:100%;"><?php echo $store_address; ?></textarea></td>
	        </tr>
	    </table>
	    <?php
	}
	public function save_store_pickup_fields( $store_pickup_id, $store_pickup ) {
	    // Check post type for movie reviews
	    if ( $store_pickup->post_type == 'pickup_stores' ) {
	        // Store data in post meta table if present in post data
	        if ( isset( $_POST['store_pickup_email'] ) && $_POST['store_pickup_email'] != '' ) {
	            update_post_meta( $store_pickup_id, 'store_email', $_POST['store_pickup_email'] );
	        }
	        if ( isset( $_POST['store_pickup_phone'] ) && $_POST['store_pickup_phone'] != '' ) {
	            update_post_meta( $store_pickup_id, 'store_address', $_POST['store_pickup_phone'] );
	        }
	        if ( isset( $_POST['store_pickup_address'] ) && $_POST['store_pickup_address'] != '' ) {
	            update_post_meta( $store_pickup_id, 'store_phone', $_POST['store_pickup_address'] );
	        }
	    }
	}

	// Add checkbox in product data editor 
	public function product_add_store_pickup_checkbox( $options ) {
		return array_merge( $options, array(
			'wc_booking_store_pickup' => array(
				'id'            => '_wc_booking_store_pickup',
				'wrapper_class' => '',
				'label'         => __( 'Store pickup', 'woocommerce' ),
				'description'   => __( 'Check this if this product must be collected at external store', 'woocommerce' ),
				'default'       => 'no'
			)
		) );
		return $options;
	}
	
	// Add tab in product data editor (enable if checkbox is cheched)
	public function product_add_store_pickup_tab() {
		?>
		<li class="bookings_tab bookings_store_pickup_tab" style="display:none;">
			<a href="#store_product_data"><?php _e('Store pickup', 'woothemes'); ?></a>
		</li>
		<?php
	}
	
	// Add custom fields in "store pickup" panel in product data editor
	public function product_add_store_pickup_panel() {
		?>
		<div id="store_product_data" class="panel woocommerce_options_panel">
		<div class="options_group">
		<?php
		woocommerce_wp_checkbox( 
			array( 
				'id'            => 'gen_code', 
				'label'         => __('Generate code', 'woocommerce' ), 
				'desc_tip'    	=> 'true',
				'description'   => __( 'Check if you want to generate a specific code for each product sold and send it both to customer and store', 'woocommerce' ) 
				)
		);
		?>	
		</div>
		<div class="options_group">
		<?php
		
		//Get existing stores
		$args = array(
		  'post_type' => 'pickup_stores',
		  'post_status' => 'publish',
		  'posts_per_page' => -1,
		);
		$stores = get_posts($args);
		$arr = array();
		if ( $stores ) {
			foreach ( $stores as $store ) {
				$arr[$store->ID] = esc_html( $store->post_title );
			}
		}	
	    woocommerce_wp_select( 
			array( 
				'id'			=> 'store_id', 
				'label'			=> __( 'Pickup store', 'woocommerce' ), 
				'desc_tip'		=> 'true',
				'description'	=> __( 'Select here the pickup store', 'woocommerce' ),
				'options'		=> $arr
			)
		);   		 
	    woocommerce_wp_text_input( 
			array( 
				'id'			=> 'extra_email', 
				'label'			=> __( 'Extra email', 'woocommerce' ), 
				'placeholder'	=> 'email@domain.com',
				'desc_tip'		=> 'true',
				'description'	=> __( 'If you want to send the code to an extra email', 'woocommerce' ) 
			)
		);    
		?>
		</div>
		</div>
		<?php
	}
	
	// Save "Store pickup" fields data
	public function product_save_store_pickup_meta( $post_id ) {
		if( isset( $_POST['gen_code'] ) )
			update_post_meta( $post_id, 'gen_code', esc_attr( $_POST['gen_code'] ) );
		else
			update_post_meta( $post_id, 'gen_code', 'no' );
			
		$woocommerce_text_field = $_POST['extra_email'];
		if( !empty( $woocommerce_text_field ) )
			update_post_meta( $post_id, 'extra_email', esc_attr( $woocommerce_text_field ) );
			
		$woocommerce_text_field = $_POST['store_id'];
		if( !empty( $woocommerce_text_field ) )
			update_post_meta( $post_id, 'store_id', esc_attr( $woocommerce_text_field ) );
	}

}

new WC_Store_pickup_Admin();
	
