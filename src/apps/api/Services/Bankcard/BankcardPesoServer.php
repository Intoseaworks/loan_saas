<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2018/12/28
 * Time: 10:02
 */

namespace Api\Services\Bankcard;

use Api\Models\BankCard\BankCardPeso;
use Api\Models\User\User;
use Api\Services\BaseService;
use Common\Utils\Data\StringHelper;

class BankcardPesoServer extends BaseService
{
    public function create($data)
    {
        $attributes = [];
        $userId = array_get($data, 'user_id', '');
        $paymentType = array_get($data, 'type', '');
        $user = User::find($userId);
        $attributes['user_id'] = $userId;
        $attributes['payment_type'] = $paymentType;
        $attributes['account_name'] = array_get($data, 'account_name', '');
        $bank_code = StringHelper::delSpace(trim(array_get($data, 'bank_code', $user->telephone)));
        if ($bank_code == 'undefined' || empty($bank_code)){
            $bank_code = $user->telephone;
        }
        $bank_no = StringHelper::delSpace(trim(array_get($data, 'bank_no', $user->telephone)));
        if ($bank_no == 'undefined' || empty($bank_no)){
            $bank_no = $user->telephone;
        }
        //新需求bank_code付款类型other更改
        //type=other时,bank_code字符数10-11更改
//        if ( !($paymentType == BankCardPeso::PAYMENT_TYPE_OTHER && (strlen($bank_code)==10 || strlen($bank_code)==11)) ){
//            return false;
//        }
        $attributes['account_no'] = $paymentType == BankCardPeso::PAYMENT_TYPE_OTHER ? $bank_code : $bank_no;
        //u3传参问题
        if ($bank_code == $user->telephone && $bank_no!=$user->telephone ){
            $attributes['account_no'] = $bank_no;
        }
        $attributes['bank_name'] = $paymentType == BankCardPeso::PAYMENT_TYPE_OTHER ? "" : array_get($data, 'bank_code', '');
        $attributes['instituion_name'] = array_get($data, 'institution_name', '');
        $attributes['channel'] = array_get($data, 'channel_name', '');
        $attributes['other_payment'] = array_get($data, 'other_payment', '{}');
        if($attributes['other_payment'] == ''){
            $attributes['other_payment'] = '{}';
        }
        if ( $bank_name = array_get($data, 'bank_name', '') ){
            $attributes['bank_name'] = $bank_name;
        }
        BankCardPeso::model()->clear($userId);
        //兼容u3
        $attributes['status'] = BankCardPeso::STATUS_ACTIVE;
        $model = BankCardPeso::model()->updateOrCreateModel($attributes,['payment_type'=>$attributes['payment_type'],
            'account_no'=>$attributes['account_no'],'bank_name'=>$attributes['bank_name'],'user_id'=>$attributes['user_id'],'status'=>BankCardPeso::STATUS_DELETE,
            'channel'=>$attributes['channel']
        ]);
//        $model = BankCardPeso::model()->createModel($attributes);
        if ($model) {
            $date = date('Y-m-d H:i:s');
            $model->created_at = $date;
            $model->updated_at = $date;
            //u3时间创建问题
            //如果等于STATUS_DELETE,判断是否是唯一卡,是则改成STATUS_ACTIVE
            if ( $model->status == BankCardPeso::STATUS_DELETE ){
                $model->status = BankCardPeso::STATUS_ACTIVE;
                //判断客户的卡其它状态为1的置0
                foreach ($user->bankCards as $bankCard){
                    if ($bankCard->id != $model->id){
                        $bankCard->status = BankCardPeso::STATUS_DELETE;
                        $bankCard->save();
                    }
                }
            }
            $model->save();
            return $model;
        } else {
            return $this->outputException('银行卡信息保存失败');
        }
    }

    public function createPeralending($data)
    {
        $attributes = [];
        $userId = array_get($data, 'user_id', '');
        $paymentType = array_get($data, 'type', '');
        $user = User::find($userId);
        $attributes['user_id'] = $userId;
        $attributes['payment_type'] = $paymentType;
        $attributes['account_name'] = array_get($data, 'account_name', '');
        $bank_code = StringHelper::delSpace(trim(array_get($data, 'bank_code', $user->telephone)));
        if ($bank_code == 'undefined' || empty($bank_code)){
            $bank_code = $user->telephone;
        }
        $bank_no = StringHelper::delSpace(trim(array_get($data, 'bank_no', $user->telephone)));
        if ($bank_no == 'undefined' || empty($bank_no)){
            $bank_no = $user->telephone;
        }
        //新需求bank_code付款类型other更改
        //type=other时,bank_code字符数10-11更改
//        if ( !($paymentType == BankCardPeso::PAYMENT_TYPE_OTHER && (strlen($bank_code)==10 || strlen($bank_code)==11)) ){
//            return false;
//        }
        $attributes['account_no'] = $paymentType == BankCardPeso::PAYMENT_TYPE_OTHER ? $bank_code : $bank_no;
        //u3传参问题
        if ($bank_code == $user->telephone && $bank_no!=$user->telephone ){
            $attributes['account_no'] = $bank_no;
        }
        $attributes['bank_name'] = $paymentType == BankCardPeso::PAYMENT_TYPE_OTHER ? "" : array_get($data, 'bank_code', '');
        $attributes['instituion_name'] = array_get($data, 'institution_name', '');
        $attributes['channel'] = array_get($data, 'channel_name', '');
        if ( $bank_name = array_get($data, 'bank_name', '') ){
            $attributes['bank_name'] = $bank_name;
        }
        //兼容u3迁移数据
        $model = BankCardPeso::model()->where('user_id',$attributes['user_id']);
        if ($attributes['payment_type']){
            $model->where('payment_type',$attributes['payment_type']);
        }
        if ($attributes['account_no']){
            $model->where('account_no',$attributes['account_no']);
        }
        if ($attributes['bank_name']){
            $model->where('bank_name',$attributes['bank_name']);
        }
        if ($attributes['channel']){
            $model->where('channel',$attributes['channel']);
        }
        $model = $model->where('status','!=',BankCardPeso::STATUS_DELETE_PERALENDING)->first();
        if ( $model ){
            $model->saveModel($attributes);
        }else{
            //u3添加银行卡
            $attributes['status'] = BankCardPeso::STATUS_DELETE;
            $model = BankCardPeso::model()->updateOrCreateModel($attributes,['payment_type'=>$attributes['payment_type'],
                'account_no'=>$attributes['account_no'],'bank_name'=>$attributes['bank_name'],'user_id'=>$attributes['user_id'],'status'=>BankCardPeso::STATUS_DELETE,
                'channel'=>$attributes['channel']
            ]);
        }
//        $model = BankCardPeso::model()->createModel($attributes);
        if ($model) {
            $date = date('Y-m-d H:i:s');
            $model->created_at = $date;
            $model->updated_at = $date;
            $model->account_name = $attributes['account_name'];
            //u3时间创建问题
            //如果等于STATUS_DELETE,判断是否是唯一卡,是则改成STATUS_ACTIVE
            if ( $model->status == BankCardPeso::STATUS_DELETE ){
                $model->status = BankCardPeso::STATUS_ACTIVE;
                //判断客户的卡其它状态为1的置0
                foreach ($user->bankCards as $bankCard){
                    if ($bankCard->id != $model->id){
                        $bankCard->status = BankCardPeso::STATUS_DELETE;
                        $bankCard->save();
                    }
                }
            }
            $model->save();
            return $model;
        } else {
            return $this->outputException('银行卡信息保存失败');
        }
    }
}
