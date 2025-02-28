<?php

namespace Risk\Common\Models\Experian;

use Risk\Common\Models\RiskBaseModel;

/**
 * Risk\Common\Models\Experian\ExperianIndividualInfo
 *
 * @property int $id
 * @property int $app_id
 * @property int $user_id
 * @property int $relate_id
 * @property string $relate_type 关联类型  third_experian
 * @property string|null $username Username
 * @property string|null $report_date ReportDate
 * @property string|null $version Version
 * @property string|null $report_number ReportNumber
 * @property string|null $subscriber_name SubscriberName
 * @property string|null $date_of_birth_applicant DateOfBirthApplicant
 * @property string|null $driver_license_expiration_date DriverLicenseExpirationDate
 * @property string|null $driver_license_issue_date DriverLicenseIssueDate
 * @property string|null $driver_license_number DriverLicenseNumber
 * @property string|null $email_id EmailId
 * @property string|null $first_name FirstName
 * @property string|null $gender_code GenderCode
 * @property string|null $income_tax_pan IncomeTaxPan
 * @property string|null $last_name LastName
 * @property string|null $mobile_phone_number MobilePhoneNumber
 * @property string|null $pan_expiration_date PanExpirationDate
 * @property string|null $pan_issue_date PanIssueDate
 * @property string|null $passport_expiration_date PassportExpirationDate
 * @property string|null $passport_issue_date PassportIssueDate
 * @property string|null $passport_number PassportNumber
 * @property string|null $ration_card_expiration_date RationCardExpirationDate
 * @property string|null $ration_card_issue_date RationCardIssueDate
 * @property string|null $ration_card_number RationCardNumber
 * @property string|null $telephone_type TelephoneType
 * @property string|null $universal_id_expiration_date UniversalIdExpirationDate
 * @property string|null $universal_id_issue_date UniversalIdIssueDate
 * @property string|null $universal_id_number UniversalIdNumber
 * @property string|null $voter_id_expiration_date VoterIdExpirationDate
 * @property string|null $voter_id_issue_date VoterIdIssueDate
 * @property string|null $voters_identity_card VotersIdentityCard
 * @property string|null $employment_status EmploymentStatus
 * @property string|null $income Income
 * @property string|null $marital_status MaritalStatus
 * @property string|null $number_of_major_credit_card_held NumberOfMajorCreditCardHeld
 * @property string|null $time_with_employer TimeWithEmployer
 * @property string|null $city City
 * @property string|null $state State
 * @property string|null $pin_code PinCode
 * @property string|null $country_code CountryCode
 * @property string|null $bldg_no_society_name BldgNoSocietyName
 * @property string|null $flat_no_plot_no_house_no FlatNoPlotNoHouseNo
 * @property string|null $road_no_name_area_locality RoadNoNameAreaLocality
 * @property string|null $created_at 创建时间
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo newQuery()
 * @method static Builder|RiskBaseModel orderByCustom($defaultSort = null)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo query()
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereAppId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereBldgNoSocietyName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereCity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereCountryCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereDateOfBirthApplicant($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereDriverLicenseExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereDriverLicenseIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereDriverLicenseNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereEmailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereEmploymentStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereFirstName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereFlatNoPlotNoHouseNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereGenderCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereIncome($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereIncomeTaxPan($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereLastName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereMaritalStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereMobilePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereNumberOfMajorCreditCardHeld($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePanExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePanIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePassportExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePassportIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePassportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo wherePinCode($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRationCardExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRationCardIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRationCardNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRelateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRelateType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereReportDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereReportNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereRoadNoNameAreaLocality($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereState($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereSubscriberName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereTelephoneType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereTimeWithEmployer($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereUniversalIdExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereUniversalIdIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereUniversalIdNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereUsername($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereVoterIdExpirationDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereVoterIdIssueDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ExperianIndividualInfo whereVotersIdentityCard($value)
 * @mixin \Eloquent
 */
class ExperianIndividualInfo extends RiskBaseModel
{
    /** 关联：third_experian表 */
    const TYPE_THIRD_EXPERIAN = 'third_experian';
    /** 关联：third_report */
    const TYPE_THIRD_REPORT = 'third_report';
    public $table = 'experian_individual_info';
    public $timestamps = false;
    protected $fillable = [];
    protected $guarded = [];

    /**
     * 安全属性
     * @return array
     */
    public function safes()
    {
        return [];
    }
}
