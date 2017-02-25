<?php

require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'config.php');

if (network::get('action') != '') {
  switch (network::get('action')) {
    case 'change':
      if (network::get('emulator') != '') {
        cache::setClientVariable($module->id.'_emulator', network::get('emulator'));
        cache::unsetClientVariable($module->id.'_id');
        $output = '';
        if (cache::getClientVariable($module->id.'_filter') != '') {
          $romdata = rom::readAll(network::get('emulator'));
          foreach (emulator::readRomlist(network::get('emulator')) as $rom) {
            $offset = array_search($rom, array_column($romdata, 'id'));
            $data = null;
            if ($offset >= 0) {
              if (isset($romdata[$offset])) {
                $data = $romdata[$offset];
              }
            }
            if (cache::getClientVariable($module->id.'_filter') == 'nodata') {
              if (!isset($data) || isset($data) && !isset($data['fields']['name']) || isset($data['fields']) &&  $data['fields']['name'] == '') {
                $output .= '<option value="'.$rom.'">'.$rom.'</option>';
              }
            } elseif (cache::getClientVariable($module->id.'_filter') == 'noimage') {
              if (!isset($data) || isset($data) && !isset($data['fields']['image']) || isset($data['fields']) &&  $data['fields']['image'] == '') {
                $output .= '<option value="'.$rom.'">'.$rom.'</option>';
              }
            } elseif (cache::getClientVariable($module->id.'_filter') == 'novideo') {
              if (!isset($data) || isset($data['fields']) && !isset($data['fields']['video']) || isset($data['fields']) && $data['fields']['video'] == '') {
                $output .= '<option value="'.$rom.'">'.$rom.'</option>';
              }
            }
          }
        } else {
          foreach (emulator::readRomlist(network::get('emulator')) as $rom) {
            $output .= '<option value="'.$rom.'">'.$rom.'</option>';
          }
        }
        network::success($output);
      } else {
        network::success('');
      }
      break;
    case 'filter':
      cache::setClientVariable($module->id.'_filter', network::get('type'));
      cache::unsetClientVariable($module->id.'_id');
      $romdata = rom::readAll(cache::getClientVariable($module->id.'_emulator'));
      $output = '';
      foreach (emulator::readRomlist(cache::getClientVariable($module->id.'_emulator')) as $rom) {
        if (network::get('type') == '') {
          $output .= '<option value="'.$rom.'">'.$rom.'</option>';
        } else {
          $offset = array_search(rom::parse($rom), array_column($romdata, 'id'));
          $data = null;
          if (isset($romdata[$offset])) {
            $data = $romdata[$offset];
          }
          if (network::get('type') == 'nodata') {
            if (!isset($data) || isset($data) && !isset($data['fields']['name']) || isset($data['fields']) &&  $data['fields']['name'] == '') {
              $output .= '<option value="'.$rom.'">'.$rom.'</option>';
            }
          } elseif (network::get('type') == 'noimage') {
            if (!isset($data) || isset($data) && !isset($data['fields']['image']) || isset($data['fields']) &&  $data['fields']['image'] == '') {
              $output .= '<option value="'.$rom.'">'.$rom.'</option>';
            }
          } elseif (network::get('type') == 'novideo') {
            if (!isset($data) || isset($data['fields']) && !isset($data['fields']['video']) || isset($data['fields']) && $data['fields']['video'] == '') {
              $output .= '<option value="'.$rom.'">'.$rom.'</option>';
            }
          }
        }
      }
      network::success($output);
      break;
    case 'clean':
      $romdata = rom::readAll(cache::getClientVariable($module->id.'_emulator'));
      $media = array();
      $invalid = array();
      
      foreach ($romdata as $rom) {
        if (!file_exists(db::read('config', 'roms_path').DIRECTORY_SEPARATOR.cache::getClientVariable($module->id.'_emulator').DIRECTORY_SEPARATOR.rom::parse($rom['id']))) {
          array_push($invalid, $rom['id']);
        }
        if (isset($rom['fields']['image']) && $rom['fields']['image'] != '') {
          array_push($media, pathinfo($rom['fields']['image'], PATHINFO_BASENAME));
        }
        if (isset($rom['fields']['video']) && $rom['fields']['video'] != '') {
          array_push($media, pathinfo($rom['fields']['video'], PATHINFO_BASENAME));
        }
        if (isset($rom['fields']['marquee']) && $rom['fields']['marquee'] != '') {
          array_push($media, pathinfo($rom['fields']['marquee'], PATHINFO_BASENAME));
        }
        if (isset($rom['fields']['thumbnail']) && $rom['fields']['thumbnail'] != '') {
          array_push($media, pathinfo($rom['fields']['thumbnail'], PATHINFO_BASENAME));
        }
      }
      
      foreach (scandir(db::read('config', 'media_path').DIRECTORY_SEPARATOR.cache::getClientVariable($module->id.'_emulator')) as $item) {
        if (is_file(db::read('config', 'media_path').DIRECTORY_SEPARATOR.cache::getClientVariable($module->id.'_emulator').DIRECTORY_SEPARATOR.$item)) {
          if (!in_array($item, $media)) {
            unlink(db::read('config', 'media_path').DIRECTORY_SEPARATOR.cache::getClientVariable($module->id.'_emulator').DIRECTORY_SEPARATOR.$item);
          }
        }
      }
      
      rom::clean(cache::getClientVariable($module->id.'_emulator'), $invalid);
      network::success('Successfully Cleaned Orphaned', 'true');
      break;
    case 'presave':
      network::success('', "$('[data-toggle=\"post\"]').submit();");
      break;
    case 'save':
      rom::write(cache::getClientVariable($module->id.'_emulator'), network::post('id'), $_POST);
      network::success('Successfully Saved Gamelist', 'true');
      break;
    case 'delete':
      rom::delete(cache::getClientVariable($module->id.'_emulator'), network::get('id'));
      cache::unsetClientVariable($module->id.'_id');
      network::success('Successfully Deleted Rom', 'core.metadata.reset();');
      break;
    default:
      network::error('invalid action - '.network::get('action'));
      break;
  }
}

?>