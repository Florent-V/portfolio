<?php

declare(strict_types=1);

namespace App\Service\Admin;

use EasyCorp\Bundle\EasyAdminBundle\Context\AdminContext;
use EasyCorp\Bundle\EasyAdminBundle\Contracts\Field\FieldInterface;
use EasyCorp\Bundle\EasyAdminBundle\Field\CodeEditorField;

class ContactMessageLogFieldsConfigurationService extends AbstractFieldsConfigurationService
{
    /**
     * @return FieldInterface[]
     */
    protected function buildIndexFields(): array
    {
        return [
            $this->createdAtField(),
            $this->createTextField('name', 'Nom'),
            $this->createTextField('email', 'Email'),
            $this->createTextField('subject', 'Sujet'),
            $this->createBooleanField('blocked', 'Bloqué'),
            $this->createBooleanField('mailSent', 'Email envoyé'),
            $this->createTextField('ipAddress', 'IP'),
            $this->createTextField('countryCode', 'Pays'),
            $this->createTextField('city', 'Ville'),
            $this->createTextField('isp', 'FAI (ISP)'),
        ];
    }

    /**
     * @return FieldInterface[]
     */
    protected function buildDetailFields(): array
    {
        return [
            $this->createdAtField(),
            $this->createTextField('name', 'Nom'),
            $this->createTextField('email', 'Email'),
            $this->createTextField('subject', 'Sujet'),
            $this->createTextAreaField('message', 'Message'),
            $this->createBooleanField('blocked', 'Bloqué'),
            $this->createTextField('blockReason', 'Raison du blocage'),
            $this->createBooleanField('mailSent', 'Email envoyé'),
            $this->createTextField('ipAddress', 'IP'),
            $this->createTextField('forwardedFor', 'X-Forwarded-For'),
            $this->createTextField('userAgent', 'User-Agent'),
            $this->createTextField('acceptLanguage', 'Langue'),
            $this->createTextField('referer', 'Referer'),
            $this->createTextField('geoLookupStatus', 'Statut géoloc'),
            $this->createTextField('country', 'Pays'),
            $this->createTextField('region', 'Région'),
            $this->createTextField('city', 'Ville'),
            $this->createTextField('isp', 'FAI (ISP)'),
            $this->createTextField('org', 'Organisation'),
            $this->createTextField('asn', 'ASN'),
            CodeEditorField::new('requestHeaders', 'En-têtes HTTP')
                ->setLanguage('javascript')
                ->formatValue(static fn (?array $value) => $value ? json_encode($value, JSON_PRETTY_PRINT) : null),
        ];
    }

    /**
     * Journal en lecture seule : aucune page NEW/EDIT n'est exposée par le contrôleur.
     *
     * @param AdminContext<object>|null $context
     *
     * @return FieldInterface[]
     */
    protected function buildFormFields(?AdminContext $context = null): array
    {
        return [];
    }
}
