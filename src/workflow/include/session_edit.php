<?php
# session_edit.php will allow the user to edit session data
# $Revision: 1903 $

include_once("include/functions_error.php");
include_once("include/functions_misc.php");


require('Smarty.class.php');

class Smarty2 extends Smarty 
{
    // $cache and $cache_lifetime are the two main variables
    // that control caching within Smarty
    function Smarty2()
    {
        // Run Smarty's constructor
        $this->Smarty();
    }
    
    function compile_string_template($templatestring)
    {              
        if (!isset($templatestring)) 
        {
            $this->trigger_error("compile_string_template: missing 'templatestring' parameter");
            return;
        }
        if($templatestring == '') 
        {
            return "";
        }
         
        $this->_compile_source("string template", $templatestring, $source);
        //Dump compiled version in output buffer
        ob_start();
        eval('?>' . $source);
        $evaled = ob_get_contents();
        ob_end_clean();
        return $evaled;
    }
} 

$smarty = new Smarty2;

session_start(); 
/*if ( $_POST['form_action'] == 'submit' )
{
    printf_r($_POST);
    foreach( $_POST as $key=>$post_item )
    {
        echo("<br> $key=$post_item");
        if ( $key != 'form_action' && !is_array($post_item) )
            $_SESSION[$key] = $post_item;
    }
}*/

if ( $_POST['form_action'] == 'submit' )
{
    //printf_r($_POST);
    foreach( $_POST as $key=>$post_item )
    {
        //echo("<br> $key=$post_item");
        if ( $key != 'form_action' && $key != 'new_variable' && $key != 'new_value' && !is_array($post_item) )
            $_SESSION[$key] = $post_item;
    }
    if ( $_POST['new_variable'] != '' )
        $_SESSION[$_POST['new_variable']] = $_POST['new_value'];
}

$input_fields = generate_html_input_r($_SESSION);
$input_fields = $smarty->compile_string_template($input_fields);
$smarty->assign('input_fields',$input_fields);

$system_message = merge_arrays( $_SESSION['system_message'], $system_message );
$error_message = merge_arrays( $_SESSION['error_message'], $error_message );
$smarty->assign('system_message',$system_message);
$smarty->assign('error_message',$error_message);
$_SESSION['system_message'] = '';
$_SESSION['error_message']  = '';


$smarty->display('session_edit.tpl.html');

function generate_html_input_r($array) 
{
   global $smarty;
   ksort($array);
   reset($array);
   /* $strout .= "<font size=2>"; */
   $strout .= "<table>";
   foreach($array as $f_name=>$f_value) {
       $strout .= "<tr><td valign='top'><div align='right'>";
           if(!$f_value) { $strout .= "<span style='color: red;'>"; }
               $strout .= $f_name;
           if(!$f_value) { $strout .= "</span>"; }
       $strout .= "&nbsp;:</div></td><td valign='top'><div align='left'>&nbsp;";

       if(is_array($f_value)) {
           $strout .= generate_html_input_r($f_value);
       } else {
           $strout .= "<input name=\"{$f_name}\" type=\"text\" value=\"{$f_value}\"/>";
       }
       $strout .= "</div></td></tr>";
   }
   $strout .= "<tr>
                 <td><input name=\"new_variable\" type=\"text\" value=\"\"/></td>
                 <td><input name=\"new_value\" type=\"text\" value=\"\"/></td>
               </tr>";
   $strout .= "</table>";
   /* $strout .= "</FONT>"; */
   return $strout;
}


?>


