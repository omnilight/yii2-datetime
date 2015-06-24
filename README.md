Date and Time behaviors for Yii 2
=================================

This extension helps you to deal with date and time attributes of the models for Yii framework 2.0

Installation
------------

The preferred way to install this extension is through [composer](http://getcomposer.org/download/).

Either run

```
php composer.phar require --prefer-dist omnilight/yii2-datetime "*"
```

or add

```json
"omnilight/yii2-datetime": "*"
```

to the `require` section of your composer.json.

Why?
----

When working with models and forms it is often needed to provide user a way to edit attributes that holds
date and/or time. In this case typical problems are:

1. Date/time formats could be different in the database and in the displayed form (due to local settings, for ex.)
2. You should validate values entered by the user

So you have to set correct format for database, form, somehow convert them from one to another, and it would be
nice if you can setup correct formats once (on application level) and lately do not worry about them. This extension
helps is this area.

The idea
--------

Idea is to have separate attribute for each editable date/time property of the model, that will be used in the form.
This attribute will not be stored in the DB, but it will be used to present user with correctly formatted value.
And when you assign value to this attribute, it will be automatically converted and assigned to the DB property of
the model.

This extension provides special behavior, that automates work with this attributes


How to use
----------

In your model:
```php
/**
 * @property string posted_at This is your property that you have in the database table, it has DATETIME format
 */
class Post extends ActiveRecord
{
    // ... Some code here

    public function behaviors()
    {
        return [
            'datetime' => [
                'class' => DateTimeBehavior::className(), // Our behavior
                'attributes' => [
                    'posted_at', // List all editable date/time attributes
                ],
            ]
        ];
    }
}
```

Now in your view with the form:
```php
// $model has instance of Post
<?= $form->field($model, 'posted_at_local')->widget(\yii\jui\DatePicker::className(), \omnilight\datetime\DatePickerConfig::get($model, 'posted_at_local')) ?>
// DatePickerConfig is used to properly configure widget. Currently it only supports DatePicker from the Jui extension
```

That's all! User will enter date in the his local format and will be converted to the database automatically.

How is works
------------

Behavior creates "virtual" attribute named attribute_name_local for each attribute you define in the 'attributes' section.
When you read `$yourModel->attribute_name_local` behavior will return object with the type DateTimeAttribute. If
this object will be used in the string context, it will be converted to string with the magical __toString method.
And during this original value of `attribute_name` will be converted into the local representation.

When you assign value to the `$yourModel->attribute_name_local` internally it will be assigned to `value` property
of the DateTimeAttribute class and converted to the your database-stored property.

You can also define individual configuration for each attribute and define it's local name, format and so on.
