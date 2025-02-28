<?php

namespace Risk\Common\Services\SystemApprove\RuleData;

interface RuleDataInterface
{
    public function getAge(): ?int;

    public function hasRejectedOrders($day): bool;

    public function onBlackList(): bool;

    public function getEducation(): ?string;

    public function getAddressStateProbableValue(): array;

    public function getCareerType(): ?string;

    public function getFirstLoanInformationFillingTime(): ?int;

    public function getOrderCreateTime(): ?string;

    public function deviceSameApplyCount(): ?int;

    public function deviceSameTimeRangeApplyCount($day): ?int;

    public function deviceSameInApproveCount(): ?int;

    public function deviceSameOverdueCount($day): ?int;

    public function deviceSameMatchingPancard(): ?int;

    public function deviceSameMatchingAddhaar(): ?int;

    public function deviceSameMatchingTelephone(): ?int;

    public function deviceSameTimePeriodApplyCount($timeRange): ?int;

    public function getDeviceType(): ?string;

    public function authSameBankCardMatchingPanCard(): ?int;

    public function authSameBankCardMatchingAddhaar(): ?int;

    public function authSameBankCardMatchingTelephone(): ?int;

    public function authSameIdentityMatchingTelephone(): ?int;

    public function authSameIdentityMatchingDevice(): ?int;

    public function authRelateAccountHasRejectedOrders($day): bool;

    public function authRelateAccountHasUnderwayOrder(): bool;

    public function getUserFaceMatchScore(): float;

    public function contactsInApproveCount(): ?int;

    public function contactsInOverdueCount($day): ?int;

    public function contactsCount(): ?int;

    public function behaviorRejectCount(): ?int;

    public function behaviorOverdueCount($day): ?int;

    public function behaviorMaxOverdueDays(): ?int;

    public function behaviorLatelyOverdueDays(): ?int;

    public function behaviorLatelyCollectionTelephoneCount(): ?int;

    public function deviceApplyUniqueCount(): ?int;
}
