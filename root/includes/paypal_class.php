<?php
/**
* @package paypal_class.php
* @copyright (c) DougA http://action-replay.co.uk 2011
* @license http://opensource.org/licenses/gpl-license.php GNU Public License
*
*/
if (!defined('IN_PHPBB'))
{
	exit;
}

/*******************************************************************************
 *					  PHP Paypal Express Checkout API Integration Class
 *******************************************************************************
 *  DESCRIPTION:
 *
 *	  This file tries to provides a neat and simple method to interface with paypal express checkout
 *	  It's NOT intended to make the paypal integration "plug 'n' play" but does go a long way towards that. 
 *	  It still requires the developer (i.e. you) to understand the paypal process and know the variables to
 *		pass to paypal to achieve what you want.  
 *
 *	  This class handles the submission of an order to paypal aswell as the
 *	  processing the returned values.
 *  
 *
 *	  In case you are new to paypal, here is some information to help you:
 *
 *	  1. Download and read the Merchant User Manual and Integration Guide from
 *		 http://www.paypal.com/en_US/pdf/integration_guide.pdf.  This gives 
 *		 you all the information you need including the fields you can pass to
 *		 paypal (using add_field() with this class) aswell as all the fields
 *		 that are returned in an IPN post (stored in the EC_data() array in
 *		 this class).  It also diagrams the entire transaction process.
 *
 *	  2. Create a "sandbox" account for a buyer and a seller.  This is just
 *		 a test account(s) that allow you to test your site from both the 
 *		 seller and buyer perspective.  The instructions for this is available
 *		 at https://developer.paypal.com/ as well as a great forum where you
 *		 can ask all your paypal integration questions.  Make sure you follow
 *		 all the directions in setting up a sandbox test environment, including
 *		 the addition of fake bank accounts and credit cards.
 * 
 *******************************************************************************
*/
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'includes/payment_class.' . $phpEx);

class paypal_class extends payment_class

{
	var $paypal_response;				// holds the IPN response from paypal	
	var $EC_data = array();		 // array contains the POST values for IPN
	
	var $fields = array();			// array holds the fields to submit to paypal

	const PP_VERSION = '71.0';


	function __construct()
	{
		
		// initialization constructor.  Called when class is created.
		
		parent::__construct();
		global $config, $user, $userid, $groupid, $phpEx;

		$this->host			= 'api-3t.'.($config['pp_paypal_use_sandbox'] ? 'sandbox.' : '').'paypal.com';
		$this->paypal_url	= 'https://www.'.($config['pp_paypal_use_sandbox'] ? 'sandbox.' : '').'paypal.com/cgi-bin/webscr';
		
		$this_script		= generate_board_url() . "/application.$phpEx";
		
		parent::add_field('RETURNURL', generate_board_url() . "/shopping.{$phpEx}?mode=process_payment&method=paypal&{$this->fields['PAYMENTREQUEST_0_CUSTOM']}&sid=" . $user->session_id);

		parent::add_field('CANCELURL', $this_script . '?action=cancel');

		$this->endpoint	= '/nvp';

		$this->last_error = '';

		$this->hosted=true;
		
		$this->paypal_response = '';

		// populate $fields array with a few default values.  See the paypal
		// documentation for a list of fields and their data types. These default
		// values can be overwritten by the calling script.
		
		$this->fields['VERSION'] = self::PP_VERSION;
	}

	public function checkout()
	{
		parent::calc_basket_total();		
		if ($this->process_request($this->fields))
		{
			parent::preserve_shopping_basket($this->fields);
			header('Location: '.$this->paypal_url.'?cmd=_express-checkout&useraction=commit&token='.$this->EC_data['TOKEN'].'');
			exit();
		}
		$this->failed(__FILE__, __LINE__, $this);
		return;
	
	}

	public function take_payment()
	
	{
		$token = $_GET['token'];
		$payer = $_GET['PayerID'];
		parent::retrieve_shopping_basket();
		if ($this->fields==null)
		{
			$this->failed(__FILE__, __LINE__, $this);
			trigger_error('your session has expired');
			return -1;
		}
		else
		{
			$this->fields['TOKEN']  = $token;
			$this->fields['METHOD'] ='GetExpressCheckoutDetails';
		}
		
		if (!$this->process_request($this->fields))
		{
			$this->failed(__FILE__, __LINE__, $this);
			return false;
		}
		if (isset($this->fields['BILLINGFREQUENCY']))
		{
			$this->fields['METHOD']='CreateRecurringPaymentsProfile';
		}
		else
		{
			$this->fields['METHOD']='DoExpressCheckoutPayment';			
		}
		$this->fields['TOKEN']		  = $token;
		$this->fields['PayerID']		= $payer;
		$this->fields["AMT"]			= $this->EC_data["AMT"];  
//		$this->fields["SHIPPINGAMT"]	= $this->EC_data["SHIPPINGAMT"];  
//		$this->fields["HANDLINGAMT"]	= $this->EC_data["HANDLINGAMT"];
//		$this->fields["TAXAMT"]		 = $this->EC_data["TAXAMT"];
		$this->fields['PAYMENTREQUEST_0_PAYMENTACTION']  = 'Sale';

		parent::remove_shopping_basket($token);
		if (!$this->process_request($this->fields))
		{
			$this->failed(__FILE__, __LINE__, $this);
			return 'failed';
		}
		else
		{
			if (isset($this->fields['BILLINGFREQUENCY']))
			{
				$this->subscriber_id = $this->EC_data['PROFILEID'];
			}
			return false;
		}
	}

