<?php

namespace Risk\Common\Helper;

use Common\Utils\Helper;

/**
 * 印度行政区域划分。??后表示其他可能的名称
 * 最前面全大写的 名称来自 pincode表，本类中的cityList()方法中的邦名也根据pincode表进行了统一处理
 *
 * 邦(pincode表)               百度经纬度识别结果   名称      name           首府        人口     面积(平方公里)  官方语言
 *
 * ANDHRA PRADESH              Andhra Pradesh    => 安得拉邦    Andhra Pradesh    海得拉巴    49506799    160205    泰卢固语
 * ARUNACHAL PRADESH           Arunachal Pradesh => + 阿鲁纳恰尔邦  Arunachal Pradesh   伊塔那噶    1382611    83743   英语
 * ASSAM                       Assam             => 阿萨姆邦    Assam    迪斯布尔    31205576    78550    阿萨姆语
 * BIHAR                       Bihar ?? Bihari Colony  => 比哈尔邦    Bihār    巴特那    104099452    94163    印地语
 * CHATTISGARH ?? Chhatisgarh  Chhattisgarh      => 恰蒂斯加尔邦    Chhatisgarh    赖布尔    25545198    135194    印地语
 * GOA                         Goa ?? South Goa ?? North Goa ?? Goa Bagan    => 果阿邦    Goa    帕那吉    1458545    3702    贡根语
 * GUJARAT                     Gujarat           => 古吉拉特邦    Gujarat    甘地讷格尔    60439692    196024    古吉拉特语
 * HARYANA                     Haryana           => 哈里亚纳邦    Haryana    昌迪加尔[1]    25351462    44212    印地语
 * HIMACHAL PRADESH            Himachal Pradesh  => 喜马偕尔邦    Himāchal Pradesh    西姆拉    6864602    55673    印地语
 * JHARKHAND                   Jharkhand         => 贾坎德邦    Jharkhand    兰契    32988134    74677    印地语
 * KARNATAKA                   Karnataka         => 卡纳塔克邦    Karnātaka    班加罗尔    61095297    191791    卡纳塔克语
 * KERALA                      Kerala            => 喀拉拉邦    Kerala    特里凡得琅    33406061    38863    马拉雅拉姆语
 * MADHYA PRADESH              Madhya Pradesh    => 中央邦    Madhya Pradesh    博帕尔    72626809    308252    印地语
 * MAHARASHTRA                 Maharashtra       => 马哈拉施特拉邦    Maharashtra    孟买    112374333    307713    马拉提语
 * MANIPUR                     Manipur           => 曼尼普尔邦    Manipur    因帕尔    2855794    22347    曼尼普尔语
 * MEGHALAYA                   Meghalaya         => 梅加拉亚邦    Meghālaya    西隆    2966889    22720    英语
 * MIZORAM                                       => 米佐拉姆邦    Mizorām    艾藻尔    1097206    21081    英语、印地语、米佐语
 * NAGALAND                    Nagaland          => 那加兰邦    Nāgāland    科希马    1978502    16579    英语
 * ODISHA  ??  Orissa          Odisha            => 奥里萨邦    Odisha  (旧)Orissa    布巴内斯瓦尔    41974218    155820    奥里萨语
 * PUNJAB ?? Punjub            Punjab            => 旁遮普邦    Punjub    昌迪加尔[2]    27743338    50362    旁遮普语
 * RAJASTHAN                   Rajasthan         => 拉贾斯坦邦    Rājasthān    斋浦尔    68548437    342269    印地语
 * SIKKIM                      Sikkim            => 锡金邦    Sikkim    甘托克    610577    7096    英语
 * TAMIL NADU                  Tamil Nadu        => 泰米尔纳德邦    Tamil Nādu    金奈    72147030    130058    泰米尔语
 * TELANGANA                   Telangana         => + 特伦甘纳邦  Telangana   海得拉巴  35193978  114,840  泰卢固语、乌尔都语
 * TRIPURA                     Tripura ?? West Tripura   => 特里普拉邦    Tripura    阿加尔塔拉    3673917    10492    廓博罗克语、孟加拉语、英语
 * UTTAR PRADESH               Uttar Pradesh     => 北方邦    Uttar Pradesh    勒克瑙    199812341    243286    印地语
 * UTTARAKHAND                 Uttarakhand       => 北阿坎德邦    Uttarakhand    德拉敦    10086292    53483    印地语
 * WEST BENGAL                 West Bengal       => 西孟加拉邦    West Bengal    加尔各答    91276115    88752    尼泊尔语、孟加拉语
 *
 * 中央直辖区
 * ANDAMAN & NICOBAR ISLANDS ?? ANDAMAN AND NICOBAR ISLANDS                             => 安达曼-尼科巴群岛    Andaman & Nicobar Islands    布莱尔港    380581    8249    印地语、英语
 * CHANDIGARH                                                  Chandigarh               => 昌迪加尔    Chandīgarh    昌迪加尔    1055450    114    英语
 * DADRA & NAGAR HAVELI ?? DADRA AND NAGAR HAVELI              Dadra and Nagar Haveli   => 达德拉-纳加尔哈维利    Dādra & Nagar Haveli    锡尔瓦萨    343709    491    古吉拉特语、印地语
 * DAMAN & DIU ?? DAMAN AND DIU                                Daman and Diu ?? Daman   => 达曼第乌    Damān & Diu    达曼    243247    112    英语、古吉拉特语、印地语、贡根语
 * LAKSHADWEEP                                                                          => 拉克沙群岛    Lakshadweep    卡瓦拉蒂    64473    32    英语
 * JAMMU & KASHMIR ?? JAMMU AND KASHMIR                                                 => +查谟和克什米尔  Jammu and Kashmir  12,541,302   //   印地语, 英语
 * DELHI                                                       Delhi ?? North West Delhi ?? New Delhi ... => 德里    Delhi    新德里    16787941    1490    印地语
 * PONDICHERRY ?? Puducherry                                   Pondicherry ?? Puducherry => 本地治里    Pondicherry    本地治里    1247953    492    英语、泰米尔语
 */
