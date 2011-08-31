<?php
# functions_misc.php: includes misc. functions used throughout the website.
# $Revision: 2917 $


function printf_r($array) 
{
   if ( is_array($array) )
   {
       ksort($array);
       reset($array);
   }
   //echo "<FONT FACE='Courier New' SIZE=50%'>";
   echo "<table>";
   if ( is_array($array) )
   {
       foreach($array as $f_name=>$f_value) {
           echo "<tr><td valign='top'><div align='right'>";
               if(!$f_value) { echo "<font color='red'>"; }
                   echo $f_name;
               if(!$f_value) { echo "</font>"; }
           echo "&nbsp;:</div></td><td valign='top'><div align='left'>&nbsp;";
    
           if(is_array($f_value)) {
               printf_r($f_value);
           } else {
               echo $f_value;
           }
           echo "</div></td></tr>";
       }
   }
   echo "</table>";
   //echo "</FONT>";
}

function printf_r_nonum($array) 
{
   echo "<FONT FACE='Courier New' SIZE=50%'>";
   echo "<table>";
   if ( is_array($array) )
   {
       foreach($array as $f_name=>$f_value) 
       {
           echo "<tr><td valign='top'><div align='right'>";
           echo $f_name;
           echo "&nbsp;:</div></td><td valign='top'><div align='left'>&nbsp;";
           if(is_array($f_value)) 
               printf_r_nonum2($f_value);
           else 
               echo $f_value;
           echo "</div></td></tr>";
       }
   }
   echo "</table>";
   echo "</FONT>";
}

function printf_r_nonum2($array) 
{
   echo "<FONT FACE='Courier New' SIZE=50%'>";
   echo "<table>";
   if ( is_array($array) )
   {
       foreach($array as $f_name=>$f_value) 
       {
           echo "<tr><td valign='top'><div align='right'>";
           if ( !is_numeric($f_name) )
           {
               echo $f_name;
           }
           if ( !is_numeric($f_name) )
           {
               echo "&nbsp;:</div></td><td valign='top'><div align='left'>&nbsp;";
           }    
           if(is_array($f_value)) 
           {
               printf_r_nonum($f_value);
           } 
           else 
           {
               if ( !is_numeric($f_name) )
               {
                   echo $f_value;
               }
           }
           echo "</div></td></tr>";
       }
   }
   echo "</table>";
   echo "</FONT>";
}

function just_numbers($input_string)
{
    $str_length = strlen($input_string);
    for ( $x = 0; $x < $str_length; ++$x )
    {
        if ( !strstr("0123456789",$input_string[$x]) )
            return false;
    }
    return true;
}

function sprintf_r($array) {
   ksort($array);
   reset($array);
   //$strout .= "<FONT FACE='Courier New' SIZE=50%'>";
   $strout .= "<table>";
   foreach($array as $f_name=>$f_value) {
       $strout .= "<tr><td valign='top'><div align='right'>";
           if(!$f_value) { $strout .= "<font color='red'>"; }
               $strout .= $f_name;
           if(!$f_value) { $strout .= "</font>"; }
       $strout .= "&nbsp;:</div></td><td valign='top'><div align='left'>&nbsp;";

       if(is_array($f_value)) {
           $strout .= sprintf_r($f_value);
       } else {
           $strout .= $f_value;
       }
       $strout .= "</div></td></tr>";
   }
   $strout .= "</table>";
   //$strout .= "</FONT>";
   return $strout;
}

function sprint_r($array,$levels_deep=0) 
{
    $strout = '';
    $tabover = '';
   if ( is_array($array) )
   {
       ksort($array);
       reset($array);
   }
   for ( $i = 0; $i < $levels_deep; ++$i )
       $tabover .= ' ';
   if ( is_array($array) )
   {
       foreach($array as $f_name=>$f_value) 
       {
           $strout .= "\n".$tabover.$f_name.'=';
           if( is_array($f_value) ) 
           {
               $strout .= sprint_r( $f_value, ++$levels_deep );
           } 
           else 
           {
               if ( is_object($f_value) )
                   $strout .= serialize($f_value);
               else
                   $strout .= $f_value;
           }
       }
   }
   return $strout;
}

