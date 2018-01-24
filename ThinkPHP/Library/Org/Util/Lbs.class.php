<?php
/**
 * LBS 基类
 *
 * PHP version 5
 *
 * @package   LS
 * @author    Fee <Fee@shorigo.com>
 * @copyright 2015 SRG Inc.
 */
namespace Org\Util;
use Think;
class Lbs
{
    //索引长度
    protected $index_len = 4;
 
    protected $redis;
 
    protected $geohash;

    //缓存有效时间
    protected $cachetime;

    public function __construct($config)
    {
        $this->redis = new \Redis();
        $this->redis->pconnect($config["host"],$config["port"]);
        $this->geohash = new Geohash();
        $this->cachetime = 345600; //四天
    }

    /**
     * 获取LBS
     *
     * @param Int   $id   关联ID
     * @param Int   $type      类型 0用户 1部落 2抱团
     *
     * @return void
     * @author Fee
     */
    public function getLbs($id,$type=0) {
        switch ($type) {
            case 1:
                $key_prefix = "tribe_lbs_";
                break;
            case 2:
                $key_prefix = "team_lbs_";
                break;
            default:
                $key_prefix = "user_lbs_";
                break;
        }
        $key = $key_prefix.$id;
        return array(
                    "lat" => $this->redis->hGet($key,'la'),
                    "lon" => $this->redis->hGet($key,'lo'),
                    "time" => $this->redis->hGet($key,'t'),
                );
    }

    /**
     * 更新LBS信息
     *
     * @param Int   $id   关联ID
     * @param Float $latitude  纬度
     * @param Float $longitude 经度
     * @param Int   $type      类型 0用户 1部落 2抱团
     *
     * @return void
     * @author Fee
     */
    public function upinfo($id,$latitude,$longitude,$type=0)
    {
        switch ($type) {
            case 1:
                $key_prefix = "tribe_lbs_";
                break;
            case 2:
                $key_prefix = "team_lbs_";
                break;
            default:
                $key_prefix = "user_lbs_";
                break;
        }
        $key = $key_prefix.$id;

        //获取原Geohash
        $o_hashdata = $this->redis->hGet($key,'geo');

        if(!empty($o_hashdata)) {
            //删除原索引
            $old_key = substr($o_hashdata, 0, $this->index_len);
            $this->redis->sRem($old_key,$key);
        }

        //新数据处理
 
        //纬度
        $this->redis->hSet($key,'la',$latitude);
 
        //经度
        $this->redis->hSet($key,'lo',$longitude);
 
        //更新时间
        $this->redis->hSet($key,'t',time());

        //Geohash
        $hashdata = $this->geohash->encode($latitude,$longitude);
        $this->redis->hSet($key,'geo',$hashdata);
        
        //索引
        $index_key = $key_prefix.substr($hashdata, 0, $this->index_len);
        
        //存入
        $this->redis->sAdd($index_key,$id);
        
        return true;
    }
 
    /**
     * 获取附近用户/部落/抱团/脱单
     *
     * @param Float $latitude  纬度
     * @param Float $longitude 经度
     * @param Int   $type      类型 0用户 1部落 2抱团 3脱单
     *
     * @return void
     * @author Fee
     */
    public function serach($latitude,$longitude,$type=0)
    {
        switch ($type) {
            case 1:
                $key_prefix = "tribe_lbs_";
                break;
            case 2:
                $key_prefix = "team_lbs_";
                break;
            default:
                $key_prefix = "user_lbs_";
                break;
        }

        //Geohash
        $hashdata = $this->geohash->encode($latitude,$longitude);
        
        $key = substr($hashdata, 0, $this->index_len);

        //组合附近八个格子
        $neighbors = $this->geohash->neighbors($key);

        $neighbors['index'] = $key;

        $id_array = array();
        $time = time();

        foreach ($neighbors as $k => $v) {
            $ids = $this->redis->sMembers($key_prefix.$v);
            if(!empty($ids)) {        
                foreach ($ids as $id) {
                    if($type == 0) {
                        $key = $key_prefix.$id;
                        $t = $this->redis->hGet($key,'t');
                        if($time - $t > $this->cachetime) {
                            continue;
                        }
                    }
                    $id_array [] = $id;
                }
            }
        }
        
        return $id_array;
    }
}