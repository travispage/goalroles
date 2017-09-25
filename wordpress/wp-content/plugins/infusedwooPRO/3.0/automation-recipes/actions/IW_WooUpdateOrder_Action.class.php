<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_WooUpdateOrder_Action extends IW_Automation_Action {
	function get_title() {
		return "Update Order Record in Woocommerce";
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger',
				'IW_Checkout_Trigger'
			);
	}

	function on_class_load() {
		add_action( 'adm_automation_recipe_after', array($this, 'woo_ofield_script'));
	}

	function get_order_fields() {
		global $iwpro; 
		$merge_fields = array(
				'order_status' => 'Order Status',
				'new_line_item' => 'Add Order Item',
				'order_note'	=> 'Add Order Note (Admin)',
				'customer_note' => 'Customer Note',
				'add_discount' => 'Add Discount Line',
				'add_fee' 		=> 'Add Custom Fee Line',
				'custom_meta_value' => 'Custom Meta Value',
				'_billing_first_name' => 'Billing First Name',
                '_billing_last_name'  => 'Billing Last Name',
                '_billing_company'    => 'Billing Company',
                '_billing_address_1'  => 'Billing Address 1',
                '_billing_address_2'  => 'Billing Address 2',
                '_billing_city'       => 'Billing City',
                '_billing_state'      => 'Billing State',
                '_billing_postcode'   => 'Billing PostCode',
                '_billing_country'    => 'Billing Country',
                '_billing_email'      => 'Billing Email',
                '_billing_phone'      => 'Billing Phone',
                '_shipping_first_name' => 'Shipping First Name',
                '_shipping_last_name'  => 'Shipping Last Name',
                '_shipping_company'    => 'Shipping Company',
                '_shipping_address_1'  => 'Shipping Address 1',
                '_shipping_address_2'  => 'Shipping Address 2',
                '_shipping_city'       => 'Shipping City',
                '_shipping_state'      => 'Shipping State',
                '_shipping_postcode'   => 'Shipping PostCode',
                '_shipping_country'    => 'Shipping Country'
			);

		return $merge_fields;
	}

	function woo_ofield_script() {
		$merge_fields = $this->get_order_fields();

		?>
		<script>
		var iwar_woorder_fields = <?php echo json_encode($merge_fields) ?>;

		jQuery("body").on("click", ".woo_ofield_add", function() {
			var $fieldarea = jQuery(this).parent().children(".iwar_owoo_fields");
			var htm = '<div class="owoo_field"><select name="ofields[]" style="width: 45%">';
			htm += '<option value="">Select Order Field...</option>';

			for(fld in iwar_woorder_fields) {
				htm += '<option value="'+fld+'">'+iwar_woorder_fields[fld]+'</option>';
			}
			htm += '</select>&nbsp;&nbsp;&nbsp;<input type="text" name="ovalues[]" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable" />';
			htm += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			htm += '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" title="Remove Field" aria-hidden="true"></i></div>';
			$fieldarea.append(htm);
			return false;
		});

		jQuery("body").on("change",".woorder_id_sel", function() {
			if(jQuery(this).val() == 'custom_order_id') {
				jQuery(this).after('<input type="text" style="width: 200px;" name="order_id" value="{{WPUser:LastOrderId}}" class="iwar-mergeable" /><i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>');
				jQuery(this).remove();
			}
		});

		</script>
		<?php
	}

	function display_html($config = array(), $trigger_class = "") {
		$ofields = isset($config['ofields']) ? $config['ofields'] : array('');
		$ovalues = isset($config['ovalues']) ? $config['ovalues'] : array('');
		$order_id = isset($config['order_id']) ? $config['order_id'] : '';

		$fields = $this->get_order_fields();
		$html = '<div class="iwar_owoo_fields">';

		foreach($ofields as $i => $ofield) {
			$html .= '<div class="owoo_field">';
			$html .= '<select name="ofields[]" style="width: 45%">';
			$html .= '<option value="">Select Order Field...</option>';
			foreach($fields as $k => $field) {
				$html .= '<option value="'.$k.'"'.($k == $ofields[$i] ? ' selected ' : "").'>'.$field.'</option>';
			}
			$html .= '</select>';
			$html .= '&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="ovalues[]" value="'.$ovalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<a href="#" class="woo_ofield_add">Add more fields ...</a>';
		$html .= '<hr>';
		$html .= 'Order ID to Update:&nbsp;&nbsp;';

		if(in_array($order_id, array('','{{WPUser:LastOrderId}}', 'current_order_id'))) {
			$html .= '<select name="order_id" class="woorder_id_sel">';

			if(in_array($trigger_class, array('IW_OrderCreation_Trigger','IW_OrderStatusChange_Trigger','IW_Purchase_Trigger'))) {
				$html .= '<option value="current_order_id"'.($order_id == 'current_order_id' ? ' selected ' : '').'>Triggered Order\'s ID</option>';
			}

			$html .= '<option value="{{WPUser:LastOrderId}}"'.($order_id == '{{WPUser:LastOrderId}}' ? ' selected ' : '').'>User\'s Last Woo Order</option>';
			$html .= '<option value="custom_order_id">Custom Order ID...</option>';
			$html .= '</select>';
		} else {
			$html .= '<input type="text" style="width: 200px;" name="order_id" value="'.$order_id.'" class="iwar-mergeable" /><i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
		}
		
		return $html;
	}

	function validate_entry($config) {
		if(!is_array($config['ofields'])) return "Please ensure that all order fields are not empty";
		
		foreach($config['ofields'] as $k => $key) {
			if(empty($key)) return "Please ensure all order fields are not empty";
			else if(strpos($config['ovalues'][$k], '{{') === false) {
				if($key == 'order_status') {
					$order_statuses = wc_get_order_statuses();
					$order_statuses = array_keys($order_statuses);
					if(!in_array('wc-' . $config['ovalues'][$k], $order_statuses)) {
						$allowed = array();
						foreach($order_statuses as $stat) {
							$allowed[] = str_replace("wc-", '', $stat);
						}
						$allowed = implode(", ", $allowed);
						return "Order Status can only have these values: $allowed";
					}
				} else if($key == 'new_line_item') {
					if(strpos($config['ovalues'][$k], '|') === false || count(explode("|",$config['ovalues'][$k])) < 2) {
						return 'New Line Item Format should be: WooProductID|Quantity';
					}
				} else if($key == 'add_discount') {
					if(strpos($config['ovalues'][$k], '|') === false || count(explode("|",$config['ovalues'][$k])) < 2) {
						return 'Discount Item Format should be: DiscountCode|DiscountAmount';
					}
				} else if($key == 'add_fee') {
					if(strpos($config['ovalues'][$k], '|') === false || count(explode("|",$config['ovalues'][$k])) < 2) {
						return 'Fee Item Format should be: FeeName|FeeAmount';
					}
				} else if($key == 'custom_meta_value') {
					if(strpos($config['ovalues'][$k], '=') === false || count(explode("=",$config['ovalues'][$k])) < 2) {
						return 'Custom Meta Format should be: MetaKey=MetaValue';
					}
				}
			}
		}
	}

	function process($config, $trigger) {
		// find order id
		if($config['order_id'] == 'current_order_id') {
			$order_id = $trigger->pass_vars[0];
		} else {
			$order_id = $trigger->merger->merge_text($config['order_id'] );
		}

		// now update the order
		if($order_id > 0) {
			global $iwpro;
			$wc_order = new WC_Order($order_id);
			$upd = array();

			foreach($config['ofields'] as $k => $key) {
				$val = $trigger->merger->merge_text($config['ovalues'][$k]);

				if($key == 'order_status') {
					$wc_order->update_status($val);
				} else if($key == 'new_line_item') {
					$args = explode("|", $val);
					$product = new WC_Product((int) $args[0]);
					$wc_order->add_product($product,  (int) $args[1]);
				} else if($key == 'order_note') {
					$wc_order->add_order_note($val);
				} else if($key == 'customer_note') {
					$wc_order->add_order_note($val,1);
				} else if($key == 'add_discount') {
					$args = explode("|", $val);
					$subtotal = $wc_order->get_subtotal();

					if(strpos($args[1], '%') !== false) {
						$perc = (float) str_replace('%', '', $args[1]);
						$disc = round(($perc / 100.0) * $subtotal,2);
					} else {
						$disc = (float) $args[1];
					}

					$custom = new WC_Shipping_Rate;
					$custom->label =  "Discount: " . $args[0];
					$custom->cost = -$disc;
					$custom->name =  "Discount: " . $args[0];
					$custom->amount = -$disc;
					$custom->taxable = false;

					$wc_order->add_fee( $custom );

				} else if($key == 'add_fee') {
					$args = explode("|", $val);
					$subtotal = $wc_order->get_subtotal();

					if(strpos($args[1], '%') !== false) {
						$perc = (float) str_replace('%', '', $args[1]);
						$fee_amount = round(($perc / 100.0) * $subtotal,2);
					} else {
						$fee_amount = (float) $args[1];
					}

					$custom = new WC_Shipping_Rate;
					$custom->label =  $args[0];
					$custom->cost = $fee_amount;
					$custom->name =  $args[0];
					$custom->amount = $fee_amount;
					$custom->taxable = false;

					$wc_order->add_fee( $custom );

				}  else if($key == 'custom_meta_value') {
					$args = explode("=", $val);
					$meta_key = $args[0];

					if(count($args) > 2) {
						$meta_val = $args;
						unset($meta_val[0]);
						$meta_val = implode("=", $meta_val);
					} else {
						$meta_val = $args[1];
					}
					$meta_val = $trigger->merger->merge_text($meta_val);
					update_post_meta( $wc_order->id, $meta_key, $meta_val );
				} else {
					update_post_meta( $wc_order->id, $key, $val );
				}

				$wc_order->calculate_totals();
			}
		}
	}
}

iw_add_action_class('IW_WooUpdateOrder_Action');