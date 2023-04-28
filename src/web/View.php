<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\web;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\Modal;
use shopack\base\frontend\turbo\Frame;

class View extends \yii\web\View
{
	protected $metaKeywords = [];
	protected $metaDescription = [];
	// protected $positionalContents = [];
	protected $modals = [];

	// public $themeAssetClassName = 'ThemeAsset';
	// protected $_themeAsset = null;

	// public function getThemeAsset()
	// {
	// 	if (($this->_themeAsset == null) && !empty($this->theme->template))
	// 	{
	// 		$t = implode("\\", [
	// 			Yii::$app->isFrontend ? 'frontend' : 'backend',
	// 			'themes',
	// 			$this->theme->template,
	// 			'views',
	// 			'layouts',
	// 			$this->themeAssetClassName,
	// 		]);
	// 		$this->_themeAsset = $t::register($this);
	// 	}
	// 	return $this->_themeAsset;
	// }

	public function clear()
	{
		parent::clear();

		$this->metaKeywords = [];
		$this->metaDescription = [];
		// $this->positionalContents = [];
		$this->modals = [];
	}

	public function registerMetaKeywords($keywords)
	{
		$this->metaKeywords[] = $keywords;
	}

	public function registerMetaDescription($description)
	{
		$this->metaDescription[] = $description;
	}

	public function registerModal($id, $options=[], $pos=self::POS_END)
	{
		$modal = Modal::createNew($id, $options);
// var_dump($modal);
		echo $modal;

		if ($pos == self::POS_BEGIN)
			$this->modals = array_merge([$id => $modal], $this->modals);
		else
			$this->modals = array_merge($this->modals, [$id => $modal]);
	}

	/* override */
	protected function renderHeadHtml()
	{
		if (count($this->metaKeywords) > 0)
			$this->registerMetaTag([
				'name' => 'keywords',
				'content' => implode(',', $this->metaKeywords)
			]);

		if (count($this->metaDescription) > 0)
			$this->registerMetaTag([
				'name' => 'description',
				'content' => implode(" - ", $this->metaDescription)
			]);

		return parent::renderHeadHtml();
	}

	public function renderModal($viewFile, $params = [], $context = null)
	{
		if (!isset($params['renderModal']))
			$params['renderModal'] = true;
		return $this->render($viewFile, $params, $context);
	}

	public function renderAjaxModal($viewFile, $params = [], $context = null)
	{
		if (!isset($params['renderModal']))
			$params['renderModal'] = true;
		$params['title'] = false;
		$params['isAjax'] = true;
		return $this->renderAjax($viewFile, $params, $context);
	}
	protected $isInRenderAjaxLegacy = false;
	protected $isInRenderAjax = false;
	public function renderAjaxLegacy($view, $params = [], $context = null)
	{
		$this->isInRenderAjaxLegacy = true;
		try
		{
			return parent::renderAjax($view, $params, $context);
		}
		catch(\Exception $exp)
		{
			throw $exp;
		}
		finally
		{
			$this->isInRenderAjaxLegacy = false;
		}
	}
	public function renderAjax($view, $params = [], $context = null)
	{
		$this->isInRenderAjax = true;
		try
		{
			// \kartik\grid\ExpandRowColumnAsset::register($this);

			$ret = parent::renderAjax($view, $params, $context);
			// $ret .= "<script>(function ($) { initExpandRowColumn(); });</script>";
			// $ret .= "<script>initExpandRowColumn();</script>";

			return $ret;
		}
		catch(\Exception $exp)
		{
			throw $exp;
		}
		finally
		{
			$this->isInRenderAjax = false;
		}
	}

	public function renderFile($viewFile, $params = [], $context = null)
	{
		$profile = YII_DEBUG;
		$profile and Yii::beginProfile($viewFile, __METHOD__);

		try {
			$output = $this->internalRenderFile($viewFile, $params, $context);

			$profile and Yii::endProfile($viewFile, __METHOD__);

			return $output;
		} catch (\Throwable $th) {
			$profile and Yii::endProfile($viewFile, __METHOD__);
			throw $th;
		}
	}

