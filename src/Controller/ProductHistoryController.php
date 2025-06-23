<?php

declare(strict_types=1);

namespace App\Controller;

use App\Entity\Product;
use App\Enum\Role;
use Doctrine\ORM\EntityManagerInterface;
use Gedmo\Loggable\Entity\LogEntry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted(Role::ADMIN->value)]
class ProductHistoryController extends AbstractController
{
    #[Route('history/product/', name: 'product_history_index')]
    public function index(EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(LogEntry::class);
        $logs = $repo->findBy(['objectClass' => Product::class], ['loggedAt' => 'ASC']);

        // Regrouper les logs par objectId
        $groupedLogs = [];
        foreach ($logs as $log) {
            $groupedLogs[$log->getObjectId()][] = $log;
        }

        $processedLogs = [];

        // Pour chaque produit, calculer old/new
        foreach ($groupedLogs as $productLogs) {
            $previousValues = [];

            foreach ($productLogs as $log) {
                $logData = [];
                foreach ($log->getData() as $field => $newValue) {
                    $oldValue               = $previousValues[$field] ?? 'N/A';
                    $logData[$field]        = ['old' => $oldValue, 'new' => $newValue];
                    $previousValues[$field] = $newValue;
                }
                $log->setData($logData);
                $processedLogs[] = $log;
            }
        }

        usort($processedLogs, fn ($a, $b) => $b->getLoggedAt() <=> $a->getLoggedAt());

        return $this->render('product/history.html.twig', [
            'logs' => $logs,
        ]);
    }

    #[Route('history/product/{id}', name: 'product_history_show')]
    public function show(Product $product, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(LogEntry::class);
        $logs = $repo->findBy(['objectId' => $product->getId()], ['loggedAt' => 'ASC']);

        $previousValues = []; // Stocke les valeurs précédentes

        foreach ($logs as $log) {
            $logData = [];
            foreach ($log->getData() as $field => $newValue) {
                $oldValue = $previousValues[$field] ?? 'N/A'; // L'ancienne valeur est celle déjà stockée

                $logData[$field] = ['old' => $oldValue, 'new' => $newValue];

                $previousValues[$field] = $newValue; // Met à jour pour la prochaine itération
            }
            $log->setData($logData);
        }

        // Inverser les logs pour afficher du plus récent au plus ancien
        $logs = array_reverse($logs);

        return $this->render('product/show_history.html.twig', [
            'product' => $product,
            'logs'    => $logs,
        ]);
    }
}
