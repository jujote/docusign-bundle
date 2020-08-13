<?php

/*
 * This file is part of the DocusignBundle.
 *
 * (c) Grégoire Hébert <gregoire@les-tilleuls.coop>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace DocusignBundle\EnvelopeCreator;

use DocuSign\eSign\Api\EnvelopesApi;
use DocuSign\eSign\Client\ApiClient;
use DocuSign\eSign\Configuration;
use DocusignBundle\EnvelopeBuilderInterface;
use DocusignBundle\Grant\GrantInterface;
use Symfony\Component\Routing\RouterInterface;

final class SendEnvelope implements EnvelopeBuilderCallableInterface
{
    public $grant;
    private $router;
    private $envelopeBuilder;

    public function __construct(EnvelopeBuilderInterface $envelopeBuilder, GrantInterface $grant, RouterInterface $router)
    {
        $this->grant = $grant;
        $this->router = $router;
        $this->envelopeBuilder = $envelopeBuilder;
    }

    /**
     * @throws \DocuSign\eSign\ApiException
     *
     * @return string|void
     */
    public function __invoke(array $context = [])
    {
        if ($context['signature_name'] !== $this->envelopeBuilder->getName()) {
            return;
        }

        $this->envelopeBuilder->setEnvelopesApi($this->setUpConfiguration());
        $this->envelopeBuilder->setEnvelopeId($this->envelopeBuilder->getEnvelopesApi()->createEnvelope((string) $this->envelopeBuilder->getAccountId(), $this->envelopeBuilder->getEnvelopeDefinition())->getEnvelopeId());
    }

    private function setUpConfiguration(): EnvelopesApi
    {
        $config = new Configuration();
        $config->setHost($this->envelopeBuilder->getApiUri());
        $config->addDefaultHeader('Authorization', 'Bearer '.($this->grant)());

        return new EnvelopesApi(new ApiClient($config));
    }
}
