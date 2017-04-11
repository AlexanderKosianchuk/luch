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
            mkdir($runtimeDirectory, 0755, true);
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

    public static function storeUploadedFile($fileName)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $uploadedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . 'uploadedFlights';

        if (!is_dir($uploadedFilesDir)) {
            mkdir($uploadedFilesDir, 0755, true);
        }

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . uniqid() . '.tmpsf';

        if (!file_exists($storedFilePath)) {
            move_uploaded_file ($fileName, $storedFilePath);
        }

        return $storedFilePath;
    }

    public static function unlinkProgressFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
