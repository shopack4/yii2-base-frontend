<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;

class DatepickerThemeAsset extends AssetBundle
{
	public $sourcePath = '@bower/persian-datepicker/dist';
	public $css = [
		// 'css/persian-datepicker-0.4.5.min.css',
		'css/persian-datepicker.min.css',
	];
}
