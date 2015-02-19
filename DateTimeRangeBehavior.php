<?php

namespace omnilight\datetime;

use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidConfigException;
use yii\db\BaseActiveRecord;


/**
 * Class DateTimeRangeBehavior
 */
class DateTimeRangeBehavior extends Behavior
{
    /**
     * @var string
     */
    public $namingTemplate = '{start}_{end}_range';
    /**
     * @var string
     */
    public $startAttribute;
    /**
     * @var string
     */
    public $endAttribute;
    /**
     * @var string Separator between start and end dates
     */
    public $separator = ' - ';
    /**
     * @var bool
     */
    public $performValidation = true;
    /**
     * @var string Defines name of the target attribute. {start} and {end} placeholders can be used.
     * After behaviour initialization this name will be replaced with real name of the target attribute
     */
    public $targetAttribute = '{start}_{end}_range';
    /**
     * @var string
     */
    protected $_value;

    public function init()
    {
        parent::init();

        if ($this->startAttribute === null)
            throw new InvalidConfigException('$startAttribute is not set');
        if ($this->endAttribute === null)
            throw new InvalidConfigException('$endAttribute is not set');

        $this->targetAttribute = strtr($this->targetAttribute, [
            '{start}' => $this->startAttribute,
            '{end}' => $this->endAttribute,
        ]);
    }


    public function events()
    {
        $events = [];
        if ($this->performValidation) {
            $events[BaseActiveRecord::EVENT_BEFORE_VALIDATE] = 'onBeforeValidate';
        }
        return $events;
    }

    /**
     * Performs validation for all the attributes
     * @param Event $event
     */
    public function onBeforeValidate($event)
    {
        foreach ($this->attributeValues as $targetAttribute => $value) {
            if ($value instanceof DateTimeAttribute) {
                $validator = \Yii::createObject([
                    'class' => DateValidator::className(),
                    'format' => self::normalizeIcuFormat($value->targetFormat, $this->formatter)[1],
                ]);
                $validator->validateAttribute($this->owner, $targetAttribute);
            }
        }
    }

    public function canSetProperty($name, $checkVars = true)
    {
        if ($name === $this->targetAttribute)
            return true;
        else
            return parent::canSetProperty($name, $checkVars);
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if ($name === $this->targetAttribute)
            return true;
        else
            return parent::canGetProperty($name, $checkVars);
    }

    public function __get($name)
    {
        if ($name === $this->targetAttribute)
            return $this->getAttributeValue($name);
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($name === $this->targetAttribute) {
            $this->setAttributeValue($name, $value);
            return;
        }
        parent::__set($name, $value);
    }

    public function getAttributeValue($name)
    {
        if ($this->_value) {
            return $this->_value;
        }
        return $this->owner->{$this->startAttribute} . $this->separator . $this->owner->{$this->endAttribute};
    }

    public function setAttributeValue($value)
    {
        $this->_value = $value;
        if ($this->validateValue($value)) {
            $separator = preg_quote($this->separator, '/');
            list($start, $end) = preg_split("/\\s*{$separator}\\s*/", $value, 2, PREG_SPLIT_NO_EMPTY);
            $this->owner->{$this->startAttribute} = $start;
            $this->owner->{$this->endAttribute} = $end;
        }
    }

    /**
     * @param string $value
     * @return int
     */
    protected function validateValue($value)
    {
        $separator = preg_quote($this->separator, '/');
        return preg_match("/^.+{$separator}.+$/", $value) === 1;
    }
}