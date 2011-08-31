<?php
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
  $home_link = '<p><h2><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h2></p>';
  if(!user_access(apiaryPermissionsClass::$ADMINISTER_APIARY)) {
    return false;
  }
?>
<?php echo $home_link;?>
<html>
<head>
</head>
<body>
<form  action="apiary?ref=process_batch_create_specimens" method="post" enctype="multipart/form-data">
<table>
<tr>
  <td>
    <b>Specimen Image:</b><br>
       Max Specimens to be Created (<font color="red">leave blank or 0 for no max</font>):<br><input type=text size="15" name="max_specimens"><br>
       File name source file:<br><input type=text size="150" name="source_file"><br>
       Referent ID (rft_id):<br><input type=text size="150" name="rft_id"><br> <?php //The txt document needs a rft_id column added ?>
       JPEG2000 Url Base:<br><input type=text size="150" name="jp2URL_base"><br>
       Source Url Base:<br><input type=text size="150" name="sourceURL_base"><br>
  </td>
</tr>
</table>
<input type="submit" name="Batch Create Specimens" value="Submit" />
</form>
</body>
</html>