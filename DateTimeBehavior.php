<?php

namespace omnilight\datetime;

use yii\base\Behavior;
use yii\helpers\ArrayHelper;
use yii\i18n\Formatter;


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
     * @var string|array
     */
    public $originalFormat = 'datetime';
    /**
     * @var string|array
     */
    public $targetFormat = 'datetime';
    /**
     * @var array
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


} 