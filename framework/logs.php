<?php

class Logs {
	
	private $table = "logs";
	public static $instance = null;
	
	function __construct($id = null)
	{
		$this->table = "logs";
	}
	
	public static function getInstance($id = null)
	{
		if(!$id)
		{
			if(!self::$instance){
				$object = new Logs();
				self::$instance = $object;
				return $object;
			}else{
				return self::$instance;
			}
		}else{
			return new Logs($id);
		}
	}
	
	public static function saveLog($subject, $action, $object=null, $table=null)
	{
		$db = App::getDbo();
		
		$self = self::getInstance();
		
		$obj = new stdClass();
		$obj->user = $subject;
		$obj->aksi = $action;
		$obj->objek = $object;
		$obj->tabel = $table;
		$obj->waktu = time();
		$result = $db->insertObject($self->table, $obj, "id");
		
		return $result;
	}
	
	public static function getAll()
	{
		$db = App::getDbo();
		
		$sql = "select a.*, b.nama as username from `logs` a left join `user` b on b.id=a.`user` order by id desc";
		$db->setQuery($sql);
		
		return $db->loadObjectList();
	}
}

?>