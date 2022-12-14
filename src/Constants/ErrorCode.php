<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Constants;

use Hyperf\Constants\AbstractConstants;
use Hyperf\Constants\Annotation\Constants;

/**
 * 错误code
 * 3位HTTP码 + 6位业务码[前3位为模块，后3位为业务]
 * 有其它错误码需求，即使补充
 * 业务模块码:
 * 100  -  公共模块
 * 100  -  授权模块
 * 200  -  通讯录模块
 * 300  -  外部联系人模块
 * 301  -  外部联系人 - 高级属性模块
 * 400  -  会话内容存档模块.
 * @method static string getMessage(int $code) 获取错误码信息
 * @method static int getHttpCode(int $code) 获取错误码的httpCode
 */
#[Constants]
class ErrorCode extends AbstractConstants
{
    /**
     * @Message("token失效")
     * @HttpCode("401")
     */
    public const TOKEN_INVALID = 401001;

    /**
     * @Message("用户或密码错误")
     * @HttpCode("401")
     */
    public const AUTH_LOGIN_FAILED = 401002;

    /**
     * @Message("非法token")
     * @HttpCode("401")
     */
    public const AUTH_TOKEN_INVALID = 401003;

    /**
     * @Message("token过期")
     * @HttpCode("401")
     */
    public const AUTH_SESSION_EXPIRED = 401004;

    /**
     * @Message("未认证,没有token")
     * @HttpCode("401")
     */
    public const AUTH_UNAUTHORIZED = 401005;

    /**
     * @Message("认证失败")
     * @HttpCode("401")
     */
    public const AUTH_FAILED = 401006;

    /**
     * @Message("用户信息异常")
     * @HttpCode("401")
     */
    public const AUTH_USER_INVALID = 401007;

    /**
     * @Message("没有权限")
     * @HttpCode("403")
     */
    public const ACCESS_DENIED = 403007;

    /**
     * @Message("拒绝客户端请求")
     * @HttpCode("403")
     */
    public const ACCESS_REFUSE = 403008;

    /**
     * @Message("禁止重复操作")
     * @HttpCode("403")
     */
    public const NO_REPETITION_OPERATION = 403009;

    /**
     * @Message("客户端错误")
     * @HttpCode("400")
     */
    public const BAD_REQUEST = 400010;

    /**
     * @Message("非法的Content-Type头")
     * @HttpCode("401")
     */
    public const INVALID_CONTENT_TYPE = 401011;

    /**
     * @Message("资源未找到")
     * @HttpCode("404")
     */
    public const URI_NOT_FOUND = 404012;

    /**
     * @Message("非法的参数")
     * @HttpCode("422")
     */
    public const INVALID_PARAMS = 422013;

    /**
     * @Message("服务器异常")
     * @HttpCode("500")
     */
    public const SERVER_ERROR = 500014;

    /**
     * @Message("服务器异常(third-party-api)")
     * @HttpCode("500")
     */
    public const THIRD_API_ERROR = 500015;

    /**
     * @Message("请求方法错误")
     * @HttpCode("405")
     */
    public const INVALID_HTTP_METHOD = 405016;
}
