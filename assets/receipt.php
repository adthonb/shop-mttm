<html>
<head>
<style>
.container, .footer {
    line-height: 1.7;
}
.info-left, .info-right {
    float: left;
    width: 49%;
}
.address {
    width: 85%;
}
p {
    margin: 0;
}
.receipt-header {
    width: 100%;
    margin-top: 10px;
    border-top: 1px solid gray;
}
.receipt-header td {
    width: 50%;
    padding: 0;
    line-height: 1.5;
}
.summary td, .summary th {
    line-height: 1.8;
}
.items-list, .total {
    width: 100%;
    margin-top: 10px;
    border-collapse: collapse;
}
.items-list td {
    border-bottom: 1px solid gray;
}
.items-list th {
    border-top: 1px solid gray;
    border-bottom: 1px solid gray;
}
.total td, .total th {
    text-align: right;
}
.footer {
    position: absolute;
    bottom: 8mm;
    width: 85%;
}
.box {
    border: 5px solid gray;
}
.sign-col {
    width: 36mm;
    text-align: center;
    border-top: 1px solid gray;
    padding-top: 5px;
}
</style>
</head>
<body>
    <div class="container">
        <div class="info">
            <div class="info-left">
                <div class="seller" style="margin-bottom: 10px;">
                    <img src="<?php echo plugin_dir_path( __DIR__ ) . 'assets/logo-m2m-black.jpg' ?>" width="140px" style="margin-bottom: 10px;"/>
                    <p>บริษัท มิชชั่น ทู เดอะ มูน มีเดีย จำกัด (สำนักงานใหญ่)</p>
                    <p class="address">471/1 ซอยพระรามเก้า51 (ถนนเสรี 6) แขวงพัฒนาการ เขตสวนหลวง กรุงเทพฯ 10250</p>
                    <p>เลขประจำตัวผู้เสียภาษี 0105532093376</p>
                    <p>www.missiontothemoon.co</p>
                </div>
                <div class="buyer">
                    <p style="color: #4a99da;font-weight: bold;">ลูกค้า</p>
                    <p><?php echo ($order->get_billing_company()) ? esc_html($order->get_billing_company()) : esc_html($order->get_formatted_billing_full_name()); ?></p>
                    <?php
                        echo "<p class='address'>{$order->get_billing_address_1()} {$subcity_prefix}{$order->get_meta('_billing_sub_city')} {$city_prefix}{$order->get_billing_city()} {$states_list[$states]}</p>";
                    if ($order->get_meta('requestVAT')) {
                        echo "<p>เลขประจำตัวผู้เสียภาษี {$order->get_meta('requestVAT')}</p>";
                    } ?>
                </div>
            </div>
            <div class="info-right">
                <div style="text-align: center;">
                    <p style="line-height: 1.3;font-size: 13pt;font-weight: bold;color: #4a99da;">ใบกำกับภาษี/ใบเสร็จรับเงิน/ใบส่งสินค้า</p>
                    <p style="font-weight: bold;color: #4a99da;"><?php echo $doc_subtitle; ?> (เอกสารออกเป็นชุด)</p>
                </div>
                <table class="receipt-header">
                    <tr><td style="padding-top: 10px;">เลขที่</td><td style="padding-top: 10px;">
                    <?php $number = ($order->get_meta('mttm_receipt_number')) ? $order->get_meta('mttm_receipt_number') : get_option('mttm_receipt_count');
                    echo 'MMS'.date('Y').str_pad($number, 6, '0', STR_PAD_LEFT); ?></td></tr>
                    <tr><td>รายการสั่งซื้อเลขที่</td><td><?php echo $post_id; ?></td></tr>
                    <tr><td>วันที่</td><td><?php echo ($order->get_date_paid()) ? wc_format_datetime($order->get_date_paid()) : 'รอการชำระเงิน'; ?></td></tr>
                    <tr><td>ผู้ขาย</td><td>ประเสริฐ หงษ์สุวรรณ</td></tr>
                </table>
                <table class="receipt-header">
                    <tr><td style="padding-top: 10px;">ผู้ติดต่อ</td><td style="padding-top: 10px;"><?php echo esc_html($order->get_formatted_billing_full_name()); ?></td></tr>
                    <tr><td>เบอร์โทร</td><td><?php echo $order->get_billing_phone(); ?></td></tr>
                    <tr><td>อีเมล์</td><td><?php echo $order->get_billing_email(); ?></td></tr>  
                </table>
            </div>
        </div>
        <div class="summary">
            <table class="items-list">
                <tr>
                    <th>#</th>
                    <th>รายละเอียด</th>
                    <th align="right">จำนวน</th>
                    <th align="right">ราคาต่อหน่วย</th>
                    <th align="right">ยอดรวม</th>
                </tr>
                <?php
                $line_items = $order->get_items();
                foreach ( $line_items as $item_id => $item ) { ?>
                <tr>
                    <td align="center">1</td>
                    <td><?php echo wp_kses_post( $item->get_name() ); ?></td>
                    <td align="right"><?php echo esc_html( $item->get_quantity() ); ?></td>
                    <td align="right"><?php echo wc_price($order->get_item_total($item, true, true), array('currency' => ' ')); ?></td>
                    <td align="right"><?php echo wc_price($order->get_line_total($item, true, true), array('currency' => ' ')); ?></td>
                </tr>
                <?php } ?>
            </table>
            <table class="total">
                <tr>
                    <th width="80%"></th>
                    <th></th>
                </tr>
                <tr>
                    <td style="font-weight: bold;color: #2196f3;">ค่าจัดส่ง</td>
                    <td><?php echo wc_price($order->get_shipping_total() + $order->get_shipping_tax(), array('currency' => ' ')); ?> บาท</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;color: #2196f3;">รวมเป็นเงิน</td>
                    <?php
                    foreach ( $line_items as $item ) {
                        $items_subtotal += $item->get_subtotal() + $item->get_subtotal_tax();
                    } 
                    $subtotal = wc_price($items_subtotal + $order->get_shipping_total() + $order->get_shipping_tax(), array('currency' => ' '));
                    echo "<td>{$subtotal} บาท</td>";
                    ?>
                </tr>
                <tr>
                    <td style="font-weight: bold;color: #2196f3;">ภาษีมูลค่าเพิ่ม 7%</td>
                    <td><?php echo wc_price($order->get_total_tax(), array('currency' => ' ')); ?> บาท</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;color: #2196f3;">ราคาไม่รวมภาษีมูลค่าเพิ่ม</td>
                    <td><?php echo wc_price($order->get_subtotal(), array('currency' => ' ')); ?> บาท</td>
                </tr>
                <tr>
                    <td style="font-weight: bold;color: #2196f3;">จำนวนเงินรวมทั้งสิ้น</td>
                    <td><?php echo wc_price($order->get_total(), array('currency' => ' ')); ?> บาท</td>
                </tr>
            </table>
        </div>
    </div>
    <div class="footer">
        <p style="font-weight: bold;">หมายเหตุ : <?php esc_html_e($order->get_customer_note()); ?></p>
        <p>การชำระเงินจะสมบูรณ์เมื่อบริษัทได้รับเงินเรียบร้อยแล้ว <input type="checkbox"> เงินสด <input type="checkbox"> เช็ค <input type="checkbox"> โอนเงิน <input type="checkbox" > บัตรเครดิต</p>
        <p>ธนาคาร____________________________เลขที่___________________วันที่________________จำนวนเงิน________________</p>
        <div style="width: 49%;float: left;">ในนาม <?php echo esc_html($order->get_formatted_billing_full_name()); ?></div>
        <div style="width: 49%;float: left;text-align:right;">ในนาม บริษัท มิชชั่น ทู เดอะ มูน มีเดีย จำกัด</div>
        <table style="border-spacing: 10px 5px;margin-top: 50px;">
            <tr>
                <td></td>
                <td></td>
                <td></td>
                <td><img src="<?php echo plugin_dir_path( __DIR__ ) . 'assets/sign.jpg'; ?>" width="36mm"></td>
                <td align="center"><?php echo date('d/m/Y'); ?></td>
            </tr>    
            <tr>
                <td class="sign-col">ผู้จ่ายเงิน</td>
                <td class="sign-col">วันที่</td>
                <td width="36mm"></td>
                <td class="sign-col">ผู้รับเงิน</td>
                <td class="sign-col">วันที่</td>
            </tr>
        </table>
    </div>
</body>
</html>