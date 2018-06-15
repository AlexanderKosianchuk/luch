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
  public function syncAction($uuid, $login, $pass, $fdrId, $calibrationId = null)
  {
    if (!isset($_FILES['file']['tmp_name'])) {
      throw new BadRequestException('necessary param flightFile not passed.');
    }

    $user = $this->em()->getRepository('Entity\User')
      ->findOneBy(['login' => $login, 'pass' => md5($pass)]);

    if (!$user) {
      throw new BadRequestException('user not found. Login: '.$login.', pass: '.$pass);
    }

    $fileName = strval($_FILES['file']['tmp_name']);
    $uploadingUid = strval($uuid);
    $fdrId = intval($fdrId);
    $userId = $user->getId();

    if (!$this->dic('fdr')->isAvaliable($fdrId)) {
      throw new Exception("Trying to access unavaliable fdrType."
        . " User id: " . $userId
        . " Fdr id: " . $fdrId, 1);
    }

    $flights = $this->em()
      ->getRepository('Entity\Flight')
      ->findBy(['guid' => $uploadingUid]);

    if ($flights) {
      return json_encode([
        "status" => "complete",
        "uploadingUid" => $uploadingUid,
        'item' => $flights[0]->get(true)
      ]);
    }

    $flightInfo = $this->dic()
      ->get('flightProcessor')
      ->readHeaderAndFillInfo($fdrId, $fileName);

    $storedFlightFile = $this->dic()
      ->get('runtimeManager')
      ->storeFlight($fileName);

    $flightInfo['path'] = $this->dic()
      ->get('runtimeManager')
      ->getRuntimeFileUrl($storedFlightFile);

    $totalPersentage = 50;

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
        $flight->getStartCopyTime(),
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
      'item' => $flight->get(true)
    ]);
  }

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

    $uploadedFile = $this->dic('runtimeManager')->getFilePathByIud($uploadingUid);
    $response = $this->dic('flightProcessor')->preview($fdrId, $uploadedFile);

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

    if (($calibrationId !== null) && !empty($calibrationId)) {
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
    } else {
      $calibrationId = null;
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

    $flightAditionalInfoParsed = [];
    if ($flightAditionalInfo != 0) {
      for ($i = 0; $i < count($flightAditionalInfo); $i+=2) {
        $flightAditionalInfoParsed[(string)$flightAditionalInfo[$i]] =
          (string)$flightAditionalInfo[$i + 1];
      }
    }

    $copyCreationTime = $flightInfoParsed['copyCreationTime'] ?? '';
    $copyCreationDate = $flightInfoParsed['copyCreationDate'] ?? '';
    if (strlen($copyCreationTime) > 5) {
      $flightInfoParsed['startCopyTime'] = strtotime($copyCreationDate . ' ' . $copyCreationTime);
    } else {
      $flightInfoParsed['startCopyTime'] = strtotime($copyCreationDate . ' ' . $copyCreationTime . ':00');
    }

    $flightInfoParsed['aditionalInfo'] = $flightAditionalInfoParsed;

    $storedFlightFile = $this->dic()
      ->get('runtimeManager')
      ->storeFlight($fileName);

    $flightInfoParsed['path'] = $this->dic()
      ->get('runtimeManager')
      ->getRuntimeFileUrl($storedFlightFile);

    $flight = $this->dic()
      ->get('flight')
      ->insert(
        $uploadingUid,
        $flightInfoParsed,
        $fdrId,
        $userId,
        $calibrationId
      );

    $this->dic()
      ->get('flightProcessor')
      ->process(
        $uploadingUid,
        $storedFlightFile,
        $flight->getStartCopyTime(),
        100, /* total persentage */
        $fdrId,
        $calibrationId
      );

    //TODO: add persentage calc in second pass
    /*$this->dic()
      ->get('postProcessor')
      ->secondPassProcess($flight);*/

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

    if (!$this->dic('fdr')->isAvaliable($fdrId)) {
      throw new Exception("Trying to access unavaliable fdrType."
        . " User id: " . $userId
        . " Fdr id: " . $fdrId, 1);
    }

    $flightInfo = $this->dic()
      ->get('flightProcessor')
      ->readHeaderAndFillInfo($fdrId, $fileName);

    $storedFlightFile = $this->dic()
      ->get('runtimeManager')
      ->storeFlight($fileName);

    $flightInfo['path'] = $this->dic()
      ->get('runtimeManager')
      ->getRuntimeFileUrl($storedFlightFile);

    $totalPersentage = 50;

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
        $flight->getStartCopyTime(),
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
          <img src='/images/loading.gif'/></div>";

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

  public function itemImportAction($flightGuid)
  {
    if (!isset($_FILES['flightFileArchive']['tmp_name'])) {
      throw new BadRequestException('necessary param flightFileArchive not passed.');
    }

    $uploadedArchivePath = strval($_FILES['flightFileArchive']['tmp_name']);
    $copiedFilesDir = $this->dic('runtimeManager')->getImportFolder();
    $copiedFilePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$_FILES['flightFileArchive']['name'];

    move_uploaded_file($uploadedArchivePath, $copiedFilePath);

    $zip = new ZipArchive;
    $res = $zip->open($copiedFilePath);
    $importFolderName = sprintf("Imported_%s", date('Y-m-d'));
    $needToCreateImportedFolder = true;

    $folderInfo = [];
    $userId = $this->user()->getId();

    if ($res !== TRUE) {
      echo json_encode('err');
    }

    $i = 0;
    $headerFiles = [];
    do {
      $fileName = $zip->getNameIndex($i);
      if((strpos($fileName, 'header') !== false)) {
        $headerFiles[] = $fileName;
      }
      $i++;
    } while($i < $zip->numFiles);

    if (count($headerFiles) === 0) {
      echo json_encode('err');
    }

    foreach ($headerFiles as $name) {
      $zip->extractTo($copiedFilesDir, $name);

      $json = file_get_contents($copiedFilesDir.'/'.$name);
      unlink($copiedFilesDir.'/'.$name);
      $flightInfoImported = json_decode($json, true);

      $fdrId = $flightInfoImported['fdrId'];
      $userId = $flightInfoImported['userId'];
      $calibrationId = $flightInfoImported['calibrationId'];

      $flightInfoImported['copyCreationTime'] = date('H:i:s', $flightInfoImported['startCopyTime']);
      $flightInfoImported['copyCreationDate'] = date('Y-m-d', $flightInfoImported['startCopyTime']);

      $flightInfoImported['aditionalInfo'] = $flightInfoImported['aditionalInfo'];
      $flight = $this->dic('flight')
        ->insert($flightGuid, $flightInfoImported, $fdrId, $userId, $calibrationId);
      $flightId = $flight->getId();

      $analogParamsCyclo = $this->dic('fdr')
        ->getPrefixGroupedParams($fdrId);

      $binaryParamsCyclo = $this->dic('fdr')
        ->getPrefixGroupedBinaryParams($fdrId);

      $this->dic('flight')->createParamTables(
        $flightGuid,
        $analogParamsCyclo,
        $binaryParamsCyclo
      );

      $apTables = $flightInfoImported["apTables"];

      for($j = 0; $j < count($apTables); $j++) {
        $zip->extractTo($copiedFilesDir, $apTables[$j]["file"]);
        $filePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$apTables[$j]["file"];
        $tableName = $this->dic('fdr')->getAnalogTable($flightGuid, $apTables[$j]['pref']);

        if (file_exists($filePath)) {
          $this->dic('flightProcessor')->loadParamFilesToTables(
            $tableName, $filePath
          );
        }
      }

      $bpTables = $flightInfoImported["bpTables"];
      for ($j = 0; $j < count($bpTables); $j++) {
        $zip->extractTo($copiedFilesDir, $bpTables[$j]["file"]);
        $filePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$bpTables[$j]["file"];
        $tableName = $this->dic('fdr')->getBinaryTable($flightGuid, $bpTables[$j]['pref']);
        if (file_exists($filePath)) {
          $this->dic('flightProcessor')->loadParamFilesToTables(
            $tableName, $filePath
          );
        }
      }

      if (isset($flightInfoImported["exTables"])
        && ($flightInfoImported["exTables"] != "")
      ) {
        $filePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$flightInfoImported["exTables"];
        $zip->extractTo($copiedFilesDir, $flightInfoImported["exTables"]);
        $tableName = $this->dic('event')
          ->createOldEventsTable($flightGuid);
        if (file_exists($filePath)) {
          $this->dic('flightProcessor')->loadParamFilesToTables(
            $tableName, $filePath
          );
        }
      }

      if (isset($flightInfoImported["eventsTable"])
        && ($flightInfoImported["eventsTable"] != "")
      ) {
        $filePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$flightInfoImported["eventsTable"];
        $zip->extractTo($copiedFilesDir, $flightInfoImported["eventsTable"]);
        $tableName = $this->dic('event')
          ->createEventsTable($flightGuid);
        if (file_exists($filePath)) {
          $this->dic('flightProcessor')->loadParamFilesToTables(
            $tableName, $filePath
          );
        }
      }

      if (isset($flightInfoImported["settlementsTable"])
        && ($flightInfoImported["settlementsTable"] != "")
      ) {
        $filePath = $copiedFilesDir.DIRECTORY_SEPARATOR.$flightInfoImported["settlementsTable"];
        $zip->extractTo($copiedFilesDir, $flightInfoImported["settlementsTable"]);
        $tableName = $this->dic('event')
          ->createSettlementsTable($flightGuid);
        if (file_exists($filePath)) {
          $this->dic('flightProcessor')->loadParamFilesToTables(
            $tableName, $filePath
          );
        }
      }
    }

    return json_encode('ok');
  }

  public function getUploadingStatusAction($uploadingUid)
  {
    $progressFile = $this->dic('runtimeManager')
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

  public function processFrameAction(
    $uploadingUid,
    $frameNum,
    $startCopyTime,
    $rawFrame,
    $userId,
    $algHeap,
    $fdrId,
    $calibrationId = null
  ) {
    $fdrId = intval($fdrId);
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    $splitedFrame = str_split($rawFrame, $fdr->getWordLength() * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need
    $dataToWrite = '';
    foreach ($splitedFrame as $item) {
      $dataToWrite .= pack('H*', $item);
    }

    $algHeap = json_decode($algHeap, true);
    //TODO check user id token and fdr valiability to this user

    $this->dic('runtimeManager')
      ->write(
        $this->params()->folders->uploadedFlights,
        $uploadingUid.'.tmpsf',
        $dataToWrite
      );

    $stepLength = $fdr->getStepLength();
    $currentTime = $startCopyTime * 1000 + (1000 * $stepLength * $frameNum);

    $analogParamsCyclo = $this->dic('fdr')
      ->getPrefixGroupedParams($fdrId);

    if ($calibrationId !== null) {
      $calibratedParams = $this->dic('calibration')
        ->getCalibrationParams($fdrId, $calibrationId);

      foreach ($analogParamsCyclo as $prefix => &$params) {
        foreach ($params as &$param) {
          $paramId = $param['id'];

          if (isset($calibratedParams[$paramId])) {
            $param['xy'] = $calibratedParams[$paramId]->getXy();
          }
        }
      }
    }

    $binaryParamsCyclo = $this->dic('fdr')
      ->getPrefixGroupedBinaryParams($fdrId);

    $fullFrame = [];

    for ($ii = 0; $ii < $fdr->getFrameLength(); $ii++) {
      if (isset($splitedFrame[$ii])) {
        $fullFrame[] = $splitedFrame[$ii];
      } else {
        $fullFrame[] = 'ff';
      }
    }

    $converted = $this->dic()
      ->get('flightProcessor')
      ->convertFrame(
        $uploadingUid,
        $analogParamsCyclo,
        $binaryParamsCyclo,
        $fullFrame,
        $startCopyTime,
        $stepLength,
        $frameNum,
        $algHeap,
        false
      );

    $normalized = $this->dic()
      ->get('frame')
      ->normalizeFrame(
        $stepLength,
        $currentTime,
        $converted['phisicsByFreq'],
        $converted['binaryFlags'],
        $analogParamsCyclo,
        $binaryParamsCyclo
      );

    $link = $this->connection()->create('runtime');
    $this->dic('runtimeDb')
      ->putRealtimeCalibrationData(
          $uploadingUid,
          $frameNum,
          $currentTime,
          $stepLength,
          $normalized['fullFrame'],
          $link
        );

    $prevEventResults = json_decode($this->redis()->get($uploadingUid . '_LAST_EVENTS'), true);

    if ($prevEventResults === null) {
      $prevEventResults = $this->dic('runtimeDb')
        ->getProcessResults($uploadingUid, $frameNum - 1, $link);
    }

    $tableName = $this->dic('runtimeDb')
      ->getDataTableName($uploadingUid);

    $eventResults = $this->dic('realtimeEvent')
      ->process($fdrId, $tableName, $frameNum, $prevEventResults, $link);

    $this->redis()->set($uploadingUid . '_LAST_EVENTS', json_encode($eventResults));

    $this->dic('runtimeDb')
      ->putRealtimeCalibrationEvents($uploadingUid, $eventResults, $frameNum, $link);
    $this->connection()->destroy($link);

    $voiceCyclo = $this->dic('voice')->getVoiceChannels($fdrId);
    $voiceData = $this->dic('voice')->processVoice($rawFrame, $voiceCyclo);

    $voiceStreamsUrl = [];
    foreach ($voiceData as $key => $arr) {
      $uploadingFileName = $this->dic('voice')->getUploadingFileName($uploadingUid, $key);

      $isExist = $this->dic('runtimeManager')
        ->exist(
          $this->params()->folders->uploadingVoice,
          $uploadingFileName
        );

      if (!$isExist) {
        $this->dic('runtimeManager')
          ->write(
            $this->params()->folders->uploadingVoice,
            $uploadingFileName,
            $this->dic('voice')->getWavHeader()
          );
      }

      $str = '';
      foreach ($arr as $value) {
        $str .= dechex($value);
      }

      $this->dic('runtimeManager')
        ->write(
          $this->params()->folders->uploadingVoice,
          $uploadingFileName,
          $str
      );

      $voiceStreamsUrl[] = $this->params()->serverName
        .'/voice/stream/fileName/'
        .$uploadingFileName;
    }

    return json_encode([
      'uploadingUid' => $uploadingUid,
      'frameNum' => $frameNum,
      'startCopyTime' => $startCopyTime,
      'rawFrame' => $rawFrame,
      'frame' => $normalized['plainFrame'],
      'binaryFlags' => $converted['binaryFlags'],
      'events' => $eventResults,
      'voiceStreams' => $voiceStreamsUrl,
      'algHeap' => $algHeap,
      'timestamp' => $currentTime,
      'fdrId' => $fdrId,
      'calibrationId' => $calibrationId,
    ]);
  }

  public function breakFramesProcessAction($uploadingUid)
  {
    try {
      $this->dic('runtimeDb')
        ->cleanUpRealtimeCalibrationData($uploadingUid);

      $scandirItems = $this->dic()
        ->get('runtimeManager')
        ->scandir(
          $this->params()->folders->uploadingVoice
        );

      foreach ($scandirItems as $item) {
        if (strpos($item, $uploadingUid) > -1) {
          $this->dic()
            ->get('runtimeManager')
            ->delete(
              $this->params()->folders->uploadingVoice,
              $item
            );
        }
      }
    } catch(Exception $e) {}

    return json_encode('ok');
  }

  public function getSegmentAction($uploadingUid, $fdrId, $timestamp, $limit = 100)
  {
    $phisics = [];
    $binary = [];
    $events = [];
    $currentFrame = 0;
    $timeline = [];

    $fdrId = intval($fdrId);
    $fdr = $this->em()->find('Entity\Fdr', $fdrId);

    try {
      $link = $this->connection()->create('runtime');

      $data = $this->dic('runtimeDb')
        ->getRealtimeCalibrationData($uploadingUid, $timestamp, $limit, $link);

      $analogParamsCyclo = $this->dic('fdr')->getParams($fdrId, true);
      $binaryParamsCyclo = $this->dic('fdr')->getBinaryParams($fdrId, true);

      $findByCode = function ($code, $cyclo) {
        foreach ($cyclo as $param) {
          if ($param['code'] === $code) {
            return $param;
          }
        }

        return null;
      };

      if (count($data) > 0) {
        $currentFrame = intval($data[count($data) - 1]['frame_num']);
      }

      foreach ($data as $rawFrame) {
        $timeline[] = intval($rawFrame['time']);
      }

      foreach ($data as $rawFrame) {
        $frame = [];
        $binaryData = [];
        $frameNum = intval($rawFrame['frame_num']);
        foreach ($rawFrame as $paramCode => $paramValue) {
          $param = $findByCode($paramCode, $analogParamsCyclo);
          $binaryParam = $findByCode($paramCode, $binaryParamsCyclo);

          if ($param !== null) {
            $frame[$param['id']] = floatval($paramValue);
          }

          if (($binaryParam !== null)
            && ($paramValue > 0)
          ) {
            $binaryData[$paramCode] = [
              'frameNum' => $frameNum,
              'time' => intval($rawFrame['time']),
              'code' => $paramCode,
              'id' => intval($binaryParam['id'])
            ];
          }
        }

        $binaryDataArray = [];
        foreach ($binaryData as $key => $binaryDataValue) {
          $binaryDataArray[] = $binaryDataValue;
        }
        $phisics[] = $frame;
        $binary[] = $binaryDataArray;
      }

      $registeredEvents = $this->dic('runtimeDb')
        ->getRealtimeCalibrationEvents($uploadingUid, $timestamp, $limit, $link);

      $findById = function ($id, $events) {
        foreach ($events as $event) {
          if ($event->getId() === $id) {
            return $event->get(true);
          }
        }

        return null;
      };

      $eventsList = $fdr->getRealtimeEvents();
      $eventsAssoc = [];
      foreach ($registeredEvents as $registeredEvent) {
        $eventDescription = $findById(intval($registeredEvent['id_event']), $eventsList);

        if ($eventDescription !== null) {
          if (!isset($eventsAssoc[$registeredEvent['frame_num']])) {
            $eventsAssoc[$registeredEvent['frame_num']] = [];
          }

          $eventsAssoc[$registeredEvent['frame_num']][] = [
            'eventId' => intval($registeredEvent['id']),
            'event' => $eventDescription,
            'value' => $registeredEvent['value'],
            'frameNum' => intval($registeredEvent['frame_num'])
          ];
        }
      }

      foreach ($eventsAssoc as $item) {
        $events[] = $item;
      }

      $this->connection()->destroy($link);
    } catch(Exception $e) {}

    return json_encode([
      'phisics' => $phisics,
      'binary' => $binary,
      'events' => $events,
      'currentFrame' => $currentFrame,
      'timeline' => $timeline
    ]);
  }
}
