<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class Frame
{
    public function MoveUploadingFile($extFileName, $extFilePath)
    {
        $fileName = $extFileName;
        $filePath = $extFilePath;

        $uploadedFilesDir = $_SERVER['DOCUMENT_ROOT'] . "/uploadedFiles/";

        $timeY = date("Y-m-d");
        $timeH = date("H-i-s");
        $uploadedFile = $uploadedFilesDir.$timeY."_".$timeH."_".$fileName;
        if($filePath != NULL)
        {
            $moveUploadedFile = move_uploaded_file(@$filePath, $uploadedFile);
        }
        else
        {
            //log
            error_log("filePath - " . $filePath. " No file assignment during file uploading. Frame.php");
        }

        return $uploadedFile;
    }

    public function OpenFile($extUploadedFile)
    {
        $uploadedFile = $extUploadedFile;
        $fileDesc = fopen($uploadedFile, 'rb');
        if(!$fileDesc)
        {
            //log
            echo("File openning error");
        }

        return $fileDesc;
    }

    public function GetFileSize($extFileDesc)
    {
        $fileDesc = $extFileDesc;
        return filesize($fileDesc);

    }

    public function CloseFile($extFileDesc)
    {
        $fileDesc = $extFileDesc;
        fclose($fileDesc);
    }

    public function ReadHeader($extFileDesc, $extHeaderLength)
    {
        $fileDesc = $extFileDesc;
        $headerLength = $extHeaderLength;
        $header = fread($fileDesc, $headerLength);
        return $header;
    }

    public function ReadFrame($extFileDesc, $extFrameLength)
    {
        $fileDesc = $extFileDesc;
        $frameLength = $extFrameLength;
        $frame = fread($fileDesc, $frameLength);
        return $frame;
    }

    public function ConvertFrameToPhisics($extSplitedFrame, $extStartTime, $extStepLength, $extChannelFreq, $extFrameNum, $extCycloAp, & $algHeap)
    {
        $frame = $extSplitedFrame;
        $startTime = $extStartTime;
        $stepLength = $extStepLength;
        $channelFreq = $extChannelFreq;
        $frameNum = $extFrameNum;
        $cycloAp = $extCycloAp;

        $phisicsFrame = array();

        for($ind1 = 0; $ind1 < count($cycloAp); $ind1++)
        {
            $paramCyclo = $cycloAp[$ind1];
            $channels = explode(",", $paramCyclo["channel"]);
            $channels = array_map('trim', $channels);

            $paramType = $paramCyclo['type'];
            $paramTypeArr = array();
            $paramTypeArg = array();

            if(strpos("/", $paramType) > -1)
            {
                $paramTypeArr = explode("/", $paramType);

                $paramTypeArg = $paramTypeArr[0];
                $paramType = $paramTypeArr[1];

                if(strpos("i", $paramTypeArg) > -1)
                {
                    $codeValue = ~$codeValue;
                }

                if(strpos("r", $paramTypeArg) > -1)
                {
                    $newCodeVal = '';
                    for($rotInd = strlen($codeValue) - 2; $rotInd >= 0; $rotInd-=2)
                    {
                        $newCodeVal .= substr ($codeValue, $rotInd, 2);
                    }
                    $codeValue = $newCodeVal;
                }
            }

            $interview = array();
            for($ind2 = 0; $ind2 < count($channels); $ind2++)
            {
                $codeValue = $frame[$channels[$ind2]];

                //get phisics analog param from code
                if($paramType == 1)//type 1 uses for graduated params
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $gradi = $paramCyclo['xy'];

                    for($j = 0; $j < count($gradi); $j++)
                    {
                        if($apCode <= $gradi[$j]['y'])
                        {
                            break;
                        }
                    }

                    //faling extrapolation
                    if($j == 0)
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[0];
                            $p1 = $gradi[1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }
                    //rising extrapolation
                    else if($j >= (count($gradi) - 1))
                    {
                        //exact match
                        if($apCode == $gradi[count($gradi) - 1]['y'])
                        {
                            $phisics = $gradi[count($gradi) - 1]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[count($gradi) - 2];
                            $p1 = $gradi[count($gradi) - 1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }

                    }
                    //interpolation
                    else
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[$j - 1];
                            $p1 = $gradi[$j];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }

                    array_push($interview, $phisics);
                }
                else if($paramType == 2)//calc param
                {
                    $alg = $paramCyclo['alg'];
                    $alg = str_replace("[p]", "'" . $codeValue . "'", $alg);
                    $alg = str_replace("[k]", $paramCyclo['k'], $alg);
                    $alg = str_replace("[mask]", $paramCyclo['mask'], $alg);
                    $alg = str_replace("[shift]", $paramCyclo['shift'], $alg);
                    $alg = str_replace("[minus]", $paramCyclo['minus'], $alg);
                    $alg = str_replace("[xy]", json_encode($paramCyclo['xy']), $alg);

                    eval($alg);//$phisics must be assigned in alg

                    array_push($interview, $phisics);

                }
                else if($paramType == 3)//left bit as sign
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift']);
                    $minus = (hexdec($codeValue) & $paramCyclo['minus']);
                    if($minus > 0)
                    {
                        $apCode = $apCode * -1;
                    }

                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 4)//unsigned params with coef
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 41)//unsigned params with coef and invers by 255
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $phisics = 255 - ($apCode * $paramCyclo['k']);

                    array_push($interview, $phisics);
                }
                else if($paramType == 42)// simple HEX
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $apCode = dechex($apCode);
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 5)//signed params with coef
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift'] + 1);
                    $minus = (hexdec($codeValue) & $paramCyclo['mask']) >> ($paramCyclo['shift']);
                    if($minus > $paramCyclo['mask'] / 2)
                    {
                        $apCode = $apCode - $paramCyclo['mask'];
                    }
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 6)//unsigned params with coef with rotation bytes in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;

                    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 62)//HEX rotation
                {
                    $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;

                    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $apCode = dechex($apCode);
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 7)//using field minus to find negative values with rotation bytes in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    if($apCode >= $paramCyclo['minus'])
                    {
                        $apCode -= $paramCyclo['minus'] * 2;
                    }
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 8)//signed params with coef with negative values
                {
                     $apCode = (hexdec($codeValue));
                     if($apCode > 32768)
                     {
                         $apCode -= 65535;
                     }
                     $phisics = $apCode * $paramCyclo['k'];

                     array_push($interview, $phisics);
                }
                else if($paramType == 9)//signed params with coef with gradual rotation
                {
                    $apCode = (hexdec($codeValue));
                    if($apCode > 32768)
                    {
                        $apCode -= 65535;
                    }
                    $phisics = $apCode * $paramCyclo['k'];
                    if($phisics < 0)
                    {
                        $phisics += 360;
                    }

                    array_push($interview, $phisics);
                }
                else if($paramType == 10)//using field minus to find negative values
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    if($apCode >= $paramCyclo['minus'])
                    {
                        $apCode -= $paramCyclo['minus'] * 2;
                    }
                    $phisics = $apCode * $paramCyclo['k'];
                    array_push($interview, $phisics);
                } else if($paramType == 30) {
                    if($paramCyclo['SUB'] ==  0)
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                        if($apCode >= $paramCyclo['minus']) {
                            $apCode -= $paramCyclo['minus'] * 2;
                        }
                        $phisics = $apCode * $paramCyclo['k'];

                    } else {
                        $phisics = 211;
                    }
                    array_push($interview, $phisics);
                }
                else if($paramType == 21)// graduated with invertion
                {
                    $apCode = (hexdec($codeValue) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $apCode = 255 - $apCode;
                    $gradi = $paramCyclo['xy'];

                    for($j = 0; $j < count($gradi); $j++)
                    {
                        if($apCode <= $gradi[$j]['y'])
                        {
                            break;
                        }
                    }

                    //faling extrapolation
                    if($j == 0)
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[0];
                            $p1 = $gradi[1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }
                    //rising extrapolation
                    else if($j >= (count($gradi) - 1))
                    {
                        //exact match
                        if($apCode == $gradi[count($gradi) - 1]['y'])
                        {
                            $phisics = $gradi[count($gradi) - 1]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[count($gradi) - 2];
                            $p1 = $gradi[count($gradi) - 1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }

                    }
                    //interpolation
                    else
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[$j - 1];
                            $p1 = $gradi[$j];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }

                    array_push($interview, $phisics);
                }
                else if($paramType == 22)// graduated with rotation
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $gradi = $paramCyclo['xy'];

                    for($j = 0; $j < count($gradi); $j++)
                    {
                        if($apCode <= $gradi[$j]['y'])
                        {
                            break;
                        }
                    }

                    //faling extrapolation
                    if($j == 0)
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[0];
                            $p1 = $gradi[1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }
                    //rising extrapolation
                    else if($j >= (count($gradi) - 1))
                    {
                        //exact match
                        if($apCode == $gradi[count($gradi) - 1]['y'])
                        {
                            $phisics = $gradi[count($gradi) - 1]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[count($gradi) - 2];
                            $p1 = $gradi[count($gradi) - 1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }

                    }
                    //interpolation
                    else
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[$j - 1];
                            $p1 = $gradi[$j];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }

                    array_push($interview, $phisics);
                }
                else if($paramType == 23)//unsigned params with coef with rotation bytes and inversion in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;

                    $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 24)//signed params with coef with rotation bytes and inversion in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);//because 2 hex  digits in byte
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;

                    $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                        if($apCode > $paramCyclo['minus'])
                            {$apCode = $apCode - $paramCyclo['mask'];}
                    $phisics = $apCode * $paramCyclo['k'];

                    array_push($interview, $phisics);
                }
                else if($paramType == 25)// graduated with rotation and inversion
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $apCode = ((65535 - hexdec($rotatedStr)) & $paramCyclo['mask']) >> $paramCyclo['shift'];
                    $gradi = $paramCyclo['xy'];

                    for($j = 0; $j < count($gradi); $j++)
                    {
                        if($apCode <= $gradi[$j]['y'])
                        {
                            break;
                        }
                    }

                    //faling extrapolation
                    if($j == 0)
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[0];
                            $p1 = $gradi[1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }
                    //rising extrapolation
                    else if($j >= (count($gradi) - 1))
                    {
                        //exact match
                        if($apCode == $gradi[count($gradi) - 1]['y'])
                        {
                            $phisics = $gradi[count($gradi) - 1]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[count($gradi) - 2];
                            $p1 = $gradi[count($gradi) - 1];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }

                    }
                    //interpolation
                    else
                    {
                        //exact match
                        if($apCode == $gradi[$j]['y'])
                        {
                            $phisics = $gradi[$j]['x'];
                        }
                        else
                        {
                            $p = $apCode;
                            $p0 = $gradi[$j - 1];
                            $p1 = $gradi[$j];

                            if($p1['y'] - $p0['y'] == 0)
                            {
                                $phisics = 0;
                            }
                            else
                            {
                                $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
                                        ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
                            }
                        }
                    }

                    array_push($interview, $phisics);
                }
            }
            array_push($phisicsFrame, $interview);
        }


        $phisicsFrame = $this->RotatePhisicsFrame($phisicsFrame, $startTime, $stepLength, $channelFreq, $frameNum);
        return $phisicsFrame;
    }

    private function RotatePhisicsFrame($extPhisicsFrame, $extStartTime, $extStepLength, $extChannelFreq, $extFrameNum)
    {
        $phisicsFrame = $extPhisicsFrame;
        $startTime = $extStartTime;
        $stepLength = $extStepLength;
        $channelFreq = $extChannelFreq;
        $frameNum = $extFrameNum;

        $phisicsFrameCopy = $phisicsFrame;
        $phisicsFrame = array();

        for($i = 0; $i < $channelFreq; $i++)
        {
            $line = array();
            array_push($line, $frameNum);
            array_push($line, ($startTime + ($frameNum * $stepLength) + ($stepLength / $channelFreq * $i)) * 1000);

            for($j = 0; $j < count($phisicsFrameCopy); $j++)
            {
                array_push($line, $phisicsFrameCopy[$j][$i]);
            }
            array_push($phisicsFrame, $line);
        }

        return $phisicsFrame;
    }

    public function ConvertFrameToBinaryParams($extSplitedFrame,
            $extFrameNum,
            $extStartTime,
            $extStepLength,
            $extChannelFreq,
            $extCycloBp,
            $apPhisicsByPrefixes,
            & $algHeap)
    {
        $frame = $extSplitedFrame;
        $frameNum = $extFrameNum;
        $cycloBp = $extCycloBp;
        $startTime = $extStartTime;
        $channelFreq = $extChannelFreq;
        $stepLength = $extStepLength;

        $phisicsBinaryParamsFrame = array();

        //get binary param from this code
        foreach($cycloBp as $binParam)
        {
            $channels = explode(",", $binParam["channel"]);
            $channels = array_map('trim', $channels);

            $binParamType = $binParam['type'];
            if(strpos("/", $binParamType) > -1)
            {
                $binParamTypeArr = explode("/", $binParam['type']);

                $binParamTypeArg = $binParamTypeArr[0];
                $binParamType = $binParamTypeArr[1];

                if(strpos("i", $binParamTypeArg) > -1)
                {
                    $codeValue = ~$codeValue;
                }

                if(strpos("r", $binParamTypeArg) > -1)
                {
                    $newCodeVal = '';
                    for($rotInd = strlen($codeValue) - 2; $rotInd >= 0; $rotInd-=2)
                    {
                        $newCodeVal .= substr ($codeValue, $rotInd, 2);
                    }
                    $codeValue = $newCodeVal;
                }
            }

            for($chInd = 0; $chInd < count($channels); $chInd++)
            {
                $codeValue = $frame[$channels[$chInd]];

                if($binParamType == 1)
                {
                    $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                    if($bpCode > 0)
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 2)//rotation bytes in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                    if($bpCode > 0)
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 41)//rotation bytes in word and > MASK
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = hexdec($rotatedStr);
                    if($bpCode >= $binParam['mask'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 42)//rotation bytes in word and < MASK
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = hexdec($rotatedStr);
                    if($bpCode < $binParam['mask'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }

                //virtual bp
                else if($binParamType == 3)
                {
                    $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                    if(!($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                //virtual bp
                else if($binParamType == 4)//rotation bytes in word
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                    if(!($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 5)
                {
                    $bpCode = (hexdec($codeValue) & $binParam['basis']);//decbin
                    if($bpCode == $binParam['mask'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 6)
                {
                    $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                    if(!($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 7)
                {
                    $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin ( virtual type  6)
                    if(($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 8)//rotation bytes in word and inversion from 65535
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = ((65535 - hexdec($rotatedStr)) & $binParam['mask']);//decbin
                    if(($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 88)//virtual 8
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = ((65535 - hexdec($rotatedStr)) & $binParam['mask']);//decbin
                    if(!($bpCode > 0))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 80)//rotation bytes in word and inversion from 65535 and code = MASK
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = 65535 - hexdec($rotatedStr);
                    if(($bpCode == $binParam['mask']))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 81)//rotation bytes in word and inversion from 65535 and code >= MASK
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = 65535 - hexdec($rotatedStr);
                    if(($bpCode >= $binParam['mask']))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 82)//invers 81 - rotation bytes in word and inversion from 65535 and code < MASK
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = 65535 - hexdec($rotatedStr);
                    if(($bpCode < $binParam['mask']))
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 83)//rotation bytes in word and inversion from 65535
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = 65535 - hexdec($rotatedStr);
                    $binAlgHeap['PARAM_NAME'] =  $bpCode;
                    }
                else if($binParamType == 71)
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $bpCode = hexdec($tempStr1);
                    if
                    (
                    (($bpCode <= 2) & ($bpCode >= 0))
                 ||    (($bpCode <= 32) & ($bpCode >= 28))
                 || (($bpCode <= 64) & ($bpCode >= 60))
                 || (($bpCode <= 96) & ($bpCode >= 92))
                 || (($bpCode <= 128) & ($bpCode >= 124))
                 || (($bpCode <= 160) & ($bpCode >= 156))
                 || (($bpCode <= 192) & ($bpCode >= 188))
                 || (($bpCode <= 224) & ($bpCode >= 220))
                    )
                            {
                            $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                }
                else if($binParamType == 72)
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $bpCode = hexdec($tempStr1);
                    if
                    (
                    (($bpCode <= 2) & ($bpCode >= 0))
                 ||    (($bpCode <= 16) & ($bpCode >= 12))
                 || (($bpCode <= 64) & ($bpCode >= 60))
                 || (($bpCode <= 80) & ($bpCode >= 76))
                 || (($bpCode <= 128) & ($bpCode >= 124))
                 || (($bpCode <= 144) & ($bpCode >= 140))
                 || (($bpCode <= 192) & ($bpCode >= 188))
                 || (($bpCode <= 208) & ($bpCode >= 204))
                    )
                            {
                            $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                }

                else if($binParamType == 73)
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $bpCode = hexdec($tempStr1);
                    if
                    (
                    (($bpCode <= 2) & ($bpCode >= 0))
                 ||    (($bpCode <= 16) & ($bpCode >= 12))
                 || (($bpCode <= 32) & ($bpCode >= 28))
                 || (($bpCode <= 48) & ($bpCode >= 44))
                 || (($bpCode <= 128) & ($bpCode >= 124))
                 || (($bpCode <= 144) & ($bpCode >= 140))
                 || (($bpCode <= 160) & ($bpCode >= 156))
                 || (($bpCode <= 176) & ($bpCode >= 172))
                    )
                            {
                            $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                }

                else if($binParamType == 74)
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $bpCode = hexdec($tempStr1);
                    if
                    (
                    (($bpCode <= 2) & ($bpCode >= 0))
                 ||    (($bpCode <= 16) & ($bpCode >= 12))
                 || (($bpCode <= 32) & ($bpCode >= 28))
                 || (($bpCode <= 48) & ($bpCode >= 44))
                 || (($bpCode <= 64) & ($bpCode >= 60))
                 || (($bpCode <= 80) & ($bpCode >= 76))
                 || (($bpCode <= 96) & ($bpCode >= 92))
                 || (($bpCode <= 112) & ($bpCode >= 108))
                    )
                            {
                            $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                }
                else if($binParamType == 21)//rotation bytes in word and for TCAS (with Basis)
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                    if($bpCode == $binParam['basis'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 22)//rotation bytes in word and for TCAS (with Basis)  (mask 63)  4,5,6 Bits
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 3;//decbin
                    if($bpCode == $binParam['basis'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 23)//rotation bytes in word and for TCAS (with Basis)  (mask 511) 7,8,9 Bits
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 6;//decbin
                    if($bpCode == $binParam['basis'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 24)//rotation bytes in word and for TCAS (with Basis)  (mask 4095) 10,11,12 Bits
                {
                    $tempStr1 = substr ($codeValue, 0, 2);
                    $tempStr2 = substr ($codeValue, 2, 2);
                    $rotatedStr = $tempStr2 . $tempStr1;
                    $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 9;//decbin
                    if($bpCode == $binParam['basis'])
                    {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                    }
                }
                else if($binParamType == 121)//rotation bytes any Bits and = Basis  (Frame = 0)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 122)//rotation bytes in word and for TCAS (with Basis)  (mask 63)  4,5,6 Bits (Frame = 0)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 3;//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 123)//rotation bytes in word and for TCAS (with Basis)  (mask 511) 7,8,9 Bits (Frame = 0)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 6;//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 124)//rotation bytes in word and for TCAS (with Basis)  (mask 4095) 10,11,12 Bits (Frame = 0)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']) >> 9;//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 125)//rotation bytes any Bits and = Basis  (Frame = 1)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 1))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 126)//rotation bytes any Bits and = Basis  (Frame = 2)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 2))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 127)//rotation bytes any Bits and = Basis  (Frame = 3)
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 3))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode == $binParam['basis'])
                        {
                        $param = array("frameNum" => $frameNum,
                                "time" => ($startTime + ($frameNum * $stepLength) +
                                    ($stepLength / $channelFreq * $chInd)) * 1000,
                                "code" => $binParam['code']);
                        array_push($phisicsBinaryParamsFrame, $param);
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                        {
                            if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                            {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            }
                        }
                }
                else if($binParamType == 27) // frame = 0
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 271) //rev 27
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if(!($bpCode > 0))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 28)  // frame = 1
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 1))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 29)  // frame = 2
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 2))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 30)  // frame = 3
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 3))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 31)  // frame = 1 or frame = 3
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 1) || ($apPhisicsByPrefixes['1'][0][2] == 3))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 32) // frame = 0 or frame = 2
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0) || ($apPhisicsByPrefixes['1'][0][2] == 2))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if($bpCode > 0)
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 311) //inv 31
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 1) || ($apPhisicsByPrefixes['1'][0][2] == 3))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if(!($bpCode > 0))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
                else if($binParamType == 321) // inv 32
                {
                    if(($apPhisicsByPrefixes['1'][0][2] == 0) || ($apPhisicsByPrefixes['1'][0][2] == 2))
                    {
                        $tempStr1 = substr ($codeValue, 0, 2);
                        $tempStr2 = substr ($codeValue, 2, 2);
                        $rotatedStr = $tempStr2 . $tempStr1;
                        $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                        if(!($bpCode > 0))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                            $algHeap[$binParam['code']] = 1;
                        }
                        else
                        {
                            $algHeap[$binParam['code']] = 0;
                        }
                    }
                    else
                    {
                        if(isset($algHeap[$binParam['code']]) &&
                                ($algHeap[$binParam['code']] == 1))
                        {
                            $param = array("frameNum" => $frameNum,
                                    "time" => ($startTime + ($frameNum * $stepLength) +
                                            ($stepLength / $channelFreq * $chInd)) * 1000,
                                    "code" => $binParam['code']);
                            array_push($phisicsBinaryParamsFrame, $param);
                        }
                    }
                }
            }
        }

        return $phisicsBinaryParamsFrame;
    }

    /*public function ConvertFrameToBinaryParams($extSplitedFrame, $extFrameNum, $extCycloBp)
    {
        $frame = $extSplitedFrame;
        $frameNum = $extFrameNum;
        $cycloBp = $extCycloBp;

        $phisicsBinaryParamsFrame = array();

        //get binary param from this code
        foreach($cycloBp as $binParam)
        {
            $codeValue = $frame[$binParam["channel"]];

            if($binParam['type'] == 1)
            {
                $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                if($bpCode > 0)
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'],
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            else if($binParam['type'] == 2)//rotation bytes in word
            {
                $tempStr1 = substr ($codeValue, 0, 2);
                $tempStr2 = substr ($codeValue, 2, 2);
                $rotatedStr = $tempStr2 . $tempStr1;
                $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                if($bpCode > 0)
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'],
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            //virtual bp
            else if($binParam['type'] == 3)
            {
                $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                if(!($bpCode > 0))
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'] + 5000,
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            //virtual bp
            else if($binParam['type'] == 4)//rotation bytes in word
            {
                $tempStr1 = substr ($codeValue, 0, 2);
                $tempStr2 = substr ($codeValue, 2, 2);
                $rotatedStr = $tempStr2 . $tempStr1;
                $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
                if(!($bpCode > 0))
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'] + 5000,
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            else if($binParam['type'] == 5)
            {
                $bpCode = (hexdec($codeValue) & $binParam['basis']);//decbin
                if($bpCode == $binParam['mask'])
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'],
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            else if($binParam['type'] == 6)
            {
                $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin
                if(!($bpCode > 0))
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'],
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
            else if($binParam['type'] == 7)
            {
                $bpCode = (hexdec($codeValue) & $binParam['mask']);//decbin ( virtual type  6)
                if(($bpCode > 0))
                {
                    $param = array("frameNum" => $frameNum,
                            "channel" => $binParam['channel'] + 5000,
                            "mask" => $binParam['mask']);
                    array_push($phisicsBinaryParamsFrame, $param);
                }
            }
        }

        return $phisicsBinaryParamsFrame;
    }*/

    /*public function ConvertFrameToPhisict($extFrame, $extFrameLength, $extFrameNum, $cycloAp, $cycloBp, $extWordLength, $algHeap)
     {
    $wordLength = $extWordLength;

    $unpackedFrame = unpack("H*", $extFrame);
    $splitedFrame = str_split($unpackedFrame[1], $wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need
    $frameNum = $extFrameNum;
    $frameLength = $extFrameLength;
    $phisicsAnalogParamsFrame = array();
    $phisicsBinaryParamsFrame = array();
    $phisicsFrame = array();

    $paramCounter = 0;
    $paramCyclo = $cycloAp[$paramCounter];

    $binCounter = 0;
    $binParam = $cycloBp[$binCounter];

    $i = 0;

    while($i < $frameLength / $wordLength)
    {
    if($paramCyclo['channel'] == $i)
    {
    //get phisics analog param from code
    if($paramCyclo['type'] == 1)//type 1 uses for graduated params
    {
    $v = $paramCyclo;
    $apCode = (hexdec($splitedFrame[$i]) & $paramCyclo['mask']) >> $paramCyclo['shift'];
    $gradi = $v['xy'];

    for($j = 0; $j < count($gradi); $j++)
    {
    if($apCode <= $gradi[$j]['y'])
    {
    break;
    }
    }

    //faling extrapolation
    if($j == 0)
    {
    //exact match
    if($apCode == $gradi[$j]['y'])
    {
    $phisics = $gradi[$j]['x'];
    }
    else
    {
    $p = $apCode;
    $p0 = $gradi[0];
    $p1 = $gradi[1];

    if($p1['y'] - $p0['y'] == 0)
    {
    $phisics = 0;
    }
    else
    {
    $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
            ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
    }
    }
    }
    //rising extrapolation
    else if($j >= (count($gradi) - 1))
    {
    //exact match
    if($apCode == $gradi[count($gradi) - 1]['y'])
    {
    $phisics = $gradi[count($gradi) - 1]['x'];
    }
    else
    {
    $p = $apCode;
    $p0 = $gradi[count($gradi) - 2];
    $p1 = $gradi[count($gradi) - 1];

    if($p1['y'] - $p0['y'] == 0)
    {
    $phisics = 0;
    }
    else
    {
    $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
            ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
    }
    }

    }
    //interpolation
    else
    {
    //exact match
    if($apCode == $gradi[$j]['y'])
    {
    $phisics = $gradi[$j]['x'];
    }
    else
    {
    $p = $apCode;
    $p0 = $gradi[$j - 1];
    $p1 = $gradi[$j];

    if($p1['y'] - $p0['y'] == 0)
    {
    $phisics = 0;
    }
    else
    {
    $phisics = $p0['x'] + (($p1['x'] - $p0['x']) *
            ($p - $p0['y'])) / ($p1['y'] - $p0['y']);
    }
    }
    }

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 2)//unsigned params with coef
    {
    //$algHeap to store global temp values
    //$Ch = new Cache();
    //$algHeap = unserialize($Ch->retrieve('algHeap'));

    $alg = $paramCyclo['alg'];
    $alg = str_replace("[p]", "'" . $splitedFrame[$i] . "'", $alg);
    $alg = str_replace("[k]", $paramCyclo['k'], $alg);
    $alg = str_replace("[mask]", $paramCyclo['mask'], $alg);
    $alg = str_replace("[shift]", $paramCyclo['shift'], $alg);
    $alg = str_replace("[minus]", $paramCyclo['minus'], $alg);

    eval($alg);//$phisics must be assigned in alg

    //$Ch->store('algHeap', serialize($algHeap), $expiration = 1);
    //unset($Ch);

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    //3 not used yet
    else if($paramCyclo['type'] == 4)//unsigned params with coef
    {
    $apCode = (hexdec($splitedFrame[$i]) & $paramCyclo['mask']) >> $paramCyclo['shift'];
    $phisics = $apCode * $paramCyclo['k'];

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 5)//signed params with coef
    {
    $apCode = (hexdec($splitedFrame[$i]) & $paramCyclo['mask']) >> ($paramCyclo['shift'] + 1);
    $minus = (hexdec($splitedFrame[$i]) & $paramCyclo['mask']) >> ($paramCyclo['shift']);
    if($minus > $paramCyclo['mask'] / 2)
    {
    $apCode = $apCode - $paramCyclo['mask'];
    }
    $phisics = $apCode * $paramCyclo['k'];

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 6)//unsigned params with coef with rotation bytes in word
    {
    $tempStr1 = substr ($splitedFrame[$i], 0, 2);//because 2 hex  digits in byte
    $tempStr2 = substr ($splitedFrame[$i], 2, 2);
    $rotatedStr = $tempStr2 . $tempStr1;

    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
    $phisics = $apCode * $paramCyclo['k'];

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 7)//using field minus to find negative values with rotation bytes in word
    {
    $tempStr1 = substr ($splitedFrame[$i], 0, 2);
    $tempStr2 = substr ($splitedFrame[$i], 2, 2);
    $rotatedStr = $tempStr2 . $tempStr1;
    $apCode = (hexdec($rotatedStr) & $paramCyclo['mask']) >> $paramCyclo['shift'];
    if($apCode >= $paramCyclo['minus'])
    {
    $apCode -= $paramCyclo['minus'] * 2;
    }
    $phisics = $apCode * $paramCyclo['k'];
    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 8)//signed params with coef with negative values
    {
    $apCode = (hexdec($splitedFrame[$i]));
    if($apCode > 32768)
    {
    $apCode -= 65535;
    }
    $phisics = $apCode * $paramCyclo['k'];

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 9)//signed params with coef with gradual rotation
    {
    $apCode = (hexdec($splitedFrame[$i]));
    if($apCode > 32768)
    {
    $apCode -= 65535;
    }
    $phisics = $apCode * $paramCyclo['k'];
    if($phisics < 0)
    {
    $phisics += 360;
    }

    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }
    else if($paramCyclo['type'] == 10)//using field minus to find negative values
    {
    $apCode = (hexdec($splitedFrame[$i]) & $paramCyclo['mask']) >> $paramCyclo['shift'];
    if($apCode >= $paramCyclo['minus'])
    {
    $apCode -= $paramCyclo['minus'] * 2;
    }
    $phisics = $apCode * $paramCyclo['k'];
    $param = array("frameNum" => $frameNum, "channel" => $paramCyclo['channel'], "value" => $phisics);
    array_push($phisicsAnalogParamsFrame, $param);
    }

    $paramCounter++;
    if(count($cycloAp) > $paramCounter)
    {
    $paramCyclo = $cycloAp[$paramCounter];
    }
    }

    //get binary param from this code
    while($binParam['channel'] == $i)
    {
    if($binParam['type'] == 1)
    {
    $bpCode = (hexdec($splitedFrame[$i]) & $binParam['mask']);//decbin
    if($bpCode > 0)
    {
    $param = array("frameNum" => $frameNum,
            "channel" => $binParam['channel'],
            "mask" => $binParam['mask']);
    array_push($phisicsBinaryParamsFrame, $param);
    }
    }
    else if($binParam['type'] == 2)//rotation bytes in word
    {
    $tempStr1 = substr ($splitedFrame[$i], 0, 2);
    $tempStr2 = substr ($splitedFrame[$i], 2, 2);
    $rotatedStr = $tempStr2 . $tempStr1;
    $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
    if($bpCode > 0)
    {
    $param = array("frameNum" => $frameNum,
            "channel" => $binParam['channel'],
            "mask" => $binParam['mask']);
    array_push($phisicsBinaryParamsFrame, $param);
    }
    }
    if($binParam['type'] == 3)
    {
    $bpCode = (hexdec($splitedFrame[$i]) & $binParam['mask']);//decbin
    if(!($bpCode > 0))
    {
    $param = array("frameNum" => $frameNum,
            "channel" => $binParam['channel'] + 5000,
            "mask" => $binParam['mask']);
    array_push($phisicsBinaryParamsFrame, $param);
    }
    }
    else if($binParam['type'] == 4)//rotation bytes in word
    {
    $tempStr1 = substr ($splitedFrame[$i], 0, 2);
    $tempStr2 = substr ($splitedFrame[$i], 2, 2);
    $rotatedStr = $tempStr2 . $tempStr1;
    $bpCode = (hexdec($rotatedStr) & $binParam['mask']);//decbin
    if(!($bpCode > 0))
    {
    $param = array("frameNum" => $frameNum,
            "channel" => $binParam['channel'] + 5000,
            "mask" => $binParam['mask']);
    array_push($phisicsBinaryParamsFrame, $param);
    }
    }

    $binCounter++;
    if(count($cycloBp) > $binCounter)
    {
    $binParam = $cycloBp[$binCounter];
    }
    else
    {
    $binParam = false;
    }
    }

    $i++;
    }

    array_push($phisicsFrame, $phisicsAnalogParamsFrame);
    array_push($phisicsFrame, $phisicsBinaryParamsFrame);

    return $phisicsFrame;
    }*/

    public function DeleteFile($extFileName)
    {
        $fileName = $extFileName;
        unlink($fileName);
    }

    public function InsertApFrame($extPhisicsFrame, $extTableName, $prefix, $link)
    {
        $phisicsFrame = $extPhisicsFrame;
        $tableName = $extTableName;

        if($link == null)
        {
            $c = new DataBaseConnector();
            $link = $c->Connect();
        }

        $query = "INSERT INTO `".$tableName."_".$prefix."` VALUES ";
        $i = 0;
        while($i < count($phisicsFrame))
        {
            $query .= "(";
            $line = $phisicsFrame[$i];
            for($j = 0; $j < count($line); $j++)
            {
                $param = $line[$j];
                $query .= $param . ", ";
            }
            $query = substr($query, 0, -2);
            $query .= "), ";
            $i++;
        }

        $query = substr($query, 0, -2);
        $query .=";";
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        if($link == null)
        {
            $c->Disconnect();
        }

        unset($c);
    }

    public function InsertBpFrame($extBinFrame, $extTableName, $link)
    {
        $binFrame = $extBinFrame;
        $tableName = $extTableName;

        if($link == null)
        {
            $c = new DataBaseConnector();
            $link = $c->Connect();
        }

        $query = "INSERT INTO `".$tableName."` (frameNum, channel, mask) VALUES ";
        $i = 0;

        while($i < count($binFrame))
        {
            $param = $binFrame[$i];
            $query .="(".$param['frameNum'].", ".$param['channel'].", ".$param['mask']."),";
            $i++;
        }

        $query = substr($query, 0, -1);
        $stmt = $link->prepare($query);
        $stmt->execute();

        $stmt->close();

        if($link == null)
        {
            $c->Disconnect();
        }

        unset($c);
    }

    public function AppendFrameToFile($extPhisicsFrameByPrefixes, $extFileDescArr)
    {
        $phisicsFramesByPrefixes = $extPhisicsFrameByPrefixes;
        $files = $extFileDescArr;

        foreach($phisicsFramesByPrefixes as $prefix => $phisicsFrames)
        {
            $curFile = $files[$prefix];
            for($i = 0; $i < count($phisicsFrames); $i++)
            {
                $line = $phisicsFrames[$i];
                $lineToWrite = implode(",", $line);
                fwrite($curFile, $lineToWrite. ";");
            }
        }
    }

    public function LoadFileToTable($extTableName, $extFileName)
    {
        $tableName = $extTableName;
        $file = $extFileName;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "LOAD DATA LOCAL INFILE '".$file."' INTO TABLE `".$tableName."` FIELDS TERMINATED BY ',' LINES TERMINATED BY ';';";
        $link->query($query);

        $c->Disconnect();

        unset($c);
    }

    public function GetFramesCount($extApTableName, $extPrefix)
    {
        $apTableName = $extApTableName;
        $prefix = $extPrefix;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT MAX(`frameNum`) FROM `".$apTableName."_". $prefix ."` LIMIT 1;";
        $result = $link->query($query);

        $row = $result->fetch_array();

        $framesCount = $row[0];

        $result->free();
        $c->Disconnect();

        unset($c);

        return $framesCount;
    }

    public function GetFlightFrame($extFlightId, $extFrameNum, $extCodesArray)
    {
        $flightId = $extFlightId;
        $frameNum = $extFrameNum;
        $codesArray = $extCodesArray;

        $flightInfo = $this->GetFlightInfo($flightId);
        $apTableName = $flightInfo['apTableName'];

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT `channel`, `value` FROM `".$apTableName."` WHERE `frameNum` = ".$frameNum." ORDER BY `channel` ASC;";
        $result = $link->query($query);

        $frame = array();

        while($row = $result->fetch_array())
        {
            $param = array("channel" => $row['channel'],
                    "code" => $codesArray[$row['channel']],
                    "value" => $row['value']);
            array_push($frame, $param);
        }

        $normFrame = array();

        $maxFreq = 16;

        for($a = 0; $a < count($frame); $a++)
        {
            $curParam = $frame[$a];
            $code = $curParam['code'];
            $channel = $curParam['channel'];
            $param = array("code" => $code, "value" => $curParam['value']);
            $preparedParam = array();
            array_push($preparedParam, $param);
            unset($frame[$a]);
            $frame = array_values($frame);
            $a--;
            for($b = 0; $b < count($frame);)
            {
                $tempParam = $frame[$b];
                if($code == $tempParam['code'])
                {
                    $param = array("code" => $code, "value" => $tempParam['value']);
                    array_push($preparedParam, $param);
                    unset($frame[$b]);
                    $frame = array_values($frame);
                }
                else
                {
                    $b++;
                }
            }

            for($i = 0; $i < count($preparedParam); $i++)
            {
                for($j = 0; $j < ($maxFreq / count($preparedParam)); $j++)
                {
                    array_push($normFrame, $preparedParam[$i]);
                }
            }


        }

        //        for($h = 0; $h < count($normFrame); $h++)
        //        {
        //            echo($normFrame[$h]['code']. " " . $normFrame[$h]['value']. "</br>");
        //        }

        $result->free();
        $c->Disconnect();

        unset($c);

        return $normFrame;
    }

    public function CreateTableNormalizedFrames($extAPheaders)
    {
        $bruType = $extBRUtype;
        $APheaders = $extAPheaders;
        $tableName = uniqid().time()."tmp";

        $c = new DataBaseConnector();

        $query = "CREATE TABLE ".$tableName." (id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY, T VARCHAR(255), ";
        for($i = 0; $i < count($APheaders); $i++)
        {
            $query .= "p".$i." VARCHAR(255),";
        }
        $query = substr($query, 0, -1);
        $query .=");";

        $link = $c->Connect();
        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $query = "INSERT INTO ".$tableName." (T,";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "p".$j.",";
        }

        $query = substr($query, 0, -1);
        $query .=") VALUES ('serv1',";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "'".$APheaders[$j]['code']."',";
        }

        $query = substr($query, 0, -1);
        $query .="), ('serv2',";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "'".$APheaders[$j]['name']."',";
        }

        $query = substr($query, 0, -1);
        $query .="), ('serv3',";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "'".$APheaders[$j]['dim']."',";
        }

        $query = substr($query, 0, -1);
        $query .="), ('serv4',";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "'".$APheaders[$j]['minValue']."',";
        }

        $query = substr($query, 0, -1);
        $query .="), ('serv5',";

        for($j = 0; $j < count($APheaders); $j++)
        {
            $query .= "'".$APheaders[$j]['maxValue']."',";
        }

        $query = substr($query, 0, -1);
        $query .=");";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);

        return $tableName;
    }

    public function InsertNormalizedFrame($extFrame, $extTableName, $extAPheaders, $extStepDivider, $extCurrFrameTime)
    {
        $frame = $extFrame;
        $tableName = $extTableName;
        $paramsCount = count($extAPheaders);
        $stepDivider = $extStepDivider;
        $currFrameTime = date_format($extCurrFrameTime, "H:i:s");

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "INSERT INTO ".$tableName." ( T, ";

        for($i = 0; $i < $paramsCount; $i++)
        {
            $query .= "p".$i.",";
        }
        $query = substr($query, 0, -1);
        $query .=") VALUES ";

        $k = 0;

        for($i = 0; $i < $stepDivider; $i++)
        {
            $query .= "('".$currFrameTime.".".round(100 / $stepDivider * $i, 0)."', ";

            for($j = $i; $j < count($frame); $j+=$stepDivider)
            {
                $param = $frame[$j];
                $query .= round($param['value'],2).",";
            }

            $query = substr($query, 0, -1);
            $query .="),";
        }

        $query = substr($query, 0, -1);
        $query .=";";

        $stmt = $link->prepare($query);
        $stmt->execute();
        $stmt->close();

        $c->Disconnect();

        unset($c);
    }

    public function GetNormalizedFrame($extTableName, $extId)
    {
        $tableName = $extTableName;
        $id = $extId;

        $c = new DataBaseConnector();
        $link = $c->Connect();

        $query = "SELECT * FROM ".$tableName." WHERE id = ".$id.";";

        $result = $link->query($query);

        $normFrame = array();
        while($row = $result->fetch_array())
        {
            for($i = 0; $i < count($row) / 2; $i++)
            {
                array_push($normFrame, $row[$i]);
            }
        }

        $c->Disconnect();

        unset($c);

        return $normFrame;
    }

    public function ShowFlightFrame($extFrame, $extStepDivider, $extCurrFrameTime)
    {
        $frame = $extFrame;
        $stepDivider = $extStepDivider;

        $currFrameTime = $extCurrFrameTime;
        $dateString = date_format($currFrameTime, "H:i:s");

        $cellCount = count($frame) / $stepDivider;
        $tableWidth = $cellCount * 180 + 180;

        printf("<table border=\"1\" width=\"%s px\">", $tableWidth);
        for($i = 0; $i < $stepDivider; $i++)
        {
            printf("<tr><td class=\"VievTableCell\" style=\"text-align:center; \">%s.%s</td>",
                    $dateString, round(100 / $stepDivider * $i, 0));

            for($j = $i; $j < count($frame); $j+=$stepDivider)
            {
                //echo($j."</br>");
                printf("<td class=\"VievTableCell\">%s</td>", round($frame[$j]['value'],3));
            }

            printf("</tr>");
        }

        printf("</table>");
    }

    public function GetFlightNormalizedFrame($extFlightId, $extCurrFrameId)
    {
        $flightId = $extFlightId;
        $currFrameId = $extCurrFrameId;

        $d = new DataTransitionProvider();
        $flightInfo = $d->GetFlightInfo($flightId);
        $startCopyTime = $flightInfo['startCopyTime'];
        $bruType = $flightInfo['bruType'];
        $bruInfo = $d->GetBRUinfo($bruType);
        $stepLength = $bruInfo['stepLength'];
        $stepDivider = $bruInfo['stepDivider'];
        $codesArray = $d->GetCodesArray($bruType);

        $frame = $d->GetFlightFrame($flightId, $currFrameId, $codesArray);

        unset($d);

        return $frame;
    }

    /*public function ShowTimeRangeSlider($extStartCopyTime, $extFrameCount, $extStepLength)
    {
        $startCopyTime = $extStartCopyTime;
        $frameCount = $extFrameCount;
        $stepLength = $extStepLength;

        //jQ script will provide range slider
        printf("
                <input id=\"startCopyTime\" type=\"hidden\" value=\"%s\" />
                <input id=\"frameCount\" type=\"hidden\" value=\"%s\" />
                <input id=\"stepLength\" type=\"hidden\" value=\"%s\" />
                <p>
                <label for=\"amount\">Price range:</label>
                <input type=\"text\" id=\"amount\" style=\"border: 0; color: #f6931f; font-weight: bold;\" />
                </p>

                <div id=\"slider-range\"></div>
                <input id=\"startFrame\" type=\"hidden\" value=\"0\" />
                <input id=\"endFrame\" type=\"hidden\" value=\"%s\" />", $startCopyTime, $frameCount, $stepLength, $frameCount);
    }*/

    public function SearchSyncroWord($extFrameSyncroCode, $extOffset, $extFileName)
    {
        $frameSyncroCode = $extFrameSyncroCode;
        $offset = $extOffset;
        $fileName = $extFileName;

        $fileDesc = $this->OpenFile($fileName);
        $fileSize = $this->GetFileSize($fileName) - $offset;
        fseek($fileDesc, $offset, SEEK_SET);

        $syncroWordSeek = $offset;
        $frameSyncroCode = strtolower($frameSyncroCode);
        if($frameSyncroCode != '')
        {
            if(substr($frameSyncroCode, -1) == '*')
            {
                $updatedSyncroCode = substr($frameSyncroCode, 0, -1);
                $syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte

                $word = fread($fileDesc, $syncroCodeLength);
                $word = unpack("H*", $word);
                $preparedWord = $word[1];

                $syncroWordSeek += $syncroCodeLength;
                do
                {
                    $byte = unpack("H*", fread($fileDesc, 1));
                    $byte = $byte[1];
                    $preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
                    $syncroWordSeek++;

                    $proccesedSyncroCode = $updatedSyncroCode;
                    $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
                    $proccessedPreparedWordArr = str_split($preparedWord, 1);

                    while(in_array('x', $proccesedSyncroCodeArr))
                    {
                        $xPos = array_search('x', $proccesedSyncroCodeArr);
                        $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
                    }

                    $proccesedSyncroCode = implode($proccesedSyncroCodeArr);
                }
                while(($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));
            }
            else
            {
                $updatedSyncroCode = $frameSyncroCode;
                $syncroCodeLength = strlen($updatedSyncroCode) / 2; // because 2 symb in byte

                $word = fread($fileDesc, $syncroCodeLength);
                $word = unpack("H*", $word);
                $preparedWord = $word[1];

                $syncroWordSeek += $syncroCodeLength;
                do
                {
                    $byte = unpack("H*", fread($fileDesc, 1));
                    $byte = $byte[1];
                    $preparedWord = substr($preparedWord, 2, strlen($preparedWord) - 2) . $byte; // add to str one more byte
                    $syncroWordSeek++;

                    $proccesedSyncroCode = $updatedSyncroCode;
                    $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
                    $proccessedPreparedWordArr = str_split($preparedWord, 1);

                    while(in_array('x', $proccesedSyncroCodeArr))
                    {
                        $xPos = array_search('x', $proccesedSyncroCodeArr);
                        $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
                    }

                    $proccesedSyncroCode = implode($proccesedSyncroCodeArr);
                }
                while(($preparedWord != $proccesedSyncroCode) && ($syncroWordSeek < $fileSize));

                $syncroWordSeek -= $syncroCodeLength;
            }
        }

        $this->CloseFile($fileDesc);

        return $syncroWordSeek;
    }

    public function CheckSyncroWord($extFrameSyncroCode, $extUnpackedFrame)
    {
        $frameSyncroCode = $extFrameSyncroCode;
        $unpackedFrame = $extUnpackedFrame;

        $syncroWordFound = false;
        if($frameSyncroCode != '')
        {
            $frameSyncroCode = strtolower($frameSyncroCode);

            if(substr($frameSyncroCode, -1) == '*')
            {
                $updatedSyncroCode = substr($frameSyncroCode, 0, -1);
                $syncroCodeLength = strlen($updatedSyncroCode);
                $suggestedSyncroWord = substr($unpackedFrame, strlen($unpackedFrame) - $syncroCodeLength, $syncroCodeLength);

                $proccesedSyncroCode = $updatedSyncroCode;
                $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
                $proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

                while(in_array('x', $proccesedSyncroCodeArr))
                {
                    $xPos = array_search('x', $proccesedSyncroCodeArr);
                    $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
                }

                $proccesedSyncroCode = implode($proccesedSyncroCodeArr);

                if($suggestedSyncroWord == $proccesedSyncroCode)
                {
                    $syncroWordFound = true;
                }
            }
            else
            {
                $updatedSyncroCode = $frameSyncroCode;
                $syncroCodeLength = strlen($updatedSyncroCode);
                $suggestedSyncroWord = substr($unpackedFrame, 0, $syncroCodeLength);

                $proccesedSyncroCode = $updatedSyncroCode;
                $proccesedSyncroCodeArr = str_split($proccesedSyncroCode, 1);
                $proccessedPreparedWordArr = str_split($suggestedSyncroWord, 1);

                while(in_array('x', $proccesedSyncroCodeArr))
                {
                    $xPos = array_search('x', $proccesedSyncroCodeArr);
                    $proccesedSyncroCodeArr[$xPos] = $proccessedPreparedWordArr[$xPos];
                }

                $proccesedSyncroCode = implode($proccesedSyncroCodeArr);

                if($suggestedSyncroWord == $proccesedSyncroCode)
                {
                    $syncroWordFound = true;
                }
            }
        }
        else
        {
            $syncroWordFound = true;
        }

        return $syncroWordFound;
    }

    public function FrameNumToTime($extFrameNum, $extStepLength, $extStartCopyTime)
    {
        $frameNum = $extFrameNum;
        $stepLength = $extStepLength;
        $startCopyTime = $extStartCopyTime;

        $dateInterval = $frameNum * $stepLength;
        $currTime = date("H:i:s", $startCopyTime + $dateInterval);
        return $currTime;

    }

    public function FrameCountToDuration($extFramesCount, $extStepLength)
    {
        $framesCount = $extFramesCount;
        $stepLength =  $extStepLength;

        $timeInterval = $framesCount * $stepLength;

        $hours = floor($timeInterval / (60*60));
        $mins = floor(($timeInterval - $hours * 60*60) / 60);
        $secs = floor(($timeInterval - $hours * 60*60 - $mins * 60));

        if(strlen($hours) < 2)
        {
            $hours = "0".$hours;
        }
        if(strlen($mins) < 2)
        {
            $mins = "0".$mins;
        }
        if(strlen($secs) < 2)
        {
            $secs = "0".$secs;
        }
        $duration = $hours .":".$mins.":".$secs;
        return $duration;

    }

    public function TimeStampToDuration($extMicrosecsCount)
    {
        $microsecsCount = $extMicrosecsCount;

        if($microsecsCount > 1000)
        {
            $timeInterval = $microsecsCount / 1000;

            $hours = floor($timeInterval / (60*60));
            $mins = floor(($timeInterval - $hours * 60*60) / 60);
            $secs = floor(($timeInterval - $hours * 60*60 - $mins * 60));

            if(strlen($hours) < 2)
            {
                $hours = "0".$hours;
            }
            if(strlen($mins) < 2)
            {
                $mins = "0".$mins;
            }
            if(strlen($secs) < 2)
            {
                $secs = "0".$secs;
            }
            $duration = $hours .":".$mins.":".$secs;
            return $duration;
        }
        else
        {
            return (float)($microsecsCount / 1000);
        }
    }
}
