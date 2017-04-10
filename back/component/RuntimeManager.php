<?php

namespace Component;

use Exception;

class RuntimeManager
{
    public static function getRuntimeFolder()
    {
        global $CONFIG;
        $runtimeDirectory = $CONFIG['params']['runtimeDirectory'];

        if (!is_dir($runtimeDirectory)) {
            mkdir($runtimeDirectory, 755, true);
        }

        return $runtimeDirectory;
    }

    public static function createProgressFile($fileName)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $filePath = $runtimeDirectory . DIRECTORY_SEPARATOR . $fileName;
        $fileNameDesc = fopen($filePath, "w");
        fclose($fileNameDesc);

        return $filePath;
    }

    public static function unlinkProgressFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
