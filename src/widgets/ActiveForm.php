<?php
namespace shopack\base\frontend\widgets;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\StringHelper;
use shopack\base\frontend\helpers\Html;
use shopack\multilanguage\helpers\LanguageHelper;
use shopack\base\frontend\widgets\datetime\DatePicker;
use shopack\base\frontend\widgets\FormBuilder;

// class ActiveForm extends \yii\bootstrap\ActiveForm
class ActiveForm extends \kartik\form\ActiveForm
{
	const doneParam = '__done__';
	public $fieldClass = 'shopack\base\frontend\widgets\ActiveField';

	public $formid;
	/**
	 * used at ActiveForm::end
	 *
	 * @var array
	 */
	public $validators = null;
	public $model = null;
	private $hiddenInputs = [];
	private $_noAjax;
	private $_waitPanel;
	private $_scripts;
	private $_errorspan;

	private $columnCount = 1;
	public function getLabelSpan()
	{
		if (isset($this->formConfig['labelSpan']))
			return $this->formConfig['labelSpan'];
		if (isset($this->fieldConfig['labelSpan']))
			return $this->fieldConfig['labelSpan'];
		return self::DEFAULT_LABEL_SPAN;
	}

	public static function begin($config = [])
	{
		$action = ArrayHelper::remove($config, 'action', null);
		if (!empty($action))
		{
			// if (!Yii::$app->user->canUrl($action))
			// 	$action = null;

			if (is_array($action))
				$action = Url::to($action);

			$config['action'] = $action;
		}

		$model = ArrayHelper::remove($config, 'model', null);
		$donewait = ArrayHelper::remove($config, 'donewait', '500');
		$errorspan = ArrayHelper::remove($config, 'errorspan', 'errorspan');
		$waitPanel = ArrayHelper::remove($config, 'waitPanel', 'waitpanel');
		$noAjax = ArrayHelper::remove($config, 'noAjax', false);
		$modalDoneInternalScript_OK = ArrayHelper::remove($config, 'modalDoneInternalScript_OK', "
if (result.redirect != false) {
	if (result.redirect) {
		window.location.href = result.redirect;
		// if (result.redirect.indexOf('#') >= 0)
			// window.location.reload();
	} else if ((result.redirect === undefined) || (result.redirect.indexOf('#') >= 0)) {
		if (result.modalDoneFragment) {
			window.location.href = replaceUrlParam(window.location.href, 'fragment', result.modalDoneFragment);
		} else {
			window.location.reload();
		}
	}
};
");
		$modalDoneScript_OK = ArrayHelper::remove($config, 'modalDoneScript_OK', "");
		$scripts = ArrayHelper::remove($config, 'scripts', [
			'modalDone' => "
				if ((result.status == undefined) || (result.status == 'OK')) {
					\$form.parent().html(\"<div class='alert alert-success'>\" + result.message + \"</div>\");
					if (result.timer != false) {
						timerid = setInterval(function() {
							\$modalDiv.modal('hide');
							clearInterval(timerid);
							timerid = null;
							{$modalDoneScript_OK}
							{$modalDoneInternalScript_OK}
						},
						{$donewait});
					};
				} else { //if (result.status == 'Error') {
					$('#{$errorspan}').html(result.error);
				}
			"
		]);

		$type = ArrayHelper::remove($config, 'type', static::TYPE_HORIZONTAL);
		// $layout = ArrayHelper::remove($config, 'layout', 'horizontal');

		// $template = false; //"{beginLabel}{labelTitle}:{endLabel}\n{beginWrapper}\n{input}\n{error}\n{hint}\n{endWrapper}";
		$template = ArrayHelper::remove($config, 'template', false);

		// $horizontalCssClasses = [
			// 'label' => 'col-sm-3',
			// 'offset' => 'col-sm-offset-1',
			// 'wrapper' => 'col-sm-9',
			// 'error' => 'small',
			// 'hint' => 'small'
		// ];
		// if (isset($config['fieldConfig']))
		// {
			// $template = ArrayHelper::remove($config['fieldConfig'], 'template', $template);
			// $horizontalCssClasses = ArrayHelper::remove($config['fieldConfig'], 'horizontalCssClasses', $horizontalCssClasses);
		// }

		if ($model) {
			if (is_string($model))
				$formid = $model;
			else
				$formid = $model->formName();
		} else
			$formid = StringHelper::generateRandomId();

		$config = ArrayHelper::merge($config, [
			'id' => $formid,
			'options' => [
				'id' => $formid,
			]
		]);

		if ($type !== null)
			$config = ArrayHelper::merge($config, ['type' => $type]);
		// if ($layout)
			// $config = ArrayHelper::merge($config, [
				// 'layout' => $layout
			// ]);
		$fieldConfig = [];
		if ($template)
			$fieldConfig = ArrayHelper::merge($fieldConfig, [
				'template' => $template,
			]);
		// if ($horizontalCssClasses)
			// $fieldConfig = ArrayHelper::merge($fieldConfig, [
				// 'horizontalCssClasses' => $horizontalCssClasses
			// ]);
		if (count($fieldConfig))
		{
			$config = ArrayHelper::merge($config, [
				'fieldConfig' => $fieldConfig,
			]);
		}
		// die(print_r($config, true));
		$form = parent::begin($config);
		$form->formid = $formid;
		if (!is_string($model))
			$form->model = $model;

		$form->_noAjax = $noAjax;
		$form->_waitPanel = $waitPanel;
		$form->_scripts = $scripts;
		$form->_errorspan = $errorspan;

		return $form;
	}

	public function endForm()
	{
// 		$formid = $this->formid;
// 		$js = "
// 			var _formvalidationdata_{$formid} = {
// 			};
// 		";
// 		Yii::$app->view->registerJs($js, \yii\web\View::POS_END);

		if (count($this->hiddenInputs) > 0)
			echo implode("\n", $this->hiddenInputs) . "\n";

		//method='get' used in _search forms
		if (Yii::$app->request->isAjax && !$this->_noAjax && ($this->method != 'get')) {
			// beforeSubmit
			$js = "
			$('form#{$this->formid}')
				.on('beforeSubmit', function(e) {
					// var \$form = $(this);
					// console.log(\$form.serialize());
					// if (!_validateFormData(_formvalidationdata_{$this->formid}))
					// 	return;
					_doSubmitForm(this, " . (Yii::$app->request->isAjax ? 1 : 0) . ");
				})
				.on('submit', function(e) {
					e.preventDefault();
				});
			";
			Yii::$app->view->registerJs($js, \yii\web\View::POS_READY);

			$js = "
var timerid = null;
function _doSubmitForm(elForm, \$ismodal)
{
	var \$form = $(elForm);
	var data = new FormData(elForm);
";
			if ($this->_waitPanel !== false)
				$js .= "	showWaitPanel(null, '{$this->_waitPanel}');";

			// $enctype = $this->options['enctype'] ?? false;

			$js .= "
	$.ajax({
		url: \$form.attr('action'),
		type: '{$this->method}',
		data: data,
		processData: false,
		contentType: false
	})
";

// $.post(
// 	\$form.attr('action'),
// 	\$form.serialize()
// )

			if ($this->_waitPanel !== false)
				$js .= "	.always(function() {
		hideWaitPanel(null, '{$this->_waitPanel}');
	})";
			$js .= "
	.done(function(result) {
// console.log('done');
// console.log(typeof(result));
// console.log(result);
// console.log(\$form.parent());
// console.log(\$form.closest('.modal'));
		\$modalDiv = \$form.closest('.modal');
		if (typeof(result) === 'string') {
			if (\$ismodal) {
				// form is invalid, html returned
				\$form.parent().html(result);
			} else {
				$('#{$this->_errorspan}').html(result);
			}
		} else {
			if (\$ismodal) {
		" . $this->_scripts['modalDone'] . "
			} else {
				$('#{$this->_errorspan}').html(result);
			}
		}
	})
	.fail(function(jqXHR, textStatus, errorThrown) {
console.log('server error');
// console.log(errorThrown);
		$('#{$this->_errorspan}').html(jqXHR.responseText);
		// \$form.replaceWith('<button>Fail</button>').fadeOut()
	})
	.progress(function(e) {
// console.log('progress');
// console.log(e);
		// $('#{$this->_errorspan}').html(e.responseText);
	})
	;
	return false;
}
";
			Yii::$app->view->registerJs($js, \yii\web\View::POS_END);
		} //if (Yii::$app->request->isAjax && !$this->_noAjax)
		else
		{
			// beforeSubmit
// 			$js = "
// 			$('form#{$this->formid}')
// 				.on('beforeSubmit', function(e) {
// 					var \$form = $(this);
// 					if (!_validateFormData(_formvalidationdata_{$this->formid}))
// 						e.preventDefault();
// 				})
// 			";
// 			Yii::$app->view->registerJs($js, \yii\web\View::POS_READY);
		}

// 		$js = "
// 			function _validateFormData(_data)
// 			{
// 				return true;
// 			};
// 		";
// 		Yii::$app->view->registerJs($js, \yii\web\View::POS_END);

		return static::end();
	}

