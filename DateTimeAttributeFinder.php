<?php
namespace omnilight\datetime;
use yii\base\Model;
use yii\base\InvalidParamException;


/**
 * Trait DateTimeAttributeFinder
 */
trait DateTimeAttributeFinder
{
    /**
     * @param Model $model
     * @param $attribute
     * @return DateTimeAttribute
     */
    protected static function findAttribute(Model $model, $attribute)
    {
        foreach ($model->behaviors as $behavior) {
            if (!($behavior instanceof DateTimeBehavior)) {
                continue;
            }

            if ($behavior->hasAttribute($attribute)) {
                return $behavior->getAttribute($attribute);
            }
        }

        throw new InvalidParamException('Model '.get_class($model).' does not have attribute '.$attribute);
    }
}