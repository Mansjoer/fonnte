<?php

namespace Mansjoer\Fonnte;

use Exception;
use Http;
use Log;

class Fonnte
{
    public $base_url;
    public $deviceToken;
    public $accountToken;

    public function __construct()
    {
        $this->base_url = config('fonnte.base_url');
        $this->deviceToken = config('fonnte.device_token');
        $this->accountToken = config('fonnte.account_token');
    }

    public function ping($recipient = null)
    {
        if (empty($recipient)) {
            $recipient = config('fonnte.fallback_recipient');
        }
        return $this->sendMessage($recipient, 'PING');
    }

    public function getDevice()
    {
        $endpoint = '/get-devices';

        return $this->requestAccountApi($endpoint);
    }

    public function myDevice()
    {
        $endpoint = '/device';

        return $this->requestAccountApi($endpoint);
    }

    public function validate($recipient)
    {
        if (is_array($recipient)) {
            $recipient = implode(',', $recipient);
        }

        $endpoint = '/validate';
        $param = [
            'target' => $recipient,
        ];

        return $this->requestDeviceApi($endpoint, $param);
    }

    public function sendMessage($recipient, $message, $additional_param = [])
    {
        // recipient only accept string by default
        if (is_array($recipient)) {
            $recipient = implode(',', $recipient);
        }

        $endpoint = '/send';
        $param = array_merge($additional_param, [
            'target' => $recipient,
            'message' => $message,
        ]);

        if (config('app.env') == 'local' && config('fonnte.fallback_recipient')) {
            // prevent send unwantend message to another user
            $param['target'] = config('fonnte.fallback_recipient');
        }

        return $this->requestDeviceApi($endpoint, $param);
    }

    public function requestAccountApi($endpoint, $param = [])
    {
        $response = Http::withHeaders([
            'Authorization' => $this->accountToken,
        ])
            ->withOptions([
                'verify' => false,
            ])
            ->asForm()
            ->accept('application/json')
            ->post($this->base_url . $endpoint, $param);

        if (!$response->ok()) {
            // log
            $logparam = $param;
            $logparam['message'] = isset($param['message']) ? (strlen($param['message']) > 20 ? substr($param['message'], 0, 20) . '...' : $param['message']) : null;
            Log::error("ERROR RESPONSE fonnte", [
                'endpoint' => $this->base_url . $endpoint,
                'request' => $logparam,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            throw new Exception("Error when connect to fonnte endpoint. Check log for more information");
        }

        $logparam = $param;
        $logparam['message'] = isset($param['message']) ? (strlen($param['message']) > 20 ? substr($param['message'], 0, 20) . '...' : $param['message']) : null;
        Log::info("OK RESPONSE fonnte", [
            'endpoint' => $this->base_url . $endpoint,
            'request' => $logparam,
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return json_decode($response->body(), true);
    }

    public function requestDeviceApi($endpoint, $param = [])
    {
        $response = Http::withHeaders([
            'Authorization' => $this->deviceToken,
        ])
            ->withOptions([
                'verify' => false,
            ])
            ->asForm()
            ->accept('application/json')
            ->post($this->base_url . $endpoint, $param);

        if (!$response->ok()) {
            // log
            $logparam = $param;
            $logparam['message'] = isset($param['message']) ? (strlen($param['message']) > 20 ? substr($param['message'], 0, 20) . '...' : $param['message']) : null;
            Log::error("ERROR RESPONSE fonnte", [
                'endpoint' => $this->base_url . $endpoint,
                'request' => $logparam,
                'response' => $response->body(),
                'status' => $response->status(),
            ]);

            throw new Exception("Error when connect to fonnte endpoint. Check log for more information");
        }

        $logparam = $param;
        $logparam['message'] = isset($param['message']) ? (strlen($param['message']) > 20 ? substr($param['message'], 0, 20) . '...' : $param['message']) : null;
        Log::info("OK RESPONSE fonnte", [
            'endpoint' => $this->base_url . $endpoint,
            'request' => $logparam,
            'response' => $response->body(),
            'status' => $response->status(),
        ]);

        return json_decode($response->body(), true);
    }
}
