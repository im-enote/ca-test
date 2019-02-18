<?php

use yii\db\Migration;

/**
 * Class m190218_065739_cacexe
 */
class m190218_065739_cacexe extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->execute('CREATE TABLE `prizes` (
                              `id` int(11) NOT NULL,
                              `user_id` int(11) NOT NULL,
                              `type_id` int(11) NOT NULL,
                              `creation_dt` datetime NOT NULL,
                              `is_canceled` tinyint(1) NOT NULL DEFAULT \'0\',
                              `is_transferred` tinyint(1) NOT NULL DEFAULT \'0\'
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->execute('CREATE TABLE `prize_bonuses` (
                                  `prize_id` int(11) NOT NULL,
                                  `amount` int(11) NOT NULL
                                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->execute('CREATE TABLE `prize_items` (
                              `prize_id` int(11) NOT NULL,
                              `item_id` int(11) NOT NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->execute('CREATE TABLE `prize_moneys` (
                              `prize_id` int(11) NOT NULL,
                              `amount` decimal(9,0) NOT NULL
                            ) ENGINE=InnoDB DEFAULT CHARSET=utf8;');

        $this->execute('ALTER TABLE `prizes`
                              ADD PRIMARY KEY (`id`),
                              ADD KEY `USER_ID` (`user_id`);');

        $this->execute('ALTER TABLE `prize_bonuses`
                              ADD PRIMARY KEY (`prize_id`);');

        $this->execute('ALTER TABLE `prize_items`
                            ADD PRIMARY KEY (`prize_id`);');

        $this->execute('ALTER TABLE `prize_moneys`
                            ADD PRIMARY KEY (`prize_id`);');

        $this->execute('ALTER TABLE `prizes`
                            MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1;');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190218_065739_cacexe cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190218_065739_cacexe cannot be reverted.\n";

        return false;
    }
    */
}
