<?php
 $chats = $admin->getChats();
?>

<div class="" style="overflow-x: hidden; overflow-y: auto; height: calc(100vh - 100px);">
<div class="d-flex flex-wrap justify-content-center" >

<?php foreach($chats as $chat):  ?>

<div class="card mx-1 mb-2" style="width: 18rem;">
  <div class="card-header text-center">
    <?php echo $chat['name']  ?>
  </div>
  <ul class="list-group list-group-flush">
    <li class="list-group-item">[chat_id] <?php echo $chat['chat_id']  ?></li>
    <li class="list-group-item">[name] <?php echo $chat['name']  ?></li>
    <li class="list-group-item">[created at] <?php echo $chat['created_at']  ?></li>
  </ul>
</div>

<?php endforeach; ?>
</div>


</div>
