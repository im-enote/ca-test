<?php

namespace app\models;

use \yii\base\BaseObject;

/**
 * Базовая сущность приза в БД
 */
class Prize extends BaseObject
{
    /**
     * Денежный приз
     */
    const TYPE_MONEY = 1;

    /**
     * Бонусные баллы (баллы лояльности)
     */
    const TYPE_BONUS = 2;

    /**
     * Подарок
     */
    const TYPE_ITEM = 3;

    /**
     * ID приза
     * @var int
     */
    public $id;

    /**
     * ID пользователя получателя приза
     * @var int
     */
    public $user_id;

    /**
     * Тип бонуса
     * Доступные значения: см. константы класса с префиксом TYPE_
     * @var int
     */
    public $type_id;

    /**
     * 0 - Пользователь не отказался от приза
     * 1 - Пользователь отказался от приза
     * @var int
     */
    public $is_canceled = 0;

    /**
     * Отказался ли пользователь от этого приза
     * @return int
     */
    public function isCanceled(){
        return $this->is_canceled === 1;
    }

    /**
     * 0 - Приз не был начислен / отправлен пользователю
     * 1 - сумма отправлена
     * @var int
     */
    public $is_transferred = 0;

    /**
     * Был ли приз начислен / отправлен пользователю
     * @return bool
     */
    public function isTransferred(){
        return $this->is_transferred === 1;
    }
}
