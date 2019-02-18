<?php

use \yii\helpers\Url;
use \yii\helpers\Html;

/**
 * @var $this yii\web\View
 * @var $aHistory app\models\Prize[]
 * @var $aMessage []
 */

$this->title = 'My Yii Application';
?>
<div class="site-index">

    <div class="jumbotron">
        <!--<h1>Congratulations!</h1>-->

        <!--<p class="lead">You have successfully created your Yii-powered application.</p>-->

        <?php if(!empty($aHistory)) { ?>

        <table class="table">
            <tbody>
            <?php foreach ($aHistory as $oPrize) { ?>
                <?php
                $oContent = $oPrize->getContent();

                if(is_null($oContent)){
                    continue;
                }
                ?>

                <tr>
                    <th scope="row">
                        <?php echo $oPrize->id; ?>
                    </th>
                    <td>
                        <?php
                            if($oPrize->isMoney()){
                                echo 'Денежный приз ' . $oContent->amount . ' $';
                            }

                            if($oPrize->isBonus()){
                                echo 'Баллы лояльности ' . $oContent->amount;
                            }

                            if($oPrize->isItem()){
                                echo 'Предмет №' . $oContent->item_id;
                            }
                        ?>
                    </td>
                    <td>
                        <?php if($oPrize->isMoney() && !$oPrize->isTransferred()) { ?>
                            <a href="<?php echo Url::toRoute(['site/play', 'converted_id' => $oPrize->id]); ?>">
                                Конвертировать в баллы
                            </a>
                        <?php } ?>
                    </td>
                    <td>
                        <?php if($oPrize->canBeCanceled()) { ?>
                        <a href="<?php echo Url::toRoute(['site/play', 'remove_id' => $oPrize->id]); ?>">
                            Отказатся
                        </a>
                        <?php }else{ ?>
                            <?php echo $oPrize->isItem() ? 'Приз отправлен' : 'Денежный приз отправлен на счет' ?>
                        <?php } ?>
                    </td>
                </tr>
            <?php } ?>
            </tbody>
        </table>

        <?php } ?>

        <?php if (!empty($aMessage['text'])){ ?>

            <div class="alert <?php echo empty($aMessage['is_error']) ? 'alert-success' : 'alert-danger' ?>" role="alert">
                <?php echo Html::encode($aMessage['text']); ?>
            </div>

        <?php } ?>

        <form method="post">
            <button class="btn btn-lg btn-success">
                Получить приз
            </button>

            <input type="hidden" name="_csrf" value="<?=Yii::$app->request->getCsrfToken()?>" />
        </form>
    </div>
</div>
