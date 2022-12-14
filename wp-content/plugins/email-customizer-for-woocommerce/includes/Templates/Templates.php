<?php

namespace YayMail\Templates;

use YayMail\Helper\Helper;
// Templates Default
use YayMail\Templates\DefaultTemplate\CancelledOrder;
use YayMail\Templates\DefaultTemplate\CustomerCompletedOrder;
use YayMail\Templates\DefaultTemplate\CustomerInvoice;
use YayMail\Templates\DefaultTemplate\CustomerNewAccount;
use YayMail\Templates\DefaultTemplate\CustomerNote;
use YayMail\Templates\DefaultTemplate\CustomerOnHoldOrder;
use YayMail\Templates\DefaultTemplate\CustomerProcessingOrder;
use YayMail\Templates\DefaultTemplate\CustomerRefundedOrder;
use YayMail\Templates\DefaultTemplate\CustomerResetPassword;
use YayMail\Templates\DefaultTemplate\FailedOrder;
use YayMail\Templates\DefaultTemplate\NewOrder;
use YayMail\Templates\DefaultTemplate\CustomOrderStastus;
use YayMail\Templates\DefaultTemplate\YITHSubscription;
use YayMail\Templates\DefaultTemplate\OrderStatus;
use YayMail\Templates\DefaultTemplate\YITHMultiVendor\YITHMultiVendorDefault;
use YayMail\Templates\DefaultTemplate\YITHMultiVendor\YITHMultiVendorNewOrder;
use YayMail\Templates\DefaultTemplate\YITHMultiVendor\YITHMultiVendorCommissionsBulk;
use YayMail\Templates\DefaultTemplate\YITHMultiVendor\YITHMultiVendorRegistration;
use YayMail\Templates\DefaultTemplate\YITHMultiVendor\YITHMultiCommissions;
use YayMail\Templates\DefaultTemplate\GermanizedForWoo\SimpleInvoice;
use YayMail\Templates\DefaultTemplate\YITHWishlist\EstimateMail;
use YayMail\Templates\DefaultTemplate\WooBookings\BookingsDefault;

use YayMail\Templates\DefaultTemplate\BackInStockNotifier\NotifierInstockMail;
use YayMail\Templates\DefaultTemplate\BackInStockNotifier\NotifierSubscribeMail;

use YayMail\Templates\DefaultTemplate\AdvancedShipmentTracking\PartiallyShipped;

defined( 'ABSPATH' ) || exit;
/**
 * Plugin activate/deactivate logic
 */
class Templates {

	protected static $instance = null;

