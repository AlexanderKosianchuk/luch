<?php

namespace Component;

use Exception;

class RuntimeDatabaseComponent extends BaseComponent
{
  public function putRealtimeCalibrationData(
    $uploadingUid,
    $frameNum,
    $currentTime,
    $normalizedFrame
  ) {
    $this->connection()->create('runtime');

    $this->connection()->destroy($link);
  }
}
