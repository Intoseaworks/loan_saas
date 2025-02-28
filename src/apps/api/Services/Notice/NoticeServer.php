<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Notice;

use Api\Models\Notice\Notice;
use Api\Services\BaseService;
use Common\Exceptions\ApiException;
use Common\Redis\Notice\NoticeRedis;

class NoticeServer extends BaseService
{

    public function getOne($param)
    {
        $data = Notice::model()->getOne($param);
        if (!$data) {
            throw new ApiException('记录不存在');
        }
        NoticeRedis::redis()->read($data->id, $param['user_id']);
        if (!\DB::table('notice_user')->where('user_id',$param['user_id'])->where('notice_id',$data->id)->exists()){
            \DB::table('notice_user')->insert(['user_id'=>$param['user_id'],'notice_id'=>$data->id,'created_at'=>date('Y-m-d H:i:s'),
                'updated_at'=>date('Y-m-d H:i:s')]);
            $readCount = \DB::table('notice_user')->where('notice_id',$data->id)->count();
            $notice = Notice::model()->newQueryWithoutScopes()->where('id',$data->id)->first();
            $notice->read_total = $readCount;
            $notice->save();
        }
        return $data;
    }

    public function setRead($param)
    {
        foreach ($param['id'] as $id){
            $newParam['id'] = $id;
            $data = Notice::model()->getOne($newParam);
            if (!$data) {
                throw new ApiException('记录不存在');
            }
            NoticeRedis::redis()->read($data->id, $param['user_id']);
            if (!\DB::table('notice_user')->where('user_id',$param['user_id'])->where('notice_id',$data->id)->exists()){
                \DB::table('notice_user')->insert(['user_id'=>$param['user_id'],'notice_id'=>$data->id,'created_at'=>date('Y-m-d H:i:s'),
                    'updated_at'=>date('Y-m-d H:i:s')]);
                $readCount = \DB::table('notice_user')->where('notice_id',$data->id)->count();
                $notice = Notice::model()->newQueryWithoutScopes()->where('id',$data->id)->first();
                $notice->read_total = $readCount;
                $notice->save();
            }
        }
        return true;
    }

    public function getUrgent()
    {
        $notice = Notice::model()->getOneByType(Notice::TAGS_URGENT);
        if(!$notice){
            return false;
        }
        return $notice->setScenario(Notice::SCENARIO_URGENT)->getText();
    }

    public function getTip()
    {
        $notice = Notice::model()->getOneByType(Notice::TAGS_NORMAL);
        if(!$notice){
            return false;
        }
        return $notice->setScenario(Notice::SCENARIO_TIP)->getText();
    }

}
