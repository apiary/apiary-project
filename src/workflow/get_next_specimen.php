<?php

if ( $_GET['current_specimen'] == '00000001-C5BE-11DF-AD1A-7377DFD72085' )
    $next_specimen = '00000002-C5BE-11DF-AD1A-7377DFD72085';
else if ( $_GET['current_specimen'] == '00000002-C5BE-11DF-AD1A-7377DFD72085' )
    $next_specimen = '00000003-C5BE-11DF-AD1A-7377DFD72085';
else if ( $_GET['current_specimen'] == '00000003-C5BE-11DF-AD1A-7377DFD72085' )
    $next_specimen = '00000004-C5BE-11DF-AD1A-7377DFD72085';
else if ( $_GET['current_specimen'] == '00000004-C5BE-11DF-AD1A-7377DFD72085' )
    $next_specimen = '00000005-C5BE-11DF-AD1A-7377DFD72085';
else if ( $_GET['current_specimen'] == '00000005-C5BE-11DF-AD1A-7377DFD72085' )
    $next_specimen = '';
    
echo $next_specimen;    
 

?>