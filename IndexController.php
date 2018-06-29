<?php
class Ptg_Pquotation_IndexController extends Mage_Core_Controller_Front_Action{
    public function IndexAction() {
      
	  $this->loadLayout();   
	  $this->getLayout()->getBlock("head")->setTitle($this->__("Titlename"));
	        $breadcrumbs = $this->getLayout()->getBlock("breadcrumbs");
      $breadcrumbs->addCrumb("home", array(
                "label" => $this->__("Home Page"),
                "title" => $this->__("Home Page"),
                "link"  => Mage::getBaseUrl()
		   ));

      $breadcrumbs->addCrumb("titlename", array(
                "label" => $this->__("Titlename"),
                "title" => $this->__("Titlename")
		   ));

      $this->renderLayout(); 
	  
    }
    public function sendquotationAction()
    {
        $post   = $this->getRequest()->getPost();
        
        $o = $this->getRequest()->getPost('options');
        $optionstring = "<dl>";
        foreach ($o as $key => $v) {
            $option_id = $key;
            $option_type_id =  $v;
            $data = $this->getcatalog_product_option($option_id,$option_type_id);
            $optionstring .= "<dt>".$data['label']."</dt>";
            $optionstring .= "<dd>".$data['value']."</dd>";
        }
        $optionstring .= "</dl>";
        $r['op'] = $optionstring;
        $p = Mage::getModel("catalog/product")->load($post['product']);

        $store = Mage::app()->getStore()->getStoreId();
        $emailTemplateID = 11;
        if($store == 2){
            $emailTemplateID = 13;
        }
        else if($store == 4){
            $emailTemplateID = 12;
        }
        else{
            $emailTemplateID = 11;
        }



        $storeId = Mage::app()->getStore()->getStoreId();
        if($post){
            $name = "Admin";
            $emailTemplate  = Mage::getModel('core/email_template')->load($emailTemplateID);
            $data = array(
            'product_name'      => $p->getname(),
            'optionstring'      => $optionstring,
            'first_name'         => $this->getRequest()->getPost('qfirstname'),
            'last_name'         => $this->getRequest()->getPost('qlastname'),
            'email'         => $this->getRequest()->getPost('qemail'),
            'country'         => $this->getRequest()->getPost('qcountry'),
            'city'         => $this->getRequest()->getPost('qcity'),
            'car_make'         => $this->getRequest()->getPost('qcarmake'),
            'car_model'         => $this->getRequest()->getPost('qcarmodel'),
            'tires'         => $this->getRequest()->getPost('tires'),
            'preffered_brand'         => $this->getRequest()->getPost('preffered_brand'),

            );           
            $vars = $data;

            $email = $this->getRequest()->getPost('qemail');
            $add_cc=array("info@baanvelgen.com");
            $emailTemplate->getMail()->addCc($add_cc);

            $emailTemplate->getProcessedTemplate($vars);
            $emailTemplate->setSenderEmail(Mage::getStoreConfig('trans_email/ident_general/email', $storeId));
            $emailTemplate->setSenderName(Mage::getStoreConfig('trans_email/ident_general/name', $storeId));
            $emailTemplate->send($email,$name, $vars);
            $r["rs"] = 1;
            $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($r));
        }
    }
    public function getcatalog_product_option($option_id  ='',$option_type_id  ='')
    {
        $data = null;
        $resource = Mage::getSingleton('core/resource');
        $readConnection = $resource->getConnection('core_read');
        $writeConnection = $resource->getConnection('core_write');
        // catalog_product_option_title
        $query = 'SELECT title FROM catalog_product_option_title WHERE option_id = ' . (int)$option_id . ' LIMIT 1';
        $query1 = 'SELECT title FROM catalog_product_option_type_title WHERE option_type_id = ' . (int)$option_type_id . ' LIMIT 1';
        $data['label'] = $catalog_product_option_title = $readConnection->fetchOne($query);

        $data['value'] = $catalog_product_option_type_title = $readConnection->fetchOne($query1);
        if($data['value'] == ""){
            $data['value'] = $option_type_id;
        }
        return $data;
    }
    public function TestAction()
    {
        echo "string";
        $this->sendMail("ERROR","AA");
    }
    public function sendMail($errorCod = "", $errorMsg = "")
    {

        $mail = new Zend_Mail('utf-8');

        
        $mailBody   = "<b>Error Code: </b>" . $errorCod . "<br />";
        $mailBody .= "<b>Error Massage: </b>" . $errorMsg . "<br />";
        $mail->setBodyHtml($mailBody)
            ->setSubject('Lorem Ipsum')
            ->addTo("phuongtieugia1@gmail.com")
            ->setFrom(Mage::getStoreConfig('trans_email/ident_general/email'), "FromName");

        //file content is attached
        
        $pdf = Mage::helper("emailattachments/ppdf")->CreatePDFbyInvoice('118');
        $pdf = Mage::helper("emailattachments/ppdf")->CreatePDFbyOrderID('252');
        //$pdf = Mage::helper("emailattachments/ppdf")->CreatePDFbyShipment('118');
        $attachment = file_get_contents($pdf);
        $mail->createAttachment(
            $pdf,
            Zend_Mime::TYPE_OCTETSTREAM,
            Zend_Mime::DISPOSITION_ATTACHMENT,
            Zend_Mime::ENCODING_BASE64,
            'Invoice.pdf'
        );
       

        try {
            $mail->send();
        } catch (Exception $e) {
            Mage::logException($e);
        }
        echo "OKIE!!!";
    }
}