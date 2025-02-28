<?php
/**
 * Created by PhpStorm.
 * User: Windy
 * Date: 2019/1/18
 * Time: 10:53
 */

namespace Api\Services\User;


use Admin\Services\Risk\RiskServer;
use Api\Models\BankCard\BankCard;
use Api\Models\User\User;
use Api\Models\User\UserAuth;
use Api\Models\User\UserContact;
use Api\Models\User\UserInfo;
use Api\Services\BaseService;
use Api\Services\Common\CaptchaServer;
use Auth;
use Common\Models\Common\Dict;
use Common\Models\Common\Pincode;
use Common\Models\Inbox\Inbox;
use Common\Models\Merchant\App;
use Common\Models\Upload\Upload;
use Common\Models\User\UserWork;
use Common\Utils\Data\ArrayHelper;
use Common\Utils\Data\StringHelper;
use Common\Utils\MerchantHelper;
use Common\Utils\Push\Services\GooglePush;
use Common\Utils\Upload\OssHelper;
use DB;

class UserInfoServer extends BaseService
{

    public function info(\Common\Models\User\User $user)
    {
        $lastPosition = RiskServer::server()->lastPosition($user->id);
        $lastAddress = array_get($lastPosition, 'address');

        $userInfo = $user->userInfo;

        $data = [
            'address' => $lastAddress ?? '',
            'marital_status' => $userInfo->marital_status ?? '',
            'education_level' => $userInfo->education_level ?? '',
            'expected_amount' => $userInfo->expected_amount ?? '',
            'company_name' => $userInfo->company_name ?? '',
            'company_telephone' => $userInfo->company_telephone ?? '',
            'salary' => $user->userWork->salary ?? '',
            'contacts' => $user->userContacts ?? []
        ];

        return $this->outputSuccess('', $data);
    }

    public function postInfo(User $user, $params)
    {
        DB::beginTransaction();

        $params['user_id'] = $user->id;
        if (!UserInfo::firstOrNewModel(UserInfo::SCENARIO_CREATE, ['user_id' => $user->id])->saveModel($params)) {
            DB::rollBack();
            return $this->outputError('用户详情保存失败');
        }

        /** 清理联系人 */
        UserContact::model()->clear($user->id);

        foreach ($params['contacts'] as $val) {
            $val['user_id'] = $user->id;
            $val['times_contacted'] = array($val, 'timesContacted');
            $val['last_time_contacted'] = array($val, 'lastTimeContacted');
            $val['has_phone_number'] = array($val, 'hasPhoneNumber');
            $val['starred'] = array($val, 'starred');
            $val['contact_last_updated_timestamp'] = array($val, 'contactLastUpdatedTimestamp');
            if (!UserContact::model(UserContact::SCENARIO_CREATE)->saveModel($val)) {
                DB::rollBack();
                return $this->outputError('紧急联系人保存失败');
            }
        }
        /** 设置基础信息认证状态 */
        UserAuthServer::server()->setAuth($user->id, UserAuth::TYPE_BASE_INFO);
        DB::commit();

        return $this->outputSuccess();

    }


    /**
     * 预处理用户信息
     * 格式化电话号码
     * @param $params
     * @return mixed
     */
    public function handleUserInfo($params)
    {
        if ($contacts = array_get($params, 'contacts')) {

            foreach ($contacts as $key => $contact) {
                $contacts[$key]['contact_telephone'] = StringHelper::formatTelephone($contact['contact_telephone']);
            }
            $params['contacts'] = $contacts;
        }

        return $params;
    }

    /*-------cashnow code--------------------*/
    /**
     * 工作邮箱验证
     *
     * @param $email
     * @param $captcha
     * @param $user
     * @return bool
     */
    public function workEmailValid($email, $captcha, $user)
    {
        $result = CaptchaServer::server()->validCaptcha(CaptchaServer::CACHE_KEY_EMAIL, $email, $captcha);
        if ($result) {
            UserAuthServer::server()->setAuth($user, UserAuth::TYPE_USER_WORK);
        }

        return $result;
    }

    /**
     * 用户工作信息
     *
     * @param $data
     * @return bool|UserWork
     */
    public function createUserWork($data)
    {
        return UserWork::updateOrCreateModel(UserWork::SCENARIO_CREATE, ['user_id' => Auth::id()], $data);
    }

    /**
     * 更新googleToken
     *
     * @param $googleToken
     * @return UserInfo
     */
    public function updateGoogleToken($googleToken)
    {
        UserInfo::updateOrCreateModel(UserInfo::SCENARIO_GOOGLE_TOKEN, ['user_id' => Auth::id()], ['google_token' => $googleToken]);
        $app = App::model()->getNormalAppById(MerchantHelper::getAppId());
        if ($app) {
            $topic = GooglePush::TOPIC_APP . $app->app_key;
            GooglePush::subscribeToTopic($googleToken, $topic, $app->google_server_key);
        }
    }

