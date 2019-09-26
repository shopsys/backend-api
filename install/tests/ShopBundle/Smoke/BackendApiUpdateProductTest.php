<?php

declare(strict_types=1);

namespace Tests\ShopBundle\Smoke;

use Shopsys\FrameworkBundle\Component\Domain\Domain;
use Shopsys\ShopBundle\DataFixtures\Demo\ProductDataFixture;
use Tests\ShopBundle\Test\OauthTestCase;

/**
 * This test must not extend TransactionFunctionalTestCase because it mustn't use transaction
 * If it is run in transaction, tokens don't work and test fails
 */
class BackendApiUpdateProductTest extends OauthTestCase
{
    /**
     * @var \Shopsys\FrameworkBundle\Component\Domain\Domain
     */
    private $domain;

    protected function setUp()
    {
        parent::setUp();
        $this->domain = $this->getContainer()->get(Domain::class);
    }

    public function testUpdateProductCompletely(): void
    {
        $uuid = $this->createProduct();

        $namesByLocale = [];
        $shortDescriptionsByDomainId = [];
        $longDescriptionsByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $namesByLocale[$domainConfig->getLocale()] = 'Changed name';
            $shortDescriptionsByDomainId[$domainConfig->getId()] = 'Changed short description';
            $longDescriptionsByDomainId[$domainConfig->getId()] = 'Changed long description';
        }

        $updateData = [
            'name' => $namesByLocale,
            'hidden' => false,
            'sellingDenied' => false,
            'sellingFrom' => null,
            'sellingTo' => null,
            'catnum' => 'co changed',
            'ean' => 'E changed',
            'partno' => 'P changed',
            'shortDescription' => $shortDescriptionsByDomainId,
            'longDescription' => $longDescriptionsByDomainId,
        ];

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), $updateData);

        $this->assertSame(204, $response->getStatusCode());

        $foundProduct = $this->getProduct($uuid);
        $this->assertSame($updateData, $foundProduct);
    }

    public function testUpdateOnlySingleField(): void
    {
        $uuid = $this->createProduct();

        $namesByLocale = [];
        $shortDescriptionsByDomainId = [];
        $longDescriptionsByDomainId = [];
        foreach ($this->domain->getAll() as $domainConfig) {
            $namesByLocale[$domainConfig->getLocale()] = 'Changed name';
            $shortDescriptionsByDomainId[$domainConfig->getId()] = 'Short description';
            $longDescriptionsByDomainId[$domainConfig->getId()] = 'Long description';
        }
        $updateData = [
            'name' => $namesByLocale,
        ];

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), $updateData);

        $this->assertSame(204, $response->getStatusCode());

        $expected = [
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

        $foundProduct = $this->getProduct($uuid);
        $this->assertSame($expected, $foundProduct);
    }

    public function testUpdateNothingChangesNothing(): void
    {
        $uuid = $this->createProduct();
        $expected = $this->getProduct($uuid);

        $updateData = [];

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), $updateData);

        $this->assertSame(204, $response->getStatusCode());

        $foundProduct = $this->getProduct($uuid);
        $this->assertSame($expected, $foundProduct);
    }

    public function testValidationError(): void
    {
        $uuid = $this->createProduct();

        $updateData = [
            'sellingFrom' => '2019-01-01',
        ];

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), $updateData);

        $this->assertSame(400, $response->getStatusCode());
    }

    public function testCannotUpdateVariant(): void
    {
        $variant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '75');
        $uuid = $variant->getUuid();

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), []);

        $this->assertSame(400, $response->getStatusCode());

        $message = json_decode($response->getContent(), true)['message'];
        $this->assertSame('cannot update/delete variant/main variant, this functionality is not supported yet', $message);
    }

    public function testCannotUpdateMainVariant(): void
    {
        $mainVariant = $this->getReference(ProductDataFixture::PRODUCT_PREFIX . '149');
        $uuid = $mainVariant->getUuid();

        $response = $this->runOauthRequest('PATCH', sprintf('/api/v1/products/%s', $uuid), []);

        $this->assertSame(400, $response->getStatusCode());

        $message = json_decode($response->getContent(), true)['message'];
        $this->assertSame('cannot update/delete variant/main variant, this functionality is not supported yet', $message);
    }

    /**
     * @return string
     */
    private function createProduct(): string
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

        $response = $this->runOauthRequest('POST', '/api/v1/products', $product);

        $location = $response->headers->get('Location');
        return $this->extractUuid($location);
    }

    /**
     * @param string $uuid
     * @return array
     */
    private function getProduct(string $uuid): array
    {
        $response = $this->runOauthRequest('GET', '/api/v1/products/' . $uuid);
        $this->assertSame(200, $response->getStatusCode());
        $data = json_decode($response->getContent(), true);
        unset($data['uuid']);
        return $data;
    }
}
