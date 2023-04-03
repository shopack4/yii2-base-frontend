<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\components;

// use yii\base\InvalidParamException;
// use shopack\base\helpers\Url;
use shopack\base\frontend\helpers\Html;
// use shopack\base\helpers\Geometry;
// use shopack\base\helpers\ArrayHelper;
// use shopack\filemanager\helpers\FileManager;
// use shopack\app\models\enuGender;
// use shopack\app\models\UserModel;

class Formatter extends \yii\i18n\Formatter
{
	private static $num_map = [
		// '0' => '۰',
		// '1' => '۱',
		// '2' => '۲',
		// '3' => '۳',
		// '4' => '۴',
		// '5' => '۵',
		// '6' => '۶',
		// '7' => '۷',
		// '8' => '۸',
		// '9' => '۹',
		',' => '٬'
	]
	// '.' => '٫',
	// '-' => '',
	;
	public function asPersian($value, $nullValue = null)
	{
		if ($value === null)
			return ($nullValue === null ? $this->nullDisplay : $nullValue);

		$_out = '';
		/*
		 * foreach ($value as $c)
		 * {
		 * if (isset(self::$num_map[$c]))
		 * //if (array_key_exists($c, self::num_map))
		 * $_out = self::$num_map[$c];
		 * else
		 * $_out = $c;
		 * }
		 */

		$keys = "/[" . implode("", array_keys(self::$num_map)) . "]/";
		$_out = preg_replace_callback($keys, function ($matches) {
			return self::$num_map[$matches[0]];
		}, $value);

		return $_out;
	}

	public function asText($value, $nullValue = null)
	{
		if ($value === null)
			return ($nullValue ? $nullValue : parent::asText($value));
// if (is_array($value)) die(Html::dump($value));
		return parent::asText($this->asPersian($value, $nullValue));
	}
	public function asPureText($value, $nullValue = null)
	{
		if ($value === null)
			return ($nullValue ? $nullValue : parent::asText($value));
		return parent::asText($value, $nullValue);
	}

	// public function asPhone($value, $nullValue = null)
	// {
	// 	if ($value === null)
	// 		return ($nullValue ? $nullValue : parent::asText($value));

	// 	return Html::tag('span', $value, [
	// 		'style' => 'display:inline-block; direction:ltr;',
	// 	]);

	// 	// return $this->asText($value);
	// }

	// public function asJson($value, $nullValue = null)
	// {
	// 	return Html::asJson($value, $nullValue);
	// }

	// public function asMultiLineText($value, $nullValue = null)
	// {
	// 	if ($value === null)
	// 		return ($nullValue === null ? $this->nullDisplay : $nullValue);
	// 	return str_replace("\n", "<br>", str_replace("\r", "", $this->asPersian($value)));
	// }

	// /**
	//  * $value : bool (yes/no) | string (icon name)
	//  * $plugin: fa (font awesome) | glyph
	//  */
	// public function asKZIcon($value, $options = [])
	// {
	// 	return Html::icon($value, $options);
	// }

	public function asPersianTimeString($value, $minuteBase=false, $afterValueChars=null)
	{
		if (!$minuteBase)
			$value *= 60;

		$minutes = ($value % 60);
		$hs = intval($value / 60);
		$hours = ($hs % 24);
		$days = intval($hs / 24);

		if (empty($afterValueChars))
			$afterValueChars = '';

		$ret = [];
		if ($days > 0)
			$ret[] = $this->asPersian($days) . $afterValueChars . " روز";
		if ($hours > 0)
			$ret[] = $this->asPersian($hours) . $afterValueChars . " ساعت";
		if ($minutes > 0)
			$ret[] = $this->asPersian($minutes) . $afterValueChars . " دقیقه";
		return implode(' و ', $ret);
	}
	// public function asWeight($value, $decimals = null, $options = [], $textOptions = [])
	// {
	// 	if ($value == null)
	// 		return static::asText(null);

	// 	$grams = ($value % 1000);
	// 	$kgs = intval($value / 1000);

