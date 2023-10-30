<?php

function currentHook()
{
  global $bot;
  $data = $bot->telegram->WebhookInfo();
  if (isset($data['result'])) {
    $url = $data['result']['url'];
    return $url ? $url : 'webhook is not set';
  } else {
    return "error fetching tg api";
  }
}

// check query params for delete/set, do action

if (isset(explode("=", $_SERVER['QUERY_STRING'])[1])) {
  $action = explode("=", $_SERVER['QUERY_STRING'])[1];
  if ($action === 'delete') {
    $bot->telegram->deleteWebHook();
  }
  if ($action === 'set') {
    $bot->telegram->setWebHook($config['WebHook']);
  }
  $currentUrl = explode("=", $_SERVER['REQUEST_URI'])[0];
  // Injecting JavaScript to modify the browser's URL
  echo "<script type='text/javascript'>
  history.pushState(null, null, '$currentUrl');
</script>";
}

?>

<div class="container d-flex flex-column align-items-center align-content-center h-100 admin-block-bg">
  <h3 class="text-center">Webhook Settings for <?php echo $botName ?></h3>

  <h5 class="mt-5 align-self-start"> <b>[env var webhook]:</b> <?php echo $config['WebHook']  ?> </h5>
  <h5 class="mt-5 align-self-start "><b>[current webhook]:</b> <?php echo currentHook()  ?> </h5>
  <div class="col-5">

    <div class="d-flex flex-column mt-5">
      <a href="admin.php?webhook=set" class="btn btn-success mb-3 fw-bold">
        <i class="bi bi-gear-fill"></i> SET Webhook
      </a>
      <a href="admin.php?webhook=delete" class="btn btn-danger fw-bold"><i class="bi bi-trash3-fill"></i> DELETE Webhook</a>
    </div>
  </div>

</div>