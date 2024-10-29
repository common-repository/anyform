<?php

namespace Anyform\Data;

class Data_Serivce
{
    public function update_fields($arr)
    {
        global $wpdb;
        foreach ($arr as $p) {
            $wpdb->update(
                "{$wpdb->prefix}anyform_fields",
                [
                    'place' => (int)$p['place'],
                    'alias' => sanitize_text_field($p['alias']),
                    'visible' => isset($p['visible']),
                    'mandatory' => isset($p['mandatory'])
                ],
                ['ID' => (int)$p['id']]
            );
        }
    }

    function get_fields()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}anyform_fields ORDER BY place");
    }

    function get_ui_fields()
    {
        global $wpdb;
        return $wpdb->get_results("SELECT * FROM {$wpdb->prefix}anyform_fields WHERE visible = 1 ORDER BY place");
    }

    public function update_contact($id, $data)
    {
        global $wpdb;
        $wpdb->update(
            "{$wpdb->prefix}anyform_customers",
            $data,
            ['ID' => $id]
        );
    }

    public function create_contact($data)
    {
        global $wpdb;
        $wpdb->insert(
            "{$wpdb->prefix}anyform_customers",
            $data
        );
    }

    public function get_contact($id)
    {
        global $wpdb;
        return $wpdb->get_row("SELECT * FROM {$wpdb->prefix}anyform_customers WHERE ID = {$id}", ARRAY_A);
    }

    public function get_contacts_paged($per_page, $page_number, $req)
    {
        global $wpdb;

        $sql = "SELECT * FROM {$wpdb->prefix}anyform_customers";

        if (!empty($req['orderby'])) {
            $sql .= ' ORDER BY ' . esc_sql($req['orderby']);
            $sql .= !empty($req['order']) ? ' ' . esc_sql($req['order']) : ' ASC';
        }
        else
        {
            $sql .= ' ORDER BY create_date DESC';
        }

        $sql .= " LIMIT $per_page";
        $sql .= ' OFFSET ' . ($page_number - 1) * $per_page;

        $result = $wpdb->get_results($sql, ARRAY_A);

        return $result;
    }

    public function customers_count()
    {
        global $wpdb;
        return $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}anyform_customers");
    }

    public function delete_customer($id)
    {
        global $wpdb;
        $wpdb->delete("{$wpdb->prefix}anyform_customers", ['ID' => $id], ['%d']);
    }
}
