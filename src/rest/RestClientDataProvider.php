<?php
/**
 * @author Kambiz Zandi <kambizzandi@gmail.com>
 */

namespace shopack\base\frontend\rest;

use yii\data\BaseDataProvider;
use yii\base\InvalidParamException;
use yii\base\InvalidConfigException;
use shopack\base\frontend\rest\RestClientQueryInterface;
use shopack\base\frontend\rest\Pagination;
use Yii;

/**
 * Class DataProvider
 *
 * @package shopack\base\frontend\rest
 */
class RestClientDataProvider extends BaseDataProvider
{
  /**
   * @var RestClientQuery
   */
  public $query;

  /**
   * Prepares the data models that will be made available in the current page.
   * @return array the available data models
   * @throws InvalidConfigException
   */
  protected function prepareModels()
  {
    if (!$this->query instanceof RestClientQueryInterface) {
      throw new InvalidConfigException(
        'The "query" property must be an instance of a class that implements the '.
        'shopack\base\frontend\rest\RestClientQueryInterface or its subclasses.'
      );
    }

    $query = clone $this->query;
    $pagination = $this->getPagination();
    if ($pagination === false) {
      return $query->all();
    }

    $pagination->totalCount = $this->getTotalCount();
    $query->limit($pagination->getLimit())
          ->offset($pagination->getOffset());

    if (($sort = $this->getSort()) !== false) {
      $query->addOrderBy($sort->getOrders());
    }

    return $query->all();
  }

  /**
   * Prepares the keys associated with the currently available data models.
   * @param RestClientActiveRecord[] $models the available data models
   * @return array the keys
   */
  protected function prepareKeys($models)
  {
    $keys = [];
    foreach ($models as $model) {
      $keys[] = $model->getPrimaryKey();
    }
    return $keys;
  }

  /**
   * Returns a value indicating the total number of data models in this data provider.
   * @return integer total number of data models in this data provider.
   */
  protected function prepareTotalCount()
  {
    return $this->query->count();
  }

  /**
   * @inheritDoc
   */
  public function setPagination($value)
  {
    if (is_array($value)) {
      $config = ['class' => Pagination::class];
      if ($this->id !== null) {
        $config['pageParam'] = $this->id . '-page';
        $config['pageSizeParam'] = $this->id . '-per-page';
      }
      return parent::setPagination(Yii::createObject(array_merge($config, $value)));
    }

    if ($value instanceof Pagination || $value === false) {
      return parent::setPagination($value);
    }

    throw new InvalidParamException('Only Pagination instance, configuration array or false is allowed.');
  }

}
