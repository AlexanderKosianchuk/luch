<?php

namespace Controller;

use Entity\Flight as FlightEntity;

use Evenement\EventEmitter;

use Exception\UnauthorizedException;
use Exception\BadRequestException;
use Exception\NotFoundException;
use Exception\ForbiddenException;

use Exception;
use ZipArchive;

class FlightsController extends BaseController
{
  public function getAllAction()
  {
    $userId = $this->user()->getId();

    $items = [];
    $flightsToFolders = $this->em()->getRepository('Entity\FlightToFolder')
      ->findBy(['userId' => $userId]);

    foreach ($flightsToFolders as $flightToFolders) {
      $flightId = $flightToFolders->getFlightId();
      $flight = $this->em()->createQueryBuilder()
        ->select('f')
        ->from('Entity\Flight', 'f')
        ->where('f.id = ?1')
        ->setParameter(1, $flightId)
        ->getQuery()
        ->getScalarResult();

      if (empty($flight)) {
        $this->em()->remove($flightToFolders);
        $this->em()->flush();
      } else {
        $items[] = $this->em()
          ->getRepository('Entity\FlightToFolder')
          ->getTreeItem(
            $flightToFolders->getFlightId(), $userId
          );
      }
    }

    return json_encode($items);
  }

  public function deleteAction($id)
  {
    $this->dic('flight')
      ->deleteFlight(intval($id), $this->user()->getId());

    return json_encode('ok');
  }

  public function getInfoAction($flightId)
  {
    $flightId = intval($flightId);
    $flight = $this->em()->getRepository('Entity\Flight')
      ->findOneBy(['id' => $flightId]);

    if (!$flight) {
      throw new NotFoundException("requested flight not found. Flight id: ". $flightId);
    }

    $flightTiming = $this->dic('flight')->getFlightTiming($flightId);

    return json_encode([
      'data' => array_merge(
        $flight->get(true), [
          'fdrId' => $flight->getFdr()->getId(),
          'fdrName' => $flight->getFdr()->getName(),
          'startCopyTimeFormated' => date('d/m/y H:i:s', $flight->getStartCopyTime()),
        ]
      ),
      'duration' => $flightTiming['duration'],
      'startFlightTime' => $flightTiming['startCopyTime'],
      'stepLength' => $flightTiming['stepLength'],
    ]);
  }

