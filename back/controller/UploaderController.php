<?php

namespace Controller;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Evenement\EventEmitter;

use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use ZipArchive;
use Exception;
use \L;

class UploaderController extends BaseController
{
    public function storeFlightFileAction ($flightFile, $uploadingUid)
    {
        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new BadRequestException("necessary param flightFile not passed.");
        }

        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $uploadingUid = strval($uploadingUid);

        $storedName = $this->dic()
            ->get('runtimeManager')
            ->storeUploadedFile($fileName, $uploadingUid);

        return json_encode('ok');
    }

    public function flightUploaderPreviewAction ($uploadingUid, $fdrId)
    {
        $fdrId = intval($fdrId);

        $uploadedFile = $this->dic()->get('runtimeManager')->getFilePathByIud($uploadingUid);
        $response = $this->dic()->get('flightProcessor')->preview($fdrId, $uploadedFile);

        return json_encode($response);
    }

    public function flightUploadingOptionsAction(
        $index,
        $uploadingUid,
        $fdrId,
        $calibrationId = null
    ) {
        $fdrId = intval($fdrId);
        $uploadingUid = strval($uploadingUid);

        if ($calibrationId !== null) {
            $calibrationId = intval($calibrationId);
        }

        $uploadedFile = $this->dic()
            ->get('runtimeManager')
            ->getFilePathByIud($uploadingUid);

        $flightParamsSrt = $this->ShowFlightParams(
            $index,
            $uploadingUid,
            $fdrId,
            $uploadedFile,
            $calibrationId
        );

        return json_encode([
            'status' => 'ok',
            'data' => $flightParamsSrt
        ]);
    }

    public function flightProccesAndCheckAction(
        $fdrId,
        $uploadingUid,
        $receivedCalibrationId,
        $uploadedFile,
        $receivedFlightInfo
    ) {
        $receivedAditionalInfo = [];
        if (isset($_POST['flightAditionalInfo'])
            && is_array($_POST['flightAditionalInfo'])
        ) {
            $receivedAditionalInfo = $_POST['flightAditionalInfo'];
        }

        $fdrId = intval($fdrId);

        $flightInfo = [];
        $flightAditionalInfo = [];

        $calibrationId = null;
        if (($receivedCalibrationId !== null)
            && !empty($receivedCalibrationId)
            && is_int(intval($receivedCalibrationId))
        ) {
            $calibrationId = intval($receivedCalibrationId);
        }

        $userId = $this->user()->getId();

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
        if ($receivedAditionalInfo != 0) {
            for ($i = 0; $i < count($receivedAditionalInfo); $i+=2) {
                $flightAditionalInfo[(string)$receivedAditionalInfo[$i]] =
                    (string)$receivedAditionalInfo[$i + 1];
            }

            $aditionalInfoVars = json_encode($flightAditionalInfo);
        }

        $flightInfo['aditionalInfo'] = $aditionalInfoVars;

$storedFlightFile = $uploadedFile;
        /*$storedFlightFile = $this->dic()
            ->get('runtimeManager')
            ->storeFlight($uploadedFile);

        $flightInfo['path'] = $storedFlightFile;

        $flight = $this->dic()
            ->get('flight')
            ->insert(
                $uploadingUid,
                $flightInfo,
                $fdrId,
                $userId,
                $calibrationId
            );*/

        $totalPersentage = 50;
        $progressFilePath = $this->dic()
            ->get('runtimeManager')
            ->createProgressFile($uploadingUid);

        $this->dic()
            ->get('flightProcessor')
            ->process(
                $storedFlightFile,
                $progressFilePath,
                $totalPersentage,
                $fdrId,
                $calibrationId
            );

        exit;

        $this->dic()
            ->get('flightProcessor')
            ->proccesFlightException(
                $flight->getId(),
                $progressFilePath
            );

        $this->dic()
            ->get('runtimeManager')
            ->unlinkRuntimeFile($progressFilePath);

        return json_encode([
            'status' => 'complete',
            'uploadingUid' => $uploadingUid,
            'item' => $this->em()
                ->getRepository('Entity\FlightToFolder')
                ->getTreeItem($flight->getId(), $userId)
        ]);
    }

    public function ShowFlightParams(
        $index,
        $uploadingUid,
        $fdrId,
        $filePath,
        $calibrationId = null
    ) {
        $fileName = basename($filePath);

        $fdr = $this->em()->find('\Entity\Fdr', $fdrId);
        $previewParams = $fdr->getPreviewParams();

        $flightInfoFromHeader = $this->dic()
            ->get('flightProcessor')
            ->readHeader($fdrId, $filePath);

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
            "<table style='width:100%'><tr><td style='width:" . $fileInfoColumnWidth . "px; padding:10px;'>" .

            "<table border='0' style='margin-bottom:15px;'>" .
            "<tr>" .
            "<td>" . L::uploader_bruType . "</td>";

        $flightParamsSrt .= "<td>"
            ."<input id='bruType' name='fdrName' class='FlightUploadingInputs form-control' value='" . $fdr->getName() .
            "' readonly /></td>" .
            "</tr><tr>";

        $bortFromHeader = "";
        if(isset($flightInfoFromHeader["bort"])) {
            $bortFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["bort"]);
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_bortNum . "</td>" .
            "<td><input id='bort' name='bort' type='text' class='FlightUploadingInputs form-control' ".
            "value='" . $bortFromHeader . "'/></td>" .
            "</tr>";

        $voyageFromHeader = "";
        if (isset($flightInfoFromHeader["voyage"])) {
            $voyageFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["voyage"]);
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_voyage . "</td>" .
            "<td><input id='voyage' name='voyage' type='text' class='FlightUploadingInputs form-control' ".
            "value='" . $voyageFromHeader . "'/></td>" .
            "</tr>";

        $departureAirportFromHeader = "";
        if(isset($flightInfoFromHeader["departureAirport"])) {
            $departureAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["departureAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_departureAirport . "</td>" .
                "<td><input id='departureAirport' name='departureAirport' type='text' class='FlightUploadingInputs form-control' ".
                "value='" . $departureAirportFromHeader . "'/></td>" .
                "</tr>";

        $arrivalAirportFromHeader = "";
        if (isset($flightInfoFromHeader["arrivalAirport"])) {
            $arrivalAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["arrivalAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_arrivalAirport . "</td>" .
                "<td><input id='arrivalAirport' name='arrivalAirport' type='text' class='FlightUploadingInputs form-control' ".
                "value='" . $arrivalAirportFromHeader . "'/></td>" .
                "</tr>";

        $captainFromHeader = "";
        if (isset($flightInfoFromHeader["captain"])) {
            $captainFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["captain"]);
        }
        $flightParamsSrt .= "<tr><td>" . L::uploader_capitan . "</td>" .
                "<td><input id='captain' name='captain' type='text' class='FlightUploadingInputs form-control' ".
                "value='" . $captainFromHeader . "'/></td>" .
                "</tr>";

        $copyCreationTimeFromHeader = "";
        $copyCreationDateFromHeader = "";
        if (isset($flightInfoFromHeader["copyCreationTime"])
            && isset($flightInfoFromHeader["copyCreationDate"])
        ) {
            $copyCreationTimeFromHeader = $flightInfoFromHeader["copyCreationTime"];
            $copyCreationDateFromHeader = $flightInfoFromHeader["copyCreationDate"];
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_flightDate . "</td>" .
                "<td><input id='copyCreationTime' name='copyCreationTime' type='time' class='FlightUploadingInputs form-control' " .
                "value='" . $copyCreationTimeFromHeader . "'/> <br>" .
                "<input id='copyCreationDate' name='copyCreationDate' type='date' class='FlightUploadingInputs form-control'" .
                "value='" . $copyCreationDateFromHeader . "'/>" .
                "</td></tr>";

        $flightParamsSrt .= "<tr><td>" . L::uploader_performer . "</td>" .
                "<td><input id='performer' name='performer' type='text' class='FlightUploadingInputs form-control' value='" .
                $this->user()->getLogin() . "'/></td>" .
                "</tr>";

        if ($fdr->getAditionalInfo() != '') {
            $aditionalInfo = [];
            if (strpos($fdr->getAditionalInfo(), ";") !== 0) {
                $aditionalInfo = explode(";", $fdr->getAditionalInfo());
                $aditionalInfo  = array_map('trim', $aditionalInfo);
            } else {
                $aditionalInfo = (array)trim($fdr->getAditionalInfo());
            }

            error_reporting(E_ERROR);
            for ($i = 0; $i < count($aditionalInfo); $i++) {
                $label = (L('uploader_'.$aditionalInfo[$i]) !== null)
                    ? L('uploader_'.$aditionalInfo[$i])
                    : $aditionalInfo[$i];

                $flightParamsSrt .= "<tr><td>" . $label . "</td>";

                $aditionalInfoFromHeader = "";
                if (isset($flightInfoFromHeader[$aditionalInfo[$i]])) {
                    $aditionalInfoFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',
                        $flightInfoFromHeader[$aditionalInfo[$i]]);
                }

                $flightParamsSrt .= "<td><input id='" . $aditionalInfo[$i] . "'
                        name='aditionalInfo" . $i . "' type='text' class='FlightUploadingInputsAditionalInfo form-control' " .
                        "value='" . $aditionalInfoFromHeader . "'/></td>
                </tr>";
            }
            error_reporting(E_ALL);
        }

        $flightParamsSrt .= "<tr><td>" . L::uploader_execProc . "</td>" .
            "<td><input id='execProc' type='checkbox' checked class='FlightUploadingInputs form-control'"
            ." style='height: 18px;'/></td>
            </tr>";

        $flightParamsSrt .= "<tr><td>" . L::uploader_ignoreDueUploading . "</td>" .
                "<td><input id='ignoreDueUploading".$index."' type='checkbox' class='FlightUploadingInputs form-control'"
                ." style='height: 18px;'/></td>
                </tr>";

        $flightParamsSrt .= "</table>";

        //priview column
        $flightParamsSrt .= "</td><td align='center' style='vertical-align:top; padding-top:7px; position: relative;'>";

        $previewParams = trim($previewParams);
        if ($previewParams != '') {
            $flightParamsSrt .= "<div id='loadingBox".$index."' width='100%' style='position:absolute;top: 110px; left: calc(50% - 30px);'>
                    <img src='/front/style/images/loading.gif'/></div>";

            $flightParamsSrt .= "<div id='previewChartContainer".$index."' " .
                    "style='width:95%; border:0;'>
                <div id='previewChartPlaceholder".$index."' " .
                    "data-index='".$index."' " .
                    "class='PreviewChartPlaceholder'></div>
                </div>";

            $flightParamsSrt .= "<button id='sliceFlightButt".$index."' ".
                    "class='SliceFlightButt btn btn-default' ".
                    "data-index='".$index."' " .
                    "data-uploading-uid='" . $uploadingUid . "' " .
                    "data-file='".$filePath."' " .
                    "data-fdr-id='".$fdrId."' " .
                    "class='Button'>".
                    L::uploader_slice . "</button>";

            $flightParamsSrt .= "<button id='sliceCyclicFlightButt".$index."' ".
                    "class='SliceCyclicFlightButt btn btn-default' ".
                    "data-index='".$index."' " .
                    "data-uploading-uid='" . $uploadingUid . "' " .
                    "data-file='".$filePath."' " .
                    "data-fdr-id='".$fdrId."' " .
                    "class='Button'>".
                    L::uploader_sliceCyclic . "</button>";
        }

        $flightParamsSrt .= "</br></form></div>";

        $flightParamsSrt .= "</td></tr></table></div>";

        return $flightParamsSrt;
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
            throw new BadRequestException(json_encode($data));
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
        return json_encode($res);
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
            throw new BadRequestException(json_encode($data));
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

        return json_encode($resp);
    }

    public function flightProcces($data)
    {
        if(!isset($data['fdrId'])
            || !isset($data['fileName'])
            || !isset($data['uploadingUid'])
            || !isset($data['flightInfo'])
            || !isset($data['flightAditionalInfo'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $fdrId = intval($data['fdrId']);
        $uploadedFile = $data['fileName'];
        $userId = intval($this->_user->userInfo['id']);

        $uploadingUid = $data['uploadingUid'];
        $receivedFlightInfo = $data['flightInfo'];
        $receivedAditionalInfo = $data['flightAditionalInfo'];
        $flightInfo = array();
        $flightAditionalInfo = array();

        $calibrationId = null;
        if (isset($data['calibrationId'])
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
        if($receivedAditionalInfo != '0') {
            for($i = 0; $i < count($receivedAditionalInfo); $i+=2) {
                $flightAditionalInfo[(string)$receivedAditionalInfo[$i]] =
                    (string)$receivedAditionalInfo[$i + 1];
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
                "data" => $uploadedFile,
                "item" => FlightComponent::getTreeItem($flightId, $userId)
        );
        return json_encode($answ);
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

        return json_encode([
            "status" => "complete",
            "uploadingUid" => $uploadingUid,
            "item" => FlightComponent::getTreeItem($flightId, $userId)
        ]);
    }

    public function itemImport($data)
    {
        if (!isset($_FILES['flightFileArchive']['tmp_name'])) {
            throw new Exception("Necessary param flightFileArchive not passed.", 1);
        }

        $fileName = strval($_FILES['flightFileArchive']['tmp_name']);
        $result = $this->ImportFlight($fileName);

        return json_encode('ok');
    }

    public function getUploadingStatus($data)
    {
        if (!isset($data['uploadingUid'])) {
            throw new BadRequestException(json_encode($data));
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
                return json_encode([
                    "status" => "busy",
                    "progress" => -1,
                    "uploadingUid" => $uploadingUid
                ]);
            }

            $val = preg_replace("/[^0-9]/","",$val);

            if (!is_int(intval($val))) {
                return json_encode([
                    "status" => "busy",
                    "progress" => -1,
                    "uploadingUid" => $uploadingUid
                ]);
            }

            $val = intval($val);

            if ($val >= 0 && $val <= 100) {
                return json_encode([
                    "status" => "ok",
                    "progress" => $val,
                    "uploadingUid" => $uploadingUid
                ]);
            }
        } else {
            return json_encode([
                "status" => "complete",
                "progress" => 101,
                "uploadingUid" => $uploadingUid
            ]);
        }
    }

    public function copyToRuntime($data)
    {
        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new BadRequestException(json_encode($_FILES));
        }

        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $userId = intval($this->_user->userInfo['id']);

        $storedName = RuntimeManager::storeUploadedFile($fileName);

        return json_encode([
            'status' => 'ok',
            'file' => $storedName
        ]);
    }


}
