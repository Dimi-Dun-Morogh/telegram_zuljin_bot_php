<?php
declare(strict_types=1);
namespace App\Services;

use App\Db\Db;



class AdminService
{

  public function __construct(private Db $db)
  {
  }

  public function register(string $login, string $password)
  {
    $password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    $this->db->query("INSERT into `admins` (`login`, `password`)
    VALUES (:login, :password)
     ", ['login' => $login, 'password' => $password]);


    // session_regenerate_id();


    // $_SESSION['user'] = $this->db->id();
  }
}