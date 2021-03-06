<?php

class Gift {
    public function create($entity) {
        global $_W;
        $rec = array_elements(array('title', 'type', 'remark', 'tag'), $entity);
        $rec['uniacid'] = $_W['uniacid'];
        $condition = '`uniacid`=:uniacid AND `title`=:title';
        $pars = array();
        $pars[':uniacid'] = $rec['uniacid'];
        $pars[':title'] = $rec['title'];
        $sql = 'SELECT * FROM ' . tablename('mbrp_gifts') . " WHERE {$condition}";
        $exists = pdo_fetch($sql, $pars);
        if(!empty($exists)) {
            return error(-1, '这个礼品名称已经使用, 请更换');
        }

        $ret = pdo_insert('mbrp_gifts', $rec);
        if(!empty($ret)) {
            $id = pdo_insertid();
            return $id;
        }
        return false;
    }

    public function modify($id, $entity) {
        global $_W;
        $id = intval($id);
        $rec = array_elements(array('title', 'type', 'remark', 'tag'), $entity);
        $rec['uniacid'] = $_W['uniacid'];
        $condition = '`uniacid`=:uniacid AND `title`=:title AND `id`!=:id';
        $pars = array();
        $pars[':uniacid'] = $rec['uniacid'];
        $pars[':title'] = $rec['title'];
        $pars[':id'] = $id;
        $sql = 'SELECT * FROM ' . tablename('mbrp_gifts') . " WHERE {$condition}";
        $exists = pdo_fetch($sql, $pars);
        if(!empty($exists)) {
            return error(-1, '这个礼品名称已经使用, 请更换');
        }

        $ret = pdo_update('mbrp_gifts', $rec, array('id'=>$id));
        return $ret !== false;
    }

    public function remove($id) {
        global $_W;
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];

        $sql = 'DELETE FROM ' . tablename('mbrp_activity_gifts') . ' WHERE `gift`=:id';
        pdo_query($sql, $pars);
        
        $pars[':id'] = $id;
        $sql = 'DELETE FROM ' . tablename('mbrp_gifts') . ' WHERE `uniacid`=:uniacid AND `id`=:id';
        pdo_query($sql, $pars);
        $sql = 'DELETE FROM ' . tablename('mbrp_records') . ' WHERE `uniacid`=:uniacid AND `gift`=:id';
        pdo_query($sql, $pars);
        return true;
    }

    public function getOne($id) {
        global $_W;
        $condition = '`uniacid`=:uniacid AND `id`=:id';
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        $pars[':id'] = $id;
        $sql = 'SELECT * FROM ' . tablename('mbrp_gifts') . " WHERE {$condition}";
        $entity = pdo_fetch($sql, $pars);
        if(!empty($entity)) {
            $entity['tag'] = @unserialize($entity['tag']);
        }
        return $entity;
    }

    public function getAll($filters, $pindex = 0, $psize = 20, &$total = 0) {
        global $_W;
        $condition = '`uniacid`=:uniacid';
        $pars = array();
        $pars[':uniacid'] = $_W['uniacid'];
        if(!empty($filters['type'])) {
            $condition .= ' AND `type`=:type';
            $pars[':type'] = $filters['type'];
        }
        if(!empty($filters['title'])) {
            $condition .= ' AND `title` LIKE :title';
            $pars[':title'] = "%{$filters['title']}%";
        }
        $sql = 'SELECT * FROM ' . tablename('mbrp_gifts') . " WHERE {$condition} ORDER BY `id` DESC";
        if($pindex > 0) {
            $sql = "SELECT COUNT(*) FROM " . tablename('mbrp_gifts') . " WHERE {$condition}";
            $total = pdo_fetchcolumn($sql, $pars);
            $start = ($pindex - 1) * $psize;
            $sql = "SELECT * FROM " . tablename('mbrp_gifts') . " WHERE {$condition} ORDER BY `id` DESC LIMIT {$start},{$psize}";
        }
        $ds = pdo_fetchall($sql, $pars);
        return $ds;
    }
}