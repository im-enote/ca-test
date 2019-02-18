<?php

namespace app\models;

use app\models\prizes\BonusPoint;
use app\models\prizes\Item;
use app\models\prizes\Money;
use yii\db\ActiveRecord;

/**
 * Базовая сущность приза в БД
 * @property int $id ID приза
 * @property int $user_id ID пользователя получателя приза
 * @property int $type_id Тип бонуса. Доступные значения: см. константы класса с префиксом TYPE_
 * @property string $creation_dt Дата и время создания приза
 * @property int $is_canceled 0 - Пользователь не отказался от приза, 1 - Пользователь отказался от приза
 * @property int $is_transferred 0 - Приз не был начислен / отправлен пользователю, 1 - сумма отправлена
 */
class Prize extends ActiveRecord
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
     * Получить связанную сущность в зависимости от type_id
     * @return null|Money|BonusPoint|Item
     */
    public function getContent()
    {
        switch ($this->type_id){
            case self::TYPE_MONEY:
                return $this->hasOne(Money::class, ['prize_id' => 'id'])->one();

            case self::TYPE_BONUS:
                return $this->hasOne(BonusPoint::class, ['prize_id' => 'id'])->one();

            case self::TYPE_ITEM:
                return $this->hasOne(Item::class, ['prize_id' => 'id'])->one();

            default:
                return null;
        }
    }

    /**
     * Вернет true, если подарок может быть отменен
     * @return bool
     */
    public function canBeCanceled(){
        if($this->isBonus()){
            return true;
        }

        return !$this->isTransferred();
    }

    /**
     * Отказался ли пользователь от этого приза
     * @return int
     */
    public function isCanceled()
    {
        return $this->is_canceled === 1;
    }

    /**
     * Был ли приз начислен / отправлен пользователю
     * @return bool
     */
    public function isTransferred()
    {
        return $this->is_transferred === 1;
    }

    /**
     * Является ли этот приз денежным
     * @return bool
     */
    public function isMoney()
    {
        return $this->type_id === self::TYPE_MONEY;
    }

    /**
     * Является ли этот приз бонусами
     * @return bool
     */
    public function isBonus()
    {
        return $this->type_id === self::TYPE_BONUS;
    }

    /**
     * Является ли этот приз подарком
     * @return bool
     */
    public function isItem()
    {
        return $this->type_id === self::TYPE_ITEM;
    }

    /**
     * @param bool $isInsert
     * @return bool
     */
    public function beforeSave($isInsert)
    {
        //Делаем для даты создания приза значение по умолчанию
        if(empty($this->creation_dt)){
            $this->creation_dt = date('Y-m-d H:i:s');
        }

        return parent::beforeSave($isInsert);
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'user_id' => 'User ID',
            'type_id' => 'Type ID',
            'creation_dt' => 'Creation dt',
            'is_canceled' => 'Is canceled',
            'is_transferred' => 'Is canceled',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'type_id'], 'required'],
            [['user_id', 'type_id'], 'integer', 'min' => 1],
            [['is_canceled', 'is_transferred'], 'integer', 'min' => 0, 'max' => 1],
            [['creation_dt'], 'string']
        ];
    }

    public static function tableName()
    {
        return 'prizes';
    }
}
