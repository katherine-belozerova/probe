<?php
namespace common\models;
use Yii;
use yii\base\Model;
use common\models\Employee;
/**
 * LoginForm is the model behind the login form.
 */
class LoginForm extends Model
{
	public $login;
	public $pass;
	private $_user = false;
	/**
	 * @return array the validation rules.
	 */
	public function rules()
	{
		return [
			[['login', 'pass'], 'required', 'message' => 'Обязательное поле'],
			['password', 'validatePassword'],
		];
	}
	
	public function validatePassword()
	{
		if (!$this->hasErrors()) {
			$user = $this->getUser();
			if (!$user || !$user->validatePassword($this->pass)) {
				$this->addError('pass', 'Неверный логин или пароль');
			}
		}
	}
	
	public function login()
	{
        if ($this->validate()) {
            if ($this->getUser()) {
                $access_token = $this->_user->access_token;
                Yii::$app->user->login($this->_user);
                return $access_token;
            }
        }
        return false;
	}

	/**
	 * Finds user by [[username]]
	 *
	 * @return User|null
	 */
	public function getUser()
	{
		if ($this->_user === false) {
			$this->_user = Employee::findByUsername($this->login);
		}
		return $this->_user;
	}
}