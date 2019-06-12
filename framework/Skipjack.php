<?php
class PHP_Crypt
{
	// Ciphers
	const CIPHER_SKIPJACK = "Skipjack";
	
	// Modes
	const MODE_CBC = "CBC";
	
	// The source of random data used to create keys and IV's
	// Used for PHP_Crypt::createKey(), PHP_Crypt::createIV()
	const RAND = "rand"; // uses mt_rand(), windows & unix
	
	// Padding types
	const PAD_ZERO = 0;
	
	private $cipher = null;
	private $mode = null;
	
	public function __construct($key, $cipher, $mode, $padding = self::PAD_ZERO)
	{
		/*
		 * CIPHERS
		 */
		$this->cipher = new Cipher_Skipjack($key);
		
		/*
		 * MODES
		 */
		$this->mode = new Mode_CBC($this->cipher);
		
		// set the default padding
		$this->padding($padding);
	}
	
	/**
	 * Encrypt a plain text message using the Mode and Cipher selected.
	 * Some stream modes require this function to be called in a loop
	 * which requires the use of $result parameter to retrieve
	 * the decrypted data.
	 *
	 * @param string $text The plain text string
	 * @return string The encrypted string
	 */
	public function encrypt($text)
	{
		// check that an iv is set, if required by the mode
		$this->mode->checkIV();

		// the encryption is done inside the mode
		$this->mode->encrypt($text);
		return $text;
	}
	
	/**
	 * Decrypt an encrypted message using the Mode and Cipher selected.
	 * Some stream modes require this function to be called in a loop
	 * which requires the use of $result parameter to retrieve
	 * the decrypted data.
	 *
	 * @param string $text The encrypted string
	 * @return string The decrypted string
	 */
	public function decrypt($text)
	{
		// check that an iv is set, if required by the mode
		$this->mode->checkIV();

		// the decryption is done inside the mode
		$this->mode->decrypt($text);
		return $text;
	}

	/**
	 * Returns Ciphers required block size in bytes
	 *
	 * @return integer The cipher data block size, in bytes
	 */
	public function cipherBlockSize()
	{
		return $this->cipher->blockSize();
	}
	
	/**
	 * Returns the name of the cipher being used
	 *
	 * @return string The name of the cipher currently in use,
	 *	it will be one of the predefined phpCrypt cipher constants
	 */
	public function cipherName()
	{
		return $this->cipher->cipher_name;
	}

	/**
	 * Return the name of the mode being used
	 *
	 * @return string The name of the mode in use, it will
	 * be one of the predefined phpCrypt mode constants
	 */
	public function modeName()
	{
		return $this->mode->mode_name;
	}

	/**
	 * Sets the IV to use. Note that you do not need to call
	 * this function if creating an IV using createIV(). This
	 * function is used when an IV has already been created
	 * outside of phpCrypt and needs to be set. Alternatively
	 * you can just pass the $iv parameter to the encrypt()
	 * or decrypt() functions
	 *
	 * When the $iv parameter is not given, the function will
	 * return the current IV being used. See createIV() if you
	 * need to create an IV.
	 *
	 * @param string $iv Optional, The IV to use during Encryption/Decryption
	 * @return void
	 */
	public function IV($iv = "")
	{
		return $this->mode->IV($iv);
	}

	/**
	 * Creates an IV for the the Cipher selected, if one is required.
	 * If you already have an IV to use, this function does not need
	 * to be called, instead set it with setIV(). If you create an
	 * IV with createIV(), you do not need to set it with setIV(),
	 * as it is automatically set in this function
	 *
	 * $src values are:
	 * PHP_Crypt::RAND - Default, uses mt_rand()
	 * PHP_Crypt::RAND_DEV_RAND - Unix only, uses /dev/random
	 * PHP_Crypt::RAND_DEV_URAND - Unix only, uses /dev/urandom
	 * PHP_Crypt::RAND_WIN_COM - Windows only, uses Microsoft's CAPICOM SDK
	 *
	 * @param string $src Optional, how the IV is generated
	 * @return string The IV that was created, and set for the mode
	 */
	public function createIV($src = self::RAND)
	{
		return $this->mode->createIV($src);
	}

	/**
	 * Sets the type of padding to be used within the specified Mode
	 *
	 * @param string $type One of the predefined padding types
	 * @return void
	 */
	public function padding($type = "")
	{
		return $this->mode->padding($type);
	}

}

class Cipher_Skipjack
{
	public $cipher_name = "Skipjack";
	
	/** @type integer ENCRYPT Indicates when we are in encryption mode */
	const ENCRYPT = 1;

	/** @type integer DECRYPT Indicates when we are in decryption mode */
	const DECRYPT = 2;
	
	/** @type integer BLOCK Indicates that a cipher is a block cipher */
	const BLOCK = 1;
	
