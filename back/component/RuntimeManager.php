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

    public static function getProgressFilePath($uploadingUid)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        return $runtimeDirectory . DIRECTORY_SEPARATOR . $uploadingUid . '.tmps';
    }

    public static function createProgressFile($uploadingUid)
    {
        $filePath = self::getProgressFilePath($uploadingUid);
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

        $storedFileName = uniqid() . '.tmpf';
        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $storedFileName;

        if (!file_exists($storedFilePath)) {
            move_uploaded_file ($fileName, $storedFilePath);
        }

        return $storedFileName;
    }

    public static function getUploadedFilePath($fileName)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $uploadedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . 'uploadedFlights';

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($storedFilePath)) {
            throw new Exception("Requested uploaded file unexist. Name ". $fileName, 1);
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
