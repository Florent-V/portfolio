<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class ProductControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private ObjectRepository $productRepository;
    private string $path = '/product/';

    protected function setUp(): void
    {
        $this->client            = static::createClient();
        $this->manager           = static::getContainer()->get('doctrine')->getManager();
        $this->productRepository = $this->manager->getRepository(Product::class);

        foreach ($this->productRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Product index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'product[name]'        => 'Testing',
            'product[description]' => 'Testing',
            'product[price]'       => 'Testing',
            'product[picture]'     => 'Testing',
            'product[stock]'       => 'Testing',
            'product[updatedAt]'   => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->productRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Product();
        $fixture->setName('My Title');
        $fixture->setDescription('My Title');
        $fixture->setPrice('My Title');
        $fixture->setPicture('My Title');
        $fixture->setStock('My Title');
        $fixture->setUpdatedAt('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Product');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Product();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setPrice('Value');
        $fixture->setPicture('Value');
        $fixture->setStock('Value');
        $fixture->setUpdatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'product[name]'        => 'Something New',
            'product[description]' => 'Something New',
            'product[price]'       => 'Something New',
            'product[picture]'     => 'Something New',
            'product[stock]'       => 'Something New',
            'product[updatedAt]'   => 'Something New',
        ]);

        self::assertResponseRedirects('/product/');

        $fixture = $this->productRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getPrice());
        self::assertSame('Something New', $fixture[0]->getPicture());
        self::assertSame('Something New', $fixture[0]->getStock());
        self::assertSame('Something New', $fixture[0]->getUpdatedAt());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Product();
        $fixture->setName('Value');
        $fixture->setDescription('Value');
        $fixture->setPrice('Value');
        $fixture->setPicture('Value');
        $fixture->setStock('Value');
        $fixture->setUpdatedAt('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/product/');
        self::assertSame(0, $this->productRepository->count([]));
    }
}
