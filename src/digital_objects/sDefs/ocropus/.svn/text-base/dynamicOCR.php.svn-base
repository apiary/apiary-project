<?php
  if(!isset($_GET["url"])) {
    echo 'A url must be provided!';
  }
  else {
    $url=$_GET["url"];
    $command="wget -O /tmp/tempim.jpg \"$url\"";
    shell_exec("$command");
    $output = shell_exec("ocropus page /tmp/tempim.jpg");
    echo $output;
  }
?>