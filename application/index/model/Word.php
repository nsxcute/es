<?php
namespace app\index\model;
use think\Model;
use think\Db;
class Word extends Model
{
    protected $wordtable = 'es_near';
    public function wordlists($post,$dwhere,$nwhere,$createtime,$updatetime)
    {
        $res = Db::table($this->wordtable)
            ->where($dwhere)
            ->whereOr($nwhere)
            ->order($createtime)
            ->order($updatetime)
            ->page($post['tpage'],10)
            ->select();
        //dump( Db::table('es_near')->getLastSql());die;
        return $res;
    }
    public function addword($arr)
    {
        $res = Db::table($this->wordtable)
            ->insert($arr);
        return $res;
    }
    public function selword()
    {
        $res = Db::table($this->wordtable)
            ->select();
        return $res;
    }
    public function seldword($dword)
    {
        $res = Db::table($this->wordtable)
            ->where($dword)
            ->select();
        return $res;
    }
    public function delword($id)
    {
        $res = Db::table($this->wordtable)
            ->delete($id);
        return $res;
    }
    public function updateword($id,$where)
    {
        $res = Db::table($this->wordtable)
            ->where($id)
            ->update($where);
        return $res;
    }
}