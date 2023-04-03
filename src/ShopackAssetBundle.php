<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend;

class ShopackAssetBundle extends \yii\web\AssetBundle
{
	public $depends = [
		'yii\web\YiiAsset',
    'yii\bootstrap5\BootstrapAsset',
		'\kartik\dialog\DialogAsset',
		// '\kartik\form\ActiveFormAsset',
		// '\shopack\base\FontAwesomeAsset',
		// '\shopack\widgets\datetime\DatepickerAsset',
    FontAwesomeAsset::class,
		DynamicParamsFormAsset::class,
	];

	public function init()
	{
		$this->sourcePath = dirname(__FILE__) . '/assets/';
		parent::init();

		// if (Yii::$app->language === null)
		// 	$language = null;
		// else
		// 	$language = substr(Yii::$app->language, 0, 2);

		// $this->js[] = 'js/jquery.cookie.js';
		$this->js[] = (YII_ENV_DEV ? 'js/jquery.lazyload.js' : 'js/jquery.lazyload.min.js');
		// $this->js[] = (YII_ENV_DEV ? 'js/accounting.js' : 'js/accounting.min.js');
		$this->js[] = 'js/shopack.js';
		// $this->js[] = (YII_ENV_DEV ? 'js/headroom.js' : 'js/headroom.min.js');
		// $this->js[] = 'js/lanceng.js';
		$this->js[] = 'js/ajax-modal-popup.js';

		$this->css[] = "css/fonts.css";

/*
		$cols = Yii::$app->shopack->configmgmt->config('site', \shopack\site\common\models\SiteConfigModel::KEY_CARD_COLS);
		if ($cols == 4)
			$this->css[] = 'css/bs4card-4cols.css';
		else
			$this->css[] = 'css/bs4card-3cols.css';

		if ($language == "fa")
			$this->css[] = "css/bootstrap-rtl.css";
		$this->css[] = 'css/ie10-viewport-bug-workaround.css';
		$this->css[] = 'css/base.css';
		$this->css[] = 'css/headroom.css';

		// if (Yii::$app->isFrontend)
		// {
			$this->css[] = "css/site.css";
			if ($language == "fa")
				$this->css[] = "css/site-rtl.css";
		// }

		if (Yii::$app->isBackend)
		{
			$this->css[] = "css/backsite.css";
			if ($language == "fa")
				$this->css[] = "css/backsite-rtl.css";
		}

		// $this->publishOptions['beforeCopy'] = function ($from, $to) {
		// 	return preg_match('%(/|\\\\)(fonts|css)%', $from);
		// };
*/
		static::overrideSystemDialogs();
	}

	public static function overrideSystemDialogs()
	{
		\Yii::$app->view->registerJs('yii.confirm = function(message, ok, cancel) {
			krajeeDialog.confirm(message, function(result) {
				if (result) { !ok || ok(); } else { !cancel || cancel(); }
			});
		};');
	}

}
