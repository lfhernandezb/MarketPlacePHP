<?php
/**
 * Class FrontController
 * Version 1.1.0
 * Author: Deepak Dutta, http://www.eocene.net
 * Unrestricted license, subject to no modifcations to the line above.
 * Please include any modifcation history.
 * 10/01/2002 Initial creation.
 * 03/17/2003 Added templateEngine member variable, changed rootURL, rootFolder, added appSettings
 * 04/13/2003 pswitek changed _getCommandClassFromRequest() to remove redirect for default command
 * It is a front controller. Instantiate it using a config file and run 
 * executeCommand method to handle any action command. It calls the execute method
 * of an appropriate command controller defined in the config file.
 *
 * PUBLIC PROPERTIES
 *	var $paths		All the paths
 *	var $rootFolder	root path of the web. e.g. D:/folder1/folder2
 *	var $rootURL	root URL of the web. e.g. http://www.myweb.com
 *  var $defaultCommand		A default command if no command found
 *	var $request	a Request object
 *	var $response	a Response object
 *	var $configFile	the config file
 *	var $config		an XmlDoc object of $configFile. This variable should be unset after using it to conserve memory
 *	var $receivers	an associative array of receivers node
 *  var $templates	all the templates for the current command class
 *	var $commandClass	current command class (including any .). Use &getCommandClass() for the name of the class
 *  var $dbInfo			associative array with data base information
 *  var $appSettings	associative array for appSettings node in config file
 *  var $plugs			All the plugs
 *  var $templateEngine The type of template engine (e.g. Smarty)
 *PUBLIC METHODS
 *	FrontController($configFile)
 *	executeCommand()				execute an action. Action is in fc.php?do=someCommand
 *  &getCommandClass()				returns the command class name only (without .)
 *	writeError($errorString)		instantiate the error object and exit
 *  import($aString)				performs include_once. $aString is in format aaa.bbb.ccc where aaa is in paths of config.xml and ccc is file ccc.php
 *	vd($varibale,$message=Default)	utility method to print any variable if global $isDebug=true
 *	setConfig()						sets $this->config which should be unset to preserve memory
 * 	&getLink()						return a database link in DBConnect class
 *  &getDBConnection()				return an instance of DBConnect class
 *  &getPlugContents(&$plugName)	executes plug class (plugName) and returns the content that is used by TemplateEngine
*/
//include bootstrap classes from core library
include_once("XmlDoc.php");
include_once("Error.php");
include_once("DBConnect.php");

class FrontController {	
	var $rootFolder;		
	var $rootURL;
	var $defaultCommand;
	var $paths;	
	var $request;
	var $response;
	var $configFile;
	var $config;
	var $receivers;
	var $templates;
	var $commandClass;
	var $dbInfo;
	var $appSettings;
	var $plugs;
	var $templateEngine;
	var $nivel;        		// MODIFICACION CURSOR 24/04/2003
	var $idSitio;			// MODIFICACION CURSOR 24/04/2003
	var $seccion;			// MODIFICACION CURSOR 26/04/2003

	/******************************************************************************
	PUBLIC METHODS
	*******************************************************************************/
	//Constructor takes the config file (with path)
	function FrontController($configFile){
		$this->configFile=$configFile;
		//Initialize Request and Response objects and set $this->request, $this->response
		$this->import("Request");
		$this->import("Response");
		$this->request=new Request();
		$this->response=new Response();
	}

	//Exceute an action. Action is in the form fc.php?do=someAction
	/****************************************************
	*All the action commands are in the format fc.php?do=someAction
	*A class SomeAction should exists and someAction should be
	*associated with this class in the config file.  Class SomeAction
	*is the command controller that can serve one or more commands.
	*****************************************************/
	function executeCommand(){

		$this->_setMembers();

		$this->import("BaseCommand");
		$cc=&$this->commandClass;
		$this->import("commands.".$cc);

		$cc=&$this->getCommandClass();
		$command=new $cc;
		$command->execute();
	}
	
	function &getCommandClass(){
		$cc=&$this->commandClass;
		$explodedCC=explode(".",$cc);
		$theSize=count($explodedCC);
		if($theSize==1) return $explodedCC[0];
		else return $explodedCC[$theSize-1];	
	}

	//insert an error and abort
	function writeError($errorMessage){
		$error=new Error($errorMessage);
	}

	//An utility function to print a variable. Global $isDebug should be true;
	function vd(&$variable,$label="Default label"){
		global $isDebug;
		if(!$isDebug) return;

		echo $label.":<pre>"; 
		if (!is_array($variable) && !is_object($variable)){ 
			$variable=htmlspecialchars($variable); 
		} 
		print_r($variable); 
		if ( is_array($variable) || is_object($variable)){ 
			reset($variable); 
		} 
		echo "</pre><hr>"; 
	}

