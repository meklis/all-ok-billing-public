<?php


namespace envPHP;


interface StoreObjectInterface
{
    /**
     * @param $id
     * @return self
     */
    public function fillById($id);

    /**
     * @return self
     */
    public function save();

    /**
     * @return self[]
     */
    public static function getAll();

    /**
     * @param $id
     * @return void
     */
    public static function delete($id);

    /**
     * @return array
     */
    public function getAsArray();
}