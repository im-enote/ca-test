<?php

namespace app\models\prizes;

use \yii\base\BaseObject;

/**
 * Денежгый приз
 */
class Money extends BaseObject
{
    /**
     * ID базового приза
     * @var int
     */
    public $prize_id;

    /**
     * Сумма подарка
     * @var float
     */
    public $amount;
}
