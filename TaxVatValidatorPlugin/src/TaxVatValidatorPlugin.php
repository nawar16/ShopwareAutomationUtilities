<?php declare(strict_types=1);

namespace TaxVatValidatorPlugin;

use Shopware\Core\Framework\Plugin;
use Shopware\Core\Framework\Plugin\Context\InstallContext;
use Shopware\Core\Framework\Plugin\Context\UninstallContext;
use Shopware\Core\Framework\CustomField\CustomFieldTypes;
use Shopware\Core\System\CustomField\CustomFieldService;

class TaxVatValidatorPlugin extends Plugin
{
    private const CUSTOM_FIELD_SET_NAME = 'custom_tax_vat_validator';
    private const CUSTOM_FIELD_NAME = 'tax_vat_validator_status';

    public function install(InstallContext $installContext): void
    {
        $container = $this->getContainer();
        if (!$container) {
            return;
        }
        $customFieldService = $container->get(CustomFieldService::class);
        $customFieldService->upsertCustomFieldSet([
            'name' => self::CUSTOM_FIELD_SET_NAME,
            'config' => [
                'label' => [
                    'en-GB' => 'Tax VAT Validator Tracking',
                    'de-DE' => 'USt-IdNr. Validierung'
                ]
            ],
            'relation' => [
                'entityName' => 'customer'
            ],
            'customFields' => [
                [
                    'name' => self::CUSTOM_FIELD_NAME,
                    'type' => CustomFieldTypes::TEXT,
                    'config' => [
                        'label' => [
                            'en-GB' => 'VAT Status Code',
                            'de-DE' => 'USt-IdNr Statuscode'
                        ],
                        'componentName' => 'sw-field',
                        'customFieldType' => 'text',
                        'customFieldPosition' => 1
                    ]
                ]
            ]
        ], $installContext->getContext());
    }

    public function uninstall(UninstallContext $uninstallContext): void
    {
        if ($uninstallContext->keepUserData()) {
            return;
        }
        $container = $this->getContainer();
        if (!$container) {
            return;
        }
        $customFieldService = $container->get(CustomFieldService::class);
        $customFieldService->deleteCustomFieldSet(self::CUSTOM_FIELD_SET_NAME, $uninstallContext->getContext());
    }
}
