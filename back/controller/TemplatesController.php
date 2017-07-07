<?php

namespace Controller;

use Model\Language;
use Model\Flight;
use Model\Fdr;
use Model\Frame;
use Model\FlightTemplate;
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

        for ($i = 0; $i < count($params); $i++) {
            if (!isset($params[$i]['id'])) {
                continue;
            }

            $paramId = $params[$i]['id'];
            $paramType = $params[$i]['paramType'];
            $paramInfo = [];
            if ($paramType === PARAM_TYPE_AP) {
                $paramInfo = $fdr->GetParamInfoById($cycloApTableName, $paramId);
                $paramInfo['paramType'] = PARAM_TYPE_AP;
            } else if ($paramType === PARAM_TYPE_BP) {
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

        $flightTemplate = new FlightTemplate;
        $fdrCode = $fdrInfo['code'];
        $templatesTable = $fdrInfo['paramSetTemplateListTableName'];

        //if no template table - create it
        if ($templatesTable == "") {
            $templatesTable = $fdrCode . FlightTemplate::$TABLE_PREFIX;
            $flightTemplate->CreatePSTTable($templatesTable);
            $flightTemplate->AddPSTTable($fdrId, $templatesTable);
        }
        $flightTemplate->DeleteTemplate($PSTTableName, $tplName, $this->_user->username);
        $flightTemplate->CreateTplWithDistributedParams($PSTTableName, $tplName, $paramsWithType, $this->_user->username);

        unset($Ch);
        unset($flightTemplate);
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

        $flightTemplate = new FlightTemplate;
        $params = $flightTemplate->GetDefaultTemplateParams($paramSetTemplateListTableName, $this->_user->username);
        unset($flightTemplate);

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

        $flightTemplate = new FlightTemplate;
        $params = $flightTemplate->GetPSTByName($paramSetTemplateListTableName, $tplName, $this->_user->username);
        unset($flightTemplate);

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

    public function mergeTemplates($args)
    {
        if (!isset($args['flightId'])
            || !isset($args['resultTemplateName'])
            || !isset($args['templatesToMerge'])
        ) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page TemplatesController.php";
            echo(json_encode($answ));
        }

        $flightId = intval($args['flightId']);
        $resultTemplateName = $args['resultTemplateName'];
        $templatesToMerge = $args['templatesToMerge'];
        $username = $this->_user->username;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $tableName = $fdrInfo['paramSetTemplateListTableName'];

        $templatesParams = [];

        $flightTemplate = new FlightTemplate;
        foreach ($templatesToMerge as $templateName) {
            $params = $flightTemplate->GetPSTByName($tableName, $templateName, $username);

            for ($i = 0; $i < count($params); $i++) {
                $paramCode = $params[$i];
                $templatesParams[] = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);
            }
        }

        $this->CreateTemplate($flightId, $templatesParams, $resultTemplateName);

        echo json_encode(['status'=> 'ok']);
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

        $flight = new Flight;
        $flightInfo = $flight->getFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($flight);

        $templateName = $args['templateName'];

        $params = $this->GetTplParamCodes($flightId, $templateName);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $paramSetTemplateListTableName = $fdrInfo['paramSetTemplateListTableName'];

        $analogParams = [];
        $binaryParams = [];

        foreach ($params['ap'] as $code) {
            $analogParams[] = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code, PARAM_TYPE_AP);
        }

        foreach ($params['bp'] as $code) {
            $binaryParams[] = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code, PARAM_TYPE_BP);
        }

        unset($fdr);

        $user = $this->_user->userInfo['login'];
        $ft = new FlightTemplate;
        $defaultName = $ft->GetDefaultPST($paramSetTemplateListTableName, $user);
        unset($ft);

        echo json_encode([
            'name' => $templateName,
            'ap' => $analogParams,
            'bp' => $binaryParams,
            'servisePurpose' => (($defaultName === $templateName)
                ? ['isDefault' => true]
                : false
            )
        ]);
    }

    public function removeTemplate($args)
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
        $username = $this->_user->username;

        $flight = new Flight;
        $flightInfo = $flight->getFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($flight);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $templateTable = $fdrInfo['paramSetTemplateListTableName'];
        unset($fdr);

        $template = new FlightTemplate;
        $template->DeleteTemplate($templateTable, $templateName, $username);
        unset($template);

        echo json_encode(['status' => 'ok']);
    }

    public function getFlightTemplates($args)
    {
        if (!isset($args['flightId'])) {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page TemplatesController.php";
            echo(json_encode($answ));
        }

        $flightId = intval($args['flightId']);
        $userId = intval($this->_user->userInfo['id']);

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $fdrCode = $fdrInfo['code'];
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];
        $exTableName = $fdrCode . FlightException::$TABLE_PREFIX;
        $templatesTable = $fdrInfo['paramSetTemplateListTableName'];

        $flightTemplate = new FlightTemplate;
        //if no template table - create it
        if ($templatesTable == "") {
            $templatesTable = $fdrCode . FlightTemplate::$TABLE_PREFIX;
            $flightTemplate->CreatePSTTable($templatesTable);
            $flightTemplate->AddPSTTable($fdrId, $templatesTable);
        }

        //if isset excListTable create list to add template
        $excEventsList = array();
        if ($exTableName !== "") {
            $FEx = new FlightException;
            $excEventsList = $FEx->GetFlightEventsParamsList($exTableName);
            unset($FEx);
        }

        $templatesList = $flightTemplate->GetPSTList($templatesTable, $this->_user->username);
        $templatesToSend = [];

        for ($i = 0; $i < count($templatesList); $i++) {
            $template = $templatesList[$i];
            $templateName = $template['name'];
            $templateParamCodesArr = $template['params'];
            $isDefault = $template['isDefault'];
            $params = [];

            foreach ($templateParamCodesArr as $code) {
                $params[] =  $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code);
            }

            if ($templateName === FlightTemplate::$EVENTS_TPL_NAME) {
                continue;
            }

            if ($templateName === FlightTemplate::$LAST_TPL_NAME) {
                $templatesToSend[] = [
                    'name' => $templateName,
                    'paramCodes' => $templateParamCodesArr,
                    'params' => $params,
                    'servicePurpose' => [
                        'isLast' => true
                    ]
                ];
                continue;
            }

            if ($isDefault) {
                $templatesToSend[] = [
                    'name' => $templateName,
                    'paramCodes' => $templateParamCodesArr,
                    'params' => $params,
                    'servicePurpose' => [
                        'isDefault' => true
                    ]
                ];
                continue;
            }

            $templatesToSend[] = [
                'name' => $templateName,
                'paramCodes' => $templateParamCodesArr,
                'params' => $params
            ];
        }

        //if performed exception search and isset events
        if (!(empty($excEventsList))) {
            $templateParamCodesArr = [];
            for ($i = 0; $i < count($excEventsList); $i++) {
                $templateParamCodesArr[] = $excEventsList[$i];
            }

            $params = [];
            foreach ($templateParamCodesArr as $code) {
                $params[] =  $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $code);
            }

            $templatesToSend[] = [
                'name' => FlightTemplate::$EVENTS_TPL_NAME,
                'paramCodes' => $templateParamCodesArr,
                'params' => $params,
                'servicePurpose' => [
                    'isEvents' => true
                ]
            ];

            $this->CreateTemplate($flightId, $params, FlightTemplate::$EVENTS_TPL_NAME);
        }

        unset($fdr);
        unset($flightTemplate);

        echo json_encode($templatesToSend);
    }
}
