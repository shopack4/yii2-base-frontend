<?php
namespace shopack\base\frontend\web;

use Yii;
// use shopack\base\helpers\Url;
// use shopack\base\helpers\Html;
// use shopack\base\helpers\ArrayHelper;
// use yii\helpers\StringHelper;

class SideNav extends \kartik\sidenav\SideNav
{
	// public $activeSubmenuTemplate = "\n<ul style='display:block;'>\n{items}\n</ul>\n";
	// public $linkTemplate = '<a href="{url}">{icon}{label}</a>';
	// public $labelTemplate = '{icon}{label}';

	protected function isItemActive($item)
	{
		if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0]))
		{
			$route = Yii::getAlias($item['url'][0]);
			if ($route[0] !== '/' && Yii::$app->controller)
				$route = Yii::$app->controller->module->getUniqueId() . '/' . $route;

			$route = ltrim($route, '/');

			if (str_ends_with($route, '/index'))
				$route = substr($route, 0, (-1) * strlen('/index'));

			$route = rtrim($route, '/');
			$route .= "/";
			/*
			 * $pos = strpos($route, $this->route);
			 * $pos2 = strpos($this->route, $route);
			 * Yii::info("route({$route}) this->route({$this->route}) pos({$pos},{$pos2})", __METHOD__);
			 */
			if (strpos($this->route, $route) === 0)
				return true;
		}

		return parent::isItemActive($item);
	}

	// protected function renderItems($items)
	// {
	// 	$n = count($items);
	// 	$lines = [];
	// 	foreach ($items as $i => $item)
	// 	{
	// 		$options = array_merge($this->itemOptions, ArrayHelper::getValue($item, 'options', []));
	// 		$tag = ArrayHelper::remove($options, 'tag', 'li');
	// 		$class = [];
	// 		if ($item['active'])
	// 			$class[] = $this->activeCssClass;

	// 		if ($i === 0 && $this->firstItemCssClass !== null)
	// 			$class[] = $this->firstItemCssClass;

	// 		if ($i === $n - 1 && $this->lastItemCssClass !== null)
	// 			$class[] = $this->lastItemCssClass;

	// 		if (!empty($class))
	// 		{
	// 			if (empty($options['class']))
	// 				$options['class'] = implode(' ', $class);
	// 			else
	// 				$options['class'] .= ' ' . implode(' ', $class);
	// 		}

	// 		$menu = $this->renderItem($item);
	// 		if (!empty($menu))
	// 		{
	// 			if (!empty($item['items']))
	// 			{
	// 				if ($item['active'])
	// 					$submenuTemplate = ArrayHelper::getValue($item, 'activeSubmenuTemplate', $this->activeSubmenuTemplate);
	// 				else
	// 					$submenuTemplate = ArrayHelper::getValue($item, 'submenuTemplate', $this->submenuTemplate);

	// 				$menu .= strtr($submenuTemplate, [
	// 					'{items}' => $this->renderItems($item['items'])
	// 				]);
	// 			}
	// 			$lines[] = Html::tag($tag, $menu, $options);
	// 		}
	// 	}

	// 	return implode("\n", $lines);
	// }

	// protected function renderItem($item)
	// {
	// 	if (isset($item['url']))
	// 	{
	// 		return Html::link([
	// 				'icon' => (empty($item['icon']) ? '' : Html::icon($item['icon'])),
	// 				'text' => $item['label'],
	// 			],
	// 			$item['url'],
	// 			[
	// 				'permission' => false,
	// 			]
	// 		);
	// 	}
	// 	else
	// 	{
	// 		$template = ArrayHelper::getValue($item, 'template', $this->labelTemplate);
	// 		return strtr($template, [
	// 			'{label}' => $item['label'],
	// 			'{icon}' => (empty($item['icon']) ? '' : Html::icon($item['icon']))
	// 		]);
	// 	}
	// }

}
