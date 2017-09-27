<?php

namespace Repository;

use Doctrine\ORM\EntityRepository;

use Entity\Fdr;
use Entity\CalibrationParam;
use Entity\FdrAnalogParam;

use Component\RealConnectionFactory as LinkFactory;

use Exception;

class CalibrationRepository extends EntityRepository
{
    public function getCalibratedParams($fdrId)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
                . json_encode($fdrId), 1);
        }

        $em = $this->getEntityManager();

        $fdr = $em->find('Entity\Fdr', $fdrId);

        if ($fdr === null) {
            return null;
        }

        $link = LinkFactory::create();
        $fdrAnalogParamTable = FdrAnalogParam::getTable($link, $fdr->getCode());
        LinkFactory::destroy($link);

        if ($fdrAnalogParamTable === null) {
            return null;
        }

        $em->getClassMetadata('Entity\FdrAnalogParam')->setTableName($fdrAnalogParamTable);

        $fdrAnalogParam = $em->getRepository('Entity\FdrAnalogParam')->findAll();

        $params = [];

        foreach($fdrAnalogParam as $item) {
            // 1 - calibration param type
            if ($item->isCalibrated()) {
                $params[] = $item->get();
            }
        }

        return $params;
    }

    public function getCalibration($id)
    {
        if (!is_int($id)) {
            throw new Exception("Incorrect calibration id passed. Int is required. Passed: "
                . json_encode($id), 1);
        }

        $em = $this->getEntityManager();

        $calibration = $em->find('Entity\Calibration', $id);
        $fdr = $calibration->getFdr();

        if ($fdr === null) {
            return null;
        }

        $link = LinkFactory::create();
        $calibrationParamTable = CalibrationParam::getTable($link, $fdr->getCode());
        $fdrAnalogParamTable = FdrAnalogParam::getTable($link, $fdr->getCode());
        LinkFactory::destroy($link);

        if (($fdrAnalogParamTable === null)
            || ($calibrationParamTable === null)
        ) {
            return null;
        }

        $em->getClassMetadata('Entity\CalibrationParam')->setTableName($calibrationParamTable);
        $em->getClassMetadata('Entity\FdrAnalogParam')->setTableName($fdrAnalogParamTable);

        $calibrationParams = $em->getRepository('Entity\CalibrationParam')->findBy([
            'calibrationId' => $id
        ]);

        $params = [];

        foreach($calibrationParams as $item) {
            $params[] = array_merge($item->get(), [
                    'description' => (!empty($item->getFdrAnalogParam())) ? $item->getFdrAnalogParam()->get() : null,
                ]
            );
        }

        return array_merge(
            $calibration->get(),
            [ 'params' => $params ]
        );
    }
}
