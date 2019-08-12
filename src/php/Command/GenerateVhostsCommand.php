<?php
declare(strict_types=1);

namespace Hipper\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Twig_Environment;

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
        Twig_Environment $twig
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

        $this->addOption('fastcgi-pass', null, InputOption::VALUE_REQUIRED, 'fastcgi_pass', '127.0.0.1:9000');
    }

    public function execute(InputInterface $input, OutputInterface $output)
    {
        $outputDir = $input->getArgument('path-to-vhosts');
        $repositoryRoot = realpath(__DIR__ . '/../../../');
        $this->twig->getLoader()->setPaths($repositoryRoot . '/config/vhost_templates');

        $parameters = [
            'domain' => $input->getArgument('domain'),
            'fastcgi_pass' => $input->getOption('fastcgi-pass'),
            'path_to_ssl_cert' => $input->getArgument('path-to-ssl-cert'),
            'path_to_code' => $repositoryRoot,
        ];

        foreach (self::TEMPLATES as $twigFile => $outputName) {
            $rendered = $this->twig->render($twigFile . '.twig', $parameters);
            file_put_contents($outputDir . '/' . $outputName . '.conf', $rendered);
        }

        $output->writeln('ALL DONE!');
        $output->writeln('✅ Vhosts generated and placed in ' . realpath($outputDir));
        $output->writeln('🐘 Don’t forget to reload nginx!');
    }
}
