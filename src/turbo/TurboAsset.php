<?php
/**
 * @package yii2-widget-turbo
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace shopack\base\frontend\turbo;

use yii\web\AssetBundle;
use yii\web\View;

//"npm-asset/hotwired--turbo": "^7.2",

class TurboAsset extends AssetBundle
{
  public $sourcePath = '@npm/hotwired--turbo/dist';

  public $js = [
    'turbo.es2017-umd.js',
    // 'turbo.es2017-esm.js',
  ];

  public $jsOptions = [
    'position' => View::POS_HEAD,
  ];

  // public $js = [
  //   // 'js/turbo.es2017-umd.js'
  //   '@npm/hotwired--turbo/dist/turbo.es2017-umd.js'
  // ];

  // public $jsOptions = [
  //   'position' => View::POS_HEAD
  // ];

  // public $publishOptions = [
  //     'only' => [
  //         'turbo.es2017-umd.js'
  //     ]
  // ];

	// public function init()
	// {
	// 	parent::init();


  // }

}
