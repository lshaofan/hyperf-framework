<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Action\Traits;

use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface as PsrResponseInterface;

trait ResponseTrait
{
    /**
     * Respond with a no content response.
     */
    public function noContent(string $message = ''): PsrResponseInterface
    {
        return $this->success([], $message, 204);
    }

    /**
     *  以接受的响应进行响应，并关联位置和或内容（如果提供）。
     */
    public function accepted(array $data = [], string $message = '', string $location = ''): PsrResponseInterface
    {
        $response = $this->success($data, $message, 202);
        if ($location) {
            $response = $response->withAddedHeader('Location', $location);
        }

        return $response;
    }

    /**
     * 使用创建的响应进行响应并关联位置（如果提供） 固定返回状态码201。
     */
    public function created(?array $data = [], string $message = '创建成功', string $location = ''): PsrResponseInterface
    {
        $response = $this->success($data, $message, 201);
        if ($location) {
            $response = $response->withAddedHeader('Location', $location);
        }

        return $response;
    }

    /**
     * 成功方法的别名，无需指定数据参数。
     */
    public function ok(string $message = '操作成功', int $code = 200, array $headers = []): PsrResponseInterface
    {
        return $this->success([], $message, $code, $headers);
    }

    /**
     * 成功方法的别名，不需要指定message和data参数。
     * 您可以使用 ResponseCodeEnum 本地化消息。
     */
    public function localize(int $code = 200, array $headers = [], int $option = 0): PsrResponseInterface
    {
        return $this->ok('', $code, $headers, $option);
    }

    public function success(mixed $data = null, string $message = '', int $code = 200, array $headers = []): PsrResponseInterface
    {
        return $this->formatArrayResponse($data, $message, $code, $headers);
    }

    /**
     * Return a 400 bad request error.
     */
    public function errorBadRequest(string $message = ''): void
    {
        $this->fail($message, 400);
    }

    /**
     * Return a 401 unauthorized error.
     */
    public function errorUnauthorized(string $message = ''): void
    {
        $this->fail($message, 401);
    }

    /**
     * Return a 403 forbidden error.
     */
    public function errorForbidden(string $message = ''): void
    {
        $this->fail($message, 403);
    }

    /**
     * Return a 404 not found error.
     */
    public function errorNotFound(string $message = ''): void
    {
        $this->fail($message, 404);
    }

    /**
     * Return a 405 method not allowed error.
     */
    public function errorMethodNotAllowed(string $message = ''): void
    {
        $this->fail($message, 405);
    }

    /**
     * Return a 500 internal server error.
     */
    public function errorInternal(string $message = ''): void
    {
        $this->fail($message);
    }

    /**
     * Return an fail response.
     *
     * @param null|array $errors
     */
    public function fail(string $message = '操作失败', int $code = 500, array|null $errors = null, array $header = []): PsrResponseInterface
    {
        return $this->response(
            $this->formatData(null, $message, $code, $errors),
            $code,
            $header,
        );

//        if (is_null($errors)) {
//            $response->throwResponse();
//        }
    }

    /**
     * Format normal array data.
     */
    protected function formatArrayResponse(mixed $data, string $message = '', int $code = 200, array $headers = []): PsrResponseInterface
    {
        return $this->response($this->formatData($data, $message, $code), $code, $headers);
    }

    /**
     * Return a new JSON response from the application.
     *
     * @param array|mixed $data
     */
    protected function response(mixed $data = [], int $status = 200, array $headers = []): PsrResponseInterface
    {
        $body = new SwooleStream(json_encode($data, JSON_UNESCAPED_UNICODE));

        $response = $this->response->withBody($body)->withStatus($status)->withHeader('Content-Type', 'application/json');

        foreach ($headers as $key => $value) {
            $response = $response->withAddedHeader($key, $value);
        }
        return $response;
    }

    /**
     * Format return data structure.
     *
     * @param $message
     * @param $code
     * @param null $errors
     */
    protected function formatData(mixed $data, $message, &$code, $errors = null): array
    {
        $originalCode = $code;
        $code = (int) substr(strval($code), 0, 3); // notice
        if ($code >= 400 && $code <= 499) {// client error
            $type = 'error';
        } elseif ($code >= 500 && $code <= 599) {// service error
            $type = 'fail';
        } else {
            $type = 'success';
        }
        $res = [
            'type' => $type,
            'code' => $originalCode,
            'message' => $message,
        ];
        # 如果type==='success' 则删除error字段
        if ($type === 'success') {
            $res['result'] = $data ?: (object) $data;
        } else {
            $res['error'] = $errors ?: (object) [];
        }
        # 如果code===201 则删除 result字段
        if ($code === 201) {
            unset($res['result']);
        }

        return $this->formatDataFields($res);
    }

    /**
     * Format response data fields.
     */
    protected function formatDataFields(array $responseData, array $dataFieldsConfig = []): array
    {
        return $responseData;
    }
}
