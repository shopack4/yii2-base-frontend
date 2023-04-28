<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use Yii;
use yii\base\Event;
use kartik\form\ActiveForm;
use shopack\base\frontend\widgets\Select2;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\ArrayHelper;
use shopack\base\common\helpers\StringHelper;
// use shopack\multilanguage\helpers\LanguageHelper;
// use shopack\base\frontend\widgets\datetime\DatePicker;

class FormBuilder extends \yii\base\Component
{
	const FIELD_TEXT										= 'text';
	const FIELD_TEXT_MULTILANGUAGE			= 'ml_text';
	const FIELD_TEXTAREA								= 'textarea';
	const FIELD_TEXTAREA_MULTILANGUAGE	= 'ml_textarea';
	const FIELD_PASSWORD								= 'password';
	const FIELD_FILE										= 'file';
	const FIELD_WIDGET									= 'widget';
	const FIELD_CHECKBOX								= 'checkbox';
	const FIELD_CHECKBOXLIST						= 'checkboxlist';
	const FIELD_RADIOLIST								= 'radiolist';
	const FIELD_DUALDATEPICKER					= 'dualdatepicker';
	const FIELD_MULTIPLEFIELDS					= 'multipleFields';
	const FIELD_STATIC									= 'static';
	const FIELD_GEOMAP									= 'geomap';
	const FIELD_PERCENT									= 'percent';

	public $form;
	protected $fields = [];
	protected $footer = '';

	public function field($model, $fieldName=null)
	{
		if (is_array($model)) {
			$fieldName = $model[1];
			$model = $model[0];
		} else if ($fieldName == null) {
			$fieldName = $model;
			$model = null;
		}

		foreach ($this->fields as $k => $field) {
			if (is_array($field)
					&& isset($field[0])
					&& (is_array($field[0]) || (substr($field[0], 0, 1) !== '@'))
			) {
				if (is_array($field[0])) {
					if (($field[0][0] == $model) && ($field[0][1] == $fieldName))
						return [$field, $k];
				} else {
					if ($field[0] == $fieldName)
						return [$field, $k];
				}
			}
		}

		return null;
	}

	public function fields($fields)
	{
		return $this->fieldsAfter(null, $fields);
	}

	public function fieldsAfter($after, $fields)
	{
		if (empty($after) && empty($fields))
			throw new \Exception('nothing provided!');

		if (is_string($fields))
			$fields = [$fields];

		if (is_array($fields) && !ArrayHelper::isIndexed($fields))
			$fields = [$fields];

		if ($after === null)
			$this->fields = ArrayHelper::merge($this->fields, $fields);
		else {
			//find prev
			$foundKey = null;
			if ($after === -1) //at start
				$foundKey = -1;
			else {
				foreach ($this->fields as $k => $v) {
					if (is_array($v[0])) { //[$model, 'field']
						if ($v[0][1] == $after) {
							$foundKey = $k;
							break;
						}
					} else {
						if ($v[0] == $after) {
							$foundKey = $k;
							break;
						}
					}
				}
			}

			if ($foundKey === null)
				$this->fields = ArrayHelper::merge($this->fields, $fields);
			else {
				if ($foundKey == -1)
					$this->fields = ArrayHelper::merge($fields, $this->fields);
				else if ($foundKey == count($this->fields)-1)
					$this->fields = ArrayHelper::merge($this->fields, $fields);
				else
					$this->fields = ArrayHelper::merge(
						array_slice($this->fields, 0, $foundKey+1),
						$fields,
						array_slice($this->fields, $foundKey+1, count($this->fields)-$foundKey-1)
					);
			}
		}

		return $this;
	}

	protected $_after = null;
	public function beginField($after=null)
	{
		$this->_after = $after;
		ob_start();
		ob_implicit_flush(false);
	}

	public function endField()
	{
		$fields = ob_get_clean();
		$this->fieldsAfter($this->_after, $fields);
	}

	public function beginFooter()
	{
		ob_start();
		ob_implicit_flush(false);
	}

