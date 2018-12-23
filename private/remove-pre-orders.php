<?php
{
    /**
     * remove pre orders after 10m | and on hold places
     */

    # require config file
    require_once(dirname(__FILE__) . '/../include/config.inc.php');

    $currentTime = time();

    # get -10m time
    $checkDate = $currentTime - OneMinute * 10;

    $docIds = getExpiredPreOrders($checkDate);

    $pnrs = [];
    foreach ($docIds as $docId) {
        $pnrs[] = $docId['pnr'];
    }

    removePreOrdersByPnr($pnrs);
    removeOnHoldPlacesByPnr($pnrs);
}

/**
 * @param $checkDate
 * @return \MongoDB\Driver\Cursor
 */
function getExpiredPreOrders($checkDate) {
    $festa = new CFesta();

    $filter = [
        'createdAt' => [
            '$lte' => $checkDate
        ]
    ];

    $option = [
        'pnr' => 1
    ];

    $docIds = $festa->find(PreOrders, $filter, $option);

    return $docIds;
}

/**
 * @param $pnrs
 */
function removePreOrdersByPnr($pnrs) {
    $festa = new CFesta();

    $filter = [
        'pnr' => [
            '$in' => $pnrs
        ]
    ];

    $festa->deleteMany(PreOrders, $filter);
}

/**
 * @param $pnrs
 */
function removeOnHoldPlacesByPnr($pnrs) {
    $festa = new CFesta();

    $filter = [
        'pnr' => [
            '$in' => $pnrs
        ]
    ];

    $festa->deleteMany(OnHold, $filter);
}