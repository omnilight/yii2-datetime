<?php

namespace omnilight\datetime;

use yii\base\InvalidParamException;
use yii\helpers\FormatConverter;
use yii\i18n\Formatter;


/**
 * Class DatePickerConfig
 */
class DatePickerConfig
{
    /**
     * @param DateTimeAttribute $attribute
     * @param string $datePickerClass
     * @return array
     */
    public static function get($attribute, $datePickerClass = 'yii\jui\DatePicker')
    {
        $format = DateTimeBehavior::normalizeIcuFormat($attribute->targetFormat, $attribute->behavior->formatter);
        switch ($datePickerClass) {
            case 'yii\jui\DatePicker':
                return [
                    'language' => \Yii::$app->language,
                    'clientOptions' => [
                        'dateFormat' => FormatConverter::convertDateIcuToJui($format[1], $format[0]),
                    ]
                ];
            default:
                return [];
        }
    }
}