<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;

class DateAsset extends AssetBundle
{
	// public $sourcePath = '@bower/persian-datepicker/lib';
	public $sourcePath = '@bower/persian-datepicker/assets';
	public $js = [
		'persian-date.min.js',
	];
	public $depends = [
		'yii\web\JqueryAsset',
		// 'yii\bootstrap\BootstrapPluginAsset',
		// 'yii\bootstrap\BootstrapAsset',
	];
}
