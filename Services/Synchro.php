<?php

namespace Itipart\SynchroBundle\Services;

use Itipart\SynchroBundle\Services\dao\Session;
use Itipart\SynchroBundle\Services\parsers\XmlParser;

class Synchro
{
    protected $conn;

    public function __construct(){
        $this->conn = Session::Instance();
    }

    public function getSeek($file, &$iSeek, &$iOk, &$iKo, &$conn, &$time_start)
    {
        $sql_seek = "SELECT Seek , Num_Ok ,Num_Ko ,Execution_Time FROM `demat_CronTraceability` WHERE File_name LIKE '" . $file . "'";
        $result = $this->conn->query($sql_seek);

        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $iSeek = $row['Seek'] + 1;
                $iOk = $row['Num_Ok'];
                $iKo = $row['Num_Ko'];
                $time_start = $time_start - $row['Execution_Time'];
            }
        }
    }

    //logging and change Seek tables
    public function ToLog($file, $lineNumber, $Line_to_log, $state, $parent_path, $file_name)
    {
        $parser = new XmlParser($parent_path, $file_name . ".xml");
        if ($parser->getLogTable() === 'TRUE') {
            $sql_log = "INSERT INTO `demat_Log`(`File_Name`, `Date_Of_treatement`, `row`, `Error_Or_Info`, `Stat`) VALUES ('" . $file . "',CURRENT_TIMESTAMP(),'" . ($lineNumber + 1) . "','" . addslashes($Line_to_log) . "','" . $state . "') ";
            if ($this->conn->query($sql_log) === False) {
                echo "Log sauvegard problem " . $conn->error . "\n";
            }
        }

        if ($parser->getLogFile() === 'TRUE') {
            $log_path = $parent_path . '/repos/logs/' . $file_name . '.log';
            $f = (file_exists($log_path)) ? fopen($log_path, "a+") : fopen($log_path, "w+");
            fwrite($f, "\t" . date('d-m-Y H:i:s') . "  " . $Line_to_log . "  " . ($lineNumber + 1));
            chmod($log_path, 0777);
        }
    }

    public function Seek($file, $lineNumber, $aLines, $iOk, $iKo, $time_execution)
    {

    }

    public function Archive($dir, $parent_path, $file_to_move)
    {
        copy($dir . "/" . $file_to_move, $parent_path . '/repos/archive/' . $file_to_move);
        unlink($dir . "/" . $file_to_move);
        chmod($parent_path . '/repos/archive/' . $file_to_move, 0777);
        $this->conn->close();
    }

    public function getConfig($parent_path, $iFileNameForUse, &$sql, $lineContent)
    {
        $values = " ON DUPLICATE KEY UPDATE ";
        $parser = new XmlParser($parent_path, $iFileNameForUse . ".xml");
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable['configs'];
        foreach ($aConfig as $Xml_lineNumber => $field) {
            $name = $aConfig[$Xml_lineNumber]['name'];
            $begin = $aConfig[$Xml_lineNumber]['begin'];
            $length = $aConfig[$Xml_lineNumber]['length'];
            $aOneData[$name] = preg_replace('/\t+/', '', addslashes(substr($lineContent, $begin, $length)));
            $values = $values . "`" . $name . "`='" . $aOneData[$name] . "',";
            $sql = $sql . "`" . $name . "`='" . $aOneData[$name] . "',";
        }
        $values = substr($values, 0, strlen($values) - 1);
        $sql = substr($sql, 0, strlen($sql) - 1) . $values . "\n";
    }

    public function deleteItem($parent_path, $iFileNameForUse, &$sql, $lineContent)
    {
        $parser = new XmlParser($parent_path, $iFileNameForUse . ".xml");
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable['configs'];
        $name = $aConfig[0]['name'];
        $begin = $aConfig[0]['begin'];
        $length = $aConfig[0]['length'];
        $sValue = preg_replace('/\t+/', '', addslashes(substr($lineContent, $begin, $length)));
        $sql = $sql . "`" . $name . "`='" . $sValue . "'";
    }

    public function read_file(&$dir, &$iFileNameForUse, &$aLines, &$file_to_move)
    {
        $result = false;
        if (is_dir($dir)) {
            $files2 = scandir($dir, 0);
            if (is_array($files2)) {
                foreach ($files2 as $file) {
                    if (strpos($file, $iFileNameForUse) > -1 && strpos($file, '~') != 1) {
                        $aLines = file($dir . "/" . $file);
                        $file_to_move = $file;
                        $result = true;
                        break;
                    } else {
                        $result = false;
                    }
                }
            } else {
                $result = false;
            }
        } else {
            $result = false;
        }
        return $result;
    }

    public function getLastCronDate($dDateCommande, $conn)
    {
        $sql_date = "SELECT MAX(Date_Last_Treatment) AS LAST_UPDATE ,  Execution_Time FROM `demat_CronTraceability` WHERE File_name LIKE 'CmdEnt%'";
        $result = $conn->query($sql_date);
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $to_return = new DateTime(date("Y-m-d H:i:s", strtotime(date('Y-m-d H:i:s', strtotime($row['LAST_UPDATE']))) - strtotime($row['Execution_Time'])));
            }
            return (new DateTime($dDateCommande) > $to_return);
        }
    }

    public function get_customer_data($lineContent, $parent_path, $iFileNameForUse, &$customerInfo, &$address)
    {
        $parser = new XmlParser($parent_path, $iFileNameForUse);
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable[configs];
        $customerInfo = array(
            'website_id' => 2,
            'store_id' => 1,
            'group_id' => 3,
            'add_date' => '2010-01-21 13:37:00',
            'birth_date' => '1980-01-01',
            'gender' => 1);

        foreach ($aConfig as $Xml_lineNumber => $field) {
            $name = $aConfig[$Xml_lineNumber][name];
            $begin = $aConfig[$Xml_lineNumber][begin];
            $length = $aConfig[$Xml_lineNumber][length];
            $customerInfo[$name] = preg_replace('/\t+/', '', addslashes(substr($lineContent, $begin, $length)));
        }

        $parser = new XmlParser($parent_path, "adress.xml");
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable[configs];
        $address = array();
        foreach ($aConfig as $Xml_lineNumber => $field) {
            $name = $aConfig[$Xml_lineNumber][name];
            $begin = $aConfig[$Xml_lineNumber][begin];
            $length = $aConfig[$Xml_lineNumber][length];
            $address[$name] = preg_replace('/\t+/', '', addslashes(substr($lineContent, $begin, $length)));
        }
    }

    public function preg_trim($subject)
    {
        $regex = "/\s*(\.*)\s*/s";
        if (preg_match($regex, $subject, $matches)) {
            $subject = $matches[1];
        }
        return $subject;
    }

    public function read_csv_file($parent_path, &$dir, &$iFileNameForUse, &$aLines, &$file_to_move)
    {
        $parser = new XmlParser($parent_path, "Customer_csv.xml");
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable[configs];
        $parser = new XmlParser($parent_path, "Customer_csv_adress.xml");
        $aTable = $parser->getParseConfig();
        $aConfig_adress = $aTable[configs];

        if (is_dir($dir)) {
            $files2 = scandir($dir, 0);
            if (is_array($files2)) {
                foreach ($files2 as $file) {
                    if (strpos($file, $iFileNameForUse) > -1 && strpos($file, '~') != 1) {
                        echo "\t" . $file . " is an " . $iFileNameForUse . " file \n";
                        $file_to_move = $file;
                        $row = 1;
                        if (($handle = fopen($dir . $file, "r"))) {
                            while (($data = fgetcsv($handle, 100000, ";"))) {
                                $num = count($data);
                                if ($row > 1 && count($data) > 1) {
                                    foreach ($aConfig as $Xml_lineNumber => $field) {
                                        $name = $aConfig[$Xml_lineNumber][name];
                                        $index = $aConfig[$Xml_lineNumber][begin];
                                        $aCust[$name] = $data[$index];
                                    }
                                    foreach ($aConfig_adress as $Xml_lineNumber => $field) {
                                        $name = $aConfig_adress[$Xml_lineNumber][name];
                                        $index = $aConfig_adress[$Xml_lineNumber][begin];
                                        $aCustAdress[$name] = $data[$index];
                                    }
                                    if (count($aCust) > 0) {
                                        $aLines[]["info"] = $aCust;
                                    }
                                    if (count($aCustAdress) > 0) {
                                        $aLines[]["adress"] = $aCustAdress;
                                    }
                                }
                                $row++;
                            }
                            fclose($handle);
                        }
                        break;
                    } else {
                        echo "\t " . $file . " Not an " . $iFileNameForUse . " file \n";
                    }
                }
            } else {
                echo "\033[31m =====> no files";
            }
        } else {
            echo "\033[31m =====> no dir";
        }
    }

    public function execute(){
        $time_start = microtime(true);
        $parent_path = getcwd();
        $parser = new XmlParser($parent_path, 'Article.xml');
        $iFileNameForUse = $parser->getToparse();
        $aTable = $parser->getParseConfig();
        $aConfig = $aTable['configs'];
        $dir = $parent_path . '/repos/in/';
        $aLines = array();
        if($this->read_file($dir, $iFileNameForUse, $aLines, $file))
        {
            if (is_array($aLines)) {
                echo "updating Costumer Table";
                $iOk = 0;
                $iKo = 0;
                $iSeek = 0;
                //$this->getSeek($file, $iSeek, $sql, $iKo, $conn, $time_start);
                for ($lineNumber = $iSeek; $lineNumber < count($aLines); $lineNumber++) {
                    $state = 'none';
                    $lineContent = $aLines[$lineNumber];
                    if ($lineContent[6] === 'M') {
                        $sql = "INSERT INTO `demat_Customer` SET";
                        $this->getConfig($parent_path, 'Customer', $sql, $lineContent);
                    } elseif ($lineContent[6] === 'A') {
                        $sql = "DELETE FROM `demat_Customer` WHERE ";
                        $this->deleteItem($parent_path, 'Customer', $sql, $lineContent);
                    }
                    if (($lineNumber % 1000) === 0) {
                        $time_end = microtime(true);
                        $time_execution = $time_end - $time_start;
                        $this->Seek($file, $lineNumber, $aLines, $iOk, $iKo, $this->conn, $time_execution);
                    }
                    //  $this->ToLog($file, $lineNumber, $Line_to_log, $state, $this->conn, $parent_path, 'Customer');
                }
                $time_end = microtime(true);
                $time_execution = $time_end - $time_start;
                $this->Seek($file, $lineNumber, $aLines, $iOk, $iKo, $this->conn, $time_execution);
            }
        }

    }
}