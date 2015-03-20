<?php
require_once __DIR__ . "/../config/config.php";

if (!@$argv[1])
{
	die("Usage: " . $argv[0] . " <config name>");
}

echo constant($argv[1]);