<?php include __DIR__ . "/partials/header.php" ?>
<!-- end header -->

<?php

$app  = require(__DIR__ . "/../app.php");
 $bot = $app['bot'];
 $config = $app['config'];
 function botName() {
  global $bot;
  $data = $bot->telegram->getMe();
  if(isset($data['result'])) {
    return $data['result']['username'];
  }
 }

function dynamic() {
  global $bot;
  global $config;
 $botName = botName();
  switch (explode("=",$_SERVER['QUERY_STRING'])[0]) {
    case 'webhook':
      include __DIR__ . "/partials/webhook.php" ;
      break;
    case 'main':
    default:

    include __DIR__ . "/partials/main.php" ;
      break;
  }
}

?>

<div class="d-flex flex-column flex-md-row flex-grow-1">

  <div class="admin-sidebar-wrap">

    <!-- Sidebar -->
    <?php include __DIR__ . "/partials/sidebar.php" ?>

  </div>

  <div class="col admin-content ">

    <?php dynamic(); ?>
  </div>

</div>
<!-- end of content container -->

<?php include __DIR__ . "/partials/footer.php" ?>


<?php

function redirectLogin()
{
  $currentUrl = $_SERVER['REQUEST_URI'];
  $parsedUrl = parse_url($currentUrl);
  $cleanUrl = $parsedUrl['path'];
  $newUrl = str_replace("admin", "login", $cleanUrl);

  header("Location: $newUrl");
  exit;
}

if ($_SERVER['QUERY_STRING'] === 'logout') {
  session_destroy();
  redirectLogin();
}


if (!isset($_SESSION['user'])) {

  redirectLogin();
}


?>