function implode_r($array,$levels_deep=0) 
{
   if ( is_array($array) )
   {
       ksort($array);
       reset($array);
   }
   for ( $i = 0; $i < $levels_deep; ++$i )
       $tabover .= ' ';
   if ( is_array($array) )
   {
       foreach($array as $f_name=>$f_value) 
       {
           if( is_array($f_value) ) 
           {
               $strout .= sprint_r( $f_value, ++$levels_deep );
           } 
           else 
           {
               $strout .= "&".$tabover.$f_name.'=';
               $strout .= $f_value;
           }
       }
   }
   return $strout;
}

function merge_array( $array1, $array2 )
{
    return merge_arrays( $array1, $array2 );
}

function merge_arrays( $array1, $array2, $directive = '' )
{
    if ( strstr($directive,'NO_BLANKS') )
        $no_blanks_flag = true;
    else
        $no_blanks_flag = false;
    if ( strstr($directive,'KEEP_KEYS') )
        $keep_keys_flag = true;
    else
        $keep_keys_flag = false;

    if ( !is_array($array1) )
    {
        if ( $array1 == '' )
            $arrayA[] = '#blank#';
        else
            $arrayA[] = $array1;
    }
    else
        $arrayA = $array1;
    
    if ( !is_array($array2) )
    {
        if ( $array2 == '' )
            $arrayB[] = '#blank#';
        else
            $arrayB[] = $array2;
    }
    else
        $arrayB = $array2;
    
    //$arrayOut = array_merge( $arrayA, $arrayB);  
    foreach ( $arrayA as $key=>$element )
    {
        if ( $no_blanks_flag )
        {
            if ( $keep_keys_flag )
            {
                if ( $element != '#blank#' && $element != '' )
                    $arrayOut[$key] = $element;
            }
            else
            {
                if ( $element != '#blank#' && $element != '' )
                    $arrayOut[] = $element;
            }
        }
        else
        {
            if ( $element != '#blank#' )
            {
                if ( $keep_keys_flag )
                {
                    $arrayOut[$key] = $element;
                }
                else
                {
                    $arrayOut[] = $element;
                }
            }
        }
    }
    foreach ( $arrayB as $key=>$element )
    {
        if ( $no_blanks_flag )
        {
            if ( $keep_keys_flag )
            {
                if ( $element != '#blank#' && $element != '' )
                    $arrayOut[$key] = $element;
            }
            else
            {
                if ( $element != '#blank#' && $element != '' )
                    $arrayOut[] = $element;
            }
        }
        else
        {
            if ( $element != '#blank#' )
            {
                if ( $keep_keys_flag )
                {
                    $arrayOut[$key] = $element;
                }
                else
                {
                    $arrayOut[] = $element;
                }
            }
        }
    }
            
    return $arrayOut;
}

function url_exists($url) 
{
    $a_url = parse_url($url);
    if (!isset($a_url['port'])) 
         $a_url['port'] = 80;
    $errno = 0;
    $errstr = '';
    $timeout = 3;
    if(isset($a_url['host']) && $a_url['host']!=gethostbyname($a_url['host']))
    {
        $fid = fsockopen($a_url['host'], $a_url['port'], $errno, $errstr, $timeout);
        if (!$fid) 
            return false;
        $page = isset($a_url['path'])  ?$a_url['path']:'';
        $page .= isset($a_url['query'])?'?'.$a_url['query']:'';
        fputs($fid, 'HEAD '.$page.' HTTP/1.0'."\r\n".'Host: '.$a_url['host']."\r\n\r\n");
        $head = fread($fid, 4096);
        fclose($fid);
        return preg_match('#^HTTP/.*\s+[200|302]+\s#i', $head);
    } 
    else 
    {
        return false;
    }
}



