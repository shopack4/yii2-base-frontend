<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use shopack\base\frontend\rest\RestClientActiveRecord;

/**
 * Interface RestClientQueryInterface
 * Query to REST interface
 *
 * @package shopack\base\frontend\rest
 */
interface RestClientQueryInterface
{
  /**
   * JSON header
   */
  const JSON_TYPE = 'application/json';

  /**
   * XML header
   */
  const XML_TYPE = 'application/xml';

  /**
   * GET request to collection
   * @return RestClientActiveRecord[]
   */
  public function all();

  /**
   * OPTIONS request to collection
   * @return int
   */
  public function count();

  /**
   * GET request to resource element by id
   * @param string $id
   * @return RestClientActiveRecord
   */
  public function one($id);

  /**
   * POST request
   * @param RestClientActiveRecord $model
   * @return RestClientActiveRecord
   * @internal param RestClientActiveRecord $payload
   */
  public function restCreate(RestClientActiveRecord $model);

  /**
   * PUT request
   * @param RestClientActiveRecord $model
   * @return RestClientActiveRecord
   * @internal param RestClientActiveRecord $payload
   */
  public function restUpdate(RestClientActiveRecord $model);

  /**
   * Set fields to select
   * @param array $fields
   * @return RestClientQuery
   */
  public function select(array $fields);

  /**
   * Add conditions to filter in request to collection
   * @param array $conditions
   * @return RestClientQuery
   */
  public function where(array $conditions);

  /**
   * Set limit to request to collection
   * @param int $limit
   * @return RestClientQuery
   */
  public function limit($limit);

  /**
   * Set offset to request to collection
   * @param int $offset
   * @return RestClientQuery
   */
  public function offset($offset);

}
