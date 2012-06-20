<?php
class debugger
{
	private $notices;
	private $warnings;
	private $errors;
	
	function __construct()
	{
		$this->notices = array();
		$this->warnings = array();
		$this->errors = array();
		set_error_handler(array($this, "new_error"));
	}
	
	public function new_error($errno, $errstr)
	{
		switch($errno)
		{
			case 2:	
				$this->notice($errstr);
			case 8:
				$this->warning($errstr);
			default:
				$this->error($errstr);
			break;
		}
		return false;
	}
	
	
	private function notice($notice)
	{
		$this->notice[]=$notice;
	}
	
	private function warning($warning)
	{
		$this->warning[]=$warning;
	}
	
	private function error($error)
	{
		$this->error[]=$error;
	}
	
	public function getBug($level=0)
	{
		switch($level)
		{
			case 1:
				echo $this->getNotices();
			case 2:
				echo $this->getWarnings();
			default:
				echo $this->getErrors();
			break;		
		}
	}
	
	private function getNotices()
	{
		$notices="";
		foreach($this->notices as $value)
		{
			$notices.="Notice: ".$value."\n";
		}
		return $notices;
	}
	
	private function getWarnings()
	{
		$warnings="";
		foreach($this->warnings as $value)
		{
			$warnings.="Warning: ".$value."\n";
		}
		return $warnings;
	}
	
	private function getErrors()
	{
		$errors="";
		foreach($this->errors as $value)
		{
			$errors.="Error: ".$value."\n";
		}
		return $errors;
	}
}
?>