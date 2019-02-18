<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\commands;

use app\models\Prize;
use app\models\prizes\Money;
use app\services\BankService;
use yii\base\Exception;
use yii\console\Controller;
use yii\console\ExitCode;

/**
 * This command echoes the first argument that you have entered.
 *
 * This command is provided as an example for you to learn how to create console commands.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class SendController extends Controller
{
    /**
     * Размер пачки денежных призов отправляемых клиентам за 1 раз
     * @var int
     */
    protected $_moneySendGroupSize = 10;

    /**
     * @return int Exit code
     */
    public function actionIndex()
    {
        $moneyTbl = Money::tableName();
        $prizeTbl = Prize::tableName();

        $aMoneyPrizes = Money::find()
            ->select($moneyTbl . '.*')
            ->leftJoin($prizeTbl, '`' . $moneyTbl . '`.`prize_id` = `' . $prizeTbl . '`.`id`')
            ->where([
                $prizeTbl . '.is_canceled' => 0,
                $prizeTbl . '.is_transferred' => 0,
                $prizeTbl . '.type_id' => Prize::TYPE_MONEY
            ])
            ->orderBy('creation_dt DESC')
            ->limit($this->_moneySendGroupSize)
            ->all();

        if(empty($aMoneyPrizes)){
            $this->stdout(sprintf('No money prizes found'));
            return ExitCode::OK;
        }

        $this->stdout(sprintf('Sending money prizes. amount: [%s]', count($aMoneyPrizes)));

        $oBankService = new BankService();

        /**
         * @var $oMoneyPrize Money
         */
        foreach ($aMoneyPrizes as $oMoneyPrize){
            $oPrize = Prize::findOne(['id' => $oMoneyPrize->prize_id]);

            if(is_null($oPrize)){
                $this->stdout(sprintf('Prize for money content is not found. Prize id: [%s]', $oPrize->id));
                continue;
            }

            if(!$oBankService->sendMoney($oPrize->user_id, $oMoneyPrize->amount)){
                $this->stdout(sprintf('Bank service api is return an error. Prize id: [%s]', $oPrize->id));
                continue;
            }

            //Помечаем приз, как отправленый
            $oPrize->is_transferred = 1;
            $oPrize->save();
        }

        return ExitCode::OK;
    }
}
