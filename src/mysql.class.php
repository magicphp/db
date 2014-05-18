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
    
    /**
     * Class tables MySQL
     */
    class MySQLTable{
        /**
         * Connection object to the database
         * 
         * @access private
         * @var object 
         */
        private $oConnection = null;
        
        /**
         * Table name
         * 
         * @access private
         * @var string 
         */
        private $sTableName = null;
        
        /**
         *
         * @var type 
         */
        private $aQuery = array();
        
        /**
         * Class constructor function
         * 
         * @access public
         * @param object $oConnection
         * @param string $sTablename
         * @return void
         */
        public function __construct(&$oConnection, $sTablename){
            $this->oConnection = $oConnection;
            $this->sTableName = $sTablename;
        }
        
        /**
         * Magic function to return to class if the called function does not exist
         * 
         * @param string $sName
         * @return \MySQLTable
         */
        public function Cache(){
            return $this;
        }
        
        /**
         * Function to set the return fields
         * 
         * @return \MySQLTable
         */
        public function Select(){
            $aFields = func_get_args();
            $this->aQuery = array();
            
            if(is_array($aFields))
                $this->aQuery["fields"] = $aFields;
            
            return $this;
        }
        
        /**
         * Function to set the return filter
         * 
         * @return \MySQLTable
         */
        public function Filter($sField, $sValue, $sType = "="){                    
            switch(strtolower($sType)){
                case "in": $sValue = "(".$sValue.")"; break;
            }
            
            if(!array_key_exists("filters", $this->aQuery))
                $this->aQuery["filters"] = array();
            
            $this->aQuery["filters"][] = array("field" => $sField,
                                               "value" => $sValue,
                                               "type" => $sType);
            
            return $this;
        }
        
        /**
         * Function to set order
         * 
         * @param string $sField
         * @param string $sType
         * @return \MySQLTable
         */
        public function Order($sField, $sType = "ASC"){
            if(!array_key_exists("orders", $this->aQuery))
                $this->aQuery["orders"] = array();
            
            $this->aQuery["orders"][] = array("field" => $sField,
                                              "type" => $sType);
            
            return $this;
        }
        
        /**
         * Function to set groups
         * 
         * @param string $sField
         * @return \MySQLTable
         */
        public function Group($sField){
            if(!array_key_exists("groups", $this->aQuery))
                $this->aQuery["groups"] = array();
            
            $this->aQuery["groups"][] = array("field" => $sField);
            
            return $this;
        }
        
        /**
         * Function to set return limit
         * 
         * @param integer $iLimit
         * @return \MySQLTable
         */
        public function Limit($iLimit = 100){
            if(is_int($iLimit))
                $this->aQuery["limit"] = $iLimit;
            
            return $this;
        }
        
        /**
         * Function to format numbers
         * 
         * @param string $sField
         * @param integer $iDecimals
         * @param string $sDecPoint
         * @param string $sThousandSep
         * @return \MySQLTable
         */
        public function FormatNumber($sField, $iDecimals = 2, $sDecPoint = ',', $sThousandSep = '.'){
            if(!array_key_exists("formatnumber", $this->aQuery))
                $this->aQuery["formatnumber"] = array();
            
            $this->aQuery["formatnumber"][$sField] = array("decimals" => $iDecimals,
                                                           "decpoint" => $sDecPoint,
                                                           "thousandsep" => $sThousandSep);
            return $this;
        }
        
        /**
         * Function to format Date and Time
         * 
         * @param string $sField
         * @param string $sFormat
         * @return \MySQLTable
         */
        public function FormatDateTime($sField, $sFormat){
            if(!array_key_exists("formatdatetime", $this->aQuery))
                $this->aQuery["formatdatetime"] = array();
            
            $this->aQuery["formatdatetime"][$sField] = array("format" => $sFormat);
            return $this;
        }
        
        /**
         * Function to execute the query
         * 
         * @param function $fCallback
         * @return \MySQLTable 
         */
        public function Execute($fCallback){
            $sSQL = " SELECT";
            
            foreach($this->aQuery["fields"] as $sField)
                $sSQL .= " `".$sField."`, ";
            
            $sSQL = substr($sSQL, 0, -2);
            $sSQL .= " FROM `".$this->sTableName."` ";
            
            if(array_key_exists("filters", $this->aQuery)){
                $sSQL .= " WHERE ";
                                
                foreach($this->aQuery["filters"] as $aFilter)
                    $sSQL .= "`".$aFilter["field"]."` ".$aFilter["type"]." ? AND ";

                $sSQL = substr($sSQL, 0, -4); 
            }
            
            if(array_key_exists("groups", $this->aQuery)){
                $sSQL .= " GROUP BY ";
                
                foreach($this->aQuery["groups"] as $aOrder)
                    $sSQL .= "`".$aOrder["field"]."`, ";
                
                $sSQL = substr($sSQL, 0, -2); 
            }
            
            if(array_key_exists("orders", $this->aQuery)){
                $sSQL .= " ORDER BY ";
                
                foreach($this->aQuery["orders"] as $aOrder)
                    $sSQL .= "`".$aOrder["field"]."` ".$aOrder["type"].", ";
                
                $sSQL = substr($sSQL, 0, -2); 
            }
                       
            if(array_key_exists("limit", $this->aQuery))
                $sSQL .= " LIMIT ".intval($this->aQuery["limit"]);

            $oSTMT = $this->oConnection->stmt_init();
            $iMicrotime = microtime(true);
            
            $bBreak = (Events::Has("BeforeQuery")) ? Events::Call("BeforeQuery", array($sSQL, $fCallback)) : false;
                 
            if(!$bBreak){
                if(strnatcmp(phpversion(),'5.3') >= 0)
                    $oSTMT = mysqli_query($this->oConnection, $sSQL);
                else
                    $oSTMT->prepare($sSQL);

                if(array_key_exists("filters", $this->aQuery)){
                    $sTypes = "";
                    $aRefs = array();

                    foreach($this->aQuery["filters"] as $iKey => $aFilter){
                        $aRefs[$iKey] = $this->aQuery["filters"][$iKey]["value"];

                        switch(gettype($aFilter["value"])){
                            case "integer": $sTypes .= "i"; break;
                            case "double": $sTypes .= "d"; break;
                            case "string": default: $sTypes .= "s"; break;
                        }
                    }

                    /**
                     * @Bugfix PHP 5.3
                     */
                    if(strnatcmp(phpversion(),'5.3') >= 0){ 
                        foreach($aRefs as $mValue)
                            $sSQL = substr_replace($sSQL, (substr($mValue, 0, 1) != "(") ? "'".$mValue."'" : $mValue, strpos($sSQL, "?"), 1);
                        
                         //echo "<pre>".$sSQL; die();

                        $oSTMT = mysqli_query($this->oConnection, $sSQL);
                    }
                    else{
                        call_user_func_array(array($oSTMT, 'bind_param'), array_merge(array($sTypes), $aRefs));
                    }
                }  
            
                if($oSTMT instanceof mysqli_stmt){
                    if($oSTMT->execute()){
                        if(method_exists($oSTMT, "get_result"))
                            $mResult = $oSTMT->get_result();
                        else
                            $mResult = false;
                    }
                    else{
                        if($fCallback)
                            $fCallback(null, $oSTMT->error);
                    }
                }
                elseif($oSTMT instanceof mysqli_result){
                    $mResult = $oSTMT;
                }

                if($mResult instanceof mysqli_result){
                    $aResult = array();

                    while($aItem = $mResult->fetch_assoc()){
                        foreach($aItem as $sKey => $mValue)
                            if(is_null($mValue)){
                                $aItem[$sKey] = "";
                            }else{
                                // Format fields -  by Patrick
                                if (!is_null($this->aQuery['formatnumber'][$sKey])){
                                    $aItem[$sKey] = number_format(  $mValue, 
                                                                    $this->aQuery['formatnumber'][$sKey]['decimals'], 
                                                                    $this->aQuery['formatnumber'][$sKey]['decpoint'], 
                                                                    $this->aQuery['formatnumber'][$sKey]['thousandsep']);
                                }
                                if (!is_null($this->aQuery['formatdatetime'][$sKey])){
                                    $aItem[$sKey] = date(   $this->aQuery['formatdatetime'][$sKey]['format'],
                                                            strtotime($mValue));
                                }
                            }
                            

                        $aResult[] = $aItem;
                    }
                }
                else{
                    $aResult = null;                
                }        
                if(Events::Has("AfterQuery"))
                    Events::Call("AfterQuery", array($sSQL, $aResult));
            
                Db::RecordLog($sSQL, $iMicrotime, (($aResult != null) ? count($aResult) : 0), $this->Affected());

                if($fCallback)
                    $fCallback($aResult, mysqli_error($this->oConnection));
                
                @$oSTMT->close();
            } 
        }
        
        /**
         * Function to execute a SQL
         * 
         * @access public
         * @param string $sSQL SQL command to be sent
         * @param function $fCallback Treatment according to the result
         * @return \MySQLTable
         */
        public function Query($sSQL, $fCallback = false){
            $iMicrotime = microtime(true);
            
            $bBreak = (Events::Has("BeforeQuery")) ? Events::Call("BeforeQuery", array($sSQL, $fCallback)) : false;
            
            if(!$bBreak){
                $mResult = mysqli_query($this->oConnection, $sSQL);

                if($mResult instanceof mysqli_result){
                    $aResult = array();

                    while($aItem = $mResult->fetch_assoc()){
                        foreach($aItem as $sKey => $mValue)
                            if(is_null($mValue))
                                $aItem[$sKey] = "";

                        $aResult[] = $aItem;
                    }
                }
                else{
                    $aResult = null;                
                } 
                
                if(Events::Has("AfterQuery"))
                    Events::Call("AfterQuery", array($sSQL, $aResult));

                Db::RecordLog($sSQL, $iMicrotime, (($aResult != null) ? count($aResult) : 0), $this->Affected());
                                
                if($fCallback)
                    $fCallback($aResult, mysqli_error($this->oConnection));
            }
                        
            return $this;
        }
        
        /**
         * Function to insert record in the table
         * 
         * @access public
         * @param array $aData List of values ​​to be inserted (associative array)
         * @param function $fCallback Treatment according to the result
         * @return \MySQLTable
         */
        public function Insert($aData, $fCallback = false){
            if(count($aData) > 0){
                //$aData = Event::BeforeDatabaseInsert($aData); 
                
                $sFields = "";
                $sValues = "";

                foreach($aData as $mField => $mValue){
                    $sFields .= "`".$mField."`,";
                    $sValues .= "'".$mValue."',";
                }
                
                $sFields = substr($sFields, 0, -1);
                $sValues = substr($sValues, 0, -1);
                     
                $iMicrotime = microtime(true);
                $mResult = mysqli_query($this->oConnection, "INSERT INTO `".$this->sTableName."` (".$sFields.") VALUES (".$sValues.");");
                Db::RecordLog("INSERT INTO `".$this->sTableName."` (".$sFields.") VALUES (".$sValues.");", $iMicrotime, 0, $this->Affected());
                
                //Event::AfterDatabaseInsert($mResult, mysqli_error($this->oConnection));                
                
                if($fCallback)
                    $fCallback($mResult, mysqli_error($this->oConnection));
            }
            
            return $this;
        }
        
        /**
         * Function to update record in the database
         * 
         * @access public
         * @param array $aSets List of changes
         * @param array $aFilters Filter update
         * @param integer $iLimit Maximum of records that can be changed
         * @param function $fCallback Treatment according to the result
         * @return \MySQLTable
         */
        public function Update($aSets, $aFilters, $iLimit = 1, $fCallback = false){
            if(is_array($aSets) && is_array($aFilters)){
                $sSetsSQL = "";

                foreach($aSets as $mField => $mValue){
                    $mField = str_replace(array("&amp;amp;", "&amp;", "&quot;", "'"), array("&", "&", '"', "&#39;"), $mField);
                    $mValue = str_replace(array("&amp;amp;", "&amp;", "&quot;", "'"), array("&", "&", '"', "&#39;"), $mValue);
                    $sSetsSQL .= "`{$mField}` = '{$mValue}',";
                }

                $sSetsSQL = substr($sSetsSQL, 0, -1);

                //Filtros
                $sFilterSQL = "";

                foreach($aFilters as $mField => $mValue)
                    $sFilterSQL .= ((empty($sFilterSQL)) ? " WHERE " : " AND ") . "`{$mField}` = '{$mValue}'";

                //Limites
                $sLimitSQL = (is_int($iLimit)) ? " LIMIT ".$iLimit : "";

                if(!empty($sFilterSQL) && !empty($sSetsSQL)){
                    //die("UPDATE `".$this->sTableName."` SET {$sSetsSQL} {$sFilterSQL} {$sLimitSQL};");
                    $iMicrotime = microtime(true);
                    $mResult = mysqli_query($this->oConnection, "UPDATE `".$this->sTableName."` SET {$sSetsSQL} {$sFilterSQL} {$sLimitSQL};");
                    Db::RecordLog("UPDATE `".$this->sTableName."` SET {$sSetsSQL} {$sFilterSQL} {$sLimitSQL};", $iMicrotime, 0, $this->Affected());
                    
                    if($fCallback)
                        $fCallback($mResult, mysqli_error($this->oConnection));
                }
            }
            
            return $this;
        }
        
        /**
         * Function to unregister
         * 
         * @access public
         * @param array $aFilters Removing filter
         * @param integer $iLimit Maximum of records that can be removed
         * @param function $fCallback Treatment according to the result
         * @return \MySQLTable
         */
        public function Delete($aFilters, $iLimit = 1, $fCallback = false){
            if(is_array($aFilters)){
                //Filtros
                $sFilterSQL = "";

                foreach($aFilters as $mField => $mValue)
                    $sFilterSQL .= ((empty($sFilterSQL)) ? " WHERE " : " AND ") . "`{$mField}` = '{$mValue}'";
                        
                if(intval($iLimit) <= 0)
                    $iLimit = 1;
                
                if(!empty($sFilterSQL)){
                    $iMicrotime = microtime(true);
                    $mResult = mysqli_query($this->oConnection, "DELETE FROM `".$this->sTableName."` {$sFilterSQL} LIMIT {$iLimit};");
                    Db::RecordLog("DELETE FROM `".$this->sTableName."` {$sFilterSQL} LIMIT {$iLimit};", $iMicrotime, 0, $this->Affected());

                    if($fCallback)
                        $fCallback($mResult, mysqli_error($this->oConnection));
                }
            }
            
            return $this;
        }
        
        /**
         * Function to return how many records were affected
         * 
         * @access public
         * @return integer
         */
        public function Affected(){
            return mysqli_affected_rows($this->oConnection);
        }
        
        /**
         * Function to return last insert id
         * 
         * @access public
         * @return integer
         */
        public function InsertID(){
            return mysqli_insert_id($this->oConnection);
        }
    }