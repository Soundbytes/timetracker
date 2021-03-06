<?php
// db/authormapper.php

namespace OCA\TimeTracker\Db;

use OCP\IDBConnection;
use OCP\AppFramework\Db\Mapper;

class WorkIntervalMapper extends Mapper {

    private $dbengine;
    public function __construct(IDBConnection $db) {
        $this->dbengine = 'MYSQL';
        if (strpos(get_class($db->getDatabasePlatform()),'PostgreSQL') !== FALSE){
            $this->dbengine = 'POSTGRES';
        }
        parent::__construct($db, 'timetracker_work_interval');
    }

    public function findByName($name) {
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` ' .
            'WHERE `name` = ?';
            
            try {
                $e = $this->findEntity($sql, [$name]);
                return $e;
            } catch (\OCP\AppFramework\Db\DoesNotExistException $e){
                return null;
            }
        
    }

    /**
     * @throws \OCP\AppFramework\Db\DoesNotExistException if not found
     * @throws \OCP\AppFramework\Db\MultipleObjectsReturnedException if more than one result
     */
    public function find($id) {
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` ' .
            'WHERE `id` = ?';
        return $this->findEntity($sql, [$id]);
    }


    public function findAllForWorkItem($workItemId, $limit=null, $offset=null) {
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where `work_item_id` = ?';
        return $this->findEntities($sql, [$workItemId],$limit, $offset);
    }

    public function findLatest($user, $limit = 10, $offset = 0){
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where user_uid = ? order by id desc';
        return $this->findEntities($sql, [$user],$limit, $offset);
    }

    public function findLatestByName($user, $name){
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where user_uid = ? and name = ? order by id desc';
        try {
            return $this->findEntity($sql, [$user, $name], 1, 0);
        } catch (\OCP\AppFramework\Db\DoesNotExistException $e){
            return null;
        }
    }

    public function findLatestDays($user, $limitDays = 10, $startDay = 0, $limit = 100, $offset = 0){
        if ($this->dbengine == 'MYSQL'){
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where user_uid = ? and 
                start > unix_timestamp(curdate() + interval 1 day - interval ? day) and 
                start < unix_timestamp(curdate() + interval 1 day - interval ? day) 
                order by id desc';
                return $this->findEntities($sql, [$user,$limitDays,$startDay],$limit, $offset);
        } else {
            $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where user_uid = ? and 
            start > extract(epoch from current_date + interval \'1\' day - interval \''.(int)$limitDays.'\' day) and 
            start < extract(epoch from current_date + interval \'1\' day - interval \''.(int)$startDay.'\' day)
            order by id desc';
            return $this->findEntities($sql, [$user],$limit, $offset);
        }
        
    }

    public function findAllRunning($user, $limit = 100, $offset = 0){
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where user_uid = ? and running = 1 order by id desc';
        return $this->findEntities($sql, [$user],$limit, $offset);
    }

    public function stopAllRunning($user, $limit = 100, $offset = 0){
        $sql = 'update wi FROM `*PREFIX*timetracker_work_interval` wi set wi.running = 0 where user_uid = ? and running = 1';
        return $this->findEntities($sql, [$user],$limit, $offset);
    }

    public function findAllForProject($project_id){
        $sql = 'SELECT * FROM `*PREFIX*timetracker_work_interval` where project_id = ?';
        return $this->findEntities($sql, [$project_id]);
    }

    public function deleteAllForProject($project_id) {
        $sql = 'delete FROM `*PREFIX*timetracker_work_interval` ' .
            ' where project_id = ?';
            
            try {
                $this->execute($sql, [$project_id]);
                return;
            } catch (\OCP\AppFramework\Db\DoesNotExistException $e){
                return;
            }
        
    }

}