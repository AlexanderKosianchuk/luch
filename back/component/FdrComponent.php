<?php

namespace Component;

use Exception;

class FdrComponent extends BaseComponent
{
    private static $_codeToTable = [];

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

    private function setAnalogParamsTable($fdrCode)
    {
        $link = $this->connection()->create('fdrs');
        $table = $this->FdrAnalogParam::getTable($link, $fdrCode);
        $this->connection()->destroy($link);

        if ($table === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrAnalogParam')
            ->setTableName($table);
    }

    private function setBinaryParamsTable($fdrCode)
    {
        $link = $this->connection()->create('fdrs');
        $table = $this->FdrBinaryParam::getTable($link, $fdrCode);
        $this->connection()->destroy($link);

        if ($table === null) {
            return null;
        }

        $this->em('fdrs')
            ->getClassMetadata('Entity\FdrBinaryParam')
            ->setTableName($table);
    }

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

        $this->setAnalogParamsTable($fdr->getCode());

        return $this->em('fdrs')
            ->getRepository('Entity\FdrAnalogParam')
            ->findAll('Entity\FdrAnalogParam');
    }

    public function getPrefixGroupedParams($fdrId)
    {
        $params = $this->getParams($fdrId);

        $grouped = [];
        foreach ($params as $param) {
            if (!isset($grouped[$param->getPrefix()])) {
                $grouped[$param->getPrefix()] = [];
            }

            $grouped[$param->getPrefix()][] = $param->get(true);
        }

        return $grouped;
    }

    public function getBinaryParams($fdrId)
    {
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $this->setBinaryParamsTable($fdr->getCode());

        return $this->em('fdrs')
            ->getRepository('Entity\FdrBinaryParam')
            ->findAll('Entity\FdrBinaryParam');
    }

    public function getPrefixGroupedBinaryParams($fdrId)
    {
        $params = $this->getBinaryParams($fdrId);

        $grouped = [];
        foreach ($params as $param) {
            if (!isset($grouped[$param->getPrefix()])) {
                $grouped[$param->getPrefix()] = [];
            }

            $grouped[$param->getPrefix()][] = $param->get(true);
        }

        return $grouped;
    }

    public function getAnalogPrefixes($fdrId)
    {
        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $this->setAnalogParamsTable($fdr->getCode());

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

        $this->setBinaryParamsTable($fdr->getCode());

        $params = $this->em('fdrs')
            ->getRepository('Entity\FdrBinaryParam')
            ->findAll('Entity\FdrBinaryParam');

        $prefixes = [];

        foreach ($params as $item) {
            $prefixes[$item->getPrefix()] = 0;
        }

        return array_keys($prefixes);
    }

    public function getPrefixFrequency($paramsCyclo)
    {
        $freq = [];
        foreach ($paramsCyclo as $prefix => $params) {
            foreach ($params as $param) {
                if (!isset($freq[$param['prefix']])) {
                    $freq[$param['prefix']] = [];
                }

                $freq[strval($param['prefix'])] = count($param['channel']);
            }
        }

        return $freq;
    }

    public function getCodeToTableArray($fdrId, $flightTable)
    {
        if (!is_int($fdrId)) {
            throw new Exception("Incorrect fdrId passed. Int is required. Passed: "
                . json_encode($fdrId), 1);
        }

        $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

        $this->setAnalogParamsTable($fdr->getCode());
        $this->setBinaryParamsTable($fdr->getCode());

        $params = $this->getParams($fdrId);
        $binary = $this->getBinaryParams($fdrId);

        if (count(self::$_codeToTable) > 0) {
            return self::$_codeToTable;
        }

        foreach ($params as $param) {
            self::$_codeToTable[$param->getCode()] = $flightTable
                .$this->FdrAnalogParam->getTablePrefix()
                .'_'.$param->getPrefix();
        }

        foreach ($binary as $param) {
            self::$_codeToTable[$param->getCode()] = $flightTable
                .$this->FdrBinaryParam->getTablePrefix()
                .'_'.$param->getPrefix();
        }

        return self::$_codeToTable;
    }
}