    /**
     * 用户扩展信息
     *
     * @param $data
     * @return bool
     */
    public function createUserInfo($data)
    {
        /** @var User $user */
        $user = Auth::user();
        $pincode = array_get($data, 'pincode');
        if ($pincodeData = Pincode::model()->getPincodeData($pincode)) {
            $data['province'] = $pincodeData->statename;
            $data['city'] = $pincodeData->districtname;
        }
        $userInfo = UserInfo::updateOrCreateModel(UserInfo::SCENARIO_CREATE, ['user_id' => $user->id], $data);
        $userWorkData = array_only($data,
            ['profession', 'employment_type', 'working_since', 'income', 'company', 'workplace_pincode']);
        $userWorkData = array_merge($userWorkData, [
            'work_start_date' => $userWorkData['working_since'],
            'salary' => $userWorkData['income'],
            'occupation' => $this->getOccupation($userWorkData['profession']),
        ]);
        $this->createUserWork($userWorkData);
        UserAuthServer::server()->setAuth($user, UserAuth::TYPE_USER_EXTRA_INFO);

        return $userInfo;
    }

    /**
     * 用户基础信息
     *
     * @param $data
     * @return UserInfo
     */
    public function createBaseUserInfo($data)
    {
        /** @var User $user */
        $user = Auth::user();
        $data['fullname'] = str_replace(["/r/n", "/r", "/n"], '', $data['fullname']);
        if ($user->fullname == '') {
            $user->fullname = $data['fullname'];
            $user->save();
        }
        $data['input_name'] = $data['fullname'];
        $birthday = array_get($data, 'birthday', '');

        $data['input_birthday'] = $birthday;
        $userInfo = UserInfo::updateOrCreateModel(UserInfo::SCENARIO_CREATE, ['user_id' => $user->id], $data);

        UserAuthServer::server()->setAuth($user, UserAuth::TYPE_BASE_INFO);

        return $userInfo;
    }

    public function createUserIntention($data)
    {
        /** @var User $user */
        $user = Auth::user();
        $data = array_only($data, ['principal', 'loan_days']);
        $userInfo = UserInfo::updateOrCreateModel(UserInfo::SCENARIO_CREATE, ['user_id' => $user->id], $data);
        return $userInfo;
    }

    public function createUserContact($data)
    {
        $relation1 = array_get($data, 'relation1');
        $relation2 = array_get($data, 'relation2');
        if (in_array($relation1, ['FATHER', 'MOTHER', 'WIFE', 'HUSBAND', 'OTHER']) && $relation1 == $relation2) {
            return $this->outputException("There can only be one {$relation1}");
        }
        /** @var $user User */
        $user = Auth::user();
        DB::transaction(function () use ($data, $user) {

            $extend1 = $this->getExtend(array_get($data, 'extend1'));
            $extend2 = $this->getExtend(array_get($data, 'extend2'));
            UserContact::model()->clear($user->id);
            UserContact::model()->setScenario(UserContact::SCENARIO_CREATE)->saveModels([
                [
                    'user_id' => $user->id,
                    'contact_fullname' => array_get($data, 'contactFullname1'),
                    'contact_telephone' => array_get($data, 'contactTelephone1'),
                    'relation' => array_get($data, 'relation1'),
                    'times_contacted' => array_get($extend1, 'timesContacted'),
                    'last_time_contacted' => array_get($extend1, 'lastTimeContacted'),
                    'has_phone_number' => array_get($extend1, 'hasPhoneNumber'),
                    'starred' => array_get($extend1, 'starred'),
                    'contact_last_updated_timestamp' => array_get($extend1, 'contactLastUpdatedTimestamp'),
                ],
                [
                    'user_id' => $user->id,
                    'contact_fullname' => array_get($data, 'contactFullname2'),
                    'contact_telephone' => array_get($data, 'contactTelephone2'),
                    'relation' => array_get($data, 'relation2'),
                    'times_contacted' => array_get($extend2, 'timesContacted'),
                    'last_time_contacted' => array_get($extend2, 'lastTimeContacted'),
                    'has_phone_number' => array_get($extend2, 'hasPhoneNumber'),
                    'starred' => array_get($extend2, 'starred'),
                    'contact_last_updated_timestamp' => array_get($extend2, 'contactLastUpdatedTimestamp'),
                ],
            ], true, true);
            UserAuthServer::server()->setAuth($user, UserAuth::TYPE_CONTACTS);
        });
        return $this->outputSuccess();
    }

    public function getExtend($paramExtend)
    {
        if($paramExtend && $extend = json_decode($paramExtend, true)){
            return $extend;
        }
        return [];
    }

    /**
     * 获取用户信息
     *
     * @param $userId
     * @return array
     */
    public function getUserInfo($userId)
    {
        $userInfo = (new UserInfo)
            ->with(['userWork', 'userContact'])
            ->where('user_id', $userId)
            ->first();
        $userInfo->setScenario(UserInfo::SCENARIO_USER_INFO)->getText();
        $data = [
            'work' => $userInfo->userWork,
            'contact' => $userInfo->userContact,
        ];
        unset($userInfo->userWork);
        unset($userInfo->userContact);

        $data['info'] = $userInfo;

        return $data;
    }

