<?php

namespace omnilight\datetime;

use yii\base\InvalidParamException;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;
use yii\i18n\Formatter;


/**
 * Class DatePickerConfig
 */
class DatePickerConfig
{
    /**
     * @param DateTimeAttribute $attribute
     * @param array $options
     * @param string $datePickerClass
     * @return array
     */
    public static function get(DateTimeAttribute $attribute, $options = [], $datePickerClass = 'yii\jui\DatePicker')
    {
        $format = DateTimeBehavior::normalizeIcuFormat($attribute->targetFormat, $attribute->behavior->formatter);
        switch ($datePickerClass) {
            case 'yii\jui\DatePicker':
                $defaults = [
                    'language' => \Yii::$app->language,
                    'clientOptions' => [
                        'dateFormat' => 'php://'.FormatConverter::convertDateIcuToJui($format[1], $format[0]),
                    ]
                ];
                break;
            default:
                return $options;
        }
        return ArrayHelper::merge($defaults, $options);
    }
}