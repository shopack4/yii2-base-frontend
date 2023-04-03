<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;

class DatepickerRedblackThemeAsset extends AssetBundle
{
	public $sourcePath = '@bower/persian-datepicker/dist';
	public $css = [
		'css/theme/persian-datepicker-redblack.css',
	];
}
