<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once 'google-api-php-client/src/Google_Client.php';
require_once 'google-api-php-client/src/contrib/Google_YouTubeService.php';

$DEVELOPER_KEY = 'AIzaSyBN8gvRSd8k_NuqBKF4ITdGxbDfLnNocuw';


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
  $pieces = preg_split('/[\s|\x{3000}]+/u', $text);
  $startbot = $pieces[0];
  $word = $pieces[1];
  $service = $pieces[2];
  
  $sendtext = '';


  if($startbot == "ranran" or $startbot == "(´･ω･`)" or $startbot == "らん豚" or $startbot == "linebot" or $startbot == "ranpig" or $startbot == "らんらん")
  {
    if(($service == 'youtube' or $service == '動画') and isset($word))
    {
      $client = new Google_Client();
      $client->setDeveloperKey($DEVELOPER_KEY);
      $youtube = new Google_YoutubeService($client);

      $maxResults = 1;
      $order = 'date';
      
      if(isset($pieces[3]) and 1 < $pieces[3] and $pieces[3] < 20)
      {
        $maxResults = $pieces[3];
      }
      if(isset($pieces[4]) and ($pieces[4] == "date" or $pieces[4] == "rating" or $pieces[4] == "title" or $pieces[4] == "viewCount" or $pieces[4] == "videoCount" or $pieces[4] == "relevance"))
      {
        $order = $pieces[4];
      }

      try {
      $searchResponse = $youtube->search->listSearch('id,snippet', array(
            'q' => $word,  //検索キーワード
            'maxResults' => $maxResults, //検索動画数
            'order' => $order // 順番
          ));

      $i = 1;
      foreach ($searchResponse['items'] as $searchResult) {
        switch ($searchResult['id']['kind']) {
          case 'youtube#video':
            $sendtext .= $i.'.';
            $sendtext .= $searchResult['snippet']['title'] . "\n";
            $sendtext .= "http://www.youtube.com/watch?v=" . $searchResult['id']['videoId'] . "\n";
            $i++;
          break;
        }
      }

      $bot->replyText($event->getReplyToken(), $sendtext);

      } catch (Google_ServiceException $e) {
        $bot->replyText($event->getReplyToken(), "(´･ω･`)らんらんは豚だから難しいことはわからないよ");
      } catch (Google_Exception $e) {
        $bot->replyText($event->getReplyToken(), "(´･ω･`)らんらんは豚だから難しいことはわからないよ");
      }
    }

    else if(($service == 'image' || $service == '画像') and isset($word)){

      $maxResults = 1;
      if(isset($pieces[3]) and 1 < $pieces[3] and $pieces[3] < 20)
      {
        $maxResults = $pieces[3];
      }
      //検索エンジンID
      $cx = "011043179743664306189:yvmhmv_3uqo";
      // 検索用URL
      $url = "https://www.googleapis.com/customsearch/v1?q=" . $word . "&key=" . $DEVELOPER_KEY . "&cx=" . $cx ."&searchType=image". "&num=".$maxResults;
      $json = file_get_contents($url, true);

      $obj = json_decode($json, false);
      $sendtext = "";
      $imagePath ='';
      foreach ($obj->items as $value) {
        $sendtext .= $value->title . "\n";
        $sendtext .= $value->link . "\n";
      }
      $bot->replyText($event->getReplyToken(), $sendtext);
    }

    else if(($service == 'search' or $service == '検索' ) and isset($word)) {

      $maxResults = 1;  
      if(isset($pieces[3]) && 1 < $pieces[3] && $pieces[3] < 20){
        $maxResults = $pieces[3];
      }
      //検索エンジンID
      $cx = "011043179743664306189:yvmhmv_3uqo";
      // 検索用URL
      $url = "https://www.googleapis.com/customsearch/v1?q=" . $word . "&key=" . $DEVELOPER_KEY . "&cx=" . $cx ."&num=".$maxResults;
      $json = file_get_contents($url, true);

      $obj = json_decode($json, false);
      $sendtext = "";
      $imagePath ='';
      foreach ($obj->items as $value) {
        $sendtext .= $value->title . "\n";
        $sendtext .= $value->link . "\n";
      }
      $bot->replyText($event->getReplyToken(), $sendtext);
    }

    else{
      $bot->replyText($event->getReplyToken(), "(´･ω･`)やんやん？");
    }
  }

  else {
    // $bot->replyText($event->getReplyToken(), $text);
  }
}

 ?>
