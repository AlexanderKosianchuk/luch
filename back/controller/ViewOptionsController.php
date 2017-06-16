<?php

namespace Controller;

use Model\Language;
use Model\Flight;
use Model\Fdr;
use Model\Frame;
use Model\FlightException;
use Model\FlightComments;
use Model\Channel;
use Model\User;

use Entity\FlightEvent;
use Entity\FlightSettlement;

use Component\EntityManagerComponent as EM;
use Component\RealConnectionFactory as LinkFactory;

use Exception;

class ViewOptionsController extends CController
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

    public function PutWorkspace()
    {
        //MainContainer
        $workspace = "<div id='flightOptionsWorkspace' class='WorkSpace'></div>";

        return $workspace;
    }

    public function GetEventsListHeader($flightId)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Int expected. Passed: "
                . json_encode($flightId), 1);
        }

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $prefixArr = $fdr->GetBruApCycloPrefixes($fdrId);
        unset($fdr);

        $bort = $flightInfo['bort'];
        $voyage = $flightInfo['voyage'];
        $copyDate = date ( 'H:i:s d-m-Y', $flightInfo['startCopyTime'] );

        $Fr = new Frame;
        $framesCount = $Fr->GetFramesCount($flightInfo['apTableName'], $prefixArr[0]); //giving just some prefix
        $flightDuration = $Fr->FrameCountToDuration ($framesCount, $fdrInfo ['stepLength'] );
        unset ($Fr);

        $str = '<h4 class="container__events-header">' . $this->lang->bruType . ' - ' . $fdrInfo['name'] . '. ' .
                $this->lang->bort . ' - ' . $flightInfo['bort'] . '; ' .
                $this->lang->voyage . ' - ' . $flightInfo['voyage'] . '; ' .

        $this->lang->route . ' : ' . $new_string = preg_replace ( '/[^a-zA-z0-9]/', '', $flightInfo['departureAirport'] ) . ' - ' .
        preg_replace ( '/[^a-zA-z1-9]/', '', $flightInfo['arrivalAirport'] ) . '. ' .
        $this->lang->flightDate . ' - ' . date ( 'H:i:s d-m-Y', $flightInfo['startCopyTime'] ) . '; ' .
        $this->lang->duration . ' - ' . $flightDuration . '. ';

        $fileName = date ( 'Y-m-d_H.i.s', $flightInfo['startCopyTime']) . '_' . $flightInfo['bort'] . '_' .  $flightInfo['voyage'] . '_' . $fdrInfo['name'];

        if ((strpos ( $fdrInfo ['aditionalInfo'], ";" ) >= 0)
            && ($flightInfo['flightAditionalInfo'] !== null)
        ) {
            $aditionalInfoArr = json_decode($flightInfo['flightAditionalInfo'], true);
            if (is_array($aditionalInfoArr)) {
                foreach ( $aditionalInfoArr as $name => $val) {
                    $str .= (isset($this->lang->$name) ? $this->lang->$name : $name) . " - " . $val . "; ";
                }
            }
        }

        return $str . "</h4>";
    }

    private static $exceptionTypeOther = 'other';
    private static $exceptionTypes = [
        '000', '001', '002', '003', 'other'
    ];
    private static $statusColor = [
        "C" => "LightCoral",
        "D" => "LightYellow",
        "E" => "LightGreen",
    ];

    public function ShowEventsList($flightId)
    {
        if (!is_int($flightId)) {
            throw new Exception("Incorrect flightId passed. Integer is required. Passed: "
                . json_encode($flightId), 1);
        }

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $fdrId = intval($flightInfo['id_fdr']);
        $exTableName = $flightInfo['exTableName'];
        unset($Fl);

        $fdr = new Fdr;
        $fdrInfo = $fdr->getFdrInfo($fdrId);
        $flightApHeaders = $fdr->GetBruApHeaders($fdrId);
        $flightBpHeaders= $fdr->GetBruBpHeaders($fdrId);
        $excListTableName = $fdrInfo['excListTableName'];
        unset($fdr);

        $em = EM::get();
        $flight = $em->find('Entity\Flight', $flightId);
        $flightGuid = $flight->getGuid();
        $startCopyTime = $flight->getStartCopyTime();
        $frameLength = $fdrInfo['frameLength'];

        $role = $this->_user->userInfo['role'];
        $isDisabled = " disabled='disabled' ";
        if (User::isAdmin($role) || User::isModerator($role)) {
            $isDisabled = '';
        }

        $flightEvents = $em->getRepository('Entity\FlightEvent')
            ->getFormatedFlightEvents($flightGuid, $isDisabled, $startCopyTime, $frameLength);

        $eventsList = "";
        $eventTypeCount = [];

        if (($exTableName !== "") && ($flightEvents === null)) {
            $eventsList .= sprintf ("<table border='1' align='center' style='padding:2px'>
                <tr><td>&nbsp;%s&nbsp;</td></tr>
                </table>", $this->lang->processingWasNotPerformed);

            return $eventsList;
        }

        $FEx = new FlightException;
        $excEventsList = $FEx->GetFlightEventsList($exTableName);

        if (empty($excEventsList) && (count($flightEvents) === 0)) {
            $eventsList .= sprintf ("<table border='1' align='center' style='padding:2px'>
                <tr><td>&nbsp;%s&nbsp;</td></tr>
                </table>", $this->lang->noEvents);

            return $eventsList;
        }

        $Frame = new Frame;
        //change frame num to time
        for ($i = 0; $i < count($excEventsList); $i++) {
            $event = $excEventsList[$i];
            $event['start'] = date("H:i:s", $event['startTime'] / 1000);
            $reliability = "checked";
            //converting false alarm to reliability
            if($event['falseAlarm'] == 0) {
                $reliability = "checked";
            } else {
                $reliability = "";
            }
            $event['reliability'] = $reliability;
            $event['end'] = date("H:i:s", $event['endTime'] / 1000);
            $event['duration'] = $Frame->TimeStampToDuration(
            $event['endTime'] - $event['startTime']);

            $event = array_merge($event, $FEx->GetExcInfo($excListTableName,
                $event['refParam'], $event['code']));

            if (isset(self::$statusColor[$event['status']])) {
                $style = "background-color:" . self::$statusColor[$event['status']];
            } else {
                $style = "background-color:none;";
            }

            $event['excAditionalInfo'] = str_replace(";", ";</br>", $event['excAditionalInfo']);
            $event['isDisabled'] = $isDisabled;
            $event['style'] = $style;
            $event['eventType'] = 1;

            $flightEvents[] = $event;
        }
        unset($Frame);

        $accordion = [];
        $eventsListTable = sprintf ("<table align='center' class='ExeptionsTable NotSelectable'>
                <tr class='ExeptionsTableHeader'><td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell' width='210px'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell' width='50px'> %s </td>
                <td class='ExeptionsCell' width='210px'> %s </td></tr>",
        $this->lang->start,
        $this->lang->end,
        $this->lang->duration,
        $this->lang->code,
        $this->lang->eventName,
        $this->lang->algText,
        $this->lang->aditionalInfo,
        $this->lang->reliability,
        $this->lang->comment);

        for ($ii = 0; $ii < count(self::$exceptionTypes); $ii++) {
            $accordion[self::$exceptionTypes[$ii]] = $this->buildEventAccordionItem ($ii, $eventsListTable);
        }

        for($i = 0; $i < count($flightEvents); $i++) {
            $event = $flightEvents[$i];
            $eventsListRow = $this->buildEventTableRow($event);

            $codePrefix = substr($event['code'], 0, 3);
            if(in_array($codePrefix, self::$exceptionTypes)) {
                $accordion[$codePrefix] .= $eventsListRow;
                $eventTypeCount[$codePrefix] = true;
            } else {
                $accordion[self::$exceptionTypeOther] .= $eventsListRow;
                $eventTypeCount[self::$exceptionTypeOther] = true;
            }
        }

        for($ii = 0; $ii < count(self::$exceptionTypes); $ii++) {
            $accordion[self::$exceptionTypes[$ii]] .= sprintf ("</table></div></div>");

            if(!isset($eventTypeCount[self::$exceptionTypes[$ii]]) ||
                !$eventTypeCount[self::$exceptionTypes[$ii]]) {
                unset($accordion[self::$exceptionTypes[$ii]]);
            }
        }

        $eventsList = '';
        foreach ($accordion as $item) {
            $eventsList .= $item;
        }

        unset($FEx);

        return $eventsList;
    }

    private function buildEventAccordionItem ($num, $eventsListTable)
    {
        $langMask = $this->lang->eventCodeMask;
        if (in_array($num, self::$exceptionTypes)) {
            $mask = 'eventCodeMask00'. $num;
            if (isset($this->lang->$mask)) {
                $langMask = $this->lang->$mask;
            }
        }

        return sprintf('<div class="exceptions-accordion">'.
            '<div class="exceptions-accordion-title" data-shown="true" data-section="%s"><p>%s - %s</p></div>'.
            '<div class="exceptions-accordion-content"> %s',
            self::$exceptionTypes[$num],
            $langMask,
            self::$exceptionTypes[$num],
            $eventsListTable);
    }

    private function buildEventTableRow($args)
    {
        return sprintf ("<tr style='%s' class='ExceptionTableRow'
                    data-refparam='%s'
                    data-startframe='%s'
                    data-endframe='%s'><td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell'> %s </td>
                <td class='ExeptionsCell' style='text-align:center;'>
                    <input class='reliability' data-excid='%s' data-event-type='%s' type='checkbox' %s %s></input>
                </td>
                <td class='ExeptionsCell events_user-comment' data-excid='%s' %s> %s </td></tr>",
        $args['style'],
        $args['refParam'],
        $args['frameNum'],
        $args['endFrameNum'],
        $args['start'],
        $args['end'],
        $args['duration'],
        $args['code'],
        $args['comment'],
        $args['algText'],
        $args['excAditionalInfo'],
        $args['id'],
        $args['eventType'],
        $args['reliability'],
        $args['isDisabled'],
        $args['id'],
        $args['isDisabled'],
        $args['userComment']);
    }

    public function UpdateExceptionComment($flightId, $excId, $text)
    {
        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);
        $excTableName = $flightInfo['exTableName'];

        $FE = new FlightException;
        $res = $FE->UpdateUserComment($excTableName, $excId, $text);
        unset($FE);
        return $res;
    }

    public function SetExcReliability($extFlightId, $extExcId, $extState)
    {
        $flightId = $extFlightId;
        $excId = $extExcId;
        $state = $extState;

        $Fl = new Flight;
        $flightInfo = $Fl->GetFlightInfo($flightId);
        unset($Fl);
        $excTableName = $flightInfo['exTableName'];

        if (($state == false) || ($state == 'false')) {
            $state = 1;
        } else if(($state == true) || ($state == 'true')) {
            $state = 0;
        } else {
            $state = 0;
        }

        $FE = new FlightException;
        $res = $FE->UpdateFalseAlarmState($excTableName, $excId, $state);
        unset($FE);
        return $res;
    }

    /*
    * ==========================================
    * REAL ACTIONS
    * ==========================================
    */

    public function putViewOptionsContainer($data)
    {
        $workspace = $this->PutWorkspace();

        $data = array(
            'workspace' => $workspace
        );
        $answ["status"] = "ok";
        $answ["data"] = $data;

        echo json_encode($answ);
    }

    public function getFlightDuration($data)
    {
        if(isset($data['flightId']))
        {
            $flightId = intval($data['flightId']);
            $flightTiming = $this->GetFlightTiming($flightId);

            $data = array(
                'duration' => $flightTiming['duration'],
                'startCopyTime' => $flightTiming['startCopyTime'],
                'stepLength' => $flightTiming['stepLength']
            );
            $answ["status"] = "ok";
            $answ["data"] = $data;

            echo json_encode($answ);
        }
        else
        {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
        }
    }

    public function getEventsList($data)
    {
        if(isset($data['flightId'])) {
            $flightId = intval($data['flightId']);
            $eventsListHeader = $this->GetEventsListHeader($flightId);
            $eventsList = $this->ShowEventsList($flightId);

            $data = array(
                    'eventsList' => $eventsList,
                    'eventsListHeader' => $eventsListHeader
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

    public function setEventReliability($data)
    {
        if((isset($data['flightId']))
            && (isset($data['excId']))
            && (isset($data['state']))
        ) {
            $flightId = intval($data['flightId']);
            $excId = intval($data['excId']);
            $state = $data['state'];
            $this->SetExcReliability($flightId, $excId, $state);

            $answ["status"] = "ok";
            echo json_encode($answ);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
        }
    }

    public function updateComment($data)
    {
        if (isset($data['flightId'])
            && isset($data['excId'])
            && isset($data['text'])
        ) {
            $flightId = intval($data['flightId']);
            $excid = $data['excId'];
            $text = $data['text'];

            $this->UpdateExceptionComment($flightId, $excid, $text);
            $answ["status"] = "ok";

            echo json_encode($answ);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
        }
    }

    public function saveFlightComment($data)
    {
        $params = [];
        parse_str($data, $params);

        if (isset($params['flight-id'])) {
            $flightId = intval($params['flight-id']);

            $Fd = new Folder;
            $folder = $Fd->GetFlightFolder($flightId, $c->_user->userInfo['id']);
            unset($Fd);

            $answ = [];
            $answ["status"] = "not allowed";
            if (!empty($folder)) {
                $c->UpdateFlightComment($flightId, $params);
                $answ["status"] = "ok";
            }

            echo json_encode($answ);
        } else {
            $answ["status"] = "err";
            $answ["error"] = "Not all nessesary params sent. Post: ".
                    json_encode($_POST) . ". Page ViewOptionsController.php";
            echo(json_encode($answ));
        }
    }
}
