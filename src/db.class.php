<?php
    /**
     * Database Controller
     * 
     * @package     MagicPHP Db
     * @author      André Ferreira <andrehrf@gmail.com>
     * @link        https://github.com/magicphp/db MagicPHP(tm)
     * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
     */

    class Db{
        /**
         * List of connections to databases
         * 
         * @access private
         * @var array 
         */
        protected $aConnections = array();
        
        /**
         * List of database logs
         * 
         * @access private
         * @var type 
         */
        private $aLogs = array();
        
        /**
         * Function to auto instance
         * 
         * @static
         * @access public
         * @return \self
         */
        public static function &CreateInstanceIfNotExists(){
            static $oInstance = null;

            if(!$oInstance instanceof self)
                $oInstance = new self();

            return $oInstance;
        } 
        
        /**
         * Magic function to return connections
         * 
         * @param string $sName Connection name
         * @return boolean
         */
        public static function __callStatic($sName, $aArgs = null){
            $oThis = self::CreateInstanceIfNotExists();
                                              
            if(array_key_exists($sName, $oThis->aConnections))
                return $oThis->aConnections[$sName]["resource"];
            else
                return false;               
        }
        
        /**
         * Function to create connections
         * 
         * @static
         * @access public
         * @param string $sName Connection name
         * @param string $sDrive Drive database to be used (mysql, postgresql, sqlite, mongodb)
         * @param string $sHostname Hostname or path to the database
         * @param string $sUsername User access to the database
         * @param string $sPassword Password to access the database
         * @param string $sSchema Schema to be used (for MySQL, PostgreSQL and MongoDB)
         * @param integer $iPort Door access to the database (for MySQL, PostgreSQL and MongoDB) 
         * @return void
         */
        public static function CreateConnection($sName, $sDrive = "mysql", $sHostname, $sUsername = null, $sPassword = null, $sSchema = null, $iPort = 3306){
            $oThis = self::CreateInstanceIfNotExists();
            Storage::SetArray("class.list", "db", Storage::Join("dir.core", "db" . SP));
            $oThis->aConnections[$sName] = array("drive" => $sDrive);
                        
            switch(strtolower($sDrive)){
                case "mysql": $oThis->aConnections[$sName]["resource"] = new MySQL($sHostname, $sUsername, $sPassword, $sSchema, $iPort); break;
                default: unset($oThis->aConnections[$sName]); break;
            }
        }
        
        /**
         * Function for setting the encoding of the database
         * 
         * @static
         * @access public
         * @param string $sName
         * @param string $sCharset
         * @return boolean
         */
        public static function SetCharset($sName, $sCharset = "UTF8"){
            $oThis = self::CreateInstanceIfNotExists();
                       
            if(array_key_exists($sName, $oThis->aConnections)){
                switch($oThis->aConnections[$sName]["drive"]){
                    case "mysql": return $oThis->aConnections[$sName]["resource"]->SetCharset($sCharset); break;
                    default: return false; break;
                }
            }
        }
        
        /** 
         * Function to mark Queries performed by the application
         *  
         * @static 
         * @access public 
         * @param string $sQuery SQL command sent to the bank
         * @param integer $iProcessStart Microtime the beginning of the process
         * @param integer $iProcessEnd Microtime the end of the process
         * @param string $sResult Result of request
         * @param integer $iResultAffected Results affected by the query
         * @return void 
         */
        public static function RecordLog($sQuery, $iProcessStart, $sResult, $iResultAffected){
            $oThis = self::CreateInstanceIfNotExists(); 
  
            $iResultAffected = intval($iResultAffected);
  
            if($iResultAffected <= 0) 
                $sResultAffected = "None"; 
            elseif($iResultAffected == 1) 
                $sResultAffected = $iResultAffected . " record"; 
            elseif($iResultAffected > 1) 
                $sResultAffected = $iResultAffected . " records"; 
  
            $fTimer = (microtime(true) - $iProcessStart); 
            $fTimer = number_format($fTimer, 4, ".", "")."s"; 
  
            $oThis->aLogs[] = array("query" => $sQuery, 
                                    "timer" => $fTimer,  
                                    "result" => $sResult, 
                                    "affected" => $sResultAffected); 
        }
        
        /**
         * Function to return the list of logs
         * 
         * @static
         * @access public
         * @return array
         */
        public static function ReturnLogs(){
            $oThis = self::CreateInstanceIfNotExists(); 
            return $oThis->aLogs;
        }
    }
