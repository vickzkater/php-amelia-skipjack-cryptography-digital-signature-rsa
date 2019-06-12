<?php

class Zipper extends ZipArchive
{
	function __construct()
	{
		return new parent();
	}
	
	public static function zip()
	{
		return parent::CREATE;
	}
	
	public static function unzip($src_file, $dest_dir=false, $create_zip_name_dir=true, $overwrite=true) 
	{
	  if ($zip = zip_open($src_file)) 
	  {
		if ($zip) 
		{
		  $splitter = ($create_zip_name_dir === true) ? "." : "/";
		  if ($dest_dir === false) $dest_dir = substr($src_file, 0, strrpos($src_file, $splitter))."/";
		  
		  // Create the directories to the destination dir if they don't already exist
		  self::create_dirs($dest_dir);

		  // Check is compressed file is true (same as $src_file)
		  if(is_resource($zip)){
			  // For every file in the zip-packet
			  while ($zip_entry = zip_read($zip)) 
			  {
				// Now we're going to create the directories in the destination directories
				
				// If the file is not in the root dir
				$pos_last_slash = strrpos(zip_entry_name($zip_entry), "/");
				if ($pos_last_slash !== false)
				{
				  // Create the directory where the zip-entry should be saved (with a "/" at the end)
				  self::create_dirs($dest_dir.substr(zip_entry_name($zip_entry), 0, $pos_last_slash+1));
				}

				// Open the entry
				if (zip_entry_open($zip,$zip_entry,"r")) 
				{
				  
				  // The name of the file to save on the disk
				  $file_name = $dest_dir.zip_entry_name($zip_entry);
				  
				  // Check if the files should be overwritten or not
				  if ($overwrite === true || $overwrite === false && !is_file($file_name))
				  {
					// Get the content of the zip entry
					$fstream = zip_entry_read($zip_entry, zip_entry_filesize($zip_entry));

					file_put_contents($file_name, $fstream );
					// Set the rights
					chmod($file_name, 0777);
					// echo "save: ".$file_name."<br />";
				  }
				  
				  // Close the entry
				  zip_entry_close($zip_entry);
				}       
			  }
		  }else{
			return false;
		  }
		  // Close the zip-file
		  zip_close($zip);
		}
	  } 
	  else
	  {
		return false;
	  }
	  
	  return true;
	}
	
	private static function create_dirs($path)
	{
	  if (!is_dir($path))
	  {
		$directory_path = "";
		$directories = explode("/",$path);
		array_pop($directories);
		
		foreach($directories as $directory)
		{
		  $directory_path .= $directory."/";
		  if (!is_dir($directory_path))
		  {
			mkdir($directory_path);
			chmod($directory_path, 0777);
		  }
		}
	  }
	}
}
?>