	/**
	 * @type integer $operation Indicates if a cipher is Encrypting or Decrypting
	 * this can be set to either Cipher::ENCRYPT or Cipher::DECRYPT
	 */
	protected $operation = self::ENCRYPT; // can be either Cipher::ENCRYPT | Cipher::DECRYPT;
	
	/** @type integer BYTES_BLOCK The size of the block, in bytes */
	const BYTES_BLOCK = 8; // 64 bits

	/** @type integer BYTES_KEY The size of the key, in bytes */
	const BYTES_KEY = 10; // 80 bits
	
	/** @type string $key Stores the key for the Cipher */
	private $key = "";
	
	/** @type integer $key_len Keep track of the key length, so we don't
	have to make repeated calls to strlen() to find the length */
	private $key_len = 0;
	
	/** @type integer $block_size The block size of the cipher in bytes */
	protected $block_size = 0;
	
	/** @type string $expanded_key The expanded key */
	private $expanded_key = "";

	/** @type array $_f The Skipjack F-Table, this is a constant */
	private static $_f = array();
	
	/**
	 * Constructor
	 *
	 * @param string $key The key used for Encryption/Decryption
	 * @return void
	 */
	public function __construct($key)
	{
		// set the Skipjack key
		// parent::__construct("Skipjack", $key, self::BYTES_KEY);
		$this->key($key, self::BYTES_KEY);

		// initialize variables
		$this->initTables();

		// set the block size used
		$this->block_size = self::BYTES_BLOCK;

		// expand the key from 10 bytes to 128 bytes
		$this->expandKey();
	}
	
	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		
	}
	
	/**
	 * Initialize all the tables, this function is called inside the constructor
	 *
	 * @return void
	 */
	private function initTables()
	{
		self::$_f = array(
			0xa3, 0xd7, 0x09, 0x83, 0xf8, 0x48, 0xf6, 0xf4, 0xb3, 0x21, 0x15, 0x78, 0x99, 0xb1, 0xaf, 0xf9,
			0xe7, 0x2d, 0x4d, 0x8a, 0xce, 0x4c, 0xca, 0x2e, 0x52, 0x95, 0xd9, 0x1e, 0x4e, 0x38, 0x44, 0x28,
			0x0a, 0xdf, 0x02, 0xa0, 0x17, 0xf1, 0x60, 0x68, 0x12, 0xb7, 0x7a, 0xc3, 0xe9, 0xfa, 0x3d, 0x53,
			0x96, 0x84, 0x6b, 0xba, 0xf2, 0x63, 0x9a, 0x19, 0x7c, 0xae, 0xe5, 0xf5, 0xf7, 0x16, 0x6a, 0xa2,
			0x39, 0xb6, 0x7b, 0x0f, 0xc1, 0x93, 0x81, 0x1b, 0xee, 0xb4, 0x1a, 0xea, 0xd0, 0x91, 0x2f, 0xb8,
			0x55, 0xb9, 0xda, 0x85, 0x3f, 0x41, 0xbf, 0xe0, 0x5a, 0x58, 0x80, 0x5f, 0x66, 0x0b, 0xd8, 0x90,
			0x35, 0xd5, 0xc0, 0xa7, 0x33, 0x06, 0x65, 0x69, 0x45, 0x00, 0x94, 0x56, 0x6d, 0x98, 0x9b, 0x76,
			0x97, 0xfc, 0xb2, 0xc2, 0xb0, 0xfe, 0xdb, 0x20, 0xe1, 0xeb, 0xd6, 0xe4, 0xdd, 0x47, 0x4a, 0x1d,
			0x42, 0xed, 0x9e, 0x6e, 0x49, 0x3c, 0xcd, 0x43, 0x27, 0xd2, 0x07, 0xd4, 0xde, 0xc7, 0x67, 0x18,
			0x89, 0xcb, 0x30, 0x1f, 0x8d, 0xc6, 0x8f, 0xaa, 0xc8, 0x74, 0xdc, 0xc9, 0x5d, 0x5c, 0x31, 0xa4,
			0x70, 0x88, 0x61, 0x2c, 0x9f, 0x0d, 0x2b, 0x87, 0x50, 0x82, 0x54, 0x64, 0x26, 0x7d, 0x03, 0x40,
			0x34, 0x4b, 0x1c, 0x73, 0xd1, 0xc4, 0xfd, 0x3b, 0xcc, 0xfb, 0x7f, 0xab, 0xe6, 0x3e, 0x5b, 0xa5,
			0xad, 0x04, 0x23, 0x9c, 0x14, 0x51, 0x22, 0xf0, 0x29, 0x79, 0x71, 0x7e, 0xff, 0x8c, 0x0e, 0xe2,
			0x0c, 0xef, 0xbc, 0x72, 0x75, 0x6f, 0x37, 0xa1, 0xec, 0xd3, 0x8e, 0x62, 0x8b, 0x86, 0x10, 0xe8,
			0x08, 0x77, 0x11, 0xbe, 0x92, 0x4f, 0x24, 0xc5, 0x32, 0x36, 0x9d, 0xcf, 0xf3, 0xa6, 0xbb, 0xac,
			0x5e, 0x6c, 0xa9, 0x13, 0x57, 0x25, 0xb5, 0xe3, 0xbd, 0xa8, 0x3a, 0x01, 0x05, 0x59, 0x2a, 0x46
		);
	}

	/**
	 * Expands the key from 10 bytes, to 128 bytes
	 * This is done by copying the key 1 byte at a time and
	 * appending it to $this->expanded_key, when we reach the
	 * end of the key, we start over at position 0 and continue
	 * until we reach 128 bytes
	 *
	 * @return void
	 */
	private function expandKey()
	{
		$this->expanded_key = "";
		$key_bytes = $this->key_len;
		$key = $this->key();
		$pos = 0;

		for($i = 0; $i < 128; ++$i)
		{
			if($pos == $key_bytes)
				$pos = 0;

			$this->expanded_key .= $key[$pos];
			++$pos;
		}
	}
	
	/**
	 * Returns the size (in bytes) required by the cipher.
	 *
	 * @return integer The number of bytes the cipher requires the key to be
	 */
	public function keySize()
	{
		return $this->key_len;
	}

	/**
	 * Set the cipher key used for encryption/decryption. This function
	 * may lengthen or shorten the key to meet the size requirements of
	 * the cipher.
	 *
	 * If the $key parameter is not given, this function simply returns the
	 * current key being used.
	 *
	 * @param string $key Optional, A key for the cipher
	 * @param integer $req_sz The byte size required for the key
	 * @return string They key, which may have been modified to fit size
	 *	requirements
	 */
	public function key($key = "", $req_sz = 0)
	{
		if($key != "" && $key != null)
		{
			// in the case where the key is changed changed after
			// creating a new Cipher object and the $req_sz was not
			// given, we need to make sure the new key meets the size
			// requirements. This can be determined from the $this->key_len
			// member set from the previous key
			if($this->key_len > 0 && $req_sz == 0)
				$req_sz = $this->key_len;
			else
				$this->key_len = strlen($key);

			if($req_sz > 0)
			{
				if($this->key_len > $req_sz)
				{
					// shorten the key length
					$key = substr($key, 0, $req_sz);
					$this->key_len = $req_sz;
				}
				else if($this->key_len < $req_sz)
				{
					// send a notice that the key was too small
					// NEVER PAD THE KEY, THIS WOULD BE INSECURE!!!!!
					$msg = strtoupper($this->name())." requires a $req_sz byte key, {$this->key_len} bytes received";
					trigger_error($msg, E_USER_WARNING);
					
					return false;
				}
			}

			$this->key = $key;
		}

		return $this->key;
	}

	/**
	 * Encrypt plain text data using Skipjack
	 *
	 * @param string $data A plain text string, 8 bytes long
	 * @return boolean Returns true
	 */
	public function encrypt(&$text)
	{
		$this->operation(self::ENCRYPT);
		
		for($i = 1; $i <= 32; ++$i)
		{
			$pos = (4 * $i) - 4;
			$subkey = substr($this->expanded_key, $pos, 4);

			if($i >= 1 && $i <= 8)
				$this->ruleA($text, $subkey, $i);

			if($i >= 9 && $i <= 16)
				$this->ruleB($text, $subkey, $i);

			if($i >= 17 && $i <= 24)
				$this->ruleA($text, $subkey, $i);

			if($i >= 25 && $i <= 32)
				$this->ruleB($text, $subkey, $i);
		}
		
		return true;
	}
	
	/**
	 * Decrypt a Skipjack encrypted string
	 *
	 * @param string $encrypted A Skipjack encrypted string, 8 bytes long
	 * @return boolean Returns true
	 */
	public function decrypt(&$text)
	{
		$this->operation(self::DECRYPT);
		
		for($i = 32; $i >= 1; --$i)
		{
			$pos = ($i - 1) * 4;
			$subkey = substr($this->expanded_key, $pos, 4);

			if($i <= 32 && $i >= 25)
				$this->ruleB($text, $subkey, $i);

			if($i <= 24 && $i >= 17)
				$this->ruleA($text, $subkey, $i);

			if($i <= 16 && $i >= 9)
				$this->ruleB($text, $subkey, $i);

			if($i <= 8 && $i >= 1)
				$this->ruleA($text, $subkey, $i);
		}
		
		return true;
	}
	
	/**
	 * Determine if we are Encrypting or Decrypting
	 * Since some ciphers use the same algorithm to Encrypt or Decrypt but with only
	 * slight differences, we need a way to check if we are Encrypting or Decrypting
	 * An example is DES, which uses the same algorithm except that when Decrypting
	 * the sub_keys are reversed
	 *
	 * @param integer $op Sets the operation to Cipher::ENCRYPT or Cipher::DECRYPT
	 * @return integer The current operation, either Cipher::ENCRYPT or Cipher::DECRYPT
	 */
	public function operation($op = 0)
	{
		if($op == self::ENCRYPT || $op == self::DECRYPT)
			$this->operation = $op;

		return $this->operation;
	}
	
	/**
	 * Perform SkipJacks RuleA function. Split the data into 4 parts,
	 * 2 bytes each: W0, W1, W2, W3.
	 *
	 * @param string $bytes An 8 byte string
	 * @param string $key 4 bytes of $this->expanded_key
	 * @param integer $i The round number
	 * @return void
	 */
	private function ruleA(&$bytes, $key, $i)
	{
		$w = str_split($bytes, 2);

		if($this->operation() == self::ENCRYPT)
		{
			/*
			 * Set the W3 as the old W2
			 * Set the W2 as the old W1
			 * Set the W1 as the G(W0)
			 * Set the W0 as the W1 xor W4 xor i
			 */

			$w[4] = $w[3];
			$w[3] = $w[2];
			$w[2] = $w[1];
			$w[1] = $this->gPermutation($w[0], $key);

			$hex1 = $this->str2Hex($w[1]);
			$hex4 = $this->str2Hex($w[4]);
			$hexi = $this->dec2Hex($i);
			$w[0] = $this->xorHex($hex1, $hex4, $hexi);
			$w[0] = $this->hex2Str($w[0]);
		}
		else // parent::DECRYPT
		{
			/*
			 * Set W4 as W0 xor W1 xor i
			 * Set W0 as Inverse G(W1)
			 * Set W1 as the old W2
			 * Set W2 as the old W3
			 * Set W3 as W4
			 */

			$hex0 = $this->str2Hex($w[0]);
			$hex1 = $this->str2Hex($w[1]);
			$hexi = $this->dec2Hex($i);
			$w[4] = $this->xorHex($hex0, $hex1, $hexi);
			$w[4] = $this->hex2Str($w[4]);

			$w[0] = $this->gPermutation($w[1], $key);
			$w[1] = $w[2];
			$w[2] = $w[3];
			$w[3] = $w[4];
		}

		// glue all the pieces back together
		$bytes = $w[0].$w[1].$w[2].$w[3];
	}

	/**
	 * Perform SkipJacks RuleB function. Split the data into 4 parts,
	 * 2 bytes each: W0, W1, W2, W3.
	 *
	 * @param string $bytes An 8 bytes string
	 * @param string $key 4 bytes of $this->expanded_key
	 * @param integer $i The round number
	 * @return void
	 */
	private function ruleB(&$bytes, $key, $i)
	{
		$w = str_split($bytes, 2);

		if($this->operation() == self::ENCRYPT)
		{
			/*
			 * Set the new W3 as the old W2
			 * Set the new W2 as the old W0 xor old W1 xor i
			 * Set the new W1 as G(old W0)
			 * Set the new W0 as the old W3
			 */

			$w[4] = $w[3];
			$w[3] = $w[2];

			$hex0 = $this->str2Hex($w[0]);
			$hex1 = $this->str2Hex($w[1]);
			$hexi = $this->dec2Hex($i);
			$w[2] = $this->xorHex($hex0, $hex1, $hexi);
			$w[2] = $this->hex2Str($w[2]);

			$w[1] = $this->gPermutation($w[0], $key);
			$w[0] = $w[4];
		}
		else // self::DECRYPT
		{
			/*
			 * Set W4 as the old W0
			 * Set new W0 as Inverse G(old W1)
			 * Set new W1 as Inverse G(old W1) xor old W2 xor i
			 * Set new W2 as the old W3
			 * Set new W3 as the old W0 (W4)
			 */

			$w[4] = $w[0];
			$w[0] = $this->gPermutation($w[1], $key);

			$hex0 = $this->str2Hex($w[0]);
			$hex2 = $this->str2Hex($w[2]);
			$hexi = $this->dec2Hex($i);
			$w[1] = $this->xorHex($hex0, $hex2, $hexi);
			$w[1] = $this->hex2Str($w[1]);

			$w[2] = $w[3];
			$w[3] = $w[4];
		}

		$bytes = $w[0].$w[1].$w[2].$w[3];
	}

	/**
	 * Convert a string to hex
	 * This function calls the PHP bin2hex(), and is here
	 * for consistency with the other string functions
	 *
	 * @param string $str A string
	 * @return string A string representation of hexidecimal number
	 */
	public static function str2Hex($str)
	{
		return bin2hex($str);
	}
	
	/**
	 * Convert binary string (ie 00110110) to hex
	 *
	 * @param string $bin A binary string
	 * @return string A string representation of hexidecimal number
	 */
	public static function bin2Hex($bin)
	{
		$parts = str_split($bin, 8);

		$parts = array_map(function($v) {
			$v = str_pad($v, 8, "0", STR_PAD_LEFT);
			$v = dechex(bindec($v));
			return str_pad($v, 2, "0", STR_PAD_LEFT);
		}, $parts);

		return implode("", $parts);
	}
	
	/**
	 * Converts Decimal to Hex
	 * This function just calls php's dechex() function,  but I
	 * encapsulated it in this function to keep things uniform
	 * and have all possible conversion function available in
	 * the Cipher class
	 *
	 * The parameter $req_bytes will pad the return hex with NULL (00)
	 * until the hex represents the number of bytes given to $req_bytes
	 * This is because dechex() drops null bytes from the Hex, which may
	 * be needed in some cases
	 *
	 * @param integer $dec A decimal number to convert
	 * @param integer $req_bytes Optional, forces the string to be at least
	 *	$req_bytes in size, this is needed because on occasion left most null bytes
	 *	are dropped in dechex(), causing the string to have a shorter byte
	 *	size than the initial integer.
	 * @return string A hexidecimal representation of the decimal number
	 */
	public static function dec2Hex($dec, $req_bytes = 0)
	{
		$hex = dechex($dec);

		// if we do not have an even number of hex characters
		// append a 0 to the beginning. dechex() drops leading 0's
		if(strlen($hex) % 2)
			$hex = "0$hex";

		// if the number of bytes in the hex is less than
		// what we need it to be, add null bytes to the
		// front of the hex to padd it to the required size
		if(($req_bytes * 2) > strlen($hex))
			$hex = str_pad($hex, ($req_bytes * 2), "0", STR_PAD_LEFT);

		return $hex;
	}

	/**
	 * ExclusiveOR hex values. Supports an unlimited number of parameters.
	 * The values are string representations of hex values
	 * IE: "0a1b2c3d" not 0x0a1b2c3d
	 *
	 * @param string Unlimited number parameters, each a string representation of hex
	 * @return string A string representation of the result in Hex
	 */
	public static function xorHex()
	{
		$hex   = func_get_args();
		$count = func_num_args();

		// we need a minimum of 2 values
		if($count < 2)
			return false;

		// first get all hex values to an even number
		array_walk($hex, function(&$val, $i){
			if(strlen($val) % 2)
				$val = "0".$val;
		});

		$res = 0;
		for($i = 0; $i < $count; ++$i)
		{
			// if this is the first loop, set the 'result' to the first
			// hex value
			if($i == 0)
				$res = $hex[0];
			else
			{
				// to make the code easier to follow
				$h1 = $res;
				$h2 = $hex[$i];

				// get lengths
				$len1 = strlen($h1);
				$len2 = strlen($h2);

				// now check that both hex values are the same length,
				// if not pad them with 0's until they are
				if($len1 > $len2)
					$h2 = str_pad($h2, $len1, "0", STR_PAD_LEFT);
				else if($len1 < $len2)
					$h1 = str_pad($h1, $len2, "0", STR_PAD_LEFT);

				// PHP knows how to XOR each byte in a string, so convert the
				// hex to a string, XOR, and convert back
				$res = self::hex2Str($h1) ^ self::hex2Str($h2);
				$res = self::str2Hex($res);
			}
		}

		return $res;
	}
	
	/**
	 * Convert hex to a string
	 *
	 * @param string $hex A string representation of Hex (IE: "1a2b3c" not 0x1a2b3c)
	 * @return string a string
	 */
	public static function hex2Str($hex)
	{
		// php version >= 5.4 have a hex2bin function, use it
		// if it exists
		if(function_exists("hex2bin"))
			return hex2bin($hex);

		$parts = str_split($hex, 2);
		$parts = array_map(function($v) {
				return chr(self::hex2Dec($v));
		}, $parts);

		return implode("", $parts);
	}
	
	/**
	 * Converts Hex to Decimal
	 * This function just calls php's hexdec() function,  but I
	 * encapsulated it in this function to keep things uniform
	 * and have all possible conversion function available in
	 * the Cipher class
	 *
	 * @param string $hex A hex number to convert to decimal
	 * @return integer A decimal number
	 */
	public static function hex2Dec($hex)
	{
		return hexdec($hex);
	}
	
	/**
	 * Convert a string of characters to a decimal number
	 *
	 * @param string $str The string to convert to decimal
	 * @return integer The integer converted from the string
	 */
	public static function str2Dec($str)
	{
		$hex = self::str2Hex($str);
		return self::hex2Dec($hex);
	}
	
	/**
	 * Convert a decimal to a string of bytes
	 *
	 * @param integer $dec A decimal number
	 * @param integer $req_bytes Optional, forces the string to be at least
	 *	$req_bytes in size, this is needed because on occasion left most null bytes
	 *	are dropped in dechex(), causing the string to have a shorter byte
	 *	size than the initial integer.
	 * @return string A string with the number of bytes equal to $dec
	 */
	public static function dec2Str($dec, $req_bytes = 0)
	{
		$hex = self::dec2Hex($dec, $req_bytes);
		return self::hex2Str($hex);
	}
	
	/**
	 * For the G Permutations, the input data is 2 Bytes The first byte is
	 * the left side and the second is the right side.The round key is 4 bytes
	 * long (Indices 8*i-8 to 8*i), which is split as 4 pieces: K0, K1, K2, K3
	 *
	 * @param string $bytes A 2 byte string
	 * @param string $key 4 bytes of $this->expanded_key
	 * @return string A 2 byte string, the G Permutation of $bytes
	 */
	private function gPermutation($bytes, $key)
	{
		$left = ord($bytes[0]);
		$right = ord($bytes[1]);

		if($this->operation() == self::ENCRYPT)
		{
			for($i = 0; $i < 4; ++$i)
			{
				if($i == 0 || $i == 2)
				{
					$pos = $right ^ $this->str2Dec($key[$i]);
					$left = $left ^ self::$_f[$pos];
				}
				else
				{
					$pos = $left ^ $this->str2Dec($key[$i]);
					$right = $right ^ self::$_f[$pos];
				}
			}
		}
		else // parent::DECRYPT
		{
			// we do the same as in encryption, but apply the key backwards,
			// from key[3] to key[0]
			for($i = 3; $i >= 0; --$i)
			{
				if($i == 0 || $i == 2)
				{
					$pos = $right ^ $this->str2Dec($key[$i]);
					$left = $left ^ self::$_f[$pos];
				}
				else
				{
					$pos = $left ^ $this->str2Dec($key[$i]);
					$right = $right ^ self::$_f[$pos];
				}
			}
		}

		return $this->dec2Str($left).$this->dec2Str($right);
	}

	/**
	 * Indicates that this is a block cipher
	 *
	 * @return integer Returns Cipher::BLOCK
	 */
	public function type()
	{
		return self::BLOCK;
	}
	
	/**
	 * Size of the data in Bits that get used during encryption
	 *
	 * @param integer $bytes Number of bytes each block of data is required by the cipher
	 * @return integer The number of bytes each block of data required by the cipher
	 */
	public function blockSize($bytes = 0)
	{
		if($bytes > 0)
			$this->block_size = $bytes;

		// in some cases a blockSize is not set, such as stream ciphers.
		// so just return 0 for the block size
		if(!isset($this->block_size))
			return 0;

		return $this->block_size;
	}
}

