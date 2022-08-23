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

use ErrorException;
use Gb\Framework\Action\Traits\ResponseTrait;
use Gb\Framework\Constants\ErrorCode;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\ExceptionHandler\ExceptionHandler;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Throwable;

class ErrorExceptionHandler extends ExceptionHandler
{
    use ResponseTrait;

    protected StdoutLoggerInterface $logger;

    public function __construct(StdoutLoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * @return mixed
     */
    public function handle(Throwable $throwable, \Psr\Http\Message\ResponseInterface $response)
    {
        # # 记录日志
        $this->logger->error(sprintf('%s[%s] in %s', $throwable->getMessage(), $throwable->getLine(), $throwable->getFile()));
        $this->logger->error($throwable->getTraceAsString());

        # # 格式化输出
        $level = $throwable instanceof ErrorException ? 'error' : 'hard';
        # 格式化输出
        $code = ErrorCode::SERVER_ERROR;

        $data = $this->formatData(null, '后台服务异常.' . $level, $code);
        $data = json_encode($data, JSON_UNESCAPED_UNICODE);
        if ($data === false) {
            $data = '服务器内部错误';
        }
        $dataStream = new SwooleStream($data);
        # # 阻止异常冒泡
        $this->stopPropagation();
        return $response->withHeader('Server', 'Gb')
            ->withAddedHeader('Content-Type', 'application/json;charset=utf-8')
            ->withStatus(ErrorCode::getHttpCode(ErrorCode::SERVER_ERROR))
            ->withBody($dataStream);
    }

    public function isValid(Throwable $throwable): bool
    {
        return true;
    }
}
