<?php
{
    /**
     * config file for festa private scripts
     */

    # PHP ini
    ini_set('memory_limit', '3072M');

    define('DIR_INCLUDE',           '/var/www/festa-php/include/');
    define('DIR_LIBRARY',           '/var/www/festa-php/library/');

    # DB Mongo
    $mongoHost = [
        'host'      => "mongodb://localhost:27017",
        'params'    => ''
    ];

    define('DB_MONGO_HOSTNAME', json_encode($mongoHost));

    # Define databases
    define('DB_MONGO_FESTA', 'festa');

    define('FestaRate', 5);
    define('OneMinute', 60);

    # define time zone
    date_default_timezone_set('UTC');

    # For mongoDB php7.* connection
    require_once(DIR_LIBRARY . 'mongo/vendor/autoload.php');

    # defined tables
    require_once(DIR_INCLUDE . 'catalog/tables.inc.php');

    # AutoLoad class
    require_once(DIR_INCLUDE . 'catalog/autoload.inc.php');
}