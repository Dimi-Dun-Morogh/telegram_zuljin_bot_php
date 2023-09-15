<?php

use App\Db\Db;
use Config\Config;
use App\Services\AdminService;
require __DIR__ . "/../../../vendor/autoload.php";

$dbConfig = Config::dbConfig();


$db = new Db('mysql', [
  'host' =>  $dbConfig['host'],
  'port' =>  $dbConfig['port'],
  'dbname' => $dbConfig['dbname']
], $dbConfig['user'], $dbConfig['pass']);

$sqlFile = file_get_contents("../../../database.sql");

$db->connection->query($sqlFile);
//create admin default acc;

$admin = new AdminService($db);
$admin->register('admin', 'admin');