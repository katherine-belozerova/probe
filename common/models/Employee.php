<?php

namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;

/**
 * This is the model class for table "{{%employee}}".
 *
 * @property int $id
 * @property string $role
 * @property string $surname
 * @property string $name
 * @property string $fathername
 * @property string|null $login
 * @property int $pas_ser
 * @property int $pas_num
 * @property string $dob
 * @property string $e_date
 * @property string $by_whom
 * @property string $when
 * @property string $email
 * @property string $tel
 * @property string|null $status
 * @property string|null $pass
 */
class Employee extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_SUPER_ADMIN = 5;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%employee}}';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['role', 'surname', 'name', 'passport_series', 'passport_number', 'birth_date', 'date_of_employment', 
            'passport_issued_by', 'date_of_issue_of_passport', 'email', 'telephone', 'pass', 'login'], 'required', 'message' => 'Обязательное поле'],
            [['passport_series', 'passport_number'], 'integer'],
            [['surname', 'name', 'passport_series', 'passport_number', 'birth_date', 'date_of_employment', 'passport_issued_by', 
            'date_of_issue_of_passport', 'email', 'telephone'], 'trim'],
            [['role', 'login', 'pass'], 'string', 'max' => 16],
            [['surname', 'name', 'fathername'], 'string', 'max' => 64],
            [['birth_date', 'date_of_employment', 'date_of_issue_of_passport'], 'date', 'format' => 'dd.mm.yyyy', 'message' => 'Неверный формат даты (дд.мм.гггг)'],
            [['passport_issued_by', 'email'], 'string', 'max' => 128],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_DELETED]],
            [['login'], 'unique', 'targetClass' => 'common\models\Employee', 'message' => 'Данный логин уже зарегистрирован, пожалуйста, придумайте другой'],
            [['telephone'], 'unique', 'targetClass' => 'common\models\Employee', 'message' => 'Данный телефон уже зарегистрирован'],
            [['email'], 'unique', 'targetClass' => 'common\models\Employee', 'message' => 'Данный адрес электронной почты уже зарегистрирован'],
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'role' => 'Роль',
            'surname' => 'Фамилия',
            'name' => 'Имя',
            'fathername' => 'Отчество (если имеется)',
            'login' => 'Придумайте логин',
            'passport_series' => 'Серия паспорта',
            'passport_number' => 'Номер паспорта',
            'birth_date' => 'Дата рождения',
            'date_of_employment' => 'Дата приема на работу',
            'passport_issued_by' => 'Кем выдан',
            'date_of_issue_of_passport' => 'Когда выдан',
            'email' => 'E-mail',
            'telephone' => 'Телефон',
            'pass' => 'Придумайте пароль',
        ];
    }

    public function beforeSave($insert)
    {
        if(parent::beforeSave($insert)) {
            $this->pass = Yii::$app->security->generatePasswordHash($this->pass);
            $this->access_token = Yii::$app->security->generateRandomString();
            return true;
        } return false;
    }

    public function afterSave($insert, $changedAttributes)
    {
        parent::afterSave($insert, $changedAttributes);
        $auth = Yii::$app->authManager;
        $admin = $auth->getRole('admin');
        $director = $auth->getRole('director');
        $manager = $auth->getRole('manager');
        if ($this->role == 'Администратор') {
            $auth->assign($admin, $this->id); 
        } elseif ($this->role == 'Директор') {
            $auth->assign($director, $this->id); 
        } elseif ($this->role == 'Менеджер') {
            $auth->assign($manager, $this->id); 
        } 

    }

    public static function findIdentity($id)
    {
        return static::findOne($id);
    }

    public function getId()
    {
        return $this->id;
    }

    public static function findIdentityByAccessToken($token, $type = null)
    {
        $user = static::find()
        ->where(['access_token' => $token, 'status' => self::STATUS_ACTIVE])
        ->orWhere(['access_token' => $token, 'status' => self::STATUS_SUPER_ADMIN])
        ->one();
        if (!$user) {
            return false;
        }
        return $user;
    }

    public function getAuthKey()
    {
        return $this->access_token;
    }

    public function validateAuthKey($access_token)
    {
        return $this->access_token === $access_token;
    }

    public static function findByUsername($login)
    {
        return self::find()->where(['login' => $login])->one();
    }

    public function validatePassword($pass)
    {
        return Yii::$app->security->validatePassword($pass, $this->pass);
    }

}