	public function internalRenderFile($viewFile, $params = [], $context = null)
	{
		$renderModal = ArrayHelper::remove($params, 'renderModal', false);
		$isAjax = ArrayHelper::remove($params, 'isAjax', false);
		$title = ArrayHelper::remove($params, 'title', null);

		$output = parent::renderFile($viewFile, $params, $context);

		if (!$renderModal)
			return $output;

		//parts
		$startPart = '';
		$endPart = '';
		$headerPart = '';
		$bodyPart = '';
		$footerPart = '';

		if (!$isAjax) {
			if ($title === null)
				$title = Html::tag('h1', Html::encode($this->title), ['class' => 'modal-title', 'id' => 'myModalLabel']);
			if ($title !== false)
				$headerPart = "<div class='modal-header'>{$title}</div>";
		}

		//find begin form tag
		$posBeginForm = stripos($output, '<form ');
		if ($posBeginForm === false)
			return Html::div($output, ['class' => 'modal-body']);
		else {
			$posBeginForm = strpos($output, '>', $posBeginForm);
			if ($posBeginForm === false) //Error
				return Html::div($output, ['class' => 'modal-body']);
			++$posBeginForm;

			$startPart = substr($output, 0, $posBeginForm);
			$output = substr($output, $posBeginForm);
		}

		//find end form tag
		$posEndForm = stripos($output, '</form>');
		if ($posEndForm === false) //Error
			return Html::div($output, ['class' => 'modal-body']);
		$endPart = substr($output, $posEndForm);
		$output = substr($output, 0, $posEndForm);

		//find form-footer
		// $formFooterClass = 'form-group form-footer';
		$formFooterClass = 'card-footer';
		$posFormFooter = strpos($output, '<div class="' . $formFooterClass . '">');
		if ($posFormFooter === false)
			$posFormFooter = strpos($output, "<div class='{$formFooterClass}'>");
		if ($posFormFooter !== false) {//footer part exists
			if ($isAjax)
				$bodyPart = Html::div(substr($output, 0, $posFormFooter), ['class' => 'modal-body', 'id' => 'modalContent']);
			else
				$bodyPart = Html::div(substr($output, 0, $posFormFooter), ['class' => 'modal-body']);
			// $footerPart = Html::div(substr($output, $posFormFooter), ['class' => 'modal-footer']);
			$footerPart = str_replace($formFooterClass, 'modal-footer', substr($output, $posFormFooter));
		}
		else
			$bodyPart = Html::div($output, ['class' => 'modal-body']);
// die(var_dump($startPart));
// die(var_dump($endPart));
// die(var_dump($headerPart));
// die(var_dump($bodyPart));
// die(var_dump($footerPart));

		if ($isAjax)
		{
			$output = "
				{$startPart}
				{$bodyPart}
				{$footerPart}
				{$endPart}
			";
		}
		else
		{
			$output = "
				{$startPart}
				<div class='modal-content'>
					{$headerPart}
					{$bodyPart}
					{$footerPart}
				</div>
				{$endPart}
			";
		}
// die(var_dump($output));

		return $output;
	}

	public function beginPage()
	{
		/*
		if (!YII_ENV_DEV)
		{
			$verifykey = Yii::$app->shopack->configmgmt->config('site',
				Yii::$app->isFrontend
					? SiteConfigModel::KEY_GOOGLE_SITE_VERIFY_KEY_FE
					: SiteConfigModel::KEY_GOOGLE_SITE_VERIFY_KEY_BE
			);
			if (!empty($verifykey))
			{
				$this->registerMetaTag([
					'name' => 'google-site-verification',
					'content' => $verifykey,
				]);
			}
		}
		*/

		parent::beginPage();
	}

	public function head()
	{
		parent::head();

		/*
		if (!YII_ENV_DEV)
		{
			if (Yii::$app->isFrontend)
			{
				$tag = Yii::$app->shopack->configmgmt->config('site', SiteConfigModel::KEY_YEKTANET_TAG);
				if (!empty($tag))
				{
					echo <<<TAG
<!-- Yektanet Analytics -->
<script>
	!function (t, e, n) {
		t.yektanetAnalyticsObject = n, t[n] = t[n] || function () {
			t[n].q.push(arguments)
		}, t[n].q = t[n].q || [];
		var a = new Date, r = a.getFullYear().toString() + "0" + a.getMonth() + "0" + a.getDate() + "0" + a.getHours(),
			c = e.getElementsByTagName("script")[0], s = e.createElement("script");
		s.id = "ua-script-{$tag}"; s.dataset.analyticsobject = n; s.async = 1; s.type = "text/javascript";
		s.src = "https://cdn.yektanet.com/rg_woebegone/scripts_v3/{$tag}/rg.complete.js?v=" + r, c.parentNode.insertBefore(s, c)
	}(window, document, "yektanet");
</script>
<!-- End Yektanet Analytics -->
TAG;
				}
			}

			$tag = Yii::$app->shopack->configmgmt->config('site',
				Yii::$app->isFrontend
					? SiteConfigModel::KEY_GOOGLE_TAG_KEY_FE
					: SiteConfigModel::KEY_GOOGLE_TAG_KEY_BE
			);
			if (!empty($tag))
			{
				echo <<<TAG
<!-- Google Tag Manager -->
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$tag}');</script>
<!-- End Google Tag Manager -->
TAG;
			}

			$tag = Yii::$app->shopack->configmgmt->config('site',
				Yii::$app->isFrontend
					? SiteConfigModel::KEY_GOOGLE_ANALYTICS_KEY_FE
					: SiteConfigModel::KEY_GOOGLE_ANALYTICS_KEY_BE
			);
			if (!empty($tag))
			{
				echo <<<TAG
<!-- Global site tag (gtag.js) - Google Analytics -->
<script async src="https://www.googletagmanager.com/gtag/js?id={$tag}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{$tag}');
</script>
TAG;
			}
		}
		*/
	}