	public function cancel_recurring_payment($subscriber_id='', $groupid, $userid)
	{
		$this->add_field('METHOD', 'GetRecurringPaymentsProfileDetails');	
		$this->add_field('PROFILEID', $subscriber_id);	
		if ($this->process_request($this->fields))	
		{		
			list($EC_groupid, $EC_userid) = explode('-', $this->EC_data['PROFILEREFERENCE']);		
			if (($EC_groupid == $groupid) && ($EC_userid == $userid))		
			{			
				$this->add_field('METHOD', 'ManageRecurringPaymentsProfileStatus');			
				$this->add_field('PROFILEID', $subscriber_id);			
				$this->add_field('ACTION', 'Cancel');			
				if ($this->process_request($this->fields))			
				{				
					return true;			
				}		
			}	
		}	
		log_message('LOG_USER_INVALID_SUBSCRIPTION' . $subscriber_id, $userid, $groupid);	
		return false;
	}
	
	private function failed($file, $line, $data)
	{
		global $config, $phpbb_root_path, $phpEx;

		$message	= $this->debug($file, $line, $this, 'Im sorry but checkout failed with the following error ');
				
		if ($config['pp_paypal_err_email'])
		{
			if (!class_exists('messenger'))
			{
				include($phpbb_root_path . 'includes/functions_messenger.' . $phpEx);
			}
			$messenger = new messenger();
		
			$messenger->template('admin_send_email');
	
			$messenger->to($config['pp_email_notification']);
	
			$messenger->headers('X-AntiAbuse: Board servername - ' . $config['server_name']);
//			$messenger->headers('X-AntiAbuse: User_id - ' . $user->data['user_id']);
//			$messenger->headers('X-AntiAbuse: Username - ' . $user->data['username']);
//			$messenger->headers('X-AntiAbuse: User IP - ' . $user->ip);
	
			$messenger->assign_vars(array(
				'MESSAGE'		=> $message,
			));
	
			$messenger->send();
	
//			$messenger->save_queue();
		}

		return;
	}
	
	private function process_request($data)
	{
		global $config;
		
		$r = new HTTPRequest($this->host, $this->endpoint, 'POST', $config['pp_paypal_secure']);
		$data['USER'] = $config['pp_paypal'.($config['pp_paypal_use_sandbox'] ? '_sandbox_' : '_').'API_username'];
		$data['PWD'] = $config['pp_paypal'.($config['pp_paypal_use_sandbox'] ? '_sandbox_' : '_').'API_password'];
		$data['SIGNATURE'] = $config['pp_paypal'.($config['pp_paypal_use_sandbox'] ? '_sandbox_' : '_').'API_signature'];
		$temp = http_build_query($data);
		$result = $r->connect($temp);
		$return=false;

		if (is_array($result))
		{
			$this->last_error = "failed with code {$result[0]} - {$result[1]}";
		}
		elseif ($result<400)
		{
	  		$this->response = $r->get_content();
			$this->EC_data = $this->parse_response($this->response);
			$this->write_results($this->fields['METHOD'] . ' process request returned = ' . $this->EC_data['ACK']);			
			if ($this->EC_data['ACK'] == 'Success')
			{
				$return=true;
			}
			else
			{
				$this->last_error = 'Failed Validation';
			}
		}
		else
		{
			$this->last_error = "failed with code {$result}";
		}
		return $return;
	}

	private function validate_response($result)
	{
		$response = $result->get_content();
		$this->EC_data = $this->parse_response($this->response);
		return ($this->EC_data['ACK'] == 'Success');
	}

