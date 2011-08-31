<?php
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past
global $user;
include_once(drupal_get_path('module', 'apiary_project') . '/apiaryPermissionsClass.php');
include_once(drupal_get_path('module', 'apiary_project') . '/workflow/include/search.php');

function send_request($function, $param1, $param2, $param3){
  //echo "function = ".$function." param1 = ".$param1." param2 = ".$param2." param3 = ".$param3;
  if($param3 != "0") {
    call_user_func($function, $param1, $param2, $param3);
  }
  else if($param2 != "0") {
    call_user_func($function, $param1, $param2);
  }
  else if($function == "clear_session") {
    call_user_func($function);
  }
  else {
    call_user_func($function, $param1);
  }
}

function get_comparer() {
  $return_html = '';
  $server_base = variable_get('apiary_research_base_url', 'http://localhost');
  $home_link = '<p><h3><a href="'.$server_base.'/drupal">Home</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="'.$server_base.'/drupal/apiary/admin">Administer Apiary</a></h3></p>';
  $return_html .= $home_link;
  $return_html .= 'Please enter text to compare. Results are displayed in daisydiff and highlighted format with similar text percentages.</br>'."\n";
  $return_html .= '<table>'."\n";
  $return_html .= '<tr><td>Old Text:</td></td><td>'."\n";
  $return_html .= 'New Text: </td></tr><tr>'."\n";
  $return_html .= '<td><textarea rows="10" cols="40" id="old_text" name="old_text" wrap="physical">'.$_GET['quote1'].'</textarea></td>'."\n";
  $return_html .= '<td><textarea rows="10" cols="40" id="new_text" name="new_text" wrap="physical">'.$_GET['quote2'].'</textarea></td></tr></table>'."\n";
  $return_html .= "<button onclick='reset();' style='margin: 5px 0px; border: 1px solid grey;'>Reset</button>"."\n";
  $return_html .= "<button onclick='compare_text();' style='margin: 5px 0px; border: 1px solid grey;'>Compare Text</button><br>"."\n";
  $return_html .= '<div id="daisydiff"></div>'."\n";
  $return_html .= '<div id="highlight_diff"></div>'."\n";
  $return_html .= '<div id="percent_results"></div>'."\n";
  echo $return_html;
}

