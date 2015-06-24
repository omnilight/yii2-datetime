<?php

namespace omnilight\datetime;

use yii\base\Arrayable;
use yii\base\Object;
use yii\helpers\FormatConverter;


/**
 * Class DateTimeAttribute
 * @property string $value
 */
class DateTimeAttribute extends Object implements Arrayable
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
            if ($this->_value)
                return $this->_value;
            else {
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
        $value = date_create_from_format($phpFormat, $value);
        $this->behavior->owner->{$this->originalAttribute} = $this->behavior->formatter->format($value, $this->originalFormat);
    }

    /**
     * @inheritdoc
     */
    public function fields()
    {
        return [];
    }

    /**
     * Returns the list of additional fields that can be returned by [[toArray()]] in addition to those listed in [[fields()]].
     *
     * This method is similar to [[fields()]] except that the list of fields declared
     * by this method are not returned by default by [[toArray()]]. Only when a field in the list
     * is explicitly requested, will it be included in the result of [[toArray()]].
     *
     * @return array the list of expandable field names or field definitions. Please refer
     * to [[fields()]] on the format of the return value.
     * @see toArray()
     * @see fields()
     */
    public function extraFields()
    {
        return [];
    }

    /**
     * Converts the object into an array.
     *
     * @param array $fields the fields that the output array should contain. Fields not specified
     * in [[fields()]] will be ignored. If this parameter is empty, all fields as specified in [[fields()]] will be returned.
     * @param array $expand the additional fields that the output array should contain.
     * Fields not specified in [[extraFields()]] will be ignored. If this parameter is empty, no extra fields
     * will be returned.
     * @param boolean $recursive whether to recursively return array representation of embedded objects.
     * @return array the array representation of the object
     */
    public function toArray(array $fields = [], array $expand = [], $recursive = true)
    {
        return $this->getValue();
    }
}