<?php

class download_order_nscwto
{

    public function download_by_specific_ordernumbers_nscwto($orderNumbers)
    {
        $all_orders = $this->get_orders("order_id_list", $orderNumbers);
        $xmlArray = $this->get_opentrans_xml($all_orders);
        if (empty($xmlArray["string"])) {
            return __("No order found for order numbers.");
        }

        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xmlArray["string"]);
        $xml = $xmlDoc->saveXML();

        apply_filters('containing_order_ids_for_download_nsc_wto', $xmlArray["containingOrderIds"]);

        if (!current_user_can('view_woocommerce_reports')) {
            return __("No permissions to export orders.");
        }

        header("Content-type: application/xml", true, 200);
        header("Content-Disposition: attachment; filename=order-list.xml");
        header("Pragma: no-cache");
        header("Expires: 0");
        echo $xml;
        exit;
    }

    public function download_by_ordernumber_nscwto($startOrderNummer)
    {
        $all_orders = $this->get_orders("ordernum", $startOrderNummer);
        $xml = $this->get_opentrans_xml($all_orders);
        apply_filters('containing_order_ids_for_download_nsc_wto', $xml["containingOrderIds"]);
        if (empty($xml["string"])) {
            return __("No orders found after specified order number.");
        }

        if (!current_user_can('view_woocommerce_reports')) {
            return __("No permissions to export orders.");
        }

        header("Content-type: application/xml", true, 200);
        header("Content-Disposition: attachment; filename=orders-starting-from-" . $startOrderNummer . ".xml");
        header("Pragma: no-cache");
        header("Expires: 0");
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xml["string"]);
        echo $xmlDoc->saveXML();
        exit;
    }

    public function download_by_date_nscwto($startDate)
    {
        $all_orders = $this->get_orders("date", $startDate);
        $xml = $this->get_opentrans_xml($all_orders);
        apply_filters('containing_order_ids_for_download_nsc_wto', $xml["containingOrderIds"]);
        if (empty($xml["string"])) {
            return __("No orders found after specified date.");
        }

        if (!current_user_can('view_woocommerce_reports')) {
            return __("No permissions to export orders.");
        }

        header("Content-type: application/xml", true, 200);
        header("Content-Disposition: attachment; filename=orders-starting-from-" . $startDate . ".xml");
        header("Pragma: no-cache");
        header("Expires: 0");
        $xmlDoc = new DOMDocument();
        $xmlDoc->loadXML($xml["string"]);
        echo $xmlDoc->saveXML();
        exit;
    }

    public function get_opentrans_xml($all_orders)
    {
        global $wpdb;
        $arrayToBeReturned = array("string" => "", "containingOrderIds" => array());

        if ($all_orders === false) {
            $arrayToBeReturned = apply_filters('before_opentrans_xml_return_nsc_wto', $arrayToBeReturned);
            return $arrayToBeReturned;
        }

        $xml_string = '<?xml version="1.0" encoding="UTF-8"?>' . "\n" . "<ORDER_LIST>\n";
        $xml_string = apply_filters('xml_string_nsc_wto', $xml_string, "opening_order_list", null, null, null);
        /* check each order */
        $order_ids = array();
        foreach ($all_orders as $order) {
            $order_id = $this->get_order_id($order);
            $order_ids[] = array("order_id" => $order_id);
            $query = $wpdb->prepare("SELECT meta_key, meta_value FROM " . esc_sql($wpdb->prefix) . "postmeta WHERE post_id=%s", esc_sql($order_id));
            $order_metas = $wpdb->get_results($query);
            $order_items = $this->get_items($order_id);
            $xml_string_before_filter = $this->get_single_order_xml_string($order, $order_metas, $order_items);
            $xml_string .= apply_filters('xml_string_nsc_wto', $xml_string_before_filter, "order", $order, $order_metas, $order_items);
        }

        $xml_string_before_filter = "</ORDER_LIST>";
        $xml_string .= apply_filters('xml_string_nsc_wto', $xml_string_before_filter, "closing_order_list", null, null, null);

        $arrayToBeReturned = array("string" => $xml_string, "containingOrderIds" => $order_ids);
        $arrayToBeReturned = apply_filters('before_opentrans_xml_return_nsc_wto', $arrayToBeReturned, $all_orders);
        return $arrayToBeReturned;
    }

    private function get_single_order_xml_string($order, $order_metas, $order_items)
    {
        $order_id = $this->get_order_id($order);
        $bill_country = $this->get_post_meta_key('_billing_country', $order_metas);
        $xml_string = "\t" . '<ORDER xmlns="http://www.opentrans.org/XMLSchema/1.0" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.0" type="standard">' . "\n" .
        "\t\t<ORDER_HEADER>\n" .
        "\t\t\t<CONTROL_INFO>\n" .
        "\t\t\t\t<GENERATOR_INFO>Order Export to Lexware " . NSC_WTO_VERSION . "</GENERATOR_INFO>\n" .
        "\t\t\t\t<GENERATION_DATE>" . date(DATE_ATOM) . "</GENERATION_DATE>\n" .
            "\t\t\t</CONTROL_INFO>\n";
        $xml_string .= "\t\t\t<ORDER_INFO>\n";
        $xml_string .= "\t\t\t\t<ORDER_ID>" . esc_html($order_id) . "</ORDER_ID>\n";
        $xml_string .= "\t\t\t\t<ORDER_DATE>" . esc_html($this->get_order_date($order_id)) . "</ORDER_DATE>\n";
        $xml_string .= "\t\t\t\t<ORDER_PARTIES>\n";
        $xml_string .= "\t\t\t\t\t<BUYER_PARTY>\n";
        $xml_string .= "\t\t\t\t\t\t<PARTY>\n";
        $xml_string .= "\t\t\t\t\t\t\t<ADDRESS>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME>" . $this->get_name_tag_value('shipping', $order_metas, "opentransV1") . "</NAME>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME2>" . $this->get_name_two_tag_value('shipping', $order_metas, "opentransV1") . "</NAME2>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME3>" . $this->get_name_three_tag_value('shipping', $order_metas, "opentransV1") . "</NAME3>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<STREET>" . $this->get_post_meta_key('_shipping_address_1', $order_metas) . "</STREET>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<DEPARTMENT>" . $this->get_post_meta_key('_shipping_address_2', $order_metas) . "</DEPARTMENT>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<ZIP>" . $this->get_post_meta_key('_shipping_postcode', $order_metas) . "</ZIP>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<CITY>" . $this->get_post_meta_key('_shipping_city', $order_metas) . "</CITY>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<COUNTRY>" . $this->get_post_meta_key('_shipping_country', $order_metas) . "</COUNTRY>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<PHONE type=\"other\">" . $this->get_post_meta_key('_shipping_phone', $order_metas) . "</PHONE>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<FAX>" . $this->get_post_meta_key('_shipping_fax', $order_metas) . "</FAX>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<EMAIL>" . $this->get_post_meta_key('_shipping_email', $order_metas) . "</EMAIL>\n";
        $xml_string .= "\t\t\t\t\t\t\t</ADDRESS>\n";
        $xml_string .= "\t\t\t\t\t\t</PARTY>\n";
        $xml_string .= "\t\t\t\t\t</BUYER_PARTY>\n";
        $xml_string .= "\t\t\t\t\t<INVOICE_PARTY>\n";
        $xml_string .= "\t\t\t\t\t\t<PARTY>\n";
        $xml_string .= "\t\t\t\t\t\t\t<ADDRESS>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME>" . $this->get_name_tag_value('billing', $order_metas, "opentransV1") . "</NAME>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME2>" . $this->get_name_two_tag_value('billing', $order_metas, "opentransV1") . "</NAME2>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<NAME3>" . $this->get_name_three_tag_value('billing', $order_metas, "opentransV1") . "</NAME3>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<STREET>" . $this->get_post_meta_key('_billing_address_1', $order_metas) . "</STREET>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<DEPARTMENT>" . $this->get_post_meta_key('_billing_address_2', $order_metas) . "</DEPARTMENT>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<ZIP>" . $this->get_post_meta_key('_billing_postcode', $order_metas) . "</ZIP>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<CITY>" . $this->get_post_meta_key('_billing_city', $order_metas) . "</CITY>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<COUNTRY>" . esc_html($bill_country) . "</COUNTRY>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<PHONE type=\"other\">" . $this->get_post_meta_key('_billing_phone', $order_metas) . "</PHONE>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<FAX>" . $this->get_post_meta_key('_billing_fax', $order_metas) . "</FAX>\n";
        $xml_string .= "\t\t\t\t\t\t\t\t<EMAIL>" . $this->get_post_meta_key('_billing_email', $order_metas) . "</EMAIL>\n";
        $xml_string .= "\t\t\t\t\t\t\t</ADDRESS>\n";
        $xml_string .= "\t\t\t\t\t\t</PARTY>\n";
        $xml_string .= "\t\t\t\t\t</INVOICE_PARTY>\n";
        $xml_string .= "\t\t\t\t</ORDER_PARTIES>\n";
        $xml_string .= "\t\t\t\t<PRICE_CURRENCY>" . esc_html($this->get_currency($order_metas)) . "</PRICE_CURRENCY>\n";
        /* payment-block is missing */
        $xml_string .= "\t\t\t\t<REMARK type=\"tax_area\">" . esc_html($this->get_remark_tax($bill_country)) . "</REMARK>\n";
        $xml_string .= "\t\t\t\t<REMARK type=\"order\">" . esc_html($this->get_order_remark($order)) . "</REMARK>\n";
        /* additional costs don't work because WooCommerce ist adding extra costs to fright costs */
        $xml_string .= "\t\t\t</ORDER_INFO>\n";
        $xml_string .= "\t\t</ORDER_HEADER>\n";
        $xml_string .= "\t\t<ORDER_ITEM_LIST>\n";

        $gesamtpreis = 0;
        $order_items_count = count($order_items);
        for ($i = 0; $i < $order_items_count; $i++) {
            $xml_string .= "\t\t\t<ORDER_ITEM>\n";
            $xml_string .= "\t\t\t\t<LINE_ITEM_ID>" . esc_html($i) . "</LINE_ITEM_ID>\n";
            $xml_string .= "\t\t\t\t<ARTICLE_ID>\n";
            $xml_string .= "\t\t\t\t\t<SUPPLIER_AID>" . esc_html($order_items[$i]['sku']) . "</SUPPLIER_AID>\n";
            $xml_string .= "\t\t\t\t\t<DESCRIPTION_SHORT>" . esc_html($order_items[$i]['description_short']) . "</DESCRIPTION_SHORT>\n";
            $xml_string .= "\t\t\t\t\t<DESCRIPTION_LONG>" . "" . "</DESCRIPTION_LONG>\n";
            $xml_string .= "\t\t\t\t</ARTICLE_ID>\n";
            $xml_string .= "\t\t\t\t<QUANTITY>" . esc_html($order_items[$i]['quantity']) . "</QUANTITY>\n";
            $xml_string .= "\t\t\t\t<ORDER_UNIT>1</ORDER_UNIT>\n";
            $xml_string .= "\t\t\t\t<ARTICLE_PRICE type=\"net_list\">\n";
            $xml_string .= "\t\t\t\t\t<PRICE_AMOUNT>" . esc_html($order_items[$i]['item_price']) . "</PRICE_AMOUNT>\n";
            $xml_string .= "\t\t\t\t\t<TAX_AMOUNT>" . esc_html($order_items[$i]['item_tax_amount']) . "</TAX_AMOUNT>\n";
            $xml_string .= "\t\t\t\t\t<PRICE_LINE_AMOUNT>" . esc_html($order_items[$i]['line_total']) . "</PRICE_LINE_AMOUNT>\n";
            $gesamtpreis += esc_html($order_items[$i]['line_total']);
            $xml_string .= "\t\t\t\t</ARTICLE_PRICE>\n";
            $xml_string .= "\t\t\t</ORDER_ITEM>\n";
        }

        $xml_string .= "\t\t</ORDER_ITEM_LIST>\n";
        $xml_string .= "\t\t<ORDER_SUMMARY>\n";
        $xml_string .= "\t\t\t<TOTAL_ITEM_NUM>" . esc_html($order_items_count) . "</TOTAL_ITEM_NUM>\n";
        $xml_string .= "\t\t\t<TOTAL_AMOUNT>" . esc_html($gesamtpreis) . "</TOTAL_AMOUNT>\n";
        $xml_string .= "\t\t</ORDER_SUMMARY>\n";
        $xml_string .= "\t</ORDER>\n";
        return $xml_string;

    }

    private function get_name_tag_value($type, $metaKeys, $openTransVersion)
    {
        $nameValue = $this->get_post_meta_key('_' . $type . '_company', $metaKeys);
        $nameValue = apply_filters('get_name_value_xml_export_nsc_wto', $nameValue, $type, $metaKeys, $openTransVersion);
        return $nameValue;
    }

    private function get_name_two_tag_value($type, $metaKeys, $openTransVersion)
    {
        $nameValue = $this->get_post_meta_key('_' . $type . '_last_name', $metaKeys);
        $nameValue = apply_filters('get_name_two_value_xml_export_nsc_wto', $nameValue, $type, $metaKeys, $openTransVersion);
        return $nameValue;
    }

    private function get_name_three_tag_value($type, $metaKeys, $openTransVersion)
    {
        $nameValue = $this->get_post_meta_key('_' . $type . '_first_name', $metaKeys);
        $nameValue = apply_filters('get_name_three_value_xml_export_nsc_wto', $nameValue, $type, $metaKeys, $openTransVersion);
        return $nameValue;
    }

    private function replace_special_chars($string)
    {
        $string = str_replace('€', 'EUR', $string);
        $string = htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
        $string = str_replace('#039', 'apos', $string);
        return $string;
    }

    private function get_orders($limitType, $limitValue)
    {
        global $wpdb;
        $limitString = "AND p.ID >= " . esc_sql($limitValue);
        if ($limitType === "date") {
            $limitString = "AND p.post_date >= '" . esc_sql($limitValue) . "'";
        }

        if ($limitType === "order_id_list") {
            $limitString = "AND p.ID in (" . esc_sql($limitValue) . ")";
        }

        $query = "SELECT ID, post_date, post_excerpt FROM `" . esc_sql($wpdb->prefix) . "posts` as p WHERE p.post_type='shop_order' " . $limitString . " ORDER BY p.ID";
        $query = apply_filters('query_get_order_meta_nsc_wto', $query, $limitType, $limitValue, $limitString);

        $result = $wpdb->get_results($query);
        if ($wpdb->num_rows === 0) {
            return false;
        }
        return $result;
    }

    private function get_order_id($order)
    {
        return $order->ID;
    }

    private function get_order_date($order_id)
    {
        return get_post_time(DateTime::ATOM, false, $order_id);
    }

    private function get_order_remark($order)
    {
        return $this->replace_special_chars($order->post_excerpt);
    }

    private function get_currency($metaKeys)
    {
        return $this->get_post_meta_key("_order_currency", $metaKeys);
    }

    private function get_items($order_id)
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT order_item_name,order_item_type,order_item_id FROM " . esc_sql($wpdb->prefix) . "woocommerce_order_items WHERE order_id=%s", esc_sql($order_id));
        $result = $wpdb->get_results($query);
        $items_in_order = $wpdb->num_rows;
        $items = array();
        foreach ($result as $item) {
            if ($item->order_item_type === "line_item") {
                $newItem = $this->get_item($item);
                $newItem = apply_filters('modify_order_line_item_nscwto', $newItem, $item, $item->order_item_type);
                $items[] = $newItem;
            }
            $items = apply_filters('get_items_order_items_nscwto', $items, $item, $item->order_item_type);
        }

        return $items;

    }

    private function get_item_metas($order_item_id)
    {
        global $wpdb;
        $query = $wpdb->prepare("SELECT * FROM " . esc_sql($wpdb->prefix) . "woocommerce_order_itemmeta WHERE order_item_id= %s ORDER BY meta_key", esc_sql($order_item_id));
        $result = $wpdb->get_results($query);
        return $result;
    }

    private function get_item($item)
    {
        $order_item_id = $item->order_item_id;
        $new_item = array();
        $new_item['description_short'] = $item->order_item_name;
        $item_metas = $this->get_item_metas($order_item_id);
        $new_item['quantity'] = $this->get_post_meta_key("_qty", $item_metas);
        $new_item['line_total'] = $this->get_post_meta_key("_line_total", $item_metas);
        if (empty($new_item['line_total'])) {
            $new_item['line_total'] = 0;
        }
        $new_item['line_tax_amount'] = $this->get_post_meta_key("_line_tax", $item_metas);
        if (empty($new_item['line_tax_amount'])) {
            $new_item['line_tax_amount'] = 0;
        }
        $new_item['item_price'] = $new_item['line_total'] / $new_item['quantity'];
        $new_item['item_tax_amount'] = round($new_item['line_tax_amount'] / $new_item['quantity'], 2);
        $new_item['sku'] = $this->get_item_sku($item_metas);
        return $new_item;
    }

    private function get_item_sku($item_metas)
    {
        global $wpdb;
        $item_id = $this->get_post_meta_key("_variation_id", $item_metas);
        if (empty($item_id)) {
            $item_id = $this->get_post_meta_key("_product_id", $item_metas);
        }
        $query = "SELECT meta_value FROM " . esc_sql($wpdb->prefix) . "postmeta WHERE post_id= " . esc_sql($item_id) . " AND meta_key='_sku'";
        return $wpdb->get_var($query);
    }

    private function get_post_meta_key($searchedMetaKey, $metaKeys)
    {

        foreach ($metaKeys as $meta_entry) {
            if ($meta_entry->meta_key === $searchedMetaKey) {
                if (!empty($meta_entry->meta_value)) {
                    return esc_html($this->replace_special_chars($meta_entry->meta_value));
                }
            }
        }

        $returnValue = "";
        $returnValue = apply_filters('get_post_meta_key_before_empty_return_nsc_wto', $returnValue, $searchedMetaKey, $metaKeys);
        return trim($returnValue);
    }

    private function get_remark_tax($country)
    {
        if ($this->is_eu_country($country)) {
            return "Merchant";
        }
        return "Non_EU";
    }

    private function is_eu_country($coutry_iso_two)
    {
        $eu = array(
            'BE',
            'BG',
            'DK',
            'DE',
            'EE',
            'FI',
            'FR',
            'GR',
            'IE',
            'IT',
            'HR',
            'LV',
            'LT',
            'LU',
            'MT',
            'NL',
            'AT',
            'PL',
            'PT',
            'RO',
            'SE',
            'SK',
            'SI',
            'ES',
            'CZ',
            'HU',
            'CY',
        );

        return in_array($coutry_iso_two, $eu);
    }

}
