<?php
/**
 * PDF invoice template body.
 *
 * This template can be overridden by copying it to youruploadsfolder/woocommerce-pdf-invoices/templates/invoice/simple/yourtemplatename/body.php.
 *
 * HOWEVER, on occasion WooCommerce PDF Invoices will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @author  Bas Elbers
 * @package WooCommerce_PDF_Invoices/Templates
 * @version 0.0.1
 */

$templater                  = WPI()->templater();
$invoice                    = $templater->invoice;
$order                      = $invoice->order;
$line_items                 = $order->get_items( 'line_item' );
$formatted_shipping_address = $order->get_formatted_shipping_address();
$formatted_billing_address  = $order->get_formatted_billing_address();
$columns                    = $invoice->get_columns();
$theme_color_background     = WPI()->get_option( 'template', 'color_theme_background' );
$theme_color_text           = WPI()->get_option( 'template', 'color_theme_text' );
$terms                      = WPI()->get_option( 'terms' );
$order_item_totals          = $invoice->get_order_item_totals();

// Header and footer margin and padding.
$this->mpdf->setAutoTopMargin    = 'stretch';
$this->mpdf->setAutoBottomMargin = 'stretch';
$this->mpdf->autoMarginPadding   = 25; // mm.
?>

<div class="title">
	<div>
		<h2><?php echo esc_html( WPI()->get_option( 'template', 'title' ) ); ?></h2>
	</div>
	<div class="watermark">
		<?php
		if ( WPI()->get_option( 'template', 'show_payment_status' ) && $order->is_paid() ) {
			printf( '<h2 class="green">%s</h2>', esc_html__( 'Paid', 'woocommerce-pdf-invoices' ) );
		}

		do_action( 'wpi_watermark_end', $order, $invoice );
		?>
	</div>
</div>
<table>
	<tr class="information">
		<td width="50%">
			<?php
			/**
			 * Invoice object.
			 *
			 * @var BEWPI_Invoice $invoice .
			 */
			foreach ( $invoice->get_invoice_info() as $info_id => $info ) {
				if ( empty( $info['value'] ) ) {
					continue;
				}

				printf( '<span class="%1$s">%2$s %3$s</span>', esc_attr( $info_id ), esc_html( $info['title'] ), esc_html( $info['value'] ) );
				echo '<br>';
			}
			?>
		</td>

		<td class="bill-to">
			<?php
			printf( '<strong>%s</strong><br />', esc_html__( 'Bill to:', 'woocommerce-pdf-invoices' ) );
			echo $formatted_billing_address;

			do_action( 'wpi_after_formatted_billing_address', $invoice );
			?>
		</td>

		<td class="ship-to">
			<?php
			if ( WPI()->get_option( 'template', 'show_ship_to' ) && ! WPI()->has_only_virtual_products( $order ) && ! empty( $formatted_shipping_address ) ) {
				printf( '<strong>%s</strong><br />', esc_html__( 'Ship to:', 'woocommerce-pdf-invoices' ) );
				echo $formatted_shipping_address;

				do_action( 'wpi_after_formatted_shipping_address', $invoice );
			}
			?>
		</td>
	</tr>
</table>
<table>
	<thead>
	<tr class="heading" style="background-color:<?php echo esc_attr( $theme_color_background ); ?>;">
		<?php
		foreach ( $columns as $key => $data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $k => $d ) {
					printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $k ), esc_attr( $theme_color_text ), $d );
				}

				continue;
			}

			printf( '<th class="%1$s" style="color:%2$s;">%3$s</th>', esc_attr( $key ), esc_attr( $theme_color_text ), $data );
		}
		?>
	</tr>
	</thead>
	<tbody>
	<?php
	foreach ( $invoice->get_columns_data() as $index => $row ) {
		echo '<tr class="item">';
		foreach ( $row as $key => $data ) {
			if ( is_array( $data ) ) {
				foreach ( $data as $k => $d ) {
					printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $d );
				}

				continue;
			}

			printf( '<td class="%1$s">%2$s</td>', esc_attr( $key ), $data );
		}

		echo '</tr>';
	}
	?>

	<tr class="spacer">
		<td></td>
	</tr>

	</tbody>
</table>

<table>
	<tbody>

	<?php
	$rowspan = count( $order_item_totals );
	foreach ( $order_item_totals as $key => $total ) {
		$class = str_replace( '_', '-', $key );
		?>

		<tr class="total">
			<?php
			// Only display row for first element and use rowspan.
			if ( $rowspan > 0 ) {
				?>
				<td width="50%" rowspan="<?php echo esc_attr( $rowspan ); ?>">
					<?php do_action( 'wpi_order_item_totals_left', $key, $invoice ); ?>
				</td>
				<?php
				$rowspan = 0;
			}
			?>

			<td width="25%" align="left" class="border <?php echo esc_attr( $class ); ?>">
				<?php echo $total['label']; ?>
			</td>

			<td width="25%" align="right" class="border <?php echo esc_attr( $class ); ?>">
				<?php echo str_replace( '&nbsp;', '', $total['value'] ); ?>
			</td>
		</tr>

	<?php } ?>
	</tbody>
</table>

<table class="notes">
	<tr>
		<td>
			<?php
			// Customer notes.
			if ( WPI()->get_option( 'template', 'show_customer_notes' ) ) {
				// Note added by customer.
				$customer_note = BEWPI_WC_Order_Compatibility::get_customer_note( $order );
				if ( $customer_note ) {
					printf( '<strong>' . __( 'Note from customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $customer_note ) );
				}

				// Notes added by administrator on 'Edit Order' page.
				foreach ( $order->get_customer_order_notes() as $custom_order_note ) {
					printf( '<strong>' . __( 'Note to customer: %s', 'woocommerce-pdf-invoices' ) . '</strong><br>', nl2br( $custom_order_note->comment_content ) );
				}
			}
			?>
		</td>
	</tr>

	<tr>
		<td>
			<?php
			// Zero Rated VAT message.
			if ( 'true' === WPI()->get_meta( $order, '_vat_number_is_valid' ) && count( $order->get_tax_totals() ) === 0 ) {
				echo esc_html__( 'Zero rated for VAT as customer has supplied EU VAT number', 'woocommerce-pdf-invoices' ) . '<br>';
			}
			?>
		</td>
	</tr>
</table>

<?php if ( $terms ) { ?>
	<!-- Using div to position absolute the block. -->
	<div class="terms">
		<table>
			<tr>
				<td style="border: 1px solid #000;">
					<?php echo nl2br( $terms ); ?>
				</td>
			</tr>
		</table>
	</div>
<?php } ?>
