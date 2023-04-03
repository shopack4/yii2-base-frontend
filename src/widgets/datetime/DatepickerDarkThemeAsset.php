<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;

class DatepickerDarkThemeAsset extends AssetBundle
{
	public $sourcePath = '@bower/persian-datepicker/dist';
	public $css = [
		'css/theme/persian-datepicker-dark.css',
	];
}
