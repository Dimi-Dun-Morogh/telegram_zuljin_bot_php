

<?php
$data = $bot->telegram->getMe();
 $chats = $admin->getChats();
 $chatUsers= $admin->getChatUsers();
?>
<div class="container d-flex flex-row justify-content-around align-items-center h-100">
  <div class="col border border-success  admin-block-bg px-1">

    <h3 class="text-center">bot info:</h3>


    <table class="table table-stripped table-sm fw-bold">

      <tbody>
        <?php

        foreach ($data['result']  as $key => $value) {
          echo  "<tr><td>{$key}</td>
                 <td> {$value}</td></tr>";
        }

        ?>

      </tbody>
    </table>

  </div>
  <div class="col-3 border border-success admin-block-bg " style="margin-left: 5px;">
    <div>
  <h3 class="text-center">chats:</h3>
  <span class="text-center w-100 d-block text-large fw-bold"> <?php
  if($chats) echo count($chats);
  ?> </span>
  </div>
  <div>
  <h3 class="text-center">chat users:</h3>
  <span class="text-center w-100 d-block text-large fw-bold"><?php
  if($chatUsers) echo count($chatUsers);
  ?></span>
  </div>
  </div>

</div>