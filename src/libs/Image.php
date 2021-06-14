<?php

namespace tiny\libs;

use Exception;

/**
* Image::set($destination, 'file')->upload()
* ->resize($w, $h, $dest)
* ->watermark($watermarkPath)
* ->crop($w, $h, $dest);
*/
class Image extends File {
	private static $_instance;
	private	$_resizedPath,
	$_croppedPath,
	$_gps;

	public static function set(string $destination, $fieldname): self {
		if(!isset(self::$_instance)){
			self::$_instance = new self();
		}
		if(is_array($fieldname)){
			self::$_instance->setFileArray($destination, $fieldname);
		}else{
			self::$_instance->setFile($destination, $fieldname);
		}
		self::$_instance->setGPS();
		return self::$_instance;
	}

	private function makeDestination($destination){
		if(!file_exists($destination)){
			mkdir($destination);
		}
	}

	private function checkOptions($option) {
		if(isset($this->_options[$option]))
			return $this->_options[$option];
		else
			return false;
	}

	public function resize($w, $h, $destination=null, int $quality = 85): self{
		$this->makeDestination($destination);
		$target = $this->_fullPath;
		$destinationFilePath = '';
		if($destination){
			$destinationFilePath = rtrim($destination, '/') . '/';
			$destinationFilePath = $destinationFilePath . $this->_name;
		}else{
			$destinationFilePath = $this->_fullPath;
		}
		// $destination = $destination? $destination . $this->_name : $this->_fullPath;
		$ext = $this->_ext;
		list($w_orig, $h_orig) = getimagesize($target);
		$scale_ratio = $w_orig/$h_orig;
		if (($w/$h) < $scale_ratio){
			$w = $h * $scale_ratio;
		}else{
			$h = $w / $scale_ratio;
		}
		$img = '';
		if ($ext == "gif"){
			$img = imagecreatefromgif($target);
		}else if($ext == "png"){
			$img = imagecreatefrompng($target);
		}else{
			$img = imagecreatefromjpeg($target);
		}
		$tci = imagecreatetruecolor($w, $h);
		imagecopyresampled($tci, $img, 0, 0, 0, 0, $w, $h, $w_orig, $h_orig);
		if ($ext == "gif"){ 
			imagegif($tci, $destinationFilePath);
		} else if($ext =="png"){ 
			imagepng($tci, $destinationFilePath);
		} else { 
			imagejpeg($tci, $destinationFilePath, $quality);
		}
		$this->_resizedPath = $this->_lastPath = $destinationFilePath;
		return $this;
	}

	public function crop($w, $h, $destination = null, int $quality = 85): self {
		$this->makeDestination($destination);
		$target = $this->_lastPath;
		$destinationFilePath = '';
		if($destination){
			$destinationFilePath = rtrim($destination, '/') . '/';
			$destinationFilePath = $destinationFilePath . $this->_name;
		}else{
			$destinationFilePath = $this->_fullPath;
		}
		// $destination .= $destination? $this->_name : $this->_path;
		$ext = $this->_ext;
		list($w_orig, $h_orig) = getimagesize($target);
		$src_x = ($w_orig/2)-($w/2);
		$src_y = ($h_orig/2)-($h/2);
		$img = null;
		if ($ext == "gif"){
			$img = imagecreatefromgif($target);
		}else if($ext == "png"){
			$img = imagecreatefrompng($target);
		}else{
			$img = imagecreatefromjpeg($target);
		}
		$tci = imagecreatetruecolor($w, $h);
		imagecopyresampled($tci, $img, 0, 0, $src_x, $src_y, $w, $h, $w, $h);
		if ($ext == "gif"){ 
			imagegif($tci, $destinationFilePath);
		} else if($ext =="png"){ 
			imagepng($tci, $destinationFilePath);
		} else { 
			imagejpeg($tci, $destinationFilePath, $quality);
		}
		$this->_croppedPath = $this->_lastPath = $destinationFilePath;
		if($this->checkOptions('watermarkAll'))
			$this->watermark($destination);
		return $this;
	}

	public function watermark($waterMarkFilePath): self {
		$target = $this->_lastPath;
		$ext = $this->_ext;
		$destinationFilePath = $target;

		if(!file_exists($waterMarkFilePath)){
			throw new Exception("Watermark File not found");
		}

		$watermark = imagecreatefrompng($waterMarkFilePath); 
		imagealphablending($watermark, false); 
		imagesavealpha($watermark, true); 
		$img = null;
		//$img = imagecreatefromjpeg($target);
		if ($ext == "gif"){
			$img = imagecreatefromgif($target);
		}else if($ext == "png"){
			$img = imagecreatefrompng($target);
		}else{
			$img = imagecreatefromjpeg($target);
		}
		$img_w = imagesx($img); 
		$img_h = imagesy($img); 
		$wtrmrk_w = imagesx($watermark); 
		$wtrmrk_h = imagesy($watermark);
		// $dst_x = $dst_y = 0;
		$dst_x = ($img_w / 2) - ($wtrmrk_w / 2); // For centering the watermark on any image
		$dst_y = ($img_h / 2) - ($wtrmrk_h / 2); // For centering the watermark on any image
		
		imagecopy($img, $watermark, $dst_x, $dst_y, 0, 0, $wtrmrk_w, $wtrmrk_h); 
		//imagejpeg($img, $destinationFilePath, 100);
		if ($ext == "gif"){ 
			imagegif($img, $destinationFilePath);
		} else if($ext =="png"){ 
			imagepng($img, $destinationFilePath);
		} else { 
			imagejpeg($img, $destinationFilePath, 100);
		}
		imagedestroy($img); 
		imagedestroy($watermark); 

		return $this;
	}

	public function getGPS() {
		return $this->_gps;
	}

	private function setGPS(){
		@$exif = exif_read_data($this->_path.$this->_name);
		$result['latitude'] = false;
		$result['longitude'] = false;
	
		//get the Hemisphere multiplier
		$LatM = 1; $LongM = 1;
		if(isset($exif["GPSLatitudeRef"]) && $exif["GPSLatitudeRef"] == 'S'){
			$LatM = -1;
		}
		if(isset($exif["GPSLongitudeRef"]) && $exif["GPSLongitudeRef"] == 'W'){
			$LongM = -1;
		}
	
		//get the GPS data
		if(!isset($exif["GPSLatitude"][0]))
			return $result;

		$gps['LatDegree'] = $exif["GPSLatitude"][0];
		$gps['LatMinute'] = $exif["GPSLatitude"][1];
		$gps['LatSeconds'] = $exif["GPSLatitude"][2];
		$gps['LongDegree'] = $exif["GPSLongitude"][0];
		$gps['LongMinute'] = $exif["GPSLongitude"][1];
		$gps['LongSeconds'] = $exif["GPSLongitude"][2];
	
		//convert strings to numbers
		foreach($gps as $key => $value){
			$pos = strpos($value, '/');
			if($pos !== false){
				$temp = explode('/',$value);
				$gps[$key] = $temp[0] / $temp[1];
			}
		}
	
		//calculate the decimal degree
		$result['latitude'] = $LatM * ($gps['LatDegree'] + ($gps['LatMinute'] / 60) + ($gps['LatgSeconds'] / 3600));
		$result['longitude'] = $LongM * ($gps['LongDegree'] + ($gps['LongMinute'] / 60) + ($gps['LongSeconds'] / 3600));
	
		// return $result;
		$this->_gps = $result;
		return $this;
	}
}