<?php

include_once "Skipjack.php";
include_once "RSA.php";
include_once "ZIP.php";

class Files {
	
	public $id;
	public $idpegawai;
	public $nama;
	public $jenis;
	public $ukuran;
	public $waktu;
	public $keterangan;
	
	public static $instance = null;
	
	function __construct($id = null)
	{
		$db = App::getDbo();
		
		if(isset($_GET['id'])){
			$id = (int)$_GET['id'];
		}
		
		if($id < 1){
			$this->id = 0;
			$this->idpegawai = 0;
			$this->nama = null;
			$this->jenis = null;
			$this->ukuran = null;
			$this->waktu = null;
			$this->keterangan = null;
			self::$instance = $this;
			return false;
		}
		
		$sql = "select * from `files` where id = ".(int)$id;
		
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->id = $a->id;
				$this->idpegawai = $a->idpegawai;
				$this->nama = $a->nama;
				$this->jenis = $a->jenis;
				$this->ukuran = $a->ukuran;
				$this->waktu = $a->waktu;
				$this->keterangan = $a->keterangan;
			}
		}else{
			$this->id = 0;
			$this->idpegawai = 0;
			$this->nama = null;
			$this->jenis = null;
			$this->ukuran = null;
			$this->waktu = null;
			$this->keterangan = null;
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$object = new Files();
				self::$instance = $object;
				return $object;
			}else{
				return self::$instance;
			}
		}else{
			return new Files($id);
		}
	}
	
	public static function getAll($idpegawai = null, $total = false)
	{
		$db = App::getDBo();
		
		$sql = "select * from files";
		if($idpegawai){
			$sql .= " where idpegawai = ".$idpegawai;
		}
		$sql .= " order by id desc";
		
		$db->setQuery($sql);
		
		if($total)
			return $db->getNumRows();
		else
			return $db->loadObjectList();
	}
	
	public static function upload($path, $id, $judul, $file, $keterangan)
	{
		$db = App::getDBo();
		$cfg = App::getConfig();
		$table = "files";
		
		// $allowed = $cfg->allowed;
		$sizeMB = $cfg->maxsize;
		if($sizeMB > 0){
			$sizeKB = $sizeMB*1024;
		}else{
			$sizeKB = 512;
		}
		$maxsize = $sizeKB*1024;
		
		// check file size
		if($file['size'] > 0){
			if($file['size'] > $maxsize){ // over size
				$msg = "Filesize";
				
				if($file['size'] > 0) $msg .= " (".number_format($file['size'], 0, '.', ',')." bytes)";
				
				$msg .= " is greater than ".number_format($maxsize, 0, '.', ',')." bytes";
				if($sizeMB > 0){
					$msg .= " (".$sizeMB." MB)";
				}
				return $msg;
			}
			else{ // allowed size
				self::deleteAllDecFiles(); // delete all decrypted files
				
				$arrName = explode(".", $file['name']);
				$filename = $arrName[0];
				$fileext = strtolower($arrName[count($arrName)-1]);
				$origins = $file["tmp_name"];
				$file_name = $judul.".".$fileext; // specified filename 
				
				// insert into table first to record upload time begin
				$obj = new stdClass();
				$obj->id = null;
				$obj->idpegawai = $id;
				$obj->nama = $judul;
				$obj->jenis = $fileext;
				$obj->ukuran = $file['size'];
				$obj->upload = time();
				$obj->keterangan = $keterangan;
				$result = $db->insertObject($table, $obj, "id", true);
				$insert_id = $result[1];
				
				/* DIGITAL SIGNATURE - SIGNING */
				$rsa = new Crypt_RSA();
				extract($rsa->createKey());
				$plaintext = file_get_contents($origins);
				$rsa->loadKey($privatekey);
				$signature = $rsa->sign($plaintext);
				/* DIGITAL SIGNATURE - DONE */
				
				// update table to save public & private key
				$obj = new stdClass();
				$obj->id = $insert_id;
				$obj->signed = time();
				$obj->public_key = $publickey;
				$obj->signature = $signature;
				$result = $db->updateObject($table, $obj, "id");
				
				/* COMPRESSION ZIP */
				$desti_compress = $path."/".$judul.".zip"; // compressed file destination folder
				$zip = new Zipper();
				if ($zip->open($desti_compress, $zip->zip())!==TRUE) {
					return "Cannot create ZIP file";
				}
				$zip->addFile($origins, $file_name);
				$zip->close();
				/* COMPRESSION - DONE */
				
				/* SKIPJACK CRYPTOGRAPH ALGORITHM - ENCRYPTION */
				// auto-generate Secret Key from file details
				$key = App::generateSecretKey($id, $judul, $fileext);
				$cipher = "Skipjack";
				$mode = "CBC";
				$crypt = new PHP_Crypt($key, $cipher, $mode);
				$cipher_block_sz = $crypt->cipherBlockSize(); // in bytes
				$result = "";
				// encryption destination folder (sample: $path/6_1_namafile.vic)
				$destination = $path."/".$id."_".$insert_id."_".$judul.$cfg->ext;
				
				$rhandle = fopen($desti_compress, "r");
				$whandle = fopen($destination, "w+b");
				
				// CBC mode requires an IV, create it
				$iv = $crypt->createIV();
				
				while (!feof($rhandle))
				{
					$bytes = fread($rhandle, $cipher_block_sz);
					$result = $crypt->encrypt($bytes);
					fwrite($whandle, $result);
				}
				fclose($rhandle);
				fclose($whandle);
				/* SKIPJACK CRYPTOGRAPH ALGORITHM - DONE */
				
				// update table to record upload time finish
				$obj = new stdClass();
				$obj->id = $insert_id;
				$obj->waktu = time();
				$obj->kompresi = filesize($desti_compress);
				$result = $db->updateObject($table, $obj, "id");
				
				// remove zip file from server after encrypting
				unlink($desti_compress);
				
				// logging
				$user = App::getUser();
				Logs::saveLog($user->id, "Upload employee's file", $insert_id, $table);
				
				if($result){
					return "Successfully uploaded & encrypted file";
				}
				else{
					return "Successfully uploaded & encrypted file, but failed to record the progress in database";
				}
			}
		}
		else{
			return "File is not found to be uploaded or the choosen filesize is greater than ".$sizeMB." MB";
		}
	}

	public static function download($path, $id)
	{
		$db = App::getDBo();
		$table = "files";
		$db->setQuery("select * from $table where id = ".$id);
		$result = $db->loadObject();
		
		$cfg = App::getConfig();
		$idpegawai = $result->idpegawai;
		$judul = $result->nama;
		$fileext = $result->jenis;
		$resid = $result->id;
		$publickey = $result->public_key;
		$signature = $result->signature;
		
		$origin = $idpegawai."_".$id."_".$judul.$cfg->ext; // encrypted filename (e.g. 6_1_namafile.vic)
		$origin_path = $path."/".$origin; // encrypted file's path
		
		$dec_file = $judul.".zip"; // decrypted filename
		$dec_file_path = $path."/".$dec_file; // decrypted file's path
		
		$target_dir = "decrypted";
		$pathdir = $path."/".$target_dir;
		$target_file = $judul.".".$fileext; // example: test.txt
		$target_path = $pathdir."/".$target_file; // target file's path
		
		// generate url to download decrypted & decompressed file
		$download_url = $cfg->protocol."://".$_SERVER["SERVER_NAME"]."/download.php?id=".$id;
		
		// checking is decrypted file requested is existing
		if(file_exists($target_path)){
			return $download_url; // if existing, just download it
		}
		
		self::deleteAllDecFiles(); // delete all decrypted files
		
		// update table to record decrypted time begin
		$obj = new stdClass();
		$obj->id = $resid;
		$obj->dekrip_mulai = time();
		$db->updateObject($table, $obj, "id");
		
		/* SKIPJACK CRYPTOGRAPH ALGORITHM - DECRYPTION */
		// auto-generate Secret Key from file details
		$key = App::generateSecretKey($idpegawai, $judul, $fileext);
		$cipher = "Skipjack";
		$mode = "CBC";
		
		$crypt = new PHP_Crypt($key, $cipher, $mode);
		$cipher_block_sz = $crypt->cipherBlockSize(); // in bytes
		$result = "";
		
		$rhandle = fopen($origin_path, "rb");
		$whandle = fopen($dec_file_path, "w+");

		// we need to set the IV to the same IV used for encryption
		$crypt->createIV();

		while (!feof($rhandle))
		{
			$bytes = fread($rhandle, $cipher_block_sz);
			$result = $crypt->decrypt($bytes);
			fwrite($whandle, $result);
		}
		fclose($rhandle);
		fclose($whandle);
		/* SKIPJACK CRYPTOGRAPH ALGORITHM - DONE */
		
		// update table to record decrypted time finish
		$obj = new stdClass();
		$obj->id = $resid;
		$obj->dekrip_selesai = time();
		$db->updateObject("files", $obj, "id");
		
		/* DECOMPRESSION/EXTRACTION */
		$zip = new Zipper();
		$unzip = $zip->unzip($dec_file_path, $pathdir."/", true, true);
		if(!$unzip){
			unlink($dec_file_path); // delete ZIP file from server
			// return "File is failed to be extracted";
			return "File is failed to be decrypted";
			// gagal extract karena gagal dekripsi menggunakan kunci yg berbeda
		}
		unlink($dec_file_path); // delete ZIP file from server
		/* DECOMPRESSION/EXTRACTION - DONE */
		
		/* DIGITAL SIGNATURE - VERIFY */
		$rsa = new Crypt_RSA();
		$plaintext = file_get_contents($target_path);
		$rsa->loadKey($publickey);
		if($rsa->verify($plaintext, $signature)){
			// update table to record verification digital signature time
			$obj = new stdClass();
			$obj->id = $resid;
			$obj->verifikasi = time();
			$db->updateObject($table, $obj, "id");
			
			// logging
			$user = App::getUser();
			Logs::saveLog($user->id, "Download employee's file", $id, $table);
			
			return $download_url;
		}else{
			// delete target file from server
			unlink($target_path);
			
			return "File is unverified by Digital Signature";
		}
	}
	
	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "files";
		$item = "file";
		$cfg = App::getConfig();
		
		// first, check is paramater 'id' have value
		if(!$id){
			App::message("No ".ucwords($item)." is selected to be deleted", "error");
			return false;
		}
		
		// check data with inputted paramater is exist
		$db->setQuery("SELECT * FROM $table WHERE id = ".$id);
		if($db->getNumRows() == 0){
			App::message("Selected ".ucwords($item)." is not found", "error");
			return false;
		}
		$exist = $db->loadObject();
		$deleted = $item." (".$exist->nama.".".$exist->jenis.")";
		
		// delete data with inputted paramater
		$db->setQuery("DELETE FROM $table WHERE id = ".$id);
		if($db->getAffectedRows() > 0){
			$path = APPS."/uploads/files/";
			$file = $exist->idpegawai."_".$exist->id."_".$exist->nama.$cfg->ext;
			$origin = $path.$file;
			if(file_exists($origin)){
				unlink($origin);
			}
			
			// logging
			$user = App::getUser();
			Logs::saveLog($user->id, "Delete ".$deleted, null, null);
			
			App::message("Successfully deleted ".$deleted);
			return true;
		}else{
			App::message("Failed to delete ".$deleted.", please try again", "error");
			return false;
		}
	}

	public static function deleteAllDecFiles()
	{
		$cfg = App::getConfig();
		$pathdir = $cfg->path."/decrypted/*";
		$files = glob($pathdir); // get all file names
		foreach($files as $file){ // iterate files
		  if(is_file($file))
			unlink($file); // delete file
		}
	}
}

?>