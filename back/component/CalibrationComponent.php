<?php

namespace Component;

use Exception;

class CalibrationComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Entity\CalibrationParam
     */
    private $CalibrationParam;

    public function getCalibrationParams ($fdrId, $id)
    {
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $link = $this->connection()->create('fdrs');
        $calibrationTable = $this->CalibrationParam::getTable($link, $fdr->getCode());
        $this->connection()->destroy($link);

        if ($calibrationTable === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\CalibrationParam')
            ->setTableName($calibrationTable);

        return $this->em('fdrs')
                ->getRepository('Entity\CalibrationParam')
                ->findBy(['calibrationId' => $id]);
    }

    /*public function getCalibration($id)
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
    }*/
}
