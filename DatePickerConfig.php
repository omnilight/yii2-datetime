<?php

namespace omnilight\datetime;

use yii\base\InvalidParamException;
use yii\base\Model;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;


/**
 * Class DatePickerConfig
 */
class DatePickerConfig
{
    use AttributeFinder;

    /**
     * @param Model $model
     * @param $attribute
     * @param array $options
     * @param string $datePickerClass
     * @return array
     */
    public static function get(Model $model, $attribute, $options = [], $datePickerClass = 'yii\jui\DatePicker')
    {
        $attribute = self::findAttribute($model, $attribute);

        $format = DateTimeBehavior::normalizeIcuFormat($attribute->targetFormat, $attribute->behavior->formatter);
        switch ($datePickerClass) {
            case 'yii\jui\DatePicker':
                $defaults = [
                    'language' => \Yii::$app->language,
                    'clientOptions' => [
                        'dateFormat' => 'php:' . FormatConverter::convertDateIcuToJui($format[1], $format[0]),
                    ]
                ];
                break;
            case 'omnilight\widgets\DatePicker':
                $defaults = [
                    'language' => \Yii::$app->language,
                    'dateFormat' => 'php:' . FormatConverter::convertDateIcuToPhp($format[1], $format[0]),
                ];
                break;
            default:
                return $options;
        }
        return ArrayHelper::merge($defaults, $options);
    }
}