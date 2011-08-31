<?php
/*
* Smarty plugin
* ————————————————————-
* File:     function.recurse_messages.php
* Type:     function
* Name:     recurse_messages
* Purpose:  prints out elements of an array recursively
* ————————————————————-
*/

function smarty_function_recurse_messages($params, &$smarty)
{

    if (is_array($params['array']) && count($params['array']) > 0) 
    {
       $markup = '';
       if ( $params['array'][0]['depth'] == '' )
           $markup .= "\n<ul class=tree>";
       else
           $markup .= "\n<ul>";
    
       foreach ($params['array'] as $element) 
       {
            $indent = '';
            for($i = 0; $i < $element['depth']; ++$i)
                $indent .= "    ";
          $markup .= "\n<li>".$indent;
          $markup .= $element['message'].' -  <font size=1>'.$element['screen_name'].' '.days_from_now($element['timestamp']).'</font>';  
          if ( $element['from_id'] != $_SESSION['id'] && $_SESSION['id'] != '' && !$element['read_only'] )
              $markup .= ' <font size=1><a href="message.php?msg='.$element['id'].'">respond to this user</a></font>';
          if ( $_SESSION['admin_type'] > '1')
              $markup .= ' <font size=1><a href="message_list.php?manage='.$element['id'].'">manage</a></font>';
          /*$markup .= '<h1>' . $element['timestamp'] . '</h1>';
          $markup .= '<p>' . $element['message'] . '</p>';*/
    
          if (isset($element['messages'])) 
          {
             $markup .= smarty_function_recurse_messages(array('array' => $element['messages']), $smarty);
          }
    
           $markup .= "\n</li>";
       }
    
       $markup.= "\n</ul>";
    
       return $markup;
    
    } 
    else 
    {
       return 'not array';
    }
}
