<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime\classes;

class Util
{
	/**
	 * @param string      $date1
	 * @param string      $date2
	 * @param string|null $delimiter
	 * @param string      $designator
	 * @return int
	 */
	public static function diff($date1, $date2, $delimiter = '/', $designator = '%a')
	{
		$jalali = new Jalali();
		$date1  = $jalali->setJalaliDate($date1, $delimiter)->getGregorian();
		$date2  = $jalali->setJalaliDate($date2, $delimiter)->getGregorian();
		return (int) $date2->diff($date1)->format($designator);
	}

	/**
	 * @param int    $maxDiff
	 * @param string $date1
	 * @param string $date2
	 * @param string $delimiter
	 * @param string $designator
	 * @return bool
	 */
	public static function checkMaxDiff($maxDiff, $date1, $date2, $delimiter = '/', $designator = '%a')
	{
		return static::diff($date1, $date2, $delimiter, $designator) <= $maxDiff;
	}
}
