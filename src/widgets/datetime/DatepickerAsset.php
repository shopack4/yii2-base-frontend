<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;
use shopack\base\frontend\widgets\datetime\DateAsset;

class DatepickerAsset extends AssetBundle
{
	// public $publishOptions = [
		// 'forceCopy' => YII_ENV_DEV,
	// ];

	public $sourcePath = '@bower/persian-datepicker/dist';
	public $css = [];
	public $depends = [
		DateAsset::class,
	];

	public function init()
	{
		// $this->sourcePath = dirname(dirname(__FILE__)) . '/assets'; //[css|js|img]'
		parent::init();
		// $this->js[] = (YII_DEBUG ? 'js/persian-datepicker-0.4.5.js' : 'js/persian-datepicker-0.4.5.min.js');
		$this->js[] = 'js/persian-datepicker' . (YII_ENV_DEV ? '' : '.min') . '.js';
	}

}
