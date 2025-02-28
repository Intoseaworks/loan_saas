<?php

namespace Common\Models\UserData;

use Common\Traits\Model\StaticModel;
use Common\Utils\Data\StringHelper;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\UserData\UserPhoneHardware
 *
 * @property int $id 未使用内存空间
 * @property int $user_id 用户id
 * @property string|null $imei Imei号
 * @property string|null $phone_type 手机型号
 * @property string|null $os_version 安卓版本
 * @property string|null $model 设备型号
 * @property string|null $advertising_id 广告ID
 * @property string|null $phone_brand 手机品牌
 * @property string|null $ext_info 手机硬件扩展信息
 * @property string|null $created_at 添加时间
 * @property string $total_rom 内存空间总大小
 * @property string $used_rom 已使用内存空间
 * @property string $free_rom 未使用内存空间
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware query()
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereExtInfo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereFreeDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereImsi($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware wherePhoneBrand($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware wherePhoneType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereSystemVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereSystemVersionCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereTotalDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereUsedDisk($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\Common\Models\UserData\UserPhoneHardware whereUserId($value)
 * @mixin \Eloquent
 * @property int|null $order_id 申请流水号
 * @property string|null $os 客户端操作系统：安卓：android； 苹果ios：ios； h5：h5-android、h5-ios、h5-windows
 * @property string|null $total_ram 总运存大小
 * @property string|null $used_ram 已使用运存大小
 * @property string|null $free_ram 剩余可用运存大小
 * @property string|null $cookie_id
 * @property string|null $native_phone SIM卡本地手机号
 * @property string|null $device_name 设备名称
 * @property string|null $product 产品的内部代号
 * @property string|null $intranet_ip 内网IP
 * @property string $is_agent 是否代理
 * @property string|null $is_root 是否root
 * @property string|null $is_simulator 是否模拟器
 * @property string|null $system_language 系统语言
 * @property string|null $time_zone 时区
 * @property string|null $system_time 系统时间
 * @property string|null $screen_brightness 屏幕亮度
 * @property string|null $nfc_function 是否有NFC功能
 * @property int|null $number_of_photos 照片数量
 * @property int|null $number_of_messages 短信数量
 * @property int|null $number_of_call_records 通话记录数量
 * @property int|null $number_of_videos 视频数量
 * @property int|null $number_of_applications 应用程序数量
 * @property int|null $number_of_songs 歌曲数量
 * @property string|null $system_build_time 系统构建时间
 * @property string|null $iccid sim卡序列号
 * @property string|null $persistent_device_id 永久ID
 * @property string|null $request_ip IPV6
 * @property string|null $resolution 屏幕分辨率
 * @property string|null $resolution_high 分辨率高
 * @property string|null $resolution_width 分辨率宽
 * @property int|null $boot_time 开机时的时间戳(毫秒级)
 * @property string|null $up_time 从开机到当前的时长(包含休眠时间)
 * @property string|null $wifi_mac wifimac
 * @property string|null $ssid 当前连接的无线网络名称
 * @property string|null $wifi_ip wifi连接的IP
 * @property string|null $cell_ip 移动网络Ip
 * @property string|null $meid MEID
 * @property string|null $sid 序列号
 * @property string|null $baseband_version 基带版本
 * @property string|null $battery_status 电池状态
 * @property string|null $battery_power 电池电量
 * @property string|null $network_operators 网络运营商
 * @property string|null $signal_strength 信号强度
 * @property string|null $mobile_network_type 移动网络类型
 * @property string|null $user_agent 浏览器UA
 * @property string|null $mnc mnc
 * @property string|null $mcc mcc
 * @property string|null $carrier carrier
 * @property string|null $dns DNS地址
 * @property string|null $canvas 帆布指纹
 * @property string|null $radio_type 网络制式
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereAdvertisingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereBasebandVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereBatteryPower($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereBatteryStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereBootTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereCanvas($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereCarrier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereCellIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereCookieId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereDeviceName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereDns($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereFreeRam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereFreeRom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIccid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIntranetIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsRoot($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereIsSimulator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereMcc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereMeid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereMnc($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereMobileNetworkType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNativePhone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNetworkOperators($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNfcFunction($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfCallRecords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfMessages($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfPhotos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfSongs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereNumberOfVideos($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereOs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereOsVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware wherePersistentDeviceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereProduct($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereRadioType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereRequestIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereResolution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereResolutionHigh($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereResolutionWidth($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereScreenBrightness($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSignalStrength($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSsid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSystemBuildTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSystemLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereSystemTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereTimeZone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereTotalRam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereTotalRom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUpTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUsedRam($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUsedRom($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereWifiIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UserPhoneHardware whereWifiMac($value)
 */
class UserPhoneHardware extends Model
{
    use StaticModel;

    protected $table = 'user_phone_hardware';
    protected $fillable = [
        'id',
        'user_id',
        'order_id', //申请流水号
        'native_phone', //SIM卡本地手机号
        'os', //客户端操作系统：安卓：android； 苹果ios：ios； h5：h5-android、h5-ios、h5-windows
        'os_version', //系统版本号
        'total_rom', //内存空间总大小
        'used_rom', //已使用内存空间
        'free_rom', //未使用内存空间
        'total_ram', //总运存大小
        'used_ram', //已使用运存大小
        'free_ram', //剩余可用运存大小
        'cookie_id',
        'imei',
        'phone_brand', //手机品牌
        'device_name', //设备名称
        'model', //设备型号
        'product', //产品的内部代号
        'advertising_id', //广告ID
        'intranet_ip', //内网IP
        'is_agent', //是否代理
        'is_root', //是否root
        'is_simulator', //是否模拟器
        'system_language', //系统语言
        'time_zone', //时区
        'system_time', //系统时间
        'screen_brightness', //屏幕亮度
        'nfc_function', //是否有NFC功能
        'number_of_photos', //照片数量
        'number_of_messages', //短信数量
        'number_of_call_records', //通话记录数量
        'number_of_videos', //视频数量
        'number_of_applications', //应用程序数量
        'number_of_songs', //歌曲数量
        'system_build_time', //系统构建时间
        'iccid', //sim卡序列号
        'persistent_device_id', //永久ID
        'request_ip', //IPV6
        'resolution', //屏幕分辨率
        'resolution_high', //分辨率高
        'resolution_width', //分辨率宽
        'boot_time', //开机时的时间戳(毫秒级)
        'up_time', //从开机到当前的时长(包含休眠时间)
        'wifi_mac', //wifimac
        'ssid', //当前连接的无线网络名称
        'wifi_ip', //wifi连接的IP
        'cell_ip', //移动网络Ip
        'meid', //MEID
        'sid', //序列号
        'baseband_version', //基带版本
        'battery_status', //电池状态
        'battery_power', //电池电量
        'network_operators', //网络运营商
        'signal_strength', //信号强度
        'mobile_network_type', //移动网络类型
        'user_agent', //浏览器UA
        'ext_info', //手机硬件扩展信息
        'created_at',
        'mnc',
        'mcc',
        'carrier', //carrier
        'dns', //DNS地址
        'canvas', //帆布指纹
        'radio_type', //网络制式
    ];
    protected $guarded = [];
    protected $hidden = [];

    public $timestamps = false;

    public function add($data)
    {
        $item = array_only($data, $this->fillable);
        return $this->create($item);
    }

    public function batchAdd($data)
    {
        foreach ($data as &$item) {
            $item = array_only($item, $this->fillable);
            /** 格式化sim卡手机号 */
            $item['native_phone'] = StringHelper::formatTelephone(array_get($item, 'native_phone'));
            $item['created_at'] = date('Y-m-d H:i:s');
        }

        return $this->insertIgnore($data);

//        $this->clearRepeatData($userId);
//
//        return $this->insert($data);

    }

    public function clearRepeatData($userId)
    {
        return self::query()->where('user_id', $userId)->delete();
    }
}
