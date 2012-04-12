<?php
/**
* DO NOT CHANGE
*/
if (!defined('IN_PHPBB'))

{
	exit;

}

if (empty($lang) || !is_array($lang))

{
	$lang = array();

}

// DEVELOPERS PLEASE NOTE
//
// All language files should use UTF-8 as their encoding and the files must not contain a BOM.
//
// Placeholders can now contain order information, e.g. instead of
// 'Page %s of %s' you can (and should) write 'Page %1$s of %2$s', this allows
// translators to re-order the output of data while ensuring it remains correct
//
// You do not need this where single placeholders are used, e.g. 'Message %d' is fine
// equally where a string contains only two placeholders which are used to wrap text
// in a url you again do not need to specify an order e.g., 'Click %sHERE%s' is fine

$lang = array_merge($lang, array(
	'ACTIONS'						=> 'Actions',
	'ADD_TO_CART'					=> 'Add to Cart',
	'AMOUNT'						=> 'Amount',
	'BACK'							=> 'Back',
	'CANCEL_APPLICATION'			=> 'Cancel Application',
	'CANCEL_APPLICATION_CONFIRM'	=> 'Are you sure you want to cancel your payment?',
	'CANCEL_SUBSCRIPTION'			=> 'Cancel Subscription',
	'CANCEL_SUBSCRIPTION_CONFIRM'	=> 'Are you sure you want to cancel your subscription?',
	'CHECKOUT'						=> 'Checkout',
	'CONTINUE'					  	=> 'Continue',
	'CONTINUE_SHOPPING'				=> 'Continue Shopping',
	'CURRENCY_CODE'					=> 'Currency Code',
	'DELETE_LINE'					=> 'Delete Item',
	'DELETE_LINE_CONFIRM'			=> 'Are you sure you want to delete this item from the shopping basket?',
	'DELETE_POST'					=> 'Delete Product',
	'DELETE_POST_WARN'				=> 'Once deleted the product cannot be recovered',
	'DESCRIPTION'					=> 'Description',
	'EDIT_POST'					 	=> 'Edit Product',
	'EMPTY_BASKET'					=> 'Empty Shopping Basket',
	'EMPTY_BASKET_CONFIRM'			=> 'Are you sure you want to empty the shopping basket?',
	'GRAND_TOTAL'					=> 'Grand Total',
	'INVALID_AMOUNT'				=> 'The amount you entered on Line %1$s is invalid',
	'INVALID_QUANTITY'				=> 'The quantity you entered on Line %1$s is invalid',
	'PAY_BY_CHEQUE'					=> 'Please send your payment cheque to :-<BR />',
	'PAY_BY_EFT'					=> 'Please arrange to send your membership payment to our account:-',
	'PAYMENT_ERROR'				 	=> 'Payment Error',
	'PAYMENT_ERROR_TEXT'			=> 'I\'m sorry but we have encountered an error processing your payment.<br>Our technical team have been informed',
	'PAYMENT_PAGETITLE'				=> 'Payment form',
	'PAYMENT_REFERENCE'				=> 'Please make your payment out for %1$s and quote the following reference on your payment:- %2$s',
	'POST_TOPIC'					=> 'Add New Product',
	'PP_CHEQUE_ADDRESS'				=> 'Address',
	'PP_EFT_ACCOUNT'				=> 'Bank Account Number',
	'PP_EFT_BANKNAME'				=> 'Bank Name',
	'PP_EFT_BANKCODE'				=> 'Bank Sort Code',
	'PP_PAYMENT_METHOD_CHEQUE'		=> 'Cheques',
	'PP_PAYMENT_METHOD_CHEQUE_EXPLAIN'=> 'Send payment by cheque',
	'PP_PAYMENT_METHOD_EFT'			=> 'Electronic Funds Transfer',
	'PP_PAYMENT_METHOD_EFT_EXPLAIN'	=> 'Send payment by bank transfer',
	'PP_PAYMENT_METHOD_PAYPAL'		=> 'Paypal',
	'PP_PAYMENT_METHOD_PAYPAL_EXPLAIN'=> 'Pay by credit card through Paypal',
	'PP_EFT_BANKADDRESS'			=> 'Bank Address',
	'PRODUCT_CODE'					=> 'Product Code',
	'PRODUCT'						=> 'Product',
	'PRODUCTS'				  		=> 'Products',
	'PRODUCT_CATEGORIES'			=> 'Product Categories',
	'PRODUCT_PRICE'			 		=> 'Price',	
	'PRODUCT_OPTIONS'			 	=> 'Options',	
	'QUANTITY'					  	=> 'Quantity',
	'SHOP_PAGETITLE'				=> 'How to Pay',
	'TOPICS'						=> 'Products',
	'TOTAL'							=> 'Total',
 ));
?>