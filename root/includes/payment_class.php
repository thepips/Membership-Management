<?php
/**
* @package payment_class.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

class payment_class
{
	var $last_error;				 // holds the last error encountered
	
	var $logging_enabled;					// bool: log IPN results to text file?
	
	var $log_file;				// filename of the IPN log
	var $EC_data = array();		 // array contains the POST values for IPN
	
	var $fields = array();			// array holds the fields

/**
* initialization constructor.  Called when class is created.

*/

	function __construct()
	{
		global $config, $user;
		
		// populate $fields array with a few default values.  See the paypal
		// documentation for a list of fields and their data types. These default
		// values can be overwritten by the calling script.

		$this->last_error = '';
		$this->subscriber_id = '';
		
		$this->log_file = '.results.log';
		if (!isset($this->fields['METHOD']))
		{
			$this->fields['BRANDNAME'] = $config['pp_paypal_co_name'];
			$this->fields['EMAIL'] = $user->data['user_email'];
			$this->fields['PAYMENTREQUEST_0_CURRENCYCODE']	= $config['pp_paypal_currency_code'];
			$this->fields['METHOD'] = 'SetExpressCheckout';
			$this->fields['PAYMENTREQUEST_0_AMT'] = 0;
//			$this->fields['PAYMENTREQUEST_n_NOTIFYURL'] = 
		}
		$this->logging_enabled = true; 
		$this->hosted = false;
		
		$this->retrieve_shopping_basket();
	}

/**
* adds a key=>value pair to the fields array, which is what will be 
* available to the payment class as variables.  If the value is already in the 
* array, it will be overwritten.
*/
	public function add_field($field, $value)
	{
		$this->fields["$field"] = $value;
	}
