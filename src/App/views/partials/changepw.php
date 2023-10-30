<?php
$adminAcc = $admin->getAdmin($_SESSION['user']);
$msg = '';
if(isset($_POST['new_password'])){
 $msg = $admin->changePassword($_POST);
}
?>
<div class="container d-flex flex-row justify-content-around align-items-center h-100">
<div class="mx-auto  my-auto col-4 admin-login-form-wrap">
  <?php echo $msg ?>
    <form action="admin.php?changepw" method="post">
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">Login:</label>
        <input name="login" required value="<?php echo $adminAcc['login'] ?>"  type="text" class="form-control form-control-sm" id="exampleFormControlInput1" disabled>
      </div>
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">Old Password:</label>
        <input required name="password" type="password" class="form-control form-control-sm" id="exampleFormControlInput1">
      </div>
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">New Password:</label>
        <input name="new_password" type="password" class="form-control form-control-sm" id="exampleFormControlInput1">
      </div>
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">Repeat New Password:</label>
        <input required name="repeat_password" type="password" class="form-control form-control-sm" id="exampleFormControlInput1">
      </div>
      <div class="mb-3">
        <input type="submit" class="form-control btn btn-success" value="OK" id="exampleFormControlInput1">
      </div>

    </form>
  </div>
</div>