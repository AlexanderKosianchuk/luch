<?php

namespace Component;

use Exception;

class UserSettingsComponent extends BaseComponent
{
  private static $defaultSettings = [
    'printTableStep' => 1,
    'mainChartColor' => 'fff',
    'lineWidth' => 1,
    'flightShowAction' => 'events',
    'pointsMaxCount' => 16000,
  ];

  public static function getDefaultSettings()
  {
    return self::$defaultSettings;
  }

  public function getSettings($userId)
  {
    $settings = $this->em()->getRepository('Entity\UserSetting')
      ->findBy(['userId' => $userId]);

    $arr = [];
    foreach ($settings as $item) {
      $arr[$item->getName()] = $item->getValue();
    }

    if (count($arr) === 0) {
      return self::getDefaultSettings();
    }

    if (count($arr) !== self::$defaultSettings) {
      foreach (self::$defaultSettings as $key => $value) {
        if (!isset($arr[$key])) {
          $arr[$key] = $value;
        }
      }
    }

    return $arr;
  }

  public function getSettingValue($name, $userId = null)
  {
    if ($userId === null) {
      $userId = $this->user()->getId();
    }

    $setting = $this->em()->getRepository('Entity\UserSetting')
      ->findOneBy([
        'name' => $name,
        'userId' => $userId
      ]);

    if ($setting) {
      return $setting->getValue();
    }

    if (!$setting
      && isset(self::getDefaultSettings()[$name])
    ) {
      return self::getDefaultSettings()[$name];
    }

    throw new Exception("User setting unexist. Name: ".$name, 1);
  }


  public function updateSettings($settings, $userId = null)
  {
    if (!isset($userId)) {
      $userId = $this->user()->getId();
    }

    $userSettings = $this->em()->getRepository('Entity\UserSetting')
      ->findBy(['userId' => $userId]);

    foreach ($settings as $key => $val) {
      $existSetting = $this->em()->getRepository('Entity\UserSetting')
        ->findOneBy(['userId' => $userId, 'name' => $key]);

      if ($existSetting) {
        $existSetting->setValue($val);
        $existSetting->setUpdateDate(new \DateTime('now'));
        $this->em()->persist($existSetting);
      } else {
        $newSetting = new \Entity\UserSetting;
        $newSetting->setName($key);
        $newSetting->setValue($val);
        $newSetting->setUserId($userId);
        $newSetting->setCreateDate(new \DateTime('now'));
        $newSetting->setUpdateDate(new \DateTime('now'));
        $this->em()->persist($newSetting);
      }
    }

    $this->em()->flush();
  }
}
