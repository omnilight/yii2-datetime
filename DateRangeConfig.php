<?php

namespace omnilight\datetime;
use omnilight\widgets\DateRangePicker;
use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;


/**
 * Class DateRangeConfig
 */
class DateRangeConfig 
{
    use DateTimeAttributeFinder, DateTimeRangeBehaviorFinder;

    /**
     * @param Model $model
     * @param string $attribute
     * @param array $options
     * @param string $datePickerClass
     * @return array
     */
    public static function get(Model $model, $attribute, $options = [], $datePickerClass = DateRangePicker::class)
    {
        $behavior = self::findBehavior($model, $attribute);

        $formatAttribute = null;

        try {
            $formatAttribute = self::findAttribute($model, $behavior->startAttribute);
            $formatAttribute = self::findAttribute($model, $behavior->endAttribute);
        } catch (InvalidParamException $e) {

        }

        $defaults = [];
        switch ($datePickerClass) {
            case DateRangePicker::class:
                $defaults = [
                    'separator' => $behavior->separator,
                ];
                if ($formatAttribute) {
                    $format = DateTimeBehavior::normalizeIcuFormat($formatAttribute->targetFormat, $formatAttribute->behavior->formatter);
                    $defaults['dateFormat'] = 'php:'.FormatConverter::convertDateIcuToPhp($format[1], $format[0]);
                }
                break;
        }
        return ArrayHelper::merge($defaults, $options);
    }
}