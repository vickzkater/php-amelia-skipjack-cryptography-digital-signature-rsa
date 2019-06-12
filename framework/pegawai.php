<?php

class Pegawai {
	
	public $id;
	public $nip;
	public $nama_lengkap;
	public $nama_panggilan;
	public $tempat_lahir;
	public $tgl_lahir;
	public $alamat;
	public $telp;
	public $mobile;
	public $email;
	public $email_kantor;
	public $status;
	public $keterangan;
	public $diubah;
	
	public static $instance = null;
	
	function __construct($id=null)
	{
		$db = App::getDbo();
		
		if($id < 1){
			$this->id = 0;
			$this->nip = 0;
			$this->nama_lengkap = "unknown";
			$this->nama_panggilan = "unknown";
			$this->tempat_lahir = null;
			$this->tgl_lahir = null;
			$this->alamat = null;
			$this->telp = null;
			$this->mobile = null;
			$this->email = null;
			$this->email_kantor = null;
			$this->status = "non-aktif";
			$this->keterangan = null;
			$this->diubah = null;
			self::$instance = $this;
			return false;
		}
		
		$sql = "select * from `pegawai` where id = ".(int)$id;
				
		$db->setQuery($sql);
		$results = $db->loadObjectList();
		if(count($results) > 0){
			foreach ($results as $a)
			{
				$this->id = $a->id;
				$this->nip = $a->nip;
				$this->nama_lengkap = $a->nama_lengkap;
				$this->nama_panggilan = $a->nama_panggilan;
				$this->tempat_lahir = $a->tempat_lahir;
				$this->tgl_lahir = $a->tgl_lahir;
				$this->alamat = $a->alamat;
				$this->telp = $a->telp;
				$this->mobile = $a->mobile;
				$this->email = $a->email;
				$this->email_kantor = $a->email_kantor;
				$this->status = $a->status;
				$this->keterangan = $a->keterangan;
				$this->diubah = $a->diubah;
			}
		}else{
			$this->id = 0;
			$this->nip = 0;
			$this->nama_lengkap = "unknown";
			$this->nama_panggilan = "unknown";
			$this->tempat_lahir = null;
			$this->tgl_lahir = null;
			$this->alamat = null;
			$this->telp = null;
			$this->mobile = null;
			$this->email = null;
			$this->email_kantor = null;
			$this->status = "non-aktif";
			$this->keterangan = null;
			$this->diubah = null;
		}
	}

	public static function getInstance($id = null)
	{
		//just call this from child
		if(!$id)
		{
			if(!self::$instance){
				$user = new Pegawai();
				self::$instance = $user;
				return $user;
			}else{
				return self::$instance;
			}
		}else{
			return new Pegawai($id);
		}
	}
	
