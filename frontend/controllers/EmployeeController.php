<?php

namespace frontend\controllers;

use Yii;
use yii\base\Model;
use frontend\models\Empl;
use common\models\Employee;
use common\models\LoginForm;
use yii\filters\AccessControl;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\filters\VerbFilter;
use yii\filters\auth\HttpBasicAuth;
use yii\filters\auth\QueryParamAuth;

class EmployeeController extends ApiController
{
	const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUPER_ADMIN = 5;

	public $modelClass = Empl::class;

	public function behaviors()
	{
		$behaviors = parent::behaviors();
		$behaviors['authenticator'] = [
                'class'=> QueryParamAuth:: className (),
                'except'=> ['login'],
        ];
		$behaviors['access'] = [
			'class' => AccessControl::class,
			'denyCallback' => function ($rule, $action) 
				{ 
					throw new \Exception('У Вас нет прав для доступа к данной странице'); 
				},
			'rules' => [
				[
					'actions' => ['view', 'index', 'create', 'update', 'dismiss', 'restore-work'],
					'allow' => false,
					'roles' => ['?'],
				],
				[
					'actions' => ['view', 'index', 'logout'],
					'allow' => true,
					'roles' => ['viewEmployee'],
				],
				[
                    'actions' => ['create', 'dismiss', 'restore-work', 'logout'],
                    'allow' => true,
                    'roles' => ['createEmployee'],
                ],
                [
                    'actions' => ['update', 'dismiss', 'restore-work', 'logout'],
                    'allow' => true,
                    'roles' => ['updateEmployee'],
                ],
                [
					'actions' => ['login'],
					'allow' => true,
					'roles' => ['?'],
				],
				[
					'actions' => ['login'],
					'allow' => false,
					'roles' => ['@'],
				],
			],
		];
		 	return $behaviors;
  	}

  	public function actionLogin()
    {
        $model = new LoginForm();
        if ($model->load(Yii::$app->getRequest()->getBodyParams(), '') && $model->login()) {
            return [
                'access_token' => $model->login(),
            ];
        } else {
            return $model->getFirstErrors();
        }
    }

  	public function actionDismiss($id)
    {
        $model = $this->findModel($id);
	    Yii::$app->db->createCommand()->update('employee', ['status' => self::STATUS_DELETED], 'id='.$model->id)->execute();
	    return $this->redirect(['index']);
    }

    public function actionRestoreWork($id)
	{
	    $model = $this->findModel($id);
	    Yii::$app->db->createCommand()->update('employee', ['status' => self::STATUS_ACTIVE], 'id='.$model->id)->execute();
	    return $this->redirect(['index']);
	}

  	protected function findModel($id)
    {
        if (!empty(($model = Empl::findOne($id)))) {
            return $model;
        } return false;
    }
}