function compare_text() {
  $successfully_compared_text = "false";
  $msg = '';
  $highlight_diff_html = '';
  $daisydiff_html = '';
  $percent_results_html = '';
  if((isset($_POST['old_text']) && $_POST['old_text'] != '') || (isset($_POST['new_text']) && $_POST['new_text'] != '')){
    $old_text = $_POST['old_text'];
    $new_text = $_POST['new_text'];
    $levenshtein_distance = levenshtein($old_text,$new_text);//levenshtein
    $levenshtein_percentage = asimilar($old_text,$new_text);//levenshtein percentage
    similar_text($old_text,$new_text,$similar_text_percentage);//returns similar text percentage
    $trimmedstr1 = trim($old_text);
    $trimmedstr2 = trim($new_text);
    $f1_arr = explode(" ", $trimmedstr1);
    $f2_arr = explode(" ", $trimmedstr2);

    $f1 = implode( "\n", $f1_arr );
    $f2 = implode( "\n", $f2_arr );

    $array_val = PHPDiff( $f1, $f2 );

    $f2_string = explode("\n", $f2);

    $array_val_arr = explode("\n",$array_val);
    array_pop($array_val_arr);

    $highlightarr = array();
    foreach ($array_val_arr as &$value) {
      $range = "";
      $range = strstr($value, ',');
      if ($range) {
        $rangefrom = substr($value, 0, strpos($value, ','));
        $rangeto = substr($value, strpos($value, ',')+1);

        for($i=$rangefrom;$i<=$rangeto;$i++) {
          $highlightarr[] = $i - 1;
        }
      }
      else {
        $highlightarr[] = $value - 1;
      }
    }

    $highlight_diff_html = '<p><h2>Simple text comparison results</h2></p>';
    $highlight_diff_html .= "<table width='100%' border='1'><tr><td width='10%'>Orginal:</td><td>" .$old_text. "</td></tr>";
    $highlight_diff_html .= "<tr><td width='10%'>Change:</td><td>";
    for($i = 0;$i<sizeof($f2_string); $i++) {
      foreach ($highlightarr as $value) {
        if ($i== $value) {
          $flg = 1;
        }
      }
      if ($flg == 1) {
        $flg = 0;
        $highlight_diff_html .= "<font color='#00FF00'>" .$f2_string[$i]."</font>&nbsp;";
      }
      else {
        $highlight_diff_html .= $f2_string[$i]."&nbsp;";
      }
    }
    $highlight_diff_html .= "</td></tr></table>";
    $daisydiff_html = '<p><h2>Daisydiff comparison results</h2></p>';
    $daisydiff_html .= daisydiff_text($old_text, $new_text);
    $percent_results_html = '<p><h2>Percentage of similar text</h2></p>';
    $percent_results_html .= "The levenshtein distance for the strings is " .$levenshtein_distance. " <br/>";
    $percent_results_html .= "Using levenshtein distance, the strings are " .$levenshtein_percentage. " percent similar <br/>";
    $percent_results_html .= "Using similar text, the strings are ".$similar_text_percentage." percent similar <br/>";
    $successfully_compared_text = "true";
  }
  else {
    $msg = 'No text passed to compare!';
  }
  $returnJSON['levenshtein_distance'] = $levenshtein_percentage;
  $returnJSON['levenshtein_percentage'] = $levenshtein_percentage;
  $returnJSON['similar_text_percentage'] = $similar_text_percentage;
  $returnJSON['daisydiff_html'] = $daisydiff_html;
  $returnJSON['highlight_diff_html'] = $highlight_diff_html;
  $returnJSON['percent_results_html'] = $percent_results_html;
  $returnJSON['successfully_compared_text'] = $successfully_compared_text;
  $returnJSON['msg'] = $msg;
  echo json_encode($returnJSON);
}

function PHPDiff($old, $new)
{
  # split the source text into arrays of lines
  $t1 = explode("\n",$old);
  $x=array_pop($t1);
  if ($x>'') $t1[]="$x\n\\ No newline at end of file";
  $t2 = explode("\n",$new);
  $x=array_pop($t2);
  if ($x>'') $t2[]="$x\n\\ No newline at end of file";
    # build a reverse-index array using the line as key and line number as value
    # don't store blank lines, so they won't be targets of the shortest distance
    # search
    foreach($t1 as $i=>$x) {
      if ($x>'') {
        $r1[$x][]=$i;
      }
    }
    foreach($t2 as $i=>$x) {
      if ($x>'') {
        $r2[$x][]=$i;
      }
    }

    $a1=0; $a2=0;   # start at beginning of each list
    $actions=array();

    # walk this loop until we reach the end of one of the lists
    while ($a1<count($t1) && $a2<count($t2)) {
      # if we have a common element, save it and go to the next
      if ($t1[$a1]==$t2[$a2]) {
        $actions[]=4; $a1++; $a2++; continue;
      }

      # otherwise, find the shortest move (Manhattan-distance) from the
      # current location
      $best1=count($t1); $best2=count($t2);
      $s1=$a1; $s2=$a2;
      while(($s1+$s2-$a1-$a2) < ($best1+$best2-$a1-$a2)) {
        $d=-1;
        foreach((array)@$r1[$t2[$s2]] as $n) {
          if ($n>=$s1) {
            $d=$n; break;
          }
        }
        if ($d>=$s1 && ($d+$s2-$a1-$a2)<($best1+$best2-$a1-$a2)) {
          $best1=$d; $best2=$s2;
        }
        $d=-1;
        foreach((array)@$r2[$t1[$s1]] as $n) {
          if ($n>=$s2) {
            $d=$n; break;
          }
        }
        if ($d>=$s2 && ($s1+$d-$a1-$a2)<($best1+$best2-$a1-$a2)) {
          $best1=$s1; $best2=$d;
        }
        $s1++; $s2++;
     }
     while ($a1<$best1) {
       $actions[]=1; $a1++;
     }  # deleted elements
     while ($a2<$best2) {
       $actions[]=2; $a2++;
     }  # added elements
  }

  # we haveve reached the end of one list, now walk to the end of the other
  while($a1<count($t1)) {
    $actions[]=1; $a1++;
  }  # deleted elements
  while($a2<count($t2)) {
    $actions[]=2; $a2++;
  }  # added elements

  # and this marks our ending point
  $actions[]=8;

  # now, let us follow the path we just took and report the added/deleted
  # elements into $out.
  $op = 0;
  $x0=$x1=0; $y0=$y1=0;
  $out = array();
  foreach($actions as $act) {
    if ($act==1) {
      $op|=$act;
      $x1++; continue;
    }
    if ($act==2) {
      $op|=$act;
      $y1++;
      continue;
    }
    if ($op>0) {
      $xstr = ($x1==($x0+1)) ? $x1 : ($x0+1).",$x1";
      $ystr = ($y1==($y0+1)) ? $y1 : ($y0+1).",$y1";
      if ($op==1) {
        $out[] = "{$y1}";
      }
      else if ($op==3) {
        $out[] = "{$ystr}";
        while ($x0<$x1) {
          // $out[] = '< '.$t1[$x0];
	      $x0++;
	    }   # deleted elems
	  }
      if ($op==2) {
        $out[] = "{$ystr}";
      }
      else if ($op==3) {
        // $out[] = '---';
        while ($y0<$y1) {
          //$out[] = '> '.$t2[$y0];
	      $y0++;
	    }   # added elems
	  }
    }
    $x1++;
    $x0=$x1;
    $y1++;
    $y0=$y1;
    $op=0;
  }
  $out[] = '';
  return join("\n",$out);
}

