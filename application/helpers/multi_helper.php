<?php
defined("BASEPATH") OR exit("No direct script access allowed");

/*
|
|----------------------------------------------------------------
|
| PRE Function (for debug use, not in production / live)
|
|----------------------------------------------------------------
|
*/

if (!function_exists("pre"))
{
	function pre($value="")
	{
		if (ENVIRONMENT !== "production") {
			echo "<pre>";
			print_r($value);
			echo "</pre>";
		}
	}
}

if (!function_exists("run_javascript")) {
	function run_javascript($script = array()) {
		echo "<script type=\"text/javascript\">";
		if (!empty($script)) {
			foreach ($script as $js) {
				echo $js;
			}
		}
		echo "</script>";
	}
}

/*
|
|----------------------------------------------------------------
|
| Generate Random String
|
|----------------------------------------------------------------
|
*/

if (!function_exists("generateRandomString")) {
	function generateRandomString($length) {
		$include_chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
		// $include_chars .= "[{(!@#$%^/&*_+;?\:)}]";
		$charlength = strlen($include_chars);
		$randomstring = "";
		for ($i=0; $i < $length; $i++) { 
			$randomstring .= $include_chars[rand(0,$charlength - 1)];
		}
		return $randomstring;
	}
}

/*
|
|----------------------------------------------------------------
|
| Send email helper default
|
|----------------------------------------------------------------
|
*/

if (!function_exists("ngirim_email"))
{
	function ngirim_email($to, $subject, $msg, $email_sender = NULL, $name_sender = NULL)
	{
		$CI = &get_instance();
		$CI->load->config("email",TRUE);
		$config = $CI->config->item("email");
		$CI->load->library("email", $config);
		$CI->email->set_newline("\r\n");
		// $CI->email->clear();
		if (empty($email_sender) && empty($name_sender)) {
			$email_sender = "postmaster@localhost";
			$name_sender = "admin_local";
		}
		$CI->email->from($email_sender, $name_sender);
		$CI->email->to($to);
		$CI->email->subject($subject);
		$CI->email->message($msg);
		if (ENVIRONMENT !== "localhost") {
			$CI->email->send();
		}
	}
}

/*
|
|----------------------------------------------------------------
|
| Send email helper -> iThenticate
|
|----------------------------------------------------------------
|
*/

if (!function_exists("email_ithen"))
{
	function email_ithen($to, $subject, $msg, $notify = TRUE)
	{
		$CI = &get_instance();
		if ($notify === TRUE) {
			$email_sender = $CI->config->item("notify_email","ion_auth");
			$name_sender = $email_sender;
		} else {
			$email_sender = $CI->config->item("admin_email","ion_auth");
			$name_sender = $CI->config->item("site_title","ion_auth");
		}
		ngirim_email($to, $subject, $msg, $email_sender, $name_sender);
	}
}

/*
|
|----------------------------------------------------------------
|
| Array PUSH Values
|
|----------------------------------------------------------------
|
*/

if (!function_exists("array_push_values")) {

	function array_push_values($to_array,$values)
	{
		/*Check values is not empty and set*/
		if (isset($values) && !empty($values)) {
			/*Check values is array or not*/
			if (is_array($values)) {
				/*if values is array*/
				foreach ($values as $value) {
					/*push the values value to to_array */
					array_push($to_array, $value);
				}
				return $to_array;
			}
		}
	}

}

/*
|
|----------------------------------------------------------------
|
| Cek sudah pernah ganti password default belum
|
|----------------------------------------------------------------
|
*/

if (!function_exists("cek_password_default")) {

	function cek_password_default($userdata)
	{
		$code = $userdata->activation_code;
		$selector = $userdata->activation_selector;
		if (!empty($code) || !empty($selector)) {
			redirect("en_us/user/password_reset","refresh");
		}
	}

}

/* Generate a list of all IP addresses
   between $start and $end (inclusive). */
if (!function_exists("ip_range")) {
	function ip_range($start, $end)
	{
		$start = ip2long($start);
		$end = ip2long($end);
		$range = array_map("long2ip",range($start,$end));
		// pre($range);
		return $range;
		// return array_map("long2ip", range($start, $end));
	}
}


if (!function_exists("number_to_alphabet")) {
	function number_to_alphabet($number)
	{
		$number = intval($number);
		if ($number <= 0) {
			return "";
		}
		$alphabet = "";
		while ($number != 0) {
			$p = ($number - 1) % 26;
			$number = intval(($number - $p) / 26);
			$alphabet = chr(65 + $p) . $alphabet;
		}
		return $alphabet;
	}
}

if (!function_exists("alphabet_to_number")) {
	function alphabet_to_number($string)
	{
		$string = strtoupper($string);
		$length = strlen($string);
		$number = 0;
		$level = 1;
		while ($length >= $level) {
			$char = $string[$length - $level];
			$c = ord($char) - 64;
			$number += $c * (26 ** ($level - 1 ));
			$level++;
		}
		return $number;
	}
}

if (!function_exists("excel_reader")) {
	function excel_reader($lib_excel_reader, $upload_path, $filename)
	{
		$sheet = array();
		if (isset($lib_excel_reader) && !empty($lib_excel_reader) && isset($upload_path) && isset($filename) && !empty($upload_path) && !empty($filename)) {
			include $lib_excel_reader;
			$excelreader = new PHPExcel_Reader_Excel2007();
			$loadexcel = $excelreader->load($upload_path.$filename.".xlsx");
			$sheet = $loadexcel->getActiveSheet()->toArray(null, true, true ,true);
		}
		return $sheet;
	}
}