	public static function getInstance() {
		if ( null == self::$instance ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

	public static function getList() {
		$wc_emails             = \WC_Emails::instance();
		$listEmails            = (array) $wc_emails::instance()->emails;
		$listEmails            = apply_filters( 'YaymailCreateFollowUpTemplates', $listEmails );
		$listEmails            = apply_filters( 'YaymailCreateGermanMarketTemplates', $listEmails );
		$listEmailDefaultOfWoo = array(
			'new_order',
			'failed_order',
			'customer_reset_password',
			'customer_refunded_order',
			'customer_processing_order',
			'customer_on_hold_order',
			'customer_note',
			'customer_new_account',
			'customer_invoice',
			'customer_completed_order',
			'cancelled_order',
			'notifier_instock_mail',
			'notifier_subscribe_mail',
			'customer_partial_shipped_order',
		);
		// Get Default Template
		$newOrderArr       = NewOrder::getTemplates();
		$cancelledOrderArr = CancelledOrder::getTemplates();
		$cusComdOrderArr   = CustomerCompletedOrder::getTemplates();
		$cusInvoiceArr     = CustomerInvoice::getTemplates();
		$cusNewAccountArr  = CustomerNewAccount::getTemplates();
		$cusNoteArr        = CustomerNote::getTemplates();
		$cusOnHoldOrderArr = CustomerOnHoldOrder::getTemplates();
		$cusProOrderArr    = CustomerProcessingOrder::getTemplates();
		$cusRefdOrderArr   = CustomerRefundedOrder::getTemplates();
		$cusResPasswordArr = CustomerResetPassword::getTemplates();
		$faiOrderArr       = FailedOrder::getTemplates();

		$notifierInstockMail   = NotifierInstockMail::getTemplates();
		$notifierSubscribeMail = NotifierSubscribeMail::getTemplates();

		$customerPartialShippedOrder = PartiallyShipped::getTemplates();

		$listTemplates = array();
		$listTemplates = array_merge( $listTemplates, $notifierInstockMail );
		$listTemplates = array_merge( $listTemplates, $notifierSubscribeMail );
		$listTemplates = array_merge( $listTemplates, $newOrderArr );
		$listTemplates = array_merge( $listTemplates, $cancelledOrderArr );
		$listTemplates = array_merge( $listTemplates, $faiOrderArr );
		$listTemplates = array_merge( $listTemplates, $cusOnHoldOrderArr );
		$listTemplates = array_merge( $listTemplates, $cusProOrderArr );
		$listTemplates = array_merge( $listTemplates, $cusComdOrderArr );
		$listTemplates = array_merge( $listTemplates, $cusRefdOrderArr );
		$listTemplates = array_merge( $listTemplates, $cusInvoiceArr );
		$listTemplates = array_merge( $listTemplates, $cusNoteArr );
		$listTemplates = array_merge( $listTemplates, $cusResPasswordArr );
		$listTemplates = array_merge( $listTemplates, $cusNewAccountArr );
		$listTemplates = array_merge( $listTemplates, $customerPartialShippedOrder );

		foreach ( $listEmails as $key => $value ) {
			if ( ! in_array( $value->id, $listEmailDefaultOfWoo ) ) {
				if ( strpos( $value->id, 'wc_order_status_email' ) !== false ) {
					$orderStatusArr = OrderStatus::getTemplates( $value );
					$listTemplates  = array_merge( $listTemplates, $orderStatusArr );
				} else {
					$newTempalte = apply_filters( 'YaymailNewTempalteDefault', '', $key, $value );
					if ( isset( $newTempalte ) && null != $newTempalte && is_array( $newTempalte ) ) {
						$listTemplates = array_merge( $listTemplates, $newTempalte );
					}
				}
			}
		}

		return $listTemplates;
	}

	public static function getCssFortmat() {
		$yaymail_settings       = get_option( 'yaymail_settings' );
		$colorTableItems        = isset( $yaymail_settings['background_color_table_items'] ) && ! empty( $yaymail_settings['background_color_table_items'] ) ? $yaymail_settings['background_color_table_items'] : '#e5e5e5';
		$colorTitleTableItems   = isset( $yaymail_settings['title_items_color'] ) && ! empty( $yaymail_settings['title_items_color'] ) ? $yaymail_settings['title_items_color'] : '#96588a';
		$colorContentTableItems = isset( $yaymail_settings['content_items_color'] ) && ! empty( $yaymail_settings['content_items_color'] ) ? $yaymail_settings['content_items_color'] : '#636363';
		$custom_css             = isset( $yaymail_settings['custom_css'] ) && ! empty( $yaymail_settings['custom_css'] ) ? $yaymail_settings['custom_css'] : '';
		$enableCustomCss        = isset( $yaymail_settings['enable_css_custom'] ) && ! empty( $yaymail_settings['enable_css_custom'] ) ? $yaymail_settings['enable_css_custom'] : '';
		$orderImagePostions     = isset( $yaymail_settings['image_position'] ) && ! empty( $yaymail_settings['image_position'] ) ? $yaymail_settings['image_position'] : 'Top';
		/*
		 ======
		@@@ Start css for shortcode [yaymail_items_border]
		@@@ note: use for table has border
		====== */
		$order_dh = '<table></table>';
		$css      = 'table.yaymail_builder_table_items_border {
      border-collapse: separate !important;
      width: 100%;
      border: 1px solid ' . $colorTableItems . ';
      flex-direction: inherit;
    }';
		$css     .= 'table.yaymail_builder_table_items_content tbody tr td, table.yaymail_builder_table_items_content tbody tr th {
      vertical-align:middle;
      padding:12px;
      text-align:left;
      font-size: 14px;
      border-width: 1px;
      border-style: solid;
      border-color: inherit;
    }';
		$css     .= 'table.yaymail_builder_table_items_content tbody, table.yaymail_builder_table_items_content tbody tr, span[data-shordcode="yaymail_billing_shipping_address_content"] tbody, span[data-shordcode="yaymail_billing_shipping_address_content"] tbody tr {
      border-color: inherit;
    }';