class Mode_CBC
{
	/** @type integer HASH_LEN The length of md5() hash string */
	const HASH_LEN = 16;
	
	/**
	 * @type object $cipher The cipher object used within the mode
	 */
	protected $cipher = null;

	/**
	 * @type string $iv The IV used for the mode, not all Modes
	 * use an IV so this may be empty
	 */
	protected $iv = "";

	/**
	 * @type string $register For modes that use a register to do
	 * encryption/decryption. This stores the unencrypted register.
	 */
	protected $register = "";

	/**
	 * @type string $enc_register For modes that use a register to do
	 * encryption/decryption. This stores the encrypted register
	 */
	protected $enc_register = "";

	/**
	 * @type integer $block_size The byte size of the block to
	 * encrypt/decrypt for the Mode
	 */
	private $block_size = 0;

	/** @type string $mode_name The name of mode currently used */
	public $mode_name = "CBC";
	
	/**
	 * @type string $padding The type of padding to use when required.
	 * Padding types are defined in phpCrypt class. Defaults to
	 * PHP_Crypt::PAD_ZERO
	 */
	private $padding = 0;
	
	/**
	 * Constructor
	 * Sets the cipher object that will be used for encryption
	 *
	 * @param object $cipher one of the phpCrypt encryption cipher objects
	 * @return void
	 */
	function __construct($cipher)
	{
		$this->name = "CBC";
		$this->cipher = $cipher;
		$this->block_size = $this->cipher->blockSize();

		// this works with only block Ciphers
		if($cipher->type() != 1)
			trigger_error("CBC mode requires a block cipher", E_USER_WARNING);
	}