	public static function getAll($total = false)
	{
		$db = App::getDBo();
		$db->setQuery("select ok.*, c.nama as namadivisi, d.nama as namajabatan from
		(
			select tes.* from
			(
				select a.*, b.posisi, b.bergabung, b.`divisi`, b.`jabatan` from pegawai a
				left join karier b on a.id = b.idpegawai
				order by b.bergabung desc
			) tes group by tes.id
		) ok
		left join divisi c on c.id = ok.`divisi`
		left join jabatan d on d.id = ok.`jabatan`
		order by ok.nama_lengkap");
		$results = $db->loadObjectList();
		if($results){
			if($total)
				return $db->getNumRows();
			else
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
		$table = "pegawai";
		$item = "employee";
		
		$id = Jinput::post("id", null, "int");
		// personal data
		$nama_lengkap = Jinput::post("objfullname", null, "words");
		$nama_panggilan = Jinput::post("objname", null, "words");
		$tempat_lahir = Jinput::post("objpob", null, "words");
		$tgl_lahir = Jinput::post("objdob", null, "string");
		$phone = Jinput::post("objphone", null, "number");
		$mobile = Jinput::post("objmobile", null, "number");
		$email = Jinput::post("objemail", null, "email");
		$alamat = Jinput::post("objaddr", null, "text");
		// office data
		$nip = Jinput::post("objnip", null, "alnum");
		$email_kantor = Jinput::post("objemail2", null, "email");
		$keterangan = Jinput::post("objnote", null, "text");
		$status = Jinput::post("objstatus", "aktif", "text");
		$modified = null;
		// career data
		$divisi = Jinput::post("objdiv", 0, "int");
		$jabatan = Jinput::post("objlevel", 0, "int");
		$posisi = Jinput::post("objpos", null, "words");
		$bergabung = Jinput::post("objdos", null, "string");
		$berakhir = Jinput::post("objdoe", null, "string");
		$ketkerja = Jinput::post("objnotejob", null, "text");
		
		if($berakhir){
			$berakhir = strtotime($berakhir);
		}
		$item .= " (".$nama_lengkap.")";
		
		if($id){ // EDIT
			$modified = time();
			$obj = new stdClass();
			$obj->id = $id;
			$obj->nip = $nip;
			$obj->nama_lengkap = $nama_lengkap;
			$obj->nama_panggilan = $nama_panggilan;
			$obj->tempat_lahir = $tempat_lahir;
			$obj->tgl_lahir = strtotime($tgl_lahir);
			$obj->alamat = $alamat;
			$obj->telp = $phone;
			$obj->mobile = $mobile;
			$obj->email = $email;
			$obj->email_kantor = $email_kantor;
			$obj->status = $status;
			$obj->keterangan = $keterangan;
			$obj->diubah = $modified;
			
			$result = $db->updateObject($table, $obj, "id");
			if($result){
				// logging
				Logs::saveLog($user->id, "Update employee detail", $id, $table);
				
				App::message("Successfully updated ".$item);
				return true;
			}else{
				App::message("Failed to update ".$item, "error");
				return false;
			}
		}
		else{ // INSERT NEW
			$obj = new stdClass();
			$obj->id = null;
			$obj->nip = $nip;
			$obj->nama_lengkap = $nama_lengkap;
			$obj->nama_panggilan = $nama_panggilan;
			$obj->tempat_lahir = $tempat_lahir;
			$obj->tgl_lahir = strtotime($tgl_lahir);
			$obj->alamat = $alamat;
			$obj->telp = $phone;
			$obj->mobile = $mobile;
			$obj->email = $email;
			$obj->email_kantor = $email_kantor;
			$obj->status = $status;
			$obj->keterangan = $keterangan;
			$db->insertObject($table, $obj, "id", true);
			$result = $db->getResult();
			
			if(!is_array($result)){ // insert first career
				$insert_id = $db->connect->insert_id;
				$data = new stdClass();
				$data->idpegawai = $insert_id;
				$data->divisi = $divisi;
				$data->jabatan = $jabatan;
				$data->posisi = $posisi;
				$data->bergabung = strtotime($bergabung);
				$data->berakhir = $berakhir;
				$data->keterangan = $ketkerja;
				$result = $db->insertObject("karier", $data, "idpegawai");
				
				// logging
				Logs::saveLog($user->id, "Add new employee", $insert_id, $table);
				
				if($result){
					App::message("Successfully added new ".$item);
					return true;
				}else{
					App::message("Successfully added new ".$item.", but failed to save first career", "error");
					return false;
				}
			}
			else{
				$msg = "";
				foreach($result as $res){
					$msg .= "ERROR".$res['errno'].": ".$res['error']."<br>";
				}
				
				App::message($msg."Failed to add new ".$item, "error");
				return false;
			}
		}
	}

	public static function delete($id)
	{
		$db = App::getDbo();
		$table = "pegawai";
		$item = "employee";
		
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
		$deleted = $item." (".$exist->nama_lengkap.")";
		
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
	
	public static function getTotal($active = true)
	{
		$db = App::getDbo();
		$sql = "select count(*) as total from pegawai where status";
		if($active)
			$sql .= " = 'aktif'";
		else
			$sql .= " != 'aktif'";
		$db->setQuery($sql);
		
		return $db->loadObject()->total;
	}
}

?>