<?php

namespace Api\Services\OrderAgreement;

class LoanContractTemplate
{
    const FIX_FIELD = [
    ];

    public static $content = <<<CONTENT
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <meta http-equiv="Content-Style-Type" content="text/css"/>
    <meta name="generator" content="Aspose.Words for .NET 15.1.0.0"/>
    <meta name="viewport" content="width=640, user-scalable=no">
    <title></title></head>
<body style="text-align: center;width: 600px;margin: auto;">
<div><p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span>
</p>
    <p style="font-size:14pt;  margin:0pt 0pt 10pt; text-align:center"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; font-weight:normal; text-decoration:none">LOAN AGREEMENT</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">This Agreement is made on</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> {{contract_time}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span><br/><br/><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">BY AND BETWEEN:</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">M/s </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> through its website </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.  </span><br/><br/><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">(hereinafter referred to as the “</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">” which expression shall, unless repugnant to or inconsistent with the context, mean and include their successors and permitted assignees of the FIRST</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> PART).</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span><br/><br/><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">and</span><br/><br/><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Mr </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{fullname}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> (hereinafter referred to as the “Borrower” which expression shall, unless repugnant to or inconsistent with the context, mean and include their successors and permitted assignees of the SECOND PART).</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span><br/><br/><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">[Borrower and </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> shall together be referred to as the “Parties” and severally as the “Party”]</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Witnesseth</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Whereas, </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> is an online social lending platform by M/s </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> that provides loan facility as per the terms provided under the agreements made available on the application (viz app) and website of the company as the case may be, in relation to the lending/ borrowing transactions made through </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> website/platform.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Whereas, a person who creates an account with </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> can find a suitable </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">/borrower. On freezing of the loan transaction for the borrower or closure of the bid for the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">, as the case may be the terms between the borrower &amp; </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> are materialized by entering into this agreement which is binding upon both the parties.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt;table-layout: fixed;word-wrap: break-word;width: 100%;">
        <tr style="height:15.2pt">
            <td colspan="2" style="vertical-align:bottom; width:495pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">                                   LOAN APPLICATION FORM</span>
            </p></td>
        </tr>
        <tr style="height:16.9pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:248pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">ACCOUNT TYPE:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Save</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">NAME:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:10pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="color:#000000; font-family:'Bookman Old Style'; font-size:10pt">{{fullname}}</span></p>
            </td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">FATHER/SPOUSE NAME:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:10pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="color:#000000; font-family:'Bookman Old Style'; font-size:10pt">{{father_name}}</span></p>
            </td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">DATE OF BIRTH:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{birthday}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">GENDER:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{gender}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">MARITAL STATUS:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{marital_status}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">OCCUPATION:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{profession}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">NATIONALITY:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">India</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">RESIDENTIAL STATUS:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{residence_type}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">PROOF OF IDENTITY:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{aadhaar_card_no}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">PAN:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{pan_card_no}}</span>
                </p></td>
        </tr>
        <tr style="height:9.9pt">
            <td style="border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">PERMANENT ADDRESS:</span>
                </p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{aadhaar_address}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">CURRENT ADDRESS:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{address}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">PHONE NUMBER:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{telephone}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Gurantors Name No.1: </span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{contact_fullname}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Gurantor No.1’s Number:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{contact_telephone}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Gurantors Name No.2: </span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{contact_fullname2}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Gurantor No.2’s Number:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{contact_telephone2}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">NAME OF BANK:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{bank_name}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">BANK A/C NO:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{bank_no}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">IFSC:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{ifsc}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Any other bank account</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">NAME OF BANK:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">BANK A/C NO:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:247pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">IFSC:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:246.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:0pt">
            <td style="width:248pt; border:none"></td>
            <td style="width:247pt; border:none"></td>
        </tr>
    </table>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">                            SUMMARY OF THE LOAN TERMS</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt;table-layout: fixed;word-wrap: break-word;width: 100%;">
        <tr style="height:12.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:43pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">S. No.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:196.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Particulars</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:263.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Details</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:43pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:196.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">LOAN ID / SERIAL NO.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:263.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{contract_no}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:43pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:196.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">CITY</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:263.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{contract_city}}</span>
                </p></td>
        </tr>
    </table>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I understand the terms of the loan to be provided to me, </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> if approved as per the internal policies and law shall be as specified below (“Loan”):</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt;table-layout: fixed;word-wrap: break-word;width: 100%;">
        <tr style="height:12.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">PARTICULARS</span>
                </p></td>
            <td colspan="2"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:150.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">DETAILS</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; border-top-color:#000000; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">:</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:254.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Platform:</span>
                </p></td>
            <td colspan="2"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:150.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Loan Amount:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:145.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{principal}}</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:12.95pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Rate of Interest:</span>
                </p></td>
            <td colspan="2"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:150.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{daily_rate}}</span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> percent(%) per day</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Purpose of Loan:</span>
                </p></td>
            <td colspan="2"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:150.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{loan_reason}}</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:10.15pt">
            <td style="border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Processing Fees:</span>
                </p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:144.65pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; font-style:normal; text-decoration:none; text-transform:none">{{charge}}</span>
                </p></td>
            <td style="vertical-align:bottom; width:5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:103.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Default Charges:</span>
                </p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{overdue_rate}}</span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">percent(%) per day</span>
                </p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:15.2pt">
            <td style="border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Full Prepayment Charges:</span>
                </p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:144.65pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt"><span
                        style="color:#000000; font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
                <p style="font-size:13pt;  margin:0pt 0pt 10pt"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; font-style:normal; text-decoration:none; text-transform:none">{{repayment_amount}}</span>
                </p></td>
            <td style="vertical-align:bottom; width:5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:103.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Service Charges:</span>
                </p></td>
            <td rowspan="2"
                style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">None</span>
                </p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:12.9pt">
            <td style="border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:144.65pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:103.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:10.7pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:144.65pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td colspan="2"
                style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:108.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:9.9pt">
            <td style="border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Banking Details for disbursal of</span>
                </p></td>
            <td style="vertical-align:bottom; width:145.15pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">As specified in the form</span>
            </p></td>
            <td colspan="2" style="vertical-align:bottom; width:109pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">above.</span>
            </p></td>
            <td style="border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:13.9pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#000000; border-left-style:solid; border-left-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:155.8pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Loan:</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:145.15pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; vertical-align:bottom; width:104pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#000000; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:92.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:1.5pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:0pt">
            <td style="width:156.8pt; border:none"></td>
            <td style="width:145.15pt; border:none"></td>
            <td style="width:5pt; border:none"></td>
            <td style="width:104pt; border:none"></td>
            <td style="width:93pt; border:none"></td>
            <td style="width:1.5pt; border:none"></td>
        </tr>
    </table>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Borrowers agrees to submit the following documents for availing this facility.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt;table-layout: fixed;word-wrap: break-word;">
        <tr style="height:19pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:26.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Sr.no.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:341.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Documents from the Applicant and the Co- Applicant(if any)</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:81.45pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Status(Tick )</span>
                </p></td>
        </tr>
        <tr style="height:21.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:26.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">  1. </span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:341.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Pan card or Form 60*</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:81.45pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:Arial; font-size:13pt; text-decoration:none">√</span>
                </p></td>
        </tr>
        <tr style="height:17.5pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:26.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">  2. </span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:341.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Last 3 months bank statements or other income proof</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:81.45pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:21.75pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:26.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">  3.</span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:341.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Any other document requested by the </span><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> </span>
                </p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:81.45pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:17.5pt">
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:26.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:341.7pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-bottom-color:#000000; border-bottom-style:solid; border-bottom-width:0.75pt; border-left-color:#000000; border-left-style:solid; border-left-width:0.75pt; border-right-color:#000000; border-right-style:solid; border-right-width:0.75pt; border-top-color:#000000; border-top-style:solid; border-top-width:0.75pt; padding-left:5.03pt; padding-right:5.03pt; vertical-align:top; width:81.45pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
    </table>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">*Compulsory Requirement</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="width:36pt; text-indent:0pt; display:inline-block"></span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I / We further acknowledge, understand and agree that </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> has adopted risk-based pricing, which is arrived by taking into account, broad parameters like the customers financial and credit profile etc. I understand all the terms listed and hereby apply for the said Loan to </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    
    {{user_img_file}} 
    
    </div>
