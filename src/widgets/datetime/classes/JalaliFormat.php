<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime\classes;

class JalaliFormat
{
	public static $JALALI_MONTHS = array(
		'فروردین',
		'اردیبهشت',
		'خرداد',
		'تیر',
		'مرداد',
		'شهریور',
		'مهر',
		'آبان',
		'آذر',
		'دی',
		'بهمن',
		'اسفند',
	);

	public static $WEEK_DAYS = array(
		'يكشنبه',
		'دوشنبه',
		'سه شنبه',
		'چهارشنبه',
		'پنجشنبه',
		'جمعه',
		'شنبه');

	public static $GREGORIAN_MONTH_DAYS = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
	public static $JALALI_MONTH_DAYS = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);

	/**
	 * Returns the zero-based index of the provided month
	 *
	 * @param string $month
	 * @return int|null
	 */
	public static function getMonthIndex($month)
	{
		for ($i = 0; $i < count(self::$JALALI_MONTHS); $i++)
			if (self::$JALALI_MONTHS[$i] === $month)
				return $i;

		return null;
	}
}
