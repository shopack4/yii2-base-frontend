<?php
/**
 * @package yii2-widget-turbo
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace shopack\base\frontend\turbo;

use Yii;
use yii\base\InvalidConfigException;
use yii\base\Widget;
use shopack\base\frontend\helpers\Html;
use shopack\base\common\helpers\Url;

/**
 *
 */
class Frame extends Widget
{
    /**
     * @var array the HTML attributes for the widget container tag. The following special options are recognized:
     *
     * - `src`: array|string, the source to be loaded by frame. Will be processed by [[\shopack\base\common\helpers\Url::to()]] if set.
     *
     * @see \shopack\base\common\helpers\Url::to() for details on how src option is being processed.
     * @see \shopack\base\frontend\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $options = [];

    /**
     * @var bool Whether or not the content should not be loaded until the frame is visible.
     */
    public bool $lazyLoading = false;

    /**
     * @var bool Prevents any navigation if true.
     */
    public bool $disabled = false;

    /**
     * @var string|null Refers to another [[Frame]] element by id to be navigated when a descendant `<a>` is
     * clicked. When `_top`, navigate the window.
     */
    public ?string $target;

    /**
     * @var bool Controls whether or not to scroll a [[Frame]] element (and its descendant [[Frame]] elements)
     * into view when after loading.
     */
    public bool $autoscroll = false;

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init(): void
    {
        parent::init();

        if (!isset($this->options['id'])) {
            throw new InvalidConfigException("The 'id' attribute must be set!");
        }
        if (isset($this->options['src'])) {
            $this->options['src'] = Url::to($this->options['src']);

            if ($this->lazyLoading && !isset($this->options['loading'])) {
                $this->options['loading'] = 'lazy';
            }
        }
        if ($this->disabled && !isset($this->options['disabled'])) {
            $this->options['disabled'] = true;
        }
        if ($this->autoscroll && !isset($this->options['autoscroll'])) {
            $this->options['autoscroll'] = true;
        }
        if (isset($this->target) && !isset($this->options['target'])) {
            $this->options['target'] = $this->target;
        }

        if ($this->getRequestTurboFrame() === $this->options['id']) {
            if (isset($this->options['src'])) {
                unset($this->options['src']);
            }
            $view = $this->getView();
            if (Yii::$app->hasModule('debug')) {
                $module = Yii::$app->getModule('debug');
                $view->off($view::EVENT_END_BODY, [$module, 'renderToolbar']);
            }
            $view->clear();
            $view->beginPage();
            echo Html::beginTag('turbo-frame', $this->options);
            $view->head();
            $view->beginBody();
        } else {
            echo Html::beginTag('turbo-frame', $this->options);
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run()
    {
        if ($this->getRequestTurboFrame() === $this->options['id']) {
            $view = $this->getView();
            $view->endBody();
            $view->endPage(true);
            echo Html::endTag('turbo-frame');
            Yii::$app->end();

            return;
        }

        TurboAsset::register($this->view);
        echo Html::endTag('turbo-frame');
    }

    /**
     * Check if turbo is needed
     * @return string|null
     */
    protected function getRequestTurboFrame(): ?string
    {
        $headers = Yii::$app->getRequest()->getHeaders();

        return $headers->get('Turbo-Frame');
    }
}