/**
* Adds a line into the shopping cart. If num is not passed then the function finds the next available line number
*/
	public function add_cart_item($num='', $desc, $price, $qty=1)
	{
		global $config;				
		if (is_null($num))
		{
			for ($num=1;;$num++)
			{
				if (!isset($this->fields["PAYMENTREQUEST_0_DESC{$num}"]))
				{
					break; 
				}
			}
		}

		$this->add_field("PAYMENTREQUEST_0_DESC", $desc); 
		$this->add_field("PAYMENTREQUEST_0_DESC{$num}", $desc); 
		$this->add_field("PAYMENTREQUEST_0_QTY{$num}", $qty); 
		$this->add_field("PAYMENTREQUEST_0_AMT{$num}", sprintf("%01.2f", $price)); 
		$this->add_field("PAYMENTREQUEST_0_CURRENCYCODE{$num}", $config['pp_paypal_currency_code']);
		$this->add_field("PAYMENTREQUEST_0_PAYMENTACTION{$num}", 'Sale');
		$this->preserve_shopping_basket();
	}

	public function cancel_cart_item($num)
	{
		unset($this->fields["PAYMENTREQUEST_0_DESC"]); 
		unset($this->fields["PAYMENTREQUEST_0_DESC{$num}"]); 
		unset($this->fields["PAYMENTREQUEST_0_QTY{$num}"]); 
		unset($this->fields["PAYMENTREQUEST_0_AMT{$num}"]); 
		unset($this->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"]);
		unset($this->fields["PAYMENTREQUEST_0_PAYMENTACTION{$num}"]); 
//		unset($this->fields["PAYMENTREQUEST_0_INVNUM"]); 
		$this->preserve_shopping_basket();
	}

/**
* Adds a subscription specific line into the shopping cart. Only a single subscription is supported
*/
	public function add_subscription_item($invnum, $num, $desc, $price, $startdate, $joining, $billing_cycle = '1', $billing_cycle_basis='y', $extra_days='0', $joining_fee)
	{
		$num=0;
		$this->add_field('CURRENCYCODE', $this->fields['PAYMENTREQUEST_0_CURRENCYCODE']);
		$this->add_field("L_BILLINGTYPE{$num}",'RecurringPayments');
		$this->add_field("L_BILLINGAGREEMENTDESCRIPTION{$num}", $desc);
		$this->add_field("DESC", $desc);
		$this->add_field('PROFILESTARTDATE', gmdate('c', $startdate));
		$this->add_field('PROFILEREFERENCE', $invnum);
		$this->add_field("PAYMENTREQUEST_0_DESC{$num}", $desc);
		$this->add_field("PAYMENTREQUEST_0_CURRENCYCODE{$num}", $this->fields["PAYMENTREQUEST_0_CURRENCYCODE"]);
		$this->add_field("PAYMENTREQUEST_0_ITEMCATEGORY{$num}", 'Digital');
		$this->add_field("PAYMENTREQUEST_0_NAME{$num}", 'Online Membership');
		$this->add_field("PAYMENTREQUEST_0_QTY{$num}", 1);
		$this->add_field("PAYMENTREQUEST_0_AMT{$num}", sprintf("%01.2f", $price));
		$this->add_field('PAYMENTREQUEST_0_AMT', sprintf("%01.2f", $price));
				
		$billing_periods = array(
			'd' => 'Day',
			'w' => 'Week',
			'm' => 'Month',
			'y' => 'Year'
			);
		$this->add_field('BILLINGPERIOD', $billing_periods[$billing_cycle_basis]);
		$this->add_field('BILLINGFREQUENCY', $billing_cycle);
		if ($extra_days>0)
		{
			$this->add_field('TRIALBILLINGFREQUENCY', $extra_days);
			$this->add_field('TRIALBILLINGPERIOD', 'Day');
			$this->add_field('TRIALTOTALBILLINGCYCLES', 1);
		}

		if($joining && $joining_fee>0)
			{
			$this->add_field('INITAMT', sprintf("%01.2f", $joining_fee));
			$this->add_field('FAILEDINITAMTACTION', 'CancelOnFailure');
		}

		$this->preserve_shopping_basket();
	}

	public function cancel_subscription_item($num)
	{
		unset($this->fields['INITAMT']);
		unset($this->fields['CURRENCY']);
		unset($this->fields['FAILEDINITAMTACTION']);
		unset($this->fields["L_BILLINGTYPE{$num}"]);
		unset($this->fields["L_BILLINGAGREEMENTDESCRIPTION{$num}"]);
		unset($this->fields['PROFILESTARTDATE']);
		unset($this->fields['PROFILEREFERENCE']);
		unset($this->fields['PAYMENTREQUEST_0_DESC']); 
		unset($this->fields['BILLINGPERIOD']);
		unset($this->fields['BILLINGFREQUENCY']);
		unset($this->fields['TRIALBILLINGFREQUENCY']);
		unset($this->fields['TRIALBILLINGPERIOD']);
		unset($this->fields['TRIALTOTALBILLINGCYCLES']);
		unset($this->fields['PAYMENTREQUEST_0_AMT']);
		unset($this->fields['INITAMT']);
		unset($this->fields['FAILEDINITAMTACTION']);

		$this->preserve_shopping_basket();
	}

	public function cancel_subscription($subscriber_id='', $groupid, $userid)
	{
		global $db;
		// if called from application we know group/user find out subscriber	
		// if called from ipnlistener we know subscriber
		if (empty($subscriber_id))
		{		
			$sql = 'SELECT subscriber_id FROM ' . MEMBERSHIP_TABLE . " WHERE user_id = '{$userid}' AND group_id = '{$groupid}'";		
			$result =$db->sql_query($sql);		
			$subscriber_id = $db->sql_fetchfield('subscriber_id');				
		}
		$sql = 'UPDATE ' . MEMBERSHIP_TABLE . " SET subscriber_id='', portal='' WHERE group_id='{$groupid}' AND user_id='{$userid}'";
		$db->sql_query($sql);	
		log_message('LOG_USER_CANCELED_SUBSCRIPTION', $userid, $groupid);	
		$this->remove_shopping_basket();
		
		if (function_exists('cancel_recurring_payment'))
		{
			cancel_recurring_payment($subscriber_id, $groupid, $userid);
		}

		return;
	}
	
	public function calc_basket_total()
	{
		$total_amount = 0;
		for ($num=0;$num<10;$num++)
		{
			if (isset($this->fields["PAYMENTREQUEST_0_AMT{$num}"]))
			{
				$total_amount = $total_amount + $this->fields["PAYMENTREQUEST_0_AMT{$num}"];
			}
		}

		if (isset($this->fields["INITAMT"]))
		{
				$total_amount = $total_amount + $this->fields['INITAMT'];
		}
		$this->fields['PAYMENTREQUEST_0_AMT']=sprintf("%01.2f", $total_amount);
		return $total_amount;
	}
	
	public function preserve_shopping_basket()
	{
		global $user, $db;

		if (!empty($this->params))
		{
			$string='';
			foreach ($this->params as $field=>$value)
			{
				if(!empty($string))
				{
					$string .= '&';
				}
				$string .= $field . '=' . $value;
			}
			$this->fields['PAYMENTREQUEST_0_CUSTOM'] = $string;
		}
		$x=serialize($this->fields);
		
		// update session table
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
		  SET shopping_basket = "' . $db->sql_escape($x) . '"
		  WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
		$db->sql_query($sql);
	}

	public function retrieve_shopping_basket()
	{
		global $user, $db;
		$sql = 'SELECT shopping_basket FROM ' . SESSIONS_TABLE . '
		  WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
		$result = $db->sql_query($sql);
		$shopping_basket	= $db->sql_fetchfield('shopping_basket');
		if (!empty($shopping_basket))
		{
			$this->fields	= unserialize($shopping_basket);
			if (isset($this->fields['PAYMENTREQUEST_0_CUSTOM']))
			{
				$this->params	= $this->parse_response($this->fields['PAYMENTREQUEST_0_CUSTOM']);
			}
		}
	}

	public function remove_shopping_basket()
	{
		global $user, $db;
		$sql = 'UPDATE ' . SESSIONS_TABLE . '
		  SET shopping_basket = ""
		  WHERE session_id = "' . $db->sql_escape($user->session_id) . '"';
		$db->sql_query($sql);
		$shopping_basket=null;
	}

	public function checkout()
	{
		parent::calc_basket_total();	
		parent::preserve_shopping_basket();
		
		return true;
	}

	public function take_payment()
	{
		global $config;
		parent::retrieve_shopping_basket();
		if ($this->fields==null)
		{
			trigger_error("I'm sorry but your session has expired");
			return 'expired';
		}
		parent::remove_shopping_basket();
		return $config['ms_process_on_payment'];
	}

	function write_results($text)
	{
		// Write to log
		$fp=fopen($this->log_file,'a');
		fwrite($fp,date('r') . ' ');
		if (!is_array($text))
		{
			$text = (array) $text;						
		}
		foreach($text as $part_text)
		{
			fwrite($fp, $this->build_message($part_text) . "\n\n");
		} 
		fwrite($fp, "============\n\n");
		fclose($fp);  // close file
	}

	function debug($file, $line, $data, $title='')
	{
		global $config, $user;
		$x = substr($file, strlen(getcwd()));
		$this->write_results(date('r') . ' ' . $title ."\n\nFile:".$x . " Line:" .$line. "\n".$this->build_message($data));
	}

	function dump_fields($data, $title='dump_fields() Output:')
	{
 
		// Used for debugging, this function will output all the field/value pairs
		// that are currently defined in the instance of the class using the
		// add_field() function.
		ob_start();	  
		echo	date('r') . ' ' . $title . "\n";
		$this->build_message($data);
		echo	"\n============================\n";
		$log = ob_get_contents();
		$this->write_results($log);
		ob_end_clean();
		return;
	}
//		echo	'<h3>' . date('r') . ' ' . $title . '</h3>';
//		echo	"<table width=\"95%\" border=\"1\" cellpadding=\"2\" cellspacing=\"0\">";
//		echo	"	<tr>";
//		echo	"<td bgcolor=\"black\"><b><font color=\"white\">Field Name</font></b></td>";
//		echo	"<td bgcolor=\"black\"><b><font color=\"white\">Value</font></b></td>";
//		echo	"</tr>";
//		$this->build_message($data);
//		echo	"</table><br>";
//		echo	"\n============================\n";
//		$log = ob_get_contents();
//		$this->write_results($log);
//		ob_end_clean();
//		return;
//	}
	function build_message($data, $title='')
	{
		$string='';
		if (!is_array($data))
		{
			$data = (array) $data;
		}
		foreach ($data as $key => $value)
		{
			$string .= $title.$key . " = "; 
			if (is_array($value))
			{
				$string .= "Array\n";
				$string .= $this->build_message($value, $key.'/');
			}
			else
			{		  
				$string .= "'" . urldecode($value) . "'\n";
			}
		}
		return $string;
	}
	
	function parse_response($response, $sep = '&', $assign = '=')
	{
		$arguments=explode($sep, $response);
		$output = array();
		foreach ($arguments as $argument)
		{
			$nvp=explode($assign,$argument);
			if(isset($nvp[1]))
			{
				$output[$nvp[0]]=urldecode($nvp[1]);
			}
			else
			{
				$output[$nvp[0]]='';
			}
		}
		return($output);
	}
	
}
?>