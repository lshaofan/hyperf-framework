<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Exception\Handler;

use Gb\Framework\Action\Traits\ResponseTrait;
use Gb\Framework\Constants\ErrorCode;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

class ValidationExceptionHandler extends ExceptionHandler
{
    use ResponseTrait;

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        $this->stopPropagation();

        /** @var \Hyperf\Validation\ValidationException $throwable */
        $falseMsg = $throwable->validator->errors()->first();

        # 格式化输出
        $code = ErrorCode::INVALID_PARAMS;

        $data = $this->formatData(null, $falseMsg, $code);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data === false) {
            $data = '服务器内部错误';
        }
        $dataStream = new SwooleStream($data);

        return $response->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($throwable->status)
            ->withBody($dataStream);
    }

    public function isValid(Throwable $throwable): bool
    {
        $validateException = \Hyperf\Validation\ValidationException::class;
        if (class_exists($validateException) && $throwable instanceof $validateException) {
            return true;
        }
        return false;
    }
}
