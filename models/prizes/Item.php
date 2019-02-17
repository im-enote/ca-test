<?php

namespace app\models\prizes;

use \yii\base\BaseObject;

/**
 * Предмет в подарок
 */
class Item extends BaseObject
{
    /**
     * ID базового приза
     * @var int
     */
    public $prize_id;

    /**
     * ID предмета из списка предметов
     * @var int
     */
    public $item_id;
}
