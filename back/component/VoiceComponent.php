<?php

namespace Component;

use Exception;

class VoiceComponent extends BaseComponent
{
  /**
   * @Inject
   * @var Entity\FdrVoice
   */
  private $FdrVoice;

  private $wavSingleChannelHeader = [
    0x52,0x49,0x46,0x46,0x7B,0x35,0xF6,0x04,0x57,0x41,0x56,0x45,0x66,
    0x6D,0x74,0x20,0x10,0x00,0x00,0x00,0x01,0x00,0x04,0x00,0x40,0x1F,
    0x00,0x00,0x00,0x7D,0x00,0x00,0x02,0x00,0x08,0x00,0x64,0x61,0x74,
    0x61,0x0C,0x34,0xF6,0x04
  ];

  private function setVoiceTable($fdrCode)
  {
    $link = $this->connection()->create('fdrs');
    $table = $this->FdrVoice::getTable($link, $fdrCode);
    $this->connection()->destroy($link);

    if ($table === null) {
      return null;
    }

    $this->em('fdrs')
      ->getClassMetadata('Entity\FdrVoice')
      ->setTableName($table);
  }

  public function getWavHeader()
  {
    $bindata = '';

    foreach ($this->wavSingleChannelHeader as $item) {
      $bindata .= pack('c', $item);
    }

    return $bindata;
  }

  public function getVoiceChannels($fdrId)
  {
    $fdr = $this->em()->find('Entity\Fdr', ['id' => $fdrId]);

    $table = $this->setVoiceTable($fdr->getCode());

    if ($table === null) {
      return [];
    }

    $params = $this->em('fdrs')
      ->getRepository('Entity\FdrVoice')
      ->findAll();

    $array = [];
    foreach ($params as $param) {
      $array[] = $param->get();
    }

    return $array;
  }

  public function processVoice(
    $frame,
    $voiceCyclo
  ) {
    $channels = [];

    foreach ($voiceCyclo as $item) {
      if (strlen($frame) < $item->offset + $item->dataLength) {
        continue;
      }

      $stream = substr($frame, $item->offset, $item->dataLength);
      $unpackedFrame = unpack("H*", $stream);
      $splitedFrame = str_split($unpackedFrame[1], $item->wordLength * 2);// div 2 because each byte 2 hex digits. $unpackedFrame[1] - dont know why [1], but hexdec($b[$i]) what we need

      foreach ($splitedFrame as $value) {
        if (!isset($channels[$item->code])) {
          $channels[$item->code] = [];
        }

        $channels[$item->code][] = intval($value) * $item->k;
      }
    }

    return $channels;
  }

  public function getUploadingFileName($uid, $code)
  {
    return $uid.'_'.$code.'_.wav';
  }
}
