<?php

namespace Component;

use Exception;

class RuntimeManager extends BaseComponent
{
    public function getRuntimeFolder()
    {
        $runtimeDirectory = $this->params()->runtimeDirectory;

        if (!is_dir($runtimeDirectory)) {
            mkdir($runtimeDirectory, 0755, true);
        }

        return $runtimeDirectory;
    }

    public function getExportFolder()
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        $exportedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . $this->EXPORTED_FOLDER;

        if (!is_dir($exportedFilesDir)) {
            mkdir($exportedFilesDir, 0755, true);
        }

        return $exportedFilesDir;
    }

    public function getImportFolder()
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        $importFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . $this->IMPORTED_FOLDER;

        if (!is_dir($importFilesDir)) {
            mkdir($importFilesDir, 0755, true);
        }

        return $importFilesDir;
    }

    public function getExportedUrl($fileName)
    {
        $runtimeDirectory = $this->getRuntimeFolder();
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

        return $exportedUrl.'/'.$runtimeDirName.'/'.$this->EXPORTED_FOLDER.'/'.$fileName . '.zip';
    }

    public function getExportedFilePath($fileName)
    {
        $exportedFileDir = $this->getExportFolder();
        return $exportedFileDir . DIRECTORY_SEPARATOR . $fileName . '.zip';
    }

    public function createExportedFile($fileName)
    {
        $filePath = $this->getExportedFilePath($fileName);
        $fileNameDesc = fopen($filePath, "w");
        fclose($fileNameDesc);

        return $filePath;
    }

    public function getProgressFilePath($uploadingUid)
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        return $runtimeDirectory . DIRECTORY_SEPARATOR . $uploadingUid . '.tmps';
    }

    public function createProgressFile($uploadingUid)
    {
        $filePath = $this->getProgressFilePath($uploadingUid);
        $fileNameDesc = fopen($filePath, "w");
        fclose($fileNameDesc);

        return $filePath;
    }

    public function storeFile($fileName, $folder, $name = null, $dim = 'tmpsf')
    {
        $storedFilesDir = $folder;

        if (!is_dir($storedFilesDir)) {
            mkdir($storedFilesDir, 0755, true);
        }

        if ($name === null) {
            $name = uniqid() ;
        }

        $storedFileName = $name . '.' . $dim;
        $storedFilePath = $storedFilesDir . DIRECTORY_SEPARATOR . $storedFileName;
        $res = null;

        if (!file_exists($storedFilePath)) {
            $res = move_uploaded_file ($fileName, $storedFilePath);
        }

        return $storedFilePath;
    }

    public function storeFlight($fileName)
    {
        $storedFlightsDir = $this->params()->storedFlights;

        if (!is_dir($storedFlightsDir)) {
            mkdir($storedFlightsDir, 0755, true);
        }

        $storedFlightsDir .= DIRECTORY_SEPARATOR . date("Ymd");

        if (!is_dir($storedFlightsDir)) {
            mkdir($storedFlightsDir, 0755, true);
        }

        $name = basename($fileName, '.tmpsf') . '.flt';

        $storedFilePath = $storedFlightsDir . DIRECTORY_SEPARATOR . $name;
        $res = null;

        if (file_exists($fileName) && !file_exists($storedFilePath)) {
            $res = rename($fileName, $storedFilePath);
        }

        return $storedFilePath;
    }

    public function storeUploadedFile($fileName, $uid = null)
    {
        return basename($this->storeFile(
            $fileName,
            $this->params()->uploadedFlightsFolder,
            $uid
        ));
    }

    public function getFilePathByIud($uid)
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        $uploadedFilesDir = $this->params()->uploadedFlightsFolder;

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $uid . '.tmpsf';

        return $storedFilePath;
    }

    public function getUploadedFilePath($fileName)
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        $uploadedFilesDir = $runtimeDirectory . DIRECTORY_SEPARATOR . $this->UPLOADED_FLIGHTS_FOLDER;

        $storedFilePath = $uploadedFilesDir . DIRECTORY_SEPARATOR . $fileName;

        if (!file_exists($storedFilePath)) {
            throw new Exception("Requested uploaded file unexist. Name ". $fileName, 1);
        }

        return $storedFilePath;
    }

    public function unlinkRuntimeFile($filePath)
    {
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }
}
