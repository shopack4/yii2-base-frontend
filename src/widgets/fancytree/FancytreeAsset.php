<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\fancytree;

use yii\web\AssetBundle;

class FancytreeAsset extends AssetBundle
{
	/**
	 * @var boolean
	 */
	public static $cookies = false;

	/**
	 * @var string The FancyTree skin.
	 */
	public static $skin = 'vista';

	/**
	 * @var array list of bundle class names that this bundle depends on.
	 */
	public $depends = [
		'yii\web\JqueryAsset',
	];

	/**
	 * @var string the directory that contains the source asset files for this asset bundle.
	 */
	public $sourcePath = '@bower';

	/**
	 * @inheritdoc
	 */
	public function registerAssetFiles($view): void
	{
		if (self::$cookies) {
			$this->js[] = 'js-cookie/src/js.cookie.js';
		}

		$this->js[] = 'fancytree/dist/jquery.fancytree-all-deps.min.js';

		$this->css[] = "fancytree/dist/skin-" . self::$skin . '/ui.fancytree'
			. (defined('YII_DEBUG') && YII_DEBUG ? '.css' : '.min.css');

		parent::registerAssetFiles($view);
	}

}