	public function registerActiveHiddenInput($model, $attribute, $options = [])
	{
		$this->hiddenInputs[] = Html::activeHiddenInput($model, $attribute, $options);
	}

	/**
	 *
	 * $models_fields: array [ [model(1), field(1){, label(1)}], [model(2), field(2){, label(2)}], ... ]
	 *
	 * $fieldCallback = function ($field, $model, $attribute, $config)
	 *	-> return $field
	 */
	public function multipleFields($models_fields, $label=null, $labelSpan=null)
	{
		if ($labelSpan === null)
			$labelSpan = $this->getLabelSpan();
		if ($label === null)
			$label = $models_fields[0][0]->getAttributeLabel($models_fields[0][1]);

		$ret =
				'<div class="form-group kv-fieldset-inline">'
			. Html::activeLabel($models_fields[0][0], $models_fields[0][1], [
					'label' => $label,
					'class' => "col-sm-{$labelSpan} control-label",
				])
		;
		$r = 12 - $labelSpan;
		$ret .= "<div class='col-sm-{$r}'>";
		$ret .= "<div class='row'>";
		// $w = (12 - $labelSpan) / count($models_fields);
		$w = 12 / count($models_fields);

		foreach ($models_fields as $model_field)
		{
			$model = $model_field[0]; //array_shift($model_field);
			$fieldName = $model_field[1]; //array_shift($model_field);
			$fieldOptions = ArrayHelper::getValue($model_field, 'fieldOptions', []);
			$fieldCallback = ArrayHelper::getValue($model_field, 'fieldCallback', null);
			$containerOptions = ArrayHelper::getValue($model_field, 'containerOptions', []);

			$l = ArrayHelper::remove($fieldOptions, 'label', $model->getAttributeLabel($fieldName));
			if ($l == false)
			{
				$opts = [
					'showLabels' => false,
				];
			}
			else
			{
				$opts = [
					'showLabels' => false,
					'addon' => [
						'prepend' => [
							'content' => $l . ':',
							// 'options' => [
								// 'style' => 'font-weight:bold',
							// ],
						],
					],
				];
			}

			$appendLabel = ArrayHelper::remove($fieldOptions, 'appendLabel', null);
			if (!empty($appendLabel))
			{
				$opts = array_merge_recursive($opts, [
					'addon' => [
						'append' => [
							'content' => $appendLabel,
							// 'options' => [
								// 'style' => 'font-weight:bold',
							// ],
						],
					],
				]);
			}

			$opts = array_replace_recursive($opts, $fieldOptions);

			/*if (isset($model_field['addon']['prepend']))
			{
				if (!ArrayHelper::isIndexed($model_field['addon']['prepend']))
					$model_field['addon']['prepend'] = [$model_field['addon']['prepend']];

				if (!isset($opts['addon']['prepend']))
					$opts['addon']['prepend'] = [];

				if (!ArrayHelper::isIndexed($opts['addon']['prepend']))
					$opts['addon']['prepend'] = [$opts['addon']['prepend']];
				$opts['addon']['prepend'] = ArrayHelper::merge($opts['addon']['prepend'], $model_field['addon']['prepend']);
				// echo Html::pre(print_r($opts, true)); die();
			}

			if (isset($model_field['addon']['append']))
			{
				if (!ArrayHelper::isIndexed($model_field['addon']['append']))
					$model_field['addon']['append'] = [$model_field['addon']['append']];

				if (!isset($opts['addon']['append']))
					$opts['addon']['append'] = [];

				if (!ArrayHelper::isIndexed($opts['addon']['append']))
					$opts['addon']['append'] = [$opts['addon']['append']];
				$opts['addon']['append'] = ArrayHelper::merge($opts['addon']['append'], $model_field['addon']['append']);
				// echo Html::pre(print_r($opts, true)); die();
			}*/

			$field = $this->field($model, $fieldName, $opts);

			if (($fieldCallback !== null) && ($fieldCallback instanceof \Closure))
			{
				$field = call_user_func($fieldCallback, $field, $model, $fieldName, $model_field);
			}

			$containerOptions = array_merge_recursive(['class' => "col-sm-{$w}"], $containerOptions);
			$ret .= Html::div($field, $containerOptions);
		}
		$ret .= '</div>';
		$ret .= '</div>';
		$ret .= '</div>';

		return $ret;
	}

