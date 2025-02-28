<?php

namespace Api\Controllers\Callback;

use Api\Services\Order\OrderServer;
use Common\Jobs\ContractByEsignPdfJob;
use Common\Models\Order\OrderSignDoc;
use Common\Response\ServicesApiBaseController;
use Common\Utils\DingDing\DingHelper;
use Common\Utils\Host\HostHelper;
use Common\Utils\MerchantHelper;
use Illuminate\Support\Facades\DB;

class AadhaarController extends ServicesApiBaseController
{
    // esign 能测通的aadhaar账号 877817398339
    // esign 直接失败的aadhaar账号 401086899529
    protected $failedEsignResponse = '{"id":"53fed57b-9a45-4948-9983-22c1e380995c","response_timestamp":"2019-08-21T07:30:50.230Z","transaction_status":17,"public_ip":"157.48.239.201","signer_consent":"Y","request_medium":"W","current_document":1,"signed_document_count":0,"documents":[{"id":"a6edbf0c-14d9-4626-b7c9-d832656c000a","index":1,"doc_info":"Formal signing of the order","type":"pdf","dynamic_url":"https:\/\/secured-esign-pdf-storage-prod.s3.ap-southeast-1.amazonaws.com\/a6edbf0c-14d9-4626-b7c9-d832656c000a?AWSAccessKeyId=ASIAQC54FU6IENOYOQFE&Expires=1566373541&Signature=ZoOUZJAHUqohDWhqBF1UZSghicM%3D&response-content-disposition=attachment%3B%20filename%20%3D%22a6edbf0c-14d9-4626-b7c9-d832656c000a.pdf%22&x-amz-security-token=AgoJb3JpZ2luX2VjEB0aCmFwLXNvdXRoLTEiRjBEAh80QO6GdPXVSVeFCHfXqQ0LunsRUakSkzZxl1LJ44fxAiEAvsOedBCOiEOlvm0xyiEy56gTNK9BtIbqHV%2B%2F2lTi0YMq5QMItv%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FARABGgwwMDYzMDAyMTUxODQiDCL9s%2FUg7s1M1dN%2BZCq5AwwXEVHoctglXasfMNy85te%2FDTQ5rewXcZc5mPS85GuhDSNWCVlF7UonVmVWHoLAHjNYFPuJgNcPleqvEOrahziu04bbrLFuN8P86sNEiBpLnXUeGfNcaMdQMMnDP%2F1YOWZa4x4u2oKYk7TWO9CXWKr45NIXQYYjC93oGdsTFQ8rszWZ%2F8FcgVgkrBr%2BMAgnfwqq09H%2B%2Bhz4BWVo2yeKIB1wxKTl36VLLSqjOzUFOckNn3l2bQDW4ldVzqg%2BwzzsEUzqUQoedUhVENIpucwlrYM9bmRWR5okUg5WC7FpSX1BC5aJ%2BjaFsjzqdefYnwpld1DVX4nnaU7WRlhq0q8sgWvUamlz0ZXFOq0ChEf6AYRq7uvhHbvTzM%2BrlAu7LgVYYIOjCAOf507pObiS1mw98Bvm8C3YFYNWhGib4XM46IR7FMd4ySp1v1ZGhyNAfZhKqgaXm5rNpwyltiqEwFANU8%2FzdRFro2gKneBYurxzUDp9urknAflyNe7SO%2F6eWjJ7JOXftJND6An%2FggFBH%2BVuvZCSU%2BFNbSwSzmXjl8CcAlMFZej4oWDj9SSUZ00XuGZHZB3TXEebjef%2FjDDzlfPqBTq1AcMAuz3EFt851BWapogdL8V1T3mMtmbNEa8meoEcCAwRsjfa9Ypaq5AeuhnpOi9WrTZhBJoWMnY4ocnLGXV6%2B1BRjlXa3rk%2F6iH9yqjtkKtE0ryn0JiIexsAlkBLHcMeRBdw959jqfta9WxoK2qcAGKq2pGMZugaiiqnbDpx5TgqYhVfuXub4E0DGaaXqw9PbZnxjI%2BBspCVr22OrYTtgusURB30MRf2pWNurQpRP7QrCSAEJ8g%3D","sign_status":"N","auth_mode":"O","response_status":"N","error_code":"112","error_message":"Aadhaar number does not have both email ID and mobile number","esp_res_code":"60F79A100970BF25644988BE4BF18E2697749840","request_timestamp":"2019-08-21T07:30:50.230Z"}]}';

