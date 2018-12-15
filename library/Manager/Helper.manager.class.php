<?php

/**
 * Class CHelperManager
 */
class CHelperManager {

    /**
     * @return mixed
     */
    public static function getCentralBankExchangeRate() {
        $url = 'http://cb.am/latest.json.php';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $result = curl_exec($ch);

        return $result;
    }

}