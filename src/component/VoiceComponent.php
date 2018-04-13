<?php

namespace Component;

use ComponentTraits\dynamicInjectedEntityTable;

use Exception;

class VoiceComponent extends BaseComponent
{
    use dynamicInjectedEntityTable;
    
  /**
   * @Inject
   * @var Entity\FdrVoice
   */
  private $FdrVoice;

  private $wavSingleChannelHeader = [
    0x52,0x49,0x46,0x46,0xEF,0xC7,0xBE,0x7B,0x57,0x41,0x56,0x45,0x66,
    0x6D,0x74,0x20,0x10,0x00,0x00,0x00,0x01,0x00,0x04,0x00,0x40,0x1F,
    0x00,0x00,0x00,0x7D,0x00,0x00,0x02,0x00,0x08,0x00,0x64,0x61,0x74,
    0x61,0x80,0xC6,0xBE,0x7B
  ];

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

    $table = $this->setEntityTable('fdrs', $this->FdrVoice, $fdr->getCode());

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

  public function getUploadingFilePath($file)
  {
    return $this->params()->folders->runtimeDirectory
      . DIRECTORY_SEPARATOR
      . $this->params()->folders->uploadingVoice
      . DIRECTORY_SEPARATOR
      . $file;
  }

  public function getUploadingFileName($uid, $code)
  {
    return $uid.'_'.$code.'.wav';
  }
}
