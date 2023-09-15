<?php include __DIR__ . "/partials/header.php" ?>
<!-- end header -->

<div class="d-flex content my-1 flex-grow-1 ">
  <div class="col-2 admin-sidebar-wrap">

    <!-- Sidebar -->
    <?php include __DIR__ . "/partials/sidebar.php" ?>

  </div>
  <div class="col admin-content">
    <h3>hello content</h3>
    <?php echo $_SERVER['QUERY_STRING']; ?>
  </div>

</div>
<!-- end of content container -->

<?php include __DIR__ . "/partials/footer.php" ?>