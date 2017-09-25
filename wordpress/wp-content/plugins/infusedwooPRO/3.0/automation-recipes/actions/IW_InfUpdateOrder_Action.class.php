<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class IW_InfUpdateOrder_Action extends IW_Automation_Action {
	function get_title() {
		return "Update Order Record in Infusionsoft";
	}

	function allowed_triggers() {
		return array(
				'IW_HttpPost_Trigger',
				'IW_OrderCreation_Trigger',
				'IW_OrderStatusChange_Trigger',
				'IW_Purchase_Trigger',
				'IW_UserAction_Trigger'
			);
	}

	function on_class_load() {
		add_action( 'adm_automation_recipe_after', array($this, 'inf_ofield_script'));
	}

	function get_order_fields() {
		global $iwpro; 
		$merge_fields = array(
				'DueDate' => 'Order Date',
				'JobNotes' => 'Order Notes',
				'JobTitle' => 'Order Title',
				'OrderStatus' =>'Pay Status',
				'OrderType' => 'Order Type',
				'ShipCity' => 'Ship City',
				'ShipCompany' => 'Ship Company',
				'ShipCountry' => 'Ship Country',
				'ShipFirstName' => 'Ship First Name',
				'ShipLastName' => 'Ship Last Name',
				'ShipMiddleName' => 'Ship Middle Name',
				'ShipPhone' => 'Ship Phone',
				'ShipState' => 'Ship State',
				'ShipStreet1' => 'Ship Street 1',
				'ShipStreet2' => 'Ship Street 2',
				'ShipZip' => 'Ship Zip',
				'StartDate' => 'StartDate'

			);

		if($iwpro->ia_app_connect()) {
			$custfields = $iwpro->app->dsFind("DataFormField", 200,0, "FormId", -9, array("Name","Label","DataType"));
			if(is_array($custfields) && count($custfields) > 0) {
				foreach($custfields as $custfield) {
					$merge_fields["_" . $custfield["Name"]] = "Custom Field: " . $custfield["Label"];
				}
			}
		}

		return $merge_fields;
	}

	function inf_ofield_script() {
		$merge_fields = $this->get_order_fields();

		?>
		<script>
		var iwar_order_fields = <?php echo json_encode($merge_fields) ?>;

		jQuery("body").on("click", ".inf_ofield_add", function() {
			var $fieldarea = jQuery(this).parent().children(".iwar_oinf_fields");
			var htm = '<div class="oinf_field"><select name="ofields[]" style="width: 45%">';
			htm += '<option value="">Select Order Field...</option>';

			for(fld in iwar_order_fields) {
				htm += '<option value="'+fld+'">'+iwar_order_fields[fld]+'</option>';
			}
			htm += '</select>&nbsp;&nbsp;&nbsp;<input type="text" name="ovalues[]" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable" />';
			htm += '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			htm += '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" title="Remove Field" aria-hidden="true"></i></div>';
			$fieldarea.append(htm);
			return false;
		});

		jQuery("body").on("change",".order_id_sel", function() {
			if(jQuery(this).val() == 'custom_order_id') {
				jQuery(this).after('<input type="text" style="width: 200px;" name="order_id" value="{{InfContact:LastOrderId}}" class="iwar-mergeable" /><i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>');
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
		$html = '<div class="iwar_oinf_fields">';

		foreach($ofields as $i => $ofield) {
			$html .= '<div class="oinf_field"><select name="ofields[]" style="width: 45%">';
			$html .= '<option value="">Select Order Field...</option>';
			foreach($fields as $k => $field) {
				$html .= '<option value="'.$k.'"'.($k == $ofields[$i] ? ' selected ' : "").'>'.$field.'</option>';
			}
			$html .= '</select>&nbsp;&nbsp;&nbsp;';
			$html .= '<input type="text" name="ovalues[]" value="'.$ovalues[$i].'" style="width: 45%" placeholder="Desired Value..." class="iwar-mergeable"  />';
			$html .= '<i class="fa fa-compress merge-button merge-overlap" aria-hidden="true" title="Insert Merge Field"></i>';
			if($i > 0) $html .= '&nbsp;&nbsp;<i style="color:red; font-style: 11pt; cursor:pointer; position: relative; top: 1px; left: 1px" class="fa fa-minus-circle" aria-hidden="true" title="Remove Field"></i>';
			$html .= '</div>';
		}

		$html .= '</div>';
		$html .= '<a href="#" class="inf_ofield_add">Add more fields ...</a>';
		$html .= '<hr>';
		$html .= 'Order ID to Update:&nbsp;&nbsp;';

		if(in_array($order_id, array('','{{InfContact:LastOrderId}}', 'current_order_id'))) {
			$html .= '<select name="order_id" class="order_id_sel">';

			if(in_array($trigger_class, array('IW_OrderCreation_Trigger','IW_OrderStatusChange_Trigger','IW_Purchase_Trigger'))) {
				$html .= '<option value="current_order_id"'.($order_id == 'current_order_id' ? ' selected ' : '').'>Triggered Order\'s ID</option>';
			}

			$html .= '<option value="{{InfContact:LastOrderId}}"'.($order_id == '{{InfContact:LastOrderId}}' ? ' selected ' : '').'>Contact\'s Last Order</option>';
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
				if($key == 'OrderStatus') {
					if(!in_array($config['ovalues'][$k], array('Paid', 'Unpaid'))) {
						return 'Pay Status can only have these values: "Paid" and "Unpaid"';
					}
				} else if($key == 'OrderType') {
					if(!in_array($config['ovalues'][$k], array('Online', 'Offline'))) {
						return 'Order Type can only have these values: "Online" and "Offline"';
					}
				} else if($key == 'DueDate' || $key == 'StartDate') {
					if(strtotime($config['ovalues'][$k]) <= 0) {
						return 'Due Date must be a valid date format (YYYY-MM-DD)';
					}
				}
			}
		}
	}

	function process($config, $trigger) {
		// find order id
		if($config['order_id'] == 'current_order_id') {
			$order_id = $trigger->pass_vars[0];
			$inf_order_id = get_post_meta( $order_id, 'infusionsoft_order_id', true );

			if(empty($inf_order_id)) {
				$order = new WC_Order($order_id);
				$order_status = $order->get_status();

				if($order->is_paid()) {
					ia_woocommerce_payment_complete( $order_id, false, false);
				} else {
					ia_woocommerce_payment_complete( $order_id, false, true);
				}
			}

			$inf_order_id = get_post_meta( $order_id, 'infusionsoft_order_id', true );
		} else {
			$inf_order_id = $trigger->merger->merge_text($config['order_id'] );
		}

		// now update the order
		if($inf_order_id > 0) {
			global $iwpro;
			$upd = array();
			$iwpro->ia_app_connect();

			foreach($config['ofields'] as $k => $key) {
				$val = $trigger->merger->merge_text($config['ovalues'][$k]);

				if($key == 'OrderStatus') {
					$upd['JobStatus'] = $val;
					if($val == 'Paid') {
						$upd[$key] = 0;
						$recipe_id = $trigger->recipe_id_proc;
						$inv = $iwpro->app->dsFind('Invoice',1,0,'JobId',$inf_order_id, array('Id'));

						if(isset($inv[0]['Id'])) {
							$inv_id = (int) $inv[0]['Id'];
							$orderDate = date('Ymd\TH:i:s', current_time('timestamp'));
							$totals = (float) $iwpro->app->amtOwed($inv_id);
							if($totals > 0)
								$iwpro->app->manualPmt($inv_id, $totals, $orderDate, 'Manual Payment', "InfusedWoo Recipe # $recipe_id",false);
						}
					} else {
						$upd[$key] = 1;
					}
				} else if($key == 'DueDate' || $key == 'StartDate') {
					$upd[$key] = date("Y-m-d", strtotime($val));
				} else {
					$upd[$key] = $val;
				}
			}

			if(count($upd) > 0) {
				$iwpro->app->dsUpdate('Job', $inf_order_id, $upd);
			}
		}
	}
}

iw_add_action_class('IW_InfUpdateOrder_Action');