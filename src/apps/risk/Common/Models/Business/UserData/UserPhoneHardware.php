<?php

namespace Risk\Common\Models\Business\UserData;

use Risk\Common\Models\Business\BusinessBaseModel;

/**
 * Risk\Common\Models\Business\UserData\UserPhoneHardware
 *
 * @property int $id 未使用内存空间
 * @property int $app_id
 * @property int $user_id 用户id
 * @property string|null $imei Imei号
 * @property string|null $phone_type 手机型号
 * @property string|null $system_version 安卓版本
 * @property string|null $system_version_code 版本号
 * @property string|null $imsi Imsi号
 * @property string|null $phone_brand 手机品牌
 * @property string|null $ext_info 手机硬件扩展信息
 * @property string|null $created_at 添加时间
 * @property string $total_disk 内存空间总大小
 * @property string $used_disk 已使用内存空间
 * @property string $free_disk 未使用内存空间
 * @property string $is_vpn_used 是否使用vpn
 * @property string $is_wifi_proxy 是否使用wifi
 * @property string|null $pixels 手机摄像头
 * @property string|null $sim_phone_num SIM卡手机号
 * @property string|null $net_type 网络类型，上传相关字段信息：如4G，3G，wifi等
 * @property string|null $net_type_original 原始网络类型
 * @property string|null $is_double_sim 是否双卡双待
 * @property string|null $wifi_ip_address wifiIP
 * @property string|null $sync_time
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware query()
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereExtInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereFreeDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereImsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsDoubleSim($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsVpnUsed($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsWifiProxy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNetType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNetTypeOriginal($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware wherePhoneBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware wherePhoneType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware wherePixels($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSimPhoneNum($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSyncTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSystemVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSystemVersionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereTotalDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUsedDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereWifiIpAddress($value)
 * @mixin \Eloquent
 */
class UserPhoneHardware extends BusinessBaseModel
{
    public static $validate = [
        'data' => 'array',
        'data.*.id' => 'required|numeric', // 记录列ID
        'data.*.imei' => 'nullable|string', // Imei号
        'data.*.phone_type' => 'nullable|string', // 手机型号 COL-AL10、MI 8 Lite、MI 8、Redmi 4A 等等
        'data.*.os_version' => 'nullable|string', // 安卓版本 9、8.0.0、10 等等
        'data.*.model' => 'nullable|string', // 设备型号
        'data.*.advertising_id' => 'nullable|string', // advertising_id号
        'data.*.phone_brand' => 'nullable|string', // 手机品牌 Xiaomi、HONOR
        'data.*.ext_info' => 'nullable|string', // 手机硬件扩展信息 json字符串。[uuid,mac地址,ip地址,androidId]  {"uuid":"2369ea50d4e719fdee088da3a2193f9e","addressMac":"02:00:00:00:00:00","addressIP":"","androidId":"dc63dbb4f7f4663d"}
        'data.*.created_at' => 'nullable|date', // 记录添加时间
        'data.*.total_rom' => 'nullable|string', // 内存空间总大小。例：'53859.38 MB'
        'data.*.used_rom' => 'nullable|string', // 已使用内存空间 20142.05 MB
        'data.*.free_rom' => 'nullable|string', // 未使用内存空间 33717.34 MB
        'data.*.is_agent' => 'nullable|string', // 是否使用vpn
        'data.*.is_wifi_proxy' => 'nullable|string', // 是否使用wifi
        'data.*.pixels' => 'nullable|string', // 手机摄像头
        'data.*.native_phone' => 'nullable|string', // SIM卡手机号
        'data.*.net_type' => 'nullable|string', // 网络类型，上传相关字段信息：如4G，3G，wifi等
        'data.*.net_type_original' => 'nullable|string', // 原始网络类型
        'data.*.is_double_sim' => 'nullable|string', // 是否双卡双待
        'data.*.wifi_ip' => 'nullable|string', // wifi ip 地址
    ];
    public $timestamps = false;
    protected $table = 'data_user_phone_hardware';
    protected $fillable = [
        'id',
        'user_id',
        'order_id',
        'phone',
        'native_phone',
        'os',
        'os_version',
        'total_rom',
        'used_rom',
        'free_rom',
        'total_ram',
        'used_ram',
        'free_ram',
        'cookie_id',
        'imei',
        'phone_brand',
        'device_name',
        'model',
        'product',
        'advertising_id',
        'intranet_ip',
        'is_agent',
        'is_root',
        'is_simulator',
        'system_language',
        'time_zone',
        'system_time',
        'screen_brightness',
        'nfc_function',
        'number_of_photos',
        'number_of_messages',
        'number_of_call_records',
        'number_of_videos',
        'number_of_applications',
        'number_of_songs',
        'system_build_time',
        'iccid',
        'persistent_deviceId',
        'request_ip',
        'resolution',
        'resolution_high',
        'resolution_width',
        'boot_time',
        'up_time',
        'wifi_mac',
        'ssid',
        'wifi_ip',
        'cell_ip',
        'meid',
        'sid',
        'baseband_version',
        'battery_status',
        'battery_power',
        'network_operators',
        'signal_strength',
        'mobile_network_type',
        'user_agent',
        'ext_info',
        'created_at',
        'mnc',
        'mcc',
        'carrier',
        'dns',
        'canvas',
        'radio_type',
    ];

    public static function getByUser($userId)
    {
        return UserPhoneHardware::query()
            ->where('user_id', $userId)
            ->latest('id')
            ->first();
    }

    protected static function boot()
    {
        parent::boot();

        static::setMerchantIdBootScope();
    }
}
