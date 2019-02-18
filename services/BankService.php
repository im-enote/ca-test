<?php

namespace app\services;


/**
 * Зашлушка под реалзицию Http апи для отправки денег через банк
 * @version 1.0.0
 */
class BankService
{
    /**
     * Отправка средств на кошелек пользователя
     * @param int $userId
     * @param float $amount
     * @return bool
     */
    public function sendMoney($userId, $amount){
        return true;
    }
}