<?php

namespace Controller;

use Exception\BadRequestException;
use Exception\NotFoundException;;

class VoiceController extends BaseController
{
  public function streamAction($fileName, $checkStreaming = true)
  {
    set_time_limit(60 * 60 * 3); // 3 hours

    $filePath = $this->dic('voice')->getUploadingFilePath($fileName);

    if (!file_exists($filePath)) {
      throw new NotFoundException('voice uploading file not found. File name: '. $filePath);
    }

    header('Content-Type: audio/wav');
    header('Connection: Close');
    header('Cache-Control: no-cache, no-store');
    header('Pragma: no-cache');

    $offset = 0;
    $step = 10000;
    $isStreaming = true;
    $contents = '';
    $filesize = filesize($filePath);
    $similarFilesize = 0;

    while ($isStreaming) {
      if ($filesize === filesize($filePath)) {
        $similarFilesize++;
      }

      if ($checkStreaming && $similarFilesize > 1000) {
        $isStreaming = false;
      }

      $filesize = filesize($filePath);
      if ($filesize < $offset + $step) {
        usleep(10000);
        continue;
      }

      try {
        $handle = fopen($filePath, 'r');
        fseek($handle, $offset);
        $contents = fread($handle, $step);
        $offset += $step;
        fclose($handle);
        echo($contents);
      } catch (Exception $e) {
        usleep(10000);
      }
    }

    exit;
  }
}
