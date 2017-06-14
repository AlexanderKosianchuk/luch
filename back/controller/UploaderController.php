<?php

namespace Controller;

use Model\Fdr;
use Model\User;
use Model\Airport;
use Model\Frame;
use Model\Flight;
use Model\Calibration;
use Model\Folder;
use Model\FlightException;

use Component\FlightComponent;
use Component\EventProcessingComponent;
use Component\RuntimeManager;
use Component\RealConnectionFactory as LinkFactory;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Evenement\EventEmitter;

use ZipArchive;
use \Exception;

class UploaderController extends CController
{
    public $curPage = 'uploaderPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();
    }

    public function ShowFlightParams($index, $uploadingUid, $fdrId, $filePath, $calibrationId = null)
    {
        $fileName = basename($filePath);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $previewParams = $fdrInfo['previewParams'];
        unset($fdr);

        $flightInfoFromHeader = $this->ReadHeader($fdrId, $filePath);

        $fileInfoColumnWidth = '100%';
        if ($previewParams != '') {
            $fileInfoColumnWidth = 450;
        }

        $flightParamsSrt = "<div id='fileFlightInfo".$index."' class='MainContainerContentRows' " .
            "data-filename='" . $filePath . "' " .
            "data-uploading-uid='" . $uploadingUid . "' " .
            "data-fdr-id='" . $fdrId . "' " .
            "data-index='" . $index . "' " .
            "data-previewparams='" . $previewParams . "' " .
            "data-calibration-id='" . $calibrationId . "' " .
            "align='left'>" .
            "</br>" .
             //left column for flight info - right for preview
            "<table style='width:100%'><tr><td style='width:" . $fileInfoColumnWidth . "px'>" .

            "<table border='0' style='margin-bottom:15px;'>" .
            "<tr>" .
            "<td>" . $this->lang->bruType . "</td>";

        $flightParamsSrt .= "<td>"
            ."<input id='bruType' name='fdrName' class='FlightUploadingInputs' value='" . $fdrInfo['name'] .
            "' readonly /></td>" .
            "</tr><tr>";

        $bortFromHeader = "";
        if(isset($flightInfoFromHeader["bort"])) {
            $bortFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["bort"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->bortNum . "</td>" .
            "<td><input id='bort' name='bort' type='text' class='FlightUploadingInputs' ".
            "value='" . $bortFromHeader . "'/></td>" .
            "</tr>";

        $voyageFromHeader = "";
        if (isset($flightInfoFromHeader["voyage"])) {
            $voyageFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["voyage"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->voyage . "</td>" .
            "<td><input id='voyage' name='voyage' type='text' class='FlightUploadingInputs' ".
            "value='" . $voyageFromHeader . "'/></td>" .
            "</tr>";

        $departureAirportFromHeader = "";
        if(isset($flightInfoFromHeader["departureAirport"])) {
            $departureAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["departureAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->departureAirport . "</td>" .
                "<td><input id='departureAirport' name='departureAirport' type='text' class='FlightUploadingInputs' ".
                "value='" . $departureAirportFromHeader . "'/></td>" .
                "</tr>";

        $arrivalAirportFromHeader = "";
        if (isset($flightInfoFromHeader["arrivalAirport"])) {
            $arrivalAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["arrivalAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->arrivalAirport . "</td>" .
                "<td><input id='arrivalAirport' name='arrivalAirport' type='text' class='FlightUploadingInputs' ".
                "value='" . $arrivalAirportFromHeader . "'/></td>" .
                "</tr>";

        $copyCreationTimeFromHeader = "";
        $copyCreationDateFromHeader = "";
        if (isset($flightInfoFromHeader["copyCreationTime"])
            && isset($flightInfoFromHeader["copyCreationDate"])
        ) {
            $copyCreationTimeFromHeader = $flightInfoFromHeader["copyCreationTime"];
            $copyCreationDateFromHeader = $flightInfoFromHeader["copyCreationDate"];
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->flightDate . "</td>" .
                "<td><input id='copyCreationTime' name='copyCreationTime' type='time' class='FlightUploadingInputs' " .
                "value='" . $copyCreationTimeFromHeader . "'/> <br>" .
                "<input id='copyCreationDate' name='copyCreationDate' type='date' class='FlightUploadingInputs'" .
                "value='" . $copyCreationDateFromHeader . "'/>" .
                "</td></tr>";

        $flightParamsSrt .= "<tr><td>" . $this->lang->performer . "</td>" .
                "<td><input id='performer' name='performer' type='text' class='FlightUploadingInputs' value='" .
                $this->_user->username . "'/></td>" .
                "</tr>";

        if ($fdrInfo['aditionalInfo'] != '') {
            if (strpos($fdrInfo['aditionalInfo'], ";") !== 0) {
                $aditionalInfo = explode(";", $fdrInfo['aditionalInfo']);
                $aditionalInfo  = array_map('trim', $aditionalInfo);
            } else {
                $aditionalInfo = (array)trim($fdrInfo['aditionalInfo']);
            }

            for ($i = 0; $i < count($aditionalInfo); $i++) {

                if (property_exists($this->lang, $aditionalInfo[$i])) {
                    $labelsArr = get_object_vars($this->lang);
                    $label = $labelsArr[$aditionalInfo[$i]];
                } else {
                    if(!(property_exists($this->lang, 'aditionalInfo'))) {
                        $this->lang->aditionalInfo = "Aditional info";
                    }

                    $label = $this->lang->aditionalInfo;
                }

                $flightParamsSrt .= "<tr><td>" . $label . "</td>";

                $aditionalInfoFromHeader = "";
                if(isset($flightInfoFromHeader[$aditionalInfo[$i]])) {
                    $aditionalInfoFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',
                            $flightInfoFromHeader[$aditionalInfo[$i]]);
                }

                $flightParamsSrt .= "<td><input id='" . $aditionalInfo[$i] . "'
                        name='aditionalInfo" . $i . "' type='text' class='FlightUploadingInputsAditionalInfo' " .
                        "value='" . $aditionalInfoFromHeader . "'/></td>
                </tr>";
            }
        }

        if(in_array(User::$PRIVILEGE_TUNE_FLIGHTS, $this->_user->privilege)) {
            $flightParamsSrt .= "<tr><td>" . $this->lang->execProc . "</td>" .
                "<td><input id='execProc' type='checkbox' checked class='FlightUploadingInputs'/></td>
                </tr>";
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->ignoreDueUploading . "</td>" .
                "<td><input id='ignoreDueUploading".$index."' type='checkbox' class='FlightUploadingInputs'/></td>
                </tr>";

        $flightParamsSrt .= "</table>";

        //priview column
        $flightParamsSrt .= "</td><td align='center' style='vertical-align:top; padding-top:7px;'>";

        $previewParams = trim($previewParams);
        if ($previewParams != '') {
            $flightParamsSrt .= "<div id='loadingBox".$index."' width='100%' style='position:absolute;'>
                    <img style='margin:0px auto 0px;' src='/front/stylesheets/basicImg/loading.gif'/></div>";

            $flightParamsSrt .= "<div id='previewChartContainer".$index."' " .
                    "style='width:95%; border:0;'>
                <div id='previewChartPlaceholder".$index."' " .
                    "data-index='".$index."' " .
                    "class='PreviewChartPlaceholder'></div>
                </div>";

            $flightParamsSrt .= "<button id='sliceFlightButt".$index."' ".
                    "class='SliceFlightButt' ".
                    "data-index='".$index."' " .
                    "data-uploading-uid='" . $uploadingUid . "' " .
                    "data-file='".$filePath."' " .
                    "data-fdr-id='".$fdrId."' " .
                    "class='Button'>".
                    $this->lang->slice . "</button>";

            $flightParamsSrt .= "<button id='sliceCyclicFlightButt".$index."' ".
                    "class='SliceCyclicFlightButt' ".
                    "data-index='".$index."' " .
                    "data-uploading-uid='" . $uploadingUid . "' " .
                    "data-file='".$filePath."' " .
                    "data-fdr-id='".$fdrId."' " .
                    "class='Button'>".
                    $this->lang->sliceCyclic . "</button>";
        }

        $flightParamsSrt .= "</br></form></div>";

        $flightParamsSrt .= "</td></tr></table></div>";

        return $flightParamsSrt;
    }

    public function ReadHeader($fdrId, $file)
    {
        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $frameLength = $fdrInfo['frameLength'];
        $stepLength = $fdrInfo['stepLength'];
        $wordLength = $fdrInfo['wordLength'];
        $headerLength = $fdrInfo['headerLength'];
        $headerScr = $fdrInfo['headerScr'];
        $frameSyncroCode = $fdrInfo['frameSyncroCode'];
        unset($fdr);

        if(($headerScr != '') || ($headerScr != null))
        {
            $headerScr = str_replace('Frame', '\Model\Frame', $headerScr);
            eval($headerScr);

            unset($Fl);

            if(isset($flightInfo['startCopyTime']))
            {
                $startCopyTime = $flightInfo['startCopyTime'];
                $flightInfo['startCopyTime'] = date('H:i:s Y-m-d', $startCopyTime);
                $flightInfo['copyCreationTime'] = date('H:i:s', $startCopyTime);
                $flightInfo['copyCreationDate'] = date('Y-m-d', $startCopyTime);
            }

            $airport = new Airport;
            if(isset($flightInfo['takeOffLat']) && isset($flightInfo['takeOffLong']))
            {
                $lat = $flightInfo['takeOffLat'];
                $long = $flightInfo['takeOffLong'];
                $landingAirport = $airport->getAirportByLatAndLong($lat, $long);
                if(!empty($landingAirport))
                {
                    $flightInfo['departureAirport'] = $landingAirport['ICAO'];
                    $flightInfo['departureAirportName'] = $landingAirport['name'];
                }
            }

            if(isset($flightInfo['landingLat']) && isset($flightInfo['landingLong']))
            {
                $lat = $flightInfo['landingLat'];
                $long = $flightInfo['landingLong'];
                $landingAirport = $airport->getAirportByLatAndLong($lat, $long);
                if(!empty($landingAirport))
                {
                    $flightInfo['arrivalAirport'] = $landingAirport['ICAO'];
                    $flightInfo['arrivalAirportName'] = $landingAirport['name'];
                }
            }
            unset($airport);
        }

        return $flightInfo;
    }

    public function CheckAditionalInfoFromHeader($fdrId, $headerInfo)
    {
        $aditionalInfo = [];

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $aditionalInfoArr = explode(";", $fdrInfo["aditionalInfo"]);

        foreach($aditionalInfoArr as $key => $val) {
            if (isset($headerInfo[$val])) {
                $aditionalInfo[$val] = $headerInfo[$val];
            } else {
                $aditionalInfo[$val] = "x";
            }
        }

        unset($fdr);

        return json_encode($aditionalInfo);
    }

    public function CopyPreview($fdrId, $file)
    {
        $fdrId = intval($fdrId);
        $flightInfo['id_fdr'] = $fdrId;

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $frameLength = $fdrInfo['frameLength'];
        $stepLength = $fdrInfo['stepLength'];
        $wordLength = $fdrInfo['wordLength'];
        $headerLength = $fdrInfo['headerLength'];
        $headerScr = $fdrInfo['headerScr'];
        $frameSyncroCode = $fdrInfo['frameSyncroCode'];

        $previewParams = $fdrInfo['previewParams'];
        $cycloAp = $fdr->GetBruApCyclo($fdrId, -1, -1, -1);

        $previewParams = explode(";", $previewParams);
        $previewParams = array_map('trim', $previewParams);

        $previewCyclo = array();
        $cycloApByPrefixes = array();

        foreach ($cycloAp as $row => $val) {
            if(in_array($val['code'], $previewParams)) {
                $previewCyclo[] = $val;
                if(!in_array($val['prefix'], $cycloApByPrefixes)) {
                    $prefixFreqArr[$val['prefix']] = count(explode(",",$val['channel']));
                }

                $cycloApByPrefixes[$val['prefix']][] = $val;
            }
        }

        $prefixFreqArr = $fdr->GetBruApCycloPrefixFreq($fdrId);
        unset($fdr);

        $Fr = new Frame;
        $fileDesc = $Fr->OpenFile($file);
        $fileSize = $Fr->GetFileSize($file);

        if(($headerScr != '') || ($headerScr != null))
        {
            $headerScr = str_replace('Frame', '\Model\Frame', $headerScr);
            eval ($headerScr);
        }

        $startCopyTime = 0; // to be 0 hours
        /*if(isset($flightInfo['startCopyTime']))
        {
            $startCopyTime = $flightInfo['startCopyTime'] * 1000;
        }*/

        $Fr = new Frame;
        $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $headerLength, $file);

        $fileDesc = $Fr->OpenFile($file);
        $fileSize = $Fr->GetFileSize($file);

        $frameNum = 0;
        $totalFrameNum = floor(($fileSize - $headerLength - $syncroWordOffset)  / $frameLength);

        fseek($fileDesc, $syncroWordOffset, SEEK_SET);
        $curOffset = $syncroWordOffset;

        $algHeap = array();
        $data = array();

        while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
        //while(($frameNum < 30) && ($curOffset < $fileSize))
        {
            $curOffset = ftell($fileDesc);
            $frame = $Fr->ReadFrame($fileDesc, $frameLength);
            $unpackedFrame = unpack("H*", $frame);

            if($Fr->CheckSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true)
            {
                $splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

                $apPhisicsByPrefixes = array();
                foreach($cycloApByPrefixes as $prefix => $cycloAp)
                {
                    $channelFreq = $prefixFreqArr[$prefix];
                    $phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $cycloAp, $algHeap);

                    $phisicsFrame = $phisicsFrame[0]; // 0 - ap 1 - bp

                    for($i = 0; $i < count($cycloAp); $i++)
                    {
                        $data[$cycloAp[$i]['code']][] = array($phisicsFrame[1], $phisicsFrame[$i + 2]); //+2 because 0 - frameNum, 1 - time
                    }
                }

                $frameNum++;
            }
            else
            {
                $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $curOffset, $file);

                fseek($fileDesc, $syncroWordOffset, SEEK_SET);

                $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
                $totalFrameNum = $frameNum + $framesLeft;

            }
        }

        $Fr->CloseFile($fileDesc);
        unset($Fr);

        return $data;
    }

    public function CutCopy (
        $fdrId,
        $newUid,
        $filePath,
        $startCopyTime,
        $endCopyTime,
        $startSliceTime,
        $endSliceTime
    ) {
        $fdrId = intval($fdrId);

        $newFileName = RuntimeManager::getFilePathByIud($newUid);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        unset ($fdr);
        $headerLength = $fdrInfo['headerLength'];
        $frameLength = $fdrInfo['frameLength'];

        $handle = fopen($filePath, "r");
        $newHandle = fopen($newFileName, "w");

        if ($headerLength > 0) {
            $fileHeader = fread($handle, $headerLength);
            fwrite($newHandle, $fileHeader);
        }

        //$writtenHeaderLength = file_put_contents($newFileName, $fileHeader);

        $fileSize = filesize ($filePath);
        $Bs = ($fileSize - $headerLength) / ($endCopyTime - $startCopyTime);
        $stB = $Bs * ($startSliceTime - $startCopyTime) + $headerLength;
        $endB = $Bs * ($endSliceTime - $startCopyTime) + $headerLength;

        $stB = round($stB / $frameLength , 0) * $frameLength + $headerLength;

        if ($endB > $fileSize) {
            $endB = $fileSize;
        }

        if ($stB > 0 && $stB < $fileSize && $endB > 0 && $endB <= $fileSize) {
            fseek($handle, $stB);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB) {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }
            fclose($handle);
            fclose($newHandle);

            $newFileName = basename($newFileName);

            $answ["status"] = "ok";
            $answ["data"] = $newFileName;

            return $answ;
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Invalid slice range. Page UploaderController.php";

            return $answ;
        }
    }

    public function CyclicSliceCopy(
        $fdrId,
        $newUid,
        $filePath,
        $startCopyTime,
        $endCopyTime,
        $startSliceTime
    ) {
        $newFileName = RuntimeManager::getFilePathByIud($newUid);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $headerLength = $fdrInfo['headerLength'];
        $frameLength = $fdrInfo['frameLength'];

        $handle = fopen($filePath, "r");
        $newHandle = fopen($newFileName, "w");

        if ($headerLength > 0) {
            $fileHeader = fread($handle, $headerLength);
            fwrite($newHandle, $fileHeader);
        }

        $fileSize = filesize ($filePath);
        $Bs = ($fileSize - $headerLength) / ($endCopyTime - $startCopyTime);
        $stB = $Bs * ($startSliceTime - $startCopyTime) + $headerLength;
        $endB = $fileSize;

        $stB = round($stB / $frameLength , 0) * $frameLength + $headerLength;

        $stB2 = $headerLength;
        $endB2 = $stB - 1;

        if ($endB > $fileSize) {
            $endB = $fileSize;
        }

        if ($stB > 0 && $stB < $fileSize && $endB > 0 && $endB <= $fileSize) {
            fseek($handle, $stB);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB) {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }

            fseek($handle, $headerLength);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB2) {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }
            fclose($handle);
            fclose($newHandle);

            $newFileName = basename($newFileName);

            $answ["status"] = "ok";
            $answ["data"] = $newFileName;

            return $answ;
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Invalid slice range. Page UploaderController.php";

            return $answ;
        }
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
            $uploadedFile,
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
        $flightId = $Fl->InsertNewFlight($bort, $voyage,
                $startCopyTime, $fdrId,
                $fdrCode, $performer,
                $departureAirport, $arrivalAirport,
                $uploadedFile, $aditionalInfo, $userId);

        $flightInfo = $Fl->GetFlightInfo($flightId);
        $tableNameAp = $flightInfo['apTableName'];
        $tableNameBp = $flightInfo['bpTableName'];
        $flightId = $flightInfo['id'];
        $fileName = $flightInfo['fileName'];

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
        foreach($cycloApByPrefixes as $prefix => $item) {
            $fileNameAp = $tmpProccStatusFilesDir . "/" . $tableNameAp . "_".$prefix.".tbl";
            $fileNameApArr[$prefix] = $fileNameAp;
            $fileNameApDesc = fopen($fileNameAp, "w");
            $fileNameApDescArr[$prefix] = $fileNameApDesc;
        }

        $fileNameBpArr = array();
        $fileNameBpDescArr = array();
        foreach($cycloBpByPrefixes as $prefix => $item)
        {
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

        if($frameSyncroCode != '') {
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
        unlink($uploadedFile);

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

    public function ProccesFlightException(
        $flightId,
        $tempFilePath
    ) {
        $flightId = intval($flightId);

        $tmpProccStatusFilesDir = RuntimeManager::getRuntimeFolder();
        if (!is_dir($tmpProccStatusFilesDir)) {
            mkdir($tmpProccStatusFilesDir);
        }

        $this->writeStatus ($tempFilePath, 50);

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        $apTableName = $flightInfo["apTableName"];
        $bpTableName = $flightInfo["bpTableName"];
        $excEventsTableName = $flightInfo["exTableName"];
        $startCopyTime = $flightInfo["startCopyTime"];
        $tableGuid = substr($apTableName, 0, 14);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $excListTableName = $fdrInfo["excListTableName"];
        $apGradiTableName = $fdrInfo["gradiApTableName"];
        $bpGradiTableName = $fdrInfo["gradiBpTableName"];
        $stepLength = $fdrInfo["stepLength"];

        if ($excListTableName != "") {
            $excListTableName = $fdrInfo["excListTableName"];
            $apGradiTableName = $fdrInfo["gradiApTableName"];
            $bpGradiTableName = $fdrInfo["gradiBpTableName"];

            $FEx = new FlightException;
            $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
            //Get exc refParam list
            $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

            $exList = $FEx->GetFlightExceptionTable($excListTableName);

            //file can be accesed by ajax what can cause warning
            error_reporting(E_ALL ^ E_WARNING);
            set_time_limit (0);

            //50 because we think previous 50 ware used during proc
            $tmpStatus = 50;
            $newStatus = 50;

            for($i = 0; $i < count($exList); $i++) {
                $newStatus = round(50 + (25 / count($exList) * $i));
                if ($newStatus > $tmpStatus) {
                    $tmpStatus = $newStatus;
                    $this->writeStatus ($tempFilePath, $tmpStatus);
                }

                $curExList = $exList[$i];
                $FEx->PerformProcessingByExceptions($curExList,
                        $flightInfo, $flightExTableName,
                        $apTableName, $bpTableName,
                        $startCopyTime, $stepLength);
            }

            $emitter = new EventEmitter();

            $emitter->on('EventProcessing:start', function ($count) use ($tempFilePath, $tmpStatus) {
                $tmpStatus = 75;
                $this->writeStatus ($tempFilePath, $tmpStatus);
            });

            $emitter->on('EventProcessing:progress', function ($progress, $total) use ($tempFilePath, $tmpStatus, $newStatus) {
                $newStatus = round(75 + (25 / count($total) * $progress));
                if ($newStatus > $tmpStatus) {
                    $tmpStatus = $newStatus;
                    $this->writeStatus ($tempFilePath, $tmpStatus);
                }
            });

            $emitter->on('EventProcessing:end', function () use ($tempFilePath, $tmpStatus) {
                $tmpStatus = 100;
                $this->writeStatus ($tempFilePath, $tmpStatus);
            });

            EventProcessingComponent::processEvents($flightId, $emitter);

            error_reporting(E_ALL);
        }

        unset($fdr);
    }

    private function writeStatus ($path, $status)
    {
        if (file_exists($path)) {
            try {
                $fp = fopen($path, "w");
                fwrite($fp, json_encode($status));
                fclose($fp);
            } catch (Exception $e) { }
        }
    }

    public function DeleteFlight()
    {
         $FC = new FlightComponent;
         $result = $FC->DeleteFlight($this->flightId, intval($this->_user->userInfo['id']));
         unset($FC);

         return $result;
    }

    public function ImportFlight($copiedFilePath)
    {
        $copiedFilesDir = RuntimeManager::getImportFolder() . DIRECTORY_SEPARATOR;

        $zip = new ZipArchive;
        $res = $zip->open($copiedFilePath);
        $importFolderName = sprintf("Imported_%s", date('Y-m-d'));
        $needToCreateImportedFolder = true;

        $Fl = new Flight;
        $fdr = new Fdr;
        $Fr = new Frame;
        $FlE = new FlightException;
        $Fd = new Folder;

        $folderInfo = [];
        $userId = intval($this->_user->userInfo['id']);

        if ($res === TRUE) {
            $i = 0;
            $headerFiles = [];
            do {
                $fileName = $zip->getNameIndex($i);
                if((strpos($fileName, "header") !== false)) {
                    $headerFiles[] = $fileName;
                }
                $i++;
            } while($i < $zip->numFiles);

            foreach ($headerFiles as $name) {
                $zip->extractTo($copiedFilesDir, $name);

                $json = file_get_contents($copiedFilesDir."/".$name);
                unlink($copiedFilesDir."/".$name);
                $flightInfoImported = json_decode($json, true);
                $fdrId = intval($flightInfoImported['id_fdr']);

                $flightId = $Fl->InsertNewFlight(
                    $flightInfoImported['bort'],
                    $flightInfoImported['voyage'],
                    $flightInfoImported['startCopyTime'],
                    $fdrId,
                    $flightInfoImported['bruType'],
                    $flightInfoImported['performer'],
                    $flightInfoImported['departureAirport'],
                    $flightInfoImported['arrivalAirport'],
                    $copiedFilePath,
                    $flightInfoImported['flightAditionalInfo'],
                    $userId
                );

                $flightInfo = $Fl->GetFlightInfo($flightId);

                $tableNameAp = $flightInfo['apTableName'];
                $tableNameBp = $flightInfo['bpTableName'];
                $flightGuid = $flightInfo["guid"];

                $fdrInfo = $fdr->getFdrInfo($fdrId);
                $apPrefixes = $fdr->GetBruApCycloPrefixes($fdrId);
                $bpPrefixes = $fdr->GetBruBpCycloPrefixes($fdrId);

                $apCyclo = $fdr->GetBruApCycloPrefixOrganized($fdrId);

                $tables = $Fl->CreateFlightParamTables(
                    $flightId,
                    $apCyclo,
                    $bpPrefixes
                );

                $apTables = $flightInfoImported["apTables"];

                for($j = 0; $j < count($apTables); $j++) {
                    $zip->extractTo($copiedFilesDir, $apTables[$j]["file"]);
                    if (file_exists($copiedFilesDir.$apTables[$j]["file"])) {
                        $Fr->LoadFileToTable($tableNameAp . "_" . $apTables[$j]["pref"], $copiedFilesDir.$apTables[$j]["file"]);
                        unlink($copiedFilesDir.$apTables[$j]["file"]);
                    }
                }

                $bpTables = $flightInfoImported["bpTables"];
                for ($j = 0; $j < count($bpTables); $j++) {
                    $zip->extractTo($copiedFilesDir, $bpTables[$j]["file"]);
                    if(file_exists($copiedFilesDir.$bpTables[$j]["file"])) {
                        $Fr->LoadFileToTable($tableNameBp . "_" . $bpTables[$j]["pref"], $copiedFilesDir.$bpTables[$j]["file"]);
                        unlink($copiedFilesDir.$bpTables[$j]["file"]);
                    }
                }

                if (isset($flightInfoImported["exTableName"])
                    && ($flightInfoImported["exTableName"] != "")
                ) {
                    $flightExTableName = $FlE->CreateFlightExceptionTable($flightId, $flightGuid);

                    $exTables = $flightInfoImported["exTables"];
                    $zip->extractTo($copiedFilesDir, $exTables);
                    $Fr->LoadFileToTable($flightExTableName, $copiedFilesDir.$exTables);
                    if (file_exists($copiedFilesDir.$exTables)) {
                        unlink($copiedFilesDir.$exTables);
                    }
                }

                if (isset($flightInfoImported["eventsTable"])
                    && ($flightInfoImported["eventsTable"] != "")
                ) {
                    $link = LinkFactory::create();
                    $flightEventTable = FlightEvent::createTable($link, $flightGuid);
                    LinkFactory::destroy($link);

                    $fileName = $flightInfoImported["eventsTable"];
                    $zip->extractTo($copiedFilesDir, $fileName);
                    $Fr->LoadFileToTable($flightEventTable, $copiedFilesDir.$fileName);
                    if (file_exists($copiedFilesDir.$fileName)) {
                        unlink($copiedFilesDir.$fileName);
                    }
                }

                if (isset($flightInfoImported["settlementsTable"])
                    && ($flightInfoImported["settlementsTable"] != "")
                ) {
                    $link = LinkFactory::create();
                    $flightSettlementTable = FlightSettlement::createTable($link, $flightGuid);
                    LinkFactory::destroy($link);

                    $fileName = $flightInfoImported["settlementsTable"];
                    $zip->extractTo($copiedFilesDir, $fileName);
                    $Fr->LoadFileToTable($flightSettlementTable, $copiedFilesDir.$fileName);
                    if (file_exists($copiedFilesDir.$fileName)) {
                        unlink($copiedFilesDir.$fileName);
                    }
                }

                if (count($headerFiles) > 1) {
                    if ($needToCreateImportedFolder) {
                        $folderInfo = $Fd->CreateFolder($importFolderName, 0, $userId);
                        $needToCreateImportedFolder = false;
                    }

                    if (isset($folderInfo['folderId'])) {
                        $Fd->PutFlightInFolder($flightId, $folderInfo['folderId'], $userId);
                    } else {
                        $Fd->PutFlightInFolder($flightId, 0, $userId); //we put currently uploaded file in root
                    }
                } else {
                    //into root if only one
                    $Fd->PutFlightInFolder($flightId, 0, $userId); //we put currently uploaded file in root
                }
            }

            $zip->close();
            unlink($copiedFilePath);

            unset($zip);
            unset($Fl);
            unset($FlE);
            unset($Fr);
            unset($Fd);
            unset($fdr);

            if(count($headerFiles) <= 0) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */

    public function flightShowUploadingOptions($data)
    {
        if (!isset($data['index'])
            || !isset($data['uploadingUid'])
            || !isset($data['fdrId'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $index = $data['index'];
        $fdrId = intval($data['fdrId']);
        $uploadingUid = strval($data['uploadingUid']);

        $calibrationId = null;
        if(isset($data['calibrationId'])
            && !empty($data['calibrationId'])
            && is_int(intval($data['calibrationId']))
        ) {
            $calibrationId = intval($data['calibrationId']);
        }

        $uploadedFile = RuntimeManager::getFilePathByIud($uploadingUid);
        $flightParamsSrt = $this->ShowFlightParams(
            $index,
            $uploadingUid,
            $fdrId,
            $uploadedFile,
            $calibrationId
        );

        $answ["status"] = "ok";
        $answ["data"] = $flightParamsSrt;
        echo(json_encode($answ));
    }

    public function flightUploaderPreview($data)
    {
        if (!isset($data['fdrId'])
            || !isset($data['uploadingUid'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". " .
                "Action: " . $this->action . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $fdrId = intval($data['fdrId']);
        $uploadingUid = strval($data['uploadingUid']);

        $uploadedFile = RuntimeManager::getFilePathByIud($uploadingUid);
        $resp = $this->CopyPreview($fdrId, $uploadedFile);

        echo(json_encode($resp));
    }

    public function flightCutFile($data)
    {
        if(!isset($data['fdrId'])
            || !isset($data['file'])
            || !isset($data['newUid'])
            || !isset($data['uploadingUid'])
            || !isset($data['startCopyTime'])
            || !isset($data['endCopyTime'])
            || !isset($data['startSliceTime'])
            || !isset($data['endSliceTime'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". " .
                "Action: " . $this->action . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $fdrId = intval($data['fdrId']);
        $filePath = $data['file'];
        $newUid = $data['newUid'];
        $uploadingUid = $data['uploadingUid'];

        $startCopyTime = $data['startCopyTime'];
        $endCopyTime = $data['endCopyTime'];
        $startSliceTime = $data['startSliceTime'];
        $endSliceTime = $data['endSliceTime'];

        $res = $this->CutCopy(
            $fdrId,
            $newUid,
            $filePath,
            $startCopyTime,
            $endCopyTime,
            $startSliceTime,
            $endSliceTime
        );

        $res['newUid'] = $newUid;
        echo(json_encode($res));
    }

    public function flightCyclicSliceFile($data)
    {
        if(!isset($data['fdrId'])
            || !isset($data['file'])
            || !isset($data['uploadingUid'])
            || !isset($data['newUid'])
            || !isset($data['startCopyTime'])
            || !isset($data['endCopyTime'])
            || !isset($data['startSliceTime'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". " .
                "Action: " . $this->action . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $fdrId = intval($data['fdrId']);
        $filePath = $data['file'];
        $uploadingUid = $data['uploadingUid'];
        $newUid = $data['newUid'];
        $startCopyTime = $data['startCopyTime'];
        $endCopyTime = $data['endCopyTime'];
        $startSliceTime = $data['startSliceTime'];

        $resp = $this->CyclicSliceCopy(
                $fdrId,
                $newUid,
                $filePath,
                $startCopyTime,
                $endCopyTime,
                $startSliceTime
            );

        $resp['newUid'] = $newUid;

        echo(json_encode($resp));
    }

    public function flightProcces($data)
    {
        if(!isset($data['fdrId'])
            || !isset($data['fileName'])
            || !isset($data['uploadingUid'])
            || !isset($data['flightInfo'])
            || !isset($data['flightAditionalInfo'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". " .
                "Action: " . $this->action . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $fdrId = intval($data['fdrId']);
        $uploadedFile = $data['fileName'];

        $uploadingUid = $data['uploadingUid'];
        $receivedFlightInfo = $data['flightInfo'];
        $receivedFlightAditionalInfo = $data['flightAditionalInfo'];
        $flightInfo = array();
        $flightAditionalInfo = array();

        $calibrationId = null;
        if(isset($data['calibrationId'])
            && !empty($data['calibrationId'])
            && is_int(intval($data['calibrationId']))
        ) {
            $calibrationId = intval($data['calibrationId']);
        }

        //in such way it was passed in js because of imposible to do it by usual asoc arr
        for($i = 0; $i < count($receivedFlightInfo); $i+=2) {
            if((string)$receivedFlightInfo[$i + 1] != '') {
                $flightInfo[(string)$receivedFlightInfo[$i]] =
                    (string)$receivedFlightInfo[$i + 1];
            } else {
                $flightInfo[(string)$receivedFlightInfo[$i]] = "x";
            }
        }

        $aditionalInfoVars = '';
        if($receivedFlightAditionalInfo != '0') {
            for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2) {
                $flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] =
                    (string)$receivedFlightAditionalInfo[$i + 1];
            }

            $aditionalInfoVars = json_encode($flightAditionalInfo);
        }

        $bort = $flightInfo["bort"];
        $voyage = $flightInfo["voyage"];
        $copyCreationTime = $flightInfo["copyCreationTime"];
        $copyCreationDate = $flightInfo["copyCreationDate"];
        $performer = $flightInfo["performer"];
        $departureAirport = $flightInfo["departureAirport"];
        $arrivalAirport = $flightInfo["arrivalAirport"];
        $totalPersentage = 100;

        $progressFileName = RuntimeManager::createProgressFile($uploadingUid);

        $this->ProccessFlightData($progressFileName,
            $bort,
            $voyage,
            $copyCreationTime,
            $copyCreationDate,
            $fdrId,
            $performer,
            $departureAirport,
            $arrivalAirport,
            $aditionalInfoVars,
            $uploadedFile,
            $totalPersentage,
            $calibrationId
        );

        RuntimeManager::unlinkRuntimeFile($progressFilePath);

        $answ = array(
                "status" => "ok",
                "data" => $uploadedFile
        );
        echo(json_encode($answ));
    }

    public function flightProccesAndCheck($data)
    {
        if (!isset($data['fdrId'])
            || !isset($data['uploadingUid'])
            || !isset($data['fileName'])
            || !isset($data['flightInfo'])
            || !isset($data['flightAditionalInfo'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". " .
                "Action: " . $this->action . ". Page UploaderController.php";
            echo(json_encode($answ));
        }

        $uploadingUid = $data['uploadingUid'];
        $fdrId = intval($data['fdrId']);
        $uploadedFile = $data['fileName'];

        $receivedFlightInfo = $data['flightInfo'];
        $receivedFlightAditionalInfo = $data['flightAditionalInfo'];
        $flightInfo = array();
        $flightAditionalInfo = array();

        $calibrationId = null;
        if (isset($data['calibrationId'])
            && !empty($data['calibrationId'])
            && is_int(intval($data['calibrationId']))
        ) {
            $calibrationId = intval($data['calibrationId']);
        }

        //in such way it was passed in js because of imposible to do it by usual aasoc arr
        for ($i = 0; $i < count($receivedFlightInfo); $i+=2) {
            if ((string)$receivedFlightInfo[$i + 1] != '') {
                $flightInfo[(string)$receivedFlightInfo[$i]] =
                    (string)$receivedFlightInfo[$i + 1];
            } else {
                $flightInfo[(string)$receivedFlightInfo[$i]] = "x";
            }
        }

        $aditionalInfoVars = '';
        if ($receivedFlightAditionalInfo != 0) {
            for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2) {
                $flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] =
                    (string)$receivedFlightAditionalInfo[$i + 1];
            }

            $aditionalInfoVars = json_encode($flightAditionalInfo);
        }

        $bort = $flightInfo["bort"];
        $voyage = $flightInfo["voyage"];
        $copyCreationTime = $flightInfo["copyCreationTime"];
        $copyCreationDate = $flightInfo["copyCreationDate"];
        $performer = $flightInfo["performer"];
        $departureAirport = $flightInfo["departureAirport"];
        $arrivalAirport = $flightInfo["arrivalAirport"];
        $totalPersentage = 50;

        $progressFilePath = RuntimeManager::createProgressFile($uploadingUid);

        $flightId = $this->ProccessFlightData($progressFilePath,
            $bort,
            $voyage,
            $copyCreationTime,
            $copyCreationDate,
            $fdrId,
            $performer,
            $departureAirport,
            $arrivalAirport,
            $aditionalInfoVars,
            $uploadedFile,
            $totalPersentage,
            $calibrationId
        );

        $this->ProccesFlightException($flightId,
            $progressFilePath
        );

        RuntimeManager::unlinkRuntimeFile($progressFilePath);

        echo(json_encode([
            "status" => "complete",
            "uploadingUid" => $uploadingUid
        ]));

        exit;
    }

    public function flightEasyUpload($data)
    {
        if (!isset($_POST['fdrId'])) {
            throw new Exception("Necessary param fdrId not passed.", 1);
        }

        if (!isset($_POST['uploadingUid'])) {
            throw new Exception("Necessary param uploadingUid not passed.", 1);
        }

        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new Exception("Necessary param flightFile not passed.", 1);
        }

        $fdrId = intval($_POST['fdrId']);
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Integer is required. Passed: "
                . json_encode($fdrId), 1);
        }


        $uploadingUid = strval($_POST['uploadingUid']);
        if (!is_string($_POST['uploadingUid'])) {
            throw new Exception("Incorrect uploadingUid passed. String is required. Passed: "
                . json_encode($_POST['uploadingUid']), 1);
        }

        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $userId = intval($this->_user->userInfo['id']);

        if (!$this->_user->checkFdrAvailable($fdrId, $userId)) {
            throw new Exception("Trying to access unavaliable fdrType."
                . " User id: " . $userId
                . " Fdr id: " . $fdrId, 1);
        }

        $calibrationId = null;
        if(isset($data['calibrationId'])
            && !empty($data['calibrationId'])
            && is_int(intval($data['calibrationId']))
        ) {
            $calibrationId = intval($data['calibrationId']);
        }

        $flightInfoFromHeader = $this->ReadHeader($fdrId, $fileName);

        $bort = "x";
        if(isset($flightInfoFromHeader["bort"])) {
            $bort = $flightInfoFromHeader["bort"];
        }

        $voyage = "x";
        if(isset($flightInfoFromHeader["voyage"])) {
            $voyage = $flightInfoFromHeader["voyage"];
        }

        $departureAirport = "x";
        if(isset($flightInfoFromHeader["departureAirport"])) {
            $departureAirport = $flightInfoFromHeader["departureAirport"];
        }

        $arrivalAirport = "x";
        if(isset($flightInfoFromHeader["arrivalAirport"])) {
            $arrivalAirport = $flightInfoFromHeader["arrivalAirport"];
        }

        $copyCreationTime = "00:00:00";
        $copyCreationDate = "2000-01-01";
        if(isset($flightInfoFromHeader['startCopyTime'])) {
            $startCopyTime = strtotime($flightInfoFromHeader['startCopyTime']);
            $copyCreationTime = date('H:i:s', $startCopyTime);
            $copyCreationDate = date('Y-m-d', $startCopyTime);
        }

        $performer = null;

        $aditionalInfoVars = $this->CheckAditionalInfoFromHeader($fdrId, $flightInfoFromHeader);
        $totalPersentage = 50;

        $progressFilePath = RuntimeManager::createProgressFile($uploadingUid);

        $flightId = $this->ProccessFlightData($progressFilePath,
                $bort,
                $voyage,
                $copyCreationTime,
                $copyCreationDate,
                $fdrId,
                $performer,
                $departureAirport,
                $arrivalAirport,
                $aditionalInfoVars,
                $fileName,
                $totalPersentage,
                $calibrationId
        );

        $this->ProccesFlightException($flightId, $progressFilePath);

        RuntimeManager::unlinkRuntimeFile($progressFilePath);

        echo(json_encode([
            "status" => "complete",
            "uploadingUid" => $uploadingUid
        ]));
        exit;
    }

    public function itemImport($data)
    {
        if (!isset($_FILES['flightFileArchive']['tmp_name'])) {
            throw new Exception("Necessary param flightFileArchive not passed.", 1);
        }

        $fileName = strval($_FILES['flightFileArchive']['tmp_name']);
        $result = $this->ImportFlight($fileName);

        $answ = array();
        if ($result) {
            $answ = [
                'status' => 'ok'
            ];

            $this->RegisterActionExecution($this->action, "executed", $fileName, "fileName");
        }
        else
        {
            $answ['status'] = 'err';
            $answ['data']['error'] = 'Error during flight import.';
            $this->RegisterActionReject($this->action, "rejected", 0, $answ['data']['error']);
        }

        echo json_encode($answ);
        exit;
    }

    public function getUploadingStatus($data)
    {
        if (!isset($data['uploadingUid'])) {
            throw new Exception("Necessary param uploadingUid not passed.", 1);
        }

        $uploadingUid = strval($data['uploadingUid']);
        if (!is_string($data['uploadingUid'])) {
            throw new Exception("Incorrect uploadingUid passed. String is required. Passed: "
                . json_encode($data['uploadingUid']), 1);
        }

        $progressFilePath = RuntimeManager::getProgressFilePath($uploadingUid);

        //file can be accesed by ajax while try to open what can cause warning
        error_reporting(0);

        if (file_exists($progressFilePath)) {
            try {
                $val = file_get_contents($progressFilePath);
            } catch(Exception $e) {
                echo(json_encode([
                    "status" => "busy",
                    "progress" => -1,
                    "uploadingUid" => $uploadingUid
                ]));
                exit;
            }

            $val = preg_replace("/[^0-9]/","",$val);

            if (!is_int(intval($val))) {
                echo(json_encode([
                    "status" => "busy",
                    "progress" => -1,
                    "uploadingUid" => $uploadingUid
                ]));
                exit;
            }

            $val = intval($val);

            if ($val >= 0 && $val <= 100) {
                echo(json_encode([
                    "status" => "ok",
                    "progress" => $val,
                    "uploadingUid" => $uploadingUid
                ]));
                exit;
            }
        } else {
            echo(json_encode([
                "status" => "complete",
                "progress" => 101,
                "uploadingUid" => $uploadingUid
            ]));
            exit;
        }
    }

    public function copyToRuntime($data)
    {
        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new Exception("Necessary param flightFile not passed.", 1);
        }

        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $userId = intval($this->_user->userInfo['id']);

        $storedName = RuntimeManager::storeUploadedFile($fileName);

        echo (json_encode([
            'status' => 'ok',
            'file' => $storedName
        ]));
    }

    public function storeFlightFile ($args)
    {
        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new Exception("Necessary param flightFile not passed.", 1);
        }

        if (!isset($_POST['uploadingUid'])) {
            throw new Exception("Necessary param uploadingUid not passed.", 1);
        }

        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $uploadingUid = strval($_POST['uploadingUid']);
        $userId = intval($this->_user->userInfo['id']);

        $storedName = RuntimeManager::storeUploadedFile($fileName, $uploadingUid);

        echo (json_encode([
            'status' => 'ok'
        ]));
    }
}
