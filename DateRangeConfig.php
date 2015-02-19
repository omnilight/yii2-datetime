<?php

namespace omnilight\datetime;
use yii\helpers\ArrayHelper;
use yii\helpers\FormatConverter;


/**
 * Class DateRangeConfig
 */
class DateRangeConfig 
{
    /**
     * @param DateTimeRangeBehavior $behavior
     * @param array $options
     * @param string $datePickerClass
     * @return array
     */
    public static function get(DateTimeRangeBehavior $behavior, $options = [], $datePickerClass = 'omnilight\widgets\DateRangePicker')
    {
        $startAttribute = $behavior->owner->{$behavior->startAttribute};
        $endAttribute = $behavior->owner->{$behavior->startAttribute};
        $formatAttribute = ($startAttribute instanceof DateTimeAttribute) ? $startAttribute :
            (($endAttribute instanceof DateTimeAttribute) ? $endAttribute : null);

        $defaults = [];
        switch ($datePickerClass) {
            case 'omnilight\widgets\DateRangePicker':
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