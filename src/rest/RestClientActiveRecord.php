<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use Yii;
use yii\base\NotSupportedException;
use yii\db\BaseActiveRecord;
use shopack\base\common\rest\enuColumnInfo;
use shopack\base\common\validators\JsonValidator;

abstract class RestClientActiveRecord extends BaseActiveRecord
  implements \shopack\base\common\rest\ActiveRecordInterface
{
	use \shopack\base\common\rest\ActiveRecordTrait;

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

  public static function staticInit()
  {
    static::$apiUrl = Yii::$app->params['apiServerAddress'];
  }

  public static function primaryKey()
	{
    return (array)static::$primaryKey;
	}

	public static function find() : RestClientQuery
	{
    $query = \Yii::createObject(RestClientQuery::class, [
      get_called_class()
      // ['requestHeaders' => $requestHeaders],
    ]);

    return $query;
	}

  public function afterFind()
	{
    $JsonValidator_class = JsonValidator::class;
    $columnsInfo = static::columnsInfo();
    foreach ($columnsInfo as $column => $info) {
      if (isset($info[enuColumnInfo::type])
          && $info[enuColumnInfo::type] === $JsonValidator_class
          && empty($this->$column) == false
      ) {
        $this->$column = json_decode($this->$column, true);
        $this->setOldAttribute($column, $this->$column);
      }
    }

		parent::afterFind();
	}

  public function save($runValidation = true, $attributeNames = null)
  {
    $values = $this->getDirtyAttributes($attributeNames);
    if (empty($values) == false) {
      $JsonValidator_class = JsonValidator::class;
      $columnsInfo = static::columnsInfo();
      foreach (array_keys($values) as $column) {
        if (isset($columnsInfo[$column][enuColumnInfo::type])
            && $columnsInfo[$column][enuColumnInfo::type] === $JsonValidator_class
            && empty($this->$column) == false
        ) {
          $this->$column = array_filter($this->$column);
        }
      }
    }

    return parent::save($runValidation, $attributeNames);
  }

	public function insert($runValidation = true, $attributes = null)
	{
		if ($runValidation && !$this->validate($attributes)) {
			Yii::info('Model not inserted due to validation error.', __METHOD__);
			return false;
		}

		return $this->insertInternal($attributes);
	}

	protected function insertInternal($attributes = null)
	{
		if (!$this->beforeSave(true))
			return false;

    $values = $this->getDirtyAttributes($attributes);

		$model = static::find()->restCreate($this);
		if ($model->hasErrors())
			return false;

    $resultAttributes = array_filter($model->getAttributes());
    if (empty($resultAttributes) == false) {
      foreach ($resultAttributes as $name => $value) {
        $this->setAttribute($name, $value);
        $values[$name] = $value;
      }
    }

		// if (($primaryKeys = static::getDb()->schema->insert(static::tableName(), $values)) === false) {
		// 	return false;
		// }
		// foreach ($primaryKeys as $name => $value) {
		// 	$id = static::getTableSchema()->columns[$name]->phpTypecast($value);
		// 	$this->setAttribute($name, $id);
		// 	$values[$name] = $id;
		// }

		$changedAttributes = array_fill_keys(array_keys($values), null);
		$this->setOldAttributes($values);
		$this->afterSave(true, $changedAttributes);

		return true;
	}

  // public function update($runValidation = true, $attributeNames = null)
  // {
  //   if ($runValidation && !$this->validate($attributeNames)) {
  //     Yii::info('Model not updated due to validation error.', __METHOD__);
  //     return false;
  //   }

  //   return $this->updateInternal($attributeNames);
  // }

  // protected function updateInternal($attributes = null)
  // {

  // }

	public static function updateAll($attributes, $condition = '', $params = [])
	{
		return self::find()->restUpdateAll($attributes, $condition, $params);
	}

  public static function deleteAll($condition = null)
  {
		return self::find()->restDeleteAll($condition);
  }

	public static function getDb()
	{
		throw new NotSupportedException(__METHOD__ . ' is not supported.');
	}

  private $_cached_attributes = null;
	public function attributes()
  {
    if (empty($this->_cached_attributes) == false)
      return $this->_cached_attributes;

    $attributes = [];
    // $rules = $this->rules();
    // foreach ($rules as $rule) {
    //   $columns = $rule[0];

    //   if (is_array($columns)) {
    //     foreach ($columns as $column) {
    //       if (array_key_exists($column, $attributes) == false)
    //         $attributes[] = $column;
    //     }
    //   } else {
    //     if (array_key_exists($columns, $attributes) == false)
    //       $attributes[] = $columns;
    //   }
    // }

    $columnsInfo = static::columnsInfo();
    foreach ($columnsInfo as $column => $info) {
      if (array_key_exists($column, $attributes) == false)
        $attributes[] = $column;
    }

    $this->_cached_attributes = $attributes;
    return $this->_cached_attributes;
  }

  public function getParentKey() {
    return null;
  }

  abstract public function isSoftDeleted();

  public function load($data, $formName = null)
  {
		$ret = parent::load($data, $formName);

    if ($ret) {
      $scope = ($formName === null ? $this->formName() : $formName);

      if (empty($scope))
        $uploadeFiles = $_FILES ?? null;
      else
        $uploadeFiles = $_FILES[$scope] ?? null;

      if (empty($uploadeFiles) == false) {
        if (is_array($uploadeFiles['name'])) {
          foreach ($uploadeFiles['name'] as $fieldName => $name) {
            $full_path = $uploadeFiles['full_path'][$fieldName];
            $type      = $uploadeFiles['type'][$fieldName];
            $tmp_name  = $uploadeFiles['tmp_name'][$fieldName];
            $error     = $uploadeFiles['error'][$fieldName];
            $size      = $uploadeFiles['size'][$fieldName];

            $fileData = new FileData();
            $fileData->name      = $name;
            $fileData->full_path = $uploadeFiles['full_path'][$fieldName];
            $fileData->type      = $uploadeFiles['type'][$fieldName];
            $fileData->tmp_name  = $uploadeFiles['tmp_name'][$fieldName];
            $fileData->error     = $uploadeFiles['error'][$fieldName];
            $fileData->size      = $uploadeFiles['size'][$fieldName];

            $this->setAttribute($fieldName, $fileData);
          }
        } else {
          //not an array!!
        }
      }
    }

		return $ret;
	}

}
