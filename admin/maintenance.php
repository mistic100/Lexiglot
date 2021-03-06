<?php
// +-----------------------------------------------------------------------+
// | Lexiglot - A PHP based translation tool                               |
// +-----------------------------------------------------------------------+
// | Copyright(C) 2011-2013 Damien Sorel       http://www.strangeplanet.fr |
// +-----------------------------------------------------------------------+
// | This program is free software; you can redistribute it and/or modify  |
// | it under the terms of the GNU General Public License as published by  |
// | the Free Software Foundation                                          |
// |                                                                       |
// | This program is distributed in the hope that it will be useful, but   |
// | WITHOUT ANY WARRANTY; without even the implied warranty of            |
// | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU      |
// | General Public License for more details.                              |
// |                                                                       |
// | You should have received a copy of the GNU General Public License     |
// | along with this program; if not, write to the Free Software           |
// | Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, |
// | USA.                                                                  |
// +-----------------------------------------------------------------------+

defined('LEXIGLOT_PATH') or die('Hacking attempt!');


// +-----------------------------------------------------------------------+
// |                         ACTIONS
// +-----------------------------------------------------------------------+
if (!empty($_GET['action']))
{
  switch ($_GET['action'])
  {
    case 'optimize_db' :
    {
      $result = $db->query('SHOW TABLE STATUS FROM '. DB_NAME .';');
      while ($table = $result->fetch_assoc())
      {
        if (strstr($table['Name'], DB_PREFIX) != false)
        {
          $db->query('OPTIMIZE TABLE '.$table['Name'].';');
        }
      }
      array_push($page['infos'], 'Database cleaned');
      break;
    }
    
    case 'make_stats' :
    {
      set_time_limit(3600);
      make_full_stats();
      array_push($page['infos'], 'Statistics updated');
      break;
    }
    
    case 'delete_unused_categories' :
    {
      $query = '
DELETE FROM '.CATEGORIES_TABLE.'
  WHERE 
    id NOT IN (
      SELECT category_id as id
        FROM '.PROJECTS_TABLE.'
        GROUP BY category_id
      )
    AND id NOT IN (
      SELECT category_id as id
        FROM '.LANGUAGES_TABLE.'
        GROUP BY category_id
      )
;';
      $db->query($query);
      array_push($page['infos'], $db->affected_rows.' unused categories deleted');
      break;
    }
    
    case 'delete_done_rows' :
    {
      $query = '
DELETE FROM '.ROWS_TABLE.'
  WHERE status = "done"
;';
      $db->query($query);
      array_push($page['infos'], $db->affected_rows.' commited strings deleted');
      break;
    }
    
    case 'clean_mail_history' :
    {
      $db->query('TRUNCATE TABLE '.MAIL_HISTORY_TABLE.';');
      array_push($page['infos'], 'Mail archive cleared');
      break;
    }
    
    case 'purge_template' :
    {
      $template->delete_compiled_templates();
      array_push($page['infos'], 'Compiled templates cleared');
      break;
    }
  }
}


// +-----------------------------------------------------------------------+
// |                         GET INFOS
// +-----------------------------------------------------------------------+
// database time
list($db_current_date) = $db->query('SELECT NOW();')->fetch_row();

// database space and tables size
$db_tables = array();
$db_size = $db_free = 0;
$result = $db->query('SHOW TABLE STATUS FROM '. DB_NAME .';');
while ($table = $result->fetch_assoc())
{
  if (strstr($table['Name'], DB_PREFIX) != false)
  {
    $db_size += $table['Data_length'] + $table['Index_length'] + $table['Data_free'];
    $db_free += $table['Data_free'];
    $db_tables[ str_replace(DB_PREFIX, null, $table['Name']) ] = $table['Rows'];
  }
}

// unused categories
$query = '
SELECT COUNT(*) as total
  FROM '.CATEGORIES_TABLE.'
  WHERE 
    id NOT IN (
      SELECT category_id as id
        FROM '.PROJECTS_TABLE.'
        GROUP BY category_id
      )
    AND id NOT IN (
      SELECT category_id as id
        FROM '.LANGUAGES_TABLE.'
        GROUP BY category_id
      )
;';
list($nb_unused_categories) = $db->query($query)->fetch_row();


// +-----------------------------------------------------------------------+
// |                         TEMPLATE
// +-----------------------------------------------------------------------+
$template->assign(array(
  'PHP_INFO' => phpversion().' ['.date("Y-m-d H:i:s").']',
  'MYSQL_INFO' => $db->server_info.' ['.$db_current_date.']',
  'DATABASE' => array(
    'used_space' => round($db_size/1024,2),
    'free_space' => round($db_free/1024,2),
    'optimize_uri' => get_url_string(array('action'=>'optimize_db')),
    ),
  'TABLES' => $db_tables,
  'UNUSED_CATS' => $nb_unused_categories,
  'DELETE_UNUSED_CATS_URI' => get_url_string(array('action'=>'delete_unused_categories')),
  'MAKE_STATS_URI' => get_url_string(array('action'=>'make_stats')),
  'DELETE_DONE_ROWS_URI' => get_url_string(array('action'=>'delete_done_rows')),
  'CLEAN_MAIL_URI' => get_url_string(array('action'=>'clean_mail_history')),
  'PURGE_TEMPLATE_URI' => get_url_string(array('action'=>'purge_template')),
  ));


// +-----------------------------------------------------------------------+
// |                         OUTPUT
// +-----------------------------------------------------------------------+
$template->close('admin/maintenance');

?>