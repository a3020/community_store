<?php
namespace Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Methods\Invoice;

use Core;
use Config;

use \Concrete\Package\CommunityStore\Src\CommunityStore\Payment\Method as StorePaymentMethod;

class InvoicePaymentMethod extends StorePaymentMethod
{
    public function dashboardForm()
    {
        $this->set('form',Core::make("helper/form"));
        $this->set('invoiceMinimum',Config::get('communitystore.invoiceMinimum'));
        $this->set('invoiceMaximum',Config::get('communitystore.invoiceMaximum'));
    }
    
    public function save($data)
    {
        Config::save('communitystore.invoiceMinimum',$data['invoiceMinimum']);
        Config::save('communitystore.invoiceMaximum',$data['invoiceMaximum']);
    }
    public function validate($args,$e)
    {
        
        //$e->add("error message");        
        return $e;
        
    }
    public function checkoutForm()
    {
        //nada
    }
    
    public function submitPayment()
    {
        
        //nothing to do except return success
        return array('error'=>0, 'transactionReference'=>'');
        
    }

    public function getPaymentMinimum() {
        $defaultMin  = 0;

        $minconfig = trim(Config::get('communitystore.invoiceMinimum'));

        if ($minconfig == '') {
            return $defaultMin;
        } else {
            return max($minconfig, $defaultMin);
        }
    }

    public function getPaymentMaximum() {
        $defaultMax  = 1000000000;

        $maxconfig = trim(Config::get('communitystore.invoiceMaximum'));
        if ($maxconfig == '') {
            return $defaultMax;
        } else {
            return min($maxconfig, $defaultMax);
        }
    }

}
