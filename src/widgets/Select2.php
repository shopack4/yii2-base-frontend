<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

use yii\web\JsExpression;

class Select2 extends \kartik\widgets\Select2
{
	public function init()
	{
		if (isset($_GET['ajax_popupSize'])) {
			$this->pluginOptions = array_replace_recursive($this->pluginOptions, [
				'dropdownParent' => new JsExpression("$('#modal-{$_GET['ajax_popupSize']}')"),
			]);
		}

		parent::init();
	}

}
