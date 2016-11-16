<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class SearchFlightController extends CController
{
    public $curPage = 'searchFlightPage';
    public $controllerActions;

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        $this->controllerActions = (array)$L->GetServiceStrs($this->curPage);
        unset($L);
    }

    public function ShowSearchForm()
    {
        $form = '';
        $form .= sprintf("<div class='search-flight-filter'>");
        $form .= sprintf("<form id='search-form' enctype='multipart/form-data'>");

        $Usr = new User();
        $avalibleBruTypes = $Usr->GetAvaliableBruTypes($this->_user->username);
        unset($Usr);

        $Bru = new Bru();
        $bruList = $Bru->GetBruList($avalibleBruTypes);
        unset($Bru);

        $optionString = "";

        $selectedFdr = '';
        foreach($bruList as $bruInfo)
        {
            if($selectedFdr == '') {
                $selectedFdr = $bruInfo['id'];
                $optionString .="<option selected='selected' value='".$bruInfo['id']."'>".$bruInfo['bruType']."</option>";
            } else {
                $optionString .="<option value='".$bruInfo['id']."'>".$bruInfo['bruType']."</option>";
            }
        }

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->bruType);
        $form .= sprintf("<select id='fdrForFilter' name='fdr' class='search-form-inputs'>%s</select>", $optionString);

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->bort);
        $form .= sprintf("<input name='bort' type='text' class='search-form-inputs' value=''/>");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->voyage);
        $form .= sprintf("<input name='voyage' type='text' class='search-form-inputs' value=''/>");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->departureAirport);
        $form .= sprintf("<input type='text' name='departureAirport' class='search-form-inputs' value=''/>");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->arrivalAirport);
        $form .= sprintf("<input type='text' name='arrivalAirport' class='search-form-inputs' value=''/>");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->flightDateFrom);
        $form .= sprintf("<input type='date' name='flightDateFrom' class='search-form-inputs' />");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->flightDateTo);
        $form .= sprintf("<input type='date' name='flightDateTo' class='search-form-inputs' />");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->performer);
        $form .= sprintf("<input name='performer' type='text' class='search-form-inputs' value=''/>");

        $form .= sprintf("<p class='search-form-labels'>%s</p>", $this->lang->aditionalInfo);
        $form .= sprintf("<input name='aditionalInfo' type='text' class='search-form-inputs' value='' />");

        $form .= "</form>";
        $form .= "</div>";

        $alg = $this->BuildSearchFlightAlgorithmesList($selectedFdr);

        $form .= sprintf("<div class='search-form-alg'><form id='search-form-alg-list'>%s</form></div>", $alg);
        $form .= sprintf("<div id='search-form-flights' class='search-form-flights'>&nbsp;</div>");
        $form .= "<div class='search-form-clear'></div>";
        return $form;
    }

    public function BuildSearchFlightAlgorithmesList($fdrId)
    {
        $SF = new SearchFlights();
        $alg = $SF->GetSearchAlgorithmes($fdrId);

        $form = '';
        foreach ($alg as $item) {
            $form .= '<p class="search-form-alg-item-row"><label>'.
                    '<input name="alg" type="radio" class="search-form-alg-item" value="'.$item['id'].'"/>'.
                    '<span class="search-form-alg-text"> '.$item['name'].'</span>'.
                    '</label></p>'.
                    '<div class="search-form-alg-clear"></div>';
        }

        return $form;
    }

    public function GetFlightsByCriteria($filterData)
    {
        $filterParams = [];
        if(isset($filterData['fdr']) && !empty($filterData['fdr'])) {
            $FDR = new Bru();
            $FDRinfo = $FDR->GetBruInfoById($filterData['fdr']);
            $filterParams['bruType'] = $FDRinfo['bruType'];
        }

        if(isset($filterData['bort']) && !empty($filterData['bort'])) {
            $filterParams['bort'] = $filterData['bort'];
        }

        if(isset($filterData['voyage']) && !empty($filterData['voyage'])) {
            $filterParams['voyage'] = $filterData['voyage'];
        }

        if(isset($filterData['departureAirport']) && !empty($filterData['departureAirport'])) {
            $filterParams['departureAirport'] = $filterData['departureAirport'];
        }

        if(isset($filterData['arrivalAirport']) && !empty($filterData['arrivalAirport'])) {
            $filterParams['arrivalAirport'] = $filterData['arrivalAirport'];
        }

        if(isset($filterData['aditionalInfo']) && !empty($filterData['aditionalInfo'])) {
            $filterParams['flightAditionalInfo'] = $filterData['aditionalInfo'];
        }

        if(isset($filterData['performer']) && !empty($filterData['performer'])) {
            $filterParams['performer'] = $filterData['performer'];
        }

        if(isset($filterData['flightDateFrom']) &&
                !empty($filterData['flightDateFrom']) &&
                strtotime($filterData['flightDateFrom'])) {
            $filterParams['from'] = strtotime($filterData['flightDateFrom']);
        }

        if(isset($filterData['flightDateTo']) &&
                !empty($filterData['flightDateTo']) &&
                strtotime($filterData['flightDateTo'])) {
            $filterParams['to'] = strtotime($filterData['flightDateTo']);
        }

        $F = new Flight();
        $flights = $F->GetFlightsByFilter($filterParams);
        unset($F);

        return $flights;
    }

    public function SearchByAlgorithm($algId, $flightsArr)
    {
        $foundFlights = [];
        $SF = new SearchFlights();
        $searchAlg = $SF->GetSearchAlgorithById($algId);
        unset($SF);

        $F = new Flight();

        if($searchAlg) {
            foreach ($flightsArr as $flightid) {
                $query = $searchAlg['alg'];
                $flightInfo = $F->GetFlightInfo($flightid);

                $apTableName = $flightInfo['apTableName'];
                $bpTableName = $flightInfo['bpTableName'];

                $query = str_replace("[ap]", $apTableName, $query);
                $query = str_replace("[bp]", $bpTableName, $query);

                foreach ($flightInfo as $flightInfoKey => $flightInfoVal)
                {
                    $query = str_replace("[".$flightInfoKey."]", $flightInfoVal, $query);
                }

                $c = new DataBaseConnector();
                $link = $c->Connect();

                if (!$link->multi_query($query))
                {
                    //err log
                    error_log("Impossible to execute multiquery: (" .
                            $query . ") " . $link->error);
                }

                do
                {
                    if ($res = $link->store_result())
                    {
                        $resultArr = array();
                        if($row = $res->fetch_array())
                        {
                            $foundFlights[] = $flightInfo;
                        }

                        $res->free();
                    }
                } while ($link->more_results() && $link->next_result());

                $c->Disconnect();
                unset($c);
            }
        }
        unset($F);

        return $foundFlights;
    }

    public function BuildFlightList($foundFlights)
    {
        $form = '';
        foreach ($foundFlights as $val) {
            $name = $val['bort'] . ", " .  $val['voyage']  . ", " . date('d/m/y H:i', $val['startCopyTime'])  .
            ", " . $val['bruType']  . ", " . $val['departureAirport']  . "-" . $val['arrivalAirport'] ;

            $form .= '<p class="found-flight-row"><label>'.
                    '<input name="flight" type="radio" class="ItemsCheck found-flight-item" data-type="flight" data-flightid="'.$val['id'].'" value="'.$val['id'].'"/>'.
                    '<span class="found-flight-text"> '.$name.'</span>'.
                    '</label></p>'.
                    '<div class="found-flight-clear"></div>';
        }

        return $form;
    }

    public function GetUserInfo()
    {
        $U = new User();
        $uId = $U->GetUserIdByName($this->_user->username);
        $userInfo = $U->GetUserInfo($uId);
        unset($U);

        return $userInfo;
    }
}
