<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use yii\web\JsExpression;

class DepDrop extends \kartik\widgets\DepDrop
{
	public function init()
	{
		if (isset($_GET['ajax_popupSize'])) {
			$this->select2Options = array_replace_recursive($this->select2Options, [
				'pluginOptions' => [
					'dropdownParent' => new JsExpression("$('#modal-{$_GET['ajax_popupSize']}')"),
				],
			]);
		}

		parent::init();
	}

}
