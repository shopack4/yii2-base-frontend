<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend;

class FontAwesomeAsset extends \yii\web\AssetBundle
{
	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets/';
		parent::init();

		$this->css[] = "css/fontawesome.5.13.0.free.css";
	}

}
