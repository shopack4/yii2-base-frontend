<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\widgets;

class PopoverX extends \kartik\popover\PopoverX
{
	public function init()
	{
		$this->options = array_merge_recursive($this->options ?? [], [
			'style' => [
				'display' => 'none',
			],
		]);

		parent::init();
	}

}