	/**
	 * Destructor
	 *
	 * @return void
	 */
	public function __destruct()
	{
		
	}
	
	/**
	 * Sets or Returns the padding type used with the mode
	 * If the $type parameter is not given, this function
	 * returns the the padding type only.
	 *
	 * @param string $type One of the predefined padding types
	 * @return void
	 */
	public function padding($type = "")
	{
		if($type != "")
			$this->padding = $type;

		return $this->padding;
	}

	/**
	 * Create an IV if the Mode used requires an IV.
	 * The IV should be saved and used for Encryption/Decryption
	 * of the same blocks of data.
	 * There are 3 ways to auto generate an IV by setting $src parameter
	 * PHP_Crypt::RAND - Default, uses mt_rand()
	 * PHP_Crypt::RAND_DEV_RAND - Unix only, uses /dev/random
	 * PHP_Crypt::RAND_DEV_URAND - Unix only, uses /dev/urandom
	 * PHP_Crypt::RAND_WIN_COM - Windows only, uses Microsoft's CAPICOM SDK
	 *
	 * @param string $src Optional, Sets how the IV is generated, must be
	 *	one of the predefined PHP_Crypt RAND constants. Defaults to
	 *	PHP_Crypt::RAND if none is given.
	 * @return string The IV that is being used by the mode
	 */
	public function createIV($src = "rand")
	{
		// if the mode does not use an IV, lets not waste time
		if(!$this->requiresIV())
			return false;

		$iv = self::randBytes($src, $this->block_size);
		return $this->IV($iv);
	}
	