    /**
     * 获取用户详情
     *
     * @param $userId
     * @return array
     */
    public function getUserDetail($userId)
    {
        /** @var User $user */
        $user = Auth::user();
        /** @var Upload $userFace */
        $userFace = Upload::model()->getFileByUser($userId, Upload::TYPE_FACES)->last();
        $facebookName = '';
        if (isset($user->userFaceBook) && isset($user->userFaceBook->facebookData)) {
            $facebookName = $user->userFaceBook->facebookData->name;
        }
        $bankCardStatus = UserAuth::STATUS_INVALID;
        if (isset($user->bankCard->status) && $user->bankCard->status == BankCard::STATUS_ACTIVE) {
            $bankCardStatus = UserAuth::STATUS_VALID;
        }
        $userDetail = [
            'user_id' => $userId,
            'telephone' => $user->telephone,
            'email' => $user->userInfo->email,
            'quota' => '5000',
            'quality' => $user->getRealQuality(),
            'fullname' => $user->fullname ?? '',
            'last_name' => $user->userInfo->last_name,
            'first_name' => $user->userInfo->first_name,
            'aadhaar_card_no' => $user->userInfo->address_aadhaar_no,
            'bank_card_no' => $user->bankCard->account_no ?? '',
            'identity_status' => $user->getIdentityStatus(),
            'personal_info_status' => $user->getPersonalInfoStatus(),
            'contacts_status' => $user->getContactsStatus(),
            'telephone_status' => $user->getTelephoneStatus(),
            'basic_detail_status' => $user->getBaseInfoStatus(),
            'is_completed' => $user->getIsCompleted(),
            'bank_card_status' => $bankCardStatus,
            'unread_inbox_sum' => Inbox::getUnreadSum($user->id),
            'face_img' => $userFace ? OssHelper::helper()->picTokenUrl($userFace->path) : '',
            'address' => $user->userInfo->address ?: '',
            'facebook_name' => $facebookName,
        ];

        return $userDetail;
    }

    public function setUserContactCount($count)
    {
        /** @var User $user */
        $user = Auth::user();
        $data['contacts_count'] = $count;
        return UserInfo::updateOrCreateModel(UserInfo::SCENARIO_CONTACTS_COUNT, ['user_id' => $user->id], $data);
    }

    /**
     * @param $userId
     * @param $userInfoCardNoName
     * @param $no
     * @param $address
     * @param $pincode
     * @return UserInfo
     */
    public function updateCardData($userId, $userInfoCardNoName, $no, $address, $pincode)
    {
        $data = [
            $userInfoCardNoName => $no,
        ];
        if ($address != '') {
            $data['permanent_address'] = $address;
        }
        if ($pincode != '') {
            $data['permanent_pincode'] = $pincode;
            if (!$pincodeData = Pincode::model()->getPincodeData($pincode)) {
                return $this->outputException('pincode is error');
            }
            $data['permanent_province'] = $pincodeData->statename;
            $data['permanent_city'] = $pincodeData->districtname;
        }
        $userInfo = UserInfo::updateOrCreateModel(UserInfo::SCENARIO_UPDATE_CARD, ['user_id' => $userId], $data);
        return $userInfo;
    }

    /**
     * 获取职业列表
     * @param string $parent
     * @return array|mixed
     */
    public function getProfession() {
        return Dict::model()->getList(["parent_code" => "PROFESSION"]);
    }

    /**
     * 获取人的关系
     * @param string $parent
     * @return array|mixed
     */
    public function getRelationship() {
        return Dict::model()->getList(["parent_code" => "RELATIONSHIP"]);
    }

    public function getIndustry() {
        return Dict::model()->getList(["parent_code" => "INDUSTRY"]);
    }

