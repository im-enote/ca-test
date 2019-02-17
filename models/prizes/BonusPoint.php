<?php

namespace app\models\prizes;

use \yii\base\BaseObject;

/**
 * Бонусные балы
 */
class BonusPoint extends BaseObject
{
    /**
     * ID базового приза
     * @var int
     */
    public $prize_id;

    /**
     * Количество бонусов
     * @var int
     */
    public $count;
}
