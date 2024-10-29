<?php

namespace Anyform\Front;

use \Anyform\Data\Data_Serivce;

Form_UI::init_actions();

class Form_UI
{
    public static function init_actions()
    {
        add_action('init', function () {
            if (isset($_POST['submitted'])) {
                $post_data = self::get_query_data();
                $db = new Data_Serivce();
                $db->create_contact($post_data);

                $count = get_option('anyform_count', 0);
                update_option('anyform_count', $count + 1);

                self::redirect_back();
            }
        });

        add_action('wp_enqueue_scripts', function () {
            wp_register_style('anyform', plugins_url('css/style.css', __FILE__));
            wp_enqueue_style('anyform');
        });
    }

    public static function redirect_back()
    {
        $nav_url = "";
        $config = get_option('anyform_config');
        if (isset($config) && isset($config['redirect_page'])) {
            $nav_url = get_page_link($config['redirect_page']);
        } else {
            $nav_url = get_home_url();
        }

        wp_redirect($nav_url);
        exit;
    }

    public static function get_query_data()
    {
        // check for spam, phone4 is not realy in use
        if (self::validate($_POST['phone4'])) exit;

        $db = new Data_Serivce();
        $fields = $db->get_ui_fields();
        $res_arr = [];
        foreach ($fields as $r) {
            if ($r->mandatory == 1 && !self::validate($_POST[$r->type])) {
                wp_die("Invalid user input for " . $r->alias, 'Error!');
                break;
            }
            $res_arr[$r->type] = sanitize_textarea_field($_POST[$r->type]);
        }

        return $res_arr;
    }

    public static function validate($val)
    {
        if (isset($val) && trim($val) != "")
            return true;

        return false;
    }

    public static function field_type($type)
    {
        switch ($type) {
            case 'email':
                return 'email';
            case 'phone':
                return 'tel';
            default:
                return 'text';
        }
    }

    public static function render($attr)
    {
        $db = new Data_Serivce();
        $data = $db->get_ui_fields();

        $form_title = "";
        $config = get_option('anyform_config');
        if (isset($config) && isset($config['form_title'])) {
            $form_title = $config['form_title'];
        }

        ob_start();
?>
        <div id="anyform-container">
            <form method="post" onsubmit="submit.disabled = true; return true;">
                <? if ($form_title != "") : ?>
                    <h3><?= esc_attr($form_title) ?></h3>
                <? endif; ?>
                <? foreach ($data as $r) : ?>
                    <fieldset>
                        <? if ($r->type == 'textarea') : ?>
                            <textarea name="<?= $r->type ?>" placeholder="<?= $r->alias ?>" <?= _e($r->mandatory == 1 ? 'required' : '') ?>></textarea>
                        <? else : ?>
                            <input name="<?= $r->type ?>" placeholder="<?= $r->alias ?>" type="<?= self::field_type($r->type) ?>" <?= _e($r->mandatory == 1 ? 'required' : '') ?> autofocus>
                        <? endif; ?>
                    </fieldset>
                <? endforeach; ?>
                <fieldset style="display:none">
                    <input type="text" name="phone4" value="">
                </fieldset>
                <fieldset>
                    <input type="hidden" name="submitted" value="true" />
                    <button name="submit" type="submit" id="contact-submit"><?= __('Submit', 'af') ?></button>
                </fieldset>
            </form>
        </div>
<?
        return ob_get_clean();
    }
}
