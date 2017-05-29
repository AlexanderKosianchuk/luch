<?php

namespace Controller;

use Model\Language;
use Model\PSTempl;
use Model\Channel;
use Model\Fdr;
use Model\Flight;

use Component\EntityManagerComponent as EM;
use Component\FdrComponent;

use \Exception;

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

    public function ShowParamList($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Integer is required. Passed: "
                . json_encode($fdrId), 1);
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
                'workspace' => $workspace,
            ]
        ];

        echo json_encode($answ);
    }

    public function getFdrTypes($args)
    {
        $userId = intval($this->_user->userInfo['id']);
        $fdrsAndCalibrations = FdrComponent::getAvaliableFdrs($userId);

        echo json_encode($fdrsAndCalibrations);
    }

}
