<?php

namespace app\models;

use yii\base\Model;
use yii\web\UploadedFile;

class UploadForm extends Model
{
    /**
     * @var UploadedFile
     */
    public $imageFile;

    public function rules()
    {
        return [
            [['imageFile'], 'file', 'skipOnEmpty' => false],
        ];
    }

    public function attributeLabels()
    {
        return [
            'imageFile' => 'Файл прайс-листа'
        ];
    }

    public function upload()
    {
        if ($this->validate()) {
            $fileName = $this->imageFile->baseName . '.' . $this->imageFile->extension;
            $this->imageFile->saveAs('uploads/' . $fileName);
            Settings::setValueByKey(Settings::KEY_PRICE_LIST, $fileName);
            return true;
        } else {
            return false;
        }
    }
}