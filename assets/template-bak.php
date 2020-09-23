<html>
<head>
<style>
.container {
    font-family: 'sarabun';
}
p, address {
    margin: 0;
    line-height: 1.1;
    font-style: normal;
}
.sender {
    margin-bottom: 50px;
    font-size: 80%;
}
.receive {
    width: 88%;
    margin: auto;
    font-weight: bold;
    border: 0.5mm solid black;
    padding: 5%;
}
.item {
    list-style-type: none;
    font-size: 70%;
}
table {
    border-collapse: collapse;
    font-family: sans-serif;
    margin-top: 2%;
}
td {
    font-weight: bold;
    border: 1px solid black;
    padding: 2mm;
}
</style>
</head>
<body>
    <div class="container">
        <div class="sender">
            <p>บริษัท มิชชั่น ทู เดอะ มูน มีเดีย จำกัด</p>
            <address>471/1 ซอยพระรามเก้า51 (ถนนเสรี 6) แขวงพัฒนาการ เขตสวนหลวง กรุงเทพฯ 10250</address>
        </div>
        <div class="receive">
            <p>ผู้รับ:</p>
            <p>คุณ<?php esc_html_e( $order->get_formatted_shipping_full_name(), 'misson-tt-moon' ); ?></p>
            <p>โทร. <?php esc_html_e( $order->get_billing_phone(), 'misson-tt-moon' ); ?></p>
            <address><?php esc_html_e( $address, 'misson-tt-moon' ); ?></address>
            <table>
                <tr>
                <?php
                foreach (str_split($order->get_shipping_postcode()) as $number) {
                    echo "<td>{$number}</td>";
                }
                ?>
                </tr>
            </table>
        </div>
        <ul class="item">
            <?php
            foreach ($order->get_items() as $item ) {
                $iname = $item->get_name();
                $qty = $item->get_quantity();
                echo "<li>{$iname} ({$qty})</li>";
            }
            ?>
        </ul>
    </div>
    <div style="width: 50%;position: fixed;bottom: 0;right: 0;">
        <p style="text-align: right;font-family: 'sarabun';font-size: 70%;"><?php esc_html_e($order->get_order_number()); ?></p>
    </div>
</body>
</html>