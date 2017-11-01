<?php

namespace Component;

use Exception;

class RuntimeManager extends BaseComponent
{
    public static $ext = '.t';

    private $_descriptors = [];

    public function getRuntimeFolder()
    {
        $runtimeDirectory = $this->params()->folders->runtimeDirectory;

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

    public function storeFile($fileName, $folder, $name = null, $dim = 'tmpsf')
    {
        if (!is_dir($folder)) {
            mkdir($folder, 0755, true);
        }

        if ($name === null) {
            $name = uniqid() ;
        }

        $storedFileName = $name . '.' . $dim;
        $storedFilePath = $folder . DIRECTORY_SEPARATOR . $storedFileName;
        $res = null;

        if (!file_exists($storedFilePath)) {
            $res = move_uploaded_file ($fileName, $storedFilePath);
        }

        return $storedFilePath;
    }

    public function storeFlight($fileName)
    {
        $storedFlightsDir = $this->params()->folders->storedFlights;

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
            $this->params()->folders->uploadedFlightsFolder,
            $uid
        ));
    }

    public function getFilePathByIud($uid)
    {
        $runtimeDirectory = $this->getRuntimeFolder();
        $uploadedFilesDir = $this->params()->folders->uploadedFlightsFolder;

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

    public function writeToRuntimeTemporaryFile(
        $category,
        $fileName,
        $data,
        $dataType = 'raw',
        $truncate = false,
        $writeType = 'w',
        $close = false
    ) {
        $file = $this->getTemporaryFileDesc(
            $category,
            $fileName,
            'open'
        );

        if (file_exists($file->path) && flock($file->desc, LOCK_EX)) {
            try {
                if ($truncate) {
                    ftruncate($file->desc, 0);
                }

                switch ($dataType) {
                    case 'json':
                        fwrite($file->desc, json_encode($data));
                        break;
                    case 'csv':
                        fputcsv($file->desc, $data);
                        break;
                    default:
                        fwrite($file->desc, $data);
                        break;
                }
            } catch (Exception $e) { }
            flock($file->desc, LOCK_UN);
        }

        if ($close) {
            if (get_resource_type($file->desc) === 'file') {
                fclose($file->desc);
                unset($this->_descriptors[$fileName]);
            }
        }
    }

    public function getTemporaryFileDesc(
        $category,
        $fileName,
        $task = 'noop',
        $writeType = 'w'
    ) {
        $file = $category
            .DIRECTORY_SEPARATOR
            .$fileName
            .$this::$ext;

        // this method is necessary runtime folder to be createExportedFile
        // if it is not exist
        $this->getRuntimeFolder();

        if (!is_dir($category)) {
            mkdir($category, 0755, true);
        }

        $desc = null;
        switch ($task) {
            case 'open':
                if (isset($this->_descriptors[$fileName])) {
                    $desc = $this->_descriptors[$fileName];
                    break;
                }

                if ((in_array($writeType, ['w', 'w+', 'a', 'x', 'x+']) || file_exists($file))
                    && (!isset($this->_descriptors[$fileName]) || get_resource_type($this->_descriptors[$fileName]) !== 'stream')
                ) {
                    $this->_descriptors[$fileName] = fopen($file, $writeType);
                }
                break;
            case 'close':
                if (isset($this->_descriptors[$fileName])
                    && (get_resource_type($this->_descriptors[$fileName]) === 'stream')
                ) {
                    fclose($this->_descriptors[$fileName]);
                    unset($this->_descriptors[$fileName]);
                }
                break;
            default:
                $desc = $this->_descriptors[$fileName];
                break;
        }

        return (object)[
            'path' => $file,
            'desc' => $desc
        ];
    }
}
