<?php

namespace omnilight\datetime;

use yii\base\Behavior;
use yii\base\Event;
use yii\base\InvalidParamException;
use yii\db\BaseActiveRecord;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;
use yii\validators\DateValidator;


/**
 * Class DateTimeBehavior
 */
class DateTimeBehavior extends Behavior
{
    /**
     * @var string
     */
    public $namingTemplate = '{attribute}_local';
    /**
     * @var Formatter
     */
    public $formatter;
    /**
     * @var string|array Format of the attributes in the database
     */
    public $originalFormat = ['datetime', 'yyyy-MM-dd HH:mm:ss'];
    /**
     * @var string|array Format of the attribute that should be shown to the user
     */
    public $targetFormat = 'date';
    /**
     * @var array List of the model attributes in one of the following formats:
     * ```php
     *  [
     *      'first', // This will use default configuration and virtual attribute template
     *      'second' => 'target_second', // This will use default configuration with custom attribute template
     *      'third' => [
     *          'targetAttribute' => 'target_third', // Optional
     *          // Rest of configuration
     *      ]
     *  ]
     * ```
     */
    public $attributes = [];
    /**
     * @var array
     */
    public $attributeConfig = ['class' => 'omnilight\datetime\DateTimeAttribute'];
    /**
     * @var bool
     */
    public $performValidation = true;
    /**
     * @var DateTimeAttribute[]
     */
    public $attributeValues = [];

    public function init()
    {
        if (is_null($this->formatter))
            $this->formatter = \Yii::$app->formatter;
        elseif (is_array($this->formatter))
            $this->formatter = \Yii::createObject($this->formatter);

        $this->prepareAttributes();
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

    protected function prepareAttributes()
    {
        foreach ($this->attributes as $key => $value) {
            $config = $this->attributeConfig;
            $config['originalFormat'] = $this->originalFormat;
            $config['targetFormat'] = $this->targetFormat;
            if (is_integer($key)) {
                $originalAttribute = $value;
                $targetAttribute = $this->processTemplate($originalAttribute);
            } else {
                $originalAttribute = $key;
                if (is_string($value)) {
                    $targetAttribute = $value;
                } else {
                    $targetAttribute = ArrayHelper::remove($value, 'targetAttribute', $this->processTemplate($originalAttribute));
                    $config = array_merge($config, $value);
                }
            }
            $config['behavior'] = $this;
            $config['originalAttribute'] = $originalAttribute;

            $this->attributeValues[$targetAttribute] = $config;
        }
    }

    protected function processTemplate($originalAttribute)
    {
        return strtr($this->namingTemplate, [
            '{attribute}' => $originalAttribute,
        ]);
    }

    public function canGetProperty($name, $checkVars = true)
    {
        if ($this->hasAttributeValue($name))
            return true;
        else
            return parent::canGetProperty($name, $checkVars);
    }

    protected function hasAttributeValue($name)
    {
        return isset($this->attributeValues[$name]);
    }

    public function canSetProperty($name, $checkVars = true)
    {
        if ($this->hasAttributeValue($name))
            return true;
        else
            return parent::canSetProperty($name, $checkVars);
    }

    public function __get($name)
    {
        if ($this->hasAttributeValue($name))
            return $this->getAttributeValue($name);
        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if ($this->hasAttributeValue($name))
            $this->setAttributeValue($name, $value);
        parent::__set($name, $value);
    }

    protected function getAttributeValue($name)
    {
        if (is_array($this->attributeValues[$name])) {
            $this->attributeValues[$name] = \Yii::createObject($this->attributeValues[$name]);
        }
        return $this->attributeValues[$name];
    }

    protected function setAttributeValue($name, $value)
    {
        if (is_array($this->attributeValues[$name])) {
            $this->attributeValues[$name] = \Yii::createObject($this->attributeValues[$name]);
        }

        if ($value instanceof DateTimeAttribute)
            $this->attributeValues[$name] = $value;
        else
            $this->attributeValues[$name]->value = $value;
    }

    /**
     * @param string|array $format
     * @param Formatter $formatter
     * @throws InvalidParamException
     * @return array|string
     */
    public static function normalizeIcuFormat($format, $formatter)
    {
        if (is_string($format)) {
            switch ($format) {
                case 'date':
                    return ['date', $formatter->dateFormat];
                case 'time':
                    return ['time', $formatter->timeFormat];
                case 'datetime':
                    return ['datetime', $formatter->datetimeFormat];
                default:
                    throw new InvalidParamException('$format has incorrect value');
            }
        } elseif (is_array($format) && count($format) < 2) {
            throw new InvalidParamException('When $format is presented in array form, it must have at least two elements');
        }
        return $format;
    }
} 