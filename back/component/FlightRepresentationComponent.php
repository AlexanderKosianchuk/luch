<?php

namespace Component;

use Framework\Application as App;

use Exception;

class FlightRepresentationComponent extends BaseComponent
{
    /**
     * @Inject
     * @var Component\FrameComponent
     */
    private $frameComponent;

    /**
     * @Inject
     * @var Component\FdrComponent
     */
    private $fdrComponent;

    public function preview($fdrId, $file)
    {
        if (!file_exists($file)) {
            throw new Exception("Trying to preview unexisted file. Path: " . $file, 1);
        }

        $fdrId = intval($fdrId);
        $fdr = App::em()->find('Entity\Fdr', $fdrId);
        $previewParams = array_map('trim', explode(";", $fdr->getPreviewParams()));

        $params = $this->fdrComponent->getParams($fdrId);
        $previewCyclo = [];

        $groupedCyclo = [];
        foreach ($params as $param) {
            if (in_array($param->getCode(), $previewParams)) {
                if (!isset($grouped[$param->getPrefix()])) {
                    $groupedCyclo[$param->getPrefix()] = [];
                }

                $groupedCyclo[$param->getPrefix()][] = $param->get(true);
            }
        }

        $fileDesc = fopen($file, 'rb');
        $fileSize = filesize($file);

        $headerScr = $fdr->getHeaderScr();
        $headerLength = $fdr->getHeaderLength();
        $flightInfo = [];
        if (($headerScr !== '') && ($headerScr !== null)) {
            eval ($headerScr);
        }

        $frameLength = $fdr->getFrameLength();
        $frameSyncroCode = $fdr->getFrameSyncroCode();
        $startCopyTime = 0; // to be 0 hours
        if (isset($flightInfo['startCopyTime'])) {
            $startCopyTime = $flightInfo['startCopyTime'] * 1000;
        }

        $syncroWordOffset = $this->frameComponent->searchSyncroWord(
            $frameSyncroCode,
            $headerLength,
            $fileDesc,
            $fileSize
        );

        $frameNum = 0;
        $totalFrameNum = floor(($fileSize - $headerLength - $syncroWordOffset)  / $frameLength);

        fseek($fileDesc, $syncroWordOffset, SEEK_SET);
        $curOffset = $syncroWordOffset;

        $algHeap = [];
        $data = [];

        while (($frameNum < $totalFrameNum) && ($curOffset < $fileSize)) {
        //while(($frameNum < 30) && ($curOffset < $fileSize)) {
            $curOffset = ftell($fileDesc);
            $frame = stream_get_contents($fileDesc, $frameLength);
            $unpackedFrame = unpack("H*", $frame);

            if ($this->frameComponent->checkSyncroWord($frameSyncroCode, $unpackedFrame[1]) === true) {
                $splitedFrame = str_split(
                    $unpackedFrame[1],
                    $fdr->getWordLength() * 2
                );// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

                foreach ($groupedCyclo as $prefix => $cycloAp) {
                    $phisicsFrame = $this->frameComponent->convertFrameToPhisics(
                        $splitedFrame,
                        $startCopyTime,
                        $fdr->getStepLength(),
                        $frameNum,
                        $cycloAp,
                        $algHeap
                    );

                    $phisicsFrame = $phisicsFrame[0]; // 0 - ap 1 - bp

                    for($i = 0; $i < count($cycloAp); $i++) {
                        $data[$cycloAp[$i]['code']][] = array($phisicsFrame[1], $phisicsFrame[$i + 2]); //+2 because 0 - frameNum, 1 - time
                    }
                }

                $frameNum++;
            } else {
                $syncroWordOffset = $this->frameComponent->searchSyncroWord(
                    $frameSyncroCode,
                    $curOffset,
                    $fileDesc,
                    $fileSize
                );

                fseek($fileDesc, $syncroWordOffset, SEEK_SET);

                $framesLeft = floor(($fileSize - $syncroWordOffset)  / $frameLength);
                $totalFrameNum = $frameNum + $framesLeft;
            }
        }

        fclose($fileDesc);

        return $data;
    }
}
