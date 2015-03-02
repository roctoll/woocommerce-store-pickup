<?php 
/*
Plugin Name: WooCommerce Store Pickup
Plugin URI: 
Description: Plugin to generate a unique code each selected item and send it to both, customer and external retailer.
Author: Roc Toll
Version: 1.1
Author URI: http://roctoll.com
*/


if ( ! defined( 'ABSPATH' ) ) exit; 

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {


class WooCommerce_Store_Pickup {

	private static $instance = null;
	private $plugin_path;
	private $plugin_url;
    private $text_domain = '';
	/**
	 * Creates or returns an instance of this class.
	 */
	public static function get_instance() {
		// If an instance hasn't been created and set to $instance create an instance and set it to $instance.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}
	/**
	 * Initializes the plugin by setting localization, hooks, filters, and administrative functions.
	 */
	private function __construct() {
		$this->plugin_path = plugin_dir_path( __FILE__ );
		$this->plugin_url  = plugin_dir_url( __FILE__ );
		load_plugin_textdomain( $this->text_domain, false, $this->plugin_path . '\lang' );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'register_styles' ) );
/*
		add_action( 'wp_enqueue_scripts', array( $this, 'register_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'register_styles' ) );
*/
		// Create custom post type "Pickup Store"
		add_action( 'init', array( $this,'create_store_pickup' ) );

		register_activation_hook( __FILE__, array( $this, 'activation' ) );
		register_deactivation_hook( __FILE__, array( $this, 'deactivation' ) );

		include( 'includes/admin/class-wc-store-pickup-admin.php' );

		$this->run_plugin();
	}
	
	public function get_plugin_url() {
		return $this->plugin_url;
	}
	
	public function get_plugin_path() {
		return $this->plugin_path;
	}
	
	public function create_store_pickup() {
	    register_post_type( 'pickup_stores',
	        array(
	            'labels' => array(
	                'name' 				=> 'Pickup stores',
	                'singular_name' 	=> 'Store',
	                'add_new' 			=> __('Add New','woocommerce'),
	                'add_new_item' 		=> __('Add New store','woocommerce'),
	                'edit' 				=> __('Edit','woocommerce'),
	                'edit_item' 		=> __('Edit store','woocommerce'),
	                'new_item' 			=> __('New store review','woocommerce'),
	                'view' 				=> 'View',
	                'view_item' 		=> __('View Store review','woocommerce'),
	                'search_items' 		=> 'Search Movie Reviews',
	                'not_found' 		=> 'No Movie Reviews found',
	                'parent' 			=> 'Parent Movie Review'
	            ),
                'menu_icon' 	=> 'dashicons-store',
	            'public' 		=> true,
	            'menu_position' => 58,
	            'supports' 		=> array( 'title'),
	            'taxonomies' 	=> array( '' ),
	            'has_archive' 	=> true
	        )
	    );
	}
	
    /**
     * Place code that runs at plugin activation here.
     */
    public function activation() {
		
	}
    /**
     * Place code that runs at plugin deactivation here.
     */
    public function deactivation() {
	}
    /**
     * Enqueue and register JavaScript files here.
     */
    public function register_scripts() {
		wp_register_script( 'wc_store_pickup_panel_js', $this->get_plugin_url() . 'assets/js/panel.js' );
		wp_enqueue_script( 'wc_store_pickup_panel_js' );    
	}
    /**
     * Enqueue and register CSS files here.
     */
    public function register_styles() {
		wp_register_style( 'wc_store_pickup_admin_styles', $this->get_plugin_url() . 'assets/css/panel.css' );
		wp_enqueue_style( 'wc_store_pickup_admin_styles' );    		
	}
    /**
     * Place code for your plugin's functionality here.
     */
    private function run_plugin() {

		//Generate the code
		add_filter('woocommerce_get_cart_item_from_session', 'cart_item_from_session', 99, 3);
	 	// this one does the same as woocommerce_update_cart_action() in plugins\woocommerce\woocommerce-functions.php
		// but with your "code" variable
		add_action( 'init', array( $this, 'update_cart_action' ), 9);
		
		// this is in Order summary. It show Code variable under product name. Same place where Variations are shown.
		add_filter( 'woocommerce_get_item_data', 'item_data', 10, 2 );
		// this adds Code as meta in Order for item
		add_action ('woocommerce_add_order_item_meta', 'add_item_meta', 10, 2);
		// this send emails both to user and retailer with the order code. One code for each product
		add_action ('woocommerce_order_status_completed', 'send_mail', 10, 2);
   
	}

	public function cart_item_from_session( $data, $values, $key ) {
		//set id length 
		$random_id_length = 4; 
		//generate a random id encrypt it and store it in $rnd_id 
		$rnd_id = crypt(uniqid(rand(),1)); 
		//to remove any slashes that might have come 
		$rnd_id = strip_tags(stripslashes($rnd_id)); 	
		//Removing any . or / and reversing the string 
		$rnd_id = str_replace(".","",$rnd_id); 
		$rnd_id = strrev(str_replace("/","",$rnd_id)); 	
		//take the first characters from the $rnd_id 
		$rnd_id = substr($rnd_id,0,$random_id_length); 
		//Add user+product info
		$current_usr = get_current_user_id(); 
		$ids = $data['data']->post->ID.'u'.$current_usr;
		
	    $data['code'] = $ids.'-'.$rnd_id;
	    return $data;
	}


	public function update_cart_action() {
	    global $woocommerce;
	    if ( ( ! empty( $_POST['update_cart'] ) || ! empty( $_POST['proceed'] ) ) && $woocommerce->verify_nonce('cart')) {
	        $cart_totals = isset( $_POST['cart'] ) ? $_POST['cart'] : '';
	        if ( sizeof( $woocommerce->cart->get_cart() ) > 0 ) {
	            foreach ( $woocommerce->cart->get_cart() as $cart_item_key => $values ) {
	                if ( isset( $cart_totals[ $cart_item_key ]['code'] ) ) {
	                    $woocommerce->cart->cart_contents[ $cart_item_key ]['code'] = $cart_totals[ $cart_item_key ]['code'];
	                }
	            }
	        }
	    }
	}

	public function item_data( $data, $cart_item ) {
	    if ( isset( $cart_item['code'] ) ) {
	        $data['code'] = array('name' => 'Code', 'value' => $cart_item['code']);
	    }
	    return $data;
	}
	
	public function add_item_meta( $item_id, $values ) {
	    woocommerce_add_order_item_meta( $item_id, 'Code', $values['code'] );
	}
	
	public function send_mail( $order_id ) {
	
	   $order = new WC_Order($order_id);
	   $userid = $order->get_user_id();
	   $user = get_userdata( $userid );
	   $items = $order->get_items();
	   
		foreach ( $items as $item ) { 
		
			$is_retailer = get_post_meta($item['product_id'],'is_retailer',true);
			
			if ( $is_retailer ) {
			
				$code			= $item['item_meta']['Code'][0];
				$book_date		= $item['item_meta']['Booking Date'][0];
				$book_time		= $item['item_meta']['Booking Time'][0];
			    $product_name	= $item['name'];
			    $product_id		= $item['product_id'];
	/* 		    $product_variation_id	= $item['variation_id']; */
	
			    $headers	= 'From: Escuelaskibaqueira<ventas@escuelaskibaqueira.com>' . "\r\n";
			    $headers	.= "Content-type: text/html; charset=\"UTF-8\"; format=flowed \r\n";
		
			    $store_id	= get_post_meta($item['product_id'],'store_id',true);
			    $store_name = get_the_title($store_id); 
			    //$store_email = get_post_meta($store_id, 'email', true);
		
			    //Retailer email template
				$re_email	= get_post_meta($item['product_id'],'retailer_email',true);
				$re_subject	= 'Notificación compra en escuelaskibaqueira.com';
		
			    $re_content	= '<p>Se ha realizado una nueva compra de sus servicios:</p>';
			    $re_content	.= '<h3>Producto: '.$product_name.'</br>Cliente: '.$user->user_lastname.', '.$user->user_firstname.'</h3>';
			    if($book_date){
			    	$re_content	.= '<h2>Fecha: '.$book_date.' - '.$book_time.'</h2>';
			    }
			    $re_content	.= '<h2>Código: '.$code.'</h2>';
			    $re_content	.= '<p>Gracias por utilizar nuestros servicios<br><br>Equipo escuelaskibaqueira.com</p>';
			    
			    //Customer email template
				$cu_email	= $user->user_email;
				$cu_subject	= 'Código de compra escuelaskibaqueira.com';
		
				$cu_content	= '<p>Hola '.$user->user_firstname.',</p>';
				$cu_content	.= '<p>Aquí tiene el código de compra que debe entregar en el establecimiento.</p>';
			    $cu_content	.= '<h2>Código: '.$code.'</h2><br>';
			    $cu_content	.= '<h3>Producto: '.$product_name.'</h3>';
			    $cu_content	.= '<p>Gracias por utilizar nuestros servicios<br><br>Equipo escuelaskibaqueira.com</p>';
			    
			    wp_mail( $re_email, $re_subject, $re_content, $headers ); 
			    wp_mail( $cu_email, $cu_subject, $cu_content, $headers ); 
			    echo $re_content.'<br><hr><br>'; 
			    echo $cu_content;
			    die();
			}
		}
	
	}


} // end class

WooCommerce_Store_Pickup::get_instance();

} // endif