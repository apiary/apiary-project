<?php
/*
* Smarty plugin
* 覧覧覧覧覧覧覧覧覧覧-
* File:     function.floor.php
* Type:     function
* Name:     floor
* Purpose:  return php floor()
* 覧覧覧覧覧覧覧覧覧覧-
*/

function smarty_function_floor($params, &$smarty)
{
    return floor($params['param']);
}
