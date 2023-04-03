<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use yii\helpers\Json;
use shopack\base\frontend\rest\Unserializer;

/**
 * Class JsonUnserializer
 *
 * @package shopack\base\frontend\rest
 */
class JsonUnserializer extends Unserializer
{
  /**
   * @param string $data
   * @param bool $asArray
   * @return mixed
   */
  public function unserialize($data, $asArray = true)
  {
    return Json::decode($data, $asArray);
  }

}
