<?php
	/**
	 * Plugin Name: Primary Category
	 * Plugin URI:
	 * Description: Set primary category for posts
	 * Author: matheusgimenez
	 * Author URI: http://matheusgimenez.com.br
	 * Version: 1.0.0
	 * License: GPLv2 or later
	 * Text Domain: primary-category
 	 * Domain Path: /languages/
	 */

	if ( ! defined( 'ABSPATH' ) )
		exit; // Exit if accessed directly.

	/**
	 * Primary_Category
	 * 
	 * @author Matheus Gimenez (contato@matheusgimenez.com.br)
	 */
	class Primary_Category {
		/**
		 * Instance of this class.
		 *
		 * @var object
		 */
		protected static $instance = null;

		/**
		 * @var string
		 */
		private $nested_level_space = '';

		/**
		 * Metabox field name
		 * @var string
		 */
		public $field_name = '_primary_category';

		/**
		 * Initialize the plugin
		 */
		private function __construct() {
			// Load plugin text domain
			add_action( 'plugins_loaded', array( $this, 'load_plugin_textdomain' ) );

			// Init Primary Category Metabox
			add_action( 'add_meta_boxes', array( $this, 'init_metabox' ) );

			// Save Primary Category field
			add_action( 'save_post_post', array( $this, 'save_meta' ), 99, 1 );
		}
		/**
		 * 
		 * Init metabox system
		 * @since 1.0.0
		*/
		public function init_metabox() {
			// Adds the metabox
			add_meta_box( 'primary_category_meta', __( 'Primary Category', 'primary-category' ), array( $this, 'display_metabox' ), 'post', 'side', 'default', null );
		}
		/**
		 * Display each category in the <select> metabox
		 * @param object $category 
		 * @since 1.0.0
		 */
		private function display_each_category_option( $category, $current_category ) {
			$selected = '';
			if ( $current_category === $category->term_id ) {
				$selected = 'selected';
			}
			echo '<option value="' . $category->term_id . '" '. $selected . '>';
			if ( $category->parent && $category->parent > 0 ) {
				echo $this->nested_level_space;
			}
			echo $category->name;
			echo '</option>';

			$categories = get_categories( array( 'hide_empty' => false, 'parent' => $category->term_id ) );
			if ( $categories && ! is_wp_error( $categories ) ) {
				foreach( $categories as $category ) {
					$this->nested_level_space .= '&nbsp;&nbsp;&nbsp;';
					$this->display_each_category_option( $category, $current_category );
				}
				return;
			}
			$this->nested_level_space = '';
		}
		/**
		 * Display metabox options (<select> input)
		 * @since 1.0.0
		 */
		public function display_metabox() {
			// get current selected category id
			$current_category = get_post_meta( get_the_ID(), $this->field_name, true );
			// get top-level categories only
			$categories = get_categories( array( 'hide_empty' => false, 'parent' => 0 ) );
			if ( ! $categories || is_wp_error( $categories ) ) {
				return;
			}

			echo '<select name="primary-category">';
			// set default option
			if ( 'false' === $current_category || ! $current_category ) {
				echo '<option value="false" selected>' . __( 'Without Primary Category', 'primary-category' ) . '</option>';
			} else {
				echo '<option value="false">' . __( 'Without Primary Category', 'primary-category' ) . '</option>';
			}
			$current_category = absint( $current_category );
			// display all categories in options
			foreach( $categories as $category ) {
				$this->display_each_category_option( $category, $current_category );
			}
			echo '</select>';
		}
		/**
		 * Save metabox
		 * @param int $post_id 
		 * @return void
		 * @since 1.0.0
		 */
		public function save_meta( $post_id ) {
			if ( ! isset( $_REQUEST[ 'primary-category' ] ) ) {
				return;
			}
			if ( 'false' === $_REQUEST[ 'primary-category' ] ) {
				delete_post_meta( $post_id, $this->field_name );
				return;
			}
			$value = absint( $_REQUEST[ 'primary-category'] );
			if ( 0 === $value ) {
				return;
			}
			update_post_meta( $post_id, $this->field_name, $value, null );
		}
		/**
		 * Return an instance of this class.
		 *
		 * @return object A single instance of this class.
		 */
		public static function get_instance() {
			// If the single instance hasn't been set, set it now.
			if ( null == self::$instance ) {
				self::$instance = new self;
			}

			return self::$instance;
		}


		/**
		 * Load the plugin text domain for translation.
		 *
		 * @return void
		 */
		public function load_plugin_textdomain() {
			load_plugin_textdomain( 'primary-category', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		}

	} // end class Primary_Category();
	add_action( 'plugins_loaded', array( 'Primary_Category', 'get_instance' ), 0 );
