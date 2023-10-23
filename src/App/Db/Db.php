<?php

declare(strict_types=1);



namespace App\Db;

use PDO;
use PDOException;
use PDOStatement;

class Db
{
  public PDO $connection;
  private PDOStatement $stmt;
  public function __construct(string $driver, array $config, string $username, string $password)
  {


    $config = http_build_query($config, arg_separator: ';');

    $dsn  = "{$driver}:{$config}";

    try {
      $this->connection = new PDO($dsn, $username, $password, [
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
      ]);
    } catch (PDOException $e) {
      die("unable to connect to database");
    }
  }

  public function query(string  $query, array $params=[]):Db{
   $this->stmt =  $this->connection->prepare($query);

   $this->stmt->execute($params);
   return $this;
  }

  public function count(){
    return $this->stmt->fetchColumn();
  }


  public function find()
  {
    return $this->stmt->fetch();
  }
  public function id(){
    return $this->connection->lastInsertId();
  }

  public function findAll(){
    return $this->stmt->fetchAll();
  }
}