    /**
     * 二级职业获取一级职业
     *
     * @param $profession
     * @return mixed
     */
    public function getOccupation($profession) {
        $occupationJson = '{"Audit":"Accountant","Clerk\/Book Keeper":"Accountant","Finance":"Teacher","Taxation":"Accountant","3D Modeler":"Animation","Animator":"Animation","Art director":"Animation","Cartoonist":"Animation","Digital Painter":"Animation","Effects Animator":"Animation","Flash Animator":"Animation","Forensic animator":"Animation","Graphic designer":"Animation","Lighting technician":"Animation","other":"Waiter\/Steward","Stop motion Animator":"Animation","Video Editor":"Animation","Video Game designer":"Animation","Visual development artist":"Animation","Exterior Design":"Architect","Interior Design":"Architect","Airlines\/Airport":"Bartender","Bars\/Pubs":"Bouncer","Casino":"Bouncer","Clubs":"Bartender","Hotel & Restaurant":"Waiter\/Steward","Pubs and Breweries":"Bartender","Beauty Care":"Marketing","Hair":"Beautician","Make-up":"Beautician","Manicure":"Beautician","Massage":"Beautician","Mens Salon":"Beautician","Nailcare":"Beautician","Pedicure":"Beautician","Pet Grooming":"Beautician","Shampoo":"Beautician","Skin Care":"Beautician","Slimming":"Beautician","Unisex Salon":"Beautician","Woman\'s Salon":"Beautician","Concerts\/Events":"Bouncer","Nightclubs":"Bouncer","Ranches":"Bouncer","Domestic":"Gardener","Incoming":"BPO\/ Telecaller","International":"BPO\/ Telecaller","Non Voice":"BPO\/ Telecaller","Non-Technical":"BPO\/ Telecaller","Outgoing":"BPO\/ Telecaller","Sales ":"BPO\/ Telecaller","Support":"BPO\/ Telecaller","Technical":"BPO\/ Telecaller","Voice ":"BPO\/ Telecaller","Anova":"Business Analyst","Business Analysis":"Business Analyst","Data Analysis":"Business Analyst","Excel":"Business Analyst","OBIEE Analytics":"Business Analyst","SAS":"Business Analyst","SPSS":"Business Analyst","SQL":"IT Software - QA\/Test Engineer","Statistical Simulation\/Regression Modelling":"Business Analyst","Statistics":"Business Analyst","Events":"Ticketing Executive","News":"Content Writer","Travel":"Videographer","Wildlife & Nature":"Cameraman","Commercial":"Gardener","Furniture":"Carpenter","Industrial":"Wireman","Maintenance":"Electroplater","Residential":"Electrician","Banking & Finance":"Product Manager","Construction":"Quality and Inspection","Hotel & Resturant":"Cashier","Manufacturing":"Quality and Inspection","Retail":"Sales","Airport":"Cleaner\/Washer","Automobiles":"Cleaner\/Washer","Chemical":"Lab Assistant","Corporate":"Cleaner\/Washer","Educational Institutions":"Cleaner\/Washer","Hospitals":"Cleaner\/Washer","Hotel & Resturants":"Cleaner\/Washer","Lab":"Cleaner\/Washer","Machine":"Cleaner\/Washer","Public Places":"Cleaner\/Washer","Railway Station":"Cleaner\/Washer","Banking & Financial Services":"Sales","Entertainment":"Reporter","Fashion":"Videographer","Medical":"Legal","Review":"Content Writer","Social":"Content Writer","Technology":"Content Writer","Baking and Confectionary":"Cook\/Chef","Chinese":"Language Translator","Continental":"Cook\/Chef","French":"Teacher","Indian Cuisine":"Cook\/Chef","Italian":"Language Translator","Mexcian":"Cook\/Chef","Mughlai":"Cook\/Chef","Multi Cuisine":"Cook\/Chef","Non Veg":"Cook\/Chef","North Indian":"Cook\/Chef","South Indian":"Cook\/Chef","Thai":"Cook\/Chef","Veg":"Cook\/Chef","Banking and Finance":"Counsellor","Career":"Counsellor","Education":"Counsellor","Legal":"Counsellor","Marriage":"Counsellor","Mental Health":"Counsellor","Rehabilitation":"Counsellor","Field":"Reporter","Internet \/ Online":"Data Collection\/Survey","Networking":"Database\/System\/Linux Administrator","Patch Management(WSUS)":"Database\/System\/Linux Administrator","Perl":"IT Software - QA\/Test Engineer","Python":"IT Software - QA\/Test Engineer","Ruby":"Database\/System\/Linux Administrator","Scripting-Powershell":"Database\/System\/Linux Administrator","Troubleshooting":"Database\/System\/Linux Administrator","Windows\/Linux Admin":"Database\/System\/Linux Administrator","Cash Collection":"Delivery\/Collections","Courier Delivery":"Delivery\/Collections","Courier\/Packet Collection":"Delivery\/Collections","Documents Collection":"Delivery\/Collections","Food Delivery":"Delivery\/Collections","Grocery Delivery":"Delivery\/Collections","Other Delivery\/Collection":"Delivery\/Collections","Recovery Collection":"Delivery\/Collections","Animation":"Designer","Design CAD":"Designer","Designer":"Designer","Fashion Design":"Designer","Furniture \/ Home Supplies":"Designer","Graphic Design":"Designer","Jewelry Design":"Designer","Multimedia":"Designer","Packaging":"Pharmacist","Pattern Division":"Designer","Product":"Designer","Textile Designer":"Designer","Aged Care":"Doctor","Anaesthetist":"Doctor","Ayurvedic":"Doctor","Cardiologist":"Doctor","Dermatologist":"Doctor","General Physician":"Doctor","Gynaecologists":"Doctor","Homeopathic":"Doctor","Neurologists":"Doctor","Nutritionist":"Doctor","Oncologist":"Doctor","Opthalmologist":"Doctor","Orthodontist":"Doctor","Pathalogy":"Doctor","Pediatrics":"Doctor","Pet Care\/ Veterinary":"Doctor","Physiotherapy":"Doctor","Plastic Surgeon":"Doctor","Psychiatrists":"Doctor","Radiologist":"Doctor","Unani Medicine":"Doctor","Architectural":"Draftsman","Automobile":"Sales","Civil":"Draftsman","Design":"Draftsman","Electrical":"Draftsman","Fire and Gas":"Draftsman","Mechanical":"Draftsman","structural":"Draftsman","Ambulance Driver":"Driver","Auto Driver":"Driver","Commercial Transport Driver":"Driver","Company driver":"Driver","Freight Transport Driver":"Driver","Heavy Vehicle Driver":"Driver","Interstate Driver":"Driver","Private Driver":"Driver","School Bus Driver":"Driver","Taxi driver":"Driver","Taxi\/Cab":"Driver","Truck driver":"Driver","CAD":"DTP Operator\/Print Consultant","Corel draw":"DTP Operator\/Print Consultant","MS Powerpoint":"DTP Operator\/Print Consultant","MS publisher":"DTP Operator\/Print Consultant","Photoshop":"IT Software - Web Designer","Cartoon":"Dubbing Artist","English":"Teacher","Hindi":"Dubbing Artist","Movie":"Dubbing Artist","Tamil":"Dubbing Artist","Telgu":"Dubbing Artist","High Voltage":"Electrician","Low Voltage":"Electrician","Birthday":"Event Planner","Concerts":"Event Planner","Corporate Events":"Event Planner","Cultural Events":"Event Planner","Exhibition & Conference":"Event Planner","Family Events":"Event Planner","Fashion Show":"Event Planner","Film & Musical Events":"Event Planner","Parties & DJ Nights":"Event Planner","Road Shows":"Event Planner","Sports":"Videographer","Wedding":"Videographer"," Assemble machines":"Fitter"," Electronic Assembler":"Fitter"," Maintenance":"Fitter"," Mechanical Assembler":"Fitter"," Plastic Work Assembler":"Fitter"," Production Assembler":"Fitter"," Repair":"Fitter"," Welding":"Fitter","Cutting":"Fitter","Installing machines":"Fitter","Turning":"Fitter","Retail Store":"Floor Manager","Salon":"Floor Manager","Building Gardens":"Gardener","Plant Nursery":"Gardener","Private Home Garden":"Gardener","Public Parks":"Gardener","Street Plants":"Gardener","Aviation":"Hospitality Executive","Hospital":"Receptionist\/Front Office","Airport Ground Staff":"Host\/Hostess","In-flight\/ Cabin Crew":"Host\/Hostess","Retail Store Sales Host":"Host\/Hostess","Compliance":"HR\/Admin","HRMS":"HR\/Admin","Learning & Development":"HR\/Admin","Payrolls":"HR\/Admin","Personnel Management":"HR\/Admin","Recruitment":"HR\/Admin","Training":"HR\/Admin","Hardware Installation":"IT Hardware & Network Engineer","Network Engineering":"IT Hardware & Network Engineer","Software Installation & Troubleshooting":"IT Hardware & Network Engineer","Android Development":"IT Software - Developer","Axiom":"IT Software - Developer","Biztalk":"IT Software - Developer","C \/ C++":"IT Software - Developer","C#":"IT Software - Developer","Dot Net":"IT Software - Developer","Drupal":"IT Software - Developer","Java":"IT Software - QA\/Test Engineer","Joomla":"IT Software - Developer","Magneto":"IT Software - Developer","PHP":"IT Software - Developer","Pega":"IT Software - Developer","Salesforce":"IT Software - Developer","Service Now":"IT Software - Developer","Sharepoint":"IT Software - Developer","Siebel":"IT Software - Developer","Tibco":"IT Software - Developer","Visual Basic":"IT Software - Developer","Windows Development":"IT Software - Developer","Wordpress":"IT Software - Developer","iOS Development":"IT Software - Developer","Automated Testing":"IT Software - QA\/Test Engineer","Manual Testing":"IT Software - QA\/Test Engineer","Mobile Testing":"IT Software - QA\/Test Engineer","Other Scripting Languages":"IT Software - QA\/Test Engineer","Selenium":"IT Software - QA\/Test Engineer","Shell Scripting":"IT Software - QA\/Test Engineer","Adobe Creative Suit":"IT Software - Web Designer","Adobe Dream weaver":"IT Software - Web Designer","Adobe Illustrator Software":"IT Software - Web Designer","Adobe In-Design Software":"IT Software - Web Designer","CSS":"IT Software - Web Designer","Corel Draw":"IT Software - Web Designer","Dot Net Frameworks":"IT Software - Web Designer","Fire works":"IT Software - Web Designer","Flash":"IT Software - Web Designer","HTML":"IT Software - Web Designer","Media Designs":"IT Software - Web Designer","Poster Design":"IT Software - Web Designer","Sketch":"IT Software - Web Designer","Typography":"IT Software - Web Designer","Visual Communication":"IT Software - Web Designer","XHTML":"IT Software - Web Designer","Cardiology":"Medical Assistant","Clinical":"Medical Assistant","Haemotology":"Lab Assistant","Metallurgy":"Lab Assistant","Micro Biology":"Medical Assistant","Microbiology":"Lab Assistant","Pathology":"Medical Assistant","Phlebotomist":"Lab Assistant","Radio":"Sound Engineer","X-Ray Technician":"Lab Assistant","German":"Language Translator","Japanese":"Language Translator","Korean":"Language Translator","Portugal":"Language Translator","Russian":"Language Translator","Spanish":"Teacher"," Commercial Agreement":"Legal"," Contract Management":"Legal"," Corporate":"Legal"," Intellectual Property":"Legal","Bankruptcy":"Legal","Constitutional":"Legal","Criminal":"Legal","Employment":"Legal","Environmental":"Legal","Family & Divorce":"Legal","Government":"Legal","Immigration":"Legal","Labour":"Legal","Property":"Legal","Real Estate":"Legal","Tax":"Legal","Apparel":"Sales","Brand Marketing":"Marketing","Club Memberships":"Marketing","Digital Marketing":"Marketing","Exhibition Planning":"Marketing","Healthcare":"Waiter\/Steward","Inventory Management":"Marketing","Media":"Videographer","AC repair":"Mechanic","Fitter":"Mechanic","Four-wheeler":"Mechanic","Fridge":"Mechanic","HVAC":"Mechanic","Mobile-Repair":"Mechanic","Other Machine Repair":"Mechanic","Radio\/TV repair":"Mechanic","Three-Wheeler":"Mechanic","Two-wheeler":"Mechanic","Washing Machine Repair":"Mechanic","Welder":"Mechanic"," Dermatology":"Medical Assistant"," ENT":"Medical Assistant"," Gynaecology":"Medical Assistant"," Pediatric":"Medical Assistant"," Urology":"Medical Assistant","Audiologist":"Medical Assistant","Dental":"Medical Assistant","Alcohol":"Microbiologist","Bio Technology":"Microbiologist","Dairy":"Microbiologist","Environment":"Microbiologist","Pharma Manufacturing":"Microbiologist","Research":"Microbiologist","Restaurant":"Microbiologist","Confection Injection Moulding":"Moulder","Elastomer Injection Moulding":"Moulder","Glass Injection Moulding":"Moulder","Plastic Injection Moulding":"Moulder","Ambulance":"Nurse","Clinic":"Nurse","First aid kit":"Nurse","Private House":"Nurse"," Front desk":"Office Assistant\/Helper"," Inventory Incharge":"Office Assistant\/Helper"," Pantry Boy":"Office Assistant\/Helper","Clerk":"Office Assistant\/Helper","Annealing":"Operator\/Machinist","Boiler":"Operator\/Machinist","CNC Drill":"Operator\/Machinist","Control Room Operator":"Operator\/Machinist","Crane":"Operator\/Machinist","Cylinderical Grinder":"Operator\/Machinist","Elevator":"Operator\/Machinist","Excavator":"Operator\/Machinist","Finishing":"Operator\/Machinist","Fire Alarm":"Operator\/Machinist","Forklift":"Operator\/Machinist","Hydra":"Operator\/Machinist","Lathe Machine":"Operator\/Machinist","Laundry":"Operator\/Machinist","Milling Machine":"Operator\/Machinist","Molding":"Operator\/Machinist","Pump":"Operator\/Machinist","Punch Machine":"Operator\/Machinist","Stacker":"Operator\/Machinist","Thermoforming":"Operator\/Machinist","Tow Truck":"Operator\/Machinist","Tubewell":"Operator\/Machinist","Turning Machine":"Operator\/Machinist","VMC Machine":"Operator\/Machinist","Car Painter":"Painter","Exterior wall painting":"Painter","Glass Painting":"Painter","Industrial Steel Painter":"Painter","Interior wall painting":"Painter","Metal Painter":"Painter","Portrait painter":"Painter","Spray Painter":"Painter","Wood Painter":"Painter","Formulation":"Pharmacist","QC":"Pharmacist","Research & Development":"Pharmacist","Clogged Drain Pipe ":"Plumber","Drain Water System":"Plumber","Faucet Repair\/ Replacement":"Plumber","Industrial Drainage Coursework":"Plumber","Pipe Cutting":"Plumber","Pipes Securing":"Plumber","Repairing Plumbing Systems":"Plumber","Repairing\/Installing Water Heaters":"Plumber","Residential Drainage Coursework":"Plumber","Sewage Treatment":"Plumber","Shower Repair\/Replacement":"Plumber","Tap Repair\/ Replacement":"Plumber","Valves Installation":"Plumber","Water Supply Expert":"Plumber","Airport Porter":"Porter","Commercial Porter":"Porter","Corporate Porter":"Porter","Hospital Porter":"Porter","Hotel Porter":"Porter","Industrial Porter":"Porter","Railway Porter":"Porter","Residential Porter":"Porter","IT":"Sales","Marketing":"Teacher","2 wheeler evaluator":"Quality and Inspection","4 wheeler evaluator":"Quality and Inspection","Pharmaceutical":"Quality and Inspection","Reporting":"Quality and Inspection","Steel":"Quality and Inspection","Bank":"Receptionist\/Front Office","Company":"Receptionist\/Front Office","Educational Institute":"Receptionist\/Front Office","Hotel":"Receptionist\/Front Office","Industry":"Receptionist\/Front Office","Offices":"Receptionist\/Front Office","Service Stations":"Receptionist\/Front Office","Showroom":"Receptionist\/Front Office","Business":"Reporter","Crime":"Reporter","General":"Reporter","Press":"Reporter","Weather":"Reporter","Consumer Durables":"Sales","E-Commerce":"Sales","Entertainment\/Event":"Sales","FMCG":"Sales","Health":"Sales","Insurance":"Sales","Jewellery":"Sales","Laptop\/Mobile":"Sales","Pharma":"Sales","Real estate":"Sales","Shares Derivatives \/ Financial Trading":"Sales","Telecom":"Sales","Tours & Travels":"Sales","Alarm Systems":"Security\/Guard","Armed Forces":"Security\/Guard","Fire Safety":"Security\/Guard","Gun Man":"Security\/Guard","Intelligence":"Security\/Guard","Mobile\/Patrol":"Security\/Guard","Pistol Man":"Security\/Guard","Police":"Security\/Guard","Private Security":"Security\/Guard","Security Services\/Custodian":"Security\/Guard","Vigilance":"Security\/Guard","Brazing Soldering":"Soldering Operator","Breadboard Soldering":"Soldering Operator","Connector Soldering":"Soldering Operator","Electronic Soldering":"Soldering Operator","IC Soldering":"Soldering Operator","PCB Soldering":"Soldering Operator","Pipe Soldering":"Soldering Operator","SMD Soldering":"Soldering Operator","Splice Soldering":"Soldering Operator","Wave Soldering":"Soldering Operator","Commercials":"Sound Engineer","Computer Games":"Sound Engineer","Corporate Videos":"Sound Engineer","Film":"Sound Engineer","Interviews":"Sound Engineer","Podcast":"Sound Engineer","Seminars":"Sound Engineer","Short Films \/ Documentry":"Sound Engineer","Studio":"Sound Engineer","TV":"Sound Engineer","Web Series":"Sound Engineer","Adults":"Sports Trainer","Athletes":"Sports Trainer","Differently Abled":"Sports Trainer","Kids":"Sports Trainer","Teenagers":"Sports Trainer","Women":"Sports Trainer","Boutique Tailor":"Tailor","Designer Tailor":"Tailor","Gents Tailor":"Tailor","Industrial Tailor":"Tailor","Ladies Tailor":"Tailor"," Computer":"Teacher"," Hindi":"Teacher"," Math":"Teacher"," Sanskrit":"Teacher"," Science":"Teacher"," Social Science":"Teacher","Accounts":"Teacher","Arabic":"Teacher","Biology":"Teacher","Botany":"Teacher","Chemistry":"Teacher","Commerce":"Teacher","Economics":"Teacher","HR":"Teacher","Physics":"Teacher","Urdu":"Teacher","Zoology":"Teacher","Airlines":"Waiter\/Steward","Multiplex":"Ticketing Executive","Railway":"Ticketing Executive","Aerobics":"Trainer","Dance":"Trainer","Gym":"Trainer","Language":"Trainer","Martial Arts":"Trainer","Musical Instruments":"Trainer","Soft Skills":"Trainer","Sport":"Trainer","Yoga":"Trainer","Event":"Videographer","Films":"Videographer","Nature":"Videographer","Wildlife":"Videographer"," Butt Welder":"Welder"," Mig and Tig Welder":"Welder"," Spot Welder":"Welder","Arc Welder ":"Welder","Automotive Welders":"Welder","Pipe Welders":"Welder"," Commercial":"Wireman"," Residential":"Wireman"}';
        $occupationList = ArrayHelper::jsonToArray($occupationJson);
        return array_get($occupationList, $profession, '');
    }

