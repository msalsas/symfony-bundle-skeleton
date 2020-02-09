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
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Stopwatch\Stopwatch;
use Symfony\Component\Validator\Constraints\Date;
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
            ->addArgument('bundle-description', InputArgument::OPTIONAL, 'The bundle description. E.g. "This bundle adds support for ..."')
            ->addArgument('bundle-keywords', InputArgument::OPTIONAL, 'The bundle keywords. E.g. "foo, bar"')
            ->addArgument('your-name', InputArgument::OPTIONAL, 'Your name. E.g. "John Doe"')
            ->addArgument('your-email', InputArgument::OPTIONAL, 'Your email. E.g. "johndoe@email.com"')
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
        if (
            null !== $input->getArgument('domain-name') &&
            null !== $input->getArgument('bundle-name') &&
            null !== $input->getArgument('bundle-description') &&
            null !== $input->getArgument('bundle-keywords') &&
            null !== $input->getArgument('your-name')  &&
            null !== $input->getArgument('your-email')) {

            $this->validator->validateDomainName($input->getArgument('domain-name'));
            $this->validator->validateBundleName($input->getArgument('bundle-name'));
            $this->validator->validateBundleDescription($input->getArgument('bundle-description'));
            $this->validator->validateBundleKeywords($input->getArgument('bundle-keywords'));
            $this->validator->validateFullName($input->getArgument('your-name'));
            $this->validator->validateEmail($input->getArgument('your-email'));
            return;
        }

        $this->io->title('Create Bundle Command Interactive Wizard');
        $this->io->text([
            'If you prefer to not use this interactive wizard, provide the',
            'arguments required by this command as follows:',
            '',
            ' $ php bin/console skeleton-bundle:create domain-name bundle-name bundle-description bundle-keywords your-name your-email',
            '',
            'Now we\'ll ask you for the value of all the missing command arguments.',
        ]);

        // Ask for arguments if they are not defined
        $this->askForArgument($input, 'domain-name', 'The domain name', 'validateDomainName');
        $this->askForArgument($input, 'bundle-name', 'The bundle name', 'validateBundleName');
        $this->askForArgument($input, 'bundle-description', 'The bundle description', 'validateBundleDescription');
        $this->askForArgument($input, 'bundle-keywords', 'The bundle keywords. Caution! Type it like this ["foo", "bar"]', 'validateBundleKeywords');
        $this->askForArgument($input, 'your-name', 'Your Full Name', 'validateFullName');
        $this->askForArgument($input, 'your-email', 'Your Email', 'validateEmail');

    }

    private function askForArgument(InputInterface $input, $key, $text, $validationMethod)
    {
        // Ask for argument if it's not defined
        $argument = $input->getArgument($key);
        if (null !== $argument) {
            $this->io->text(" > <info>{$text}</info>: ".u('*')->repeat(u($argument)->length()));
        } else {
            $argument = $this->io->ask($text, null, [$this->validator, $validationMethod]);
            $input->setArgument($key, $argument);
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
        $bundleDescription = $input->getArgument('bundle-description');
        $bundleKeywords = $input->getArgument('bundle-keywords');
        $yourName = $input->getArgument('your-name');
        $yourEmail = $input->getArgument('your-email');

        $domainName = $this->sanitizeDomainName($domainName);
        $bundleName = $this->sanitizeBundleName($bundleName);

        // make sure to validate the bundle data is correct
        $this->validateBundleData($domainName, $bundleName);

        $this->createBundleSkeletonDir($domainName, $bundleName);
        $this->createBundleMainFile($domainName, $bundleName);
        $this->createBundleControllerFile($domainName, $bundleName);
        $this->createBundleDependencyInjectionDir($domainName, $bundleName);
        $this->createBundleExtensionFile($domainName, $bundleName);
        $this->createBundleConfigurationFile($domainName, $bundleName);
        $this->createBundleEntityDir($domainName, $bundleName);
        $this->createBundleEntityFile($domainName, $bundleName);
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
        $this->createBundleServiceFile($domainName, $bundleName);
        $this->createBundleTestDir($domainName, $bundleName);
        $this->createBundleMockDir($domainName, $bundleName);
        $this->createBundleUserMockFile($domainName, $bundleName);
        $this->createBundleServiceTestFile($domainName, $bundleName);
        $this->createBundleTestsBootstrapFile($domainName, $bundleName);
        $this->createBundleComposerFile($domainName, $bundleName, $yourName, $yourEmail, $bundleDescription, $bundleKeywords);
        $this->createBundleReadmeFile($domainName, $bundleName);
        $this->createBundleLicenseFile($domainName, $bundleName, $yourName);
        $this->createBundleGitIgnoreFile($domainName, $bundleName);
        $this->createBundleTravisFile($domainName, $bundleName);
        $this->createBundlePhpUnitFile($domainName, $bundleName);
        $this->updateComposerMainFile($domainName, $bundleName);
        $this->updateBundlesMainFile($domainName, $bundleName);
        $this->updatePackageMainFile($domainName, $bundleName);

        $this->io->success(sprintf('The bundle skeleton was successfully created at: /lib/%s/%s', $domainName, $bundleName));

        $process = new Process(['composer install']);
        $process->run();
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        echo $process->getOutput();

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

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);

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

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
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

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function createBundleConfigurationFile($domainName, $bundleName)
    {
        $dir = $this->getDependencyInjectionDir($domainName, $bundleName);
        $filename = 'Configuration.php';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getConfigurationPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, "_");

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function createBundleEntityDir($domainName, $bundleName)
    {
        $dir = $this->getEntityDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleEntityFile($domainName, $bundleName)
    {
        $dir = $this->getEntityDir($domainName, $bundleName);
        $filename = 'Car.php';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getEntityPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
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

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
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

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
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

    private function createBundleServiceFile($domainName, $bundleName)
    {
        $dir = $this->getServiceDir($domainName, $bundleName);
        $filename = CreateBundleUtils::SERVICE_FILE;
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getServicePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        return $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function createBundleTestsBootstrapFile($domainName, $bundleName)
    {
        $dir = $this->getTestsDir($domainName, $bundleName);
        $filename = CreateBundleUtils::BOOTSTRAP_FILE;
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getBootstrapPath($this->projectDir);
        $this->copyFile($oldPath, $path);
    }

    private function createBundleTestDir($domainName, $bundleName)
    {
        $dir = $this->getTestsDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleMockDir($domainName, $bundleName)
    {
        $dir = $this->getMocksDir($domainName, $bundleName);

        return $this->createDir($dir);
    }

    private function createBundleUserMockFile($domainName, $bundleName)
    {
        $dir = $this->getMocksDir($domainName, $bundleName);
        $filename = CreateBundleUtils::USER_MOCK_FILE;
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getUserMockPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function createBundleServiceTestFile($domainName, $bundleName)
    {
        $dir = $this->getTestsDir($domainName, $bundleName);
        $filename = CreateBundleUtils::SERVICE_TEST_FILE;
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getServiceTestPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function createBundleComposerFile($domainName, $bundleName, $yourName, $yourEmail, $bundleDescription, $bundleKeywords)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'composer.json';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getComposerPath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
        $this->replaceFileContents('/acme/', '/' . $domainName . '/', $path);
        $this->replaceFileContents('Your name', $yourName, $path);
        $this->replaceFileContents('Your email', $yourEmail, $path);
        $this->replaceFileContents('Symfony bundle for ...', $bundleDescription, $path);
        $this->replaceFileContents('["foo", "bar"]', $bundleKeywords, $path);

        return $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, ["/", "_"]);
    }

    private function createBundleReadmeFile($domainName, $bundleName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'README.md';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getReadmePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, ["/", "_"]);

        $this->replaceFileContents('acme', $domainName, $path);
    }

    private function createBundleLicenseFile($domainName, $bundleName, $yourName)
    {
        $dir = $this->getBundleSkeletonDir($domainName, $bundleName);
        $filename = 'LICENSE';
        $path = $this->getPath($dir, $filename);

        $oldPath = CreateBundleUtils::getLicensePath($this->projectDir);
        $this->copyFile($oldPath, $path);

        $this->replaceFileContents('Current year', date('Y'), $path);
        $this->replaceFileContents('Your name', $yourName, $path);

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

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);
    }

    private function updateComposerMainFile($domainName, $bundleName)
    {
        $path = CreateBundleUtils::getComposerMainFile($this->projectDir);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $path, '/');
    }

    private function updateBundlesMainFile($domainName, $bundleName)
    {
        $path = CreateBundleUtils::getBundlesMainFile($this->projectDir);

        $this->replaceFileContentsBundleFullName($domainName, $bundleName, $path);

        $str = file_get_contents($path);
        $replace = str_replace('#', '', $str);
        file_put_contents($path, $replace);
    }

    private function updatePackageMainFile($domainName, $bundleName)
    {
        $oldPath = CreateBundleUtils::getPackageMainFile($this->projectDir);

        $this->replaceFileContentsWithLowercase($domainName, $bundleName, $oldPath, ["/", "_"]);

        $dir = substr($oldPath, 0, -14);
        $filename = $this->getBundleNameWithUnderscores($domainName, $bundleName) . '.yaml';
        $path = $this->getPath($dir, $filename);

        $this->moveFile($oldPath, $path);
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

    private function moveFile($oldPath, $path)
    {
        if (!rename($oldPath, $path)) {
            die('Error renaming file ' . $oldPath);
        }
    }

    private function replaceFileContentsBundleFullName($domainName, $bundleName, $path)
    {
        $str = file_get_contents($path);
        $replace = str_replace('AcmeFoo', $this->getBundleName($domainName, $bundleName), $str);
        $replace = str_replace('Acme', $this->getDomainOrBundleName($domainName), $replace);
        $replace = str_replace('FooBundle', $this->getDomainOrBundleName($bundleName), $replace);
        file_put_contents($path, $replace);

        return true;
    }

    private function replaceFileContents($search, $replacement, $path)
    {
        $str = file_get_contents($path);

        $replace = str_replace($search, $replacement, $str);

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

            $replace =  $this->getBundleFullNameReplacementLowercase($domainName, $bundleName, $str, $separator);

            file_put_contents($path, $replace);
        }

        return true;
    }

    private function getBundleFullNameReplacementLowercase($domainName, $bundleName, $content, $separator)
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

    private function getMocksDir($domainName, $bundleName)
    {
        return $this->getTestsDir($domainName, $bundleName) . '/Mock';
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
