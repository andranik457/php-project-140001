<?php
{
    /**
     *  get classes info from file by flightId's
     */

    die('Check content before run');

    # require config file
    require_once(dirname(__FILE__) . '/../include/config.inc.php');

    # get bash arguments
    isset($argv[1]) ? $className = $argv[1] : die('Please check file name and try again!');

    $flightIds = [
        "5c3f1a38321c9665aa16bfb2",
        "5c3f1a38321c9665aa16bfb3",
        "5c3f1a38321c9665aa16bfb4",
        "5c3f1a38321c9665aa16bfb5",
        "5c3f1a38321c9665aa16bfb6",
        "5c3f1a38321c9665aa16bfb7",
    ];

    $festa = new CFesta();

    foreach ($flightIds as $flightId) {
        # get info from file
        $fileInfo = fopen(DIR_TMP . $className .".csv", "r");

        if ($fileInfo) {
            $documents = [];
            while (($row = fgets($fileInfo)) !== false) {
                $rowArray = explode(',', $row);

                $documents[] = [
                    'flightId'                  => $flightId,
                    'onlyForAdmin'              => (str_replace('"', "", $rowArray[0]) === "ONLY ADMIN") ? true : false,
                    'className'                 => str_replace('"', "", $rowArray[1]),
                    'classType'                 => str_replace('"', "", $rowArray[4]),
                    'travelType'                => str_replace('"', "", $rowArray[5]),
                    'currency'                  => trim(preg_replace('/\s\s+/', ' ', (str_replace('"', "", $rowArray[18])))),
                    "numberOfSeats"             => (int)str_replace('"', "", $rowArray[2]),
	                "availableSeats"            => (int)str_replace('"', "", $rowArray[3]),
	                "fareRules"                 => str_replace('"', "", $rowArray[6]),
	                "fareAdult"                 => floatval(str_replace('"', "", $rowArray[7])),
                    "fareChd"                   => floatval(str_replace('"', "", $rowArray[8])),
                    "fareInf"                   => floatval(str_replace('"', "", $rowArray[9])),
                    "taxAdult"                  => floatval(str_replace('"', "", $rowArray[10])),
                    "taxChd"                    => floatval(str_replace('"', "", $rowArray[11])),
                    "cat"                       => floatval(str_replace('"', "", $rowArray[12])),
                    "surchargeMultiDestination" => floatval(str_replace('"', "", $rowArray[13])),
                    "surchargeLongRange"        => floatval(str_replace('"', "", $rowArray[14])),
                    "surchargeShortRange"       => floatval(str_replace('"', "", $rowArray[15])),
                    "commChd"                   => floatval(str_replace('"', "", $rowArray[17])),
                    "commAdult"                 => floatval(str_replace('"', "", $rowArray[16])),
                    "updatedAt"                 => time(),
                    "createdAt"                 => time()
                ];
            }

            if (count($documents) > 0) {
                $result = $festa->save(Classes . '-10001', $documents);
                var_dump($result);
            }

            fclose($fileInfo);
        }
    }
}
