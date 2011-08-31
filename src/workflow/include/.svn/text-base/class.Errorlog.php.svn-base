<?php
// class.Errorlog.php
// $Revision: 2929 $
if ( function_exists('drupal_get_path') )
{
    $rel_path = drupal_get_path('module','apiary_project');
    include_once($rel_path."/workflow/include/functions_error.php");
    include_once($rel_path."/workflow/include/config.inc.php");
    include_once($rel_path."/workflow/include/db_utils.php");
}
else
{
    include_once("include/functions_error.php");
    include_once("include/config.inc.php");
    include_once("include/db_utils.php");
}

class Errorlog
{
    public $found_rows;
    
    function getList($status)
    {
        $conn = db_mysqli_connect();
        if ( $status == 'processed' )
        {
        	$query = "select errorlog_id,         
        	                 errorlog_timestamp,  
        	                 errorlog_type,       
        	                 errorlog_string,     
        	                 errorlog_status,     
        	                 errorlog_environment
        	                 from errorlog
        	                 where errorlog_status = '$status' 
        	                 order by errorlog_id DESC";
        }
        else
        {
        	$query = "select errorlog_id,         
        	                 errorlog_timestamp,  
        	                 errorlog_type,       
        	                 errorlog_string,     
        	                 errorlog_status,     
        	                 errorlog_environment,
        	                 from errorlog
        	                 order by errorlog_id DESC";
        }	                 
        $result = $conn->query($query);
         
        if ( !$result )
        {
        	log_error( "<br>error in query=$query<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to retrieve information from the error log database." );
        }
        else
        {
        	while ( $row = mysqli_fetch_array ($result))
        	{
        		$errorlog_rows[] = $row;
        	}
            return $errorlog_rows;
        	mysqli_free_result ($result);
        }
        mysqli_close ($conn);
    }
    
    function getUnprocessedEntries()
    {
        $conn = db_mysqli_connect();
    	$query = "select errorlog_id,         
    	                 errorlog_timestamp,  
    	                 errorlog_type,       
    	                 errorlog_string,     
    	                 errorlog_status,     
    	                 errorlog_environment
    	                 from errorlog
    	                 where errorlog_status = 'new' 
    	                 order by errorlog_id DESC";
        $result = $conn->query($query);
         
        if ( !$result )
        {
        	log_error( "<br>error in query=$query<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to retrieve information from the error log database." );
        }
        else
        {
        	while ( $row = mysqli_fetch_array ($result))
        	{
        		$errorlog_rows[] = $row;
        	}
            return $errorlog_rows;
        	mysqli_free_result ($result);
        }
        mysqli_close ($conn);
    }
    function getCriticalEntries()
    {
        $conn = db_mysqli_connect();
    	$query = "select errorlog_id,         
    	                 errorlog_timestamp,  
    	                 errorlog_type,       
    	                 errorlog_string,     
    	                 errorlog_status,     
    	                 errorlog_environment
    	                 from errorlog
    	                 where errorlog_status = 'new' 
    	                 and errorlog_type = 'critical'
    	                 order by errorlog_id DESC";
        $result = $conn->query($query);
         
        if ( !$result )
        {
        	log_error( "<br>error in query=$query<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to retrieve information from the error log database." );
        }
        else
        {
        	while ( $row = mysqli_fetch_array ($result))
        	{
        		$errorlog_rows[] = $row;
        	}
            return $errorlog_rows;
        	mysqli_free_result ($result);
        }
        mysqli_close ($conn);
    }
    
    function setEntriesAsProcessed()
    {
        $conn = db_mysqli_connect();
    	$update = "update errorlog set         
    	                 errorlog_status = CURRENT_TIMESTAMP
    	                 where errorlog_status = 'new'
    	                 and errorlog_type <> 'critical'";
        $result = $conn->query($update);
         
        if ( !$result )
        {
        	log_error( "<br>error in update=$update<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to update the error log records." );
        	return false;
        }
        else
        {
            return true;
        }
        mysqli_close ($conn);
    }
    function setCriticalEntriesAsProcessed()
    {
        $conn = db_mysqli_connect();
    	$update = "update errorlog set         
    	                 errorlog_status = CURRENT_TIMESTAMP
    	                 where errorlog_status = 'new'
    	                 and errorlog_type = 'critical'";
        $result = $conn->query($update);
         
        if ( !$result )
        {
        	log_error( "<br>error in update=$update<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to update the error log records." );
        	return false;
        }
        else
        {
            return true;
        }
        mysqli_close ($conn);
    }
    
