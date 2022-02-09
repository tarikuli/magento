<?php

namespace Imgix\Magento\Ui\Component\Listing\Column\ConfigurableProducts;

use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class Thumbnail extends \Magento\Ui\Component\Listing\Columns\Column
{
    public const NAME = 'thumbnail';

    public const ALT_FIELD = 'name';

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @param ContextInterface $context
     * @param UiComponentFactory $uiComponentFactory
     * @param \Magento\Catalog\Helper\Image $imageHelper
     * @param \Magento\Framework\UrlInterface $urlBuilder
     * @param array $components
     * @param array $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        \Magento\Catalog\Helper\Image $imageHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
        $this->imageHelper = $imageHelper;
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Prepare Data Source
     *
     * @param array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            $fieldName = $this->getData('name');
            foreach ($dataSource['data']['items'] as & $item) {
                $product = new \Magento\Framework\DataObject($item);
                $imageUrl = $product->getThumbnail();
                if (strpos($imageUrl, 'imgix') !== false) {
                    $item[$fieldName . '_src'] = $imageUrl;
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $product->getName();
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                    );
                    $item[$fieldName . '_orig_src'] = $imageUrl;

                } else {
                    $imageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail');
                    $item[$fieldName . '_src'] = $imageHelper->getUrl();
                    $item[$fieldName . '_alt'] = $this->getAlt($item) ?: $imageHelper->getLabel();
                    $item[$fieldName . '_link'] = $this->urlBuilder->getUrl(
                        'catalog/product/edit',
                        ['id' => $product->getEntityId(), 'store' => $this->context->getRequestParam('store')]
                    );
                    $origImageHelper = $this->imageHelper->init($product, 'product_listing_thumbnail_preview');
                    $item[$fieldName . '_orig_src'] = $origImageHelper->getUrl();
                }
            }
        }
        return $dataSource;
    }

    /**
     * Get Alt
     *
     * @param array $row
     *
     * @return null|string
     */
    protected function getAlt($row)
    {
        $altField = $this->getData('config/altField') ?: self::ALT_FIELD;
        return $row[$altField] ?? null;
    }
}