	/**
	 * Sets or Returns an IV for the mode to use. If the $iv parameter
	 * is not given, this function only returns the current IV in use.
	 *
	 * @param string $iv Optional, An IV to use for the mode and cipher selected
	 * @return string The current IV being used
	 */
	public function IV($iv = null)
	{
		if($iv != null)
		{
			// check that the iv is the correct length,
			$len = strlen($iv);
			if($len != $this->block_size)
			{
				$msg = "Incorrect IV size. Supplied length: $len bytes, Required: {$this->block_size} bytes";
				trigger_error($msg, E_USER_WARNING);
			}

			$this->clearRegisters();
			$this->register = $iv;
			$this->iv = $iv;
		}

		return $this->iv;
	}

	/**
	 * Clears the registers used for some modes
	 *
	 * @return void
	 */
	private function clearRegisters()
	{
		$this->register = "";
		$this->enc_register = "";
	}
	
	/**
	 * Create a string of random bytes, used for creating an IV
	 * and a random key. See PHP_Crypt::createKey() and PHP_Crypt::createIV()
	 * There are 4 ways to auto generate random bytes by setting $src parameter
	 * PHP_Crypt::RAND - Default, uses mt_rand()
	 * PHP_Crypt::RAND_DEV_RAND - Unix only, uses /dev/random
	 * PHP_Crypt::RAND_DEV_URAND - Unix only, uses /dev/urandom
	 * PHP_Crypt::RAND_WIN_COM - Windows only, uses Microsoft's CAPICOM SDK
	 *
	 * @param string $src Optional, Use the $src to create the random bytes
	 * 	by default PHP_Crypt::RAND is used when $src is not specified
	 * @param integer $byte_len The length of the byte string to create
	 * @return string A random string of bytes
	 */
	public function randBytes($src, $byte_len)
	{
		$bytes = "";
		$err_msg = "";
		
		// if the random bytes where not created properly or PHP_Crypt::RAND was
		// passed as the $src param, create the bytes using mt_rand(). It's not
		// the most secure option but we have no other choice
		if(strlen($bytes) < $byte_len)
		{
			$bytes = "";

			// md5() hash a random number to get a 16 byte string, keep looping
			// until we have a string as long or longer than the ciphers block size
			for($i = 0; ($i * self::HASH_LEN) < $byte_len; ++$i){
				// $bytes .= md5(mt_rand(), true);
				$bytes .= md5(12012014, true);
			}
		}

		// because $bytes may have come from mt_rand() or /dev/urandom which are not
		// cryptographically secure, lets add another layer of 'randomness' before
		// the final md5() below
		// $bytes = str_shuffle($bytes);

		// md5() the $bytes to add extra randomness. Since md5() only returns
		// 16 bytes, we may need to loop to generate a string of $bytes big enough for
		// some ciphers which have a block size larger than 16 bytes
		$tmp = "";
		$loop = ceil(strlen($bytes) / self::HASH_LEN);
		for($i = 0; $i < $loop; ++$i)
			$tmp .= md5(substr($bytes, ($i * self::HASH_LEN), self::HASH_LEN), true);

		// grab the number of bytes equal to the requested $byte_len
		return substr($tmp, 0, $byte_len);
	}
	
