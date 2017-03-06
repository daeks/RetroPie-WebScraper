<?php

set_time_limit(180);
require_once(dirname(realpath(__FILE__)).DIRECTORY_SEPARATOR.'config.php');

if (network::get('action') != '') {
  switch (network::get('action')) {
    case 'init':
      $total = 0;
      foreach (db::instance()->read('emulators') as $emulator) {
        if (isset($emulator['count'])) {
          $total += $emulator['count'];
        }
      }
      network::success(''.$total);
      break;
    case 'sync':
      $output = '';
      $emulator = db::instance()->read('emulators', 'count is null');
      if (sizeof($emulator) >= 1) {
        $current = $emulator[0];
        $next = array();
        if (sizeof($emulator) >= 2) {
          $next = $emulator[1];
        }
        emulator::sync($current['id']);
        $output .= '<form class="form-horizontal" id="modal-data" name="modal-data" data-validate="modal" data-toggle="modal" data-target="#modal-body" action="'.CONTROLLER.'?action=sync" method="GET"><fieldset>';
        if (sizeof($next) > 0) {
          if (sizeof($emulator) > 1) {
            $totalroms = 0;
            $totalemulators = 0;
            $finished = 0;
            foreach (db::instance()->read('emulators') as $tmp) {
              $totalemulators += 1;
              if ($tmp['count'] != '') {
                $totalroms += $tmp['count'];
                $finished += 1;
              }
            }
            $output .= '<p>Roms synced: <b>'.$totalroms.'</b> - Emulators processed: <b>'.$finished.' / '.$totalemulators.'</b><br>';
            $percent = 0;
            if ($finished > 0) {
              $percent = round(($finished/$totalemulators) * 100, 0);
            }
            $color = 'progress-bar-danger';
            if ($percent > 50 && $percent < 75) {
              $color = 'progress-bar-warning';
            } elseif ($percent >= 75) {
              $color = 'progress-bar-success';
            }
            $output .=  '<div class="progress">';
            $output .=  '<div class="progress-bar progress-bar-striped active '.$color.'" role="progressbar" style="width: '.$percent.'%;">'.$percent.'%</div>';
            $output .=  '</div></p>';
          }
          $output .= '<p>Now installing <b>'.$next['name'].'</b>...</p>';
          $output .= '<div class="alert alert-warning" role="alert"><b>Warning</b> Depending on your romset this might take a while.</div>';
        } else {
          $output .= '<p>Installed <b>'.$current['name'].'</b>...</p>';
        }
        $output .= '</fieldset></form>';
        network::send(301, $output, 'core.sync();');
      } else {
        network::success('Successfully Finished Sync', 'location.reload();');
      }
      break;
    default:
      network::error('invalid action - '.network::get('action'));
      break;
  }
}

?>