<?php
/**
 * Created by PhpStorm.
 * User: jinqianbao
 * Date: 2019/2/14
 * Time: 17:14
 */

namespace Common\Console\Commands\Test;

use Api\Models\Upload\Upload;
use Common\Models\User\User;
use Common\Models\User\UserAuth;
use Common\Utils\Services\AuthRequestHelper;
use Common\Utils\Upload\OssHelper;
use Illuminate\Console\Command;

class TestConsole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:run';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '测试';

    /**
     * @return void
     */
    public function handle()
    {
        //$ids = [241754,241854,241845,241838,241807,241825,241817,241814,241806,241808,241789,241783,241451,241751,241551,226209,241387,241190,241630,241290,240566,239658,241608,241603,240563,241091,240983,221389,226043,241584,241580,241581,240915,241224,226145,221275,226256,233134,240930,241479,226587,241468,239582,239508,241571,241570,241561,241550,241477,241548,241546,241542,241545,241543,241540,241533,240588,241531,241504,241523,241509,241497,241489,241478,241474,241464,225996,240617,239749,241365,241453,241450,241454,241436,241430,241434,241414,241415,241406,241402,241405,241404,241390,241345,241385,240576,241360,241350,226455,241348,241335,241341,241326,226598,241298,241295,240535,241131,233436,226098];
        //$ids = [241754, 241854, 241845, 241838, 241807, 241825, 241817, 241814, 241806, 241808];
        $ids = [241789, 241783, 241451, 241751, 241551, 226209, 241387, 241190, 241630, 241290, 240566, 239658, 241608, 241603, 240563, 241091, 240983, 221389, 226043, 241584, 241580, 241581, 240915, 241224, 226145, 221275, 226256, 233134, 240930, 241479, 226587, 241468, 239582, 239508, 241571, 241570, 241561, 241550, 241477, 241548, 241546, 241542, 241545, 241543, 241540, 241533, 240588, 241531, 241504, 241523, 241509, 241497, 241489, 241478, 241474, 241464, 225996, 240617, 239749, 241365, 241453, 241450, 241454, 241436, 241430, 241434, 241414, 241415, 241406, 241402, 241405, 241404, 241390, 241345, 241385, 240576, 241360, 241350, 226455, 241348, 241335, 241341, 241326, 226598, 241298, 241295, 240535, 241131, 233436, 226098];
        //$ids = [932, 1319, 1132];
        //$ids = [869];
        //$ids = [284];
        $users = User::whereIn('id', $ids)->get();
        /** @var User $user */
        foreach ($users as $user) {
            echo $user->id . PHP_EOL;
            $this->AadhaarCardFrontOcr($user);
            $this->AadhaarCardBackOcr($user);
            $this->panCardOcr($user);
            $this->panCard($user);
            if ($user->getAddressVoterIdCardStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                $this->voterOcr($user);
                $this->voterId($user);
            }
            if ($user->getAddressPassportStatus() == UserAuth::AUTH_STATUS_SUCCESS) {
                $this->passportOcr($user);
                $this->passport($user);
            }
            $this->bankCard($user);
        }
    }

    public function AadhaarCardFrontOcr(User $user)
    {
        $front = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_AADHAAR_CARD_FRONT);
        if (!$front) {
            echo 'AadhaarCard front have not upload' . PHP_EOL;
            return;
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->aadhaarCardFrontOcr($url, $user);
    }

    public function AadhaarCardBackOcr(User $user)
    {
        $back = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_AADHAAR_CARD_BACK);
        if (!$back) {
            echo 'AadhaarCard back have not upload' . PHP_EOL;
            return;
        }
        $url = OssHelper::helper()->picTokenUrl($back->path);
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->aadhaarCardBackOcr($url, $user);
    }

    public function panCardOcr(User $user)
    {
        $front = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_PAN_CARD);
        if (!$front) {
            echo 'Pancard have not upload' . PHP_EOL;
            return;
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->panCardOcr($url, $user);
    }

    public function panCard(User $user)
    {
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->panCard($user->userInfo->pan_card_no, $user);
    }

    public function voterOcr(User $user)
    {
        $front = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_VOTER_ID_CARD_FRONT);
        if (!$front) {
            echo 'voterId have not upload' . PHP_EOL;
            return;
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->voterIdOcr($url, $user);
    }

    public function voterId(User $user)
    {
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->voterId($user->userInfo->voter_id_card_no, $user);
    }

    public function passportOcr(User $user)
    {
        $front = Upload::model()->getOneFileByUser($user->id, Upload::TYPE_PASSPORT_IDENTITY);
        if (!$front) {
            echo 'Passport have not upload' . PHP_EOL;
        }
        $url = OssHelper::helper()->picTokenUrl($front->path);
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->passportOcr($url, $user);
    }

    public function passport(User $user)
    {
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->passport($user->userInfo->passport_no, $user);
    }

    public function bankCard(User $user)
    {
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->bankCard($user->bankCard->no, $user->bankCard->ifsc, $user);
    }

    public function aadhaarCard(User $user)
    {
        $authRequestHelper = new AuthRequestHelper();
        $authRequestHelper->setParams(['c' => 'z']);
        $requestRes = $authRequestHelper->aadhaarCard($user->userInfo->aadhaar_card_no, $user);
    }

}
