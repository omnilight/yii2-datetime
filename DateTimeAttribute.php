<?php

namespace omnilight\datetime;

use yii\base\Arrayable;
use yii\base\Object;
use yii\helpers\FormatConverter;


/**
 * Class DateTimeAttribute
 * @property string $value
 */
class DateTimeAttribute extends Object
{
    /**
     * @var DateTimeBehavior
     */
    public $behavior;
    /**
     * @var string
     */
    public $originalAttribute;
    /**
     * @var string|array
     */
    public $originalFormat;
    /**
     * @var string|array
     */
    public $targetFormat;
    /**
     * @var string
     */
    public $nullValue;
    /**
     * @var string
     */
    protected $_value;

    function __toString()
    {
        return $this->getValue();
    }

    function __invoke()
    {
        return $this->getValue();
    }

    /**
     * @return string
     */
    public function getValue()
    {
        try {
            if ($this->_value !== null) {
                return $this->_value;
            } else {
                $originalValue = $this->behavior->owner->{$this->originalAttribute};
                if ($originalValue === null)
                    return $this->nullValue;
                else
                    return $this->behavior->formatter->format($originalValue, $this->targetFormat);
            }
        } catch (\Exception $e) {
            return $this->nullValue;
        }
    }

    /**
     * @param string $value
     */
    public function setValue($value)
    {
        $this->_value = $value;
        $normalizedFormat = DateTimeBehavior::normalizeIcuFormat($this->targetFormat, $this->behavior->formatter);
        $phpFormat = FormatConverter::convertDateIcuToPhp($normalizedFormat[1], $normalizedFormat[0], \Yii::$app->language);
        $dateTime = date_create_from_format($phpFormat, $value);
        $this->behavior->owner->{$this->originalAttribute} = $this->behavior->formatter->format($dateTime, $this->originalFormat);
    }
}