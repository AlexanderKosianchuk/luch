<?php

namespace Controller;

use Component\RuntimeManager;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use \L;

use TCPDF;

require_once (SITE_ROOT_DIR."/src/tcpdf/tcpdf.php");
require_once (SITE_ROOT_DIR."/src/tcpdf/config/tcpdf_config.php");

class FlightEventsController extends BaseController
{
  public function getFlightEventsAction($flightId)
  {
    $flightId = intval($flightId);
    $userId = $this->user()->getId();

    $flightToFolders = $this->em()->getRepository('Entity\FlightToFolder')
      ->findOneBy(['userId' => $userId, 'flightId' => $flightId]);

    if ($flightToFolders === null) {
      throw new ForbiddenException('requested flight not avaliable for current user. Flight id: '. $flightId);
    }

    $flight = $this->em()->find('Entity\Flight', $flightId);

    if ($flight === null) {
      throw new NotFoundException("requested flight not found. Flight id: ". $flightId);
    }

    $fdr = $flight->getFdr();

    $isDisabled = true;
    if ($this->member()->isAdmin() || $this->member()->isModerator()) {
      $isDisabled = false;
    }

    $startCopyTime = $flight->getStartCopyTime();
    $frameLength = $fdr->getFrameLength();

    $flightEvents = $this->dic()
      ->get('event')
      ->getFormatedFlightEvents(
        $flight->getId(),
        $flight->getGuid(),
        $isDisabled,
        $startCopyTime,
        $frameLength
      );

    if (empty($flightEvents) && (count($flightEvents) === 0)) {
      return json_encode([
        'items' => [],
        'isProcessed' => true
      ]);
    }

    $accordion = [];

    for($ii = 0; $ii < count($flightEvents); $ii++) {
      $codePrefix = substr($flightEvents[$ii]['code'], 0, 3);

      if (in_array($codePrefix, self::$exceptionTypes)) {
        if (!isset($accordion[$codePrefix])) {
          $accordion[$codePrefix] = [];
        }
        $accordion[$codePrefix][] = $flightEvents[$ii];
      } else {
        if (!isset($accordion[self::$exceptionTypeOther])) {
          $accordion[self::$exceptionTypeOther] = [];
        }
        $accordion[self::$exceptionTypeOther][] = $flightEvents[$ii];
      }
    }

    return json_encode([
      'items' => $accordion,
      'isProcessed' => true
    ]);
  }