	public function endFooter()
	{
		$this->footer = ob_get_clean();
	}

	public function render()
	{
// Html::dump($this->fields);
// return;
		$formFields = [];
		$visFormFields = [];

		$col_count = 1;
		$colWidth = 'col-sm-12';
		$vertical = false;

		$beginRowCount = 0;
		$beginColCount = 0;

		echo Html::beginTag('div', ['class' => 'card-body']);

		if (!empty($this->fields)) {
			echo Html::beginTag('div', ['class' => 'row']);
			++$beginRowCount;

			foreach ($this->fields as &$field) {
				if (is_string($field)) {
					while ($beginColCount > 0) {
						echo Html::endTag('div'); //col
						--$beginColCount;
					}
					echo Html::endTag('div'); //row
					echo $field; //same as <hr> ...
					echo Html::beginTag('div', ['class' => 'row']);
					if ($vertical) {
						echo Html::beginTag('div', ['class' => $colWidth]);
						++$beginColCount;
					}
				} elseif (is_array($field)) {
					if (isset($field[0])
							&& (is_array($field[0]) || (substr($field[0], 0, 1) !== '@'))
					) {
						$type = ArrayHelper::getValue($field, 'type', static::FIELD_TEXT);
						$fieldOptions = ArrayHelper::getValue($field, 'fieldOptions', []);
						$widgetOptions = ArrayHelper::getValue($field, 'widgetOptions', []);

						if ($type == static::FIELD_PERCENT) {
							throw new \Exception('not implemented yet!');

							//todo: (vvvvvi) regex for percentage
							$type = $field['type'] = static::FIELD_WIDGET;
							$widget = $field['widget'] = \yii\widgets\MaskedInput::class;
							$widgetOptions = $field['widgetOptions'] = [
								'options' => [
									'maxlength' => true,
									'style' => 'direction:ltr;',
								],
								'aliases' => [
									'percent' => [
										// 'regex' => '\d+(?:\.\d+)?',
										// 'regex' => '\b(?<!\.)(?!0+(?:\.0+)?%)(?:\d|[1-9]\d|100)(?:(?<!100)\.\d+)?%',
										// 'regex' => '[a-z][a-z0-9]+(?:-[a-z0-9]+)*',
									],
								],
								'clientOptions' => [
									'alias' => 'percent',
								],
							];
						}

						if ($col_count > 1) {
							$fieldOptions['labelSpan'] = $this->form->getLabelSpan() * $col_count;
						}

						$fnField = 'field';
						if ($type == static::FIELD_TEXT_MULTILANGUAGE) {
							$fnField = 'multiLanguageFields';
							if (!isset($fieldOptions['callback'])) {
								$fieldOptions['callback'] = function($form, $model, $fieldName, $lng, $field) use ($widgetOptions) {
									return $field->textInput(array_merge_recursive($widgetOptions, [
										'maxlength' => true,
										'style' => [
											'direction' => ($lng['info']['lngIsRTL'] ? 'rtl' : 'ltr'),
										],
									]));
								};
							}
						} else if ($type == static::FIELD_TEXTAREA_MULTILANGUAGE) {
							$fnField = 'multiLanguageFields';
							if (!isset($fieldOptions['callback'])) {
								$fieldOptions['callback'] = function($form, $model, $fieldName, $lng, $field) use ($widgetOptions) {
									return $field->textArea(array_merge_recursive($widgetOptions, [
										'maxlength' => true,
										'style' => [
											'direction' => ($lng['info']['lngIsRTL'] ? 'rtl' : 'ltr'),
										],
									]));
								};
							}
						}

						$fieldName = null;
						if (is_array($field[0])) {
							$model = $field[0][0];
							$fieldName = $field[0][1];
						} else {
							$model = $this->form->model;
							$fieldName = $field[0];
						}

						if ($model === null) { //TODO: static field without form
						} else {
							$visibleConditions = ArrayHelper::getValue($field, 'visibleConditions', []);
							$fieldID = Html::getInputId($model, $fieldName);
							$formFields[$fieldName] = [
								// 'model' => $model,
								// 'name' => $fieldName,
								'id' => $fieldID,
								'type' => $type,
								'widget' => ArrayHelper::getValue($field, 'widget', null),
								'visibleConditions' => $visibleConditions,
							];
							if (!empty($visibleConditions))
								$visFormFields[] = $fieldName;

							$_hidden = null;
							$label = ArrayHelper::getValue($field, 'label', null);
							if ($type === static::FIELD_DUALDATEPICKER) {
								$secondField = ArrayHelper::getValue($field, 'secondField', null);
								$_field = $this->form->dualDatePicker($model, $fieldName, $secondField, $label, $fieldOptions['labelSpan'] ?? null);
								$label = false;
							} else if ($type === static::FIELD_MULTIPLEFIELDS) {
								$otherFields = ArrayHelper::getValue($field, 'otherFields', []);
								$fieldCallback = ArrayHelper::getValue($field, 'fieldCallback', null);
								$labelSpan = ArrayHelper::getValue($field, 'labelSpan', null);
								if (count($otherFields) == 0)
									throw new \Exception('otherFields is empty');

								$firstField = [
									$model,
									$fieldName,
								];
								if ($firstLabel = ArrayHelper::getValue($field, 'firstLabel', null))
									$firstField = array_replace_recursive($firstField, [
										'fieldOptions' => ['label' => $firstLabel],
									]);
								if ($firstAppendLabel = ArrayHelper::getValue($field, 'firstAppendLabel', null))
									$firstField = array_replace_recursive($firstField, [
										'fieldOptions' => ['appendLabel' => $firstAppendLabel],
									]);
								if ($firstFieldOptions = ArrayHelper::getValue($field, 'firstFieldOptions', null))
									$firstField = array_replace_recursive($firstField, [
										'fieldOptions' => $firstFieldOptions,
									]);
								if ($fieldCallback !== null)
									$firstField = array_replace_recursive($firstField, ['fieldCallback' => $fieldCallback]);
								$fields = ArrayHelper::merge([$firstField], $otherFields);
								foreach ($fields as $__k => &$__v) {
									if (is_string($__v[0]))
										array_unshift($__v, $model);
									if (isset($__v['label'])) {
										if (!empty($__v['label']))
											$__v = array_replace_recursive($__v, ['fieldOptions' => [
												'label' => $__v['label'],
											]]);
										unset($__v['label']);
									}
									if (isset($__v['appendLabel'])) {
										if (!empty($__v['appendLabel']))
											$__v = array_replace_recursive($__v, ['fieldOptions' => [
												'appendLabel' => $__v['appendLabel'],
											]]);
										unset($__v['appendLabel']);
									}
									if ($fieldCallback !== null)
										$__v = array_replace_recursive($__v, ['fieldCallback' => $fieldCallback]);
									$fields[$__k] = $__v;
								}
								$_field = $this->form->multipleFields($fields, $label, $labelSpan);
								$label = false;
							} else if ($type === static::FIELD_STATIC) {
								if (!empty($field['staticValue']))
									$staticValue = $field['staticValue'];
								else if (!empty($field['staticValueLookup'])) {
									$staticValueLookup = $field['staticValueLookup'];
									$staticValue = ($staticValueLookup[$model->$fieldName] ?? '');
								} else
									$staticValue = $model->$fieldName; //TODO: raise error?

								if (empty($staticValue) && !empty($field['staticNullValueText']))
									$staticValue = $field['staticNullValueText'];

								$staticFormat = ArrayHelper::remove($field, 'staticFormat', null);

								if ($staticFormat)
									$fieldOptions['staticValue'] = Yii::$app->formatter->format($staticValue, $staticFormat);
								else
									$fieldOptions['staticValue'] = $staticValue;

								$_field = $this->form->$fnField($model, $fieldName, $fieldOptions)->staticInput();

								$_hidden = Html::activeHiddenInput($model, $fieldName);
								if ($label !== null)
									$_field->label($label);
							} else {
								$_field = $this->form->$fnField($model, $fieldName, $fieldOptions);
								if ($label !== null)
									$_field->label($label);
							}

							switch ($type) {
								case static::FIELD_TEXT:
									$_field->textInput($widgetOptions);
									break;

								case static::FIELD_TEXT_MULTILANGUAGE:
									// $_field->textInput($widgetOptions);
									break;

								case static::FIELD_GEOMAP:
									$_field->geoMap($widgetOptions);
									break;

								case static::FIELD_TEXTAREA:
									$_field->textArea($widgetOptions);
									break;

								case static::FIELD_TEXTAREA_MULTILANGUAGE:
									// $_field->textInput($widgetOptions);
									break;

								case static::FIELD_PASSWORD:
									$_field->passwordInput($widgetOptions);
									break;

								case static::FIELD_FILE:
									$_field->fileInput($widgetOptions);
									break;

								case static::FIELD_WIDGET:
									$widget = ArrayHelper::getValue($field, 'widget', null);
									// $_field->widget($widget, $widgetOptions);
									$this->createWidget($field, $_field, $widget, $widgetOptions);
									break;

								case static::FIELD_DUALDATEPICKER:
								case static::FIELD_MULTIPLEFIELDS:
									break;

								case static::FIELD_CHECKBOX:
									$_field->checkbox($widgetOptions[0] ?? [], $widgetOptions[1] ?? null);
									break;

								case static::FIELD_CHECKBOXLIST:
									$data = ArrayHelper::getValue($field, 'data', []);
									$widgetOptions['custom'] = true;
									$_field->checkboxList($data, $widgetOptions);
									break;

								case static::FIELD_RADIOLIST:
									$data = ArrayHelper::getValue($field, 'data', []);
									$widgetOptions['custom'] = true;
									$_field->radioList($data, $widgetOptions);
									break;
							}

							$panelOptions = ArrayHelper::getValue($field, 'panelOptions', []);
							if (!empty($visibleConditions)) {
								$panelOptions = array_merge_recursive($panelOptions, [
									'id' => "panel_{$fieldID}",
									'style' => 'display:none',
								]);
							}
							if (!$vertical) {
								$panelOptions = array_merge_recursive($panelOptions, [
									'class' => $colWidth,
								]);
							}

							$field['fieldObject'] = $_field;

							echo Html::beginTag('div', $panelOptions);
							if ($_hidden)
								echo $_hidden;
							echo $_field;
							echo Html::endTag('div');
						} //non-static field
					} //if (isset($field[0]) && (is_array($field[0]) || (substr($field[0], 0, 1) !== '@')))
					else {
						list ($command, $params) = ArrayHelper::parseCommands($field);
	// Html::dump($field, $command, $params);
						switch ($command) {
							case '@col':
								while ($beginColCount > 0) {
									echo Html::endTag('div'); //col
									--$beginColCount;
								}
								echo Html::endTag('div'); //row
								echo Html::beginTag('div', ['class' => 'row']);

								$col_count = $params[$command];
								if ($col_count == 5)
									$colWidth = 'col-sm-1-of-5';
								else
									$colWidth = 'col-sm-' . intval(12 / $col_count);

								$vertical = isset($params['vertical']);
								if ($vertical) {
									echo Html::beginTag('div', ['class' => $colWidth]);
									++$beginColCount;
								}
								break;

							case '@col-break':
								if ($vertical) {
									echo Html::endTag('div'); //col
									echo Html::beginTag('div', ['class' => $colWidth]);
									// ++$beginColCount;
								}
								break;

							case '@reset-cols':
								$col_count = 1;
								$colWidth = 'col-sm-12';
								$vertical = false;

								while ($beginColCount > 0) {
									echo Html::endTag('div'); //col
									--$beginColCount;
								}
								echo Html::endTag('div'); //row
								echo Html::beginTag('div', ['class' => 'row']);
								if ($vertical) {
									echo Html::beginTag('div', ['class' => $colWidth]);
									++$beginColCount;
								}
								break;

							case '@section':
								$label = ArrayHelper::getValue($params, 'label', '');
								$content = Html::tag('h4', $label, ['class' => 'form-section']);
								$visibleConditions = ArrayHelper::getValue($params, 'visibleConditions', []);
								if (!empty($visibleConditions)) {
									$fieldID = uniqid('fb'); //\yii\base\Widget::getId();
									$formFields[$fieldID] = [
										'id' => $fieldID,
										'type' => '@section',
										'widget' => null,
										'visibleConditions' => $visibleConditions,
									];
									$visFormFields[] = $fieldID;
									$content = Html::tag('h4', $label, [
										'class' => 'form-section',
										'id' => 'panel_' . $fieldID,
										'style' => 'display:none',
									]);
								}

								while ($beginColCount > 0) {
									echo Html::endTag('div'); //col
									--$beginColCount;
								}
								echo Html::endTag('div'); //row
								echo $content;
								echo Html::beginTag('div', ['class' => 'row']);
								if ($vertical) {
									echo Html::beginTag('div', ['class' => $colWidth]);
									++$beginColCount;
								}
								break;

							case '@static':
								$content = $params[$command];
								$visibleConditions = ArrayHelper::getValue($params, 'visibleConditions', []);
								if (!empty($visibleConditions)) {
									$fieldID = uniqid('fb'); //\yii\base\Widget::getId();
									$formFields[$fieldID] = [
										'id' => $fieldID,
										'type' => '@static',
										'widget' => null,
										'visibleConditions' => $visibleConditions,
									];
									$visFormFields[] = $fieldID;
									$content = Html::div($content, [
										'id' => 'panel_' . $fieldID,
										'style' => 'display:none',
									]);
								}

								// Html::dump($field, $command, $params);
								while ($beginColCount > 0) {
									echo Html::endTag('div'); //col
									--$beginColCount;
								}
								echo Html::endTag('div'); //row
								echo $content; //same as <hr> ...
								echo Html::beginTag('div', ['class' => 'row']);
								if ($vertical) {
									echo Html::beginTag('div', ['class' => $colWidth]);
									++$beginColCount;
								}
								break;
						}
					}
				} //is_array($field)
				else {
					//???
				}
			}

			echo Html::endTag('div'); //row
			--$beginRowCount;

			$beginRowCount += $beginColCount;
			$beginColCount = 0;
			while ($beginRowCount > 0) {
				echo Html::endTag('div'); //row or col
				--$beginRowCount;
			}
		}
		echo Html::endTag('div'); //card-body

		echo $this->footer;

		//on-change handlers
		if (!empty($visFormFields)) {
			$checkingFields = [];
			$setCommands = [];
			foreach ($visFormFields as $fieldName) {
				$jsCompFieldName = StringHelper::convertToJsVarName($fieldName);

				$checkingFields = ArrayHelper::merge($checkingFields, $formFields[$fieldName]['visibleConditions']);
				$jsFormula = [];
				$fnEncloseString = function($value) {
					if (is_string($value))
						return "'" . $value . "'";
					return $value;
				};
				foreach ($formFields[$fieldName]['visibleConditions'] as $k => $v) {
					$jsCompCondFieldName = StringHelper::convertToJsVarName($k);

					if (is_array($v)) {
						if (($v[0] == '<') || ($v[0] == '<=') || ($v[0] == '>') || ($v[0] == '>=') || ($v[0] == '!=') || ($v[0] == '!=='))
							$jsFormula[] = "{$jsCompCondFieldName} {$v[0]} " . $fnEncloseString($v[1]);
						else
							$jsFormula[] = "({$jsCompCondFieldName} == " . implode(") || ({$jsCompCondFieldName} == ", $fnEncloseString($v)) . ')';
					} else if (($k == 'js') || ($k == 'JS'))
						$jsFormula[] = "{$v}";
					else
						$jsFormula[] = "{$jsCompCondFieldName} == " . $fnEncloseString($v);
				}
				$jsFormula = '(' . implode(') && (', $jsFormula) . ')';
				$setCommands[] = "if ({$jsFormula}) \$('#panel_{$formFields[$fieldName]['id']}').fadeIn(150); else \$('#panel_{$formFields[$fieldName]['id']}').fadeOut(50);";
			}
			$setCommands = implode("\n\t", $setCommands);

			// Html::dump($checkingFields);
			$getCommands = [];
			$events = [];
			foreach ($checkingFields as $fieldName => $val) {
				if (($fieldName == 'js') || ($fieldName == 'JS'))
					continue;

				if (!isset($formFields[$fieldName]))
					continue;

				$jsCompFieldName = StringHelper::convertToJsVarName($fieldName);
				$id = $formFields[$fieldName]['id'];
				switch ($formFields[$fieldName]['type']) {
					case static::FIELD_TEXT:
					case static::FIELD_TEXT_MULTILANGUAGE:
						break;

					case static::FIELD_TEXTAREA:
					case static::FIELD_TEXTAREA_MULTILANGUAGE:
						break;

					case static::FIELD_GEOMAP:
						break;

					case static::FIELD_PASSWORD:
						break;

					case static::FIELD_WIDGET:
						//check select2 and other widget types
						switch ($formFields[$fieldName]['widget']) {
							case Select2::class:
								// $events[] = "\$('#{$id}').on('select2:select', function(e) { checkPanelsVisibility(); })";
								// $events[] = "\$('#{$id}').on('select2:unselect', function(e) { checkPanelsVisibility(); })";
								$events[] = "\$('#{$id}').on('change', function(e) { checkPanelsVisibility(); })";
								$getCommands[] = "var {$jsCompFieldName} = \$('#{$id}').val();";
								break;
						}
						break;

					case static::FIELD_CHECKBOX:
					case static::FIELD_CHECKBOXLIST: //todo: check
						$events[] = "\$('#{$id}').on('change', function(e) { checkPanelsVisibility(); })";
						$getCommands[] = "var {$jsCompFieldName} = \$('#{$id}').is(':checked');";
						break;

					case static::FIELD_RADIOLIST:
						$events[] = "\$('#{$id}').on('change', function(e) { checkPanelsVisibility(); })";
						$getCommands[] = "var {$jsCompFieldName} = \$('#{$id} :checked').val();";
						break;
				}
			}
			$getCommands = implode("\n\t", $getCommands);
			$events = implode("\n", $events);

			$js =<<<JS
function checkPanelsVisibility() {
	{$getCommands}
	{$setCommands}
}
{$events}
checkPanelsVisibility();
JS;
			Yii::$app->view->registerJs($js, \yii\web\View::POS_READY);
		}

		$this->trigger('afterRender', new Event());

		//reset for preventing multi call of render()
		$this->fields = [];
		$this->footer = '';
	}

