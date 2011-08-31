<?php
# include/db_utils.php - library file with utility routine for connecting to MySQL
# $Revision$
if ( function_exists('drupal_get_path') )
{
    $rel_path = drupal_get_path('module','apiary_project');
    include_once($rel_path."/workflow/include/functions_error.php");
    include_once($rel_path."/workflow/include/config.inc.php");
}
else
{
    include_once("include/functions_error.php");
    include_once("include/config.inc.php");
}

function db_mysql_connect ()
{
    
	$conn_id = @mysql_connect (DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
	if (!$conn_id)
	{
		if (@mysql_errno ())
		{
            log_error("Unable to connect to the ".DATABASE_HOST." server. Error:".@mysql_error (),LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
            log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
		}
		else
		{
            log_error("Unable to connect to the ".DATABASE_HOST." database.",LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
            log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
		}
	}
	if (!@mysql_select_db (DATABASE))
	{
        log_error("Unable to select ".DATABASE." database.",LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
        log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
	}
	return ($conn_id);
}

function db_mysqli_connect ()
{
    
	$conn_id = @mysqli_connect (DATABASE_HOST, DATABASE_USERNAME, DATABASE_PASSWORD);
	if (!$conn_id)
	{
	    echo "database connection error";
		if (@mysqli_errno ($conn_id))
		{
            log_error("Unable to connect to the ".DATABASE_HOST." server. Error:".@mysqli_error (),LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
            log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
		}
		else
		{
            log_error("Unable to connect to the ".DATABASE_HOST." database.",LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
            log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
		}
	}
	if (!@mysqli_select_db ($conn_id, DATABASE))
	{
        log_error("Unable to select ".DATABASE." database.",LOG_TO_FILE|LOG_TO_DISPLAY,"Unable to connect to the ".DATABASE_HOST." database.","critical");
        log_error("Please contact the system administrator at ".ADMINISTRATOR.".",LOG_TO_DISPLAY,"Please contact the system administrator at ".ADMINISTRATOR.".","critical");
	}
	return ($conn_id);
}

function get_rows($query)
{
    $conn = db_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' ) 
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
            log_error("Unable to execute query: $query\n".$conn->error,LOG_TO_ALL,"Unable to obtain data.",$trace_data['file'].':'.$trace_data['function'].':'.$trace_data['line']);
    	}
    	else
    	{
    		while ( $row = mysqli_fetch_array ($result) )
    		{
    			$data_rows[] = $row;
    		}
    		return $data_rows;
    	}
    }    
}

function get_row($query)
{
    $conn = db_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' ) 
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
            log_error("Unable to execute query: $query\n".$conn->error,LOG_TO_ALL,"Unable to obtain data.",$trace_data['file'].':'.$trace_data['function'].':'.$trace_data['line']);
    	}
    	else
    	{
    		$row = mysqli_fetch_array ($result);
    		return $row;
    	}
    }    
}

function run($query)
{
    $conn = db_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' ) 
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
            log_error("Unable to execute query: $query\n".$conn->error,LOG_TO_ALL,"Unable to execute query.",$trace_data['file'].':'.$trace_data['function'].':'.$trace_data['line']);
            return false;
    	}
    	else
    		return true;
    }    
}

function run_insert($query)
{
    $conn = db_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' ) 
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
            log_error("Unable to execute query: $query\n".$conn->error,LOG_TO_ALL,"Unable to execute query.",$trace_data['file'].':'.$trace_data['function'].':'.$trace_data['line']);
    	}
    	else
    	{
      		return true;
        }
    }    
}



function clean_single_quotes($array)
{
    if ( is_array($array) )
    {
        foreach( $array as &$array_value )
        {
            if ( is_array($array_value) )
                $array_value = clean_single_quotes($array_value);
            else
            {
                $array_value = str_replace("&lt;","<",$array_value);
                $array_value = str_replace("&gt;",">",$array_value);
                $array_value = str_replace("'","&#039;",$array_value);
                //$array_value = addslashes($array_value);
            }
        }
    }
    else
    {
        $array_value = str_replace("&lt;","<",$array_value);
        $array_value = str_replace("&gt;",">",$array_value);
        $array = str_replace("'","&#039;",$array);
    }

    return $array;
}

function clean_quotes($array)
{
    if ( is_array($array) )
    {
        foreach( $array as &$array_value )
        {
            if ( is_array($array_value) )
                $array_value = clean_quotes($array_value);
            else
            {
                $array_value = str_replace("&lt;","<",$array_value);
                $array_value = str_replace("&gt;",">",$array_value);
                $array_value = str_replace("'","&#039;",$array_value);
                $array_value = str_replace("\"","&#034;",$array_value);
                //$array_value = addslashes($array_value);
            }
        }
    }
    else
    {
        $array_value = str_replace("&lt;","<",$array_value);
        $array_value = str_replace("&gt;",">",$array_value);
        $array = str_replace("'","&#039;",$array);
        $array = str_replace("\"","&#034;",$array);
    }

    return $array;
}

?>