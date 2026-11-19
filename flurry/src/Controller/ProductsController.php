<?php

namespace App\Controller;

use App\Entity\Products;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class ProductsController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/products", name="get_all_products", methods={"GET"})
     */
    public function getAllProducts(): JsonResponse
    {
        $items = $this->entityManager->getRepository(Products::class)->findAll();
        if (!$items) {
            return $this->json(['error' => 'Products not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($items);
    }

    /**
     * @Route("/products/{id}", name="get_product", methods={"GET"})
     */
    public function getOneProductById(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Products::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($item);
    }

    /**
     * @Route("/products", name="add_product", methods={"POST"})
     */
    public function addNewProduct(Request $request): JsonResponse
    {
        $data = $request->toArray();
        if (!isset($data['title']) || !array_key_exists('price', $data)) {
            return $this->json(['error' => 'Title and price are required'], Response::HTTP_BAD_REQUEST);
        }

        $item = new Products();
        $this->applyFields($item, $data);
        $item->setDateCreate(new \DateTimeImmutable());
        $item->setDateModified(new \DateTime());

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json($item, Response::HTTP_CREATED);
    }

    /**
     * @Route("/products/{id}", name="edit_product", methods={"PUT", "PATCH"})
     */
    public function updateProductById(Request $request, int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Products::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $this->applyFields($item, $request->toArray());
        $item->setDateModified(new \DateTime());
        $this->entityManager->flush();

        return $this->json($item);
    }

    /**
     * @Route("/products/{id}", name="delete_product", methods={"DELETE"})
     */
    public function deleteProductById(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Products::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Product not found'], Response::HTTP_NOT_FOUND);
        }

        $title = $item->getTitle();
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $this->json(['message' => 'Product '.$title.' successfully deleted']);
    }

    private function applyFields(Products $item, array $data): void
    {
        $fieldSetters = [
            'title' => 'setTitle',
            'price' => 'setPrice',
            'product_image' => 'setProductImage',
            'product_type' => 'setProductType',
            'product_description' => 'setProductDescription',
            'quantity' => 'setQuantity',
            'likes' => 'setLikes',
            'tags' => 'setTags',
        ];

        foreach ($fieldSetters as $field => $setter) {
            if (array_key_exists($field, $data)) {
                $item->$setter($data[$field]);
            }
        }
    }
}
