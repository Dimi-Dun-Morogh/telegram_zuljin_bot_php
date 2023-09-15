<?php

function activeLink(string $name)
{
  $currQuer = $_SERVER['QUERY_STRING'];
  if (!$currQuer) {
    $currQuer = 'main';
  }
  $status =  $name === $currQuer;
  echo  $status ? 'active' : null;
}


?>

<nav class="navbar navbar-expand-lg admin-sidebar-nav mt-4" data-bs-theme="dark">

  <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
    <span class="navbar-toggler-icon"></span>
  </button>
  <div class="collapse navbar-collapse " id="navbarNav">
    <ul class="navbar-nav flex-column  text-center w-100">
      <li class="nav-item <?php activeLink('main')?>">
        <a class="nav-link <?php activeLink('main')?>" href="admin.php?main">BOT INFO</a>
      </li>
      <li class="nav-item <?php activeLink('webhook')?>">
        <a class="nav-link <?php activeLink('webhook')?>" href="admin.php?webhook">WEBHOOK SETTINGS</a>
      </li>
      <li class="nav-item <?php activeLink('errors')?>">
        <a class="nav-link <?php activeLink('errors')?>" href="admin.php?errors">ERROR LOGS</a>
      </li>
      <li class="nav-item <?php activeLink('chats')?>">
        <a class="nav-link <?php activeLink('chats')?>" href="admin.php?chats">CHATS</a>
      </li>
    </ul>
  </div>
</nav>
