<?php

namespace Anyform\Admin;

use \Anyform\Data\Data_Serivce;

if (!class_exists('WP_List_Table')) {
  require_once(ABSPATH . 'wp-admin/includes/class-wp-list-table.php');
}

class Customers_List extends \WP_List_Table
{
  private static $db;
  /** Class constructor */
  public function __construct()
  {
    // reset new contacts count
    $new_count = get_option('anyform_count', 0);
    if ($new_count > 0)
      update_option('anyform_count', 0);

    self::$db = new Data_Serivce();
    parent::__construct([
      'singular' => __('Customer', 'sp'), //singular name of the listed records
      'plural'   => __('Customers', 'sp'), //plural name of the listed records
      'ajax'     => false //does this table support ajax?
    ]);
  }

  /**
   * Retrieve customers data from the database
   *
   * @param int $per_page
   * @param int $page_number
   *
   * @return mixed
   */
  public static function get_customers($per_page = 5, $page_number = 1)
  {
    return self::$db->get_contacts_paged($per_page, $page_number, $_GET);
  }

  /**
   * Delete a customer record.
   *
   * @param int $id customer ID
   */
  public static function delete_customer($id)
  {
    self::$db->delete_customer($id);
  }

  /** Text displayed when no customer data is available */
  public function no_items()
  {
    _e('No items avaliable.', 'af');
  }

  /**
   * Render a column when no column specific method exist.
   *
   * @param array $item
   * @param string $column_name
   *
   * @return mixed
   */
  public function column_default($item, $column_name)
  {
    switch ($column_name) {
      case 'ID':
      case 'name':
      case 'email':
      case 'phone':
      case 'create_date':
        return esc_html_e($item[$column_name]);
      default:
        return print_r($item, true); //Show the whole array for troubleshooting purposes
    }
  }

  /**
   * Render the bulk edit checkbox
   *
   * @param array $item
   *
   * @return string
   */
  function column_cb($item)
  {
    return sprintf(
      '<input type="checkbox" name="bulk-delete[]" value="%s" />',
      $item['ID']
    );
  }

  /**
   * Method for name column
   *
   * @param array $item an array of DB data
   *
   * @return string
   */
  function column_name($item)
  {
    $delete_nonce = wp_create_nonce('sp_delete_customer');

    $title = '<strong>' . $item['name'] . '</strong>';

    $actions = array(
      'delete' => sprintf('<a href="?page=%s&action=%s&customer=%s&_wpnonce=%s">Delete</a>', esc_attr($_REQUEST['page']), 'delete', absint($item['ID']), $delete_nonce),
      'edit' => sprintf('<a href="?page=af_contacts_edit&customer=%s">Edit</a>', absint($item['ID']))
    );

    return $title . $this->row_actions($actions);
  }

  /**
   *  Associative array of columns
   *
   * @return array
   */
  function get_columns()
  {
    return [
      'cb'      => '<input type="checkbox" />',
      'name'    => __('Name', 'af'),
      'email'   => __('Email', 'af'),
      'phone'   => __('Phone', 'af'),
      'create_date'    => __('Date', 'af')
    ];
  }

  /**
   * Columns to make sortable.
   *
   * @return array
   */
  public function get_sortable_columns()
  {
    $sortable_columns = array(
      'name' => array('name', true),
      'phone' => array('phone', true),
      'email' => array('email', true),
      'create_date' => array('create_date', true)
    );

    return $sortable_columns;
  }

  /**
   * Returns an associative array containing the bulk action
   *
   * @return array
   */
  public function get_bulk_actions()
  {
    $actions = [
      'bulk-delete' => 'Delete'
    ];

    return $actions;
  }

  /**
   * Handles data query and filter, sorting, and pagination.
   */
  public function prepare_items()
  {

    $this->_column_headers = $this->get_column_info();

    /** Process bulk action */
    $this->process_bulk_action();

    $per_page     = $this->get_items_per_page('contacts_per_page', 20);
    $current_page = $this->get_pagenum();
    $total_items  = self::$db->customers_count();

    $this->set_pagination_args([
      'total_items' => $total_items, //WE have to calculate the total number of items
      'per_page'    => $per_page //WE have to determine how many items to show on a page
    ]);

    $this->items = self::get_customers($per_page, $current_page);
  }

  public function process_bulk_action()
  {
    //Detect when a bulk action is being triggered...
    if ('delete' === $this->current_action()) {

      // In our file that handles the request, verify the nonce.
      $nonce = esc_attr($_REQUEST['_wpnonce']);

      if (!wp_verify_nonce($nonce, 'sp_delete_customer')) {
        die('Invalid request');
      } else {
        self::delete_customer(absint($_GET['customer']));
      }
    }

    // If the delete bulk action is triggered
    if ((isset($_POST['action']) && $_POST['action'] == 'bulk-delete')
      || (isset($_POST['action2']) && $_POST['action2'] == 'bulk-delete')
    ) {

      $delete_ids = esc_sql($_POST['bulk-delete']);

      // loop over the array of record IDs and delete them
      foreach ($delete_ids as $id) {
        self::delete_customer($id);
      }
    }
  }
}
