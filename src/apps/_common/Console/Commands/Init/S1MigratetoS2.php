<?php

/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/2/2
 * Time: 9:31
 */

namespace Common\Console\Commands\Init;

use Illuminate\Console\Command;

class S1MigratetoS2 extends Command {
    # 每次提取任务数

    const PER_NUM = 10000;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'init:s1migratetos2 {--phones=} {--del=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 's1签约复贷客户到s2';

    public function handle() {
        if ($this->option('del')){
            $this->del($this->option('del'));
        }
//        $s1_apply_ids = \DB::connection('u3_prod_u1')->table('apply')->where('phone','9050337619')->pluck('apply_id')->toArray();
//        $s1_images = \DB::connection('u3_prod_u1')->table('images')->whereIn('apply_id',$s1_apply_ids)->get()->toArray();
//        print_r($s1_images);
//        exit;
//        $phones = explode(',',$this->option('phones'));
       $phones = ['9665635765','9275014745','9776755864','9301803159','9178941176','9084911226','9163499451','9955071674','9263739381','9362071091','9156202667','9988566990','9171805577','9199227733','9275423665','9171824113','9159204255','9062222385','9758540354','9272904855','9055363997','9505656891','9090058702','9071552789','9484945701','9279383042','9267676677','9196762145','9979230582','9471402853','9276519233','9956996214','9088906243','9493306371','9189644522','9169390121','9171177179','9158531217','9354019072','9562940847','9224745276','9664502496','9208648296','9060593052','9463345131','9277460474','9176173022','9175272088','9506408288','9050337619','9776989544','9613775834','9452181339','9173249143','9163927407','9202382054','9171261122','9516622107','9194511708','9292478380','9456203593','9777079431','9065644794','9088826574','9196224224','9195083275','9278914250','9398924280','9099876309','9669710598','9264839481','9171877208','9051932495','9989689442','9070872125','9081703186','9268141047','9362538891','9274827800','9983994573','9173047902','9267818823','9151309516','9124050277','9458832170','9296026587','9183494776','9369787939','9478116836','9567593248','9171759932','9171275949','9195456800','9050719947','9985728066','9303121406','9061428722','9177923493','9988401538','9064643182','9052700942','9450966282','9260794384','9197472139','9057977481','9497542466','9051770436','9217818393','9189596563','9266907232','9279015437','9565026160','9058326566','9225191783','9178921456','9478918035','9278372349','9151156004','9454797913','9276725989','9560294059','9772365400','9565198019','9083485380','9323371277','9568095708','9393734828','9612477305','9266663492','9052473732','9077563111','9356008232','9217940183','9489293354','9478484086','9369575417','9958453014','9998804367','9166570590','9301090727','9179340927','9365162655','9363417532','9086711277','9193830474','9217751411','9192807657','9052969020','9056023298','9163188168','9971739545','9354558019','9083146199','9052575849','9959181341','9357611942','9156448409','9178491573','9165706681','9052389787','9171500502'];
       $phones_inserted = ['9050719947','9055363997','9056023298','9060593052','9062222385','9071552789','9084911226','9088906243','9090058702','9156202667','9158531217','9159204255','9163499451','9169390121','9171177179','9171805577','9171824113','9178941176','9189644522','9196762145','9199227733','9208648296','9224745276','9263739381','9267676677','9272904855','9275014745','9275423665','9276519233','9277460474','9279383042','9301803159','9323371277','9354019072','9362071091','9463345131','9471402853','9484945701','9493306371','9505656891','9562940847','9664502496','9758540354','9776755864','9955071674','9979230582','9985383935','9988566990','9998804367'];
        foreach ($phones as $phone) {
           //s2存在的客户先跳过
            if (in_array($phone,$phones_inserted)){
                continue;
            }
           $s2_clm_customer = \DB::connection('clm_prod_ph')->table('clm_customer')->where('phone',$phone)->where('org','200000000002')->first();
           \DB::connection('clm_prod_ph')->beginTransaction();
           if ($s2_clm_customer) {
               \DB::connection('clm_prod_ph')->table('clm_customer')->where('phone',$phone)->where('org','200000000002')->delete();
           }
           //改变clm_prod_ph
           $s1_clm_customer = \DB::connection('clm_prod_ph')->table('clm_customer')->where('phone',$phone)
               ->where('org','000000000003')->first();
           //s1_clm没有也跳过
           if (!$s1_clm_customer) {
               echo 'clm_customer表不存在该s1客户'.$phone.PHP_EOL;
               continue;
           }
           $s1_clm_customer->org =  '200000000002';
           try {
               //s2存在的id_card客户先删除
               \DB::connection('clm_prod_ph')->table('clm_customer')->where('org','200000000002')->where('id_card',$s1_clm_customer->id_card)->delete();
               \DB::connection('clm_prod_ph')->table('clm_customer')->where('org','000000000003')->where('phone',$phone)->update(['org'=>'200000000002']);
               \DB::connection('clm_prod_ph')->table('clm_customer_group_relation')
                   ->insert(['group_id'=>9,'customer_id'=>$s1_clm_customer->id,'create_time'=>date('Y-m-d H:i:s'),'update_time'=>date('Y-m-d H:i:s')]);
               //user处理
               $s1_user = \DB::connection('u3_prod_u1')->table('user')->where('phone',$phone)->first();
               $s1_user->user_id = null;
               //s2有这个人先删除
               \DB::connection('s2_prod')->table('user')->where('phone',$phone)->delete();
               $s2_user_id = \DB::connection('s2_prod')->table('user')->insertGetId((array)$s1_user);
               //custome处理
               $s1_customers = \DB::connection('u3_prod_u1')->table('customer')->where('phone',$phone)->get()->toArray();
               foreach ($s1_customers as $k=>$s1_customer) {
                   $s1_customer->customer_id = null;
                   $s1_customer->user_id = $s2_user_id;
                   $s1_customer->job_position = 'test';
                   switch ($s1_customer->industry) {
                       case 'Transportation logistics/commerce':
                           $s1_customer->industry = 'Transportation/Logistics';
                           break;
                       case 'Finance/Banking':
                           $s1_customer->industry = 'Financial Services/Banking';
                           break;
                       case 'Medical/Pharmaceutical':
                           $s1_customer->industry = 'Medical/Pharmaceuticals';
                           break;
                       case 'IT related industries/communications':
                           $s1_customer->industry = 'IT/Communications';
                           break;
                       case 'Travel/Services':
                           $s1_customer->industry = 'Tourism/Culture and Sports Services';
                           break;
                       case 'energy':
                           $s1_customer->industry = 'Energy/Water Supply/Electricity/Gas';
                           break;
                       case 'Government/public utility':
                           $s1_customer->industry = 'Government/Community';
                           break;
                       case null:
                           $s1_customer->industry = 'others';
                           break;
                   }
                   switch ($s1_customer->contact_relation) {
                       case 'brother':
                       case null:
                           $s1_customer->contact_relation = 'brothers';
                           break;
                       case 'father':
                       case 'mother':
                       case 'father/mother in law':
                           $s1_customer->contact_relation = 'parents';
                           break;
                       case 'husband/wife':
                           $s1_customer->contact_relation = 'spouse';
                           break;
                       case 'sister':
                           $s1_customer->contact_relation = 'sisters';
                   }
                   switch ($s1_customer->contact_relation_code) {
                       case 'FATHER':
                       case 'MOTHER':
                           $s1_customer->contact_relation_code = 'PARENT';
                           break;
                       case null:
                           $s1_customer->contact_relation_code = 'BROTHER';
                   }
                   switch ($s1_customer->incumbency) {
                       case 'HOW_LONG_STAYING_03':
                           $s1_customer->incumbency = 'INCUMBENCY_03';
                           break;
                       case 'HOW_LONG_STAYING_02':
                           $s1_customer->incumbency = 'INCUMBENCY_02';
                           break;
                       case 'HOW_LONG_STAYING_04':
                           $s1_customer->incumbency = 'INCUMBENCY_04';
                           break;
                       case null:
                       case 'HOW_LONG_STAYING_01':
                           $s1_customer->incumbency = 'INCUMBENCY_01';
                   }
                   switch ($s1_customer->education_code) {
                       case null:
                           $s1_customer->education_code = 'EDUCATION_07';
                   }
                   switch ($s1_customer->education) {
                       case null:
                           $s1_customer->education = 'Elementary';
                   }
                   $s1_customers[$k] = (array)$s1_customer;
               }
               \DB::connection('s2_prod')->table('customer')->insert($s1_customers);
               $s2_customers = \DB::connection('s2_prod')->table('customer')->where('phone',$phone)
                   ->orderByDesc('customer_id')->pluck('customer_id')->toArray();
               //处理apply申请订单
               $s1_applies = \DB::connection('u3_prod_u1')->table('apply')->where('phone',$phone)->get()->toArray();
               foreach ($s1_applies as $k=>$s1_apply) {
                   $s1_apply->user_id = $s2_user_id;
                   $s1_apply->customer_id = $s2_customers[0];
                   $s1_apply->repayment_state = 'SETTLE';
                   $s1_apply->update_time = date('Y-m-d H:i:s');
                   $s1_applies[$k] = (array)$s1_apply;
               }
               \DB::connection('s2_prod')->table('apply')->insert($s1_applies);
               //处理approve审批
               $s1_approves = \DB::connection('u3_prod_u1')->table('approve')->where('phone',$phone)->orderByDesc('create_time')->get()->toArray();
               foreach ($s1_approves as $k=>$s1_approve) {
                   if (0==$k) {
                       $s1_approve->customer_id = $s2_customers[0];
                   }else {
                       $s1_approve->customer_id = null;
                   }
                   $s1_approves[$k] = (array)$s1_approve;
               }
               \DB::connection('s2_prod')->table('approve')->insert($s1_approves);
               //处理contract合约
               $s1_contracts = \DB::connection('u3_prod_u1')->table('contract')->where('phone',$phone)->get()->toArray();
               foreach ($s1_contracts as $k=>$s1_contract) {
                   $s1_contracts[$k] = (array)$s1_contract;
               }
               \DB::connection('s2_prod')->table('contract')->insert($s1_contracts);
               //处理图片
               $s1_apply_ids = \DB::connection('u3_prod_u1')->table('apply')->where('phone',$phone)->pluck('apply_id')->toArray();
               $s1_images = \DB::connection('u3_prod_u1')->table('images')->whereIn('apply_id',$s1_apply_ids)->get()->toArray();
               foreach ($s1_images as $k=>$s1_image){
                   $s1_image->image_id = null;
                   $s1_image->customer_id = $s2_customers[0];
                   $s1_images[$k] = (array)$s1_image;
               }
               \DB::connection('s2_prod')->table('images')->insert($s1_images);
               \DB::connection('clm_prod_ph')->commit();
//               echo "End".$phone.PHP_EOL;
           }catch (\Exception $exception) {
               \DB::connection('clm_prod_ph')->rollBack();
               echo '错误地方--'.$exception->getMessage().'--'.$exception->getLine();
               echo "导入出错,回滚数据----End".$phone.PHP_EOL;
               exit;
           }
//           sleep(1);
       }
    }

