<?php
/**
 * Created by PhpStorm.
 * User: Павел
 * Date: 01.07.2015
 * Time: 18:33
 */

namespace omnilight\datetime;
use yii\base\InvalidParamException;
use yii\base\Model;


/**
 * Trait DateTimeRangeBehaviorFinder
 */
trait DateTimeRangeBehaviorFinder
{
    /**
     * @param Model $model
     * @param $attribute
     * @return DateTimeRangeBehavior
     */
    protected static function findBehavior(Model $model, $attribute)
    {
        foreach ($model->behaviors as $behavior) {
            if (!($behavior instanceof DateTimeRangeBehavior)) {
                continue;
            }

            if ($behavior->hasAttribute($attribute)) {
                return $behavior;
            }
        }

        throw new InvalidParamException('Model '.get_class($model).' does not have attribute '.$attribute);
    }
}