class CityHelper
{
    use Helper;

    const ANDHRA_PRADESH = 'Andhra Pradesh';
    const ARUNACHAL_PRADESH = 'Arunachal Pradesh';
    const ASSAM = 'Assam';
    const BIHAR = 'Bihar';
    const CHATTISGARH = 'Chattisgarh';
    const GOA = 'Goa';
    const GUJARAT = 'Gujarat';
    const HARYANA = 'Haryana';
    const HIMACHAL_PRADESH = 'Himachal Pradesh';
    const JHARKHAND = 'Jharkhand';
    const KARNATAKA = 'Karnataka';
    const KERALA = 'Kerala';
    const MADHYA_PRADESH = 'Madhya Pradesh';
    const MAHARASHTRA = 'Maharashtra';
    const MANIPUR = 'Manipur';
    const MEGHALAYA = 'Meghalaya';
    const MIZORAM = 'Mizoram';
    const NAGALAND = 'Nagaland';
    const ODISHA = 'Odisha';
    const PUNJAB = 'Punjab';
    const RAJASTHAN = 'Rajasthan';
    const SIKKIM = 'Sikkim';
    const TAMIL_NADU = 'Tamil Nadu';
    const TELANGANA = 'Telangana';
    const TRIPURA = 'Tripura';
    const UTTAR_PRADESH = 'Uttar Pradesh';
    const UTTARAKHAND = 'Uttarakhand';
    const WEST_BENGAL = 'West Bengal';
    const ANDAMAN_AND_NICOBAR_ISLANDS = 'Andaman & Nicobar Islands';
    const CHANDIGARH = 'Chandigarh';
    const DADRA_AND_NAGAR_HAVELI = 'Dadra & Nagar Haveli';
    const DAMAN_AND_DIU = 'Daman & Diu';
    const LAKSHADWEEP = 'Lakshadweep';
    const JAMMU_AND_KASHMIR = 'Jammu & Kashmir';
    const DELHI = 'Delhi';
    const PONDICHERRY = 'Pondicherry';

    public function getCityList($state = '')
    {
        $cityList = collect($this->cityList());

        if (!$state) {
            $cityList = $cityList->flatten();
        } else {
            $cityList = collect($cityList->get($state));
        }

        return $cityList->unique()->sort()->values();
    }

