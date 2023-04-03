<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\grid;

use shopack\base\frontend\helpers\Html;

class EnumDataColumn extends \kartik\grid\DataColumn
{
	public $enumClass;
	public $allowFilter = true;

	public function init()
	{
		$model = $this->grid->filterModel;
		$attribute = $this->attribute;

		$this->format = ['enum', $this->enumClass];

		if ($this->allowFilter) {
			$this->filter = Html::activeDropDownList(
				$model,
				$attribute,
				$this->enumClass::getList(),
				[
					'class' => 'form-control',
					'prompt' => '-- همه --',
					'encode' => false,
					// 'options' => $catOptions,
				]
			);
		}

		parent::init();
	}

	// protected function renderFilterCellContent()
	// {
	// 	$model = $this->grid->filterModel;
	// 	$attribute = $this->attribute;

	// 	return Html::activeDropDownList(
	// 		$model,
	// 		$attribute,
	// 		$this->enumClass::getList(),
	// 		[
	// 			'class' => 'form-control',
	// 			'prompt' => '-- همه --',
	// 			'encode' => false,
	// 			// 'options' => $catOptions,
	// 		]
	// 	);
	// }

}
