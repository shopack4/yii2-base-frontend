<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime\classes;

class Jalali
{
	/**
	 * @var \DateTime
	 */
	protected $gregorian;

	/**
	 * @var int
	 */
	protected $year;

	/**
	 * @var int
	 */
	protected $month;

	/**
	 * @var int
	 */
	protected $day;

	/**
	 * @var int
	 */
	protected $weekDay;

	public function __construct($year = null, $month = null, $day = null)
	{
		$this->year  = $year;
		$this->month = $month;
		$this->day   = $day;
	}

	/**
	 * @param \DateTime $gregorian
	 * @return Jalali
	 */
	public function setGregorianDate(\DateTime $gregorian)
	{
		$this->gregorian = $gregorian;
		return $this;
	}

	/**
	 * Sets the jalali date for the further calculations and conversions.
	 * To provide a valid jalali date a YYYY MM DD formatted date should
	 * be passed to the $date param.
	 *
	 * @param string $date
	 * @param string $delimiter
	 * @throws \Exception In case an invalid date is provided
	 * @return Jalali
	 */
	public function setJalaliDate($date, $delimiter = '/')
	{
		$date = explode($delimiter, $date);

		if (count($date) != 3)
			throw new \Exception('Invalid jalali date provided.');

		foreach ($date as $datePart)
			if (!is_numeric($datePart) || strlen(trim($datePart)) < 1)
				throw new \Exception('Invalid jalali date provided.');

		$this->year  = $date[0];
		$this->month = $date[1];
		$this->day   = $date[2];
		return $this;
	}

	/**
	 * @param int $timestamp
	 * @throws DateException In case an invalid timestamp is provided.
	 * @return Jalali
	 */
	public function setTimestamp($timestamp)
	{
		if ((int) $timestamp > 0) {
			$this->gregorian = new \DateTime();
			$this->gregorian->setTimestamp((int) $timestamp);
			return $this;
		}

		throw new DateException('Invalid timestamp is provided.');
	}

	/**
	 * @param bool $recalculate
	 * @throws DateException
	 * @return Jalali
	 */
	public function getJalali($recalculate = false)
	{
		if (!$recalculate)
			if (empty($this->gregorian) && !empty($this->year) && !empty($this->month) && !empty($this->day))
				return $this;

		if (empty($this->gregorian))
			throw new DateException('No gregorian date has been provided yet.');

		$gYear  = (int) $this->gregorian->format('Y') - 1600;
		$gMonth = (int) $this->gregorian->format('m') - 1;
		$gDay   = (int) $this->gregorian->format('d') - 1;

		$gDayNumber = 365 * $gYear
			+ $this->divide($gYear + 3, 4)
			- $this->divide($gYear + 99, 100)
			+ $this->divide($gYear + 399, 400);

		for ($i = 0; $i < $gMonth; ++$i)
			$gDayNumber += JalaliFormat::$GREGORIAN_MONTH_DAYS[$i];

		if ($gMonth > 1 && (($gYear % 4 == 0 && $gYear % 100 != 0) || ($gYear % 400 == 0)))
			$gDayNumber++;

		$gDayNumber += $gDay;
		$jDayNumber = $gDayNumber - 79;
		$j_np       = $this->divide($jDayNumber, 12053);
		$jDayNumber = $jDayNumber % 12053;
		$jYear      = 979 + 33 * $j_np + 4 * $this->divide($jDayNumber, 1461);
		$jDayNumber %= 1461;

		if ($jDayNumber >= 366) {
			$jYear += $this->divide($jDayNumber - 1, 365);
			$jDayNumber = ($jDayNumber - 1) % 365;
		}

		for ($i = 0; $i < 11 && $jDayNumber >= JalaliFormat::$JALALI_MONTH_DAYS[$i]; ++$i)
			$jDayNumber -= JalaliFormat::$JALALI_MONTH_DAYS[$i];

		$jMonth = $i + 1;
		$jDay   = $jDayNumber + 1;

		$this->year    = $jYear;
		$this->month   = $jMonth;
		$this->day     = $jDay;
		$this->weekDay = $this->gregorian->format('w');
		return $this;
	}

	/**
	 * @return \DateTime
	 * @throws DateException
	 */
	public function getGregorian()
	{
		if (empty($this->year) || empty($this->month) || empty($this->day))
			throw new DateException('No jalali date has been provided yet.');

		$jy         = $this->year - 979;
		$jm         = $this->month - 1;
		$jd         = $this->day - 1;
		$jDayNumber = 365 * $jy + $this->divide($jy, 33) * 8 + $this->divide($jy % 33 + 3, 4);

		for ($i = 0; $i < $jm; ++$i)
			$jDayNumber += JalaliFormat::$JALALI_MONTH_DAYS [$i];

		$jDayNumber += $jd;
		$gDayNumber = $jDayNumber + 79;
		$gy         = 1600 + 400 * $this->divide($gDayNumber, 146097);
		$gDayNumber = $gDayNumber % 146097;
		$leap       = true;

		if ($gDayNumber >= 36525) {
			$gDayNumber--;
			$gy += 100 * $this->divide($gDayNumber, 36524);
			$gDayNumber = $gDayNumber % 36524;

			if ($gDayNumber >= 365)
				$gDayNumber++;
			else
				$leap = false;
		}

		$gy += 4 * $this->divide($gDayNumber, 1461);
		$gDayNumber %= 1461;

		if ($gDayNumber >= 366) {
			$leap = false;
			$gDayNumber--;
			$gy += $this->divide($gDayNumber, 365);
			$gDayNumber = $gDayNumber % 365;
		}

		for ($i = 0; $gDayNumber >= JalaliFormat::$GREGORIAN_MONTH_DAYS [$i] + ($i == 1 && $leap); $i++)
			$gDayNumber -= JalaliFormat::$GREGORIAN_MONTH_DAYS [$i] + ($i == 1 && $leap);

		$gm = $i + 1;
		$gd = $gDayNumber + 1;

		if ($gm < 10)
			$gm = "0" . $gm;

		if ($gd < 10)
			$gd = "0" . $gd;

		return new \DateTime("{$gy}/{$gm}/{$gd}");
	}


