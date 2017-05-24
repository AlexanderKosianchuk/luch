<?php

namespace Controller;

use Model\Language;
use Model\Flight;
use Model\Fdr;
use Model\Frame;
use Model\PSTempl;
use Model\FlightException;
use Model\FlightComments;
use Model\Channel;
use Model\User;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Component\EntityManagerComponent as EM;
use Component\RealConnectionFactory as LinkFactory;

use Exception;

class TemplatesController extends CController
{
    public $curPage = 'viewOptionsPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language;
        $this->lang = $L->GetLanguage($this->curPage);
        unset($L);
    }

    private function CreateTemplate($flightId, $params, $tplName)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);

        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $PSTTableName = $fdrInfo['paramSetTemplateListTableName'];

        $paramsWithType = array();
        $Ch = new Channel;

        for($i = 0; $i < count($params); $i++) {
            if (!isset($params[$i]['id'])) {
                continue;
            }

            $paramId = $params[$i]['id'];
            $paramType = $params[$i]['type'];
            $paramInfo = [];
            if ($paramType === 'a') {
                $paramInfo = $fdr->GetParamInfoById($cycloApTableName, $paramId);
                $paramInfo['paramType'] = PARAM_TYPE_AP;
            } else if ($paramType === 'b') {
                $paramInfo = $fdr->GetParamInfoById($cycloBpTableName, $paramId);
                $paramInfo['paramType'] = PARAM_TYPE_BP;
            }

            if (count($paramInfo) === 0) {
                continue;
            }

            if (isset($paramInfo['paramType'])
                && ($paramInfo['paramType'] == PARAM_TYPE_AP)
            ) {
                $apTableNameWithPrefix = $apTableName . "_" . $paramInfo['prefix'];
                $paramMinMax = $Ch->GetParamMinMax($apTableNameWithPrefix,
                $paramInfo['code'], $this->_user->username);

                $paramsWithType[PARAM_TYPE_AP][] = [
                    'code' => $paramInfo['code'],
                    'min' => $paramMinMax['min'],
                    'max' => $paramMinMax['max']
                ];
            } else if(isset($paramInfo['paramType'])
                && ($paramInfo['paramType'] == PARAM_TYPE_BP)
            ) {
                $paramsWithType[PARAM_TYPE_BP][] = [
                    'code' => $paramInfo['code']
                ];
            }
        }
        unset($fdr);

        $PSTempl = new PSTempl;
        $PSTempl->DeleteTemplate($PSTTableName, $tplName, $this->_user->username);
        $PSTempl->CreateTplWithDistributedParams($PSTTableName, $tplName, $paramsWithType, $this->_user->username);

        unset($Ch);
        unset($PSTempl);
    }

    public function ShowTempltList($flightId)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        $apTableName = $flightInfo['apTableName'];
        $bpTableName = $flightInfo['bpTableName'];
        $exTableName = $flightInfo['exTableName'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $paramSetTemplateListTableName = $fdrInfo['paramSetTemplateListTableName'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $stepLength = $fdrInfo['stepLength'];
        $prefixArr = $fdr->GetBruApCycloPrefixes($fdrId);

        $Frame = new Frame;
        $framesCount = $Frame->GetFramesCount($apTableName, $prefixArr[0]); //giving just some prefix
        unset($Frame);

        $PSTempl = new PSTempl;
        //if no template table - create it
        $PSTTableName = $paramSetTemplateListTableName;
        if ($PSTTableName == "") {
            $dummy = substr($cycloApTableName, 0, -3);
            $paramSetTemplateListTableName = $dummy . "_pst";
            $PSTTableName = $paramSetTemplateListTableName;
            $PSTempl->CreatePSTTable($PSTTableName);
            $PSTempl->AddPSTTable($fdrId, $PSTTableName);
        }

        //if isset excListTable create list to add template
        $excEventsParamsList = array();
        if($exTableName != "") {
            $FEx = new FlightException;
            $excEventsList = $FEx->GetFlightEventsParamsList($exTableName);
            unset($FEx);
        }

        $flightTplsStr = "<select id='tplList' size='10' class='TplListSelect' multiple>";

        //here builds template options list
        $flightTplsStr .= $this->BuildTplOptionList($paramSetTemplateListTableName, $fdrId);

        $foundedEventsTplName = $this->lang->foundedEventsTplName;

        //if performed exception search and isset events
        if(!(empty($excEventsList)))
        {
            $params    = "";
            $paramsToAdd = array();
            for($i = 0; $i < count($excEventsList); $i++)
            {
                $params .= $excEventsList[$i] . ", ";
                $paramsToAdd[] = $excEventsList[$i];
            }
            $params = substr($params, 0, -2);

            $paramNamesStr = $fdr->GetParamNames($fdrId, $paramsToAdd);

            $flightTplsStr .= "<option id='tplOption' " .
                    "name='".EVENTS_TPL_NAME."'  " .
                    "data-comment='".$paramNamesStr."'  " .
                    "data-params='".$params."'  " .
                    "data-defaulttpl='true'  " .
                    "selected> " .
                    $foundedEventsTplName . " - ".$params."</option>";

            $this->CreateTemplate($flightId, $paramsToAdd, EVENTS_TPL_NAME);
        }

        unset($fdr);
        unset($PSTempl);

        $flightTplsStr .= "</select><br><br>
            <textarea id='tplComment' class='TplListTextareaComment'
                rows='10' readonly/></textarea>";

        return $flightTplsStr;
    }

    private function BuildTplOptionList($paramSetTemplateListTableName, $fdrId)
    {
        $fdrId = intval($fdrId);

        $PSTempl = new PSTempl;
        $PSTList = $PSTempl->GetPSTList($paramSetTemplateListTableName, $this->_user->username);
        $defaultPSTName = $PSTempl->GetDefaultPST($paramSetTemplateListTableName, $this->_user->username);
        unset($PSTempl);

        $optionsStr = "";

        $fdr = new Fdr;
        for($i = 0; $i < count($PSTList); $i++) {
            $PSTRow = $PSTList[$i];
            $paramsArr = $PSTRow[1];
            $params = implode(", ", $paramsArr);

            $paramNamesStr = $fdr->GetParamNames($fdrId, $paramsArr);

            if($PSTRow[0] == $defaultPSTName) {
                $optionsStr .= "<option id='tplOption' " .
                        "name='".$PSTRow[0]."'  " .
                        "title='".$params."' " .
                        "data-comment='".$paramNamesStr."'  " .
                        "data-params='".$params."'  " .
                        "data-defaulttpl='true'  " .
                        "selected> " .
                        "(".$this->lang->defaultTpl.") " . $PSTRow[0] . " - ".$params."</option>";
            } else if($PSTRow[0] == PARAMS_TPL_NAME) {
                $optionsStr .= "<option id='tplOption' " .
                        "name='".$PSTRow[0]."'  " .
                        "title='".$params."' " .
                        "data-comment='".$paramNamesStr."'  " .
                        "data-params='".$params."'  " .
                        "data-defaulttpl='true'  " .
                        "selected> " .
                        $this->lang->lastTpl." - ".$params."</option>";
            } else {
                if($PSTRow[0] != EVENTS_TPL_NAME) {
                    $optionsStr .= "<option id='tplOption' " .
                        "name='".$PSTRow[0]."'  " .
                        "title='".$params."' " .
                        "data-comment='".$paramNamesStr."'  " .
                        "data-params='".$params."'  " .
                        "data-defaulttpl='true'  " .
                        "selected> " .
                        $PSTRow[0] . " - ".$params."</option>";
                }
            }
        }
        unset($fdr);

        return $optionsStr;
    }

    public function GetDefaultTplParams($extFlightId)
    {
        $flightId = $extFlightId;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $paramSetTemplateListTableName = $fdrInfo['paramSetTemplateListTableName'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        $PSTempl = new PSTempl;
        $params = $PSTempl->GetDefaultTemplateParams($paramSetTemplateListTableName, $this->_user->username);
        unset($PSTempl);

        $apParams = array();
        $bpParams = array();
        foreach($params as $paramCode) {
            $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);
            if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
                $apParams[] = $paramInfo['code'];
            } else if($paramInfo["paramType"] == PARAM_TYPE_BP) {
                $bpParams[] = $paramInfo['code'];
            }
        }

        unset($fdr);
        return array(
            'ap' => $apParams,
            'bp' => $bpParams);
    }

    public function GetTplParamCodes($flightId, $tplName)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $paramSetTemplateListTableName = $fdrInfo['paramSetTemplateListTableName'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        $PSTempl = new PSTempl;
        $params = $PSTempl->GetPSTByName($paramSetTemplateListTableName, $tplName, $this->_user->username);
        unset($PSTempl);

        $apParams = array();
        $bpParams = array();
        foreach ($params as $paramCode) {
            $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);
            if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
                $apParams[] = $paramInfo['code'];
            } else if($paramInfo["paramType"] == PARAM_TYPE_BP) {
                $bpParams[] = $paramInfo['code'];
            }
        }

        unset($fdr);
        return [
            'ap' => $apParams,
            'bp' => $bpParams
        ];
    }

    public function getDefaultTemplateParamCodes($data)
    {
        if(isset($data['flightId'])) {
            $flightId = intval($data['flightId']);

            $params = $this->GetDefaultTplParams($flightId);

            $data = array(
                    'ap' => $params['ap'],
                    'bp' => $params['bp']
            );
            $answ["status"] = "ok";
            $answ["data"] = $data;

            echo json_encode($answ);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
        }
    }

    public function getTemplates($data)
    {
        if (!isset($data['flightId'])) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
            exit;
        }

        $flightId = intval($data['flightId']);
        $fdrTpls = $this->ShowTempltList($flightId);

        $data = array(
            'bruTypeTpls' => $fdrTpls
        );
        $answ["status"] = "ok";
        $answ["data"] = $data;

        echo json_encode($answ);
    }

    public function setTemplate($data)
    {
        if(!isset($data['flightId'])
            || !isset($data['templateName'])
            || !isset($data['analogParams'])
            || !isset($data['binaryParams'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page TemplatesController.php";
            echo(json_encode($answ));
        }

        $flightId = intval($data['flightId']);
        $templateName = $data['templateName'];
        $analogParams = $data['analogParams'];
        $binaryParams = $data['binaryParams'];

        $this->CreateTemplate($flightId, array_merge($analogParams, $binaryParams), $templateName);

        echo json_encode(['status' => 'ok']);
    }

    public function getTemplate($args)
    {
        if (!isset($args['flightId'])
            || !isset($args['templateName'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page TemplatesController.php";
            echo(json_encode($answ));
        }

        $flightId = intval($args['flightId']);
        $templateName = $args['templateName'];

        $params = $this->GetTplParamCodes($flightId, $templateName);

        echo json_encode([
            'a' => $params['ap'],
            'b' => $params['bp']
        ]);
    }
}
