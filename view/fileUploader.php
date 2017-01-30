<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");
require_once(@$_SERVER['DOCUMENT_ROOT'] ."/controller/UploaderController.php");

$c = new UploaderController();

if ($c->_user && isset($c->_user->username) && ($c->_user->username !== '')) {
    if($c->action === "flightShowUploadingOptions") {
        if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['index']) &&
                    isset($c->data['bruType']) &&
                    isset($c->data['file']))
            {
                $index = $c->data['index'];
                $bruType = $c->data['bruType'];
                $filePath = UPLOADED_FILES_PATH . $c->data['file'];

                $flightParamsSrt = $c->ShowFlightParams($index, $bruType, $filePath);

                $answ["status"] = "ok";
                $answ["data"] = $flightParamsSrt;
                echo(json_encode($answ));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page fileUploader.php";
                echo(json_encode($answ));
            }
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightUploaderPreview")
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['bruType']) &&
                    isset($c->data['file']))
            {
                $bruType = $c->data['bruType'];
                $filePath = UPLOADED_FILES_PATH . $c->data['file'];

                $c->CopyPreview($bruType, $filePath);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". " .
                        "Action: " .
                        $c->action . ". Page fileUploader.php";
                echo(json_encode($answ));
            }
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightCutFile")
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['bruType']) &&
                    isset($c->data['file']) &&
                    isset($c->data['startCopyTime']) &&
                    isset($c->data['endCopyTime']) &&
                    isset($c->data['startSliceTime']) &&
                    isset($c->data['endSliceTime']))
            {
                $bruType = $c->data['bruType'];
                $filePath = $c->data['file'];

                $startCopyTime = $c->data['startCopyTime'];
                $endCopyTime = $c->data['endCopyTime'];
                $startSliceTime = $c->data['startSliceTime'];
                $endSliceTime = $c->data['endSliceTime'];

                $c->CutCopy($bruType, $filePath,
                    $startCopyTime, $endCopyTime,
                    $startSliceTime, $endSliceTime);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". " .
                        "Action: " .
                        $c->action . ". Page fileUploader.php";
                echo(json_encode($answ));
            }
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightCyclicSliceFile")
    {
        if(in_array(User::$PRIVILEGE_EDIT_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['bruType']) &&
                    isset($c->data['file']) &&
                    isset($c->data['startCopyTime']) &&
                    isset($c->data['endCopyTime']) &&
                    isset($c->data['startSliceTime']))
            {
                $bruType = $c->data['bruType'];
                $filePath = $c->data['file'];

                $startCopyTime = $c->data['startCopyTime'];
                $endCopyTime = $c->data['endCopyTime'];
                $startSliceTime = $c->data['startSliceTime'];

                $c->CyclicSliceCopy($bruType, $filePath,
                        $startCopyTime, $endCopyTime, $startSliceTime);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". " .
                        "Action: " .
                        $c->action . ". Page fileUploader.php";
                echo(json_encode($answ));
            }
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightProcces")
    {
        if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['bruType']) &&
                    isset($c->data['fileName']) &&
                    isset($c->data['tempFileName']) &&
                    isset($c->data['flightInfo']) &&
                    isset($c->data['flightAditionalInfo']))
            {
                $bruType = $c->data['bruType'];
                $uploadedFile = $c->data['fileName'];

                $tempFileName = $c->data['tempFileName'];
                $receivedFlightInfo = $c->data['flightInfo'];
                $receivedFlightAditionalInfo = $c->data['flightAditionalInfo'];
                $flightInfo = array();
                $flightAditionalInfo = array();

                //in such way it was passed in js because of imposible to do it by usual asoc arr
                for($i = 0; $i < count($receivedFlightInfo); $i+=2)
                {
                    if((string)$receivedFlightInfo[$i + 1] != '')
                    {
                        $flightInfo[(string)$receivedFlightInfo[$i]] =
                            (string)$receivedFlightInfo[$i + 1];
                    }
                    else
                    {
                        $flightInfo[(string)$receivedFlightInfo[$i]] = "x";
                    }
                }

                $aditionalInfoVars = '';
                if($receivedFlightAditionalInfo != '0')
                {
                    for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2)
                    {
                        $flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] =
                            (string)$receivedFlightAditionalInfo[$i + 1];
                    }

                    foreach($flightAditionalInfo as $key => $val)
                    {
                        $aditionalInfoVars .= $key . ":" . $val . ";";
                    }
                }

                $bort = $flightInfo["bort"];
                $voyage = $flightInfo["voyage"];
                $copyCreationTime = $flightInfo["copyCreationTime"];
                $copyCreationDate = $flightInfo["copyCreationDate"];
                $performer = $flightInfo["performer"];
                $departureAirport = $flightInfo["departureAirport"];
                $arrivalAirport = $flightInfo["arrivalAirport"];
                $totalPersentage = 100;

                $c->ProccessFlightData($tempFileName,
                    $bort,
                    $voyage,
                    $copyCreationTime,
                    $copyCreationDate,
                    $bruType,
                    $performer,
                    $departureAirport,
                    $arrivalAirport,
                    $aditionalInfoVars,
                    $uploadedFile,
                    $totalPersentage
                );

                $answ = array(
                        "status" => "ok",
                        "data" => $uploadedFile
                );
                echo(json_encode($answ));
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = "Not all nessesary params sent. Post: ".
                        json_encode($_POST) . ". " .
                        "Action: " .
                        $c->action . ". Page fileUploader.php";
                echo(json_encode($answ));
            }
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightProccesAndCheck")
    {
        if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
        {
            if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
            {
                if(isset($c->data['bruType']) &&
                        isset($c->data['fileName']) &&
                        isset($c->data['tempFileName']) &&
                        isset($c->data['flightInfo']) &&
                        isset($c->data['flightAditionalInfo']))
                {
                    $bruType = $c->data['bruType'];
                    $uploadedFile = $c->data['fileName'];

                    $tempFileName = $c->data['tempFileName'];
                    $receivedFlightInfo = $c->data['flightInfo'];
                    $receivedFlightAditionalInfo = $c->data['flightAditionalInfo'];
                    $flightInfo = array();
                    $flightAditionalInfo = array();

                    //in such way it was passed in js because of imposible to do it by usual aasoc arr
                    for($i = 0; $i < count($receivedFlightInfo); $i+=2)
                    {
                        if((string)$receivedFlightInfo[$i + 1] != '')
                        {
                            $flightInfo[(string)$receivedFlightInfo[$i]] =
                                (string)$receivedFlightInfo[$i + 1];
                        }
                        else
                        {
                            $flightInfo[(string)$receivedFlightInfo[$i]] = "x";
                        }
                    }

                    $aditionalInfoVars = '';
                    if($receivedFlightAditionalInfo != 0)
                    {
                        for($i = 0; $i < count($receivedFlightAditionalInfo); $i+=2)
                        {
                            $flightAditionalInfo[(string)$receivedFlightAditionalInfo[$i]] =
                                (string)$receivedFlightAditionalInfo[$i + 1];
                        }

                        foreach($flightAditionalInfo as $key => $val)
                        {
                            $aditionalInfoVars .= $key . ":" . $val . ";";
                        }
                    }

                    $bort = $flightInfo["bort"];
                    $voyage = $flightInfo["voyage"];
                    $copyCreationTime = $flightInfo["copyCreationTime"];
                    $copyCreationDate = $flightInfo["copyCreationDate"];
                    $performer = $flightInfo["performer"];
                    $departureAirport = $flightInfo["departureAirport"];
                    $arrivalAirport = $flightInfo["arrivalAirport"];
                    $totalPersentage = 50;

                    $flightId = $c->ProccessFlightData($tempFileName,
                        $bort,
                        $voyage,
                        $copyCreationTime,
                        $copyCreationDate,
                        $bruType,
                        $performer,
                        $departureAirport,
                        $arrivalAirport,
                        $aditionalInfoVars,
                        $uploadedFile,
                        $totalPersentage
                    );

                    $c->ProccesFlightException($flightId,
                            $tempFileName
                    );

                    $answ = array(
                            "status" => "ok",
                            "data" => $uploadedFile
                    );
                    echo(json_encode($answ));
                }
                else
                {
                    $answ["status"] = "err";
                    $answ["error"] = "Not all nessesary params sent. Post: ".
                            json_encode($_POST) . ". " .
                            "Action: " .
                            $c->action . ". Page fileUploader.php";
                    echo(json_encode($answ));
                }
            }
            else
            {
                echo($c->lang->notAllowedByPrivilege);
            }
        }
    }
    else if($c->action === "flightProccesCheckAndCompareToEtalon")
    {
        if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
        {

            $answ["status"] = "ok";
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "flightEasyUpload")
    {
        if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
        {
            if(in_array(User::$PRIVILEGE_ADD_FLIGHTS, $c->_user->privilege))
            {
                if(isset($c->data['bruType']) &&
                        isset($c->data['fileName']) &&
                        isset($c->data['tempFileName']))
                {
                    $bruType = $c->data['bruType'];
                    $fileName = $c->data['fileName'];
                    $uploadedFile = UPLOADED_FILES_PATH . $fileName;
                    $tempFileName = $c->data['tempFileName'];

                    $flightInfoFromHeader = $c->ReadHeader($bruType, $uploadedFile);

                    $bort = "x";
                    if(isset($flightInfoFromHeader["bort"]))
                    {
                        $bort = $flightInfoFromHeader["bort"];
                    }

                    $voyage = "x";
                    if(isset($flightInfoFromHeader["voyage"]))
                    {
                        $voyage = $flightInfoFromHeader["voyage"];
                    }

                    $departureAirport = "x";
                    if(isset($flightInfoFromHeader["departureAirport"]))
                    {
                        $departureAirport = $flightInfoFromHeader["departureAirport"];
                    }

                    $arrivalAirport = "x";
                    if(isset($flightInfoFromHeader["arrivalAirport"]))
                    {
                        $arrivalAirport = $flightInfoFromHeader["arrivalAirport"];
                    }

                    $copyCreationTime = "00:00:00";
                    $copyCreationDate = "2000-01-01";
                    if(isset($flightInfoFromHeader['startCopyTime']))
                    {
                        $startCopyTime = strtotime($flightInfoFromHeader['startCopyTime']);
                        $copyCreationTime = date('H:i:s', $startCopyTime);
                        $copyCreationDate = date('Y-m-d', $startCopyTime);
                    }

                    $performer = null;

                    $aditionalInfoVars = $c->CheckAditionalInfoFromHeader($bruType, $flightInfoFromHeader);
                    $totalPersentage = 50;

                    $flightId = $c->ProccessFlightData($tempFileName,
                            $bort,
                            $voyage,
                            $copyCreationTime,
                            $copyCreationDate,
                            $bruType,
                            $performer,
                            $departureAirport,
                            $arrivalAirport,
                            $aditionalInfoVars,
                            $uploadedFile,
                            $totalPersentage
                    );

                    $c->ProccesFlightException($flightId,
                            $tempFileName
                    );

                    $answ = array(
                            "status" => "ok",
                            "data" => $fileName
                    );
                    echo(json_encode($answ));
                }
                else
                {
                    $answ["status"] = "err";
                    $answ["error"] = "Not all nessesary params sent. Post: ".
                            json_encode($_POST) . ". " .
                            "Action: " .
                            $c->action . ". Page fileUploader.php";
                    echo(json_encode($answ));
                }
            }
            else
            {
                echo($c->lang->notAllowedByPrivilege);
            }
        }
    }
    else if($c->action === "flightDelete")
    {
        if(in_array(User::$PRIVILEGE_DEL_FLIGHTS, $c->_user->privilege))
        {
            $c->DeleteFlight();

            echo("<script>location.href=location.protocol + '//' + location.host + '/index.php'</script>");
            exit();
        }
        else
        {
            echo($c->lang->notAllowedByPrivilege);
        }
    }
    else if($c->action === "itemImport")
    {
        if(in_array(User::$PRIVILEGE_VIEW_FLIGHTS, $c->_user->privilege))
        {
            if(isset($c->data['file']))
            {
                $file = $c->data['file'];
                $result = $c->ImportFlight($file);

                $answ = array();
                if($result)
                {
                    $answ = [
                        'status' => 'ok'
                    ];

                    $action = $c->action;
                    $c->RegisterActionExecution($action, "executed", $file, "fileName");
                }
                else
                {
                    $answ['status'] = 'err';
                    $answ['data']['error'] = 'Error during flight import.';
                    $c->RegisterActionReject($c->action, "rejected", 0, $answ['data']['error']);
                }
                echo json_encode($answ);
            }
            else
            {
                $answ["status"] = "err";
                $answ["error"] = $c->lang->notAllowedByPrivilege;
                $c->RegisterActionReject($c->action, "rejected", 0, 'notAllowedByPrivilege');
                echo(json_encode($answ));
            }
        }
        else
        {
                echo("Undefined action. Data: " . json_encode($_POST['data']) .
                    " . Action: " . json_encode($_POST['action']) .
                    " . Page: " . $c->curPage. ".");
                error_log("Undefined action. Data: " . json_encode($_POST['data']) .
                    " . Action: " . json_encode($_POST['action']) .
                    " . Page: " . $c->curPage. ".");
        }
    }
    else
    {
        echo("Authorization error. Page: " . $c->curPage);
        error_log("Authorization error. Page: " . $c->curPage);
    }
}
