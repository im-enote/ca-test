<?php

namespace app\models\prizes;

use yii\db\ActiveRecord;

/**
 * Предмет в подарок
 *
 * @property int $prize_id ID базового приза
 * @property int $item_id ID предмета из списка предметов
 */
class Item extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'prize_id' => 'Prize id',
            'item_id' => 'Item id',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['prize_id', 'item_id'], 'required'],
            [['prize_id', 'item_id'], 'integer', 'min' => 1],
        ];
    }

    /**
     * @return string
     */
    public static function tableName()
    {
        return 'prize_items';
    }
}
