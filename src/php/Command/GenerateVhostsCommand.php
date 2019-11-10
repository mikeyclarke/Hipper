<?php
declare(strict_types=1);

namespace Hipper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig\Loader\FilesystemLoader;
use Twig\Environment as Twig;

class GenerateVhostsCommand extends Command
{
    const TEMPLATES = [
        'assets' => '0-hipper-assets',
        'site' => '1-hipper-site',
        'app' => '2-hipper-app',
    ];

    private $twig;
    protected static $defaultName = 'app:generate-vhosts';

    public function __construct(
        Twig $twig
    ) {
        $this->twig = $twig;

        parent::__construct();
    }

    protected function configure()
    {
        $this->setDescription('Generate nginx vhosts');
        $this->addArgument('domain', InputArgument::REQUIRED, 'Domain name, e.g. usehipper.test');
        $this->addArgument(
            'path-to-vhosts',
            InputArgument::REQUIRED,
            'Path to vhosts, e.g. `/usr/local/etc/nginx/servers`'
        );
        $this->addArgument(
            'path-to-ssl-cert',
            InputArgument::REQUIRED,
            'Path to SSL cert, e.g. `/usr/local/etc/nginx/ssl`'
        );

        $this->addOption(
            'path-to-code',
            null,
            InputOption::VALUE_REQUIRED,
            'Path to the hipper codebase, e.g. /var/hipper',
            realpath(__DIR__ . '/../../../')
        );
        $this->addOption('fastcgi-pass', null, InputOption::VALUE_REQUIRED, 'fastcgi_pass', '127.0.0.1:9000');
        $this->addOption(
            'internet-visible',
            null,
            InputOption::VALUE_NONE,
            'Make this instance of Hipper crawlable by bots; if this option isn’t present a restrictive ' .
            'X-Robots-Tag header will be applied to naked domain requests'
        );
        $this->addOption(
            'load-balancer-ip',
            null,
            InputOption::VALUE_REQUIRED,
            'IP address of the load balancer sat in front of the web servers; if provided, this will be used to ' .
            'configure proxy_protocol'
        );
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $input->getArgument('path-to-vhosts');
        if (!is_string($outputDir)) {
            return null;
        }
        $repositoryRoot = realpath(__DIR__ . '/../../../');

        $loader = $this->twig->getLoader();
        if ($loader instanceof FilesystemLoader) {
            $loader->setPaths($repositoryRoot . '/config/vhost_templates');
        }

        $parameters = [
            'domain' => $input->getArgument('domain'),
            'fastcgi_pass' => $input->getOption('fastcgi-pass'),
            'internet_visible' => $input->getOption('internet-visible'),
            'load_balancer_ip' => $input->getOption('load-balancer-ip'),
            'path_to_ssl_cert' => $input->getArgument('path-to-ssl-cert'),
            'path_to_code' => $input->getOption('path-to-code'),
        ];

        foreach (self::TEMPLATES as $twigFile => $outputName) {
            $rendered = $this->twig->render($twigFile . '.twig', $parameters);
            $path = sprintf('%s/%s.conf', $outputDir, $outputName);
            file_put_contents($path, $rendered);
        }

        $output->writeln('ALL DONE!');
        $output->writeln('✅ Vhosts generated and placed in ' . realpath($outputDir));
        $output->writeln('🐘 Don’t forget to reload nginx!');
    }
}
