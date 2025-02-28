<?php

namespace Common\Models\Third;

use Common\Traits\Model\StaticModel;
use Illuminate\Database\Eloquent\Model;

/**
 * Common\Models\Third\ThirdDataAppsflyer
 *
 * @property int $id 交易记录自增长id
 * @property string|null $idfv
 * @property string|null $device_category
 * @property string|null $af_sub1
 * @property string|null $customer_user_id
 * @property string|null $is_lat
 * @property string|null $contributor_2_af_prt
 * @property string|null $bundle_id
 * @property string|null $gp_broadcast_referrer
 * @property string|null $contributor_2_touch_time
 * @property string|null $contributor_3_touch_type
 * @property string|null $event_source
 * @property string|null $af_cost_value
 * @property string|null $contributor_1_match_type
 * @property string|null $app_version
 * @property string|null $contributor_3_af_prt
 * @property string|null $custom_data
 * @property string|null $contributor_2_touch_type
 * @property string|null $gp_install_begin
 * @property string|null $city
 * @property string|null $amazon_aid
 * @property string|null $gp_referrer
 * @property string|null $af_cost_model
 * @property string|null $af_c_id
 * @property string|null $attributed_touch_time_selected_timezone
 * @property string|null $selected_currency
 * @property string|null $app_name
 * @property string|null $install_time_selected_timezone
 * @property string|null $postal_code
 * @property string|null $wifi
 * @property string|null $install_time
 * @property string|null $operator
 * @property string|null $attributed_touch_type
 * @property string|null $af_attribution_lookback
 * @property string|null $keyword_match_type
 * @property string|null $af_adset_id
 * @property string|null $device_download_time_selected_timezone
 * @property string|null $contributor_2_media_source
 * @property string|null $contributor_2_match_type
 * @property string|null $api_version
 * @property string|null $attributed_touch_time
 * @property string|null $revenue_in_selected_currency
 * @property string|null $is_retargeting
 * @property string|null $country_code
 * @property string|null $gp_click_time
 * @property string|null $contributor_1_af_prt
 * @property string|null $match_type
 * @property string|null $appsflyer_id
 * @property string|null $dma
 * @property string|null $http_referrer
 * @property string|null $af_sub5
 * @property string|null $af_prt
 * @property string|null $event_revenue_currency
 * @property string|null $store_reinstall
 * @property string|null $install_app_store
 * @property string|null $media_source
 * @property string|null $deeplink_url
 * @property string|null $campaign
 * @property string|null $af_keywords
 * @property string|null $region
 * @property string|null $cost_in_selected_currency
 * @property string|null $event_value
 * @property string|null $ip
 * @property string|null $oaid
 * @property string|null $event_time
 * @property string|null $is_receipt_validated
 * @property string|null $contributor_1_campaign
 * @property string|null $af_sub4
 * @property string|null $imei
 * @property string|null $contributor_3_campaign
 * @property string|null $event_revenue_usd
 * @property string|null $af_sub2
 * @property string|null $original_url
 * @property string|null $contributor_2_campaign
 * @property string|null $android_id
 * @property string|null $contributor_3_media_source
 * @property string|null $af_adset
 * @property string|null $af_ad
 * @property string|null $state
 * @property string|null $network_account_id
 * @property string|null $device_type
 * @property string|null $idfa
 * @property string|null $retargeting_conversion_type
 * @property string|null $af_channel
 * @property string|null $af_cost_currency
 * @property string|null $contributor_1_media_source
 * @property string|null $keyword_id
 * @property string|null $device_download_time
 * @property string|null $contributor_1_touch_type
 * @property string|null $af_reengagement_window
 * @property string|null $af_siteid
 * @property string|null $language
 * @property string|null $app_id
 * @property string|null $contributor_1_touch_time
 * @property string|null $event_revenue
 * @property string|null $af_ad_type
 * @property string|null $carrier
 * @property string|null $event_name
 * @property string|null $af_sub_siteid
 * @property string|null $advertising_id
 * @property string|null $os_version
 * @property string|null $platform
 * @property string|null $af_sub3
 * @property string|null $contributor_3_match_type
 * @property string|null $selected_timezone
 * @property string|null $af_ad_id
 * @property string|null $contributor_3_touch_time
 * @property string|null $user_agent
 * @property string|null $is_primary_attribution
 * @property string|null $sdk_version
 * @property string|null $event_time_selected_timezone
 * @property string|null $all_data
 * @property string|null $compaign_type
 * @property \Illuminate\Support\Carbon $created_at 创建时间
 * @property \Illuminate\Support\Carbon $updated_at 修改时间
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer query()
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAdvertisingId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAdId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAdType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAdset($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAdsetId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfAttributionLookback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfCId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfChannel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfCostCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfCostModel($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfCostValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfKeywords($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfPrt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfReengagementWindow($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSiteid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSub1($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSub2($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSub3($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSub4($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSub5($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAfSubSiteid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAllData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAmazonAid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAndroidId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereApiVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAppName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAppVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAppsflyerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAttributedTouchTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAttributedTouchTimeSelectedTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereAttributedTouchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereBundleId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCampaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCarrier($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCompaignType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1AfPrt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1Campaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1MatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1MediaSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1TouchTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor1TouchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2AfPrt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2Campaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2MatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2MediaSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2TouchTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor2TouchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3AfPrt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3Campaign($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3MatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3MediaSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3TouchTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereContributor3TouchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCostInSelectedCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCustomData($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereCustomerUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDeeplinkUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDeviceCategory($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDeviceDownloadTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDeviceDownloadTimeSelectedTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDeviceType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereDma($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventRevenue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventRevenueCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventRevenueUsd($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventTimeSelectedTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereEventValue($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereGpBroadcastReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereGpClickTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereGpInstallBegin($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereGpReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereHttpReferrer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIdfa($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIdfv($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereImei($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereInstallAppStore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereInstallTime($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereInstallTimeSelectedTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIp($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIsLat($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIsPrimaryAttribution($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIsReceiptValidated($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereIsRetargeting($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereKeywordId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereKeywordMatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereLanguage($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereMatchType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereMediaSource($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereNetworkAccountId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereOaid($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereOperator($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereOriginalUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereOsVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer wherePlatform($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer wherePostalCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereRegion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereRetargetingConversionType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereRevenueInSelectedCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereSdkVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereSelectedCurrency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereSelectedTimezone($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereStoreReinstall($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ThirdDataAppsflyer whereWifi($value)
 * @mixin \Eloquent
 */
class ThirdDataAppsflyer extends Model
{
    use StaticModel;

    protected $table = 'third_data_appsflyer';
    protected $fillable = [];
    protected $guarded = [];
    
    protected static function boot() {
        parent::boot();

        static::setMerchantIdBootScope();
    }

    public function textRules()
    {
        return [];
    }

}
