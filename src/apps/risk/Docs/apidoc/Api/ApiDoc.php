<?php

namespace Risk\Docs\apidoc\Api;

/**
 * 风控接口
 * @package Risk\Docs\apidoc\Api
 */
interface ApiDoc
{
    /**
     * @api {post} /api/risk/task/start_task 创建机审任务
     * @apiGroup Api
     * @apiVersion 1.0.0
     * @apiDescription 创建机审任务
     *
     * @apiParam {String} user_id  用户唯一标识
     * @apiParam {String} order_id  申请订单ID
     * @apiParam {String} notice_url  机审结果回调通知地址
     *
     * @apiSuccess (返回值) {int} status 请求状态 18000:成功 13000:失败 **全局状态**
     * @apiSuccess (返回值) {String} msg 信息
     * @apiSuccess (返回值) {Array} data 返回数据
     * @apiSuccess (返回值) {String} data.taskNo 机审任务编号
     * @apiSuccess (返回值) {Array} data.required 机审所需数据项
     * @apiSuccess (返回值) {String} data.required.-- 机审所需数据项
     *
     * @apiSuccessExample {json} 成功返回:
     * {
     * "code":18000,
     * "msg":"ok",
     * "data":{
     * "taskNo":"TASK_D523085A3FA5C2B7",
     * "required":[
     * "BANK_CARD",
     * "COLLECTION_RECORD",
     * "ORDER",
     * "ORDER_DETAIL",
     * "REPAYMENT_PLAN",
     * "USER",
     * "USER_AUTH",
     * "USER_CONTACT",
     * "USER_INFO",
     * "USER_THIRD_DATA",
     * "USER_WORK",
     * "USER_APPLICATION",
     * "USER_CONTACTS_TELEPHONE",
     * "USER_PHONE_HARDWARE",
     * "USER_PHONE_PHOTO",
     * "USER_POSITION"
     * ]
     * }
     * }
     */

    /**
     * @api {post} /api/risk/task/exec_task 执行机审任务
     * @apiGroup Api
     * @apiVersion 1.0.0
     * @apiDescription 执行机审任务。若还有机审任务对应的用户数据上传未完善，会返回status=CREATE的状态。成功发起执行则会返回status=WAITING状态，需等待机审结果回调。
     *
     * @apiParam {String} task_no  机审任务编号。注意上传数据的 [ORDER] 和 [USER] 数据必须包含创建任务时的ID对应的记录
     *
     * @apiSuccess (返回值) {int} status 请求状态 18000:成功 13000:失败 **全局状态**
     * @apiSuccess (返回值) {String} msg 信息
     * @apiSuccess (返回值) {Array} data 返回数据
     * @apiSuccess (返回值) {Array} data.status 状态。若还有机审任务对应的用户数据上传未完善，会返回 CREATE 的状态。成功发起执行则会返回 WAITING 状态，需等待机审结果回调。
     * @apiSuccess (返回值) {Array} data.required 机审所需数据项
     * @apiSuccess (返回值) {Array} data.required.-- 机审所需数据项
     *
     * @apiSuccessExample {json} 数据上传未完善:
     * {
     * "code":18000,
     * "msg":"required data is incomplete",
     * "data":{
     * "status":"CREATE",
     * "required":[
     * "COLLECTION_RECORD",
     * "ORDER",
     * "ORDER_DETAIL",
     * "REPAYMENT_PLAN",
     * "USER",
     * "USER_AUTH",
     * "USER_CONTACT",
     * "USER_INFO",
     * "USER_THIRD_DATA"
     * ]
     * }
     * }
     *
     * @apiSuccessExample {json} 成功发起执行:
     * {"code":18000,"msg":"ok","data":{"status":"WAITING"}}
     */
}
