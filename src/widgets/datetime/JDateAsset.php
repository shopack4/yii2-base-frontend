<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\datetime;

use yii\web\AssetBundle;

class JDateAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets/';
		parent::init();

//https://github.com/mer30hamid/jdate
		$this->js[] = 'js/calendar.js';
		$this->js[] = 'js/jdate' . (YII_ENV_DEV ? '-class' : '.min') . '.js';
	}

}
