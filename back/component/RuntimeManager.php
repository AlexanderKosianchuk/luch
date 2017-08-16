<?php

namespace Component;

use Exception;

class RuntimeManager
{
    const UPLOADED_FLIGHTS_FOLDER = 'uploaded-flights';
    const EXPORTED_FOLDER = 'exported';
    const IMPORTED_FOLDER = 'imported';

    public static function getRuntimeFolder()
    {
        global $CONFIG;
        $runtimeDirectory = $CONFIG['params']['runtimeDirectory'];

        if (!is_dir($runtimeDirectory)) {
            mkdir($runtimeDirectory, 0755, true);
        }

        return $runtimeDirectory;
    }

    public static function getExportFolder()
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $exportedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . self::EXPORTED_FOLDER;

        if (!is_dir($exportedFilesDir)) {
            mkdir($exportedFilesDir, 0755, true);
        }

        return $exportedFilesDir;
    }

    public static function getImportFolder()
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $importFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . self::IMPORTED_FOLDER;

        if (!is_dir($importFilesDir)) {
            mkdir($importFilesDir, 0755, true);
        }

        return $importFilesDir;
    }

    public static function getExportedUrl($fileName)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $runtimeDirName = basename($runtimeDirectory);

        $exportedUrl = 'http';
        if (isset($_SERVER["HTTPS"]) &&  ($_SERVER["HTTPS"] == "on")) {
           $exportedUrl .= "s";
        }
        $exportedUrl .= "://";
        if ($_SERVER["SERVER_PORT"] != "80") {
           $exportedUrl .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"];
        }
        else
        {
           $exportedUrl .= $_SERVER["SERVER_NAME"];
        }

        return $exportedUrl.'/'.$runtimeDirName.'/'.self::EXPORTED_FOLDER.'/'.$fileName . '.zip';
    }

    public static function getExportedFilePath($fileName)
    {
        $exportedFileDir = self::getExportFolder();
        return $exportedFileDir . DIRECTORY_SEPARATOR . $fileName . '.zip';
    }

    public static function createExportedFile($fileName)
    {
        $filePath = self::getExportedFilePath($fileName);
        $fileNameDesc = fopen($filePath, "w");
        fclose($fileNameDesc);

        return $filePath;
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

    public static function storeFile($fileName, $folder, $uid = null)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $storedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . $folder;

        if (!is_dir($storedFilesDir)) {
            mkdir($storedFilesDir, 0755, true);
        }

        if ($uid === null) {
            $uid = uniqid() ;
        }

        $storedFileName = $uid . '.tmpsf';
        $storedFilePath = $storedFilesDir . DIRECTORY_SEPARATOR . $storedFileName;

        if (!file_exists($storedFilePath)) {
            move_uploaded_file ($fileName, $storedFilePath);
        }

        return $storedFilePath;
    }

    public static function storeUploadedFile($fileName, $uid = null)
    {
        return basename(self::storeFile($fileName, self::UPLOADED_FLIGHTS_FOLDER, $uid));
    }

    public static function getFilePathByIud($uid)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $uploadedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . self::UPLOADED_FLIGHTS_FOLDER;

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $uid . '.tmpf';

        return $storedFilePath;
    }

    public static function getUploadedFilePath($fileName)
    {
        $runtimeDirectory = self::getRuntimeFolder();
        $uploadedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . self::UPLOADED_FLIGHTS_FOLDER;

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($storedFilePath)) {
            throw new Exception("Requested uploaded file unexist. Name ". $fileName, 1);
        }

        return $storedFilePath;
    }

    public static function unlinkRuntimeFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
