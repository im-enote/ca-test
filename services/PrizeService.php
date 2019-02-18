<?php

namespace app\services;


use app\models\Prize;
use app\models\prizes\BonusPoint;
use app\models\prizes\Item;
use app\models\prizes\Money;
use yii\base\Exception;
use yii\base\InvalidArgumentException;
use yii\helpers\ArrayHelper;

/**
 * Сервис реализующий логику розыгрышей призов
 */
class PrizeService
{
    /**
     * @var int
     */
    protected $_userId;

    /**
     * @var \yii\db\Connection
     */
    protected $_oDb;

    /**
     * @var int
     */
    protected $_moneyMin;

    /**
     * @var int
     */
    protected $_moneyMax;

    /**
     * @var int
     */
    protected $_bonusMin;

    /**
     * @var int
     */
    protected $_bonusMax;

    /**
     * Коэфицент преобраования денежного приза в баллы лояльности
     * @var int
     */
    protected $_moneyToBonusFactor = 2;

    /**
     * @param int $userId ID пользователя для которого разыгрыватся приз
     * @param array|null $aParams
     * @throws InvalidArgumentException
     */
    public function __construct($userId, array $aParams = null)
    {
        $userId = intval($userId);

        if(empty($userId)){
            throw new InvalidArgumentException('Обязательный параметр [userId] не задан');
        }

        $this->_userId = $userId;
        $this->_oDb = \Yii::$app->db;

        if(!is_array($aParams)){
            $aParams = \Yii::$app->params;
        }

        $this->_moneyMin = intval(ArrayHelper::getValue($aParams, 'prize_money_min', 1));
        $this->_moneyMax = intval(ArrayHelper::getValue($aParams, 'prize_money_max', 100));

        $this->_bonusMin = intval(ArrayHelper::getValue($aParams, 'prize_bonus_min', 1));
        $this->_bonusMax = intval(ArrayHelper::getValue($aParams, 'prize_bonus_max', 200));

        //Тут можно было бы добавить контрольные проверки на валидность значения настроек
    }

    /**
     * Разыграть случайный приз для пользователя
     * @param int|null $typeId
     * @param string|null $frontendMessage
     * @throws \yii\db\Exception
     * @return Prize
     */
    public function playPrize($typeId = null, &$frontendMessage = null){
        if(is_null($typeId)){
            //Если typeId не задан, берем случайный
            $typeId = mt_rand(1, 3);
        }

        if(!$this->validatePrizeType($typeId)){
           throw new InvalidArgumentException('Параметр [typeId] имеет не валидное значение');
        }

        //Открываем транзакцию БД для контроля целостности данных, так как планируеться создание нескольких зависимых сущностей и сбой может произойти в любой из них
        $oTransaction = $this->_oDb->beginTransaction();

        try{
            $oPrize = $this->createPrize($typeId);
        }
        catch (\Exception $oEx){
            //Откатываем транзацию в случае ошибки
            $oTransaction->rollBack();

            throw $oEx;
        }

        //Применяем транзакцию
        $oTransaction->commit();

        $frontendMessage = 'Вы выйграли приз: ';

        if($oPrize->isMoney()){
            $frontendMessage .= 'денежную сумму '  . $oPrize->getContent()->amount . ' $';
        }

        if($oPrize->isBonus()){
            $frontendMessage .= 'бонусные  баллы '  . $oPrize->getContent()->amount;
        }

        if($oPrize->isItem()){
            $frontendMessage .= 'предмет № '  . $oPrize->getContent()->item_id;
        }

        return $oPrize;
    }

    /**
     * Получить историю подарков для пользователя
     * @return Prize[]
     */
    public function getPrizesForHistory(){
        return Prize::find()
            ->where([
                'user_id' => $this->_userId,
                'is_canceled' => 0
            ])
            ->orderBy('creation_dt DESC')
            ->all();
    }

    /**
     * Получить текущий коэфицент преобраования денежного приза в баллы лояльности
     * @var int
     */
    public function getMoneyToBonusFactor(){
        return $this->_moneyToBonusFactor;
    }

    /**
     * Преобразовать не отправленный денежный приз в бонусные балы
     * @param int $prizeId
     * @param string|null $frontendMessage
     * @return BonusPoint|Item|Money|null
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function prizeMoneyToBonuses($prizeId, &$frontendMessage = null){
        $oPrize = Prize::findOne(['id' => intval($prizeId), 'user_id' => $this->_userId, 'is_canceled' => 0]);

        if(is_null($oPrize) || !$oPrize->isMoney() || $oPrize->isTransferred()){
            $frontendMessage = 'Денежный приз не найден.';
            return null;
        }

        $oContent = $oPrize->getContent();

        if(is_null($oContent)){
            throw new Exception('Для приза не найдена сущность с контентом');
        }

        /**
         * @var $oContent Money
         */

        //Открываем транзакцию БД для контроля целостности данных, так как планируеться создание нескольких зависимых сущностей и сбой может произойти в любой из них
        $oTransaction = $this->_oDb->beginTransaction();

        try{
            $oContent = $this->changeMoneyToPrice($oPrize, $oContent);
        }
        catch (\Exception $oEx){
            //Откатываем транзацию в случае ошибки
            $oTransaction->rollBack();

            throw $oEx;
        }

        //Применяем транзакцию
        $oTransaction->commit();