  public function printBlankAction($flightId, $colored = false, $sections = [])
  {
    $colored = $colored === 'true' ? true : false;
    $flight = $this->em()->find('Entity\Flight', $flightId);

    if (!$flight) {
      throw new NotFoundException('flight not found. Id: '.$flightId);
    }

    $prefixArr = $this->dic()
      ->get('fdr')
      ->getAnalogPrefixes($flight->getFdrId());

    $framesCount = $this->dic()
      ->get('flight')
      ->getFramesCount(
        $flight->getGuid(),
        $prefixArr[0]
      );

    $user = $this->user()->getName();
    // create new PDF document
    $pdf = new TCPDF ( 'L', 'mm', 'A4', true, 'UTF-8', false );

    // set document information
    $pdf->SetCreator ( $user );
    $pdf->SetAuthor ( $user );
    $pdf->SetTitle ( 'Flight events list' );
    $pdf->SetSubject ( 'Flight events list' );

    $bort = $flight->getBort();
    $voyage = $flight->getVoyage();
    $copyDate = date ( 'H:i:s d-m-Y', $flight->getStartCopyTime() );
    $timing = $this->dic('flight')->getFlightTiming($flight->getId());
    $flightDuration = $timing['duration'];
    $headerStr = $this->user()->getCompany();
    $imageFile = '';

    if($colored && ($this->user()->getLogo() != '')) {
      $imageFile = $this->dic()
        ->get('runtimeManager')
        ->getRuntimeFolder().DIRECTORY_SEPARATOR.uniqid().'.png';

      file_put_contents($imageFile, $this->user()->getLogo());
      $img = file_get_contents($imageFile);

      $pdf->SetHeaderData('$'.$img,
        "20", /*PDF_HEADER_LOGO_WIDTH*/
        $headerStr, /*HEADER_TITLE*/
        "", /*HEADER_STRING*/
        [0, 10, 50],
        [0, 10, 50]
      );
    } else {
      // set default header data
      $pdf->SetHeaderData("", 0, $headerStr, "",
        [0, 10, 50], [0, 10, 50]
      );
    }

    $pdf->setFooterData ([0, 10, 50], [0, 10, 50]);

    // set header and footer fonts
    $pdf->setHeaderFont ( Array (
        'dejavusans',
        '',
        11
    ));

    $pdf->setFooterFont ( Array (
      PDF_FONT_NAME_DATA,
      '',
      PDF_FONT_SIZE_DATA
    ));

    // set default monospaced font
    $pdf->SetDefaultMonospacedFont ( PDF_FONT_MONOSPACED );

    // set margins
    $pdf->SetMargins ( PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT );
    $pdf->SetHeaderMargin ( PDF_MARGIN_HEADER );
    $pdf->SetFooterMargin ( PDF_MARGIN_FOOTER );

    // set auto page breaks
    $pdf->SetAutoPageBreak ( TRUE, PDF_MARGIN_BOTTOM );

    // set image scale factor
    $pdf->setImageScale ( PDF_IMAGE_SCALE_RATIO );

    // ---------------------------------------------------------

    // set default font subsetting mode
    $pdf->setFontSubsetting ( true );

    // Set font
    // dejavusans is a UTF-8 Unicode font, if you only need to
    // print standard ASCII chars, you can use core fonts like
    // helvetica or times to reduce file size.
    $pdf->SetFont ( 'dejavusans', '', 12, '', true );

    // Add a page
    // This method has several options, check the source code documentation for more information.
    $pdf->AddPage ();

    if($imageFile !== '') {
      unlink($imageFile);
    }

    // set text shadow effect
    $pdf->setTextShadow ( array (
      'enabled' => true,
      'depth_w' => 0.2,
      'depth_h' => 0.2,
      'color' => [196, 196, 196],
      'opacity' => 1,
      'blend_mode' => 'Normal'
    ));

    // Pasport
    $strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(0, 10, 64);";
    $str = '<p style="' . $strStyle . '">' . L::flightEvents_pasport . '</p>';

    $pdf->writeHTML ( $str, true, false, false, false, '' );

    // Pasport info
    $strStyle = "text-align:center;";
    $str = '<p style="' . $strStyle . '">' . L::flightEvents_bruType . ' - ' . $flight->getFdr()->getName() . '. <br>' .
        L::flightEvents_bort . ' - ' . $flight->getBort() . '; ' .
        L::flightEvents_voyage . ' - ' . $flight->getVoyage() . '; ' .

    L::flightEvents_route . ' : ' . $new_string = preg_replace ( '/[^a-zA-z0-9]/', '', $flight->getDepartureAirport() ) . ' - ' .
    preg_replace ( '/[^a-zA-z1-9]/', '', $flight->getArrivalAirport() ) . '. <br>' .
    L::flightEvents_flightDate . ' - ' . date ( 'H:i:s d-m-Y', $flight->getStartCopyTime() ) . '; ' .
    L::flightEvents_duration . ' - ' . $flightDuration . '. <br>';

    $fileName = date ( 'Y-m-d_H.i.s', $flight->getStartCopyTime()) . '_' . $flight->getBort() . '_' .  $flight->getVoyage() . '_' . $flight->getFdr()->getName();

    if ((strpos ( $flight->getFdr()->getAditionalInfo(), ";" ) >= 0)
      && ($flight->getAditionalInfo() !== null)
    ) {
      $counterNeedBrake = false;
      $aditionalInfoArr = $flight->getAditionalInfo();
      if (is_array($aditionalInfoArr)) {
        foreach ( $aditionalInfoArr as $name => $val) {
          if ($counterNeedBrake) {
            $str .= (
                (L('uploader_'.$name) !== null)
                ? L('uploader_'.$name)
                : $name
              ).' - '.$val.'; </br>';

            $counterNeedBrake = ! $counterNeedBrake;
          } else {
            $str .= (
                (L('uploader_'.$name) !== null)
                ? L('uploader_'.$name)
                : $name
              ).' - '.$val.';';

            $counterNeedBrake = ! $counterNeedBrake;
          }
        }
      }
    }

    $str .= "</p>";

    $pdf->writeHTML ( $str, true, false, false, false, '' );

    $flightEvents = $this->dic()
      ->get('event')
      ->getFormatedFlightEvents(
        $flight->getId(),
        $flight->getGuid(),
        false,
        $flight->getStartCopyTime(),
        $flight->getFdr()->getFrameLength()
      );

    // if isset events
    if (count($flightEvents) > 0) {
      $pdf->SetFont ('dejavusans', '', 9, '', true);

      $strStyle = 'style="text-align:center; font-weight: bold; background-color:#708090; color:#FFF"';
      $str = '<p><table border="1" cellpadding="1" cellspacing="1">'
        .'<tr ' . $strStyle . '><td width="70"> '
        . L::flightEvents_start . '</td>'
        . '<td width="70">'
        . L::flightEvents_end
        . '</td>'
        . '<td width="70">'
        . L::flightEvents_duration
        . '</td>'
        . '<td width="70">'
        . L::flightEvents_code
        . '</td>'
        . '<td width="260">'
        . L::flightEvents_eventName
        . '</td>'
        . '<td width="110">'
        . L::flightEvents_algText
        . '</td>'
        . '<td width="180">'
        . L::flightEvents_aditionalInfo
        . '</td>'
        . '<td width="110">'
        . L::flightEvents_comment
        . '</td></tr>';

      for ($ii = 0; $ii < count ($flightEvents); $ii++) {
        $event = $flightEvents[$ii];
        $codePrefix = substr($event['code'], 0, 3);
        $sectionsCheck = true;
        if (count($sections) > 0) {
           $sectionsCheck = in_array($codePrefix, $sections)
             || (!preg_match('/00[0-9]/', $codePrefix) && in_array('other', $sections));
        }

        if (!$event ['falseAlarm'] && $sectionsCheck) {
          if ($colored && $event ['status'] == "C") {
            $style = "background-color:LightCoral";
          } else if ($colored && $event ['status'] == "D") {
            $style = "background-color:LightYellow";
          } else if ($colored && $event ['status'] == "E") {
            $style = "background-color:LightGreen";
          } else {
            $style = "";
          }

          $excAditionalInfo = '';
          if (is_array($event['excAditionalInfo'])) {
            $excAditionalInfo = implode(';<br>', $event['excAditionalInfo']);
          } else if (is_string($event['excAditionalInfo'])) {
            $excAditionalInfo = str_replace ( ";", ";<br>", $excAditionalInfo );
          }

          $event ['algText'] = str_replace ( '<', "less", $event ['algText'] );

          $str .= '<tr style="' . $style . '" nobr="true">' .
          '<td width="70" style="text-align:center;">' . $event ['start'] . '</td>' .
          '<td width="70" style="text-align:center;">' . $event ['end'] . '</td>' .
          '<td width="70" style="text-align:center;">' . $event ['duration'] . '</td>' .
          '<td width="70" style="text-align:center;">' . $event ['code'] . '</td>' .
          '<td width="260" style="text-align:center;">' . $event ['comment'] . '</td>' .
          '<td width="110" style="text-align:center;">' . $event ['algText'] . '</td>' .
          '<td width="180" style="text-align:center;">' . $excAditionalInfo . '</td>' .
          '<td width="110" style="text-align:center;"> ' . $event ['userComment'] . '</td></tr>';
        }
      }

      unset ( $FEx );

      $str .= "</table></p>";

      $pdf->writeHTML ( $str, false, false, false, false, '' );

      $pdf->SetFont ( 'dejavusans', '', 12, '', true );
      $str = "</br></br>" . L::flightEvents_performer
        . ' : '
        . '_____________________ '
        . $flight->getPerformer()
        . ', ' . date ( 'd-m-Y' ) . '';

      $pdf->writeHTML ( $str, false, false, false, false, '' );
    } else {
      $strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(128, 10, 0);";
      $str = '<p style="' . $strStyle . '">' . L::flightEvents_noEvents . '</p>';

      $pdf->writeHTML ( $str, false, false, false, false, '' );
    }

    $pdf->Output ($fileName, 'I');
  }

  private static $exceptionTypeOther = 'other';
  private static $exceptionTypes = [
    '000', '001', '002', '003', 'other'
  ];

  public function changeReliabilityAction($flightId, $eventId, $eventType, $reliability = false)
  {
    $falseAlarm = $reliability === 'true' ? false : true;
    $eventType = intval($eventType);

    $flightToFolders = $this->em()->getRepository('Entity\FlightToFolder')
      ->findOneBy(['userId' => $this->user()->getId(), 'flightId' => $flightId]);

    if ($flightToFolders === null) {
      throw new ForbiddenException('requested flight not avaliable for current user. Flight id: '. $flightId);
    }

    $flight = $this->em()->getRepository('Entity\Flight')->findOneById($flightId);

    if ($flight === null) {
      throw new NotFoundException("requested flight not found. Flight id: ". $flightId);
    }

    $this->dic('event')->updateFalseAlarm(
      $flight->getGuid(),
      $eventType,
      $eventId,
      $falseAlarm
    );

    return json_encode('ok');
  }
}
