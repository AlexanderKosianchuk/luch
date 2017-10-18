<?php

namespace Component;

use Framework\Application as App;

use Entity\FdrAnalogParam;
use Entity\CalibrationParam;

use Exception;

class FdrComponent extends BaseComponent
{
    public function getFdrs()
    {
        $userId = $this->user()->getId();

        $fdrs = $this->em()
            ->getRepository('Entity\FdrToUser')
            ->getAvaliableFdrs($userId);

        $fdrsAndCalibrations = [];
        foreach ($fdrs as $fdr) {
            $fdrCalibrations = $this->em()->getRepository('Entity\Calibration')
                ->findBy([
                    'userId' => $userId,
                    'fdrId' => $fdr->getId()
                ]);

            $calibrations = [];
            foreach ($fdrCalibrations as $item) {
                $calibrations[] = $item->get();
            }

            $fdrsAndCalibrations[] = [
                'id' => $fdr->getId(),
                'name' => $fdr->getName(),
                'calibrations' => $calibrations
            ];
        }

        return $fdrsAndCalibrations;
    }

    public function getParams($fdrId)
    {
        $fdr = App::em()->find('Entity\Fdr', ['id' => $fdrId]);

        $link = App::connection()->create('fdrs');
        $fdrAnalogParamTable = FdrAnalogParam::getTable($link, $fdr->getCode());
        App::connection()->destroy($link);

        if ($fdrAnalogParamTable === null) {
            return null;
        }

        App::em('fdrs')
            ->getClassMetadata('Entity\FdrAnalogParam')
            ->setTableName($fdrAnalogParamTable);

        return App::em('fdrs')
            ->getRepository('Entity\FdrAnalogParam')
            ->findAll('Entity\FdrAnalogParam');
    }
}
