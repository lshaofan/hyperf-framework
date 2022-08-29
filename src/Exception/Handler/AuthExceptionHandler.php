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
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Qbhy\HyperfAuth\Exception\AuthException;
use Qbhy\HyperfAuth\Exception\UnauthorizedException;
use Qbhy\SimpleJwt\Exceptions\InvalidTokenException;
use Qbhy\SimpleJwt\Exceptions\JWTException;
use Qbhy\SimpleJwt\Exceptions\TokenExpiredException;
use Throwable;

class AuthExceptionHandler extends ExceptionHandler
{
    use ResponseTrait;

    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, \Psr\Http\Message\ResponseInterface $response): mixed
    {
        $code = ErrorCode::AUTH_FAILED;

        # # 格式化输出
        $throwable instanceof UnauthorizedException && $code = ErrorCode::AUTH_UNAUTHORIZED;
        $throwable->getPrevious() instanceof TokenExpiredException && $code = ErrorCode::AUTH_SESSION_EXPIRED;
        $throwable->getPrevious() instanceof InvalidTokenException && $code = ErrorCode::AUTH_TOKEN_INVALID;

        $falseMsg = ErrorCode::getMessage($code);
        $httpCode = ErrorCode::getHttpCode($code);

        # 格式化输出
        $data = $this->formatData(null, $falseMsg, $code);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data === false) {
            $data = '服务器内部错误';
        }
        $dataStream = new SwooleStream($data);
        # # 阻止异常冒泡
        $this->stopPropagation();
        return $response->withHeader('Server', 'Gb')
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($httpCode)
            ->withBody($dataStream);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof AuthException || $throwable instanceof JWTException;
    }
}