	// 	if (($kgs > 0) && ($grams > 0))
	// 		return $this->asPersian($kgs) . '/' . $this->asPersian($grams) . ' کیلوگرم';

	// 	if ($kgs > 0)
	// 		return $this->asPersian($kgs) . ' کیلوگرم';

	// 	return $this->asPersian($grams) . ' گرم';
	// }
	public function asJalali($value, $format = null, $nullLabel = null)
	{
		if ($value === null || $value === 0) {
			if ($nullLabel === null)
				return $this->nullDisplay;
			return $nullLabel;
		}

		if ($format === null)
			$format = 'Y/m/d';

		$jdate = new \shopack\base\frontend\widgets\datetime\classes\Jalali();

		if (is_numeric($value)) {
			$timestamp = new \DateTime('@' . (int)$value); //, new DateTimeZone('UTC'));
			//Note that a UNIX timestamp is always in UTC by its definition
			$timestamp->setTimezone(new \DateTimeZone(date_default_timezone_get()));
		} elseif (is_string($value))
			$timestamp = new \DateTime($value);
		else
			$timestamp = $value;

		$jdate->setGregorianDate($timestamp);
		return $this->asPersian($jdate->getJalali()->format($format));
	}
	public function formatTime($value, $format='h:i:s a')
	{
		if (is_string($value))
			$value = new \DateTime($value);
		return $this->asText($value->format($format));
	}
	public function asJalaliWithTime($value, $nullLabel = null)
	{
		return $this->asJalali($value, 'Y/m/d - h:i:s a', $nullLabel);
	}
	// public function asKZImage($value, $path = '', $width = 100, $options = [])
	// {
	// 	$p = [];
	// 	if (is_array($path))
	// 	{
	// 		foreach ($path as $k => $v)
	// 		{
	// 			$p[] = $k;
	// 			$p[] = $v;
	// 		}
	// 	}
	// 	else
	// 		$p[] = $path;
	// 	$p[] = $value;
	// 	$path = "/" . implode("/", $p);

	// 	return FileManager::img($path, $width, $options);
	// }
	// public function asStars($value, $options = [])
	// {
	// 	return Html::stars($value, $options);
	// }
	// public function asUser($value, $format = null, $nullLabel = null)
	// {
	// 	return $this->asModel($value, "\shopack\app\models\UserModel", $format, $nullLabel);
	// }
	// public function asUserWithLink($value, $format = null, $nullLabel = null)
	// {
	// 	return Html::link($this->asUser($value, $format, $nullLabel), ['/app/user/view', 'id' => $value]);
	// }
	// /**
	//  * $model: string(model class)|model (model object)
	//  */
	// public function asModel($value, $model, $format = null, $nullLabel = null)
	// {
	// 	$modelObject = null;

	// 	if ($value !== null)
	// 	{
	// 		if (is_string($model))
	// 			// $model = new $model();
	// 			$modelObject = $model::findOne($value);
	// 		else
	// 			$modelObject = $model->findOne($value);
	// 	}

	// 	if ($modelObject == null)
	// 	{
	// 		if ($nullLabel === null)
	// 			return $this->nullDisplay;
	// 		return $this->asText($nullLabel);
	// 	}

	// 	if ($format !== null)
	// 		$format = urldecode($format);

	// 	if (is_object($modelObject) && method_exists($modelObject, 'toString'))
	// 		//TODO: persian number in url -> return $this->asText($modelObject->toString($format));
	// 		return $modelObject->toString($format);

	// 	return 'unknown';
	// }

	// public function asCurrencyWOUnit($value, $decimals = null, $options = [], $textOptions = [])
	// {
	// 	$options = array_replace_recursive($options, [
	// 		'unit' => false,
	// 	]);
	// 	return $this->asCurrency($value, $decimals, $options, $textOptions);
	// }
	// public function asCurrency($value, $decimals = null, $options = [], $textOptions = [])
	// {
	// 	if ($decimals === null)
	// 		$decimals = 0;

