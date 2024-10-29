<?php

namespace Anyform\Admin;

use \Anyform\Data\Data_Serivce;

Form_Config_UI::init_actions();

class Form_Config_UI
{
    public static function init_actions()
    {
        add_action('admin_post_update_form_fields', function () {
            $config = [];
            $config['form_title'] = sanitize_text_field($_POST['form_title']);
            $config['redirect_page'] = (int)$_POST['redirect_page'];
            update_option('anyform_config', $config);

            $db = new Data_Serivce();
            $db->update_fields($_POST['fields']);
            wp_redirect(admin_url('admin.php?page=af_form_config&saved=true'));
        });

        add_action('admin_notices', function () {
            if (isset($_REQUEST['saved'])) {
                Common_UI::notice_success();
            }
        });
    }

    public static function render()
    {
        $db = new Data_Serivce();
        $data = $db->get_fields();
        $i = 0;

        $form_title = "Contact Form";
        $page_id = 0;

        $config = get_option('anyform_config');
        if (isset($config)) {
            if (isset($config['form_title']))
                $form_title = $config['form_title'];

            if (isset($config['redirect_page']))
                $page_id = $config['redirect_page'];
        }
?>
        <div class="wrap">
            <h1><?= get_admin_page_title() ?></h1>
            <form method="post" action="<?= admin_url('admin-post.php') ?>" method="POST">
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th><label><?= __('Shortcode', 'af') ?></label></th>
                            <td>
                                <span class="shortcode">
                                    <input type="text" onfocus="this.select();" readonly="readonly" value="[any-form]" class="regular-text code">
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?= __('Form Title', 'af') ?></label>
                            </th>
                            <td>
                                <input type="text" name="form_title" maxlength="200" class="regular-text" value="<?= esc_attr($form_title) ?>">
                                <p class="description"><?= __('This title will be appeared on a top of the form', 'af') ?></p>
                            </td>
                        </tr>
                        <tr>
                            <th scope="row">
                                <label><?= __('Redirect to Page', 'af') ?></label>
                            </th>
                            <td>
                                <? wp_dropdown_pages(array(
                                    'name' => 'redirect_page',
                                    'show_option_none' => __('— Select —', 'af'),
                                    'option_none_value' => '0',
                                    'selected' => $page_id,
                                )); ?>
                                <p class="description"><?= __('Users will be redirected to this page after the form is submitted', 'af') ?></p>
                            </td>
                        </tr>
                    </tbody>
                </table>
                <h2><?= __('Fields configuration', 'af') ?></h2>
                <table class="wp-list-table widefat fixed striped" style="width: auto" role="presentation">
                    <thead>
                        <tr>
                            <td><?= __('Visible', 'af') ?></td>
                            <th><?= __('Type', 'af') ?></th>
                            <td><?= __('Field Name', 'af') ?></td>
                            <td><?= __('Order', 'af') ?></td>
                            <td><?= __('Mandatory', 'af') ?></td>
                        </tr>
                    </thead>
                    <tbody>
                        <? foreach ($data as $r) : ?>
                            <tr class="user-rich-editing-wrap">
                                <td>
                                    <input type="checkbox" name="fields[<?= $i ?>][visible]" value="yes" <?= _e($r->visible == 1 ? 'checked' : '') ?>>
                                </td>
                                <th scope="row">
                                    <input type="hidden" name="fields[<?= $i ?>][id]" value="<?= $r->ID ?>">
                                    <?= $r->type ?>
                                </th>
                                <td>
                                    <input type="text" maxlength="240" name="fields[<?= $i ?>][alias]" value="<?= esc_attr($r->alias) ?>">
                                </td>
                                <td>
                                    <input type="number" style="width: 50px" name="fields[<?= $i ?>][place]" value="<?= $r->place ?>">
                                </td>
                                <td>
                                    <input type="checkbox" name="fields[<?= $i ?>][mandatory]" value="yes" <?= _e($r->mandatory == 1 ? 'checked' : '') ?>>
                                </td>
                            </tr>
                        <? $i++;
                        endforeach; ?>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="update_form_fields">
                <p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="<?= __('Save', 'af') ?>"></p>
            </form>
        </div><!-- .wrap -->
<?
    }
}
?>