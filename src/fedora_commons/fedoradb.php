<?php
include_once("config_fedora.inc");

function fedoradb_mysql_connect ()
{
    //global $db_primary_database,FEDORA_DATABASE_HOST,FEDORA_DATABASE_USERNAME,FEDORA_DATABASE_PASSWORD;

	$conn_id = @mysql_connect (FEDORA_DATABASE_HOST, FEDORA_DATABASE_USERNAME, FEDORA_DATABASE_PASSWORD);
	if (!$conn_id)
	{
		if (@mysql_errno())
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." server. Error:".@mysql_error()."<br>Please contact the system administrator at ".FEDORA_ADMINISTRATOR),'error');
		}
		else
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
		}
	}
	if (!@mysql_select_db (FEDORA_DATABASE))
	{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
	}
	return ($conn_id);
}

function fedoradb_mysqli_connect ()
{
    //global $db_primary_database,FEDORA_DATABASE_HOST,FEDORA_DATABASE_USERNAME,FEDORA_DATABASE_PASSWORD;

	$conn_id = @mysqli_connect (FEDORA_DATABASE_HOST, FEDORA_DATABASE_USERNAME, FEDORA_DATABASE_PASSWORD);
	if (!$conn_id)
	{
		if (@mysqli_errno ($conn_id))
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." server. Error:".@mysqli_errno()."<br>Please contact the system administrator at ".FEDORA_ADMINISTRATOR),'error');
		}
		else
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
		}
	}
	if (!@mysqli_select_db ($conn_id, FEDORA_DATABASE))
	{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
	}
	return ($conn_id);
}

function user_mysqli_connect()
{
    //global $db_primary_database,FEDORA_DATABASE_HOST,FEDORA_DATABASE_USERNAME,FEDORA_DATABASE_PASSWORD,$db_user_database;

	$conn_id = @mysqli_connect (FEDORA_DATABASE_HOST, FEDORA_DATABASE_USERNAME, FEDORA_DATABASE_PASSWORD);
	if (!$conn_id)
	{
		if (@mysqli_error ())
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." server. Error:".@mysqli_error()."<br>Please contact the system administrator at ".FEDORA_ADMINISTRATOR),'error');
		}
		else
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
		}
	}
	if (!@mysqli_select_db ($conn_id, USER_DATABASE))
	{
	      drupal_set_message(t("Unable to connect to the ".USER_DATABASE." database."),'error');
	}
	return ($conn_id);
}

function user_mysql_connect ()
{
    //global $db_primary_database,FEDORA_DATABASE_HOST,FEDORA_DATABASE_USERNAME,FEDORA_DATABASE_PASSWORD,$db_user_database;

	$conn_id = @mysql_connect (FEDORA_DATABASE_HOST, FEDORA_DATABASE_USERNAME, FEDORA_DATABASE_PASSWORD);
	if (!$conn_id)
	{
		# If mysql_errno()/mysql_error() work for failed connections, use
		# them (invoke with no argument). Otherwise, use $php_errormsg.
		if (@mysql_errno ())
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." server. Error:".@mysql_errno()."<br>Please contact the system administrator at ".FEDORA_ADMINISTRATOR),'error');
		}
		else
		{
	      drupal_set_message(t("Unable to connect to the ".FEDORA_DATABASE_HOST." database."),'error');
		}
	}
	if (!@mysql_select_db (USER_DATABASE))
	{
	      drupal_set_message(t("Unable to connect to the ".$db_user_database." database."),'error');
		//		mysql_errno ($conn_id)));
	}
	return ($conn_id);
}

function get_data_rows($query)
{
    $conn = fedoradb_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
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

function get_data_row($query)
{
    $conn = fedoradb_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
    	    //var_dump(debug_backtrace());
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
        }
    	else
    	{
    		$row = mysqli_fetch_array ($result);
    		return $row;
    	}
    }
}

function run_query($query)
{
    $conn = fedoradb_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
            return false;
    	}
    	else
    		return true;
    }
}

function run_insert_query($query)
{
    $conn = fedoradb_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result || $conn->error != '' )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
       }
    	else
    	{
    	    if ( $conn->insert_id != 0 )
          		return $conn->insert_id;
            else
                return true;
        }
        return 0;
    }
}
function run_delete_query($delete)
{
    $conn = fedoradb_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($delete);
    	$result = $conn->query($delete);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
        }
    	else
    	{
      		return $conn->affected_rows;
        }
    }
}

function get_user_data_rows($query)
{
    $conn = user_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
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

function get_user_data_row($query)
{
    $conn = user_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
    	    //var_dump(debug_backtrace());
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
        }
    	else
    	{
    		$row = mysqli_fetch_array ($result);
    		return $row;
    	}
    }
}

function run_user_query($query)
{
    $conn = user_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
            return false;
    	}
    	else
    		return true;
    }
}

function run_user_insert_query($query)
{
    $conn = user_mysqli_connect ();

    if ( $conn )
    {
        if ( $_GET['debug'] != '' || $_POST['debug'] != '' || $_SESSION['debug'] != '' )
            log_to_db($query);
    	$result = $conn->query($query);
    	if ( !$result )
    	{
    	    $trace_data = debug_backtrace();
	        drupal_set_message(t("Unable to execute query: $query\n".$conn->error),'error');
        }
    	else
    	{
      		return $conn->insert_id;
        }
    }
}

function escape_values($array)
{
    if ( is_array($array) )
    {
        foreach( $array as &$array_value )
        {
            if ( is_array($array_value) )
                $array_value = escape_values($array_value);
            else
            {
                if (strstr($array_value,"\'") || strstr($array_value,'\"') || strstr($array_value,"\\\\"))
                    $array_value = strip_tags(addslashes(stripslashes($array_value)));
                else
                  $array_value = strip_tags(addslashes($array_value));
            }
        }
    }
    else
    {
                if (strstr($array,"\'") || strstr($array,'\"') || strstr($array,"\\\\"))
                    $array = strip_tags(addslashes(stripslashes($array)));
                else
                  $array = strip_tags(addslashes($array));
    }

    return $array;
}

?>