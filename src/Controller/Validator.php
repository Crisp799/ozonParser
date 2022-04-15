<?php

namespace App\Controller;

use GuzzleHttp\Client;

class Validator
{
    private $errors=[];
    public function ValidateUrl($url) {
        $client = new Client();
        $matches =[];
        preg_match('/(https:\/\/)?(www.ozon.ru\/)?(category\/)?([\w-]+\/)?(\?page=)?([\d]+)?/', $url, $matches);
        if (!isset($matches[2]) || strlen($matches[2]) === 0) {
            array_push($this->errors, "Missing 'www.ozon.ru/'");
            return $this->errors;
        }
        if (!isset($matches[3]) || strlen($matches[3]) === 0) {
            array_push($this->errors, "Missing 'category/'");
            return $this->errors;
        }
        if (count($matches) === 0 || $response = $client->request('get', $url)->getStatusCode() !== 200) {
            array_push($this->errors, 'Invalid url');
            return $this->errors;
        }
        if (!isset($matches[4]) || strlen($matches[4]) === 0)
            array_push($this -> errors, "Missing your-category-name");
        if(isset($matches[5]) && strlen($matches[5]) > 0) {
            if(!isset($matches[6]) || strlen($matches[6]) === 0)
                array_push($this -> errors, 'Missing your-page-number');
        }
        //var_dump($matches);
        return $this->errors;
    }
}
