<?php

namespace App\Controller;

use App\Entity\Menu;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/api", name="api_")
 */
class MenuController extends AbstractController
{
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    /**
     * @Route("/menu", name="get_all_positions", methods={"GET"})
     */
    public function getAllPositions(): JsonResponse
    {
        $items = $this->entityManager->getRepository(Menu::class)->findAll();
        if (!$items) {
            return $this->json(['error' => 'Positions not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($items);
    }

    /**
     * @Route("/menu/{id}", name="get_position", methods={"GET"})
     */
    public function getOnePositionById(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Menu::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Position not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($item);
    }

    /**
     * @Route("/menu", name="add_position", methods={"POST"})
     */
    public function addNewPosition(Request $request): JsonResponse
    {
        $data = $request->toArray();
        if (!isset($data['title']) || !array_key_exists('price', $data)) {
            return $this->json(['error' => 'Title and price are required'], Response::HTTP_BAD_REQUEST);
        }

        $item = new Menu();
        $this->applyFields($item, $data);
        $item->setDateCreate(new \DateTimeImmutable());
        $item->setDateModified(new \DateTime());

        $this->entityManager->persist($item);
        $this->entityManager->flush();

        return $this->json($item, Response::HTTP_OK);
    }

    /**
     * @Route("/menu/{id}", name="edit_position", methods={"PUT", "PATCH"})
     */
    public function updatePositionById(Request $request, int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Menu::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Position not found'], Response::HTTP_NOT_FOUND);
        }

        $this->applyFields($item, $request->toArray());
        $item->setDateModified(new \DateTime());
        $this->entityManager->flush();

        return $this->json($item);
    }

    /**
     * @Route("/menu/{id}", name="delete_position", methods={"DELETE"})
     */
    public function deletePostionById(int $id): JsonResponse
    {
        $item = $this->entityManager->getRepository(Menu::class)->find($id);
        if (!$item) {
            return $this->json(['error' => 'Position not found'], Response::HTTP_NOT_FOUND);
        }

        $title = $item->getTitle();
        $this->entityManager->remove($item);
        $this->entityManager->flush();

        return $this->json(['message' => 'Position '.$title.' deleted successfully.']);
    }

    private function applyFields(Menu $item, array $data): void
    {
        $fieldSetters = [
            'title' => 'setTitle',
            'price' => 'setPrice',
            'description' => 'setDescription',
            'image' => 'setImage',
        ];

        foreach ($fieldSetters as $field => $setter) {
            if (array_key_exists($field, $data)) {
                $item->$setter($data[$field]);
            }
        }
    }
}
