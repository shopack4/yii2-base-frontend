<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend;

class DynamicParamsFormAsset extends \yii\web\AssetBundle
{
	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets/';
		parent::init();

		$this->js[] = 'js/dynaparamsform.js';
	}

}
