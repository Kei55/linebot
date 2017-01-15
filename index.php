<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';

$DEVELOPER_KEY_YOUTUBE = 'AIzaSyBN8gvRSd8k_NuqBKF4ITdGxbDfLnNocuw';

$httpClient = new \LINE\LINEBot\HTTPClient\CurlHTTPClient(getenv('CHANNEL_ACCESS_TOKEN'));
$bot = new \LINE\LINEBot($httpClient, ['channelSecret' => getenv('CHANNEL_SECRET')]);

$signature = $_SERVER["HTTP_" . \LINE\LINEBot\Constant\HTTPHeader::LINE_SIGNATURE];
try {
  $events = $bot->parseEventRequest(file_get_contents('php://input'), $signature);
} catch(\LINE\LINEBot\Exception\InvalidSignatureException $e) {
  error_log("parseEventRequest failed. InvalidSignatureException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownEventTypeException $e) {
  error_log("parseEventRequest failed. UnknownEventTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\UnknownMessageTypeException $e) {
  error_log("parseEventRequest failed. UnknownMessageTypeException => ".var_export($e, true));
} catch(\LINE\LINEBot\Exception\InvalidEventRequestException $e) {
  error_log("parseEventRequest failed. InvalidEventRequestException => ".var_export($e, true));
}

foreach ($events as $event) {
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent)) {
    error_log('Non message event has come');
    continue;
  }
  if (!($event instanceof \LINE\LINEBot\Event\MessageEvent\TextMessage)) {
    error_log('Non text message has come');
    continue;
  }
  $text = $event->getText();
  $pieces = explode(" ", $text);
  $bot = $pieces[0];
  $word = $pieces[1];
  $service = $pieces[2];

  $maxResults = 5;
  $order = 'date';
 // or $bot == 'ranran' or $bot == 'らんらん' or $bot == 'ranpig' or $bot == 'linebot'
  if($bot == '(´･ω･`)'){
    $bot->replyText($event->getReplyToken(), "(´･ω･`)らんらん？");
    if($service == 'youtube' or $service == 'よつべ'){

      $client = new Google_Client();
      $client->setDeveloperKey($DEVELOPER_KEY_YOUTUBE);
      $youtube = new Google_YoutubeService($client);

      try {
      $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $text,  //検索キーワード
            'maxResults' => $maxResults, //検索動画数
            'order' => $order // 順番
          ));

      $sendtext = '';

      foreach ($searchResponse['items'] as $searchResult) {
        switch ($searchResult['id']['kind']) {
          case 'youtube#video':
            $sendtext .= $searchResult['snippet']['title'] . "\n";
            $sendtext .= "http://www.youtube.com/watch?v=" . $searchResult['id']['videoId'] . "\n";
          break;
        }
      }

      $bot->replyText($event->getReplyToken(), $sendtext);

      } catch (Google_ServiceException $e) {
        $bot->replyText($event->getReplyToken(), "error");
      } catch (Google_Exception $e) {
        $bot->replyText($event->getReplyToken(), "error");
      }
    }

    else{
      $bot->replyText($event->getReplyToken(), "error");
    }
  }
  else{
    $bot->replyText($event->getReplyToken(), $text);
  }
}

 ?>