	//sets $this-config. Should be unset after use to preserve memory
	function setConfig(){
		$config= new XmlDoc();
		$config->parse($this->configFile);
		if($config->isError())
			$this->writeError($config->error);
	
		$this->config=&$config;
	}

	var $_import=array();		//private
	function import($aString){
		if(isset($_import[$aString])) return;

		$theArray=explode(".",$aString);
		$theSize=count($theArray);

		if($theSize==1){
			$theFile=$aString.".php";
			include_once($theFile);
			$this->_import[$aString]=1;
			return;
		}
		
		$pathAlias=$theArray[0];
		if(!isset($this->paths[$pathAlias]))
			$this->writeError("Fatal: Alias for path $pathAlias not found in config.xml");
		
		$thePath=$this->paths[$pathAlias];
		for($i=1;$i<$theSize-1;$i++)
			$thePath=$thePath."/".$theArray[$i];
		
		$theFile=$thePath."/".$theArray[$theSize-1].".php";	
		include_once($theFile);
		$this->_import[$aString]=1;
	}

	function &getLink(){
		$dbc=&$this->getDBConnection();
		return $dbc->getLink();
	}

	var $_dbConnection;				//private	
	function &getDBConnection(){
		if(isset($this->_dbConnection))
			return $this->_dbConnection;
		
		$this->_dbConnection=new DBConnect();
		return $this->_dbConnection;	
	}
		
	function &getPlugContents(&$plugName){
		if(!isset($this->plugs[$plugName])) $this->writeError("No node found in config file for plug $plugName in plugCommands section");
		$pa=$this->plugs[$plugName];
		$className=$pa['class'];
		$this->import("plugCommands.".$className);
		$command=new $className;
		$command->execute();
		$successName=$pa['success'];
		if(empty($successName)){
			return $command->getContents();
		}
		else{	
			$plugPath=$this->paths['plugTemplates'];
			$successName=$plugPath.'/'.$successName;		
			return $command->getContentsFromBaseCommand($successName,$plugPath);
		}
	}
	/***********************************************************
	PRIVATE METHODS
	************************************************************/
	//Initializes the instance variables
	function _setMembers(){
		//set $this->config
		$this->setConfig();
		//Set $this->rootFolder
		$this->_setRootFolder();
		//Set $this->rootURL
		$this->_setRootURL();
		//$this->templateEngine
		$this->templateEngine=$this->_getChildNodeData($this->config->root,"templateEngine");
		//set $this->receivers
		$this->_setReceivers();
		//$this->defaultCommand
		$this->defaultCommand=$this->_getChildNodeData($this->config->root,"defaultCommand");
		if(empty($this->defaultCommand)) $this->writeError("Fatal: defaultCommand node not found in config file");			

		//Set $this->paths[]
		$this->_setPaths();
		//set $this->templates
		$this->_setTemplatesAndCommandClass();
		//set $this->dbinfo
		$this->_setDBInfo();
		//set $this->appSettings
		$this->_appSettings();
		//set plugs			
		$this->_setPlugs();
		/*** INICIO MODIFICACION CURSOR 24/04/2003 ***/
		$this->idSitio=$this->_getChildNodeData($this->config->root,"idSitio");
		/*** FIN MODIFICACION CURSOR ***/

	}
	//set the root folder
	function _setRootFolder(){
		$this->rootFolder=$this->_getChildNodeData($this->config->root,"rootFolder");
		if(empty($this->rootFolder)) $this->writeError("Fatal: rootFolder node not found in config file");
		if(is_dir($this->rootFolder)) return;
		global $configFile;
		$this->rootFolder=realpath(dirname($configFile) . $this->rootFolder);
	}
	//set the root url
	function _setRootURL(){
		$this->rootURL=$this->_getChildNodeData($this->config->root,"rootURL");
		if(empty($this->rootURL)) $this->writeError("Fatal: rootURL node not found in config file");	
		if($this->rootURL=="/"){
			$this->rootURL="http://" . $_SERVER['HTTP_HOST'];		
			return;
		}
		if(!strstr($this->rootURL,'http://'))
			$this->rootURL="http://" . $_SERVER['HTTP_HOST'] . $this->rootURL;		
	}
	//sets $this->receivers
	function _setReceivers(){
		$this->receivers=array();
		$node=&$this->config->root->getChild("receivers");		
		if($node == NULL) $this->writeError("Fatal: receivers node not found in config file");
		$n=$node->numChildren();		
		if($n == 0) $this->writeError("Fatal: Could not find any receiver");
		
		for($i=0;$i<$n;$i++){
			$child=&$node->children[$i];
			$charData=$child->charData;
			$pos=strpos($charData,"http://",0);
			if($pos===0)
				$this->receivers[$child->name]=$child->charData;
			else
				$this->receivers[$child->name]=$this->rootURL.'/'.$child->charData;
		}
	}
	//function convert children of a node to array
	//sets $this->paths
	function _setPaths(){
		$this->paths=array();
		$node=&$this->config->root->getChild("paths");
		if($node == NULL) $this->writeError("Fatal: paths node found in config file");
		$n=$node->numChildren();
		for($i=0;$i<$n;$i++){
			$child=&$node->children[$i];
			$this->paths[$child->name]=realpath($this->rootFolder.$child->charData);
		}
	}
	
