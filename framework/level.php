<?php

class Level {
	
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
		
		$sql = "select * from `level` where id = ".(int)$id;
				
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
				$level = new Level();
				self::$instance = $level;
				return $level;
			}else{
				return self::$instance;
			}
		}else{
			return new Level($id);
		}
	}
	
	public static function getAll()
	{
		$db = App::getDbo();
		$db->setQuery("SELECT * FROM level");
		return $db->loadObjectList();
	}
	
	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "level";
		$item = "Level";
		
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
		$content = $db->loadObject();
		$item .= " (".$content->nama.")";
		
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
		$table = "level";
		$item = "Level";
		
		$id = Jinput::post("id", null, "int");
		$nama = Jinput::post("objname", null, "words");
		
		if($id){ // EDIT
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nama = $nama;
			$item .= " (".$obj->nama.")";
			
			$result = $db->updateObject($table, $obj, "id");
			if($result){
				// logging
				Logs::saveLog($user->id, "Update level", $id, $table);
				
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
			$item .= " (".$obj->nama.")";
			
			$db->insertObject($table, $obj, "id");
			$result = $db->getResult();
			if(!is_array($result)){
				// logging
				Logs::saveLog($user->id, "Add new level", $db->connect->insert_id, $table);
				
				App::message("Successfully added new ".$item);
				return true;
			}else{
				$msg = "";
				foreach($result as $res){
					$msg .= "ERROR".$res['errno'].": ".$res['error']."<br>";
				}
				
				App::message($msg."Failed to add new ".$item, "error");
				return false;
			}
		}
		
	}
	
}	
?>