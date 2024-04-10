<?php

namespace app\components;

class DateHelper
{
    /**
     * Возвращает человеческий день недели
     * @return void
     */
    public static function getWeekDay($date)
    {
        $weekDay = date('N', strtotime($date));
        $weekDay = $weekDay == 0 ? 7 : $weekDay;
        return $weekDay;
    }

}