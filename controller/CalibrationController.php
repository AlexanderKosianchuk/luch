<?php

require_once(@$_SERVER['DOCUMENT_ROOT'] ."/includes.php");

class CalibrationController extends CController
{
   public $curPage = 'calibrationPage';

   public function getAvaliableFDRs($data) {
       $userId = intval($this->_user->userInfo['id']);
       $avaliablefdrIds = $this->_user->getAvailableFDRs($userId);

       $FDR = new Bru();
       $fdrInfoList = $FDR->GetFDRList($avaliablefdrIds);

       $fdrsWithCalibration = [];
       foreach ($fdrInfoList as $fdrInfo) {
           $calibrationParamsExist = $FDR->checkCalibrationParamsExist(intval($fdrInfo['id']));

           if ($calibrationParamsExist) {
               $fdrsWithCalibration[] = $fdrInfo;
           }
       }

       $fdrsAndCalibrations = [];
       $calibration = new Calibration();
       foreach ($fdrsWithCalibration as $fdrInfo) {
           $FDRcalibrations = $calibration->getCalibrations(intval($fdrInfo['id']), $userId);
           $calibratedParams = $FDR->getCalibratedParams(intval($fdrInfo['id']));

           $fdrsAndCalibrations[] = [
               'id' => $fdrInfo['id'],
               'name' => $fdrInfo['bruType'],
               'calibrations' => $FDRcalibrations,
               'calibratedParams' => $calibratedParams
           ];
       }

       echo json_encode($fdrsAndCalibrations);
   }

   public function saveCalibration($data)
   {
       $userId = intval($this->_user->userInfo['id']);
       $fdrId = intval($data['fdrId']);
       $calibrationsName = $data['name'];
       $calibrations = $data['calibrations'];
       echo 1;
   }

}
