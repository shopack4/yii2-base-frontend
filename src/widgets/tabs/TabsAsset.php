<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\tabs;

use yii\web\AssetBundle;

class TabsAsset extends AssetBundle
{
	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets';
		parent::init();
		$this->css[] = 'css/tabs.css';
	}
}
