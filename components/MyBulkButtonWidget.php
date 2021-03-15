<?php

namespace app\components;

use johnitvn\ajaxcrud\BulkButtonWidget;

class MyBulkButtonWidget extends BulkButtonWidget
{
    public function run()
    {
        $content = '<div class="pull-left">' .
            $this->buttons .
            '</div>';
        return $content;
    }
}