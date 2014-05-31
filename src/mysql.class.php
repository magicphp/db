<?php
    /**
     * MySQL Drive
     * 
     * @package     MagicPHP Db
     * @author      André Ferreira <andrehrf@gmail.com>
     * @link        https://github.com/magicphp/db MagicPHP(tm)
     * @license     MIT License (http://www.opensource.org/licenses/mit-license.php)
     */

    class MySQL{
        /**
         * Connection object to the database
         * 
         * @access private
         * @var object 
         */
        protected $oConnection = null;
        
        /**
         * List of tables from the database
         * 
         * @access private
         * @var array 
         */
        protected $aTables = array();
        
        /**
         * Class constructor function
         * 
         * @access public
         * @param type $sHostname Hostname to the database
         * @param type $sUsername User access to the database
         * @param type $sPassword Password to access the database
         * @param type $sSchema Schema to be used
         * @param type $iPort Door access to the database
         */
        public function __construct($sHostname, $sUsername, $sPassword, $sSchema, $iPort = 3306){
            $this->oConnection = mysqli_connect($sHostname, $sUsername, $sPassword, $sSchema, $iPort);
        }
                
        /**
         * Magic function to return table of the database
         * 
         * @access public
         * @param string $sTableName Table name
         * @return \MySQLTable
         */
        public function __get($sTableName){            
            if(array_key_exists($sTableName, $this->aTables)){
                return $this->aTables[$sTableName];
            }
            else{
                $oTmpMysqlTable = new MySQLTable($this->oConnection, $sTableName);
                $this->aTables[$sTableName] = $oTmpMysqlTable;
                return $this->aTables[$sTableName];
            }            
        }
        
        /**
         * Function for setting the encoding of the database
         * 
         * @access public
         * @param string $sCharset
         * @return boolean
         */
        public function SetCharset($sCharset = "UTF8"){
            return mysqli_set_charset($this->oConnection, $sCharset);
        }
    }