		$css .= 'table.yaymail_builder_table_items_border img {
      max-width: 100%;
    }';

		$css .= 'table.yaymail_builder_table_items_border thead {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border thead tr {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border thead tr th {
      vertical-align:middle;
      padding:12px;
      text-align:left;
      font-family: inherit;
      font-size: 14px;
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tbody {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tbody tr {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tbody tr td {
      padding:12px;
      text-align:left;
      vertical-align:middle;
      font-family: inherit;
      word-break:normal;
      font-size: 14px;
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tfoot {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tfoot tr {
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tfoot tr td {
      vertical-align:middle;
      padding:12px;
      text-align:left;
      font-family: inherit;
      font-size: 14px;
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= ' table.yaymail_builder_table_items_border tfoot tr th {
      vertical-align:middle;
      padding:12px;
      text-align:left;
      font-size: 14px;
      border-width: 1px;
      border-style: solid;
      border-color: inherit
    }';

		$css .= 'table.yaymail_builder_table_items_border tr.order_item ul {
      font-size: small;
      margin: 1em 0 0;
      padding: 0;
      list-style: none;
    }';

		$css .= 'table.yaymail_builder_table_items_border tr.order_item li  strong {
      float:left;
      margin-right:.25em;
      clear:both;
    }';

		$css .= 'table.yaymail_builder_table_items_border tr.order_item li {
      margin:0.5em 0 0;
      padding:0;
    }';

		$css .= 'table.yaymail_builder_table_items_border tr.order_item li p {
      margin: 0;
    }';

		/* ====== End ====== */

		/*
		 ======
		@@@ Start  css for shortcode [yaymail_items]
		@@@ note: use for table not border
		====== */
		$css .= 'table.yaymail_builder_table_items {
      border-collapse: collapse !important;
      width: 100%;
      color:' . $colorContentTableItems . ';
    }';

		$css .= 'table.yaymail_builder_table_items img {
      max-width: 100%
    }';

			$css .= 'table.yaymail_builder_table_items tbody tr,
      table.yaymail_builder_table_items tbody tr td,
      table.yaymail_builder_table_items thead tr,
      table.yaymail_builder_table_items thead tr th,
      table.yaymail_builder_table_items thead tr td,
      table.yaymail_builder_table_items tfoot tr,
      table.yaymail_builder_table_items tfoot tr th,
      table.yaymail_builder_table_items tfoot tr td {
        vertical-align:middle;
        padding:12px;
        text-align:left;
        font-size:14px;
    }';

		$css .= 'table td {
      font-family: inherit;
    }';

		$css .= 'table.yaymail_builder_table_items tr {
      border-bottom: 1px solid ' . $colorTableItems . '
    }';

		$css .= '.yaymail_builder_bank_details, .yaymail_builder_account_name {
      color: inherit;
      display:block;
      font-family: inherit;
      font-size:18px;
      font-weight:bold;
      line-height:130%;
      margin:0 0 18px;
    }';

		$css .= '.yaymail_builder_order {
      margin-bottom: 0.83em;
      display:block;
      font-family: inherit;
      font-size:18px;
      font-weight:bold;
      line-height:130%;
      margin:0 0 18px;
    }';

		$css .= 'table.yaymail_builder_table_items tr.order_item ul {
      font-size: small;
      margin: 1em 0 0;
      padding: 0;
      list-style: none;
    }';

		$css .= 'table.yaymail_builder_table_items tr.order_item li  strong {
      float:left;
      margin-right:.25em;
      clear:both
    }';

		$css .= 'table.yaymail_builder_table_items tr.order_item li {
      margin:0.5em 0 0;
      padding:0;
    }';

		$css .= 'table.yaymail_builder_table_items tr.order_item li p {
      margin: 0;
    }';

		$css .= 'table.yaymail_builder_table_items ul li {
      list-style: none;
      margin-left: 0px;
    }';
		/* ====== End ====== */

				$css .= 'h3.yaymail_builder_account_name {
      font-size:16px;
    }';

		$css .= '.yaymail_builder_order a.yaymail_builder_link {
      font-weight: normal;
      text-decoration: underline;
    }';

		$css .= '.yaymail_builder_order ul {
      padding-left: 0px
    }';

		$css .= 'section.yaymail_builder_wrap_account {
      color: inherit;
      font-family: inherit;
      font-size: 14px;
      line-height: 150%;
      text-align: left;
    }';

		$css .= 'section.yaymail_builder_wrap_account ul {
      color: ' . $colorContentTableItems . ';
    }';

		$css .= '.yaymail_builder_instructions{
      color: ' . $colorContentTableItems . ';
      font-family: inherit;
      font-size: 14px;
      line-height: 150%;
      text-align: left;
    }';

		// reset css
		$css .= 'table p {
      margin: 0px;
    }';

		$css .= 'table h1, table h2, table h3, table h4, table h5, table h6 {
      margin: 0px;
    }';

		$css .= '.yaymail-items-order-border tbody[data-shordcode="yaymail_items_border_content"],
              .yaymail-items-order-border tbody[data-shordcode="yaymail_items_border_content"]
              .yaymail_builder_table_items_border {
              border-color: inherit;
              flex-direction: inherit;
            }
            .yaymail-items-order-border tbody[data-shordcode="yaymail_items_border_content"]
              .yaymail_builder_table_items_border tbody {
                flex-direction: inherit;
            }';
		  // add css for table subcription, item download
		$css .= 'table.yaymail_builder_table_subcription,
            table.yaymail_builder_table_tracking_item,
            table.yaymail_builder_table_item_download,
            table.yaymail_builder_table_item_subscription,
            table.yaymail_builder_table_item_multi_vendor {
            color: inherit;
            border-width: 1px;
            border-style: solid;
            border-color: inherit;
          }';
		$css .= '.yaymail-items-order-border span[data-shordcode="yaymail_items_subscription_expired"],
            .yaymail-items-order-border span[data-shordcode="yaymail_items_subscription_suspended"],
            .yaymail-items-order-border span[data-shordcode="yaymail_items_subscription_cancelled"],
            .yaymail-items-order-border span[data-shordcode="yaymail_items_subscription_information"],
            .yaymail-items-tracking-item span[data-shordcode="yaymail_order_meta:_wc_shipment_tracking_items"],
            .yaymail-items-item-download span[data-shordcode="yaymail_items_downloadable_product"],
            .yaymail-items-subscript-border span[data-shordcode="yaymail_subscription_table"]
              {
              border-color: inherit;
            }';

		$css .= '#web-ad422370-f762-4a26-92de-c4cf3821b8eb-order-item .yaymail_items_border_default h2,
    #web-ad422370-f762-4a26-92de-c4cf3821b8eb-order-item .yaymail_items_border_default h3 {
      color: #96588a;
    }';
		$css .= '.yaymail_items_border_custom h2,.yaymail_items_border_custom h3 {
      color: inherit !important;
    }';
		$css .= '.nta-two-column-items table.web-main-row ,
             .nta-three-column-items table.web-main-row,
             .nta-four-column-items table.web-main-row
            {
              width: 100% !important;
            }';
		if ( 'yes' == $enableCustomCss ) {
			$css .= $custom_css;
		}

		if ( 'Left' == $orderImagePostions ) {
			$css .= '.yaymail-product-image{
        float: left;
      }';
		}

		if ( 'Top' == $orderImagePostions ) {
			$css .= '.yaymail-product-image{
        float: unset;
      }';
		}
		return $css;
	}
}
