<?php
namespace com\selfcoders\albumshowcase\service\exception;

class NotFoundException extends ServiceException
{
	public function __construct()
	{
		$this->code = "not_found";

		parent::__construct("The requested resource could not be found.");
	}
}