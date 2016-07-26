<?php

$url = "https://www.compasscard.ca/";


$data = "__CSRFTOKEN=%2FwEFJDJhOGM2NzUyLWY3MDUtNDQ2Ni1hM2RhLTE5ODQzMGQ4MjdjNw%3D%3D&__EVENTTARGET=&__EVENTARGUMENT=&__VIEWSTATE=B9ijBLB%2BBfNSGZAGyg2juerp8Hc0gwcGWlbH1vCgENdxePfU7JB1L5NLgJlRGxnEtmA58TeAuhfgcmCcgEgp7mk4l93N1x94uWovN6L9Ec3EN3HoJLDSMkrOziaWetYYCGRuA6KxJT7Kg5aIQiLWgdxls4eeQK51CtInZ4FiRxKLdZTRt4465CeomWFL%2BDilHniG6Q%3D%3D&__VIEWSTATEGENERATOR=CA0B0334&__PREVIOUSPAGE=29DoMmba8MA7GDCjyJaAupeO5jBd1s9MEBrvR5wKBehDggHWHGT-VFR1frENakxie4lQkAF_aKBuAtUQL_U7yUC0qiw1&__EVENTVALIDATION=a9hOT3AzdPg%2Bq1ZQ0LZxgKbv5Ublvuo0tR0mHwqP1QfOXw11qpRelD0jt4QRg9Qgm7HmJT%2F8Vnlky%2BGtDr8aAvYH5RYXvU5Q7SHmwZeRTms62RZ0Ln3JKpxPkddrbz%2BF%2BoJtexWYc6ExzZmxMtot4BPFs4QM3bjYiMvDBfIIIAMreXabY88w16UMcnmzs2Nj96UwBJhvKMC8iZSRrTW2uCUDqs3Rc7j94rcCvM%2FM3Yixz0kguFv98vJEhLIFpIe3AfmwOihrGe5DqTGI%2FgIKEr9DdBbgeWxoCP6GDAEEifN51IeQJpliRUI0cGj6THOVUmR9SA%3D%3D&ctl00%24Content%24ucCardInput%24txtSerialNumber=".$compass_card_number."&ctl00%24Content%24ucCardInput%24txtCvn=".$compass_card_cvn."&ctl00%24Content%24btnCheckBalance=Continue+as+guest";
$ch = curl_init($url);

curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
curl_setopt($ch,CURLOPT_ENCODING , "");
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);	
curl_setopt($ch, CURLOPT_HTTPHEADER, array(        
    'Accept-Encoding: gzip, deflate, br',
    'Accept-Language: en-US,en;q=0.8,en-CA;q=0.6',
    'Upgrade-Insecure-Requests: 1',
    'Content-Type: application/x-www-form-urlencoded',
    'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
    'Cookie: ASP.NET_SessionId=h1vta4yax2yltynsjelvkuyq; Coyote-2-31091cac=15071cac:0; __CSRFCOOKIE=2a8c6752-f705-4466-a3da-198430d827c7; visid_incap_147861=FhJxSYqRS8iK0Syq6Zxs8BZtcVcAAAAAQUIPAAAAAAA0w0g/4O4GSfaDkGyI20pS; incap_ses_226_147861=JEgLB24DqDcM6DcJQusiA5p7jFcAAAAANIXoOHw7Ml1USeu6gSPCpg==; ADRUM_BTa=R:27|g:bb4c0dec-70ed-4e36-8c87-f44f9d68a4fd; ADRUM_BT1=R:27|i:5497|e:5'
    ));
$response = curl_exec($ch);

   if(!$response) {
        die('no response!');
    }
       

$dom = new DOMDocument();

$dom->loadHTML($response); 

$xpath = new DOMXPath($dom);

$value = $xpath->query('//div[@class="stored-value"]');

$card = $xpath->query('//div[@class="card-history"]');

$card = $card->item(0);

echo $dom->saveXML($value->item(0));

echo $dom->saveXML($card);

