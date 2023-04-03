<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use Yii;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use Psr\Http\Message\ResponseInterface;
use yii\base\Component;
use yii\base\InvalidCallException;
use yii\base\InvalidParamException;
use yii\base\InvalidArgumentException;
use yii\web\HttpException;
use yii\web\ServerErrorHttpException;
use shopack\base\frontend\rest\RestClientQueryInterface;
// use shopack\base\frontend\rest\Model;
use shopack\base\frontend\rest\RestClientActiveRecord;
use shopack\base\common\helpers\Url;

/**
 * Class RestClientQuery
 * HTTP transport by GuzzleHTTP
 *
 * @package shopack\base\frontend\rest
 */
class RestClientQuery
  extends \yii\db\ActiveQuery //Component
  implements RestClientQueryInterface
{
  // use ActiveRelationTrait;

  /**
   * Data type for requests and responses
   * Required.
   * @var string
   */
  public $dataType = self::JSON_TYPE;

  /**
   * Headers for requests
   * @var array
   */
  public $requestHeaders = [];

  /**
   * Wildcard for response headers object
   * @see RestClientQuery::count()
   * @var array
   */
  public $responseHeaders = [
    'totalCount'    => 'X-Pagination-Total-Count',
    'pageCount'     => 'X-Pagination-Page-Count',
    'currPage'      => 'X-Pagination-Current-Page',
    'perPageCount'  => 'X-Pagination-Per-Page',
    'links'         => 'Link',
  ];

  /**
   * Response unserializer class
   * @var array|object
   */
  public $unserializers = [
    self::JSON_TYPE => [
      'class' => 'shopack\base\frontend\rest\JsonUnserializer'
    ]
  ];

  /**
   * HTTP client that performs HTTP requests
   * @var object
   */
  public $httpClient;

  /**
   * Configuration to be supplied to the HTTP client
   * @var array
   */
  public $httpClientExtraConfig = [];

  /**
   * Model class
   * @var RestClientActiveRecord
   */
  public $modelClass;

  /**
   * Get param name for select fields
   * @var string
   */
  public $selectFieldsKey = 'fields';

  public $filterKey = 'filter';

  public $orderByKey = 'order-by';

  /**
   * Request LIMIT param name
   * @see shopack\base\frontend\rest\RestClientActiveRecord::$limitKey
   * @var string
   */
  public $limitKey;

  /**
   * Request OFFSET param name
   * @see shopack\base\frontend\rest\RestClientActiveRecord::$offsetKey
   * @var string
   */
  public $offsetKey;

  /**
   * Model class envelope
   * @see shopack\base\frontend\rest\RestClientActiveRecord::$collectionEnvelope
   * @var string
   */
  protected $_collectionEnvelope;

  /**
   * Model class pagination envelope
   * @see shopack\base\frontend\rest\RestClientActiveRecord::$paginationEnvelope
   * @var string
   */
  protected $_paginationEnvelope;

  /**
   * Model class pagination envelope keys mapping
   * @see shopack\base\frontend\rest\RestClientActiveRecord::$paginationEnvelopeKeys
   * @var array
   */
  private $_paginationEnvelopeKeys;

  /**
   * Pagination data from pagination envelope in GET request
   * @var array
   */
  private $_pagination;

  /**
   * Array of fields to select from REST
   * @var array
   */
  private $_select = [];

  /**
   * Conditions
   * @var array
   */
  // private $_where;

  /**
   * RestClientQuery limit
   * @var int
   */
  private $_limit;

  /**
   * RestClientQuery offset
   * @var int
   */
  private $_offset;

  /**
   * Flag Is this query is sub-query
   * to prevent recursive requests
   * for get enveloped pagination
   * @see RestClientQuery::count()
   * @var bool
   */
  private $_subQuery = false;

  //used for creating url
  private $_urlParameters = null;

  public $_endpoint;

  /**
   * Constructor. Really.
   * @param RestClientActiveRecord $modelClass
   * @param array $config
   */
  public function __construct($modelClass, $config = [])
  {
    parent::__construct($config);

    $modelClass::staticInit();
    $this->modelClass = $modelClass;
    $this->_collectionEnvelope = $modelClass::$collectionEnvelope;
    $this->_paginationEnvelope = $modelClass::$paginationEnvelope;
    $this->_paginationEnvelopeKeys = $modelClass::$paginationEnvelopeKeys;
    $this->offsetKey = $modelClass::$offsetKey;
    $this->limitKey = $modelClass::$limitKey;

    $this->requestHeaders = ['Accept' => RestClientQuery::JSON_TYPE];
    if (Yii::$app->request->headers->has('Authorization'))
      $this->requestHeaders['Authorization'] = Yii::$app->request->headers->get('Authorization');
    else {
      // if (Yii::$app->request->cookies->has('token'))
      // $headers[] = 'Authorization Bearer ' . Yii::$app->request->cookies->get('token');
      $jwt = Yii::$app->user->getJwtByCookie();
      if ($jwt !== null)
        $this->requestHeaders['Authorization'] = 'Bearer ' . $jwt;
    }

    $httpClientConfig = array_merge(
      [
        /* @link http://docs.guzzlephp.org/en/latest/quickstart.html */
        'base_uri' => $this->_getUrl('api'),
        /* @link http://docs.guzzlephp.org/en/latest/request-options.html#headers */
        'headers' => $this->_getRequestHeaders(),
      ],
      $this->httpClientExtraConfig
    );
    $this->httpClient = new Client($httpClientConfig);
  }

  public function andWhere($condition, $params = [])
  {
    if (empty($this->where))
      $this->where = null;

    return parent::andWhere($condition, $params);
  }

  public function orWhere($condition, $params = [])
  {
    if (empty($this->where))
      $this->where = null;

    return parent::orWhere($condition, $params);
  }

  protected function fillWhereByRelation()
  {
    if (empty($this->primaryModel))
      return;

    $thisKey = array_keys($this->link)[0];

    // $this->where = is_array($this->where) ? $this->where : [];
    ///@TODO: make correct finding in complex where array: ['or', ['and', ['like', ...
    if (isset($this->where[$thisKey]))
      return;

    $primaryKey = array_values($this->link)[0];

    $this->andWhere([
      $thisKey => $this->primaryModel->$primaryKey
    ]);
  }

  protected function getIdFromWhere()
  {
    $id = null;
    $primaryKey = $this->modelClass::primaryKey();

    if (isset($primaryKey[0])) {
      $this->where = is_array($this->where) ? $this->where : [];
      ///@TODO: make correct finding in complex where array: ['or', ['and', ['like', ...

      foreach ($this->where as $k => $v) {
        if ($k == $primaryKey[0]) {
          $id = $v;
          unset($this->where[$k]);
          break;
        }
      }
    }

    return $id;
  }

  /**
   * GET resource collection request
   * @inheritdoc
   */
  public function all($db = null)
  {
    $this->fillWhereByRelation();

    $response = $this->_request('get', $this->_getUrl('collection'), [
      'query' => $this->_buildQueryParams(),
    ]);

    return $this->_populate($response);
  }

  /**
   * Get collection count
   * If $this->_pagination isset (from get request before call this method) return count from it
   * else execute HEAD request to collection and get count from X-Pagination-Total-Count(default) response header
   * If header is empty and isset pagination envelope - do get collection request with limit 1 to get pagination data
   * @see RestClientQuery::$_subQuery
   * @inheritdoc
   */
  public function count($q = '*', $db = null)
  {
    $this->fillWhereByRelation();

    if ($this->_pagination)
      return isset($this->_pagination['totalCount']) ? (int) $this->_pagination['totalCount'] : 0;

    if ($this->_subQuery)
      return 0;

    // try to get count by HEAD request
    $response = $this->_request('head', $this->_getUrl('collection'), [
      'query' => $this->_buildQueryParams(),
    ]);
    $count = $response->getHeaderLine($this->responseHeaders['totalCount']);

    // REST server not allow HEAD query and X-Total header is empty
    if ($count === '' && $this->_paginationEnvelope) {
      $query = clone $this;
      $query->_setSubQueryFlag()->offset(0)->limit(1)->all();
      return $query->count();
    }

    return (int) $count;
  }

  /**
   * GET resource element request
   * @inheritdoc
   */
  public function one($id = null)
  {
    $this->fillWhereByRelation();

    if (empty($id))
      $id = $this->getIdFromWhere();

    if (empty($id))
      return new $this->modelClass;
  		// throw new InvalidArgumentException('The id not provided');

    $response = $this->_request('get', $this->_getUrl('element', $id), [
      'query' => $this->_buildQueryParams(),
    ]);

    return $this->_populate($response, false);
  }

  /**
   * return : null|array of multipart attriutes
   */
  protected function convertForFileDataIfHas($attributes)
  {
    $hasFileData = false;
    foreach ($attributes as $k => $v) {
      if ($v instanceof FileData) {
        $hasFileData = true;
        break;
      }
    }

    if ($hasFileData == false)
      return false;

    $multipartAttributes = [];

    foreach ($attributes as $k => $v) {
      if ($v instanceof FileData) {
        $multipartAttributes[] = [
          'name' => $k,
          'contents' => fopen($v->tmp_name, 'r'),
          'filename' => $v->name,
        ];
      } else {
        $multipartAttributes[] = [
          'name' => $k,
          'contents' => $v,
        ];
      }
    }

    return $multipartAttributes;
  }

  /**
   * POST request
   * @inheritdoc
   */
  public function restCreate(RestClientActiveRecord $model)
  {
    $options = [];

    $attributes = array_filter($model->getAttributes());
    $multipartAttributes = $this->convertForFileDataIfHas($attributes);
    if ($multipartAttributes) {
      $options['multipart'] = $multipartAttributes;
    } else {
      $options['json'] = $attributes;
    }

    $response = $this->_request('post', $this->_getUrl('element', $model->getParentKey()), $options);

    return $this->_populate($response, false, $model);
  }

  /**
   * PUT request
   * // TODO non-json (i.e. form-data) payload
   * @inheritdoc
   */
  public function restUpdate(RestClientActiveRecord $model)
  {
    $options = [];

    $attributes = $model->getDirtyAttributes();
    $multipartAttributes = $this->convertForFileDataIfHas($attributes);
    if ($multipartAttributes) {
      $options['multipart'] = $multipartAttributes;
    } else {
      $options['json'] = $attributes;
    }

    $options['query'] = $this->_buildQueryParams();

    $response = $this->_request('put', $this->_getUrl('element', $model->getPrimaryKey()), $options);

    return $this->_populate($response, false);
  }

	public function restUpdateAll($attributes, $condition = '', $params = [])
	{
    if (isset($condition)) {
      foreach ((array)$condition as $name => $value) {
        $this->andWhere([$name => $value], $params);
      }
    }

    $id = $this->getIdFromWhere();

    $options = [];

    $multipartAttributes = $this->convertForFileDataIfHas($attributes);
    if ($multipartAttributes) {
      $options['multipart'] = $multipartAttributes;
    } else {
      $options['json'] = $attributes;
    }

    $options['query'] = $this->_buildQueryParams();

    $response = $this->_request('put', $this->_getUrl('element', $id), $options);

    $this->_populate($response, false);
	}

  public function restDelete(RestClientActiveRecord $model)
  {
    $response = $this->_request('delete', $this->_getUrl('element', $model->getPrimaryKey()), [
      'json' => $model->getAttributes(),
    ]);

    return $response->getStatusCode() === 204;
  }

  public function restDeleteAll($condition, $params = [])
	{
    if (isset($condition)) {
      foreach ((array)$condition as $name => $value) {
        $this->andWhere([$name => $value], $params);
      }
    }

    $id = $this->getIdFromWhere();

    $response = $this->_request('delete', $this->_getUrl('element', $id), [
      'query' => $this->_buildQueryParams(),
    ]);

    $this->_populate($response, false);

    $statusCode = $response->getStatusCode();
    return ($statusCode == 200);
	}

  /**
   * @inheritdoc
   */
  public function select($columns, $option = null)
  {
    $this->_select = $columns;
    return $this;
  }

  /**
   * @inheritdoc
   */
  // public function where($condition, $params = [])
  // {
  //   if (empty($this->_where))
  //     $this->_where = [$condition];
  //   else
  //     $this->_where[] = $condition;

  //   return $this;
  // }

  // public function andWhere($condition, $params = [])
  // {
  //   return $this->where($condition, $params);
  // }

  /**
   * @inheritdoc
   */
  public function limit($limit)
  {
    if (empty($limit))
      $this->_limit = null;
    else
      $this->_limit = (int)$limit;

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function offset($offset)
  {
    if (empty($offset))
      $this->_offset = null;
    else
      $this->_offset = (int)$offset;

    return $this;
  }

  public function endpoint($endpoint)
  {
    $this->_endpoint = $endpoint;
    return $this;
  }

  /**
   * HTTP request
   * @param string $method
   * @param string $url
   * @param array $options
   * @return ResponseInterface
   * @throws ServerErrorHttpException
   */
  private function _request($method, $url, array $options)
  {
    $rawQuery = $url;
    if (empty($options['query']) == false) {
      $rawQuery .= '?';
      $rawQuery .= \http_build_query($options['query'], '', '&');
    }

    $loggingCategory = __METHOD__ . '(' . $method . ')';
    $profile = YII_DEBUG; //$this->enableProfiling

    // if ($this->enableLogging)
    Yii::info($rawQuery, $loggingCategory);

    $profile and Yii::beginProfile($rawQuery, $loggingCategory);
    try {
      $response = $this->httpClient->{$method}($url, $options);
      $profile and Yii::endProfile($rawQuery, $loggingCategory);

    } catch (ClientException $e) {
      $profile and Yii::endProfile($rawQuery, $loggingCategory);
      $response = $e->getResponse();

    } catch (ConnectException $e) {
      $profile and Yii::endProfile($rawQuery, $loggingCategory);
      $this->_throwServerError($e);

    } catch (RequestException $e) {
      $profile and Yii::endProfile($rawQuery, $loggingCategory);
      $this->_throwServerError($e);
    }

    return $response;
  }

  /**
   * Throw 500 error exception
   * @param \Exception $e
   * @throws ServerErrorHttpException
   */
  private function _throwServerError(\Exception $e)
  {
    $uri = (string) $this->httpClient->getConfig('base_uri');

    throw new ServerErrorHttpException(get_class($e).': url='.$uri .' '. $e->getMessage(), 500);
  }

  /**
   * Unserialize and create models
   * @param ResponseInterface $response
   * @param bool $asCollection
   * @return $this|RestClientActiveRecord|array|void
   * @throws HttpException
   */
  protected function _populate(ResponseInterface $response, $asCollection = true, RestClientActiveRecord $model = null)
  {
    $models = [];
    $statusCode = $response->getStatusCode();
    $data = $this->_unserializeResponseBody($response);

    // if (is_object($data)) {
    //   $dataAsArray = [(array)$data];
    // } else
    //   $dataAsArray = (array)$data;

    // errors
    if ($statusCode >= 400) {
      if (($model !== null) && ($statusCode === 422)) { // && count($data) === 1 && isset($data[0])) {
        $model->addError($data->field ?? null, $data->message);
        return $model;
      }

      throw new HttpException(
        $statusCode,
        is_string($data) ? $data : $data->message,
        $statusCode
      );
    }

    // array of objects or arrays - probably resource collection
    if (is_array($data)) {
      return $this->_createModels($data);
    }
    // collection with data envelope or single element
    if (is_object($data)) {
      if ($asCollection) {
        return $this->_populateAsCollection($data);
      }
      $models = $this->_createModels([$data])[0];
    }

    return $models;
  }

  /**
   * @param RestClientActiveRecord $data
   *
   * @return RestClientActiveRecord[]
   */
  protected function _populateAsCollection($data)
  {
    $elements = [];
    if ($this->_collectionEnvelope) {
      $elements = isset($data->{$this->_collectionEnvelope})
        ? $data->{$this->_collectionEnvelope}
        : [];
    }
    if ($this->_paginationEnvelope && isset($data->{$this->_paginationEnvelope})) {
      $this->_setPagination(
        $this->_getProps($data->{$this->_paginationEnvelope})
      );
    }
    return $this->_createModels($elements);
  }

  /**
   * Create models from array of elements
   * @param array $elements
   * @return array
   */
  protected function _createModels(array $elements)
  {
    $modelClass = $this->modelClass;
    $models = [];
    foreach ($elements as $element) {
      $attributes = $this->_getProps($element);
      $model      = $modelClass::instantiate($attributes); //->setAttributes($attributes);

      $modelClass::populateRecord($model, $attributes);

      //load relations
      $columns = array_flip($model->attributes());
      foreach ($attributes as $name => $value) {
        if (empty($value) || isset($columns[$name]))
          continue;

        $relatedMethodName = 'get' . ucfirst($name);
        if (method_exists($model, $relatedMethodName)) {
          $relation = call_user_func(array($model, $relatedMethodName));

          $relationModelClass = $relation->modelClass;

          $attrs = $this->_getProps($value);

          //TODO: check if $value is array for hasMany
          $relatedModel = $relationModelClass::instantiate($attrs);
          $relationModelClass::populateRecord($relatedModel, $attrs);
          $model->populateRelation($name, $relatedModel);
        }
      }

      if (!$this->asArray)
        $model->afterFind();

      //
      $models[] = $model;

      // $models[]   = $model->setId(
      //   $model->getAttribute($modelClass::primaryKey())
      // );
    }

    return $models;
  }

  /**
   * Try to unserialize response body data
   * @param ResponseInterface $response
   * @return object[]|object|string
   * @throws \yii\base\InvalidConfigException
   */
  protected function _unserializeResponseBody(ResponseInterface $response)
  {
    $body = (string) $response->getBody();
    $contentType = $response->getHeaderLine('Content-type');

    try {
      if (false !== stripos($contentType, $this->dataType)
        && isset($this->unserializers[$this->dataType])
      ) {
        /** @var UnserializerInterface $unserializer */
        $unserializer = \Yii::createObject($this->unserializers[$this->dataType]);
        if ($unserializer instanceof UnserializerInterface) {
          return $unserializer->unserialize($body, false);
        }
      }

      return $body;

    } catch (InvalidArgumentException $e) {
      return $body;

    } catch (InvalidParamException $e) {
      return $body;
    }
  }

  /**
   * Pagination data setter
   * If pagination data isset in GET request result
   * @param array $pagination
   * @return $this
   */
  private function _setPagination(array $pagination)
  {
    foreach ($this->_paginationEnvelopeKeys as $key => $name) {
      $this->_pagination[$key] = isset($pagination[$name])
        ? $pagination[$name]
        : null;
    }

    return $this;
  }

  /**
   * Get array of properties from object
   * @param $object
   * @return array
   */
  private function _getProps($object)
  {
    return is_object($object) ? get_object_vars($object) : $object;
  }

  public function addUrlParameter($name, $value)
  {
    if (isset($this->_urlParameters[$name]))
      $this->_urlParameters[$name] = array_merge((array)$this->_urlParameters[$name], [$value]);
    else
      $this->_urlParameters[$name] = $value;

    return $this;
  }

  /**
   * Build query params
   * @return array
   */
  private function _buildQueryParams()
  {
    $query = [];

    if (count($this->_select)) {
      $query[$this->selectFieldsKey] = implode(',', $this->_select);
    }

    $this->_buildQueryFilterPart($query);

    if (empty($this->orderBy) == false) {
      $orders = [];
      foreach ($this->orderBy as $name => $direction) {
        $orders[] = ($direction === SORT_DESC ? '-' : '') . $name;
        // $orders[] = $name . ($direction === SORT_DESC ? ' DESC' : '');
      }

      $query[$this->orderByKey] = implode(',', $orders);
    }

    if ($this->_limit !== null) {
      $query[$this->limitKey] = $this->_limit;
    }

    if ($this->_offset !== null) {
      if ($this->_limit === null)
        $query[$this->offsetKey] = $this->_offset;
      else
        $query[$this->offsetKey] = ($this->_offset / $this->_limit) + 1;
    }

    if (isset($this->_urlParameters)) {
      foreach ($this->_urlParameters as $name => $value) {
        if (is_array($value))
          $query[$name] = /*urlencode*/(implode(',', $value));
        else
          $query[$name] = /*urlencode*/($value);
      }
    }

    return $query;
  }

  private function _buildQueryFilterPart(&$query)
  {
    $this->where = is_array($this->where) ? $this->where : [];
    if (empty($this->where))
      return;

    $query[$this->filterKey] = json_encode($this->where);
  }

  /**
   * Get headers for request
   * @return array
   */
  private function _getRequestHeaders()
  {
    return $this->requestHeaders ?: ['Accept' => $this->dataType];
  }

  /**
   * Get url to collection or element of resource
   * with check base url trailing slash
   * @param string $type api|collection|element
   * @param string $id
   * @return string
   */
  private function _getUrl($type = 'base', $id = null)
  {
    $modelClass = $this->modelClass;

    if ($type == 'api')
      return $this->_trailingSlash($modelClass::getApiUrl());

    $collection = $modelClass::getResourceName();
    if (empty($this->_endpoint) == false)
      $collection .= '/' . $this->_endpoint;

    $url = $this->_trailingSlash($collection, false);

    if (($type == 'element') && (empty($id) == false))
      $url .= '/' . $this->_trailingSlash($id, false);

    return $url;

    // switch ($type) {
    //   case 'api':
    //     return $this->_trailingSlash($modelClass::getApiUrl());
    //     break;

    //   case 'collection':
    //     return $this->_trailingSlash($collection, false);
    //     break;

    //   case 'element':
    //     if (is_null($id))
    //       return $this->_trailingSlash($collection, false);
    //     return $this->_trailingSlash($collection) . $this->_trailingSlash($id, false);
    //     break;
    // }

    // return '';
  }

  /**
   * Check trailing slash
   * if $add - add trailing slash
   * if not $add - remove trailing slash
   * @param $string
   * @param bool $add
   * @return string
   */
  private function _trailingSlash($string, $add = true)
  {
    return substr($string, -1) === '/'
      ? ($add ? $string : substr($string, 0, strlen($string) - 1))
      : ($add ? $string . '/' : $string);
  }

  /**
   * Mark query as subquery to prevent queries recursion
   * @see count()
   * @return RestClientQuery
   */
  private function _setSubQueryFlag()
  {
    $this->_subQuery = true;
    return $this;
  }

}
