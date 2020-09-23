<?php 
/**
 * Plugin Name:       Misson to the Moon Extend
 * Plugin URI:        https://shop.missiontothemoon.co/
 * Description:       Extend function for WooCommerce and Customize CSS.
 * Version:           3.0
 * Requires at least: 5.2
 * Requires PHP:      7.1
 * Author:            Bom Mongkon
 * Author URI:        https://github.com/adthonb
 * Text Domain:       misson-tt-moon
 * Domain Path:       /languages
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {

    add_action('woocommerce_init', 'woocommerce_loaded' );

    // Take care of anything that needs woocommerce to be loaded
    function woocommerce_loaded() {
        // Hook in
        add_action( 'woocommerce_after_checkout_billing_form', 'mttm_taxid_checkout_field');
        add_action( 'woocommerce_checkout_update_order_meta', 'mttm_taxid_checkout_order_meta' );
        add_action( 'wp_enqueue_scripts', 'mttm_enqueue' );
        add_filter( 'bulk_actions-edit-shop_order', 'mttm_parcel_sticker_bulk_actions', 20, 1 );
        add_filter( 'handle_bulk_actions-edit-shop_order', 'mttm_handle_parcel_sticker_bulk_actions', 10, 3 );
        add_filter( 'bulk_actions-edit-shop_order', 'mttm_receipt_bulk_actions', 30, 1 );
        add_filter( 'handle_bulk_actions-edit-shop_order', 'mttm_handle_receipt_bulk_actions', 20, 3 );
        add_action( 'admin_menu', 'mttm_export_order_menu');
        add_action( 'admin_post_mttm_export_order', 'mttm_export_order_handler' );
        add_option( 'mttm_receipt_count', 1);
        //delete_useless_post_meta();
    }

    function mttm_enqueue() {
        wp_enqueue_style( 'mttm-css', plugins_url('assets/mttm.css', __FILE__ ) );
        if (is_checkout()) {
            wp_enqueue_script( 'mttm-js', plugins_url('assets/mttm.js', __FILE__ ), array('jquery') );
        }
    }

    // Our hooked in function - $fields is passed via the filter!
    function custom_checkout_fields( $fields ) {

    unset($fields['billing']['billing_address_2']);
    $fields['billing']['billing_company']['placeholder'] = 'หากต้องการใบกำกับภาษี กรุณากรอกชื่อบริษัท';
    $fields['billing']['billing_company']['priority'] = 120;

    return $fields;
    }

    // Add Tax ID field to check out page
    function mttm_taxid_checkout_field( $fields ){
        woocommerce_form_field( 'requestVAT', array(
            'type'          => 'text',
            'class'         => array('my-field-class form-row-wide'),
            'label'         => __('เลขประจำตัวผู้เสียภาษี'),
            'placeholder'         => __('หากต้องการใบกำกับภาษี กรุณากรอกเลขประจำตัวผู้เสียภาษี'),
            ), $fields->get_value( 'requestVAT' ));
    }

    function mttm_taxid_checkout_order_meta( $order_id ) {
        if ( ! empty( $_POST['requestVAT'] ) ) {
            update_post_meta( $order_id, 'requestVAT', sanitize_text_field( $_POST['requestVAT'] ) );
        }
    }

    function mttm_parcel_sticker_bulk_actions( $actions ) {
        $actions['parcel_sticker'] = __('Print Parcel Sticker', 'misson-tt-moon');
        return $actions;
    }

    function mttm_handle_parcel_sticker_bulk_actions( $redirect_to, $action, $post_ids ) {
        if ($action !== 'parcel_sticker')
            return $redirect_to;
        
        require_once 'vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'th-TH',
            'format' => [76.2, 101.6],
            'dpi' => 300,
            'margin_left' => 5,
            'margin_right' => 5,
            'margin_top' => 5,
            'margin_bottom' => 5
        ]);

        foreach($post_ids as $key => $post_id) {
            $order = wc_get_order($post_id);
            $states_list = WC()->countries->get_states('TH');
            $states = $order->get_shipping_state();
            $subcity_prefix = ($states === 'TH-10') ? 'แขวง' : 'ตำบล';
            $city_prefix = ($states === 'TH-10') ? 'เขต' : 'อำเภอ';
            $company = ($order->get_billing_company()) ? $order->get_billing_company() : '';

            $address = "{$company} {$order->get_billing_address_1()} {$subcity_prefix}{$order->get_meta('_billing_sub_city')} {$city_prefix}{$order->get_billing_city()} {$states_list[$states]}";

            // Buffer the following html with PHP so we can store it to a variable later
            ob_start();           
            include 'assets/sticker.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);

            end($post_ids);
            if ($key !== key($post_ids)) {
                $mpdf->AddPage();
            }
        }
        
        //$mpdf->Output('sticker.pdf', \Mpdf\Output\Destination::DOWNLOAD);
        $mpdf->Output();
    }

    function mttm_export_order_menu() {
        add_management_page( __('MTTM Export Order'), __('MTTM Export Order'), 'manage_options', 'mttm-export-order-page', 'mttm_export_order_page');
    }

    function mttm_export_order_page() { ?>
        <div id="mttm-export-order">
            <h1>ดาวน์โหลดข้อมูลลูกค้าตามสินค้าที่สั่งซื้อ</h1>
            <p>สินค้าที่ซื้อ: Super Productive Planner 2020</p>
            <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="get">
                <input type="hidden" name="action" value="mttm_export_order">
                <input type="submit" value="Download">
            </form>
        </div>
    <?php }

    function mttm_export_order_handler() {
        $orders_id = mttm_get_orders_ids_by_product_id(424);
        $data = array(
            array('Order Number', 'Name', 'Email')
        );

        foreach($orders_id as $orders_id) {
            $order = wc_get_order($orders_id);
            $data[] = [
                $order->get_order_number(),
                $order->get_formatted_shipping_full_name(),
                $order->get_billing_email()
            ];
        }

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename=data.csv');
        $output = fopen('php://output', 'w');
        foreach ($data as $row) {
            fputcsv($output, $row);
        }
        fclose($output);
    }

    function mttm_get_orders_ids_by_product_id( $product_id, $order_status = array('wc-processing') ) {
        global $wpdb;

        $results = $wpdb->get_col("
        SELECT order_items.order_id
        FROM {$wpdb->prefix}woocommerce_order_items as order_items
        LEFT JOIN {$wpdb->prefix}woocommerce_order_itemmeta as order_item_meta ON order_items.order_item_id = order_item_meta.order_item_id
        LEFT JOIN {$wpdb->posts} AS posts ON order_items.order_id = posts.ID
        WHERE posts.post_type = 'shop_order'
        AND posts.post_status IN ( '" . implode( "','", $order_status ) . "' )
        AND order_items.order_item_type = 'line_item'
        AND order_item_meta.meta_key = '_product_id'
        AND order_item_meta.meta_value = '$product_id'
        ");

        return $results;
    }

    function mttm_receipt_bulk_actions($actions) {
        $actions['print_receipt'] = __('Print Receipt', 'misson-tt-moon');
        return $actions;
    }

    function mttm_handle_receipt_bulk_actions($redirect_to, $action, $post_ids) {
        if ($action !== 'print_receipt') {
            return $redirect_to;
        }

        require_once 'vendor/autoload.php';
        $mpdf = new \Mpdf\Mpdf([
            'mode' => 'th-TH',
            'default_font_size' => 9,
            'margin_top' => 8,
            'margin_bottom' => 8
        ]);

        foreach($post_ids as $key => $post_id) {
            $order = wc_get_order($post_id);
            $states_list = WC()->countries->get_states('TH');
            $states = $order->get_billing_state();
            $subcity_prefix = ($states === 'TH-10') ? 'แขวง' : 'ตำบล';
            $city_prefix = ($states === 'TH-10') ? 'เขต' : 'อำเภอ';

            $doc_subtitle = 'ต้นฉบับ';
            ob_start();           
            include 'assets/receipt.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);
            $mpdf->AddPage();

            $doc_subtitle = 'สำเนา';
            ob_start();           
            include 'assets/receipt.php';
            $html = ob_get_contents();
            ob_end_clean();
            $mpdf->WriteHTML($html);

            // check if order not have receipt number, add it
            if (!$order->get_meta('mttm_receipt_number')) {
                $order->add_meta_data('mttm_receipt_number', get_option('mttm_receipt_count'));
                $order->save_meta_data();
                update_option('mttm_receipt_count', get_option('mttm_receipt_count') + 1);
            }

            end($post_ids);
            if ($key !== key($post_ids)) {
                $mpdf->AddPage();
            }
        }

        $mpdf->Output(); //$mpdf->Output('receipt.pdf', \Mpdf\Output\Destination::DOWNLOAD);
    }

    function delete_useless_post_meta() {
        global $wpdb;
        $table = $wpdb->prefix.'postmeta';
        $wpdb->delete ($table, array('meta_key' => 'mttm_receipt_number'));
    }
}
?>