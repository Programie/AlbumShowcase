<?php
namespace com\selfcoders\albumshowcase;

class Track
{
	/**
	 * @var int
	 */
	public $id;
	/**
	 * @var int
	 */
	public $number;
	/**
	 * @var string
	 */
	public $title;
	/**
	 * @var string
	 */
	public $artist;
	/**
	 * @var int
	 */
	public $length;

	public function isValid()
	{
		return $this->number or $this->title or $this->artist or $this->length;
	}
}