	//sets $this->commandClass and $this->templates
	function _setTemplatesAndCommandClass(){		
		$this->templates=array();
		
		$this->accessLevel=0;	// MODIFICACION CURSOR 24/04/2003
		
		$commandString=$this->_getCommandClassFromRequest();
	
		$cinfonode=&$this->config->root->getChild("commands");
		
		if($cinfonode==NULL) $this->writeError("Fatal: commands node not found in config file");
		
		
		/*** INICIO MODIFICACION CURSOR 24/04/2003 ***/
		// Permite utilizar plantillas globales 
		
		$templatePath=$this->paths['templates'];
		
		$commandNode=&$cinfonode->getChild('global');
		if($commandNode != NULL) {

			$n=$commandNode->numChildren();
			for($i=0;$i<$n;$i++){
				$child=&$commandNode->children[$i];
				$nodeName=$child->name;			
				$templateFile=$child->charData;
				if(!empty($templateFile)){
					$fullPath=realpath($templatePath.'/'.$templateFile);
					$this->templates[$nodeName]=$fullPath;			
				}
			}
	
		}
		
		/*** FIN MODIFICACION CURSOR ***/
		
		$commandNode=&$cinfonode->getChild($commandString);
		
		//if($commandNode == NULL) $this->writeError("Fatal: Invalid command: $commandString. No information found in commands section");

		if($commandNode == NULL){
			$this->commandClass= 'ComandoInvalido';
			return;
		}
		
		
		$n=$commandNode->numChildren();
		for($i=0;$i<$n;$i++){
			$child=&$commandNode->children[$i];
			$nodeName=$child->name;	
			if($nodeName == "class"){
				$this->commandClass=$child->charData;		
			}

			/*** INICIO MODIFICACION CURSOR 24/04/2003 ***/
			// Obtenemos información del nivel de acceso del comando 

			elseif($nodeName == "nivel"){
				$this->nivel=$child->charData;		
			}

			/*** FIN MODIFICACION CURSOR ***/
		
			else{
				$templateFile=$child->charData;
				if(!empty($templateFile)){
					$fullPath=realpath($templatePath.'/'.$templateFile);
					$this->templates[$nodeName]=$fullPath;			
				}
			}
		}
		if(!isset($this->commandClass))
			$this->writeError("Fatal: Invalid command: $commandString (no class node)");
			
		if(empty($this->commandClass))
			$this->writeError("Fatal: Invalid command: $commandString (empty class node)");			
	}	

	function _getCommandClassFromRequest(){
		if(!isset($this->request->do)){
			$_GET['do'] = $this->defaultCommand;
			$this->request->do = $this->defaultCommand ;
			$this->request->param['do'] = $this->defaultCommand ;
		}

		$commandString=$this->request->do;

		if($commandString == "")
			$this->writeError("Fatal: empty command is invalid");

		return $commandString;
	}

	function _setDBInfo(){
		$this->db=array();
		$dbinfoNode=&$this->config->root->getChild("dbInfo");
		if($dbinfoNode == NULL) return;		
		$n=$dbinfoNode->numChildren();
		for($i=0;$i<$n;$i++){
			$adbinfo=&$dbinfoNode->children[$i];
			$this->dbInfo[$adbinfo->name]=$adbinfo->charData;
		}
	}
	
	function _appSettings(){
		$this->appSettings=array();
		$appSeetingNode=&$this->config->root->getChild("appSettings");
		if($appSeetingNode == NULL) return;
		$n=$appSeetingNode->numChildren();
		for($i=0;$i<$n;$i++){
			$aSetting=&$appSeetingNode->children[$i];
			$this->appSettings[$aSetting->name]=$aSetting->charData;
		}
	}	
	
	function _setPlugs(){
		$this->plugs=array();
		$plugsNode =&$this->config->root->getChild("plugCommands");
		if($plugsNode == null) return;
		$n=$plugsNode->numChildren();
		
		for($i=0;$i<$n;$i++){
			$aPlugNode=&$plugsNode->children[$i];
			$plugName=$aPlugNode->name;
			$classNode=&$aPlugNode->getChild("class");
			$className=$classNode->charData;
			$successNode=&$aPlugNode->getChild("success");
			$successName=$successNode->charData;
	
			$aPlug=array();
			$aPlug['class']=$className;
			$aPlug['success']=$successName;
			$this->plugs[$plugName]=$aPlug;
		}
	}	

	function _getChildNodeData(&$parentNode,$nodeName){
		$node=&$parentNode->getChild($nodeName);
		if($node != NULL)
			return $node->charData;
		return "";		
	}
}
?>