	/**
	 * This method returns the timestamp equivalent of Gregorian date
	 * If you want more accurate timestamp, you should use addHour and...
	 *
	 * @throws DateException
	 * @return string
	 */
	public function getTimestamp()
	{
		if (empty($this->year) || empty($this->month) || empty($this->day))
			throw new DateException('No jalali date has been provided yet.');

		return $this->getGregorian()->format('U');
	}

	/**
	 * This method accepts a combination of standard date format characters,
	 * including <s, i, h, H, g, G, d, j, w, n, m, F, y, Y>
	 *
	 * @param string $format
	 * @link http://php.net/manual/en/function.date.php
	 *
	 * @throws DateException
	 * @return string
	 */
	public function format($format)
	{
		if (empty($this->year) || empty($this->month) || empty($this->day))
			throw new DateException('No date is yet available to be formatted.');

		if (empty($this->weekDay))
			$this->weekDay = $this->getGregorian()->format('w');

		$format = str_replace('d', $this->day < 10 ? '0' . $this->day : $this->day, $format);
		$format = str_replace('j', $this->day, $format);
		$format = str_replace('w', JalaliFormat::$WEEK_DAYS[(int) $this->weekDay], $format);
		$format = str_replace('n', $this->month, $format);
		$format = str_replace('m', $this->month < 10 ? '0' . $this->month : $this->month, $format);
		$format = str_replace('F', JalaliFormat::$JALALI_MONTHS[(int) $this->month - 1], $format);
		$format = str_replace('y', substr($this->year, 2, 2), $format);
		$format = str_replace('Y', $this->year, $format);
		$format = str_replace('s', $this->gregorian->format('s'), $format);
		$format = str_replace('i', $this->gregorian->format('i'), $format);
		$format = str_replace('h', $this->gregorian->format('h'), $format);
		$H = $this->gregorian->format('H');
		$format = str_replace('H', $H, $format);
		$format = str_replace('g', $this->gregorian->format('g'), $format);
		$format = str_replace('G', $this->gregorian->format('G'), $format);

		$format = str_replace('a', intval($H) < 12 ? 'صبح' : 'عصر', $format);
		return $format;
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addYears($unit)
	{
		return $this->addDuration($unit, false, 'Y');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addMonths($unit)
	{
		return $this->addDuration($unit, false, 'M');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addDays($unit)
	{
		return $this->addDuration($unit, false, 'D');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addWeeks($unit)
	{
		return $this->addDuration($unit, false, 'W');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addHours($unit)
	{
		return $this->addDuration($unit, true, 'H');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addMinutes($unit)
	{
		return $this->addDuration($unit, true, 'M');
	}

	/**
	 * Passing negative values will subtract from the Jalali date.
	 *
	 * @param int $unit
	 * @return Jalali
	 */
	public function addSeconds($unit)
	{
		return $this->addDuration($unit, true, 'S');
	}

	/**
	 * @return int
	 */
	public function getYear()
	{
		return $this->year;
	}

	/**
	 * @return int
	 */
	public function getMonth()
	{
		return $this->month;
	}

	/**
	 * @return int
	 */
	public function getDay()
	{
		return $this->day;
	}

	/**
	 * @param int $weekDay
	 */
	public function setWeekDay($weekDay)
	{
		$this->weekDay = $weekDay;
	}

	/**
	 * @return int
	 */
	public function getWeekDay()
	{
		return $this->weekDay;
	}

	/**
	 * It is advised NOT to invoke this method directly. Instead this method should be utilized
	 * by calling the addDuration() method.
	 *
	 * In case it is calling this method seems to be the only option, please follow the provided link
	 * to find about how to provide a valid interval spec string.
	 * @link http://www.php.net/manual/en/dateinterval.construct.php
	 *
	 * @param string $intervalSpec
	 * @param bool   $add
	 * @return Jalali
	 * @throws DateException
	 */
	protected function add($intervalSpec, $add = true)
	{
		if (empty($this->gregorian) && (empty($this->year) || empty($this->month) || empty($this->day)))
			throw new DateException('No date is yet available to add units to it.');

		if (empty($this->gregorian))
			$this->gregorian = $this->getGregorian();

		$this->gregorian = (int) $add
			? $this->gregorian->add(new \DateInterval($intervalSpec))
			: $this->gregorian->sub(new \DateInterval($intervalSpec));

		$this->getJalali(true);
		return $this;
	}

	/**
	 * This method adds an amount of date/time to the desired Jalali date.
	 * Passing negative values to $unit will cause subtraction from the Jalali date.
	 *
	 * To find out about the valid list of designator characters please follow the provided link.
	 * @link http://www.php.net/manual/en/dateinterval.construct.php
	 *
	 * @param int    $unit
	 * @param bool   $isTime
	 * @param string $designator
	 * @return Jalali
	 */
	protected function addDuration($unit, $isTime, $designator)
	{
		$unit         = (int) $unit;
		$intervalSpec = 'P' . (((bool) $isTime) ? 'T' : '') . abs($unit) . $designator;
		return $this->add($intervalSpec, $unit > 0);
	}

	/**
	 * @param int $a
	 * @param int $b
	 * @return int
	 */
	private function divide($a, $b)
	{
		return (int) ($a / $b);
	}
}
