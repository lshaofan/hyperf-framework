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
use Gb\Framework\Exception\CommonException;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * 常规错误信息返回.
 */
class CommonExceptionHandler extends ExceptionHandler
{
    use ResponseTrait;

    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    public function handle(Throwable $throwable, ResponseInterface $response): ResponseInterface
    {
        # # 记录日志
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        # # 格式化输出
        $code = $throwable->getCode();
        $httpCode = ErrorCode::getHttpCode($code);

        # 如果默认code不存在，则寻找业务自定义错误码
        if (! $httpCode && class_exists(\Gb\App\Common\Constants\AppErrCode::class)) {
            $httpCode = \Gb\App\Common\Constants\AppErrCode::getHttpCode($code);
        }
        $data = $this->formatData(null, $throwable->getMessage(), $code);
        # # 阻止异常冒泡
        $this->stopPropagation();
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data === false) {
            $data = '服务器内部错误';
        }
        $dataStream = new SwooleStream($data);
        return $response->withHeader('Server', 'Gb')
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus($httpCode)
            ->withBody($dataStream);
    }

    public function isValid(Throwable $throwable): bool
    {
        return $throwable instanceof CommonException;
    }
}