        $frontendMessage = 'Денежный приз преобразован в балы лояльности успешно';

        return $oContent;
    }

    /**
     * Пометить приз пользователя, как отклоненный
     * @param int $prizeId
     * @param null|string $frontendMessage
     * @return bool
     * @throws Exception
     * @throws \Throwable
     * @throws \yii\db\Exception
     */
    public function removePrize($prizeId, &$frontendMessage = null){
        $oPrize = Prize::findOne(['id' => intval($prizeId), 'user_id' => $this->_userId, 'is_canceled' => 0]);

        if(is_null($oPrize) || $oPrize->isTransferred()){
            $frontendMessage = 'Денежный приз не найден.';
            return false;
        }

        //Открываем транзакцию БД для контроля целостности данных, так как планируеться создание нескольких зависимых сущностей и сбой может произойти в любой из них
        $oTransaction = $this->_oDb->beginTransaction();

        try{
            $oPrize->is_canceled = 1;

            if(!$oPrize->save()){
                throw new Exception('Ошибка измения статуса приза на отклоненный');
            }
        }
        catch (\Exception $oEx){
            //Откатываем транзацию в случае ошибки
            $oTransaction->rollBack();

            throw $oEx;
        }

        //Применяем транзакцию
        $oTransaction->commit();

        $frontendMessage = 'Отказ от приза принят успешно';

        return true;
    }

    /**
     * @param int $typeId
     * @return Prize
     * @throws Exception
     * @throws \Throwable
     */
    protected function createPrize($typeId){
        $oPrize = new Prize();

        $oPrize->type_id = $typeId;
        $oPrize->user_id = $this->_userId;

        if(!$oPrize->insert()){
            throw new Exception(printf('В процессе вставки приза в БД произошла ошибка. ID пользователя: [%s]', $this->_userId));
        }

        if(empty($oPrize->id)){
            throw new Exception(printf('Вставка приза в БД вернула true, но id приза пуст. ID пользователя: [%s]', $this->_userId));
        }

        $oPrizeContent = $this->createPrizeContent($typeId, $oPrize->id);

        if(is_null($oPrizeContent)){
            throw new Exception(printf('Вставка содержимого приза в БД вернула ошибку. ID пользователя: [%s]', $this->_userId));
        }

        return $oPrize;
    }

    /**
     * @param int $typeId
     * @param int $prizeId
     * @return BonusPoint|Item|Money|null
     * @throws \Throwable
     */
    protected function createPrizeContent($typeId, $prizeId){
        switch ($typeId){
            case Prize::TYPE_MONEY:
                $oPrizeContent = $this->buildPrizeMoney();
                break;
            case Prize::TYPE_BONUS:
                $oPrizeContent = $this->buildPrizeBonus();
                break;
            case Prize::TYPE_ITEM:
                $oPrizeContent = $this->buildPrizeItem();
                break;

            default:
                throw new InvalidArgumentException('Параметр [typeId] имеет не валидное значение');
        }

        $oPrizeContent->prize_id = $prizeId;

        if(!$oPrizeContent->insert()){
            \Yii::error(printf('В процессе вставки содержимого приза в БД произошла ошибка. ID пользователя: [%s]', $this->_userId), __METHOD__);
            return null;
        }

        return $oPrizeContent;
    }

    /**
     * @return Money
     */
    protected function buildPrizeMoney(){
        $oPrizeContent = new Money();
        $oPrizeContent->amount = mt_rand($this->_moneyMin, $this->_moneyMax);

        return $oPrizeContent;
    }

    /**
     * @return BonusPoint
     */
    protected function buildPrizeBonus(){
        $oPrizeContent = new BonusPoint();
        $oPrizeContent->amount = mt_rand($this->_bonusMin, $this->_bonusMax);

        return $oPrizeContent;
    }

    /**
     * @return Item
     */
    protected function buildPrizeItem(){
        $oPrizeContent = new Item();
        $oPrizeContent->item_id = $this->getFreeItemId();

        return $oPrizeContent;
    }

    /**
     * Заглушка для логики поиска своболного предмета
     * @return int
     */
    protected function getFreeItemId(){
        return 1;
    }

    /**
     * @param int|null $typeId
     * @return bool
     */
    protected function validatePrizeType($typeId){
        if(is_null($typeId)){
            return true;
        }

        return in_array($typeId, [Prize::TYPE_ITEM, Prize::TYPE_BONUS, Prize::TYPE_MONEY]);
    }

    /**
     * @param Prize $oPrize
     * @param Money $oContent
     * @return BonusPoint|Money|null
     * @throws \Throwable
     * @throws \yii\db\StaleObjectException
     */
    protected function changeMoneyToPrice(Prize $oPrize, Money $oContent){
        $amount = $oContent->amount;

        if(!$oContent->delete()){
            throw new Exception('Ошибка удаления контента [Money] для приза');
        }

        $oContent = new BonusPoint();

        //$amount - Это float, поэтому округляем значение
        $oContent->amount = ceil($amount * $this->_moneyToBonusFactor);
        $oContent->prize_id = $oPrize->id;

        if(!$oContent->save()){
            throw new Exception('Ошибка сохранения контента [BonusPoint] для приза');
        }

        $oPrize->type_id = Prize::TYPE_BONUS;

        if(!$oPrize->save()){
            throw new Exception('Ошибка изменения параметра [type_id] для приза');
        }

        return $oContent;
    }
}