	// 	if (($options === null) || (count($options) == 0))
	// 		$options = [];
	// 	if (!isset($options['default']))
	// 		$options['default'] = 0;
	// 	if (!isset($options['negColor']))
	// 		$options['negColor'] = '#ff0000';

	// 	$default = ArrayHelper::remove($options, 'default', null);
	// 	if ($value === null)
	// 	{
	// 		if ($default !== null)
	// 			$value = $default;
	// 		else
	// 			$value = $this->nullDisplay;
	// 	}
	// 	if (!is_numeric($value))
	// 		return $value;

	// 	$unit = ArrayHelper::remove($options, 'unit', 'تومان');
	// 	if ($value == 0)
	// 		$unit = false;

	// 	$negColor = ArrayHelper::remove($options, 'negColor', false);
	// 	$color = ArrayHelper::remove($options, 'color', false);
	// 	$value = $this->normalizeNumericValue($value);
	// 	$neg = ($value < 0);
	// 	$value = abs($value);
	// 	$ret = [];
	// 	if ($neg && ($negColor !== false))
	// 		$ret[] = "<span style='color:{$negColor}'>";
	// 	else if (!$neg && ($color !== false))
	// 		$ret[] = "<span style='color:{$color}'>";
	// 	//$ret[] = $this->asPersian(parent::asDecimal($value, $decimals, $options, $textOptions)) . ($neg ? '-' : '');
	// 	$ret[] = parent::asDecimal($value, $decimals, $options, $textOptions) . ($neg ? '-' : '');

	// 	$suffix = ArrayHelper::remove($options, 'suffix', null);
	// 	if (!empty($suffix))
	// 		$ret[] = $suffix;

	// 	if ($unit)
	// 		$ret[] = ' ' . $unit;
	// 	if ($neg && ($negColor !== false))
	// 		$ret[] = "</span>";
	// 	else if (!$neg && ($color !== false))
	// 		$ret[] = "</span>";
	// 	return implode('', $ret);
	// }

	public function asEnum($value, $enum=null)
	{
		return $this->asText($enum::getLabel($value));
	}

	// public function asLink($value, $link, $idParam='id', $linkOptions=null, $text=null, $nullText=null)
	// {
	// 	if ($text === null)
	// 		$text = $value;

	// 	if ($value === null)
	// 		return ($nullText ? $nullText : parent::asText($value));

	// 	if (!is_array($link))
	// 		$link = [$link];
	// 	$link[$idParam] = $value;

	// 	return Html::link(
	// 		$text,
	// 		$link,
	// 		$linkOptions
	// 	);
	// }

	// public function asZeroDecimal($value, $decimals = null, $options = [], $textOptions = [])
	// {
	// 	return $this->asDecimal($value, $decimals, $options, $textOptions, '0');
	// }

	// public function asDecimal($value, $decimals = null, $options = [], $textOptions = [], $nullValue = null)
	// {
	// 	if ($value === null)
	// 		return $this->asText($nullValue ?? null);

	// 	return parent::asDecimal($value, $decimals, $options, $textOptions);
	// }

	// public function asDistance($distance, $format='{d}', $options=[])
	// {
	// 	return Geometry::formatDistance($distance, $format, $options);
	// }

	// public function asGeomap($value, $options=[], $showIfEmpty=true)
	// {
	// 	if (empty($value) && !$showIfEmpty)
	// 		return null;

	// 	$options = array_replace_recursive([
	// 			'center' => $value,
	// 			// 'zoom' => (empty(Yii::$app->user->identity->usrAddressCoordinates) ? '10' : '14'),
	// 			'style' => 'width:100%; height:75px; border:1px solid black;',
	// 		], $options);

	// 	return Html::geoMap(
	// 		[
	// 			[
	// 				// 'title' => $model->usrFullName,
	// 				// 'label' => 'L',
	// 				'position' => $value,
	// 			]
	// 		],
	// 		$options
	// 	);
	// }

}
