<?php

namespace Controller;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use ZipArchive;
use Exception;
use \L;

class UploaderController extends BaseController
{
    public function storeFlightFileAction ($uploadingUid)
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

    public function flightProccesAction(
        $fdrId,
        $uploadingUid,
        $calibrationId,
        $fileName,
        $flightInfo,
        $flightAditionalInfo = [],
        $check = true
    ) {
        $fdrId = intval($fdrId);

        if (($calibrationId !== null)
            && !empty($calibrationId)
            && is_int(intval($calibrationId))
        ) {
            $calibrationId = intval($calibrationId);
        }

        $userId = $this->user()->getId();

        //in such way it was passed in js because of imposible to do it by usual aasoc arr
        $flightInfoParsed = [];
        for ($i = 0; $i < count($flightInfo); $i+=2) {
            if ((string)$flightInfo[$i + 1] != '') {
                $flightInfoParsed[(string)$flightInfo[$i]] =
                    (string)$flightInfo[$i + 1];
            } else {
                $flightInfoParsed[(string)$flightInfo[$i]] = "x";
            }
        }

        $aditionalInfoVars = '';
        $flightAditionalInfoParsed = [];
        if ($flightAditionalInfo != 0) {
            for ($i = 0; $i < count($flightAditionalInfo); $i+=2) {
                $flightAditionalInfoParsed[(string)$flightAditionalInfo[$i]] =
                    (string)$flightAditionalInfo[$i + 1];
            }

            $aditionalInfoVars = json_encode($flightAditionalInfo);
        }

        $flightInfoParsed['aditionalInfo'] = $aditionalInfoVars;

        $storedFlightFile = $this->dic()
            ->get('runtimeManager')
            ->storeFlight($fileName);

        $flightInfo['path'] = $storedFlightFile;

        $flight = $this->dic()
            ->get('flight')
            ->insert(
                $uploadingUid,
                $flightInfoParsed,
                $fdrId,
                $userId,
                $calibrationId
            );

        $totalPersentage = 50;

        $this->dic()
            ->get('flightProcessor')
            ->process(
                $uploadingUid,
                $storedFlightFile,
                0,
                $totalPersentage,
                $fdrId,
                $calibrationId
            );

        $this->dic()
            ->get('eventProcessor')
            ->analyze($flight);

        $file = $this->dic()
            ->get('runtimeManager')
            ->getTemporaryFileDesc(
                $this->params()->folders->uploadingStatus,
                $uploadingUid,
                'close'
            );

        if (file_exists($file->path)) {
            unlink($file->path);
        }

        return json_encode([
            'status' => 'complete',
            'uploadingUid' => $uploadingUid,
            'item' => $this->em()
                ->getRepository('Entity\FlightToFolder')
                ->getTreeItem($flight->getId(), $userId)
        ]);
    }

    public function flightEasyUploadAction(
        $fdrId,
        $uploadingUid,
        $calibrationId = null
    ) {
        if (!isset($_FILES['flightFile']['tmp_name'])) {
            throw new Exception("Necessary param flightFile not passed.", 1);
        }

        $fdrId = intval($fdrId);
        $uploadingUid = strval($uploadingUid);
        $fileName = strval($_FILES['flightFile']['tmp_name']);
        $userId = $this->user()->getId();

        if (!$this->dic()->get('fdr')->isAvaliable($fdrId)) {
            throw new Exception("Trying to access unavaliable fdrType."
                . " User id: " . $userId
                . " Fdr id: " . $fdrId, 1);
        }

        $flightInfoFromHeader = $this->dic()
            ->get('flightProcessor')
            ->readHeader($fdrId, $fileName);

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

        $aditionalInfoVars = $this->dic()
            ->get('flightProcessor')
            ->checkAditionalInfoFromHeader($fdrId, $flightInfoFromHeader);

        $totalPersentage = 50;

        $storedFlightFile = $this->dic()
            ->get('runtimeManager')
            ->storeFlight($fileName);

        $flightInfo = array_merge(
            $flightInfoFromHeader,
            ['aditionalInfo' => $aditionalInfoVars],
            ['path' => $storedFlightFile]

        );

        $flight = $this->dic()
            ->get('flight')
            ->insert(
                $uploadingUid,
                $flightInfo,
                $fdrId,
                $userId,
                $calibrationId
            );

        $this->dic()
            ->get('flightProcessor')
            ->process(
                $uploadingUid,
                $storedFlightFile,
                0,
                50,
                $fdrId,
                $calibrationId
            );

        $this->dic()
            ->get('eventProcessor')
            ->analyze($flight);

        $file = $this->dic()
            ->get('runtimeManager')
            ->getTemporaryFileDesc(
                $this->params()->folders->uploadingStatus,
                $uploadingUid,
                'close'
            );

        if (file_exists($file->path)) {
            unlink($file->path);
        }

        return json_encode([
            "status" => "complete",
            "uploadingUid" => $uploadingUid,
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

    public function itemImport($data)
    {
        if (!isset($_FILES['flightFileArchive']['tmp_name'])) {
            throw new Exception("Necessary param flightFileArchive not passed.", 1);
        }

        $fileName = strval($_FILES['flightFileArchive']['tmp_name']);
        $result = $this->ImportFlight($fileName);

        return json_encode('ok');
    }

    public function getUploadingStatusAction($uploadingUid)
    {
        $progressFile = $this->dic()->get('runtimeManager')
            ->getTemporaryFileDesc(
                $this->params()->folders->uploadingStatus,
                $uploadingUid,
                'open',
                'r'
            );

        $progressFilePath = $progressFile->path;

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
