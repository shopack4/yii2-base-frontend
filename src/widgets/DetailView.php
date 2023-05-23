<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use Yii;
use yii\base\InvalidConfigException;
use kartik\detail\DetailView as BaseDetailView;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\ArrayHelper;
// use shopack\multilanguage\helpers\LanguageHelper;

class DetailView extends BaseDetailView
{
	public $enableEditMode = false;
	// public $labelColOptions = ['style' => 'width:25%; white-space:nowrap'];
	public $labelColOptions = ['class' => ['w-25', 'text-nowrap']];
	public $hAlign = BaseDetailView::ALIGN_LEFT;
	public $vAlign = BaseDetailView::ALIGN_TOP;
	public $cols = 1;
	public $isVertical = true;

	public function init()
	{
		if ($this->cols > 1)
		{
			$section = [];
			$attrs = [];
			$lastcol = [];
			foreach ($this->attributes as $attr)
			{
				if (ArrayHelper::getValue($attr, 'group', false)) {
					if (!empty($section)) {
						$attrs = ArrayHelper::merge($attrs, $this->columnifySection($section));
						$section = [];

						$c = ArrayHelper::getValue($attr, 'cols', null);
						if ($c !== null)
							$this->cols = $c;

						$this->isVertical = ArrayHelper::getValue($attr, 'isVertical', $this->isVertical);
					}

					if (empty($attr['label']) == false)
						$attrs = ArrayHelper::merge($attrs, [$attr]);
				} else {
					if (!is_array($attr)) {
						$attr = $this->parseAttributeItem($attr);
						// $attr = [
							// 'attribute' => $attr,
						// ];
					}

					$labelColOptions = $attr['labelColOptions'] ?? [];
					Html::addCssStyle($labelColOptions, ['width' => (30 / $this->cols) . '%']);
					$attr['labelColOptions'] = $labelColOptions;

					$valueColOptions = $attr['valueColOptions'] ?? [];
					Html::addCssStyle($valueColOptions, ['width' => (70 / $this->cols) . '%']);
					$attr['valueColOptions'] = $valueColOptions;

					$section[] = $attr;
				}
			}

			if (!empty($section)) {
				$attrs = ArrayHelper::merge($attrs, $this->columnifySection($section));
				$section = [];
			}

			$this->attributes = $attrs;
		}

		parent::init();
	}

	protected function columnifySection($section)
	{
		if (empty($section))
			return [];

		if ($this->cols == 1)
			return $section;

		$rows = ceil(count($section) / $this->cols);
		$attrs = [];

		for ($r=0; $r<$rows; $r++) {
			$cols = [];
			for ($c=0; $c<$this->cols; $c++) {
				if ($this->isVertical)
					$idx = ($c * $rows) + $r;
				else
					$idx = ($r * $this->cols) + $c;

				if (isset($section[$idx]))
					$cols[] = $section[$idx];
				else {
					$_cc = count($cols);
					$attr = $cols[$_cc - 1];
					$remain_vcw = 100 - ((100 / $this->cols) * ($_cc - 1)) - (30 / $this->cols);

					$valueColOptions = $attr['valueColOptions'] ?? [];
					Html::addCssStyle($valueColOptions, ['width' => $remain_vcw . '%']);
					$attr['valueColOptions'] = $valueColOptions;

					$cols[$_cc - 1] = $attr;

					break;
				}
			}
			$attrs[] = [
				'columns' => $cols,
			];
		}
		return $attrs;
	}

	protected function parseAttributeItem($attribute)
	{
		if (is_string($attribute))
		{
			// if (!preg_match('/^([\w\.]+)(:([\\\[\],=> \'\"\w]*))?(:(.*))?$/', $attribute, $matches))
			if (!preg_match('/^([\w\.]+)(:([^:]*))?(:(.*))?$/', $attribute, $matches))
			{
				throw new InvalidConfigException(
						'The attribute must be specified in the format of "attribute", "attribute:format" or ' .
						'"attribute:format:label"'
					);
			}
			$attribute = [
				'attribute' => $matches[1],
				'format' => isset($matches[3]) ? $matches[3] : 'text',
				'label' => isset($matches[5]) ? $matches[5] : null,
			];
			if ((strlen($attribute['format']) > 0) && ($attribute['format'][0] == '['))
			{
				$attribute['format'] = substr($attribute['format'], 1, -1);
				$a = null;
				eval('$a = [' . $attribute['format'] . '];');
				$attribute['format'] = $a;
			}
		}

		return parent::parseAttributeItem($attribute);
	}

}

/*$behavior = $this->model->behaviors();
if (!empty($behavior[LanguageHelper::BehaviorKey])
		&& !empty($behavior[LanguageHelper::BehaviorKey]['rules']))
{
	$mlAttrs = $behavior[LanguageHelper::BehaviorKey]['rules'];
	if (count($mlAttrs) > 0)
	{
		$lngMap = ArrayHelper::map(LanguageHelper::getLanguages(), 'lngCode', 'lngName');
		$attrs = [];
		foreach ($this->attributes as $attr)
		{
			if (is_array($attr))
			{
				if (isset($attr['attribute']))
					$name = $attr['attribute'];
				else
					$name = null;
			}
			else
				$name = $attr;
			if (($name !== null) && (array_key_exists($name, ArrayHelper::map($mlAttrs, 0, 1))))
			{
				foreach ($lngMap as $lngCode => $lngName)
				{
					if (is_array($attr))
						$f = $attr;
					else
						$f = ['attribute' => $attr];
					$f['attribute'] .= '_' . $lngCode;

					$attrs[] = $f;
				}
			}
			else
				$attrs[] = $attr;
		}
		$this->attributes = $attrs;
	}
}
*/
