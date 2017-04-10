<?php

namespace Controller;

use Model\Language;
use Model\PSTempl;
use Model\Channel;
use Model\Fdr;
use Model\Flight;

use Component\EntityManagerComponent as EM;
use Component\FdrComponent;

class FdrController extends CController
{
    public $curPage = 'bruTypesPage';

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        unset($L);
    }

    public function PutTopMenu()
    {
        $topMenu = "<div id='topMenuBruType' class='TopMenu'></div>";
        return $topMenu;
    }

    public function PutLeftMenu()
    {
        $leftMenu = sprintf("<div id='leftMenuBruType' class='LeftMenu'>");

        if(in_array($this->_user->bruTypesPrivilegeArr[0], $this->_user->privilege) ||
                in_array($this->_user->bruTypesPrivilegeArr[3], $this->_user->privilege))
        {
            $leftMenu .= "<div id='editBruTplsLeftMenuRow' class='LeftMenuRowOptions'>" .
                    "<img class='LeftMenuRowIcon' src='/front/stylesheets/basicImg/templates.png'></img>" .
                    " " . $this->lang->templates .
                    "</div>";
        }

        $leftMenu .= sprintf("</div>");

        return $leftMenu;
    }

    public function PutWorkspace()
    {
        //MainContainer
        $workspace = "<div id='bruTypeWorkspace' class='WorkSpace'></div>";

        return $workspace;
    }

    public function GetTplsList($fdrId)
    {
        $tplsListWithControlButtns = '';

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfoById($fdrId);
        $bruType = $fdrInfo['name'];
        $paramSetTemplateListTableName = $fdrInfo['paramSetTemplateListTableName'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $stepLength = $fdrInfo['stepLength'];

        $prefixArr = $Bru->GetBruApCycloPrefixes($fdrId);
        unset($Bru);

        $PSTempl = new PSTempl;
        //if no template table - create it
        $PSTTableName = $paramSetTemplateListTableName;
        if($PSTTableName == "")
        {
            $dummy = substr($cycloApTableName, 0, -3);
            $paramSetTemplateListTableName = $dummy . "_pst";
            $PSTTableName = $paramSetTemplateListTableName;
            $PSTempl->CreatePSTTable($PSTTableName);
            $PSTempl->AddPSTTable($bruType, $PSTTableName);
        }

        //here builds template options list
        $tplsListWithControlButtns .= $this->BuildTplOptionList($paramSetTemplateListTableName, $bruType);

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

            $Bru = new Fdr;
            $paramNamesStr = $Bru->GetParamNames($bruType, $paramsToAdd);

            $tplsListWithControlButtns .= "<option id='tplOption' " .
                    "name='".EVENTS_TPL_NAME."'  " .
                            "data-comment='".$paramNamesStr."'  " .
                            "data-params='".$params."'  " .
                            "data-defaulttpl='true'  " .
                    "selected> " .
                            $foundedEventsTplName . " - ".$params."</option>";

            $this->CreateTemplate($flightId, $paramsToAdd, EVENTS_TPL_NAME);
        }

        unset($PSTempl);

        return $tplsListWithControlButtns;
    }

    private function BuildTplOptionList($extParamSetTemplateListTableName, $extBruType) {
        $bruType = $extBruType;
        $paramSetTemplateListTableName = $extParamSetTemplateListTableName;
        $PSTempl = new PSTempl;
        $PSTList = $PSTempl->GetPSTList ( $paramSetTemplateListTableName, $this->_user->username);
        $defaultPSTName = $PSTempl->GetDefaultPST($paramSetTemplateListTableName, $this->_user->username);
        unset ( $PSTempl );

        $optionsStr = "";

        $Bru = new Fdr;
        for($i = 0; $i < count ( $PSTList ); $i ++) {
            $PSTRow = $PSTList [$i];
            $paramsArr = $PSTRow [1];
            $params = implode ( ", ", $paramsArr );

            $paramNamesStr = $Bru->GetParamNames ( $bruType, $paramsArr );

            if ($PSTRow [0] == $defaultPSTName) {
                $optionsStr .= "<option id='tplOption' " .
                    "name='" . $PSTRow [0] . "'  " .
                    "title='" . $params . "' " .
                    "data-comment='" . $paramNamesStr . "'  " .
                    "data-params='" . $params . "'  " .
                    "data-defaulttpl='true'  " . "selected> " .
                    "(" . $this->lang->defaultTpl . ") " . $PSTRow [0] . " - " . $params . "</option>";
            } else {
                if (($PSTRow [0] != EVENTS_TPL_NAME) && ($PSTRow [0] != PARAMS_TPL_NAME)) {
                    $optionsStr .= "<option id='tplOption' " .
                        "name='" . $PSTRow [0] . "'  " .
                        "title='" . $params . "' " .
                        "data-comment='" . $paramNamesStr . "'  " .
                        "data-params='" . $params . "'  " .
                        "data-defaulttpl='true'  " .
                        "selected> " . $PSTRow [0] . " - " . $params . "</option>";
                }
            }
        }
        unset ( $Bru );

        return $optionsStr;
    }

    public function ShowParamList($extBruTypeId)
    {
        $bruTypeId = $extBruTypeId;

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfoById($bruTypeId);
        $bruType = $fdrInfo['name'];
        $flightApHeaders = $Bru->GetBruApHeaders($bruType);
        $flightBpHeaders= $Bru->GetBruBpHeaders($bruType);
        unset($Bru);

        $paramList = sprintf ("<div class='BruTypeTemplatesParamsListContainer is-scrollable'>");
        $paramList .= sprintf ("<div class='BruTypeTemplatesApList'>");

        for ($i = 0; $i < count($flightApHeaders); $i++)
        {
            $paramList .= sprintf ("
                <input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
                    data-colorpicker='false' readonly/>
                <label style='display:inline;'><input type='checkbox' class='ParamsCheckboxGroup' value='%s'/>
                %s, %s </label>
                </br>",
                    $flightApHeaders[$i]['color'],
                    $flightApHeaders[$i]['color'],
                    $flightApHeaders[$i]['code'],
                    $flightApHeaders[$i]['color'],
                    $flightApHeaders[$i]['code'],
                    $flightApHeaders[$i]['name'],
                    $flightApHeaders[$i]['code']);
        }

            $paramList .= sprintf ("</div><div class='BruTypeTemplatesBpList'>");

        for ($i = 0; $i < count($flightBpHeaders); $i++)
        {
            $paramList .= sprintf ("<input size='1' class='colorpicker-popup' style='background-color:#%s; color:#%s; display:inline;' data-paramcode='%s' value='%s'
                data-colorpicker='false' readonly/>
            <label style='display:inline;'>
            <input type='checkbox' class='ParamsCheckboxGroup' value='%s'/>
            %s, %s</label></br>",
                    $flightBpHeaders[$i]['color'],
                    $flightBpHeaders[$i]['color'],
                    $flightBpHeaders[$i]['code'],
                    $flightBpHeaders[$i]['color'],
                    $flightBpHeaders[$i]['code'],
                    $flightBpHeaders[$i]['name'],
                    $flightBpHeaders[$i]['code']);
        }

        $paramList .= sprintf("</div></div></div></br>");
        return $paramList;
    }

    public function CreateTemplate($extBruTypeId, $extTplName, $extParams)
    {
        $bruTypeId = $extBruTypeId;
        $tplName = $extTplName;
        $paramsToAdd = $extParams;

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfoById ($bruTypeId);
        $gradiApTableName = $fdrInfo ['gradiApTableName'];
        $gradiBpTableName = $fdrInfo ['gradiBpTableName'];
        $PSTTableName = $fdrInfo ['paramSetTemplateListTableName'];

        $paramsWithType = array ();
        $Ch = new Channel;

        for($i = 0; $i < count ( $paramsToAdd ); $i ++) {
            $paramInfo = $Bru->GetParamInfoByCode ( $gradiApTableName, $gradiBpTableName, $paramsToAdd [$i] );
            if ($paramInfo ['paramType'] == PARAM_TYPE_AP) {

                $paramsWithType [PARAM_TYPE_AP] [] = array (
                        'code' => $paramsToAdd [$i],
                        'min' => -1,
                        'max' => 1
                );
            } else if ($paramInfo ['paramType'] == PARAM_TYPE_BP) {
                $paramsWithType [PARAM_TYPE_BP] [] = array (
                        'code' => $paramsToAdd [$i]
                );
            }
        }
        unset ( $Bru );

        $PSTempl = new PSTempl;
        $PSTempl->DeleteTemplate ( $PSTTableName, $tplName, $this->_user->username);

        $apCount = count ( $paramsWithType [PARAM_TYPE_AP] );

        for($i = 0; $i < count ( $paramsWithType [PARAM_TYPE_AP] ); $i ++) {
            $paramCode = $paramsWithType [PARAM_TYPE_AP] [$i];
            $yMax = $paramsWithType [PARAM_TYPE_AP] [$i] ['max'];
            $yMin = $paramsWithType [PARAM_TYPE_AP] [$i] ['min'];
            $curCorridor = 0;

            if ($yMax > 0) {
                $curCorridor = ($yMax - $yMin);
            } else {
                $curCorridor = - ($yMin - $yMax);
            }

            $axisMax = $yMax + ($i * $curCorridor);
            $axisMin = $yMin - (($apCount - $i) * $curCorridor);

            $PSTempl->AddParamToTemplateWithMinMax ( $PSTTableName, $tplName, $paramCode ['code'], $axisMin, $axisMax, $this->_user->username);
        }

        if (isset ( $paramsWithType [PARAM_TYPE_BP] )) {
            $busyCorridor = (($apCount - 1) / $apCount * 100);
            $freeCorridor = 100 - $busyCorridor; // 100%

            $bpCount = count ( $paramsWithType [PARAM_TYPE_BP] );
            $curCorridor = $freeCorridor / $bpCount;
            $j = 0;

            for($i = $apCount; $i < $apCount + $bpCount; $i ++) {

                $axisMax = 100 - ($curCorridor * $j);
                $axisMin = 0 - ($curCorridor * $j);

                $PSTempl->AddParamToTemplateWithMinMax ( $PSTTableName, $tplName, $paramsWithType [PARAM_TYPE_BP] [$j] ['code'], $axisMin, $axisMax, $this->_user->username);
                $j ++;
            }
        }

        unset ( $Ch );
        unset ( $PSTempl );

        return "ok";
    }

    public function DeleteTemplate($extBruTypeId, $extTplName)
    {
        $bruTypeId = $extBruTypeId;
        $tplName = $extTplName;

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfoById ($bruTypeId);
        $PSTTableName = $fdrInfo ['paramSetTemplateListTableName'];
        unset ( $Bru );

        $PSTempl = new PSTempl;
        $PSTempl->DeleteTemplate ( $PSTTableName, $tplName, $this->_user->username);
        unset($PSTempl);

        return "ok";
    }

    public function SetDefaultTemplate($extBruTypeId, $extTplName)
    {
        $bruTypeId = $extBruTypeId;
        $tplName = $extTplName;

        $Bru = new Fdr;
        $fdrInfo = $Bru->GetBruInfoById ($bruTypeId);
        $PSTTableName = $fdrInfo ['paramSetTemplateListTableName'];
        unset ( $Bru );

        $PSTempl = new PSTempl;
        $PSTempl->SetDefaultTemplate($PSTTableName, $tplName, $this->_user->username);
        unset($PSTempl);

        return "ok";
    }

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */

    public function putBruTypeContainer($data)
    {
        $topMenu = $this->PutTopMenu();
        $leftMenu = $this->PutLeftMenu();
        $workspace = $this->PutWorkspace();
        $this->RegisterActionExecution($this->action, "executed");

        $answ = [
            'status' => 'ok',
            'data' => [
                'topMenu' => $topMenu,
                'leftMenu' => $leftMenu,
                'workspace' => $workspace,
            ]
        ];

        echo json_encode($answ);
    }

    public function editingBruTypeTemplatesReceiveTplsList($data)
    {
        if(isset($data['bruTypeId']))
        {
            $bruTypeId = $data['bruTypeId'];
            $tplsList = $this->GetTplsList($bruTypeId);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                    'status' => 'ok',
                    'data' => array(
                            'bruTypeTpls' => $tplsList
                    )
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function editingBruTypeTemplatesReceiveParamsList($data)
    {
        if(isset($data['bruTypeId'])) {
            $bruTypeId = $data['bruTypeId'];
            $paramsList = $this->ShowParamList($bruTypeId);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                'status' => 'ok',
                'data' => array(
                    'bruTypeParams' => $paramsList
                )
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function createTpl($data)
    {
        if(isset($data['bruTypeId']) &&
                    isset($data['name']) &&
                    isset($data['params']))
        {
            $bruTypeId = $data['bruTypeId'];
            $name = $data['name'];
            $params = $data['params'];

            $this->CreateTemplate($bruTypeId, $name, $params);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                    'status' => 'ok',
                    'data' => array()
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function deleteTpl($data)
    {
        if(isset($data['bruTypeId']) &&
                isset($data['name']))
        {
            $bruTypeId = $data['bruTypeId'];
            $name = $data['name'];

            $this->DeleteTemplate($bruTypeId, $name);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                    'status' => 'ok',
                    'data' => array()
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function defaultTpl($data)
    {
        if(isset($data['bruTypeId']) &&
                isset($data['name']))
        {
            $bruTypeId = $data['bruTypeId'];
            $name = $data['name'];

            $this->SetDefaultTemplate($bruTypeId, $name);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                    'status' => 'ok',
                    'data' => array()
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function updateTpl($data)
    {
        if(isset($data['bruTypeId']) &&
                isset($data['name']) &&
                isset($data['tplOldName']) &&
                isset($data['params']))
        {
            $bruTypeId = $data['bruTypeId'];
            $name = $data['name'];
            $tplOldName = $data['tplOldName'];
            $params = $data['params'];

            $this->DeleteTemplate($bruTypeId, $tplOldName);
            $this->CreateTemplate($bruTypeId, $name, $params);
            $this->RegisterActionExecution($this->action, "executed");

            $answ = array(
                    'status' => 'ok',
                    'data' => array()
            );

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function copyTemplate($data)
    {
        if(isset($data['flightId']) &&
                isset($data['tplName']))
        {
            $flightId = $data['flightId'];
            $tplName = $data['tplName'];

            $Fl = new Flight;
            $flightInfo = $Fl->GetFlightInfo($flightId);
            unset($Fl);

            $bruType = $flightInfo['bruType'];
            $Bru = new Fdr;
            $fdrInfo = $Bru->GetBruInfo ($bruType);
            $tableName = $fdrInfo ['paramSetTemplateListTableName'];
            unset ( $Bru );

            $newName = date('Y-m-d') . '_' . $this->_user->username . '_' . $this->generateRandomString(3);
            $username = $this->_user->username;
            $PSTempl = new PSTempl;
            $tpl = $PSTempl->getTemplate($tableName, $oldName, $username);
            $PSTempl->createTemplate($newName, $tpl, $tableName, $username);
            unset($PSTempl);

            $answ = 'ok';

            $this->RegisterActionExecution($this->action, "executed");
            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page FdrController.php";
            $this->RegisterActionReject($this->action, "rejected", 0, $answ["error"]);
            echo(json_encode($answ));
        }
    }

    public function getFdrTypes($args)
    {
        $userId = intval($this->_user->userInfo['id']);
        $fdrsAndCalibrations = FdrComponent::getAvaliableFdrs($userId);

        echo json_encode($fdrsAndCalibrations);
    }

}