	/**
	 * This mode requires an IV
	 *
	 * @return boolean Returns True
	 */
	public function requiresIV()
	{
		return true;
	}
	
	/**
	 * Checks to see if the current mode requires an IV and that it is set
	 * if it is required. Triggers E_USER_WARNING an IV is required and not set
	 *
	 * @return void
	 */
	public function checkIV()
	{
		if($this->requiresIV() && strlen($this->register) == 0)
		{
			$msg = strtoupper($this->mode_name)." mode requires an IV or the IV is empty";
			trigger_error($msg, E_USER_WARNING);
		}
	}
	
	/**
	 * Encrypts an the entire string $plain_text using the cipher passed
	 * to the constructor in CBC mode
	 * The steps to encrypt using CBC are as follows
	 * 1) Get a block of plain text data to use in the Cipher
	 * 2) XOR the block with IV (if it's the first round) or the previous
	 *	round's encrypted result
	 * 3) Encrypt the block using the cipher
	 * 4) Save the encrypted block to use in the next round
	 *
	 * @param string $text the string to be encrypted in CBC mode
	 * @return boolean Returns true
	 */
	public function encrypt(&$text)
	{
		$this->pad($text);
		$blocksz = $this->cipher->blockSize();

		$max = strlen($text) / $blocksz;
		for($i = 0; $i < $max; ++$i)
		{
			// get the current position in $text
			$pos = $i * $blocksz;

			// grab a block of plain text
			$block = substr($text, $pos, $blocksz);

			// xor the block with the register
			for($j = 0; $j < $blocksz; ++$j)
				$block[$j] = $block[$j] ^ $this->register[$j];

			// encrypt the block, and save it back to the register
			$this->cipher->encrypt($block);
			$this->register = $block;

			// replace the plain text block with the cipher text
			$text = substr_replace($text, $this->register, $pos, $blocksz);
		}

		return true;
	}

