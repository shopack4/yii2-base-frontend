<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use Yii;
use shopack\base\frontend\rest\ModelInterface;
use shopack\base\frontend\rest\RestClientQuery;

//@deprecated, use RestClientActiveRecord

/**
 * Class Model
 *
 * @package shopack\base\frontend\rest
 */
abstract class Model extends \yii\base\Model implements ModelInterface
{
  /**
   * Url to REST API without resource name with trailing slash
   * Resource name will be added as postfix
   * @var string
   */
  public static $apiUrl;

  /**
   * REST resource name without trailing slash
   * @var string
   */
  public static $resourceName;

  /**
   * REST response data envelope, i.e. 'data'
   *
   * @var string
   */
  public static $collectionEnvelope = 'data';

  /**
   * REST response pagination envelope, i.e. 'pagination'
   * @var array
   */
  public static $paginationEnvelope = 'pagination';

  /**
   * REST response pagination envelope keys mapping
   * @var array
   */
  public static $paginationEnvelopeKeys = [
    'totalCount'    => 'total',
    'pageCount'     => 'pages',
    'currPage'      => 'offset',
    'perPageCount'  => 'limit',
    'links'         => 'links',
  ];

  /**
   * Request LIMIT param name
   * @var string
   */
  public static $limitKey = 'per-page';

  /**
   * Request OFFSET param name
   * @var string
   */
  public static $offsetKey = 'page';

  /**
   * @var string
   */
  public static $primaryKey;

  /**
   * Primary key value
   * @var
   */
  public $id;

  /**
   * Model errors
   * @var array
   */
  protected $_errors = [];

  /**
   * Model attributes with values
   * @var array
   */
  private $_attributes = [];

  /**
   * @var array|null old attribute values indexed by attribute names.
   * This is `null` if the record [[isNewRecord|is new]].
   */
  private $_oldAttributes;

  /**
   * @inheritdoc
   */
  public static function staticInit()
  {
    static::$apiUrl = Yii::$app->params['apiServerAddress'];
  }

  /**
   * @inheritdoc
   * @return string
   */
  public static function primaryKey()
  {
    return static::$primaryKey;
  }

  /**
   * @inheritdoc
   * @return string
   */
  public static function getApiUrl()
  {
    return static::$apiUrl;
  }

  /**
   * @inheritdoc
   * @return string
   */
  public static function getResourceName()
  {
    return static::$resourceName;
  }

  /**
   * @inheritdoc
   * @throws \yii\base\InvalidConfigException
   */
  public static function find()
  {
    $query = \Yii::createObject(RestClientQuery::class, [
      get_called_class()
      // ['requestHeaders' => $requestHeaders],
    ]);

    return $query;
  }

  /**
   * @inheritdoc
   */
  public static function findAll(array $conditions)
  {
    return static::find()->where($conditions)->all();
  }

  /**
   * @inheritdoc
   */
  public static function findOne($id)
  {
    return static::find()->one($id);
  }

  /**
   * @inheritdoc
   */
  public function getPrimaryKey()
  {
    return $this->id !== null ? $this->id : $this->getAttribute(static::$primaryKey)[0];
  }

  /**
   * @inheritdoc
   */
  public function setId($id)
  {
    $this->id = is_array($id) ? reset($id) : $id;

    return $this;
  }

  /**
   * @return
   */
  public function delete()
  {
    return static::find()->delete($this);
  }

  /**
   * @inheritdoc
   */
  public function save()
  {
    if (static::SCENARIO_CREATE === $this->getScenario()) {
      $model = static::find()->create($this);
      if ($model->hasErrors()) {
        return false;
      }

      return true;
    }

    if (static::SCENARIO_UPDATE === $this->getScenario()) {
      return static::find()->update($this);
    }

    return false;
  }

  //kz
  public function attributes()
  {
    $attributes = [];
    $rules = $this->rules();
    foreach ($rules as $k => $v) {
      if (is_array($k)) {
        foreach ($k as $k2 => $v2) {
          if (array_key_exists($k2, $attributes) == false)
            $attributes[$k2] = $k2;
        }
      } else {
        if (array_key_exists($k, $attributes) == false)
          $attributes[$k] = $k;
      }
    }
    return array_keys($attributes);
  }

  /**
   * @inheritdoc
   * @return array
   */
  public function fields()
  {
    $fields = array_keys($this->_attributes);
    return array_combine($fields, $fields);
  }

  /**
   * @inheritdoc
   */
  public function getAttributes($names = null, $except = [])
  {
    return $this->_attributes;
  }

  /**
   * @inheritdoc
   */
  public function setAttributes($values, $safeOnly = true)
  {
    $attributes = $values;
    $useForce   = ! $safeOnly;
    if ($useForce) {
      $this->_attributes = $attributes;
      return $this;
    }

    foreach ($attributes as $key => $val) {
      $this->_attributes[$key] = $val;
    }

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function getAttribute($name)
  {
    return isset($this->_attributes[$name])
      ? $this->_attributes[$name]
      : null;
  }

  /**
   * @inheritdoc
   */
  public function setAttribute($name, $value)
  {
    $this->_attributes[$name] = $value;

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function unsetAttribute($name)
  {
    unset($this->_attributes[$name]);

    return $this;
  }

  /**
   * @inheritdoc
   */
  public function hasAttribute($name)
  {
    // return isset($this->_attributes[$name]) || in_array($name, $this->getAttributes());
    return isset($this->_attributes[$name]) || in_array($name, $this->attributes());
  }

  //kz
  public static function populateRecord($record, $row)
  {
    $columns = array_flip($record->attributes());
    foreach ($row as $name => $value) {
      if (isset($columns[$name])) {
        $record->_attributes[$name] = $value;
      } elseif ($record->canSetProperty($name)) {
        $record->$name = $value;
      }
    }
    $record->_oldAttributes = $record->_attributes;
    $record->_related = [];
    $record->_relationsDependencies = [];
  }

  /**
   * @inheritdoc
   */
  public static function instantiate()
  {
    return new static;
  }

  /**
   * PHP getter magic method.
   * This method is overridden so that attributes and related objects can be accessed like properties.
   *
   * @param string $name property name
   * @throws \yii\base\InvalidParamException if relation name is wrong
   * @return mixed property value
   * @see getAttribute()
   */
  public function __get($name)
  {
    if (isset($this->_attributes[$name]) || array_key_exists($name, $this->_attributes)) {
      return $this->_attributes[$name];
    }

    if ($this->hasAttribute($name)) {
      return null;
    }

    return parent::__get($name);
  }

  /**
   * PHP setter magic method.
   * This method is overridden so that AR attributes can be accessed like properties.
   * @param string $name property name
   * @param mixed $value property value
   */
  public function __set($name, $value)
  {
    if ($this->hasAttribute($name)) {
      $this->_attributes[$name] = $value;
    }

    parent::__set($name, $value);
  }

}