  public function exportAction($flightIds)
  {
    if (!is_array($flightIds)) {
      $flightIds = [$flightIds];
    }

    $exportedFiles = [];

    foreach ($flightIds as $flightId) {
      $flight = $this->em()->find('Entity\Flight', $flightId);
      $flightInfo = $this->em()->find('Entity\Flight', $flightId)->get(true);

      $fileGuid = uniqid();
      $exportedFileName = $flightInfo['bort']
        .'_'.date('Y-m-d', $flightInfo['startCopyTime'])
        .'_'.$flightInfo['voyage']
        .'_'.$fileGuid;

      $exportedFileDir = $this->dic('runtimeManager')
        ->getExportFolder();
      $exportedFilePath = $this->dic('runtimeManager')
        ->createExportedFile($exportedFileName);

      $headerFile = [];
      $headerFile['filename'] = "header_".$flightInfo['bort']."_".$flightInfo['voyage'].$fileGuid.".json";
      $headerFile['root'] = $exportedFileDir.DIRECTORY_SEPARATOR.$headerFile['filename'];
      $exportedFiles[] = $headerFile;

      $apPrefixes = $this->dic('fdr')
        ->getAnalogPrefixes($flight->getFdrId());

      $link = $this->connection()->create('flights');

      for ($i = 0; $i < count($apPrefixes); $i++) {
        $table = $flight->getGuid().'_'.$this->dic('fdr')->getApType().'_'.$apPrefixes[$i];
        $exportedTable = $this->connection()->exportTable(
          $table,
          $exportedFileDir.DIRECTORY_SEPARATOR.$table."_".$fileGuid,
          $link
        );

        $exportedFiles[] = $exportedTable;

        $flightInfo['apTables'][] = [
          'pref' => $apPrefixes[$i],
          'file' => $exportedTable['filename']
        ];
      }

      $bpPrefixes = $this->dic('fdr')
        ->getBinaryPrefixes($flight->getFdrId());

      for ($i = 0; $i < count($bpPrefixes); $i++) {
        $table = $flight->getGuid().'_'.$this->dic('fdr')->getBpType().'_'.$bpPrefixes[$i];

        $exportedTable = $this->connection()->exportTable(
          $table,
          $exportedFileDir.DIRECTORY_SEPARATOR.$table.'_'.$fileGuid,
          $link
        );

        $exportedFiles[] = $exportedTable;

        $flightInfo['bpTables'][] = [
          'pref' => $bpPrefixes[$i],
          'file' => $exportedTable['filename']
        ];
      }

      $eventTables = [
        ['table' => $flightInfo['guid'].'_ex', 'label' => 'exTables'],
        ['table' => $flightInfo['guid'].'_events', 'label' => 'eventsTable'],
        ['table' => $flightInfo['guid'].'_settlements', 'label' => 'settlementsTable'],
      ];

      foreach ($eventTables as $item) {
        $tableName = $item['table'];
        if ($this->connection()->isExist($tableName, null, $link)) {
          $exportedTable = $this->connection()->exportTable(
            $tableName,
            $exportedFileDir.DIRECTORY_SEPARATOR.$tableName.'_'.$fileGuid,
            $link
          );
          $exportedFiles[] = $exportedTable;
          $flightInfo[$item['label']] = $exportedTable['filename'];
        }
      }

      $exportedFileDesc = fopen($headerFile['root'], "w");
      fwrite ($exportedFileDesc , json_encode($flightInfo));
      fclose($exportedFileDesc);
    }

    $this->connection()->destroy($link);

    $zip = new ZipArchive;
    if ($zip->open($exportedFilePath, ZipArchive::CREATE) === TRUE) {
      for($i = 0; $i < count($exportedFiles); $i++) {
        $zip->addFile($exportedFiles[$i]['root'], $exportedFiles[$i]['filename']);
      }
      $zip->close();
    } else {
      error_log('Failed zipping flight.');
    }

    for ($i = 0; $i < count($exportedFiles); $i++) {
      if (file_exists($exportedFiles[$i]['root'])) {
        unlink($exportedFiles[$i]['root']);
      }
    }

    error_reporting(E_ALL);
    $zipUrl = $this->dic('runtimeManager')
      ->getExportedUrl($exportedFileName);

    $answ = [
      'status' => 'empty',
      'info' => 'No flights to export'
    ];

    if ($zipUrl) {
      $answ = [
        'status' => 'ok',
        'zipUrl' => $zipUrl
      ];
    }

    return json_encode($answ);
  }

  public function coordinatesAction($data)
  {
    if (!isset($data['id'])) {
      throw new BadRequestException(json_encode($data));
    }

    header("Content-Type: text/comma-separated-values; charset=utf-8");
    header("Content-Disposition: attachment; filename=coordinates.kml");  //File name extension was wrong
    header("Expires: 0");
    header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
    header("Cache-Control: private", false);

    $id = $data['id'];
    if (!is_int(intval($flightId))) {
      throw new Exception("Incorrect flightId passed into GetCoordinates FlightsController." . $flightId, 1);
    }

    $Fl = new Flight;
    $flight = $Fl->GetFlightInfo($flightId);
    $fdrId = intval($flightInfo['id_fdr']);
    unset($Fl);

    $apTableName = $flight['apTableName'];
    $bpTableName = $flight['bpTableName'];

    $fdr = new Fdr;
    $fdrInfo = $fdr->getFdrInfo($fdrId);
    unset($fdr);

    $kmlScript = $fdrInfo['kml_export_script'];
    $kmlScript = str_replace("[ap]", $apTableName, $kmlScript);
    $kmlScript = str_replace("[bp]", $bpTableName, $kmlScript);

    $c = new DataBaseConnector;
    $link = $c->Connect();

    $info = [];
    $averageLat = 0;
    $averageLong = 0;

    if (!$link->multi_query($kmlScript)) {
      //err log
      error_log("Impossible to execute multiquery: (" .
        $kmlScript . ") " . $link->error);
    }

    do
    {
      if ($res = $link->store_result())  {
        while($row = $res->fetch_array()) {
          $lat = $row['LAT'];
          $long = $row['LONG'];
          $h = $row['H'];

          $averageLat += $lat;
          $averageLong += $long;
          $averageLat /= 2;
          $averageLong /= 2;

          if ($h < 0) {
            $h = 10.00;
          }
          $h = round($h, 2);
          $info[] = [
            $long,
            $lat,
            $h,
          ];
        }

        $res->free();
      }
    } while ($link->more_results() && $link->next_result());

    $c->Disconnect();

    unset($c);

    $figPrRow = '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL
     .'<kml xmlns="http://www.opengis.net/kml/2.2"' . PHP_EOL
     .' xmlns:gx="http://www.google.com/kml/ext/2.2"> <!-- required when using gx-prefixed elements -->' . PHP_EOL
    .'<Placemark>' . PHP_EOL
      .'<name>gx:altitudeMode Example</name>' . PHP_EOL
      .'<LineString>' . PHP_EOL
      .'<extrude>1</extrude>' . PHP_EOL
      .'<gx:altitudeMode>absolute </gx:altitudeMode>' . PHP_EOL
      .'<coordinates>' . PHP_EOL;

    foreach ($info as $fields) {
      for($i = 0; $i < count($fields); $i++) {
        $figPrRow .= $fields[$i] . ",";
      }

      $figPrRow = substr($figPrRow, 0, -1);
      $figPrRow .= PHP_EOL;
    }

    $figPrRow .= '</coordinates>' . PHP_EOL
      .'</LineString>' . PHP_EOL
      .'</Placemark>' . PHP_EOL
      .'</kml>';

    unset($U);
    return $figPrRow;
  }

