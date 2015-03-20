<?php
namespace com\selfcoders\albumshowcase\service\exception;

class FileUploadException extends ServiceException
{
	public function __construct()
	{
		$this->code = "file_upload_failed";
	}
}