<?php
function DEFINE_money_format () 
{
    function money_format($format, $number)
    {
		$LocaleConfig = localeConv();
		forEach($LocaleConfig as $key => $val)
		{
			$$key = $val; // Converts array into variables
		}

		// Sign specifications:
		if ($number<0) 
		{
			$sign = $negative_sign;
			$sign_posn = $n_sign_posn;
			$sep_by_space = $n_sep_by_space;
			$cs_precedes = $n_cs_precedes;
		} else 
		{
			$sign = $positive_sign;
			$sign_posn = $p_sign_posn;
			$sep_by_space = $p_sep_by_space;
			$cs_precedes = $p_cs_precedes;
		}

		// Currency format:
		$m = number_format(abs($number), $frac_digits, $mon_decimal_point, $mon_thousands_sep);
		if ($sep_by_space) 
		{
			$space = ' '; 
		}
		else
		{
			$space = '';
		}
		if ($cs_precedes)
		{
			$m = "$currency_symbol$space$m";
		}
		else
		{
			$m = "$m$space$currency_symbol";
		}
		$m = str_replace(' ', '&nbsp;', $m);
		switch($sign_posn)
		{
			case 0: 
				$m = "($m)"; 
				break;
			case 1: 
				$m = "$sign$m"; 
				break;
			case 2: 
				$m = "$m$sign"; 
				break;
			case 3: 
				$m = "$sign$m"; 
				break;
			case 4: 
				$m = "$m$sign"; 
				break;
			default: 
				$m = "$m [error sign_posn=$sign_posn&nbsp;!]";
		}
		return $m;
    }
}

if (!function_exists('money_format'))
{
    DEFINE_money_format() ;
}
function currency_format($num)
{
	global $config;
    $mask=$config['pp_payment_locale'];
    
    return sprintf(htmlspecialchars_decode($mask),$num);
	$ret = setlocale(LC_MONETARY,'Eng');
	if ($ret===FALSE) 
	{
		echo 'Language ' . $config['pp_payment_locale'] . "is not supported by this system.\n";
		return;
	}
	$money=money_format('%n', $num);
    setlocale(LC_MONETARY,$ret);
	return $money;
}
?>