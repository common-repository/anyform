<?php namespace Anyform\Admin;

class Common_UI
{
    public static function notice_success()
    {
        ?>
         <div class="notice notice-success is-dismissible">
            <p><?= __('Data saved succesfuly'); ?></p>
        </div>
        <?
    }
}