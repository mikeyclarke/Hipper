<?php
declare(strict_types=1);

namespace Hipper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Dotenv\Dotenv;
use Twig\Loader\FilesystemLoader;

class DumpCombinedEnvConfigCommand extends Command
{
    protected static $defaultName = 'app:dump-combined-env-config';

    public function __construct()
    {
        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Dump a .env.local.php containing all config applicable to this environment');
        $this->addArgument('env', InputArgument::REQUIRED, 'Name of the environment, e.g. “prod”, “stag”');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $path = dirname(__DIR__) . '/../../.env';
        $env = $input->getArgument('env');

        $vars = $this->loadEnvVars($path, $env);
        $vars = var_export($vars, true);
        $vars = <<<EOF
<?php
return $vars;

EOF;

        file_put_contents($path . '.local.php', $vars, LOCK_EX);

        return 0;
    }

    private function loadEnvVars(string $path, string $env): array
    {
        $globalsBackup = [$_SERVER, $_ENV];
        unset($_SERVER['APP_ENV']);
        $_ENV = ['APP_ENV' => $env];
        $_SERVER['SYMFONY_DOTENV_VARS'] = implode(',', array_keys($_SERVER));
        putenv('SYMFONY_DOTENV_VARS=' . $_SERVER['SYMFONY_DOTENV_VARS']);

        try {
            $dotenv = new Dotenv(false);
            $dotenv->loadEnv($path);
            unset($_ENV['SYMFONY_DOTENV_VARS']);
            $vars = $_ENV;
        } finally {
            list($_SERVER, $_ENV) = $globalsBackup;
        }

        return $vars;
    }
}
