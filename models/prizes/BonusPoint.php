<?php

namespace app\models\prizes;

use yii\db\ActiveRecord;

/**
 * Бонусные балы
 *
 * @property int $prize_id ID базового приза
 * @property int $amount Количество бонусов
 */
class BonusPoint extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prize_id' => 'Prize id',
            'amount' => 'Amount',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prize_id', 'amount'], 'required'],
            [['prize_id', 'amount'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'prize_bonuses';
    }
}
