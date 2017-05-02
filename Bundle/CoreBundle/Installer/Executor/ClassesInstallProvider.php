<?php

namespace CoreShop\Bundle\CoreBundle\Installer\Executor;

use Pimcore\Model\Object;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Output\Output;
use Symfony\Component\HttpKernel\KernelInterface;

final class ClassesInstallProvider
{
    /**
     * @var string
     */
    private $installResourcesDirectory;

    /**
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @param string $installResourcesDirectory
     * @param KernelInterface $kernel
     */
    public function __construct($installResourcesDirectory, KernelInterface $kernel)
    {
        $this->installResourcesDirectory = $installResourcesDirectory;
        $this->kernel = $kernel;
    }

    /**
     * Installs all CoreShop needed Pimcore Classes
     */
    public function installClasses(Output $output) {
        $classes = [
            'CoreShopAddress',
            'CoreShopCategory',
            'CoreShopProduct',
            'CoreShopCartItem',
            'CoreShopCart',
            'CoreShopCustomer',
            'CoreShopCustomerGroup',
            'CoreShopOrderItem',
            'CoreShopOrder',
            'CoreShopOrderInvoice',
            'CoreShopOrderInvoiceItem',
        ];

        $fieldCollections = [
            'CoreShopProposalCartPriceRuleItem'
        ];

        $progress = new ProgressBar($output);
        $progress->setBarCharacter('<info>░</info>');
        $progress->setEmptyBarCharacter(' ');
        $progress->setProgressCharacter('<comment>░</comment>');
        $progress->setFormat(' %current%/%max% [%bar%] %percent:3s%% %message%');

        $progress->start(count($classes) + count($fieldCollections));


        foreach ($fieldCollections as $fieldCollection) {
            $progress->setMessage(sprintf('Installing Fieldcollection %s', $fieldCollection));

            $this->createFieldCollection($this->kernel->locateResource(sprintf('%s/fieldcollection_%s_export.json', $this->installResourcesDirectory, $fieldCollection)), $fieldCollection);

            $progress->advance();
        }

        foreach ($classes as $class) {
            $progress->setMessage(sprintf('Installing Class %s', $class));

            $this->createClass($this->kernel->locateResource(sprintf('%s/class_%s_export.json', $this->installResourcesDirectory, $class)), $class, true);

            $progress->advance();
        }
    }

    /**
     * @param $jsonFile
     * @param $className
     * @param bool $updateClass
     * @return Object\ClassDefinition
     */
    protected function createClass($jsonFile, $className, $updateClass = false)
    {
        $class = Object\ClassDefinition::getByName($className);

        if (!$class || $updateClass) {
            $json = file_get_contents($jsonFile);

            if (!$class) {
                $class = Object\ClassDefinition::create();
            }

            $class->setName($className);
            $class->setUserOwner(0);

            Object\ClassDefinition\Service::importClassDefinitionFromJson($class, $json, true);

            /**
             * Fixes Object Brick Stuff
             */
            $list = new Object\Objectbrick\Definition\Listing();
            $list = $list->load();

            if (!empty($list)) {
                foreach ($list as $brickDefinition) {
                    $clsDefs = $brickDefinition->getClassDefinitions();
                    if (!empty($clsDefs)) {
                        foreach ($clsDefs as $cd) {
                            if ($cd['classname'] == $class->getId()) {
                                $brickDefinition->save();
                            }
                        }
                    }
                }
            }

            return $class;
        }

        return $class;
    }

    /**
     * @param $name
     * @param null $jsonFile
     * @return mixed|null|Object\Fieldcollection\Definition
     */
    protected function createFieldCollection($jsonFile, $name)
    {
        try {
            $fieldCollection = Object\Fieldcollection\Definition::getByKey($name);
        } catch (\Exception $e) {
            $fieldCollection = new Object\Fieldcollection\Definition();
            $fieldCollection->setKey($name);
        }

        $json = file_get_contents($jsonFile);

        Object\ClassDefinition\Service::importFieldCollectionFromJson($fieldCollection, $json, true);

        return $fieldCollection;
    }
}