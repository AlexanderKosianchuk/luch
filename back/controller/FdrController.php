<?php

namespace Controller;

use Framework\Application as App;

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
            App::dic()
                ->get('FdrComponent')
                ->getFdrs()
        );
    }

    public function ShowParamList($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new BadRequestException(json_encode($args));
        }

        $fdr = new Fdr;
        $flightApHeaders = $fdr->GetBruApHeaders($fdrId);
        $flightBpHeaders= $fdr->GetBruBpHeaders($fdrId);
        unset($fdr);

        $paramList = sprintf ("<div class='BruTypeTemplatesParamsListContainer'>");
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

    private function generateRandomString($length = 10) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }
        return $randomString;
    }


    public function getCyclo($args)
    {
        if (!isset($args['fdrId'])
            && !isset($args['flightId'])
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

        $fdr = new Fdr;
        $flightApHeaders = $fdr->GetBruApHeaders($fdrId);
        $flightBpHeaders= $fdr->GetBruBpHeaders($fdrId);
        unset($fdr);

        return json_encode([
            'fdrId' => $fdrId,
            'analogParams' => $flightApHeaders,
            'binaryParams' => $flightBpHeaders
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
