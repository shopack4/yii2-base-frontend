<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime\classes;

class DateException extends \Exception
{
	public function __construct($message)
	{
		parent::__construct($message);
	}
}
