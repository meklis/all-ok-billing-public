<?php
//@TODO Закончить писать хук
require_once __DIR__ . "/../envPHP/load.php";

use envPHP\classes\std;
use envPHP\service\OmoLocalControl;
use \OmoSystemsApi\Models\Event;
use \OmoSystemsApi\EventHook;

$data =  file_get_contents('php://input');
if(!$data) {
    throw new \Exception("Empty data");
}

$events = (new EventHook())->handle();
//std::msg("Incomming omo hook : ". file_get_contents('php://input'));

$responses = [];
foreach ($events as $event) {
    //Insert event to database for debbuging
    if(!dbConn()->query("INSERT INTO omo_events (created_at, `type`, `correlation_id`, `user_uid`, `hub_uid`, `device_uid`, `device_type`, `receiver_phone`, `shared_from_uid`, `shared_from_phone`, `reason`)
VALUES (
NOW(), 
'{$event->getEventType()}', 
'{$event->getCorrelationId()}', 
'{$event->getUserId()}', 
'{$event->getHubId()}', 
'{$event->getDeviceId()}', 
'{$event->getDeviceType()}', 
'{$event->getReceiverPhone()}', 
'{$event->getSharedFromId()}', 
'{$event->getSharedFromPhone()}', 
'{$event->getReason()}'
)")) std::msg(dbConn()->error);
    try {
        $omoLocal = new OmoLocalControl();
        switch ($event->getEventType()) {
            case Event::DeviceAdded:
                $devId = $omoLocal->deviceAdd($event->getHubId(),$event->getDeviceId(), $event->getUserId(), $event->getDeviceType());
                $userId = $omoLocal->userAdd("", $event->getUserId());
                $response = $devId;
                break;
            case Event::SharedDeviceReceived:
                //Добавляем устройство
                $devId = $omoLocal->deviceAdd($event->getHubId(),$event->getDeviceId(), $event->getUserId(), $event->getDevType());

                //Актуализируем инфу по юзерам
                $sharedFromUser = $omoLocal->userAdd($event->getSharedFromPhone());
                $omoLocal->userSetUid($event->getSharedFromPhone(), $event->getSharedFromId())->userSetPhone($event->getSharedFromId(), $event->getSharedFromPhone());
                $receiverUser = $omoLocal->userAdd($event->getReceiverPhone());
                $omoLocal->userSetUid($event->getReceiverPhone(), $event->getUserId())->userSetPhone($event->getUserId(), $event->getReceiverPhone());


                //Закрепляем устройство за пользователем
                    $omoLocal->bindAddDevice($sharedFromUser, $devId);
                $response = $omoLocal->bindAddDevice($receiverUser, $devId);

                //foreach ($omoLocal->userFindAgreementIdFromClients($event->getReceiverPhone()) as $agreeId) {
                //    $omoLocal->bindAddAgreement($receiverUser, $agreeId);
                //}
                break;
            case Event::DeviceDeleted:
                $devId = $omoLocal->deviceGetByUid($event->getDeviceId());
                $userId = $omoLocal->userGetByUid($event->getUserId())['id'];

                $omoLocal->bindDeleteDevice($userId, $devId);

                $bindedUsers = $omoLocal->userFindByDevice($devId);
                if(count($bindedUsers) == 0) {
                    $omoLocal->deviceDelete($devId);
                }
                break;
            default:
                throw new Exception("Unknown event type");
        }
        $responses[] = [
            'correlation_id' => $event->getCorrelationId(),
            'status' => 'success',
            'reason' => '',
        ];
    } catch (\Exception $e) {
        std::msg("EXCEPTION: {$e->getMessage()}");
        std::msg("FILE: {$e->getFile()}");
        std::msg("LINE: {$e->getLine()}");
        std::msg("TRACE: {$e->getTraceAsString()}");
        $responses[] = [
            'correlation_id' => $event->getCorrelationId(),
            'status' => 'failed',
            'reason' => $e->getMessage(),
        ];
    }
}

std::Response($responses);
