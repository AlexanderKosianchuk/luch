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
    ];

    public function getSettings($userId)
    {
        $settings = $this->em()->getRepository('Entity\UserSetting')
            ->findBy(['userId' => $userId]);

        $arr = [];
        foreach ($settings as $item) {
            $arr[$item->getName()] = $item->getValue();
        }

        if (count($arr) === 0) {
            return self::$defaultSettings;
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
}
