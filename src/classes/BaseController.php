<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\classes;

use Yii;

class BaseController extends \yii\web\Controller
{
	public function renderAjaxLegacy($view, $params = [])
	{
		return $this->getView()->renderAjaxLegacy($view, $params, $this);
	}

	public function renderAjaxModal($view, $params = [])
	{
		return $this->getView()->renderAjaxModal($view, $params, $this);
	}

	public function renderJson($resultArray)
	{
		Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
		return $resultArray;
	}

	public function redirect($url, $statusCode = 302)
	{
		if (is_array($url) && (strtolower($url[0] == 'post'))) {
			//make post

		}

		return parent::redirect($url, $statusCode);
	}

}
