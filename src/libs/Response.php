<?php


namespace Tiny\Libs;

use Tiny\Exceptions\FileNotFoundException;
use Tiny\Exceptions\ViewNotFoundException;
use Tiny\Interfaces\IResponse;

class Response implements IResponse {
    private $extra = [];

    public function setExtra(array $extra): void
    {
        $this->extra = $extra;
    }

    public function status(int $code){
        HttpHeader::setStatusCode($code);
        return $this;
    }

    public function view($_path, array $extra=[]){
        foreach($extra as $key => $value){
            $$key = $value;
        }
        $extra = array_merge($extra, $this->extra);
        HttpHeader::setContentType("html");
        $_path = preg_replace("(\.php)", "", $_path);
        if(is_file(App::VIEW_PATH . $_path . ".php")){
            require_once App::VIEW_PATH . $_path . ".php";
        }else{
            throw new ViewNotFoundException("View not found.");
        }
    }

    public function file($path){
        $stringArray = explode(".", $path);
        $ext = end($stringArray);
        array_pop($stringArray);
        $fileName = implode($stringArray);
        $attachment_location = $_SERVER["DOCUMENT_ROOT"] . $path;
        if (file_exists($attachment_location)) {
            
            header($_SERVER["SERVER_PROTOCOL"] . " 200 OK");
            header("Cache-Control: public"); // needed for internet explorer
            HttpHeader::setContentType($ext);
            // header("Content-Type: application/zip");
            header("Content-Transfer-Encoding: Binary");
            header("Content-Length:". filesize($attachment_location));
            header("Content-Disposition: attachment; filename=$fileName");
            readfile($attachment_location);
            die();        
        } else {
            throw new FileNotFoundException("File Not Found");
        }
    }

    public function json($body, $statusCode=200){
        HttpHeader::setContentType('json');
        HttpHeader::setStatusCode($statusCode);
        echo json_encode($body); //, JSON_PRETTY_PRINT
        exit();
    }

    public function write(string $text): void
    {
        echo $text;
        exit();
    }
    
    public function text(string $text){
        header('Content-type: text/plain; charset=utf-8');
        echo $text;
        exit();
    }

    public function setCookies(string $name, string $value): void
    {
        Cookie::set($name, $value);
    }

    public static function arrayToQueryParams(array $array): string
    {
        $queryString = "?";
        $count = 0;
        foreach($array as $key => $value){
            $queryString .= $key . "=" . $value;
            $queryString .= ($count++ < count($array) - 1) ? "&" : "";
        }
    
        return ("?" == $queryString) ? "" : $queryString;
    }

    public function redirect(string $path, $queryParams = [])
    {
        self::goTo($path . self::arrayToQueryParams($queryParams));
        exit();
    }

    public static function goTo(string $path): void
    {
        header('Location: ' . $path);
		exit();
    }

    public function setHeader(string $key, string $value): void
    {
        HttpHeader::setHeader($key, $value);
    }
}