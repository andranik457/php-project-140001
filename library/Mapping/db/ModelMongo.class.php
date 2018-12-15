<?php

/**
 * Class CModelMongo
 */
class CModelMongo {

    /**
     * @var mixed|string|void
     */
    private $mongoHost = DB_MONGO_HOSTNAME;

    /**
     * @var string
     */
    private $mongoDB = DB_MONGO_FESTA;

    /**
     * CModelMongo constructor.
     */
    public function __construct() {
        // decode
        $host = json_decode( $this->mongoHost, JSON_OBJECT_AS_ARRAY);
        if ($host['params'] != '') {
            $m = new MongoDB\Client( $host['host'], $host['params'] );
        }
        else {
            $m = new MongoDB\Client( $host['host'] );
        }

        $this->db = $m->selectDatabase( $this->mongoDB );
    }

    /**
     *
     */
    public function __destruct() {
        $this->db = null;
    }

}