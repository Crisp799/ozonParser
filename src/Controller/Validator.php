<?php

namespace App\Controller;

use GuzzleHttp\Client;

class Validator
{
    private $errors = [];

    public function ValidateUrl(string $url): array
    {
        $matched = [];
        preg_match('/(https:\/\/)?(www.ozon.ru\/)?(category\/)?([\w-]+\/)?(\?page=)?([\d]+)?/', $url, $matched);
        $client = new Client();
        $url = $matched[0];
        if (!isset($matched[2]) || strlen($matched[2]) === 0) {
            array_push($this->errors, "Missing 'www.ozon.ru/'");
            return $this->errors;
        }
        if (!isset($matched[3]) || strlen($matched[3]) === 0) {
            array_push($this->errors, "Missing 'category/'");
            return $this->errors;
        }
        if (count($matched) === 0 || $response = $client->request('get', $url)->getStatusCode() !== 200) {
            array_push($this->errors, 'Invalid url');
            return $this->errors;
        }
        if (!isset($matched[4]) || strlen($matched[4]) === 0)
            array_push($this->errors, "Missing your-category-name");
        if (isset($matched[5]) && strlen($matched[5]) > 0) {
            if (!isset($matched[6]) || strlen($matched[6]) === 0)
                array_push($this->errors, 'Missing your-page-number');
            if (isset($matched[6]) && $matched[6]<1)
                array_push($this->errors, 'Invaled your-page-number');
        }
        //var_dump($matches);
        return $this->errors;
    }
}