    function get( $id = '', $text = '' )
    {
        $conn = db_mysqli_connect ();

        if ( is_array($id) )
        {
            $ids = implode(',',$id);
        	$query = "select SQL_CALC_FOUND_ROWS
        	                 errorlog_id,         
        	                 errorlog_timestamp,  
        	                 errorlog_type,       
        	                 errorlog_string,     
        	                 errorlog_status,     
        	                 errorlog_environment,
        	                 from errorlog
        	                 where errorlog_id = in ( $ids ) 
        	                 order by errorlog_id DESC";
        }
        else
        {
            if ( $id != 'last_id' )
            {
                $words = explode(' ',trim($text));
            	$query = "select SQL_CALC_FOUND_ROWS
            	                 errorlog_id,         
            	                 errorlog_timestamp,  
            	                 errorlog_type,       
            	                 errorlog_string,     
            	                 errorlog_status,     
            	                 errorlog_environment
            	                 from errorlog";
                if ( $id != 'all' && $id != '' && $id != '0' )
                {
                    $query .= "\nwhere errorlog_id = '$id'";
                }
                if ( is_array($words) != ''  )
                {
                    if ( $id == '' )
                        $query .= "\nwhere 1=1";
                    foreach($words as $word)
                    {
                        $string = trim($word);
                        if ( $string != '' )
                        {
                            $query .= "\nand ( errorlog.errorlog_string like '%{$string}%'
                                           or errorlog.errorlog_status like '%{$string}%'
                                           or errorlog.errorlog_environment like '%{$string}%'
                                            )";    
                        }                	 
                    }
                }                    	 
                $query .= "\norder by errorlog_id DESC";
            }
            else
            {            
             	$query = "select SQL_CALC_FOUND_ROWS
            	                 errorlog_id,         
            	                 errorlog_timestamp,  
            	                 errorlog_type,       
            	                 errorlog_string,     
            	                 errorlog_status,     
            	                 errorlog_environment
            	                 from errorlog
            	                 where errorlog_id = ( select max(errorlog_id) from errorlog )";
            }
       }
        if ( $this->page_control_number == 0 )
            $this->page_control_number = 50;
        if ( $this->num == '' )
            $this->num = $this->page_control_number;
        if ( $this->start == '' )
            $this->start = 0;

      	$query_sorted = $query;

        if ( $this->num != -1 )
            $query_paged = $query_sorted." limit $this->start,$this->num";
        else
            $query_paged = $query_sorted;

