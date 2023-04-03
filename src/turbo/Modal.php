<?php
/**
 * @package yii2-widget-turbo
 * @author Simon Karlen <simi.albi@gmail.com>
 */

namespace shopack\base\frontend\turbo;

use yii\base\InvalidConfigException;
use yii\base\Widget;

class Modal extends Widget
{
    /**
     * @var string The PHP modal class to use
     */
    public string $modalClass = '\yii\bootstrap4\Modal';

    /**
     * @var array the HTML attributes for the widget container tag.
     *
     * @see \shopack\base\frontend\helpers\Html::renderTagAttributes() for details on how attributes are being rendered.
     */
    public array $options = [];

    /**
     * {@inheritDoc}
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if (!class_exists($this->modalClass)) {
            throw new InvalidConfigException("The class '{$this->modalClass}' does not exists.");
        }
        if (!isset($this->options['id'])) {
            throw new InvalidConfigException("The 'id' attribute must be set!");
        }
    }

    /**
     * {@inheritDoc}
     */
    public function run(): string
    {
        ob_start();
        $this->options['bodyOptions'] = ['class' => ['widget' => '']];
        call_user_func([$this->modalClass, 'begin'], $this->options);
        echo Frame::widget([
            'options' => [
                'id' => $this->options['id'] . '-frame'
            ]
        ]);
        call_user_func([$this->modalClass, 'end']);

        $this->registerPlugin();

        return ob_get_clean();
    }

    /**
     * Register js code
     */
    protected function registerPlugin()
    {
        $id = $this->options['id'];
        $js = <<<JS
jQuery(document).on('click.sa.turbo', '[data-turbo-frame="$id-frame"]', function (evt) {
    // debugger;
    var \$modal = jQuery('#$id'),
        \$this = jQuery(this),
        frame = \$modal.find('#$id-frame').get(0);

    evt.preventDefault();

    \$modal.modal('show');
    if (frame.src === \$this.attr('href')) {
        frame.reload();
    } else {
        frame.src = \$this.attr('href');
    }
});
JS;
        $this->view->registerJs($js);
    }
}
