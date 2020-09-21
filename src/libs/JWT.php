<?php

declare(strict_types=1);

namespace tiny\libs;

class JWT
{
    public static function sign(object $payload, string $secret, $algo = 'sha256'): string
    {
        // Create token header as a JSON string
        $header = json_encode(['typ' => 'JWT', 'alg' => strtoupper($algo)]);

        // Create token payload as a JSON string
        $payload = json_encode([
            "data" => $payload, 
            "iat" => date("Y-m-d H:i:s"), 
            "exp" => date("Y-m-d H:i:s", strtotime("+5 days")),
            "nfb" => SITE_NAME ?? "APP"
            ]);
            
        $base64UrlHeader = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($header));

        // Encode Payload to Base64Url String
        $base64UrlPayload = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($payload));

        // Create Signature Hash
        $signature = hash_hmac($algo, $base64UrlHeader . "." . $base64UrlPayload, $secret, true);

        // Encode Signature to Base64Url String
        $base64UrlSignature = str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($signature));

        // Return JWT
        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    public static function decode(string $token, $full = false): object
    {
        $tokenArray = explode(".", $token);
        if($full){
            return json_decode(base64_decode(str_replace(['-', '_', ''], ['+', '/', '='], $tokenArray[1])));
        }else {
            $object = json_decode(base64_decode(str_replace(['-', '_', ''], ['+', '/', '='], $tokenArray[1])));
            return $object->data;
        }
    }

    public static function verify(string $token, string $secret, $algo = 'sha256'): bool
    {
        //verify signature
        $tokenArray = explode(".", $token);
        $hashed_expected = base64_decode(str_replace(['-', '_', ''], ['+', '/', '='], end($tokenArray)));
        if(count($tokenArray) != 3) 
            return false;

        $hashed_value =  hash_hmac($algo, $tokenArray[0] . "." . $tokenArray[1], $secret, true);
        if(!hash_equals($hashed_expected, $hashed_value) ) {
           return false;
        }
        //verify that token has not expired
        if(self::hasTokenExpired($token)){
            return false;
        }

        return true;
    }

    public static function hasTokenExpired($token): bool
    {
        $fullToken = self::decode($token, true);
        $expirationDate = $fullToken->exp;
        
        if(strtotime($expirationDate) < time()){
            return true;
        }
        return false;
    }
}
