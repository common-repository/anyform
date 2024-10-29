<?php

namespace Anyform\Admin;

use \Anyform\Data\Data_Serivce;

Contacts_Edit_UI::init_actions();

class Contacts_Edit_UI
{
    public static function init_actions()
    {
        add_action('admin_post_anyform_create', function () {
            $post_data = self::valid_post_data();
            $db = new Data_Serivce();
            $db->create_contact($post_data);
            self::redirect_back();
        });
        
        add_action('admin_post_anyform_update', function () {
            $post_data = self::valid_post_data();
            $db = new Data_Serivce();
            $db->update_contact((int)$_POST['id'], $post_data);
            self::redirect_back();
        });
    }

    public static function redirect_back()
    {
        $back_url = esc_url_raw($_POST['ref']);
        if (!isset($back_url) || !strpos($back_url, 'mf_contacts_list')) {
            $back_url = admin_url('admin.php?page=mf_contacts_list');
        }
        wp_redirect($back_url);
    }

    public static function valid_post_data()
    {
        $db = new Data_Serivce();
        $fields = $db->get_fields();
        $res_arr = [];
        foreach ($fields as $r) {
            $res_arr[$r->type] = sanitize_textarea_field($_POST[$r->type]);
        }
        return $res_arr;
    }

    public static function render()
    {
        $contact = [];
        $action = 'anyform_create';

        $db = new Data_Serivce();
        $fields = $db->get_fields();
        foreach ($fields as $r) {
            $contact[$r->type] = "";
        }

        if (isset($_GET['customer'])) {
            $id = absint($_GET['customer']);
            $action = 'anyform_update';
            $contact = $db->get_contact($id);
        }
?>
        <div class="wrap">
            <h1><?= get_admin_page_title() ?></h1>
            <h2>Edit Contact Details</h2>

            <form method="post" action="<?= admin_url('admin-post.php') ?>" method="POST">
                <table class="form-table" role="presentation">
                    <tbody>
                        <? foreach ($fields as $r) : ?>
                            <tr class="form-required">
                                <th scope="row">
                                    <label for="<?= $r->type ?>"><?= $r->alias ?></label>
                                </th>
                                <td>
                                    <? if ($r->type == 'textarea') : ?>
                                        <textarea rows="5" cols="30" class="regular-text" name="<?= $r->type ?>"><?= esc_textarea($contact[$r->type]) ?></textarea>
                                    <? else : ?>
                                        <input type="text" name="<?= $r->type ?>" class="regular-text" value="<?= esc_attr($contact[$r->type]) ?>">
                                    <? endif; ?>
                                </td>
                            </tr>
                        <? endforeach; ?>
                    </tbody>
                </table>

                <input name="ref" type="hidden" value="<?= wp_get_referer() ?>">
                <input name="id" type="hidden" value="<?= $id ?>">
                <input type="hidden" name="action" value="<?= $action ?>">
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?= __('Save', 'af') ?>"></p>
            </form>
        </div><!-- .wrap -->
<?
    }
}
?>