    private function del($param)
    {
        $phones = ['9485511232','9558802963','9428179677','9650517319'];
        foreach ($phones as $phone){
            //处理图片
            $s1_apply_ids = \DB::connection('s2_prod')->table('apply')->where('phone',$phone)->pluck('apply_id')->toArray();
            \DB::connection('s2_prod')->beginTransaction();
            try {
                \DB::connection('s2_prod')->table('images')->whereIn('apply_id',$s1_apply_ids)->delete();
                \DB::connection('s2_prod')->table('contract')->where('phone',$phone)->delete();
                \DB::connection('s2_prod')->table('approve')->where('phone',$phone)->delete();
                \DB::connection('s2_prod')->table('apply')->where('phone',$phone)->delete();
                \DB::connection('s2_prod')->table('customer')->where('phone',$phone)->delete();
                \DB::connection('s2_prod')->table('user')->where('phone',$phone)->delete();
                \DB::connection('clm_prod_ph')->table('clm_customer')->where('org','200000000002')->where('phone',$phone)->update(['org'=>'000000000003']);
                $s1_clm_customer = \DB::connection('clm_prod_ph')->table('clm_customer')->where('phone',$phone)
                    ->where('org','000000000003')->first();
                \DB::connection('clm_prod_ph')->table('clm_customer_group_relation')->where('group_id',9)
                    ->where('customer_id',$s1_clm_customer->id)->delete();
                \DB::connection('s2_prod')->commit();
                echo "delete".$phone.PHP_EOL;
            }catch (\Exception $exception) {
                \DB::connection('s2_prod')->rollBack();
                echo '错误地方--'.$exception->getMessage().'--'.$exception->getLine();
                echo "删除出错,回滚数据----End".$phone.PHP_EOL;
                exit;
            }
        }
        exit;
    }
}
