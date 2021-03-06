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

$selection = $_POST['select'];

switch ($_POST['selectAction'])
{
  // DELETE PROJECTS
  case 'delete_projects':
  {
    if (!isset($_POST['confirm_deletion']))
    {
      array_push($page['errors'], 'For security reasons you must confirm the deletion.');
    }
    else
    {
      // delete projects from user infos
      $query = '
DELETE FROM '.USER_PROJECTS_TABLE.'
  WHERE project IN("'.implode('","', $selection).'")
;';
      $db->query($query);
      
      // delete directories
      foreach ($selection as $project)
      {
        @rrmdir($conf['local_dir'].$project);
      }
      
      // delete from stats table
      $query = '
DELETE FROM '.STATS_TABLE.'
  WHERE project IN("'.implode('","', $selection).'")
;';
      $db->query($query);
      
      // delete from projects table
      $query = '
DELETE FROM '.PROJECTS_TABLE.' 
  WHERE id IN("'.implode('","', $selection).'")
;';
      $db->query($query);
      
      array_push($page['infos'], '<b>'.$db->affected_rows.'</b> projects deleted.');
    }
    break;
  }
  
  // REFRESH STATS
  case 'make_stats':
  {
    foreach ($selection as $project)
    {
      make_project_stats($project);
    }
    
    array_push($page['infos'], 'Stats refreshed for <b>'.count($selection).'</b> projects.');
    break;
  }
    
  // CHANGE RANK
  case 'change_rank':
  {
    if (!is_numeric(@$_POST['batch_rank']) or @$_POST['batch_rank'] < 1)
    {
      array_push($errors, 'Rank must be an non null integer for project &laquo;'.$project_id.'&raquo;.');
    }
    else
    {
      $query = '
UPDATE '.PROJECTS_TABLE.'
  SET rank = '.$_POST['batch_rank'].'
  WHERE id IN("'.implode('","', $selection).'")
;';
      $db->query($query);
      
      array_push($page['infos'], 'Rank changed for <b>'.$db->affected_rows.'</b> projects.');
    }
    break;
  }
  
  // CHANGE CATEGORY
  case 'change_category':
  {
    if ( !empty($_POST['batch_category_id']) and !is_numeric($_POST['batch_category_id']) )
    {
      $_POST['batch_category_id'] = add_category($_POST['batch_category_id'], 'project');
    }
    if (empty($_POST['batch_category_id']))
    {
      $_POST['batch_category_id'] = 0;
    }
    
    $query = '
UPDATE '.PROJECTS_TABLE.'
  SET category_id = '.$_POST['batch_category_id'].'
  WHERE id IN("'.implode('","', $selection).'")
;';
    $db->query($query);
    
    array_push($page['infos'], 'Category changed for <b>'.$db->affected_rows.'</b> projects.');
    break;
  }
}

?>