    protected $successEsignResponse = '{"id":"ee4d0ab9-7abe-4cbd-8176-c90460ec51af","response_timestamp":"2019-08-23T03:39:49.454Z","transaction_status":16,"public_ip":"183.239.150.90","signer_consent":"Y","request_medium":"W","last_document":true,"current_document":1,"signed_document_count":1,"documents":[{"id":"3496f353-199b-4df1-9b29-0e5027aa9261","index":1,"doc_info":"Formal signing of the order","type":"pdf","dynamic_url":"https:\/\/secured-esign-pdf-storage-prod.s3.ap-southeast-1.amazonaws.com\/3496f353-199b-4df1-9b29-0e5027aa9261?AWSAccessKeyId=ASIAQC54FU6IDJ7AOFOH&Expires=1566704636&Signature=jf5Srchh842pfvd%2Bgzkf6E%2F5m60%3D&response-content-disposition=attachment%3B%20filename%20%3D%223496f353-199b-4df1-9b29-0e5027aa9261.pdf%22&x-amz-security-token=AgoJb3JpZ2luX2VjEEoaCmFwLXNvdXRoLTEiRzBFAiEA8eVFHwe%2BHvJCClQA3u4LYrYd%2BQ%2FipYeE824pE8CFDzwCICzLuN0xTxSjTyYeczTbbrmd%2BIlrK%2FwQ2JARQuVqS4aMKuUDCOP%2F%2F%2F%2F%2F%2F%2F%2F%2F%2FwEQARoMMDA2MzAwMjE1MTg0Igzs2yl4p4BEcX3hpC4quQNTulL%2BdEsmR6%2BzzeYMAvhi4jmABUpKYkHzxiAWGlUDbFxoo4JuLJ2rgXjPiweqDnE%2FM0ClrxTyMtXdAWXpRbxab%2BY%2BrKW5f3TGT8I5KTDgQ%2Bkb1PA6vAkB38%2BIMSclKpsgeONmwTjS5Ry416x%2B6lU2cjBszLRuUR17ld2%2FAtVEMfegEyfd2zuBUUZIE0nCBdaCOvOJ4ExhnZv9aKlCgBnHPpowzFikz7i7vtMXvEluZoYSzsEJkH0eZ11CmeqQ0wZolemz34gp%2F3Kg%2BZwPoz%2ByN1dwezrMXtB5nNlOhM24Yoa7VCfv9%2Bnc3tKn7NuMe%2BR9CB5c6tVixRyNrck0zyFWvfWLWCK0sDP6%2BPrukcA%2BgBBkI8k5KlJRhzU8Rq6pHbqf2aX4ikZYbyVoRtpZiddHM7eRBOicDh7RavW%2BO1hc%2BfhgSmf0BSx%2BbDebSf1ZN%2FqFdmNJtsUMsE61eTaboqaaWGXyuP6lCfcZUvDiBwuV9WV%2BCTzU7CThrut5aERZLltUK7fqw8ax0xyS8HywdFh%2FHwHrNjS61rUbnK4SRPNUDYuGe227Z3X6HIUjlX2m7N655zUbNDE9%2FKAwxIf96gU6tAEYSxBSZdY9XnWC3%2FEvSz0G3GbKtkhRUrSk4QM1Z%2Bpi%2BKfaJKz6yc4VHECW4x4%2BOvmMq28rK5jJqcb7iHIa3OwgCEoZ2Kp4ct8zFaB2OObJ37b3T0CYk8EbJJAmlUqxJAIqkn1%2FcVounG5xl8mDjUEM%2B0c7oOHv4bIHBSGULZiDMYxEmODg%2Fa%2FrNQEt0p1aPisfNBSFuyTfgvNB3%2Ffn3%2FWOmBiUMRyw%2Bve%2FMfcx%2BbvFHhMfKoY%3D","sign_status":"Y","auth_mode":"O","response_status":"Y","error_code":null,"error_message":null,"esp_res_code":"4E55705FE30A20EF12BA4FC632DFEEFD3CF68D5B","request_timestamp":"2019-08-23T03:39:49.454Z","signer_name_esp":"Udbale Vittal","aadhaar_identifier":"8339","signer_location_esp":"500034","name_match_score":"0.00"}],"app\/callback\/aadhaar\/esign-response":""}';

    private function addReturnHeader($isSuccess = true)
    {
        $location = 'Location:' . HostHelper::getDomain();
        if ($isSuccess) {
            header($location . "/app/callback/digio/success");
        } else {
            header($location . "/app/callback/digio/fail");
        }
    }

    public function esignResponse()
    {
        $data = $this->request->all();
        //$data = json_decode($this->successEsignResponse, true);

        DingHelper::notice(json_encode($data), "esign签约回调~~", DingHelper::AT_SOLIANG);


        if (!$data || !isset($data['id']) || !isset($data['documents']) || !isset($data['transaction_status'])) {
            die('error in data');
        }

        $orderSignDoc = OrderSignDoc::model()->getByDocId($data['id']);

        if (!$orderSignDoc) {
            die('The document does not exist:' . $data['id']);
        }
        DB::beginTransaction();
        try {
            $document = $data['documents'];
            if ($data['transaction_status'] != '16' || empty($document) || $document[0]['sign_status'] != 'Y') {
                $orderSignDoc->toDisabled();
                DB::commit();
                $this->addReturnHeader(false);
                die('sign failed');
            }
            $document = $document[0];

            $user = $orderSignDoc->user;
            // 全局添加商户id
            MerchantHelper::setAppId($user->app_id, $user->merchant_id);
            OrderServer::server()->sign($user);
            // 签名后合同拉取
            $orderSignDoc->saveDocUrl($document['dynamic_url']);
            dispatch(new ContractByEsignPdfJob($orderSignDoc->id));

            DB::commit();
            $this->addReturnHeader(true);
            die('success');
        } catch (\Exception $e) {
            DB::rollBack();
            $paramsStr = json_encode($data, true);
            $errorStr = "\n{$e->getMessage()}\nfile:{$e->getFile()}:{$e->getLine()}";
            DingHelper::notice($paramsStr . $errorStr, 'esign回调报错', DingHelper::AT_SOLIANG);

            $this->addReturnHeader(false);
            exit();
        }
    }

}
