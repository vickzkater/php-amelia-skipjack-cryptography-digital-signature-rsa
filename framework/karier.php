<?php

class Karier {
	
	public $idpegawai;
	public $divisi;
	public $jabatan;
	public $posisi;
	public $bergabung;
	public $berakhir;
	public $keterangan;
	
	public static $instance = null;
	
	function __construct($id=null)
	{
		$db = App::getDbo();
		
		if(isset($_GET['id'])){
			$id = (int)$_GET['id'];
		}
		
		if($id < 1){
			$this->idpegawai = 0;
			$this->divisi = array();
			$this->jabatan = array();
			$this->posisi = array();
			$this->bergabung = array();
			$this->berakhir = array();
			$this->keterangan = array();
			self::$instance = $this;
			return false;
		}
		
		$sql = "select a.*, group_concat(b.nama separator ',') as namadivisi, group_concat(c.nama separator ',') as namajabatan, group_concat(a.posisi separator ',') as namaposisi, group_concat(a.bergabung separator ',') as wktgabung, group_concat(a.berakhir separator ',') as wktakhir, group_concat(a.keterangan separator '|') as listket from karier a left join divisi b on b.id=a.`divisi` left join jabatan c on c.id=a.`jabatan` where a.idpegawai=".(int)$id;
		
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->idpegawai = $a->idpegawai;
				$this->divisi = explode(",", $a->namadivisi);
				$this->jabatan = explode(",", $a->namajabatan);
				$this->posisi = explode(",", $a->namaposisi);
				$this->bergabung = explode(",", $a->wktgabung);
				$this->berakhir = explode(",", $a->wktakhir);
				$this->keterangan = explode("|", $a->listket);
			}
		}else{
			$this->idpegawai = 0;
			$this->divisi = array();
			$this->jabatan = array();
			$this->posisi = array();
			$this->bergabung = array();
			$this->berakhir = array();
			$this->keterangan = array();
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$object = new Karier();
				self::$instance = $object;
				return $object;
			}else{
				return self::$instance;
			}
		}else{
			return new Karier($id);
		}
	}
	
	public static function getCareer($id)
	{
		$db = App::getDBo();
		$db->setQuery("select a.*, b.nama as namadivisi, c.nama as namajabatan from karier a left join divisi b on b.id=a.`divisi` left join jabatan c on c.id=a.`jabatan` where a.idpegawai=".$id." order by a.bergabung desc");
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
		$table = "karier";
		$item = "career";
		
		$emp_id = Jinput::post("emp_id", null, "int");
		$id = Jinput::post("id", null, "int");
		$divisi = Jinput::post("objdiv", 0, "int");
		$jabatan = Jinput::post("objlevel", 0, "int");
		$posisi = Jinput::post("objpos", null, "words");
		$bergabung = Jinput::post("objdos", null, "string");
		$berakhir = Jinput::post("objdoe", null, "string");
		$ketkerja = Jinput::post("objnotejob", null, "text");
		$berakhir_sblm = Jinput::post("objdoe_prev", null, "string");
		
		if($berakhir){
			$berakhir = strtotime($berakhir);
		}
		if($berakhir_sblm){
			$berakhir_sblm = strtotime($berakhir_sblm);
		}
		
		if($id){ // EDIT
			$data = new stdClass();
			$data->idpegawai = $id;
			$data->divisi = $divisi;
			$data->jabatan = $jabatan;
			$data->posisi = $posisi;
			$data->bergabung = strtotime($bergabung);
			$data->berakhir = $berakhir;
			$data->keterangan = $ketkerja;
			
			$result = $db->updateObject($table, $data, "id");
			if($result){
				App::message("Successfully updated ".$item);
				return true;
			}else{
				App::message("Failed to update ".$item, "error");
				return false;
			}
		}
		else{ // INSERT NEW
			$data = new stdClass();
			$data->idpegawai = $emp_id;
			$data->divisi = $divisi;
			$data->jabatan = $jabatan;
			$data->posisi = $posisi;
			$data->bergabung = strtotime($bergabung);
			$data->berakhir = $berakhir;
			$data->keterangan = $ketkerja;
			
			if($berakhir_sblm){
				// get last id of previous career
				$db->setQuery("select id from ".$table." where idpegawai=".$emp_id." order by id desc limit 1");
				$prev_id = $db->loadObject()->id;
				// update "date of end"
				$db->setQuery("update ".$table." set berakhir=".$berakhir_sblm." where id=".$prev_id);
			}
			
			$result = $db->insertObject($table, $data, "id");
			if($result){
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