<?php

namespace Component;

use Exception;

class FdrComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Entity\FdrAnalogParam
     */
    private $FdrAnalogParam;

    /**
     * @Inject
     * @var Entity\FdrBinaryParam
     */
    private $FdrBinaryParam;

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
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $link = $this->connection()->create('fdrs');
        $fdrAnalogParamTable = $this->FdrAnalogParam::getTable($link, $fdr->getCode());
        $this->connection()->destroy($link);

        if ($fdrAnalogParamTable === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrAnalogParam')
            ->setTableName($fdrAnalogParamTable);

        return $this->em('fdrs')
            ->getRepository('Entity\FdrAnalogParam')
            ->findAll('Entity\FdrAnalogParam');
    }

    public function getAnalogPrefixes($fdrId)
    {
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $link = $this->connection()->create('fdrs');
        $fdrAnalogTable = $this->FdrAnalogParam::getTable($link, $fdr->getCode());
        $this->connection()->destroy($link);

        if ($fdrAnalogTable === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrAnalogParam')
            ->setTableName($fdrAnalogTable);

         $params = $this->em('fdrs')
            ->getRepository('Entity\FdrAnalogParam')
            ->findAll('Entity\FdrAnalogParam');

        $prefixes = [];

        foreach ($params as $item) {
            $prefixes[$item->getPrefix()] = 0;
        }

        return array_keys($prefixes);
    }

    public function getBinaryPrefixes($fdrId)
    {
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $link = $this->connection()->create('fdrs');
        $fdrBinaryTable = $this->FdrBinaryParam::getTable($link, $fdr->getCode());
        $this->connection()->destroy($link);

        if ($fdrBinaryTable === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrBinaryParam')
            ->setTableName($fdrBinaryTable);

        $params = $this->em('fdrs')
            ->getRepository('Entity\FdrBinaryParam')
            ->findAll('Entity\FdrBinaryParam');

        $prefixes = [];

        foreach ($params as $item) {
            $prefixes[$item->getPrefix()] = 0;
        }

        return array_keys($prefixes);
    }
}
