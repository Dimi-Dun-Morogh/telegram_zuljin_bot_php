<?php
$data = $admin->getErrors();
?>
<div class="container d-flex flex-row justify-content-around align-items-center ">

<ul class="list-group  overflow-auto">
  <?php foreach ($data as $error): ?>
    <li class="list-group-item mb-3 fs-5">[<?php echo $error['created_at'] ?>]  <?php echo $error['text'] ?></li>

    <?php endforeach; ?>
</ul>

</div>