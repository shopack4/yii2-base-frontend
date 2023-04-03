<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

/**
 * Interface UnserializerInterface
 *
 * @package shopack\base\frontend\rest
 */
interface UnserializerInterface
{
  /**
   * Unserialize data from JSON, XML, CSV, etc.
   * @param string $data
   * @param bool $asArray
   * @return mixed
   */
  public function unserialize($data, $asArray = true);

}