    protected function cityList()
    {
        $data = [
            self::ANDAMAN_AND_NICOBAR_ISLANDS => ["Nicobar", "North Middle Andaman", "South Andaman"],
            self::ANDHRA_PRADESH => ["Chittoor", "Anantapur", "East Godavari", "Guntur", "Kadapa", "Krishna", "Kurnool", "Nellore", "Prakasam", "Srikakulam", "Visakhapatnam", "Vizianagaram", "West Godavari"],
            self::ARUNACHAL_PRADESH => ["Anjaw", "Central Siang", "Changlang", "Dibang Valley", "East Kameng", "East Siang", "Kra Daadi", "Kurung Kumey", "Lohit", "Longding", "Lower Dibang Valley", "Lower Siang", "Lower Subansiri", "Namsai", "Papum Pare", "Tawang", "Tirap", "Upper Siang", "Upper Subansiri", "West Kameng", "West Siang"],
            self::ASSAM => ["Baksa", "Barpeta", "Biswanath", "Bongaigaon", "Cachar", "Charaideo", "Chirang", "Darrang", "Dhemaji", "Dhubri", "Dibrugarh", "Dima Hasao", "Goalpara", "Golaghat", "Hailakandi", "Hojai", "Jorhat", "Kamrup", "Kamrup Metropolitan", "Karbi Anglong", "Karimganj", "Kokrajhar", "Lakhimpur", "Majuli", "Morigaon", "Nagaon", "Nalbari", "Sivasagar", "Sonitpur", "South Salmara-Mankachar", "Tinsukia", "Udalguri", "West Karbi Anglong"],
            self::BIHAR => ["Araria", "Arwal", "Aurangabad", "Banka", "Begusarai", "Bhagalpur", "Bhojpur", "Buxar", "Darbhanga", "East Champaran", "Gaya", "Gopalganj", "Jamui", "Jehanabad", "Kaimur", "Katihar", "Khagaria", "Kishanganj", "Lakhisarai", "Madhepura", "Madhubani", "Munger", "Muzaffarpur", "Nalanda", "Nawada", "Patna", "Purnia", "Rohtas", "Saharsa", "Samastipur", "Saran", "Sheikhpura", "Sheohar", "Sitamarhi", "Siwan", "Supaul", "Vaishali", "West Champaran"],
            self::CHANDIGARH => ["Chandigarh"],
            self::CHATTISGARH => ["Balod", "Baloda Bazar", "Balrampur", "Bastar", "Bemetara", "Bijapur", "Bilaspur", "Dantewada", "Dhamtari", "Durg", "Gariaband", "Janjgir Champa", "Jashpur", "Kabirdham", "Kanker", "Kondagaon", "Korba", "Koriya", "Mahasamund", "Mungeli", "Narayanpur", "Raigarh", "Raipur", "Rajnandgaon", "Sukma", "Surajpur", "Surguja"],
            self::DADRA_AND_NAGAR_HAVELI => ["Dadra & Nagar Haveli"],
            self::DAMAN_AND_DIU => ["Daman", "Diu"],
            self::DELHI => ["Central Delhi", "East Delhi", "New Delhi", "North Delhi", "North East Delhi", "North West Delhi", "Shahdara", "South Delhi", "South East Delhi", "South West Delhi", "West Delhi"],
            self::GOA => ["North Goa", "South Goa"],
            self::GUJARAT => ["Ahmedabad", "Amreli", "Anand", "Aravalli", "Banaskantha", "Bharuch", "Bhavnagar", "Botad", "Chhota Udaipur", "Dahod", "Dang", "Devbhoomi Dwarka", "Gandhinagar", "Gir Somnath", "Jamnagar", "Junagadh", "Kheda", "Kutch", "Mahisagar", "Mehsana", "Morbi", "Narmada", "Navsari", "Panchmahal", "Patan", "Porbandar", "Rajkot", "Sabarkantha", "Surat", "Surendranagar", "Tapi", "Vadodara", "Valsad"],
            self::HARYANA => ["Ambala", "Bhiwani", "Charkhi Dadri", "Faridabad", "Fatehabad", "Gurugram", "Hisar", "Jhajjar", "Jind", "Kaithal", "Karnal", "Kurukshetra", "Mahendragarh", "Mewat", "Palwal", "Panchkula", "Panipat", "Rewari", "Rohtak", "Sirsa", "Sonipat", "Yamunanagar"],
            self::HIMACHAL_PRADESH => ["Bilaspur", "Chamba", "Hamirpur", "Kangra", "Kinnaur", "Kullu", "Lahaul Spiti", "Mandi", "Shimla", "Sirmaur", "Solan", "Una"],
            self::JAMMU_AND_KASHMIR => ["Anantnag", "Bandipora", "Baramulla", "Budgam", "Doda", "Ganderbal", "Jammu", "Kargil", "Kathua", "Kishtwar", "Kulgam", "Kupwara", "Leh", "Poonch", "Pulwama", "Rajouri", "Ramban", "Reasi", "Samba", "Shopian", "Srinagar", "Udhampur"],
            self::JHARKHAND => ["Bokaro", "Chatra", "Deoghar", "Dhanbad", "Dumka", "East Singhbhum", "Garhwa", "Giridih", "Godda", "Gumla", "Hazaribagh", "Jamtara", "Khunti", "Koderma", "Latehar", "Lohardaga", "Pakur", "Palamu", "Ramgarh", "Ranchi", "Sahebganj", "Seraikela Kharsawan", "Simdega", "West Singhbhum"],
            self::KARNATAKA => ["Bagalkot", "Bangalore Rural", "Bangalore Urban", "Belgaum", "Bellary", "Bidar", "Vijayapura ", "Chamarajanagar", "Chikkaballapur", "Chikkamagaluru", "Chitradurga", "Dakshina Kannada", "Davanagere", "Dharwad", "Gadag", "Gulbarga", "Hassan", "Haveri", "Kodagu", "Kolar", "Koppal", "Mandya", "Mysore", "Raichur", "Ramanagara", "Shimoga", "Tumkur", "Udupi", "Uttara Kannada", "Yadgir"],
            self::KERALA => ["Alappuzha", "Ernakulam", "Idukki", "Kannur", "Kasaragod", "Kollam", "Kottayam", "Kozhikode", "Malappuram", "Palakkad", "Pathanamthitta", "Thiruvananthapuram", "Thrissur", "Wayanad"],
            self::LAKSHADWEEP => ["Lakshadweep"],
            self::MADHYA_PRADESH => ["Agar Malwa", "Alirajpur", "Anuppur", "Ashoknagar", "Balaghat", "Barwani", "Betul", "Bhind", "Bhopal", "Burhanpur", "Chhatarpur", "Chhindwara", "Damoh", "Datia", "Dewas", "Dhar", "Dindori", "Guna", "Gwalior", "Harda", "Hoshangabad", "Indore", "Jabalpur", "Jhabua", "Katni", "Khandwa", "Khargone", "Mandla", "Mandsaur", "Morena", "Narsinghpur", "Neemuch", "Panna", "Raisen", "Rajgarh", "Ratlam", "Rewa", "Sagar", "Satna", "Sehore", "Seoni", "Shahdol", "Shajapur", "Sheopur", "Shivpuri", "Sidhi", "Singrauli", "Tikamgarh", "Ujjain", "Umaria", "Vidisha"],
            self::MAHARASHTRA => ["Ahmednagar", "Akola", "Amravati", "Aurangabad", "Beed", "Bhandara", "Buldhana", "Chandrapur", "Dhule", "Gadchiroli", "Gondia", "Hingoli", "Jalgaon", "Jalna", "Kolhapur", "Latur", "Mumbai City", "Mumbai Suburban", "Nagpur", "Nanded", "Nandurbar", "Nashik", "Osmanabad", "Palghar", "Parbhani", "Pune", "Raigad", "Ratnagiri", "Sangli", "Satara", "Sindhudurg", "Solapur", "Thane", "Wardha", "Washim", "Yavatmal"],
            self::MANIPUR => ["Bishnupur", "Chandel", "Churachandpur", "Imphal East", "Imphal West", "Jiribam", "Kakching", "Kamjong", "Kangpokpi", "Noney", "Pherzawl", "Senapati", "Tamenglong", "Tengnoupal", "Thoubal", "Ukhrul"],
            self::MEGHALAYA => ["East Garo Hills", "East Jaintia Hills", "East Khasi Hills", "North Garo Hills", "Ri Bhoi", "South Garo Hills", "South West Garo Hills", "South West Khasi Hills", "West Garo Hills", "West Jaintia Hills", "West Khasi Hills"],
            self::MIZORAM => ["Aizawl", "Champhai", "Kolasib", "Lawngtlai", "Lunglei", "Mamit", "Saiha", "Serchhip"],
            self::NAGALAND => ["Dimapur", "Kiphire", "Kohima", "Longleng", "Mokokchung", "Mon", "Peren", "Phek", "Tuensang", "Wokha", "Zunheboto"],
            self::ODISHA => ["Angul", "Balangir", "Balasore", "Bargarh", "Bhadrak", "Boudh", "Cuttack", "Debagarh", "Dhenkanal", "Gajapati", "Ganjam", "Jagatsinghpur", "Jajpur", "Jharsuguda", "Kalahandi", "Kandhamal", "Kendrapara", "Kendujhar", "Khordha", "Koraput", "Malkangiri", "Mayurbhanj", "Nabarangpur", "Nayagarh", "Nuapada", "Puri", "Rayagada", "Sambalpur", "Subarnapur", "Sundergarh"],
            self::PONDICHERRY => ["Karaikal", "Mahe", "Puducherry", "Yanam"],
            self::PUNJAB => ["Amritsar", "Barnala", "Bathinda", "Faridkot", "Fatehgarh Sahib", "Fazilka", "Firozpur", "Gurdaspur", "Hoshiarpur", "Jalandhar", "Kapurthala", "Ludhiana", "Mansa", "Moga", "Mohali", "Muktsar", "Pathankot", "Patiala", "Rupnagar", "Sangrur", "Shaheed Bhagat Singh Nagar", "Tarn Taran"],
            self::RAJASTHAN => ["Ajmer", "Alwar", "Banswara", "Baran", "Barmer", "Bharatpur", "Bhilwara", "Bikaner", "Bundi", "Chittorgarh", "Churu", "Dausa", "Dholpur", "Dungarpur", "Ganganagar", "Hanumangarh", "Jaipur", "Jaisalmer", "Jalore", "Jhalawar", "Jhunjhunu", "Jodhpur", "Karauli", "Kota", "Nagaur", "Pali", "Pratapgarh", "Rajsamand", "Sawai Madhopur", "Sikar", "Sirohi", "Tonk", "Udaipur"],
            self::SIKKIM => ["East Sikkim", "North Sikkim", "South Sikkim", "West Sikkim"],
            self::TAMIL_NADU => ["Ariyalur", "Chennai", "Coimbatore", "Cuddalore", "Dharmapuri", "Dindigul", "Erode", "Kanchipuram", "Kanyakumari", "Karur", "Krishnagiri", "Madurai", "Nagapattinam", "Namakkal", "Nilgiris", "Perambalur", "Pudukkottai", "Ramanathapuram", "Salem", "Sivaganga", "Thanjavur", "Theni", "Thoothukudi", "Tiruchirappalli", "Tirunelveli", "Tiruppur", "Tiruvallur", "Tiruvannamalai", "Tiruvarur", "Vellore", "Viluppuram", "Virudhunagar"],
            self::TELANGANA => ["Adilabad", "Bhadradri Kothagudem", "Hyderabad", "Jagtial", "Jangaon", "Jayashankar", "Jogulamba", "Kamareddy", "Karimnagar", "Khammam", "Komaram Bheem", "Mahabubabad", "Mahbubnagar", "Mancherial", "Medak", "Medchal", "Nagarkurnool", "Nalgonda", "Nirmal", "Nizamabad", "Peddapalli", "Rajanna Sircilla", "Ranga Reddy", "Sangareddy", "Siddipet", "Suryapet", "Vikarabad", "Wanaparthy", "Warangal Rural", "Warangal Urban", "Yadadri Bhuvanagiri"],
            self::TRIPURA => ["Dhalai", "Gomati", "Khowai", "North Tripura", "Sepahijala", "South Tripura", "Unakoti", "West Tripura"],
            self::UTTAR_PRADESH => ["Agra", "Aligarh", "Allahabad", "Ambedkar Nagar", "Amethi", "Amroha", "Auraiya", "Azamgarh", "Baghpat", "Bahraich", "Ballia", "Balrampur", "Banda", "Barabanki", "Bareilly", "Basti", "Bhadohi", "Bijnor", "Budaun", "Bulandshahr", "Chandauli", "Chitrakoot", "Deoria", "Etah", "Etawah", "Faizabad", "Farrukhabad", "Fatehpur", "Firozabad", "Gautam Buddha Nagar", "Ghaziabad", "Ghazipur", "Gonda", "Gorakhpur", "Hamirpur", "Hapur", "Hardoi", "Hathras", "Jalaun", "Jaunpur", "Jhansi", "Kannauj", "Kanpur Dehat", "Kanpur Nagar", "Kasganj", "Kaushambi", "Kheri", "Kushinagar", "Lalitpur", "Lucknow", "Maharajganj", "Mahoba", "Mainpuri", "Mathura", "Mau", "Meerut", "Mirzapur", "Moradabad", "Muzaffarnagar", "Pilibhit", "Pratapgarh", "Raebareli", "Rampur", "Saharanpur", "Sambhal", "Sant Kabir Nagar", "Shahjahanpur", "Shamli", "Shravasti", "Siddharthnagar", "Sitapur", "Sonbhadra", "Sultanpur", "Unnao", "Varanasi"],
            self::UTTARAKHAND => ["Almora", "Bageshwar", "Chamoli", "Champawat", "Dehradun", "Haridwar", "Nainital", "Pauri", "Pithoragarh", "Rudraprayag", "Tehri", "Udham Singh Nagar", "Uttarkashi"],
            self::WEST_BENGAL => ["Alipurduar", "Bankura", "Birbhum", "Cooch Behar", "Dakshin Dinajpur", "Darjeeling", "Hooghly", "Howrah", "Jalpaiguri", "Jhargram", "Kalimpong", "Kolkata", "Malda", "Murshidabad", "Nadia", "North 24 Parganas", "Paschim Bardhaman", "Paschim Medinipur", "Purba Bardhaman", "Purba Medinipur", "Purulia", "South 24 Parganas", "Uttar Dinajpur"],
        ];

        return $data;

        $jsonStr = <<<json
{
	"Andaman & Nicobar Islands": ["Nicobar", "North Middle Andaman", "South Andaman"],
	"Andhra Pradesh": ["Chittoor", "Anantapur", "East Godavari", "Guntur", "Kadapa", "Krishna", "Kurnool", "Nellore", "Prakasam", "Srikakulam", "Visakhapatnam", "Vizianagaram", "West Godavari"],
	"Arunachal Pradesh": ["Anjaw", "Central Siang", "Changlang", "Dibang Valley", "East Kameng", "East Siang", "Kra Daadi", "Kurung Kumey", "Lohit", "Longding", "Lower Dibang Valley", "Lower Siang", "Lower Subansiri", "Namsai", "Papum Pare", "Tawang", "Tirap", "Upper Siang", "Upper Subansiri", "West Kameng", "West Siang"],
	"Assam": ["Baksa", "Barpeta", "Biswanath", "Bongaigaon", "Cachar", "Charaideo", "Chirang", "Darrang", "Dhemaji", "Dhubri", "Dibrugarh", "Dima Hasao", "Goalpara", "Golaghat", "Hailakandi", "Hojai", "Jorhat", "Kamrup", "Kamrup Metropolitan", "Karbi Anglong", "Karimganj", "Kokrajhar", "Lakhimpur", "Majuli", "Morigaon", "Nagaon", "Nalbari", "Sivasagar", "Sonitpur", "South Salmara-Mankachar", "Tinsukia", "Udalguri", "West Karbi Anglong"],
	"Bihar": ["Araria", "Arwal", "Aurangabad", "Banka", "Begusarai", "Bhagalpur", "Bhojpur", "Buxar", "Darbhanga", "East Champaran", "Gaya", "Gopalganj", "Jamui", "Jehanabad", "Kaimur", "Katihar", "Khagaria", "Kishanganj", "Lakhisarai", "Madhepura", "Madhubani", "Munger", "Muzaffarpur", "Nalanda", "Nawada", "Patna", "Purnia", "Rohtas", "Saharsa", "Samastipur", "Saran", "Sheikhpura", "Sheohar", "Sitamarhi", "Siwan", "Supaul", "Vaishali", "West Champaran"],
	"Chandigarh": ["Chandigarh"],
	"Chattisgarh": ["Balod", "Baloda Bazar", "Balrampur", "Bastar", "Bemetara", "Bijapur", "Bilaspur", "Dantewada", "Dhamtari", "Durg", "Gariaband", "Janjgir Champa", "Jashpur", "Kabirdham", "Kanker", "Kondagaon", "Korba", "Koriya", "Mahasamund", "Mungeli", "Narayanpur", "Raigarh", "Raipur", "Rajnandgaon", "Sukma", "Surajpur", "Surguja"],
	"Dadra & Nagar Haveli": ["Dadra & Nagar Haveli"],
	"Daman & Diu": ["Daman", "Diu"],
	"Delhi": ["Central Delhi", "East Delhi", "New Delhi", "North Delhi", "North East Delhi", "North West Delhi", "Shahdara", "South Delhi", "South East Delhi", "South West Delhi", "West Delhi"],
	"Goa": ["North Goa", "South Goa"],
	"Gujarat": ["Ahmedabad", "Amreli", "Anand", "Aravalli", "Banaskantha", "Bharuch", "Bhavnagar", "Botad", "Chhota Udaipur", "Dahod", "Dang", "Devbhoomi Dwarka", "Gandhinagar", "Gir Somnath", "Jamnagar", "Junagadh", "Kheda", "Kutch", "Mahisagar", "Mehsana", "Morbi", "Narmada", "Navsari", "Panchmahal", "Patan", "Porbandar", "Rajkot", "Sabarkantha", "Surat", "Surendranagar", "Tapi", "Vadodara", "Valsad"],
	"Haryana": ["Ambala", "Bhiwani", "Charkhi Dadri", "Faridabad", "Fatehabad", "Gurugram", "Hisar", "Jhajjar", "Jind", "Kaithal", "Karnal", "Kurukshetra", "Mahendragarh", "Mewat", "Palwal", "Panchkula", "Panipat", "Rewari", "Rohtak", "Sirsa", "Sonipat", "Yamunanagar"],
	"Himachal Pradesh": ["Bilaspur", "Chamba", "Hamirpur", "Kangra", "Kinnaur", "Kullu", "Lahaul Spiti", "Mandi", "Shimla", "Sirmaur", "Solan", "Una"],
	"Jammu & Kashmir": ["Anantnag", "Bandipora", "Baramulla", "Budgam", "Doda", "Ganderbal", "Jammu", "Kargil", "Kathua", "Kishtwar", "Kulgam", "Kupwara", "Leh", "Poonch", "Pulwama", "Rajouri", "Ramban", "Reasi", "Samba", "Shopian", "Srinagar", "Udhampur"],
	"Jharkhand": ["Bokaro", "Chatra", "Deoghar", "Dhanbad", "Dumka", "East Singhbhum", "Garhwa", "Giridih", "Godda", "Gumla", "Hazaribagh", "Jamtara", "Khunti", "Koderma", "Latehar", "Lohardaga", "Pakur", "Palamu", "Ramgarh", "Ranchi", "Sahebganj", "Seraikela Kharsawan", "Simdega", "West Singhbhum"],
	"Karnataka": ["Bagalkot", "Bangalore Rural", "Bangalore Urban", "Belgaum", "Bellary", "Bidar", "Vijayapura ", "Chamarajanagar", "Chikkaballapur", "Chikkamagaluru", "Chitradurga", "Dakshina Kannada", "Davanagere", "Dharwad", "Gadag", "Gulbarga", "Hassan", "Haveri", "Kodagu", "Kolar", "Koppal", "Mandya", "Mysore", "Raichur", "Ramanagara", "Shimoga", "Tumkur", "Udupi", "Uttara Kannada", "Yadgir"],
	"Kerala": ["Alappuzha", "Ernakulam", "Idukki", "Kannur", "Kasaragod", "Kollam", "Kottayam", "Kozhikode", "Malappuram", "Palakkad", "Pathanamthitta", "Thiruvananthapuram", "Thrissur", "Wayanad"],
	"Lakshadweep": ["Lakshadweep"],
	"Madhya Pradesh": ["Agar Malwa", "Alirajpur", "Anuppur", "Ashoknagar", "Balaghat", "Barwani", "Betul", "Bhind", "Bhopal", "Burhanpur", "Chhatarpur", "Chhindwara", "Damoh", "Datia", "Dewas", "Dhar", "Dindori", "Guna", "Gwalior", "Harda", "Hoshangabad", "Indore", "Jabalpur", "Jhabua", "Katni", "Khandwa", "Khargone", "Mandla", "Mandsaur", "Morena", "Narsinghpur", "Neemuch", "Panna", "Raisen", "Rajgarh", "Ratlam", "Rewa", "Sagar", "Satna", "Sehore", "Seoni", "Shahdol", "Shajapur", "Sheopur", "Shivpuri", "Sidhi", "Singrauli", "Tikamgarh", "Ujjain", "Umaria", "Vidisha"],
	"Maharashtra": ["Ahmednagar", "Akola", "Amravati", "Aurangabad", "Beed", "Bhandara", "Buldhana", "Chandrapur", "Dhule", "Gadchiroli", "Gondia", "Hingoli", "Jalgaon", "Jalna", "Kolhapur", "Latur", "Mumbai City", "Mumbai Suburban", "Nagpur", "Nanded", "Nandurbar", "Nashik", "Osmanabad", "Palghar", "Parbhani", "Pune", "Raigad", "Ratnagiri", "Sangli", "Satara", "Sindhudurg", "Solapur", "Thane", "Wardha", "Washim", "Yavatmal"],
	"Manipur": ["Bishnupur", "Chandel", "Churachandpur", "Imphal East", "Imphal West", "Jiribam", "Kakching", "Kamjong", "Kangpokpi", "Noney", "Pherzawl", "Senapati", "Tamenglong", "Tengnoupal", "Thoubal", "Ukhrul"],
	"Meghalaya": ["East Garo Hills", "East Jaintia Hills", "East Khasi Hills", "North Garo Hills", "Ri Bhoi", "South Garo Hills", "South West Garo Hills", "South West Khasi Hills", "West Garo Hills", "West Jaintia Hills", "West Khasi Hills"],
	"Mizoram": ["Aizawl", "Champhai", "Kolasib", "Lawngtlai", "Lunglei", "Mamit", "Saiha", "Serchhip"],
	"Nagaland": ["Dimapur", "Kiphire", "Kohima", "Longleng", "Mokokchung", "Mon", "Peren", "Phek", "Tuensang", "Wokha", "Zunheboto"],
	"Odisha": ["Angul", "Balangir", "Balasore", "Bargarh", "Bhadrak", "Boudh", "Cuttack", "Debagarh", "Dhenkanal", "Gajapati", "Ganjam", "Jagatsinghpur", "Jajpur", "Jharsuguda", "Kalahandi", "Kandhamal", "Kendrapara", "Kendujhar", "Khordha", "Koraput", "Malkangiri", "Mayurbhanj", "Nabarangpur", "Nayagarh", "Nuapada", "Puri", "Rayagada", "Sambalpur", "Subarnapur", "Sundergarh"],
	"Pondicherry": ["Karaikal", "Mahe", "Puducherry", "Yanam"],
	"Punjab": ["Amritsar", "Barnala", "Bathinda", "Faridkot", "Fatehgarh Sahib", "Fazilka", "Firozpur", "Gurdaspur", "Hoshiarpur", "Jalandhar", "Kapurthala", "Ludhiana", "Mansa", "Moga", "Mohali", "Muktsar", "Pathankot", "Patiala", "Rupnagar", "Sangrur", "Shaheed Bhagat Singh Nagar", "Tarn Taran"],
	"Rajasthan": ["Ajmer", "Alwar", "Banswara", "Baran", "Barmer", "Bharatpur", "Bhilwara", "Bikaner", "Bundi", "Chittorgarh", "Churu", "Dausa", "Dholpur", "Dungarpur", "Ganganagar", "Hanumangarh", "Jaipur", "Jaisalmer", "Jalore", "Jhalawar", "Jhunjhunu", "Jodhpur", "Karauli", "Kota", "Nagaur", "Pali", "Pratapgarh", "Rajsamand", "Sawai Madhopur", "Sikar", "Sirohi", "Tonk", "Udaipur"],
	"Sikkim": ["East Sikkim", "North Sikkim", "South Sikkim", "West Sikkim"],
	"Tamil Nadu": ["Ariyalur", "Chennai", "Coimbatore", "Cuddalore", "Dharmapuri", "Dindigul", "Erode", "Kanchipuram", "Kanyakumari", "Karur", "Krishnagiri", "Madurai", "Nagapattinam", "Namakkal", "Nilgiris", "Perambalur", "Pudukkottai", "Ramanathapuram", "Salem", "Sivaganga", "Thanjavur", "Theni", "Thoothukudi", "Tiruchirappalli", "Tirunelveli", "Tiruppur", "Tiruvallur", "Tiruvannamalai", "Tiruvarur", "Vellore", "Viluppuram", "Virudhunagar"],
	"Telangana": ["Adilabad", "Bhadradri Kothagudem", "Hyderabad", "Jagtial", "Jangaon", "Jayashankar", "Jogulamba", "Kamareddy", "Karimnagar", "Khammam", "Komaram Bheem", "Mahabubabad", "Mahbubnagar", "Mancherial", "Medak", "Medchal", "Nagarkurnool", "Nalgonda", "Nirmal", "Nizamabad", "Peddapalli", "Rajanna Sircilla", "Ranga Reddy", "Sangareddy", "Siddipet", "Suryapet", "Vikarabad", "Wanaparthy", "Warangal Rural", "Warangal Urban", "Yadadri Bhuvanagiri"],
	"Tripura": ["Dhalai", "Gomati", "Khowai", "North Tripura", "Sepahijala", "South Tripura", "Unakoti", "West Tripura"],
	"Uttar Pradesh": ["Agra", "Aligarh", "Allahabad", "Ambedkar Nagar", "Amethi", "Amroha", "Auraiya", "Azamgarh", "Baghpat", "Bahraich", "Ballia", "Balrampur", "Banda", "Barabanki", "Bareilly", "Basti", "Bhadohi", "Bijnor", "Budaun", "Bulandshahr", "Chandauli", "Chitrakoot", "Deoria", "Etah", "Etawah", "Faizabad", "Farrukhabad", "Fatehpur", "Firozabad", "Gautam Buddha Nagar", "Ghaziabad", "Ghazipur", "Gonda", "Gorakhpur", "Hamirpur", "Hapur", "Hardoi", "Hathras", "Jalaun", "Jaunpur", "Jhansi", "Kannauj", "Kanpur Dehat", "Kanpur Nagar", "Kasganj", "Kaushambi", "Kheri", "Kushinagar", "Lalitpur", "Lucknow", "Maharajganj", "Mahoba", "Mainpuri", "Mathura", "Mau", "Meerut", "Mirzapur", "Moradabad", "Muzaffarnagar", "Pilibhit", "Pratapgarh", "Raebareli", "Rampur", "Saharanpur", "Sambhal", "Sant Kabir Nagar", "Shahjahanpur", "Shamli", "Shravasti", "Siddharthnagar", "Sitapur", "Sonbhadra", "Sultanpur", "Unnao", "Varanasi"],
	"Uttarakhand": ["Almora", "Bageshwar", "Chamoli", "Champawat", "Dehradun", "Haridwar", "Nainital", "Pauri", "Pithoragarh", "Rudraprayag", "Tehri", "Udham Singh Nagar", "Uttarkashi"],
	"West Bengal": ["Alipurduar", "Bankura", "Birbhum", "Cooch Behar", "Dakshin Dinajpur", "Darjeeling", "Hooghly", "Howrah", "Jalpaiguri", "Jhargram", "Kalimpong", "Kolkata", "Malda", "Murshidabad", "Nadia", "North 24 Parganas", "Paschim Bardhaman", "Paschim Medinipur", "Purba Bardhaman", "Purba Medinipur", "Purulia", "South 24 Parganas", "Uttar Dinajpur"]
}
json;
        return json_decode($jsonStr, true);
    }

