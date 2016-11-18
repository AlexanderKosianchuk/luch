<?php
require_once (@$_SERVER ['DOCUMENT_ROOT'] . "/includes.php");
require_once ("../tcpdf/tcpdf.php");
require_once ("../tcpdf/config/tcpdf_config.php");

class PrinterController extends CController
{
    public $curPage = 'printerPage';
    public $printerActions;

    function __construct()
    {
        $this->IsAppLoggedIn();
        $this->setAttributes();

        $L = new Language();
        $this->printerActions = ( array ) $L->GetServiceStrs ( $this->curPage );
        unset($L);
    }

    public function ConstructFlightEventsList($extFlightId, $sections = [], $colored = false)
    {
        $flightId = $extFlightId;
        $user = $this->_user->username;

        $Fl = new Flight();
        $flightInfo = $Fl->GetFlightInfo($flightId);
        $bruType = $flightInfo['bruType'];
        unset($Fl);

        $Bru = new Bru();
        $bruInfo = $Bru->GetBruInfo($bruType);
        $flightApHeaders= $Bru->GetBruApHeaders($bruType);
        $flightBpHeaders = $Bru->GetBruBpHeaders($bruType);

        $prefixArr = $Bru->GetBruApCycloPrefixes($bruType);
        unset($Bru);

        $Frame = new Frame();
        $framesCount = $Frame->GetFramesCount($flightInfo['apTableName'], $prefixArr[0]); //giving just some prefix
        unset($Frame);

        // create new PDF document
        $pdf = new TCPDF ( 'L', 'mm', 'A4', true, 'UTF-8', false );

        // set document information
        $pdf->SetCreator ( $user );
        $pdf->SetAuthor ( $user );
        $pdf->SetTitle ( 'Flight events list' );
        $pdf->SetSubject ( 'Flight events list' );
        // $pdf->SetKeywords('TCPDF, PDF, example, test, guide');

        $bort = $flightInfo['bort'];
        $voyage = $flightInfo['voyage'];
        $copyDate = date ( 'H:i:s d-m-Y', $flightInfo['startCopyTime'] );

        $Fr = new Frame ();
        $flightDuration = $Fr->FrameCountToDuration ($framesCount, $bruInfo ['stepLength'] );
        unset ($Fr);

        $usrInfo = $this->_user->userInfo;

        $headerStr = $usrInfo ['company'];
        $imageFile = '';

        if($colored && ($usrInfo['logo'] != '')) {
            $imageFile =  SITE_ROOT_DIR.'/fileUploader/files/'.uniqid().'.png';
            file_put_contents($imageFile, $usrInfo['logo']);
            $img = file_get_contents($imageFile);

            $pdf->SetHeaderData('$'.$img,
                /*PDF_HEADER_LOGO_WIDTH*/ "20",
                /*HEADER_TITLE*/ $headerStr,
                /*HEADER_STRING*/ "",
                [0, 10, 50],
                [0, 10, 50]
            );
        } else {
            // set default header data
            $pdf->SetHeaderData("", "", $headerStr, "",
                [0, 10, 50 ], [0, 10, 50]
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
        $str = '<p style="' . $strStyle . '">' . $this->lang->pasport . '</p>';

        $pdf->writeHTML ( $str, true, false, false, false, '' );

        // Pasport info
        $strStyle = "text-align:center;";
        $str = '<p style="' . $strStyle . '">' . $this->lang->bruType . ' - ' . $bruInfo ['bruType'] . '. <br>' .
                $this->lang->bort . ' - ' . $flightInfo['bort'] . '; ' .
                $this->lang->voyage . ' - ' . $flightInfo['voyage'] . '; ' .

        $this->lang->route . ' : ' . $new_string = preg_replace ( '/[^a-zA-z0-9]/', '', $flightInfo['departureAirport'] ) . ' - ' .
        preg_replace ( '/[^a-zA-z1-9]/', '', $flightInfo['arrivalAirport'] ) . '. <br>' .
        $this->lang->flightDate . ' - ' . date ( 'H:i:s d-m-Y', $flightInfo['startCopyTime'] ) . '; ' .
        $this->lang->duration . ' - ' . $flightDuration . '. <br>';

        $fileName = date ( 'Y-m-d_H.i.s', $flightInfo['startCopyTime']) . '_' . $flightInfo['bort'] . '_' .  $flightInfo['voyage'] . '_' . $bruInfo ['bruType'];

        if (strpos ( $bruInfo ['aditionalInfo'], ";" ) >= 0) {
            $counterNeedBrake = false;
            $aditionalInfoArr = explode ( ";", $flightInfo['flightAditionalInfo'] );
            foreach ( $aditionalInfoArr as $aditionalInfo ) {
                if ($aditionalInfo != "") {
                    $nameVal = explode ( ":", $aditionalInfo );

                    if(count($nameVal) > 1){
                        $name = $nameVal [0];
                        $val = $nameVal [1];

                        if ($counterNeedBrake) {
                            $str .= (isset($this->lang->$name) ? $this->lang->$name : $name) . " - " . $val . "; </br>";
                            $counterNeedBrake = ! $counterNeedBrake;
                        } else {
                            $str .= (isset($this->lang->$name) ? $this->lang->$name : $name) . " - " . $val . "; ";
                            $counterNeedBrake = ! $counterNeedBrake;
                        }
                    }
                }
            }
        }

        $str .= "</p>";

        $pdf->writeHTML ( $str, true, false, false, false, '' );

        if ($flightInfo ['exTableName'] != "") {
            $FEx = new FlightException ();
            $excEventsList = $FEx->GetFlightEventsList ( $flightInfo ['exTableName'] );

            $Frame = new Frame ();
            // change frame num to time
            for($i = 0; $i < count ( $excEventsList ); $i ++) {
                $event = $excEventsList [$i];

                $excEventsList [$i] ['start'] = date ( "H:i:s", $excEventsList [$i] ['startTime'] / 1000 );
                $reliability = "checked";
                // converting false alarm to reliability
                if ($excEventsList [$i] ['falseAlarm'] == 0) {
                    $reliability = true;
                } else {
                    $reliability = false;
                }

                $excEventsList [$i] ['reliability'] = $reliability;
                $excEventsList [$i] ['end'] = date ( "H:i:s", $excEventsList [$i] ['endTime'] / 1000 );
                $excEventsList [$i] ['duration'] = $Frame->TimeStampToDuration ( $excEventsList [$i] ['endTime'] - $excEventsList [$i] ['startTime'] );
            }
            unset ( $Frame );

            // if isset events
            if (! (empty ( $excEventsList ))) {
                $pdf->SetFont ( 'dejavusans', '', 9, '', true );

                $strStyle = 'style="text-align:center; font-weight: bold; background-color:#708090; color:#FFF"';
                $str = '<p><table border="1" cellpadding="1" cellspacing="1">' . '<tr ' . $strStyle . '><td width="70"> ' . $this->lang->start . '</td>' . '<td width="70">' . $this->lang->end . '</td>' . '<td width="70">' . $this->lang->duration . '</td>' . '<td width="70">' . $this->lang->code . '</td>' . '<td width="260">' . $this->lang->eventName . '</td>' . '<td width="110">' . $this->lang->algText . '</td>' . '<td width="180">' . $this->lang->aditionalInfo . '</td>' . '<td width="110">' . $this->lang->comment . '</td></tr>';

                for($i = 0; $i < count ( $excEventsList ); $i ++) {
                    $event = $excEventsList [$i];
                    $excInfo = $FEx->GetExcInfo ( $bruInfo ['excListTableName'], $event ['refParam'], $event ['code'] );

                    $codePrefix = substr($event['code'], 0, 3);
                    if ($event ['reliability'] && (in_array($codePrefix, $sections)
                        || (!preg_match('/00[0-9]/', $codePrefix) && in_array('other', $sections)))) {

                        if ($colored && $excInfo ['status'] == "C") {
                            $style = "background-color:LightCoral";
                        } else if ($colored && $excInfo ['status'] == "D") {
                            $style = "background-color:LightYellow";
                        } else if ($colored && $excInfo ['status'] == "E") {
                            $style = "background-color:LightGreen";
                        } else {
                            $style = "";
                        }

                        $excAditionalInfo = $event ['excAditionalInfo'];
                        $excAditionalInfo = str_replace ( ";", ";<br>", $excAditionalInfo );

                        $excInfo ['algText'] = str_replace ( '<', "less", $excInfo ['algText'] );

                        $str .= '<tr style="' . $style . '" nobr="true">' .
                        '<td width="70" style="text-align:center;">' . $event ['start'] . '</td>' .
                        '<td width="70" style="text-align:center;">' . $event ['end'] . '</td>' .
                        '<td width="70" style="text-align:center;">' . $event ['duration'] . '</td>' .
                        '<td width="70" style="text-align:center;">' . $event ['code'] . '</td>' .
                        '<td width="260" style="text-align:center;">' . $excInfo ['comment'] . '</td>' .
                        '<td width="110" style="text-align:center;">' . $excInfo ['algText'] . '</td>' .
                        '<td width="180" style="text-align:center;">' . $excAditionalInfo . '</td>' .
                        '<td width="110" style="text-align:center;"> ' . $event ['userComment'] . '</td></tr>';
                    }
                }

                unset ( $FEx );

                $str .= "</table></p>";

                $pdf->writeHTML ( $str, false, false, false, false, '' );

                $pdf->SetFont ( 'dejavusans', '', 12, '', true );
                $str = "</br></br>" . $this->lang->performer . ' : ' . '_____________________ ' . $flightInfo['performer']. ', ' . date ( 'd-m-Y' ) . '';

                $pdf->writeHTML ( $str, false, false, false, false, '' );
            } else {
                $strStyle = "text-align:center; font-size: xx-large; font-weight: bold; color: rgb(128, 10, 0);";
                $str = '<p style="' . $strStyle . '">' . $this->lang->noEvents . '</p>';

                $pdf->writeHTML ( $str, false, false, false, false, '' );
            }
        }

        $pdf->Output ($fileName, 'I');
    }

    public function GetUserInfo() {
        $U = new User ();
        $uId = $U->GetUserIdByName ( $this->_user->username );
        $userInfo = $U->GetUserInfo ( $uId );
        unset ( $U );

        return $userInfo;
    }
}
