<?php
namespace com\selfcoders\albumshowcase\service\exception;

class MissingDataException extends ServiceException
{
	public function __construct()
	{
		$this->code = "missing_data";
	}
}