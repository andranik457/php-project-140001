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

        $departureClassInfo = getBestMatchClassIdForSeatsBy($orderInfo['departureClassId'], $orderInfo['departureFlightId']);
        $returnClassInfo = getBestMatchClassIdForSeatsBy($orderInfo['returnClassId'], $orderInfo['returnFlightId']);

        # 1. add to seats for departure class
        # 2. add to seats for return class
        # 3. update order status

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

        ///////////////////

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

        ///////////////////
        $filter = ['_id' => new \MongoDB\BSON\ObjectID($orderId)];
        $updateInfo = [
            '$set' => [
                'ticketStatus'  => 'Canceled',
                'updatedAt'     => time()
            ]
        ];

        $festa->update(Orders, $filter, $updateInfo);
    }
}

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
            'usedSeats' => $order['travelInfo']['usedSeats'],
            'departureFlightId' => $order['travelInfo']['departureClassInfo']['flightId'],
            'departureClassId' => $order['travelInfo']['departureClassInfo']['_id'],
        ];

        if (isset($order['travelInfo']['returnClassInfo'])) {
            $expiredBookings[$orderId]['returnFlightId'] = $order['travelInfo']['returnClassInfo']['flightId'];
            $expiredBookings[$orderId]['returnClassId'] = $order['travelInfo']['returnClassInfo']['_id'];
        }
    }

    return $expiredBookings;
}

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