<?php

/**
 * @author Action Replay
 * @copyright 2011
 */


/**
* @ignore
*/
define('IN_PHPBB', true);
$phpbb_root_path = (defined('PHPBB_ROOT_PATH')) ? PHPBB_ROOT_PATH : './';
$phpEx = substr(strrchr(__FILE__, '.'), 1);
include($phpbb_root_path . 'common.' . $phpEx);
include($phpbb_root_path . 'includes/functions_display.' . $phpEx);
include($phpbb_root_path . 'includes/currency_format.' . $phpEx);

// Start session management
$user->session_begin();
$auth->acl($user->data);
$user->setup(array('mods/shop','mods/application'));

global $db, $user, $auth, $template;
global $config, $phpbb_root_path, $phpEx;

page_header($user->lang['SHOP_PAGETITLE']);

$mode				= request_var('mode', '');
$payment_method		= request_var('method', 'payment');
$ref				= request_var('ref',0);

$payment_class	= $payment_method . '_class';
include($phpbb_root_path . "includes/{$payment_class}.{$phpEx}");
$p = new $payment_class;

if (isset($_POST['return']))
{
	redirect(append_sid("{$phpbb_root_path}{$p->params['return']}.$phpEx", "{$p->fields['PAYMENTREQUEST_0_CUSTOM']}"));
}
if (isset($_POST['backout']))
{
	confirm_box(false, 'EMPTY_BASKET', build_hidden_fields(array(
		'action'	=> 'x',
		'mode'		=> $mode,
		'method'	=> $payment_method

	)));
}

$quantities = $amounts = $error = array();
$qty_error = $amt_error = array_fill(0,10,false);

$action	= request_var('action', '');
if ($action == 'x')
{
	if (confirm_box(true))
	{
		if (isset($p->params['return']))
		{
			$return_point=$p->params['return'];
		}
		else
		{
			$return_point='index';
		}
		$p->remove_shopping_basket();
		redirect(append_sid("{$phpbb_root_path}{$return_point}.$phpEx", "{$p->fields['PAYMENTREQUEST_0_CUSTOM']}"));
	}
}
	$template->set_filenames(array(
		'body' => 'payment_form.html',
		));

