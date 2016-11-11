<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class UploaderController extends CController
{
    public $curPage = 'uploaderPage';
    public $flightActions;

    function __construct($post, $session)
    {
        $this->IsAppLoggedIn($post, $session);

        $L = new Language();
        $this->flightActions = (array)$L->GetServiceStrs($this->curPage);
        unset($L);

        //even if flight was selected if file send this variant will be processed
        if((isset($post['action']) && ($post['action'] != '')) &&
            (isset($post['data']) && ($post['data'] != '')))
        {
            $this->action = $post['action'];
            $this->data = $post['data'];
        }
        else
        {
            echo("Incorect input. Data: " . json_encode($post['data']) .
                " . Action: " . json_encode($post['action']) .
                " . Page: " . $this->curPage. ".");

            error_log("Incorect input. Data: " . json_encode($post['data']) .
                " . Action: " . json_encode($post['action']) .
                " . Page: " . $this->curPage. ".");
        }
    }

    public function IsAppLoggedIn()
    {
        return isset($_SESSION['uid']) && isset($_SESSION['username']) && isset($_SESSION['loggedIn']) && ($_SESSION['loggedIn'] === true);
    }

    public function GetUserPrivilege()
    {
        $this->_user->username = isset($_SESSION['username']) ? $_SESSION['username'] : "";
        $Usr = new User();
        $this->privilege = $Usr->GetUserPrivilege($this->_user->username);
        unset($Usr);
    }

    public function ShowFlightParams($extIndex, $extBruType, $extFilePath)
    {
        $index = $extIndex;
        $bruType = $extBruType;
        $filePath = $extFilePath;

        $fileName = basename($filePath);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $previewParams = $bruInfo['previewParams'];
        unset($Bru);

        $flightInfoFromHeader = $this->ReadHeader($bruType, $filePath);

        $fileInfoColumnWidth = '100%';
        if($previewParams != '')
        {
            $fileInfoColumnWidth = 450;
        }

        $flightParamsSrt = "<div id='fileFlightInfo".$index."' class='MainContainerContentRows' " .
                "data-filename='" . $filePath . "' " .
                "data-brutype='" . $bruType . "' " .
                "data-index='" . $index . "' " .
                "data-previewparams='" . $previewParams . "' " .
                "align='left'>" .
                "<a style='margin-left:5px; font-weight:bold;'>" .
                    $this->lang->enterFlightDetails . " - " . $fileName . "</a>" .
                "</br>" .
                 //left column for flight info - right for preview
                "<table style='width:100%'><tr><td style='width:" . $fileInfoColumnWidth . "px'>" .

                "<table border='0' style='margin-bottom:15px;'>" .
                "<tr>" .
                "<td>" . $this->lang->bruType . "</td>";

        $flightParamsSrt .= "<td><input id='bruType' name='bruType' class='FlightUploadingInputs' value='" . $bruType .
                "' readonly /></td>" .
                "</tr><tr>";

        $bortFromHeader = "";
        if(isset($flightInfoFromHeader["bort"]))
        {
            $bortFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["bort"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->bortNum . "</td>" .
                "<td><input id='bort' name='bort' type='text' class='FlightUploadingInputs' ".
                "value='" . $bortFromHeader . "'/></td>" .
                "</tr>";


        $voyageFromHeader = "";
        if(isset($flightInfoFromHeader["voyage"]))
        {
            $voyageFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["voyage"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->voyage . "</td>" .
                "<td><input id='voyage' name='voyage' type='text' class='FlightUploadingInputs' ".
                "value='" . $voyageFromHeader . "'/></td>" .
                "</tr>";

        $departureAirportFromHeader = "";
        if(isset($flightInfoFromHeader["departureAirport"]))
        {
            $departureAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["departureAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->departureAirport . "</td>" .
                "<td><input id='departureAirport' name='departureAirport' type='text' class='FlightUploadingInputs' ".
                "value='" . $departureAirportFromHeader . "'/></td>" .
                "</tr>";

        $arrivalAirportFromHeader = "";
        if(isset($flightInfoFromHeader["arrivalAirport"]))
        {
            $arrivalAirportFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',$flightInfoFromHeader["arrivalAirport"]);
        }

        $flightParamsSrt .= "<tr><td>" . $this->lang->arrivalAirport . "</td>" .
                "<td><input id='arrivalAirport' name='arrivalAirport' type='text' class='FlightUploadingInputs' ".
                "value='" . $arrivalAirportFromHeader . "'/></td>" .
                "</tr>";

        $copyCreationTimeFromHeader = "";
        $copyCreationDateFromHeader = "";
        if(isset($flightInfoFromHeader["copyCreationTime"]) &&
            isset($flightInfoFromHeader["copyCreationDate"]))
        {
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

        if($bruInfo['aditionalInfo'] != '')
        {
            if (strpos($bruInfo['aditionalInfo'], ";") !== 0)
            {
                $aditionalInfo = explode(";", $bruInfo['aditionalInfo']);
                $aditionalInfo  = array_map('trim', $aditionalInfo);
            }
            else
            {
                $aditionalInfo = (array)trim($bruInfo['aditionalInfo']);
            }

            for($i = 0; $i < count($aditionalInfo); $i++)
            {

                if(property_exists($this->lang, $aditionalInfo[$i]))
                {
                    $labelsArr = get_object_vars($this->lang);
                    $label = $labelsArr[$aditionalInfo[$i]];
                }
                else
                {
                    if(!(property_exists($this->lang, 'aditionalInfo')))
                    {
                        $this->lang->aditionalInfo = "Aditional info";
                    }

                    $label = $this->lang->aditionalInfo;
                }

                $flightParamsSrt .= "<tr><td>" . $label . "</td>";

                $aditionalInfoFromHeader = "";
                if(isset($flightInfoFromHeader[$aditionalInfo[$i]]))
                {
                    $aditionalInfoFromHeader = preg_replace('/[\x00-\x09\x0B\x0C\x0E-\x1F\x7F]/', '',
                            $flightInfoFromHeader[$aditionalInfo[$i]]);
                }

                $flightParamsSrt .= "<td><input id='" . $aditionalInfo[$i] . "'
                        name='aditionalInfo" . $i . "' type='text' class='FlightUploadingInputsAditionalInfo' " .
                        "value='" . $aditionalInfoFromHeader . "'/></td>
                </tr>";
            }
        }

        $U = new User();

        if(in_array($U::$PRIVILEGE_TUNE_FLIGHTS, $this->privilege))
        {
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
        if($previewParams != '')
        {
            $flightParamsSrt .= "<div id='loadingBox".$index."' width='100%' style='position:absolute;'>
                    <img style='margin:0px auto 0px;' src='stylesheets/basicImg/loading.gif'/></div>";

            $flightParamsSrt .= "<div id='previewChartContainer".$index."' " .
                    "style='width:95%; border:0;'>
                <div id='previewChartPlaceholder".$index."' " .
                    "data-index='".$index."' " .
                    "class='PreviewChartPlaceholder'></div>
                </div>";

            $flightParamsSrt .= "<button id='sliceFlightButt".$index."' ".
                    "class='SliceFlightButt' ".
                    "data-index='".$index."' " .
                    "data-file='".$filePath."' " .
                    "data-brutype='".$bruType."' " .
                    "class='Button'>".
                    $this->lang->slice . "</button>";

            $flightParamsSrt .= "<button id='sliceCyclicFlightButt".$index."' ".
                    "class='SliceCyclicFlightButt' ".
                    "data-index='".$index."' " .
                    "data-file='".$filePath."' " .
                    "data-brutype='".$bruType."' " .
                    "class='Button'>".
                    $this->lang->sliceCyclic . "</button>";
        }

        $flightParamsSrt .= "</br></form></div>";

        $flightParamsSrt .= "</td></tr></table></div>";

        return $flightParamsSrt;
    }

    public function ReadHeader($extBruType, $extFilePath)
    {
        $bruType = $extBruType;
        $file = $extFilePath;

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $frameLength = $bruInfo['frameLength'];
        $stepLength = $bruInfo['stepLength'];
        $wordLength = $bruInfo['wordLength'];
        $headerLength = $bruInfo['headerLength'];
        $headerScr = $bruInfo['headerScr'];
        $frameSyncroCode = $bruInfo['frameSyncroCode'];
        unset($Bru);

        $flightInfo['bruType'] = $bruType;

        if(($headerScr != '') || ($headerScr != null))
        {
            eval($headerScr);

            unset($Fl);

            if(isset($flightInfo['startCopyTime']))
            {
                $startCopyTime = $flightInfo['startCopyTime'];
                $flightInfo['startCopyTime'] = date('H:i:s Y-m-d', $startCopyTime);
                $flightInfo['copyCreationTime'] = date('H:i:s', $startCopyTime);
                $flightInfo['copyCreationDate'] = date('Y-m-d', $startCopyTime);
            }

            $airport = new Airport();
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

    public function CheckAditionalInfoFromHeader($extBruType, $extHeaderInfo)
    {
        $bruType = $extBruType;
        $headerInfo = $extHeaderInfo;

        $aditionalInfo = array();

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $aditionalInfoArr = explode(";", $bruInfo["aditionalInfo"]);

        foreach($aditionalInfoArr as $key => $val)
        {
            if(isset($headerInfo[$val]))
            {
                $aditionalInfo[$val] = $headerInfo[$val];
            }
            else
            {
                $aditionalInfo[$val] = "x";
            }
        }

        unset($Bru);

        $aditionalInfoVars = '';
        foreach($aditionalInfo as $key => $val)
        {
            $aditionalInfoVars .= $key . ":" . $val . ";";
        }

        return $aditionalInfoVars;
    }

    public function CopyPreview($extBruType, $extFilePath)
    {
        $bruType = $extBruType;
        $file = $extFilePath;

        $flightInfo['bruType'] = $bruType;

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $frameLength = $bruInfo['frameLength'];
        $stepLength = $bruInfo['stepLength'];
        $wordLength = $bruInfo['wordLength'];
        $headerLength = $bruInfo['headerLength'];
        $headerScr = $bruInfo['headerScr'];
        $frameSyncroCode = $bruInfo['frameSyncroCode'];

        $previewParams = $bruInfo['previewParams'];
        $cycloAp = $Bru->GetBruApCyclo($bruType, -1, -1, -1);

        $previewParams = explode(";", $previewParams);
        $previewParams = array_map('trim', $previewParams);

        $previewCyclo = array();
        $cycloApByPrefixes = array();

        foreach ($cycloAp as $row => $val)
        {
            if(in_array($val['code'], $previewParams))
            {
                $previewCyclo[] = $val;
                if(!in_array($val['prefix'], $cycloApByPrefixes))
                {
                    $prefixFreqArr[$val['prefix']] = count(explode(",",$val['channel']));
                }

                $cycloApByPrefixes[$val['prefix']][] = $val;
            }
        }

        $prefixFreqArr = $Bru->GetBruApCycloPrefixFreq($bruType);
        unset($Bru);

        $Fr = new Frame();
        $fileDesc = $Fr->OpenFile($file);
        $fileSize = $Fr->GetFileSize($file);
        unset($Fr);

        if(($headerScr != '') || ($headerScr != null))
        {
            eval ($headerScr);
        }

        $startCopyTime = 0; // to be 0 hours
        /*if(isset($flightInfo['startCopyTime']))
        {
            $startCopyTime = $flightInfo['startCopyTime'] * 1000;
        }*/

        $Fr = new Frame();
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
        echo(json_encode($data));
    }

    public function CutCopy($extBruType, $extFilePath,
            $extStartCopyTime, $extEndCopyTime,
            $extStartSliceTime, $extEndSliceTime)
    {
        $bruType = $extBruType;
        $filePath = $extFilePath;

        $startCopyTime = $extStartCopyTime;
        $endCopyTime = $extEndCopyTime;
        $startSliceTime = $extStartSliceTime;
        $endSliceTime = $extEndSliceTime;

        $newFileName = $filePath;
        $newFileAppendix = 'a';

        do
        {
            $newFileAppendix++;
        }
        while(file_exists($newFileName . $newFileAppendix));
        $newFileName = $newFileName . $newFileAppendix;

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $headerLength = $bruInfo['headerLength'];
        $frameLength = $bruInfo['frameLength'];

        $handle = fopen($filePath, "r");
        $newHandle = fopen($newFileName, "w");

        if($headerLength > 0)
        {
            $fileHeader = fread($handle, $headerLength);
            fwrite($newHandle, $fileHeader);
        }

        //$writtenHeaderLength = file_put_contents($newFileName, $fileHeader);

        $fileSize = filesize ($filePath);
        $Bs = ($fileSize - $headerLength) / ($endCopyTime - $startCopyTime);
        $stB = $Bs * ($startSliceTime - $startCopyTime) + $headerLength;
        $endB = $Bs * ($endSliceTime - $startCopyTime) + $headerLength;

        $stB = round($stB / $frameLength , 0) * $frameLength + $headerLength;

        if($endB > $fileSize)
        {
            $endB = $fileSize;
        }

        if($stB > 0 && $stB < $fileSize && $endB > 0 && $endB <= $fileSize)
        {
            fseek($handle, $stB);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB)
            {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }
            fclose($handle);
            fclose($newHandle);

            $newFileName = basename($newFileName);

            $answ["status"] = "ok";
            $answ["data"] = $newFileName;

            echo(json_encode($answ));
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Invalid slice range. Page UploaderController.php";

            error_log("Invalid slice range. Page UploaderController.php");
            echo(json_encode($answ));
            exit();
        }
    }

    public function CyclicSliceCopy($extBruType, $extFilePath,
            $extStartCopyTime, $extEndCopyTime, $extStartSliceTime)
    {
        $bruType = $extBruType;
        $filePath = $extFilePath;

        $startCopyTime = $extStartCopyTime;
        $endCopyTime = $extEndCopyTime;
        $startSliceTime = $extStartSliceTime;

        $newFileName = $filePath;
        $newFileAppendix = 'a';

        do
        {
            $newFileAppendix++;
        }
        while(file_exists($newFileName . $newFileAppendix));
        $newFileName = $newFileName . $newFileAppendix;

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $headerLength = $bruInfo['headerLength'];
        $frameLength = $bruInfo['frameLength'];

        $handle = fopen($filePath, "r");
        $newHandle = fopen($newFileName, "w");

        if($headerLength > 0)
        {
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

        if($endB > $fileSize)
        {
            $endB = $fileSize;
        }

        if($stB > 0 && $stB < $fileSize && $endB > 0 && $endB <= $fileSize)
        {
            fseek($handle, $stB);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB)
            {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }

            fseek($handle, $headerLength);
            while ((ftell($handle) <= $fileSize - $frameLength) && ftell($handle) < $endB2)
            {
                $fileFrame = fread($handle, $frameLength);
                fwrite($newHandle, $fileFrame);
            }
            fclose($handle);
            fclose($newHandle);

            $newFileName = basename($newFileName);

            $answ["status"] = "ok";
            $answ["data"] = $newFileName;

            echo(json_encode($answ));
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Invalid slice range. Page UploaderController.php";

            error_log("Invalid slice range. Page UploaderController.php");
            echo(json_encode($answ));
            exit();
        }
    }

    public function ProccessFlightData($extTempFileName,
            $extBort,
            $extVoyage,
            $extCopyCreationTime,
            $extCopyCreationDate,
            $extBruType,
            $extPerformer,
            $extDepartureAirport,
            $extArrivalAirport,
            $extAditionalInfo,
            $extUploadedFile,
            $extTotalPersentage
        )
    {

        $tempFile = $extTempFileName;
        $tempFilePath = UPLOADED_FILES_PATH . "proccessStatus/" . $tempFile;
        $bort = $extBort;
        $voyage = $extVoyage;
        $copyCreationTime = $extCopyCreationTime;
        $copyCreationDate = $extCopyCreationDate;
        $totalPersentage = $extTotalPersentage;

        if(strlen($copyCreationTime) > 5)
        {
            $startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime);
        }
        else
        {
            $startCopyTime = strtotime($copyCreationDate . " " . $copyCreationTime . ":00");
        }

        $bruType = $extBruType;
        $performer = $extPerformer;
        if($performer == null){
            $performer = $this->_user->username;
        }

        $departureAirport = $extDepartureAirport;
        $arrivalAirport = $extArrivalAirport;
        $uploadedFile = $extUploadedFile;
        $aditionalInfo = $extAditionalInfo;

        $Fl = new Flight();
        $flightId = $Fl->InsertNewFlight($bort, $voyage,
                $startCopyTime,
                $bruType, $performer,
                $departureAirport, $arrivalAirport,
                $uploadedFile, $aditionalInfo);

        $flightInfo = $Fl->GetFlightInfo($flightId);
        $tableNameAp = $flightInfo['apTableName'];
        $tableNameBp = $flightInfo['bpTableName'];
        $flightId = $flightInfo['id'];
        $fileName = $flightInfo['fileName'];

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $frameLength = $bruInfo['frameLength'];
        $stepLength = $bruInfo['stepLength'];
        $wordLength = $bruInfo['wordLength'];
        $headerLength = $bruInfo['headerLength'];
        $headerScr = $bruInfo['headerScr'];
        $frameSyncroCode = $bruInfo['frameSyncroCode'];
        //$cycloAp = $Bru->GetBruApGradi($bruType);
        $cycloApByPrefixes = $Bru->GetBruApCycloPrefixOrganized($bruType);
        $prefixFreqArr = $Bru->GetBruApCycloPrefixFreq($bruType);

        $cycloBpByPrefixes = $Bru->GetBruBpCycloPrefixOrganized($bruType);
        $prefixBpFreqArr = $Bru->GetBruBpCycloPrefixFreq($bruType);
        unset($Bru);
        $apTables = $Fl->CreateFlightParamTables($flightId, $cycloApByPrefixes, $cycloBpByPrefixes);
        unset($Fl);

        $Fr = new Frame();
        $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $headerLength, $fileName);

        $fileDesc = $Fr->OpenFile($fileName);
        $fileSize = $Fr->GetFileSize($fileName);

        $frameNum = 0;
        $totalFrameNum = floor(($fileSize - $syncroWordOffset)  / $frameLength);

        $tmpProccStatusFilesDir = UPLOADED_FILES_PATH . "proccessStatus";
        if (!is_dir($tmpProccStatusFilesDir)) {
            mkdir($tmpProccStatusFilesDir);
        }

        $fileNameApArr = array();
        $fileNameApDescArr = array();
        foreach($cycloApByPrefixes as $prefix => $item)
        {
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

        $algHeap = array();
        if($frameSyncroCode != '')
        {
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
                    foreach($cycloApByPrefixes as $prefix => $cycloAp)
                    {
                        $channelFreq = $prefixFreqArr[$prefix];
                        $phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $cycloAp, $algHeap);
                        $apPhisicsByPrefixes[$prefix] = $phisicsFrame;
                    }

                    $bpPhisicsByPrefixes = array();
                    foreach($cycloBpByPrefixes as $prefix => $cycloBp)
                    {
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
                }
                else
                {
                    $syncroWordOffset = $Fr->SearchSyncroWord($frameSyncroCode, $curOffset, $fileName);

                    fseek($fileDesc, $syncroWordOffset, SEEK_SET);

                    $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
                    $totalFrameNum = $frameNum + $framesLeft;

                }

                $tmpStatus =  round($totalPersentage / $fileSize * $frameNum * $frameLength) . "%";

                $fp = fopen($tempFilePath, "w");
                fwrite($fp, json_encode($tmpStatus));
                fclose($fp);
            }
        }
        else
        {
            while(($frameNum < $totalFrameNum) && ($curOffset < $fileSize))
            //while(($frameNum < 20) && ($curOffset < $fileSize))
            {
                $curOffset = ftell($fileDesc);
                $frame = $Fr->ReadFrame($fileDesc, $frameLength);
                $unpackedFrame = unpack("H*", $frame);

                $splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

                $apPhisicsByPrefixes = array();
                foreach($cycloApByPrefixes as $prefix => $cycloAp)
                {
                    $channelFreq = $prefixFreqArr[$prefix];
                    $phisicsFrame = $Fr->ConvertFrameToPhisics($splitedFrame, $startCopyTime, $stepLength, $channelFreq, $frameNum, $cycloAp, $algHeap);
                    $apPhisicsByPrefixes[$prefix] = $phisicsFrame;
                }

                $bpPhisicsByPrefixes = array();
                foreach($cycloBpByPrefixes as $prefix => $cycloBp)
                {
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

                $tmpStatus =  round($totalPersentage / $fileSize * $frameNum * $frameLength) . "%";

                $fp = fopen($tempFilePath, "w");
                fwrite($fp, json_encode($tmpStatus));
                fclose($fp);
            }
        }

        $tmpStatus = $this->lang->uploadingToDb;

        $fp = fopen($tempFilePath, "w");
        fwrite($fp, json_encode($tmpStatus));
        fclose($fp);

        error_reporting(E_ALL);

        //not need any more
        $Fr->CloseFile($fileDesc);
        unlink($uploadedFile);

        foreach($fileNameApArr as $prefix => $fileNameAp)
        {
            fclose($fileNameApDescArr[$prefix]);
            $Fr->LoadFileToTable($tableNameAp . "_" . $prefix, $fileNameAp);
            unlink($fileNameAp);
        }

        foreach($fileNameBpArr as $prefix => $fileNameBp)
        {
            fclose($fileNameBpDescArr[$prefix]);
            $Fr->LoadFileToTable($tableNameBp . "_" . $prefix, $fileNameBp);
            unlink($fileNameBp);
        }

        $Usr = new User();
        $Usr->SetFlightAvaliable($this->_user->username, $flightId);


        $userId = $Usr->GetUserIdByName($this->_user->username);
        $Fd = new Folder();
        $Fd->PutFlightInFolder($flightId, 0, $userId); //we put currently uploaded file in root
        unset($Fd);
        unset($Usr);

        unset($Fr);

        unlink($tempFilePath);

        return $flightId;
    }

    public function ProccesFlightException($extFlightId,
            $extTempFileName)
    {
        $flightId = $extFlightId;
        $tempFile = $extTempFileName;
        $tempFilePath = UPLOADED_FILES_PATH . "proccessStatus/" . $tempFile;

        $tmpProccStatusFilesDir = UPLOADED_FILES_PATH . "proccessStatus";
        if (!is_dir($tmpProccStatusFilesDir)) {
            mkdir($tmpProccStatusFilesDir);
        }

        $tmpStatus = $this->lang->startFlExcProcc;
        $fp = fopen($tempFilePath, "w");
        fwrite($fp, json_encode($tmpStatus));
        fclose($fp);

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $apTableName = $flightInfo["apTableName"];
        $bpTableName = $flightInfo["bpTableName"];
        $excEventsTableName = $flightInfo["exTableName"];
        $startCopyTime = $flightInfo["startCopyTime"];
        $tableGuid = substr($apTableName, 0, 14);
        unset($Fl);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
        $excListTableName = $bruInfo["excListTableName"];
        $apGradiTableName = $bruInfo["gradiApTableName"];
        $bpGradiTableName = $bruInfo["gradiBpTableName"];
        $stepLength = $bruInfo["stepLength"];

        if ($excListTableName != "")
        {
            $bruInfo = $Bru->GetBruInfo($flightInfo["bruType"]);
            $excListTableName = $bruInfo["excListTableName"];
            $apGradiTableName = $bruInfo["gradiApTableName"];
            $bpGradiTableName = $bruInfo["gradiBpTableName"];

            $FEx = new FlightException();
            $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
            //Get exc refParam list
            $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

            $exList = $FEx->GetFlightExceptionTable($excListTableName);

            //file can be accesed by ajax what can cause warning
            error_reporting(E_ALL ^ E_WARNING);

            //perform proc be cached table
            for($i = 0; $i < count($exList); $i++)
            {
                //50 because we think previous 50 ware used during proc
                $tmpStatus =  round(50 + (50 / count($exList) * $i)) . "%";

                $fp = fopen($tempFilePath, "w");
                fwrite($fp, json_encode($tmpStatus));
                fclose($fp);

                $curExList = $exList[$i];
                $FEx->PerformProcessingByExceptions($curExList,
                        $flightInfo, $flightExTableName,
                        $apTableName, $bpTableName,
                        $startCopyTime, $stepLength);
            }
            unlink($tempFilePath);
            error_reporting(E_ALL);
        }
        else
        {
            unlink($tempFilePath);
        }

        unset($Bru);
    }

    /*public function ProccessComparingToEtalon()
    {
        if(isset($_POST['flightId']) && ($_POST['flightId'] != NULL))
        {
            $flightId = $_POST['flightId'];
        }

        if(isset($_POST['sliceId']) && ($_POST['sliceId'] != NULL))
        {
            $sliceId = $_POST['sliceId'];
        }

        if(isset($_POST['tempFileName']) && ($_POST['tempFileName'] != NULL))
        {
            $tempFile = $_POST['tempFileName'];
        }

        $tempFilePath = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/" . $tempFile;

        $fp = fopen($tempFilePath, "w");
        fwrite($fp, json_encode("proccess"));
        fclose($fp);

        $counstructorData = array("action" => SLICE_COMPARE,
                "flightId" => $flightId,
                "sliceId" => $sliceId);

        //bad style to use View class in async scripts but it is very comfortable here
        //do no populate
        $SliceView = new SliceView($counstructorData);
        $Sl = new Slice();
        $sliceInfo = $Sl->GetSliceInfo($sliceId);
        $sliceTypeInfo = $Sl->GetSliceTypeInfo($sliceInfo['code']);

        //file can be accesed by ajax what can cause warning
        error_reporting(E_ALL ^ E_WARNING);

        if($sliceTypeInfo['children'] != '')
        {
            $childCodesArray = (array)explode(",", $sliceTypeInfo['children']);
            $childCodesArray = array_filter($childCodesArray);
            $childCodesArray = array_map('trim', $childCodesArray);

            for($j = 0; $j < count($childCodesArray); $j++)
            {
                $sliceCode = $childCodesArray[$j];
                $sliceTypeInfo = $Sl->GetSliceTypeInfo($sliceCode);

                $fp = fopen($tempFilePath, "w");
                fwrite($fp, json_encode($sliceCode));
                fclose($fp);

                $SliceView->CompareSliceToEtalon($flightId, $sliceInfo, $sliceTypeInfo, $sliceCode);
            }
        }
        else
        {
            $sliceCode = $this->sliceInfo['code'];

            $fp = fopen($tempFilePath, "w");
            fwrite($fp, json_encode($sliceCode));
            fclose($fp);

            $SliceView->CompareSliceToEtalon($flightId, $sliceInfo, $sliceTypeInfo, $sliceCode);
        }
        unset($Sl);

        error_reporting(E_ALL);

        $fp = fopen($tempFilePath, "w");
        fwrite($fp, json_encode("done"));
        fclose($fp);
        exit();
    }*/

    public function DeleteFlight()
    {
        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($this->flightId);
        $bruType = $flightInfo["bruType"];

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $prefixArr = $Bru->GetBruApGradiPrefixes($bruType);

        $Fl->DeleteFlight($this->flightId, $prefixArr);

        $Usr = new User();
        $Usr->UnsetFlightAvaliable($this->flightId);
        unset($Usr);

        unset($Fl);
    }

    public function ImportFlight($importedFileName)
    {
        $copiedFilesDir = UPLOADED_FILES_PATH;
        $copiedFilePath = $copiedFilesDir . $importedFileName;

        $zip = new ZipArchive;
        $res = $zip->open($copiedFilePath);
        $importFolderName = sprintf("Imported_%s", date('Y-m-d'));
        $needToCreateImportedFolder = true;

        $Fl = new Flight();
        $Bru = new Bru();
        $Fr = new Frame();
        $FlE = new FlightException();
        $Usr = new User();
        $Fd = new Folder();

        $folderInfo = [];

        $userId = $Usr->GetUserIdByName($this->_user->username);

        if ($res === TRUE) {
            $i = 0;
            $headerFiles = [];
            do
            {
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

                $bruType = $flightInfoImported['bruType'];

                $flightId = $Fl->InsertNewFlight($flightInfoImported['bort'], $flightInfoImported['voyage'],
                    $flightInfoImported['startCopyTime'],
                    $flightInfoImported['bruType'], $flightInfoImported['performer'],
                    $flightInfoImported['departureAirport'], $flightInfoImported['arrivalAirport'],
                    $importedFileName, $flightInfoImported['flightAditionalInfo']);

                $flightInfo = $Fl->GetFlightInfo($flightId);

                $tableNameAp = $flightInfo['apTableName'];
                $tableNameBp = $flightInfo['bpTableName'];

                $bruInfo = $Bru->GetBruInfo($bruType);
                $apPrefixes = $Bru->GetBruApCycloPrefixes($bruType);
                $bpPrefixes = $Bru->GetBruBpCycloPrefixes($bruType);

                $apCyclo = $Bru->GetBruApCycloPrefixOrganized($bruType);

                $tables = $Fl->CreateFlightParamTables($flightId,
                    $apCyclo, $bpPrefixes);

                $apTables = $flightInfoImported["apTables"];

                for($j = 0; $j < count($apTables); $j++)
                {
                    $zip->extractTo($copiedFilesDir, $apTables[$j]["file"]);
                    if(file_exists($copiedFilesDir.$apTables[$j]["file"])) {
                        $Fr->LoadFileToTable($tableNameAp . "_" . $apTables[$j]["pref"], $copiedFilesDir.$apTables[$j]["file"]);
                        unlink($copiedFilesDir.$apTables[$j]["file"]);
                    }
                }

                $bpTables = $flightInfoImported["bpTables"];
                for($j = 0; $j < count($bpTables); $j++)
                {
                    $zip->extractTo($copiedFilesDir, $bpTables[$j]["file"]);
                    if(file_exists($copiedFilesDir.$bpTables[$j]["file"])) {
                        $Fr->LoadFileToTable($tableNameBp . "_" . $bpTables[$j]["pref"], $copiedFilesDir.$bpTables[$j]["file"]);
                        unlink($copiedFilesDir.$bpTables[$j]["file"]);
                    }
                }

                if(isset($flightInfoImported["exTableName"]) &&
                    $flightInfoImported["exTableName"] != "")
                {
                    $tableGuid = substr($tableNameAp, 0, 14);
                    $FlE->CreateFlightExceptionTable($flightId, $tableGuid);
                    $flightInfo = $Fl->GetFlightInfo($flightId);

                    $exTables = $flightInfoImported["exTables"];
                    $zip->extractTo($copiedFilesDir, $exTables);
                    $Fr->LoadFileToTable($flightInfo["exTableName"], $copiedFilesDir.$exTables);
                    if(file_exists($copiedFilesDir.$exTables)) {
                        unlink($copiedFilesDir.$exTables);
                    }
                }

                $Usr->SetFlightAvaliable($this->_user->username, $flightId);

                if(count($headerFiles) > 1) {
                    if($needToCreateImportedFolder) {
                        $folderInfo = $Fd->CreateFolder($importFolderName, 0, $userId);
                        $needToCreateImportedFolder = false;
                    }

                    if(isset($folderInfo['folderId'])) {
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
            unset($Usr);
            unset($Bru);

            if(count($headerFiles) <= 0) {
                return false;
            }

            return true;
        } else {
            return false;
        }
    }

    public function RegisterActionExecution($extAction, $extStatus,
        $extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
    {
        $action = $extAction;
        $status = $extStatus;
        $senderId = $extSenderId;
        $senderName = $extSenderName;
        $targetId = $extTargetId;
        $targetName = $extTargetName;

        $userInfo = $this->GetUserInfo();
        $userId = $userInfo['id'];

        $U = new User();
        $U->RegisterUserAction($action, $status, $userId,
            $senderId, $senderName, $targetId, $targetName);

        unset($U);
    }

    public function RegisterActionReject($extAction, $extStatus,
        $extSenderId = null, $extSenderName = null, $extTargetId = null, $extTargetName = null)
    {
        $action = $extAction;
        $status = $extStatus;
        $senderId = $extSenderId;
        $senderName = $extSenderName;
        $targetId = $extTargetId;
        $targetName = $extTargetName;
        $userInfo = $this->GetUserInfo();
        $userId = $userInfo['id'];

        $U = new User();
        $U->RegisterUserAction($action, $status, $userId,
                $senderId, $senderName, $targetId, $targetName);

        unset($U);
    }

    public function GetUserInfo()
    {
        $U = new User();
        $uId = $U->GetUserIdByName($this->_user->username);
        $userInfo = $U->GetUserInfo($uId);
        unset($U);

        return $userInfo;
    }
}
