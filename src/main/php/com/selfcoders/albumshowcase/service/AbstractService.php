<?php
namespace com\selfcoders\albumshowcase\service;

use com\selfcoders\albumshowcase\DBConnection;

abstract class AbstractService
{
	protected $pdo;
	public $data;
	public $parameters;
	public $username;

	public function __construct()
	{
		$this->pdo = DBConnection::getConnection();
	}
}