<?php

namespace Controller;

use Entity\FdrTemplate;
use Entity\FlightEvent;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;

class TemplatesController extends BaseController
{
    public function getFlightTemplatesAction($flightId)
    {
        $flightId = intval($flightId);
        $userId = $this->user()->getId();

        $flight = $this->em()->find('Entity\Flight', $flightId);
        $fdr = $flight->getFdr();

        $isExist = $this->connection()->isExist($fdr->getCode().FdrTemplate::getPrefix());

        if (!$isExist) {
            $this->dic()->get('fdrTemplate')->createFdrTemplateTable($fdr->getCode());
        }

        $templatesToSend = $this->dic()->get('fdrTemplate')->getTemplates($flightId, true);
        $flightEvents = $this->dic()->get('event')->getFlightEvents($flightId);

        //if performed exception search and isset events
        if (count($flightEvents) > 0) {
            $codesArr = [];
            $params = [];
            foreach ($flightEvents as $event) {
                $paramDesc = $this->dic()->get('fdr')
                    ->getParamByCode(
                        $fdr->getId(),
                        $event['refParam']
                    );

                if (!empty($paramDesc) && !in_array($event['refParam'], $codesArr)) {
                    $codesArr[] = $event['refParam'];
                    $params[] = $paramDesc;
                }
            }

            $this->dic()->get('fdrTemplate')->create(
                $fdr->getCode(),
                $this->dic()->get('fdrTemplate')::getEventsName(),
                $params
            );

            $templatesToSend[] = [
                'name' =>  $this->dic()->get('fdrTemplate')::getEventsName(),
                'paramCodes' => $codesArr,
                'params' => $params,
                'servicePurpose' => [
                    'isEvents' => true
                ]
            ];
        }

        return json_encode($templatesToSend);
    }

    public function getTemplateAction($flightId, $templateName)
    {
        $flightId = intval($flightId);

        $flight = $this->em()->find('Entity\Flight', $flightId);

        if (!$flight) {
            throw new NotFoundException('fligth id: '.$flightId);
        }

        $fdrTemplateParams = $this->dic()->get('fdrTemplate')->getTemplateByName(
            $flight->getFdr()->getCode(),
            $templateName
        );

        $analogParams = [];
        $binaryParams = [];
        foreach ($fdrTemplateParams as $templateParam) {
            $param = $this->dic()->get('fdr')->getParamByCode(
                $flight->getFdrId(),
                $templateParam->getParamCode()
            );

            if ($param['type'] === $this->dic()->get('fdr')->getApType()) {
                $analogParams[] = $param;
            }

            if ($param['type'] === $this->dic()->get('fdr')->getBpType()) {
                $binaryParams[] = $param;
            }
        }

        return json_encode([
            'name' => $templateName,
            'ap' => $analogParams,
            'bp' => $binaryParams,
            'servisePurpose' => (($this->dic()->get('fdrTemplate')->isDefault($templateName))
                ? ['isDefault' => true]
                : false
            )
        ]);
    }

    public function CreateTemplate($flightId, $params, $tplName)
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
        if (!isset($data['flightId'])) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);

        $params = $this->GetDefaultTplParams($flightId);

        $data = array(
                'ap' => $params['ap'],
                'bp' => $params['bp']
        );
        $answ["status"] = "ok";
        $answ["data"] = $data;

        return json_encode($answ);
    }

    public function setTemplate($data)
    {
        if (!isset($data['flightId'])
            || !isset($data['templateName'])
            || !isset($data['analogParams'])
            || !isset($data['binaryParams'])
        ) {
            throw new BadRequestException(json_encode($data));
        }

        $flightId = intval($data['flightId']);
        $templateName = $data['templateName'];
        $analogParams = $data['analogParams'];
        $binaryParams = $data['binaryParams'];


        $this->CreateTemplate($flightId, array_merge($analogParams, $binaryParams), $templateName);

        return json_encode('ok');
    }

    public function mergeTemplates($args)
    {
        if (!isset($args['flightId'])
            || !isset($args['resultTemplateName'])
            || !isset($args['templatesToMerge'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $flightId = intval($args['flightId']);
        $resultTemplateName = $args['resultTemplateName'];
        $templatesToMerge = json_decode(html_entity_decode($args['templatesToMerge']));
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

        return json_encode('ok');
    }

    public function removeTemplate($args)
    {
        if (!isset($args['flightId'])
            || !isset($args['templateName'])
        ) {
            throw new BadRequestException(json_encode($args));
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

        return json_encode('ok');
    }
}
