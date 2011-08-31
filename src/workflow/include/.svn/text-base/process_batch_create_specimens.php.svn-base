<?
  $max_specimens = $_POST['max_specimens'];
  $source_file = $_POST['source_file'];
  $jp2URL_base = $_POST['jp2URL_base'];
  $rft_id = $_POST['rft_id'];
  $sourceURL_base = $_POST['sourceURL_base'];

  if (isSet($jp2URL_base) && $jp2URL_base != '') {
    if(!strstr($jp2URL_base, "http://")) {
      $jp2URL_base = 'http://'.$jp2URL_base;
    }
    if (substr($jp2URL_base, -1, 1) != '/') {
      $jp2URL_base .= '/';
    }
    if (substr($rft_id, -1, 1) != '/') {
      $rft_id .= '/';
    }
    if (isSet($sourceURL_base) && $sourceURL_base != '') {
      if(!strstr($sourceURL_base, "http://")) {
        $sourceURL_base = 'http://'.$sourceURL_base;
      }
      if (substr($sourceURL_base, -1, 1) != '/') {
        $sourceURL_base .= '/';
      }
    }
    if($max_specimens != '' && $max_specimens != '0') {
      $max = (int)$max_specimens;
    }
    else {
      $max = 1000000;
    }
    if($max > 0) {
      include_once(drupal_get_path('module', 'apiary_project') . '/adore-djatoka/functions_djatoka.php');
      $i = 0;
      if($file = @fopen($source_file, 'r')) {
        while(!feof($file) && $i < $max) {
          $jp2_file = trim(fgets($file));
          $jp2URL = $jp2URL_base.$jp2_file;
          $sourceURL = $sourceURL_base.substr($jp2_file, 0, -4).'.tif';
          $jp2_rft_id = $rft_id . substr($jp2_file, 0, -4);
          if($handle = @fopen($jp2URL, 'r')) {
            module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_Specimen');
            $new_specimen = new AP_Specimen();
            $specimen_label = '';
            if($new_specimen->createSpecimenObject('', '', '', '', $specimen_label)) {
              module_load_include('php', 'Apiary_Project', 'fedora_commons/class.AP_Image');
              $new_image = new AP_Image();
              //Ultimately this will have a djatoka plugin check the resolver db and add records as needed
	          list($width, $height) = getimagesize($jp2URL);
              $image_label = '';
              $jpeg_datastream_url = getDjatokaURL($jp2_rft_id, 'getRegion', '4', '0', '0', $height, $width, '', '');
              if($new_image->createImageObject($new_specimen->pid, $jp2URL, $jp2_rft_id, $sourceURL, $width, $height, $jpeg_datastream_url, $image_label)) {
                echo 'Specimen '.$new_specimen->pid.' and image '.$new_image->pid.' successfully created.<br>';
              }
              else {
                echo 'Unable to create a new image. Specimen '.$new_specimen->pid.' successfully created.<br>';;
              }
            }
            else {
              echo 'Unable to create a new specimen.<br>';
            }
            fclose($handle);
          }
          else {
            echo 'The url '.$jp2URL.' is invalid! <br>';
          }
          $i++;
        }
        fclose($file);
      }
      else {
        echo 'Unable to open file '.$source_file.'<br>';
      }
    }
    else {
      echo 'Invalid Max specimens value <br>';
    }
  }
  else {
    echo 'A jp2 base url must be provided in order to batch process objects.';
  }
?>