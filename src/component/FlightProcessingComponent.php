<?php

namespace Component;

use Exception;

class FlightProcessingComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Component\FlightComponent
   */
  private $flightComponent;

  /**
   * @Inject
   * @var Component\FrameComponent
   */
  private $frameComponent;

  /**
   * @Inject
   * @var Component\FdrComponent
   */
  private $fdrComponent;

  /**
   * @Inject
   * @var Component\CalibrationComponent
   */
  private $calibrationComponent;

  /**
   * @Inject
   * @var Component\RuntimeManager
   */
  private $runtimeManager;

  public function readHeader($fdrId, $file)
  {
    $fdr = $this->em()->find('\Entity\Fdr', $fdrId);
    $headerScr = $fdr->getHeaderScr();

    $flightInfo = [];
    if (($headerScr != '') || ($headerScr != null)) {
      $headerScr = str_replace('new Frame', 'new \Model\Frame', $headerScr);
      //$filePath may be user in eval
      eval($headerScr);

      if (isset($Fl)) {
        unset($Fl);
      }

      if (isset($flightInfo['startCopyTime'])) {
        $startCopyTime = $flightInfo['startCopyTime'];
        $flightInfo['startCopyTime'] = date('H:i:s Y-m-d', $startCopyTime);
        $flightInfo['copyCreationTime'] = date('H:i:s', $startCopyTime);
        $flightInfo['copyCreationDate'] = date('Y-m-d', $startCopyTime);
      }

      if (isset($flightInfo['takeOffLat']) && isset($flightInfo['takeOffLong'])) {
        $lat = $flightInfo['takeOffLat'];
        $long = $flightInfo['takeOffLong'];
        $landingAirport = $this->em()
          ->getRepository('Entity\Airport')
          ->getAirportByLatAndLong($lat, $long);
        if (!empty($landingAirport)) {
          $flightInfo['departureAirport'] = $landingAirport['ICAO'];
          $flightInfo['departureAirportName'] = $landingAirport['name'];
        }
      }

      if (isset($flightInfo['landingLat']) && isset($flightInfo['landingLong'])) {
        $lat = $flightInfo['landingLat'];
        $long = $flightInfo['landingLong'];
        $landingAirport = $this->em()
          ->getRepository('Entity\Airport')
          ->getAirportByLatAndLong($lat, $long);
        if(!empty($landingAirport)) {
          $flightInfo['arrivalAirport'] = $landingAirport['ICAO'];
          $flightInfo['arrivalAirportName'] = $landingAirport['name'];
        }
      }
    }

    return $flightInfo;
  }

  public function readHeaderAndFillInfo($fdrId, $file)
  {
    $flightInfoFromHeader = $this->readHeader($fdrId, $file);
    $flightInfo = $flightInfoFromHeader;

    $flightInfo['bort'] = "x";
    if(isset($flightInfoFromHeader["bort"])) {
      $flightInfo['bort'] = $flightInfoFromHeader["bort"];
    }

    $flightInfo['voyage'] = "x";
    if(isset($flightInfoFromHeader["voyage"])) {
      $flightInfo['voyage'] = $flightInfoFromHeader["voyage"];
    }

    $flightInfo['departureAirport'] = "x";
    if(isset($flightInfoFromHeader["departureAirport"])) {
      $flightInfo['departureAirport'] = $flightInfoFromHeader["departureAirport"];
    }

    $flightInfo['arrivalAirport'] = "x";
    if(isset($flightInfoFromHeader["arrivalAirport"])) {
      $flightInfo['arrivalAirport'] = $flightInfoFromHeader["arrivalAirport"];
    }

    if (isset($flightInfo['copyCreationTime'])
      && isset($flightInfo['copyCreationDate'])
    ) {
      $flightInfo['startCopyTime'] =
        strtotime(
          $flightInfo['copyCreationDate']
          .' '
          .$flightInfo['copyCreationTime']
        );
    }

    $flightInfo['aditionalInfo'] = $this
      ->checkAditionalInfoFromHeader(
        $fdrId,
        $flightInfoFromHeader
      );

    return $flightInfo;
  }

  public function preview($fdrId, $file)
  {
    if (!file_exists($file)) {
      throw new Exception("Trying to preview unexisted file. Path: " . $file, 1);
    }

    $fdrId = intval($fdrId);
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);
    $previewParams = array_map('trim', explode(";", $fdr->getPreviewParams()));

    $params = $this->fdrComponent->getParams($fdrId);
    $previewCyclo = [];

    $groupedCyclo = [];
    foreach ($params as $param) {
      if (in_array($param->getCode(), $previewParams)) {
        if (!isset($groupedCyclo[$param->getPrefix()])) {
          $groupedCyclo[$param->getPrefix()] = [];
        }

        $groupedCyclo[$param->getPrefix()][] = $param->get(true);
      }
    }

    $fileDesc = fopen($file, 'rb');
    $fileSize = filesize($file);

    $headerScr = $fdr->getHeaderScr();
    $headerLength = $fdr->getHeaderLength();
    $flightInfo = [];
    if (($headerScr !== '') && ($headerScr !== null)) {
      eval ($headerScr);
    }

    $frameLength = $fdr->getFrameLength();
    $frameSyncroCode = $fdr->getFrameSyncroCode();
    $startCopyTime = 0; // to be 0 hours
    if (isset($flightInfo['startCopyTime'])) {
      $startCopyTime = $flightInfo['startCopyTime'] * 1000;
    }

    $syncroWordOffset = $this->frameComponent->searchSyncroWord(
      $frameSyncroCode,
      $headerLength,
      $fileDesc,
      $fileSize
    );

    $frameNum = 0;
    $totalFrameNum = floor(($fileSize - $headerLength - $syncroWordOffset)  / $frameLength);

    fseek($fileDesc, $syncroWordOffset, SEEK_SET);
    $curOffset = $syncroWordOffset;

    $algHeap = [];
    $data = [];

    while (($frameNum < $totalFrameNum) && ($curOffset < $fileSize)) {
    //while(($frameNum < 30) && ($curOffset < $fileSize)) {
      $curOffset = ftell($fileDesc);
      $frame = stream_get_contents($fileDesc, $frameLength);
      $unpackedFrame = unpack("H*", $frame);

      if ($this->frameComponent->checkSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true) {
        $splitedFrame = str_split(
          $unpackedFrame[1],
          $fdr->getWordLength() * 2
        );// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

        foreach ($groupedCyclo as $prefix => $cycloAp) {
          $phisicsFrame = $this->frameComponent->convertFrameToPhisics(
            $splitedFrame,
            $startCopyTime,
            $fdr->getStepLength(),
            $frameNum,
            $cycloAp,
            $algHeap
          );

          $phisicsFrame = $phisicsFrame[0]; // 0 - ap 1 - bp

          for ($i = 0; $i < count($cycloAp); $i++) {
            $data[$cycloAp[$i]['code']][] = array($phisicsFrame[1], $phisicsFrame[$i + 2]); //+2 because 0 - frameNum, 1 - time
          }
        }

        $frameNum++;
      } else {
        $syncroWordOffset = $this->frameComponent->searchSyncroWord(
          $frameSyncroCode,
          $curOffset,
          $fileDesc,
          $fileSize
        );

        fseek($fileDesc, $syncroWordOffset, SEEK_SET);

        $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
        $totalFrameNum = $frameNum + $framesLeft;
      }
    }

    fclose($fileDesc);

    return $data;
  }

  public function process(
    $flightUid,
    $file,
    $startCopyTime,
    $totalPersentage,
    $fdrId,
    $calibrationId = null
  ) {
    $fdrId = intval($fdrId);
    $userId = $this->user()->getId();
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    $analogParamsCyclo = $this->fdrComponent
      ->getPrefixGroupedParams($fdrId);

    $analogParamsCyclo = $this->calibrationComponent
      ->putGradiToPrefixGroupedCyclo(
        $calibrationId,
        $fdrId,
        $analogParamsCyclo
      );

    $binaryParamsCyclo = $this->fdrComponent
      ->getPrefixGroupedBinaryParams($fdrId);

    $fileDesc = fopen($file, 'rb');
    $fileSize = filesize($file);

    $frameSyncroCode = $fdr->getFrameSyncroCode();
    $syncroWordOffset = $this->frameComponent
      ->searchSyncroWord(
        $frameSyncroCode,
        $fdr->getHeaderLength(),
        $fileDesc,
        $fileSize
      );

    fseek ($fileDesc, $syncroWordOffset, SEEK_SET);
    $curOffset = $syncroWordOffset;
    $frameLength = $fdr->getFrameLength();

    $algHeap = [];
    $frameNum = 0;
    $status = 0;
    $totalFrameNum = floor(($fileSize - $syncroWordOffset)  / $fdr->getFrameLength());

    //file can be accesed by ajax while try to open what can cause warning
    //error_reporting(E_ALL ^ E_WARNING);
    set_time_limit (0);

    if (isset($frameSyncroCode) && ($frameSyncroCode != '')) {
      while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize)) {
      //while(($frameNum < 20) && ($curOffset < $fileSize)) {
        $curOffset = ftell($fileDesc);
        $frame = stream_get_contents($fileDesc, $frameLength);
        $unpackedFrame = unpack("H*", $frame);

        if ($this->frameComponent->checkSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true) {
          $this->processFrame(
            $flightUid,
            $unpackedFrame,
            $analogParamsCyclo,
            $binaryParamsCyclo,
            $startCopyTime,
            $fdr,
            $frameNum,
            $status,
            $algHeap
          );

          $frameNum++;
          $status = intval(100 / $totalFrameNum * $frameNum);
        } else {
          $syncroWordOffset = $this->frameComponent
            ->searchSyncroWord(
              $frameSyncroCode,
              $headerLength,
              $fileDesc,
              $fileSize
            );

          fseek($fileDesc, $syncroWordOffset, SEEK_SET);

          $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
          $totalFrameNum = $frameNum + $framesLeft;
          $status = 100 / $totalFrameNum * $frameNum;
        }
      }
    } else {
      while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize)) {
      //while(($frameNum < 20) && ($curOffset < $fileSize)) {
        $curOffset = ftell($fileDesc);
        $frame = stream_get_contents($fileDesc, $frameLength);
        $unpackedFrame = unpack("H*", $frame);

        $this->processFrame(
          $flightUid,
          $unpackedFrame,
          $analogParamsCyclo,
          $binaryParamsCyclo,
          $startCopyTime,
          $fdr,
          $frameNum,
          $status,
          $algHeap
        );

        $frameNum++;
        $status = intval(100 / $totalFrameNum * $frameNum);
      }
    }

    $this->runtimeManager->writeToRuntimeTemporaryFile(
      $this->params()->folders->uploadingStatus,
      $flightUid,
      $totalPersentage,
      'json',
      true,
      'w',
      true
    );

    error_reporting(E_ALL);
    fclose($fileDesc);

    $this->flightComponent->createParamTables(
      $flightUid,
      $analogParamsCyclo,
      $binaryParamsCyclo
    );

    foreach ($analogParamsCyclo as $prefix => $cyclo) {
      $this->loadParamFilesToTables(
        $this->fdrComponent->getAnalogTable($flightUid, $cyclo[0]['prefix'])
      );
    }

    foreach($binaryParamsCyclo as $prefix => $cyclo) {
      $this->loadParamFilesToTables(
        $this->fdrComponent->getBinaryTable($flightUid, $cyclo[0]['prefix'])
      );
    }

    return $flightUid;
  }

  public function convertFrame (
    $flightUid,
    $analogParamsCyclo,
    $binaryParamsCyclo,
    $splitedFrame,
    $startCopyTime,
    $stepLength,
    $frameNum,
    &$algHeap,
    $writeToFile = true
  ) {
    $framesByFreq = [];
    $phisicsFrames = [];
    foreach ($analogParamsCyclo as $prefix => $cyclo) {
      $channelFreq = count($cyclo[0]['channel']);

      /*
       * convertFrameToPhisics may return few frames,
       * because few channel values in frame
       */
      $phisicsFrames = $this->frameComponent->convertFrameToPhisics(
        $splitedFrame,
        $startCopyTime,
        $stepLength,
        $frameNum,
        $cyclo,
        $algHeap,
        $channelFreq
      );

      $framesByFreq[$channelFreq] = $phisicsFrames;

      if ($writeToFile) {
        foreach ($phisicsFrames as $frame) {
          $this->runtimeManager->writeToRuntimeTemporaryFile(
            $this->params()->folders->uploadingFlightsTables,
            $this->fdrComponent->getAnalogTable($flightUid, $channelFreq),
            $frame,
            'csv'
          );
        }
      }
    }

    foreach ($binaryParamsCyclo as $prefix => $cyclo) {
      $channelFreq = count($cyclo[0]['channel']);

      $convBinFrame = $this->frameComponent->convertFrameToBinaryParams(
        $splitedFrame,
        $frameNum,
        $startCopyTime,
        $stepLength,
        $channelFreq,
        $cyclo,
        $framesByFreq,
        $algHeap
      );

      if ($writeToFile) {
        foreach ($convBinFrame as $frame) {
          $this->runtimeManager->writeToRuntimeTemporaryFile(
            $this->params()->folders->uploadingFlightsTables,
            $this->fdrComponent->getBinaryTable($flightUid, $channelFreq),
            $frame,
            'csv'
          );
        }
      }
    }

    return [
      'phisicsByFreq' => $framesByFreq,
      'binaryFlags' => $convBinFrame
    ];
  }

  private function processFrame (
    $flightUid,
    $unpackedFrame,
    $analogParamsCyclo,
    $binaryParamsCyclo,
    $startCopyTime,
    $fdr,
    $frameNum,
    $status,
    &$algHeap
  ) {
    $splitedFrame = str_split($unpackedFrame[1], $fdr->getWordLength() * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

    $this->convertFrame(
      $flightUid,
      $analogParamsCyclo,
      $binaryParamsCyclo,
      $splitedFrame,
      $startCopyTime,
      $fdr->getStepLength(),
      $frameNum,
      $algHeap
    );

    $this->runtimeManager->writeToRuntimeTemporaryFile(
      $this->params()->folders->uploadingStatus,
      $flightUid,
      $status,
      'raw',
      true
    );
  }

  public function loadParamFilesToTables($tableName, $filePath = null)
  {
    if ($filePath === null) {
      $file = $this->runtimeManager->getTemporaryFileDesc(
        $this->params()->folders->uploadingFlightsTables,
        $tableName,
        'close'
      );
      $filePath = $file->path;
    }

    $this->connection()->loadFile($tableName, $filePath);

    if (file_exists($filePath)) {
      unlink($filePath);
    }
  }

  public function checkAditionalInfoFromHeader($fdrId, $headerInfo)
  {
    $aditionalInfo = [];

    $fdr = $this->em()->find('Entity\Fdr', $fdrId);
    $aditionalInfoArr = explode(";", $fdr->getAditionalInfo());

    foreach($aditionalInfoArr as $key => $val) {
      if (isset($headerInfo[$val])) {
        $aditionalInfo[$val] = $headerInfo[$val];
      } else {
        $aditionalInfo[$val] = "x";
      }
    }

    return $aditionalInfo;
  }
}
