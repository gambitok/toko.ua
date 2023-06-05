<?php

define('RDD', __DIR__);
require_once (RDD . "/../vendor/autoload.php");

$client = new Google_Client();
$client->setApplicationName('GmailApiServer');
$client->setAuthConfig('emails/src/credentials.json');
$scopes = array(
    Google_Service_Gmail::GMAIL_MODIFY
);
$client->setScopes($scopes);

$redirectUrl = 'http://' . $_SERVER['HTTP_HOST'] . '/gtest/';
$client->setRedirectUri($redirectUrl);
$client->setAccessType('offline');
$client->setApprovalPrompt('force');
//$client->setIncludeGrantedScopes(true);

$tokenFile = 'emails/src/token.json';

// Load previously authorized credentials from a file.
if (file_exists($tokenFile)) {
    $accessToken = json_decode(file_get_contents($tokenFile), true);

    $client->setAccessToken($accessToken);
} else {
    // Request authorization from the user.
    $authUrl = $client->createAuthUrl();
    header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));

    if (isset($_GET['code'])) {
        $authCode = $_GET['code'];
        // Exchange authorization code for an access token.
        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        header('Location: ' . filter_var($redirectUrl, FILTER_SANITIZE_URL));
        if (!file_exists(dirname($tokenFile))) {
            mkdir(dirname($tokenFile), 0700, true);
        }

        file_put_contents($tokenFile, json_encode($accessToken));
    } else {
        exit('No code found');
    }

    $client->setAccessToken($accessToken);
}

// Refresh the token if it's expired.
if ($client->isAccessTokenExpired()) {
    // save refresh token to some variable
    $refreshTokenSaved = $client->getRefreshToken();

    // update access token
    $client->fetchAccessTokenWithRefreshToken($refreshTokenSaved);

    // pass access token to some variable
    $accessTokenUpdated = $client->getAccessToken();

    // append refresh token
    $accessTokenUpdated['refresh_token'] = $refreshTokenSaved;

    //Set the new access token
    $accessToken = $refreshTokenSaved;
    $client->setAccessToken($accessToken);

    // save to file
    file_put_contents($tokenFile, json_encode($accessTokenUpdated));
}

function cReplace($s) {
    $s = str_replace('<', '', $s);
    $s = str_replace('>', '', $s);
    $s = str_replace('"', '', $s);
    return $s;
}

function getAttachments($service, $message_id, $parts) {
    $attachments = [];
    foreach ($parts as $part) {
        if (!empty($part->body->attachmentId)) {
            $attachment = $service->users_messages_attachments->get('me', $message_id, $part->body->attachmentId);
            $data = strtr($attachment->data, '-_', '+/');
            $size = $attachment->size;
            $extension = explode("/", $part->mimeType)[1];

            // data type trouble (octet-stream)
            if ($extension === '' || $extension === 'octet-stream') {
                $n = strrpos($part->filename, ".");
                $extension = ($n === false) ? "" : substr($part->filename, $n + 1);
            }

            // save files
            $filepath = "emails/files/". $message_id ."/";
            if(!is_dir($filepath)) {
                mkdir($filepath);
            }
            $filename = (string)$filepath . $message_id . "." . $extension;
            file_put_contents($filename, base64_decode($data));

            // unzip files
            if (in_array($extension, ['zip', 'rar'])) {
                $zip = new ZipArchive;
                if ($zip->open($filename) === TRUE) {
                    $zip->extractTo($filepath);
                    $zip->close();
                    unlink($filename);
                }
            }

            $attachments[] = [
                'message_id'    => $message_id,
                'attachment_id' => $part->body->attachmentId,
                'filename'      => $part->filename,
                'mimeType'      => $part->mimeType,
                'data'          => $data,
                'size'          => $size,
                'extension'     => $extension
            ];
        } else if (!empty($part->parts)) {
            $attachments = array_merge($attachments, getAttachments($service, $message_id, $part->parts));
        }
    }

    return $attachments;
}

$extensions = ['csv', 'xls', 'xlsx', 'zip', 'rar'];

if (empty($_GET['code'])) {
    $auth_url = $client->createAuthUrl();
    header('Location: ' . filter_var($auth_url, FILTER_SANITIZE_URL));
} else {
    $client->authenticate($_GET['code']);
    $access_token = $client->getAccessToken();
    $client->setAccessToken($access_token);

    $service = new Google_Service_Gmail($client);

    $optParams = [
        'maxResults'    => 5,
        'labelIds'      => 'INBOX',
        'q'             => 'is:unread newer_than:30d'
    ];

    $messages = $service->users_messages->listUsersMessages('me', $optParams);

    $list = $messages->getMessages();

    $result = [];

    foreach ($list as $messageObj) {
        $messageId  = $messageObj->id;
        $message    = $service->users_messages->get('me', $messageId, ['format' => 'full']);
        $headers    = $message->getPayload()->getHeaders();
        $snippet    = $message->getSnippet();
        $parts      = $message->getPayload()->getParts();
        $attachment = getAttachments($service, $messageId, $parts);

        $result[$messageId]['attachment'] = $attachment;
        $result[$messageId]['snippet'] = $snippet;

        foreach ($headers as $header) {
            if ($header->getName() === 'From') {
                $from = $header->getValue();
                if (strpos($from, '<') !== false) {
                    $bit = explode('<', $from);
                    $bit2 = explode('>', $bit[1]);
                    $final_email = $bit2[0];
                    $sender_name = str_replace('"', '', $bit[0]);
                } else {
                    $final_email = "";
                    $sender_name = str_replace('"', '', $from);
                }
                $result[$messageId]['sender_name'] = $sender_name;
                $result[$messageId]['sender_email'] = $final_email;

            }
            if ($header->getName() === 'Subject') {
                $result[$messageId]['subject'] = $header->getValue();
            }
            if ($header->getName() === 'Date') {
                $date = date('Y-m-d H:i:s', strtotime($header->getValue()));
                $result[$messageId]['date'] = $date;
            }
        }

        // read message
        // $message = $service->users_messages->modify('me', $messageId, new Google\Service\Gmail\ModifyMessageRequest(['removeLabelIds' => 'UNREAD']));
    }

    echo '<pre>';
    print_r($result);
    echo '</pre>';
}

?>
<br />
<a href="http://localhost/gtest/">Main page</a>