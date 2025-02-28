<?php

namespace Api\Models\User;

use Api\Models\BankCard\BankCardPeso;
use Api\Models\Order\Order;

/**
 * Api\Models\User\User
 *
 * @property int $id 用户id
 * @property string|null $telephone 手机号码
 * @property string|null $fullname 姓名
 * @property string|null $id_card_no 身份证号
 * @property int|null $status 状态 1正常 2禁用
 * @property int|null $created_at 注册时间
 * @property int|null $updated_at 更新时间
 * @property string|null $platform 下载平台
 * @property string|null $client_id 注册终端 Android/ios/h5
 * @property string|null $channel_id 注册渠道
 * @property int $quality 新老用户 0新用户 1老用户
 * @property string $app_version 注册版本
 * @property-read \Common\Models\BankCard\BankCardPeso $bankCard
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\BankCard\BankCardPeso[] $bankCards
 * @property-read \Common\Models\Channel\Channel $channel
 * @property-read \Api\Models\Order\Order $order
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\Order\Order[] $orders
 * @property-read \Common\Models\User\UserBlack $userBlack
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\User\UserContact[] $userContact
 * @property-read \Common\Models\User\UserInfo $userInfo
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereChannelId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereClientId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereFullname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereIdCardNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereQuality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereTelephone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereUpdatedAt($value)
 * @mixin \Eloquent
 * @property-read \Illuminate\Database\Eloquent\Collection|\Common\Models\User\UserContact[] $userContacts
 * @property string|null $api_token 令牌token
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereApiToken($value)
 * @property string|null $quality_time 转老用户时间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\User\User orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereQualityTime($value)
 * @property int $merchant_id merchant_id
 * @property int $app_id app_id
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Api\Models\User\User whereMerchantId($value)
 * @property string|null $card_type 证件卡号类型
 * @property string|null $password
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCardType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @property string|null $firstname firstname
 * @property string|null $middlename middlename
 * @property string|null $lastname lastname
 * @method static \Illuminate\Database\Eloquent\Builder|User whereFirstname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLastname($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereMiddlename($value)
 */
class User extends \Common\Models\User\User
{

    const SCENARIO_ID_CARD = 'create_from_id_front';

    const SCENARIO_DETAIL = 'detail';
    const SCENARIO_HOME = 'home';
    const SCENARIO_IDENTITY = 'identity';

    public function safes()
    {
        return [
            self::SCENARIO_ID_CARD => [
                'fullname',
                'id_card_no',
            ],
        ];
    }

    public function order($class = Order::class)
    {
        return parent::order($class);
    }

    public function bankCard($class = BankCardPeso::class)
    {
        return parent::bankCard($class);
    }

    public function texts()
    {
        return [
            self::SCENARIO_DETAIL => [
                'id',
                'telephone',
                'fullname',
                'id_card_no',
                'quality',
            ],
            self::SCENARIO_HOME => [
                'id',
                'telephone',
                'fullname',
                'id_card_no',
                'card_type',
                'quality',
            ]
        ];
    }

    public function textRules()
    {
        return [
            'function' => [
                'id' => function () {
                    if ($this->scenario == self::SCENARIO_HOME) {

                    }
                }
            ],
        ];
    }

    public function userInfo($class = UserInfo::class)
    {
        return parent::userInfo($class);
    }
}
