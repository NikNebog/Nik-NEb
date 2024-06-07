<?php

namespace app\models;

use Exception;
use MongoDB\BSON\ObjectId;
use Yii;
use yii\base\NotSupportedException;
use yii\mongodb\ActiveRecord;
use yii\validators\EmailValidator;
use yii\validators\NumberValidator;
use yii\validators\StringValidator;

/**
 * class Users
 * @package app\models
 * @property Object $id
 * @property string $login
 * @property string $email
 * @property string $password
 * @property string $auth_key
 */

class User extends ActiveRecord implements \yii\web\IdentityInterface
{

    public static function collectionName(): string
    {
        return 'user';
    }


    public function attributes(): array
    {
        return [
            'id',
            'login',
            'email',
            'password',
            'auth_key'
        ];
    }
    public function rules():array
    {
        return [
            [['login'],StringValidator::class],
            [['email'],EmailValidator::class],
//                [['password'],'string','max'=>15]


        ];
    }
    /**
     * {@inheritdoc}
     */

    public static function findIdentity($id): User|\yii\web\IdentityInterface|null
    {
        return static::findOne($id);
    }

    /**
     * {@inheritdoc}
     * @throws NotSupportedException
     */
    public static function findIdentityByAccessToken($token, $type = null): \yii\web\IdentityInterface|static|null
    {
        throw new NotSupportedException('"findIdentityByAccessToken"is not implemented');
    }

    /**
     * Finds user by login
     *
     * @param string $login
     * @return User
     */
    public static function findByUsername(string $login): User
    {
       return static::findOne(['login'=>$login]);
    }

    /**
     * {@inheritdoc}
     */
    public function getId(): string
    {
        return (string)$this->_id;
    }

    /**
     * {@inheritdoc}
     */

    public function validateAuthKey($authKey): ?bool
    {
        return $this->auth_key === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword(string $password): bool
    {
        return Yii::$app->security->validatePassword($password,$this->password);
    }

    /**
     * @throws \yii\base\Exception
     */
    public function setPassword($password): void
    {
         $this->password = Yii::$app->security->generatePasswordHash($password);
    }

    /**
     * @throws Exception
     */

    public function beforeSave($insert): bool
    {
        if(parent::beforeSave($insert)){
            if($this->isNewRecord){
                $this->auth_key = \Yii::$app->security->generateRandomString();
            }
            return true;
        }
        return false;
    }

    /**
     * @throws Exception
     */

    public function generateAuthKey(): void
    {
        $this->auth_key = Yii::$app->security->generateRandomString();
    }

    public function getAuthKey(): string
    {
        return $this->auth_key;
    }
}