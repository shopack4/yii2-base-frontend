<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets\tabs;

// use Closure;
use Yii;
// use yii\base\Widget;
use shopack\base\common\helpers\Url;
use kartik\tabs\TabsX;
use shopack\base\common\helpers\ArrayHelper;
// use shopack\base\frontend\helpers\Html;
use shopack\base\frontend\widgets\tabs\TabsAsset;

class Tabs
{
	public $view = null;
	public $items = [];
	public $options = [];

	public static function begin($view, $options = [])
	{
		$tabs = new Tabs();
		$tabs->view = $view;
		$tabs->options = $options;
		return $tabs;
	}

	protected function _internal_beginTabPage($title, $active)
	{
		if (is_string($active))
			$active = [$active];
		$result = false;
		$fragment = (empty($_GET['fragment']) ? false : $_GET['fragment']);
		if (is_array($active) && $fragment) {
			foreach ($active as $a) {
				if ($a == $fragment) {
					$result = true;
					break;
				}
			}
			// if ($active !== true)
				// $active = false;
		} else {
			//activate first tab if fragment not exists
			if (($fragment === false) && empty($this->items))
				$result = true;
			// else
				// $active = false;
		}
// Html::dump($title, $active, $fragment, $result);
		return $result;
	}

	public function setActive($tabIndex)
	{
		if (!isset($this->items[$tabIndex]))
			return;

		array_walk($this->items, function (&$value, $key) use ($tabIndex) {
			$value['active'] = ($key == $tabIndex);
		});
	}

	public function beginTabPage($title, $active=false, $options=null)
	{
		$active = $this->_internal_beginTabPage($title, $active);
		$item = [
			'label' => $title,
			'active' => $active,
		];
		if (!empty($options))
			$item = array_replace_recursive($item, $options);
		$this->items[] = $item;
		$this->view->beginBlock(count($this->items) - 1, false);
		return count($this->items) - 1;
	}

	public function endTabPage()
	{
		$this->view->endBlock();
		$this->items[count($this->items) - 1]['content'] = $this->view->blocks[count($this->items) - 1];
	}

	public function newAjaxTabPage($title, $url, $active=false, $options=null)
	{
		if (is_array($url))
			$url = Url::to(ArrayHelper::merge($url, [
				'fragment' => $_GET['fragment'] ?? null,
				'anchor' => $_GET['anchor'] ?? null,
			]));

		$active = $this->_internal_beginTabPage($title, $active);
		$item = [
			'label' => $title,
			'active' => $active,
			'linkOptions' => [
				'data-url' => $url,
			],
		];

		if (!empty($options))
			$item = array_replace_recursive($item, $options);

		if (empty($item['content'])) {
			//loading indicator
			$item['content'] = "<div style='text-align:center'><img src='"
				. Url::to(['/images/loading17.gif']) . "' alt='" . Yii::t('site', 'Loading...') . "'></div>";
		}

		$this->items[] = $item;

		if ($active)
			echo "<span id='{$active}'></span>";
	}

	public function end()
	{
		if (empty($this->items))
			return;

		$activeFound = false;
		foreach ($this->items as $tab) {
			if ($tab['active'] ?? false) {
				$activeFound = true;
				break;
			}
		}
		if (!$activeFound)
			$this->items[0] = array_replace_recursive($this->items[0], ['active' => true]);

		$needAutoloadAjaxTab = false;
		foreach ($this->items as $k => $tab) {
			if (!empty($tab['linkOptions']['data-url']) && ($tab['active'] ?? false)) {
				$needAutoloadAjaxTab = true;
				break;
			}
		}

		if (!$needAutoloadAjaxTab)
			$needAutoloadAjaxTab = !empty($this->items[0]['linkOptions']['data-url']);

		if ($needAutoloadAjaxTab) {
			// $this->view->registerJs('$("document").ready(function() {
			// 	setTimeout(function() {
			// 		$(".tabs-krajee").find("li.active a").click();
			// 	}, 10);
			// });', \yii\web\View::POS_READY);
			$js =<<<JS
setTimeout(function() {
	$(".tabs-krajee").find("li.nav-item>a.active").click();
	$("document").ready(function() {
		checkMainAnchor();
	});
}, 10);
JS;
			$this->view->registerJs($js, \yii\web\View::POS_READY);
			//todo: set # to the url after ajax tab loaded
		}

		$loading = "<div style='text-align:center'><img src='"
			. Url::to(['/images/loading17.gif']) . "' alt='" . Yii::t('site', 'Loading...') . "'></div>";
		$options = [
			'items' => $this->items,
			// 'tabsPluginOptions' => false,

//TODO: https://computerrock.com/blog/html5-changing-the-browser-url-without-refreshing-page/
//change url in change tab for history and refreshing page

			'pluginEvents' => [
				"tabsX:beforeSend" => "function(event, jqXHR, settings) {
					tab = $(event.target.hash);
					tab.html(\"{$loading}\");
				}",
				"tabsX:error" => "function(event, jqXHR, status, message) {
					pane = $(event.target);
					pane.removeClass('kv-tab-loading');

					tab = $(event.target.hash);
					if ((jqXHR.responseText !== undefined) && (typeof jqXHR.responseText === 'string'))
						tab.html(jqXHR.responseText);
					else
						tab.html(message);
				}",
			],
		];
		if (!empty($this->options['pluginOptions']))
			$options = array_replace_recursive($options, $this->options['pluginOptions']);
// die(html::dump($options['pluginEvents']));
		$out = TabsX::widget($options);
		TabsAsset::register($this->view);
		echo $out;
	}

}
