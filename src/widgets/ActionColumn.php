<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use Yii;
use shopack\base\common\helpers\ArrayHelper;
use kartik\base\Lib;
use shopack\base\frontend\helpers\Html;

class ActionColumn extends \kartik\grid\ActionColumn
{
	const LABEL_ICON = 'I';
	const LABEL_TEXT = 'T';
	const LABEL_BOTH = 'B';

	public $lableMode = self::LABEL_BOTH;

	public $deleteOptions = [
		'data-params' => [
			'confirmed' => 1,
		],
	];

	public $undeleteOptions = [
		'data-params' => [
			'confirmed' => 1,
		],
	];

	public $updateOptions = [
		'modal' => true,
		// 'data-popup-size' => 'lg',
	];

	public function init()
	{
		$this->noWrap = true;

		parent::init();

		// if ($this->isVertical)
			// $this->template = str_replace(' ', "<br><br>", $this->template);

		if (!isset($this->viewOptions['class']))
			$this->viewOptions['class'] = 'btn btn-sm btn-info';

		if (!isset($this->updateOptions['class']))
			$this->updateOptions['class'] = 'btn btn-sm btn-primary';

		if (!isset($this->deleteOptions['class']))
			$this->deleteOptions['class'] = 'btn btn-sm btn-danger';

		if (!isset($this->undeleteOptions['class']))
			$this->undeleteOptions['class'] = 'btn btn-sm btn-warning';
	}

	protected function renderLabel(&$options, $title, $iconOptions = [])
	{
		$label = ArrayHelper::remove($options, 'label');
		if (is_null($label))
			$label = $title;

		$icon = '';
		if ($this->_isDropdown
				|| ($this->lableMode == self::LABEL_ICON)
				|| ($this->lableMode == self::LABEL_BOTH)) {
			$icon = $this->renderIcon($options, $iconOptions);

			if (Lib::strlen($icon) > 0)
				$label = ($this->lableMode == self::LABEL_ICON) ? $icon : ($icon . ' ' . $title);
		}

		return $label;
	}

	protected function initDefaultButtons()
	{
		parent::initDefaultButtons();

		$notBs3 = !$this->grid->isBs(3);
		$this->setDefaultButton('undelete', Yii::t('app', 'Undelete'), $notBs3 ? 'trash-alt' : 'trash');
	}

	protected function setDefaultButton($name, $title, $icon)
	{
			$notBs3 = !$this->grid->isBs(3);
			if (isset($this->buttons[$name])) {
					return;
			}
			$this->buttons[$name] = function ($url) use ($name, $title, $icon, $notBs3) {
					$opts = "{$name}Options";
					$options = ['title' => $title, 'aria-label' => $title, 'data-pjax' => '0'];
					if ($this->grid->enableEditedRow && $name != 'delete') {
							Html::addCssClass($options, 'enable-edited-row');
					}
					if ($name === 'delete') {
							$item = !empty($this->grid->itemLabelSingle) ? $this->grid->itemLabelSingle : Yii::t('kvgrid', 'item');
							$options['data-method'] = 'post';
							$options['data-confirm'] = Yii::t('kvgrid', 'Are you sure to delete this {item}?', ['item' => $item]);
					}
					// kz >>
					if ($name === 'undelete') {
						$item = !empty($this->grid->itemLabelSingle) ? $this->grid->itemLabelSingle : Yii::t('kvgrid', 'item');
						$options['data-method'] = 'post';
						$options['data-confirm'] = Yii::t('app', 'Are you sure to un-delete this {item}?', ['item' => $item]);
					}
					// kz <<
					$options = array_replace_recursive($options, $this->buttonOptions, $this->$opts);
					$label = $this->renderLabel($options, $title,
							['class' => $this->grid->getDefaultIconPrefix().$icon, 'aria-hidden' => 'true']);
					if (!$this->_isDropdown) {
							return Html::a($label, $url, $options);
					}
					if ($notBs3)  {
							Html::addCssClass($options, ['dropdown-item']);
					}
					$options['tabindex'] = '-1';
					$link = Html::a($label, $url, $options);
					return $notBs3 ? $link : "<li>{$link}</li>\n";
			};
	}

}
