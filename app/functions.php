<?php

use app\libs\AuthenticationManager;
use tiny\libs\App;

function startsWith ($string, $startString) 
{ 
    $len = strlen($startString); 
    return (substr($string, 0, $len) === $startString); 
}

function stringContains($haystack, $needle): bool 
{
    return strpos($haystack, $needle);
}

function countOccurrence($haystack, $needle): int 
{
    return substr_count($haystack, $needle);
}

function formatText($text)
{
    return preg_replace("\n|\n\r|\r", "<br><br>", $text);
}

function getUserId(){
    return AuthenticationManager::user('id');
}

function isLoggedIn(){
    return AuthenticationManager::isLoggedIn();
}

function getProfilePicture(){
    return AuthenticationManager::user('profile_picture');
}

function getProfileSource($user){
    if($user->profile_picture == "default.jpg"){
        return "/public/imgs/default.jpg";
    }else{
        return "/public/uploads/user_{$user->id}/{$user->profile_picture }";
    }
}

function sendEmail($toEmail, $subject, $body){
    $toName = "";
    $fromEmail = "no-reply@app.com";
    $fromName = "app.com";
    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=utf-8\r\n";
    $headers .= "To: $toName <$toEmail>\r\n";
    $headers .= "From: $fromName <$fromEmail>\r\n";
    $message = "<html xmlns=\"http://www.w3.org/1999/xhtml\" lang=\"en\" xml:lang=\"en\">\r\n
    <head>\r\n
        <title>$subject</title>\r\n
    </head>\r\n
    <body>\r\n
        <p></p>\r\n
        $body\r\n
    </body>\r\n
    </html>";
    return mail($toEmail, $subject, $message, $headers);
}

function slugIt(string $string){
    return strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $string))) ."-". rand(0, 10);
}

function getAccept($type){
    switch ($type) {
        case 'IMAGE':
            return 'image/*';
            break;
        case 'VIDEO':
            return 'video/*';
            break;
        case 'AUDIO':
            return '.mp3,audio/mp3'; //.mp3,audio/*
            break;
        default:
            return '';
            break;
    }
}

function friendlyTime(string $time): string
{
    if(!$time){
        return $time;
    }
    $timeArray = explode(":", $time);
    $hour = (int) $timeArray[0];
    $amPM = "AM";
    
    $timeArray[0] = ($hour > 12)? $hour - 12 : $hour;
    $amPM = ($hour > 12)? "pm" : "am";

    return implode(":", $timeArray) . " " . $amPM;
}

function getFileExtension(string $filename): string
{
    $nameArray = explode(".", $filename);
    $ext = end($nameArray);
    return $ext;
}

function getViewPath($path = null): string {
    if($path){
        return App::VIEW_PATH . '/' . trim($path, '(/|.php)') . '.php';
    }else{
        return App::VIEW_PATH;
    }
}

function pagination($link, $pageNumber, $totalRows, $resultsPerPage = 20) {
    $centerLeft = $nextBtn = $previousBnt = $centerRight = $center = $open = $close = '';
    $last = ceil($totalRows / $resultsPerPage);
    $pageNumber = $pageNumber == 0? 1 : $pageNumber;
    
    if($last < 1){
        $last = 1;
    }
    
    if($last != 1 ){
        $open = '<nav aria-label="Page navigation"><ul class="pagination justify-content-center mb-0">
            <li class="page-item"><a class="page-link" href="'. $link . "1" .'">FIRST</a></li>';
        $close = '<li class="page-item"><a class="page-link" href="'. $link . $last .'">LAST</a></li></ul></nav>';
        if($pageNumber > 1){
        
            for($i = $pageNumber - 2; $i < $pageNumber; $i++){
                if($i > 0){
                    $centerLeft	.= '<li class="page-item"><a class="page-link" href="'. $link . $i.'">'.$i.'</a></li>';
                }
            }
        }else{
        $center = '<li class="page-item active"><a class="page-link" href="#" onclick="return false;">'.$pageNumber .'</a></li>';
        }
        $center = '<li class="page-item active"><a class="page-link" href="#" onclick="return false;">'.$pageNumber .'</a></li>';
        $nextCount = $pageNumber + 2 > $last ? $last : $pageNumber + 2;
        for($i = $pageNumber + 1; $i <= $nextCount; $i++){
            $centerRight .= '<li class="page-item"><a class="page-link" href="'.$link.$i.'">'.$i.'</a></li>';
            if($i > $pageNumber +4){
                break;
            }
        }
    } 
    echo  $open . $previousBnt . $centerLeft . $center . $centerRight . $nextBtn . $close; 
}

function formatDateTime($dateString){
    return date("g:i:s a dS M Y", strtotime($dateString));
}

function formatDate($dateString){
    return date("jS M Y", strtotime($dateString));
}

function formatTime($dateString){
    return date("g:i:s a", strtotime($dateString));
}

function arrayToQueryParams(array $array): string
{
    $queryString = "?";
    $count = 0;
    foreach($array as $key => $value){
        $queryString .= $key . "=" . $value;
        $queryString .= ($count++ < count($array) - 1) ? "&" : "";
    }

    return $queryString;
}

function queryParam($name)
{
    return $_GET[$name] ?? null;
}