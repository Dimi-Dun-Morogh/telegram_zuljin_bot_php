

<?php
$data = $bot->telegram->getMe();
?>
<div class="container d-flex flex-column align-items-center align-content-center h-100">
  <div class="col-5">

    <h3 class="text-center">bot info:</h3>


    <table class="table">

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
</div>