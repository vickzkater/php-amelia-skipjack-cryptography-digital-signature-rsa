<?php

class Divisi {
	
	public $id;
	public $nama;
	
	public static $instance = null;
	
	function __construct($id=null)
	{
		$db = App::getDbo();
		
		if($id < 1){
			$this->id = 0;
			$this->nama = null;
			self::$instance = $this;
			return false;
		}
		
		$sql = "select * from `divisi` where id = ".(int)$id;
				
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->id = $a->id;
				$this->nama = $a->nama;
			}
		}else{
			$this->id = 0;
			$this->nama = null;
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$object = new Divisi();
				self::$instance = $object;
				return $object;
			}else{
				return self::$instance;
			}
		}else{
			return new Divisi($id);
		}
	}
	
	public static function getAll()
	{
		$db = App::getDBo();
		$db->setQuery("select * from `divisi`");
		$results = $db->loadObjectList();
		if($results){
			return $results;
		}else{
			return false;
		}
	}
	
	public static function save()
	{
		$db = App::getDbo();
		$cfg = App::getConfig();
		$user = App::getUser();
		$table = "divisi";
		$item = "division";
		
		$id = Jinput::post("id", null, "int");
		$nama = Jinput::post("objname", null, "words");
		$item .= " (".$nama.")"; 
		
		if($id){ // EDIT
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = $nama;
			
			$result = $db->updateObject($table, $obj, "id");
			if($result){
				// logging
				Logs::saveLog($user->id, "Update division detail", $id, $table);
				
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
			$obj->nama = $nama;
			
			$result = $db->insertObject($table, $obj, "id", true);
			if($result){
				// logging
				Logs::saveLog($user->id, "Add new division", $result[1], $table);
				
				App::message("Successfully added new ".$item);
				return true;
			}else{
				App::message("Failed to add new ".$item, "error");
				return false;
			}
		}
		
	}

	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "divisi";
		$item = "division";
		
		// first, check is paramater 'id' have value
		if(!$id){
			App::message("No ".ucwords($item)." is selected to be deleted", "error");
			return false;
		}
		
		// check data with inputted paramater is exist
		$db->setQuery("SELECT * FROM $table WHERE id = ".$id);
		if($db->getNumRows() == 0){
			App::message("Selected ".ucwords($item)." is not found in database", "error");
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
	
}

?>