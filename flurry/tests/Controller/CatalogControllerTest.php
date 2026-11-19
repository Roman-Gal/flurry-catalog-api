<?php

namespace App\Tests\Controller;

use App\Controller\MenuController;
use App\Controller\ProductsController;
use App\Entity\Menu;
use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\HttpFoundation\Request;

final class CatalogControllerTest extends TestCase
{
    public static function catalogResources(): array
    {
        return [
            'menu' => [Menu::class, MenuController::class, 'updatePositionById', 'description', 'setDescription', 'getDescription'],
            'products' => [Products::class, ProductsController::class, 'updateProductById', 'product_description', 'setProductDescription', 'getProductDescription'],
        ];
    }

    /**
     * @dataProvider catalogResources
     */
    public function testPartialUpdatesPreserveOmittedFields(
        string $entityClass,
        string $controllerClass,
        string $updateMethod,
        string $field,
        string $setter,
        string $getter
    ): void {
        $entity = new $entityClass();
        $entity->setTitle('Original title');
        $entity->setPrice(12.50);
        $entity->$setter('Original description');

        $controller = $this->controllerFor($controllerClass, $entity);
        $request = Request::create('/', 'PATCH', [], [], [], [], json_encode([$field => 'Updated']));

        $response = $controller->$updateMethod($request, 1);

        self::assertSame(200, $response->getStatusCode());
        self::assertSame('Original title', $entity->getTitle());
        self::assertSame(12.50, $entity->getPrice());
        self::assertSame('Updated', $entity->$getter());
    }

    /**
     * @dataProvider catalogResources
     */
    public function testExplicitNullClearsNullableFields(
        string $entityClass,
        string $controllerClass,
        string $updateMethod,
        string $field,
        string $setter,
        string $getter
    ): void {
        $entity = new $entityClass();
        $entity->setTitle('Original title');
        $entity->setPrice(12.50);
        $entity->$setter('Original description');

        $controller = $this->controllerFor($controllerClass, $entity);
        $request = Request::create('/', 'PATCH', [], [], [], [], json_encode([$field => null]));

        $controller->$updateMethod($request, 1);

        self::assertNull($entity->$getter());
        self::assertSame('Original title', $entity->getTitle());
    }

    public function testProductCreationAcceptsAnExplicitlyNullPrice(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects(self::once())->method('persist')->with(
            self::callback(static function (Products $product): bool {
                return $product->getTitle() === 'Sample' && $product->getPrice() === null;
            })
        );
        $entityManager->expects(self::once())->method('flush');

        $controller = new ProductsController($entityManager);
        $controller->setContainer(new Container());
        $request = Request::create('/', 'POST', [], [], [], [], '{"title":"Sample","price":null}');

        self::assertSame(201, $controller->addNewProduct($request)->getStatusCode());
    }

    private function controllerFor(string $controllerClass, object $entity): AbstractController
    {
        $repository = $this->createMock(EntityRepository::class);
        $repository->method('find')->with(1)->willReturn($entity);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->method('getRepository')->willReturn($repository);
        $entityManager->expects(self::once())->method('flush');

        $controller = new $controllerClass($entityManager);
        $controller->setContainer(new Container());

        return $controller;
    }
}
