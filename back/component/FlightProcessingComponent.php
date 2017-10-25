<?php

namespace Component;

use Framework\Application as App;

use Exception;

class FlightProcessingComponent extends BaseComponent
{
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
                $landingAirport = $this::em()
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
                $landingAirport = $this::em()
                    ->getRepository('Entity\Airport')
                    ->getAirportByLatAndLong($lat, $long);
                if(!empty($landingAirport)) {
                    $flightInfo['arrivalAirport'] = $landingAirport['ICAO'];
                    $flightInfo['arrivalAirportName'] = $landingAirport['name'];
                }
            }
            unset($airport);
        }

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
                if (!isset($grouped[$param->getPrefix()])) {
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

                    for($i = 0; $i < count($cycloAp); $i++) {
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
        $storedFlightFile,
        $progressFilePath,
        $totalPersentage,
        $fdrId,
        $calibrationId = null
    ) {
        $fdrId = intval($fdrId);
        $userId = $this->user()->getId();

        $analogParams = $this
            ->fdrComponent
            ->getPrefixGroupedParams($fdrId);

        if ($calibrationId !== null) {
            $calibratedParams = $this->calibrationComponent
                ->getCalibrationParams($fdrId, $calibrationId);

            //var_dump($calibratedParams); exit;

            foreach ($analogParams as $prefix => &$params) {
                foreach ($params as &$param) {
                    $paramId = $param['id'];

                    if(isset($calibratedParams[$paramId])) {
                        $param['xy'] = $calibratedParams[$paramId]['xy'];
                    }
                }
            }
        }



        $prefixFreqArr = $fdr->GetBruApCycloPrefixFreq($fdrId);

        $cycloBpByPrefixes = $fdr->GetBruBpCycloPrefixOrganized($fdrId);
        $prefixBpFreqArr = $fdr->GetBruBpCycloPrefixFreq($fdrId);
        unset($fdr);
        $apTables = $Fl->CreateFlightParamTables($flightId, $cycloApByPrefixes, $cycloBpByPrefixes);
        unset($Fl);

        $Fr = new Frame;
        $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $headerLength, $fileName);

        $fileDesc = $Fr->OpenFile($fileName);
        $fileSize = $Fr->GetFileSize($fileName);

        $frameNum = 0;
        $totalFrameNum = floor(($fileSize - $syncroWordOffset)  / $frameLength);

        $tmpProccStatusFilesDir = RuntimeManager::getRuntimeFolder();

        $fileNameApArr = array();
        $fileNameApDescArr = array();
        foreach ($cycloApByPrefixes as $prefix => $item) {
            $fileNameAp = $tmpProccStatusFilesDir . "/" . $tableNameAp . "_".$prefix.".tbl";
            $fileNameApArr[$prefix] = $fileNameAp;
            $fileNameApDesc = fopen($fileNameAp, "w");
            $fileNameApDescArr[$prefix] = $fileNameApDesc;
        }

        $fileNameBpArr = array();
        $fileNameBpDescArr = array();
        foreach ($cycloBpByPrefixes as $prefix => $item) {
            $fileNameBp = $tmpProccStatusFilesDir . "/" . $tableNameBp . "_".$prefix.".tbl";
            $fileNameBpArr[$prefix] = $fileNameBp;
            $fileNameBpDesc = fopen($fileNameBp, "w");
            $fileNameBpDescArr[$prefix] = $fileNameBpDesc;
        }

        fseek($fileDesc, $syncroWordOffset, SEEK_SET);
        $curOffset = $syncroWordOffset;

        //file can be accesed by ajax while try to open what can cause warning
        error_reporting(E_ALL ^ E_WARNING);
        set_time_limit (0);

        $algHeap = array();
        $tmpStatus = 0;
        $newStatus = 0;
        $this->writeStatus ($tempFilePath, $tmpStatus);

        if ($frameSyncroCode != '') {
            while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
            //while(($frameNum < 20) && ($curOffset < $fileSize))
            {
                $curOffset = ftell($fileDesc);
                $frame = $Fr->ReadFrame($fileDesc, $frameLength);
                $unpackedFrame = unpack("H*", $frame);

                if($Fr->CheckSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true)
                {
                    $splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

                    $apPhisicsByPrefixes = array();
                    foreach ($cycloApByPrefixes as $prefix => $cycloAp) {
                        $channelFreq = $prefixFreqArr[$prefix];
                        $phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $cycloAp, $algHeap);
                        $apPhisicsByPrefixes[$prefix] = $phisicsFrame;
                    }

                    $bpPhisicsByPrefixes = array();
                    foreach($cycloBpByPrefixes as $prefix => $cycloBp) {
                        $channelFreq = $prefixBpFreqArr[$prefix];
                        $convBinFrame = $Fr->ConvertFrameToBinaryParams($splitedFrame,
                            $frameNum,
                            $startCopyTime,
                            $stepLength,
                            $channelFreq,
                            $cycloBp,
                            $apPhisicsByPrefixes,
                            $algHeap);

                        $bpPhisicsByPrefixes[$prefix] = $convBinFrame;
                    }

                    $Fr->AppendFrameToFile($apPhisicsByPrefixes, $fileNameApDescArr);
                    $Fr->AppendFrameToFile($bpPhisicsByPrefixes, $fileNameBpDescArr);

                    $frameNum++;
                } else {
                    $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $curOffset, $fileName);

                    fseek($fileDesc, $syncroWordOffset, SEEK_SET);

                    $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
                    $totalFrameNum = $frameNum + $framesLeft;

                }

                $newStatus = round($totalPersentage / $fileSize * $frameNum * $frameLength);
                if ($newStatus > $tmpStatus) {
                    $tmpStatus = $newStatus;
                    $this->writeStatus ($tempFilePath, $tmpStatus);
                }
            }
        } else {
            while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
            //while(($frameNum < 20) && ($curOffset < $fileSize))
            {
                $curOffset = ftell($fileDesc);
                $frame = $Fr->ReadFrame($fileDesc, $frameLength);
                $unpackedFrame = unpack("H*", $frame);

                $splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

                $apPhisicsByPrefixes = array();
                foreach ($cycloApByPrefixes as $prefix => $cycloAp) {
                    $channelFreq = $prefixFreqArr[$prefix];
                    $phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $cycloAp, $algHeap);
                    $apPhisicsByPrefixes[$prefix] = $phisicsFrame;
                }

                $bpPhisicsByPrefixes = array();
                foreach($cycloBpByPrefixes as $prefix => $cycloBp) {
                    $channelFreq = $prefixBpFreqArr[$prefix];
                    $convBinFrame = $Fr->ConvertFrameToBinaryParams($splitedFrame,
                        $frameNum,
                        $startCopyTime,
                        $stepLength,
                        $channelFreq,
                        $cycloBp,
                        $apPhisicsByPrefixes,
                        $algHeap);

                    $bpPhisicsByPrefixes[$prefix] = $convBinFrame;
                }

                $Fr->AppendFrameToFile($apPhisicsByPrefixes, $fileNameApDescArr);
                $Fr->AppendFrameToFile($bpPhisicsByPrefixes, $fileNameBpDescArr);

                $frameNum++;

                $newStatus = round($totalPersentage / $fileSize * $frameNum * $frameLength);
                if ($newStatus > $tmpStatus) {
                    $tmpStatus = $newStatus;
                    $this->writeStatus ($tempFilePath, $tmpStatus);
                }
            }
        }

        $this->writeStatus ($tempFilePath, $totalPersentage);

        error_reporting(E_ALL);

        //not need any more
        $Fr->CloseFile($fileDesc);
        unlink($fileName);

        foreach($fileNameApArr as $prefix => $fileNameAp) {
            fclose($fileNameApDescArr[$prefix]);
            $Fr->LoadFileToTable($tableNameAp . "_" . $prefix, $fileNameAp);
            unlink($fileNameAp);
        }

        foreach($fileNameBpArr as $prefix => $fileNameBp) {
            fclose($fileNameBpDescArr[$prefix]);
            $Fr->LoadFileToTable($tableNameBp . "_" . $prefix, $fileNameBp);
            unlink($fileNameBp);
        }

        $userId = intval($this->_user->userInfo['id']);
        $observerIds = $this->_user->GetObservers($userId);

        if (!in_array($userId, $observerIds)) {
            $observerIds[] = $userId;
        }

        $Fd = new Folder;
        foreach ($observerIds as $id) {
            $Fd->PutFlightInFolder($flightId, 0, $id); //we put currently uploaded file in root
        }
        unset($Fd);
        unset($Fr);

        return $flightId;
    }
}