	public function beginBody()
	{
		parent::beginBody();

		/*
		if (!YII_ENV_DEV)
		{
			$tag = Yii::$app->shopack->configmgmt->config('site',
			Yii::$app->isFrontend
				? SiteConfigModel::KEY_GOOGLE_TAG_KEY_FE
				: SiteConfigModel::KEY_GOOGLE_TAG_KEY_BE
			);
			if (!empty($tag))
			{
				echo <<<TAG
<!-- Google Tag Manager (noscript) -->
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id={$tag}"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<!-- End Google Tag Manager (noscript) -->
TAG;
			}
		}
		*/
	}

	public function endBody($custom=true)
	{
		if ($custom) {
			if (!Yii::$app->request->isAjax) {
				$img = Url::to(['/images/loading17.gif']);
				echo <<<Html
<div id='waitpanel' style='display:none; position:fixed; left:0; top:0; width:100%; height:100vh; background-color: rgba(0, 0, 0, .5); z-index:100000; text-align:center; vertical-align:middle; line-height:100vh;'>
	<div style='padding:27px 10px; border:1px solid black; background-color:#fff; border-radius:10px; color:black; display:inline;'>
		<img src='{$img}' alt='در حال انجام عملیات...'>
		در حال انجام عملیات...
	</div>
</div>
Html;
				Modal::put();
				// kartik\dialog\DialogAsset::register($this);
				\kartik\dialog\Dialog::widget([
					'dialogDefaults' => [
						\kartik\dialog\Dialog::DIALOG_ALERT => [
							'type' => \kartik\dialog\Dialog::TYPE_WARNING,
							'title' => 'اخطار',
						],
					],
				]);

				$JS =<<<JS
$('[data-toggle="tooltip"]').tooltip({
	container: 'body'
})
JS;
				$this->registerJs($JS, \yii\web\View::POS_READY);

				// function showWaitPanel(containerID=null, waitPanelID='waitpanel') {
				// 	$('#' + waitPanelID).show();
				// }
				// function hideWaitPanel(containerID=null, waitPanelID='waitpanel') {
				// 	$('#' + waitPanelID).hide();
				// }
				$JS =<<<JS
function checkMainAnchor() {
	var url = $(location).attr("href");
	var pattern = new RegExp('(\\\?|\\\&)(anchor=)(.*?)(#|&|$)');
	var parts = url.match(pattern);
	if (parts !== null)
	{
		var pos = url.indexOf('#');
		if (pos > 0)
			url = url.substring(0, pos);
		url = url + '#' + parts[3];
		window.location.href = url;
	}
}
checkMainAnchor();
JS;
				$this->registerJs($JS, \yii\web\View::POS_END);

			} //if (!Yii::$app->request->isAjax)

			$this->registerJs('$("img.lazyload").lazyload();', \yii\web\View::POS_READY);

			//clear this items from output:
			// <link href=".../assets/d3a4dffc/css/bootstrap.css" rel="stylesheet">
			if ($this->isInRenderAjaxLegacy) {
				// if (isset($this->assetBundles['yii\web\JqueryAsset']))
					// unset($this->assetBundles['yii\web\JqueryAsset']);
				// if (isset($this->assetBundles['yii\web\YiiAsset']))
					// unset($this->assetBundles['yii\web\YiiAsset']);
				if (isset($this->assetBundles['yii\bootstrap\BootstrapAsset']))
					unset($this->assetBundles['yii\bootstrap\BootstrapAsset']);
				// if (isset($this->assetBundles['\shopack\base\AssetBundle']))
					// unset($this->assetBundles['\shopack\base\AssetBundle']);
			} else if ($this->isInRenderAjax) {
				if (isset($this->assetBundles['yii\web\YiiAsset']))
					unset($this->assetBundles['yii\web\YiiAsset']);
				if (isset($this->assetBundles['yii\bootstrap\BootstrapAsset']))
					unset($this->assetBundles['yii\bootstrap\BootstrapAsset']);
				if (isset($this->assetBundles['\shopack\base\AssetBundle']))
					unset($this->assetBundles['\shopack\base\AssetBundle']);
			}
		}

		return parent::endBody();
	}

