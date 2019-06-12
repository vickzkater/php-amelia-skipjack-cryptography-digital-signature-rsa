<?php

class User {
	
	public $id;
	public $nama;
	public $email;
	public $password;
	public $level;
	public $dibuat;
	public $status;
	public $diubah;
	public $collapsed;
	
	public static $instance = null;
	
	function __construct($id = null)
	{
		$db = App::getDbo();
		
		$reqid = $id;

		if(!$id){
			if(isset($_SESSION['userid'])){
				$id = $_SESSION['userid'];
			}else{
				$id = 0;
			}
		}
		
		if($id < 1){
			$this->id = 0;
			$this->nama = "Guest";
			$this->email = null;
			$this->password = null;
			$this->level = null;
			$this->dibuat = null;
			$this->status = "non-aktif";
			$this->diubah = null;
			$this->collapsed = 0;
			self::$instance = $this;
			return false;
		}
		
		$sql = "select * from `user` where id = ".(int)$id;
				
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->id = $a->id;
				$this->nama = $a->nama;
				$this->email = $a->email;
				$this->password = $a->password;
				$this->level = $a->level;
				$this->dibuat = $a->dibuat;
				$this->status = $a->status;
				$this->diubah = $a->diubah;
				$this->collapsed = $a->collapsed;
			}
		}else{
			$this->id = 0;
			$this->nama = "Guest";
			$this->email = null;
			$this->password = null;
			$this->level = null;
			$this->dibuat = null;
			$this->status = "non-aktif";
			$this->diubah = null;
			$this->collapsed = 0;
		}
		
		if(!$reqid){
			self::$instance = $this;
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$user = new User();
				self::$instance = $user;
				return $user;
			}else{
				return self::$instance;
			}
		}else{
			return new User($id);
		}
	}
	
	public static function login()
	{
		App::clearMessages();
		$db = App::getDbo();
		$cfg = App::getConfig();
		
		$logname = Jinput::post("logname", null, "email");
		$logpass = Jinput::post("logpass", null, "string");
		
		if(!$logname){
			App::message("Email must be filled", "error");
			return false;
		}
		if(!$logpass){
			App::message("Password must be filled", "error");
			return false;
		}
		
		// encrypt password
		$passcode = App::encrypt($logpass);
		
		$db->setQuery("SELECT * FROM user WHERE email = ".$db->quote($logname));
		$result = $db->loadObject();
		
		if($result){
			if($result->status != "aktif"){
				App::message("Sorry, your account has been disabled for some reasons. Please contact System Administrator for support.", "error");
				return false;
			}
			if($result->password != $passcode){
				App::message("Password is incorrect", "error");
				return false;
			}
			// success login
			$_SESSION['userid'] = $result->id;
			
			// logging
			Logs::saveLog($result->id, "Login", null, null);
			
			App::message("Welcome to ".$cfg->sitename." (".$cfg->sitefullname.")");
			return true;
		}else{
			App::message("Email/Password is incorrect", "error");
			return false;
		}
	}
	
	public static function getAll()
	{
		$db = App::getDBo();
		$db->setQuery("select a.*, b.nama as levelname from `user` a left join `level` b on b.id=a.`level` order by a.nama");
		$results = $db->loadObjectList();
		if($results){
			return $results;
		}else{
			return false;
		}
	}
	
	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "user";
		$item = "user";
		
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
		$deleted = $item." (".$exist->nama.")";
		
		// delete data with inputted paramater
		$db->setQuery("DELETE FROM $table WHERE id = ".$id);
		if($db->getAffectedRows() > 0){
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
	
	public static function save()
	{
		$db = App::getDbo();
		$cfg = App::getConfig();
		$user = App::getUser();
		$table = "user";
		
		$id = Jinput::post("id", null, "int");
		$nama = Jinput::post("objname", null, "words");
		$email = Jinput::post("objemail", null, "email");
		$pass = Jinput::post("objpass", null, "string");
		$pass2 = Jinput::post("objpass2", null, "string");
		$level = Jinput::post("objlevel", $cfg->userlevel, "int");
		$status = Jinput::post("objstatus", "aktif", "string");
		$created = null;
		$modified = null;
		
		if($id){ // EDIT
			$modified = time();
			
			if(isset($pass)){
				if($pass != $pass2){
					App::message("Password & Confirm Password must be same", "error");
					return false;
				}
				$pass = App::encrypt($pass);
			}
			
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = $nama;
			$obj->email = $email;
			$obj->password = $pass;
			$obj->level = $level;
			$obj->dibuat = $created;
			$obj->status = $status;
			$obj->diubah = $modified;
			
			$result = $db->updateObject($table, $obj, "id");
			if($result){
				if($pass){
					// logging
					Logs::saveLog($user->id, "Update new password", $id, $table);
					
					App::message("Successfully updated new password");
				}else{
					// logging
					Logs::saveLog($user->id, "Update user detail", $id, $table);
					
					App::message("Successfully updated user");
				}
				
				return true;
			}else{
				App::message("Failed to update user", "error");
				return false;
			}
		}
		else{ // INSERT NEW
			if($pass != $pass2){
				App::message("Password & Confirm Password must be same", "error");
				return false;
			}
			
			$pass = App::encrypt($pass);
			$created = time();
			
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = $nama;
			$obj->email = $email;
			$obj->password = $pass;
			$obj->level = $level;
			$obj->dibuat = $created;
			$obj->status = $status;
			$obj->diubah = $modified;
			
			$result = $db->insertObject($table, $obj, "id", true);
			if(is_array($result)){
				// logging
				Logs::saveLog($user->id, "Add new user", $result[1], $table);
				
				App::message("Successfully added new user (".$nama.")");
				return true;
			}else{
				App::message("Failed to add new user (".$nama.")", "error");
				return false;
			}
		}
		
	}

	public static function setCollapsedSidebar($id)
	{
		$db = App::getDbo();
		$self = self::getInstance($id);
		
		if($self->collapsed > 0){
			$set = 0;
		}else{
			$set = 1;
		}
		$sql = "update user set collapsed = ".$set." where id = ".$self->id;
		$db->setQuery($sql);
		if($db->result){
			return "success";
		}else{
			return "failed";
		}
	}
}
?>