<br style="clear:both; mso-break-type:section-break; page-break-before:auto"/>
<div>
            
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">     SELF-DECELERATION AND UNDERTAKING:</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I hereby apply for the Loan facility from the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> as specified above.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I represent that the information and details provided in this Application Form and the documents submitted by me are true, correct and that I have not withheld/suppressed/misrepresented/mislead any information.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I have read and understood and accepted without any alteration, doubt, dispute, demure, the fees and charges applicable to the Loan that I may avail from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I confirm that no insolvency proceedings or civil suits or any other civil or criminal proceedings for recovery of outstanding dues or any other alligations civil or criminal in nature have been initiated and / or are pending against me.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I hereby authorize </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to exchange or share information and details relating to this Application Form its group companies or any third party, as may be required or deemed fit, for the purpose of processing this loan application and/or related offerings or other products / services / recovery of money or any other purpose as they may deem fit  which I may apply for from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I hereby consent to and authorize </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to increase or decrease the credit limit assigned to me basis </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> internal credit policy.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">By submitting this Application Form, I hereby expressly authorize </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to send me communications regarding various financial products offered by or from </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">, its group companies and / or third parties through telephone calls / SMS / emails / post etc. including but not limited to promotional communications. And confirm that I shall not challenge receipt of such communications as unsolicited communication, defined under TRAI Regulations on Unsolicited Commercial Communications under the Do Not Call Registry.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">In case, If the borrower fails to make the loan re payment in such case </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">/ </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> through its representatives/ employee etc will have authority to call or initiate recovery procedure against me, my contacts references, friends, family, acquaintance or any other person, company, organization as may be available in my phone data, phone book, other source or any other details made available to </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">/ </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> etc.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I understand and acknowledge that </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> has the absolute discretion, without assigning any reasons to reject my application and that </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> is not liable to provide me a reason for the same.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1.That </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> shall have the right to make disclosure of any information relating to me including personal information, details in relation to Loan, defaults, security, etc. to the Credit Information Bureau of India (CIBIL) and/or any other governmental/regulatory/statutory or private agency / entity, credit bureau, RBI, CKYCR, including publishing the name as part of willful defaulter's list from time to time, as also use for KYC information verification, credit risk analysis, or for other related purposes.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2.I agree and accept that </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> may in its sole discretion, by its self or through authorized persons, advocate, agencies, bureau, etc. verify any information given, check credit references, employment details and obtain credit reports to determine creditworthiness from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">That I have not taken any loan from any other bank/ finance company unless specifically declared by me.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">That the funds shall be used for the Purpose specified in above and will not be used for speculative or antisocial purpose.</span><a
            name="page4"></a></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I have understood and accepted the late payment and other default charges listed above.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I hereby confirm that I contacted </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> for my requirement of personal loan and no representative of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> has emphasized me directly / indirectly to make this application for the Loan.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">I hereby confirm having read and understood the Standard Terms and Conditions applicable to this Loan and are signing this Application Form after understanding of each term.</span>
    </p></div>
