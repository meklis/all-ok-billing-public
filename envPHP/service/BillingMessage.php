<?php
namespace envPHP\service;
/**
 * Created by PhpStorm.
 * User: Meklis
 * Date: 29.01.2019
 * Time: 16:31
 */

class BillingMessage
{
   public static function sendActivateSMS($activation) {
        $config = getGlobalConfigVar('SMS_TEXTS');
        if(!$config['defrost']['enabled']) {
            \envPHP\classes\std::msg('SMS for defrost is disabled, ignoring...');
            return false;
        }
        $data = dbConnPDO()->query("SELECT cc.value phone, c.agreement, bp.sms_name service 
            FROM bill_prices bp 
            JOIN client_prices cp on cp.price = bp.id 
            JOIN clients c on c.id = cp.agreement
            JOIN client_contacts cc on c.id = cc.agreement_id and cc.type='PHONE' and cc.main = 1 
            WHERE cp.id = '$activation'
            LIMIT 1 ")->fetch();
        if($data['service']  && $data['phone']) {
            $text = self::prepareTemplate($config['defrost']['text'], $data);
            \envPHP\classes\std::msg("Service is named and phone is defined. Generated SMS: $text");
            shedule::add('19', 'notification/sendSMS', [
                'phone' => $data['phone'],
                'message' => $text,
            ]);
            return true;
        } else {
            \envPHP\classes\std::msg("Service or phone not defined, SMS not sended");
        }
        return false;
   }
   public static function sendDeactivateSMS($activation) {
       $config = getGlobalConfigVar('SMS_TEXTS');
       if(!$config['frost']['enabled']) {
           \envPHP\classes\std::msg('SMS for frost is disabled, ignoring...');
           return false;
       }
       $data = dbConnPDO()->query("SELECT cc.value phone, c.agreement, bp.sms_name service 
            FROM bill_prices bp 
            JOIN client_prices cp on cp.price = bp.id 
            JOIN clients c on c.id = cp.agreement
            JOIN client_contacts cc on c.id = cc.agreement_id and cc.type='PHONE' and cc.main = 1 
            WHERE cp.id = '$activation'
            LIMIT 1 ")->fetch();
       if($data['service']  && $data['phone']) {
           $text = self::prepareTemplate($config['frost']['text'], $data);
           \envPHP\classes\std::msg("Service is named and phone is defined. Generated SMS: $text");
           shedule::add('19', 'notification/sendSMS', [
               'phone' => $data['phone'],
               'message' => $text,
           ]);
           return true;
       } else {
           \envPHP\classes\std::msg("Service or phone not defined, SMS not sended");
       }
       return false;
   }

   private static function prepareTemplate($templateText, $placeholders = []) {
       foreach ($placeholders as $placeholder=>$value) {
           $templateText = str_replace('{{'.$placeholder.'}}', $value, $templateText);
       }
       return $templateText;
   }
}
