<?php

namespace App\Traits;

trait Uuid
{
  protected static function bootUuid() {
    static::creating(function ($model) {
      if (! $model->getKey()) {
        $model->{$model->getKeyName()} = (string) \Uuid::generate(4);
      }
      $model->auto_id = ($model->max('auto_id')) ? $model->max('auto_id') + 1 : 1;
    });
  }

  public function getIncrementing()
  {
    return false;
  }

  public function getKeyType()
  {
    return 'string';
  }
}                                                                                          