<br style="clear:both; mso-break-type:section-break; page-break-before:always"/>
<div><p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">STANDARD TERMS</span>
</p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Now therefore, in consideration of the mutual promises, covenants and conditions hereinafter set forth, the receipt and sufficiency of which is hereby acknowledged, the parties hereto agree as follows:</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Borrower can make a request for any loan amount from the platform named </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> which is secured app run and controlled by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> after scrutinizing the credibility of the borrower grants loan approval, disburse loans, conduct activities for recovery of the money disbursed and which will be governed by the terms and conditions mentioned below read together with the application form, drawdown request and MITC as exchanged between the parties (together referred to as “Transaction Document”) </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; font-weight:bold; text-decoration:none">Applicability :</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Standard Terms set out hereunder, shall if the Application Form so provides, be applicable to the Facility provided by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">              </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; font-weight:bold; text-decoration:none">Definitions and Interpretations:</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">In these Standard Terms unless there is anything repugnant to the subject or context thereof, the expressions listed below, if applicable, shall have the following meanings:</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1.  “Access Code(s)” means any authentication mode as approved, specified by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> including without limitation combination of user name and password.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2. "Account" means the bank account where the Loan disbursement is requested and more specifically provided under the Application Form or Draw-down Request; </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3. "Application Form" means the loan application form submitted by the Borrower to the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> for applying and availing of the Facility, together with all other information, particulars, clarifications and declarations, if any, furnished by the Borrower or any other persons from time to time in connection with the Facility</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">4. "Availability Period" means </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">the period between the date of approval of the loan and the date of repayment (or the extension date approved by the lender in its discretion)</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">5. "Borrower" means jointly and severally each applicant and co-applicants (if any) and the term shall include their successors and permitted assigns;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">6.“Business Day” means a day which is not a non working Saturday, Sunday, Public or Bank holiday.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">7. "Default Rate" means the rate provided as such under the Application Form, the penalty charges/ overdue charges chargeable due to default in payments;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">8. “Interest Rate” means the rate of interest at which the said loan facility is provided by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to the borrower.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">9. "Draw-down Request" means a request from the Borrower in a form and manner acceptable to the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> for seeking disbursement of Loan;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">10. "Drawing Power" means the threshold limit(s) assessed by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">, in its sole discretion from time to time which shall be within the overall sanctioned limit and shall determine the amount of draw-down that can be requested by the Borrower at any given time under the Facility.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">11. Due Date" means such date(s) on which any payment becomes due and payable under the terms of the Transaction Documents (or otherwise);</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">12. “Facility" means a loan facility extended to the borrower for any purpose stated by him to the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">13. "Increased Costs" means any cost which is charged to the borrower on account of expenses incurred by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> on account of default, recovery, etc. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">a reduction in the rate of return from the Loan(s) or on the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">'s overall capital (including as a result of any reduction in the rate of return on capital brought about by more capital being required to be allocated by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">)</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">any additional or increased cost including provisioning as may be required under or as may be set out in RBI regulations or any other such regulations from time to time; </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">a reduction of any amount due and payable under the Transaction Documents;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">14. </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">“</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">”</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> means </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> as specified in Application Form and shall include its successors and assigns;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">15. “Loan" means each disbursement made under the Facility and a fixed amount given by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to the borrower for fixed time;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">16. “MITC" means the most important terms and conditions reiterated by the Borrower at the time of availing the Facility;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 17. “Portal” shall mean such platform or portal as described in the Application Form for availing this facility. i.e </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">18. “Purpose” shall have the same meaning as is provided in the Application Form; </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">19. “Sanctioning Authority” includes the Reserve Bank of India, Office of Foreign Assets Control of the Department of Treasury of the United States of America, the United Nations Security Council, the European Union, Her Majesty’s Treasury of the United Kingdom or any combination of the foregoing;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">20. “Tenure" means the period provided as such under the Application Form.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 21.  “Repayment” means the repayment of the principal amount and of loan interest thereon, commitment and/or any other charges, fees or other dues payable in terms of this agreement to the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">22. “Pre-payment” means premature repayment of the loan in partial or full.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">23. “Installment” means the amount of monthly payment over the period of loan.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">24. “Post Dated Cheques” or “PDCs” means cheques for the amount of the installment drawn by the borrower in favor of the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> bearing the dates to match the due date of each installment.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">25. “EMI” means Equated Monthly Installments. i.e fixed amount paid by the borrower every month. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 26. “Working day” shall mean the day on which the Banks are open for business in India.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Capitalized terms used in these Standard Terms but not defined herein, shall have the meaning ascribed to such terms under the Application Form or Drawdown Request.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; font-weight:bold; text-decoration:none">Important Clauses of Loan Agreement</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1. Commencement</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">    This agreement shall come into effect from the date of acceptance of this agreement, by way of clicking the accept option below.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 2. Purpose</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">   The borrower hereby confirms that the amount borrowed shall be used for the         purpose mentioned by the Borrower in the Application form and no other except otherwise stated by the borrower as alternate purpose. The borrower also understand that misleading the company can lead the borrower into problem. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3. Agreement and terms of the loan</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> has agreed to grant the loan to the borrower a sum of Rs. </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{principal}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> of which process fees and other charges will be deducted upfront. Also that in case of default charges for overdue charges/ penalty charges, recovery, legal, etc will be added to the outstanding dues and Borrower accepts to pay  the same. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">4. Disbursement of loan</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">/</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> will ensure that the amount collected from the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> in the name of the borrower is deposited into the borrower’s designated account within 3-5 working days after execution of this document by both the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">s and borrowers. In case there is a delay in providing the money from </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> due to unforeseen circumstances, </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> will intimate the borrower immediately and </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> is provided additional 5 working days to deposit his loan amount with </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">. In the event repayment is not done within the due date after closure of bid as stated above, </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> will take necessary steps to reach to other </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> to offer the remaining amount to the borrower. The borrower can however choose to either take the offered amount or wait till the total loan amount is made available. This however, may take additional 5-10 working days to complete the loan transaction after execution of this agreement by both the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> and borrower.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">5. When the loan, interest, etc becomes due.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Interest will be calculated as per the details mentioned on the website/application (</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">) on the loan amount disbursed to the borrower (for more clarity, when the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> is meeting the commitment of the borrower in partial, then interest would be calculated for his amount which is lent as mentioned on the website/application (</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">) .</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">6. Mode of payment of Installment</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">   1</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">） </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Borrower shall make each payment under the Transaction Documents on or before the respective Due Date. No Due Date shall exceed the Tenure of the Facility.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">   2</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">） </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">It is irrespective if the  Due Date is Business Day or a holiday for any reason, then the Borrower agrees that  the payment is made by way of internet banking and no facility of extension of time will be granted for any due date falling on working day/ weekend/holiday/any other day.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">   3</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">All the payments made will be subject to taxation as provided by the government of India whether state or central government and both the parties shall abide by the laws pertaining to taxation as and when required as applicable in the transaction. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">    4</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Notwithstanding anything to the contrary, the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> may, at any time, without assigning any reason, cancel the undisbursed portion of the Facility and can also recall any or all portion of the disbursed Loan on demand. Upon such recall, the Loan and other amounts stipulated by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> shall be payable forthwith with the new deadline or new due date provided by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">    5</span><span
            style="background-color:#ffffff; font-family:宋体; font-size:13pt; text-decoration:none">）</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Borrower will make repayment of the principal amount plus interest, penalty, delay interest, processing charges, taxation, any other charges (as applicable) under the Loan(s) in such proportion and periodicity as may be provided in the Transaction Documents or as communicated by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">7. Interest</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The rate of interest applicable to the said loan as the same date upon which the disbursement amount is debited from the bank account of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">  and will be compounded with the monthly rests on the outstanding balance, namely the balance of loan and unpaid interest and costs, charges and expenses outstanding at the end of the month. Any dispute being raised about the amount due or interest computation will not enable the borrower to withhold payment of any installment. For purpose of computation of interest 30 days shall be considered per calendar month.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Therefor the Borrower shall be obliged to pay interest at the rate of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{daily_rate}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt"> </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">percent (%) per day, the "Interest", such interest to be paid together with the capital sum of the loan at the end of the loan period.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Or</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The Borrower shall be obliged to pay the Interest Rate  On the due date of the repayment, preclosure date (as applicable) in case the borrower exceeds the due date and defaults in repayment in such case delay interest will be charged and the rate of delay interest will differ on day to day basis. The delay interest will be charged  </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{overdue_rate}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt"> </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> percent (%)  per day on principal amount disbursed.   </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">8. Period of disbursement of loan</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The loan provided under this agreement will be for </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{loan_days}} </span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">days   </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">9. Covenants / undertakings of the parties</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">By - Borrower</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">a) To utilize the entire loan for the required purpose.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">b) To promptly notify any event or circumstances, which might operate as a cause of delay in the completion of this agreement.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">c) To provide accurate and true information.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">d) To repay the required funded amount without any failure.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">e) To maintain sufficient balance in the account of the borrowers bank </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">f) Due performance of all the terms and conditions provided under this loan agreement.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">g) Borrower agree to indemnify and hold </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> harmless from and against any and all claims, action, liability, cost, loss, damage, endured by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> by your access in violation to the listed terms of service and also to the applicable laws, rules and regulations or agreements prevailing from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">h) The collection charges, calling, recovery, legal expenses if any, incurred by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">, to be borne by the borrower</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">i) Cost of initiating legal proceedings.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">By- </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">a) To provide accurate and true information. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">b) To fund the accepted amount to the borrower.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">c) To maintain sufficient balance in the account of the drawee bank for payment of share of the borrower loan amount.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">d) Due performance of all the terms and conditions provided under this loan agreement.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">e) Borrower agree to indemnify and hold </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> harmless from and against any and all claims, action, liability, cost, loss, damage, endured by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{app_name}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> by your access in violation to the listed terms of service and also to the applicable laws, rules and regulations or agreements prevailing from time to time.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">10. Events of defaults</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> Notwithstanding anything to the contrary in this Agreement, if the Borrower defaults in the performance of any obligation under this Agreement , then the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> may declare the principal amount, interest,  delay interest, penalty , overdue charges, tele-calling charges, recovery charges, legal expenses, , other charges owing under this Agreement at that time to be immediately due and payable.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 11. Consequence of default</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> will take such necessary steps as permitted by law against the borrower to realize the amounts due along with the principal amount, interest,  delay interest, penalty , overdue charges, tele-calling charges, recovery charges, legal expenses, other charges at the decided rate and other fees / costs as agreed in this agreement including appointment of collection agents, appointment of attorneys/ consultants, as it thinks fit.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">In case of default matter be referred under Arbitration and Conciliation act 2019 (as amended) and the matter be referred to the sole Arbitrator. The sole arbitrator will be appointed by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> only the arbitrator be of the choice of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">. The award passed by the arbitrator will be considered as a final award and the borrower will neither challenge the appointment of arbitrator nor will challenge the award of the arbitrator. All such legal expenses for recovering the money from the borrower will be borne by the borrower including, principal amount, interest,  delay interest, penalty , overdue charges, tele-calling charges, recovery charges, legal expenses, other charges.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">In case of any disputes, litigation, subject to Mumbai jurisdiction only.  </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">12. Cancellation</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> reserves the unconditional right to cancel the limits sanctioned without giving any prior notice to the Borrower, on the occurrence of any one or more of the following-</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1. in case the Facility (in full or in part) is not disbursed; or</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2. in case of deterioration in the creditworthiness of the Borrower (as determined by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">) in any manner whatsoever; or</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3. in case of non-compliance of the Transaction Documents.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">13. Sever ability</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">If any provision of this agreement is found to be invalid or unenforceable, then the invalid or unenforceable provision will be deemed superseded by a valid enforceable provision that most closely matches the intent of the original provision and the remainder of the agreement shall continue in effect.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">14. Governing laws &amp; Jurisdiction</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">This agreement will be construed in accordance with and governed by laws of India. The parties have agreed to the exclusive jurisdiction of the courts at Mumbai.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">15. Mandatory Arbitration</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Any and all disputes or differences between the parties to the agreement, arising out of or in connection with this agreement or its performance shall, so far as it is possible, be settled by negotiations between the parties amicably through consultation.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Any dispute, which could not be settled by the parties through amicable settlement (as provided for under above clause) shall be initiated for settlement  by way Arbitration which will be governed by The Arbitration and Conciliation Act, 2019 ( as amended). In case of default matter be referred under Arbitration and Conciliation act 2019 (as amended) and the matter be referred to the sole Arbitrator. The sole arbitrator will be appointed by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> only . the arbitrator be of the choice of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">. The award passed by the arbitrator will be considered as a final award and the borrower will neither challenge the appointment of arbitrator nor will challenge the award of the arbitrator. All such legal expenses for recovering the money from the borrower will be borne by the borrower including, principal amount, interest,  delay interest, penalty , overdue charges, tele-calling charges, recovery charges, legal expenses, , other charges.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> 16. Force majeure</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">No party shall be liable to the other if, and to the extent, that the performance or delay in performance of any of their obligations under this agreement is prevented, restricted, delayed or interfered with, due to circumstances beyond the reasonable control of such party, including but not limited to, Government legislation's, fires, floods, explosions, epidemics, accidents, acts of God, wars, riots, strikes, lockouts, or other concerted acts of workmen, acts of Government and/or shortages of materials. The party claiming an event of force majeure shall promptly notify the other parties in writing, and provide full particulars of the cause or event and the date of first occurrence thereof, as soon as possible after the event and also keep the other parties informed of any further developments. The party so affected shall use its best efforts to remove the cause of non-performance, and the parties shall resume performance hereunder with the utmost dispatch when such cause is removed.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">17. Binding effect</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">All warranties, undertakings and agreements given herein by the parties shall be binding upon the parties and upon its legal representatives and estates. This agreement (together with any amendments or modifications thereof) supersedes all prior discussions and agreements (whether oral or written) between the parties with respect to the transaction.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">English shall be used in all correspondence and communications between the Parties.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">18. Benefit of the Loan Agreement</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The loan agreement shall be binding upon and to ensure to the benefit of each party thereto and its successors or heirs, administrators, as the case may be.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> Any delay in exercising or omission to exercise any right, power or remedy accruing to the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> under this agreement or any other agreement or document shall not impair any such right, power or remedy and shall not be construed to be a waiver thereof or any acquiescence in any default; nor shall the action or inaction of the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> in respect of any default or any acquiescence in any default, affect or impair any right, power or remedy of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> in respect of any other default.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">19. Notices</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Any notice or request to be given or made by a party to the other shall be in writing. All correspondence shall be addressed to </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> will be on </span><span
            style="font-family:Calibri; font-size:10pt">{{address}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">20. Acceptance</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The parties hereby declares as follows: </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1. They have read the entire agreement and shall be bound by all the conditions.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2. This agreement and other documents have been read to by the borrower and has thoroughly understood the contents thereof. </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3. They agree that this agreement shall be concluded and become legally binding on the date when it is signed by the parties.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">21. Entire Agreement</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">The parties confirm that this contract contains the full terms of their agreement and that no addition to or variation of the contract shall be of any force and effect unless done in writing and signed by </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.  </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">IN WHEREOF the Parties have executed this Agreement as of the day and year first above written.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">on behalf of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">on behalf of </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{fullname}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">&#xa0;</span>
    </p></div>
<br style="clear:both; mso-break-type:section-break; page-break-before:auto"/>
<div><p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></div>
<br style="clear:both; mso-break-type:section-break; page-break-before:always"/>
<div><p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><a name="page5"></a><a name="page3"></a><span
        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">                                         ANNEXURE A</span>
</p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">        THE MOST IMPORTANT TERMS AND CONDITONS – MITC</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1.We refer to the application form dated</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> {{contract_time}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> (“ Application Form ”) for grant of the Loan described below.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">2.Capitalized terms used but not defined hereunder shall have the meaning ascribed to the term in other Transaction Documents.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">3.The Borrower acknowledges and confirms that the below mentioned are the most important terms and conditions in the application for the Loan (and which would apply to the Borrower in respect of the Loan, if the request for the Loan is accepted by the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">) and they shall be read in conjunction with the Application Form(s), drawdown request(s) and the Standard Terms):</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <table cellspacing="0" cellpadding="0" style="border-collapse:collapse; margin-left:0pt;table-layout: fixed;word-wrap: break-word;">
        <tr style="height:15.95pt">
            <td style="border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; border-top-color:#373435; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Borrower</span>
                </p></td>
            <td colspan="3"
                style="border-right-color:#373435; border-right-style:solid; border-right-width:1pt; border-top-color:#373435; border-top-style:solid; border-top-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{fullname}}</span>
                </p></td>
        </tr>
        <tr style="height:4.6pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Purpose</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{loan_reason}}</span>
                </p></td>
        </tr>
        <tr style="height:10.8pt; border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt;">
            <td style="border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Tenure</span>
                </p></td>
            <td style="vertical-align:bottom; width:2pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:12pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:267.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt">{{loan_days}} </span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Days</span>
                </p></td>
        </tr>
        <tr style="height:10.8pt; border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt;">
            <td style="border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Rate of Interest ( p.m %)</span>
                </p></td>
            <td style="vertical-align:bottom; width:2pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="vertical-align:bottom; width:12pt"><p
                    style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                    style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></td>
            <td style="border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:267.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{daily_rate}}</span><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> % per day. at fixed rate of interest</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Repayment</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">One time repayment</span>
                </p></td>
        </tr>
        <tr style="height:12.95pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Risk Category</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">√ Low Medium High</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Loan Amount</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{principal}}</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Management fee</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Rs.50/day</span>
                </p></td>
        </tr>
        <tr style="height:11.75pt">
            <td style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-left-color:#373435; border-left-style:solid; border-left-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:198pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Late fee:</span>
                </p></td>
            <td colspan="3"
                style="border-bottom-color:#373435; border-bottom-style:solid; border-bottom-width:1pt; border-right-color:#373435; border-right-style:solid; border-right-width:1pt; vertical-align:bottom; width:281.5pt">
                <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
                        style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">1-3 Rs.50/day, 4-7 Rs.100/day, 8+ Rs.200/day</span>
                </p></td>
        </tr>
        <tr style="height:0pt">
            <td style="width:199pt; border:none"></td>
            <td style="width:2pt; border:none"></td>
            <td style="width:12pt; border:none"></td>
            <td style="width:268pt; border:none"></td>
        </tr>
    </table>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">4.The Borrower understands that the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> has adopted risk-based pricing, which is arrived by taking into account, broad parameters like the customers financial and credit profile. Further, the Borrower acknowledges and confirms that the </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> shall have the discretion to change prospectively the rate of interest and other charges applicable to the Loan.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">  5. The Borrower acknowledges and confirms having received a copy of each     Transaction Document and agrees that this letter is a Transaction Document.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">ACKNOWELDEGEMENT: </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> acknowledges receipt of your Application Form together with the Standard Term We will revert within 5 working days subject to furnishing the necessary documents to </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">’s satisfaction.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Date:</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> {{date}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">           </span><span
            style="width:22.88pt; text-indent:0pt; display:inline-block"></span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">                Sign:</span><span
            style="width:9.24pt; text-indent:0pt; display:inline-block"></span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{fullname}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Digitally Signed by: </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Name</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">:{{fullname}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Location</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">:{{contract_city}}</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Reason: loan</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Date:</span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> {{date}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none"> Time - </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{time}}</span><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">.</span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="background-color:#ffffff; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">Signed via  </span><span
            style="background-color:#ffffff; color:#000000; font-family:'Bookman Old Style'; font-size:13pt; text-decoration:none">{{lender}} </span>
    </p>
    <p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><span
            style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></div>
<br style="clear:both; mso-break-type:section-break; page-break-before:always"/>
<div><p style="font-size:13pt;  margin:0pt 0pt 10pt; text-align:justify"><a name="page14"></a><span
        style="font-family:'Bookman Old Style'; font-size:13pt">&#xa0;</span></p></div>
</body>
</html>
CONTENT;

    public static $signContent = '';
}