	public function dualDatePicker($model, $fieldNameFrom, $fieldNameTo, $label, $labelSpan=null, $options=null)
	{
		$fieldCallback = function ($field, $model, $attribute, $config) use ($fieldNameFrom, $fieldNameTo) {
			return $field->widget(DatePicker::className(), [
				'rangeSelector' => [
					'isFrom' => ($attribute == $fieldNameFrom),
					'otherID' => Html::getInputId($model, $attribute == $fieldNameFrom ? $fieldNameTo : $fieldNameFrom),
				],
				'allowClear' => true,
				'withTime' => isset($config['options']['withTime']),
			]);
		};

		return $this->multipleFields([
				[
					$model,
					$fieldNameFrom,
					'fieldOptions' => [
						'label' => 'از',
					],
					'fieldCallback' => $fieldCallback,
					'options' => $options,
				],
				[
					$model,
					$fieldNameTo,
					'fieldOptions' => [
						'label' => 'تا',
					],
					'fieldCallback' => $fieldCallback,
					'options' => $options,
				],
			],
			$label,
			$labelSpan
		);
	}

	public function multiLanguageFields($model, $fieldName, $params=[]) //, $callback=null)
	{
		$callback = ArrayHelper::remove($params, 'callback', null);
		$lngMap = LanguageHelper::getLanguagesMap();

		foreach ($model->getTranslateAttributes($fieldName) as $translateAttribute)
		{
			$ps = explode('_', $translateAttribute);
			$ps = array_pop($ps);
			$lng = [
				'lngCode' => $ps,
				'info' => $lngMap[$ps],
			];

			$field = $this->field($model, $translateAttribute, $params);
			if ($callback != null)
				$field = $callback($this, $model, $fieldName, $lng, $field);

			echo $field; //->label($model->getAttributeLabel($fieldName) . ' (' . $lng['lngName'] . ')');
		}
	}

	private $_builder;
	public function getBuilder($options=[])
	{
		if ($this->_builder == null)
		{
			$this->_builder = new FormBuilder($options);
			$this->_builder->form = $this;
		}
		return $this->_builder;
	}

	// public function columns($colCount)
	// {
		// $this->columnCount = $colCount;
	// }

	// public function field($model, $attribute, $options = [])
	// {
		// return parent::field($model, $attribute, $options);
	// }

}
