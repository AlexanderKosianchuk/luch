<?php

namespace Component;

use Framework\Application as App;

use Exception;

class FlightProcessingComponent extends BaseComponent
{
    public function readHeader($fdrId, $file)
    {
        $fdr = App::em()->find('\Entity\Fdr', $fdrId);
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

    public function ProccessFlightData($tempFilePath,
            $bort,
            $voyage,
            $copyCreationTime,
            $copyCreationDate,
            $fdrId,
            $performer,
            $departureAirport,
            $arrivalAirport,
            $aditionalInfo,
            $fileName,
            $totalPersentage,
            $calibrationId = null
    ) {
        $fdrId = intval($fdrId);

        if (strlen($copyCreationTime) > 5) {
            $startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime);
        } else {
            $startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime . ":00");
        }

        if ($performer == null) {
            $performer = $this->_user->username;
        }

        $userId = intval($this->_user->userInfo['id']);

        $fdr = new Fdr;
        $fdrInfo = $fdr->GetFdrInfo($fdrId);
        $fdrCode = $fdrInfo['code'];

        $Fl = new Flight;
        $flightId = $Fl->InsertNewFlight(
            $bort,
            $voyage,
            $startCopyTime,
            $fdrId,
            $fdrCode,
            $performer,
            $departureAirport,
            $arrivalAirport,
            $fileName,
            $aditionalInfo,
            $userId
        );

        $flightInfo = $Fl->GetFlightInfo($flightId);
        $tableNameAp = $flightInfo['apTableName'];
        $tableNameBp = $flightInfo['bpTableName'];
        $flightId = $flightInfo['id'];

        $frameLength = $fdrInfo['frameLength'];
        $stepLength = $fdrInfo['stepLength'];
        $wordLength = $fdrInfo['wordLength'];
        $headerLength = $fdrInfo['headerLength'];
        $headerScr = $fdrInfo['headerScr'];
        $frameSyncroCode = $fdrInfo['frameSyncroCode'];
        $cycloApByPrefixes = $fdr->GetBruApCycloPrefixOrganized($fdrId);

        if ($calibrationId !== null) {
            $calibration = new Calibration;
            $fdrCalibration = $calibration->getCalibrationById($calibrationId, $userId);
            $dynamicCalibrationTableName = $calibration->getTableName($fdrCode);
            $calibratedParams = $calibration->getCalibrationParams($dynamicCalibrationTableName, $calibrationId);

            foreach ($cycloApByPrefixes as $prefix => &$params) {
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
