<?php

namespace Anyform\Data;

class Install_Service
{
    public static function install($db_version)
    {
        $installed_ver = get_option("anyform_db_version");
        if ($installed_ver == $db_version) {
            return;
        }

        self::create_fields();
        self::create_customers();
        self::insert_data();

        update_option('anyform_db_version', $db_version);
    }

    public static function create_fields()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'anyform_fields';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            ID bigint(20) NOT NULL AUTO_INCREMENT,
            type varchar(100) NOT NULL,
            place int(11) NOT NULL,
            mandatory bit(1) NOT NULL,
            alias varchar(250) DEFAULT NULL,
            visible bit(1) NOT NULL,
            PRIMARY KEY (ID)
          ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function create_customers()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'anyform_customers';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE $table_name (
            ID bigint(11) NOT NULL AUTO_INCREMENT,
            address varchar(250)  DEFAULT NULL,
            city varchar(250) DEFAULT NULL,
            name varchar(250) DEFAULT NULL,
            create_date timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
            email varchar(250) DEFAULT NULL,
            phone varchar(250)  DEFAULT NULL,
            textarea text,
            custom1 varchar(250) NULL,
            custom2 varchar(250) DEFAULT NULL,
            custom3 varchar(250) DEFAULT NULL,
            PRIMARY KEY (ID)
            ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }

    public static function insert_data()
    {
        global $wpdb;
        $table_name = $wpdb->prefix . 'anyform_fields';

        $rowcount = $wpdb->get_var("SELECT COUNT(*) FROM $table_name");
        if ($rowcount > 0) {
            return;
        }

        $sql = "INSERT INTO $table_name (ID, type, place, mandatory, alias, visible) VALUES
        (1, 'name', 0, b'1', 'Full Name', b'1'),
        (2, 'email', 1, b'1', 'Email', b'1'),
        (3, 'phone', 2, b'1', 'Phone', b'1'),
        (4, 'city', 3, b'0', 'City', b'1'),
        (5, 'address', 4, b'0', 'Address', b'1'),
        (6, 'textarea', 5, b'0', 'Message', b'1'),
        (7, 'custom1', 6, b'0', 'Custom 1', b'0'),
        (8, 'custom2', 7, b'0', 'Custom 2', b'0'),
        (9, 'custom3', 8, b'0', 'Custom 3', b'0');";

        $wpdb->query($sql);
    }

    public static function remove_database()
    {
        global $wpdb;

        $tableArray = [
            $wpdb->prefix . "anyform_fields",
            $wpdb->prefix . "anyform_customers"
        ];

        foreach ($tableArray as $tablename) {
            $wpdb->query("DROP TABLE IF EXISTS $tablename");
        }

        delete_option("anyform_db_version");
        delete_option("anyform_config");
        delete_option('anyform_count');
    }
}
