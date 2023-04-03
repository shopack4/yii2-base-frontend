<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\helpers;

use Yii;
use shopack\base\common\helpers\Url;
use shopack\base\common\helpers\ArrayHelper;

class Html extends \yii\bootstrap5\Html
{
	public static function createButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Create');

		$url = array_replace_recursive(['create'], $url ?? []);

		return static::a($text, $url, array_replace_recursive([
			'title' => Yii::t('app', 'Create'),
			'class' => ['btn', 'btn-sm', 'btn-success'],
			'modal' => true,
      // 'data-popup-size' => 'lg',
	 	], $options));
	}

	public static function updateButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Update');

		$url = array_replace_recursive(['update'], $url ?? []);

		$modal = ArrayHelper::remove($options, 'modal', true);

		return static::a($text, $url, array_replace_recursive([
			'title' => Yii::t('app', 'Update'),
			'class' => ['btn', 'btn-sm', 'btn-primary'],
			'modal' => $modal,
      // 'data-popup-size' => 'lg',
	 	], $options));
	}

	public static function confirmButton($text, $url, $message, $options = []) {
		$disabled = ArrayHelper::remove($options, 'disabled', false);
		if ($disabled) {
			$options['data'] = array_merge($options['data'] ?? [], [
				'href' => Url::to($url),
			]);

			$url = 'javascript:void(0)';

			$options['class'] = array_merge($options['class'] ?? [], [
				'disabled'
			]);
		}

		return static::a($text, $url, array_replace_recursive([
			'title' => $text,
			'class' => ['btn', 'btn-sm', 'btn-danger'],
			'data' => [
				'confirm' => $message,
				'method' => 'post',
				'params' => [
					'confirmed' => 1,
				],
				'data-pjax' => '0',
			],
	 	], $options));
	}

	public static function deleteButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Delete');

		$url = array_replace_recursive(['delete'], $url ?? []);

		return self::confirmButton($text, $url, Yii::t('app', 'Are you sure you want to delete this item?'), $options);
	}

	public static function undeleteButton($text = null, $url = null, $options = []) {
		if ($text == null)
			$text = Yii::t('app', 'Undelete');

		$url = array_replace_recursive(['undelete'], $url ?? []);

		return self::confirmButton($text, $url, Yii::t('app', 'Are you sure you want to un-delete this item?'), $options);
	}

	public static function a($text, $url = null, $options = []) {
		if (ArrayHelper::remove($options, 'modal', false)) {
			Html::addCssClass($options, 'showModalButton');

			if (empty($options['value']) && !empty($url))
				$options['value'] = Url::to($url);

			$url = null;

			if (empty($options['title']))
				$options['title'] = $text;
		}

		if (isset($options['method']) && (strcasecmp($options['method'], 'get') != 0)) {
			return Html::beginForm($url, $options['method'], $options['form'] ?? null) //['csrf' => false])
			. Html::submitButton($text, $options['button'] ?? null)
			. Html::endForm();
		}

		return parent::a($text, $url, $options);
	}

	/*public static function a($text, $url = null, $options = [])
	{
		$token = 'aaa';



		if (empty($token) == false && ($url !== null)) {
			$url = Url::to($url);

			if ((str_starts_with($url, 'http://') == false)
				&& (str_starts_with($url, 'https://') == false))
			{
				//local



			}
		}

		return parent::a($text, $url, $options);
	}*/

	public static function pre($value, $options = [])
	{
		return Html::tag('pre', $value, $options);
	}
	public static function btn($value, $options = [])
	{
		return Html::tag('btn', $value, $options);
	}
	public static function div($value, $options = [])
	{
		return Html::tag('div', $value, $options);
	}
	public static function span($value, $options = [])
	{
		return Html::tag('span', $value, $options);
	}
	public static function p($value, $options = [])
	{
		return Html::tag('p', $value, $options);
	}

	public static function formErrorSummary($models, $options = [])
	{
		return static::span('', ['id' => 'errorspan']) . static::errorSummary($models, $options);
	}

	public static function activeSubmitButton($model, $caption = null, $options = [])
	{
		if (empty($caption))
			$caption = Yii::t('app', ($model->isNewRecord ?? true) ? 'Create' : 'Save Changes');
		else if (is_array($caption))
			$caption = $caption[$model->isNewRecord ?? true];

		// if (isset($options['done']))
			// shopack\base\widgets\ActiveForm::doneParam

		return Html::submitButton($caption, [
			'class' => 'btn btn-' . (($model->isNewRecord ?? true) ? 'success' : 'primary'),
		]);
	}

	public static function formatRowDates(
		$createdAt, $createdBy,
		$updatedAt=null, $updatedBy=null,
		$removedAt=null, $removedBy=null
	) {
		$ret = [];

		if (empty($createdAt) == false) {
			$ret[] = 'ایجاد: ' .	Yii::$app->formatter->asJalaliWithTime($createdAt);
			if (!empty($createdBy))
				$ret[] = $createdBy->actorName();
		}

		if (empty($updatedAt) == false) {
			$ret[] = 'ویرایش: ' .	Yii::$app->formatter->asJalaliWithTime($updatedAt);
			if (!empty($updatedBy))
				$ret[] = $updatedBy->actorName();
		}

		if (empty($removedAt) == false) {
			$ret[] = 'حذف: ' .	Yii::$app->formatter->asJalaliWithTime($removedAt);
			if (!empty($removedBy))
				$ret[] = $removedBy->actorName();
		}

		return Html::tag('small', implode("<br>", $ret));
	}

}
