<?php
{
    /**
     *  get flights info from file
     */

    die('Check content before run');

    # require config file
    require_once(dirname(__FILE__) . '/../include/config.inc.php');

    # get bash arguments
    isset($argv[1]) ? $fileName = $argv[1] : die('Please check file name and try again!');

    # get info from file
    $fileInfo = fopen(DIR_TMP . $fileName .".csv", "r");

    if ($fileInfo) {
        $documents = [];
        while (($row = fgets($fileInfo)) !== false) {
            $rowArray = explode(',', $row);

            $documents[] = [
                'from'  => str_replace('"', "", $rowArray[0]) . ',' . str_replace('"', "", $rowArray[1]),
                'to'    => str_replace('"', "", $rowArray[2]) . ',' . str_replace('"', "", $rowArray[3]),
                'duration' => str_replace('"', "", $rowArray[18]),
                'dateInfo' => [
                    "startDate"     => str_replace('"', "", $rowArray[10]),
                    "startTime"     => str_replace('"', "", $rowArray[6]),
                    "endDate"       => str_replace('"', "", $rowArray[17]),
                    "endTime"       => str_replace('"', "", $rowArray[13]),
                    "startDateTime" => str_replace('"', "", $rowArray[10]) . ' ' .str_replace('"', "", $rowArray[6]),
                    "endDateTime"   => str_replace('"', "", $rowArray[17]) . ' ' .str_replace('"', "", $rowArray[13]),
                ],
                'flightNumber'      => str_replace('"', "", $rowArray[21]),
                'airline'           => str_replace('"', "", $rowArray[22]),
                'airlineIataIcao'   => str_replace('"', "", $rowArray[20]),
                'numberOfSeats'     => (int)str_replace('"', "", $rowArray[19]),
                'currency'          => trim(preg_replace('/\s\s+/', ' ', (str_replace('"', "", $rowArray[23])))),
                'status'            => 'upcoming',
                "updatedAt"         => time(),
	            "createdAt"         => time()
            ];
        }

        $festa = new CFesta();
        $result = $festa->save(Flights . '-10001', $documents);
        var_dump($result);

        fclose($fileInfo);
    }
}
