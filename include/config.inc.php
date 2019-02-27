<?php
{
    /**
     * config file for festa private scripts
     */

    # PHP ini
    ini_set('memory_limit', '3072M');

    define('DIR_ROOT',              '/var/www/test-v101-php/');
    define('DIR_INCLUDE',           '/var/www/test-v101-php/include/');
    define('DIR_LIBRARY',           '/var/www/test-v101-php/library/');
    define('DIR_TMP',               '/tmp/');

    # DB Mongo
    $mongoHost = [
        'host'      => "mongodb://localhost:27017",
        'params'    => ''
    ];

    define('DB_MONGO_HOSTNAME', json_encode($mongoHost));

    # Define databases
    define('DB_MONGO_FESTA', 'testV101');

    define('FestaRate', 5);
    define('OneMinute', 60);
    define('OneDay', 86400);

    # define time zone
    date_default_timezone_set('Asia/Yerevan');

    # For mongoDB php7.* connection
    require_once(DIR_LIBRARY . 'mongo/vendor/autoload.php');

    # defined tables
    require_once(DIR_INCLUDE . 'catalog/tables.inc.php');

    # AutoLoad class
    require_once(DIR_INCLUDE . 'catalog/autoload.inc.php');
}