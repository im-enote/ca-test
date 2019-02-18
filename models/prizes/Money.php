<?php

namespace app\models\prizes;

use yii\db\ActiveRecord;

/**
 * Денежный приз
 *
 * @property int $prize_id ID базового приза
 * @property int $amount Сумма подарка
 */
class Money extends ActiveRecord
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
            [['prize_id'], 'integer', 'min' => 1],
            [['amount'], 'number', 'min' => 0],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'prize_moneys';
    }
}
