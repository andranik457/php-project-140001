<?php
{
    # require config file
    require_once(dirname(__FILE__) . '/../include/config.inc.php');

    $currentDate = date('Y-m-d', time());

    if (!getExistRatesByDate($currentDate)) {
        $dailyCBRate = CHelperManager::getCentralBankExchangeRate();

        saveNewRate($currentDate, $dailyCBRate);
    }
    else {
        echo "Rate info for this day already exists! \n";
    }


}

/**
 * @param $currentDate
 * @return bool
 */
function getExistRatesByDate($currentDate) {
    $festa = new CFesta();

    $filter = ['date' => $currentDate];

    $count = $festa->count(ExchangeRate, $filter);

    if ($count > 0) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * @param $currentDate
 * @param $dailyCBRate
 */
function saveNewRate($currentDate, $dailyCBRate) {
    $dailyRateInfo = [
        'date'      => $currentDate,
        'festaRate' => [
            "AMD" => 1
        ],
        'cbRate'    => [
            "AMD" => 1
        ]
    ];
    foreach (json_decode($dailyCBRate, true) as $country => $rate) {
        $dailyRateInfo['festaRate'][$country] = round((floatval($rate) + FestaRate), 2);
        $dailyRateInfo['cbRate'][$country] = round(floatval($rate), 2);
    }

    if (isset($dailyRateInfo['festaRate']['USD'])) {
        $festa = new CFesta();
        $festa->insertOne(ExchangeRate, $dailyRateInfo);
    }

}