    public function getCity($city)
    {
        $cityList = $this->cityList();
        foreach ($cityList as $state => $stateCity) {
            if ($hasCity = in_array($city, $stateCity)) {
                return ['state' => $state, 'city' => $city];
            }
        }
        return false;
    }

    public function getStateList()
    {
        $list = collect($this->cityList());
        return $list->keys();
    }

    /**
     * 检查州和city
     *
     * @param $state
     * @param $city
     * @return bool
     */
    public function checkStateCity($state, $city)
    {
        $list = $this->cityList();
        if (!array_key_exists($state, $list)) {
            return false;
        }

        if (!in_array($city, $list[$state])) {
            return false;
        }

        return true;
    }

    /**
     * 获取 state 的更多名字
     * @param $state
     * @return mixed
     */
    public function getStateMoreName($state)
    {
        $data = [
            self::ANDHRA_PRADESH => ['Andhra Pradesh'],
            self::ARUNACHAL_PRADESH => ['Arunachal Pradesh'],
            self::ASSAM => ['Assam'],
            self::BIHAR => ['Bihar', 'Bihari Colony'],
            self::CHATTISGARH => ['Chattisgarh', 'Chhatisgarh', 'Chhattisgarh'],
            self::GOA => ['Goa', 'South Goa', 'North Goa', 'Goa Bagan'],
            self::GUJARAT => ['Gujarat'],
            self::HARYANA => ['Haryana'],
            self::HIMACHAL_PRADESH => ['Himachal Pradesh', 'Himāchal Pradesh'],
            self::JHARKHAND => ['Jharkhand'],
            self::KARNATAKA => ['Karnataka', 'Karnātaka'],
            self::KERALA => ['Kerala'],
            self::MADHYA_PRADESH => ['Madhya Pradesh'],
            self::MAHARASHTRA => ['Maharashtra'],
            self::MANIPUR => ['Manipur'],
            self::MEGHALAYA => ['Meghalaya', 'Meghālaya'],
            self::NAGALAND => ['Nagaland', 'Nāgāland'],
            self::ODISHA => ['Andhra Pradesh'],
            self::PUNJAB => ['Punjab', 'Punjub'],
            self::RAJASTHAN => ['Rajasthan', 'Rājasthān'],
            self::SIKKIM => ['Sikkim'],
            self::TAMIL_NADU => ['Tamil Nadu', 'Tamil Nādu'],
            self::TELANGANA => ['Telangana'],
            self::TRIPURA => ['Tripura', 'West Tripura'],
            self::UTTAR_PRADESH => ['Uttar Pradesh'],
            self::UTTARAKHAND => ['Uttarakhand'],
            self::WEST_BENGAL => ['West Bengal'],
            self::ANDAMAN_AND_NICOBAR_ISLANDS => ['Andaman & Nicobar Islands', 'Andaman and Nicobar Islands'],
            self::CHANDIGARH => ['Chandigarh', 'Chandīgarh'],
            self::DADRA_AND_NAGAR_HAVELI => ['Dadra & Nagar Haveli', 'Dadra and Nagar Haveli', 'Dādra & Nagar Haveli', 'Dādra and Nagar Haveli'],
            self::DAMAN_AND_DIU => ['Daman and Diu', 'Daman', 'Daman & Diu', 'Damān and Diu', 'Damān & Diu'],
            self::LAKSHADWEEP => ['Lakshadweep'],
            self::JAMMU_AND_KASHMIR => ['Jammu and Kashmir', 'Jammu & Kashmir'],
            self::DELHI => ['Delhi', 'New Delhi', 'North West Delhi'],
            self::PONDICHERRY => ['Pondicherry', 'Puducherry'],
        ];

        return array_get($data, $state);
    }
}
