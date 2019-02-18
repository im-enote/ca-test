<?php

namespace tests\unit\models;

use app\models\Prize;
use app\models\prizes\Money;
use app\models\User;
use app\services\PrizeService;

class MoneyToBonusTest extends \Codeception\Test\Unit
{
    public function testMoneyToBonusByUserId()
    {
        $amount = 100;

        expect_that($oUser = User::findIdentity(100));
        expect($oUser->username)->equals('admin');

        expect_that($oTransaction = \Yii::$app->db->beginTransaction());

        expect_that($oPrize = new Prize());
        expect_that($oMoney = new Money());
        expect_that($oService = new PrizeService($oUser->id));

        $oPrize->user_id = $oUser->id;
        $oPrize->type_id = Prize::TYPE_MONEY;

        expect_that($oPrize->save());

        $oMoney->prize_id = $oPrize->id;
        $oMoney->amount = $amount;

        expect_that($oMoney->save());
        expect_that($oService->prizeMoneyToBonuses($oPrize->id));

        //Обновляем prize из БД
        expect_that($oPrize = Prize::findOne(['id' => $oPrize->id]));
        expect($oPrize->type_id)->equals(Prize::TYPE_BONUS);
        expect_that($oBonus = $oPrize->getContent());
        expect($oBonus->amount)->equals($amount * $oService->getMoneyToBonusFactor());

        $oTransaction->rollBack();
    }
}
