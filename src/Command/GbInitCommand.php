<?php

declare(strict_types=1);
/**
 * This file is part of 绿鸟科技.
 *
 * @link     https://www.greenbirds.cn
 * @document https://greenbirds.cn
 * @contact  liushaofan@greenbirds.cn
 */
namespace Gb\Framework\Command;

use Gb\App\User\Contract\BackendUserContract;
use Gb\App\User\Model\BackendUser;
use Hyperf\Command\Annotation\Command;
use Hyperf\Command\Command as HyperfCommand;
use Hyperf\Contract\ConfigInterface;
use Hyperf\DbConnection\Db;
use Hyperf\Di\Annotation\Inject;
use Hyperf\Redis\Redis;
use Psr\Container\ContainerInterface;
use Qbhy\HyperfAuth\AuthManager;
use Qbhy\SimpleJwt\JWTManager;
use Swoole\Coroutine\System;
use Symfony\Component\Console\Question\Question;

#[Command]
class GbInitCommand extends HyperfCommand
{
    protected ContainerInterface $container;

    #[Inject]
    protected ConfigInterface $config;

    #[Inject]
    protected AuthManager $authManager;

    protected BackendUserContract $userService;

    public function __construct(ContainerInterface $container, ConfigInterface $config, AuthManager $authManager)
    {
        $this->container = $container;
        $this->config = $config;
        $this->authManager = $authManager;

        parent::__construct('gb:init');
    }

    public function handle()
    {       # 创建.env
        if (! file_exists(BASE_PATH . '/.env')) {
            $content = '';
            file_exists(BASE_PATH . '/.env.example') && $content = file_get_contents(BASE_PATH . '/.env.example');
            file_put_contents(BASE_PATH . '/.env', $content);
        }
        $this->setDomain();
        $this->mysqlInit();
        try {
            $this->mysqlDataInit();
        } catch (\Exception $e) {
            $this->warn('数据迁移执行失败，请在继续下面的配置前执行：' . 'php bin/hyperf.php migrate');
        }
         $this->redisInit();

        $this->userService = $this->container->get(BackendUserContract::class);
        $this->adminRegister();
        $this->line('项目初始化完成.', 'info');
    }

    public function configure()
    {
        parent::configure();
        $this->setDescription('绿鸟-项目初始化');
    }

    /**
     * Prompt the user for input with validator.
     *
     * @param null|mixed $default
     * @return mixed
     */
    public function askWithValidator(string $question, callable $validator, $default = null)
    {
        $question = new Question($question, $default);
        $question->setValidator($validator);
        return $this->output->askQuestion($question);
    }

    protected function redisInit(): void
    {
        $data = [
            'redis.default.host' => [
                'REDIS_HOST',
                $this->ask('输入redis.host', env('REDIS_HOST', 'redis')),
            ],
            'redis.default.port' => [
                'REDIS_PORT',
                $this->ask('输入redis.port', env('REDIS_PORT', '6379')),
            ],
            'redis.default.auth' => [
                'REDIS_AUTH',
                $this->ask('输入redis.auth', env('REDIS_AUTH', '')),
            ],
            'redis.default.db' => [
                'REDIS_DB',
                $this->ask('输入redis.db', env('REDIS_DB', '0')),
            ],
        ];
        try {
            $this->setEnvs($data);
            $this->container->get(Redis::class)->ping('demo');
        } catch (\Exception $e) {
            $falseMsg = $e->getPrevious() ? $e->getPrevious()->getMessage() : '';
            $this->error(sprintf('redis设置错误:%s,请重新填写', $falseMsg));
            $this->redisInit();
        }
    }

    /**
     * 数据库初始化.
     */
    protected function mysqlInit(): void
    {
        $data = [
            'databases.default.host' => [
                'DB_HOST',
                $this->ask('输入mysql.host', env('DB_HOST', 'mysql')),
            ],
            'databases.default.port' => [
                'DB_PORT',
                $this->ask('输入mysql.port', env('DB_PORT', '3306')),
            ],
            'databases.default.database' => [
                'DB_DATABASE',
                $this->ask('输入mysql.database', env('DB_DATABASE', 'app')),
            ],
            'databases.default.username' => [
                'DB_USERNAME',
                $this->ask('输入mysql.username', env('DB_USERNAME', 'gb_app')),
            ],
            'databases.default.password' => [
                'DB_PASSWORD',
                $this->ask('输入mysql.password', env('DB_PASSWORD', '123456')),
            ],
        ];

        try {
            $this->setEnvs($data);
            Db::select('show databases like \'' . $data['databases.default.database'][1] . '\'');
            $this->mysqlDatabase = $data['databases.default.database'][1];
        } catch (\Exception $e) {
            $this->error(sprintf('mysql设置错误:%s,请重新填写', $e->getPrevious()->getMessage()));
            $this->mysqlInit();
        }
    }

