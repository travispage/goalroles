<?php

if( ! defined( 'ABSPATH' ) ) exit;

/**
 * Class WPJM_Pack_Admin_WC
 *
 * @property string label_fallback
 * @property string post_type
 * @property string product_type
 * @property string slug
 *
 * @since 1.0.0
 *
 */
class WPJM_Pack_Admin_WC {

	/**
	 * @var WPJM_Pack_Job|WPJM_Pack_Resume
	 */
	protected $type;

	/**
	 * WPJM_Pack_Admin_WC constructor.
	 *
	 * @param $type WPJM_Pack_Job|WPJM_Pack_Resume
	 * @param $config array
	 */
	public function __construct( $type, $config ) {

		$this->type = $type;

		add_filter( 'product_type_selector', array( $this, 'product_type_selector' ) );
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'hide_product_data_tabs' ) );
		add_action( 'woocommerce_product_options_general_product_data', array( $this, 'product_data' ) );

		add_action( "woocommerce_process_product_meta_{$config['product_type']}", array( $this, 'save_product' ) );
		add_action( "woocommerce_process_product_meta_{$config['sub_type']}", array( $this, 'save_product' ) );
		add_filter( 'woocommerce_subscription_product_types', array( $this, 'sub_product_types' ) );

		//add_filter( 'parse_query', array( $this, 'parse_query' ) );

		// No additional tabs for now, but maybe later
		//add_filter( 'woocommerce_product_data_tabs', array( $this, 'data_tabs' ) );
	}

	/**
	 * Get Shortcode HTML to output below Short Description
	 *
	 * Returns minified HTML that can be passed to jQuery to append to short description meta box
	 * @see $this->product_data();
	 *
	 * @since 1.0.0
	 *
	 * @return mixed|string
	 */
	public function get_shortcode_html(){

		ob_start();
		?>

		<div id="wpjmpack_<?php echo $this->slug ?>_short_description" class="show_if_<?php echo $this->product_type; ?> show_if_<?php echo $this->sub_type; ?>">
				<h4><?php _e( 'Available Shortcodes:', 'wp-job-manager-packages' ); ?></h4>
				<ul>
					<?php foreach( $this->get_excerpt_shortcodes() as $shortcode => $sc_conf ): ?>
						<li><code>[<?php echo $shortcode; ?>]</code> - <?php echo $sc_conf['desc']; ?></li>
					<?php endforeach; ?>
				</ul>
			</div>

		<?php

		$shortcode_html = ob_get_clean();

		// remove line breaks and tabs
		$shortcode_html = str_replace( array( "\r\n", "\r", "\n", "\t" ), '', $shortcode_html );
		// add slashes
		$shortcode_html = addslashes( $shortcode_html );

		return $shortcode_html;
	}

	/**
	 * Add Product in Dropdown
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $types
	 *
	 * @return mixed
	 */
	public function product_type_selector( $types ){

		$types[ $this->product_type ] = sprintf( __( '%s Visibility Package', 'wp-job-manager-packages' ), $this->get_label() );

		if( class_exists( 'WC_Subscriptions' ) ){
			$types[ $this->sub_type ] = sprintf( __( '%s Visibility Subscription', 'wp-job-manager-packages' ), $this->get_label() );
		}

		return $types;
	}

	/**
	 * Set Subscription Product Types
	 *
	 * This method should only be called for subscription types
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $types
	 *
	 * @return array
	 */
	public function sub_product_types( $types ){

		$types[] = $this->sub_type;

		return $types;
	}

	/**
	 * Hide Tabs
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function hide_product_data_tabs( $tabs ){

		$hide_classes = "hide_if_{$this->product_type} hide_if_variable_{$this->product_type}";
		//$tabs['attribute']['class'][] = $hide_classes;
		$tabs['shipping']['class'][] = $hide_classes;


		return $tabs;
	}

	/**
	 * Return Post Type Label
	 *
	 *
	 * @since 1.0.0
	 *
	 * @return mixed
	 */
	public function get_label(){

		$post_obj      = get_post_type_object( $this->post_type );
		$label_singular = is_object( $post_obj ) ? $post_obj->labels->singular_name : $this->label_fallback;

		if( ! $label_singular ){
			$label_singular = $this->label_fallback;
		}

		return $label_singular;

	}

	/**
	 * Output/Include Product Page Data/HTML
	 *
	 *
	 * @since 1.0.0
	 *
	 */
	public function product_data(){

		global $post;

		$vars = array(
			'post_id' => $post->ID,
		    'product_type' => $this->product_type,
		    'sub_type'	=> $this->sub_type,
		    'slug'  => $this->slug,
		    'label' => $this->get_label(),
		    'shortcode_html' => $this->get_shortcode_html(),
			'apply_label' => $this->type->packages->get_package_type( 'apply', 'verb', true ),
		);

		$view = "admin/wc/views/html-view-{$this->slug}-package-data";

		WP_Job_Manager_Packages::include_view( $view, $vars );
	}

	/**
	 * Add Tabs on Product Page
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $tabs
	 *
	 * @return mixed
	 */
	public function data_tabs( $tabs ){

		$tabs['fields'] = array(
			'label'  => __( 'Fields', 'wp-job-manager-packages' ),
			'target' => 'view_listing_options',
			'class'  => array( 'show_if_' . $this->product_type , 'show_if_variable_' . $this->product_type ),
		);

		return $tabs;
	}

	/**
	 * Save Package Field Data
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $post_id
	 */
	public function save_product( $post_id ){

		$slug = $this->type->slug;

		// Save meta
		$meta_to_save = (array) $this->get_meta_fields();

		foreach( $meta_to_save as $meta_key => $sanitize ) {
			$value = ! empty( $_POST[$meta_key] ) ? $_POST[$meta_key] : '';
			switch ( $sanitize ) {
				case 'int' :
					$value = absint( $value );
					break;
				case 'float' :
					$value = floatval( $value );
					break;
				case 'yesno' :
					$value = $value == 'yes' ? 'yes' : 'no';
					break;
				default :
					$value = sanitize_text_field( $value );
			}

			update_post_meta( $post_id, $meta_key, $value );
		}
	}

	/**
	 * Customize Query
	 *
	 * If we pass the specific variable in the GET request on the list table page, this method
	 * will automatically filter the query to only show the associated listings.
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $query
	 *
	 * @return mixed
	 */
	public function parse_query( $query ){

		global $typenow, $wp_query;

		if( 'job_listing' === $typenow || 'resume' === $typenow ){
			if( isset( $_GET['view_package'] ) ){
				$query->query_vars['meta_key']   = '_user_view_package_id';
				$query->query_vars['meta_value'] = absint( $_GET['view_package'] );
			}
		}

		return $query;
	}

	public static function multiple_select_field( $field ) {
		global $thepostid, $post, $woocommerce;

		$thepostid              = empty( $thepostid ) ? $post->ID : $thepostid;
		$field['class']         = isset( $field['class'] ) ? $field['class'] : 'select short';
		$field['wrapper_class'] = isset( $field['wrapper_class'] ) ? $field['wrapper_class'] : '';
		$field['name']          = isset( $field['name'] ) ? $field['name'] : $field['id'];
		$field['value']         = isset( $field['value'] ) ? $field['value'] : ( get_post_meta( $thepostid, $field['id'], true ) ? get_post_meta( $thepostid, $field['id'], true ) : array() );

		echo '<p class="form-field ' . esc_attr( $field['id'] ) . '_field ' . esc_attr( $field['wrapper_class'] ) . '"><label for="' . esc_attr( $field['id'] ) . '">' . wp_kses_post( $field['label'] ) . '</label><select id="' . esc_attr( $field['id'] ) . '" name="' . esc_attr( $field['name'] ) . '" class="' . esc_attr( $field['class'] ) . '" multiple="multiple">';

		foreach ( $field['options'] as $key => $value ) {

			echo '<option value="' . esc_attr( $key ) . '" ' . ( in_array( $key, $field['value'] ) ? 'selected="selected"' : '' ) . '>' . esc_html( $value ) . '</option>';

		}

		echo '</select> ';

		if ( ! empty( $field['description'] ) ) {

			if ( isset( $field['desc_tip'] ) && false !== $field['desc_tip'] ) {
				echo '<img class="help_tip" data-tip="' . esc_attr( $field['description'] ) . '" src="' . esc_url( WC()->plugin_url() ) . '/assets/images/help.png" height="16" width="16" />';
			} else {
				echo '<span class="description">' . wp_kses_post( $field['description'] ) . '</span>';
			}

		}
		echo '</p>';
	}

	/**
	 * Magic Method for GET
	 *
	 *
	 * @since 1.0.0
	 *
	 * @param $key
	 *
	 * @return mixed
	 */
	public function __get( $key ){

		return $this->type->wc->$key;
	}
}
