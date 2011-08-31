<?php

  function getDjatokaURL($rft_id, $type, $level = null, $reg_y = null, $reg_x = null, $reg_h = null, $reg_w = null, $scale_w = null, $scale_h = null) {
  //NOTE, though getRegion is y,x,h,w scale reverses to w,h
    $djatoka_server_base = variable_get('apiary_research_djatoka_url', 'http://localhost:8080/adore-djatoka');
    $djatoka_url = $djatoka_server_base.'/resolver?url_ver=Z39.88-2004';
    $djatoka_url .= '&rft_id='.$rft_id;
    $djatoka_url .= '&svc_id=info:lanl-repo/svc/'.$type;
    if($type == 'getRegion') {
      $djatoka_url .= '&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000';
      $djatoka_url .= '&svc.format=image/jpeg';
      if($level == '') {
        $level = '3';
      }
      $djatoka_url .= '&svc.level='.$level;
      $djatoka_url .= '&svc.rotate=0';
      if($reg_y == '') {
        $reg_y = '0';
      }
      if($reg_x == '') {
        $reg_x = '0';
      }
      if($reg_w == '' || $reg_h == '') {
        list($reg_w, $reg_h) = getimagesize($rft_id);
      }
      $djatoka_url .= '&svc.region='.$reg_y.','.$reg_x.','.$reg_h.','.$reg_w;
      //check for scaling
      if((isSet($scale_w) && $scale_w != '') || (isSet($scale_h) && $scale_h != '')) {
        if($scale_w == '') {
          $scale_w = '0';
        }
        if($scale_h == '') {
          $scale_h = '0';
        }
        $djatoka_url .= '&svc.scale='.$scale_w.','.$scale_h;
      }
    }
    if($type == 'getMetadata') {
      //nothing needs to be done as the Url ends above
      //this is for reference that there is more than one type
    }
    return $djatoka_url;
  }

  function getRftID($jp2URL) {
    //Idea here is to query the djatoka server to see if an existing rft_id exists for the url to an existing jpeg2000 file
    //If no rft_id exists, djatoka checks if it already has that filename and tries to get the rft_id from there
    //If there is still no rft_id, djatoka server uploads the image into its images/uploaded_images dir and assigns it a rft_id
    //These will be large images so allow the object/datastream to be created immediately
    //Then where the image is displayed in the UI, notify if it is currently being uploaded
    return $getRftID;
  }

  function getJPEG_URL() {
    $djatoka_server_base = variable_get('apiary_research_djatoka_url', 'http://localhost:8080/adore-djatoka');
    $_jpeg_url = $djatoka_server_base.'/resolver?url_ver=Z39.88-2004';
    $_jpeg_url .= '&rft_id='.$obj_jp2URL;
    $_jpeg_url .= '&svc_id=info:lanl-repo/svc/getRegion';
    $_jpeg_url .= '&svc_val_fmt=info:ofi/fmt:kev:mtx:jpeg2000';
    $_jpeg_url .= '&svc.format=image/jpeg';
    $_jpeg_url .= '&svc.level=4';
    $_jpeg_url .= '&svc.rotate=0';
    $_jpeg_url .= '&svc.region=0,0,'.$obj_height.','.$obj_width;
  }

  function scaleDjatokaURL($djatoka_url, $scale_w = null, $scale_h = null) {
    if((isSet($scale_w) && $scale_w != '') || (isSet($scale_h) && $scale_h != '')) {
      if($scale_w == '') {
        $scale_w = '0';
      }
      if($scale_h == '') {
        $scale_h = '0';
      }
      $djatoka_url .= '&svc.scale='.$scale_w.','.$scale_h;
    }
    return $djatoka_url;
  }

?>