    public function initUserInfo($data) {
        $userId = Auth::id();
        UserAuthServer::server()->setAuth($userId, UserAuth::TYPE_BASE_INFO);
        return UserInfo::updateOrCreateModel(UserInfo::SCENARIO_CREATE, ['user_id' => $userId], $data);
    }

    public function buildOccupationByProfession()
    {
        $occupationArr = [];
        $professionJson = '{"Accounting":["Clerk/Book Keeper","Audit","Taxation"],"Security":["Bouncer","Security"],"Law/Legal":["Legal Consultant","Law Consultant"],"Service":["Bartender","BPO/ Telecaller","Cashier","Counsellor","Food Delivery","Courier Delivery/Collection","Other Delivery/Collection","Documents Collection","Grocery Delivery","Floor Manager","Steward","Sales","Porter","Tailor","Ticketing Executive","Waiter"],"Administrative / Clerical":["DTP Operator/Print Consultant","Language Translator","Office Assistant/Helper","Receptionist/Front Office"],"Education":["Teacher"],"Training Course":["Sports Trainer","Trainer"],"Construction":["Carpenter","Draftsman","Painter"],"Production":["Cameraman"],"Creative Arts":["Dubbing Artist"],"Research/Development":["Lab Assistant","Medical Assistant","Microbiologist"],"Technician":["Electrician","Electroplater","Fitter","Mechanic","Moulder","Operator/Machinist","Plumber","Soldering Operator","Welder","Wireman"],"IT / Development":["Database/System/Linux Administrator","IT Hardware & Network Engineer","IT Software - Developer","IT Software - QA/Test Engineer","IT Software - Web Designer"],"Sales":["Retail Sales","Financial Sales","Car Sales","Insurance Sales","Real estate Sales","Medical Sales"],"Food / Beverages / Catering":["Cook/Chef"],"Design":["Architect","Fashion Designer","Designer"],"Beauty Service":["Beautician","Hairdresser"],"HR / Recruiting":["HR / Recruiting"],"Driver":["Company driver","Taxi/Cab/Private Driver","Commercial Transport Driver","Heavy Vehicle Driver","Ambulance Driver","School Bus Driver"],"Healthcare / Medical":["Mental Health Counsellor","Rehabilitation Counsellor","Doctor","Pet Care/ Veterinary","Nurse","Pharmacist","Care Worker"],"Maintenance/Cleaning":["Cleaner/Washer","Gardener"],"Finance":["Financial Consultant","Recovery Collection","Product Manager"],"Manufacturing":["Quality and Inspection"],"Business":["Business Analyst","Data Collection/Survey"],"Multimedia":["Animation","Content Writer","Reporter","Sound Engineer","Videographer"],"Marketing":["Event Planner","Marketing"]}';
        $professions = ArrayHelper::jsonToArray($professionJson);
        foreach ($professions as $professionVal => $profession) {
            foreach ($profession as $occupation) {
                $occupationArr[$occupation] = $professionVal;
            }
        }
        echo json_encode($occupationArr, 256);
        exit();
    }
    public function initUserContact($data) {
        $relation1 = array_get($data, 'relation1');
        $relation2 = array_get($data, 'relation2');

        /** @var $user User */
        $user = Auth::user();
        DB::transaction(function () use ($data, $user) {

            $extend1 = $this->getExtend(array_get($data, 'extend1'));
            $extend2 = $this->getExtend(array_get($data, 'extend2'));
            UserContact::model()->clear($user->id);
            UserContact::model()->setScenario(UserContact::SCENARIO_CREATE)->saveModels([
                [
                    'user_id' => $user->id,
                    'contact_fullname' => array_get($data, 'contactFullname1'),
                    'contact_telephone' => array_get($data, 'contactTelephone1'),
                    'relation' => array_get($data, 'relation1'),
                    'times_contacted' => array_get($extend1, 'timesContacted'),
                    'last_time_contacted' => array_get($extend1, 'lastTimeContacted'),
                    'has_phone_number' => array_get($extend1, 'hasPhoneNumber'),
                    'starred' => array_get($extend1, 'starred'),
                    'contact_last_updated_timestamp' => array_get($extend1, 'contactLastUpdatedTimestamp'),
                ],
                [
                    'user_id' => $user->id,
                    'contact_fullname' => array_get($data, 'contactFullname2'),
                    'contact_telephone' => array_get($data, 'contactTelephone2'),
                    'relation' => array_get($data, 'relation2'),
                    'times_contacted' => array_get($extend2, 'timesContacted'),
                    'last_time_contacted' => array_get($extend2, 'lastTimeContacted'),
                    'has_phone_number' => array_get($extend2, 'hasPhoneNumber'),
                    'starred' => array_get($extend2, 'starred'),
                    'contact_last_updated_timestamp' => array_get($extend2, 'contactLastUpdatedTimestamp'),
                ],
                    ], true, true);
            UserAuthServer::server()->setAuth($user, UserAuth::TYPE_CONTACTS);
        });
        return $this->outputSuccess();
    }

