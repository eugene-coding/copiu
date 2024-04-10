<?php

namespace app\controllers;

use app\models\OrderBlank;
use app\models\OrderBlankToNomenclature;
use app\models\OrderToNomenclature;
use app\models\Users;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;

class NomenclatureController extends \yii\web\Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    public function actionPriceList()
    {

//
//        $blanks = OrderBlank::find()->all();
//
//        $data[] = [
//            'Бланк',
//            'Наименование',
//            'Ед. изм.',
//            'Цена'
//        ];
//        foreach ($blanks as $blank) {
//            $ob = OrderBlankToNomenclature::find()->where(['ob_id' => $blank->id])->all();
//            foreach ($ob as $row) {
//                $data[] = [
//                    $blank->number,
//                    $row->n->name,
//                    $row->n->measure->name,
//                    (string) $row->n->getPriceForBuyer()
//                ];
//            }
//        }
//
//        $objPHPExcel = new \PHPExcel();
//        $objPHPExcel->setActiveSheetIndex(0);
//        $objPHPExcel->getActiveSheet()->fromArray($data, null, 'A1');
//
//        $sheet = $objPHPExcel->getActiveSheet();
//        foreach ($sheet->getColumnIterator() as $column) {
//            $sheet->getColumnDimension($column->getColumnIndex())->setAutoSize(true);
//        }
//
//        $writer = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel5');
//        header('Content-Type: application/vnd.ms-excel');
//        header('Content-Disposition: attachment;filename="price-list.xls"');
//        header('Cache-Control: max-age=0');
//        $writer->save('php://output');
    }
}