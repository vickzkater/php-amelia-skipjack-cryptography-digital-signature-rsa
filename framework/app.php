<?php

class App {
	
	/*
	 * config singleton
	 */

	public static $config = null;	

	public static function getUser($id = null)
	{
		return User::getInstance($id);
	}
	
	public static function getConfig()
	{
		if(!self::$config)
		{
				self::$config = new Config();
		}
		return self::$config;
	}

	public static function getDbo()
	{
		return Database::getInstance();
	}
	
	public static function getPegawai($id = null)
	{
		return Pegawai::getInstance($id);
	}
	
	public static function dumping($var=null)
	{
		if(!$var){
			echo "<center><h3>NO VARIABLE FOR DUMPING</h3></center>";
		}else{
			echo "<pre>";
			var_dump($var);
			echo "</pre>";
		}
	}
	
	public static function message($msg, $type="success")
	{
		$msg = str_ireplace("'", "`", $msg);
		
		if(isset($_SESSION['message'])){
			$arrMsg = $_SESSION['message'];
		}
		
		$obj = new stdClass();
		$obj->message = $msg;
		$obj->type = $type;
		
		$arrMsg[] = $obj;
		
		$_SESSION['message'] = $arrMsg;
	}
	
	public static function clearMessages()
	{
		if(isset($_SESSION['message'])){
			unset($_SESSION['message']);
		}
	}
	
	public static function redirect($url=null, $own=true)
	{
		if(!$url)
		{
			$url = VSITE."/";
		}else{		
			if (strpos($url, VURI) === false && $own) {
				$url = VSITE."/".$url;
			}
		}			
		
		if (!headers_sent()){
			header('HTTP/1.1 303 See other');
			header('Location: '.$url);
		}else {
			echo '<script type="text/javascript">';
			echo 'document.location.href="'.$url.'";';
			echo '</script>';
			echo '<noscript>';
			echo '<meta http-equiv="refresh" content="0;url='.$url.'" />';
			echo '</noscript>';
		}
		
		die;
	}
	
	public static function getAllAccess()
	{
		$db = App::getDbo();
		$user = App::getUser();
		
		if(!$user->id){
			App::message("You must login first", "error");
			return false;
		}
		
		$db->setQuery("select a.`level`, group_concat(distinct b.id separator ',') as accid, group_concat(distinct b.nama separator ',') as accname, group_concat(distinct b.keterangan separator ',') as accdesc from level_akses a left join akses b on b.id=a.`akses` where a.`level`=".$user->level);
		$result = $db->loadObject();
		
		if($result){
			return $result;
		}else{
			return false;
		}
	}
	
	public static function checkAccess($access)
	{
		$db = App::getDbo();
		$user = App::getUser();
		
		if(!$user->id){
			App::message("You must login first", "error");
			return false;
		}
		if(!$access){
			App::message("Access Name must be set", "error");
			return false;
		}
		
		$db->setQuery("select * from level_akses a left join akses b on b.id=a.`akses` where a.`level`=".$user->level." and b.nama='".$access."'");
		
		if($db->getNumRows() > 0){
			return true;
		}else{
			return false;
		}
	}
	
	public static function encrypt($string)
	{
		return strtoupper(substr(md5($string), 5, 25));
	}

	public static function showDate($timestamp=null)
	{
		if($timestamp > 0){
			return date("D, d M Y H:i:s (e)", $timestamp);
		}else{
			return date("D, d M Y H:i:s (e)");
		}
	}
	
	public static function generateSecretKey($idpegawai, $nama, $jenis)
	{
		return substr(md5($idpegawai.$nama.$jenis),5,25);
	}
	
	public static function timeAgo($time)
	{
		$periods = array("second", "minute", "hour", "day", "week", "month", "year", "decade");
		$lengths = array("60","60","24","7","4.35","12","10");

		$now = time();

		$difference = $now - $time;
		$tense = "ago";

		for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		  $difference /= $lengths[$j];
		}

		$difference = round($difference);

		if($difference != 1) {
			$periods[$j].= "s";
		}

		return "$difference $periods[$j] $tense";
	}
}
?>