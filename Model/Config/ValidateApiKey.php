<?php
/**
 * Validate that admin API settings are correct.
 * on Gigya admin page save, take the submitted API, DC, App key, and app secret and create Gigya REST request
 */

namespace Gigya\GigyaIM\Model\Config;

/**
 * Customer sharing config model
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class ValidateApiKey extends \Magento\Framework\App\Config\Value
{
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer
     */
    protected $_customerResource;

    /** @var  \Magento\Store\Model\StoreManagerInterface */
    protected $_storeManager;

    /** @var  \Gigya\GigyaIM\Helper\GigyaMageHelper */
    protected $gigyaMageHelper;

	/**
	 * Constructor
	 *
	 * @param \Magento\Framework\Model\Context $context
	 * @param \Magento\Framework\Registry $registry
	 * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
	 * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
	 * @param \Magento\Store\Model\StoreManagerInterface $storeManager
	 * @param \Magento\Customer\Model\ResourceModel\Customer $customerResource
	 * @param \Gigya\GigyaIM\Helper\GigyaMageHelper $gigyaMageHelper
	 * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
	 * @param \Magento\Framework\Data\Collection\AbstractDb $resourceCollection
	 * @param array $data
	 */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\ResourceModel\Customer $customerResource,
        \Gigya\GigyaIM\Helper\GigyaMageHelper $gigyaMageHelper,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        $this->_customerResource = $customerResource;
        $this->gigyaMageHelper = $gigyaMageHelper;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

	/**
	 * Check for email duplicates before saving customers sharing options
	 *
	 * @return $this
	 *
	 * @throws \Magento\Framework\Exception\LocalizedException
	 * @throws \Gigya\GigyaIM\Helper\CmsStarterKit\sdk\GSException
	 */
    public function beforeSave()
    {
        if (isset($this->_data['fieldset_data']) == false) {
            return $this;
        }

        /* Get submitted settings */
        $api_key = $this->_data['fieldset_data']['api_key'];
        $domain = $this->_data['fieldset_data']['domain'];
        $app_key = $this->_data['fieldset_data']['app_key'];
        $key_file_location = $this->_data['fieldset_data']['key_file_location'];
        // *** cancel key save type option in admin

        /* Create object manager and reset the settings to newly submitted */
        $this->gigyaMageHelper->setApiKey($api_key);
        $this->gigyaMageHelper->setApiDomain($domain);
        $this->gigyaMageHelper->setAppKey($app_key);
        if (!$this->gigyaMageHelper->setKeyFileLocation($key_file_location))
		{
			$this->gigyaMageHelper->gigyaLog("Error while trying to save gigya settings. Invalid or incorrect key file path provided. Path given: " . $key_file_location);
			throw new \Magento\Framework\Exception\LocalizedException(
				__(
					"Could not save settings. Invalid or incorrect key file path provided."
				)
			);
		}
        $this->gigyaMageHelper->setAppSecret();
        $gigyaApiHelper = $this->gigyaMageHelper->getGigyaApiHelper();

        /* Make the call to Gigya REST API */
        $param = array("filter" => 'full');
        try {
            $gigyaApiHelper->sendApiCall("accounts.getSchema", $param);
        } catch (\Gigya\GigyaIM\Helper\CmsStarterKit\sdk\GSApiException $e) {
            $this->gigyaMageHelper->gigyaLog(
                "Error while trying to save gigya settings. " . $e->getErrorCode() .
                " " .$e->getMessage() . " " . $e->getCallId()
            );
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    "Could not save settings. Gigya API test failed with error message: {$e->getMessage()} ."
                )
            );
        }

        return $this;
    }
}
