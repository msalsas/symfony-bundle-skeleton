<?php

namespace App\Command;

use App\Entity\User;
use App\Repository\UserRepository;
use App\Utils\Validator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use function Symfony\Component\String\u;

/**
 * A console command that creates the bundle skeleton in lib directory.
 *
 * To use this command, open a terminal window, enter into your project
 * directory and execute the following:
 *
 *     $ php bin/console skeleton-bundle:create
 *
 * To output detailed information, increase the command verbosity:
 *
 *     $ php bin/console skeleton-bundle:create -vv
 *
 * See https://symfony.com/doc/current/console.html
 *
 * We use the default services.yaml configuration, so command classes are registered as services.
 * See https://symfony.com/doc/current/console/commands_as_services.html
 *
 * @author Manolo Salsas <manolez@gmail.com>
 */
class CreateBundleCommand extends Command
{
    const SEPARATOR = '/';
    const BUNDLE_ROOT = self::SEPARATOR . 'lib';

    // to make your command lazily loaded, configure the $defaultName static property,
    // so it will be instantiated only when the command is actually called.
    protected static $defaultName = 'skeleton-bundle:create';

    /**
     * @var SymfonyStyle
     */
    private $io;

    private $entityManager;
    private $passwordEncoder;
    private $validator;
    private $projectDir;

    public function __construct(KernelInterface $kernel, EntityManagerInterface $em, UserPasswordEncoderInterface $encoder, Validator $validator, UserRepository $users)
    {
        parent::__construct();

        $this->projectDir = $kernel->getProjectDir();
        $this->entityManager = $em;
        $this->passwordEncoder = $encoder;
        $this->validator = $validator;
        $this->users = $users;
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this
            ->setDescription('Creates the bundle skeleton in lib directory')
            ->setHelp($this->getCommandHelp())
            // commands can optionally define arguments and/or options (mandatory and optional)
            // see https://symfony.com/doc/current/components/console/console_arguments.html
            ->addArgument('domain-name', InputArgument::OPTIONAL, 'The domain name of the new bundle. E.g. "Acme"')
            ->addArgument('bundle-name', InputArgument::OPTIONAL, 'The bundle name. E.g. "FooBundle"')
        ;
    }

    /**
     * This optional method is the first one executed for a command after configure()
     * and is useful to initialize properties based on the input arguments and options.
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {
        // SymfonyStyle is an optional feature that Symfony provides so you can
        // apply a consistent look to the commands of your application.
        // See https://symfony.com/doc/current/console/style.html
        $this->io = new SymfonyStyle($input, $output);
    }

    /**
     * This method is executed after initialize() and before execute(). Its purpose
     * is to check if some of the options/arguments are missing and interactively
     * ask the user for those values.
     *
     * This method is completely optional. If you are developing an internal console
     * command, you probably should not implement this method because it requires
     * quite a lot of work. However, if the command is meant to be used by external
     * users, this method is a nice way to fall back and prevent errors.
     */
    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if (null !== $input->getArgument('domain-name') && null !== $input->getArgument('bundle-name')) {
            return;
        }

        $this->io->title('Create Bundle Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console skeleton-bundle:create domain-name bundle-name',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for the domain-name if it's not defined
        $domainName = $input->getArgument('domain-name');
        if (null !== $domainName) {
            $this->io->text(' > <info>Domain name</info>: '.$domainName);
        } else {
            $domainName = $this->io->ask('Domain name', null, [$this->validator, 'validateDomainName']);
            $input->setArgument('domain-name', $domainName);
        }

        // Ask for the bundle-name if it's not defined
        $bundleName = $input->getArgument('bundle-name');
        if (null !== $bundleName) {
            $this->io->text(' > <info>Bundle Name</info>: '.u('*')->repeat(u($bundleName)->length()));
        } else {
            $bundleName = $this->io->ask('Bundle Name', null, [$this->validator, 'validateBundleName']);
            $input->setArgument('bundle-name', $bundleName);
        }
    }