	/**
	 * Decrypts an the entire string $plain_text using the cipher passed
	 * to the constructor in CBC mode
	 * The decryption algorithm requires the following steps
	 * 1) Get the first block of encrypted text, save it
	 * 2) Decrypt the block
	 * 3) XOR the decrypted block (if it's the first pass use the IV,
	 *	else use the encrypted block from step 1
	 * 4) the result from the XOR will be a plain text block, save it
	 * 5) assign the encrypted block from step 1 to use in the next round
	 *
	 * @param string $text the string to be decrypted in CBC mode
	 * @return boolean Returns true
	 */
	public function decrypt(&$text)
	{
		$blocksz = $this->cipher->blockSize();

		$max = strlen($text) / $blocksz;
		for($i = 0; $i < $max; ++$i)
		{
			// get the current position in $text
			$pos = $i * $blocksz;

			// grab a block of cipher text, and save it for use later
			$block = substr($text, $pos, $blocksz);
			$tmp_block = $block;

			// decrypt the block of cipher text
			$this->cipher->decrypt($block);

			// xor the block with the register
			for($j = 0; $j < $blocksz; ++$j)
				$block[$j] = $block[$j] ^ $this->register[$j];

			// replace the block of cipher text with plain text
			$text = substr_replace($text, $block, $pos, $blocksz);

			// save the cipher text block to the register
			$this->register = $tmp_block;
		}

		$this->strip($text);
		return true;
	}

