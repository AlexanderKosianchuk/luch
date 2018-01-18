<?php

namespace Controller;

class InteractionController extends BaseController
{
  public function upAction()
  {
    $cmd = 'cd '.$this->params()->interaction->path.' && nodejs app.js > /dev/null &';
    $url = $this->params()->interaction->url . '/realtimeCalibration/getStatus';
    $output = [];

    try {
      $ch = curl_init();
      curl_setopt($ch, CURLOPT_URL, $url);
      curl_setopt($ch, CURLOPT_HEADER, TRUE);
      curl_setopt($ch, CURLOPT_NOBODY, TRUE);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1);
      curl_setopt($ch, CURLOPT_TIMEOUT, 1);
      curl_setopt($ch, CURLOPT_VERBOSE, FALSE);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      $head = curl_exec($ch);
      $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
      curl_close($ch);

      if ($httpCode != 200) {
        if (substr(php_uname(), 0, 7) == "Windows"){
          pclose(popen("start /B ". $cmd, "r"));
        }
        else {
          exec($cmd, $output);

          // timeout to allow node up complete
          sleep(2);
        }
      }
    } catch (Exception $ex) {
      return json_encode(['status' => 'err', 'cmd' => $cmd, 'message' => $ex]);
    }

    return json_encode(['status' => 'ok', 'cmd' => $cmd, 'message' => $output]);
  }
}
