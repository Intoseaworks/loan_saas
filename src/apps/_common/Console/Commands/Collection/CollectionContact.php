<?php

namespace Common\Console\Commands\Collection;

use Common\Models\User\User;
use Common\Models\UserData\UserContactsTelephone;
use Common\Utils\Data\StringHelper;
use Common\Validators\Validation;
use Illuminate\Console\Command;

class CollectionContact extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'collection:contact';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '催收通讯录修复';

    public function handle()
    {
        $users = User::query()->get();
        foreach ($users as $user) {
            $this->line('通讯录号码修正中user_id=' . $user->id);
            $contacts = UserContactsTelephone::model()->getContacts($user->id, 0);
            if (!$contacts) {
                continue;
            }
            $this->line('>>>>>>>>>>>>>>');
            foreach ($contacts as $contact) {
                $telephone = $contact->contact_telephone;
                $length = strlen($telephone);
                if ($length < 10) {
                    $this->line('号码格式小于10位移除tel=' . $telephone);
                    $contact->delete();
                    continue;
                }
                if (Validation::validateMobile('', $telephone) && $length == 10) {
                    continue;
                }
                $oldTelephone = $telephone;
                $telephone = StringHelper::formatTelephone($telephone); //格式化手机号码
                $this->line('号码格式修正旧=' . $oldTelephone . '->新=' . $telephone);
                if (!Validation::validateMobile('', $telephone)) {
                    $contact->delete();
                    $this->line('号码格式无法修正移除成功contactID=' . $contact->id);
                } else {
                    $contact->contact_telephone = $telephone;
                    $contact->save();
                    $this->line('号码格式修正修改成功contactID=' . $contact->id);
                }
            }
            $this->line('>>>>>>>>>>>>>>');
        }
    }
}
