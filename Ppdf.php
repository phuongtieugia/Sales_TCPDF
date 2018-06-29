<?php 
require_once('app/code/local/Mage/Adminhtml/Controller/Sales/TCPDF/tcpdf.php');
class NEWMYPDF extends TCPDF {

    public $isLastPage = false;
    public $shipmentlabel = "";
    //Page header
    public function Header() {
        
        // Set font -> $pdf->SetFont('362E78_0_0', 14, '', false);
        $this->SetFont('362E78_0_0', 'B', 18);
        
    }

    public function lastPage($resetmargins=false) {
        $this->setPage($this->getNumPages(), $resetmargins);
        $this->isLastPage = true;
    }
    public function setshipmentlabel($i='')
    {
        $this->shipmentlabel = $i;
        
    }
    public function setShippingaddress($i='')
    {
        $this->shippingaddress = $i;
        
    }

	public function setCountry($country)
	{

	$this->country = $country;
	}

    // Page footer
    public function Footer() {
        
        $html = '
        <table width="100%" style="vertical-align:top;" border="0">
            <tbody>
                <tr>
                    <td align="center" width="100%">
                        <p>Fashionology.nl - Kranebittenbaan 27 - 3045 AW Rotterdam - Netherlands - COC 24475476</p>
                        <p>VAT: NL154100122B01 - IBAN: NL154100122B01 - SWIFT CODE: RABONL2U</p>
                        <p>Bank: Rabobank - Blaak 333 - 3011 GB Rotterdam</p>
                    </td>
                    
                </tr>
            </tbody>
        </table>
        ';
        if($this->isLastPage) {
            $this->SetY(-50);
            $this->writeHTMLCell(0, 0, '', '', $html, 0, 0, false, "L", true);
        }


        
    }
}
class Fooman_EmailAttachments_Helper_Ppdf extends Mage_Core_Helper_Abstract
{
	public function CreatePDFbyOrderID($orderid='')
    {

    	$order = Mage::getModel('sales/order')->load($orderid);
        
        // create new PDF document
        $pdf = new NEWMYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' '.$order->getIncrementId(), PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        
        $pdf->SetFont('362E78_0_0', '', 7);

        // add a page
        $pdf->AddPage();
        $html = '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="center" width="100%">
                        <img width="100" src="media/sales/store/logo/default/fashion_transparent_logo2.png">
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        
        

        /*var_dump($order->getBillingAddress()->getData());die;*/

        $baddress = $order->getBillingAddress()->getData();

        $billing_address = $this->getPrintedBillingAddress($baddress);
        $billingAddress = $this->_formatAddress($billing_address);
        $_baddress = "";
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_baddress .= $_value;
                }
                foreach ($text as $part) {
                    $_baddress .= $part;
                }
            }
        }

        $saddress = $order->getShippingAddress()->getData();
        $shipping_address = $this->getPrintedShippingAddress($saddress);
        $shippingAddress = $this->_formatAddress($shipping_address);
        $_saddress = "";
        foreach ($shippingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_saddress .= $_value;
                }
                foreach ($text as $part) {
                    $_saddress .= $part;
                }
            }
        }

        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        $date = Mage::helper('core')->formatDate($order->getCreatedAtStoreDate(), 'medium', false);
        $email = trim($order->getCustomerEmail());

        $html .= '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="left" width="40%">
                        <p><b>'.Mage::helper("sales")->__("Date:").'</b> '.$date.'
                        <br/><b>'.Mage::helper("sales")->__("Order:").'</b> '.$order->getIncrementId().'
                        <br><br/><b>'.Mage::helper("sales")->__("Payment:").'</b><br>'.$payment.'</p>
                    </td>
                    <td align="left" width="20%"></td>
                    <td align="left" width="40%">
                    	<p><u>'.Mage::helper("sales")->__("Delivery Address:").'</u>
                        <br><address>'.$_saddress.'</address></p>
                        <p><u>'.Mage::helper("sales")->__("Billing Address:").'</u>
                        <br><address>'.$_baddress.'</address><a href="mailto:'.$email.'">'.$email.'</a></p>
                        <br>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        $html .= '
        <table width="100%" style="vertical-align:top; text-align:left" border="0" cellspacing="0">
            <thead>
                <tr style="font-weight: bold;">
                    <th width="30%"><b>'.Mage::helper('sales')->__('Items').'</b></th>
                    <th width="40%"><b>'.Mage::helper('sales')->__('Product').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Qty').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Price').'</b></th>
                    <th width="10%" style="text-align: right;"><b>'.Mage::helper('sales')->__('Total').'</b></th>
                </tr>
            </thead>
            <tbody>';

        $total_qty = 0;
        foreach ($order->getAllItems() as $item) {
                    
                    $html .= '<tr>
                        <td width="30%">'.$item->getSku().'</td>
                        <td width="40%">'.$item->getName().'</td>
                        <td width="10%">'.number_format($item->getQty()).'</td>
                        <td width="10%">'.Mage::helper('core')->currency($item->getPrice()).'</td>
                        <td width="10%" style="text-align: right;">'.Mage::helper('core')->currency($item->getRowTotal()).'</td>
                    </tr>';
                    $total_qty += $item->getQty();
        }

        $html .= '<tr><td colspan="4"></td><td style="text-align: right;"></td></tr>';
        $html .= '
        <tr>
            <td colspan="4">'.Mage::helper('sales')->__('Shipping').'</td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($order->getShippingAmount()).'</td>
        </tr>

        ';
        $html .= '
        <tr>
        <td>'.Mage::helper('sales')->__('Total').'</td>
        <td colspan="1"></td>
        <td>'.number_format($total_qty).'</td>
        <td></td>
        <td style="text-align: right;">'.Mage::helper('core')->currency($order->getSubtotal()).'</td>
      </tr>
        ';
        $html .= '
        <tr>
            <td colspan="3">'.Mage::helper('sales')->__('Btw').'</td>
            <td></td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($order->getTaxAmount()).'</td>
        </tr>
        ';
        $html .= '<tr>
            <td colspan="3"><b></b></td>
            <td></td>
            <td style="text-align: right;"><b>'.Mage::helper('core')->currency($order->getGrandTotal()).'</b></td>
          </tr>';

        

        $html .= '</tbody></table><br><br>';

        


        $pdf->writeHTML($html, true, false, true, false, '');



        $pdf->SetFont('362E78_0_0', '', 7);

        $pdf->setshipmentlabel(Mage::helper('sales')->__('Order nr: ').' '.$order->getIncrementId());
        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        //$pdf->Output('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');
        $fileatt = $pdf->Output('order'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'S');
        
        return $fileatt;
    }
	
	public function CreatePDFbyInvoice($invoiceId='')
    {
        
        // create new PDF document
        $pdf = new NEWMYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' '.$invoiceId, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        
        $pdf->SetFont('362E78_0_0', '', 7);

        // add a page
        $pdf->AddPage();
        $html = '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="center" width="100%">
                        <img width="100" src="media/sales/store/logo/default/fashion_transparent_logo2.png">
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        
        $invoice = Mage::getModel('sales/order_invoice')->load($invoiceId);

        /*var_dump($invoice->getBillingAddress()->getData());die;*/

        $baddress = $invoice->getBillingAddress()->getData();

        $billing_address = $this->getPrintedBillingAddress($baddress);
        $billingAddress = $this->_formatAddress($billing_address);
        $_baddress = "";
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_baddress .= $_value;
                }
                foreach ($text as $part) {
                    $_baddress .= $part;
                }
            }
        }

        $saddress = $invoice->getShippingAddress()->getData();
        $shipping_address = $this->getPrintedShippingAddress($saddress);
        $shippingAddress = $this->_formatAddress($shipping_address);
        $_saddress = "";
        foreach ($shippingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_saddress .= $_value;
                }
                foreach ($text as $part) {
                    $_saddress .= $part;
                }
            }
        }

        $payment = $invoice->getOrder()->getPayment()->getMethodInstance()->getTitle();

        $date = Mage::helper('core')->formatDate($invoice->getOrder()->getCreatedAtStoreDate(), 'medium', false);
        $email = trim($invoice->getOrder()->getCustomerEmail());

        $html .= '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="left" width="40%">
                        <p><b>'.Mage::helper("sales")->__("Date:").'</b> '.$date.'
                        <br/><b>'.Mage::helper("sales")->__("Invoice:").'</b> '.$invoice->getIncrementId().'
                        <br><br/><b>'.Mage::helper("sales")->__("Payment:").'</b><br>'.$payment.'</p>
                    </td>
                    <td align="left" width="20%"></td>
                    <td align="left" width="40%">
                    	<p><u>'.Mage::helper("sales")->__("Delivery Address:").'</u>
                        <br><address>'.$_saddress.'</address></p>
                        <p><u>'.Mage::helper("sales")->__("Billing Address:").'</u>
                        <br><address>'.$_baddress.'</address><a href="mailto:'.$email.'">'.$email.'</a></p>
                        <br>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        $html .= '
        <table width="100%" style="vertical-align:top; text-align:left" border="0" cellspacing="0">
            <thead>
                <tr style="font-weight: bold;">
                    <th width="30%"><b>'.Mage::helper('sales')->__('Items').'</b></th>
                    <th width="40%"><b>'.Mage::helper('sales')->__('Product').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Qty').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Price').'</b></th>
                    <th width="10%" style="text-align: right;"><b>'.Mage::helper('sales')->__('Total').'</b></th>
                </tr>
            </thead>
            <tbody>';

        $total_qty = 0;
        foreach ($invoice->getAllItems() as $item) {
                    
                    $html .= '<tr>
                        <td width="30%">'.$item->getSku().'</td>
                        <td width="40%">'.$item->getName().'</td>
                        <td width="10%">'.number_format($item->getQty()).'</td>
                        <td width="10%">'.Mage::helper('core')->currency($item->getPrice()).'</td>
                        <td width="10%" style="text-align: right;">'.Mage::helper('core')->currency($item->getRowTotal()).'</td>
                    </tr>';
                    $total_qty += $item->getQty();
        }

        $html .= '<tr><td colspan="4"></td><td style="text-align: right;"></td></tr>';
        $html .= '
        <tr>
            <td colspan="4">'.Mage::helper('sales')->__('Shipping').'</td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($invoice->getShippingAmount()).'</td>
        </tr>

        ';
        $html .= '
        <tr>
        <td>'.Mage::helper('sales')->__('Total').'</td>
        <td colspan="1"></td>
        <td>'.number_format($total_qty).'</td>
        <td></td>
        <td style="text-align: right;">'.Mage::helper('core')->currency($invoice->getSubtotal()).'</td>
      </tr>
        ';
        $html .= '
        <tr>
            <td colspan="3">'.Mage::helper('sales')->__('Btw').'</td>
            <td></td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($invoice->getTaxAmount()).'</td>
        </tr>
        ';
        $html .= '<tr>
            <td colspan="3"><b></b></td>
            <td></td>
            <td style="text-align: right;"><b>'.Mage::helper('core')->currency($invoice->getGrandTotal()).'</b></td>
          </tr>';

        

        $html .= '</tbody></table><br><br>';

        


        $pdf->writeHTML($html, true, false, true, false, '');



        $pdf->SetFont('362E78_0_0', '', 7);

        $pdf->setshipmentlabel(Mage::helper('sales')->__('Order nr: ').' '.$invoice->getOrder()->getIncrementId());
        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        /*$pdf->Output('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');*/
        
        $fileatt = $pdf->Output('invoice'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'S');
        
        return $fileatt;
    }
    public function CreatePDFbyShipment($shipmentId='')
    {
        
        // create new PDF document
        $pdf = new NEWMYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        /*$pdf->SetAuthor('Peter');
        $pdf->SetTitle('invoice '.$shipmentId);
        $pdf->SetSubject('invoice '.$shipmentId);
        $pdf->SetKeywords('invoice, PDF, Print',$shipmentId);*/

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' '.$shipmentId, PDF_HEADER_STRING);
        $pdf->setPrintHeader(false);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        
        $pdf->SetFont('362e78_0_0', '', 7);

        // add a page
        $pdf->AddPage();
        $html = '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="center" width="100%">
                        <img width="182" src="media/sales/store/logo/default/fashion_transparent_logo2.png">
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        

        $shipment = Mage::getModel('sales/order_shipment')->load($shipmentId);
        $pdf->setCountry($shipment->getBillingAddress()->getCountryId());
        /*var_dump($shipment->getBillingAddress()->getData());die;*/

        $baddress = $shipment->getBillingAddress()->getData();

        $billing_address = $this->getPrintedBillingAddress($baddress);
        $billingAddress = $this->_formatAddress($billing_address);
        $_baddress = "";
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_baddress .= $_value;
                }
                foreach ($text as $part) {
                    $_baddress .= $part;
                }
            }
        }

        $saddress = $shipment->getShippingAddress()->getData();
        $shipping_address = $this->getPrintedBillingAddress($saddress);
        $shippingAddress = $this->_formatAddress($shipping_address);
        $_saddress = "";
        foreach ($shippingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_saddress .= $_value;
                }
                foreach ($text as $part) {
                    $_saddress .= $part;
                }
            }
        }

        $payment = $shipment->getOrder()->getPayment()->getMethodInstance()->getTitle();
        $shipping = $shipment->getOrder()->getShippingDescription();
        

        $date = Mage::helper('core')->formatDate($shipment->getOrder()->getCreatedAtStoreDate(), 'medium', false);
        $email = trim($shipment->getOrder()->getCustomerEmail());
        $tax = $shipment->getOrder()->getCustomerTaxvat();
        
        $customer_groupt = $shipment->getOrder()->getCustomerGroupId();

        $customerId = $shipment->getOrder()->getCustomerId();
        $customer = Mage::getModel('customer/customer')->load($customerId);
        if($customer){
            $customer_groupt =  $customer->getGroupId();
        }
        

        

        if($customer_groupt == 5){
	        $html .= '
	        <table width="100%" style="vertical-align:top" border="0">
	            <tbody>
	                <tr>
	                    <td align="left" width="100%">
	                        <p>'.Mage::helper("sales")->__("Order number:").' '.$shipment->getOrder()->getIncrementId().'<br/> 
	                        '.Mage::helper("sales")->__("Date:").' '.$date.' </p>
	                        <address>'.$_baddress.'</address>
	                    </td>
	                </tr>
	            </tbody>
	        </table>
	        <br>
	        <br>
	        ';
        }
        else{
	        $html .= '
	        <table width="100%" style="vertical-align:top" border="0">
	            <tbody>
	                <tr>
	                    <td align="left" width="100%">
	                        <p>'.Mage::helper("sales")->__("Order number:").' '.$shipment->getOrder()->getIncrementId().'<br/> 
	                        '.Mage::helper("sales")->__("Date:").' '.$date.' </p>
	                        <address>'.$_baddress.'</address>
	                        <p>'.Mage::helper("sales")->__("Payment method:").' '.$payment.'<br/>
	                        '.Mage::helper("sales")->__("Shipping method:").' '.$shipping.'</p>
	                    </td>
	                </tr>
	            </tbody>
	        </table>
	        <br>
	        <br>
	        ';
        }

        $html .= '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="left" width="100%">
                        <h4><p><b>'.Mage::helper('sales')->__('Order').'</b></p></h4>
                    </td>
                </tr>
            </tbody>
        </table>
        ';
        
        if($customer_groupt == 5){
            $html .= '
            <table width="100%" style="vertical-align:top;text-align:left" border="0" cellspacing="5">
                <thead>
                    <tr style="font-weight:bold">
                        
                        <th width="30%">'.Mage::helper('sales')->__('SKU').'</th>
                        <th width="50%">'.Mage::helper('sales')->__('Product').'</th>
                        <th width="20%">'.Mage::helper('sales')->__('Qty').'</th>
                    </tr>
                </thead>
                <tbody>';
        }
        else{
            $html .= '
            <table width="100%" style="vertical-align:top;text-align:left" border="0" cellspacing="5">
                <thead>
                    <tr style="font-weight:bold">
                        <th width="15%">'.Mage::helper('sales')->__('Refund').'</th>
                        <th width="15%">'.Mage::helper('sales')->__('Exchange').'</th>
                        <th width="20%">'.Mage::helper('sales')->__('SKU').'</th>
                        <th width="40%">'.Mage::helper('sales')->__('Product').'</th>
                        <th width="10%">'.Mage::helper('sales')->__('Qty').'</th>
                    </tr>
                </thead>
                <tbody>';
        }

        

        $total_qty = 0;
        $i = 0;
        $options = "";
        $op = "";
        foreach ($shipment->getAllItems() as $_item){ if ($_item->getOrderItem()->getIsVirtual() || $_item->getOrderItem()->getParentItem()){ continue; };
                    
                $options = $_item->getOrderItem()->getItemOptions();
                $i = $_item->getOrderItem()->getProductOptions();
                $op = "";
                if($i['attributes_info']){
                    foreach ($i['attributes_info'] as $option){
                        $op .= $option['label']. ": ";
                        $op .= $option['value'];
                    }
                    if($op != ""){
                        $op = ' <span>('.$op.')</span>';
                    }
                }

                

                    if($customer_groupt == 5){
                        $html .= '<tr>
                            <td width="30%">'.$_item->getSku().'</td>
                            <td width="50%">'.$_item->getName().$op.' </td>
                            <td width="10%">'.number_format($_item->getQty()).'</td>
                        </tr>';

                    }
                    else{
                        $html .= '<tr>
                            <td width="15%"><input type="checkbox" name="box" value="1" readonly="true" /></td>
                            <td width="15%"><input type="checkbox" name="box" value="1" readonly="true" /></td>
                            <td width="20%">'.$_item->getSku().'</td>
                            <td width="40%">'.$_item->getName().$op.' </td>
                            <td width="10%">'.number_format($_item->getQty()).'</td>
                        </tr>';

                    }
                    
                    $total_qty += $_item->getQty();
        }

        $html .= '</tbody></table><br><br>';

        // get Giftwrap

        $o = $shipment->getOrder();
        $allow_gift_messages =  $o->getAllowGiftMessages();
        $allow_gift_messages_for_order = $o->getAllowGiftMessagesForOrder();
        $gift_messages = $o->getGiftMessages();
        $g = "";
        if($allow_gift_messages){
            $g .= Mage::helper('sales')->__("Gift wrap:")." ".Mage::helper('sales')->__("Yes").'<br>';
        }
        if($allow_gift_messages_for_order){
            $g .= Mage::helper('sales')->__("Seperate wrapping:")." ".Mage::helper('sales')->__("Yes").'<br>';
        }
        if($gift_messages != "" && $gift_messages != "0"){
            $g .= Mage::helper('sales')->__("Gift message:").'<br>';
            $g .= strip_tags($gift_messages).'<br>';
        }

        if($allow_gift_messages){
			
			if($customer_groupt == 5){

	            $html .= '
	            <table width="100%" style="vertical-align:top" border="0">
	                <tbody>
	                    <tr>
	                        <td align="left" width="100%">
	                            <p><b>'.Mage::helper('sales')->__('Giftwrap details:').'</b></p>
	                            <p>'.$g.'</p>
	                            <br><br><br><br><br><br><br><br>
	                        </td>
	                        
	                    </tr>
	                    <tr>
	                        <td align="center">
	                            <p tyle="text-align:center;">'.Mage::helper('sales')->__('Question? Visit our FAQ or get in contact with us at info@fashionology.nl or Whatsapp').'</p>
	                            <p>&nbsp;</p>
	                            <p>&nbsp;</p>
	                        </td>
	                    </tr>  
	                    
	                </tbody>
	            </table>
	            <br>
	            <br>
	            ';
        	
        	} else {
				
				$html .= '
	            <table width="100%" style="vertical-align:top" border="0">
	                <tbody>
	                    <tr>
	                        <td align="left" width="100%">
	                            <p><b>'.Mage::helper('sales')->__('Giftwrap details:').'</b></p>
	                            <p>'.$g.'</p>
	                            <p><b>'.Mage::helper('sales')->__('Exchange for:').'</b></p>
	                            <br><br><br><br><br><br><br><br>
	                        </td>
	                        
	                    </tr>
	                    <tr>
	                        <td align="center">
	                            <p tyle="text-align:center;">'.Mage::helper('sales')->__('Question? Visit our FAQ or get in contact with us at info@fashionology.nl or Whatsapp').'</p>
	                            <p>&nbsp;</p>
	                            <p>&nbsp;</p>
	                        </td>
	                    </tr>  
	                    
	                </tbody>
	            </table>
	            <br>
	            <br>
	            ';
			}
        
        } else {

			if($customer_groupt == 5){

	            $html .= '
	                <table width="100%" style="vertical-align:top" border="0">
	                    <tbody>
	                        <tr>
	                            <td align="left" width="100%">
	                                <br><br><br><br><br><br><br><br>
	                            </td>
	                        </tr>
	                        <tr>
	                            <td align="center">
	                                <p tyle="text-align:center;">'.Mage::helper('sales')->__('Question? Visit our FAQ or get in contact with us at info@fashionology.nl or Whatsapp').'</p>
	                                <p>&nbsp;</p>
	                                <p>&nbsp;</p>
	                            </td>
	                        </tr>  
	                        
	                    </tbody>
	                </table>
	                <br>
	                <br>
	            ';
			
			} else {
				
				$html .= '
	                <table width="100%" style="vertical-align:top" border="0">
	                    <tbody>
	                        <tr>
	                            <td align="left" width="100%">
	                                <p><b>'.Mage::helper('sales')->__('Exchange for:').'</b></p>
	                                <br><br><br><br><br><br><br><br>
	                            </td>
	                        </tr>
	                        <tr>
	                            <td align="center">
	                                <p tyle="text-align:center;">'.Mage::helper('sales')->__('Question? Visit our FAQ or get in contact with us at info@fashionology.nl or Whatsapp').'</p>
	                                <p>&nbsp;</p>
	                                <p>&nbsp;</p>
	                            </td>
	                        </tr>  
	                        
	                    </tbody>
	                </table>
	                <br>
	                <br>
	            ';

			}

        }
        

        // End get Giftwrap


        //$pdf->writeHTML($html, true, false, true, false, '');
		$pdf->writeHTML($html);


        $pdf->SetFont('aealarabiya', '', 9);
        // reset pointer to the last page
        $saddress = $shipment->getOrder()->getShippingAddress()->getData();
        $shipping_address = $this->getPrintedShippingAddress($saddress);
        $shippingAddress = $this->_formatAddress($shipping_address);
        $_saddress = "";
        foreach ($shippingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_saddress .= $_value;
                }
                foreach ($text as $part) {
                    $_saddress .= $part;
                }
            }
        }
        
        $pdf->setshipmentlabel(Mage::helper('sales')->__('Order nr: ').' '.$shipment->getOrder()->getIncrementId());
        $pdf->setShippingaddress($_saddress);
        $pdf->lastPage();

        // ---------------------------------------------------------

		$fileatt = $pdf->Output('shipment'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'S');
        
        return $fileatt;
    }
    public function CreatePDFbyCreditmemo($c='')
    {

        $creditmemo = Mage::getModel('sales/order_creditmemo')->load($c);
        
        
        $increment_id = $creditmemo->getIncrementId();

        $orderid = $creditmemo->getOrderId();

        
        $order = Mage::getModel('sales/order')->load($orderid);
        
        // create new PDF document
        $pdf = new NEWMYPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // set document information
        $pdf->SetCreator(PDF_CREATOR);

        // set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE.' '.$increment_id, PDF_HEADER_STRING);

        // set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, 40, PDF_MARGIN_RIGHT);
        $pdf->SetMargins(PDF_MARGIN_LEFT, 10, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

        // set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);

        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);

        // set some language-dependent strings (optional)
        if (@file_exists(dirname(__FILE__).'/lang/eng.php')) {
            require_once(dirname(__FILE__).'/lang/eng.php');
            $pdf->setLanguageArray($l);
        }

        // ---------------------------------------------------------

        // set font
        
        $pdf->SetFont('362E78_0_0', '', 7);

        // add a page
        $pdf->AddPage();
        $html = '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="center" width="100%">
                        <img width="100" src="media/sales/store/logo/default/fashion_transparent_logo2.png">
                    </td>
                    
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        
        

        $baddress = $creditmemo->getBillingAddress()->getData();

        $billing_address = $this->getPrintedBillingAddress($baddress);
        $billingAddress = $this->_formatAddress($billing_address);
        $_baddress = "";
        foreach ($billingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_baddress .= $_value;
                }
                foreach ($text as $part) {
                    $_baddress .= $part;
                }
            }
        }

        $saddress = $creditmemo->getShippingAddress()->getData();
        $shipping_address = $this->getPrintedShippingAddress($saddress);
        $shippingAddress = $this->_formatAddress($shipping_address);
        $_saddress = "";
        foreach ($shippingAddress as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 45, true, true) as $_value) {
                    $_saddress .= $_value;
                }
                foreach ($text as $part) {
                    $_saddress .= $part;
                }
            }
        }

        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        $date = Mage::helper('core')->formatDate($creditmemo->getCreatedAt(), 'medium', false);
        $email = trim($creditmemo->getCustomerEmail());

        $html .= '
        <table width="100%" style="vertical-align:top" border="0">
            <tbody>
                <tr>
                    <td align="left" width="40%">
                        <p><b>'.Mage::helper("sales")->__("Date:").'</b> '.$date.'
                        <br/><b>'.Mage::helper("sales")->__("Creditmemo:").'</b> '.$increment_id.'
                        <br><br/><b>'.Mage::helper("sales")->__("Payment:").'</b><br>'.$payment.'</p>
                    </td>
                    <td align="left" width="20%"></td>
                    <td align="left" width="40%">
                        <p><u>'.Mage::helper("sales")->__("Delivery Address:").'</u>
                        <br><address>'.$_saddress.'</address></p>
                        <p><u>'.Mage::helper("sales")->__("Billing Address:").'</u>
                        <br><address>'.$_baddress.'</address><a href="mailto:'.$email.'">'.$email.'</a></p>
                        <br>
                    </td>
                </tr>
            </tbody>
        </table>
        <br>
        <br>
        ';
        $html .= '
        <table width="100%" style="vertical-align:top; text-align:left" border="0" cellspacing="0">
            <thead>
                <tr style="font-weight: bold;">
                    <th width="30%"><b>'.Mage::helper('sales')->__('Items').'</b></th>
                    <th width="40%"><b>'.Mage::helper('sales')->__('Product').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Qty').'</b></th>
                    <th width="10%"><b>'.Mage::helper('sales')->__('Price').'</b></th>
                    <th width="10%" style="text-align: right;"><b>'.Mage::helper('sales')->__('Total').'</b></th>
                </tr>
            </thead>
            <tbody>';

        $total_qty = 0;
        foreach ($creditmemo->getAllItems() as $item) {
            
                    
                    $html .= '<tr>
                        <td width="30%">'.$item->getSku().'</td>
                        <td width="40%">'.$item->getName().'</td>
                        <td width="10%">'.number_format($item->getQty()).'</td>
                        <td width="10%">'.Mage::helper('core')->currency($item->getPrice()).'</td>
                        <td width="10%" style="text-align: right;">'.Mage::helper('core')->currency($item->getRowTotal()).'</td>
                    </tr>';
                    $total_qty += $item->getQty();
        }

        $html .= '<tr><td colspan="4"></td><td style="text-align: right;"></td></tr>';       
        $html .= '
        <tr>
            <td colspan="4">'.Mage::helper('sales')->__('Shipping').'</td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($creditmemo->getShippingAmount()).'</td>
        </tr>

        ';
        $html .= '
        <tr>
        <td>'.Mage::helper('sales')->__('Total').'</td>
        <td colspan="1"></td>
        <td>'.number_format($total_qty).'</td>
        <td></td>
        <td style="text-align: right;">'.Mage::helper('core')->currency($creditmemo->getSubtotal()).'</td>
      </tr>
        ';
        $html .= '
        <tr>
            <td colspan="3">'.Mage::helper('sales')->__('Btw').'</td>
            <td></td>
            <td style="text-align: right;">'.Mage::helper('core')->currency($creditmemo->getTaxAmount()).'</td>
        </tr>
        ';
        $html .= '<tr>
            <td colspan="3"><b></b></td>
            <td></td>
            <td style="text-align: right;"><b>'.Mage::helper('core')->currency($creditmemo->getGrandTotal()).'</b></td>
          </tr>';

        

        $html .= '</tbody></table><br><br>';

        


        $pdf->writeHTML($html, true, false, true, false, '');



        $pdf->SetFont('362E78_0_0', '', 7);

        $pdf->setshipmentlabel(Mage::helper('sales')->__('Order nr: ').' '.$order->getIncrementId());
        // reset pointer to the last page
        $pdf->lastPage();

        // ---------------------------------------------------------

        //Close and output PDF document
        /*$pdf->Output('creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'I');*/
        $fileatt = $pdf->Output('creditmemo'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', 'S');
        
        return $fileatt;
    }
    protected function _calcAddressHeight($address)
    {
        $y = 0;
        foreach ($address as $value){
            if ($value !== '') {
                $text = array();
                foreach (Mage::helper('core/string')->str_split($value, 55, true, true) as $_value) {
                    $text[] = $_value;
                }
                foreach ($text as $part) {
                    $y += 15;
                }
            }
        }
        return $y;
    }
    protected function _formatAddress($address)
    {
        $return = array();
        $i = 1;
        foreach (explode('|', $address) as $str) {
            foreach (Mage::helper('core/string')->str_split($str, 45, true, true) as $part) {
                if (empty($part)) {
                    continue;
                }
                if($i == 1){
                    $return[] = "<b>".$part."</b><br>";
                }
                else{
                    $return[] = $part."<br>";   
                }
                
                $i++;
            }
        }
        return $return;
    }
    public function getPrintedBillingAddress($customerquoteinfo){
        
        if (!empty($customerquoteinfo['company'])) {
            $address .= $customerquoteinfo['company'] . '|';
        }
        if (!empty($customerquoteinfo['firstname'])) {
        	$address .= $customerquoteinfo['firstname'] . ' ';
		}
        if (!empty($customerquoteinfo['middlename'])) {
            $address .= $customerquoteinfo['middlename'] . ' ';
        }
        if (!empty($customerquoteinfo['lastname'])) {
            $address .= $customerquoteinfo['lastname']. '|';
        }
        //$name .= '\n';
        if (isset($customerquoteinfo['street'])) {
            $address .=  $customerquoteinfo['street'].'|';
        }
        if (!empty($customerquoteinfo['postcode'])) {
            $address .= $customerquoteinfo['postcode'] . ' ';
        }
        if (!empty($customerquoteinfo['city'])) {
            $address .= $customerquoteinfo['city'] . '|';
        }        
        if (!empty($customerquoteinfo['region'])) {
        $address .= $customerquoteinfo['region'] . '|, ';
        }
        if (!empty($customerquoteinfo['country_id'])) {
            $address .= Mage::getmodel( 'directory/country' )->load( $customerquoteinfo['country_id'] )->getName(  ).'|';
        }
        if (!empty($customerquoteinfo['telephone'])) {
            $address .= "T: ".$customerquoteinfo['telephone'] . '|';
        }
        if (!empty($customerquoteinfo['vat_id'])) {
            $address .= "Vat Id: ".$customerquoteinfo['vat_id'] . '|';
        }
        return $address;
    
    }
    public function getPrintedShippingAddress($customerquoteinfo){
        
        $name = '';
        $address  = '';
        if (!empty($customerquoteinfo['company'])) {
            $address .= $customerquoteinfo['company'] . '|';
        }
        if (!empty($customerquoteinfo['firstname'])) {
        	$address .= $customerquoteinfo['firstname'] . ' ';
		}
        if (!empty($customerquoteinfo['middlename'])) {
            $address .= $customerquoteinfo['middlename'] . ' ';
        }
        if (!empty($customerquoteinfo['lastname'])) {
            $address .= $customerquoteinfo['lastname']. '|';
        }
        //$name .= '\n';
        if (isset($customerquoteinfo['street'])) {
            $address .=  $customerquoteinfo['street'].'|';
        }
        if (!empty($customerquoteinfo['postcode'])) {
            $address .= $customerquoteinfo['postcode'] . ' ';
        }
        if (!empty($customerquoteinfo['city'])) {
            $address .= $customerquoteinfo['city'] . '|';
        }        
        if (!empty($customerquoteinfo['region'])) {
        $address .= $customerquoteinfo['region'] . '|, ';
        }
        if (!empty($customerquoteinfo['country_id'])) {
            $address .= Mage::getmodel( 'directory/country' )->load( $customerquoteinfo['country_id'] )->getName(  ).'|';
        }
        if (!empty($customerquoteinfo['telephone'])) {
            $address .= "T: ".$customerquoteinfo['telephone'] . '|';
        }
        if (!empty($customerquoteinfo['vat_id'])) {
            $address .= "Vat Id: ".$customerquoteinfo['vat_id'] . '|';
        }
        return $address;
    
    }
}
