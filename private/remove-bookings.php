<?php
{
    /**
     * remove bookings
     * add seats back to class or nearest class
     */

    # require config file
    require_once(dirname(__FILE__) . '/../include/config.inc.php');

    $festa = new CFesta();

    $currentTime = time();

    # get -48h time
    $checkDate = $currentTime - OneDay * 2;

    $bookingOrdersInfo = getBookingOrdersByDate($checkDate);

    foreach ($bookingOrdersInfo as $orderId => $orderInfo) {
        echo 'OrderId: ', $orderId, "\n";

        # 1. add to seats for departure class
        # 2. add to seats for return class
        # 3. update order status
        # 4. add info to log (not implemented)
        $logInfo = [];

        # check departure info
        $departureClassInfo = getBestMatchClassIdForSeatsBy($orderInfo['departureClassId'], $orderInfo['departureFlightId']);

        if ($departureClassInfo['sameClass']) {
            $filter = ['_id' => new \MongoDB\BSON\ObjectID($departureClassInfo['classId'])];
            $updateInfo = [
                '$inc' => [
                    'availableSeats' => $orderInfo['usedSeats']
                ]
            ];
        }
        else {
            $filter = ['_id' => new \MongoDB\BSON\ObjectID($departureClassInfo['classId'])];
            $updateInfo = [
                '$inc' => [
                    'availableSeats' => $orderInfo['usedSeats'],
                    'numberOfSeats' => $orderInfo['numberOfSeats']
                ]
            ];
        }

        $festa->update(Classes, $filter, $updateInfo);

        # set log data
        $logInfo['departureInfo'] = [
            'filter'        => $filter,
            'updateInfo'    => $updateInfo
        ];


        # check isset return flight
        if (isset($orderInfo['returnClassId'])) {
            $returnClassInfo = getBestMatchClassIdForSeatsBy($orderInfo['returnClassId'], $orderInfo['returnFlightId']);

            if ($returnClassInfo['sameClass']) {
                $filter = ['_id' => new \MongoDB\BSON\ObjectID($returnClassInfo['classId'])];
                $updateInfo = [
                    '$inc' => [
                        'availableSeats' => $orderInfo['usedSeats']
                    ]
                ];
            }
            else {
                $filter = ['_id' => new \MongoDB\BSON\ObjectID($returnClassInfo['classId'])];
                $updateInfo = [
                    '$inc' => [
                        'availableSeats' => $orderInfo['usedSeats'],
                        'numberOfSeats' => $orderInfo['numberOfSeats']
                    ]
                ];
            }

            $festa->update(Classes, $filter, $updateInfo);

            # set log data
            $logInfo['returnInfo'] = [
                'filter'        => $filter,
                'updateInfo'    => $updateInfo
            ];
        }


        /////////////////// update order

        $filter = ['_id' => new \MongoDB\BSON\ObjectID($orderId)];
        $updateInfo = [
            '$set' => [
                'ticketStatus'  => 'Canceled',
                'updatedAt'     => time()
            ]
        ];

        $festa->update(Orders, $filter, $updateInfo);

        # set log data
        $logInfo['returnInfo'] = [
            'filter'        => $filter,
            'updateInfo'    => $updateInfo
        ];

        # add info to logs collection
        $logData = [
            "userId" => "CronTab",
            "action" => "Cancel Booking",
            "oldData" => json_encode([
                'orderId'   => $orderId,
                'pnr'       => $orderInfo['pnr']
            ]),
            "newData" => json_encode($logInfo),
            "createdAt" => time()
        ];

        $festa->insertOne(CollLogs, $logData);
    }
}

/**
 * @param $checkDate
 * @return array
 */
function getBookingOrdersByDate($checkDate) {
    $festa = new CFesta();

    $filter = [
        '$and' => [
            ['createdAt'    => ['$lt' => $checkDate]],
            ['ticketStatus' => 'Booking']
        ]
    ];

    $orders = $festa->find(Orders, $filter);

    $expiredBookings = [];
    foreach ($orders as $order) {
        $orderId = (string)$order['_id'];

        $expiredBookings[$orderId] = [
            'pnr'               => $order['pnr'],
            'usedSeats'         => $order['travelInfo']['usedSeats'],
            'departureFlightId' => $order['travelInfo']['departureClassInfo']['flightId'],
            'departureClassId'  => $order['travelInfo']['departureClassInfo']['_id'],
        ];

        if (isset($order['travelInfo']['returnClassInfo'])) {
            $expiredBookings[$orderId]['returnFlightId'] = $order['travelInfo']['returnClassInfo']['flightId'];
            $expiredBookings[$orderId]['returnClassId'] = $order['travelInfo']['returnClassInfo']['_id'];
        }
    }

    return $expiredBookings;
}

/**
 * @param $classId
 * @param $flightId
 * @return array
 */
function getBestMatchClassIdForSeatsBy($classId, $flightId) {
    $festa = new CFesta();

    $filter = ['flightId' => $flightId];

    $classesInfo = $festa->find(Classes, $filter);

    $orderClass = null;
    $possibleClass = null;
    foreach ($classesInfo as $classInfo) {
        if ((string)$classInfo['_id'] == $classId) {
            $orderClass = $classInfo;
        }
        else {
            if (!isset($classInfo['deletedAt'])) {
                if (!$possibleClass) {
                    $possibleClass = $classInfo;
                }
                else {
                    if (($classInfo['numberOfSeats'] - $classInfo['availableSeats']) > ($possibleClass['numberOfSeats'] - $possibleClass['availableSeats'])) {
                        $possibleClass = $classInfo;
                    }
                }
            }
        }
    }

    if ($orderClass) {
        return [
            'sameClass' => true,
            'classId' => $classId
        ];
    }
    else {
        return [
            'sameClass' => false,
            'classId' => (string)$possibleClass['_id']
        ];
    }
}