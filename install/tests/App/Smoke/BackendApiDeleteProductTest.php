<?php

declare(strict_types=1);

namespace Tests\App\Smoke;

use App\DataFixtures\Demo\ProductDataFixture;
use Ramsey\Uuid\Uuid;
use Tests\App\Test\OauthTestCase;

/**
 * This test must not extend TransactionFunctionalTestCase because it mustn't use transaction
 * If it is run in transaction, tokens don't work and test fails
 */
class BackendApiDeleteProductTest extends OauthTestCase
{
    public function testDeleteProductSuccess(): void
    {
        $namesByLocale = [];
        $shortDescriptionsByDomainId = [];
        $longDescriptionsByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $namesByLocale[$domainConfig->getLocale()] = 'Name';
            $shortDescriptionsByDomainId[$domainConfig->getId()] = 'Short description';
            $longDescriptionsByDomainId[$domainConfig->getId()] = 'Long description';
        }
        $product = [
            'name' => $namesByLocale,
            'hidden' => true,
            'sellingDenied' => true,
            'sellingFrom' => '2019-07-16T00:00:00+00:00',
            'sellingTo' => '2020-07-16T00:00:00+00:00',
            'catnum' => '123456 co',
            'ean' => 'E12346B',
            'partno' => 'P123456',
            'shortDescription' => $shortDescriptionsByDomainId,
            'longDescription' => $longDescriptionsByDomainId,
        ];
        $createResponse = $this->runOauthRequest('POST', '/api/v1/products', $product);

        $location = $createResponse->headers->get('Location');
        $uuid = $this->extractUuid($location);

        $response = $this->runOauthRequest('DELETE', sprintf('/api/v1/products/%s', $uuid));

        $this->assertSame(204, $response->getStatusCode());
    }

    public function testDeleteNotExistingProduct(): void
    {
        $response = $this->runOauthRequest('DELETE', sprintf('/api/v1/products/%s', Uuid::uuid4()->toString()));

        $this->assertSame(410, $response->getStatusCode());
    }

    public function testCannotDeleteVariant(): void
    {
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '75');
        $uuid = $variant->getUuid();

        $response = $this->runOauthRequest('DELETE', sprintf('/api/v1/products/%s', $uuid), []);

        $this->assertSame(400, $response->getStatusCode());

        $message = json_decode($response->getContent(), true)['message'];
        $this->assertSame('cannot update/delete variant/main variant, this functionality is not supported yet', $message);
    }

    public function testCannotDeleteMainVariant(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '149');
        $uuid = $mainVariant->getUuid();

        $response = $this->runOauthRequest('DELETE', sprintf('/api/v1/products/%s', $uuid), []);

        $this->assertSame(400, $response->getStatusCode());

        $message = json_decode($response->getContent(), true)['message'];
        $this->assertSame('cannot update/delete variant/main variant, this functionality is not supported yet', $message);
    }
}
