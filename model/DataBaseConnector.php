<?php
//UPDATE `bur4-1-07-01_bp` SET `color` = LEFT(MD5(UUID()), 6)

class DataBaseConnector
{

    private $host = 'localhost';
    private $user = 'dbUserdb5';
    private $pass = '124578dbUser';
    private $type = 'mysqli';
    private $dbName = 'db5';

    private $link;

    // Connection function
    public function Connect()
    {
        /*$this->link = new MySQLi($this->host, $this->user, $this->pass);
        $this->link->select_db($this->dbName);
        $this->link->set_charset("utf8");*/

        $this->link = mysqli_init();
        mysqli_options($this->link, MYSQLI_OPT_LOCAL_INFILE, true);
        mysqli_real_connect($this->link, $this->host, $this->user, $this->pass, $this->dbName);
        $this->link->select_db($this->dbName);
        $this->link->set_charset("utf8");

        if (mysqli_connect_errno())
        {
            return mysqli_connect_error();
        }
        else
        {
            return $this->link;
        }
    }

    public function TurnOffAutoCommit()
    {
        $this->link->autocommit(FALSE);
    }

    public function TurnOnAutoCommit()
    {
        $this->link->autocommit(TRUE);
    }

    public function Disconnect()
    {
        $this->link->close();
    }

    public function ExportTable($extTableName, $extFileName, $root)
    {
        $tableName = $extTableName;
        $fileName = $extFileName;
        $link = $this->Connect();

        $exportedFileName['dir'] = $root;
        $exportedFileName['root'] = $root.$fileName.".csv";
        $exportedFileName['filename'] = $fileName.".csv";

        $query = "SELECT * FROM `".$tableName."`"
            ." INTO OUTFILE '".$exportedFileName['root']."'"
            ." FIELDS TERMINATED BY ','"
            ." LINES TERMINATED BY ';';";

        $result = $link->query($query);
        $this->Disconnect();

        return $exportedFileName;
    }
}
