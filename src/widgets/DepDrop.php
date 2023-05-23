<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use Yii;
use yii\web\JsExpression;

class DepDrop extends \kartik\widgets\DepDrop
{
	public function init()
	{
		$isModal = Yii::$app->request->isAjax;
		if ($isModal) {
			$ajax_popupSize = 'sm';
			if (isset($_GET['ajax_popupSize']))
				$ajax_popupSize = $_GET['ajax_popupSize'];

			$this->select2Options = array_replace_recursive($this->select2Options, [
				'pluginOptions' => [
					'dropdownParent' => new JsExpression("$('#modal-{$ajax_popupSize}')"),
				],
			]);
		}

		parent::init();
	}

}
