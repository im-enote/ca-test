<?php

namespace app\controllers;

use app\services\PrizeService;
use Yii;
use yii\filters\AccessControl;
use yii\helpers\Url;
use yii\web\Controller;
use yii\web\Response;
use yii\filters\VerbFilter;
use app\models\LoginForm;
use app\models\ContactForm;

class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::className(),
                'only' => ['logout'],
                'rules' => [
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::className(),
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => 'yii\web\ErrorAction',
            ],
            'captcha' => [
                'class' => 'yii\captcha\CaptchaAction',
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return string
     */
    public function actionPlay()
    {
        if (Yii::$app->user->isGuest) {
            return $this->redirect(Url::toRoute('site/login'));
        }

        $oRequest = Yii::$app->request;
        $oPrizeService = new PrizeService(Yii::$app->user->id);
        $serviceMessage = null;
        $isMessageError = false;

        if(!$oRequest->isPost) {
            $convertedId = $oRequest->get('converted_id');
            $removeId = $oRequest->get('remove_id');

            //Если prizeMoneyToBonuses вернуло null - произошла ошибка
            if (!empty($convertedId) && is_null($oPrizeService->prizeMoneyToBonuses($convertedId, $serviceMessage))) {
                $isMessageError = true;
            }

            //Если removePrize вернуло false - произошла ошибка
            if (!empty($removeId) && !$oPrizeService->removePrize($removeId, $serviceMessage)) {
                $isMessageError = true;
            }
        }

        //Создаем приз, при POST запросе
        if($oRequest->isPost && !$oPrizeService->playPrize(null, $serviceMessage)) {
            $isMessageError = true;
        }

        return $this->render('index', [
            'aHistory' => $oPrizeService->getPrizesForHistory(),
            'aMessage' => [
                'text' => $serviceMessage,
                'is_error' => $isMessageError
            ]
        ]);
    }

    public function actionIndex(){
        return $this->redirect(Url::toRoute('site/play'));
    }

    /**
     * Login action.
     *
     * @return Response|string
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';
        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logout action.
     *
     * @return Response
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return Response|string
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->contact(Yii::$app->params['adminEmail'])) {
            Yii::$app->session->setFlash('contactFormSubmitted');

            return $this->refresh();
        }
        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return string
     */
    public function actionAbout()
    {
        return $this->render('about');
    }
}