    public function addUserContact($data, $userId, $isSupplement = false)
    {
        DB::transaction(function () use ($data, $userId, $isSupplement) {

            $extend = $this->getExtend(array_get($data, 'extend'));
            UserContact::model()->setScenario(UserContact::SCENARIO_CREATE)->saveModels([
                [
                    'user_id' => $userId,
                    'contact_fullname' => array_get($data, 'contactFullname'),
                    'contact_telephone' => array_get($data, 'contactTelephone'),
                    'contact_telephone_10P' => substr(array_get($data, 'contactTelephone'), -10),
                    'relation' => array_get($data, 'relation'),
                    'times_contacted' => array_get($extend, 'timesContacted'),
                    'last_time_contacted' => array_get($extend, 'lastTimeContacted'),
                    'has_phone_number' => array_get($extend, 'hasPhoneNumber'),
                    'starred' => array_get($extend, 'starred'),
                    'contact_last_updated_timestamp' => array_get($extend, 'contactLastUpdatedTimestamp'),
                    'manual_call_result' => array_get($data, 'manualCallResult'),
                    'is_supplement' => intval($isSupplement),
                ]
                    ], true, true);
        });
        return $this->outputSuccess();
    }

    public function clearUserContact($userId){
        UserContact::model()->clear($userId);
    }
}
