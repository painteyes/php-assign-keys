<?php

    class MyPDO {

        private $connection;

        public function __construct() {

            $dbname = getenv('DB_NAME');
            $dbuser = getenv('DB_USER');
            $dbpwsd = getenv('DB_PSWD');
            $dbhost = getenv('DB_HOST');
            $dbport = getenv('DB_PORT');

            $dsn = "mysql:host=$dbhost;port=$dbport;dbname=$dbname";

            $this->connection = new PDO($dsn,$dbuser,$dbpwsd);

        }

        public function query($sql) {
            return $this->connection->query($sql);
        }

    }

?>