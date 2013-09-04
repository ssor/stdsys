<?php


class changeLog
{
	public $time;
	public $author;
	public $content;
	public function  __construct($time,$author,$content)
	{
		$this->time=$time;
		$this->author=$author;
		$this->content=$content;
	}
}

// for this test, simply print that the authentication was successfull
?>
