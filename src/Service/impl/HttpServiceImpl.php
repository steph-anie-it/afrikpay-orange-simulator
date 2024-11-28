<?php

namespace App\Service\impl;

use App\Dto\PayMoneyDataResultDto;
use App\Service\HttpService;

class HttpServiceImpl implements HttpService
{
    public function callBack(PayMoneyDataResultDto $payMoneyDataResultDto){

        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $payMoneyDataResultDto->notifUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json'
            ],
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($payMoneyDataResultDto)
        ));

        $response = curl_exec($curl);
        if (curl_errno($curl)) {
            $error_msg = curl_error($curl);
        }
        curl_close($curl);
    }

}