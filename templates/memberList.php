<?php

global $FapiPlugin;

use FapiMember\FapiMemberTools;
use FapiMember\FapiLevels;

$section = get_term($_GET['sectionID']);
?>
<?php
$user_ids  =   get_users(['fields' => 'ID']);
$out_ids = [];
foreach ($user_ids as $user_id) {
    $metas = get_user_meta($user_id, 'fapi_user_memberships', true);
    if (is_array($metas)) {
        foreach ($metas as $meta) {
            if (isset($meta['level']) && $meta['level'] == $section->term_id) {
                $out_ids[] = $user_id;
                break;
            }
        }
    }
}

if (!$out_ids) {
    echo __('Tato sekce nemá žádné členy', 'fapi-member');
} else {
    if (!class_exists('WP_List_Table')) {
        require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
    }

    class Member_Table extends WP_List_Table
    {
        public $user_ids;

        public function export_csv()
        {
            $data = array();
            $user_data = array();

            foreach ($this->user_ids as $user_id) {
                $user_data[] = get_userdata($user_id);
            }

            foreach ($user_data as $user) {
                $data[] = array(
                    __('Uživatelské jméno', 'fapi-member') => $user->user_login,
                    __('Jméno a příjmení', 'fapi-member') => $user->first_name . ' ' . $user->last_name,
                    __('Email', 'fapi-member') => $user->user_email,
                );
            }

            $output = fopen('php://output', 'w');
            fputcsv($output, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($output, $row);
            }
            fclose($output);
            exit;
        }

        public function get_columns()
        {
            $columns = array(
                'username'  => __('Uživatelské jméno', 'fapi-member'),
                'name'      => __('Jméno a přijmení', 'fapi-member'),
                'email'     => __('Email', 'fapi-member'),
            );
            return $columns;
        }

        public function prepare_items()
        {

            $user_data = array();

            foreach ($this->user_ids as $user_id) {
                $user_data[] = get_userdata($user_id);
            }

            $columns = $this->get_columns();
            $pagination = 100;
            $this->_column_headers = array($columns);
            $this->items = $user_data;
            $this->set_pagination_args(array(
                'total_items' => count($user_data),
                'per_page'    => $pagination,
            ));

            $this->items = array_slice($user_data, ($this->get_pagenum() - 1) * $pagination, $pagination);
        }

        public function display_rows()
        {
            foreach ($this->items as $user) {
                echo '<tr>';
                echo '<td>' . $user->user_login . '</td>';
                echo '<td>' . $user->first_name . ' ' . $user->last_name . '</td>';
                echo '<td>'. $user->user_email . '</td>';
                echo '</tr>';
            }
        }

        public function extra_tablenav($which)
        {
            if ($which == 'bottom') {
                echo '<a class="btn outline" href="' . esc_url(add_query_arg(array('export' => 'csv','noheader'=>'1'))) .'">' . __('Exportovat do CSV', 'fapi-member') . '</a>';
            }
        }
    }
    $table = new Member_Table();
    $table->user_ids = $out_ids;
    $table->prepare_items();
    if (isset($_GET['export']) && $_GET['export'] == 'csv') {
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename='.$section->name.'_members.csv');
        header('Pragma: no-cache');
        header('Expires: 0');
        $table->export_csv();
    } 

    echo FapiMemberTools::heading();

    ?>
    <div class="page smallerPadding">
        <h3>
            <?php
            if (empty(get_term_children($section->term_id, FapiLevels::TAXONOMY))) {
                echo __('Seznam členů úrovně "' . $section->name . '"', 'fapi-member');
            } else {
                echo __('Seznam členů sekce "' . $section->name . '"', 'fapi-member');
            }
            ?>
        </h3>
    <?php
    
    $table->display();
}
    ?>
    </div>
    <?php echo FapiMemberTools::help() ?>