<?php include __DIR__ . "/partials/header.php" ?>
<!-- end header -->

<?php


$app  = require(__DIR__ . '/../app.php');
$admin = $app['admin'];

if (isset($_POST['login']) && isset($_POST['password'])) {
  $admin->login($_POST);
}
if (isset($_SESSION['user'])) {

  $currentUrl = $_SERVER['REQUEST_URI'];
  $parsedUrl = parse_url($currentUrl);
  $cleanUrl = $parsedUrl['path'];
  $newUrl = str_replace("login", "admin", $cleanUrl);

  header("Location: $newUrl");
  exit;
}

?>

<div class="d-flex content my-1 flex-grow-1 ">
  <div class="mx-auto  my-auto col-4 admin-login-form-wrap">
    <form action="login.php" method="post">
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">Login:</label>
        <input name="login" type="text" class="form-control form-control-sm" id="exampleFormControlInput1">
      </div>
      <div class="mb-3">
        <label for="exampleFormControlInput1" class="form-label">Password:</label>
        <input name="password" type="password" class="form-control form-control-sm" id="exampleFormControlInput1">
      </div>
      <div class="mb-3">
        <input type="submit" class="form-control btn btn-success" value="LOGIN" id="exampleFormControlInput1">
      </div>

    </form>
  </div>
</div>
<!-- end of content container -->

<?php include __DIR__ . "/partials/footer.php" ?>