    /**
     * @param array $data ...
     * @param string $path ...
     */
    protected function setEnvs(array $data, string $path = ''): void
    {
        $rows = [];
        foreach ($data as $configKey => $item) {
            [$key, $val] = $item;
            $isNewLine = $item[2] ?? false;

            $rows[] = [$key, $val];
            $this->setEnv($key, $val, $isNewLine, $path);
            if (! is_numeric($configKey)) {
                if ($configKey === 'redis.default.db' || $configKey === 'redis.default.port') {
                    $val = (int) $val;
                }
                $this->config->set($configKey, $val);
            }
        }

        $this->table(['属性名称', '属性值'], $rows);
        $this->info('重载配置成功');
    }

    /**
     * set value within a given file.
     *
     * @param string $key ...
     * @param null|string $val ...
     * @param bool $isNewLine ...
     * @param string $path ...
     */
    protected function setEnv(string $key, ?string $val = '', bool $isNewLine = false, string $path = ''): void
    {
        $path || $path = BASE_PATH . '/.env';
        $oldVal = env($key, false);
        $newKeyVal = $key . '=' . $val;
        # # 添加
        if ($oldVal === false) {
            $isNewLine && $newKeyVal = "\n" . $newKeyVal;
            file_put_contents($path, $newKeyVal, FILE_APPEND);
        } else {
            # # 修改
            $oldKeyVal = $key . '=' . $oldVal;
            file_put_contents($path, str_replace($oldKeyVal, $newKeyVal, file_get_contents($path)));
        }
    }

    /**
     * 设置域名.
     */
    protected function setDomain(): void
    {
        apiBaseUrl:
        $apiBaseUrl = $this->ask('输入后端接口地址，例如：https://api.greenbirds.cn', env('API_BASE_URL', 'https://api.greenbirds.cn'));
        if (! str_contains($apiBaseUrl, 'http')) {
            $this->warn('后端接口地址请添加 http(s)://');
            goto apiBaseUrl;
        }
        $this->setEnvs([['API_BASE_URL', $apiBaseUrl]]);
    }

    /**
     * 管理员注册.
     * @return bool ...
     */
    protected function adminRegister(): bool
    {
        # 手机号

        $phone = $this->askWithValidator('输入管理员的手机号', function ($value) {
            if (is_numeric($value) && strlen($value) === 11) {
                return $value;
            }
            throw new \Exception('手机号错误');
        });
        $username = $this->askWithValidator('输入管理员用户名', function ($value) {
            if (strlen($value) <= 20 && strlen($value) >= 6) {
                return $value;
            }
            throw new \Exception('用户名长度必须在6-20位之间');
        });
        $user = $this->userService->getBackendUserByName($username);
        if (! empty($user)) {
            $this->warn('已经存在该管理员');
            return true;
        }
        # # 密码
        $password = $this->secret('输入管理员密码', false);
        # # 生成新密码
        $guard = $this->authManager->guard('AdminApi');
        /** @var JWTManager $jwt */
        $jwt = $guard->getJwtManager();
        $newPassword = $jwt->getEncrypter()->signature($password);

        // @phpstan-ignore-next-line
        $user = BackendUser::create(
            [
                'id' => 1,
                'username' => $username,
                'password' => $newPassword,
                'real_name' => '管理员',
                'phone' => $phone,
                'avatar' => '',
                'status' => 'normal',
                'description' => '',
                'last_login_ip' => '127.0.0.1',
                'last_login_time' => date('Y-m-d H:i:s'),
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]
        );
        if ($user->id) {
            return true;
        }
        return false;
    }

    private function mysqlDataInit(): void
    {
        $version = Db::select('select VERSION() AS version;');
        $version = empty($version[0]->version) ? 5.6 : (float) $version[0]->version;
        if ($version < 8.0) {
            throw new \RuntimeException('mysql版本号错误，需要版本号 >= 8.0');
        }

        if (! empty(Db::select('SHOW TABLES LIKE "mc_user"'))) {
            $this->info('sql初始化已经完成，无需再重新初始化');
            return;
        }
        $publishSh = sprintf('php bin/hyperf.php %s', 'migrate');
        $shRes = System::exec($publishSh);
        if ($shRes['signal'] === false || $shRes['code'] !== 0) {
            $falseMsg = '数据库初始化失败，请手动执行' . $publishSh;
            isset($shRes['output']) && $falseMsg .= ':' . $shRes['output'];
            $this->line($shRes, 'error');
        }
    }
}
