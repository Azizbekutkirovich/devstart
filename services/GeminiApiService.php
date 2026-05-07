<?php

namespace app\services;

use Yii;
use yii\httpclient\Client;
use yii\httpclient\CurlTransport;

class GeminiApiService
{
	private $api_key = "AIzaSyBAC91Tzwh81W2djy0h0iMmFl9dsgvGxs4";

	public function getContent(string $prompt)
	{
		$client = new Client([
			'transport' => CurlTransport::class,
		]);
		$url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent";
		try {
			$response = $client->createRequest()
				->setMethod("POST")
				->setUrl($url)
				->addHeaders([
					'content-type' => 'application/json',
					'x-goog-api-key' => $this->api_key
				])
				->setOptions([
			        CURLOPT_CONNECTTIMEOUT => 4,
			        CURLOPT_TIMEOUT => 35,
			    ])
				->setContent(json_encode([
					'contents' => [
				        [
				            'parts' => [
				                [
				                    'text' => $prompt
				                ]
				            ]
				        ]
					]
				]))
				->send();

			$result = $response->getData();

			if (!$response->isOk) {
				throw new \Exception("External API error: {$result['error']['message']}", $response->statusCode);
			}

			return $result['candidates'][0]['content']['parts'][0]['text'];
		} catch (\Throwable $e) {
			Yii::error([
				'message' => $e->getMessage(),
				'code' => $e->getCode(),
			], 'httpclient');
			return "network error";
		}
	}

	public function prepareSystemForStreaming()
    {
        if (function_exists('apache_setenv')) {
            @apache_setenv('no-gzip', 1);
        }
        @ini_set('zlib.output_compression', 0);
        @ini_set('implicit_flush', 1);

        header('Content-Type: text/event-stream');
        header('Cache-Control: no-cache');
        header('X-Accel-Buffering: no');
        header('Connection: keep-alive');

        // Barcha PHP bufferlarini tozalash
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        flush();
    }

	public function streamContent($prompt, $callback)
    {
        $url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash-lite:streamGenerateContent?key=" . $this->api_key."&alt=sse";

        $data = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt]
                    ]
                ]
            ]
        ];

        $ch = curl_init($url);

        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        
        // Javobni darhol chiqarish (Return qilmaslik)
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
        
        // Timeout sozlamalari (uzun javoblar uchun)
        curl_setopt($ch, CURLOPT_TIMEOUT, 0); 
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10);

        // Xatolik xabarlarini bodysi bilan olish uchun
        curl_setopt($ch, CURLOPT_FAILONERROR, false);

        // Har bir ma'lumot bo'lagi (chunk) kelganda ishlovchi funksiya
        $errorResponse = '';
        curl_setopt($ch, CURLOPT_WRITEFUNCTION, function($ch, $chunk) use ($callback, $prompt, &$errorResponse) {
            // Agar foydalanuvchi brauzerni yopib yuborgan bo'lsa, streamni to'xtatamiz
            if (connection_aborted()) {
                return 0; // cURL ulanishni yopadi
            }

            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($httpCode >= 400) {
                // Agar xato bo'lsa, ma'lumotni ekranga chiqarmaymiz, balki o'zgaruvchiga yig'amiz
                $errorResponse .= $chunk;
                return strlen($chunk);
            }

            $clean_json = str_replace('data: ', '', $chunk);
    
            $data = json_decode($clean_json, true);

            if (isset($data['candidates'][0]['content']['parts'][0]['text'])) {
                $text_part = $data['candidates'][0]['content']['parts'][0]['text'];
                $callback($text_part);
            }

            return strlen($chunk);
        });

        // So'rovni bajarish
        $result = curl_exec($ch);

        // 1. cURL darajasidagi xatolarni tekshirish (DNS, Timeout, Connection)
        if (curl_errno($ch)) {
            $errorMsg = curl_error($ch);
            $errorCode = curl_errno($ch);

            Yii::error([
                'message' => 'cURL Error: ' . $errorMsg,
                'code' => $errorCode
            ], 'GeminiStreamClient');

            throw new \Exception("NETWORK_ERROR");
        }

        // 2. HTTP darajasidagi xatolarni tekshirish (401, 429, 500)
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode >= 400) {
            if (isset($errorResponse)) {
                $jsonStr = str_replace('data: ', '', $errorResponse);

                $data = json_decode($jsonStr, true);

                $errorMessage = $data['error']['message'] ?? 'No message provided';
            } else {
                $errorResponse = "No error data";
            }

            Yii::error([
                'message' => 'Gemini API HTTP Error',
                'http_code' => $httpCode,
                'api_error_message' => $errorMessage, // Butun xatolik javobini logga yozamiz
                'prompt' => $prompt
            ], 'GeminiStreamClient');

            throw new \Exception("API_ERROR");
        }

        curl_close($ch);
        return true;
    }
}