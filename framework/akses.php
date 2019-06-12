<?php

class Akses {
	
	public $id;
	public $nama;
	public $keterangan;
	
	public static $instance = null;
	
	function __construct($id=null)
	{
		$db = App::getDbo();
		
		if($id < 1){
			$this->id = 0;
			$this->nama = null;
			$this->keterangan = null;
			self::$instance = $this;
			return false;
		}
		
		$sql = "select * from `akses` where id = ".(int)$id;
				
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->id = $a->id;
				$this->nama = $a->nama;
				$this->keterangan = $a->keterangan;
			}
		}else{
			$this->id = 0;
			$this->nama = null;
			$this->keterangan = null;
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$object = new Akses();
				self::$instance = $object;
				return $object;
			}else{
				return self::$instance;
			}
		}else{
			return new Akses($id);
		}
	}
	
	public static function getAll()
	{
		$db = App::getDbo();
		$db->setQuery("SELECT * FROM akses");
		return $db->loadObjectList();
	}
	
	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "akses";
		$item = "Access";
		
		// first, check is paramater 'id' have value
		if(!$id){
			App::message("No ".$item." is selected to be deleted", "error");
			return false;
		}
		
		// check data with inputted paramater is exist
		$db->setQuery("SELECT * FROM $table WHERE id = ".$id);
		if($db->getNumRows() == 0){
			App::message($item." is not found", "error");
			return false;
		}
		$exist = $db->loadObject();
		$item .= " (".$exist->nama.")";
		
		// delete data with inputted paramater
		$db->setQuery("DELETE FROM $table WHERE id = ".$id);
		if($db->getAffectedRows() > 0){
			// logging
			$user = App::getUser();
			Logs::saveLog($user->id, "Delete ".$item, null, null);
			
			App::message("Successfully deleted ".$item);
			return true;
		}else{
			App::message("Failed to delete ".$item.", please try again", "error");
			return false;
		}
	}
	
	public static function save()
	{
		$db = App::getDbo();
		$cfg = App::getConfig();
		$user = App::getUser();
		$table = "akses";
		$item = "access";
		
		$id = Jinput::post("id", null, "int");
		$nama = Jinput::post("objname", null, "words");
		$ket = Jinput::post("objdesc", null, "words");
		
		if($id){ // EDIT
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = strtoupper($nama);
			$obj->keterangan = $ket;
			
			$result = $db->updateObject($table, $obj, "id");
			$item .= " (".$obj->nama.")";
			if($result){
				// logging
				Logs::saveLog($user->id, "Update access", $id, $table);
				
				App::message("Successfully updated ".$item);
				return true;
			}else{
				App::message("Failed to update ".$item, "error");
				return false;
			}
		}
		else{ // INSERT NEW
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = strtoupper($nama);
			$obj->keterangan = $ket;
			
			$result = $db->insertObject($table, $obj, "id", true);
			if($result){
				// logging
				Logs::saveLog($user->id, "Add new ".$item, $result[1], $table);
				
				App::message("Successfully added new ".$item);
				return true;
			}else{
				App::message("Failed to add new ".$item, "error");
				return false;
			}
		}
		
	}
	
}	
?>