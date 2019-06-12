<?php

class Config {
	// default sitename
	public $sitename = 'AMELIA';
	// default site fullname
	public $sitefullname = 'Aplikasi Manajemen Data Personalia';
	// site protocol (http/https)
	public $protocol = 'http';
	// copyright
	public $copyright = 'Vicky Budiman (32140004)';
	// site version
	public $version = '1.0.0';
	// logo
	public $logo = '';
	// logo mini
	public $logomini = 'AML';
	// logo image
	public $logoimg = '';
	// favicon
	public $favicon = 'favicon.ico';
	// default error message (E_ALL or 0)
	public $reporting = E_ALL;
	// database server
	public $dbserver = '[DATABASE_HOST]';
	// database user
	public $dbuser = '[DATABASE_USER]';
	// database pass
	public $dbpass = '[DATABASE_PASSWORD]';
	// database name
	public $dbname = '[DATABASE_NAME]';
	// database port (default:null)
    public $dbport = '[DATABASE_PORT]';
	// Default userlevel for new user (check database)
	public $userlevel = 1;
	// Feature forgot password
	public $forgotpass = false;
	// Color Skin Template
	public $skin = "blue";
	// Path for save uploaded files
	public $path = "uploads/files";
	// Allowed extension files to be uploaded
	public $allowed = array("png", "jpeg", "jpg", "gif", "bmp", "pdf", "doc", "docx", "txt", "csv", "xls", "xlsx");
	// Extension for encrypted file
	public $ext = ".vic";
	// Maximum Size for upload file (MB)
	public $maxsize = 5;
}

?>