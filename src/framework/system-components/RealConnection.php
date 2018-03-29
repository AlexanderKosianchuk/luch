<?php

namespace Component;

use Exception;

class RealConnection
{
  private $_dbConfig;

  public function init($dbConfig)
  {
    $this->_dbConfig = $dbConfig;
  }

  public function create($db = "default")
  {
    if ($this->_dbConfig === null) {
      throw new Exception("RealConnectionFactory did not configure", 1);
    }

    $dbConfig = $this->_dbConfig["default"];

    if (isset($this->_dbConfig[$db])) {
      $dbConfig = $this->_dbConfig[$db];
    }

    $link = mysqli_init();
    mysqli_options($link, MYSQLI_OPT_LOCAL_INFILE, true);
    mysqli_real_connect($link,
      $dbConfig["host"],
      $dbConfig["user"],
      $dbConfig["password"],
      $dbConfig["dbname"]
    );

    $link->select_db($dbConfig["dbname"]);
    $link->set_charset($dbConfig["charset"]);

    if (mysqli_connect_errno()) {
      throw new Exception("Mysqli connection error " . mysqli_connect_error(), 1);
    }

    return $link;
  }

  public function destroy($link)
  {
    if (method_exists ($link, "close")) {
      $link->close();
    }
  }

  public function drop($table, $db = 'flights', $link = null)
  {
    if (!is_string($table)) {
      throw new Exception("Incorrect table name passed. String is required. Passed: "
        . json_encode($table), 1);
    }

    if (!$this->isExist($table, $db, $link)) {
      return;
    }

    $cleanUpNeed = false;
    if ($link === null) {
      $link = $this->create($db);
    }

    $table = mysqli_real_escape_string ($link, $table);
    $query = "DROP TABLE `".$table."`;";
    $stmt = $link->prepare($query);
    $stmt->execute();
    $stmt->close();

    if ($cleanUpNeed) {
      $this->destroy($link);
    }
  }

  public function isExist($table, $db = 'fdrs', $link = null)
  {
    if (!is_string($table)) {
      throw new Exception("Incorrect table name passed. String is required. Passed: "
        . json_encode($table), 1);
    }

    $cleanUpNeed = false;
    if ($link === null) {
      $cleanUpNeed = true;
      $link = $this->create($db);
    }

    $table = mysqli_real_escape_string ($link , $table);

    $query = "SHOW TABLES LIKE '".$table."';";
    $result = $link->query($query);

    $isExist = false;

    if ($result->fetch_array()) {
      $isExist = true;
    }

    if ($cleanUpNeed) {
      $this->destroy($link);
    }

    return $isExist;
  }

  public function loadFile($table, $file, $db = 'flights')
  {
    $link = $this->create($db);

    $table = mysqli_real_escape_string ($link , $table);

    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
      $file = str_replace('\\\\', '/', $file);
      $file = str_replace('\\', '/', $file);
    }

    $query = "LOAD DATA LOCAL INFILE '".$file."' "
       ."INTO TABLE `".$table."` "
       ."FIELDS TERMINATED BY ',' "
       ."LINES TERMINATED BY ';';";
    $link->query($query);

    $this->destroy($link);

    unset($c);
  }

  public function exportTable($tableName, $filePath, $link = null, $db = 'flights')
  {
    if ($link === null) {
      $link = $this->create($db);
    }

    $fileName = basename($filePath);
    $root = dirname($filePath);

    $exportedFileName = [
      'dir' => $root,
      'tmp' => sys_get_temp_dir().DIRECTORY_SEPARATOR.$fileName.".csv",
      'root' => $root.DIRECTORY_SEPARATOR.$fileName.".csv",
      'filename' => $fileName.".csv"
    ];

    /*GRANT FILE ON *.* TO 'dbUser'@'localhost'*/
    $query = "SELECT * FROM `".$tableName."`"
      ." INTO OUTFILE '".$exportedFileName['tmp']."'"
      ." FIELDS TERMINATED BY ','"
      ." LINES TERMINATED BY ';';";
    $result = $link->query($query);

    if ($link === null) {
      $this->destroy($link);
    }

    if (file_exists($exportedFileName['tmp'])) {
      try {
        $status = copy($exportedFileName['tmp'], $exportedFileName['root']);
        //unlink($exportedFileName['tmp']);
      } catch(Exception $e) { }
    }

    return $exportedFileName;
  }
}
