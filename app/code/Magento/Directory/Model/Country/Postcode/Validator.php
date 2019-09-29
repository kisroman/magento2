<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Directory\Model\Country\Postcode;

use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\NoSuchEntityException;

class Validator implements ValidatorInterface
{
    /**
     * @var ConfigInterface
     */
    protected $postCodesConfig;

    /**
     * @var CountryInformationAcquirerInterface
     */
    private $countryInformationAcquirer;

    /**
     * @param ConfigInterface $postCodesConfig
     * @param CountryInformationAcquirerInterface|null $countryInformationAcquirer
     */
    public function __construct(
        ConfigInterface $postCodesConfig,
        CountryInformationAcquirerInterface $countryInformationAcquirer = null
    ) {
        $this->postCodesConfig = $postCodesConfig;
        $this->countryInformationAcquirer = $countryInformationAcquirer
            ?: ObjectManager::getInstance()->get(CountryInformationAcquirerInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function validate($postcode, $countryId)
    {
        $postCodes = $this->postCodesConfig->getPostCodes();

        if (isset($postCodes[$countryId]) && is_array($postCodes[$countryId])) {
            $patterns = $postCodes[$countryId];
            foreach ($patterns as $pattern) {
                preg_match('/' . $pattern['pattern'] . '/', $postcode, $matches);
                if (count($matches)) {
                    return true;
                }
            }
            return false;
        } else {
            try {
                $countryInfo = $this->countryInformationAcquirer->getCountryInfo($countryId);
                if (null !== $countryInfo) {
                    return true;
                }
            } catch (NoSuchEntityException $e) {
            }
        }
        throw new \InvalidArgumentException('Provided countryId does not exist.');
    }
}
