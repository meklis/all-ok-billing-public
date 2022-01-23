<?php


namespace Api\V2\Actions\Priv\Equipment\Pinger;


use Api\V2\Actions\Action;
use Api\V2\DomainException\DomainRecordNotFoundException;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Exception\HttpBadRequestException;

class UpdateHostStatusAction extends Action
{
    protected function action(): Response
    {
        $sql = dbConnPDO();
        $data = $this->getFormData();
        $updated_count = 0;

        $dev_status_sth = $sql->prepare("UPDATE equipment SET ping = ?, last_ping= ? WHERE id = ? ");
        $checking_sth = $sql->prepare("SELECT id, ping `status`, last_ping FROM equipment WHERE ip = ?");
        $log_sth = $sql->prepare("INSERT INTO eq_pinger_log (equipment, down, up) VALUES (?, ?, ?)");
        $nowTime = date("Y-m-d H:i:s");
        foreach ($data as $new) {
            $checking_sth->execute([$new['ip']]);

            //Получение старого состояния
            $old = $checking_sth->fetch();

           //Обновление актуального состояния
            if (
                ($old['status'] < 0 && $new['status'] < 0) ||
                ($old['status'] > 0 && $new['status'] > 0)
            ) {
                $dev_status_sth->execute([$new['status'], $old['last_ping'], $old['id']]);
            } else {
                $dev_status_sth->execute([$new['status'], $nowTime , $old['id']]);
            }

            //Запись логов падения
            if($old['status'] < 0 && $new['status'] > 0) {
                $log_sth->execute([$old['id'], $old['last_ping'], $nowTime]);
            }

            //Event notify
            if(($old['status'] <= 0 && $new['status'] > 0) || ($old['status'] > 0 && $new['status'] <= 0 )) {
                \envPHP\EventSystem\EventRepository::getSelf()
                    ->notify('pinger:status_changed', [
                        'ip' => $new['ip'],
                        'is_alive'=>$new['status'] > 0,
                    ]);
            }
            $updated_count++;
        }
        return $this->respondWithData([
            'updated' => $updated_count,
        ]);
    }

}