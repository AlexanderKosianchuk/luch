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

    public function setTemplateAction(
        $flightId,
        $templateName,
        $analogParams,
        $binaryParams = []
    ) {
        $flight = $this->em()->find('Entity\Flight', intval($flightId));

        if (!$flight) {
            throw new NotFoundException('fligth id: '.$flightId);
        }

        $paramsWithType = [];
        foreach ($analogParams as $item) {
            $param = $this->dic()
                ->get('fdr')
                ->getAnalogById(
                    $flight->getFdrId(),
                    intval($item['id'])
                );

            $table = $flight->getGuid().'_'.$this->dic()->get('fdr')->getApType().'_'.$param->getPrefix();
            $minMax = $this->dic()
                ->get('channel')
                ->getParamMinMax(
                    $table,
                    $param->getCode(),
                    $this->dic()->get('fdr')->getApType()
                );

            $paramsWithType[$this->dic()->get('fdr')->getApType()][] = [
                'code' => $param->getCode(),
                'min' => $minMax['min'],
                'max' => $minMax['max']
            ];
        }

        foreach ($binaryParams as $item) {
            $param = $this->dic()
                ->get('fdr')
                ->getBinaryById(
                    $flight->getFdrId(),
                    intval($item['id'])
                );

            $paramsWithType[$this->dic()->get('fdr')->getBpType()][] = [
                'code' => $param->getCode(),
                'min' => 0,
                'max' => 1
            ];
        }

        $link = $this->connection()->create('fdrs');
        $table = \Entity\FdrTemplate::getTable($link, $flight->getFdrCode());
        $this->connection()->destroy($link);

        //if no template table - create it
        if ($table === null) {
            $this->dic()
                ->get('fdrTemplate')
                ->createFdrTemplateTable($flight->getFdrCode());
        }

        $this->dic()
            ->get('fdrTemplate')
            ->delete(
                $flight->getFdrCode(),
                $templateName,
                $this->user()->getId()
        );

        $this->dic()
            ->get('fdrTemplate')
            ->createWithDistributedParams(
                $flight->getFdrCode(),
                $templateName,
                $paramsWithType
            );

        return json_encode('ok');
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