function push_error_message($error_string)
{
    if ( is_array($_SESSION['error_message']) )
        array_push($_SESSION['error_message'],$error_string);
    else
    {
        $array_value[] = $error_string;
        $_SESSION['error_message'] = $array_value;
    }
}

function push_system_message($system_string)
{
    if ( is_array($_SESSION['system_message']) )
        array_push($_SESSION['system_message'],$system_string);
    else
    {
        $array_value[] = $system_string;
        $_SESSION['system_message'] = $array_value;
    }
}

function array_extract($vals,$keyName)
{
    foreach ( $vals as $key=>$val )
    {
        if ( $key != $keyName )
            $outgoing_array[$key] = $val;
    }
    return $outgoing_array;    
}

function array_strip_empty_values($array)
{
    foreach ( $array as $key=>$array_val )
    {
        if ( $array_val != '' )
            $outgoing_array[$key] = $array_val;
    }
    return $outgoing_array;    
}

function add_to_query($varName, $varVal, $uri=null) 
{
   $result = '';
   $beginning = '';
   $ending = '';
   
   if (is_null($uri)) 
   {   
       //Piece together uri string
       $beginning = $_SERVER['PHP_SELF'];
       $ending = ( isset($_SERVER['QUERY_STRING']) ) ? $_SERVER['QUERY_STRING'] : '';
   } 
   else 
   {
       $qstart = strpos($uri, '?');
       if ($qstart === false) 
       {
           $beginning = $uri; //$ending is '' anyway
       } 
       else 
       {
           $beginning = substr($uri, 0, $qstart);
           $ending = substr($uri, $qstart);
       }
   }
   
   if (strlen($ending) > 0) 
   {
       $vals = array();
       $ending = str_replace('?','', $ending);
       parse_str($ending, $vals);
       $vals[$varName] = $varVal;
       $ending = '';
       $count = 0;
       foreach($vals as $k => $v) 
       {
           if ($count > 0) 
           { 
               $ending .= '&'; 
           }
           else 
           { 
               $count++; 
           }
           $ending .= "$k=" . urlencode($v);
       }
   } 
   else 
   {
       $ending = $varName . '=' . urlencode($varVal);
   }
   
   $result = $beginning . '?' . $ending;
   
   return $result;
} 

function strip_numeric_keys($array_in)
{
    foreach ( $array_in as $key=>$array_value )
    {
        if ( !is_int($key) )
            $array_out[$key] = $array_value;
        
    }   
    //printf_r($array_out);
    return $array_out;
}

function quote_strings($array_in)
{
    foreach ( $array_in as $key=>$array_value )
    {
        if ( is_int($array_value) )
            $array_out[$key] = $array_value;
        else
            $array_out[$key] = '"'.$array_value.'"';
        
    }   
    //printf_r($array_out);
    return $array_out;
}

function year($date_string)
{
    return @date("Y",strtotime($date_string));
}

function dbg($data,$location=null)
{
    if ( $data == 'clear' || $data == 'reset' || !DEBUG_MODE )
        $_SESSION['debug_output'] = '';
    else
    {
        if ( $_SESSION['admin_type'] > '4' )
        {
            $current_debug_output = $_SESSION['debug_output'];
            if ( is_array($data) )
                $more_debug_output = sprintf_r($data);
            else
                $more_debug_output = $data;
            $_SESSION['debug_output'] = $_SESSION['debug_output'].' '.$location.' '.$more_debug_output;
        }
        else
            $_SESSION['debug_output'] = '';
    }
        
}