  public function processAction($id)
  {
    $flightId = intval($data['id']);

    $Fl = new Flight;
    $flightInfo = $Fl->GetFlightInfo($flightId);
    $apTableName = $flightInfo["apTableName"];
    $bpTableName = $flightInfo["bpTableName"];
    $excEventsTableName = $flightInfo["exTableName"];
    $startCopyTime = $flightInfo["startCopyTime"];
    $tableGuid = substr($apTableName, 0, 14);
    unset($Fl);

    $fdr = new Fdr;
    $fdrInfo = $fdr->getFdrInfo(intval($flightInfo["id_fdr"]));
    $excListTableName = $fdrInfo["excListTableName"];
    $apGradiTableName = $fdrInfo["gradiApTableName"];
    $bpGradiTableName = $fdrInfo["gradiBpTableName"];
    $stepLength = $fdrInfo["stepLength"];

    if ($excListTableName != "") {
       $excListTableName = $fdrInfo["excListTableName"];
       $apGradiTableName = $fdrInfo["gradiApTableName"];
       $bpGradiTableName = $fdrInfo["gradiBpTableName"];

       $FEx = new FlightException;
       $FEx->DropFlightExceptionTable($excEventsTableName);
       $flightExTableName = $FEx->CreateFlightExceptionTable($flightId, $tableGuid);
       //Get exc refParam list
       $excRefParamsList = $FEx->GetFlightExceptionRefParams($excListTableName);

       $exList = $FEx->GetFlightExceptionTable($excListTableName);

       //file can be accesed by ajax what can cause warning
       error_reporting(E_ALL ^ E_WARNING);
       set_time_limit (0);

       //perform proc be cached table
       for($i = 0; $i < count($exList); $i++)
       {
        $curExList = $exList[$i];
        $FEx->PerformProcessingByExceptions($curExList,
          $flightInfo, $flightExTableName,
          $apTableName, $bpTableName,
          $startCopyTime, $stepLength);
       }

       EventProcessingComponent::processEvents($flightId, new EventEmitter);

       error_reporting(E_ALL);
    }

    unset($fdr);
    return json_encode('ok');
  }

  public function changePathAction($id, $parentId)
  {
    $userId = $this->user()->getId();
    $sender = intval($id);
    $target = intval($parentId);

    $flight = $this->em()->find('Entity\Flight', $sender);

    if (!$flight) {
      throw new NotFoundException('flight not found. Id: '.$sender);
    }

    $flightToFolder = $this->em()
      ->getRepository('Entity\FlightToFolder')
      ->findBy(['flightId' => $sender, 'userId' => $userId]);

    if ($flightToFolder) {
      foreach ($flightToFolder as $item) {
        $this->em()->remove($item);
      }

      $flightToFolder = new \Entity\FlightToFolder;
      $flightToFolder->setFolderId($target);
      $flightToFolder->setUserId($userId);
      $flightToFolder->setFlight($flight);
      $this->em()->persist($flightToFolder);
    }

    $this->em()->flush();

    return json_encode([
      'id' => $sender,
      'parentId' => $target
    ]);
  }
}
