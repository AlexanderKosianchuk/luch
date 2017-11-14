<?php

namespace Controller;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use \Exception;

class FdrController extends BaseController
{
    public function getFdrsAction()
    {
        return json_encode(
            $this->dic()
                ->get('fdr')
                ->getFdrs()
        );
    }

    public function getCycloAction($flightId, $fdrId = null)
    {
        if ($fdrId === null) {
            $flight = $this->em()->find('Entity\Flight', $flightId);
            $fdrId = $flight->getFdrId();
            unset($Fl);
        }

        return json_encode([
            'fdrId' => $fdrId,
            'analogParams' => $this->dic()->get('fdr')->getParams($fdrId, true),
            'binaryParams' => $this->dic()->get('fdr')->getBinaryParams($fdrId, true)
        ]);
    }

    public function setParamColor($args)
    {
        if ((!isset($args['fdrId']) && !isset($args['flightId']))
            || !isset($args['paramCode'])
            || !isset($args['color'])
        ) {
            throw new BadRequestException(json_encode($args));
        }

        $fdrId = null;

        if (!isset($args['fdrId'])) {
            $flightId = intval($args['flightId']);

            $Fl = new Flight;
            $flightInfo = $Fl->GetFlightInfo($flightId);
            $fdrId = intval($flightInfo['id_fdr']);
            unset($Fl);
        } else {
            $fdrId = intval($args['fdrId']);
        }

        $paramCode = $args['paramCode'];
        $color = $args['color'];

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $cycloApTableName = $fdrInfo['gradiApTableName'];
        $cycloBpTableName = $fdrInfo['gradiBpTableName'];

        $paramInfo = $fdr->GetParamInfoByCode($cycloApTableName, $cycloBpTableName, $paramCode);

        if ($paramInfo["paramType"] == PARAM_TYPE_AP) {
            $fdr->UpdateParamColor($cycloApTableName, $paramCode, $color);
        } else if ($paramInfo["paramType"] == PARAM_TYPE_BP) {
            $fdr->UpdateParamColor($cycloBpTableName, $paramCode, $color);
        }

        unset($fdr);

        return json_encode('ok');
    }

}