	protected function createWidget(&$field, $fieldObject, $class, $config)
	{
		/* @var $class \yii\base\Widget */
		$config['model'] = $fieldObject->model;
		$config['attribute'] = $fieldObject->attribute;
		$config['view'] = $fieldObject->form->getView();
		if (is_subclass_of($class, 'yii\widgets\InputWidget')) {
			foreach ($fieldObject->inputOptions as $key => $value) {
				if (!isset($config['options'][$key])) {
					$config['options'][$key] = $value;
				}
			}
			$config['field'] = $fieldObject;
			if (!isset($config['options'])) {
				$config['options'] = [];
			}
			if ($fieldObject->form->validationStateOn === ActiveForm::VALIDATION_STATE_ON_INPUT) {
				$fieldObject->addErrorClassIfNeeded($config['options']);
			}

			// $fieldObject->addAriaAttributes($config['options']);
			// $fieldObject->adjustLabelFor($config['options']);
		}

		ob_start();
		ob_implicit_flush(false);
		try {
			$class::begin($config);
			$field['widgetObject'] = $class::end();
		} catch (\Exception $e) {
				// close the output buffer opened above if it has not been closed already
				if (ob_get_level() > 0) {
						ob_end_clean();
				}
				throw $e;
		}
		$fieldObject->parts['{input}'] = ob_get_clean();

		return $fieldObject;
	}

}
