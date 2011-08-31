<?php
if ( function_exists('drupal_get_path') )
{
    $rel_path = drupal_get_path('module','apiary_project');
    include_once($rel_path."/workflow/include/functions_misc.php");
    include_once($rel_path."/workflow/include/class.Errorlog.php");
    include_once($rel_path."/workflow/include/config.inc.php");
}
else
{
    include_once("include/functions_misc.php");
    include_once("include/class.Errorlog.php");
    include_once("include/config.inc.php");
}

define("LOG_TO_FILE", 0x01 );
define("LOG_TO_DATABASE", 0x02 );
define("LOG_TO_DISPLAY", 0x04 );
define("LOG_TO_ALL", 0x07 );

function log_error( $error_string, $log_action='', $display_error='', $error_type='',$environment_string='' ) 
{
    if ( $log_action & LOG_TO_DATABASE )
    {
        $input_row['error_string'] = htmlentities($error_string, ENT_QUOTES);
        $input_row['error_type'] = $error_type;

        if ( $environment_string == '' )
            $input_row['environment_string'] = htmlentities(sprint_r($_ENV).' '.sprint_r($_SESSION), ENT_QUOTES);
        else
            $input_row['environment_string'] = $environment_string;

        Errorlog::insert($input_row);
        
    }
    if ( $log_action & LOG_TO_FILE )
    {
       if (!$handle = fopen(LOG_FILE, 'a')) 
       {
             echo 'Cannot open file ('.LOG_FILE.')';
             exit;
       }
       $timestamp = @date("YmdHis");
       if (fwrite($handle, "\n[".$timestamp.'] '.$error_string) === FALSE) 
       {
           echo 'Cannot write to file ('.LOG_FILE.')';
           exit;
       }
      
       fclose($handle);
        
    }
    if ( $log_action & LOG_TO_DISPLAY )
    {
        $error_message[] = $display_error;
    }
    
    if ( is_array($error_message) )     
        save_to_session($error_message);
}

function log_to_db( $error_string, $environment_string = null ) 
{
    //if ( DEBUG_MODE )
    {
        if ( is_object($error_string) )
        {
            ob_start();
            var_dump(get_object_vars($error_string));
            $output = ob_get_flush();
            $input_row['error_string'] = 'OBJECT:'.htmlentities($output);
        }
        else if ( is_array($error_string) )
            $input_row['error_string'] = htmlentities(sprint_r($error_string), ENT_QUOTES);
        else
            $input_row['error_string'] = htmlentities($error_string, ENT_QUOTES);
        if ( $environment_string == '' )    
        {
            $environment_string_temp = str_replace("\\","/",sprint_r(debug_backtrace()).' '.sprint_r($_SESSION) );
            $input_row['environment_string'] = htmlentities($environment_string_temp, ENT_QUOTES);
        }
        else
            $input_row['environment_string'] = $environment_string.' '.str_replace("\\","/",sprint_r(debug_backtrace()));
        Errorlog::insert($input_row);
    }
}


function log_to_file( $error_string ) 
{
   if (!$handle = fopen(LOG_FILE, 'a')) 
   {
         echo 'Cannot open file ('.LOG_FILE.')';
         exit;
   }
   $timestamp = @date("YmdHis");
   if (fwrite($handle, "\n[".$timestamp.'] '.$error_string) === FALSE) 
   {
       echo 'Cannot write to file ('.LOG_FILE.')';
       exit;
   }
  
   fclose($handle);
    
}

function save_to_session($error_message)
{
    //global $_SESSION;
    if ( is_array($_SESSION['error_message']) )
    {
        if ( is_array($error_message) )
        {
            foreach ( $error_message as $msg )
            {
                if ( array_search( $msg, $_SESSION['error_message'] ) === FALSE )
                    $_SESSION['error_message'][] = $msg;
            }    
        }
        else
        {
            if ( $error_message != '' )
                if ( array_search( $error_message, $_SESSION['error_message'] ) === FALSE )
                    $_SESSION['error_message'][] = $error_message;
        }
    }
    else
    {
        if ( $error_message != '' )
            $_SESSION['error_message'] = $error_message;
    }
}
?>