	/**
	 * Pads str so that final block is $block_bits in size, if the final block
	 * is $block_bits, then an additional block is added that is $block_bits in size
	 * The padding should be set by phpCrypt::setPadding()
	 *
	 * @param string $str the string to be padded
	 * @return boolean Returns true
	 */
	protected function pad(&$str)
	{
		$len = strlen($str);
		$bytes = $this->cipher->blockSize(); // returns bytes

		// now determine the next multiple of blockSize(), then find
		// the difference between that and the length of $str,
		// this is how many padding bytes we will need
		$num = ceil($len / $bytes) * $bytes;
		$num = $num - $len;

		self::zeroPad($str, $num);
		return true;
	}

	/**
	 * Pads a string with null bytes
	 *
	 * @param string $text The string to be padded
	 * @param integer $bytes The number of bytes to pad
	 * @return boolean Returns true
	 */
	private static function zeroPad(&$text, $bytes)
	{
		$len = $bytes + strlen($text);
		$text = str_pad($text, $len, chr(0), STR_PAD_RIGHT);
		return true;
	}
	
	/**
	 * Strip out the padded blocks created from Pad().
	 * Padding type should be set by phpCrypt::setPadding()
	 *
	 * @param string $str the string to strip padding from
	 * @return boolean Returns True
	 */
	protected function strip(&$str)
	{
		self::zeroStrip($str, $this->padding);
		return true;
	}
	
	/**
	 * Strips null padding off a string
	 * NOTE: This is generally a bad idea as there is no way
	 * to distinguish a null byte that is not padding from
	 * a null byte that is padding. Stripping null padding should
	 *  be handled by the developer at the application level.
	 *
	 * @param string $text The string with null padding to strip
	 * @return boolean Returns true
	 */
	private static function zeroStrip(&$text)
	{
		// with NULL byte padding, we should not strip off the
		// null bytes, instead leave this to the developer at the
		// application level
		//$text = preg_replace('/\0+$/', '', $text);
		return true;
	}
	
}
?>