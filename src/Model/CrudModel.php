<?php
/**
 * @package Nexcess-SDK
 * @license TBD
 * @copyright 2018 Nexcess.net
 */

declare(strict_types  = 1);

namespace Nexcess\Sdk\Model;

use Nexcess\Sdk\ {
  Exception\ModelException,
  Model\Model
};

abstract class CrudModel extends Model {

  /**
   * Creates a new item.
   *
   * @param array $values Map of values for new item
   * @return Model
   * @throws ApiException If creating item fails
   */
  public function create(array $values) : Model {
    $id = $values['id'] ?? $this->offsetGet('id');
    if ($id) {
      throw new ModelException(
        ModelException::MODEL_EXISTS,
        ['model' => static::NAME, 'id' => $id]
      );
    }

    foreach ($values as $key => $value) {
      $this->_set($key, $value);
    }

    $this->_sync(
      $this->_client
        ->request('POST', static::ENDPOINT, $this->_values)
        ->toArray()
    );

    return $this;
  }

  /**
   * Deletes an existing item.
   *
   * @param int|null $id Item id
   * @return Model
   * @throws ApiException If deleting item fails
   */
  public function delete(int $id = null) : Model {
    $id = $id ?? $this->offsetGet('id');
    if (! $id) {
      throw new ModelException(
        ModelException::MISSING_ID,
        ['model' => static::NAME]
      );
    }

    $this->_client->request('DELETE', static::ENDPOINT . "/{$id}");
    $this->_sync([]);

    return $this;
  }

  /**
   * Updates an existing item.
   *
   * Implementing class must define EDIT_VALUE_MAP as a name:default value map.
   *
   * @param array $update Property:value map of changes to make
   * @return Model
   * @throws ApiException If updating item fails
   */
  public function update(array $values = []) : Model {
    $id = $values['id'] ?? $this->offsetGet('id');
    if (! $id) {
      throw new ModelException(
        ModelException::MISSING_ID,
        ['model' => static::NAME]
      );
    }

    foreach ($values as $key => $value) {
      $this->_set($key, $value);
    }

    $update = array_udiff_assoc(
      $this->_values,
      $this->_stored,
      function ($value, $stored) { return ($value === $stored) ? 0 : 1; }
    );

    if (! empty($update)) {
      $this->_sync(
        $this->_client
          ->request('PATCH', static::ENDPOINT . "/{$id}/edit", $update)
          ->toArray()
      );
    }

    return $this;
  }
}
