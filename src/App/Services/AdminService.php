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

    session_regenerate_id();
    $_SESSION['user'] = $this->db->id();
  }

  public function changePassword(mixed $formData)
  {
    $adminId = $_SESSION['user'];
    $adminAcc = $this->db->query("SELECT * FROM admins where id=:id", ['id' => $adminId])->find();
    $pwMatch = password_verify($formData['password'], $adminAcc['password']);
    if(!$pwMatch) return "no match on old password";
    if($formData['new_password'] !== $formData['repeat_password']) return "repeat new password";
    $newPassword = password_hash($formData['new_password'], PASSWORD_BCRYPT, ['cost' => 12]);
    $query = "UPDATE admins SET password = '$newPassword' WHERE id=$adminId";
    $this->db->query($query);
    return 'success';
  }

  public function getAdmin(int $id)
  {

    $query = "SELECT * from admins  WHERE id=:id";
    $data = $this->db->query($query, ['id' => $id])->find();
    return $data;
  }

  public function login(array $formData)
  {
    $user = $this->db->query("SELECT * FROM admins WHERE login = :login", [
      'login' => $formData['login']
    ])->find();

    $passwordsMatch = password_verify($formData['password'], $user['password'] ?? '');
    if (!$user || !$passwordsMatch) {
      return;
    }
    session_regenerate_id();
    $_SESSION['user'] = $user['id'];
  }

  public function getChats()
  {
    $query = "SELECT * FROM chats";
    $data = $this->db->query($query)->findAll();
    return $data;
  }
  public function getChatUsers()
  {
    $query =  "SELECT * from chat_participants";
    $data = $this->db->query($query)->findAll();
    return $data;
  }

  public function getErrors()
  {
    $query = "SELECT * FROM errors ORDER BY 'created_at' ASC LIMIT 5";
    $data = $this->db->query($query)->findAll();
    return $data;
  }
}