function asimilar($str1, $str2) {
  $strlen1=strlen($str1);
  $strlen2=strlen($str2);
  $max=max($strlen1, $strlen2);

  $splitSize=250;
  if($max>$splitSize) {
    $lev=0;
    for($cont=0;$cont<$max;$cont+=$splitSize) {
      if($strlen1<=$cont || $strlen2<=$cont) {
        $lev=$lev/($max/min($strlen1,$strlen2));
        break;
      }
      $lev+=levenshtein(substr($str1,$cont,$splitSize), substr($str2,$cont,$splitSize));
    }
  }
  else {
    $lev=levenshtein($str1, $str2);
  }

  $percentage= -100*$lev/$max+100;
  if($percentage>75) {
    //Ajustar con similar_text
    similar_text($str1,$str2,$percentage);
  }

  return $percentage;
}

function daisydiff_text($old_text, $new_text) {
//save old and new texts to old_text_file and new_text_file
  $now = date("Ymdhis");
  $old_text_file = '/tmp/old_text_file_'.$now.'.txt';
  $new_text_file = '/tmp/new_text_file_'.$now.'.txt';
  $fp_old = fopen($old_text_file, 'w');
  fwrite($fp_old, $old_text);
  fclose($fp_old);
  $fp_new = fopen($new_text_file, 'w');
  fwrite($fp_new, $new_text);
  fclose($fp_new);
  $return_html = daisydiff($old_text_file, $new_text_file);
  unlink($old_text_file);
  unlink($new_text_file);
  return $return_html;
}

function daisydiff($url_old, $url_new) {
  $daisydiff_dir = '/var/www/drupal/modules/apiary_project/daisydiff';
  $daisydiff_output = '/tmp/daisydiff_output_'.date("Ymdhis").'.xml';
  $daisydiff_cmd = "java -jar $daisydiff_dir/daisydiff.jar $url_old $url_new --file=$daisydiff_output --output=xml --type=tag --q";
  shell_exec($daisydiff_cmd);
  $daisydiff_doc = new DOMDocument();
  $daisydiff_doc->load($daisydiff_output);
  //$daisydiff_xml = $daisydiff_doc->saveXML();
  foreach($daisydiff_doc->getElementsByTagName('diff') as $diff) {
    $daisydiff_html = $daisydiff_doc->saveXML($diff);
  };
  unlink($daisydiff_output);
  return $daisydiff_html;
}

?>