	protected function renderBodyEndHtml($ajaxMode)
	{
		$lines = '';
		if (!empty($this->modals)) {
			$js = [];
			foreach ($this->modals as $id => $modal) {
				$js[] = "$(document.body).append($('#{$id}-modal').detach());";
			}
			$this->registerJs(implode('', $js), self::POS_END);
			// $lines = implode("\n", $this->modals);
		}

		return parent::renderBodyEndHtml($ajaxMode) . "\n" . $lines;
	}











	// null|pjax|turbo
	public $driver = false;

	public function beginMainFrame()
	{
		if (!$this->driver)
			return;

		if ($this->driver == 'pjax') {
			$js =<<<JS
function globalPjaxBeforeSend(event, xhr, settings) {
	xhr.setRequestHeader('x-powered-by', 'ZZZZZZZZZZZZZZZZZZZZZZZZZZZZ');

	xhr.setRequestHeader('Authentication', 'Bearer *****************************');

	if (window.localStorage['token'] != undefined)
		xhr.setRequestHeader('Authentication', 'Bearer ' + window.localStorage['token']);
}
JS;
			$this->registerJs($js, \yii\web\View::POS_END);

			$pjaxWidget = \yii\widgets\Pjax::begin([
				'id' => 'main-pjax',
				'options' => [
					'class' => 'h-min-100',
				],
			]);

			$pjaxWidget_events = [
				'pjax:beforeSend' => ['globalPjaxBeforeSend'],
			];

			$js = [];
			foreach ($pjaxWidget_events as $ev => $func)
			{
				if (is_array($func))
				{
					foreach ($func as $f)
						$js[] = "on('{$ev}', {$f})";
				}
				else
					$js[] = "on('{$ev}', {$func})";
			}

			$jqobj = 'jQuery("#' . $pjaxWidget->options['id'] . '")' . "." . implode(".", $js) . ";";
			$this->registerJs($jqobj);

			return;
		}

		if ($this->driver == 'turbo') {
		// echo "<div class='turbo-progress-bar'></div>";

		// \shopack\base\frontend\turbo\TurboAsset::register($this);

		Frame::begin([
			'options' => [
				'id' => 'main-frame',
				'turbo-action' => 'advance',
				'data-turbo-progress-bar' => 'true',
			]
		]);

		$js =<<<JS
function initializeTurbo() {
	adapter = Turbo.navigator.delegate.adapter;
	progressBar = adapter.progressBar;
	session = Turbo.session;

	let progressBarTimeout = null;
	document.addEventListener('turbo:before-fetch-request', (event) => {
		const target = event.target;
		if (!(target instanceof HTMLElement)) {
			return;
		}

		if ('true' === target.getAttribute('data-turbo-progress-bar')) {
			if (!progressBarTimeout) {
				progressBar.setValue(0);
			}

			progressBarTimeout = window.setTimeout(() => progressBar.show(), session.progressBarDelay);
		}
	});

	document.addEventListener('turbo:before-fetch-response', () => {
		if (progressBarTimeout) {
			window.clearTimeout(progressBarTimeout);
			progressBar.hide();
			progressBarTimeout = null;
		}
	});
}

initializeTurbo();
JS;
		$this->registerJs($js, \yii\web\View::POS_READY);

		// $this->registerJs("$('main-frame').setDrive(true);", \yii\web\View::POS_READY);
		// $this->registerJs("Turbo.session.drive = true; Turbo.setProgressBarDelay(10);", \yii\web\View::POS_READY);

			return;
		}
	}

	public function endMainFrame()
	{
		if (!$this->driver)
			return;

		if ($this->driver == 'pjax') {
			\yii\widgets\Pjax::end();
			return;
		}

		if ($this->driver == 'turbo') {
			Frame::end();
			return;
		}
	}

}
