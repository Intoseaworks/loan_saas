<?php

namespace Common\Redis;

/**
 * Class RedisKey
 * @package App\Redis
 * @author ChangHai Zhan
 */
class RedisKey
{
    /**-----Oa start-------*/
    /**
     * 管理员前缀
     */
    const PREFIX_STAFF = 'staff:';
    /**
     * 管理员id 值：ticket
     */
    const STAFF_ID = self::PREFIX_STAFF . 'id:';
    /**
     * 管理员ticket 值：用户信息
     */
    const STAFF_TICKET = self::PREFIX_STAFF . 'ticket:';

    const PREFIX_ACCESS_TOKEN = 'access_token:';

    const PREFIX_REFRESH_TOKEN = 'refresh_token:';

    const PREFIX_CAPTCHA = 'captcha:';

    const DING_ACCESS_TOKEN = 'ding_access_token:';

    /**----------终端api-----------*/
    const API_PREFIX = 'api:';
    /**
     * api sms captcha
     */
    const SMS_CAPTCHA = self::API_PREFIX . 'sms:captcha:';

    /**
     * 用户操作原子锁
     */
    const API_LOCK = self::API_PREFIX . 'lock:';

    /**----------审批-----------*/
    /** 人审 */
    const APPROVE_MANUAL_SET = 'approve:manual:set:';

    /**----------放款-----------*/
    /** 人工放款 */
    const REMIT_MANUAL_LOCK = 'remit:manual:lock:';

    /** 通知前缀 */
    const NOTICE_PREFIX = 'notice:';

    /**----------渠道-----------*/
    /** 渠道统计 */
    const CHANNEL_COUNT_PREFIX = 'channel:count:';

    /**----------催收统计-----------*/
    const COLLECTION_STATISTICS = 'collection:statistics:';

    const COMMON = 'common:';


    const PREFIX_CASHNOW = 'cashnow:';

    /**----- 订单 -------*/
    /**
     * 订单签约
     */
    const TEST = self::PREFIX_CASHNOW . 'test:';

    /**----- 商户 -------*/
    /**----------商户 app_id 归属 merchant_id-----------*/
    const MERCHANT_APP_BELONG = 'merchant:app_belong:';


    /**------------ 缓存 Cache --------------*/
    const PREFIX_CACHE = 'cache:';

    /** config 缓存 */
    const CACHE_CONFIG = self::PREFIX_CACHE . 'config:';

    const PREFIX_APP = 'app:';

    const PREFIX_APP_USER_TOKEN = self::PREFIX_APP . 'token:user_id:';

    const PREFIX_APP_USER_UUID = self::PREFIX_APP . 'uuid:user_id:';


    /**-------------------------------------------- Risk 风控 -------------------------------------------------*/
    const PREFIX_RISK = 'risk:';
    // 用户数据上传
    const RISK_SEND_TASK_DATA = self::PREFIX_RISK . 'send_task_data:';
    // lock
    const RISK_LOCK = self::PREFIX_RISK . 'lock:';
}
