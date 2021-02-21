<?php
require __DIR__ . '/../vendor/autoload.php';

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Factory\AppFactory;

use \LINE\LINEBot;
use \LINE\LINEBot\HTTPClient\CurlHTTPClient;
use \LINE\LINEBot\MessageBuilder\MultiMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TextMessageBuilder;
use \LINE\LINEBot\MessageBuilder\StickerMessageBuilder;
use \LINE\LINEBot\MessageBuilder\TemplateBuilder;
use \LINE\LINEBot\SignatureValidator as SignatureValidator;

$pass_signature = true;

// set LINE channel_access_token and channel_secret
$channel_access_token = "ohxpQj9WvaJjPiDY9rftF1tF8HtqqBR1bgzB4o8LASpqCQceSa64brLweaDgdJnRXiyu9axxPXEiw8HW1ntYMecPyzcKI24kOReR5yVUlYoocDyUgx6ha7VHVV7nHWRlurNSw/UAnCBYWxaVTd0mywdB04t89/1O/w1cDnyilFU=";
$channel_secret = "
b8802ebd3b2ee71f039922d26d0b81bd";

// inisiasi objek bot
$httpClient = new CurlHTTPClient($channel_access_token);
$bot = new LINEBot($httpClient, ['channelSecret' => $channel_secret]);
// bot = access token and channel secret
$app = AppFactory::create();
$app->setBasePath("/public");

$app->get('/', function (Request $request, Response $response, $args) {
    $response->getBody()->write("Hello World!");
    return $response;
});

// buat route untuk webhook
$app->post('/webhook', function (Request $request, Response $response) use ($channel_secret, $bot, $httpClient, $pass_signature) 
{

    
    // get request body and line signature header
    $body = $request->getBody();
    $signature = $request->getHeaderLine('HTTP_X_LINE_SIGNATURE');
    // log body and signature
    file_put_contents('php://stderr', 'Body: ' . $body);

    if ($pass_signature === false) {
        // is LINE_SIGNATURE exists in request header?
        if (empty($signature)) {
            return $response->withStatus(400, 'Signature not set');
        }

        // is this request comes from LINE?
        if (!SignatureValidator::validateSignature($body, $channel_secret, $signature)) {
            return $response->withStatus(400, 'Invalid signature');
        }
    }

    $data = json_decode($body, true);
    if (is_array($data['events'])) {
        foreach ($data['events'] as $event) {
            if ($event['type'] == 'message' && $event['source']['type']=='user') {
                //reply message
                if ($event['message']['type'] == 'text') {
                    
                    if($event['message']['text']=='Pesan Kebab'){
                       
                        $bot->replyText($event['replyToken'],"Kebab Oyen dengan harga Rp.20,000,00 telah dipesan terimakasih telah berbelanja");
                    }

                    else if($event['message']['text']=='Pesan Burger'){
                       
                        $bot->replyText($event['replyToken'],"Burger Queen telah dipesan dengan harga Rp.30,000,00 terimakasih telah berbelanja");
                    }
                   elseif($event['message']['text']=='Pesan Kentanggor'){
                       
                   $bot->replyText($event['replyToken'],"Kentanggor telah dipesan dengan harga Rp.27,000,00 terimakasih telah berbelanja");
                        }
                     else {
                        
                        $userId = $event['source']['userId'];
                        // $getprofile = $bot->getProfile($userId);
                        // $profile = $getprofile->getJSONDecodedBody();
                        $flexTemplate = file_get_contents("../flex_message.json"); // template flex message
                      
                        $pesan = new TextMessageBuilder("Maaf aku tidak mengerti maksud mu, silahkan pesan makanan yang tersedia");
                            $bot->pushMessage($userId,$pesan);
                            $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                                'replyToken' => $event['replyToken'],
                                'messages'   => [
                                    [
                                        'type'     => 'flex',
                                        'altText'  => 'Menu Makanan',
                                        'contents' => json_decode($flexTemplate)
                                    ]
                                ],
                            ]);
                   
                        
                        }
                        
                  


                    // or we can use replyMessage() instead to send reply message
                    // $textMessageBuilder = new TextMessageBuilder($event['message']['text']);
                    // $result = $bot->replyMessage($event['replyToken'], $textMessageBuilder);


                 
                } 
               
                 
            }
            else if($event['type']=='follow'){
               
                $userId = $event['source']['userId'];
                $getprofile = $bot->getProfile($userId);
                $profile = $getprofile->getJSONDecodedBody();
                $flexTemplate = file_get_contents("../flex_message.json"); // template flex message
       
                             
                 $pesanTemanBaru = new TextMessageBuilder("Halo, ".$profile['displayName']." terimakasih telah menambahkan aku sebagai teman,
aku adalah bot foods,silahkan pilih menu yang anda inginkan");
                 $bot->pushMessage($userId,$pesanTemanBaru);
                    $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
                        'replyToken' => $event['replyToken'],
                        'messages'   => [
                            [
                                'type'     => 'flex',
                                'altText'  => 'Menu Makanan',
                                'contents' => json_decode($flexTemplate)
                            ]
                        ],
                    ]);
                  

                        
          }
        //   elseif($event['type']=='join'){

           
        //     $userId = $event['source']['userId'];
        //     $getprofile = $bot->getProfile($userId);
        //     $profile = $getprofile->getJSONDecodedBody();
        //     $pesanTemanBaru = new TextMessageBuilder("Halo, ".$profile['displayName']." terimakasih telah menambahkan aku ke grup ini");
        //     // $result = $bot->pushMessage($userId,$pesanTemanBaru);
        //     $bot->pushMessage($userId,$pesanTemanBaru);

        //     // $result = $httpClient->post(LINEBot::DEFAULT_ENDPOINT_BASE . '/v2/bot/message/reply', [
        //     //     'replyToken' => $event['replyToken'],
        //     //     'messages'   => [
        //     //         [
        //     //             'type'     => 'flex',
        //     //             'altText'  => 'Menu Makanan',
        //     //             'contents' => json_decode($flexTemplate)
        //     //         ]
        //     //     ],
        //     // ]);

        //   }
        }
        return $response->withStatus(200, 'for Webhook!'); //buat ngasih response 200 ke pas verify webhook
    }
    return $response->withStatus(400, 'No event sent!');
});



//test
$app->run();





