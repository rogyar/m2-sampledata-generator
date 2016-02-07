<?php

namespace Atwix\Samplegen\Console\Command;

use Symfony\Component\Config\Definition\Exception\Exception;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Magento\Framework\App\ObjectManagerFactory;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManager;
use Magento\Framework\App\Helper\Context;

/**
 * Command for executing cron jobs
 */
class GenerateCategoriesCommand extends Command
{
    const JOB_NAME = 'samplegen:generate:categories';
    const INPUT_KEY_COUNT = 'count';
    const INPUT_KEY_DEPTH = 'depth';

    /**
     * Object manager factory
     *
     * @var ObjectManagerFactory
     */
    private $objectManagerFactory;

    protected $context;

    /**
     * Constructor
     *
     * @param ObjectManagerFactory $objectManagerFactory
     * @param Context $context
     */
    public function __construct(ObjectManagerFactory $objectManagerFactory, Context $context)
    {
        $this->objectManagerFactory = $objectManagerFactory;
        $this->context = $context;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $options = [
            new InputOption(
                self::INPUT_KEY_COUNT,
                null,
                InputOption::VALUE_REQUIRED,
                'Generated categories count'
            ),
            new InputOption(
                self::INPUT_KEY_DEPTH,
                null,
                InputOption::VALUE_OPTIONAL,
                'Generated categories depth'
            )
        ];
        $this->setName(self::JOB_NAME)
            ->setDescription('Runs sample categories generation')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        $params[self::INPUT_KEY_COUNT] = $input->getOption(self::INPUT_KEY_COUNT);
        $params[self::INPUT_KEY_DEPTH] = $input->getOption(self::INPUT_KEY_DEPTH);


        $categoriesCreator = $objectManager->create('Atwix\Samplegen\Helper\CategoriesCreator',
            ['context' => $this->context, 'parameters' => $params]);
        try {
            $categoriesCreator->launch();
            $output->writeln('<info>' . 'Categories were successfully generated' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . "{$e->getMessage()}" . '</error>');
        }
    }
}