switch ($mode)
{
	case 'add_to_cart';

		$topic_id=request_var('t', 0);
		$sql_array = array(
			'SELECT'		=> 'p.*, t.*',
			'FROM'			=> array(
				PRODUCTS_TABLE=> 'p',
				),
			'LEFT_JOIN' 	=> array(
				array(
					'FROM'  => array(TOPICS_TABLE => 't'),
					'ON'	=> 't.topic_id=p.topic_id'
					),
				),
			'WHERE'			=> 'p.topic_id = ' . $topic_id,
			
			);
		$sql=$db->sql_build_query('SELECT', $sql_array);
		$result = $db->sql_query($sql);
		$row = $db->sql_fetchrow($result);
		
//		build cart line item

		$product_price = request_var('pp', '');
		$product_option = request_var('po', '');
		$price_arguments=explode('=', $product_price);
		
		$message = $row['product_code'] . ' - ';
		if (sizeof($price_arguments) == 1)
		{
			$price = $row['product_price'];			
		}
		else
		{
			$message .= $price_arguments[0] . ' ';
			$price		= $price_arguments[1];
		}
		$message .= $product_option . ' ' . $row['topic_title'];
		$p->params['return']	= 'viewforum';
		$p->params['f']		= request_var('f',0);

		$p->add_cart_item(null , $message, $price, 1);
	break;

	case 'payment':
		$action	= request_var('action', '');
		if ($action!='returning')
		{
			$amounts 	= request_var('amt',array('' => 0.00));
			$quantities = request_var('qty',array('' => 0.00));

			for ($num=0;$num<10; $num++)
			{
				if (!empty($p->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"]))
				{
					if ($p->fields["PAYMENTREQUEST_0_AMT{$num}"]>0)
					{
						$amounts[$num]=$p->fields["PAYMENTREQUEST_0_AMT{$num}"];
					}
					else
					{
						if(!is_numeric($amounts[$num])|| $amounts[$num]<=0)
						{
							$error[] = sprintf($user->lang['INVALID_AMOUNT'], $num+1);
							$amt_error[$num] = true;
						}
					}
					if ($p->fields["PAYMENTREQUEST_0_QTY{$num}"]>0)
					{
						$quantities[$num]=$p->fields["PAYMENTREQUEST_0_QTY{$num}"];
					}
					else
					{
						if(!is_numeric($quantities[$num])|| $quantities[$num]<=0)
						{
							$error[] = sprintf($user->lang['INVALID_QUANTITY'], $num+1);
							$qty_error[$num] = true;
						}
					}
				}
			}
			// Do not write values if there is an error
			if (sizeof($error))
			{
				break;
			}
			else
			{
				// update $p->fields
				for ($num=0;$num<10; $num++)
				{
					if (!empty($p->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"]))
					{
						$p->fields["PAYMENTREQUEST_0_QTY{$num}"]=$quantities[$num];
						$p->fields["PAYMENTREQUEST_0_AMT{$num}"]=$amounts[$num];
					}
				}
			}
			if (!$p->hosted)
			{
				// build form to display all of the fields required for the selected payment method
				page_header($user->lang['SHOP_PAGETITLE']);
				$variables=array(
					'PAYMENT_TYPE'  => $payment_method,
					'ACTION'		=> append_sid("{$phpbb_root_path}shopping.$phpEx","mode=payment&action=returning&method={$payment_method}"),
					'PAYMENT_REF'   => sprintf($user->lang['PAYMENT_REFERENCE'], currency_format($p->calc_basket_total()),$ref),
				);
				$select_config='pp_'.$payment_method.'_';
				$select_length=strlen($select_config);
				foreach($config as $config_name=>$config_value)
				{
					if (substr($config_name,0,$select_length) == $select_config)
					{
						$variables=array_merge($variables, array(strtoupper($config_name)=> $config_value));
					}
				}
				
				$template->assign_vars($variables);
				$template->set_filenames(array(
					'body' => 'make_payment.html',
					));
			}
			if (!$p->checkout()) // process the payment
			{
				$template->assign_vars(array(
					'S_CONFIRM_ACTION'	=> append_sid("{$phpbb_root_path}index.$phpEx"),
					'MESSAGE_TITLE'	=> $user->lang['PAYMENT_ERROR'],
					'MESSAGE_TEXT'	=> $user->lang['PAYMENT_ERROR_TEXT'],
					));
				$template->set_filenames(array(
					'body' => 'payment_error.html',
					));
			}
			break;
		}
	case 'process_payment':
		$uncleared = $p->take_payment())
		if ($uncleared == 'expired' || $uncleared == 'failed')		
		{
			redirect(append_sid("{$phpbb_root_path}index.$phpEx"));
		}
		redirect(append_sid("{$phpbb_root_path}{$p->params['return']}.$phpEx", "&mode=paid&status=$uncleared&{$p->fields['PAYMENTREQUEST_0_CUSTOM']}"));
	break;

	case 'delete':
		$type	= request_var('type', '');
		$billing= request_var('billing', '');
		if (confirm_box(true))
		{
			if ($type == 'cart')
			{
				$p->cancel_cart_item($billing);
			}
			else
			{
				$p->cancel_subscription_item($billing);
			}
		}
		else
		{
			confirm_box(false, 'DELETE_LINE');
		}
	case 'checkout':
	default:
	break;
}
	for ($num=0; $num<10; $num++)
	{
		if (!empty($p->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"]))
		{
			$quantities[$num]	= $p->fields["PAYMENTREQUEST_0_QTY{$num}"];
			$amounts[$num]		= sprintf("%01.2f",$p->fields["PAYMENTREQUEST_0_AMT{$num}"]); 
		}
	}
	for ($grand_total=0, $num=0; $num<10; $num++)
	{
		if (!empty($p->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"]))
		{
			$quantities		= $p->fields["PAYMENTREQUEST_0_QTY{$num}"];
			$amounts		= $p->fields["PAYMENTREQUEST_0_AMT{$num}"]; 
			$line_total 	= $quantities * $amounts;
			$amounts		= currency_format($amounts);
			$item_desc 		= $p->fields["PAYMENTREQUEST_0_DESC{$num}"];
			if (!empty($p->fields['INITAMT']))
			{
				$item_desc	= $user->lang['INITIAL_FEE'] . '<br />' . $item_desc;
				$line_total	+= $p->fields['INITAMT'];
				$amounts	= currency_format($p->fields['INITAMT']) . '<br />' . $amounts;
			}
			$grand_total 	+= $line_total;
			$template->assign_block_vars('batch', array(
				'DELETE'						=> append_sid("{$phpbb_root_path}shopping.$phpEx","&mode=delete&type=cart&billing={$num}"),
				'FIXED_QTY'						=> $p->fields["PAYMENTREQUEST_0_QTY{$num}"],
				'PAYMENT_REQUEST_QTY'			=> $quantities,
				'QTY_STYLE'						=> $qty_error[$num] ? 'error' : 'ok',
				'PAYMENT_REQUEST_DESC'			=> $item_desc,
				'FIXED_AMT'						=> $p->fields["PAYMENTREQUEST_0_AMT{$num}"]>0,
				'AMT_STYLE'						=> $amt_error[$num] ? 'wrong' : 'ok',
				'PAYMENT_REQUEST_AMT'			=> $amounts, 
				'PAYMENT_REQUEST_CURRENCYCODE'  => $p->fields["PAYMENTREQUEST_0_CURRENCYCODE{$num}"],
				'PAYMENT_REQUEST_LINE_TOTAL'	=> currency_format($line_total),
			));
		}
	}
	$template->assign_vars(array(
		'DISPLAY_ERROR'		=> sizeof($error)>0 ? 'form_error' : 'form_ok',
		'ERROR_MSG'			=> implode('<br />', $error),
		'S_ACTION'			=> append_sid("{$phpbb_root_path}shopping.$phpEx", "mode=payment&ref={$ref}"),
		'GRAND_TOTAL'		=> currency_format($grand_total),
	));

	// build the payment methods
	
	foreach($config as $config_name=>$config_value)
	{
		if (substr($config_name,0,18) == 'pp_payment_method_' && $config_value)
		{
			$payment_type = trim(substr($config_name,18));
			if (isset($subscriber) && (!$config['pp_subscription_allowed_' . $payment_type]))
			{
				continue;
			}
			$payment_method=strtoupper($config_name);
			if (empty($config['pp_'.$payment_type.'_image']))
			{
				$payment_image  = 'standard';
			}
			else
			{
				$payment_image  = $config['pp_'.$payment_type.'_image'];
			}
//			$sizes=getimagesize($payment_image);
			$template->assign_block_vars('pp_payment_methods', array(
				'NAME'			=> $payment_type,
				'LABEL'			=> $user->lang[$payment_method],
				'EXPLAIN'		=> $user->lang[$payment_method . '_EXPLAIN'],
				'PAYMENT_IMAGE' => $payment_image,
//				'IMAGE_WIDTH'   => $sizes[0],
//				'IMAGE_HEIGHT'   => $sizes[1],
			));
		}
	}

page_footer();
?>