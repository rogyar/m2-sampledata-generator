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
 *
 * Will generate N products
 *
 *
 * If there's only 1 product - simple product
 * If there are 2 products - a configurable with one simple product
 * If there are 3 products will generate 1 gonfigurable with simple connected and one separate simple.
 * The count of simple connected products 1..4
 *
 * So 30% will be configurable and 70% simple
 *
 * If there are no categories on the site - put all products into default category. Add also optional
 * ability to put all products to the default category
 *
 * If there are some categories on the site, put products randomly to different categories (one product per category)
 * and to the default category as well.
 *
 * Need to create a new attribute programmatically that is used for configurable product creation.
 * Need to provide an ability to remove generated products and the attribute as well
 *
 */

// TODO: refactor in order to create a parent abstract class

class GenerateProductsCommand extends Command
{
    const JOB_NAME = 'samplegen:generate:products';
    const INPUT_KEY_COUNT = 'count';
    const INPUT_KEY_REMOVE = 'remove';

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
                self::INPUT_KEY_REMOVE,
                null,
                InputOption::VALUE_OPTIONAL,
                'Remove all previously generated products',
                false
            )
        ];

        $this->setName(self::JOB_NAME)
            ->setDescription('Runs sample products generation')
            ->setDefinition($options);
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $productsCount = $input->getOption(self::INPUT_KEY_COUNT);
        $removeGeneratedItems = $input->getOption(self::INPUT_KEY_REMOVE);
        $messages = $this->validate($productsCount, $removeGeneratedItems);

        if (!empty($messages)) {
            $output->writeln(implode(PHP_EOL, $messages));
            return;
        }

        $omParams = $_SERVER;
        $omParams[StoreManager::PARAM_RUN_CODE] = 'admin';
        $omParams[Store::CUSTOM_ENTRY_POINT_PARAM] = true;
        $objectManager = $this->objectManagerFactory->create($omParams);

        $params[self::INPUT_KEY_COUNT] = $productsCount;
        $params[self::INPUT_KEY_REMOVE] = $removeGeneratedItems;


        $productsCreator = $objectManager->create('Atwix\Samplegen\Helper\ProductsCreator',
            ['context' => $this->context, 'objectManager' => $objectManager, 'parameters' => $params]);
        try {
            $productsCreator->launch();
            $output->writeln('<info>' . 'All operations completed successfully' . '</info>');
        } catch (\Exception $e) {
            $output->writeln('<error>' . "{$e->getMessage()}" . '</error>');
        }
    }

    /**
     * Validates categories count and returns error messages
     *
     * @param $count
     * @return array
     */
    protected function validate($count, $removeAll)
    {
        // TODO: check count for negative values
        $messages = [];
        if (false == $count && false == $removeAll) {
            $messages[] = '<error>No products count specified</error>';
        }

        return $messages;
    }
}