function days_from_now($date)
{
    $target_date = strtotime(date("F d, Y",strtotime($date)));
    $today = strtotime(date("F d, Y",strtotime('today')));
    $days = ($target_date - $today) / 86400;
    
    if ( $days > 2 )
        $output = $days.' days from now';
    else if ( $days > 0 )
        $output = 'tomorrow';
    else if ( $days == 0 )
        $output = 'today';
    else if ( $days > -2 )
        $output = 'yesterday';
    else if ( $days < -2 )
        $output = -$days.' days ago';
    else
        $output = $days;
    return $output;

}
function friendly_date($date_string)
{
    if ( trim($date_string) != '' )
        return date("F d, Y",strtotime($date_string));
}

function system_date($date_string)
{
    if ( trim($date_string) != '' )
        return date("Y-m-d",strtotime($date_string));
}

function system_timestamp($date_string)
{
    if ( trim($date_string) != '' )
        return date("Y-m-d H:i:s",strtotime($date_string));
}

function current_timestamp()
{
    return date("Y-m-d H:i:s");
}

function RFC2822_timestamp($date_string)
{
    if ( trim($date_string) != '' )
        return date("r",strtotime($date_string));
}

function clear_session_bloat()
{
    $_SESSION = array( 'admin_type'=>$_SESSION['admin_type']
                      ,'auth_time'=>$_SESSION['auth_time']
                      ,'email'=>$_SESSION['email']
                      ,'email_address'=>$_SESSION['email_address']
                      ,'error_message'=>$_SESSION['error_message']
                      ,'id'=>$_SESSION['id']
                      ,'password'=>$_SESSION['password']
                      ,'system_message'=>$_SESSION['system_message'] );
                      
 /*   if ( is_array($_SESSION) )
    {
        foreach ( $_SESSION as $key=>$item )
        {
            if ($key != 'admin_type' 
             && $key != 'auth_time'
             && $key != 'email'
             && $key != 'email_address'
             && $key != 'error_message'
             && $key != 'id'
             && $key != 'password'
             && $key != 'system_message' )
            {
                unset($_SESSION[$key]);
            } 
        }
    }*/
}

/* Use truncate to add ellipses to a string without breaking up words */
function truncate($str, $length, $minword = 3)
{
    $sub = '';
    $len = 0;
    
    foreach (explode(' ', $str) as $word)
    {
        $part = (($sub != '') ? ' ' : '') . $word;
        $sub .= $part;
        $len += strlen($part);
        
        if (strlen($word) > $minword && strlen($sub) >= $length)
        {
            break;
        }
    }
    
    return $sub . (($len < strlen($str)) ? '...' : '');
}

function formspecialchars($var) 
{ 
    $pattern = '/&(#)?[a-zA-Z0-9]{0,};/'; 
    
    if (is_array($var))     // If variable is an array 
    {
        $out = array();      // Set output as an array 
        foreach ($var as $key => $v) 
        {      
            $out[$key] = formspecialchars($v);         // Run formspecialchars on every element of the array and return the result. Also maintains the keys. 
        } 
    } 
    else 
    { 
        $out = $var; 
        while (preg_match($pattern,$out) > 0) 
        { 
            $out = htmlspecialchars_decode($out,ENT_QUOTES);       
        }                             
        $out = htmlspecialchars(stripslashes(trim($out)), ENT_QUOTES,'UTF-8',true);     // Trim the variable, strip all slashes, and encode it 
        
    } 
    
    return $out; 
} 

function cycle_system_messages($system_message='',$error_message='')
{
    global $smarty;
    
    if ( $system_message != '' )
    {
        $system_message = merge_arrays( $_SESSION['system_message'], $system_message );
        if ( $smarty )
            $smarty->assign('system_message',$system_message);
        $_SESSION['system_message'] = '';
    }
    if ( $error_message )
    {
        $error_message = merge_arrays( $_SESSION['error_message'], $error_message );
        if ( $smarty )
            $smarty->assign('error_message',$error_message);
        $_SESSION['error_message']  = '';
    }
}
?>