        if ( $conn )
        {
            if ( $this->paged == TRUE )
            {
                if ( $_GET['debug'] != '' )
                    log_to_db($query_paged);
               
            	$result = $conn->query($query_paged);
            }
            else
            {
                if ( $_GET['debug'] != '' )
                    log_to_db($query_sorted);
            	$result = $conn->query($query_sorted);
                $this->num_rows = $result->num_rows;
            }
        	if ( !$result )
        	{
                if ( $this->paged == TRUE )
            		$query = $query_paged;
                else
            		$query = $query_sorted;
      	        log_error("<br>Unable to query errorlogs:$query",LOG_TO_ALL,"Unable to query errorlogs.",'class.Errorlog');
        	}
        	else
        	{
        		while ( $row = mysqli_fetch_array ($result) )
        		{
        			$result_rows[] = $row;
        		}
        		if ( is_array( $id ) || $id == 'all' || $id == '' || $id == '0' )
        		{
	                $fr_result = $conn->query("SELECT FOUND_ROWS()");
                    $found_rows = mysqli_fetch_row($fr_result);
                    $this->found_rows = $found_rows[0];  

            		return $result_rows;
                }
                else
                    return $result_rows[0];
        	}
        }
    }    
    function _get( $id = '' )
    {
        $conn = db_mysqli_connect ();

        if ( is_array($id) )
        {
            $ids = implode(',',$id);
        	$query = "select SQL_CALC_FOUND_ROWS
        	                 errorlog_id,         
        	                 errorlog_timestamp,  
        	                 errorlog_type,       
        	                 errorlog_string,     
        	                 errorlog_status,     
        	                 errorlog_environment,
        	                 from errorlog
        	                 where errorlog_id = in ( $ids ) 
        	                 order by errorlog_id DESC";
        }
        else
        {
            if ( $id != 'last_id' )
            {
            	$query = "select SQL_CALC_FOUND_ROWS
            	                 errorlog_id,         
            	                 errorlog_timestamp,  
            	                 errorlog_type,       
            	                 errorlog_string,     
            	                 errorlog_status,     
            	                 errorlog_environment
            	                 from errorlog";
                if ( $id != 'all' && $id != '' && $id != '0' )
                {
                    $query .= " where errorlog_id = '$id'";
                }
                $query .= " order by errorlog_id DESC";
            }
            else
            {            
             	$query = "select SQL_CALC_FOUND_ROWS
            	                 errorlog_id,         
            	                 errorlog_timestamp,  
            	                 errorlog_type,       
            	                 errorlog_string,     
            	                 errorlog_status,     
            	                 errorlog_environment
            	                 from errorlog
            	                 where errorlog_id = ( select max(errorlog_id) from errorlog )";
            }
       }
        if ( $this->page_control_number == 0 )
            $this->page_control_number = 50;
        if ( $this->num == '' )
            $this->num = $this->page_control_number;
        if ( $this->start == '' )
            $this->start = 0;

      	$query_sorted = $query;

        if ( $this->num != -1 )
            $query_paged = $query_sorted." limit $this->start,$this->num";
        else
            $query_paged = $query_sorted;

        if ( $conn )
        {
            if ( $this->paged == TRUE )
            {
                if ( $_GET['debug'] != '' )
                    log_to_db($query_paged);
               
            	$result = $conn->query($query_paged);
            }
            else
            {
                if ( $_GET['debug'] != '' )
                    log_to_db($query_sorted);
            	$result = $conn->query($query_sorted);
                $this->num_rows = $result->num_rows;
            }
        	if ( !$result )
        	{
                if ( $this->paged == TRUE )
            		$query = $query_paged;
                else
            		$query = $query_sorted;
      	        log_error("<br>Unable to query errorlogs:$query",LOG_TO_ALL,"Unable to query errorlogs.",'class.Errorlog');
        	}
        	else
        	{
        		while ( $row = mysqli_fetch_array ($result) )
        		{
        			$result_rows[] = $row;
        		}
        		if ( is_array( $id ) || $id == 'all' || $id == '' || $id == '0' )
        		{
	                $fr_result = $conn->query("SELECT FOUND_ROWS()");
                    $found_rows = mysqli_fetch_row($fr_result);
                    $this->found_rows = $found_rows[0];  

            		return $result_rows;
                }
                else
                    return $result_rows[0];
        	}
        }
    }
    
    function getLastInsertID()
    {
        $conn = db_mysqli_connect();
    	$query = "select max(errorlog_id)
    	                 from errorlog";
        $result = $conn->query($query);
         
        if ( !$result )
        {
        	log_error( "<br>error in query=$query<br>", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to retrieve information from the error log database." );
        }
        else
        {
        	$row = mysqli_fetch_array ($result);
        	mysqli_free_result ($result);
            mysqli_close ($conn);
        return $row[0];
        }
        mysqli_close ($conn);
    }
    
    function insert($input_row)
    {
        $error_string = htmlentities($input_row['error_string'], ENT_QUOTES);
        $error_type = htmlentities($input_row['error_type'], ENT_QUOTES);
        $environment_string = htmlentities($input_row['environment_string'], ENT_QUOTES);
        $conn = db_mysqli_connect();
    	
    	$insert = "insert into errorlog
                    	( errorlog_timestamp, 
                    	  errorlog_type, 
                    	  errorlog_string, 
                    	  errorlog_status, 
                    	  errorlog_environment)
                    	values
                    	( CURRENT_TIMESTAMP, 
                    	  '$error_type', 
                    	  '$error_string', 
                    	  'new', 
                    	  '$environment_string' )";
                    	  
    	if ( $conn )
    	{
        	$result = $conn->query($insert);
        	if ( !$result )
        	{
            	//log_error( "<b>ERROR:</b>Unable to insert error record into the database:$insert", LOG_TO_FILE | LOG_TO_DISPLAY, "An error was encountered attempting to save information to the error log database." );
            	//error_log("\n$insert\n", 3, LOG_FILE);
            	log_to_file($insert);
                return false;
        	}
        	mysqli_close ($conn);
        	return true;
        }
        else
        {
            return false;
        }
    }
    
    function clearLog()
    {
        $delete = "delete from errorlog";
        return run_query($delete);
    }
    
        
}

?>