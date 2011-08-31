<?php
include_once("include/functions_misc.php");
session_start(); 

//if ( $_SESSION['admin_type'] >= 5 )
if ( true )
{
    echo "\n<br>SESSION VARIABLES:\n<br>";
    printf_r($_SESSION);
    echo "\n<br>COOKIES:\n<br>";
    printf_r($_COOKIE);
    echo "\n<br>HTTP_GET_VARS:\n<br>";
    printf_r($_GET);
    echo "\n<br>HTTP_POST_VARS:\n<br>";
    printf_r($_POST);
}
else
{
    header("Location: login.php");
    exit(0);
}
?>