	function validate_ipn()
	{
		// parse the paypal URL
		$url_parsed=parse_url($this->paypal_url);
		
		// generate the post string from the _POST vars aswell as load the
		// _POST vars into an arry so we can play with them from the calling
		// script.
		$post_string = '';	
		foreach ($_POST as $field=>$value) 
		{ 
			$this->fields[$field] = $value;
			$post_string .= $field.'='.urlencode(stripslashes($value)).'&'; 
		}
		$post_string.="cmd=_notify-validate"; // append ipn command
		
		// open the connection to paypal
			$fp = fsockopen ('ssl.paypal.com', 443, $errno, $errstr, 30);			 
//		$fp = fsockopen($url_parsed[host],443,$err_num,$err_str,30); 
		if(!$fp)
		{
			 // could not open the connection.  If loggin is on, the error message
			 // will be in the log.
			 return false;
		}
		else
		{			 // Post the data back to paypal
			// post back to PayPal system to validate 
			$header	 = "POST $url_parsed[path] HTTP/1.1\r\n"; 
			$header	.= "Host: $url_parsed[host]\r\n"; 
			$header	.= "Content-type: application/x-www-form-urlencoded\r\n"; 
			$header	.= "Content-length: ".strlen($post_string)."\r\n"; 

			fputs($fp, $header . "\r\n\r\n");
			fputs($fp, $post_string . "\r\n\r\n"); 
			
			// loop through the response from the server and append to variable
			$response='';
			while(!feof($fp)) 
			{ 
				$response .= fgets($fp, 1024); 
			} 
			
			fclose($fp); // close connection
		}
		if (eregi("VERIFIED",$response))
		{
			// Valid IPN transaction.
			$return=$this->log_ipn(IPN_LOG_TABLE, $this->fields['txn_id'], $post_string);
			return $return;
		}
		else
		{
		
			// Invalid IPN transaction.  Check the log for details.
			$this->write_results(array('Paypal class - Invalid IPN transaction ', $header, $response, $post_string, $url_parsed));
			return 'fail';
		}
		
	}
	private function log_ipn($table_name, $txn_id, $ipn_data)
	{
		if ($this->fields['txn_type'] == 'recurring_payment_profile_created' ||
			$this->fields['txn_type'] == 'recurring_payment_profile_cancel'
			)
		{
			return -1;
		}
		global $db;
		$sql = "SELECT COUNT(*) AS txn_exists FROM {$table_name} WHERE txn_id='{$txn_id}'";
		$result = $db->sql_query($sql);
		$already_processed	= (int) $db->sql_fetchfield('txn_exists');
		if ($already_processed == 0)
		{
			$sql = 'INSERT INTO ' . $table_name . ' ' . $db->sql_build_array('INSERT', array('txn_id'=>$txn_id, 'txn_type'=>$this->fields['txn_type'], 'creation_timestamp'=>time(), 'ipn_data'=>$ipn_data));
			$db->sql_query($sql);		

			return $db->sql_nextid();
		}
		else
		{
			return 0;	
		}
	}
 
}
class HTTPRequest
{

	private $host;
	private $path;
	private $data;
	private $method;
	private $port;
	private $rawhost;

	private $header;
	private $content;
	private $parsed_header;

	function __construct($host, $endpoint, $method = 'POST', $ssl = false, $port = 0)
	{
		$this->host = $host;

		$this->rawhost = ($ssl ? "ssl://" : "https://").$host;
		$this->path = $endpoint;
		$this->method = strtoupper($method);
		if ($port)
		{
			$this->port = $port;
		}
		else
		{
			if ($ssl)
			{
				$this->port = 443;
			}
			else
			{
				$this->port = 80;
			}
		}
	}

	public function connect( $data = '')
	{
		$fp = fsockopen($this->rawhost, $this->port, $errno, $errstr);
		if (!$fp)
		{
			return array($errno, $errstr);
		}
		fputs($fp, "$this->method $this->path HTTP/1.0\r\n");
		fputs($fp, "Host: $this->host\r\n");
		//fputs($fp, "Content-type: $contenttype\r\n");
		fputs($fp, "Content-length: ".strlen($data)."\r\n");
		fputs($fp, "Connection: close\r\n");
		fputs($fp, "\r\n");
		fputs($fp, $data);

		$responseHeader = '';
		$responseContent = '';

		do
		{
			$responseHeader.= fread($fp, 1);
		}
		while (!preg_match('/\\r\\n\\r\\n$/', $responseHeader));
			
		if (!strstr($responseHeader, "Transfer-Encoding: chunked"))
		{
			while (!feof($fp))
			{
				$responseContent.= fgets($fp, 128);
			}
		}
		else
		{

			while ($chunk_length = hexdec(fgets($fp)))
			{
				$responseContentChunk = '';
				 
				$read_length = 0;
				 
				while ($read_length < $chunk_length)
				{
					$responseContentChunk .= fread($fp, $chunk_length - $read_length);
					$read_length = strlen($responseContentChunk);
				}

				$responseContent.= $responseContentChunk;
				 
				fgets($fp);
				 
			}
			 
		}
		$this_header = rtrim($responseHeader);
			$this->parsed_header=explode("\r\n", $this_header);
		$this->content = $responseContent;
		return (intval(trim(substr($this->parsed_header[0], 9))));
	}

	public function get_content()
	{
		return $this->content;
	}
	public function get_header()
	{
		return $this->parsed_header;
	}
}
?>