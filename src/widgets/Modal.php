<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\frontend\helpers\Html;

class Modal
	// extends \yii\bootstrap5\Widget
	extends \yii\bootstrap5\Modal
{
	public $header;

/*
	const SIZE_LARGE = 'modal-lg';
	const SIZE_SMALL = 'modal-sm';
	const SIZE_DEFAULT = '';

	public $headerOptions;
	public $bodyOptions = ['class' => 'modal-container'];
	public $footer;
	public $footerOptions;
	public $size;
	public $closeButton = [];
	public $toggleButton = false;

	public function init()
	{
		parent::init();

		$this->initOptions();

		echo $this->renderToggleButton() . "\n";
		echo Html::beginTag('div', $this->options) . "\n";
		echo Html::beginTag('div', ['class' => 'modal-dialog ' . $this->size]) . "\n";
		echo Html::beginTag('div', ['class' => 'modal-content']) . "\n";
		echo $this->renderHeader() . "\n";
		echo $this->renderBodyBegin() . "\n";
		// echo Html::beginTag('div', ['id' => 'modalContent']) . "\n";
	}

	public function run()
	{
		// echo "\n" . Html::endTag('div'); // #modalContent
		echo "\n" . $this->renderBodyEnd();
		// echo "\n" . $this->renderFooter();
		echo "\n" . Html::endTag('div'); // modal-content
		echo "\n" . Html::endTag('div'); // modal-dialog
		echo "\n" . Html::endTag('div');

		$this->registerPlugin('modal');
	}
	*/

	protected function renderHeader(): string
	{
		$button = Html::tag('div', $this->renderCloseButton(), [
			'class' => 'btn-group ms-auto',
		]);

		$header = $this->header . $button . "<div class='clearfix'></div>";

		Html::addCssClass($this->headerOptions, ['widget' => 'modal-header']);
		return Html::tag('div', $header, $this->headerOptions);
	}

	protected function renderBodyBegin(): string
	{
		Html::addCssClass($this->bodyOptions, ['widget' => 'modal-container']);
		return Html::beginTag('div', $this->bodyOptions);
	}
/*
	protected function renderBodyEnd()
	{
		return Html::endTag('div');
	}

	protected function renderFooter()
	{
		if ($this->footer !== null) {
			Html::addCssClass($this->footerOptions, ['widget' => 'modal-footer']);
			return Html::tag('div', "\n" . $this->footer . "\n", $this->footerOptions);
		} else {
			return null;
		}
	}

	protected function renderToggleButton()
	{
		if (($toggleButton = $this->toggleButton) !== false) {
			$tag = ArrayHelper::remove($toggleButton, 'tag', 'button');
			$label = ArrayHelper::remove($toggleButton, 'label', 'Show');
			if ($tag === 'button' && !isset($toggleButton['type'])) {
				$toggleButton['type'] = 'button';
			}

			return Html::tag($tag, $label, $toggleButton);
		} else {
			return null;
		}
	}

	protected function renderCloseButton()
	{
		if (($closeButton = $this->closeButton) !== false) {
			$tag = ArrayHelper::remove($closeButton, 'tag', 'button');
			$label = ArrayHelper::remove($closeButton, 'label', '&times;');
			if ($tag === 'button' && !isset($closeButton['type'])) {
				$closeButton['type'] = 'button';
			}

			return Html::tag($tag, $label, $closeButton);
		} else {
			return null;
		}
	}

	protected function initOptions()
	{
		$this->options = array_merge([
			'class' => 'fade',
			'role' => 'dialog',
			'tabindex' => false, //important for Select2 to work properly
		], $this->options);
		Html::addCssClass($this->options, ['widget' => 'modal']);

		if ($this->clientOptions !== false) {
			$this->clientOptions = array_merge(['show' => false], $this->clientOptions);
		}

		if ($this->closeButton !== false) {
			$this->closeButton = array_merge([
				'data-dismiss' => 'modal',
				'aria-hidden' => 'true',
				'class' => 'close',
			], $this->closeButton);
		}

		if ($this->toggleButton !== false) {
			$this->toggleButton = array_merge([
				'data-toggle' => 'modal',
			], $this->toggleButton);
			if (!isset($this->toggleButton['data-target']) && !isset($this->toggleButton['href'])) {
				$this->toggleButton['data-target'] = '#' . $this->options['id'];
			}
		}
	}
*/

	public static function put()
	{
		if (Yii::$app->request->isAjax)
			return;

		// Yii::$app->view->registerModal('modal-lg0', ['size' => static::SIZE_LARGE]);
		// Yii::$app->view->registerModal('modal-sm');
		// Yii::$app->view->registerModal('modal-sm0');
		// Yii::$app->view->registerModal('modal-sm2');
		// Yii::$app->view->registerModal('modal-lg', ['size' => static::SIZE_LARGE]);

		$loader = Html::div(
			Html::img(Url::to(['/images/loading17.gif']), ['alt' => 'در حال فراخوانی...']),
			[
				'class' => 'modal-body',
				'style' => 'text-align:center',
			]
		);

		$jsOnHide =<<<JS
function(e) {
	$('#' + e.target.id).find('#modalContainer').empty(); //.html('');
}
JS;
		echo static::widget([
			'id' => 'modal-lg0',
			'closeButton' => [
				'label' => 'بستن',
				'class' => 'btn btn-danger btn-sm',
			],
			'size' => static::SIZE_LARGE,
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => [
				'backdrop' => 'static',
				'keyboard' => false,
			],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		]);

		// $('.select2').select2({dropdownParent: $('#modal-sm')});
		echo static::widget([
			'id' => 'modal-sm',
			'closeButton' => ['label' => 'بستن', 'class' => 'btn btn-danger btn-sm'],
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		]);

		echo static::widget([
			'id' => 'modal-sm0',
			'closeButton' => ['label' => 'بستن', 'class' => 'btn btn-danger btn-sm'],
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		]);

		echo static::widget([
			'id' => 'modal-sm2',
			'closeButton' => ['label' => 'بستن', 'class' => 'btn btn-danger btn-sm'],
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => ['backdrop' => 'static', 'keyboard' => false],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		]);

		echo static::widget([
			'id' => 'modal-lg',
			'closeButton' => [
				'label' => 'بستن',
				'class' => 'btn btn-danger btn-sm',
			],
			'size' => static::SIZE_LARGE,
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => [
				'backdrop' => 'static',
				'keyboard' => false,
			],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		]);
	}

	public static function createNew($id, $options=[])
	{
		$loader = Html::div(
			Html::img(Url::to(['/images/loading17.gif']), ['alt' => 'در حال فراخوانی...']),
			[
				'class' => 'modal-body',
				'style' => 'text-align:center',
			]
		);

		$jsOnHide =<<<JS
function(e) {
	$('#' + e.target.id).find('#modalContainer').empty(); //.html('');
}
JS;

		$options = array_replace_recursive([
			'id' => $id,
			'closeButton' => [
				'label' => 'بستن',
				'class' => 'btn btn-danger btn-sm',
			],
			'size' => static::SIZE_SMALL,
			'headerOptions' => ['id' => 'modalHeader'],
			'header' => '<div id="modalHeaderContent"></div>',
			'clientOptions' => [
				'backdrop' => 'static',
				'keyboard' => false,
			],
			'options' => [
				'data-loader' => $loader,
				// 'tabindex' => false, //important for Select2 to work properly
				// 'data-focus' => 'false',
			],
			'bodyOptions' => ['id' => 'modalContainer'],
			'clientEvents' => [
				'hide.bs.modal' => $jsOnHide,
			],
		], $options);

		return static::widget($options);
	}

}