    /**
     * This method is executed after interact() and initialize(). It usually
     * contains the logic to execute to complete this command task.
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $stopwatch = new Stopwatch();
        $stopwatch->start('create-bundle-command');

        $domainName = $input->getArgument('domain-name');
        $bundleName = $input->getArgument('bundle-name');

        $domainName = $this->sanitizeDomainName($domainName);
        $bundleName = $this->sanitizeBundleName($bundleName);

        // make sure to validate the bundle data is correct
        $this->validateBundleData($domainName, $bundleName);

        $this->createBundleSkeletonDir($domainName, $bundleName);
        //TODO: Create the bundle skeleton files
        $this->createBundleMainFile($domainName, $bundleName);
        $this->createBundleControllerFile($domainName, $bundleName);
        $this->createBundleDependencyInjectionDir($domainName, $bundleName);
        $this->createBundleExtensionFile($domainName, $bundleName);
        $this->createBundleConfigurationFile($domainName, $bundleName);
        $this->createBundleEntityDir($domainName, $bundleName);
        $this->createBundleResourcesDir($domainName, $bundleName);
        $this->createBundleConfigDir($domainName, $bundleName);
        $this->createBundleDoctrineDir($domainName, $bundleName);
        $this->createBundleRoutingDir($domainName, $bundleName);
        $this->createBundleServicesFile($domainName, $bundleName);
        $this->createBundleDocDir($domainName, $bundleName);
        $this->createBundleIndexDocFile($domainName, $bundleName);
        $this->createBundlePublicDir($domainName, $bundleName);
        $this->createBundleCssDir($domainName, $bundleName);
        $this->createBundleJsDir($domainName, $bundleName);
        $this->createBundleTranslationsDir($domainName, $bundleName);
        $this->createBundleMessagesEnFile($domainName, $bundleName);
        $this->createBundleMessagesEsFile($domainName, $bundleName);
        $this->createBundleViewsDir($domainName, $bundleName);
        $this->createBundleWidgetFile($domainName, $bundleName);
        $this->createBundleServiceDir($domainName, $bundleName);
        $this->createBundleTestDir($domainName, $bundleName);
        $this->createBundleComposerFile($domainName, $bundleName);
        $this->createBundleReadmeFile($domainName, $bundleName);
        $this->createBundleLicenseFile($domainName, $bundleName);
        $this->createBundleGitIgnoreFile($domainName, $bundleName);
        $this->createBundleTravisFile($domainName, $bundleName);
        $this->createBundlePhpUnitFile($domainName, $bundleName);

        $this->io->success(sprintf('The bundle skeleton was successfully created at: /lib/%s/%s', $domainName, $bundleName));

        $event = $stopwatch->stop('create-bundle-command');
        if ($output->isVerbose()) {
//            $this->io->comment(sprintf('New user database id: %d / Elapsed time: %.2f ms / Consumed memory: %.2f MB', $user->getId(), $event->getDuration(), $event->getMemory() / (1024 ** 2)));
        }

        return 0;
    }

    private function createBundleSkeletonDir($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleMainFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = $this->getBundleFullName($domainName, $bundleName) . '.php';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getMainFilePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContents($domainName, $bundleName, $path);

        return true;
    }

    private function createBundleControllerFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName) . '/Controller';
        $filename = $this->getBundleName($domainName, $bundleName) . 'Controller.php';
        $path = $this->getPath($dir, $filename);

        $this->createDir($dir);
        $oldPath = CreateBundleUtils::getControllerPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function createBundleDependencyInjectionDir($domainName, $bundleName)
    {
        $dir = $this->getDependencyInjectionDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleExtensionFile($domainName, $bundleName)
    {
        $dir = $this->getDependencyInjectionDir($domainName, $bundleName);
        $filename = $this->getBundleName($domainName, $bundleName) . 'Extension.php';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getExtensionPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");

        return $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function createBundleConfigurationFile($domainName, $bundleName)
    {
        $dir = $this->getDependencyInjectionDir($domainName, $bundleName);
        $filename = 'Configuration.php';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getConfigurationPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");

        return $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function createBundleEntityDir($domainName, $bundleName)
    {
        $dir = $this->getEntityDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleResourcesDir($domainName, $bundleName)
    {
        $dir = $this->getResourcesDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleConfigDir($domainName, $bundleName)
    {
        $dir = $this->getConfigDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleDoctrineDir($domainName, $bundleName)
    {
        $dir = $this->getDoctrineDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleRoutingDir($domainName, $bundleName)
    {
        $dir = $this->getRoutingDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleServicesFile($domainName, $bundleName)
    {
        $dir = $this->getConfigDir($domainName, $bundleName);
        $filename = 'services.xml';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getServicesPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");

        return $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function createBundleDocDir($domainName, $bundleName)
    {
        $dir = $this->getDocDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleIndexDocFile($domainName, $bundleName)
    {
        $dir = $this->getDocDir($domainName, $bundleName);
        $filename = 'index.rst';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getIndexDocPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function createBundlePublicDir($domainName, $bundleName)
    {
        $dir = $this->getPublicDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleCssDir($domainName, $bundleName)
    {
        $dir = $this->getCssDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleJsDir($domainName, $bundleName)
    {
        $dir = $this->getJsDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleTranslationsDir($domainName, $bundleName)
    {
        $dir = $this->getTranslationsDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleMessagesEnFile($domainName, $bundleName)
    {
        $dir = $this->getTranslationsDir($domainName, $bundleName);
        $filename = 'messages.en.yml';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getMessagesEnPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");
    }

    private function createBundleMessagesEsFile($domainName, $bundleName)
    {
        $dir = $this->getTranslationsDir($domainName, $bundleName);
        $filename = 'messages.es.yml';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getMessagesEsPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");
    }

    private function createBundleViewsDir($domainName, $bundleName)
    {
        $dir = $this->getViewsDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleWidgetFile($domainName, $bundleName)
    {
        $dir = $this->getViewsDir($domainName, $bundleName);
        $filename = $this->getBundleNameWithUnderscores($domainName, $bundleName) . '_widget.html.twig';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getWidgetPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, ["_", "-"]);
    }

    private function createBundleServiceDir($domainName, $bundleName)
    {
        $dir = $this->getServiceDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleTestDir($domainName, $bundleName)
    {
        $dir = $this->getTestsDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleComposerFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'composer.json';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getComposerPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContents($domainName, $bundleName, $path);

        return $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, ["/", "_"]);
    }

    private function createBundleReadmeFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'README.md';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getReadmePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContents($domainName, $bundleName, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, ["/", "_"]);

        $str = file_get_contents($path);
        $replace = str_replace('acme', $domainName, $str);
        file_put_contents($path, $replace);
    }

    private function createBundleLicenseFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'LICENSE';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getLicensePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return true;
    }

    private function createBundleGitIgnoreFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = '.gitignore';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getGitIgnorePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return true;
    }

    private function createBundleTravisFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = '.travis';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getTravisPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return true;
    }

    private function createBundlePhpUnitFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'phpunit.xml.dist';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getPhpUnitPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContents($domainName, $bundleName, $path);
    }

    private function getPath($dir, $filename)
    {
        return $dir . self::SEPARATOR . $filename;
    }

    private function createDir($dir)
    {
        if (!mkdir($dir, 0755, true)) {
            die('Error creating directory ' . $dir);
        }

        return true;
    }

    private function copyFile($oldPath, $path)
    {
        if (!copy($oldPath, $path)) {
            die('Error renaming file ' . $oldPath);
        }
    }

    private function replaceFileContents($domainName, $bundleName, $path)
    {
        $str = file_get_contents($path);
        $replace = str_replace('AcmeFoo', $this->getBundleName($domainName, $bundleName), $str);
        $replace = str_replace('Acme', $this->getDomainOrBundleName($domainName), $replace);
        $replace = str_replace('FooBundle', $this->getDomainOrBundleName($bundleName), $replace);
        file_put_contents($path, $replace);

        return true;
    }

    private function replaceFileContentsWithLowercase($domainName, $bundleName, $path, $separators)
    {
        if (!is_array($separators)) {
            $separators = [$separators];
        }

        foreach ($separators as $separator) {
            $str = file_get_contents($path);

            $replace =  $this->getNameReplacementLowercase($domainName, $bundleName, $str, $separator);

            file_put_contents($path, $replace);
        }

        return true;
    }

    private function getNameReplacementLowercase($domainName, $bundleName, $content, $separator)
    {
        $replacement =  substr(preg_replace_callback('/([A-Z])/', function($word) use ($separator) {
            return $separator . strtolower($word[1]);
        }, $this->getBundleName($domainName, $bundleName)), 1);

        return str_replace("acme{$separator}foo", $replacement, $content);
    }

    private function getDependencyInjectionDir($domainName, $bundleName)
    {
        return $this->getBundleSkeletonDir($domainName, $bundleName) . '/DependencyInjection';
    }

    private function getEntityDir($domainName, $bundleName)
    {
        return $this->getBundleSkeletonDir($domainName, $bundleName) . '/Entity';
    }

    private function getResourcesDir($domainName, $bundleName)
    {
        return $this->getBundleSkeletonDir($domainName, $bundleName) . '/Resources';
    }

    private function getConfigDir($domainName, $bundleName)
    {
        return $this->getResourcesDir($domainName, $bundleName) . '/config';
    }

    private function getDoctrineDir($domainName, $bundleName)
    {
        return $this->getConfigDir($domainName, $bundleName) . '/doctrine';
    }

    private function getRoutingDir($domainName, $bundleName)
    {
        return $this->getConfigDir($domainName, $bundleName) . '/routing';
    }

    private function getDocDir($domainName, $bundleName)
    {
        return $this->getResourcesDir($domainName, $bundleName) . '/doc';
    }

    private function getPublicDir($domainName, $bundleName)
    {
        return $this->getResourcesDir($domainName, $bundleName) . '/public';
    }

    private function getCssDir($domainName, $bundleName)
    {
        return $this->getPublicDir($domainName, $bundleName) . '/css';
    }

    private function getJsDir($domainName, $bundleName)
    {
        return $this->getPublicDir($domainName, $bundleName) . '/js';
    }

    private function getTranslationsDir($domainName, $bundleName)
    {
        return $this->getResourcesDir($domainName, $bundleName) . '/translations';
    }

    private function getViewsDir($domainName, $bundleName)
    {
        return $this->getResourcesDir($domainName, $bundleName) . '/views';
    }

    private function getServiceDir($domainName, $bundleName)
    {
        return $this->getBundleSkeletonDir($domainName, $bundleName) . '/Service';
    }

    private function getTestsDir($domainName, $bundleName)
    {
        return $this->getBundleSkeletonDir($domainName, $bundleName) . '/Tests';
    }

    private function getBundleFullName($domainName, $bundleName)
    {
        return preg_replace_callback('/-([a-z])/', function($word) {
            return strtoupper($word[1]);
        }, ucfirst($domainName) . ucfirst($bundleName));
    }

    private function getDomainOrBundleName($domainOrBundleName)
    {
        return preg_replace_callback('/-([a-z])/', function($word) {
            return strtoupper($word[1]);
        }, ucfirst($domainOrBundleName));
    }

    private function getBundleName($domainName, $bundleName)
    {
        $bundleFullName = $this->getBundleFullName($domainName, $bundleName);

        return substr($bundleFullName, 0, strlen($bundleFullName) - 6);
    }

    private function getBundleNameWithUnderscores($domainName, $bundleName)
    {
        return str_replace("-", "_", $domainName . "_" . substr($bundleName, 0, strlen($bundleName) - 7));
    }

    private function getBundleSkeletonDir($domainName, $bundleName)
    {
        return $this->projectDir . self::BUNDLE_ROOT . self::SEPARATOR . $domainName . self::SEPARATOR . $bundleName;
    }

    private function sanitizeDomainName($domainName): string
    {
        $domainName = preg_replace('/([A-Z])/', '-$1', $domainName);
        $domainName = str_replace('--', '-', $domainName);
        if (strpos($domainName, '-') === 0) {
            $domainName = substr($domainName, 1);
        }

        return strtolower($domainName);
    }

    private function sanitizeBundleName($bundleName): string
    {
        $bundleName = preg_replace('/([A-Z])/', '-$1', $bundleName);
        $bundleName = str_replace('--', '-', $bundleName);
        if (strpos($bundleName, '-') === 0) {
            $bundleName = substr($bundleName, 1);
        }

        $bundleName = strtolower($bundleName);

        if (!strpos($bundleName, '-bundle')) {
            $bundleName = str_replace(' bundle', 'bundle', $bundleName);
            $bundleName = str_replace('bundle', '-bundle', $bundleName);
        }

        if (!strpos($bundleName, 'bundle')) {
            $bundleName .= '-bundle';
        }

        return $bundleName;
    }

    private function validateBundleData($domainName, $bundleName): void
    {
//        // first check if a user with the same username already exists.
//        $existingUser = $this->users->findOneBy(['username' => $username]);
//
//        if (null !== $existingUser) {
//            throw new RuntimeException(sprintf('There is already a user registered with the "%s" username.', $username));
//        }
//
//        // validate password and email if is not this input means interactive.
//        $this->validator->validatePassword($plainPassword);
//        $this->validator->validateEmail($email);
//        $this->validator->validateFullName($fullName);
//
//        // check if a user with the same email already exists.
//        $existingEmail = $this->users->findOneBy(['email' => $email]);
//
//        if (null !== $existingEmail) {
//            throw new RuntimeException(sprintf('There is already a user registered with the "%s" email.', $email));
//        }
    }

    /**
     * The command help is usually included in the configure() method, but when
     * it's too long, it's better to define a separate method to maintain the
     * code readability.
     */
    private function getCommandHelp(): string
    {
        return <<<'HELP'
The <info>%command.name%</info> command creates a bundle skeleton in /lib:

  <info>php %command.full_name%</info> <comment>domain-name bundle-name</comment>

By default the command creates AcmeFooBundle in /lib/acme/foo-bundle directory.

If you omit any of the three required arguments, the command will ask you to
provide the missing values:

  # command will ask you for the domain name
  <info>php %command.full_name%</info> <comment>bundle-name</comment>

  # command will ask you for all arguments
  <info>php %command.full_name%</info>

HELP;
    }
}
