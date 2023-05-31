<?php

namespace Tiny\Libs;

use Exception;
/*
* 
* HTML EMAIL SENDER
* Version 1.3.0
* Email::builder($from, $to, $subject, $body)->send()
* Email::builder()->from('string')->to('string')->subject('string')->body('string')->send()
* Email::builder()->from('string')->to('string')->subject('string')->body('string')->attachment('filePath' | ['filePath',...])->send()
 */

class Email {
	private static $_instance = null;
	protected $to = '';
	protected $cc = '';
	protected $bcc = '';
	protected $from = '';
	protected $headers = '';
	protected $subject = '';
	protected $errors = [];
    protected $body = '';
    protected bool $hasAttachment = false;
    protected string $mimeBoundary = "";
    protected string $emailAttachment = "";
	
    public function __construct(){}
    
	public static function builder(string $from = null, string $to = null, string $subject = null, string $body = null): self {
		if(!isset(self::$_instance)){
			self::$_instance = new self();
        }
        self::$_instance->from = $from;
        self::$_instance->to = $to;
        self::$_instance->subject = $subject;
        self::$_instance->body = $body;
		return self::$_instance;
    }
    
	protected function setHeader(){
        $headers = "";
        if($this->hasAttachment){
            // Header for sender info 
            // $headers = "From: $fromName"." <".$from.">"; 
            $headers = "From: " . $this->from . "\r\n";
            $headers .= isset($this->cc) ? $this->cc : "";
            $headers .= isset($this->bcc) ? $this->bcc : "";
            
            // Boundary  
            $mimeBoundary = $this->getBoundary();
            
            // Headers for attachment  
            $headers .= "MIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$mimeBoundary}\"\r\n"; 
        }else{
            $headers = "From: " . $this->from . "\r\n";
            $headers .= isset($this->cc) ? $this->cc : "";
            $headers .= isset($this->bcc) ? $this->bcc : "";
            $headers .= 'MIME-Version: 1.0' . "\r\n";
            $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";
        }
		$this->headers = $headers;	
    }
    
	public function from($from){
		$this->from = $from;
		return $this;
    }
    
	public function to($to){
		$this->to = is_array($to) ? implode(',', $to) : $to;
		return $this;
    }

    public function bcc($bcc){
        if(is_string($bcc)){
            $bcc = trim($bcc);
            $this->bcc = "Bcc: {$bcc}\r\n";
        }else if(is_array($bcc)){
            $bcc = implode(",", $bcc);
            $this->bcc = "Bcc: {$bcc}\r\n";
        }
        return $this;
    }

    public function cc($cc){
        if(is_string($cc)){
            $cc = trim($cc);
            $this->cc = "Cc: {$cc}\r\n"; 
        }else if(is_array($cc)){
            $cc = implode(",", $cc);
            $this->cc = "Cc: {$cc}\r\n"; 
        }
        return $this;
    }
    
	public function subject($subject){
		$this->subject = $subject;
		return $this;
    }
    
	public function body($body){
		if(!$body){
			$this->errors[] = "Email Body not found";
			return $this;
		}
		$this->body = $body;
		return $this;
    }

    private function getBoundary(){
        if(empty($this->boundary)){
            // Boundary  
            $semiRand = md5(time());  
            $this->mimeBoundary = "==Multipart_Boundary_x{$semiRand}x";
        }
        return $this->mimeBoundary;
    }

    public function attachment($fileNames){
        $this->hasAttachment = true;

        $filePaths = is_string($fileNames)? [$fileNames]: $fileNames;
        
        $mimeBoundary = $this->getBoundary(); 
        foreach($filePaths as $key => $filePath){
            // Preparing attachment 
            if(!empty($filePath) > 0){ 
                if(is_file($filePath)){ 
                    // $this->attachment .= "--{$mimeBoundary}\r\n"; 
                    $fp = @fopen($filePath,"rb"); 
                    $data = @fread($fp,filesize($filePath)); 
            
                    @fclose($fp); 
                    $data = chunk_split(base64_encode($data)); 
                    $this->emailAttachment .= "Content-Type: application/octet-stream; name=\"".basename($filePath)."\"\r\n" .  
                                            "Content-Description: ".basename($filePath)."\r\n" . 
                                            "Content-Disposition: attachment;\r\n" . " filename=\"".basename($filePath)."\"; size=".filesize($filePath).";\r\n" .  
                                            "Content-Transfer-Encoding: base64\r\n\r\n" . $data . "\r\n\r\n"; 
                }else{
                    throw new Exception("$filePath is not a file");
                }
            } 
            $this->emailAttachment .= "--{$mimeBoundary}--\r\n"; 
        }

        return $this;
    }

    private function getBody(){
        if($this->hasAttachment){
            $mimeBoundary = $this->getBoundary();
        
            // Multipart boundary  
            $message = "--{$mimeBoundary}\r\n" .
                        "Content-Type: text/html; charset=\"UTF-8\"\r\n" .
                        "Content-Transfer-Encoding: 7bit\r\n\r\n" .
                        $this->body . "\r\n\r\n" . 
                        "--{$mimeBoundary}\r\n";  
            return $message . $this->emailAttachment;
        }
        return $this->body;

    }
    
	public function hasErrors(){
		return count($this->errors) > 0;
    }
    
    private function canSendEmail(){
        return $this->from == '' || $this->to == '' || $this->subject == '' || $this->body == '' || $this->hasErrors();
    }

    private function reset(){
        self::$_instance = new self();
    }
    
	public function send(){
		if($this->canSendEmail()){
			return false;
        }

        try{
            $this->setHeader();
            $body = $this->getBody();
            @mail($this->to, $this->subject, $body, $this->headers);
            $this->reset();
        }catch(Exception $e){
            return false;
        }
        return true;
    }
	
}