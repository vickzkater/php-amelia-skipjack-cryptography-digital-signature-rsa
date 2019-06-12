<?php
/* JoyBoy Framework
(C) Jiboy */

class Jinput {

	public static function get($cmd, $default=null, $type)
	{
		$a = $default;
		if(isset($_GET[$cmd]))
		{
			if($type=='int')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getInt($a, $default);
			}
			if($type=='number')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getNumber($a, $default);
			}
			if($type=='double')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getDouble($a, $default);
			}			
			if($type=='string')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getString($a, $default);
			}
			if($type == 'text')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getText($a, $default);
			}
			if($type == 'words')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getWords($a);
			}
			if($type == 'alphabets')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlphabets($a);
			}
			if($type == 'alnum')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlnum($a, $default);
			}
            if($type == 'email')
            {
                $a = $_POST[$cmd];
                $a = Jinput::getEmail($a);
            }
			if($type == 'array')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getArray($a);
			}
		}
		
		return $a;
	}	
	
	public static function post($cmd, $default=null, $type)
	{
		$a = $default;
		if(isset($_POST[$cmd]))
		{
			if($type=='int')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getInt($a, $default);
			}
			if($type=='number')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getNumber($a, $default);
			}
			if($type=='double')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getDouble($a, $default);
			}			
			if($type=='string')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getString($a, $default);
			}
			if($type == 'text')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getText($a, $default);
			}
			if($type == 'words')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getWords($a);
			}
			if($type == 'alphabets')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlphabets($a);
			}
			if($type == 'alnum')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlnum($a, $default);
			}
            if($type == 'email')
            {
                $a = $_POST[$cmd];
                $a = Jinput::getEmail($a);
            }
			if($type == 'array')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getArray($a);
			}			
        }
		
		return $a;
	}
	
	public static function request($cmd, $default=null, $type)
	{
		if(isset($_REQUEST[$cmd]))
		{
			if($type=='int')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getInt($a, $default);
			}
			if($type=='number')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getNumber($a, $default);
			}
			if($type=='double')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getDouble($a, $default);
			}			
			if($type=='string')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getString($a, $default);
			}
			if($type == 'text')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getText($a, $default);
			}
			if($type == 'words')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getWords($a);
			}
			if($type == 'alphabets')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlphabets($a);
			}
			if($type == 'alnum')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getAlnum($a, $default);
			}
            if($type == 'email')
            {
                $a = $_POST[$cmd];
                $a = Jinput::getEmail($a);
            }
			if($type == 'array')
			{
				$a = $_POST[$cmd];
				$a = Jinput::getArray($a);
			}		
        }else{
			$a = $default;
		}
		return $a;
	}	
	
	public static function files($cmd, $default=null)
	{
		if(isset($_FILES[$cmd]))
		{
			return $_FILES[$cmd];
		}else{
			return $default;
		}
	}
	
	public static function set($name, $value)
	{
		$_REQUEST[$name] = $value;
	}
	
	public static function getAlphabets($string) // without space
	{
		$a = strtolower(preg_replace("/[^a-z]+/i", "", $string));
		return $a;
	}
	
	public static function getAlnum($string, $default)
	{
		if(ctype_alnum($string))
		{
			return $string;
		}else{
			return $default;
		}
	}
	
	public static function getWords($string)
	{
		$a = preg_replace("/[^ \w]+/", "", $string);
		return $a;
	}
	
	public static function getInt($string, $default=0)
	{
		$val = filter_var($string, FILTER_VALIDATE_INT);
		if($val !== false)
		{
			return (int)$string;
		}else{
			return $default;
		}
	}
	
	public static function getDouble($string, $default=0)
	{
		// Only use the first floating point value
		preg_match('/-?[0-9]+(\.[0-9]+)?/', (string) $string, $matches);
		$result = @ (float) $matches[0];
		
		if($result)
		{
			return $result;
		}else{
			return $default;
		}
	}
	
	public static function getEmail($string)
	{
		$val = filter_var($string, FILTER_VALIDATE_EMAIL);
		if($val)
		{
			return $string;
		}else{
			return null;
		}	
	}
	
	public static function getArray($array)
	{
		if(is_array($array))
		{
			return $array;
		}
		return null;
	}	
	
	public static function getNumber($string, $default) // get only numbers
	{
		if($string == '' || !$string)
		{
			return $default;
		}
		$val = 	preg_replace( '/[^0-9]/', '', $string );
		return $val;
	}
	
	public static function getString($string, $default)
	{
		if($string == '' || !$string)
		{
			return $default;
		}
		$val = filter_var($string, 	FILTER_SANITIZE_MAGIC_QUOTES);
		return $val;
	}	

	public static function getText($string, $default)
	{
		if($string == '' || !$string)
		{
			return $default;
		}	
		$val = filter_var($string, 	FILTER_SANITIZE_MAGIC_QUOTES);
		$val = stripslashes($val);
		return $val;
	}

}

?>