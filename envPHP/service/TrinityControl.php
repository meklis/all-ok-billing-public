<?php


namespace envPHP\service;


use envPHP\classes\std;
use Meklis\Network\TrinityTV\Api;

class TrinityControl
{

    static protected $trinity;

    /**
     * ПРоверяет можно ли пользователю зарегистрировать еще одно устройство для работы.
     *
     * @param $activationId
     * @return bool
     */
    static public function isRegAllowed($activationId) {
        $sth = dbConnPDO()->prepare("SELECT id FROM trinity_bindings WHERE activation = :activation");
        $sth->execute([':activation'=>$activationId]);
        if($sth->rowCount() < 5) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Производит регистрацию по коду
     * Добавляет привязку пользователя в локальную базу и производит регистрацию.
     *
     * @param int $activationId Номер активации пользователя
     * @param int $code Код активации
     * @param int $employee Сотрудник, производящий активацию
     * @throws \Exception
     */
    static public function regByCode($activationId, $code, $employee) {
        $contract = self::getClearContract($activationId);
        $registr = self::getTrinity()->addDeviceByCode($contract, $code);
        $uuid = isset($registr->uuid) ? $registr->uuid : "";
        $mac = isset($registr->mac) ? $registr->mac : "";
        if($mac || $uuid) {
            try {
                self::addLocalBinding($activationId, $contract, $mac, $uuid, $employee);
            } catch (\Exception $e) {
                std::msg("Error working with local database when try add device");
                std::msg($e->getMessage());
                self::getTrinity()->deleteDevice($contract, $mac, $uuid);
            }
        } else {
            std::msg("Error registration trinity device over code. response from api: " . json_encode($registr));
            if(isset($registr->result)) {
                throw new \Exception("Partner API returned error: {$registr->result}");
            }
            throw new \Exception("Unknown error working with partner API");
        }
        return $registr;
    }

    /**
     * Производит регистрацию устройства.
     * Получает свободный контракт, регистрирует устройство по этому контракту, производит запись привязки в локальную базу данных
     *
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @param int $employee         локальный ID пользователя
     * @param string $mac           MAC адрес абонента, который нужно передать в тринити
     * @param string $uuid          UUID юзера который нужно передать в тринити
     * @throws \Exception
     * @return  int
     */
    static public function reg($activationId, $employee, $mac = "", $uuid = "") {
        $contract = self::getClearContract($activationId);
        if($mac || $uuid) {
            $status = self::getTrinity()->addDevice($contract, $mac, $uuid);
        } else {
            $status = self::getTrinity()->addPlaylist($contract);
            if(isset($status->playlist) && $status->playlist->status) {
                $uuid = $status->playlist->uuid;
            } else {
                throw new \Exception("Error from trinity when try create playlist");
            }
        }

        if(isset($status->result) && $status->result == 'success') {
            return  self::addLocalBinding($activationId, $contract, $mac, $uuid, $employee);
        } else {
            std::msg("Error registration trinity device for activation=$activationId, contract=$contract, mac=$mac, uuid=$uuid");
            std::msg("Unknown response from partner: " . json_encode($status));
            throw new \Exception("Unknown response from partner: " . json_encode($status));
        }
    }

    /**
     * Производит заморозку абонента.
     * Фактически, удаляет устройства с контракта тринити, но оставляет информацию о привязке в локальной базе.
     *
     * @param int $activationId ID активации
     * @param int $employee ID сотрудника, производящего заморозку
     *
     * @return int
     * @throws \Exception
     */
    static public function frost($bindingId) {
        //Get bindings
        $binding = self::getBindingById($bindingId);
        $inTrans = dbConnPDO()->inTransaction();
         if(!$inTrans) dbConnPDO()->beginTransaction();
        $frostResp = self::getTrinity()->deleteDevice($binding['contract'], $binding['mac'], $binding['uuid']);
        if (@$frostResp->result != "success") {
            if(!$inTrans) dbConnPDO()->rollBack();
            throw new \Exception("Trinity returned error: " . json_encode($frostResp));
        }
        if($binding['local_playlist_id']) {
            dbConnPDO()->prepare("UPDATE trinity_bindings SET uuid = null WHERE id = ?")->execute([$binding['id']]);
        }
        $sth = dbConnPDO()->prepare("UPDATE trinity_bindings SET contract = null WHERE id = :id");
        $sth->execute([':id'=>$binding['id']]);
        if(!$inTrans) dbConnPDO()->commit();
        return $bindingId;
    }

    /**
     * Произовдит разморозку устройтв тринити.
     * Фактически, на основе данных в локальной базе вносит устройства в тринити.
     *
     * @param int $activationId ID активации
     * @param int $employee ID сотрудника, производящего разморозку
     *
     * @return int
     * @throws \Exception
     */
    static public function defrost($activationId, $newActivation, $bindingId) {
        $binding = self::getBindingById($bindingId);
        $contract = self::getClearContract($activationId);
        std::msg("Binding info for trinity - " . json_encode($binding));
        std::msg("Defined trinity contract - $contract");
        $inTrans = dbConnPDO()->inTransaction();
        if(!$inTrans) dbConnPDO()->beginTransaction();
        if($binding['local_playlist_id']) {
            $defrost_resp = self::getTrinity()->addPlaylist($contract );
            if(isset($defrost_resp->playlist) && $defrost_resp->playlist->status) {
                $sth = dbConnPDO()->prepare("UPDATE trinity_bindings SET uuid = :uid WHERE id = :id");
                $sth->execute([':uid'=>$defrost_resp->playlist->uuid, ':id'=>$binding['id']]);
            } else {
                throw new \Exception("Error from trinity when try create playlist");
            }
        } else {
            $defrost_resp = self::getTrinity()->addDevice($contract, $binding['mac'], $binding['uuid']);
        }

        if(!$defrost_resp) {
            if(!$inTrans) dbConnPDO()->rollBack();
            throw new \Exception("Error defrosting - no response from trinity");
        } elseif (@$defrost_resp->result != "success") {
            if(!$inTrans) dbConnPDO()->rollBack();
            throw new \Exception("Trinity returned error: " . json_encode($defrost_resp));
        }
        std::msg("Defrosting response from trinity - " . json_encode($defrost_resp));
        $sth = dbConnPDO()->prepare("UPDATE trinity_bindings SET contract = :contract, activation = :activation WHERE id = :binding");
        $sth->execute([
            ':contract' => $contract,
            ':activation' => $newActivation,
            ':binding' => $binding['id'],
        ]);
        if(!$inTrans) dbConnPDO()->commit();
        return $newActivation;
    }

    /**
     * Удаляет устройства с контракта тринити, удаляет данные о привязке с локальной базы данных по ID привязки
     *
     * @param $bindId
     *
     * @return int
     * @throws \Exception
     */
    static public function deregBindById($bindId) {
        //Get bindings
        $binding = self::getBindingById($bindId);
        self::getTrinity()->deleteDevice($binding['contract'], $binding['mac'], $binding['uuid']);
        dbConnPDO()->prepare("DELETE FROM trinity_bindings WHERE id = :id")->execute([':id'=>$binding['id']]);
        return $bindId;
    }

    /**
     * Добавляет привязку в локальную базу данных, возвращает ID новой записи
     *
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @param int $contract         Номер контракта от тринити
     * @param string $mac           MAC адрес абонента, который нужно передать в тринити
     * @param string $uuid          UUID юзера который нужно передать в тринити
     * @param int $employee         локальный ID пользователя
     * @return int                  ID записи в локальной базе привязок
     * @throws \Exception
     */
    protected static function addLocalBinding($activationId, $contract, $mac, $uuid, $employee) {
        $local_playlist_id = '';
        if(strpos($uuid, 'http') !== false) {
                $local_playlist_id = self::generateId();
        }
        $sth = dbConnPDO()->prepare("INSERT INTO trinity_bindings (created, activation, contract, mac, uuid, employee, local_playlist_id) VALUES 
(NOW(), :activationId, :contract, :mac, :uuid, :employee, :local_playlist);");
        $sth->execute([
           ':activationId' => $activationId,
           ':contract' => $contract,
           ':mac' => $mac,
           ':uuid' => $uuid,
           ':employee' => $employee,
           ':local_playlist' => $local_playlist_id,
        ]);
        return dbConnPDO()->lastInsertId();
    }

    /**
     * Возвращает список привязок найденных по активации в локальной базе данных
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @return array                  [][id, contract, mac, uuid] - Список привязок по активации
     * @throws \Exception
     */
//    protected static function getLocalBindingByActivation($activationId) {
//        $sth = dbConnPDO()->query("SELECT id, contract, mac, uuid, local_playlist_id FROM trinity_bindings WHERE activation = :activation");
//        $sth->execute(['activation'=>$activationId]);
//        return $sth->fetchAll();
//    }

    /**
     * Возвращает данные привязки по ID привязки
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @return array                [id, contract, mac, uuid]
     * @throws \Exception
     */
    public static function getBindingById($binding) {
        $sth = dbConnPDO()->prepare("SELECT id, contract, mac, uuid, local_playlist_id FROM trinity_bindings WHERE id = :id");
        $sth->execute([':id'=>$binding]);
        if(!$sth->rowCount()) {
            throw new \Exception("Binding by id not found");
        }
        return $sth->fetch();
    }

    /**
     * Возвращает данные привязки по ID  плейлиста.
     * Используется в АПИ, что бы отдавать нужное содержание плейлиста
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @return array                [id, contract, mac, uuid]
     * @throws \Exception
     */
    public static function getBindingByPlaylistId($binding) {
        $sth = dbConnPDO()->prepare("SELECT id, contract, mac, uuid, local_playlist_id FROM trinity_bindings WHERE local_playlist_id = :id");
        $sth->execute([':id'=>$binding]);
        if(!$sth->rowCount()) {
            throw new \Exception("Binding by id not found");
        }
        return $sth->fetch();
    }

    /**
     * Удаляет привязку с локальной базы данных по ID
     *
     * @param int $activationId     Номер активации c нашим прайсом
     * @return int                  ID записи в локальной базе привязок
     * @throws \Exception
     */
    protected static function deleteLocalBinding($bindingId) {
        $status =  dbConnPDO()->prepare("DELETE FROM trinity_bindings WHERE id = :id")->execute(['id'=>$bindingId]);
        return  $status;
    }

    /**
     * Возвращает свободный контракт для регистрации на него привязки.
     * 1. Ищет свободные контракты по нужному прайсу и возвращает один
     * 2. Если свободных контрактов не найдено - проверяет по отключенным контрактам, включает его с нужным прайсом и возвращает
     * 3. Если отключенных контрактов нету - создает новый контракт с указанным прайсом
     *
     * @param $activationId
     * @throws \Exception
     * @return  int
     */
    static protected function getClearContract($activationId) {
       std::msg("Search clear contract for activation $activationId");
       $subscrId = self::getTrinityPrice($activationId);
       std::msg("TrinitySubscrId = $subscrId by activation $activationId");
       //Выберем из существующих подписок свободный слот для устройства
       $contractSTH = dbConnPDO()->prepare("SELECT id, `count` devices_count
            FROM `v_trinity_contract_stat`
            WHERE trinity_price_id = ? and count < 4
            ORDER BY devices_count desc limit 1;");
       $contractSTH->execute([$subscrId]);
       $contractId = 0;

       //Не нашли свободный и активный контракт, попробуем поискать среди существующих, но с прайсом = тест
       if($contractSTH->rowCount() === 0) {
           $disabledSubscribeId = getGlobalConfigVar('TRINITY')['trinity_disabled_price_id'];
           std::msg("Free contract with subscribeId = $subscrId not found, searching from disabled contracts - {$disabledSubscribeId}");
           $contractDisabledSTH = dbConnPDO()->prepare("SELECT id, `count` devices_count
            FROM `v_trinity_contract_stat`
            WHERE trinity_price_id = ? and count < 4
            ORDER BY devices_count desc limit 1;");
           $contractDisabledSTH->execute([$disabledSubscribeId]);
           if($contractDisabledSTH->rowCount() !== 0) {
               $contractId = $contractDisabledSTH->fetch()['id'];
               std::msg("Fount contract = $contractId with test subscrId");
               $resp = self::getTrinity()->createUser($subscrId, $contractId);
               if($resp) {
                   std::msg("Response from trinity - ".json_encode($resp));
               } else {
                   std::msg("No response from trinity");
               }
           }
       } else {
           $contractId = $contractSTH->fetch()['id'];
           std::msg("Free contract = $contractId");
       }

        //Не нашли свободные контракты ранее, придется регать новый
       if(!$contractId) {
           $next = dbConnPDO()->query("SELECT max(id) + 1 id FROM trinity_contracts;")->fetch()['id'];
           $data = self::getTrinity()->createUser($subscrId, $next);
           if(isset($data->contracttrinity)) {
              $state =  dbConnPDO()->query("INSERT INTO trinity_contracts (id, subscr_id, subscr_price, subscr_status_id, contract_trinity, devices_count, contract_date)
                VALUES (
                    '{$next}', 
                    '{$subscrId}',
                    '0',
                    '0',  
                    '{$data->contracttrinity}', 
                    '0', 
                    NOW()
                )");
              if(!$state) {
                  throw new \Exception("Error add contract to local storage: ". json_encode(dbConnPDO()->errorInfo()));
              }
              $contractId  = $next;
           } else {
               throw new \Exception("Not found free contracts, trying register new contract is failed");
           }
       }
       return $contractId;
    }

    /**
     * Возвращает прайс от тринити на основе активации и локального прайса.
     *
     * @param $activationId
     * @return mixed
     * @throws \Exception
     */
    static protected function getTrinityPrice($activationId) {
        $pricePSTH = dbConnPDO()->prepare("SELECT t.trinity_price_id price 
                FROM client_prices lp 
                JOIN trinity_price_binding t on t.local_price_id = lp.price
                WHERE lp.id = ?");
        $pricePSTH->execute([$activationId]);
        if($pricePSTH->rowCount() === 0) {
            throw new \Exception("Not found trinity price binding for activation $activationId");
        }
        return $pricePSTH->fetch()['price'];
    }


    /**
     * ВОзвращает инстанс апишки от тринити
     *
     * @return Api
     */
    static protected function getTrinity() {
        if(self::$trinity) {
            return self::$trinity;
        }
        self::$trinity = new Api(
            getGlobalConfigVar('TRINITY')['partnerID'],
            getGlobalConfigVar('TRINITY')['salt']
        );
        return self::$trinity;
    }
//    static protected function incrementCountDevice($id, $increment = 1) {
//            dbConnPDO()->query("UPDATE trinity_contracts SET devices_count = devices_count + ($increment) WHERE id = '{$id}'");
//    }

    protected static function generateId($numSymbols = 8) {
        $chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz1234567890';
        $code = '';
        for($i = 0; $i<$numSymbols; $i++) {
            $code .= $chars[rand(0,strlen($chars))];
        }
        return $code;
    }
}